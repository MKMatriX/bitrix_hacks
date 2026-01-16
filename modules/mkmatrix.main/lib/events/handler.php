<?

namespace MKMatriX\Main\Events;

class Handler
{
	public static function main_OnBeforeEventAdd(&$event, &$lid, &$arFields) {
		if (Functions::isFrom1CExchange($event, $arFields)) {
			return false;
		}

		$manager = getCurrentManager();
		$arFields["MANAGER_EMAIL"] = $manager["_EMAIL"] ?? "";

		if ($event == "SALE_NEW_ORDER") {
			Functions::modifyOrderItemList($arFields);
		}
	}

	// public static function iblock_OnBeforeIBlockElementAdd(&$fields)
	// {
	// 	switch ($fields['IBLOCK_ID']) {
	// 		case CATALOG_IBLOCK_ID:
	// 			Functions::deactivateUnavailable($fields);
	// 			// Functions::brandToName($fields);
	// 			break;
	// 	}

	// 	return $fields;
	// }

	// public static function iblock_OnBeforeIBlockElementUpdate(&$fields)
	// {
	// 	switch ($fields['IBLOCK_ID']) {
	// 		case CATALOG_IBLOCK_ID:
	// 			Functions::deactivateUnavailable($fields);
	// 			// Functions::brandToName($fields);
	// 			break;
	// 	}
	// }

	public static function sale_OnSaleOrderSaved(\Bitrix\Main\Event $event)
	{
		$order = $event->getParameter("ENTITY");
		// $oldValues = $event->getParameter("VALUES");
		$isNew = $event->getParameter("IS_NEW");
		$isExternal = $event->getParameter("EXTERNAL_ORDER");

		if (!$isNew || $isExternal) {
			return;
		}

		if ($order->getPersonTypeId() != LEGAL_ENTITY_CUSTOMER_ID) {
			return;
		}

		$file = Functions::createPdfBill($order->getId(), "bill" . $order->getId());

		$propertyCollection = $order->getPropertyCollection();

		\Bitrix\Main\Mail\Event::send([
			"EVENT_NAME" => "BILL_TO_PAY",
			"LID" => "s1",
			"C_FIELDS" => [
				"EMAIL_TO" => $propertyCollection->getUserEmail()->getValue()
			],
			"FILE" => [$file]
		]);
	}
}
