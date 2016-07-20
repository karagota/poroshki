<?php
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."infobar.php");

$res = mysql_query("SELECT * from articles where id=1");
if (mysql_num_rows($res)==1) $article = mysql_fetch_assoc($res);
$views_sql = "INSERT INTO views (subject_type, subject_id, viewer_id) values (1,".$article['id'].",".$user->user_id.") ON DUPLICATE KEY UPDATE `when` = NOW()";
mysql_query($views_sql);

?>
<div class="row pagecontent">
<div class="col-12 article" style="padding:0 15px;" id="article-<?php echo $article['id'];?>">
    <h3>

  </h3>
  
  <br />
  <p style="min-height:80px;margin-top:10px;">
  <?php echo $article['text'];?>
</p>
 <br /> <br />

<?php
function bad_class($comment) {
	$vote = (int)$comment['plus']-(int)$comment['minus'];
	if ($vote>=0) return '';
	else return 'bad bad'.max(-5,$vote);

}
	$sql = "select author_id as id,text,nickname, comments.id as comment_id, comments.since as since from comments,authors where authors.id=comments.author_id and article_id=".$article['id'];
	$sql_vote_plus = "select sum(grade) as grade_minus, subject_id from vote where subject_type=2 and subject_id in (select id from comments where article_id =".$article['id'].") and grade>0 GROUP BY subject_id";
	$sql_vote_minus = "select sum(grade) as grade_minus,subject_id from vote where subject_type=2 and subject_id in (select id from comments where article_id =".$article['id'].") and grade<0 GROUP BY subject_id";
	$votes = array();
	$res_vote_plus = mysql_query($sql_vote_plus);
	while ($row_vote_plus= mysql_fetch_assoc($res_vote_plus)) {
		$votes[$row_vote_plus['subject_id']]['plus']=$row_vote_plus['grade_plus'];
	}
	$res_vote_minus = mysql_query($sql_vote_minus);
	while ($row_vote_minus= mysql_fetch_assoc($res_vote_minus)) {
		$votes[$row_vote_minus['subject_id']]['minus']=$row_vote_minus['grade_minus'];
	}
	$res = mysql_query ($sql);
	$thumbs_margin_right=''; if($row['id']==$user->user_id) $thumbs_margin_right='margin-right:10px;';
	while ($row = mysql_fetch_assoc($res)) {
?>
	<div class="comment" style="margin-top:20px;">
		<?php if($row['id']!=$user->user_id) { ?>
			<div style="font-size:18px;text-align:right;float:right;" class="vote_comment" id="comment-<?php echo $row['comment_id']; ?>">
				<span class="thumbs">
					<?php echo thumbs_html(2,$row['comment_id'],$annulate,$user,$spaces=false);?>
				</span>
			</div>
			<?php } else {?>
			<div style="font-size:18px;text-align:right;float:right;" class="editcomment" id="editcomment-<?php echo $row['comment_id']; ?>">
				<a href="#" class="edit-comment"  title="<?php echo $labels['edit_comment'];?>"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;
				<a href="#" class="delete-comment"  title="<?php echo $labels['delete_comment']; ?>"><span class="glyphicon glyphicon-remove-circle" style="color:red;"></span></a>
			</div>
		<?php } ?>
	
	<br /><br />
	<div class="textcomment <?php echo bad_class($votes[$row['comment_id']]);?>"><?php echo $row['text']; ?></div>
	<div style="font-size:12px; text-align:right;float:right;">
		<a href="/authors/<?php echo $row['id']; ?>"><?php echo $row['nickname']; ?></a>, <?php echo date('d.m.Y',strtotime($row['since'])); ?> в <?php echo date('h:i:s',strtotime($row['since'])); ?>
		<div style="font-size:12px;text-align:right;<?php echo $thumbs_margin_right; ?>float:right;" class="vote_comment" id="comment-<?php echo $row['comment_id']; ?>" >
		</div>
		
		
	</div>
	<div style="clear:both;"><br /></div>
	
	<hr />
		</div> 
<?php } ?>


<?php if ($user->is_logged()  && $user->hasRight('publish_comment') && ($_GET['ac']!=1)) { ?>
<div>
<?php /*<h3><?php echo $labels['write_comment'];?></h3>*/ ?>
<form method="post" class="postform">
 <div id="alerts"></div>
    <div class="btn-toolbar" data-role="editor-toolbar" data-target="#editor">
      <div class="btn-group">
        <a class="btn dropdown-toggle" data-toggle="dropdown" title="Шрифт"><i class="icon-font"></i><b class="caret"></b></a>
          <ul class="dropdown-menu">
          </ul>
        </div>
      <div class="btn-group">
        <a class="btn dropdown-toggle" data-toggle="dropdown" title="Размер шрифта"><i class="icon-text-height"></i>&nbsp;<b class="caret"></b></a>
          <ul class="dropdown-menu">
          <li><a data-edit="fontSize 5"><font size="5">Огромный</font></a></li>
          <li><a data-edit="fontSize 3"><font size="3">Обычный</font></a></li>
          <li><a data-edit="fontSize 1"><font size="1">Маленький</font></a></li>
          </ul>
      </div>
      <div class="btn-group">
        <a class="btn" data-edit="bold" title="Жирным (Ctrl/Cmd+B)"><i class="icon-bold"></i></a>
        <a class="btn" data-edit="italic" title="Курсивом (Ctrl/Cmd+I)"><i class="icon-italic"></i></a>
        <a class="btn" data-edit="strikethrough" title="Перечеркнуть"><i class="icon-strikethrough"></i></a>
        <a class="btn" data-edit="underline" title="Подчеркнуть (Ctrl/Cmd+U)"><i class="icon-underline"></i></a>
      </div>
      <div class="btn-group">
        <a class="btn" data-edit="insertunorderedlist" title="Список"><i class="icon-list-ul"></i></a>
        <a class="btn" data-edit="insertorderedlist" title="Нумерация"><i class="icon-list-ol"></i></a>
        <a class="btn" data-edit="outdent" title="Уменьшить отступ (Shift+Tab)"><i class="icon-indent-left"></i></a>
        <a class="btn" data-edit="indent" title="Отступ (Tab)"><i class="icon-indent-right"></i></a>
      </div>
      <div class="btn-group">
        <a class="btn" data-edit="justifyleft" title="Отбить слева (Ctrl/Cmd+L)"><i class="icon-align-left"></i></a>
        <a class="btn" data-edit="justifycenter" title="По центру (Ctrl/Cmd+E)"><i class="icon-align-center"></i></a>
        <a class="btn" data-edit="justifyright" title="Отбить справа (Ctrl/Cmd+R)"><i class="icon-align-right"></i></a>
        <a class="btn" data-edit="justifyfull" title="По ширине (Ctrl/Cmd+J)"><i class="icon-align-justify"></i></a>
      </div>
      <div class="btn-group">
		  <a class="btn dropdown-toggle" data-toggle="dropdown" title="Гиперссылка"><i class="icon-link"></i></a>
		    <div class="dropdown-menu input-append">
			    <input class="span2 form-control" style="margin:3px; width:95%;" placeholder="URL" type="text" data-edit="createLink"/>
			    <button class="btn btn-primary" style="margin:3px; width:95%;" type="button">Добавить</button>
        </div>
        <a class="btn" data-edit="unlink" title="Удалить гиперссылку"><i class="icon-cut"></i></a>

      </div>
	   <div class="btn-group">
 	   <a class="btn" data-edit="undo" title="Отменить (Ctrl/Cmd+Z)"><i class="icon-undo"></i></a>
        <a class="btn" data-edit="redo" title="Вернуть (Ctrl/Cmd+Y)"><i class="icon-repeat"></i></a>
		</div>
       <input type="text" data-edit="inserttext" id="voiceBtn" x-webkit-speech="">
    </div>
  <div id="editor" ></div>
  <input type="hidden" id="editortext" name="text" />
  <input type="hidden"  name="id" value="<?php echo $article['id'];?>" />
  <br />
<button class="btn primary" type="submit" name="submit"><?php echo $labels['sumbit_comment'];?></button>
</form>
</div>
<br/><br/><br/><br/>
<?php }?>  


</div><!--/span-->
           
</div><!--/row-->