<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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

<div id="login-popup-second" aria-hidden="true" class="popup">
	<div class="popup__wrapper">

		<div class="popup__content">
			<div class="popup__header">
				<div class="popup__title">Вход или регистрация</div>
				<button data-close type="button" class="popup__close">
					<svg class="popup__close-svg">
						<use xlink:href="<?= SITE_TEMPLATE_PATH ?>/img/icons/icons.svg#svg-icon-close-modal"></use>
					</svg>
				</button>
			</div>
			<div class="popup__main">
				<div class="login">
					<div class="login__text">На ваш номер телефона выслан код подтверждения. Пожалуйста, введите его ниже. Вы сможете использовать этот код в качестве пароля или изменить его на любой другой в личном кабинете</div>
					<form
						class="form form-ajax"
						method="post"
						data-parsley-validate=""
						action="<?= POST_FORM_ACTION_URI ?>"
						data-component="<?= $this->__component->getName() ?>"
						data-action="checkSmsPass"
						name="checkSmsPass"
					>

						<input type="hidden" name="number">
						<div class="form__line">
							<label for="userMailForSendings" class="form__label">Пароль из SMS</label>
							<input id="userMailForSendings" type="" autocomplete="off" name="password" class="form__input">
							<svg class="form__clear-svg">
								<use xlink:href="<?= SITE_TEMPLATE_PATH ?>/img/icons/icons.svg#input_clear"></use>
							</svg>
						</div>
						<div class="form__line-desc js-countdown">
							Получить новый код вы сможете через <span class="js-countdown-timer">59 секунд</span>
						</div>
						<div class="login__link js-send-again" style="display: none;">Отправить код повторно</div>
						<div class="form__bottom">
							<button type="submit" class="form__btn btn btn__transparent_black">Отправить</button>
							<a href="" class="login__link" data-popup="#login-popup">Указать другой телефон</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<? if ($arResult["OPEN_SECOND_AUTH_MODAL"]) : ?>
	<script>
		$(() => {
			openModal("#login-popup-second")
		})
	</script>
<? endif; ?>

<script>
	let againNode = document.querySelector('.js-send-again')

	const startTimer = () => {
		let intervalId = setInterval(() => {
			let timerNode = document.querySelector('.js-countdown-timer')
			let countdownTextNode = document.querySelector('.js-countdown')

			if (window["canSendAgainAfter"] > 0) {
				countdownTextNode.style.display = ""
				againNode.style.display = "none"

				let text = ""
				let seconds = Math.ceil((window["canSendAgainAfter"] - (+(new Date()))) / 1000)
				if (seconds > 0) {
					text += seconds + " "

					seconds = seconds % 100
					if (seconds > 10 && seconds < 20) {
						text += "секунд"
					} else {
						let last = seconds % 10
						if (last == 1) {
							text += "секунда"
						} else if (last > 1 && last < 5) {
							text += "секунды"
						} else {
							text += "секунд"
						}
					}
					timerNode.innerText = text
				} else {
					countdownTextNode.style.display = "none"
					againNode.style.display = "block"
					clearInterval(intervalId)
				}
			}
		}, 1000)
	}

	againNode.addEventListener('click', () => {
		let authorizeForm = window['authorizeForm']
		// authorizeForm.form.querySelector('[name=tel]').value = authorizeForm.lastData.get('tel')
		authorizeForm.submit()
	})


	var authorizeFormStep2 = new AjaxForm(document.forms.checkSmsPass)
	authorizeFormStep2.onSuccess = function(res) {
		this.__proto__.onSuccess.call(this, res)
		if (window.location.pathname === '/personal/cart/') {
			window.location = "/personal/order/make/"
		} else {
			window.location = "/personal/"
		}
	}
</script>