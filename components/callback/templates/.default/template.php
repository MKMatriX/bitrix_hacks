<? if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * $arResult
 * $arParams
 */

$mainId = $this->GetEditAreaId($arParams["NAME"]);
$obName = $templateData['JS_OBJ'] = 'ob'.preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);
?>

<section class="callback">
	<div class="callback__container">
		<form
			id="<?=$obName?>"
			action="<?=POST_FORM_ACTION_URI?>"
			method="post"
			class="callback__form form"
			name="<?=$obName?>"
			data-component="<?=$this->__component->getName()?>"
			data-action="callback"
			data-parsley-validate
		>
			<div class="callback__text">Задайте вопрос, оставьте отзыв, предложение или претензию.</div>

			<? foreach ($arResult["ITEMS"] as $key => $arElement): ?>
				<?=$arElement["HTML"]?>
			<? endforeach; ?>

			<input type="hidden" value="1" name="policy">
			<div class="form__consent">
				Отправляя данные, я даю свое
				<a href="" class="form__consent-link" data-popup="#consent">
					согласие на обработку перс. данных
				</a>
			</div>

			<button type="submit" class="form__button btn-15">
				Отправить
			</button>
		</form>
	</div>
</section>


<script>
	var <?=$obName?> = new mkCallback({
		id: '<?=$obName?>',
		signedParamsString: '<?=$this->getComponent()->getSignedParameters()?>',
		YA_COUNTER: '<?=$arParams["YA_COUNTER"]?>',
		YA_GOAL: '<?=$arParams["YA_GOAL"]?>',
		validate: function(form) {
			return $(form).parsley().isValid();
		},
		onSuccess: function () {
			// если мы хотим изменить поведение после успешной отправки формы
			this.form.reset();
			openModal('#thanks')
		}
	})
</script>