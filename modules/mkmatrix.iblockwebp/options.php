<?
$moduleName = array_reverse(explode(DIRECTORY_SEPARATOR, dirname(__FILE__)))[0];

if (!$USER->IsAdmin()) {
	return;
	die("Доступ закрыт");
}

if (!\Bitrix\Main\Loader::includeModule($moduleName)) {
	return;
	die("Не удалось подключить модуль $moduleName");
}

include "options/tabs.php";

include "options/actions.php"; // обработка действий

include "options/html.php"; // шаблон

?>