<?php
namespace MKMatriX\Main;

use Bitrix\Main\FileTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\DatetimeField;

Loc::loadMessages(__FILE__);

/**
 * Class UserItemsList
 * Это кейс таблица, для хранения связей между пользователем и товарами.
 * Просто на каждом проекте: "Тут избранное, тут желаемое, а тут список покупок."
 * Поэтому все это собралось в бек и форнт решающие эту задачку абстрактно.
 * Правда разные части надо дорабатывать напильником.
 * Кстати может хранить в сессии, если пользователь не авторизован.
 * Стоило использовать sale_fuser, но что-то не пошло.
 *
 * Fields:
 * <ul>
 * <li> USER_ID int mandatory
 * <li> ITEMS array optional
 * </ul>
 *
 * @package MKMatriX\Main\UserItemsList
 **/

abstract class UserItemsList extends DataManager {
	const STORE_IN_SESSION = true;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName() {
		throw new \Exception("Redefine table name", 1);
		return '';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap() {
		// $connection = \Bitrix\Main\Application::getConnection();
		// $helper = $connection->getSqlHelper();

		return [
			(new IntegerField('USER_ID'))
				->configurePrimary()
				->configureTitle("Ид пользователя")
				->configureNullable(false),
			(new ArrayField('ITEMS'))
				->configureNullable(false)
				->configureTitle("Итемы"),
			(new Reference(
				'USER',
				\Bitrix\Main\UserTable::class,
				Join::on('this.USER_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_RIGHT)
		];
	}

	public static function getSessionKey() {
		return strtoupper(static::getTableName());
	}

	public static function saveInSession(array $itemIds) {
		$sessionKey = static::getSessionKey();
		$_SESSION[$sessionKey][CATALOG_IBLOCK_ID] = ["ITEMS" => array_flip($itemIds ?? [])];
		// $_SESSION[$sessionKey] = $itemIds;
		return $itemIds;
	}

	/**
	 * Зачем так сложно?) Ну так хранит битра) Поэтому проще примерно скопировать ее вариант
	 * А не писать свой
	 * Жалко только что избранному приходится развлекаться как сравнению...
	 * @return array
	 */
	public static function getFromSession() {
		$sessionKey = static::getSessionKey();
		$haveFirstKey = is_array($_SESSION[$sessionKey]);
		$haveSecondKey = is_array($_SESSION[$sessionKey][CATALOG_IBLOCK_ID]);
		$haveThirdKey = is_array($_SESSION[$sessionKey][CATALOG_IBLOCK_ID]["ITEMS"]);
		if (!($haveFirstKey && $haveSecondKey && $haveThirdKey)) {
			$_SESSION[$sessionKey][CATALOG_IBLOCK_ID] = ["ITEMS" => []];
		}

		if (!empty($_SESSION[$sessionKey][CATALOG_IBLOCK_ID]["ITEMS"])) {
			return array_keys($_SESSION[$sessionKey][CATALOG_IBLOCK_ID]["ITEMS"]);
		}
		$_SESSION[$sessionKey][CATALOG_IBLOCK_ID] = ["ITEMS" => []];
		return [];
	}

	public static function getMyItems() {
		global $USER;
		if (!$USER->IsAuthorized()) {
			$items = static::getFromSession();
			return $items;
		}

		$raw = static::query()
			->setSelect(["ITEMS"])
			->where("USER_ID", $USER->GetId())
			->setCacheTtl(5)
			->exec()->fetchRaw();
		return $raw["ITEMS"] ?? [];
	}

	public static function setMyItems(array $newItems) {
		global $USER;
		if (!$USER->IsAuthorized()) {
			static::saveInSession(array_values($newItems ?? []));
			return null;
		}

		$obj = static::getByPrimary($USER->GetId())->fetchObject();
		if (is_null($obj)) {
			$obj = static::createObject();
			$obj["USER_ID"] = $USER->GetId();
		}

		$obj["ITEMS"] = $newItems;
		return $obj->save();
	}

	public static function addItemToMyItems($itemId) {
		global $USER;
		if (!$USER->IsAuthorized() || !($itemId > 0)) {
			$fromSession = static::getFromSession();
			if ($itemId > 0) {
				$fromSession[] = $itemId;
				static::saveInSession($fromSession);
			}
			return null;
		}

		$obj = static::getByPrimary($USER->GetId())->fetchObject();
		if (is_null($obj)) {
			$obj = static::createObject();
			$obj["USER_ID"] = $USER->GetId();
		}

		if (is_null($obj["ITEMS"])) {
			$obj["ITEMS"] = [];
		}

		if (count($obj["ITEMS"]) < 1000) {
			$obj["ITEMS"] = array_merge($obj["ITEMS"], [(int) $itemId]);
			$obj["ITEMS"] = array_unique($obj["ITEMS"]);
		}
		static::saveInSession($obj["ITEMS"]);
		return $obj->save();
	}

	public static function deleteItemFromMyItems($itemId) {
		global $USER;
		if (!$USER->IsAuthorized() || !($itemId > 0)) {
			if ($itemId > 0) {
				$tmp = [];
				foreach (static::getFromSession() as $id) {
					if ($itemId != $id) {
						$tmp[] = $id;
					}
				}
				static::saveInSession($tmp);
			}
			return null;
		}

		$obj = static::getByPrimary($USER->GetId())->fetchObject();
		if (is_null($obj)) {
			$obj = static::createObject();
			$obj["USER_ID"] = $USER->GetId();
		}

		if (is_null($obj["ITEMS"])) {
			$obj["ITEMS"] = [];
		}

		$tmp = [];
		foreach ($obj["ITEMS"] as $id) {
			if ($itemId != $id) {
				$tmp[] = $id;
			}
		}
		$obj["ITEMS"] = $tmp;
		static::saveInSession($obj["ITEMS"]);
		return $obj->save();
	}

	public static function checkMyActualItems($itemIds) {
		if(!\CModule::IncludeModule("iblock")) {
			return [];
		}
		if(!\CModule::IncludeModule("sale")) {
			return [];
		}

		if (!(is_array($itemIds) && !empty($itemIds))) {
			return [];
		}

		$items = \Bitrix\Catalog\ProductTable::query()
			->setSelect(["ID"])
			->where("IBLOCK_ELEMENT.IBLOCK_ID", CATALOG_IBLOCK_ID)
			->where("IBLOCK_ELEMENT.ID", "in", $itemIds)
			->where("IBLOCK_ELEMENT.ACTIVE", true)
			// ->where(
			// 	\Bitrix\Main\ORM\Query\Query::filter()
			// 	->logic("or")
			// 	->where("QUANTITY_TRACE", \Bitrix\Catalog\ProductTable::STATUS_NO)
			// 	->where(
			// 		\Bitrix\Main\ORM\Query\Query::filter()
			// 		->where("QUANTITY", ">", 0)
			// 		// ->where(\Bitrix\Main\ORM\Query\Query::filter()
			// 		// 	->where("QUANTITY_TRACE", \Bitrix\Catalog\ProductTable::STATUS_YES)
			// 		// 	->where("QUANTITY_TRACE", \Bitrix\Catalog\ProductTable::STATUS_DEFAULT)
			// 		// )
			// 	)
			// )
			->setCacheTtl(60)
			->fetchAll();

		$itemIds = array_column($items, "ID");
		$itemIds = array_map("intval", $itemIds);

		self::setMyItems($itemIds);
		return $itemIds;
	}
}