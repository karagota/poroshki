
<?php
/*
$os       = php_uname('s');
$locale   = 'ru_RU';
$encoding = 'UTF-8';
$month    = '';

setlocale(LC_TIME, $locale . '.' . $encoding);

if ($os == 'FreeBSD')
    $month = mb_convert_case(strftime('%OB'), MB_CASE_TITLE, $encoding);
else
    $month = mb_convert_case(strftime('%B'),  MB_CASE_TITLE, $encoding);

echo $month . PHP_EOL;
*/
$first_date = '2014-12-02 15:43:48';
$last_date = max('2015-02-08 23:59:52','2015-02-04 23:45:36','2015-01-30 16:08:27');
$first_sunday = date('Y-m-d 00:00:01', strtotime('next sunday', strtotime($first_date)));
echo $first_sunday.'<br>'; 


$last_sunday = date('Y-m-d 00:00:01', strtotime('next sunday', strtotime($last_date)));
echo $last_sunday.'<br>';

echo strtotime($first_sunday);
echo '<br>';
echo strtotime($last_sunday);
echo '<br>';

//создаем массив дат, которые являются воскресеньями начиная с first_sunday и до last_sunday

for ($i=strtotime($first_sunday); $i<=strtotime($last_sunday); $i += 60*60*24*7)
{
	$sundays[]=date('Y-m-d 00:00:01',$i);
}

echo '<pre>';
print_r($sundays);
echo '</pre>';

?>