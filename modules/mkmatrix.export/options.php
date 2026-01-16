<?

$moduleName = dirname(__FILE__);

if (!$USER->IsAdmin()) {
	return;
	die();
}

if (!\Bitrix\Main\Loader::includeModule($moduleName)) {
	return;
	die();
}

// include "options/code.php"; // составление кнопок и параметров модуля

// include "options/actions.php"; // обработка действий

// include "options/html.php"; // шаблон

?>