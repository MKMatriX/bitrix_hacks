<?
namespace MKMatriX\Export\Controller;

use \Bitrix\Main\Error;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;



class Import_xls extends Import_spreadsheet {
	static function getReader () {
		return new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
	}
}