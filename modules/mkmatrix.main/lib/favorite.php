<?php
namespace MKMatriX\Main;


/**
 * Class FavoriteTable
 *
 * Fields:
 * <ul>
 * <li> USER_ID int mandatory
 * <li> ITEMS array optional
 * </ul>
 *
 * @package MKMatriX\Main\FavoriteTable
 **/

class FavoriteTable extends UserItemsList {
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName() {
		return 'favorite';
	}
}