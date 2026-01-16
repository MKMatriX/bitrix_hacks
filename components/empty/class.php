<?
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

class ComponentClassName extends CBitrixComponent implements Controllerable, Errorable {
	/** @var ErrorCollection */
	public $errorCollection;

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors() {
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error
	 */
	public function getErrorByCode($code) {
		return $this->errorCollection->getErrorByCode($code);
	}

	public function onPrepareComponentParams($arParams) {
		$this->errorCollection = new ErrorCollection();

		$arParams["CACHE_TIME"] = $arParams["CACHE_TIME"]? intval($arParams["CACHE_TIME"]) : 36000000;
		return $arParams;
	}


	public function executeComponent() {
		$this->arResult = [];

		if ($this->StartResultCache()){

			// if(!Loader::IncludeModule("iblock")){
			// 	$this->AbortResultCache();
			// 	ShowError("Модуль инфоблоков не установлен");
			// 	return;
			// }

			$this->IncludeComponentTemplate();
			// $this->EndResultCache();
		}
	}


	// /**
	//  * ajax/fetch configuration
	//  * @return array
	//  */
	// public function configureActions(): array {
	// 	return [
	// 		'ajaxMethodInThisClass' => [
	// 			'prefilters' => [
	// 				new Csrf(),
	// 				new Authentication(),
	// 			],
	// 		],
	// 	];
	// }

	// public function ajaxMethodInThisClassAction(int $id, bool $checked) {
	// }
}