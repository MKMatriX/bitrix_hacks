<?php
namespace MKMatriX\Main;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;


/**
 * Class SectionExtTable
 * В какой-то момент мне нужно было скрывать разделы без активных элементов.
 * В общем табличка, куда я закинул место где это можно было хранить.
 * Сама по себе задачка не особо нужная, а вот идея расширять стандартные таблицы с примером)
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> HAVE_ACTIVE_ELEMENTS bool optional
 * <li> CHECKED_ACTIVE_ELEMENTS datetime optional
 * </ul>
 *
 * @package MKMatriX\Main
 **/

class SectionExtTable extends DataManager
{
	const ACTIVE_ELEMENTS_VALID_TIME = "1 day"; // сколько времени работает токен

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'section_ext';
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
					'title' => "Ид раздела"
				]
			),
			new Fields\BooleanField(
				'HAVE_ACTIVE_ELEMENTS',
				[
					'title' => "Есть ли активные элементы"
				]
			),
			new Fields\DatetimeField(
				'CHECKED_ACTIVE_ELEMENTS',
				[
					'title' => "Дата проверки на активные элементы"
				]
			),
			(new Reference(
				'SECTIONS',
				\Bitrix\Iblock\SectionTable::class,
				Join::on('this.ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
		];
	}


	public static function checkActive(int $sectionsId) {
		$raw = self::query()
			->setSelect([
				"*",
			])
			->where("ID", $sectionsId)
			->where("CHECKED_ACTIVE_ELEMENTS", ">", (new \Bitrix\Main\Type\DateTime())->add("-" . self::ACTIVE_ELEMENTS_VALID_TIME))
			->setLimit(1)->fetchObject();

		if ($raw["ID"] > 0) {
			return !!$raw["HAVE_ACTIVE_ELEMENTS"];
		}

		return null;
	}

	public static function saveActive(int $sectionsId, bool $active) {
		$obj = self::query()
			->setSelect([
				"*",
			])
			->where("ID", $sectionsId)
			->setLimit(1)->fetchObject();

		if (is_null($obj)) {
			$obj = self::createObject();
			$obj["ID"] = $sectionsId;
		}

		$obj["CHECKED_ACTIVE_ELEMENTS"] = new \Bitrix\Main\Type\DateTime();
		$obj["HAVE_ACTIVE_ELEMENTS"] = $active;
		$obj->save();
	}
}