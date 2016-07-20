<?php 
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");
if (!isset($_SESSION['user_id']) || !isset($user)) die('<a href="/signin">Пожалуйста, залогиньтесь</a>');


$get = explode('/',$_GET['get']);
$data = array();
if (sizeof($get)==2 && is_numeric($get[1])) 
{
	$article_id = (int)$get[1]; 
	$sql = "select * from articles where author_id=".$_SESSION['user_id']." and id=".$article_id;
	//echo $sql;
	$res =mysql_query($sql);	
	while ($row = mysql_fetch_assoc($res)) $data = $row;
	if (empty($data)) die("Статья с таким номером не найдена или вы не являетесь ее автором");
	
	$res = mysql_query('select category_id from articles_categories where article_id='.$article_id);
	while ($article_cat = mysql_fetch_assoc($res)) {
		$article_cats[]=$article_cat['category_id'];
	}
	
	$cathtml="";
	foreach ($cats[0] as $cat) {
		$cathtml.="<optgroup label='".$cat['name']."'>";
		foreach ($cats[$cat['id']] as $subcat) {
			$cathtml.="\n<option value='".$subcat['id']."'";
			if (in_array($subcat['id'],$article_cats)) $cathtml.=' selected';
			$cathtml.=">".$subcat['name']."</option>";
		}
		$cathtml.="</optgroup>";	
	}
	
} else {
	if (!$user->hasRight('publish_article')) {
		 die($labels['has_no_right_to_new_articles']);
	}
}

?>
<div class="row">
<form class="postform" method="post" id="poroshok">
<div class="col-12" style="padding:0 15px;">
 <?php $text= str_replace('<br>',"\n",$data['text']); ?>
 
   <textarea class="form-control poroshok" rows="1" style="width:400px;height:100px;" name="text"><?php echo $text;?></textarea>

<br />
<?php /*if (isset($article_id)) { */?>
<div style="margin-bottom:10px;"><input type="text" name="author_comment" placeholder="<?php echo $labels['author_comment_title'];?>" class="form-control" style="width:400px;" value="<?php echo $data['comment'];?>"/></div>
<br />
<?php /*}*/ ?>

<br />
<?php if (!empty($data['id'])) { ?>
 <input type="hidden" name="article_id" value="<?php echo $data['id'];?>" />
<?php } ?>
 <button type="submit" class="btn btn-lg btn-primary" name="save"><?php echo $labels['create_save']; ?></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-lg btn-primary" name="publish"><?php echo $labels['create_publish']; ?></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        
            </div><!--/span-->
           </form> 
          </div><!--/row-->
<br /><br/><br /><br/><br /><br/>