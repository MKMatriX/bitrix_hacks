<?
namespace MKMatriX\Export\Controller;


use Bitrix\Sale;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;



class Basket extends \Bitrix\Main\Engine\Controller {
	/**
	 * Returns default pre-filters for action.
	 * @return array
	 */
	protected function getDefaultPreFilters() {
		return [
			new \Bitrix\Main\Engine\ActionFilter\HttpMethod(
				[
					\Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_GET,
					\Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_POST
				]
			),
			new \Bitrix\Main\Engine\ActionFilter\Csrf(),
		];
	}

	public function basketAction ($items) {
		if (!\Bitrix\Main\Loader::includeModule("sale")) {
			throw new \Exception("Модуль sale не установлен!", 1);
		}

		$items = json_decode($items, true);

		$basket = Sale\Basket::loadItemsForFUser(
			Sale\Fuser::getId(),
			\Bitrix\Main\Context::getCurrent()->getSite()
		);

		$haveAdd = false;

		foreach ($items as $productId => $quantity) {
			$item = $basket->getExistsItems('catalog', $productId, null)[0];

			if ($quantity == 0) {
				if ($item) {
					$item->delete();
				}
				continue;
			}

			$haveAdd = true;

			if ($item) {
				$quantity += $item->getField('QUANTITY');
				$item->setField('QUANTITY', $quantity);
			} else {
				$item = $basket->createItem('catalog', $productId);
				$item->setFields([
					'QUANTITY' => $quantity,
					'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
					'LID' => \Bitrix\Main\Context::getCurrent()->getSite(),
					'PRODUCT_PROVIDER_CLASS' => \Bitrix\Catalog\Product\Basket::getDefaultProviderName(),
				]);
			}
		}

		$result = $basket->save();

		if (!$result->isSuccess()) {
			throw new \Exception(implode(", ", $result->getErrorMessages()) , 1);

			return "none";
		}

		try {
			// перемещаем недоступные. Да-да ниже код этого файла.
			require $_SERVER["DOCUMENT_ROOT"] . "/local/templates/main/components/bitrix/sale.basket.basket.line/.default/move_delay.php";
			/*
				use Bitrix\Catalog\ProductTable;
				use Bitrix\Sale;

				CModule::IncludeModule("sale");

				$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());

				$basketItems  = $basket->getBasketItems();
				$needSave = false;

				$productIds = [];
				$delayedItems = [];
				foreach ($basket as $basketItem) {
					$pid = $basketItem->getField("PRODUCT_ID");
					$productIds[] = $pid;
					if ($basketItem->getField('DELAY') === 'Y') {
						if (isset($delayedItems[$pid])) {
							$needSave = true;

							$delayedItems[$pid]->setField(
								"QUANTITY",
								$delayedItems[$pid]->getField("QUANTITY") + $basketItem->getField("QUANTITY")
							);
							$basketItem->delete();
						} else {
							$delayedItems[$pid] = $basketItem;
						}
					}
				}

				$quantities = ProductTable::query()
					->setSelect(["ID", "QUANTITY"])
					->where("ID", "in", $productIds)
					->exec()->fetchAll();

				$quantities = array_combine(
					array_column($quantities, "ID"),
					array_column($quantities, "QUANTITY")
				);

				foreach ($basket as $basketItem) {
					if ($basketItem->getField('DELAY') === 'Y') {
						continue;
					}
					$buyQuantity = $basketItem->getField("QUANTITY");
					$pid = $basketItem->getField("PRODUCT_ID");
					$availableQuantity = (float) $quantities[$pid];
					$extra = $buyQuantity - $availableQuantity;

					if ($extra > 0) {
						$needSave = true;
						if ($availableQuantity > 0) {
							$basketItem->setField("QUANTITY", $availableQuantity);
						} else {
							$basketItem->delete();
						}

						if (isset($delayedItems[$pid])) {
							$delayedItems[$pid]->setField("QUANTITY", $delayedItems[$pid]->getField("QUANTITY") + $extra);
						} else {
							$item = $basket->createItem('catalog', $pid);
							$item->setFields([
								'QUANTITY' => $extra,
								'CURRENCY' => Bitrix\Currency\CurrencyManager::getBaseCurrency(),
								'LID' => Bitrix\Main\Context::getCurrent()->getSite(),
								'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
								'DELAY' => "Y"
							]);
						}
					}
				}

				if ($needSave) {
					$result = $basket->save();
					if (!$result->isSuccess()) {
						// вывод ошибок
					}
				}
			*/
		} catch (\Throwable $th) {
			//throw $th;
		}


		return $haveAdd? "basket" : "none";
	}
}