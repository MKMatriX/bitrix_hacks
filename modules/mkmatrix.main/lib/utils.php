<?php
namespace MKMatriX\Main;

class Utils {
	static $pageTypes = [];

	/**
	 * Вывести слайдер товаров.
	 * Мои дизайнеры пихали слайдеры товаров повсюду. Даже на 404.
	 * А на странице товара их было по три штуки. И еще сколько же на главной.
	 * Поэтому в какой-то момент я озаботился чтобы у меня была просто одна строчка, которая это делала.
	 *
	 * @param array $additionalParams дополнительные параметры компонента
	 * @param array $filter фильтр (пойдет в $GLOBALS["arrFilterCarousel"])
	 * @param string $template шаблон bitrix:catalog.section, по умолчанию ""
	 *
	 * @return any
	 */
	public static function showSlider(array $additionalParams = [], array $filter = [], string $template = "") {
		// тут параметры каталога.
		require $_SERVER["DOCUMENT_ROOT"] . "/catalog/commonParams.php";

		if (!isset($commonCatalogParams)) {
			throw new \Exception("Не заданна переменная commonCatalogParams");
		}

		$GLOBALS["arrFilterCarousel"] = $filter;

		return $GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:catalog.section",
			$template,
			$additionalParams + [
				"PRODUCT_DISPLAY_MODE" => "Y",
				"HINT" =>  "Товары",
				"CAROUSEL" => "Y",
				"HIDE_SECTION_DESCRIPTION" => "Y",
				"SHOW_ALL_WO_SECTION" => "Y",
				"TYPE" => "card",
				"DISPLAY_TOP_PAGER" => "N",
				"DISPLAY_BOTTOM_PAGER" => "N",
				"LAZY_LOAD" => "N",
				"ELEMENT_SORT_FIELD" => "NAME",
				"ELEMENT_SORT_ORDER" => "ASC",
				"SET_TITLE" => "N",
				"SET_META_KEYWORDS" => "N",
				"SET_META_DESCRIPTION" => "N",
				"FILTER_NAME" => "arrFilterCarousel",
			] + $commonCatalogParams  + [
				"SET_TITLE" => "N",
				"SET_META_KEYWORDS" => "N",
				"SET_META_DESCRIPTION" => "N",
				"ALLOW_SEO_DATA" => "N",
			],
			$component
		);
	}

	/**
	 * Если ли у текущей страницы тип,
	 * не отложенная функция, так что смотрит только по типам, что были заданы ранее
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function hasPageType(string $type) {
		return in_array($type, self::$pageTypes);
	}

	/**
	 * Добавить к текущей странице тип
	 * @param string $type
	 *
	 * @return void
	 */
	public static function addPageType(string $type) {
		if (!self::hasPageType($type)) {
			self::$pageTypes[] = $type;
		}
	}

	/**
	 * Выводит текст, если страница нужного типа, отложенная функция,
	 * т.е. тип страницы можно задать ниже по коду
	 * @param string $type тип страницы
	 * @param string $text текст для вывода
	 *
	 * @return void
	 */
	public static function echoIfPageTypeDelay(string $type, string $text) {
		global $APPLICATION;
		$showText = function () use ($type, $text) {
			if (Utils::hasPageType($type)) {
				return $text;
			}
		};

		$APPLICATION->AddBufferContent($showText);
	}

	public static function titleForSomePages() {
		global $APPLICATION;
		$showText = function () use ($APPLICATION) {
			if (!(
				// Utils::hasPageType("detail") ||
				Utils::hasPageType("main") ||
				Utils::hasPageType("brand.detail")
			)) {
				$text = '<h1 class="page__title">';
				// $text .= $APPLICATION->GetPageProperty("title");
				$text .= $APPLICATION->GetTitle();
				$text .= '</h1>';
				return $text;
			}
		};

		$APPLICATION->AddBufferContent($showText);
	}

	/**
	 * @param mixed $picture
	 * @param string $sizes "widthxheight" like "100x100"
	 * @param bool $exact exact or proportional
	 *
	 * @return [type]
	 */
	public static function thumbSrc($picture, string $sizes, $exact = false) {
		if ($picture == NO_PHOTO) {
			return NO_PHOTO;
		}

		$sizes = explode("x", $sizes);
		$res = \CFile::ResizeImageGet(
			$picture,
			["width" => $sizes[0], "height" => $sizes[1]],
			$exact? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL,
			false
		);

		if ($res === false || !strlen($res["src"])) {
			return NO_PHOTO;
		}

		return $res["src"];
	}

	public static function makeSrcSet($picture, string $sizes, string $alt = "", $exact = false) {
		$src = "";

		if ($picture == NO_PHOTO) {
			$src = NO_PHOTO;
		}

		$sizes = explode("x", $sizes);
		$res = \CFile::ResizeImageGet(
			$picture,
			["width" => $sizes[0], "height" => $sizes[1]],
			$exact? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL,
			false
		);

		if ($res === false || !strlen($res["src"])) {
			$src = NO_PHOTO;
		}

		$src = $res["src"];
		$destination = $src . '.webp';
		if (!file_exists($_SERVER["DOCUMENT_ROOT"] . $destination)) {
			\WebPConvert\WebPConvert::convert(
				$_SERVER["DOCUMENT_ROOT"] . $src,
				$_SERVER["DOCUMENT_ROOT"] . $destination
			);
		}

		$destination = str_replace(" ", "%20", $destination);
		$src = str_replace(" ", "%20", $src);

		$html = '<source srcset="' . $destination . '" type="image/webp">';
		$html .= '<source srcset="' . $src . '" type="image/jpeg">';
		$html .= '<img src="' . $src . '" alt="' . $alt . '">';

		return $html;
	}

	public static function generateRandomString($length = 10) {
		return mb_substr(
			str_shuffle(
				str_repeat(
					$x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
					ceil($length/mb_strlen($x))
				)
			),
			1,
			$length
		);
	}

	public static function throwBaseErrors($baseResult) {
		if (!is_null($baseResult) && !$baseResult->isSuccess()) {
			throw new \Exception(implode(", ", $baseResult->getErrorMessages()), 1);
		}
		return $baseResult;
	}

	/**
	 * Проверяет есть ли у раздела активные элементы, только для каталога
	 * Без учета торговых предложений или элементов привязанных к нескольким разделам
	 * @param mixed $sectionId
	 *
	 * @return bool
	 */
	public static function sectionHaveActiveElements($sectionId) {
		if (!($sectionId > 0)) {
			return false;
		}

		$cacheActive = SectionExtTable::checkActive((int) $sectionId);
		if (!is_null($cacheActive)) {
			return $cacheActive;
		}

		if(!\CModule::IncludeModule("iblock")) {
			return false;
		}

		$parentSection = (new \Bitrix\Main\Entity\Query(\Bitrix\Iblock\Model\Section::compileEntityByIblock(CATALOG_IBLOCK_ID)))
			->setSelect([
				"ID",
				"LEFT_MARGIN",
				"RIGHT_MARGIN"
			])
			->where("ID", "=", $sectionId)
			->setLimit(1)
			->setCacheTtl(60)
			->exec()->fetchRaw();

		if (is_null($parentSection) || $parentSection["ID"] != $sectionId) {
			return false;
		}

		$subSections = (new \Bitrix\Main\Entity\Query(\Bitrix\Iblock\Model\Section::compileEntityByIblock(CATALOG_IBLOCK_ID)))
			->setSelect([
				"ID",
			])
			->where("LEFT_MARGIN", ">", $parentSection["LEFT_MARGIN"])
			->where("RIGHT_MARGIN", "<", $parentSection["RIGHT_MARGIN"])
			->setCacheTtl(60)
			->exec()->fetchAll();

		$sectionsIds = array_column($subSections, "ID");
		$sectionsIds[] = $parentSection["ID"];

		if(!\CModule::IncludeModule("sale")) {
			return false;
		}

		$item = \Bitrix\Catalog\ProductTable::query()
			->setSelect(["ID"])
			->where("IBLOCK_ELEMENT.IBLOCK_SECTION.ID", "in", $sectionsIds)
			->where("IBLOCK_ELEMENT.ACTIVE", true)
			->where(\Bitrix\Main\ORM\Query\Query::filter()
				->logic("or")
				->where("QUANTITY_TRACE", \Bitrix\Catalog\ProductTable::STATUS_NO)
				->where(\Bitrix\Main\ORM\Query\Query::filter()
					->where("QUANTITY", ">", 0)
					// ->where(\Bitrix\Main\ORM\Query\Query::filter()
					// 	->where("QUANTITY_TRACE", \Bitrix\Catalog\ProductTable::STATUS_YES)
					// 	->where("QUANTITY_TRACE", \Bitrix\Catalog\ProductTable::STATUS_DEFAULT)
					// )
				)
			)
			->setCacheTtl(60)
			->setLimit(1)
			->fetchAll()[0];

		$hasActive = $item["ID"] > 0;

		SectionExtTable::saveActive($sectionId, $hasActive);

		return $item["ID"] > 0;
	}

	/**
	 * Как-то сеошник попросил разные alt для изображений.
	 * Как будто мы очень заботимся о том, чтобы людям с ридерами было веселее.
	 * Им ведь так нужно читать разные строки, не содержащие описания изображения.
	 * Ну не мудак ли?
	 *
	 * @param integer $i
	 * @param boolean $mini
	 * @return void
	 */
	public static function seoImgList($i = 0, $mini = false) {
		$fullArray = [
			"фото",
			"изображение",
			"картинка",
			"фотография",
			"рисунок",
			"снимок",
		];
		$miniArray = [
			"мини изображение",
			"мини фото",
			"мини картинка",
			"мини фотография",
			"мини снимок",
			"мини рисунок",
			"миниатюра"
		];

		$fullGen = function ($i) {
			return "снимок №{$i}";
		};

		$miniGen = function ($i) {
			return "мини №{$i}";
		};

		$usedArray = &$fullArray;
		$usedGen = &$fullGen;

		if ($mini) {
			$usedArray = &$miniArray;
			$usedGen = &$miniGen;
		}

		$count = count($usedArray);
		if ($i < $count) {
			return $usedArray[$i];
		}

		return $usedGen($i + 1 - $count);
	}

	/**
	 * Склонение существительных после числительных.
	 *
	 * @param string $value Значение
	 * @param array $words Массив вариантов, например: array('товар', 'товара', 'товаров')
	 * @param bool $show Включает значение $value в результирующею строку
	 * @return string
	 */
	public static function russianNumbers($value, $words, $show = true)
	{
		$num = $value % 100;
		if ($num > 19) {
			$num = $num % 10;
		}

		$out = ($show) ?  $value . ' ' : '';
		switch ($num) {
			case 1:  $out .= $words[0];
				break;
			case 2:
			case 3:
			case 4:  $out .= $words[1];
				break;
			default: $out .= $words[2];
				break;
		}

		return $out;
	}
}