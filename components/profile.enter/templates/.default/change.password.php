<? if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

?>

<div id="pass-change" aria-hidden="true" class="popup">
	<div class="popup__wrapper">
		<div class="popup__content">
			<div class="popup__header">
				<div class="popup__title">Смена пароля</div>
				<button data-close type="button" class="popup__close">
					<svg class="popup__close-svg">
						<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#icon-close"></use>
					</svg>
				</button>
			</div>
			<div class="popup__main">
				<div class="login">
					<form
						method="post"
						data-parsley-validate=""
						data-parsley-trigger="change"
						action="<?=POST_FORM_ACTION_URI?>"
						class="form form-ajax"
						novalidate=""
						data-component="<?=$this->__component->getName()?>"
						data-action="changePassword"
						name="changePassword"
					>
						<input type="hidden" name="checkword" value="<?=$_GET["USER_CHECKWORD"]?>"/>
						<input type="hidden" name="login" value="<?=$_GET["USER_LOGIN"]?>"/>
						<div class="form__line">
							<label for="passChange" class="form__label">Новый пароль</label>
							<input id="passChange" type="password" name="password" class="form__input">
							<svg class="form__clear-svg">
								<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>
						<div class="form__line">
							<label for="passChangeConfirm" class="form__label">Подтверждение пароля</label>
							<input id="passChangeConfirm" type="password" name="confirmPassword" class="form__input">
							<svg class="form__clear-svg">
								<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>
						<div class="form__bottom">
							<button type="submit" class="form__btn btn btn_red">Изменить</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>


<script>
	var changePasswordForm = new AjaxForm(document.forms.changePassword)
	changePasswordForm.onSuccess = function(res) {
		this.__proto__.onSuccess.call(this, res)
		window.location = "/personal/"
	}
	$(() => {
		openModal('#pass-change')
	})
</script>