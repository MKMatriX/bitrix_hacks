<?
namespace MKMatriX\Export\Controller;

use \Bitrix\Main\Error;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\IOFactory;


class Export_Xls extends Export_spreadsheet {
	static function save($spreadsheet) {
		$folder = $_SERVER["DOCUMENT_ROOT"] . "/upload/export/";
		$name = $folder . "export.xlsx";

		$writer = new Xlsx($spreadsheet);
		$writer->save($name);
	}

	static function output($spreadsheet) {
		// Redirect output to a client’s web browser (Xls)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="export.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		// header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$GLOBALS['APPLICATION']->RestartBuffer();

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');

		exit;
	}

	static function headersNames () {
		$default = parent::headersNames();
		$default[] = "Сумма";

		return $default;
	}

	static function insertPrice(&$cellIterator, $item) {
		$cellIterator->current()->setValueExplicit($item["PRICE"], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
		$cellIterator->current()->getStyle()->getNumberFormat()->setFormatCode("#,##0.00");
		$cellIterator->next();
	}

	static function insertItem($activeWorksheet, &$cellIterator, $item) {
		parent::insertItem($activeWorksheet, $cellIterator, $item);

		// T_T
		$cellIterator->prev();
		$cellIterator->prev();
		$quantityCoord = $cellIterator->current()->getCoordinate();
		$cellIterator->next();
		$priceCoord = $cellIterator->current()->getCoordinate();
		$cellIterator->next();

		$activeWorksheet->setCellValue($cellIterator->current()->getCoordinate(), "=$quantityCoord * $priceCoord");
		$cellIterator->next();
	}

}