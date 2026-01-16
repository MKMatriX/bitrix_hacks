<?
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Security\Mfa;


/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

class MatriXCallback extends CBitrixComponent implements Controllerable, Errorable {
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

		//int
		$arParams["YA_COUNTER"] = intval($arParams["YA_COUNTER"]);
		$arParams["YA_COUNTER"] = $arParams["YA_COUNTER"] ? $arParams["YA_COUNTER"] : YA_COUNTER_ID;

		//string
		$arParams["NAME"] = trim($arParams["NAME"]);
		$arParams["NAME"] = mb_strlen($arParams["NAME"]) ? $arParams["NAME"] : "callback";

		$arParams["THEME"] = trim($arParams["THEME"]);
		$arParams["THEME"] = mb_strlen($arParams["THEME"]) ? $arParams["THEME"] : "сообщение из формы обратной связи";

		$arParams["TEMPLATE_NAME"] = trim($arParams["TEMPLATE_NAME"]);
		$arParams["TEMPLATE_NAME"] = mb_strlen($arParams["TEMPLATE_NAME"]) ? $arParams["TEMPLATE_NAME"] : "callback";

		//array
		$arParams["FIELDS"] = (is_array($arParams["FIELDS"]) && !empty($arParams["FIELDS"])) ? $arParams["FIELDS"] : [];

		$arParams["CACHE_TIME"] = $arParams["CACHE_TIME"]? intval($arParams["CACHE_TIME"]) : 36000000;
		return $arParams;
	}


	public function executeComponent() {
		CJSCore::Init([
			"mk_ajax_forms",
			"mk_callback",
		]);

		foreach ($this->arParams["FIELDS"] as $key => $arField) {
			if ($arField["TYPE"] == "FILE") {
				$this->initComponentTemplate();
				$fuPath = $this->GetPath() . '/fileupload/';
				$this->GetTemplate()->addExternalJs($fuPath . 'vendor/jquery.ui.widget.js');
				$this->GetTemplate()->addExternalJs($fuPath . 'jquery.iframe-transport.js');
				$this->GetTemplate()->addExternalJs($fuPath . 'jquery.fileupload.js');
				unset($fuPath);
			}

			if ($arField["TYPE"] == "IBLOCK_ELEMENT") {
				CJSCore::Init([
					"mk_global_events",
				]);
			}
		}

		if ($this->StartResultCache()) {
			$this->createEmailTemplate();
			$this->createBXForm();

			include 'custom_fields.php';

			if (!empty($this->errorCollection->getValues())) {
				global $USER;
				if ($USER->IsAdmin()) {
					?>
					<script>
					console.log('Ошибки в компоненте обратной связи')
					console.error(<?=json_encode($this->errorCollection->getValues(), JSON_PARTIAL_OUTPUT_ON_ERROR)?>);
					</script>
					<?

				}
			}

			$this->IncludeComponentTemplate();
		}
	}

	public function getSid() {
		$sid = str_replace(['.', ' '], '_',  $this->arParams["TEMPLATE_NAME"]);
		return $sid;
	}

	public function createBXForm() {
		if (!CModule::IncludeModule("form")) {
			$this->errorCollection[] = new Error("Не установлен модуль форм");
			return;
		}

		// сформируем массив фильтра
		$arFilter = [
			"SID" => $this->getSid(),
			// "NAME" => $this->arParams["THEME"],
			// "NAME_EXACT_MATCH" => "Y",
			"SITE" => SITE_ID,
		];

		// получим список всех форм, для которых у текущего пользователя есть право на заполнение
		$rsForms = CForm::GetList(
			$by="s_sort",
			$order="asc",
			$arFilter
		);
		$this->formId = 0;
		if ($arForm = $rsForms->Fetch()) {
			$this->formId = $arForm["ID"];
		} else {
			$this->formId = CForm::Set([
				"SID" =>$this->getSid(),
				"NAME" => $this->arParams["THEME"],
				"arSITE" => [SITE_ID],
				"USE_CAPTCHA" => "N",
			]);
			if (!($this->formId > 0)) {
				global $strError;
				$this->errorCollection[] = new Error($strError);
			} else {
				$newId = CFormStatus::Set([
					"FORM_ID" => $this->formId, // ID веб-формы
					"C_SORT" => 100, // порядок сортировки
					"ACTIVE" => "Y", // статус активен
					"TITLE" => "Опубликовано", // заголовок статуса
					"DESCRIPTION" => "Окончательный статус", // описание статуса
					"CSS" => "statusgreen", // CSS класс
					"HANDLER_OUT" => "", // обработчик
					"HANDLER_IN" => "", // обработчик
					"DEFAULT_VALUE" => "Y", // по умолчанию
					"arPERMISSION_VIEW" => [2], // право просмотра для всех
					"arPERMISSION_MOVE" => [], // право перевода только админам
					"arPERMISSION_EDIT" => [], // право редактирование для админам
					"arPERMISSION_DELETE" => [], // право удаления только админам
				]);
				if (!($newId > 0)) {
					global $strError;
					$this->errorCollection[] = new Error($strError);
				}

			}
		}

		$this->createBXFormFields();
	}

	public function createBXFormFields() {
		if (!($this->formId > 0)) {
			$this->ErrorCollection[] = new Error("Не могу создать вопросы без формы");
		}

		$this->formQuestions = [];

		$rsQuestions = CFormField::GetList(
			$this->formId,
			"ALL"
		);
		while ($arQuestion = $rsQuestions->Fetch()) {
			$this->formQuestions[] = $arQuestion;
		}

		$dbSids = array_column($this->formQuestions, "SID");

		$questionChanged = false;
		foreach ($this->arParams["FIELDS"] as $arField) {
			if (in_array($arField["CODE"], $dbSids)) {
				continue;
			}

			$questionChanged = true;

			$newId = CFormField::Set([
				"FORM_ID" => $this->formId,
				"ACTIVE" => "Y",
				"TITLE" => $arField["RU_NAME"],
				"SID" => $arField["CODE"],
				"FIELD_TYPE" => "text",
			]);

			if (!($newId > 0)) {
				global $strError;
				$this->errorCollection[] = new Error("Создание вопроса " . $strError);
			} else {
				$newId = CFormAnswer::Set([
					"QUESTION_ID" => $newId,
					"MESSAGE" => "Ответ из формы обратной связи",
				]);
			}
		}

		if ($questionChanged) {
			$this->formQuestions = [];

			$rsQuestions = CFormField::GetList(
				$this->formId,
				"ALL"
			);
			while ($arQuestion = $rsQuestions->Fetch()) {
				$this->formQuestions[] = $arQuestion;
			}
		}



	}

	public function getAnswers():array {
		if (is_array($this->formAnswers)) {
			return $this->formAnswers;
		}

		$this->formAnswers = [];

		foreach ($this->formQuestions as $question) {
			$rsAnswers = CFormAnswer::GetList(
				$question["ID"]
			);
			while ($arAnswer = $rsAnswers->Fetch()) {
				$this->formAnswers[] = $arAnswer;
			}
		}

		return $this->formAnswers;
	}

	public function getAnswer($qid): array {
		foreach ($this->getAnswers() as $answer) {
			if ($answer["QUESTION_ID"] == $qid) {
				return $answer;
			}
		}
		return [];
	}

	public function getFormQuestion($code): array {
		foreach ($this->formQuestions as $question) {
			if ($question["SID"] == $code) {
				$question["ANSWER"] = $this->getAnswer($question["ID"]);
				return $question;
			}
		}
		return [];
	}

	public function createEmailTemplate() {
		$rsEventType = CEventType::GetList([
			"TYPE_ID" => $this->arParams["TEMPLATE_NAME"],
			"LID" => "ru"
		]);
		if (!($arET = $rsEventType->Fetch())) {
			$et = new CEventType;
			$et->Add([
				"LID" => "ru",
				// "ACTIVE" => "Y",
				"EVENT_NAME" => $this->arParams["TEMPLATE_NAME"],
				"NAME" => $this->arParams["THEME"],
				"DESCRIPTION" => "Автоматически созданное событие"
			]);
		}

		$rsMess = CEventMessage::GetList(
			$by="site_id",
			$order="desc",
			["TYPE_ID" => $this->arParams["TEMPLATE_NAME"]]
		);

		if (!($arMess = $rsMess->GetNext())) {
			$emess = new CEventMessage;
			$emess->Add([
				"ACTIVE" => "Y",
				"EVENT_NAME" => $this->arParams["TEMPLATE_NAME"],
				"LID" => SITE_ID,
				"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
				"EMAIL_TO" => "#DEFAULT_EMAIL_FROM#",
				"SUBJECT" => "#SITE_NAME#: #THEME#",
				"BODY_TYPE" => "html",
				"MESSAGE" => "#MESSAGE#",
			]);
		}
	}

	/**
	 * List of keys of parameters which the component have to sign,
	 *
	 * @return null|array
	 */
	protected function listKeysSignedParameters()
	{
		return [
			"FIELDS",
			"THEME",
			"TEMPLATE_NAME",
		];
	}

	/**
	 * ajax/fetch configurantion
	 * @return array
	 */
	public function configureActions(): array {
		return [
			'callback' => [
				'prefilters' => [
					new Csrf(),
				],
			],
		];
	}

	public function callbackAction(array $fields, string $policy = "0") {
		$result = [];

		// $files = $this->request->getFileList()->getValues()['fields'];

		if (!$policy) {
			$this->errorCollection[] = new Error("Не можем обработать запрос без согласия на обработку персональных данных");
			return [];
		}

		$this->createBXForm();
		$this->getAnswers();
		$formResult = [];

		if (empty($this->errorCollection->getValues())) {
			$message = "";
			foreach ($this->arParams["FIELDS"] as $key => $arField) {
				$value = $fields[$arField["CODE"]];

				$question = $this->getFormQuestion($arField["CODE"]);
				$formResult["form_text_" . $question["ANSWER"]["ID"]] = $value;

				switch ($arField["TYPE"]) {
					case 'IBLOCK_ELEMENT':
						if ($arField["IS_BASKET"]) {
							if (strpos($value, "{") === 0) {
								$basket = json_decode($value, true);

								// quantity = $basket["quantity"];
								$quantity = [];
								foreach ($basket["quantity"] as $key => $value) {
									$quantity[$key] = floatval($value);
								}

								$value = $basket["ids"];
							}
						}

						// checking that we have elements to search
						if (!($value > 0)) {
							if (is_array($value) && count($value)) {
								foreach ($value as $id) {
									if (!($id > 0)) {
										break;
									}
								}
							} else {
								break;
							}
						}

						if(!CModule::IncludeModule("iblock")) {
							$this->errorCollection[] = new Error("Модуль Инфоблоков не установлен!");
						} else {
							$rsElements = CIBlockElement::GetList(
								Array("SORT"=>"ASC"), // order
								Array(  // filter
									"ACTIVE" => "Y",
									"ID" => $value,
									"IBLOCK_ID" => $arField["IBLOCK_ID"],
								),
								false, // group
								false, // array("nTopCount" => 1), // pagination
								Array( // select
									"ID",
									"IBLOCK_ID",
									"NAME",
									"DETAIL_PAGE_URL",
									"CODE"
								)
							);

							$message .= $arField["RU_NAME"] . ": <br/>";
							while ($arElement = $rsElements->GetNext()) {
								$message .= $arElement["ID"] . ") ";
								$message .= '<a href="//'. SITE_SERVER_NAME. $arElement["DETAIL_PAGE_URL"].'">';
								$message .=  $arElement["NAME"];
								$message .= '</a>';

								if ($arField["IS_BASKET"]) {
									// die($quantity[array_keys($quantity)[0]]);
									$message .= ($quantity[$arElement["ID"]] > 0)? (" в количестве: " . $quantity[$arElement["ID"]] .' <br/>')  : "";
								}
								$message .= '<br/>';
							}

							if ($arField["IS_BASKET"]) {
								if(!CModule::IncludeModule("sale")) {
									$this->errorCollection[] = new Error("Модуль sale не установлен!");
								} else {
									CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());
								}
							}
						}
						break;
					case 'FILE':
						// $value = $files[];
						define('FILES_DIR', $_SERVER["DOCUMENT_ROOT"]."/upload/fileupload/");
						if (strlen($value) && file_exists(FILES_DIR . $value)) {
							$link = '//' . SITE_SERVER_NAME . "/upload/fileupload/" . $value;
							$value = '<a href="'.$link.'">'.$value.'</a>';
							unset($link);
							$message .= $arField["RU_NAME"] . ": " . $value . "<br/>";
						}
						break;
					default:
						if (!strlen($value) && isset($arField["DEFAULT"])) {
							$value = $arField["DEFAULT"];
						}
						if ($arField["REQUIRED"] == "Y" && !strlen($value)) {
							$this->errorCollection[] = new Error("Поле \"{$arField["RU_NAME"]}\" обязательно к заполнению");
						}
						$message .= $arField["RU_NAME"] . ": " . $value . "<br/>";

						if ($arField["ADDITIONAL"]["IS_SUBSCRIBE"]) {
							try {
								if (!check_email($value)) {
									throw new Exception("Не является имейлом", 1);
								}

								$subsсriber = MKMatriX\Main\SubscribersTable::query()
									->setSelect(["ID"])
									->where("UF_EMAIL", $value)
									->setLimit(1)
									->exec()->fetchRaw();


								if ($subsсriber["ID"] > 0) {
									$this->errorCollection[] = new Error("Вы уже подписаны");
								} else {
									$subsсriber = MKMatriX\Main\SubscribersTable::createObject();
									$subsсriber["UF_EMAIL"] = $value;
									$subsсriber->save();
								}
							} catch (\Throwable $th) {
								$this->errorCollection[] = new Error($th->getMessage());
							}
						}
						break;
				}
			}

			global $USER;
			if ($USER->IsAdmin()) {
				$result["MESSAGE"] = $message;
				$result["PARAMS"] = $this->arParams;
				$result["FORM"] = $formResult;
			}

			if (empty($this->errorCollection->getValues())) {
				CEvent::Send(
					$this->arParams["TEMPLATE_NAME"],
					SITE_ID,
					[
						"THEME" => $this->arParams["THEME"],
						"MESSAGE" => $message,
					]
				);
			}
		}

		if (empty($this->errorCollection->getValues())) {
			$newId = CFormResult::Add($this->formId, $formResult, "N");
			if (!($newId > 0)) {
				global $strError;
				$this->errorCollection[] = new Error($strError);
			} else {
				CFormCRM::onResultAdded($this->formId, $newId);
				CFormResult::SetEvent($newId);
				CFormResult::Mail($newId);
			}
		}


		return $result;
	}

}