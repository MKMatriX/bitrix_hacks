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


<? foreach ([INDIVIDUAL_CUSTOMER_ID, LEGAL_ENTITY_CUSTOMER_ID] as $typeId): ?>
	<div class="data-change__body">
		<form
			action="<?= POST_FORM_ACTION_URI ?>"
			class="form form-ajax"
			data-parsley-validate
			data-parsley-trigger="change"
			required=""
			data-component="<?= $this->__component->getName() ?>"
			data-action="<?=$formName?>"
			name="<?=$formName?>"
		>
			<input type="hidden" name="profileTypeId" value="<?=$typeId?>">
			<input type="hidden" name="ID"/>

			<? foreach ($arResult["PROFILE_PROPERTIES"] as $prop): ?>
				<?
				if ($prop["PERSON_TYPE_ID"] != $typeId) {
					continue;
				}
				$type = "text";
				if ($prop["IS_EMAIL"]) {
					$type = "email";
				}
				if ($prop["IS_PHONE"]) {
					$type = "tel";
				}
				?>

				<div class="form__line">
					<label for="add_<?=$prop["CODE"]?>" class="form__label">
						<?=$prop["NAME"]?>
					</label>
					<input
						id="add_<?=$prop["CODE"]?>"
						type="<?=$type?>"
						name="profileToSave[<?=$prop["CODE"]?>]"
						class="form__input <?=$prop["IS_PHONE"]? "js_phone _mask" : ""?>"
					/>
					<svg class="form__clear-svg">
						<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_close"></use>
					</svg>
				</div>
			<? endforeach; ?>


			<button type="submit" class="form__btn btn btn_red">Сохранить</button>
		</form>
	</div>
<? endforeach; ?>