<?php
if (!isset($user) || !$user->hasRight('admin')) echo 'Сюда нельзя!';
else {
if (isset($_POST['submit']))
{
	save_params('article');
	save_params('author');
	save_params('common');
}

function save_params($type)
{
	$params = $_POST['params_'.$type];

   //если добавились новые параметры
	if (isset ($_POST['paramnames_'.$type]))
	   $params = array_merge($params,array_combine($_POST['paramnames_'.$type],$_POST['paramvalues_'.$type]));
	
	foreach ($params as $key=>$value){
		$sql_params .= "('$key','".$value."','".$type."') ,";
	}

	$sql_params = rtrim($sql_params,',');
	mysql_query("INSERT INTO rating_params (name,value,type) values ".$sql_params." on DUPLICATE KEY UPDATE name=VALUES(name),value=VALUES(value)");
}

function display_params($type) {
	if ($type=='article') $title="Расчет рейтинга статьи";
	elseif ($type=='author') $title="Расчет рейтинга автора";
	else $title="Обшие параметры рейтинга (необходимые и для рейтинга автора, и для рейтинга статьи)";
	$template = <<<TPL
	<div class="col-12 col-sm-12 col-lg-12 rating" >
    <div class="panel panel-default" style="width:800px;">
        <div class="panel-heading">
            <h3 class="panel-title">$title</h3>
        </div>
        
        <div class="panel-body" style="width:800px;">
            <table border="0" class="rating-form">
TPL;
	if ($type=='common') $sql = "SELECT * from rating_params where type='' or type='".$type."' order by id asc";
	else $sql = "SELECT * from rating_params where type='".$type."' order by id asc";
		$res = mysql_query($sql);
		while ($row=mysql_fetch_assoc($res)) {
			$label=$row['name'];
			if (strlen($label)>40) $label=substr($label,0,20).'&shy;'.substr($label,20,20).'&shy;'.substr($label,40,20).'&shy;'.substr($label,60);
			$template.= '<tr><td valign="top" style="width:200px;" ><label>'.$label.'</label></td>
		<td><textarea name="params_'.$type.'['.$row['name'].']" rows="6" cols="80">'.$row['value'].'</textarea></td><td valign="top">';
		  //if ($row['immortal']==0) echo '&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="delete["'.$row['name'].']">Удалить';
		  echo '</td></tr>';
		}
	$artype=$type.'[]';
	$template .= <<<TPL
                <tr><td colspan="3" style="text-align:right;"><a href="#" onclick="$(this).closest('tr').after('<tr><td valign=\'top\'><input type=\'text\' name=\'paramnames_$artype\' placeholder=\'Название\' size=\'40\'/></td><td colspan=\'2\'><textarea name=\'paramvalues_$artype\' placeholder=\'Формула\' rows=\'6\' cols=\'80\'/></textarea></tr>');return false;">Добавить параметр</a></td></tr>
                
            </table>
        </div>
        
    </div>
</div>
TPL;
return $template;
}
?>
<form method="POST">
<?php 
	echo display_params('common') ;
	echo display_params('article') ;
	echo display_params('author') ;
?>
	<div class="col-12 col-sm-12 col-lg-12 rating" style="text-align:right;">
		<button type="submit" class="btn btn-lg btn-primary" name="submit">Сохранить</button>
		<br/><br/>
	</div>
</form>
<?php } ?>
