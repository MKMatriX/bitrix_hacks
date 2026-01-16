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

<div class="addresses">
	<div class="addresses__header">
		<h2 class="addresses__title">Профили доставки</h2>
		<a
			href=""
			class="addresses__btn-add btn-border btn-border_black "
			data-popup="#add-profile"
		>
			Добавить профиль
		</a>
	</div>
	<div class="addresses__content js-address-list">
		<template class="js-address-template">
			<div class="addresses__item active js-profile" data-profile-id>
				<input
					checked
					type="radio"
					name="profileId"
					value="N"
					id="profileN"
					class="addresses__input js-set-profile-default"
				/>

				<label for="profileN" class="addresses__label js-set-profile-default">
					<div class="addresses__item-inner">
						<div class="addresses__item-name js-profile-name">
							Название профиля
						</div>
						<div class="addresses__item-row">
							<div class="addresses__item-text js-type" data-tid>
								Тип лица
							</div>

							<? foreach ($arResult["PROFILE_PROPERTIES"] as $property): ?>
								<div class="addresses__item-text js-property" data-code="<?=$property["CODE"]?>" style="display: none;">
									<div class="js-property-name" style="display: none;">
										<?= htmlspecialchars($property["NAME"]) ?>
									</div>
									<div class="addresses__item-text js-property-value">
										<?= htmlspecialchars($property["VALUES"][$profile["ID"]]["VALUE"]) ?>
									</div>
								</div>
							<? endforeach; ?>
						</div>
					</div>
				</label>

				<a
					href="javascript:void(0)"
					class="addresses__change-data btn btn_grey js-fill-address-id"
					data-popup="#change-data"
				>
					Изменить данные
				</a>
				<button class="addresses__del-btn" onclick="deleteProfile(this)">
					<svg class="addresses__del-btn-svg">
						<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#del-compare"></use>
					</svg>
				</button>
			</div>
		</template>


		<? $first = true; ?>
		<? foreach ($arResult["PROFILES"] as $profile): ?>
			<div
				class="addresses__item <?= $first ? "active" : "" ?> js-profile"
				data-profile-id="<?= $profile["ID"] ?>"
			>
				<input
					<?= $first ? "checked" : "" ?>
					type="radio"
					name="profileId"
					value="<?= $profile["ID"] ?>"
					class="addresses__input js-set-profile-default"
					id="profile<?= $profile["ID"] ?>"
				/>
				<label for="profile<?= $profile["ID"] ?>" class="addresses__label js-set-profile-default">
					<div class="addresses__item-inner">
						<div class="addresses__item-name js-profile-name">
							<?= $profile["NAME"] ?>
						</div>
						<div class="addresses__item-row">
							<div class="addresses__item-text js-type" data-tid="<?=$profile["PERSON_TYPE_ID"]?>">
								<?=$component::getProfileTypeName($profile["PERSON_TYPE_ID"])?>
							</div>

							<? foreach ($arResult["PROFILE_PROPERTIES"] as $property): ?>
								<?
								if (!strlen($property["VALUES"][$profile["ID"]]["VALUE"])) {
									continue;
								}
								?>
								<div class="addresses__item-text js-property" data-code="<?=$property["CODE"]?>">
									<div class="js-property-name" style="display: none;">
										<?= htmlspecialchars($property["NAME"]) ?>
									</div>
									<div class="js-property-value">
										<?= htmlspecialchars($property["VALUES"][$profile["ID"]]["VALUE"]) ?>
									</div>
								</div>
							<? endforeach; ?>
						</div>
					</div>
				</label>

				<a
					href="javascript:void(0)"
					class="addresses__change-data btn btn_grey js-fill-address-id"
					data-popup="#change-data"
				>
					Изменить данные
				</a>
				<button class="addresses__del-btn" onclick="deleteProfile(this)">
					<svg class="addresses__del-btn-svg">
						<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#del-compare"></use>
					</svg>
				</button>
			</div>
			<?
			$first = false;
			?>
		<? endforeach; ?>

	</div>
</div>