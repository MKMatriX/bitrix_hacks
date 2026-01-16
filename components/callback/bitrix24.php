<?
define('CRM_HOST', 'example.bitrix24.ru'); // Ваш домен CRM системы
define('CRM_PORT', '443'); // Порт сервера CRM. Установлен по умолчанию
define('CRM_PATH', '/crm/configs/import/lead.php'); // Путь к компоненту lead.rest
define('CRM_LOGIN', 'login'); // Логин пользователя Вашей CRM по управлению лидами
define('CRM_PASSWORD', '----------'); // Пароль пользователя Вашей CRM по управлению лидами
$postData = Array(
	'TITLE' =>$arParams["NAME"] . " лид с сайта mysite.ru",
	'NAME' => $arParams["NAME"],
	'COMMENTS' => $arParams["COMMENTS"],
	'PHONE_MOBILE' => $arParams["PHONE"],
	'EMAIL_HOME' => $arParams["EMAIL_TO"],
	// 'ADDRESS' => $arParams["ADDRESS"],
	// 'LAST_NAME' => $arParams["LAST_NAME"],
);
if (defined('CRM_AUTH')) {
	$postData['AUTH'] = CRM_AUTH;
} else {
	$postData['LOGIN'] = CRM_LOGIN;
	$postData['PASSWORD'] = CRM_PASSWORD;
}
$fp = fsockopen("ssl://".CRM_HOST, CRM_PORT, $errno, $errstr, 30);
if ($fp) {
	$strPostData = '';
	foreach ($postData as $key => $value) {
		$strPostData .= ($strPostData == '' ? '' : '&').$key.'='.urlencode($value);
	}
	$str = "POST ".CRM_PATH." HTTP/1.0\r\n";
	$str .= "Host: ".CRM_HOST."\r\n";
	$str .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$str .= "Content-Length: ".strlen($strPostData)."\r\n";
	$str .= "Connection: close\r\n\r\n";
	$str .= $strPostData;
	fwrite($fp, $str);
	$result = '';
	while (!feof($fp)) {
		$result .= fgets($fp, 128);
	}
	fclose($fp);
	$response = explode("\r\n\r\n", $result);
	// $output = '<pre>'.print_r($response[1], 1).'</pre>';
} else {
	mk_custom_error('Connection Failed! '.$errstr.' ('.$errno.')');
	// echo 'Connection Failed! '.$errstr.' ('.$errno.')';
}
?>