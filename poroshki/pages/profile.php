<?php
$root_path = "/poroshki/";
include_once($_SERVER['DOCUMENT_ROOT'].$root_path."webstart.php");

if (isset($_POST['profile_submit'])) {

	$nickname = $_POST['nickname'];
	$firstname = $_POST['firstname'];
	$email = $_POST['email'];
	$lastname = $_POST['lastname'];
	$bday = $_POST['bday'];
	$city = $_POST['city'];
	$about = $_POST['about'];
	$sql = "UPDATE authors set nickname = '".$nickname."', name = '".$firstname."', email='".$email."', lastname = '".$lastname."', birthday = '".date('Y-m-d', strtotime($bday))."', city='".$city."', about='".$about."' where id=".$_SESSION['user_id'];
	mysql_query($sql);
	if (isset($_FILES['avatar'])) {
		$allowed_file_extensions = array(
				"jpg","JPG","png","gif","svg"
			);
		$ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
		$upload_dir = $_SERVER['DOCUMENT_ROOT'] . $root_path."images/avatars/";
		if (!file_exists($upload_dir) ) echo 'Папка для сохранения аватаров не существует. Обратитесь к администратору сайта<br>';
		else if (!is_writable($upload_dir)) echo 'Папка для сохранения аватаров не доступна для записи. Возможно, включен selinux. Обратитесь к администратору сайта<br>';
		else {
				$uploadfile = $upload_dir . $_SESSION['user_id'].".".$ext;
				//echo 'uploadfile='.$uploadfile.'<br>';
				if (($_FILES['avatar']['size']>0) && in_array($ext, $allowed_file_extensions) && move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadfile)) {
				
				echo 'ok';
			}
			//var_dump($res_file);
		}
	}
}
$res = mysql_query ("SELECT * from authors where id='".$_SESSION['user_id']."'");
if (mysql_num_rows($res)==1) $author = mysql_fetch_assoc($res); else die("Для редактирования профиля, пожалуйста, <a href='/signin'>залогиньтесь.</a>");

$_SESSION['nickname'] = $author['nickname'];
$_SESSION['first_name'] = $author['name'];
$_SESSION['last_name'] = $author['last_name'];
$_SESSION['email'] = $author['email'];
$_SESSION['name'] = $author['name']+ ' ' + $author['patronym'] + ' ' + $author['lastname'];



?>

<form method="POST" action="/profile" enctype="multipart/form-data">  
  <div class="row">
            <div class="col-12" style="padding:0 15px;">
              <div class="media-body">
				  <!-- Nested media object -->
				  <div class="media">
					  <div >
						 <img  class="media-object" data-src="holder.js/64x64/text::-)" src="<?php echo $root_path;?>images/avatars/<?php echo $_SESSION['user_id']; ?>.jpg?ok" style="display: block; margin: auto;" id="avatar" />
					
					 <br />
					<span class="btn btn-primary fileinput-button btn-xs">
						 <i class="glyphicon glyphicon-floppy-open"></i>
						 <span><?php echo $labels['upload_avatar']; ?></span>
						<input id="fileupload" type="file" name="files">
					</span>
					<br />
					 <br />
					<div class="alert" style="width:300px;hight:20px;display:none;position:relative;" role="alert"><span class="message"></span><a style="text-decoration:none;position:absolute;top:5px;right:5px;" onclick="$(this).parents('.alert').first().hide();return false;" href="#"><span class="glyphicon glyphicon-remove" style="color:#3c763d;"></span></a> </div>
					 
					</div>
             <div class="media-body" style="width:300px;">
				<input type="text" class="form-control" placeholder="Никнейм" title="Никнейм" name="nickname" value="<?php echo $author['nickname'];?>" />
				<br />
				<input type="text" class="form-control" value="<?php echo $author['name'];?>" placeholder="Имя" title="Имя" name="firstname" />
				<br />
				<input type="text" class="form-control" value="<?php echo $author['email'];?>" placeholder="email" title="email" name="email" />
				<br />
				<input type="text" class="form-control" value="<?php echo $author['lastname'];?>" placeholder="Фамилия" title="Фамилия" name="lastname" />
				<br />
				<input type="text" class="form-control datepicker_profile" value="<?php echo date('d.m.Y', strtotime($author['birthday']));?>" placeholder="<?php echo $labels['birth_date']; ?>" title="<?php echo $labels['birth_date']; ?>"  name="bday"/>
				<br />
				<input type="text"class="form-control" value="<?php echo $author['city'];?>" placeholder="<?php echo $labels['city']; ?>" title="<?php echo $labels['city']; ?>" name="city" />
				<br />
				<textarea class="form-control" title="<?php echo $labels['about_yourself']; ?>" placeholder="<?php echo $labels['about_yourself']; ?>" name="about" ><?php echo $author['about'];?></textarea>
				<br />	
				<button name="profile_submit" type="submit" style="margin-right:10px; width:300px;height:36px;" class="btn btn-primary"><?php echo $labels['save_profile'];?></button>
			</div>
		
			
          </div>
 
        </div>


            </div>
          </div><!--/row-->
		  
		</form>	  
		 