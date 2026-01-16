<?
namespace MKMatriX\Main\Controller;

use \Bitrix\Main\Error;
use MKMatriX\Main\Utils;
use MKMatriX\Main\FavoriteTable;
use MKMatriX\Main\CompareTable;

class Main extends \Bitrix\Main\Engine\Controller {
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

	private static function checkClassName(string $list) {
		$className = $list . "Table";
		if (!class_exists($className)) {
			$className = "\\MKMatriX\\Main\\" . $className;
		}

		if (!class_exists($className)) {
			throw new \Exception("Не могу найти список " . htmlspecialcharsEx($list), 1);
		}
		return $className;
	}

	public static function addItemToListAction(string $itemId, string $list) {
		$className = self::checkClassName($list);
		$res = $className::addItemToMyItems((int) $itemId);
		Utils::throwBaseErrors($res);
		return $className::getMyItems();
	}

	public static function deleteItemFromListAction(string $itemId, string $list) {
		$className = self::checkClassName($list);
		$res = $className::deleteItemFromMyItems((int) $itemId);
		Utils::throwBaseErrors($res);
		return $className::getMyItems();
	}

	public static function clearListAction(string $list) {
		$className = self::checkClassName($list);
		$res = $className::setMyItems([]);
		Utils::throwBaseErrors($res);
		return [];
	}
	/**
	 * Меню каталога для хедера
	 * @return string
	 */
	public static function getCatalogMenuAction() {
		global $APPLICATION;
		ob_start();
		$APPLICATION->IncludeComponent(
			"bitrix:catalog.section.list",
			"menu_left",
			[
				"ADD_SECTIONS_CHAIN" => "N",    // Включать раздел в цепочку навигации
				"CACHE_FILTER" => "Y",    // Кешировать при установленном фильтре
				"CACHE_GROUPS" => "Y",    // Учитывать права доступа
				"CACHE_TIME" => "36000000",    // Время кеширования (сек.)
				"CACHE_TYPE" => "A",    // Тип кеширования
				"COUNT_ELEMENTS" => "N",    // Показывать количество элементов в разделе
				"COUNT_ELEMENTS_FILTER" => "CNT_ACTIVE",    // Показывать количество
				"FILTER_NAME" => "arrFilterMenu",    // Имя массива со значениями фильтра разделов
				"IBLOCK_ID" => CATALOG_IBLOCK_ID,    // Инфоблок
				"IBLOCK_TYPE" => CATALOG_IBLOCK_TYPE,    // Тип инфоблока
				"SECTION_CODE" => "",    // Код раздела
				"SECTION_FIELDS" => [    // Поля разделов
					0 => "NAME",
					1 => "PICTURE",
					2 => "preview_picture",
				],
				"SECTION_ID" => "",    // ID раздела
				"SECTION_URL" => "",    // URL, ведущий на страницу с содержимым раздела
				"SECTION_USER_FIELDS" => [    // Свойства разделов
					0 => "UF_TITLE",
					1 => "UF_ICON",
				],
				"SHOW_PARENT_NAME" => "Y",    // Показывать название раздела
				"TOP_DEPTH" => "4",    // Максимальная отображаемая глубина разделов
				"VIEW_MODE" => "LINE",    // Вид списка подразделов
			],
			false
		);

		$html = ob_get_clean();
		return $html;
	}

	public static function getPersonalDataAction() {
		global $USER, $APPLICATION;
		$result = [];

		// $APPLICATION->IncludeComponent(
		// 	"bitrix:sale.basket.basket.line",
		// 	"json",
		// 	[
		// 		"PATH_TO_BASKET" => SITE_DIR . "personal/cart/",
		// 		"PATH_TO_ORDER" => SITE_DIR . "personal/order/make/",
		// 		"SHOW_DELAY" => "N",
		// 		"SHOW_NOTAVAIL" => "N",
		// 		"SHOW_SUBSCRIBE" => "N",
		// 		"SHOW_PRODUCTS" => "Y",
		// 		"SHOW_IMAGE" => "Y",
		// 		"SHOW_PRICE" => "Y",
		// 		"HIDE_ON_BASKET_PAGES" => "N",
		// 		"COMPONENT_TEMPLATE" => ".default",
		// 		"SHOW_NUM_PRODUCTS" => "Y",
		// 		"SHOW_TOTAL_PRICE" => "Y",
		// 		"SHOW_EMPTY_VALUES" => "Y",
		// 		"SHOW_PERSONAL_LINK" => "Y",
		// 		"PATH_TO_PERSONAL" => SITE_DIR . "personal/",
		// 		"SHOW_AUTHOR" => "N",
		// 		"PATH_TO_AUTHORIZE" => "",
		// 		"SHOW_REGISTRATION" => "N",
		// 		"PATH_TO_REGISTER" => SITE_DIR . "login/",
		// 		"PATH_TO_PROFILE" => SITE_DIR . "personal/",
		// 		"SHOW_SUMMARY" => "Y",
		// 		"POSITION_FIXED" => "N",
		// 		"MAX_IMAGE_SIZE" => "220",
		// 		"COMPOSITE_FRAME_MODE" => "N",
		// 	],
		// 	false
		// );

		// BX.onCustomEvent('OnAfterBasketChange');
		// $result["BASKET_ITEMS"] = $GLOBALS["BASKET_ITEMS"];
		$result["FAVORITE_ITEMS"] = FavoriteTable::getMyItems();
		// $result["COMPARE_ITEMS"] = CompareTable::getMyItems();
		// $result["AUTH"] = $USER->IsAuthorized();

		return $result;
	}
}