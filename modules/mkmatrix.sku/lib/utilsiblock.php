<?php
namespace MKMatriX\SKU;

use \Bitrix\Catalog\Product\PropertyCatalogFeature;
use \Bitrix\Catalog\ProductTable;
use \Bitrix\Iblock\PropertyFeatureTable;
use \Bitrix\Iblock\PropertyTable;
use \MKMatriX\SKU\Override\PropertyEnumerationTable;

/*
// Для отладки
CModule::IncludeModule("sale"); // если не подключается автоматом
CModule::IncludeModule("mkmatrix.sku");
// echo \MKMatriX\SKU\UtilsIblock::getSkuLinkPropId(); // ид свойства связки
// \MKMatriX\SKU\UtilsIblock::findWrongElements(); // найти элементы к перемещению
// \MKMatriX\SKU\UtilsIblock::moveAllElements(); // переместить

// // переместить один элемент
// $id = 123;
// \MKMatriX\SKU\UtilsIblock::moveElement($id, true);
// \MKMatriX\SKU\UtilsIblock::moveElement($id);
*/


/*
TODO:
Для апдейта можно переносить элемент обратно.
ну или апдейтить простым способом
*/


/**
 * Класс для создания торговых предложений из свойства
 * с XML_ID родителя
 */
class UtilsIblock {
	public static $linkPropId;
	public static $linkPropCode = "TORGOVOE_PREDLOZHENIE";
	public static $skuLinkPropId;
	public static $skuLinkPropCode = "CML2_LINK";
	public static $enumJoin = [];
	public static $propJoin = [];
	public static $CATALOG_IBLOCK_ID = CATALOG_IBLOCK_ID;
	public static $CATALOG_SKU_IBLOCK_ID = CATALOG_SKU_IBLOCK_ID;

	private static $spClassTail = 0;

	public static function setOptions() {
		$moduleName = "mkmatrix.sku";
		self::$linkPropCode = \COption::GetOptionString($moduleName, "linkPropCode", "TORGOVOE_PREDLOZHENIE");
		self::$skuLinkPropCode = \COption::GetOptionString($moduleName, "skuLinkPropCode", "CML2_LINK");
		self::$CATALOG_IBLOCK_ID = \COption::GetOptionString($moduleName, "CATALOG_IBLOCK_ID", CATALOG_IBLOCK_ID);
		self::$CATALOG_SKU_IBLOCK_ID = \COption::GetOptionString($moduleName, "CATALOG_SKU_IBLOCK_ID", CATALOG_SKU_IBLOCK_ID);
	}

	public static function checkOptions() {
		$errors = [];
		if (!strlen(self::$linkPropCode)) {
			$errors[] = "Не указан код свойства с XML_ID родителя";
		} else {
			$linkProp = PropertyTable::query()
				->setSelect(["ID", "CODE", "IBLOCK_ID"])
				->where("CODE", self::$linkPropCode)
				->exec()->fetchRaw();
		}
		if (!strlen(self::$skuLinkPropCode)) {
			$errors[] = "Не указан код свойства для привязки родителя";
		} else {
			$skuLinkProp = PropertyTable::query()
				->setSelect(["ID", "CODE", "IBLOCK_ID"])
				->where("CODE", self::$skuLinkPropCode)
				->exec()->fetchRaw();
		}

		if (!(self::$CATALOG_IBLOCK_ID > 0)) {
			$errors[] = "Не указан ИД инфоблока каталога";
		} else {
			$catalogIblock = \Bitrix\Iblock\IblockTable::query()
				->setSelect(["ID"])
				->where("ID", self::$CATALOG_IBLOCK_ID)
				->exec()->fetchRaw();

			if (!is_array($catalogIblock)) {
				$errors[] = "Инфоблок каталога не найден";
			} else {
				if ($catalogIblock["ID"] != $linkProp["IBLOCK_ID"]) {
					$errors[] = "Инфоблок кода свойства с XML_ID родителя не является инфоблоком каталога";
				}
			}
		}

		if (!(self::$CATALOG_SKU_IBLOCK_ID > 0)) {
			$errors[] = "Не указан ИД инфоблока каталога";
		} else {
			$catalogSkuIblock = \Bitrix\Iblock\IblockTable::query()
				->setSelect(["ID"])
				->where("ID", self::$CATALOG_SKU_IBLOCK_ID)
				->exec()->fetchRaw();

			if (!is_array($catalogSkuIblock)) {
				$errors[] = "Инфоблок предложений каталога не найден";
			} else {
				if ($catalogSkuIblock["ID"] != $skuLinkProp["IBLOCK_ID"]) {
					$errors[] = "Инфоблок кода свойства для привязки родителя не является инфоблоком торговых предложений";
				}
			}
		}

		if (!empty($errors)) {
			throw new \Exception(implode("<br/>", $errors));
		}

		return true;
	}

	public static function findWrongElements() {
		$propId = self::getParentLinkPropId();

		$dc = self::getSinglePropsDataClass(self::$CATALOG_IBLOCK_ID);
		$propCol = "PROPERTY_" . $propId;
		$propVals = $dc::query()
			->setSelect(["IBLOCK_ELEMENT_ID", $propCol])
			->whereNotNull($propCol)
			->exec()->fetchAll();

		if (empty($propVals)) {
			return [];
		}

		$propVals = array_combine(
			array_column($propVals, "IBLOCK_ELEMENT_ID"),
			array_column($propVals, $propCol),
		);

		$filterChild = \Bitrix\Iblock\ElementTable::query()
			->setSelect(["ID", "XML_ID"])
			->where("ACTIVE", "Y")
			->where("IBLOCK_ID", self::$CATALOG_IBLOCK_ID)
			->where("ID", "in", array_keys($propVals))
			->exec()->fetchAll();

		// оставляем только те, для которых действительно есть элементы
		$propVals = array_intersect_key($propVals, array_flip(array_column($filterChild, "ID")));

		// убираем те, которые ссылаются на элементы для переноса
		$propVals = array_diff($propVals, array_column($filterChild, "XML_ID"));

		if (empty($propVals)) {
			return [];
		}

		$parents = \Bitrix\Iblock\ElementTable::query()
			->setSelect(["XML_ID"])
			->where("IBLOCK_ID", self::$CATALOG_IBLOCK_ID)
			->where("ACTIVE", "Y")
			->where("XML_ID", "in", $propVals)
			->exec()->fetchAll();

		$parents = array_column($parents, "XML_ID");

		$intersect = array_intersect($propVals, $parents);
		$foundIds = array_keys($intersect);

		if (empty($foundIds)) {
			return [];
		}

		$filteredElements = \Bitrix\Iblock\ElementTable::query()
			->setSelect(["ID", "NAME", "XML_ID"])
			->where("IBLOCK_ID", self::$CATALOG_IBLOCK_ID)
			->where("ID", "in", $foundIds)
			->where("ACTIVE", "Y")
			->exec()->fetchAll();


		return array_column($filteredElements, "ID");
	}

	public static function moveAllElements() {
		\CModule::IncludeModule("sale");

		$elements = self::findWrongElements();

		// todo: улучшить построение соединения enum
		foreach ($elements as $id) {
			self::moveElement($id, true);
		}

		foreach ($elements as $id) {
			self::moveElement($id);
		}

		return $elements;
	}

	private static function createPropsIfNeeded($diffProps) {
		$catalog2skuPropIds = [];
		foreach ($diffProps as $propType) {
			foreach ($propType as $prop) {
				$catalog2skuPropIds[$prop] = self::getSKUProp($prop);
			}
		}

		return $catalog2skuPropIds;
	}

	private static function changeElementIblockId($id) {
		$query = "UPDATE " . \Bitrix\Iblock\ElementTable::getTableName() . " ";
		$query .= "SET IBLOCK_ID = '" . self::$CATALOG_SKU_IBLOCK_ID . "', ";
		$query .= "IBLOCK_SECTION_ID = NULL, ";
		$query .= "IN_SECTIONS = 'N' ";
		$query .= "WHERE ID = " . $id;
		$query .= ";";

		$connection = \Bitrix\Main\Application::getConnection();
		$connection->query($query);
	}

	private static function changeProductTypes($id, $parentId) {
		$products = ProductTable::query()
			->where(\Bitrix\Main\ORM\Query\Query::filter()
				->logic("or")
				->where("ID", $id)
				->where("ID", $parentId)
			)
			->exec()->fetchCollection();
		if (!is_null($products)) {
			foreach ($products as $product) {
				if ($product["ID"] == $parentId) {
					$product["TYPE"] = ProductTable::TYPE_SKU;
				} elseif ($product["ID"] == $id) {
					$product["TYPE"] = ProductTable::TYPE_OFFER;
				}
			}
			$products->save();
		}
	}

	public static function moveElement(int $id, $skipMove = false) {
		if(!\CModule::IncludeModule("catalog")) {
			ShowError("Модуль catalog не установлен!");
			return;
		}

		// $DB->StartTransaction();
		list($diffProps, $selfProps, $parentId) = self::findDiffProps($id);
		$catalog2skuPropIds = self::createPropsIfNeeded($diffProps);

		if ($skipMove) {
			return $id;
		}

		self::changeElementIblockId($id);
		self::changeProductTypes($id, $parentId);

		self::moveProps($id, $selfProps, $catalog2skuPropIds, $parentId);
		return $id;
	}

	// IMPROVE: хорошо бы, тут еще принимать массив идешников, а не один
	public static function findDiffProps($id) {
		list($parent, $parentId) = self::getParent($id);
		if (empty($parent)) {
			return [];
		}

		$self = self::getSelf($id);

		$diff = [];
		foreach ($self as $propType => $selfProps) {
			foreach ($selfProps as $key => $value) {
				if ($key == self::getParentLinkPropId()) {
					continue;
				}

				if ($parent[$propType][$key] !== $value) {
					$diff[$propType][] = $key;
				}
			}
		}

		return [$diff, $self, $parentId];
	}

	// IMPROVE: тогда тут надо более сложную структуру, вида ["elemId" => ["propId" => "propValue"]]
	private static function getParent($id) {
		$parentPropId = self::getParentLinkPropId();

		if (!($parentPropId > 0)) {
			return [];
		}

		// получаем XML_ID родителя
		$parentXml = self::getSinglePropsDataClass(self::$CATALOG_IBLOCK_ID)::query()
			->setSelect(["PROPERTY_" . $parentPropId])
			->where("IBLOCK_ELEMENT_ID", $id)
			->exec()->fetchRaw()["PROPERTY_" . $parentPropId];

		if (!mb_strlen($parentXml)) {
			return [];
		}

		// получаем ID родителя
		$parent = \Bitrix\Iblock\ElementTable::query()
			->setSelect([
				"ID",
			])
			->where("XML_ID", $parentXml)
			->where("IBLOCK_ID", self::$CATALOG_IBLOCK_ID)
			->exec()->fetchRaw();

		if (is_null($parent)) {
			return [];
		}
		$id = $parent["ID"];

		return [self::getProps([$id])[$id], $id];
	}

	public static function getParentLinkPropId() {
		$propCode = self::$linkPropCode;
		if (self::$linkPropId > 0) {
			return self::$linkPropId;
		}
		// получаем Ид свойства по его коду
		self::$linkPropId = self::getLinkPropIdFromBase($propCode, self::$CATALOG_IBLOCK_ID);

		return self::$linkPropId;
	}

	public static function getSkuLinkPropId() {
		$propCode = self::$skuLinkPropCode;
		if (self::$skuLinkPropId > 0) {
			return self::$skuLinkPropId;
		}
		// получаем Ид свойства по его коду
		self::$skuLinkPropId = self::getLinkPropIdFromBase($propCode, self::$CATALOG_SKU_IBLOCK_ID);

		return self::$skuLinkPropId;
	}

	public static function getLinkPropIdFromBase($propCode, $iblockId) {
		// получаем Ид свойства по его коду
		return PropertyTable::query()
			->setSelect(["ID"])
			->where("CODE", $propCode)
			->where("IBLOCK_ID", $iblockId)
			->exec()->fetchRaw()["ID"];
	}

	// IMPROVE: тогда тут надо более сложную структуру, вида ["elemId" => ["propId" => "propValue"]]
	private static function getSelf($id) {
		return self::getProps([$id])[$id];
	}

	/**
	 * Получить структуру вида ["elemId" => "SINGLE||MULTIPLE" => ["propId" => "propValue"]]
	 * для множественный свойств разделитель ", "
	 * работает только для свойств версии 2
	 *
	 * @param array $ids
	 *
	 * @return [type]
	 */
	public static function getProps(array $ids = []) {
		$result = [];

		if (empty($ids)) {
			return $result;
		}

		self::getSingleProps($ids, $result);
		self::getMultipleProps($ids, $result);

		return $result;
	}

	private static function getSingleProps(array $ids, &$result) {
		$sPropValues = self::getSinglePropsDataClass(self::$CATALOG_IBLOCK_ID)::query()
			->setSelect(["*"])
			->where("IBLOCK_ELEMENT_ID", "in", $ids)
			// ->unionAll()
			->exec()->fetchAll();

		foreach ($sPropValues as $row) {
			$tmp = [];
			$result[$row["IBLOCK_ELEMENT_ID"]]["SINGLE"] = [];
			foreach ($row as $key => $value) {
				if (!mb_strlen($value)) {
					continue;
				}

				if (mb_substr($key, 0, 9) == "PROPERTY_") {
					$propId = (int) mb_substr($key, 9);
					if ($propId == self::$linkPropId) {
						continue;
					}
					$tmp[$propId] = $value;
				}
			}
			$result[$row["IBLOCK_ELEMENT_ID"]]["SINGLE"] = $tmp;
		}
	}

	private static function setScuPropsTableChange() {
		self::$spClassTail++;
	}

	public static function getSinglePropsDataClass($iblockId) {
		$className = 'SProps' . $iblockId;

		if ($iblockId == self::$CATALOG_SKU_IBLOCK_ID) {
			$className .= "_" . self::$spClassTail;
		}

		if (class_exists($className . "Table")) {
			return $className . "Table";
		}

		$props = PropertyTable::query()
			->setSelect(["ID", "MULTIPLE", "PROPERTY_TYPE"])
			->where("IBLOCK_ID", $iblockId)
			->where("MULTIPLE", "N")
			->where("VERSION", 2)
			->exec()->fetchAll();

		$sProps = [];
		foreach ($props as $prop) {
			$key = "PROPERTY_" . $prop["ID"];
			$type = $prop["PROPERTY_TYPE"] == PropertyTable::TYPE_NUMBER ? 'float' : 'string';
			$sProps[$key] = ['data_type' => $type];
		}
		// не объявлять эту штуку как примари
		$sProps['IBLOCK_ELEMENT_ID'] = new \Bitrix\Main\Entity\IntegerField('IBLOCK_ELEMENT_ID');

		$entitySProps = \Bitrix\Main\Entity\Base::compileEntity(
			$className,
			$sProps,
			['table_name' => sprintf('b_iblock_element_prop_s%s', $iblockId)]
		);

		return $entitySProps->getDataClass();
	}

	public static function getMultipleProps(array $ids, &$result) {
		$mPropValues = self::getMultiplePropsDataClass(self::$CATALOG_IBLOCK_ID)::query()
			->setSelect(["*"])
			->where("IBLOCK_ELEMENT_ID", "in", $ids)
			->setOrder(["ID" => "DESC"])
			->exec()->fetchAll();

		$mPropsByElement = [];
		foreach ($mPropValues as $row) {
			$mPropsByElement[$row["IBLOCK_ELEMENT_ID"]][] = $row;
		}

		foreach ($mPropsByElement as $elementId => $props) {
			$tmp = [];

			// собираем значения в массивы
			foreach ($props as $row) {
				$tmp[$row["IBLOCK_PROPERTY_ID"]]["VALUE"][] = $row["VALUE"];
				$tmp[$row["IBLOCK_PROPERTY_ID"]]["VALUE_ENUM"][] = $row["VALUE_ENUM"];
				$tmp[$row["IBLOCK_PROPERTY_ID"]]["VALUE_NUM"][] = $row["VALUE_NUM"];
			}

			// TODO: optimisation: убрать, и склеивать лишь перед сравнением
			// склеиваем массивы в строки значений по клею ", "
			foreach ($tmp as $propId => $values) {
				$tmp[$propId]["VALUE"] = implode(", ", $values["VALUE"]);
				$tmp[$propId]["VALUE_ENUM"] = implode(", ", $values["VALUE_ENUM"]);
				$tmp[$propId]["VALUE_NUM"] = implode(", ", $values["VALUE_NUM"]);
			}

			// преобразуем к строке
			foreach ($tmp as $propId => $values) {
				$tmp[$propId] = "VALUE=" . $values["VALUE"] . "|";
				$tmp[$propId] .= "VALUE_ENUM=" . $values["VALUE_ENUM"] . "|";
				$tmp[$propId] .= "VALUE_NUM=" . $values["VALUE_NUM"];
			}

			if (!is_array($result[$elementId]["MULTIPLE"])) {
				$result[$elementId]["MULTIPLE"] = [];
			}
			$result[$elementId]["MULTIPLE"] = $tmp;
		}
		unset($tmp);
	}

	/**
	 * @param mixed $iblockId
	 *
	 * @return \Bitrix\Main\Entity
	 */
	public static function getMultiplePropsDataClass($iblockId) {
		$className = 'MProps' . $iblockId;

		if (class_exists($className . "Table")) {
			return $className . "Table";
		}

		$entityMProps = \Bitrix\Main\Entity\Base::compileEntity(
			$className,
			[
				'ID' => ['data_type' => 'integer', 'primary' => true],
				'IBLOCK_ELEMENT_ID' => ['data_type' => 'integer'],
				'IBLOCK_PROPERTY_ID' => ['data_type' => 'integer'],
				'VALUE' => ['data_type' => 'string'],
				'VALUE_ENUM' => ['data_type' => 'string'],
				'VALUE_NUM' => ['data_type' => 'float'],
				'DESCRIPTION' => ['data_type' => 'string'],
			],
			['table_name' => sprintf('b_iblock_element_prop_m%s', $iblockId)]
		);

		return $entityMProps->getDataClass();
	}

	public static function moveProps($elementId, $props, $catalog2skuPropIds, $parentId) {
		// single
		$spObj = self::getSinglePropEntityObject($elementId);

		$enumJoin = self::createEnumJoinArray();

		if ($prop["PROPERTY_TYPE"] == PropertyTable::TYPE_LIST && $prop["LIST_TYPE"] == PropertyTable::LISTBOX) {
			self::copyEnumRows($propId, $id);
		}
		$enumProps = PropertyTable::query()
			->setSelect(["ID"])
			->where("PROPERTY_TYPE", PropertyTable::TYPE_LIST)
			->where("LIST_TYPE", PropertyTable::LISTBOX)
			->where("IBLOCK_ID", self::$CATALOG_IBLOCK_ID)
			->exec()->fetchAll();
		$enumProps = array_column($enumProps, "ID");

		foreach ($props["SINGLE"] as $id => $value) {
			$newPropId = $catalog2skuPropIds[$id];
			if (!isset($catalog2skuPropIds[$id])) {
				continue;
			}

			if (in_array($id, $enumProps)) {
				$spObj["PROPERTY_" . $newPropId] = $enumJoin[$value];
			} else {
				$spObj["PROPERTY_" . $newPropId] = $value;
			}

		}
		$spObj["IBLOCK_ELEMENT_ID"] = $elementId;

		$linkPropId = self::getSkuLinkPropId();
		$spObj["PROPERTY_" . $linkPropId] = $parentId;

		$spObj->save();

		// multiple
		$skuMdc = self::getMultiplePropsDataClass(self::$CATALOG_SKU_IBLOCK_ID);
		$oldMdc = self::getMultiplePropsDataClass(self::$CATALOG_IBLOCK_ID);

		$newProps = $skuMdc::createCollection();
		// TODO: optimisation: у нас уже есть все значения, нужно просто их взять)
		$oldProps = $oldMdc::query()
			->setSelect(["*"])
			->where("IBLOCK_ELEMENT_ID", $elementId)
			->exec()->fetchCollection();

		foreach ($oldProps as $oldProp) {
			$newProp = $skuMdc::createObject();
			$newProp["IBLOCK_ELEMENT_ID"] = $elementId;
			$newProp["IBLOCK_PROPERTY_ID"] = $catalog2skuPropIds[$oldProp["IBLOCK_PROPERTY_ID"]];
			$newProp["VALUE"] = $oldProp["VALUE"];
			$newProp["VALUE_ENUM"] = $oldProp["VALUE_ENUM"];
			$newProp["VALUE_NUM"] = $oldProp["VALUE_NUM"];
			$newProp["DESCRIPTION"] = $oldProp["DESCRIPTION"];
			$newProps[] = $newProp;
			$oldProp->delete();
		}

		$oldProps->save();
		$newProps->save();
	}

	/**
	 * @param mixed $elementId
	 *
	 * @return \Bitrix\Iblock\Property
	 */
	private static function getSinglePropEntityObject($elementId) {
		$spdc = self::getSinglePropsDataClass(self::$CATALOG_SKU_IBLOCK_ID);

		$spObj = $spdc::query()
			->where("IBLOCK_ELEMENT_ID", $elementId)
			->exec()->fetchObject();

		if (!is_null($spObj)) {
			// кто-то может спросить зачем так извращаться?)
			// ну без primary нельзя в битре удалять
			// а primary нельзя задавать ибо его нельзя менять
			// что как бы грустно и не понятно зачем
			// вот и приходится извращаться когда это primary надо менять

			$query = "DELETE FROM " . $spdc::getTableName() . " ";
			$query .= "WHERE `IBLOCK_ELEMENT_ID` = " . $elementId;
			$query .= ";";

			$connection = \Bitrix\Main\Application::getConnection();
			$connection->query($query);
		}
		$spObj = $spdc::createObject(false);

		return $spObj;
	}

	// bind that to update of original prop
	public static function getSKUProp($oldPropId) {
		// если свойство уже есть по XML_ID_copy или совпадению CODE
		$newPropId = self::getSKUPropId($oldPropId);

		if (!$newPropId) { // если нету то создаем
			$newPropId = self::copyProp($oldPropId);
			// echo "created prop $newPropId from $oldPropId \n";
		}
		// else {
		// 	echo "found prop $newPropId from $oldPropId \n";
		// }

		return $newPropId;
	}

	private static function getSKUPropId($oldPropId) {
		// TODO: get From Cache
		$oldPropsId = is_array($oldPropId)? $oldPropId : [$oldPropId];
		$result = [];

		$props = PropertyTable::query()
			->setSelect(["ID", "XML_ID", "CODE"])
			->where("ID", "in", $oldPropsId)
			->where("IBLOCK_ID", self::$CATALOG_IBLOCK_ID)
			->exec()->fetchAll();

		if (is_null($props) || empty($props)) {
			throw new \Exception("Empty prop code", 1);
		}
		$checkArray = array_combine(
			array_column($props, "ID"),
			$props
		);
		foreach ($checkArray as $key => $prop) {
			if (!(mb_strlen($prop["XML"]) || mb_strlen($prop["CODE"]))) {
				throw new \Exception("Empty prop code: " . print_r($prop, 1), 1);
			}
		}

		$xmlAr = [];
		foreach ($props as $prop) {
			$xmlAr[$prop["ID"]] = $prop["XML_ID"] . "_copy";
		}

		$propIds = PropertyTable::query()
			->setSelect(["ID", "XML_ID"])
			->where("XML_ID", "in", $xmlAr)
			->where("IBLOCK_ID", self::$CATALOG_SKU_IBLOCK_ID)
			->exec()->fetchAll();

		$propIds = array_combine(
			array_column($propIds, "XML_ID"),
			array_column($propIds, "ID")
		);
		$findByCode = [];
		$id2code = array_combine(
			array_column($props, "ID"),
			array_column($props, "CODE")
		);
		foreach ($xmlAr as $id => $xmlId) {
			$newPropId = $propIds[$xmlId];
			if (!isset($propIds[$xmlId])) {
				$findByCode[$id] = $id2code[$id];
			}
			$result[$id] = $newPropId;
		}

		if (!empty($findByCode)) {
			$propIds = PropertyTable::query()
				->setSelect(["ID", "CODE"])
				->where("CODE", "in", $findByCode)
				->where("IBLOCK_ID", self::$CATALOG_SKU_IBLOCK_ID)
				->exec()->fetchAll();

			$skuCode2id = array_combine(
				array_column($propIds, "CODE"),
				array_column($propIds, "ID")
			);

			foreach ($findByCode as $id => $code) {
				$result[$id] = $skuCode2id[$code];
			}

		}

		if (!is_array($oldPropId)) {
			if (!($result[$oldPropId] > 0)) {
				return 0;
				// throw new \Exception("Couldn`t find prop for : " . $oldPropId, 1);
			}
			return $result[$oldPropId];
		}

		$result = array_filter($result, 'is_numeric');

		return $result;
	}

	private static function copyProp($propId) {
		if (!\CModule::IncludeModule("iblock")) {
			ShowError("Модуль iblock не установлен!");
			return;
		}

		$prop = PropertyTable::getById($propId)->fetchRaw();

		if (!is_array($prop)) {
			throw new \Exception("no parent", 1);
		}
		$prop["IBLOCK_ID"] = self::$CATALOG_SKU_IBLOCK_ID;
		$prop["XML_ID"] .= "_copy";
		$prop["VERSION"] = (int) $prop["VERSION"];
		unset($prop["TIMESTAMP_X"]);
		unset($prop["ID"]);

		$res = PropertyTable::add($prop);
		if (!$res->isSuccess()) {
			throw new \Exception(implode($res->getErrorMessages()), 1);
		}

		$id = $res->getId(); // new prop id

		if ($prop["PROPERTY_TYPE"] == PropertyTable::TYPE_LIST && $prop["LIST_TYPE"] == PropertyTable::LISTBOX) {
			self::copyEnumRows($propId, $id);
		}


		/*
		Создаем колонки в бд
		SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
		WHERE table_name = 'b_iblock_element_prop_s22' AND COLUMN_NAME = 'PROPERTY_427';

		ALTER TABLE b_iblock_element_prop_s23
		ADD PROPERTY_ZXC decimal(18,4);

		Ну и конечно тут остается еще TODO: оптимизация, собирание запросов в кучу
		*/
		$query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS ";
		$query .= "WHERE table_name = 'b_iblock_element_prop_s" . self::$CATALOG_IBLOCK_ID . "' ";
		$query .= "AND COLUMN_NAME = 'PROPERTY_". $propId ."' ";
		$query .= ";";

		$connection = \Bitrix\Main\Application::getConnection();
		$res = $connection->query($query);
		$columnType = $res->fetchRaw()["COLUMN_TYPE"];

		$query = "ALTER TABLE b_iblock_element_prop_s" . self::$CATALOG_SKU_IBLOCK_ID . " " ;
		$query .= "ADD PROPERTY_". $id ." ". $columnType ."; ";

		$connection = \Bitrix\Main\Application::getConnection();
		$connection->query($query);

		// Повторяем для описаний свойств
		$query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS ";
		$query .= "WHERE table_name = 'b_iblock_element_prop_s" . self::$CATALOG_IBLOCK_ID . "' ";
		$query .= "AND COLUMN_NAME = 'DESCRIPTION_". $propId ."' ";
		$query .= ";";

		$connection = \Bitrix\Main\Application::getConnection();
		$res = $connection->query($query);
		$columnType = $res->fetchRaw()["COLUMN_TYPE"];

		if (strlen($columnType)) {
			$query = "ALTER TABLE b_iblock_element_prop_s" . self::$CATALOG_SKU_IBLOCK_ID . " " ;
			$query .= "ADD DESCRIPTION_". $id ." ". $columnType ."; ";

			$connection = \Bitrix\Main\Application::getConnection();
			$connection->query($query);
		}

		// у нас изменилась таблица, поэтому старый класс больше не подойдет
		self::setScuPropsTableChange();

		// Это чтобы свойство участвовало в выборе у предложения
		$featureCollection = PropertyFeatureTable::query()
			->setSelect(["*"])
			->where("PROPERTY_ID", $id)
			->where("MODULE_ID", "catalog")
			->exec()->fetchCollection();

		if (is_null($featureCollection)) {
			$featureCollection = PropertyFeatureTable::createCollection();
		}

		foreach ($featureCollection as $feature) {
			if ($feature["FEATURE_ID"] == PropertyCatalogFeature::FEATURE_ID_OFFER_TREE_PROPERTY) {
				$treeFeature = $feature;
			}
			if ($feature["FEATURE_ID"] == PropertyCatalogFeature::FEATURE_ID_BASKET_PROPERTY) {
				$basketFeature = $feature;
			}
		}
		if (!$treeFeature) {
			$treeFeature = PropertyFeatureTable::createObject();
			$treeFeature["FEATURE_ID"] = PropertyCatalogFeature::FEATURE_ID_OFFER_TREE_PROPERTY;
			$treeFeature["PROPERTY_ID"] = $id;
			$treeFeature["MODULE_ID"] = "catalog";

			$featureCollection[] = $treeFeature;
		}
		if (!$basketFeature) {
			$basketFeature = PropertyFeatureTable::createObject();
			$basketFeature["FEATURE_ID"] = PropertyCatalogFeature::FEATURE_ID_BASKET_PROPERTY;
			$basketFeature["PROPERTY_ID"] = $id;
			$basketFeature["MODULE_ID"] = "catalog";

			$featureCollection[] = $basketFeature;
		}

		$treeFeature["IS_ENABLED"] = "Y";
		$basketFeature["IS_ENABLED"] = "Y";

		$featureCollection->save();

		// TODO: add/update Prop Id to cache
		return $id;
	}

	public static function copyEnumRows($oldPropId, $newPropId) {
		self::$propJoin = [];

		$enums = PropertyEnumerationTable::query()
				->setSelect(["*"])
				->where(
					\Bitrix\Main\ORM\Query\Query::filter()
						->logic("or")
						->where("PROPERTY_ID", $oldPropId)
						->where("PROPERTY_ID", $newPropId)
				)
				->exec()->fetchCollection();

		$enumCol = PropertyEnumerationTable::createCollection();

		$oldValues = PropertyEnumerationTable::createCollection();

		foreach ($enums as $enumRow) {
			if ($enumRow["PROPERTY_ID"] == $newPropId) {
				$enumRow->delete();
				$enumRow->save();
				continue;
			} else {
				$oldValues[] = $enumRow;
			}
		}
		foreach ($oldValues as $enumRow) {
			$enumCopy = PropertyEnumerationTable::createObject();
			$oldRowAsArray = $enumRow->collectValues(\Bitrix\Main\ORM\Objectify\Values::ACTUAL);
			foreach ($oldRowAsArray as $key => $value) {
				if ($key != "ID") {
					$enumCopy[$key] = $value;
				}
			}
			$enumCopy["PROPERTY_ID"] = $newPropId;
			// Если мы поменяем XML_ID, то будут проблемы со связью
			$enumCopy["XML_ID"] = $enumCopy["XML_ID"] . "_copy"; // PROP_ID.XML_ID is a key
			// unset($enumCopy["XML_ID"]);
			$enumCol[] = $enumCopy;
		}
		$res = $enumCol->save();
		if (!$res->isSuccess()) {
			$message = "Ошибка создания значений списка из $oldPropId для $newPropId \n";
			$message .= implode("\n", $res->getErrorMessages());
			throw new \Exception($message, 1);
		}
	}

	// bind that to delete of original prop
	public static function deleteProp($propId) {
		self::$propJoin = [];

		$prop = PropertyTable::getById($propId)->fetchRaw();

		if ($prop["PROPERTY_TYPE"] == PropertyTable::TYPE_LIST && $prop["LIST_TYPE"] == PropertyTable::LISTBOX) {
			self::deleteEnumRows($propId);
		}

		PropertyTable::delete($propId);
		// TODO: delete in cache
	}

	private static function deleteEnumRows($oldPropId) {
		$enums = PropertyEnumerationTable::query()
				->setSelect(["*"])
				->where("PROPERTY_ID", $oldPropId)
				->fetchCollection();

		foreach ($enums as $enum) {
			$enum->delete();
		}
	}

	public static function createEnumJoinArray() {
		// TODO: read from cache

		if (!empty(self::$propJoin)) {
			return self::$propJoin;
		}

		$props = PropertyTable::query()
			->setSelect(["ID"])
			->where("IBLOCK_ID", "in", [self::$CATALOG_IBLOCK_ID])
			->exec()->fetchAll();

		$oldPropsIds = array_column($props, "ID");
		$old2newIds = self::getSKUPropId($oldPropsIds);

		// print_r($old2newIds);

		$enums = PropertyEnumerationTable::query()
			->setSelect(["ID", "PROPERTY_ID", "XML_ID", "VALUE"])
			->where("PROPERTY_ID", "in", array_merge($old2newIds, array_keys($old2newIds)))
			->exec()->fetchAll();

		$groupedArray = [];
		foreach ($enums as $key => $value) {
			$groupBy = $value["PROPERTY_ID"];
			$groupedArray[$groupBy][$key] = $value;
		}
		$groupedArray;

		$result = [];
		foreach ($old2newIds as $oldPropId => $newPropId) {
			$oldEnums = $groupedArray[$oldPropId];
			$newEnums = $groupedArray[$newPropId];

			$newEnums = $newEnums ?? [];
			$oldEnums = $oldEnums ?? [];

			$xml2id = @array_combine(
				@array_column($newEnums, "XML_ID"),
				@array_column($newEnums, "ID"),
			);
			$value2id = @array_combine(
				@array_column($newEnums, "XML_ID"),
				@array_column($newEnums, "VALUE"),
			);

			// print_r($xml2id);

			foreach ($oldEnums as $oldEnum) {
				$newEnumId = 0;
				$newEnumId = $xml2id[$oldEnum["XML_ID"] . "_copy"];
				if (!($newEnumId > 0)) {
					$newEnumId = $value2id[$oldEnum["VALUE"]];
				}

				$result[$oldEnum["ID"]] = $newEnumId;
			}
		}

		// print_r($groupedArray);

		self::$propJoin = $result;

		return $result;
	}
}