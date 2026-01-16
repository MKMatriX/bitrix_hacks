<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var MKMatrixProfileDetailComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */
?>

<!--Добавление адреса-->
<div id="add-profile" aria-hidden="true" class="popup">
	<div class="popup__wrapper">
		<div class="popup__content">
			<div class="popup__header">
				<div class="popup__title">Добавить профиль</div>
				<button data-close type="button" class="popup__close">
					<svg class="popup__close-svg">
						<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#icon-close"></use>
					</svg>
				</button>
			</div>
			<div class="popup__main">
				<div data-tabs class="data-change">
					<nav data-tabs-titles class="data-change__navigation">
						<button type="button" class="data-change__title _tab-active">Физ. лицо</button>
						<button type="button" class="data-change__title">Юр. лицо</button>
					</nav>
					<div data-tabs-body class="data-change__content">
						<?
						$formName = "addProfile";
						require $partsPath . "_propList.php";
						unset($formName);
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
