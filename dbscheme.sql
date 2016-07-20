SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*dbuser is fictional throughout the text, rename it with your own db user name*/
DELIMITER $$
CREATE DEFINER=`dbuser`@`localhost` PROCEDURE `addLog`(IN `event` VARCHAR(100), IN `obj` VARCHAR(100), IN `instring` VARCHAR(512), IN `outstring` VARCHAR(512), IN `intval` INT)
    NO SQL
BEGIN

INSERT INTO logger ( logger_event, logger_table, logger_instring, logger_outstring,  creation_date , logger_intval) 
VALUES(event, obj, instring, outstring , now(), intval );

END$$

CREATE DEFINER=`dbuser`@`localhost` PROCEDURE `rating_main`(IN `subj_id` BIGINT UNSIGNED, IN `subj_type` VARCHAR(20) CHARSET utf8)
    MODIFIES SQL DATA
BEGIN
	DECLARE _exclude nvarchar(20);
    DECLARE _replace nvarchar(20);
    DECLARE x INT UNSIGNED;
    DECLARE reg_names nvarchar(1024);
    DECLARE _rating nvarchar(256);
    DECLARE rating_id INT UNSIGNED;
    DECLARE _type INT UNSIGNED;
    DECLARE _nom INT UNSIGNED;
    
	IF (subj_type='article') THEN
    	SET _exclude='author';
        SET _replace='СТАТЬЯ';
        SET rating_id=1;
        SET _type=1;
        SET _nom=1;
    ELSE
    	SET _exclude='article';
        SET _replace='АВТОР';
        SET rating_id=2;
        SET _type=0;
        SET _nom=100;
    END IF;
    
   # select _exclude,_replace,rating_id,_type;
    
	DELETE FROM params;
    INSERT INTO params 
    SELECT * FROM rating_params WHERE type!=_exclude;
    UPDATE params SET value = REPLACE(value,_replace,subj_id);
    #select * from params;
   	SET x = 0;
    REPEAT
     SET x = x + 1;
     SET reg_names = (SELECT GROUP_CONCAT(p.name SEPARATOR '|') from params p);
     #select reg_names;
     #select 'before';
     #select * from params;
     call solver_rating(reg_names);
     
     SET _rating = (select name from params where id=rating_id);
     #select _rating;
     UNTIL x > 10 or (CAST( _rating AS UNSIGNED ) !=0 or substring(_rating,1,1)='0' or substring(_rating,1,1)='-') 
  	 END REPEAT;  
     #select _rating as 'rating_just_after_repeat';
     IF (CAST( _rating AS UNSIGNED ) !=0 or substring(_rating,1,1)='0'or substring(_rating,1,1)='-') then
     	# select 'updating rating',_rating;
  		 UPDATE rating SET rating = _rating	* _nom
         where subject_type=_type and subject_id=subj_id;
         #select concat('UPDATE rating SET rating = ',_rating,' where subject_type=',_type,' and subject_id=',subj_id);
     END IF;
END$$

CREATE DEFINER=`dbuser`@`localhost` PROCEDURE `rating_main_article`()
    MODIFIES SQL DATA
BEGIN
 DECLARE done BOOLEAN DEFAULT FALSE;
 DECLARE _id BIGINT UNSIGNED;
 DECLARE cur CURSOR FOR SELECT id FROM articles;
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET done := TRUE;
 OPEN cur;
 testLoop: LOOP
   FETCH cur INTO _id;
   IF done THEN
	  LEAVE testLoop;
   END IF;
   call rating_main(_id,'article');
 END LOOP testLoop;
 CLOSE cur;
END$$

CREATE DEFINER=`dbuser`@`localhost` PROCEDURE `rating_main_author`()
    MODIFIES SQL DATA
    COMMENT 'For each author calculate her rating'
BEGIN
 DECLARE done BOOLEAN DEFAULT FALSE;
 DECLARE _id BIGINT UNSIGNED;
 DECLARE cur CURSOR FOR SELECT id FROM authors;
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET done := TRUE;
 OPEN cur;
 testLoop: LOOP
   FETCH cur INTO _id;
   IF done THEN
	  LEAVE testLoop;
   END IF;
   call rating_main(_id,'author');
   
 END LOOP testLoop;
 CLOSE cur;
END$$

CREATE DEFINER=`dbuser`@`localhost` PROCEDURE `single_rating`(IN `subject_type` INT UNSIGNED, IN `subject_id` BIGINT UNSIGNED)
    MODIFIES SQL DATA
BEGIN
DECLARE _type nvarchar(20);
if subject_type=0 then
	set _type='author';
else 
	set _type='article';
end if;
call rating_main(subject_id,_type);
END$$

CREATE DEFINER=`dbuser`@`localhost` PROCEDURE `solver_rating`(IN `reg_names` VARCHAR(1024) CHARSET utf8)
    MODIFIES SQL DATA
BEGIN
  DECLARE done BOOLEAN DEFAULT FALSE;
  DECLARE _pid BIGINT UNSIGNED;
  DECLARE _name nvarchar(128);
  DECLARE _value nvarchar(1024);
  
  #Устанавливаем курсор по выборке строк, не содержащих параметры (вычисляемых сразу, без подстановок)
  
  
  DECLARE cur CURSOR FOR SELECT id, name, value FROM params where value REGEXP (reg_names) =0;
  
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done := TRUE;
  
  OPEN cur;
  
#Цикл по курсору
  testLoop: LOOP
	FETCH cur INTO _pid, _name, _value;
	IF done THEN
	  LEAVE testLoop;
	END IF;
#Тело процедуры. 
	
   	set @value=_value;
    
    #Вычисляем поле value, содержащее sql запрос, начинающийся с select, или число, и сохраняем результат в переменную @r. Если value - число, подставляем перед ним слово select для корректного запроса.
    SET @value = replace(concat('set @r2= (select ',@value,');'),'select select','select');
   	#select _name, @value;
	prepare stmt2 from @value ;
	execute stmt2;
	DEALLOCATE PREPARE stmt2;
    IF @r2 is NULL then set @r2=0;
    END IF;
    
    #Заменяем имя параметра на вычисленное значение по всей таблице, включая имена и значения других параметров, и собственное имя.
	UPDATE params SET value=replace(value,_name,@r2), name=replace(name,_name,@r2);
   #select 'after update';
   #select * from params;
    #Удаляем уже вычисленные строки, т.е. те, у которых поля "name" это число.
  	DELETE from params where immortal=0 and (CAST( name AS UNSIGNED ) !=0 or substring(name,1,1)='0');
    #select 'after delete';
    #select * from params;
    
  END LOOP testLoop;

  CLOSE cur;
END$$

DELIMITER ;

CREATE TABLE IF NOT EXISTS `articles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint(20) unsigned NOT NULL,
  `title` varchar(1024) NOT NULL,
  `text` longtext NOT NULL,
  `comment` text NOT NULL,
  `tags` text NOT NULL,
  `status` tinyint(4) NOT NULL,
  `since` datetime NOT NULL,
  `last` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=333 ;
DROP TRIGGER IF EXISTS `delete_rating_when_article_deleted`;
DELIMITER //
CREATE TRIGGER `delete_rating_when_article_deleted` AFTER DELETE ON `articles`
 FOR EACH ROW BEGIN
DELETE from rating where subject_type=1 and subject_id=OLD.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `delete_votes_on_republish`;
DELIMITER //
CREATE TRIGGER `delete_votes_on_republish` AFTER UPDATE ON `articles`
 FOR EACH ROW BEGIN
	IF (OLD.status = 0 
    	AND NEW.status = 1) THEN
			DELETE from vote 
       	 	where subject_type=1 
            AND subject_id=NEW.id;
            DELETE from views
            where subject_type=1
            AND subject_id=NEW.id;
	END IF;
END
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `articles_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `article_category` (`article_id`,`category_id`),
  KEY `article_id` (`article_id`),
  KEY `cat_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `authors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(128) DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL COMMENT ' Имя',
  `patronym` varchar(256) DEFAULT NULL COMMENT 'Отчество',
  `lastname` varchar(256) DEFAULT NULL COMMENT 'Фамилия',
  `email` varchar(256) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `sex` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=undefined, 1=female, 2=male',
  `city` varchar(256) DEFAULT NULL,
  `since` datetime DEFAULT NULL,
  `last` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `about` text,
  `user_type` varchar(20) NOT NULL DEFAULT 'member',
  `role` varchar(255) DEFAULT NULL,
  `revote_count` int(11) NOT NULL DEFAULT '3',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=53 ;
DROP TRIGGER IF EXISTS `delete_rating_when_author_deleted`;
DELIMITER //
CREATE TRIGGER `delete_rating_when_author_deleted` AFTER DELETE ON `authors`
 FOR EACH ROW BEGIN
DELETE from rating where subject_type=0 and subject_id=OLD.id;
END
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `author_invites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint(20) unsigned NOT NULL,
  `invites` int(11) unsigned NOT NULL,
  `adder` bigint(20) unsigned NOT NULL,
  `when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;
CREATE TABLE IF NOT EXISTS `author_rating_hist` (
`mod_date` datetime
,`mod_type` char(1)
,`author_id` bigint(20) unsigned
,`nickname` varchar(128)
,`rating` float
);
CREATE TABLE IF NOT EXISTS `avg_rating` (
  `id` int(11) NOT NULL,
  `author` float DEFAULT NULL,
  `article` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `bans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint(20) unsigned NOT NULL,
  `admin_id` bigint(20) unsigned NOT NULL,
  `since` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `author_id` (`author_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned NOT NULL,
  `name` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint(20) unsigned NOT NULL,
  `article_id` bigint(20) unsigned NOT NULL,
  `text` text NOT NULL,
  `since` datetime NOT NULL,
  `last` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=68 ;

CREATE TABLE IF NOT EXISTS `favorites` (
  `author_id` bigint(20) unsigned NOT NULL,
  `article_id` bigint(20) unsigned NOT NULL,
  `last` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `author_article` (`author_id`,`article_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `invites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint(20) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `sent` datetime NOT NULL,
  `used` datetime DEFAULT NULL,
  `code` varchar(255) NOT NULL,
  `invited_author_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=89 ;

CREATE TABLE IF NOT EXISTS `labels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `alias` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3940 ;

CREATE TABLE IF NOT EXISTS `logger` (
  `logger_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `logger_event` varchar(50) DEFAULT NULL,
  `logger_table` varchar(50) DEFAULT NULL,
  `logger_instring` varchar(100) DEFAULT NULL,
  `logger_outstring` varchar(512) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `creation_date` datetime DEFAULT NULL,
  `logger_intval` int(10) unsigned DEFAULT NULL,
  `last_update_date` date DEFAULT NULL,
  PRIMARY KEY (`logger_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

CREATE TABLE IF NOT EXISTS `log_rating` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_date` datetime NOT NULL,
  `event_type` int(11) NOT NULL,
  `subject_id` bigint(20) NOT NULL,
  `subject_type` int(11) NOT NULL,
  `author_id` bigint(20) DEFAULT NULL,
  `vote` int(11) DEFAULT NULL,
  `voter_id` bigint(20) DEFAULT NULL,
  `new_rating` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20210 ;

CREATE TABLE IF NOT EXISTS `oauth_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` text CHARACTER SET utf8 NOT NULL,
  `network` text CHARACTER SET utf8 NOT NULL,
  `uid` varchar(255) CHARACTER SET utf8 NOT NULL,
  `email` text CHARACTER SET utf8 NOT NULL,
  `access_token` text CHARACTER SET utf8 NOT NULL,
  `first_name` text CHARACTER SET utf8 NOT NULL,
  `identity` text CHARACTER SET utf8 NOT NULL,
  `profile` text CHARACTER SET utf8 NOT NULL,
  `last_name` text CHARACTER SET utf8 NOT NULL,
  `verified_email` int(11) NOT NULL,
  `photo` text CHARACTER SET utf8 NOT NULL,
  `manual` text CHARACTER SET utf8 NOT NULL,
  `token_secret` text CHARACTER SET utf8 NOT NULL,
  `author_id` bigint(20) unsigned NOT NULL,
  `visited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=222 ;

CREATE TABLE IF NOT EXISTS `params` (
  `id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `name` varchar(128) CHARACTER SET utf8 NOT NULL,
  `value` text CHARACTER SET utf8 NOT NULL,
  `immortal` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `rating` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_type` tinyint(3) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `rating` float DEFAULT '0',
  `initial_rating` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subject` (`subject_type`,`subject_id`),
  KEY `subject_type` (`subject_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1048 ;
DROP TRIGGER IF EXISTS `trg_rating_del`;
DELIMITER //
CREATE TRIGGER `trg_rating_del` BEFORE DELETE ON `rating`
 FOR EACH ROW INSERT INTO rating_hist ( id, mod_type, mod_date, subject_type, subject_id, rating, initial_rating ) 
 VALUES (OLD.id, 'D', NOW(), OLD.subject_type, OLD.subject_id, OLD.rating, OLD.initial_rating )
//
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_rating_ins`;
DELIMITER //
CREATE TRIGGER `trg_rating_ins` AFTER INSERT ON `rating`
 FOR EACH ROW INSERT INTO rating_hist ( id, mod_type, mod_date, subject_type, subject_id, rating, initial_rating ) 
 VALUES (NEW.id, 'I', NOW(), NEW.subject_type, NEW.subject_id, NEW.rating, NEW.initial_rating )
//
DELIMITER ;
DROP TRIGGER IF EXISTS `trg_rating_upd`;
DELIMITER //
CREATE TRIGGER `trg_rating_upd` AFTER UPDATE ON `rating`
 FOR EACH ROW INSERT INTO rating_hist ( id, mod_type, mod_date, subject_type, subject_id, rating, initial_rating ) 
 VALUES (NEW.id, 'U', NOW(), NEW.subject_type, NEW.subject_id, NEW.rating, NEW.initial_rating )
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `rating_event` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned NOT NULL,
  `subject_type` int(10) unsigned NOT NULL,
  `event_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `processed` int(10) unsigned NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `rating_hist` (
  `hist_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mod_type` char(1) DEFAULT NULL,
  `mod_date` datetime DEFAULT NULL,
  `id` bigint(20) unsigned NOT NULL,
  `subject_type` tinyint(3) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `rating` float DEFAULT '0',
  `initial_rating` float DEFAULT NULL,
  PRIMARY KEY (`hist_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=55783 ;

CREATE TABLE IF NOT EXISTS `rating_scalar_params` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `value` varchar(255) CHARACTER SET utf8 NOT NULL,
  `type` int(10) unsigned NOT NULL COMMENT '0-основной, 1-технический, 2-не используется',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name_id` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=102 ;

CREATE TABLE IF NOT EXISTS `subjects` (
  `id` tinyint(3) unsigned NOT NULL,
  `name` varchar(20) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `views` (
  `subject_type` tinyint(3) unsigned NOT NULL COMMENT '0=author, 1=article',
  `subject_id` bigint(20) unsigned NOT NULL,
  `when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `viewer_id` bigint(20) unsigned NOT NULL,
  UNIQUE KEY `subject_viewer` (`subject_type`,`subject_id`,`viewer_id`),
  KEY `viewer_id` (`viewer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `vote` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `voter_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `subject_type` tinyint(3) unsigned NOT NULL COMMENT '0=author, 1=article, 2=comment',
  `grade` float NOT NULL,
  `since` datetime NOT NULL,
  `last` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `subject_type` (`subject_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1204 ;
DROP TABLE IF EXISTS `author_rating_hist`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbuser`@`localhost` SQL SECURITY DEFINER VIEW `author_rating_hist` AS select `r`.`mod_date` AS `mod_date`,`r`.`mod_type` AS `mod_type`,`r`.`subject_id` AS `author_id`,`a`.`nickname` AS `nickname`,`r`.`rating` AS `rating` from (`rating_hist` `r` join `authors` `a`) where ((`r`.`subject_type` = 0) and (`r`.`subject_id` = `a`.`id`)) order by `r`.`mod_date`;


ALTER TABLE `articles_categories`
  ADD CONSTRAINT `articles_categories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `articles_categories_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `author_invites`
  ADD CONSTRAINT `author_invites_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `bans`
  ADD CONSTRAINT `bans_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `bans_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `invites`
  ADD CONSTRAINT `invites_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`subject_type`) REFERENCES `subjects` (`id`);

DELIMITER $$
CREATE DEFINER=`dbuser`@`localhost` EVENT `recalc_revote_counts` ON SCHEDULE EVERY 1 MONTH STARTS '2014-10-01 00:00:01' ON COMPLETION PRESERVE ENABLE DO update authors set revote_count = (select GREATEST(3,round(count(1)*0.1)) from vote where voter_id=authors.id and YEAR(vote.since) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH) AND MONTH(vote.since) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH))$$

DELIMITER ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
