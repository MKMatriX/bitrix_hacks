<?php
namespace MKMatriX\Iblockwebp;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;

Loc::loadMessages(__FILE__);

/**
 * Class SizesTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * </ul>
 *
 * @package MKMatriX\Iblockwebp
 **/

class SizesTable extends DataManager
{
	static $sizes;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'sizes';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => "ID"
				]
			),
			new TextField(
				'CODE',
				[
					'title' => "Код"
				]
			),
			new TextField(
				'NAME',
				[
					'title' => "Название"
				]
			),
			new IntegerField(
				'UF_SORT',
				[
					'title' => "Сортировка"
				]
			),
			new IntegerField(
				'WIDTH',
				[
					'title' => "Ширина"
				]
			),
			new IntegerField(
				'HEIGHT',
				[
					'title' => "Высота"
				]
			),
			new IntegerField(
				'QUALITY',
				[
					'title' => "Качество",
					'default_value' => 95,
				]
			),
			new TextField(
				'FORMAT',
				[
					'title' => "Формат",
					'default_value' => "webp",
				]
			),
			new TextField(
				'UF_DESCRIPTION',
				[
					'title' => "Описание"
				]
			),
		];
	}

	private static function checkCacheSizes() {
		if (!isset(static::$sizes)) {
			$sizes = self::query()
				->setSelect(["*"])
				->exec()->fetchAll();

			$sizes = array_combine(
				array_column($sizes, "CODE"),
				$sizes
			);
			static::$sizes = $sizes;
		}
	}

	public static function getSize($code) {
		self::checkCacheSizes();

		if (!isset(static::$sizes[$code])) {
			return null;
		}

		return static::$sizes[$code];
	}

	public static function getAllSizes() {
		self::checkCacheSizes();
		return static::$sizes;
	}
}