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

<div class="personal-data">
	<div class="personal-data__header">
		<div class="personal-data__title">Данные аккаунта</div>
		<a
			data-da=".personal-data,1800,last"
			href="javascript:void(0)"
			class="personal-data__log-out btn-border btn-border_black"
			onclick="logout()"
		>
			Выйти из аккаунта
		</a>
	</div>
	<div class="personal-data__content js-user-data">
		<form
			method="post"
			data-parsley-validate=""
			action="<?=POST_FORM_ACTION_URI?>"
			class="form-ajax"
			novalidate=""
			data-component="<?=$this->__component->getName()?>"
			data-action="updateUser"
			name="updateUser"
		>
			<div class="personal-data__row">
				<div class="personal-data__name">Имя пользователя</div>
				<div class="personal-data__content js-user-name"><?= $arResult["NAME"] ?></div>
				<a href="#" class="personal-data__change">
					<svg class="personal-data__change-svg">
						<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#icon_change"></use>
					</svg>
				</a>
				<div class="personal-data__change-block">
					<div class="form__line">
						<input
							type="text"
							name="name"
							data-error="Ошибка"
							data-value=""
							class="personal-data__input form__input"
							value="<?=$arResult["NAME"]?>"
						/>
					</div>
					<button type="submit" class="personal-data__btn">
						<svg class="personal-data__btn-svg">
							<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#icon_enter"></use>
						</svg>
					</button>
				</div>
				<div class="personal-data__row-msg">Изменения внесены</div>
			</div>
			<div class="personal-data__row">
				<div class="personal-data__name">E-mail</div>
				<div class="personal-data__content js-user-email"><?= $arResult["EMAIL"] ?></div>
				<a href="#" class="personal-data__change">
					<svg class="personal-data__change-svg">
						<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#icon_change"></use>
					</svg>
				</a>
				<div class="personal-data__change-block">
					<div class="form__line">
						<input
							type="email"
							name="email"
							data-error="Ошибка"
							data-value=""
							class="personal-data__input form__input"
							value="<?=$arResult["EMAIL"]?>"
						/>
					</div>
					<button type="submit" class="personal-data__btn">
						<svg class="personal-data__btn-svg">
							<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#icon_enter"></use>
						</svg>
					</button>
				</div>
				<div class="personal-data__row-msg">Изменения внесены</div>
			</div>
			<div class="personal-data__row">
				<div class="personal-data__name">Телефон</div>
				<div class="personal-data__content js-user-phone"><?= $arResult["PHONE"] ?: "Не указан" ?></div>
				<a href="#" class="personal-data__change">
					<svg class="personal-data__change-svg">
						<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#icon_change"></use>
					</svg>
				</a>
				<div class="personal-data__change-block">
					<div class="form__line">
						<input
							type="text"
							name="phone"
							data-error="Ошибка"
							data-value=""
							class="personal-data__input form__input js_phone _mask"
							value="<?=$arResult["PHONE"]?>"
						/>
					</div>
					<button type="submit" class="personal-data__btn">
						<svg class="personal-data__btn-svg">
							<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#icon_enter"></use>
						</svg>
					</button>
				</div>
				<div class="personal-data__row-msg">Изменения внесены</div>
			</div>
			<div class="personal-data__row">
				<div class="personal-data__name">Пароль</div>
				<div class="personal-data__text">*******</div>
				<a href="#" class="personal-data__change">
					<svg class="personal-data__change-svg">
						<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#icon_change"></use>
					</svg>
				</a>
				<div class="personal-data__change-block">
					<div class="form__line">
						<input
							type="password"
							name="password"
							data-error="Ошибка"
							data-value=""
							class="personal-data__input form__input"
						/>
					</div>
					<button type="submit" class="personal-data__btn">
						<svg class="personal-data__btn-svg">
							<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#icon_enter"></use>
						</svg>
					</button>
				</div>
				<div class="personal-data__row-msg">Изменения внесены</div>
			</div>
		</form>
	</div>
</div>
