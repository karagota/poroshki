<?php 
if (!isset($user) || !$user->hasRight('admin')) echo 'Сюда нельзя!';
else {
	$sql = "select * from articles where id=1";
	$res =mysql_query($sql);	
	while ($row = mysql_fetch_assoc($res)) $data = $row;
	if (empty($data)) die("Статья с таким номером не найдена");

?>
<div class="row" id="about">
<form class="postform" method="post">
<div class="col-12" style="padding:0 15px;">
 
  <div><br /></div>
  
  <br>
 
  <textarea class="form-control" rows="25" name="text"><?php echo $data['text'];?>
  </textarea>
<br />
<br />

<button type="submit" class="btn btn-lg btn-primary" name="wishes-save">Сохранить</button>
  <br />
<br />
    
    </div>
 

 
        
            </div><!--/span-->
           </form> 
          </div><!--/row-->
<br /><br/><br /><br/><br /><br/>
<?php } ?>