<?
namespace MKMatriX\Export\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;



abstract class Import extends \Bitrix\Main\Engine\Controller {
	/**
	 * Returns default pre-filters for action.
	 * @return array
	 */
	protected function getDefaultPreFilters() {
		return [
			new \Bitrix\Main\Engine\ActionFilter\HttpMethod(
				[
					\Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_GET,
					\Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_POST
				]
			),
			new \Bitrix\Main\Engine\ActionFilter\Csrf(),
		];
	}

	static function getFile () {
		$file = $_FILES["file"];

		return $file;
	}

	function importAction () {
		throw new \Exception("Implement", 1);
	}
}