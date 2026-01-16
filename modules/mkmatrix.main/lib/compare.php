<?php
namespace MKMatriX\Main;

/**
 * Class CompareTable
 *
 * Fields:
 * <ul>
 * <li> USER_ID int mandatory
 * <li> ITEMS array optional
 * </ul>
 *
 * @package MKMatriX\Main\CompareTable
 **/

class CompareTable extends UserItemsList {
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName() {
		return 'compare';
	}
}