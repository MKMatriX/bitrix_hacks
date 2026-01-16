<?php
namespace MKMatriX\Main\Fixes;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

Loc::loadMessages(__FILE__);

/**
 * Class FieldEnumTable
 * Потому что битрикс этого не предоставил. Или предоставил, но валидатор был слишком избыточным.
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_FIELD_ID int optional
 * <li> VALUE string(255) mandatory
 * <li> DEF bool ('N', 'Y') optional default 'N'
 * <li> SORT int optional default 500
 * <li> XML_ID string(255) mandatory
 * </ul>
 *
 * @package Bitrix\User
 **/

class FieldEnumTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_user_field_enum';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('FIELD_ENUM_ENTITY_ID_FIELD')
				]
			),
			new Fields\IntegerField(
				'USER_FIELD_ID',
				[
					'title' => Loc::getMessage('FIELD_ENUM_ENTITY_USER_FIELD_ID_FIELD')
				]
			),
			new Fields\StringField(
				'VALUE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateValue'],
					'title' => Loc::getMessage('FIELD_ENUM_ENTITY_VALUE_FIELD')
				]
			),
			new Fields\BooleanField(
				'DEF',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
					'title' => Loc::getMessage('FIELD_ENUM_ENTITY_DEF_FIELD')
				]
			),
			new Fields\IntegerField(
				'SORT',
				[
					'default' => 500,
					'title' => Loc::getMessage('FIELD_ENUM_ENTITY_SORT_FIELD')
				]
			),
			new Fields\StringField(
				'XML_ID',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateXmlId'],
					'title' => Loc::getMessage('FIELD_ENUM_ENTITY_XML_ID_FIELD')
				]
			),
		];
	}

	/**
	 * Returns validators for VALUE field.
	 *
	 * @return array
	 */
	public static function validateValue()
	{
		return [
			new Fields\Validators\LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for XML_ID field.
	 *
	 * @return array
	 */
	public static function validateXmlId()
	{
		return [
			new Fields\Validators\LengthValidator(null, 255),
		];
	}
}