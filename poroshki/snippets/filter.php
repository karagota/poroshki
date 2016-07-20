<?php 
if (isset($page)) $get = $page;
else $get = null;
if (in_array($page,array('author','own','create','fav','drafts','invite','profile'))) 
	{
		include_once($docroot.$snippets_folder.'author_filtr.php');
	} 
 
else if ($get== 'articles' ) 
	{
		include_once($docroot.$snippets_folder.'common_filtr.php');
	}

?>