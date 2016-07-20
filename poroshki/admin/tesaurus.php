<?php
if (!isset($user) || !$user->hasRight('admin')) echo 'Сюда нельзя!';
else {
if (isset($_POST['submit']))
{
	save_labels();
}

?>
<form method="POST">
<?php 
	echo display_labels() ;
	
?>
	<div class="col-12 col-sm-12 col-lg-12 rating" style="text-align:right;">
		<button type="submit" class="btn btn-lg btn-primary" name="submit">Сохранить</button>
		<br/><br/>
	</div>
</form>
<?php } 


function save_labels()
{
	$labels = $_POST['labels'];
	//print_r($params);
 	foreach ($labels as $key=>$value){
		$sql_labels .= "('$key','".$value."') ,";
	}
	$sql_labels = rtrim($sql_labels,',');
	//echo "INSERT INTO rating_scalar_params (name,value) values ".$sql_params." on DUPLICATE KEY UPDATE name=VALUES(name),value=VALUES(value)";
	mysql_query("INSERT INTO labels (name,alias) values ".$sql_labels." on DUPLICATE KEY UPDATE name=VALUES(name),alias=VALUES(alias)");
}

function display_labels() {
	$title="Термины сайта";
	
	
	$template = <<<TPL
	<div class="col-12 col-sm-12 col-lg-12 rating" >
    <div class="panel panel-default" style="width:800px;">
        <div class="panel-heading">
            <h3 class="panel-title">$title</h3>
        </div>
        
        <div class="panel-body" style="width:800px;">
            <table border="0" class="rating-form">
TPL;
	$sql = "SELECT * from labels";
	
		$res = mysql_query($sql);
		while ($row=mysql_fetch_assoc($res)) {
			$label=$row['name'];
			
			$template.= '<tr><td valign="top" style="width:200px;" ><label>'.$label.'</label></td>
		<td><textarea name="labels['.$row['name'].']" rows="1" cols="40">'.$row['alias'].'</textarea></td><td valign="top">';
		  
		  echo '</td></tr>';
		}
	
	$template .= <<<TPL
            </table>
        </div>
        
    </div>
</div>
TPL;
return $template;
}
?>
