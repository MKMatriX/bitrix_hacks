<?
namespace MKMatriX\Main\Events;

use CIBlockElement, CFile, CCurrencyLang;
use Bitrix\Sale;
use Bitrix\Main\Config\Option;
use mikehaertl\wkhtmlto\Pdf;

class Functions
{
	public static function isFrom1CExchange(&$event, &$arFields) {
		if (!!$GLOBALS["USER"] && $GLOBALS["USER"]->GetLogin() === "1Cexchange") {
			return true;
		}

		$saleEvents = [
			"SALE_NEW_ORDER",
			"SALE_STATUS_CHANGED_N",
			"SALE_STATUS_CHANGED_F",
			"SALE_STATUS_CHANGED",
			"SALE_ORDER_CANCEL",
			"SALE_ORDER_DELIVERY",
			"SALE_ORDER_PAID",
		];

		$searchEvents = array_merge($saleEvents, ["NEW_USER"]);

		if (!in_array($event, $searchEvents, true)) {
			return false;
		}

		$userId = null;

		$isSaleEvent = in_array($event, $saleEvents, true);
		if ($isSaleEvent) {
			$orderId = $arFields['ORDER_ID'];
			$order = \Bitrix\Sale\Order::load($orderId);
			$userId = $order->getUserId();
		}
		if ($event === "NEW_USER") {
			$userId = $arFields["USER_ID"];
		}

		if (is_null($userId)) {
			return false;
		}


		$isReal = \Bitrix\Main\UserTable::query()
			->setSelect(["IS_REAL_USER"])
			->where("ID", $userId)
			->exec()->fetchRaw()["IS_REAL_USER"] === "Y";

		if (!$isReal) {
			return true;
		}
		return false;
	}

	/**
	 * Модифицируем тело письма о новом заказе, чтобы изменить html списка элементов
	 * выглядит некрасиво, но что вы хотели от функции составляющей html для писем?)
	 * @param mixed $arFields
	 *
	 * @return void
	 */
	public static function modifyOrderItemList (&$arFields) {
		$orderId = $arFields['ORDER_ID'];

		$order = \Bitrix\Sale\Order::load($orderId);
		$basket = $order->getBasket();

		$basketItems = $basket->getBasketItems();

		$productIds = [];
		foreach ($basketItems as $basketItem) {
			$productIds[] = $basketItem->getProductId();
		}

		$rsElements = CIBlockElement::GetList(
			["SORT"=>"ASC"], // order
			[  // filter
				"ID" => $productIds,
			],
			false, // group
			false, // ["nTopCount" => 1], // pagination
			[ // select
				"ID",
				"IBLOCK_ID",
				"NAME",
				"DETAIL_PAGE_URL",
				"CODE",
				"DETAIL_PICTURE",
			]
		);
		$catalogItems = [];
		$skuItems = [];

		$site = \CSite::GetByID("s1")->Fetch();
		$domains = array_map("trim", explode("\n", $site["DOMAINS"]));

		while ($arElement = $rsElements->GetNext()) {
			$arElement["DETAIL_PICTURE"] = \CFile::GetFileArray($arElement["DETAIL_PICTURE"]);


			$arElement["DETAIL_PICTURE"]["SRC"] = "http://" . $domains[0] . ($arElement["DETAIL_PICTURE"]["SRC"] ?? NO_PHOTO);
			$catalogItems[$arElement["ID"]] = $arElement;

			if (defined("CATALOG_SKU_IBLOCK_ID")) {
				if ($arElement["IBLOCK_ID"] == CATALOG_SKU_IBLOCK_ID) {
					$skuItems[] = $arElement["ID"];
				}
			}
		}

		if (!empty($skuItems)) {
			$rsElements = CIBlockElement::GetList(
				["SORT"=>"ASC"], // order
				[  // filter
					"IBLOCK_ID" => CATALOG_SKU_IBLOCK_ID,
					"ID" => $skuItems,
				],
				false, // group
				false, // ["nTopCount" => 1], // pagination
				[ // select
					"ID",
					"NAME",
					"IBLOCK_ID",
					"PROPERTY_CML2_LINK",
					"PROPERTY_BX_RAZMER"
				]
			);
			$originalItems = [];
			$skuItemsFull = [];
			while ($arElement = $rsElements->GetNext()) {
				$originalItems[$arElement["ID"]] = $arElement["PROPERTY_CML2_LINK_VALUE"];
				$skuItemsFull[$arElement["ID"]] = $arElement;
			}
			$rsElements = CIBlockElement::GetList(
				["SORT"=>"ASC"], // order
				[  // filter
					"IBLOCK_ID" => CATALOG_IBLOCK_ID,
					"ID" => $originalItems,
				],
				false, // group
				false, // ["nTopCount" => 1], // pagination
				[ // select
					"ID",
					"IBLOCK_ID",
					"NAME",
					"DETAIL_PAGE_URL",
					"CODE",
					"DETAIL_PICTURE",
				]
			);
			while ($arElement = $rsElements->GetNext()) {
				$arElement["DETAIL_PICTURE"] = CFile::GetFileArray($arElement["DETAIL_PICTURE"]);
				$arElement["DETAIL_PICTURE"]["SRC"] = "http://" . $domains[0] . $arElement["DETAIL_PICTURE"]["SRC"];

				foreach ($originalItems as $skuId => $originalId) {
					if ($arElement["ID"] != $originalId) {
						continue;
					}

					$arElement["NAME"] = $skuItemsFull[$skuId]["NAME"];
					// $arElement["NAME"] .= " [Размер: " . $skuItemsFull[$skuId]["PROPERTY_BX_RAZMER_VALUE"] . "]";

					$catalogItems[$skuId] = $arElement;
				}
			}
		}

		ob_start();
		?>
			<table
				align="center"
				width="90%"
			>
				<tbody>
					<? foreach ($basketItems as $basketItem):
						$itemID = $basketItem->getProductId();
						$price = $basketItem->getPrice();
						$quantity = $basketItem->getQuantity();
						$sum = $basketItem->getFinalPrice();
						$price =  CCurrencyLang::CurrencyFormat($price, "RUB");
						$sum =  CCurrencyLang::CurrencyFormat($sum, "RUB");

						$catalogItem = $catalogItems[$itemID];
						?>
						<tr>
							<td>
								<a
									href="<?=$catalogItem["DETAIL_PAGE_URL"]?>"
									target="_blank"
								><img
										src="<?=$catalogItem["DETAIL_PICTURE"]["SRC"]?>"
										height="100"
										width="100"
										border="0"
										alt=""
										style="display: inline-block; object-fit: contain;"
									/></a>
							</td>
							<td valign="top">
								<a
									href="<?=$catalogItem["DETAIL_PAGE_URL"]?>"
									target="_blank"
									style="font-family: system-ui, -apple-system, 'Segoe UI', 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 17px; line-height: 26px; color: #000000;  text-decoration: none; padding-left: 18px; display: block;"
								><?=$catalogItem["NAME"]?></a>
							</td>
							<td>
								<div style="height: 30px; width: 10px; line-height: 30px; font-size: 28px">&nbsp;</div>
							</td>
							<td valign="top">
								<div
									style="min-width: 130px; font-family: system-ui, -apple-system, 'Segoe UI', 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #000000;">
									<?=$quantity?> шт. x <?=$price?></div>
							</td>
						</tr>
					<? endforeach; ?>
				</tbody>
			</table>
			<?
			$arFields["ORDER_LIST"] = ob_get_clean();

		$arFields["LEGAL_ENTITY_DATA"] = "";
		if ($order->getPersonTypeId() == 2) {// Юр лицо LEGAL
			$propertyCollection = $order->getPropertyCollection();
			$companyName = $propertyCollection->getItemByOrderPropertyCode("COMPANY")->getValue();
			$inn = $propertyCollection->getItemByOrderPropertyCode("INN")->getValue();
			$phone = $propertyCollection->getItemByOrderPropertyCode("PHONE")->getValue();
			ob_start();

			?>
				<div
					style="font-family: system-ui, -apple-system, 'Segoe UI', 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 16px; line-height: 24px; font-weight: 400; color: #ffffff;">
					Название организации: <span style="font-weight: 700;"><?=$companyName?></span>
				</div>
				<div
					style="font-family: system-ui, -apple-system, 'Segoe UI', 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 16px; line-height: 24px; font-weight: 400; color: #ffffff;">
					ИНН: <span style="font-weight: 700;"><?=$inn?></span>
				</div>
				<div
					style="font-family: system-ui, -apple-system, 'Segoe UI', 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 16px; line-height: 24px; font-weight: 400; color: #ffffff;">
					Телефон: <span style="font-weight: 700;"><?=$phone?></span>
				</div>
				<?

			$arFields["LEGAL_ENTITY_DATA"] = ob_get_clean();
		}
	}


	/**
	 * Зачастую мне надо было деактивировать элементы за 1С
	 * Например если нету цены или количества.
	 *
	 * @param array $fields
	 * @return void
	 */
	public static function deactivateUnavailable(array &$fields)
	{
		if(!\CModule::IncludeModule("sale")) {
			return;
		}

		if ($fields["ACTIVE"] == "N") {
			return;
		}

		$product = \Bitrix\Catalog\PriceTable::query()
			->setSelect(["PRICE", "QUANTITY" => "PRODUCT.QUANTITY"])
			->where("PRODUCT_ID", $fields["ID"])
			->where("CATALOG_GROUP_ID", BASE_PRICE_ID)
			->setLimit(1)
			->exec()->fetchRaw();

		if (!($product["PRICE"] > 0 && $product["QUANTITY"] > 0)) {
			$fields["ACTIVE"] = "N";
		}
	}

	public static function formatMonth ($DATA) {
		$MES = [
			"01" => "Января",
			"02" => "Февраля",
			"03" => "Марта",
			"04" => "Апреля",
			"05" => "Мая",
			"06" => "Июня",
			"07" => "Июля",
			"08" => "Августа",
			"09" => "Сентября",
			"10" => "Октября",
			"11" => "Ноября",
			"12" => "Декабря"
		];
		$arData = explode(".", $DATA);
		$d = ($arData[0] < 10) ? substr($arData[0], 1) : $arData[0];

		$newData = $d." ".$MES[$arData[1]]." ".$arData[2];
		return $newData;
	}

	/**
	 * А тут составляется html для pdf счета
	 * Используется либа wkhtmltopdf.
	 * Ее бинарник в /modules/mkmatrix.main/wk/wkhtmltopdf-amd64
	 * А вот php либа для работы ставится через композер "mikehaertl/phpwkhtmltopdf"
	 * Если что файлик композера в битре в папке /bitrix/
	 *
	 * @param [type] $orderId
	 * @param string $output
	 * @return void
	 */
	public static function createPdfBill($orderId, $output = "res")
	{
		if (!\CModule::IncludeModule("sale")) {
			ShowError("Модуль sale не установлен!");
			return;
		}

		require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/vendor/autoload.php');

		$params = [
			'CLIENT_BANK' => 'Наименование банка',
			'CLIENT_INN' => '1111111111',
			'CLIENT_KPP' => '111111111',
			'CLIENT_BIK' => '111111111',
			'CLIENT_BILL1' => '11111111111111111111',
			'CLIENT_BILL2' => '11111111111111111111',
			'CLIENT_NAME' => 'Общество с ограниченной ответственностью "Рога и Копыта"',

			'PROVIDER' => 'Общество с ограниченной ответственностью "Рога и Копыта", ИНН ********, КПП ********, индекс, край, город , дальше адрес, тел.: 7 000 000-00-00',
			'SHIPPER'  => 'Общество с ограниченной ответственностью "Рога и Копыта", ИНН ********, КПП ********, индекс, край, город , дальше адрес, тел.: +7 000 000-00-00',
			// 'BUYER' => 'Общество с ограниченной ответственностью "Рога и Копыта", ИНН ********, КПП ********, индекс, край, город , дальше адрес, тел.: 7 000 000-00-00',
			// 'CONSIGNEE' => 'Общество с ограниченной ответственностью "Рога и Копыта", ИНН ********, КПП ********, индекс, край, город , дальше адрес, тел.: +7 000 000-00-00',

			'POSITION' => 'Директор',
			'DIRECTOR_SIGN' => 'Директоров Д.Д.',
			'ACCOUNTANT_SIGN' => 'Бухгалтеров Б.Б.',

			'WORD_TOTAL' => 'Итого:',
			'WORD_NDS' => 'В том числе НДС:',
			'WORD_PAY' => 'Всего к оплате:',
		];

		$order = Sale\Order::load($orderId);
		$basket = $order->getBasket();
		$paymentCollection = $order->getPaymentCollection();
		$propertyCollection = $order->getPropertyCollection();

		$sumPaid = $paymentCollection->getPaidSum();
		foreach ($paymentCollection as $payment) {
			$params['BILL_NUMBER'] = $payment->getId();
		}

		$params['TOTAL'] = $basket->getPrice();
		// $params['NDS'] = $order->getVatSum();
		$params['NDS'] = Sale\PriceMaths::roundPrecision(($order->getPrice()) / 6);

		$params['TOTAL_PAY'] = Sale\PriceMaths::roundPrecision($order->getPrice());
		$params['TOTAL_FORMAT'] = SaleFormatCurrency($params['TOTAL_PAY'], $order->getCurrency());

		$wc = htmlspecialcharsbx(Option::get('sale', 'weight_koef', 1, $siteId));
		if ($basket->getWeight() > 0) {
			$w = roundEx(floatval($basket->getWeight() / $wc), SALE_WEIGHT_PRECISION);
			$params['TOTAL_WEIGHT'] = "(общий вес заказа - " . $w . " " . Option::get('sale', 'weight_unit', false) . ".)";
		}

		$params['BILL_DATE_FROM'] = self::formatMonth($order->getDateInsert()->format('d.m.Y'));
		$params['ITEM_COUNT'] = count($basket);
		$params['TOTAL_AS_STRING'] = Number2Word_Rus($params['TOTAL_PAY']);

		$i = 0;
		foreach ($basket as $basketItem) {
			$product = [];
			$product['NUMBER'] = ++$i;
			$product['NAME'] = $basketItem->getField("NAME");
			$product['COUNT'] = roundEx($basketItem->getQuantity(), SALE_VALUE_PRECISION);
			$product['MEASURE'] = $basketItem->getField("MEASURE_NAME") ? htmlspecialcharsbx($basketItem->getField("MEASURE_NAME")) : "шт.";
			$product['WEIGHT'] = roundEx(floatval($basketItem->getWeight() / $wc), SALE_WEIGHT_PRECISION);
			$product['PRICE'] = roundEx($basketItem->getBasePrice(), SALE_WEIGHT_PRECISION);
			$product['PRICE_WITH_DISCOUNT'] = roundEx($basketItem->getPrice() * (1 - $basketItem->getVatRate()), SALE_WEIGHT_PRECISION);
			$product['PRICE_TOTAL'] = roundEx($basketItem->getPrice() * $basketItem->getQuantity(), SALE_WEIGHT_PRECISION);

			$params['PRODUCTS'][] = $product;
		}

		// if ($val = $propertyCollection->getItemByOrderPropertyId(8)->getValue()) {
		// 	$params['BUYER'] = $val; // Название
		// 	$params['CONSIGNEE'] = $val; // Название
		// }
		// if ($val = $propertyCollection->getItemByOrderPropertyId(10)->getValue()) {
		// 	$params['BUYER'] .= ", ИНН " . $val;
		// 	$params['CONSIGNEE'] .= ", ИНН " . $val;
		// }
		// if ($val = $propertyCollection->getItemByOrderPropertyId(11)->getValue()) {
		// 	$params['BUYER'] .= ", КПП " . $val;
		// 	$params['CONSIGNEE'] .= ", КПП " . $val;
		// }
		// if ($val = $propertyCollection->getItemByOrderPropertyId(16)->getValue()) {
		// 	$params['BUYER'] .= ", " . $val; // zip
		// 	$params['CONSIGNEE'] .= ", " . $val; // zip
		// }
		// if ($val = $propertyCollection->getItemByOrderPropertyId(9)->getValue()) {
		// 	$params['BUYER'] .= ", " . $val; // address
		// }
		$params['BUYER'] .= ", " . $propertyCollection->getAddress()->getValue();
		$params['CONSIGNEE'] .= ", " . $propertyCollection->getAddress()->getValue();
		// if ($val = $propertyCollection->getItemByOrderPropertyId(14)->getValue()) {
		// 	$params['BUYER'] .= ", тел.:  " . $val;
		// 	$params['CONSIGNEE'] .= ", тел.:  " . $val;
		// }

		// $params['CONSIGNEE'] = $params['BUYER'];
		// $params['CONSIGNEE'] = $propertyCollection->getAddress()->getValue();

		if (!function_exists("replaceHtml")) {
			function replaceHtml(&$item)
			{
				if (is_string($item)) {
					$item = htmlspecialcharsBack($item);
					$item = str_replace('&nbsp;', ' ', $item);
				} elseif (is_array($item)) {
					foreach ($item as &$sItem) {
						replaceHtml($sItem);
					}
				}
			}
		}

		replaceHtml($params);

		ob_start(); ?><!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Счет</title>
	<style type="text/css">
		body, html {
			margin: 0;
			padding: 0;
			width: 100%;
			font-family: arial;
			font-size: 8px;
		}

		.container {
			padding: 20px;
			width:800px;
		}

		.head {
			display: inline-block;
			width: 74%;
		}

		.head-right {
			display: inline-block;
			width: 24%;
		}

		.head-right img {
			width: 100%;
		}

		.block {
			border: 1px solid black;
		}

		.block div {
			height: 100%;
		}

		.fs10 {
			font-size: 10px;
		}

		.fs14 {
			font-size: 14px;
		}

		.block .left {
			display: inline-block;
			float: left;
			width: 380px;
		}

		.block .right {
			display: inline-block;
			width: 417px;
		}

		.block .w100 {
			width: 100%;
		}

		.block .h1 {
			height: 12px;
		}

		.block .h2 {
			height: 24px;
		}

		.block .h4 {
			height: 48px;
		}

		.block {
			height: 84px;
		}

		.block .ws {
			display: inline-block;
			width: 10%;
		}

		.block .wb {
			display: inline-block;
			width: 37%;
		}

		.block .wrs {
			display: inline-block;
			width: 15%;
		}

		.block .wrb {
			display: inline-block;
			width: 84%;
		}

		.block .br {
			border-right: 1px solid black;
		}
		.block .bl {
			border-left: 1px solid black;
		}
		.block .bt {
			border-top: 1px solid black;
		}
		.block .bb {
			border-bottom: 1px solid black;
		}

		.title {
			border-bottom: 2px solid black;
			padding: 7px;
			margin-top: 12px;
		}

		.address {
			margin-top: 12px;
			font-size: 10px;
			min-height: 36px;
		}

		.address .name {
			vertical-align: middle;
			width: 16%;
			float: left;
			display: inline-block;
		}

		.address .value {
			font-weight: bold;
			width: 83%;
			display: inline-block;
		}

		table.items {
			border: 1px solid black;
			width: 100%;
		}

		table.items td {
			border: 1px solid black;
		}

		table.items {
			border-collapse: collapse;
		}

		table.items thead {
			font-weight: bold;
		}

		.summary {
			font-size: 10px;
			display: inline-block;
			float: right;
		}

		.summary .name {
			width: 120px;
			font-weight: bold;
		}

		.summary .value {
			width: 90px;
		}

		.summary2 {
			clear: both;
		}

		.bold {
			font-weight: bold;
		}

		.sign, .stamp {
			width: 40%;
			float: right;
		}

		.sign img, .stamp img {
			width: 102%;
			position: relative;
			left: -9px;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="head">
			Внимание! Оплата данного счета означает согласие с условиями поставки товара. Уведомление об оплате
			обязательно, в противном случае не гарантируется наличие товара на складе. Товар отпускается по факту
			прихода денег на р/с Поставщика.
		</div>
		<div class="head-right">
			<img src="<?=$_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/bills/"?>logo.png" alt="">
		</div>
		<div class="block">
			<div class="left br">
				<div class="w100 h2 fs10">
					<?=$params['CLIENT_BANK']?>
				</div>
				<div class="w100 h1 bb">
					Банк получателя
				</div>

				<div class="w100 h1 fs10">
					<div class="ws">
						ИНН
					</div>
					<div class="wb br">
						<?=$params['CLIENT_INN']?>
					</div>
					<div class="ws">
						КПП
					</div>
					<div class="wb">
						<?=$params['CLIENT_KPP']?>
					</div>
				</div>

				<div class="w100 h2 bt fs10">
					<?=$params['CLIENT_BANK']?>
				</div>
				<div class="w100 h1 br">
					Получатель
				</div>
			</div>

			<div class="right fs10">
				<div class="w100 h1">
					<div class="wrs br bb">
						БИК
					</div>
					<div class="wrb">
						<?=$params['CLIENT_BIK']?>
					</div>
				</div>
				<div class="w100 h2">
					<div class="wrs br bb">
						Сч. №
					</div>
					<div class="wrb bb">
						<?=$params['CLIENT_BILL1']?>
					</div>
				</div>
				<div class="w100 h4">
					<div class="wrs br">
						Сч. №
					</div>
					<div class="wrb">
						<?=$params['CLIENT_BILL2']?>
					</div>
				</div>
			</div>
		</div>


		<div class="title fs14">
			Счет на оплату № <?=$params['BILL_NUMBER']?> от <?=$params['BILL_DATE_FROM']?>
		</div>

		<div class="address">
			<div class="name">
				Поставщик:
			</div>
			<div class="value">
				<?=$params['PROVIDER']?>
			</div>
		</div>
		<div class="address">
			<div class="name">
				Покупатель:
			</div>
			<div class="value">
				<?=$params['BUYER']?>
			</div>
		</div>

		<table border="0" cellspacing="0" cellpadding="4" class="items">
			<thead>
				<tr>
					<td>
						№
					</td>
					<td>
						Товары (работы, услуги)
					</td>
					<td>
						Кол-во
					</td>
					<td>
						Ед.
					</td>
					<!-- <td>
						Вес, <?=Option::get('sale', 'weight_unit', false)?>
					</td> -->
					<td>
						Цена
					</td>
					<!-- <td>
						Цена со скидкой
					</td> -->
					<td>
						Сумма
					</td>
				</tr>
			</thead>
			<tbody>
				<? foreach ($params['PRODUCTS'] as $product): ?>
					<tr>
						<td><?=$product['NUMBER']?></td>
						<td><?=$product['NAME']?></td>
						<td><?=$product['COUNT']?></td>
						<td><?=$product['MEASURE']?></td>
						<!-- <td><?=$product['WEIGHT']?></td> -->
						<td><?=$product['PRICE']?></td>
						<!-- <td><?=$product['PRICE_WITH_DISCOUNT']?></td> -->
						<td><?=$product['PRICE_TOTAL']?></td>
					</tr>
				<? endforeach; ?>
			</tbody>
		</table>

		<table class="summary">
			<tr>
				<td class="name" align="right" >Итого:</td>
				<td class="value" align="right"><?=$params['TOTAL']?></td>
			</tr>
			<tr>
				<td class="name" align="right">В том числе НДС:</td>
				<td class="value" align="right"><?=$params['NDS']?></td>
			</tr>
			<tr>
				<td class="name" align="right">Всего к оплате:</td>
				<td class="value" align="right"><?=$params['TOTAL_PAY']?></td>
			</tr>
		</table>

		<div class="summary2">
			<div class="usual fs10">
				Всего наименований <?=$params['ITEM_COUNT']?>, на сумму <?=$params['TOTAL_FORMAT']?> <?//=$params['TOTAL_WEIGHT']?>
			</div>
			<div class="bold fs10">
				<?=$params['TOTAL_AS_STRING']?>
			</div>
		</div>

		<div class="sign">
			<img src="<?=$_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/bills/"?>sign.png" alt="">
			<!-- <img src="/local/php_interface/bills/sign.png" alt=""> -->
		</div>
		<div class="stamp">
			<img src="<?=$_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/bills/"?>stamp.png" alt="">
		</div>
	</div>
</body>
</html>
<?
		$html = @ob_get_contents();
		ob_get_clean();
		// echo $html;
		//

		// You can pass a filename, a HTML string, an URL or an options array to the constructor
		$pdf = new Pdf($html);

		// On some systems you may have to set the path to the wkhtmltopdf executable
		$pdf->binary = __DIR__ . '/../../wk/wkhtmltopdf-amd64';

		$pathToFile = $_SERVER["DOCUMENT_ROOT"] . '/upload/bills/' . $output . '.pdf';

		if (!$pdf->saveAs($pathToFile)) {
			$error = $pdf->getError();
			// echo $error;
		}

		// echo $html;

		return $pathToFile;
	}
}
