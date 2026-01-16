<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$stp = SITE_TEMPLATE_PATH;
$this->arResult["ITEMS"] = [];
foreach ($this->arParams["FIELDS"] as $key => $arElement) {
	$html = false;
	$required = $arElement["REQUIRED"] == "Y"? 'required' : '';
	$disabled = $arElement["DISABLED"] == "Y" ? 'disabled' : '';
	if ($required && $arElement["SKIP_STAR"] != "Y") {
		$arElement["RU_NAME"] .= " *";
	}


	switch ($arElement["CODE"]) {
		case 'PHONE':
			$html = <<<HEREDOC
				<div class="form__line">
					<label
						for="id_{$arElement["CODE"]}"
						class="form__label"
					>{$arElement["RU_NAME"]}</label>
					<input
						{$required}
						id="id_{$arElement["CODE"]}"
						type="tel"
						name="fields[{$arElement["CODE"]}]"
						class="form__input js_phone"
					>
					<svg class="form__clear-svg">
						<use xlink:href="{$stp}/img/icons/icons.svg#input_clear"></use>
					</svg>
				</div>
HEREDOC;
			break;
		case 'FILE':
			$html = <<<HEREDOC
				<div class="form__line">
					<input
						name="{$arElement["CODE"]}"
						type="file"
						class="input input__file"
					/>
					<input
						name="fields[{$arElement["CODE"]}]"
						type="hidden"
					/>
					<label for="input__file" class="input__file-button">
						<span class="input__file-icon-wrapper">
							<svg class="input__file-icon">
								<use xlink:href="{$stp}/img/svg/sprite.svg#add_file"></use>
							</svg>
						</span>
						<div class="input__file-button-text">
							{$arElement["RU_NAME"]}
						</div>
					</label>
				</div>
HEREDOC;
			break;
		case 'EMAIL_TO':
			$html = <<<HEREDOC
				<div class="sidebar-form__line form__line">
					<label
						for="id_{$arElement["CODE"]}"
						class="form__label"
					>{$arElement["RU_NAME"]}</label>
					<input
						{$required}
						id="id_{$arElement["CODE"]}"
						type="email"
						name="fields[{$arElement["CODE"]}]"
						class="form__input"
					>
					<svg class="form__clear-svg">
						<use xlink:href="{$stp}/img/icons/icons.svg#input_clear"></use>
					</svg>
				</div>
HEREDOC;
			break;
		case 'MESSAGE':
			$html = <<<HEREDOC
				<div class="form__line">
					<label
						for="id_{$arElement["CODE"]}"
						class=""
					>{$arElement["RU_NAME"]}</label>
					<textarea
						{$required}
						id="id_{$arElement["CODE"]}"
						autocomplete="off"
						name="fields[{$arElement["CODE"]}]"
						placeholder=""
						data-error="Ошибка"
						class="form__input"
					></textarea>
					<svg class="form__clear-svg">
						<use xlink:href="{$stp}/img/icons/icons.svg#input_clear"></use>
					</svg>
				</div>
HEREDOC;
			break;
		case 'PRODUCT_ID':
			$html = '<input type="hidden" name="fields['.$arElement["CODE"].']" />';
			break;
		default:
			$html = <<<HEREDOC
				<div class="form__line">
					<label
						for="id_{$arElement["CODE"]}"
						class="form__label"
					>{$arElement["RU_NAME"]}</label>
					<input
						{$required}
						id="id_{$arElement["CODE"]}"
						type="text"
						name="fields[{$arElement["CODE"]}]"
						class="form__input"
					>
					<svg class="form__clear-svg">
						<use xlink:href="{$stp}/img/icons/icons.svg#input_clear"></use>
					</svg>
				</div>
HEREDOC;
			break;
	}

	if ($html) {
		$arElement["HTML"] = $html;
		$this->arResult["ITEMS"][] = $arElement;
	}
}