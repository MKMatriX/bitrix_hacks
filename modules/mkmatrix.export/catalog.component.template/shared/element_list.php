<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();



$intSectionID = $APPLICATION->IncludeComponent(
	"bitrix:catalog.section",
	"",
	[
		"WHOLESALE" => $isOpt? "Y" : "N",
		"DISPLAY_COMPARE" => $arParams["DISPLAY_COMPARE"],
		"HIDE_SECTION_DESCRIPTION" => "N",
		"SHOW_ALL_WO_SECTION" => "Y",
		"TYPE" => "card",
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"CARD_OPTIONS" => $cardOptions, // "full", "no_image", "simple" // пока так
		"ELEMENT_SORT_FIELD" => $by,
		"ELEMENT_SORT_ORDER" => $sort,
		"ELEMENT_SORT_FIELD2" => "", // $by,
		"ELEMENT_SORT_ORDER2" => "", // $sort,
		"PAGE_ELEMENT_COUNT" => $sectionPerPage,
		"LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
		"PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
		"PROPERTY_CODE_MOBILE" => $arParams["LIST_PROPERTY_CODE_MOBILE"],
		"META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
		"META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
		"BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
		"SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
		"INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
		"BASKET_URL" => $arParams["BASKET_URL"],
		"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
		"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
		"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
		"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
		"PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
		"FILTER_NAME" => $arParams["FILTER_NAME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_FILTER" => $arParams["CACHE_FILTER"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_BROWSER_TITLE" => $arParams["SET_BROWSER_TITLE"],
		// "SET_TITLE" => "N", // кастомно
		// "SET_BROWSER_TITLE" => "N",
		"MESSAGE_404" => $arParams["~MESSAGE_404"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],
		"SHOW_404" => $arParams["SHOW_404"],
		"FILE_404" => $arParams["FILE_404"],
		"DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
		"PRICE_CODE" => $arParams["PRICE_CODE"],
		"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
		"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
		"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
		"USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
		"ADD_PROPERTIES_TO_BASKET" => ($arParams["ADD_PROPERTIES_TO_BASKET"] ?? ''),
		"PARTIAL_PRODUCT_PROPERTIES" => ($arParams["PARTIAL_PRODUCT_PROPERTIES"] ?? ''),
		"PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
		"DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
		"DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
		"PAGER_TITLE" => $arParams["PAGER_TITLE"],
		"PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
		"PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
		"PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
		"PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
		"PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
		"PAGER_BASE_LINK_ENABLE" => $arParams["PAGER_BASE_LINK_ENABLE"],
		"PAGER_BASE_LINK" => $arParams["PAGER_BASE_LINK"],
		"PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
		"LAZY_LOAD" => $arParams["LAZY_LOAD"],
		"MESS_BTN_LAZY_LOAD" => $arParams["~MESS_BTN_LAZY_LOAD"],
		"LOAD_ON_SCROLL" => $arParams["LOAD_ON_SCROLL"],
		"OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
		"OFFERS_FIELD_CODE" => $arParams["LIST_OFFERS_FIELD_CODE"],
		"OFFERS_PROPERTY_CODE" => $arParams["LIST_OFFERS_PROPERTY_CODE"],
		"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
		"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
		"OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
		"OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
		"OFFERS_LIMIT" => $arParams["LIST_OFFERS_LIMIT"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
		"DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
		"USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
		'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
		'CURRENCY_ID' => $arParams['CURRENCY_ID'],
		'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
		'HIDE_NOT_AVAILABLE_OFFERS' => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],
		'LABEL_PROP' => $arParams['LABEL_PROP'],
		'LABEL_PROP_MOBILE' => $arParams['LABEL_PROP_MOBILE'],
		'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],
		'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'],
		'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
		'PRODUCT_BLOCKS_ORDER' => $arParams['LIST_PRODUCT_BLOCKS_ORDER'],
		'PRODUCT_ROW_VARIANTS' => $arParams['LIST_PRODUCT_ROW_VARIANTS'],
		'ENLARGE_PRODUCT' => $arParams['LIST_ENLARGE_PRODUCT'],
		'ENLARGE_PROP' => $arParams['LIST_ENLARGE_PROP'] ?? '',
		'SHOW_SLIDER' => $arParams['LIST_SHOW_SLIDER'],
		'SLIDER_INTERVAL' => $arParams['LIST_SLIDER_INTERVAL'] ?? '',
		'SLIDER_PROGRESS' => $arParams['LIST_SLIDER_PROGRESS'] ?? '',
		'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'],
		'OFFER_TREE_PROPS' => $arParams['OFFER_TREE_PROPS'],
		'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
		'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
		'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
		'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
		'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
		'MESS_SHOW_MAX_QUANTITY' => ($arParams['~MESS_SHOW_MAX_QUANTITY'] ?? ''),
		'RELATIVE_QUANTITY_FACTOR' => ($arParams['RELATIVE_QUANTITY_FACTOR'] ?? ''),
		'MESS_RELATIVE_QUANTITY_MANY' => ($arParams['~MESS_RELATIVE_QUANTITY_MANY'] ?? ''),
		'MESS_RELATIVE_QUANTITY_FEW' => ($arParams['~MESS_RELATIVE_QUANTITY_FEW'] ?? ''),
		'MESS_BTN_BUY' => ($arParams['~MESS_BTN_BUY'] ?? ''),
		'MESS_BTN_ADD_TO_BASKET' => ($arParams['~MESS_BTN_ADD_TO_BASKET'] ?? ''),
		'MESS_BTN_SUBSCRIBE' => ($arParams['~MESS_BTN_SUBSCRIBE'] ?? ''),
		'MESS_BTN_DETAIL' => ($arParams['~MESS_BTN_DETAIL'] ?? ''),
		'MESS_NOT_AVAILABLE' => ($arParams['~MESS_NOT_AVAILABLE'] ?? ''),
		'MESS_BTN_COMPARE' => ($arParams['~MESS_BTN_COMPARE'] ?? ''),
		'USE_ENHANCED_ECOMMERCE' => ($arParams['USE_ENHANCED_ECOMMERCE'] ?? ''),
		'DATA_LAYER_NAME' => ($arParams['DATA_LAYER_NAME'] ?? ''),
		'BRAND_PROPERTY' => ($arParams['BRAND_PROPERTY'] ?? ''),
		'TEMPLATE_THEME' => ($arParams['TEMPLATE_THEME'] ?? ''),
		"ADD_SECTIONS_CHAIN" => "Y",
		'ADD_TO_BASKET_ACTION' => $arParams["ADD_TO_BASKET_ACTION"],
		'SHOW_CLOSE_POPUP' => $arParams['COMMON_SHOW_CLOSE_POPUP'] ?? '',
		'COMPARE_PATH' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['compare'],
		'COMPARE_NAME' => $arParams['COMPARE_NAME'],
		'BACKGROUND_IMAGE' => ($arParams['SECTION_BACKGROUND_IMAGE'] ?? ''),
		'COMPATIBLE_MODE' => ($arParams['COMPATIBLE_MODE'] ?? ''),
		'DISABLE_INIT_JS_IN_COMPONENT' => ($arParams['DISABLE_INIT_JS_IN_COMPONENT'] ?? '')
	],
	$component
);

/*
?>

<div class="catalog__content">
	<div class="card"><!-- Карточка полная -->
		<label class="checkbox">
			<input class="checkbox__input" name="" id="" type="checkbox">
			<div class="checkbox__chunk"></div>
		</label>
		<a href="" class="card__image"><img src="<?=SITE_TEMPLATE_PATH?>/img/catalog/prd_1.webp" alt=""></a>
		<div class="card__main">
			<div class="card__available available">В наличии 325 м.</div>
			<a href="" class="card__title">Выключатель автоматический однополюсный 16А С ВА47-100 10кА</a>
			<div class="card__article"><span class="card__artic">Арт:</span>MVA40-1-016-C</div>
			<a href="" class="card__favorites">
			<svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M11.8375 1.7083C12.6954 1.43168 13.6524 1.45235 14.4852 1.8424L14.4863 1.84295C14.4908 1.84503 14.4952 1.84713 14.4997 1.84923L14.5019 1.85028V1.85139H14.5034C15.1401 2.1539 15.6539 2.60995 16.0006 3.17977C16.1617 3.44454 16.2867 3.73389 16.3711 4.04381L16.3715 4.04502C16.3725 4.04884 16.3736 4.05266 16.3746 4.05649C16.6386 5.03941 16.5163 6.31339 15.7026 7.77329L15.7003 7.77787C15.6982 7.78185 15.696 7.78582 15.6938 7.7898C15.6924 7.79235 15.691 7.79491 15.6895 7.79746C14.6126 9.74632 12.7597 11.2787 9.38479 13.9614C9.35382 13.986 9.32273 14.0107 9.29151 14.0355C9.2077 14.1021 9.12297 14.1694 9.0373 14.2374L9.03458 14.2396C9.02131 14.2501 9.00802 14.2607 8.9947 14.2712C8.98089 14.2603 8.96673 14.249 8.95297 14.2381L8.95009 14.2358C8.86503 14.1683 8.78089 14.1015 8.69767 14.0354C8.66638 14.0105 8.63523 13.9858 8.6042 13.9611C5.2281 11.278 3.37522 9.74558 2.29838 7.79616L2.29683 7.79335C2.29398 7.78819 2.29114 7.78303 2.28831 7.77787L2.28756 7.77634C1.48132 6.31644 1.36207 5.04169 1.62607 4.05649C1.62713 4.05256 1.62819 4.04863 1.62926 4.04471C1.89627 3.06618 2.56223 2.29373 3.48604 1.85139C3.4911 1.84901 3.49616 1.84663 3.50123 1.84427L3.50483 1.8426C3.51577 1.83752 3.52672 1.83251 3.53769 1.82756L3.54076 1.82617C5.01094 1.16458 6.82516 1.62202 7.83184 2.81465C8.02409 3.04241 8.18689 3.29698 8.31221 3.57569C8.37196 3.70851 8.46802 3.82108 8.58895 3.90001C8.70988 3.97894 8.85059 4.0209 8.99432 4.0209C9.13806 4.0209 9.27877 3.97894 9.3997 3.90001C9.52063 3.82108 9.61669 3.70851 9.67644 3.57569C9.80178 3.29709 9.96493 3.04264 10.1577 2.815C10.5964 2.29683 11.1886 1.91752 11.8375 1.7083ZM10.4024 0.683362C11.6357 -0.00955427 13.1431 -0.19883 14.5019 0.22306L14.5034 0.223526C14.7192 0.290615 14.9312 0.373129 15.1379 0.471496C15.1435 0.474167 15.1491 0.476848 15.1547 0.479538C15.9288 0.850652 16.5906 1.39661 17.0805 2.08163C17.4125 2.54583 17.6658 3.07395 17.8221 3.65494C17.8231 3.65891 17.8242 3.66289 17.8252 3.66686C18.2016 5.08502 17.9812 6.73597 17.0549 8.43902L17.0525 8.4435C17.0417 8.46319 17.0309 8.48289 17.02 8.50259L17.0179 8.50643C17.015 8.51172 17.012 8.51701 17.0091 8.52229C15.7203 10.8684 13.4794 12.6472 9.93632 15.4572C9.93294 15.4599 9.92956 15.4625 9.92619 15.4652L9.45594 15.8386C9.3241 15.9432 9.16161 16 8.99432 16C8.82704 16 8.66455 15.9432 8.53271 15.8386L8.06246 15.4652C8.05928 15.4627 8.05609 15.4602 8.05291 15.4576C4.50874 12.6466 2.26765 10.8678 0.978829 8.52077C0.97577 8.51523 0.972719 8.50969 0.969675 8.50415L0.968777 8.50251C0.957857 8.48263 0.947034 8.46275 0.936305 8.44287L0.935498 8.44138C0.0156171 6.73666 -0.200446 5.08541 0.175883 3.66789L0.176232 3.66658C0.177264 3.6627 0.1783 3.65882 0.179342 3.65494C0.565174 2.21967 1.5399 1.10505 2.8301 0.48149C2.83271 0.480228 2.83532 0.478969 2.83793 0.477711C2.84172 0.475889 2.84551 0.474072 2.8493 0.472258L2.8523 0.470734C4.36868 -0.245682 6.16316 -0.119743 7.5896 0.682447C7.97836 0.901069 8.33977 1.16992 8.66077 1.48564C8.77793 1.60086 8.8897 1.72233 8.99545 1.84987C9.10112 1.72262 9.21282 1.60141 9.32991 1.48642C9.65125 1.17083 10.0132 0.902021 10.4024 0.683362Z" fill="#004EA6" />
				<path d="M11.8375 1.7083C12.6954 1.43168 13.6524 1.45235 14.4852 1.8424L14.4863 1.84295L14.4997 1.84923L14.5019 1.85028V1.85139H14.5034C15.1401 2.1539 15.6539 2.60995 16.0006 3.17977C16.1617 3.44454 16.2867 3.73389 16.3711 4.04381L16.3715 4.04502L16.3746 4.05649C16.6386 5.03941 16.5163 6.31339 15.7026 7.77329L15.7003 7.77787L15.6938 7.7898L15.6895 7.79746C14.6126 9.74632 12.7597 11.2787 9.38479 13.9614L9.29151 14.0355L9.0373 14.2374L9.03458 14.2396L8.9947 14.2712L8.95297 14.2381L8.95009 14.2358L8.69767 14.0354L8.6042 13.9611C5.2281 11.278 3.37522 9.74558 2.29838 7.79616L2.29683 7.79335L2.28831 7.77787L2.28756 7.77634C1.48132 6.31644 1.36207 5.04169 1.62607 4.05649L1.62926 4.04471C1.89627 3.06618 2.56223 2.29373 3.48604 1.85139L3.50123 1.84427L3.50483 1.8426L3.53769 1.82756L3.54076 1.82617C5.01094 1.16458 6.82516 1.62202 7.83184 2.81465C8.02409 3.04241 8.18689 3.29698 8.31221 3.57569C8.37196 3.70851 8.46802 3.82108 8.58895 3.90001C8.70988 3.97894 8.85059 4.0209 8.99432 4.0209C9.13806 4.0209 9.27877 3.97894 9.3997 3.90001C9.52063 3.82108 9.61669 3.70851 9.67644 3.57569C9.80178 3.29709 9.96493 3.04264 10.1577 2.815C10.5964 2.29683 11.1886 1.91752 11.8375 1.7083Z" fill="#004EA6" />
			</svg>
			В избранное
			</a>
		</div>
		<div data-showmore="items" class="card__params">
			<div data-showmore-content="1" class="card__params-content">
			<div class="card__params-row">
				<div class="card__params-name">Высота:</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Глубина и более днинное название</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Глубина</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Высота:</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Ширина</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Глубина</div>
				<div class="card__params-value">800 мм</div>
			</div>
			</div>
			<button hidden data-showmore-button type="button" class="card__params-more"><span>Показать все свойства</span><span>Скрыть</span></button>
		</div>
		<div class="card__packing">
			<div class="card__packing-row">
			<div class="card__packing-name">Ед. измерения</div>
			<div class="card__packing-name">Цена, руб/ед</div>
			<div class="card__packing-name">Количество, шт</div>
			<div class="card__packing-name">Итого, руб</div>
			</div>
			<div class="card__packing-row">
			<div class="card__packing-item">Штука</div>
			<div class="card__packing-item">500 ₽</div>
			<div class="card__packing-item">
				<div class="quantity">
					<div class="quantity__button quantity__button_minus _icon-minus"></div>
					<div class="quantity__input"><input autocomplete="off" type="number" name="form[]" value="1"></div>
					<div class="quantity__button quantity__button_plus _icon-plus"></div>
				</div>
			</div>
			<div class="card__packing-item">500 ₽</div>
			<div class="card__packing-item"><a href="" class="card__add-basket"><img src="<?=SITE_TEMPLATE_PATH?>/img/icons/icon-card-basket.svg" alt=""></a></div>
			</div>
			<div class="card__packing-row">
			<div class="card__packing-item">
				Упаковка
				<div class="card__packing-tippy" data-tippy-content="Количество в упаковке 25 единиц">?</div>
			</div>
			<div class="card__packing-item">2000 ₽</div>
			<div class="card__packing-item">
				<div class="quantity">
					<div class="quantity__button quantity__button_minus _icon-minus"></div>
					<div class="quantity__input"><input autocomplete="off" type="number" name="form[]" value="1"></div>
					<div class="quantity__button quantity__button_plus _icon-plus"></div>
				</div>
			</div>
			<div class="card__packing-item">2000 ₽</div>
			<div class="card__packing-item"><a href="" class="card__add-basket"><img src="<?=SITE_TEMPLATE_PATH?>/img/icons/icon-card-basket.svg" alt=""></a></div>
			</div>
			<div class="card__packing-row">
			<div class="card__packing-item">
				Палет
				<div class="card__packing-tippy" data-tippy-content="Количество в палете 25 упаковок">?</div>
			</div>
			<div class="card__packing-item">500 000 ₽</div>
			<div class="card__packing-item">
				<div class="quantity">
					<div class="quantity__button quantity__button_minus _icon-minus"></div>
					<div class="quantity__input"><input autocomplete="off" type="number" name="form[]" value="1"></div>
					<div class="quantity__button quantity__button_plus _icon-plus"></div>
				</div>
			</div>
			<div class="card__packing-item">500 000 ₽</div>
			<div class="card__packing-item"><a href="" class="card__add-basket"><img src="<?=SITE_TEMPLATE_PATH?>/img/icons/icon-card-basket.svg" alt=""></a></div>
			</div>
		</div>
	</div>
	<div class="card card_no-img"><!-- Карточка без изображения -->
		<label class="checkbox">
			<input class="checkbox__input" name="" id="" type="checkbox">
			<div class="checkbox__chunk"></div>
		</label>
		<a href="" class="card__image"><img src="<?=SITE_TEMPLATE_PATH?>/img/catalog/prd_1.webp" alt=""></a>
		<div class="card__main">
			<div class="card__available available">В наличии 325 м.</div>
			<a href="" class="card__title">Выключатель автоматический однополюсный 16А С ВА47-100 10кА</a>
			<div class="card__article"><span class="card__artic">Арт:</span>MVA40-1-016-C</div>
			<a href="" class="card__favorites">
			<svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M11.8375 1.7083C12.6954 1.43168 13.6524 1.45235 14.4852 1.8424L14.4863 1.84295C14.4908 1.84503 14.4952 1.84713 14.4997 1.84923L14.5019 1.85028V1.85139H14.5034C15.1401 2.1539 15.6539 2.60995 16.0006 3.17977C16.1617 3.44454 16.2867 3.73389 16.3711 4.04381L16.3715 4.04502C16.3725 4.04884 16.3736 4.05266 16.3746 4.05649C16.6386 5.03941 16.5163 6.31339 15.7026 7.77329L15.7003 7.77787C15.6982 7.78185 15.696 7.78582 15.6938 7.7898C15.6924 7.79235 15.691 7.79491 15.6895 7.79746C14.6126 9.74632 12.7597 11.2787 9.38479 13.9614C9.35382 13.986 9.32273 14.0107 9.29151 14.0355C9.2077 14.1021 9.12297 14.1694 9.0373 14.2374L9.03458 14.2396C9.02131 14.2501 9.00802 14.2607 8.9947 14.2712C8.98089 14.2603 8.96673 14.249 8.95297 14.2381L8.95009 14.2358C8.86503 14.1683 8.78089 14.1015 8.69767 14.0354C8.66638 14.0105 8.63523 13.9858 8.6042 13.9611C5.2281 11.278 3.37522 9.74558 2.29838 7.79616L2.29683 7.79335C2.29398 7.78819 2.29114 7.78303 2.28831 7.77787L2.28756 7.77634C1.48132 6.31644 1.36207 5.04169 1.62607 4.05649C1.62713 4.05256 1.62819 4.04863 1.62926 4.04471C1.89627 3.06618 2.56223 2.29373 3.48604 1.85139C3.4911 1.84901 3.49616 1.84663 3.50123 1.84427L3.50483 1.8426C3.51577 1.83752 3.52672 1.83251 3.53769 1.82756L3.54076 1.82617C5.01094 1.16458 6.82516 1.62202 7.83184 2.81465C8.02409 3.04241 8.18689 3.29698 8.31221 3.57569C8.37196 3.70851 8.46802 3.82108 8.58895 3.90001C8.70988 3.97894 8.85059 4.0209 8.99432 4.0209C9.13806 4.0209 9.27877 3.97894 9.3997 3.90001C9.52063 3.82108 9.61669 3.70851 9.67644 3.57569C9.80178 3.29709 9.96493 3.04264 10.1577 2.815C10.5964 2.29683 11.1886 1.91752 11.8375 1.7083ZM10.4024 0.683362C11.6357 -0.00955427 13.1431 -0.19883 14.5019 0.22306L14.5034 0.223526C14.7192 0.290615 14.9312 0.373129 15.1379 0.471496C15.1435 0.474167 15.1491 0.476848 15.1547 0.479538C15.9288 0.850652 16.5906 1.39661 17.0805 2.08163C17.4125 2.54583 17.6658 3.07395 17.8221 3.65494C17.8231 3.65891 17.8242 3.66289 17.8252 3.66686C18.2016 5.08502 17.9812 6.73597 17.0549 8.43902L17.0525 8.4435C17.0417 8.46319 17.0309 8.48289 17.02 8.50259L17.0179 8.50643C17.015 8.51172 17.012 8.51701 17.0091 8.52229C15.7203 10.8684 13.4794 12.6472 9.93632 15.4572C9.93294 15.4599 9.92956 15.4625 9.92619 15.4652L9.45594 15.8386C9.3241 15.9432 9.16161 16 8.99432 16C8.82704 16 8.66455 15.9432 8.53271 15.8386L8.06246 15.4652C8.05928 15.4627 8.05609 15.4602 8.05291 15.4576C4.50874 12.6466 2.26765 10.8678 0.978829 8.52077C0.97577 8.51523 0.972719 8.50969 0.969675 8.50415L0.968777 8.50251C0.957857 8.48263 0.947034 8.46275 0.936305 8.44287L0.935498 8.44138C0.0156171 6.73666 -0.200446 5.08541 0.175883 3.66789L0.176232 3.66658C0.177264 3.6627 0.1783 3.65882 0.179342 3.65494C0.565174 2.21967 1.5399 1.10505 2.8301 0.48149C2.83271 0.480228 2.83532 0.478969 2.83793 0.477711C2.84172 0.475889 2.84551 0.474072 2.8493 0.472258L2.8523 0.470734C4.36868 -0.245682 6.16316 -0.119743 7.5896 0.682447C7.97836 0.901069 8.33977 1.16992 8.66077 1.48564C8.77793 1.60086 8.8897 1.72233 8.99545 1.84987C9.10112 1.72262 9.21282 1.60141 9.32991 1.48642C9.65125 1.17083 10.0132 0.902021 10.4024 0.683362Z" fill="#004EA6" />
				<path d="M11.8375 1.7083C12.6954 1.43168 13.6524 1.45235 14.4852 1.8424L14.4863 1.84295L14.4997 1.84923L14.5019 1.85028V1.85139H14.5034C15.1401 2.1539 15.6539 2.60995 16.0006 3.17977C16.1617 3.44454 16.2867 3.73389 16.3711 4.04381L16.3715 4.04502L16.3746 4.05649C16.6386 5.03941 16.5163 6.31339 15.7026 7.77329L15.7003 7.77787L15.6938 7.7898L15.6895 7.79746C14.6126 9.74632 12.7597 11.2787 9.38479 13.9614L9.29151 14.0355L9.0373 14.2374L9.03458 14.2396L8.9947 14.2712L8.95297 14.2381L8.95009 14.2358L8.69767 14.0354L8.6042 13.9611C5.2281 11.278 3.37522 9.74558 2.29838 7.79616L2.29683 7.79335L2.28831 7.77787L2.28756 7.77634C1.48132 6.31644 1.36207 5.04169 1.62607 4.05649L1.62926 4.04471C1.89627 3.06618 2.56223 2.29373 3.48604 1.85139L3.50123 1.84427L3.50483 1.8426L3.53769 1.82756L3.54076 1.82617C5.01094 1.16458 6.82516 1.62202 7.83184 2.81465C8.02409 3.04241 8.18689 3.29698 8.31221 3.57569C8.37196 3.70851 8.46802 3.82108 8.58895 3.90001C8.70988 3.97894 8.85059 4.0209 8.99432 4.0209C9.13806 4.0209 9.27877 3.97894 9.3997 3.90001C9.52063 3.82108 9.61669 3.70851 9.67644 3.57569C9.80178 3.29709 9.96493 3.04264 10.1577 2.815C10.5964 2.29683 11.1886 1.91752 11.8375 1.7083Z" fill="#004EA6" />
			</svg>
			В избранное
			</a>
		</div>
		<div data-showmore="items" class="card__params">
			<div data-showmore-content="1" class="card__params-content">
			<div class="card__params-row">
				<div class="card__params-name">Высота:</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Ширина</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Глубина</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Высота:</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Ширина</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Глубина</div>
				<div class="card__params-value">800 мм</div>
			</div>
			</div>
			<button hidden data-showmore-button type="button" class="card__params-more"><span>Показать все свойства</span><span>Скрыть</span></button>
		</div>
		<div class="card__packing">
			<div class="card__packing-row">
			<div class="card__packing-name">Ед. измерения</div>
			<div class="card__packing-name">Цена, руб/ед</div>
			<div class="card__packing-name">Количество, шт</div>
			<div class="card__packing-name">Итого, руб</div>
			</div>
			<div class="card__packing-row">
			<div class="card__packing-item">Штука</div>
			<div class="card__packing-item">500 ₽</div>
			<div class="card__packing-item">
				<div class="quantity">
					<div class="quantity__button quantity__button_minus _icon-minus"></div>
					<div class="quantity__input"><input autocomplete="off" type="number" name="form[]" value="1"></div>
					<div class="quantity__button quantity__button_plus _icon-plus"></div>
				</div>
			</div>
			<div class="card__packing-item">500 ₽</div>
			<div class="card__packing-item"><a href="" class="card__add-basket"><img src="<?=SITE_TEMPLATE_PATH?>/img/icons/icon-card-basket.svg" alt=""></a></div>
			</div>
			<div class="card__packing-row">
			<div class="card__packing-item">
				Упаковка
				<div class="card__packing-tippy" data-tippy-content="Количество в упаковке 25 единиц">?</div>
			</div>
			<div class="card__packing-item">2000 ₽</div>
			<div class="card__packing-item">
				<div class="quantity">
					<div class="quantity__button quantity__button_minus _icon-minus"></div>
					<div class="quantity__input"><input autocomplete="off" type="number" name="form[]" value="1"></div>
					<div class="quantity__button quantity__button_plus _icon-plus"></div>
				</div>
			</div>
			<div class="card__packing-item">2000 ₽</div>
			<div class="card__packing-item"><a href="" class="card__add-basket"><img src="<?=SITE_TEMPLATE_PATH?>/img/icons/icon-card-basket.svg" alt=""></a></div>
			</div>
			<div class="card__packing-row">
			<div class="card__packing-item">
				Палет
				<div class="card__packing-tippy" data-tippy-content="Количество в палете 25 упаковок">?</div>
			</div>
			<div class="card__packing-item">500 000 ₽</div>
			<div class="card__packing-item">
				<div class="quantity">
					<div class="quantity__button quantity__button_minus _icon-minus"></div>
					<div class="quantity__input"><input autocomplete="off" type="number" name="form[]" value="1"></div>
					<div class="quantity__button quantity__button_plus _icon-plus"></div>
				</div>
			</div>
			<div class="card__packing-item">500 000 ₽</div>
			<div class="card__packing-item"><a href="" class="card__add-basket"><img src="<?=SITE_TEMPLATE_PATH?>/img/icons/icon-card-basket.svg" alt=""></a></div>
			</div>
		</div>
	</div>
	<div class="card card_simple"><!-- Карточка упрощенная -->
		<label class="checkbox">
			<input class="checkbox__input" name="" id="" type="checkbox">
			<div class="checkbox__chunk"></div>
		</label>
		<div class="card__main">
			<div class="card__available available">В наличии 325 м.</div>
			<a href="" class="card__title">TITAN 5 Корпус металлический ЩМП-80.80.30 УХЛ1 IP66 IEK</a>
			<div class="card__article"><span class="card__artic">Арт:</span>MVA40-1-016-C</div>
			<a href="" class="card__favorites">
			<svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M11.8375 1.7083C12.6954 1.43168 13.6524 1.45235 14.4852 1.8424L14.4863 1.84295C14.4908 1.84503 14.4952 1.84713 14.4997 1.84923L14.5019 1.85028V1.85139H14.5034C15.1401 2.1539 15.6539 2.60995 16.0006 3.17977C16.1617 3.44454 16.2867 3.73389 16.3711 4.04381L16.3715 4.04502C16.3725 4.04884 16.3736 4.05266 16.3746 4.05649C16.6386 5.03941 16.5163 6.31339 15.7026 7.77329L15.7003 7.77787C15.6982 7.78185 15.696 7.78582 15.6938 7.7898C15.6924 7.79235 15.691 7.79491 15.6895 7.79746C14.6126 9.74632 12.7597 11.2787 9.38479 13.9614C9.35382 13.986 9.32273 14.0107 9.29151 14.0355C9.2077 14.1021 9.12297 14.1694 9.0373 14.2374L9.03458 14.2396C9.02131 14.2501 9.00802 14.2607 8.9947 14.2712C8.98089 14.2603 8.96673 14.249 8.95297 14.2381L8.95009 14.2358C8.86503 14.1683 8.78089 14.1015 8.69767 14.0354C8.66638 14.0105 8.63523 13.9858 8.6042 13.9611C5.2281 11.278 3.37522 9.74558 2.29838 7.79616L2.29683 7.79335C2.29398 7.78819 2.29114 7.78303 2.28831 7.77787L2.28756 7.77634C1.48132 6.31644 1.36207 5.04169 1.62607 4.05649C1.62713 4.05256 1.62819 4.04863 1.62926 4.04471C1.89627 3.06618 2.56223 2.29373 3.48604 1.85139C3.4911 1.84901 3.49616 1.84663 3.50123 1.84427L3.50483 1.8426C3.51577 1.83752 3.52672 1.83251 3.53769 1.82756L3.54076 1.82617C5.01094 1.16458 6.82516 1.62202 7.83184 2.81465C8.02409 3.04241 8.18689 3.29698 8.31221 3.57569C8.37196 3.70851 8.46802 3.82108 8.58895 3.90001C8.70988 3.97894 8.85059 4.0209 8.99432 4.0209C9.13806 4.0209 9.27877 3.97894 9.3997 3.90001C9.52063 3.82108 9.61669 3.70851 9.67644 3.57569C9.80178 3.29709 9.96493 3.04264 10.1577 2.815C10.5964 2.29683 11.1886 1.91752 11.8375 1.7083ZM10.4024 0.683362C11.6357 -0.00955427 13.1431 -0.19883 14.5019 0.22306L14.5034 0.223526C14.7192 0.290615 14.9312 0.373129 15.1379 0.471496C15.1435 0.474167 15.1491 0.476848 15.1547 0.479538C15.9288 0.850652 16.5906 1.39661 17.0805 2.08163C17.4125 2.54583 17.6658 3.07395 17.8221 3.65494C17.8231 3.65891 17.8242 3.66289 17.8252 3.66686C18.2016 5.08502 17.9812 6.73597 17.0549 8.43902L17.0525 8.4435C17.0417 8.46319 17.0309 8.48289 17.02 8.50259L17.0179 8.50643C17.015 8.51172 17.012 8.51701 17.0091 8.52229C15.7203 10.8684 13.4794 12.6472 9.93632 15.4572C9.93294 15.4599 9.92956 15.4625 9.92619 15.4652L9.45594 15.8386C9.3241 15.9432 9.16161 16 8.99432 16C8.82704 16 8.66455 15.9432 8.53271 15.8386L8.06246 15.4652C8.05928 15.4627 8.05609 15.4602 8.05291 15.4576C4.50874 12.6466 2.26765 10.8678 0.978829 8.52077C0.97577 8.51523 0.972719 8.50969 0.969675 8.50415L0.968777 8.50251C0.957857 8.48263 0.947034 8.46275 0.936305 8.44287L0.935498 8.44138C0.0156171 6.73666 -0.200446 5.08541 0.175883 3.66789L0.176232 3.66658C0.177264 3.6627 0.1783 3.65882 0.179342 3.65494C0.565174 2.21967 1.5399 1.10505 2.8301 0.48149C2.83271 0.480228 2.83532 0.478969 2.83793 0.477711C2.84172 0.475889 2.84551 0.474072 2.8493 0.472258L2.8523 0.470734C4.36868 -0.245682 6.16316 -0.119743 7.5896 0.682447C7.97836 0.901069 8.33977 1.16992 8.66077 1.48564C8.77793 1.60086 8.8897 1.72233 8.99545 1.84987C9.10112 1.72262 9.21282 1.60141 9.32991 1.48642C9.65125 1.17083 10.0132 0.902021 10.4024 0.683362Z" fill="#004EA6" />
				<path d="M11.8375 1.7083C12.6954 1.43168 13.6524 1.45235 14.4852 1.8424L14.4863 1.84295L14.4997 1.84923L14.5019 1.85028V1.85139H14.5034C15.1401 2.1539 15.6539 2.60995 16.0006 3.17977C16.1617 3.44454 16.2867 3.73389 16.3711 4.04381L16.3715 4.04502L16.3746 4.05649C16.6386 5.03941 16.5163 6.31339 15.7026 7.77329L15.7003 7.77787L15.6938 7.7898L15.6895 7.79746C14.6126 9.74632 12.7597 11.2787 9.38479 13.9614L9.29151 14.0355L9.0373 14.2374L9.03458 14.2396L8.9947 14.2712L8.95297 14.2381L8.95009 14.2358L8.69767 14.0354L8.6042 13.9611C5.2281 11.278 3.37522 9.74558 2.29838 7.79616L2.29683 7.79335L2.28831 7.77787L2.28756 7.77634C1.48132 6.31644 1.36207 5.04169 1.62607 4.05649L1.62926 4.04471C1.89627 3.06618 2.56223 2.29373 3.48604 1.85139L3.50123 1.84427L3.50483 1.8426L3.53769 1.82756L3.54076 1.82617C5.01094 1.16458 6.82516 1.62202 7.83184 2.81465C8.02409 3.04241 8.18689 3.29698 8.31221 3.57569C8.37196 3.70851 8.46802 3.82108 8.58895 3.90001C8.70988 3.97894 8.85059 4.0209 8.99432 4.0209C9.13806 4.0209 9.27877 3.97894 9.3997 3.90001C9.52063 3.82108 9.61669 3.70851 9.67644 3.57569C9.80178 3.29709 9.96493 3.04264 10.1577 2.815C10.5964 2.29683 11.1886 1.91752 11.8375 1.7083Z" fill="#004EA6" />
			</svg>
			В избранное
			</a>
		</div>
		<div data-showmore="items" class="card__params">
			<div data-showmore-content="1" class="card__params-content">
			<div class="card__params-row">
				<div class="card__params-name">Высота:</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Ширина</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Глубина</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Высота:</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Ширина</div>
				<div class="card__params-value">800 мм</div>
			</div>
			<div class="card__params-row">
				<div class="card__params-name">Глубина</div>
				<div class="card__params-value">800 мм</div>
			</div>
			</div>
			<button hidden data-showmore-button type="button" class="card__params-more"><span>Показать все свойства</span><span>Скрыть</span></button>
		</div>
		<div class="card__actions">
			<div class="card__prices">
			<div class="card__prices-row">
				<div class="card__prices-name">Стоимость:</div>
				<div class="card__prices-value">500 ₽</div>
			</div>
			<div class="card__prices-row">
				<div class="card__prices-name">Количество:</div>
				<div class="card__prices-value">
					<div class="quantity">
						<div class="quantity__button quantity__button_minus _icon-minus"></div>
						<div class="quantity__input"><input autocomplete="off" type="number" name="form[]" value="1"></div>
						<div class="quantity__button quantity__button_plus _icon-plus"></div>
					</div>
				</div>
			</div>
			<div class="card__prices-row">
				<div class="card__prices-name">Итог:</div>
				<div class="card__prices-value">2000 ₽</div>
			</div>
			</div>
			<a href="" class="card__add-basket">Купить</a>
		</div>
	</div>
</div>

<div class="pagging">
	<a href="" class="pagging__arrow">
		<svg width="32" height="24" viewBox="0 0 32 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M16 2.60673e-06L5.24537e-07 12L16 24L16 21.2505L3.66602 12L16 2.74952L16 2.60673e-06Z" fill="#141414" />
			<rect width="29" height="2" transform="matrix(-1 0 0 1 32 11)" fill="#141414" />
		</svg>
	</a>
	<ul class="pagging__list">
		<li>
			<a href="" class="pagging__item">1</a>
		</li>
		<li>
			<a href="" class="pagging__item">...</a>
		</li>
		<li>
			<a href="" class="pagging__item">6</a>
		</li>
		<li>
			<a href="" class="pagging__item _active">7</a>
		</li>
		<li>
			<a href="" class="pagging__item">8</a>
		</li>
		<li>
			<a href="" class="pagging__item">...</a>
		</li>
		<li>
			<a href="" class="pagging__item">12</a>
		</li>
	</ul>
	<a href="" class="pagging__arrow">
		<svg width="32" height="24" viewBox="0 0 32 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M16 2.60673e-06L32 12L16 24L16 21.2505L28.334 12L16 2.74952L16 2.60673e-06Z" fill="#141414" />
			<rect y="11" width="29" height="2" fill="#141414" />
		</svg>
	</a>
</div>

*/