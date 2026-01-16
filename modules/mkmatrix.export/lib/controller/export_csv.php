<?
namespace MKMatriX\Export\Controller;

use \Bitrix\Main\Error;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory;


class Export_Csv extends Export_spreadsheet {
	static function insertPrice(&$cellIterator, $item) {
		$price = (float) $item["PRICE"];
		$cellIterator->current()->setValueExplicit(number_format($price, 2, ",", " "));
	}

	static function output($spreadsheet) {
		// Redirect output to a client’s web browser (Xls)
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment;filename="export.csv"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		// header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$writer = IOFactory::createWriter($spreadsheet, 'Csv');
		$writer->setDelimiter(';');
		$writer->setEnclosure('"');
		$writer->setLineEnding("\r\n");
		$writer->setUseBOM(true);
		$writer->save('php://output');
		exit;
	}
}