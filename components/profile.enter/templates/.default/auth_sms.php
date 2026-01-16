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

<div id="login-popup" aria-hidden="true" class="popup">
	<div class="popup__wrapper">
		<div class="popup__content">
			<div class="popup__header">
				<div class="popup__title">Вход или регистрация</div>
				<button data-close type="button" class="popup__close">
					<svg class="popup__close-svg">
						<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#svg-icon-close-modal"></use>
					</svg>
				</button>
			</div>
			<div class="popup__main">
				<div class="login">
					<div class="login__text">Укажите свой номер телефона, мы вышлем вам пароль по SMS</div>
					<form
						class="form form-ajax"
						method="post"
						data-parsley-validate=""
						action="<?=POST_FORM_ACTION_URI?>"
						data-component="<?=$this->__component->getName()?>"
						data-action="authorizeBySms"
						name="authorizeBySms"
					>
						<div class="form__line">
							<label for="userMailForSendings" class="form__label">Телефон</label>
							<input id="userMailForSendings" type="tel" name="tel" class="form__input js_phone">
							<svg class="form__clear-svg">
								<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>
						<div class="form__bottom">
							<button type="submit" class="form__btn btn btn__transparent_black">Выслать пароль</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>



<script>
	var authorizeForm = new AjaxForm(document.forms.authorizeBySms)
	authorizeForm.onError = function(res) {
		if (res.data > 0) {
			window["canSendAgainAfter"] = res.data * 1000
		}
		this.__proto__.onError.call(this, res)
	}
	authorizeForm.onSuccess = function(res) {
		if (res.data > 0) {
			window["canSendAgainAfter"] = res.data * 1000
		}

		this.__proto__.onSuccess.call(this, res)
		closeModal('#login-popup')
		setTimeout(() => {
			document.forms.checkSmsPass.querySelector('input[name=number]').value = this.lastData.get("tel")
			openModal("#login-popup-second")
			startTimer()
		}, 500);
	}
</script>

<? if ($arResult["OPEN_AUTH_MODAL"]): ?>
	<script>
		$(() => {
			window.history.pushState({}, document.title, location.href.replace(location.search, ''))
			openModal('#login-popup')
		})
	</script>
<? endif; ?>
