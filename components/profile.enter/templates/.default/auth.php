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

<div id="login" aria-hidden="true" class="popup">
	<div class="popup__wrapper">
		<div class="popup__content">
			<div class="popup__header">
				<div class="popup__title">Авторизация</div>
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
						class="form  form-ajax"
						novalidate=""
						data-component="<?=$this->__component->getName()?>"
						data-action="authorize"
						name="authorize"
					>
						<div class="form__line">
							<label for="userLogin" class="form__label">E-mail </label>
							<input required id="userLogin" type="email" name="login" class="form__input">
							<svg class="form__clear-svg">
								<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>
						<div class="form__line">
							<label for="password" class="form__label">Пароль</label>
							<input required id="password" type="password" name="password" class="form__input">
							<svg class="form__clear-svg">
								<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>
						<div class="form__bottom">
							<button type="submit" class="form__btn btn btn_red">Войти</button>
							<a href="" class="form__link" data-popup="#forgot-pass">Забыли пароль?</a>
							<a href="" class="form__reg btn-border btn-border_black" data-popup="#registration">Зарегистрироваться</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	var authorizeForm = new AjaxForm(document.forms.authorize)
	authorizeForm.onSuccess = function(res) {
		this.__proto__.onSuccess.call(this, res)
		window.location = "/personal/"
	}
</script>

<? if ($arResult["OPEN_AUTH_MODAL"]): ?>
	<script>
		$(() => {
			window.history.pushState({}, document.title, location.href.replace(location.search, ''))
			openModal('login')
		})
	</script>
<? endif; ?>
