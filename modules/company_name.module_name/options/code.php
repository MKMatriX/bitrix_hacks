<?

$arDefaultValues['default'] = [];

$arAllOptions = [
	["textOptionCode", "Название свойства (текстовое)", "DEFAULT_VALUE", ["text", 20]],
	["numOptionCode", "Название свойства (Число)", 123, ["number", 20]],
];

$arButtons = [
	[
		"NAME" => "BUTTON_NAME",
		"TEXT" => "Текст на кнопке"
	],
];

$arSingleTabOptions = [
	["SINGLE_ELEMENT_ID", "ИД элемента для переноса", 0, ["number", 20]],
];

$arSingleTabButtons = [
	[
		"NAME" => "FAKE_MOVE_SINGLE",
		"TEXT" => "Создать свойства из одного элемента"
	],
	[
		"NAME" => "MOVE_SINGLE",
		"TEXT" => "Переместить один элемент"
	],
];

// Вкладки
$aTabs = [
	[
		"DIV" => "edit1",
		"TAB" => "Настройки",
		"ICON" => "ib_settings",
		"TITLE" => "Настройки",
	],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);
