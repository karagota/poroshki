<?php 
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."infobar.php");
?>
<p class="pull-right visible-xs">
	<button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas"><?php echo $labels['filter']; ?></button>
</p>
<?php

include_once($_SERVER['DOCUMENT_ROOT'].$root_path.$snippets_folder.'jumbo.php'); 
//require_once($_SERVER['DOCUMENT_ROOT']."dist/phphypher-0.1.2/hypher.php");
//$hy_ru = new phpHypher('phphypher-0.1.2/hyph_ru_RU.conf');

$content =  "<p  class='col-12 col-sm-12 col-lg-12 article' id='p$curpage' style='height:50px;'/>";

function truncate($str,$len){
	if (mb_strlen($str)<=$len) return $str;
	$trunc = mb_substr($str,0,$len);
	$res = mb_substr($str,0,mb_strrpos($trunc,' '));
	$res.='...';
	return $res;
}
function highlight($str,$search){
	$search = trim($search,'%');
	if (!empty($search))
	return mb_eregi_replace($search,'<i class="h">'.$search.'</i>',$str);
	return $str;

}

$lentasql = "Select articles.*,rating.rating from articles,rating where status=1 AND rating.subject_type=1 and rating.subject_id=articles.id ".$where_filter." ORDER BY rating DESC, `since` DESC LIMIT $start,$limit ";
//echo $lentasql;
$res = mysql_query($lentasql);
while ($article = mysql_fetch_assoc($res)) {
	$content .= '<div class="col-4 col-sm-6 col-lg-4 article" id="article-'.$article['id'].'">';
	$content.=infobar($article,$user,$annulate,$vote=1,$info=0);
	$content .='<p style="min-height:80px;margin-top:10px;"><a href="/'.$article['id'].'">'.truncate_article($article['text'],$text_filter).'</a></p>
	';
	$content.=infobar($article,$user,$annulate,$vote=0,$info=1);
	$content .= '<br /></div><!--/span-->';
}

function pagenumhtml($p,$curp,$label='',$class='') {
	return  '<li '.($p==$curp ? 'class="active"' : '').'><a href="/p'.$p.'" '.($class==''?'':'class="'.$class.'"').'>'.($label==''? $p: $label).'</a></li>';
}

function pagination($curpage,$maxpage,$firstpage,$lastpage) {
	$res = '';
	if ($maxpage==1) return $res;
		$curpage = $firstpage = $lastpage; //Заглушка против залипания номеров страниц при подгрузке без обновления страницы
		if ($firstpage>1) $res .= pagenumhtml($curpage-1,$curpage,'«');
		
		if ($curpage>3 ||($firstpage>1 && $lastpage-$firstpage>2)) {
			$res .= pagenumhtml(1,$curpage,'','visible-lg');
		}
		$middle = $curpage;
		if ($curpage>4 ||($firstpage>2 && $lastpage-$firstpage>2)) 
			$res .= pagenumhtml(ceil(max(1,$middle-2)/2),$curpage,'...','visible-lg');

		if ($lastpage-$firstpage>2) {
			$res .= pagenumhtml($firstpage,$firstpage);
			$res .= '<li class="active"><a href="#">...</a></li>';
			$res .= pagenumhtml($lastpage,$lastpage);
		} else
		for ($i=max(1,$middle-2);$i<=min($middle+2,$maxpage);++$i) {
			$class=(abs($i-$middle)==2)? "visible-lg visible-md visible-sm" :(abs($i-$middle)==1 ? "hidden-xs" : '');
			$res .= pagenumhtml($i,($firstpage<=$i && $i <=$lastpage)?$i:$curpage,'',$class);
		}

		if (max($curpage+2,$lastpage) < ($maxpage-1) ) 
			$res .= pagenumhtml(floor(($maxpage+min($middle+2,$maxpage))/2),$curpage,'...',"visible-lg");
		if (max($curpage+2,$lastpage) < $maxpage) $res .= pagenumhtml($maxpage,$curpage,'',"visible-lg");
		if ($lastpage<$maxpage) $res .= pagenumhtml($curpage +1,$curpage,'»',"");
		
	return $res;
}
?>
<div class="row pagecontent">
<?php echo $content; ?>
<div class="more"></div>
<div id ="pagination" style="text-align:center;clear:both;">
<?php if ($curpage<$maxpage) {?>
<div id="more" class="btn-group btn-group-justified" style="text-align:center;width:100%;"><div class="btn-group"><button type="button" id="loadmore" class="btn btn-default btn-primary load-<?php echo $curpage+1;?>" style="border-radius: 4px;" ><?php echo $labels['loadmore'];?></button></div></div>
<?php } ?>
<ul class="pagination pagination-lg" >
<?php	
echo pagination($curpage,$maxpage,$firstpage,$lastpage);
?>
</ul>
</div>
</div><!--/row-->	