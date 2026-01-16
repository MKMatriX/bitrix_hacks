<?
namespace MKMatriX\Export\Controller;

use \Bitrix\Main\Error;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;


abstract class Export_spreadsheet extends Export {
	public static function sectionAction($sectionId, string $searchQuery = "", string $filterPath = "", string $sort = "") {
		$items = parent::sectionAction($sectionId, $searchQuery, $filterPath, $sort);
		$spreadsheet = static::array2spreadsheet($items);

		// static::save($spreadsheet);
		static::output($spreadsheet);

		return $items;
	}

	public static function favoriteAction(string $sort = "") {
		$items = parent::favoriteAction($sort);
		$spreadsheet = static::array2spreadsheet($items);

		// static::save($spreadsheet);
		static::output($spreadsheet);

		return $items;
	}

	public static function basketAction() {
		$items = parent::basketAction();
		$spreadsheet = static::array2spreadsheet($items);

		// static::save($spreadsheet);
		static::output($spreadsheet);

		return $items;
	}

	public static function idsAction($products) {
		$items = parent::idsAction($products);
		$spreadsheet = static::array2spreadsheet($items);

		// static::save($spreadsheet);
		static::output($spreadsheet);

		return $items;
	}


	static function array2spreadsheet ($items = []) {
		$spreadsheet = new Spreadsheet();
		$activeWorksheet = $spreadsheet->getActiveSheet();

		static::setProperties($spreadsheet);

		static::insertHeader($activeWorksheet);
		static::insertItems($activeWorksheet, $items);

		$spreadsheet->setActiveSheetIndex(0);

		return $spreadsheet;
	}

	static function headersNames () {
		return [
			"Артикул",
			"Наименование",
			"Количество",
			"Цена"
		];
	}

	static function insertHeader($activeWorksheet) {
		$rowIterator =  $activeWorksheet->getRowIterator();
		$firstRow = $rowIterator->current();

		$cellIterator = $firstRow->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set

		foreach (static::headersNames() as $key => $row) {
			$cell = $cellIterator->current();
			$activeWorksheet->setCellValue($cell->getCoordinate(), $row);
			$cellIterator->next();
		}
	}

	static function insertItems($activeWorksheet, $items) {
		$rowIterator =  $activeWorksheet->getRowIterator();

		foreach ($items as $key => $item) {
			$rowIterator->next();
			$cellIterator = $rowIterator->current()->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set

			static::insertItem($activeWorksheet, $cellIterator, $item);
		}

		$activeWorksheet->getColumnDimension('A')->setAutoSize(true);
		$activeWorksheet->getColumnDimension('B')->setAutoSize(true);
		$activeWorksheet->getColumnDimension('D')->setAutoSize(true);
		$activeWorksheet->getColumnDimension('E')->setAutoSize(true);
		$activeWorksheet->getColumnDimension('F')->setAutoSize(true);
	}

	static function insertItem($activeWorksheet, &$cellIterator, $item) {
		$cellIterator->current()->setValueExplicit($item["_ARTICLE"], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		$cellIterator->next();
		$activeWorksheet->setCellValue($cellIterator->current()->getCoordinate(), $item["NAME"]);
		$cellIterator->next();

		$activeWorksheet->setCellValue($cellIterator->current()->getCoordinate(), $item["QUANTITY"] ?? 0);
		$cellIterator->next();

		static::insertPrice($cellIterator, $item);
	}

	static function insertPrice(&$cellIterator, $item) {
		$cellIterator->current()->setValueExplicit($item["PRICE"]);
	}

	static function setProperties($spreadsheet) {
		$spreadsheet->getProperties()
			->setCreator('mkmatrix')
			->setLastModifiedBy('MKMatriX')
			->setTitle('Экспорт из магазина Рога и Копыта')
			->setSubject('Товары для закупки')
			->setDescription('Экспорт товаров для закупки')
			->setKeywords('Купить товары');
	}

	static function save($spreadsheet) {
		throw new \Exception("Implement", 1);
	}

	static function output($spreadsheet) {
		throw new \Exception("Implement", 1);
	}
}