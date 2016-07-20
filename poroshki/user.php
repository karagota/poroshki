<?php
require("config.php");
class User {
	var $name;
	var $nickname;
	var $email;
	var $first_name;
	var $last_name;
	var $invites;
	var $user_id;
	var $revote_count;
	var $role;
	
	function is_logged(){
		return isset($_SESSION['user_id']);
	}
	function is_banned() {
		if ($this->is_logged())
		{
			//echo "select author_id from bans where author_id=".$_SESSION['user_id'];
			$res = mysql_query("select author_id from bans where author_id=".$_SESSION['user_id']);
			while ($row = mysql_fetch_assoc($res)) {
			//echo '5';
				$this->signout();
				
				return true;
			}
		}
		return false;
	}
	function __construct()
	{
		$this->invites=4;
	}
	function authorize(){
	
		if (isset($_POST['token'])) return $this->authorize_ulogin(isset($_SESSION['code']));
		else if ($this->is_logged()){
            $this->authorize_uid();
			$sql = "UPDATE authors set last=NOW() where id='".$_SESSION['user_id']."'";
			
			mysql_query($sql);
        }
        return $this->is_logged();
	}
	function authorize_uid() {
		if (!isset($_SESSION['user_id'])) return false;
		if ($this->is_banned()) return false;
		$sql = "select * from authors where id=".$_SESSION['user_id'];
		$res = mysql_query($sql);
		$user = mysql_fetch_assoc($res);
		
		$this->first_name = $_SESSION['first_name']=$user['name'];
		$this->last_name = $_SESSION['last_name'] = $user['lastname'];
		$this->name = $_SESSION['name'] = $this->first_name.' '.$this->last_name;
		$this->nickname = $_SESSION['nickname'] = $user['nickname'];
		$this->email = $_SESSION['email'] = $user['email'];
		$this->user_id = $_SESSION['user_id'];
		$this->user_type = $_SESSION['user_type'] = $user['user_type'];
		$this->role = $_SESSION['role'] = $user['role'];
		
		$revote_sql = "SELECT revote_count from authors where id=".$this->user_id;
		$revote_res = mysql_query($revote_sql);
		while ($revote_row=mysql_fetch_assoc($revote_res))
		$this->revote_count = $revote_row['revote_count'];
			
		
		return true;
	}
	function authorize_ulogin($is_new=false){
		$labels = $GLOBALS['labels'];
		if (!isset($_POST['token'])) return false;

		$s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);

		$user = json_decode($s, true);
		
		$this->first_name = $_SESSION['first_name']=$user['first_name'];
		$this->last_name = $_SESSION['last_name'] = $user['last_name'];
		$this->name = $_SESSION['name'] = $user['first_name'].' '.$user['last_name'];
		$this->nickname = $_SESSION['nickname'] = $user['nickname'];
		$this->email = $_SESSION['email'] = $user['email'];
		$this->role = $_SESSION['role'] = $user['role'];
		$this->uid = $_SESSION['uid'] = $user['uid'];
		
		if (empty($user['uid'])) return false;
		if ($is_new) {
			
			$sql = "INSERT INTO `oauth_users` (`nickname`,`network`,`uid`,`email`,`access_token`,`first_name`,`identity`,`profile`,`last_name`,`verified_email`,`photo`,`manual`,`token_secret`) values ('".$user['nickname']."','".$user['network']."','".$user['uid']."','".$user['email']."','".$user['access_token']."','".$user['first_name']."','".$user['identity']."','".$user['profile']."','".$user['last_name']."','".$user['verified_email']."','".$user['photo']."','".$user['manual']."','".$user['token_secret']."') ON DUPLICATE KEY UPDATE `nickname`='".$user['nickname']."', `network`='".$user['network']."', `email`='".$user['email']."',`access_token`='".$user['access_token']."',`first_name`='".$user['first_name']."',`identity`='".$user['identity']."',`profile`='".$user['profile']."',`last_name`='".$user['last_name']."',`verified_email`='".$user['verified_email']."',`photo`='".$user['photo']."',`manual`='".$user['manual']."',`token_secret`='".$user['token_secret']."';";
			

			$r = mysql_query($sql);

			
			$sql2 = "SELECT `author_id` from `oauth_users` where `uid`='".$user['uid']."'";

			$res = mysql_query($sql2);
			while ($row = mysql_fetch_assoc($res) ) {
				if ($row['author_id']==0) {

					$sql_check_email = "SELECT name, lastname,nickname, id FROM authors where email = '".$user['email']."'";

					$res_check_email = mysql_query($sql_check_email);
					if (mysql_num_rows($res_check_email)>0)
					{
						$row_check = mysql_fetch_assoc($res_check_email);
						$author_id = $row_check['id'];
						$sql4 = "UPDATE oauth_users set author_id=".$author_id." where uid='".$user['uid']."'";
						mysql_query($sql4);
						
						$this->first_name = $_SESSION['first_name']=$row_check['name'];
						$this->last_name = $_SESSION['last_name'] = $row_check['lastname'];
						$this->name = $_SESSION['name'] = $row_check['name'].' '.$row_check['lastname'];
						$this->nickname = $_SESSION['nickname'] = $row_check['nickname'];
						$this->user_id = $_SESSION['user_id'] = $row_check['id'];
						$sql4 = "UPDATE authors set last=NOW() where id='".$row['author_id']."'";
						mysql_query($sql4);
						$this->authorize_uid();
						
					} else { 
					
						$sql3 = "INSERT INTO authors (`nickname`,`name`,`lastname`,`email`,`since`,`last`) (select `nickname`,`first_name`,`last_name`,`email`,`visited`,`visited` from oauth_users where uid=".$user['uid'].")";
						
						$res3 = mysql_query($sql3);
						
						$sql4 = "UPDATE oauth_users set author_id=last_insert_id() where uid='".$user['uid']."'";
						
						mysql_query($sql4);
						$sql5 = "select author_id from oauth_users where uid='".$user['uid']."'";
						
						$res5 = mysql_query($sql5);
						while ($row5 = mysql_fetch_assoc($res5)) {
							$this->user_id = $_SESSION['user_id'] = $row5['author_id'];
						}
						calculate_initial_rating_for_a_new_author($this->user_id);
						
					}
					
				} else {
					$this->user_id = $_SESSION['user_id'] = $row['author_id'];
					$this->authorize_uid();
					$sql4 = "UPDATE authors set last=NOW() where id='".$row['author_id']."'";
					mysql_query($sql4);
					
				}
				
			}
			if (isset($_SESSION['code'])) {
				$sql = "UPDATE invites set used=NOW(), invited_author_id=".$this->user_id." where code='".$_SESSION['code']."'";
				mysql_query($sql);
				
			}
		}

		else {
			$sql2 = "SELECT `author_id` from `oauth_users` where `uid`='".$user['uid']."'";
			//echo $sql2;
			$res = mysql_query($sql2);
			if (mysql_num_rows($res)==0) header("Location: ".$domain."/register");
			while ($row = mysql_fetch_assoc($res) ) {
				if ($row['author_id']!=0) {
					$this->user_id = $_SESSION['user_id'] = $row['author_id'];
					$this->authorize_uid();
					$sql4 = "UPDATE authors set last=NOW() where id='".$row['author_id']."'";
					//echo '<br>'.$sql4;
					mysql_query($sql4);
				} 
				else header("Location: ".$domain."/register");
			}
		}
		
		//echo '2';
		if ($this->is_banned()) {
			//echo $this->is_banned();
			echo '<span class="alert alert-danger><strong>'.$labels['youarebanned'].'&nbsp;</strong></span>';
			return false;
		}
		

		return true;
		
	}
	function signout() {
		  session_unset();
	}
    function hasRight($subject,$id=0){
	    if ($subject=="admin") {
			return ($this->user_type=='admin');
		}
        if ($subject=="publish_article") {
			$max_publish_temp = get_rating_parameters('max_publish_temp');
			$min_publish_temp = get_rating_parameters('min_publish_temp');
			
			if ($this->role=='editor') {
				$temp = $max_publish_temp;
			}
			else {
				$period = get_rating_parameters('period');
			
				$sql = "select avg(rating) as avg_rating from rating where subject_type=0 and subject_id in (select distinct author_id from articles where status=1 and since>(Now() - Interval ".$period." Day))";

				$res = mysql_query($sql);
				while($row = mysql_fetch_assoc($res)) $avg_rating = $row['avg_rating'];
				
				$sql = "select rating from rating where subject_type=0 and subject_id=".$this->user_id;
				$res = mysql_query($sql);
				while($row = mysql_fetch_assoc($res)) $rating = $row['rating'];
				if ($rating < $avg_rating) $temp = $min_publish_temp;
				elseif ($avg_rating==1) $temp = $max_publish_temp;
				else $temp = ceil(($max_publish_temp*($rating - $avg_rating) + $min_publish_temp * (1-$rating))/(1-$avg_rating));
				
			}
			$sql = "select count(1) as count from articles where author_id=".$this->user_id." and status=1 and DATE_FORMAT(since, '%Y-%m-%d') = CURDATE()";

			$res = mysql_query($sql);
			while($row = mysql_fetch_assoc($res)) $his_articles_today = $row['count'];
			
			return (($temp - $his_articles_today)>0);
		}
        elseif ($subject=="rate_article" || $subject=="rate_comment") {
			//if ($this->user_type=='admin') return true;
			//if ($this->user_id==13) return true;
			$min_articles = get_rating_parameters('min_articles');
			if ($this->role=='editor')
			$min_articles = get_rating_parameters('litcolleg_min_articles_to_vote');
			$period = get_rating_parameters('period');
			$max_vote_temp = get_rating_parameters('max_vote_temp');
			$min_vote_temp = get_rating_parameters('min_vote_temp');	
			$sql = "select since, since>(Now()-Interval ".$period." Day) as modern from articles where status=1 and author_id=".$this->user_id;
			//echo $sql.'<br>';
			$res = mysql_query($sql);
			$all_articles = 0;
			$modern_articles=0;
			while ($row = mysql_fetch_assoc($res)) {
					$all_articles += 1;
					if ($row['modern']==1) $modern_articles +=1;
			}
			//echo 'modern_articles='.$modern_articles."<br>";
			if ($all_articles<$min_articles || $modern_articles==0)
			$temp = $min_vote_temp;
			else $temp = $max_vote_temp;

			$sql = "select count(distinct subject_type, subject_id) as count from vote where voter_id=".$this->user_id." and DATE_FORMAT(since, '%Y-%m-%d') = CURDATE()";
			//echo $sql.'<br>';
			$res = mysql_query($sql);
			$votes = 0;
			while ($row = mysql_fetch_assoc($res)) {
					$votes = $row['count'];
			}
			return (($temp-$votes)>0);
		}
        elseif ($subject=="publish_comment") {}
        //elseif ($subject=="rate_comment") {}
        return true;
    }
    function voted($subject,$id) {
        
        $sql = "select sum(grade) as grade from vote where subject_type='".$subject."' and voter_id='".$this->user_id."' and subject_id='".$id."'";
        $res = mysql_query($sql);
        while ($row = mysql_fetch_assoc($res)) return $row['grade'];
        return (mysql_num_rows($res)>0);
    }
	function viewed($subject,$id) {
        
        $sql = "select 1 from views where subject_type='".$subject."' and viewer_id='".$this->user_id."' and subject_id='".$id."'";
        $res = mysql_query($sql);
       // while ($row = mysql_fetch_assoc($res)) return $row['grade'];
        return mysql_num_rows($res);
    }
	function can_revote() {
	
		return false;
		return true;
		$min_correct_count   =  get_rating_parameters('min_correct_count');
		$correct_percentage  =  get_rating_parameters('correct_percentage');
		
		$votes_last_month_sql = "select count(*) as votes from vote where voter_id=".$this->user_id." and vote.since >= DATE_FORMAT(CURRENT_DATE - INTERVAL 2 MONTH, '%Y-%m-01') and vote.since <= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m-01') group by subject_type, subject_id having sum(grade)<>0";
		$res = mysql_query($votes_last_month_sql);
		while ($row = mysql_fetch_assoc($res)) {
			$votes_last_month = $row['votes'];
		}
		
		$revote_count = max($min_correct_count,round($votes_last_month*$correct_percentage/100));
		
		$sql = "select count(1)-count(distinct subject_id,subject_type) as revotes from vote where voter_id=".$this->user_id." and YEAR(vote.since) = YEAR(CURRENT_DATE) AND MONTH(vote.since) = MONTH(CURRENT_DATE)";
		//echo $sql;
		$res = mysql_query($sql);
		while ($row = mysql_fetch_assoc($res)) {
			$revotes = $row['revotes'];
		}
		
		return ($revote_count-$revotes>0);
	} 
	function is_author($subject_type,$subject_id) {
		if ($subject_type==2) $table = 'comments';
		else $table='articles';
		$sql = "select id from $table where author_id=".$this->user_id." and id=$subject_id";
		return (mysql_num_rows(mysql_query($sql))>0);
	}
}


?>