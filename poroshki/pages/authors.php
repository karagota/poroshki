<?php
$order = 'rating';
if (isset($_GET['order'])) $order = $_GET['order'];
if ($order=='nickname') $order = 'SELECT * from authors  order by nickname ASC';
else if ($order=='lastname') $order = 'SELECT * from authors  order by lastname ASC';
else 
	$order = 'select a.*, r.rating as rate from authors a left join 
rating  r on r.subject_id = a.id AND r.subject_type=0 group by a.id order by rate desc';

if (!empty($_GET['id'])) include ($docroot.$pages_folder."author.php"); else {
include_once($docroot.$snippets_folder.'jumbo_author.php'); 

?>
<div class="row" style="margin-top:40px;">
<div class="col-12" style="padding:0 15px;">
    <div class="media-body">
    <?php $rat = mysql_query("SELECT * from rating where subject_type=0"	);
	$rating = array();
	while ($rat_row = mysql_fetch_assoc($rat)) {
		$rating[$rat_row['subject_id']]=$rat_row['rating'];
	}
	$sql = $order;
	//echo $sql;
	$res = mysql_query ($sql); 
	
	while ($author = mysql_fetch_assoc($res)) {
	?> 
		<!-- Nested media object -->
          <div class="media col-lg-3  col-md-4 col-sm-6 col-xs-12">
		  <div class="pull-left frame"  style="width:64px;height:64px; text-align:center;background-color:black;">
		    <span class="helper"></span>
            <a href="/authors/<?php echo $author['id']; ?>" >
                <img data-src="holder.js/64x64/text::-)" src="<?php echo $root_path;?>images/avatars/<?php echo $author['id']; ?>.jpg" style="display: block; margin: auto;" />
            </a>
			</div>
            <div class="media-body">
              <h4 class="media-heading"><a href="/authors/<?php echo $author['id']; ?>"><?php echo $author['nickname']; ?></a></h4>
			  
             <p <?php if ($author['role']=='editor') echo 'class="tooltip-demo" title="'.$labels['editor'].'" style="color:red;"'; ?> ><?php echo $author['lastname']; ?> <?php echo $author['name']; ?></p>
			  <p><b><?php echo $labels['author_rating'];?>:</b> <?php echo isset($rating[$author['id']])?round($rating[$author['id']]*100):0 ;?></p>
            </div>
          </div>
		  
<?php } ?>

        </div>
            </div>
</div><!--/row-->
<?php }?>