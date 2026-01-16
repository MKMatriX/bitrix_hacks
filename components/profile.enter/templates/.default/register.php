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

<div id="registration" aria-hidden="true" class="popup">
	<div class="popup__wrapper">
		<div class="popup__content">
			<div class="popup__header">
				<div class="popup__title">Регистрация</div>
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
						action="<?=POST_FORM_ACTION_URI?>"
						class="form form-ajax"
						novalidate=""
						data-component="<?=$this->__component->getName()?>"
						data-action="register"
						name="register"
						data-parsley-trigger="change"
					>
						<div class="form__line">
							<label for="regName" class="form__label">Ваше имя</label>
							<input id="regName" type="text" name="name" class="form__input">
							<svg class="form__clear-svg">
								<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>
						<div class="form__line">
							<label for="regTel" class="form__label">Телефон</label>
							<input id="regTel" type="tel" name="phone" class="form__input js_phone">
							<svg class="form__clear-svg">
								<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>
						<div class="form__line">
							<label for="regMail" class="form__label">E-mail </label>
							<input id="regMail" type="email" name="email" class="form__input">
							<svg class="form__clear-svg">
								<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>
						<div class="form__line">
							<label for="regPass" class="form__label">Пароль</label>
							<input id="regPass" type="password" name="password" class="form__input">
							<svg class="form__clear-svg">
								<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>
						<div class="form__line">
							<label for="regPassConfirm" class="form__label">Подтверждение пароля</label>
							<input id="regPassConfirm" type="password" name="confirm_password" class="form__input">
							<svg class="form__clear-svg">
								<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>

						<? if ($arResult["USE_CAPTCHA_REGISTER"] == "Y") : ?>
							<div class="form__line">
								<img
									src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE_REGISTER"]?>"
									width="180"
									height="40"
									alt="CAPTCHA"
								/>
								<input
									type="text"
									name="captchaWord"
									required
									autocomplete="off"
								>
								<span class="placeholder-input">Текст с картинки</span>
								<input type="hidden" name="captchaSid" value="<?=$arResult["CAPTCHA_CODE_REGISTER"]?>" />
							</div>
						<? endif ?>

						<div class="form__bottom">
							<button type="submit" class="form__btn btn btn_red">Зарегистрироваться</button>
							<a href="" class="form__link" data-popup="#login">Авторизация</a>
							<div class="form__consent">
								Отправляя данные, я даю свое
								<a href="" class="form__consent-link" data-popup="#consent"> согласие на обработку перс. данных</a>
							</div>
							<input type="hidden" name="ch" value="on"/>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	var registerForm = new AjaxForm(document.forms.register)
	registerForm.onSuccess = function(res) {
		this.__proto__.onSuccess.call(this, res)
		window.location = "/personal/"
	}
</script>