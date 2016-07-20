<?php
include_once("webstart.php");
function sanitize($text)
{
	return filter_input(INPUT_POST,$text, FILTER_SANITIZE_SPECIAL_CHARS);
}
if (isset($_POST['about-save'])) {

	if (!isset($user) || !$user->hasRight('admin')) 
		echo $labels['save_form_error'];
	else {
		$text =  mysql_real_escape_string($_POST['text']);
		//спустить текст вниз и оставить только кириллицу
		$text = strtolower($text);
		
		$last = date("Y-m-d h:i:s");
		$sql="UPDATE `articles` set `author_id` = '".$_SESSION['user_id']."', `text` = '$text', `last`='$last' where `id`=0;";
		//echo $sql.'<br>';
		mysql_query($sql);
		header("Location: ".$domain."/0");
		exit;
	}
}
else if (isset($_POST['wishes-save'])) {

	if (!isset($user) || !$user->hasRight('admin')) 
		echo $labels['save_form_error'];
	else {
		$text =  mysql_real_escape_string($_POST['text']);
		
		$last = date("Y-m-d h:i:s");
		$sql="UPDATE `articles` set `author_id` = '".$_SESSION['user_id']."', `text` = '$text', `last`='$last' where `id`=1;";
		//echo $sql.'<br>';
		mysql_query($sql);
		header("Location: ".$domain."/wishes");
		exit;
	}
}
else
if (isset($_POST['text'])) {
//$text = implode('<br>',$_POST['text']);
//echo $_POST['text'];
//echo "<br>";
/*echo '<pre>';
echo $_POST['text'];
echo '</pre>';
echo '<br>';*/
$text = mysql_real_escape_string(str_replace(array('<div>','</div>',"\r\n"),"<br>",$_POST['text']));
$text = str_replace('<br><br>','<br>',$text);
$text = mb_strtolower(strip_tags($text,'<br>'));
//отрезать последние все символы за исключением значимых
$text = preg_replace('/^(<br>)*/', "", $text);
$text = preg_replace('/(<br>)+$/', '', $text);


//$text = sanitize($text);
/*echo '<pre>';
echo $text;
echo '</pre>';*/
$since = date("Y-m-d H:i:s");
$last = $since;
$status = (isset($_POST['save']))?"0":"1";

$comm='';
$new_article=false;
//print_r($_POST['id']);
if (isset($_POST['id'])) {
        if (!$user->hasRight('publish_comment')) {
			header('Content-Type: text/html; charset=utf-8');
			
			die($labels['has_no_right_to_comment']);
		}
		$text = trim($text);
		if (empty($text)) {
			header('Content-Type: text/html; charset=utf-8');
			header('Content-Type: text/html; charset=utf-8');
			die($labels['no_text_in_comment']);
		}
		$id = (int)$_POST['id'];
		$sql = "INSERT INTO `comments` (`author_id`,`article_id`,`text`,`since`,`last`,`status`) values ('".$_SESSION['user_id']."','".$id."','".$text."','".$since."','".$last."','".$status."');";
		//echo $sql;
		
}
else {
		if ($status==1 && !$user->hasRight('publish_article')) {
			header('Content-Type: text/html; charset=utf-8');
			die($labels['has_no_right_to_new_articles']);
		}
		$article_is_published = false;
		$header=sanitize('header');
		$tags = sanitize('tags');
		foreach ( $_POST['cats'] as $cat) $cats[]=(int)$cat;
		
		$author_comment = sanitize('author_comment');
		if (isset($_POST['article_id'])) {
			$last_status_sql="select status from articles where id=".(int)$_POST['article_id'];
			$last_status_res = mysql_query($last_status_sql);
			$last_status_row= mysql_fetch_assoc($last_status_res);
			$last_status = $last_status_row['status'];
			if ($status==1 and $last_status==0) $article_is_published=true;
			$sql="UPDATE `articles` set `author_id` = '".$_SESSION['user_id']."', `title` = '$header', `text` = '$text', `comment`='$author_comment', `tags`='$tags', `status`='$status', `last`='$last' where `id`=".(int)$_POST['article_id'].";";
		}
		else if (isset($_POST['author_comment_article_id']))
		$sql="UPDATE `articles` set `comment`='$text', `last`='$last' where author_id='".$_SESSION['user_id']."' and `id`=".(int)$_POST['author_comment_article_id'].";";
		else
		{
			$sql = "INSERT INTO `articles` (`id`, `author_id`, `title`, `text`, `comment`, `tags`, `status`, `since`, `last`) VALUES (NULL, '".$_SESSION['user_id']."', '$header', '$text', '$author_comment', '$tags', '$status', '$since', '$last');";
			$new_article=true;
		}
		
	}
	//echo '<br>'.$sql.'<br>';
	$res = mysql_query($sql);
	if (isset($_POST['author_comment_article_id'])) $id = $_POST['author_comment_article_id'];
	else 
	if (!isset($_POST['article_id'])) {
		if (!isset($id)) $id = mysql_insert_id(); else $comm = '#comment-'.mysql_insert_id();
	}
	else $id = (int)$_POST['article_id'];

	if (($new_article && $status==1) || $article_is_published) calculate_initial_rating_for_a_new_article($id);

	if (!isset($_POST['id']) && !isset($_POST['author_comment_article_id'])) {
	$sql_cat = "INSERT INTO `articles_categories` (`article_id`, `category_id`) VALUES ";
		foreach ($cats as $cat) {
			$sql_cat .= "(".$id.", '".$cat."') ,";
		}
		$sql_cat = rtrim($sql_cat,',');
	}
	mysql_query($sql_cat);
	if ($status==0) header("Location: ".$domain."/drafts".$comm);
	else	header("Location: ".$domain."/$id".$comm);

	exit;
}

$invite_sent_for_good=false;
$error = '';
if (isset($_POST['invite_email1'])) {
	$invite_email1 =filter_input ( INPUT_POST , 'invite_email1' , FILTER_SANITIZE_EMAIL  );
	{
		$to = $invite_email1;
		$Name = $user->first_name.' '.$user->last_name;
		$subject = $labels['Invitation_phrase']." ".$sitename.' '.$labels['from_user'].' '.$Name;
		$code = uniqid("",true);
		$body = strip_tags($_POST['text_invite'])."\r\n".'<br/>'.$labels['here_link'].': '.$domain.'/register?code='.$code;
		
		$headers = 'From: Poroshok <'.$admin_email.'>' . "\r\n" ;
		$headers .='Reply-To: '. $to . "\r\n" ;
		$headers .='X-Mailer: PHP/' . phpversion();
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=UTF-8\r\n";   
		$_SESSION['invite_sent_for_good'] = false;
		if(mail($to, $subject, $body,$headers)) { 
		  $sql = "INSERT INTO invites (author_id,email,sent, code, used) values(".$_SESSION['user_id'].",'".$invite_email1."',now(),'".$code."',null)";
		  mysql_query($sql);
		  $_SESSION['invite_success'] = '<div class="col-xs-12 col-sm-9">'.$labels['invitation_to_user'].' '.$invite_email1.' '.$labels['sent'].'.<br><br><a href="/invite">'.$labels['invite_someone'].'</a></div>';
		  $_SESSION['invite_sent_for_good'] = true;
		} 
		else 
		{
			$_SESSION['invite_error'] ='<h3><span style="color:red;">'.$labels['send_invite_error'].'</span><br><br>'.$labels['invite_error_to_do'].'</h3><br/><br/>';
		}
	}
}
?>

