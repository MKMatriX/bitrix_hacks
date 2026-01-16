<?php
/*
 * jQuery File Upload Plugin PHP Example
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

error_reporting(E_ERROR | E_PARSE);
require('fileupload/UploadHandler.php');
$upload_handler = new UploadHandler(Array(
	"param_name" => "FILE",
	"upload_dir" => $_SERVER["DOCUMENT_ROOT"]."/upload/fileupload/",
	'accept_file_types' => '/\.(gif|jpe?g|png|bmp|pdf|docx?|txt|rtf|djvu)$/i',
));
