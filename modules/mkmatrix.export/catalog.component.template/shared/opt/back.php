<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!CModule::IncludeModule("sale")) {
	ShowError("Модуль sale не установлен!");
	return;
}

$input = $_POST["OPT"];

$lines = explode("\n", $input);
$items = [];
$errorLines = [];
$unavailable = [];
$ids = [];


foreach ($lines as $line) {
	$line = trim($line);

	if (!strlen($line)) {
		continue;
	}

	if (mb_strpos($line, OPT_DELIMITER) === false) {
		$errorLines[] = $line;
		continue;
	}

	preg_match("/(.+)\s*\/\s*([\d]+)/", $line, $matches);

	if (count($matches) < 3) {
		$errorLines[] = $line;
		continue;
	}

	$article = (string) trim($matches[1]);
	$amount = (float) trim($matches[2]);

	if ($amount <= 0) {
		$errorLines[] = $line;
		continue;
	}

	if (isset($items[$article])) {
		$errorLines[] = $line;
		continue;
	}

	$items[$article] = $amount;
}

$articles = array_keys($items);

if (count($articles)) {
	$dbItems = \Bitrix\Iblock\Elements\ElementCatalogTable::query()
		->setSelect([
			"ID",
			"_ARTICLE" => "CML2_ARTICLE.VALUE",
		])
		->where("ACTIVE", "Y")
		->where("CML2_ARTICLE.VALUE", "in", $articles)
		// ->setLimit(20)
		->exec()->fetchAll();

	foreach ($dbItems as $key => $value) {
		$dbItems[$key]["_ARTICLE"] = trim($dbItems[$key]["_ARTICLE"]);
	}

	$dbArticles = array_column($dbItems, "_ARTICLE");

	foreach ($articles as $article) {
		if (!in_array((string) $article, $dbArticles, true)) {
			$unavailable[] = $article;
			unset($items[$article]);
		}
	}

	$art2id = array_combine(
		array_column($dbItems, "_ARTICLE"),
		array_column($dbItems, "ID")
	);

	foreach ($articles as $article) {
		if (isset($art2id[$article])) {
			$ids[] = $art2id[$article];
		}
	}
}



$inputValue = "";

if (!empty($items)) {
	foreach ($items as $article => $amount) {
		$inputValue .= $article . " " . OPT_DELIMITER . " " . $amount . "\n";
	}
} else {
	// Для своего пользователя я сразу заполнял для тестов, рекомендую.
	if ($USER->GetLogin() === "MKMatriX") {
		$inputValue = "IZRM 513 " . OPT_DELIMITER . " 1
			IZM 381.5" . OPT_DELIMITER . " 2
			IZR 1910 " . OPT_DELIMITER . "3
			UIZ-20-10-K05  " . OPT_DELIMITER . " 4
			UIZ-20-10-K04 " . OPT_DELIMITER . "  5
			UIZ-20-10-K06  " . OPT_DELIMITER . "  6
			UIZ-20-10-K01" . OPT_DELIMITER . "7
			UI1K blue    " . OPT_DELIMITER . "    8
			IU1K green " . OPT_DELIMITER . " 9
			UI1K yellow " . OPT_DELIMITER . " 10
			UI1K red " . OPT_DELIMITER . " 11
			недоступный " . OPT_DELIMITER . " 12
			товар " . OPT_DELIMITER . " 13
			для " . OPT_DELIMITER . " 14
			теста " . OPT_DELIMITER . " 15
			" . OPT_DELIMITER . " 19
		";
	}
}
