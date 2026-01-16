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

// Внутри спагетти код, по образцу битры, но вряд ли вам нужно что-то очень красивое для внутренних дел
include "options/code.php"; // составление кнопок и параметров модуля
include "options/actions.php"; // обработка действий
include "options/html.php"; // шаблон
?>