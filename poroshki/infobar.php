<?php
function grades($type,$id){
	$sql_plus = "SELECT sum(grade) FROM `vote` where subject_type=$type and subject_id=$id group by voter_id having sum(grade)>0";
	$res_plus = mysql_query($sql_plus);
	$grade_plus = mysql_num_rows($res_plus);

	$sql_minus = "SELECT sum(grade) FROM `vote` where subject_type=$type and subject_id=$id group by voter_id having sum(grade)<0";
	$res_minus = mysql_query($sql_minus);
	$grade_minus = mysql_num_rows($res_minus);

	$grade_all = $grade_plus - $grade_minus;
	
	$grades['plus']=$grade_plus;
	$grades['minus']=$grade_minus;
	$grades['all'] = $grade_all;
	return $grades;
}

function subject_is_outdated($type,$id) {
	if ($type==1) $table="articles"; else $table="comments";

	$voteper_sql="select value from rating_scalar_params where name='vote_period'";
	$voteper_res=mysql_query($voteper_sql);
	while ($voteper_row = mysql_fetch_assoc($voteper_res)) $voteper=$voteper_row['value'];

	$sql = "select since from $table where id=$id and since<(Now() - Interval ".$voteper." day)";
	
	return (mysql_num_rows(mysql_query($sql))>0) ;
}

function thumb($direction,$user_voted,$annulate,$subject_type,$subject_id,$user_is_author) {
	 $labels = $GLOBALS['labels'];
	 $title = ($direction=='up')? $labels['like'] : $labels['dislike'];
	 $voted = ($direction=='up')? "voted" : "voted-down";
	 $vote_class = 'vote-'.$direction;
	 $thumb_side = ($direction=='down')? "rotate" : '';
	 $vote_mark = ($direction=='up')? 1 : -1;
     $href_begin = '';
	 $href_end   = '';
	 if ($user_voted==$vote_mark && $annulate ) {
	 if ($subject_type==1)
		$title = $labels['undovote'];
	else $title=$labels['comment_undovote'];
	 }
	 else $voted='';
	 $grades = grades($subject_type,$subject_id);
 
	 $thumb = array();
	 $thumb['href'] = (($user_voted==false || ($annulate && $user_voted==$vote_mark)) && !$user_is_author && !subject_is_outdated($subject_type,$subject_id));
	 $thumb['title'] = $title;
	 $thumb['vote_class'] = $vote_class;
	 $thumb['voted'] = $voted;
	 $thumb['thumb_side']=$thumb_side;
	 $thumb['grade_plus']=$grades['plus'];
	 $thumb['grade_minus']=$grades['minus'];
	 $thumb['user_voted'] = $user_voted;	
	 
	 return $thumb;
}

function votestat_html($grade_plus,$grade_minus){
	return  "<span class='grade_plus'>$grade_plus</span> <span class='glyphicon glyphicon-thumbs-up'></span> <span class='glyphicon glyphicon-thumbs-up rotate'></span> <span class='grade_minus'>$grade_minus</span>";	
}

function vote_title($title,$grade_plus,$grade_minus,$user_voted){
	$vote_title = $title;
	if ($user_voted) 
		$vote_title.='<br/>'.votestat_html($grade_plus,$grade_minus);
	return $vote_title;			
}

function thumb_html($thumb,$spaces=true){	
	$thumb_side = $thumb['thumb_side'];
	$title = vote_title($thumb['title'],$thumb['grade_plus'],$thumb['grade_minus'],$thumb['user_voted']);
	
	$title = trim($title);
	if (!empty($title)) $title = 'title="'.$title.'"';
	
		
	
	$class_name= implode(' ',array('vote',$thumb['vote_class'],$thumb['voted']));
	$href = ($thumb['href']) ? ' ' : ' alone';
	$span_title = ($thumb['href']) ? ' ' : votestat_html($thumb['grade_plus'],$thumb['grade_minus']);
	$span_title = trim($span_title);
	if (!empty($span_title)) $span_title = 'title="'.$span_title.'"';
	
	
	$outdated_voted=''; 
	if (!$thumb['href'] && !empty($thumb['voted'])) $outdated_voted='style="color:black;"';
	
	$htmp_span = "<span class='glyphicon glyphicon-thumbs-up $thumb_side $href' $outdated_voted $span_title ></span>";
	$htmp_a = "<a href='#' $title class = '$class_name'>$htmp_span</a>";
	
	$lenta = ($thumb['href']) ? $htmp_a : $htmp_span;
	if ($spaces) $lenta .='&nbsp;&nbsp;';
	return  $lenta;
}

function thumbs_html($subject_type,$subject_id,$annulate,$user,$spaces=true){
	$user_voted = $user->voted($subject_type,$subject_id);
	$user_is_author = $user->is_author($subject_type,$subject_id);
	$thumb_up=thumb('up',$user_voted,$annulate,$subject_type,$subject_id,$user_is_author);
	$thumb_down=thumb('down',$user_voted,$annulate,$subject_type,$subject_id,$user_is_author);
	$htm ='';
	if ($user->is_logged()) {
		$htm .= thumb_html($thumb_up).thumb_html($thumb_down,$spaces);
		if ($spaces) $htm .='&nbsp;&nbsp;';
	}
	return $htm;
}

function infobar($article,$user,$annulate,$vote=1,$info=1) {
if (!isset($article['id'])) $article['id']=$article['article_id'];
if (!isset($article['since'])) $article['since']=$article['last'];
if (isset($article['a_id'])) $article['author_id'] = $article['a_id'];
	$labels = $GLOBALS['labels'];
	$subject_type=1;
	$subject_id = $article['id'];
	if ($user->is_logged()) {
		$username = $_SESSION['nickname'];
		$user_is_author=($article['author_id']==$user->user_id);
		$user_favored_this = (mysql_num_rows(mysql_query("select 1 from favorites where article_id=".$article['id']." and author_id=".$user->user_id))>0);
		
		$user_voted = $user->voted($subject_type,$subject_id);
		$user_viewed = $user->viewed($subject_type,$subject_id);
	} else 	{
		$user_is_author=false;
		$user_voted = false;
		$user_favored_this =0;
	}
	$pubdate = date("d.m.y", strtotime($article['since']));
	$pubtime = date('H:i:s', strtotime($article['since']));
	$float_left='';
	
	$auth_res = mysql_query("select nickname, name, lastname from authors where id=".$article['author_id']);
	
	$author=mysql_fetch_assoc($auth_res);
	
	$articles_res = mysql_query("select count(*) as num from articles where author_id=".$article['author_id']);
	while ($articles_row = mysql_fetch_assoc($articles_res)) $author['articles']=$articles_row['num'];
	
	$comments_res = mysql_query("select count(*) as num from comments where article_id=".$article['id']);
	while ($comments_row = mysql_fetch_assoc($comments_res)) $comments = $comments_row['num'];
	
	$fav=0;
	$fav_res = mysql_query("select count(*) as num from favorites where article_id=".$article['id']);
	while ($fav_row = mysql_fetch_assoc($fav_res)) $fav = $fav_row['num'];
	
	$views_res = mysql_query("select count(*) as num from views where subject_type=1 and subject_id=".$article['id']);
	while ($views_row = mysql_fetch_assoc($views_res)) $views = $views_row['num'];
	
	$favored = ''; $fav_style='-empty'; $fav_title=$labels['add_to_fav'];
	if ($user_favored_this) {$favored = 'favored'; $fav_style='';$fav_title=$labels['remove_from_fav'];}
	
	$rating_res =  mysql_query("select rating from rating where subject_type=0 and subject_id=".$article['author_id']);
	$rating ="0"; while ($rat = mysql_fetch_assoc($rating_res)) $rating = $rat['rating'];
	
	if ($vote) {
		if (!isset($article['rating'])) {
			$rat_sql = "select rating from rating where subject_type=1 and subject_id=".$article['id'];
			
			$rat_res = mysql_query($rat_sql);
			while ($row = mysql_fetch_assoc($rat_res)) $rat=round($row['rating']*500+500);
			
		}
		else if (isset($article['rating'])) {
			$rat = round($article['rating']*500+500);
		}
		if ($info ) $float_left='bar_left'; 
		
		$grades = grades($subject_type,$subject_id);
		$grades_overall = $grades['plus']+$grades['minus'];
		
		$lenta ='<div class="tooltip-demo tooltip-top votebar '.$float_left.'"><span class="glyphicon glyphicon-stats" style="color:#428bca;" title="'.$labels['grades_overall'].': '.$grades_overall.'"></span>&nbsp;<span class="rating" title="'.$labels['grades_overall'].': '.$grades_overall.'">'.$rat.'</span>&nbsp;&nbsp;&nbsp;&nbsp;';
	
		$lenta .='<span class="thumbs">';
		if ($user->is_logged()) {
			$lenta .= thumbs_html($subject_type,$subject_id,$annulate,$user);
		}
		$lenta .='</span>';
		if ($user->is_logged()) {
		
		$author_has_viewed = (mysql_num_rows(mysql_query("select 1 from views where subject_type = 1 and subject_id=".$article['id']." and viewer_id=".$user->user_id))>0);
		
		if (!$author_has_viewed) $viewed_icon = 'glyphicon-eye-close';
		else $viewed_icon = 'glyphicon-eye-open';
		$viewer_voted = ($user_voted)? 'viewer_voted' :'';
		$view_action = (!$author_has_viewed)? $labels['view']: $labels['unview'];
		if ($user_voted || $article['author_id']==$user->user_id) $view_action='';
		$viewer_is_author = ($article['author_id']==$user->user_id)? 'viewer_is_author' :'';
		$lenta .= '<a href="#" class="viewed '.$viewer_voted.' '.$viewer_is_author.'"><span  class="of_views" title="'.$views.' '.$labels['of_views'].'<br>'.$view_action.'"><span class="glyphicon '.$viewed_icon.'"></span><span class="view_num"></span></span></a>'; 
		
		$lenta.='&nbsp;&nbsp;&nbsp;&nbsp; <a href="#" class="add_fav '.$favored.'"  title="'.$fav_title.'" id="fav-'.$article['id'].'"><span class="glyphicon glyphicon-star'.$fav_style.' "></span></a>'; 
		}
		
		if ($user->is_logged() && $user_voted==0 && (($user_voted!==false && $user->can_revote()) || $user_voted===false) && !($article['author_id']==$user->user_id)) {
			$fav = '—';
			$fav_title=$labels['vote_to_see_who_favoured_this'];
		} else $fav_title=$fav .' '.$labels['favadded'];

		if ($user->is_logged()) $lenta .= '<span title="'.$fav_title.'" class="fav"> </span>';

		$lenta .='</span>&nbsp;&nbsp;&nbsp;&nbsp;';
		$lenta .= '</div>';
	}

	
	if ($info) {
		$lenta .='<div style="font-size:12px;';
		if ($vote) $lenta .='text-align:right;margin-right:10px;';
		$lenta .='" class="tooltip-demo">';
		if ($vote) $lenta .='&nbsp;&nbsp;&nbsp;&nbsp;';
		if ($user->is_logged() && $user_voted==false && $user_viewed==0 && !$user_is_author /*&& $user->hasRight('rate_article')*/) {
				$lenta .= '<span class="author" title="'.$labels['authors_restricted_author_name'].'">———————</span>';
		} else if ((!$user->is_logged()) || $user_voted!=0 || $user_viewed!=0 || $user_is_author /*|| !$user->hasRight('rate_article')*/) {
		
			$lenta .= '<span class="author" title="' . $author['lastname'] . ' ' . $author['name'] . '<br>'.$labels['author_rating'].' '.round($rating*100) .'<br>'.$labels['of_articles'].' ' . $author['articles'] . '"><a href="/author/' . $article['author_id'] . '"    >' . $author['nickname'] . '</a></span>';
		}
		$lenta .='&nbsp;&nbsp;&nbsp;&nbsp;<span    title="'.$labels['published'].' ' . $pubtime . '">' . $pubdate . '</span>&nbsp;&nbsp;&nbsp;&nbsp;<span ><a href="/'.$article['id'].'#comments"    title="'.$labels['comments'].'" ><span class="glyphicon glyphicon-comment"></span> ' . $comments . '</a>
		';
		$host = $_SERVER['HTTP_HOST'];
		$id = $article['id'];
		$description = '<br >'.str_replace(array("\r\n")," ",$article['text']).'<br><br >'.$author['lastname'] . ' ' . $author['name'].', '.$pubdate;
		$description_moimir  = str_replace(array('<br>','<br >'),array(' / ',''),$description);
		
		//$description =$article['text'].' '.$author['lastname'] . ' ' . $author['name'].' '.$pubdate;
		$lenta .= '</span><span id="ya_share-'.$id.'"><span class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="icon" data-yashareQuickServices="" ></span></span></div><br />';
		//Должен шариться текст порошка с автором и датой, в качестве картинки - лого сайта
		
		$lenta .= <<<EOT
		<script type="text/javascript">
		 new Ya.share({
                element: 'ya_share-$id',
                    elementStyle: {
                        'type': 'icon',
						'quickServices':  ['']
						
                     },
                    title: 'Порошки',
                    description: "$description",
                    link: 'http://$host/$id',
					serviceSpecific: {
                        moimir: {
                            description: "$description_moimir"
                       },
					   vkontakte: {
                            description: "$description_moimir"
                       },
					   odnoklassniki: {
                            description: "$description_moimir",
							comments: "$description"
                       }
					}

               
        });
		</script>
EOT;
	}
	return $lenta;
}

?>