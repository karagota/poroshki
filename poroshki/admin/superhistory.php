<?php
include_once("./webstart.php");

if (!isset($user) || !$user->hasRight('admin')) echo 'Сюда нельзя!';
else {
	$sql = "select id, nickname from authors";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res)) {
		$authors[$row['id']] = $row['nickname'];
	}
	$sql = "select min(mod_date) as min_date, max(mod_date) as max_date from rating_hist";
	$res = mysql_query($sql);
	while ($row=mysql_fetch_assoc($res)) {
		$min_date = $row['min_date'];
		$max_date = $row['max_date'];
	}
	if (!isset($_POST['from'])) $from = date('d.m.Y',strtotime($min_date));
	else 
	{
		
		$from = mysqldate($_POST['from']);
		
	}
	
	if (!isset($_POST['to'])) $from = date('d.m.Y',strtotime($max_date));
	else $to = mysqldate($_POST['to'],1);
	
	
?>

<div class="col-12 col-sm-12 col-lg-12" >
<div class="panel panel-default" style="width:800px;">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $labels['history'];?></h3>
        </div>
        
        <div class="panel-body" style="width:800px;">
		<form method="POST">
			<select name="who"  class="form-control" style="width:200px;float:left;margin-right:20px;height:40px;" value="" >
				<option value="">Кто:</option>
				<?php
					foreach ($authors as $id=>$nickname) {
						$selected=($id==$_POST['who'])?'selected':'';
						echo '<option value="'.$id.'" '.$selected.'>'.$nickname.'</option>'."\n";
					}
				?>
			</select>
			<input type="text" name="from" class="datepicker form-control" style="width:100px;float:left;margin-right:20px;height:40px;" value="<?php echo $_POST['from'];?>" placeholder="С:"/>
			<input type="text" name="to"  class="datepicker form-control" style="width:100px;float:left;margin-right:20px;height:40px;" value="<?php echo $_POST['to'];?>" placeholder="По:"/>
			
			<button type="submit" class="btn btn-lg btn-primary" style="font-size:14px;" name="submit" >Сформировать отчет</button>
		</form>
		<BR/>
<?php
		if (isset($_POST['submit']))
		{
			display_report();
		}
		
?>
<br><div><button class="btn btn-lg btn-primary" style="font-size:14px;" onclick="$.get('/service/recalc_rating.php');" >Пересчитать <?php echo $labels['rating']; ?> <?php echo $labels['of_articles']; ?></button>&nbsp;&nbsp;&nbsp;<button class="btn btn-lg btn-primary" style="font-size:14px;"  onclick="$.get('/service/author_rating.php');">Пересчитать <?php echo $labels['rating']; ?> <?php echo $labels['of_authors']; ?></button></div>
		</div>
</div>
</div>
	

<?php } 
function mysqldate($date,$time=0) {
	if ($time==0) $t = '00:00:00';
	else $t='23:59:59';
	return date('Y-m-d '.$t,strtotime($date));
}
function rating($rating)
{
return round($rating*100);
}
function display_report(){
	global $authors,$labels;
	$from = mysqldate($_POST['from']);
	$to = mysqldate($_POST['to'],1);
	$who = (int)$_POST['who'];
	$sql_0 = "select `rating` from `rating_hist` where `subject_type`=0 and `subject_id`=$who and `mod_date`<='$from' order by `mod_date` DESC limit 0,1";
	//echo $sql_0.'<br>';
	$res = mysql_query($sql_0);
	while ($row = mysql_fetch_assoc($res)) $rating_init = $row['rating'];
	$note='';
	if (!isset($rating_init)) 
	{
		$note= $labels['rating'].' на дату '.$_POST['from'].' берется из исходного.';
		$sql = "select initial_rating from rating where subject_type=0 and subject_id=$who";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		$rating_init = $row['initial_rating'];
		
	}
	echo $labels['rating'].' автора <a href="/author/'.$who.'">'.$authors[$who].'</a> на '.$_POST['from'].'  равняется '.rating($rating_init);
	if ($note) echo '*';
	echo '.';
	
	$sql = "select grade, vote.since as since, voter_id, vote.subject_id as article,  r_auth.rating as rating, r_art.rating as art_rating from vote,   rating_hist r_art, rating r_auth  where r_auth.subject_type=0 and r_auth.subject_id=vote.voter_id and r_art.subject_type=1 and r_art.subject_id=vote.subject_id and r_art.mod_date = vote.since and vote.subject_type=1 and vote.subject_id in (select id from articles where author_id=$who) and since between '$from' and '$to'";
	echo '<br><br>'.$sql.'<br>';
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res)) {
		$votes[] = $row;
	}
	echo '<br><br><style>#history td {padding:5px;}</style><table id="history" border="1" >';
	echo '<tr><td>Дата</td><td>Избиратель</td><td>Рейтинг избирателя на тот момент</td><td>Номер статьи</td><td>Рейтинг статьи</td><td>Голос</td></tr>';
	foreach ($votes as $vote) {
		echo '<tr><td>'.$vote['since'].'</td><td><a href="'.$vote['voter_id'].'">'.$authors[$vote['voter_id']].'</a></td><td>'.rating($vote['rating']).'</td><td>'.$vote['article'].'</td><td>'.$vote['art_rating'].'</td><td>'.$vote['grade'].'</td></tr>';
	}
	echo '</table><br>';
	$sql_1 = "select `rating` from `rating_hist` where `subject_type`=0 and `subject_id`=$who and `mod_date`>='$to' order by `mod_date` ASC limit 0,1";
	//echo '<br>'.$sql_1.'<br>';
	$res = mysql_query($sql_1);
	while ($row = mysql_fetch_assoc($res)) $rating_last = $row['rating'];
	$note2='';
	if (!isset($rating_last)) 
	{
		$sql = "select rating from rating where subject_type=0 and subject_id=$who";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		$rating_last=$row['rating'];
		$note.='<br>**'.$labels['rating'].' на дату '.$_POST['to'].' берется из своего текущего значения.';
		$note2='**';
	}
	echo $labels['rating'].' автора <a href="/author/'.$who.'">'.$authors[$who].'</a> '.' на '.$_POST['to']. ' равняется '.rating($rating_last).$note2.'.';
	
	echo '<hr>';
	if ($note) echo '<div style="font-size:small;">* '.$note.'</div>';
	
	
	
}

?>