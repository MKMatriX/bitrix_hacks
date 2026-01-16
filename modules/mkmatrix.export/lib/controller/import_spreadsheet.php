<?
namespace MKMatriX\Export\Controller;

use \Bitrix\Main\Error;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;



class Import_spreadsheet extends Import {
	function importAction () {
		$file = static::getFile();
		$reader = static::getReader();

		$spreadsheet = $reader->load($file['tmp_name']);
		return $this->getArticle2Amount($spreadsheet);
	}

	static function getReader () {
		throw new \Exception("Implement", 1);
	}

	function getArticle2Amount($spreadsheet) {
		$spreadsheet = $spreadsheet->getActiveSheet();
		$data =  $spreadsheet->toArray();

		$data = static::removeHeader($data);

		$data = array_map([$this, 'processLine'], $data);
		$data = array_filter($data);

		$data = array_column($data, "AMOUNT", "ARTICLE");

		return $data;
	}

	static function removeHeader($data) {
		$expectedHeader = Export_spreadsheet::headersNames();
		$headerRow = $data[0];

		$errors = 0;
		foreach ($expectedHeader as $i => $name) {
			if ($headerRow[$i] !== $name) {
				$errors++;
			}
		}

		// все с ошибками, вероятно заголовка нету
		if ($errors == count($expectedHeader)) {
			return $data;
		}

		if ($errors > 0) {
			throw new \Exception("Первая строка не соответствует ожидаемому заголовку", 1);
		}
		unset($data[0]);
		return array_values($data);
	}

	function processLine($line) {
		$article = self::getLineArticle($line);
		$amount = self::getLineAmount($line);
		$name = self::getLineName($line);

		if (! ($amount > 0)) {
			return false;
		}

		if (!mb_strlen($article)) {
			$this->addError(new \Bitrix\Main\Error("Отсутствует артикул у товара {$name}, заказ этого товара недоступен"));
			return false;
		}

		return [
			"ARTICLE" => $article,
			"NAME" => $name,
			"AMOUNT" => $amount,
		];
	}
	static function getLineArticle($line) {
		return trim($line[0]);
	}

	static function getLineName($line) {
		return trim($line[1]);
	}

	static function getLineAmount($line) {
		return $line[2];
	}

}