<?php 
date_default_timezone_set('Europe/Moscow');
$root_path = "/poroshki/";

require(".".$root_path."config.php");
$server_path = $_SERVER['DOCUMENT_ROOT'].$root_path;
include_once($server_path."webstart.php");
if (isset($_POST)) {
	include_once($server_path."post.php");
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Порошок — четверостишие, написанное усечённым четырехстопным ямбом. Количество слогов по строкам: 9/8/9/2. Вторая и четвертая строки рифмуются. Используются только строчные буквы и пробелы.">
    <meta name="author" content="">
	<meta property="og:url" content="<?php echo $domain.'/';?>" />
	<meta property="og:image" content="<?php echo $domain.'/';?>favicon.ico" />
	<meta property="og:description" content="Порошок — четверостишие, написанное усечённым четырехстопным ямбом. Количество слогов по строкам: 9/8/9/2. Вторая и четвертая строки рифмуются. Используются только строчные буквы и пробелы.">
	<meta property = "og:title" content="Порошки">
    <link rel="shortcut icon" href="/favicon.ico">
	<link rel="stylesheet" href="<?php echo $root_path; ?>dist/css/jquery-ui.css">
	<link href="<?php echo $root_path; ?>dist/chosen/chosen.css" rel="stylesheet">
    <title><?php echo $sitename; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo $root_path; ?>dist/css/bootstrap.min.css" rel="stylesheet">
	
	<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
	<link rel="stylesheet" href="<?php echo $root_path; ?>dist/jqupload/css/jquery.fileupload.css">
	<link rel="stylesheet" href="<?php echo $root_path; ?>dist/jqupload/css/jquery.fileupload-ui.css">
	<!-- CSS adjustments for browsers with JavaScript disabled -->
	<noscript><link rel="stylesheet" href="<?php echo $root_path; ?>dist/jqupload/css/jquery.fileupload-noscript.css"></noscript>
	<noscript><link rel="stylesheet" href="<?php echo $root_path; ?>dist/jqupload/css/jquery.fileupload-ui-noscript.css"></noscript>

	<link href="http://netdna.bootstrapcdn.com/font-awesome/3.0.2/css/font-awesome.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="<?php echo $root_path; ?>dist/offcanvas/offcanvas.css" rel="stylesheet">
	<!--[if IE]>
     <style> .rotate{display:inline;}</style>
	<![endif]--> 
	<link href="<?php echo $root_path; ?>style.css" rel="stylesheet">
		    <script src="<?php echo $root_path; ?>dist/offcanvas/jquery-1.10.2.min.js"></script>
	<script src="<?php echo $root_path; ?>dist/js/jquery-ui-1.10.4.js"></script>
<script type="text/javascript" src="//yastatic.net/share/share.js"
charset="utf-8"></script>
<script src="<?php echo $root_path; ?>dist/holder.js"></script>


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="<?php echo $root_path; ?>dist/js/html5shiv.js"></script>
      <script src="<?php echo $root_path; ?>dist/js/respond.min.js"></script>
    <![endif]-->
  </head>

  <body style="">
  
  
	<?php include_once($upper_navbar); ?>
	<div class="alert vote-message"></div>
	<div id="above">
		<div class="container">
		
		  <div class="row row-offcanvas row-offcanvas-right">
			<div class="col-xs-12 col-sm-9">


			  <?php include_once($server_path.$inc); ?>
			
			</div><!--/span-->

			<div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar" role="navigation">
			
			<?php include_once($filter_snippet); ?>
			</div><!--/span-->
		  </div><!--/row-->

		 
		</div><!--/.container-->
		</div><!--/#above-->
      <footer>
	       
		
		<p>© <?php echo $labels['trademark'];?> Текущее время <?php echo date("H:i:s");  ?></p>

		
		

      </footer>

    

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->

    <script src="<?php echo $root_path; ?>dist/offcanvas/bootstrap.min.js"></script>
    <script src="<?php echo $root_path; ?>dist/offcanvas/offcanvas.js"></script>

	<script src="<?php echo $root_path; ?>dist/js/jquery.ui.datepicker-ru.js"></script>
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="<?php echo $root_path; ?>dist/jqupload/js/vendor/jquery.ui.widget.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="<?php echo $root_path; ?>dist/jqupload/js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="<?php echo $root_path; ?>dist/jqupload/js/jquery.fileupload.js"></script>


	
	<script src="<?php echo $root_path; ?>dist/bootstrap-wysiwyg.js"></script>
    <script src="<?php echo $root_path; ?>dist/hotkeys.js"></script>

	<script src="<?php echo $root_path; ?>dist/chosen/chosen.jquery.js" type="text/javascript"></script>

<script src="<?php echo $root_path; ?>script.php" type="text/javascript"></script>

</body></html>
