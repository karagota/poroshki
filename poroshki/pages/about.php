<?php

$res = mysql_query("SELECT * from articles where id=0");
if (mysql_num_rows($res)==1) $article = mysql_fetch_assoc($res);
$views_sql = "INSERT INTO views (subject_type, subject_id, viewer_id) values (1,".$article['id'].",".$user->user_id.") ON DUPLICATE KEY UPDATE `when` = NOW()";
mysql_query($views_sql);

?>
<div class="row">
<div class="col-12 article" style="padding:0 15px;" id="article-<?php echo $article['id'];?>">
  <?php /*<a href="/create/'.$article['id'].'" style="color:green;" id="id-'.$article['id'].'" title="Править"><span class="glyphicon glyphicon-pencil"></span></a>*/?>
  
  <br />
  <?php echo $article['text'];?>

 <br /> <br />
</div><!--/span-->
           
</div><!--/row-->