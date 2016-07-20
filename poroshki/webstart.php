<?php
/*== Запускаем сессию ==*/
session_start();
require("config.php");
//$_SESSION['user_id']=$vip_id; //Для отладочного захода под другим автором

/* Разрешаем пользователям отменять свой голос */
$annulate = 1;
/*== Подключаемся к БД ==*/
mysql_connect($db_host,$db_user,$db_pass);
mysql_select_db($db_name);
mysql_set_charset('utf8');
mb_internal_encoding("utf8");
date_default_timezone_set( $server_time_zone );


/*== Определяем файловую структуру ==*/
$root_path = "/poroshki/";
$docroot = $_SERVER['DOCUMENT_ROOT'].$root_path;
$pages_folder = "pages/";
$snippets_folder = "snippets/";
$admin_folder = "admin/";
$images_folder = "images/";
$ajax_folder = "ajax/";
$upper_navbar=$docroot.$snippets_folder."upper_navbar.php";
$filter_snippet =$docroot.$snippets_folder."filter.php";
/*== Создаем пользователя ==*/
include_once($docroot.'user.php');
$user = new User();
$user->authorize();
/*== Термины сайта ==*/
$labels_sql = "SELECT * from labels";
$labels_res = mysql_query($labels_sql);
$labels = array();
while ($labels_row = mysql_fetch_assoc($labels_res)) {
	$labels[$labels_row['name']] = $labels_row['alias'];
}

/*== Вспомогательные функции ==*/
function cyrillic_date($date,$format='%d %B %Y') {
if ($date=='0000-00-00') return 'Не указана';
	//setlocale(LC_ALL, 'ru_RU.UTF-8');
	//return str_ireplace(array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'),array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'), strftime($format,strtotime($date)));
	return str_ireplace(array('January','February','March','April','May','June','July','August','September','October','November','December'),array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'), strftime($format,strtotime($date)));
}

function ru_date($format, $date = false) {
	setlocale(LC_ALL, 'ru_RU.utf8');
	if ($date === false) {
		$date = time();
	}
	if ($format === '') {
		$format = '%e&nbsp;%bg&nbsp;%Y&nbsp;г.';
	}
	$months = explode("|", '|января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря');
	$format = preg_replace("~\%bg~", $months[date('n', $date)], $format);
	$res = strftime($format, $date);
	return $res;
}

function get_rating_parameters($paramname){
	$sql = "select value from rating_scalar_params where name='".$paramname."'";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res))
		return $row['value'];
	return false;
}


$inc = "articles";

/*== Парсим url для диспетчера разделов сайта ==*/
if (isset($_GET['get'])) {
	$get_array = explode("/",$_GET['get']);
	$inc = $_GET['inc']=array_shift($get_array);

	if (is_numeric($inc)) 
	{
		$_GET['id']=$inc;
		$inc = 'article';
		if ($_GET['id']==0) $inc='about';
	} else if (substr($inc,0,1)=='p' && is_numeric(substr($inc,1))) {
		
		$_GET['p']=substr($inc,1);
		$inc = "articles";
	}
	else {
		$_GET['id'] = array_shift($get_array);
		$inc = $_GET['inc'];
	}
}

/*== Выход пользователя с сайта ==*/
if ($inc=='signout') {
	$user -> signout();
	Header("Location: /");
}

$page = $inc;
$inc = $pages_folder.$inc.'.php';

//Нужно переименовать. inc используется везде, а здесь нужно первый inc назвать иначе.
if (!file_exists($docroot.$inc))  die();


/*== Обрабатываем фильтр ==*/
//Этот элемент нужно только для articles.php
{ $filter = array();
$cat_filter='';
if (!empty($_GET['cat'])) $cat_filter=$_GET['cat'];
$subcat_filter='';
if (!empty($_GET['subcat'])) $subcat_filter=$_GET['subcat'];
$tag_filter='';
if (!empty($_GET['tag'])) $tag_filter='%'.$_GET['tag'].'%';
$author_filter='';
if (!empty($_GET['author'])) $author_filter=$_GET['author'];
$text_filter='';
if (!empty($_GET['text'])) $text_filter='%'.$_GET['text'].'%'; 
$from_filter='';

if (isset($_GET['from'])) $from_filter = $_GET['from'];
//Если фильтр отправлен и пустой, значит он был специально очищен
if (empty($from_filter)) $from_filter = '1900-01-01 00:00:00'; 
else if (!empty($_GET['from'])){
	$from_filter=$_GET['from'];
	list($day,$month,$year) = explode('.',$from_filter);
	$from_filter = "$year-$month-$day 00:00:00";
} 

$to_filter=date("Y-m-d").' 23:59:59';
if (isset($_GET['to'])) $to_filter = $_GET['to'];
if (!empty($_GET['to'])) {  	
	$to_filter=$_GET['to'];
	list($day,$month,$year) = explode('.',$to_filter);
	$to_filter = "$year-$month-$day 23:59:59";
} else if (empty($to_filter)) $to_filter = '3000-01-01 23:59:59';

$checked_filter = '';
if (!empty($_GET['checked'])) $checked_filter=$_GET['checked'];

$where_filter =array();
if (!empty($cat_filter) && empty($subcat_filter)) $where_filter[]="`id` in (select article_id from articles_categories where category_id in (select id from categories where parent_id =$cat_filter))";
if (!empty($subcat_filter)) $where_filter[]="`id` in (select article_id from articles_categories where category_id=$subcat_filter)";
if (!empty($tag_filter)) $where_filter[]="`tags` like '$tag_filter'";
if (!empty($text_filter)) $where_filter[]="(`text` like '$text_filter' OR `title` like '$text_filter')";
if (!empty($author_filter)) $where_filter[]="(`author_id` in (select id from authors where nickname like '%$author_filter%' OR `name` like '%$author_filter%' OR lastname like '%$author_filter%'))";
if (!empty($checked_filter)){
	if ($checked_filter==1) {
		$where_filter[]="articles.id not in (select subject_id from views where viewer_id=".$user->user_id." and subject_type=1) and author_id !=".$user->user_id." ";
	}
	elseif ($checked_filter==2) {
		$voteper_sql="select value from rating_scalar_params where name='vote_period'";
		$voteper_res=mysql_query($voteper_sql);
		while ($voteper_row = mysql_fetch_assoc($voteper_res)) $voteper=$voteper_row['value'];
		
		$where_filter[]="articles.id not in (select subject_id from vote where voter_id=".$user->user_id." and subject_type=1 group by subject_type, subject_id having sum(grade)!=0) and author_id !=".$user->user_id." and articles.since>(Now() - Interval ".$voteper." day) ";
	}
} 
$where_filter[]="`since` between '$from_filter' AND '$to_filter'";
$where_filter[]= " articles.id > 1 ";

$where_filter = " and ".implode(' AND ',$where_filter);

}


/*== Записываем значения фильтра в сессию ==*/
//Этот элемент нужно только для articles.php
if (isset($_GET['to']) || isset($_GET['text'])) {
		$_SESSION['filter']=$where_filter;
		$_SESSION['from'] = date('d.m.Y',strtotime($from_filter));
		if ($_SESSION['from']=='01.01.1900') $_SESSION['from']='';
		
		if (isset($_GET['to'])) $_SESSION['to'] = date('d.m.Y',strtotime($to_filter));
		if ($_SESSION['to']=='01.01.3000') $_SESSION['to']='';
		$_SESSION['text']=$_GET['text'];
		$_SESSION['author']=$_GET['author'];
		$_SESSION['tag']=$_GET['tag'];
		$_SESSION['subcat'] = $_GET['subcat'];
		$_SESSION['cat'] = $_GET['cat'];
		$_SESSION['checked'] = $_GET['checked'];
	}
else if (!empty($_SESSION['filter'])) $where_filter = $_SESSION['filter'];

/*== Разбивка на страницы и формирование панели перелистывания ==*/
//Этот элемент нужно только для articles.php
$pagesize = $limit = 12;
$start = 0;
$firstpage = $lastpage = null;
if (!empty($_GET['p'])) {
	$pages = explode('-',$_GET['p']);
	$firstpage = array_shift($pages);
	$lastpage = array_shift($pages);
	if (!is_null($firstpage)) {
		$start = (int)($firstpage-1)*$limit;
	}
	if (!is_null($lastpage)) {
		$limit = (int)($lastpage-$firstpage+1)*$limit;
	}
}
$num = mysql_fetch_array(mysql_query("Select count(*) as num from articles where status=1 ".$where_filter));
//echo "Select count(*) as num from articles where status=1 ".$where_filter;

$count = $num["num"];
$maxpage = ceil($count/$pagesize);
$curpage = floor($start/$pagesize)+1;
if ($curpage>$maxpage) {
	$curpage=$maxpage;
	$start = (int)($curpage-1)*$limit;
}
//echo 'curpage='.$curpage.'<Br>';
if (is_null($firstpage)) $fistpage = $curpage;
if (is_null ($lastpage)) $lastpage = $curpage;


function truncate_article($text,$text_filter) {
$trunc = 40;
$text = strip_tags(str_replace('<br><br>','<br>',str_replace(array('<p>','</p>','<div>','</div>'),'<br>',$text)),'<br>');
$text_lines = explode('<br>',$text);
$res ='';
foreach ($text_lines as $line) {
	$res.=truncate($line,$trunc);
	if (sizeof($line)>$trunc) $res.='...';
	$res .='<br>';
}
 return highlight($res,$text_filter);
}

/*************** РАСЧЁТ РЕЙТИНГА  ****************/

function calculate_initial_rating_for_a_new_author($author_id)
{
	$since_sql = "SELECT since from authors where  id = $author_id";
	//echo $since_sql.'<br>';
	$since_res = mysql_query($since_sql);
	$since_row = mysql_fetch_assoc($since_res);
	$since = $since_row['since'];
	$id = $author_id;
	
	$period = get_rating_parameters('period');

	//рейтинг нового автора равен среднему рейтингу пишущих авторов или 0.5

	$writing_authors_sql = "SELECT d.new_rating as rating FROM log_rating d INNER JOIN (SELECT subject_id, subject_type, max(event_date) AS ev_date FROM log_rating d
					  where event_date <  '$since'  and (select max(since) from articles where articles.author_id=d.subject_id and articles.since < '$since')> '$since' - INTERVAL $period day and subject_type=0
					  GROUP BY subject_id, subject_type
					  ) a ON a.subject_id= d.subject_id AND a.subject_type = d.subject_type AND a.ev_date = d.event_date
					 order by d.event_date ASC";
	
	//echo $writing_authors_sql.'<Br>';

	$writing_res = mysql_query($writing_authors_sql);

	while ($writing_authors_row = mysql_fetch_assoc($writing_res)) {
		$new_rating += $writing_authors_row['rating'];
	}
	if (mysql_num_rows($writing_res)==0) $new_rating=0.5;
	else $new_rating = $new_rating/mysql_num_rows($writing_res);
	
	$sql_log = "INSERT INTO log_rating (id, event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) values (NULL, '$since', 2, $id, 0, NULL, 0, 0, $new_rating)";
	mysql_query($sql_log);

	
	mysql_query("insert into rating (subject_type, subject_id, rating, initial_rating) values (0, ".$author_id .", ".$new_rating.", ".$new_rating.")");
}

function calculate_initial_rating_for_a_new_article($article_id) {
	$since_sql = "SELECT since, author_id from articles where  id = $article_id";
	$since_res = mysql_query($since_sql);
	$since_row = mysql_fetch_assoc($since_res);
	$since = $since_row['since'];
	$author_id = $since_row['author_id'];

	$all_articles_sql ="SELECT d.subject_id, d.subject_type, d.event_date, d.new_rating as rating FROM log_rating d INNER JOIN (
	  SELECT subject_id, subject_type, max(event_date) AS ev_date
	  FROM log_rating d
	  where event_date <  '$since ' 
		and subject_type=1
	  GROUP BY subject_id, subject_type
	  ) a ON a.subject_id= d.subject_id AND a.subject_type = d.subject_type AND a.ev_date = d.event_date
	 order by d.event_date ASC";
	
	$all_articles_res = mysql_query($all_articles_sql);
	while ($all_articles_row = mysql_fetch_assoc($all_articles_res)) {
		$new_rating += $all_articles_row['rating'];
	}
	if (mysql_num_rows($all_articles_res)==0) $new_rating=0;
	else $new_rating = $new_rating/mysql_num_rows($all_articles_res);
	
	$sql_log = "INSERT INTO log_rating (id, event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) values (NULL, '$since', 3, $article_id, 1, $author_id, 0, 0, $new_rating)";
	mysql_query($sql_log);
	//echo $sql_log;
	//die();

	
	mysql_query("insert into rating (subject_type, subject_id, rating, initial_rating) values (1, ".$article_id .", ".$new_rating.", ".$new_rating.")");
}

function calculate_article_rating_for_a_new_vote($vote) {


	//массив авторов, голосовавших за статью, и их рейтинги на момент  того голосования
	$author_id_sql = "select author_id from articles where id=".$vote['subject_id'];
	$author_id_res = mysql_query($author_id_sql);
	$author_id_row = mysql_fetch_assoc($author_id_res);
	$author_id = $author_id_row['author_id'];
	
	$sql_log = "INSERT INTO log_rating (id, event_date, event_type, subject_id, subject_type, author_id, vote, voter_id, new_rating) values (NULL, '".$vote['since']."', 4, '".$vote['subject_id']."', 1, $author_id, ".$vote['vote'].", ".$vote['voter_id'].", 0)";
	//echo $sql_log."\r\n";
	$res = mysql_query($sql_log);
	$id = mysql_insert_id();
	
	$voters_sql = "select event_date, voter_id, vote from log_rating
	where subject_type=1 and subject_id = ".$vote['subject_id']." and event_type=4 and event_date <='".$vote['since']."'";
	$voters_res = mysql_query($voters_sql);
	$voters = array();
	while ($v_row = mysql_fetch_assoc($voters_res)) {
		$rating_sql = "select new_rating as rating from log_rating where subject_type=0 and subject_id = ".$vote['voter_id']." and event_date = (select max(event_date) from log_rating where subject_id=".$v_row['voter_id']." and subject_type=0 and event_date <= '".$v_row['event_date']."')";
		//echo $rating_sql.'<br>';
		$rating_res = mysql_query($rating_sql);
		$rating_row = mysql_fetch_assoc($rating_res);
		$v_row['rating'] = $rating_row['rating'];
		$voters[] = $v_row;
	}
	//if ($row['subject_id']==46) print_r($voters);

	$weighted_vote = 0;
	$weighted_vote_nom = 0;
	$weighted_vote_denom = 0;
	$num_voters = sizeof($voters);
	foreach ($voters as $voter)
	{
			$weighted_vote_nom += $voter['vote']*$voter['rating'];
			$weighted_vote_denom += $voter['rating'];
	}
	if ($weighted_vote_denom==0) echo 'Исключение! Сумма рейтингов авторов равна нулю!<br>';
	else {
			$weighted_vote = $weighted_vote_nom / $weighted_vote_denom;
	}
	$avg_rating_sql = "select new_rating from log_rating where event_type=3 and subject_type=1 and subject_id='".$vote['subject_id']."'";
	//echo $avg_rating_sql.'<br>';
	$avg_rating_res = mysql_query($avg_rating_sql);
	while ($avg_rating_row = mysql_fetch_assoc($avg_rating_res))
		$avg_rating = $avg_rating_row['new_rating'];
		
		//ВПОС
		$cor_sql="select value from rating_scalar_params where name='article_cor_weight'";
		$cor_res=mysql_query($cor_sql);
		while ($cor_row = mysql_fetch_assoc($cor_res)) $cor_weight=$cor_row['value'];

		//ВПОА
		$cor_sql="select value from rating_scalar_params where name='author_cor_weight'";
		$cor_res=mysql_query($cor_sql);
		while ($cor_row = mysql_fetch_assoc($cor_res)) $a_cor_weight=$cor_row['value'];
		
		$new_rating = $weighted_vote + $cor_weight/sqrt($num_voters) * ($avg_rating - $weighted_vote);
		
		$sql_log = "update log_rating set new_rating = $new_rating where id=$id";
		//echo $sql_log."\r\n";
		mysql_query($sql_log);

		$sql_rating = "update rating set rating = $new_rating where subject_type=1 and subject_id=".$vote['subject_id'];
		mysql_query("update rating set rating = $new_rating where subject_type=1 and subject_id=".$vote['subject_id']);
		//echo $sql_rating."\r\n";
	
}
?>