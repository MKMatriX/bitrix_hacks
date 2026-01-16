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

$partsPath = $_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/parts/";
?>
<section class="data">
	<div class="data__wrapper">
		<?
		require $partsPath . "user.php";
		require $partsPath . "profileList.php";
		?>
	</div>
</section>

<?
require $partsPath . "updateProfile.php";
require $partsPath . "addProfile.php";
?>

<script src="<?=$templateFolder?>/scriptAfter.js"></script>