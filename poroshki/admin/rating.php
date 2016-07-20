<?php
if (!isset($user) || !$user->hasRight('admin')) echo 'Сюда нельзя!';
else {
if (isset($_POST['submit']))
{
	save_params();
}

?>
<form method="POST">
<?php 
	echo display_params() ;
	
?>
	<div class="col-12 col-sm-12 col-lg-12 rating" style="text-align:right;">
		<button type="submit" class="btn btn-lg btn-primary" name="submit">Сохранить</button>
		<br/><br/>
	</div>
</form>
<?php } 


function save_params()
{
	$params = $_POST['params'];
	//print_r($params);
 	foreach ($params as $key=>$value){
		$sql_params .= "('$key','".$value."') ,";
	}
	$sql_params = rtrim($sql_params,',');
	//echo "INSERT INTO rating_scalar_params (name,value) values ".$sql_params." on DUPLICATE KEY UPDATE name=VALUES(name),value=VALUES(value)";
	mysql_query("INSERT INTO rating_scalar_params (name,value) values ".$sql_params." on DUPLICATE KEY UPDATE name=VALUES(name),value=VALUES(value)");
}

function display_params() {
	$title="Параметры рейтинга";
	
	
	$template = <<<TPL
	<div class="col-12 col-sm-12 col-lg-12 rating" >
    <div class="panel panel-default" style="width:800px;">
        <div class="panel-heading">
            <h3 class="panel-title">$title</h3>
        </div>
        
        <div class="panel-body" style="width:800px;">
            <table border="0" class="rating-form">
TPL;
	$sql = "SELECT * from rating_scalar_params where type=0 ORDER BY id ASC";
	
		$res = mysql_query($sql);
		while ($row=mysql_fetch_assoc($res)) {
			$label=$row['description'];
			
			$template.= '<tr><td valign="top" style="width:200px;" ><label>'.$label.'</label></td>
		<td><textarea name="params['.$row['name'].']" rows="1" cols="40">'.$row['value'].'</textarea></td><td valign="top">';
		  
		 $template.= '</td></tr>';
		}
	$template.= '<tr><td colspan="2" style="height:100px;"><hr style="border-width: 2px;"/><h4>Технические параметры</h4></td></tr>';
	$sql = "SELECT * from rating_scalar_params where type=1 ORDER BY id ASC";
	
		$res = mysql_query($sql);
		while ($row=mysql_fetch_assoc($res)) {
			$label=$row['description'];
			
			$template.= '<tr><td valign="top" style="width:200px;" ><label>'.$label.'</label></td>
		<td><textarea name="params['.$row['name'].']" rows="1" cols="40">'.$row['value'].'</textarea></td><td valign="top">';
		  
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
