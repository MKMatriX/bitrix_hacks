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

<div id="forgot-pass" aria-hidden="true" class="popup">
	<div class="popup__wrapper">
		<div class="popup__content">
			<div class="popup__header">
				<div class="popup__title">Восстановление пароля</div>
				<button data-close type="button" class="popup__close">
					<svg class="popup__close-svg">
						<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#icon-close"></use>
					</svg>
				</button>
			</div>
			<div class="popup__main">
				<div class="login">
					<div class="login__text">
						Укажите адрес электронной почты, с которым вы регистрировались ранее,
						 и мы вышлем инструкцию по смене пароля
					</div>
					<form
						method="post"
						action="<?=POST_FORM_ACTION_URI?>"
						class="form form-ajax"
						novalidate=""
						data-component="<?=$this->__component->getName()?>"
						data-action="restore"
						name="restore"
						data-parsley-validate
						data-parsley-trigger="change"
					>
						<div class="form__line">
							<label for="forgotPassMail" class="form__label">E-mail </label>
							<input id="forgotPassMail" type="email" name="email" class="form__input">
							<svg class="form__clear-svg">
								<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>
						<div class="form__bottom">
							<button type="submit" class="form__btn btn btn_red">Восстановить пароль</button>
							<a href="" class="form__link" data-popup="#login">Авторизация</a>
							<div class="form__consent">Отправляя данные, я даю свое <a href="" class="form__consent-link" data-popup="#consent">согласие на обработку перс. данных</a></div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>


<script>
	var restoreForm = new AjaxForm(document.forms.restore)
	restoreForm.timeout = 3000;
	restoreForm.onSuccess = function(res) {
		var errorsNode = this.form.querySelector('.error-text')
		if (errorsNode) {
			errorsNode.innerHTML = ""
		}
		this.__proto__.onSuccess.call(this, res)
	}
</script>