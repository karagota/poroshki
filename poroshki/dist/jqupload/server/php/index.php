<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');


class OdinnUploadHandler extends UploadHandler
{
/*	
    protected function get_user_id()
    {
		@session_start();
		
        return $_SESSION['user_id'];
    }
	protected function get_upload_path($file_name = null, $version = null)
	{
	
		return $this->options['upload_dir'].$this->get_user_path()
            .$this->get_user_id().'.'.strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
	}
	protected function get_file_name($file_path, $name, $size, $type, $error,
            $index, $content_range) {
			return $this->get_user_id().'.jpg';
			
	}
*/
}

$upload_handler = new UploadHandler();