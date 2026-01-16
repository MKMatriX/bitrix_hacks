<?php
namespace MKMatriX\SKU\Override;

use \Bitrix\Main\Entity;

/**
 * Class PropertyEnumerationTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROPERTY_ID int mandatory
 * <li> VALUE string(255) mandatory
 * <li> DEF bool optional default 'N'
 * <li> SORT int optional default 500
 * <li> XML_ID string(200) mandatory
 * <li> TMP_ID string(40) optional
 * <li> PROPERTY reference to {@link \Bitrix\Iblock\PropertyTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PropertyEnumeration_Query query()
 * @method static EO_PropertyEnumeration_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_PropertyEnumeration_Result getById($id)
 * @method static EO_PropertyEnumeration_Result getList(array $parameters = array())
 * @method static EO_PropertyEnumeration_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_PropertyEnumeration createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_PropertyEnumeration_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_PropertyEnumeration wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_PropertyEnumeration_Collection wakeUpCollection($rows)
 */

class PropertyEnumerationTable extends Entity\DataManager {
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName() {
		return 'b_iblock_property_enum';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap() {
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				// 'title' => Loc::getMessage('IBLOCK_PROPERTY_ENUM_ENTITY_ID_FIELD'),
			],
			'PROPERTY_ID' => [
				'data_type' => 'integer',
				'primary' => true,
				// 'title' => Loc::getMessage('IBLOCK_PROPERTY_ENUM_ENTITY_PROPERTY_ID_FIELD'),
			],
			'VALUE' => [
				'data_type' => 'string',
				'required' => true,
				'validation' => [__CLASS__, 'validateValue'],
				// 'title' => Loc::getMessage('IBLOCK_PROPERTY_ENUM_ENTITY_VALUE_FIELD'),
			],
			'DEF' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
				// 'title' => Loc::getMessage('IBLOCK_PROPERTY_ENUM_ENTITY_DEF_FIELD'),
			],
			'SORT' => [
				'data_type' => 'integer',
				// 'title' => Loc::getMessage('IBLOCK_PROPERTY_ENUM_ENTITY_SORT_FIELD'),
			],
			'XML_ID' => [
				'data_type' => 'string',
				'validation' => [__CLASS__, 'validateXmlId'],
				// 'title' => Loc::getMessage('IBLOCK_PROPERTY_ENUM_ENTITY_XML_ID_FIELD'),
			],
			'TMP_ID' => [
				'data_type' => 'string',
				'validation' => [__CLASS__, 'validateTmpId'],
				// 'title' => Loc::getMessage('IBLOCK_PROPERTY_ENUM_ENTITY_TMP_ID_FIELD'),
			],
			'PROPERTY' => [
				'data_type' => 'Bitrix\Iblock\Property',
				'reference' => ['=this.PROPERTY_ID' => 'ref.ID'],
			],
		];
	}

	/**
	 * Returns validators for VALUE field.
	 *
	 * @return array
	 */
	public static function validateValue() {
		return [
			new Entity\Validator\Length(null, 255),
		];
	}

	/**
	 * Returns validators for XML_ID field.
	 *
	 * @return array
	 */
	public static function validateXmlId() {
		return [
			// new Entity\Validator\Unique(),
			new Entity\Validator\Length(null, 200),
		];
	}

	/**
	 * Returns validators for TMP_ID field.
	 *
	 * @return array
	 */
	public static function validateTmpId() {
		return [
			new Entity\Validator\Length(null, 40),
		];
	}
}
