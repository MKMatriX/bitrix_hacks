<?
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Security\Mfa;
use MKMatriX\Litebuy\AuthNumbersTable;
use MKMatriX\Main\Utils;

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

class MatriXProfileEnter extends CBitrixComponent implements Controllerable, Errorable {
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
		global $USER;

		if ($USER->IsAuthorized()) {
			return;
		}

		CJSCore::Init([
			"mk_global_events",
			"mk_ajax_forms"
		]);


		$this->arResult["OPEN_AUTH_MODAL"] = $this->request->get("AUTH") == "Y";
		$this->arResult["SHOW_CHANGE_PASSWORD"] = $this->request->get("change_password") == "yes";

		if ($this->arResult["SHOW_CHANGE_PASSWORD"] || $this->StartResultCache()) {
			$this->addAuthResult();
			$this->addRegisterResult();
			$this->IncludeComponentTemplate();
		}
	}

	/**
	 * По идее повторяет bitrix:system.auth.form
	 */
	public function addAuthResult() {
		global $USER, $APPLICATION;

		$arResult = &$this->arResult;

		$arParamsToDelete = array(
			"login",
			"login_form",
			"logout",
			"register",
			"forgot_password",
			"change_password",
			"confirm_registration",
			"confirm_code",
			"confirm_user_id",
			"logout_butt",
			"auth_service_id",
		);

		$currentUrl = $APPLICATION->GetCurPageParam("", $arParamsToDelete);

		$arResult["BACKURL"] = $currentUrl;

		$arResult['ERROR'] = false;
		$arResult['SHOW_ERRORS'] = "N";
		$arResult["RND"] = $this->randString();


		$arResult["STORE_PASSWORD"] = COption::GetOptionString("main", "store_password", "Y") == "Y" ? "Y" : "N";
		$arResult["NEW_USER_REGISTRATION"] = COption::GetOptionString("main", "new_user_registration", "N") == "Y" ? "Y" : "N";

		$arRes = array();
		foreach($arResult as $key=>$value)
		{
			$arRes[$key] = htmlspecialcharsbx($value);
			$arRes['~'.$key] = $value;
		}
		$arResult = $arRes;

		if(CModule::IncludeModule("security") && Mfa\Otp::isOtpRequired()) {
			$arResult["FORM_TYPE"] = "otp";

			$arResult["REMEMBER_OTP"] = (COption::GetOptionString('security', 'otp_allow_remember') === 'Y');

			$arResult["CAPTCHA_CODE"] = false;
			if(Mfa\Otp::isCaptchaRequired()) {
				$arResult["CAPTCHA_CODE"] = $APPLICATION->CaptchaGetCode();
			}
			if(Mfa\Otp::isOtpRequiredByMandatory()) {
				$this->errorCollection[] = new Error(
					"Вход без одноразового пароля невозможен. Обратитесь к администратору для подключения одноразовых паролей."
				);
			}
			return ;
		}

		$arResult["FORM_TYPE"] = "login";

		$loginCookieName = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN";
		$arResult["~LOGIN_COOKIE_NAME"] = $loginCookieName;
		$arResult["~USER_LOGIN"] = $_COOKIE[$loginCookieName];
		$arResult["USER_LOGIN"] = $arResult["LAST_LOGIN"] = htmlspecialcharsbx($arResult["~USER_LOGIN"]);
		$arResult["~LAST_LOGIN"] = $arResult["~USER_LOGIN"];

		$arResult["AUTH_SERVICES"] = false;
		$arResult["CURRENT_SERVICE"] = false;
		if(!$USER->IsAuthorized() && CModule::IncludeModule("socialservices")) {
			$oAuthManager = new CSocServAuthManager();
			$arServices = $oAuthManager->GetActiveAuthServices($arResult);

			if(!empty($arServices)) {
				$arResult["AUTH_SERVICES"] = $arServices;
				if(isset($_REQUEST["auth_service_id"]) && $_REQUEST["auth_service_id"] <> '' && isset($arResult["AUTH_SERVICES"][$_REQUEST["auth_service_id"]]))
				{
					$arResult["CURRENT_SERVICE"] = $_REQUEST["auth_service_id"];
					if(isset($_REQUEST["auth_service_error"]) && $_REQUEST["auth_service_error"] <> '')
					{
						$arResult['ERROR_MESSAGE'] = $oAuthManager->GetError($arResult["CURRENT_SERVICE"], $_REQUEST["auth_service_error"]);
					}
					elseif(!$oAuthManager->Authorize($_REQUEST["auth_service_id"]))
					{
						$ex = $APPLICATION->GetException();
						if ($ex)
							$arResult['ERROR_MESSAGE'] = $ex->GetString();
					}
				}
			}
		}

		$arResult["SECURE_AUTH"] = false;
		if(!CMain::IsHTTPS() && COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y') {
			$sec = new CRsaSecurity();
			if(($arKeys = $sec->LoadKeys())) {
				$sec->SetKeys($arKeys);
				$sec->AddToForm('system_auth_form'.$arResult["RND"], array('USER_PASSWORD'));
				$arResult["SECURE_AUTH"] = true;
			}
		}

		if($APPLICATION->NeedCAPTHAForLogin($arResult["USER_LOGIN"])) {
			$arResult["CAPTCHA_CODE"] = $APPLICATION->CaptchaGetCode();
		} else {
			$arResult["CAPTCHA_CODE"] = false;
		}

		if(isset($APPLICATION->arAuthResult) && $APPLICATION->arAuthResult !== true) {
			$this->errorCollection[] = new Error($APPLICATION->arAuthResult);
		}
	}

	public function addRegisterResult() {
		global $USER, $APPLICATION;
		$arResult = &$this->arResult;

		$arResult["USE_CAPTCHA_REGISTER"] = COption::GetOptionString("main", "captcha_registration", "N") == "Y" ? "Y" : "N";

		if ($arResult["USE_CAPTCHA_REGISTER"] == "Y") {
			$arResult["CAPTCHA_CODE_REGISTER"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
		}
	}


	/**
	 * ajax/fetch configurantion
	 * @return array
	 */
	public function configureActions(): array {
		return [
			'authorize' => [
				'prefilters' => [
					new Csrf(),
				],
			],
			// 'authorizeBySms' => [
			// 	'prefilters' => [
			// 		new Csrf(),
			// 	],
			// ],
			// 'checkSmsPass' => [
			// 	'prefilters' => [
			// 		new Csrf(),
			// 	],
			// ],
			'restore' => [
				'prefilters' => [
					new Csrf(),
				],
			],
			'register' => [
				'prefilters' => [
					new Csrf(),
				],
			],
			'changePassword' => [
				'prefilters' => [
					new Csrf(),
				],
			],
		];
	}

	public function authorizeAction(string $login, string $password, string $remember = "on") {
		global $USER;

		if ($USER->IsAuthorized()) {
			$this->errorCollection[] = new Error("Вы уже авторизованы, сначала выйдете из системы");
			return;
		}
		$remember = $remember == "on";

		$message = $USER->Login(
			$login,
			$password,
			// $remember ? "Y" : "N"
			"Y"
		);

		if ($message !== true) {
			if (is_array($message) && is_string($message["MESSAGE"])) {
				$this->errorCollection[] = new Error(str_replace("<br>", "\n", $message["MESSAGE"]));
			} else {
				$this->errorCollection[] = new Error($message);
			}
		}
		return $message;
	}

	public function restoreAction (string $email, string $login = "") {
		$message = CUser::SendPassword($login, $email);

		if ($message["TYPE"] == "ERROR") {
			if ($message["MESSAGE"] == "Профиль пользователя не найден.<br>") {
				$this->errorCollection[] = new Error("Указанный электронный адрес не зарегистрирован");
			} else {
				$this->errorCollection[] = new Error(str_replace("<br>", "\n", $message["MESSAGE"]));
			}
		} else {
			return "На указанный электронный адрес отправлена инструкция по смене пароля";
		}
	}

	public function registerAction (
		string $name,
		string $phone,
		string $email,
		string $password,
		string $confirm_password,
		string $ch,
		string $captchaWord = "",
		string $captchaSid = "",
		array $profile = []
	) {
		if ($ch != "on") {
			$this->errorCollection[] = new Error("Мы не можем зарегистрировать вас без сохранения ваших персональных данных");
			return;
		}

		$message = $GLOBALS["USER"]->Register(
			$email, // USER_LOGIN
			$name,
			"", // LAST_NAME
			$password,
			$confirm_password,
			$email,
			false, // SITE_ID
			$captchaWord,
			$captchaSid,
			false // skip confirm
			// $phone
		);

		if ($message["TYPE"] == "ERROR") {
			$this->errorCollection[] = new Error(str_replace("<br>", "\n", $message["MESSAGE"]));
			return;
		}

		if (!($message["ID"] > 0)) {
			$this->errorCollection[] = new Error("Неизвестная ошибка");
			return;
		}

		$user = new CUser();

		$res = $user->update($message["ID"], [
			"PERSONAL_PHONE" => $phone
		]);

		if (!$res) {
			$this->errorCollection[] = new Error($user->LAST_ERROR);
			CUser::Delete($message["ID"]);
		}

		$user->Authorize($message["ID"], true);

		if (!empty($profile)) {
			if ($profile["PROFILE_TYPE"] == LEGAL_ENTITY_CUSTOMER_ID) {
				$profile["CONTACT_PERSON"] = $name;
				$profile["EMAIL"] = $email;
				$profile["PHONE"] = $phone;
			} elseif ($profile["PROFILE_TYPE"] == INDIVIDUAL_CUSTOMER_ID) {
				$profile["FIO"] = $name;
				$profile["EMAIL"] = $email;
				$profile["PHONE"] = $phone;
			}

			$this->createProfile($message["ID"], $profile);
		}

		return $message;
	}

	public function createProfile($userId, $fields) {
		$newProfile = Bitrix\Sale\Internals\UserPropsTable::createObject();
		$newProfile["NAME"] = "Новый профиль";
		$newProfile["USER_ID"] = $userId;

		$newProfile["PERSON_TYPE_ID"] = $fields["PROFILE_TYPE"];
		$newProfile["DATE_UPDATE"] = new Bitrix\Main\Type\DateTime();
		$newProfile->save();

		$orderProperties = \Bitrix\Sale\Property::getList([
			'filter' => [
				// "IS_ADDRESS" => "Y",
				"ACTIVE" => "Y",
			]
		])->fetchCollection();

		foreach ($fields as $key => $value) {
			if ($key == "PROFILE_TYPE") {
				continue;
			}

			foreach ($orderProperties as $orderProp) {
				if ($orderProp->getPersonTypeId() != $fields["PROFILE_TYPE"]) {
					continue;
				}

				if ($orderProp->getCode() !== $key) {
					continue;
				}

				$newProp = \Bitrix\Sale\Internals\UserPropsValueTable::createObject();
				$newProp['USER_PROPS_ID'] = $newProfile["ID"];
				$newProp['ORDER_PROPS_ID'] = $orderProp->getId();
				$newProp['NAME'] = $orderProp->getName();
				$newProp['VALUE'] = $value;
				$newProp->save();
			}
		}
	}

	public function changePasswordAction (string $password, string $confirmPassword, string $checkword, string $login) {
		$user = new CUser();

		if (!strlen($login)) {
			$login = $_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"];
		}

		$message = $user->ChangePassword(
			$login,
			$checkword,
			$password,
			$confirmPassword
			// false,
			// "",
			// 0,
			// false
		);

		if ($message["TYPE"] == "ERROR") {
			$this->errorCollection[] = new Error(str_replace("<br>", "\n", $message["MESSAGE"]));
			return;
		}

		$userBase = \Bitrix\Main\UserTable::getRow([
			"select" => ["ID"],
			"filter" => ["ACTIVE" => "Y", "LOGIN" => $login]
		]);

		if (is_null($userBase)) {
			$this->errorCollection[] = new Error("Странная ошибка");
			return;
		}

		$user->Authorize($userBase["ID"]);

		return $message;
	}

	public function authorizeBySmsAction (string $tel) {
		try {
			$pass = AuthNumbersTable::generatePass($tel);

			/**
			 * @var MKMatriXApiSms $smsApi
			 */
			$smsApiClassName = CBitrixComponent::includeComponentClass("mkmatrix:api.sms");
			$smsApi = new $smsApiClassName;
			$smsApi->sendPass($tel, $pass);
		} catch (\Throwable $th) {
			$this->errorCollection[] = new Error($th->getMessage());
		}

		/**
		 * @var \Bitrix\Main\Type\DateTime $lastTime
		 */
		$lastTime = AuthNumbersTable::$lastAddedTime;
		$canSendAgainAfter = $lastTime->add(AuthNumbersTable::SEND_CHECK_INTERVAL);
		// return timestamp in php's way (not multiplying by 1000)
		return $canSendAgainAfter->getTimestamp();
	}

	public function checkSmsPassAction (string $number, string $password) {
		global $USER;
		try {
			$authObject = AuthNumbersTable::checkPassword($number, $password);
		} catch (\Throwable $th) {
			$this->errorCollection[] = new Error($th->getMessage());
			return;
		}


		if (is_null($authObject)) {
			$this->errorCollection[] = new Error("Не правильный код");
			return;
		}

		if ($authObject["USER"]["ID"] > 0) {
			$USER->Authorize($authObject["USER"]["ID"], true);
			// may be vulnerable cause of race conditions
			if ($USER->IsAdmin()) {
				$this->errorCollection[] = new Error("Данная учетная запись защищена от входа по номеру телефона");
				$USER->Logout();
			}
			return;
		}

		$this->registerAndAuth($number, $password);
	}

	private function registerAndAuth(string $number, string $password, $captchaWord = "", $captchaSid = "") {
		global $USER;
		$login = "RedSMS_" . $number;
		$password = Utils::generateRandomString(16);

		$message = $USER->Register(
			$login, // USER_LOGIN
			"", // USER_NAME
			"", // LAST_NAME
			$password, // USER_PASSWORD
			$password, // USER_CONFIRM_PASSWORD
			Utils::generateRandomString(12) . "@" . "litebuy.ru", // USER_EMAIL
			false, // SITE_ID
			$captchaWord,
			$captchaSid,
			false // skip confirm
			// $phone
		);

		if ($message["TYPE"] == "ERROR") {
			$this->errorCollection[] = new Error(str_replace("<br>", "\n", $message["MESSAGE"]));
			return;
		}

		if (!($message["ID"] > 0)) {
			$this->errorCollection[] = new Error("Неизвестная ошибка");
			return;
		}

		$user = new CUser();

		$res = $user->update($message["ID"], [
			"PERSONAL_PHONE" => $number
		]);

		if (!$res) {
			$this->errorCollection[] = new Error($user->LAST_ERROR);
			CUser::Delete($message["ID"]);
		}

		$user->Authorize($message["ID"]);
	}
}