<?

$arDefaultValues['default'] = [];

$arAllOptions = [
	"edit1" => [
	],
	"edit2" => [
		// ["textOptionCode", "Название свойства (текстовое)", "DEFAULT_VALUE", ["text", 20]],
		// ["numOptionCode", "Название свойства (Число)", 123, ["number", 20]],
		["iblockId", "ИД инфоблока", CATALOG_IBLOCK_ID, ["number", 20]],
	]
];

$arButtons = [
	"edit1" => [
		[
			"NAME" => "SAVE_SIZES",
			"TEXT" => "Сохранить размеры"
		],
	],
	"edit2" => [
		[
			"NAME" => "CONVERT_IBLOCK_PICTURES",
			"TEXT" => "Переконвертировать картинки инфоблока"
		],
	]
];

$aTabs = [
	[
		"DIV" => "edit1",
		"TAB" => "Настройки",
		"ICON" => "ib_settings",
		"TITLE" => "Настройки",
	],
	[
		"DIV" => "edit2",
		"TAB" => "Действия",
		"ICON" => "ib_settings",
		"TITLE" => "Настройки",
	],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);
