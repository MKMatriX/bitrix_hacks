<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */

/** @var CBitrixComponent $component */

// MKMatriX\Main\Utils::addPageType("opt");

$APPLICATION->SetTitle("Заказ оптом");
$APPLICATION->SetPageProperty("title", "Заказ оптом");


require "shared/opt/back.php";
?>

<section class="wholesale">
	<div class="wholesale__container">
		<? require "shared/opt/input.php"; ?>

		<? require "shared/opt/unavailable.php"; ?>

		<? if (count($ids) || $_POST["ajax_basket"] === "Y"): ?>
			<div class="wholesale__result-block catalog">
				<? require "shared/opt/list_buttons.php"; ?>

				<?
				$isOpt = true;
				$arParams["CACHE_TYPE"] = "N";
				$GLOBALS[$arParams["FILTER_NAME"]] = [
					"ID" => $ids
				];
				$by = "ID";
				$sort = $ids;
				$sectionPerPage = count($ids);
				$cardOptions = "simple";

				$arParams["SET_TITLE"] = "N";
				$arParams["SET_BROWSER_TITLE"] = "N";
				require "shared/element_list.php";
				?>
			</div>

			<script>
				window.optRequest = <?=json_encode($items, JSON_PARTIAL_OUTPUT_ON_ERROR)?>
			</script>
		<? endif; ?>
	</div>
</section>

<script src="/local/components/mkmatrix/catalog/templates/.default/shared/opt/script.js"></script>