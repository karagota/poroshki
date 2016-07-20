<?php
if (isset($_POST['submit']))
{
    $params=array();
    $params2=array();
   $params = $_POST['params'];
    if (isset ($_POST['paramnames']))
   $params2 = array_combine($_POST['paramnames'],$_POST['paramvalues']);
   $params = array_merge($params,$params2);
;
   foreach ($params as $key=>$value){
    $sql_params .= "('$key','$value') ,";
   }
    $sql_params = rtrim($sql_params,',');
$sql = "INSERT INTO rating_params (name,value) values ".$sql_params." on DUPLICATE KEY UPDATE name=VALUES(name),value=VALUES(value)";

   mysql_query($sql);
}
?>
<div class="col-12 col-sm-12 col-lg-12 rating">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Расчет рейтинга</h3>
        </div>
        <form method="POST">
        <div class="panel-body">
            <table border="0" class="rating-form">
                <?php
                    $sql = "SELECT * from rating_params";
                    $res = mysql_query($sql);
                    while ($row=mysql_fetch_assoc($res)) {
                        $label=$row['name'];
                        if (strlen($label)>40) $label=substr($label,0,20).'&shy;'.substr($label,20,20).'&shy;'.substr($label,40,20).'&shy;'.substr($label,60);
                        echo '<tr>
                    <td valign="top" style="width:100px;" ><label>'.$label.'</label></td>
                    <td><textarea name="params['.$row['name'].']" rows="6" cols="80">'.$row['value'].'</textarea></td><td valign="top">';
                      if ($row['immortal']==0) echo '&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="delete["'.$row['name'].']">Удалить';
                      echo '</td>
                </tr>';
                    }
                ?>
                <tr><td colspan="3" style="text-align:right;"><a href="#" onclick="$(this).closest('tr').after('<tr><td valign=\'top\'><input type=\'text\' name=\'paramnames[]\' placeholder=\'Название\' size=\'40\'/></td><td colspan=\'2\'><textarea name=\'paramvalues[]\' placeholder=\'Формула\' rows=\'6\' cols=\'80\'/></textarea></tr>');return false;">Добавить параметр</a></td></tr>
                <tr><td colspan="3"><input type="submit" name="submit" value="Сохранить" /></td></tr>
            </table>
        </div>
        </form>
    </div>
</div>