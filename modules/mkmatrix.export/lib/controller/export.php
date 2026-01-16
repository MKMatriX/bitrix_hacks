<?
namespace MKMatriX\Export\Controller;

use \Bitrix\Main\Error;

abstract class Export extends \Bitrix\Main\Engine\Controller {
	/**
	 * Returns default pre-filters for action.
	 * @return array
	 */
	protected function getDefaultPreFilters() {
		return [
			new \Bitrix\Main\Engine\ActionFilter\HttpMethod(
				[
					\Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_GET,
					\Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_POST
				]
			),
			new \Bitrix\Main\Engine\ActionFilter\Csrf(),
		];
	}

	public static function basketAction() {
		if (!\CModule::IncludeModule("sale")) {
			throw new \Exception("Модуль sale не установлен", 1);
		}

		global $USER;

		$basketItems = \Bitrix\Sale\Internals\BasketTable::query()
			->setSelect(["PRODUCT_ID", "QUANTITY"])
			->where("FUSER.USER_ID", $USER->GetId())
			->whereNull("ORDER_ID")
			->exec()->fetchAll();
		$ids = array_column($basketItems, "PRODUCT_ID");

		if (!is_array($ids) || empty($ids)) {
			return [];
		}

		$sort = static::parseSort("");
		$filter = ["ID" => $ids];

		$ids = static::getIdsList($filter, $sort);
		$items = static::getItems($ids);

		$id2quantity = array_combine(
			array_column($basketItems, "PRODUCT_ID"),
			array_column($basketItems, "QUANTITY")
		);

		$items = static::addQuantity($items, $id2quantity);

		return $items;
	}

	public static function idsAction($products) {
		$ids = array_keys($products);
		$items = static::getItems($ids);
		$items = static::addQuantity($items, $products);

		return $items;
	}

	public static function favoriteAction(string $sort = "") {
		if (!\CModule::IncludeModule("mkmatrix.main")) {
			throw new \Exception("Модуль mkmatrix.main не установлен", 1);
		}

		$ids = \MKMatriX\Main\FavoriteTable::getMyItems();

		if (!is_array($ids) || empty($ids)) {
			return [];
		}

		$sort = static::parseSort($sort);
		$filter = ["ID" => $ids];

		$ids = static::getIdsList($filter, $sort);
		$items = static::getItems($ids);

		return $items;
	}

	public static function sectionAction($sectionId, string $searchQuery = "", string $filterPath = "", string $sort = "") {
		$ids = static::getItemIds($sectionId, $searchQuery, $filterPath, $sort);

		$items = static::getItems($ids);

		return $items;
	}

	public static function getItemIds ($sectionId, string $searchQuery = "", string $filterPath = "", string $sort = "") {
		$filter = [];
		$filter = static::addSection($filter, $sectionId);
		$filter = static::addSearch($filter, $searchQuery);
		$filter = static::addSmartFilter($filter, $filterPath, $sectionId);

		$sort = static::parseSort($sort);

		$ids = static::getIdsList($filter, $sort);

		return $ids;
	}

	public static function getIdsList($filter, $sort) {
		if (!\CModule::IncludeModule("sale")) {
			throw new \Exception("Модуль sale не установлен", 1);
		}

		$rsElements = \CIBlockElement::GetList(
			$sort, // order
			[ // filter
				"IBLOCK_ID" => CATALOG_IBLOCK_ID,
				"ACTIVE" => "Y",
			] + $filter,
			false, // group
			false, // ["nTopCount" => 1], // pagination
			[ // select
				"ID",
				"IBLOCK_ID",
			]
		);

		$ids = [];
		while ($arElement = $rsElements->Fetch()) {
			$ids[] = $arElement["ID"];
		}
		return $ids;
	}

	static function getItems ($ids) {
		if (!\CModule::IncludeModule("sale")) {
			throw new \Exception("Модуль sale не установлен", 1);
		}

		if (empty($ids)) {
			return [];
		}

		$items = \Bitrix\Iblock\Elements\ElementCatalogTable::query()
			->setSelect([
				"ID",
				"XML_ID",
				"NAME",
				"_ARTICLE" => "CML2_ARTICLE.VALUE",
			])
			->where("ID", "in", $ids)
			->exec()->fetchAll();

		$items = static::addPrice($items);

		$items = array_combine(array_column($items, "ID"), $items);
		$sortedItems = [];
		foreach ($ids as $id) {
			if (isset($items[$id])) {
				$sortedItems[] = $items[$id];
			}
		}

		return $sortedItems;
	}

	static function addPrice($items) {
		foreach ($items as $key => $item) {
			$arPrice = \CCatalogProduct::GetOptimalPrice($item["ID"], 1);
			// $formattedPrice = \CurrencyFormat(
			// 	$arPrice["RESULT_PRICE"]["DISCOUNT_PRICE"],
			// 	$arPrice["RESULT_PRICE"]["CURRENCY"]
			// );

			// $formattedPrice = str_replace(".", ",", (string) $arPrice["RESULT_PRICE"]["DISCOUNT_PRICE"]);

			$items[$key]["PRICE"] = (float) $arPrice["RESULT_PRICE"]["DISCOUNT_PRICE"];
		}
		return $items;
	}

	static function addQuantity($items, $id2quantity) {
		foreach ($items as &$item) {
			$item["QUANTITY"] = $id2quantity[$item["ID"]] ?? 0;
		}
		return $items;
	}

	private static function addSection($filter, $sectionId) {
		if ((int) $sectionId > 0) {
			$filter["SECTION_ID"] = $sectionId;
		}
		return $filter;
	}

	private static function addSearch($filter, $searchQuery) {
		if (mb_strlen($searchQuery)) {
			$filter[] = [
				"LOGIC" => "OR",
				"NAME" => "%" . $searchQuery . "%",
				"PROPERTY_CML2_ARTICLE" => "%" . $searchQuery . "%",
			];
		}
		return $filter;
	}

	private static function addSmartFilter($filter, $filterPath, $sectionId) {
		if (mb_strlen($filterPath)) {
			ob_start();
			// тут параметры компонента каталога.
			require $_SERVER["DOCUMENT_ROOT"] . "/catalog/commonParams.php";
			$GLOBALS["FILTER"] = [];

			global $APPLICATION;
			$APPLICATION->IncludeComponent(
				"bitrix:catalog.smart.filter",
				"",
				[
					"IBLOCK_TYPE" => $commonCatalogParams["IBLOCK_TYPE"],
					"IBLOCK_ID" => $commonCatalogParams["IBLOCK_ID"],
					"SECTION_ID" => $sectionId,
					"FILTER_NAME" => "FILTER",
					"PRICE_CODE" => $commonCatalogParams["PRICE_CODE"],
					"CACHE_TYPE" => "N",
					"CACHE_TIME" => "0",
					"CACHE_GROUPS" => "N",
					"SAVE_IN_SESSION" => "N",
					"FILTER_VIEW_MODE" => $commonCatalogParams["FILTER_VIEW_MODE"],
					"XML_EXPORT" => "N",
					"SECTION_TITLE" => "NAME",
					"SECTION_DESCRIPTION" => "DESCRIPTION",
					'HIDE_NOT_AVAILABLE' => $commonCatalogParams["HIDE_NOT_AVAILABLE"],
					"TEMPLATE_THEME" => $commonCatalogParams["TEMPLATE_THEME"],
					'CONVERT_CURRENCY' => $commonCatalogParams['CONVERT_CURRENCY'],
					'CURRENCY_ID' => $commonCatalogParams['CURRENCY_ID'],
					"SEF_MODE" => $commonCatalogParams["SEF_MODE"],
					// "SEF_RULE" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["smart_filter"],
					"SMART_FILTER_PATH" => $filterPath,
					"PAGER_PARAMS_NAME" => $commonCatalogParams["PAGER_PARAMS_NAME"],
				],
				false,
				['HIDE_ICONS' => 'Y']
			);

			$filter = array_merge($filter, $GLOBALS["FILTER"]);
			ob_end_clean();
		}
		return $filter;
	}

	private static function parseSort($sort) {
		if (mb_strlen($sort)) {
			$sort = explode("-", $sort);
			$sort = [$sort[0] => mb_strtoupper($sort[1])];
		} else {
			$sort = [ "SORT" => "DESC" ];
		}
		$sort["ID"] = "ACS";
		return $sort;
	}
}