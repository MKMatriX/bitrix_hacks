<?
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;

class MKMatrixProfileDetailComponent extends CBitrixComponent implements Controllerable, Errorable {
	/** @var ErrorCollection */
	public $errorCollection;

	public $orderProperties;
	public $userProfiles;
	public $userProfileProps;

	public function onPrepareComponentParams($arParams) {
		$this->errorCollection = new ErrorCollection();

		$arParams["CACHE_TIME"] = $arParams["CACHE_TIME"]? intval($arParams["CACHE_TIME"]) : 36000000;
		return $arParams;
	}


	public function executeComponent() {
		$this->arResult = [];

		CJSCore::Init([
			"mk_ajax_forms"
		]);

		if ($this->StartResultCache()){
			if(!Loader::IncludeModule("sale")){
				$this->AbortResultCache();
				ShowError("Модуль каталога не установлен");
				return;
			}

			/** @var EO_User $user */
			$user = \Bitrix\Main\UserTable::getById($GLOBALS['USER']->GetId())->fetchObject();

			$this->arResult["NAME"] = trim($user->getLastName() . " " . $user->getName() . " " . $user->getSecondName());
			if (!strlen($this->arResult["NAME"])) {
				$this->arResult["NAME"] = "Не заполнено";
			}
			$this->arResult["EMAIL"] = $user->getEmail();
			$this->arResult["PHONE"] = $user->getPersonalPhone();

			$this->getUserProps();
			$this->fillPropsToResult();

			$this->IncludeComponentTemplate();
			// $this->EndResultCache();
		}
	}

	public function fillPropsToResult() {
		$this->arResult["PROFILE_PROPERTIES"] = [];

		foreach ($this->orderProperties as $op) {
			$arOp = $op->collectValues();
			$arOp['VALUES'] = [];

			foreach ($this->userProfiles as $profile) {
				foreach ($this->userProfileProps as $prop) {
					if ($prop->getUserPropsId() != $profile->getId()) {
						continue;
					}

					if ($prop->getOrderPropsId()  != $arOp['ID']) {
						continue;
					}

					$arOp['VALUES'][$profile->getId()] = $prop->collectValues();
				}
			}

			$this->arResult["PROFILE_PROPERTIES"][] = $arOp;
		}

		$this->arResult["PROFILES"] = [];
		foreach ($this->userProfiles as $profile) {
			$this->arResult["PROFILES"][] = $profile->collectValues();
		}
	}

	public function getUserProps() {
		if(!Loader::IncludeModule("sale")){
			new Error("Модуль каталога не установлен");
			return;
		}

		$this->orderProperties = \Bitrix\Sale\Internals\OrderPropsTable::getList([
			'filter' => [
				// "IS_ADDRESS" => "Y",
				"ACTIVE" => "Y",
				"!TYPE" => "LOCATION",
				"UTIL" => "N"
			]
		])->fetchCollection();


		$this->userProfiles = Bitrix\Sale\Internals\UserPropsTable::getList([
			"filter" => ['USER_ID' => $GLOBALS["USER"]->GetId()],
			"order" => ['DATE_UPDATE' => 'desc'],
		])->fetchCollection();

		$userProfilesIds = $this->userProfiles->getIdList();

		$this->userProfileProps = \Bitrix\Sale\Internals\UserPropsValueTable::getList([
			'filter' => [
				// 'ORDER_PROPS_ID' => $addressPropsIds,
				'USER_PROPS_ID' => $userProfilesIds,
			]
		])->fetchCollection();
	}

	/**
	 * ajax/fetch configuration
	 * @return array
	 */
	public function configureActions(): array {
		return [
			'updateUser' => [
				'prefilters' => [
					new Csrf(),
					new Authentication(),
				],
			],
			'changePassword' => [
				'prefilters' => [
					new Csrf(),
					new Authentication(),
				],
			],
			'addProfile' => [
				'prefilters' => [
					new Csrf(),
					new Authentication(),
				],
			],
			'deleteProfile' => [
				'prefilters' => [
					new Csrf(),
					new Authentication(),
				],
			],
			'updateProfile' => [
				'prefilters' => [
					new Csrf(),
					new Authentication(),
				],
			],
			'profileSetDefault' => [
				'prefilters' => [
					new Csrf(),
					new Authentication(),
				],
			],
		];
	}

	public function profileSetDefaultAction (int $ID) {
		$this->getUserProps();

		$profile = $this->userProfiles->getByPrimary($ID);
		if (is_null($profile)) {
			$this->errorCollection[] = new Error("Не удалось найти профиль для изменения");
			return;
		}
		$profile->setDateUpdate(new Bitrix\Main\Type\DateTime());
		$profile->save();
	}

	public function updateUserAction (string $name, string $phone, string $email = "", string $password = "") {
		$user = new CUser;

		$lastName = "";
		$secondName = "";
		$arName = explode(" ", htmlspecialchars($name));
		$parts = count($arName);

		if ($parts == 1) {
			$name = array_shift($arName);
		} elseif ($parts == 2) {
			$lastName = array_shift($arName);
			$name = array_shift($arName);
		} else {
			$lastName = array_shift($arName);
			$name = array_shift($arName);
			$secondName = implode(" ", $arName);
		}

		$userParams = [
			"NAME" => $name,
			"LAST_NAME" => $lastName,
			"SECOND_NAME" => $secondName,

			"PERSONAL_PHONE" => htmlspecialchars($phone),
			"EMAIL" => htmlspecialchars($email),
		];

		if (strlen($email)) {
			$userParams["EMAIL"] = htmlspecialchars($email);
		}

		if (strlen($password) > 3) {
			$userParams["PASSWORD"] = $password;
			$userParams["CONFIRM_PASSWORD"] = $password;
		}

		$res = $user->Update($GLOBALS['USER']->GetId(), $userParams);

		if (!$res) {
			$this->errorCollection[] = new Error($user->LAST_ERROR);
		}
	}

	public function updateProfileAction(int $ID, array $profileToSave, int $profileTypeId = LEGAL_ENTITY_CUSTOMER_ID) {
		$this->getUserProps();

		$profile = $this->userProfiles->getByPrimary($ID);
		if (is_null($profile)) {
			$this->errorCollection[] = new Error("Не удалось найти профиль для изменения");
			return;
		}

		$profile["PERSON_TYPE_ID"] = $profileTypeId;
		$profile->save();
		foreach ($this->userProfileProps as $prop) {
			if ($prop->getUserPropsId() == $ID) {
				$prop->delete();
			}
		}

		$propCollection = \Bitrix\Sale\Internals\UserPropsValueTable::createCollection();
		foreach ($profileToSave as $key => $value) {
			foreach ($this->orderProperties as $orderProp) {
				if ($orderProp->getPersonTypeId() != $profileTypeId) {
					continue;
				}

				if ($orderProp->getCode() !== $key) {
					continue;
				}

				if ($orderProp["IS_PROFILE_NAME"] && strlen($value)) {
					$profile["NAME"] = $value;
					$profile->save();
				}

				$newProp = \Bitrix\Sale\Internals\UserPropsValueTable::createObject();
				$newProp['USER_PROPS_ID'] = $ID;
				$newProp['ORDER_PROPS_ID'] = $orderProp->getId();
				$newProp['NAME'] = $orderProp->getName();
				$newProp['VALUE'] = $value;
				$propCollection[] = $newProp;
			}
		}

		$propCollection->save();
		// $profile->save();

		return [
			"props" => array_map(function ($p) {return $p->collectValues();}, $propCollection->getAll()),
			"profile" => $profile->collectValues(),
			"typeId" => $profileTypeId,
			"typeName" => self::getProfileTypeName($profileTypeId),
		];
	}

	public function addProfileAction(array $profileToSave, int $profileTypeId = LEGAL_ENTITY_CUSTOMER_ID) {
		$this->getUserProps();

		$profiles = $this->userProfiles->getAll();
		if (count($profiles)) {
			$profile = $profiles[0]->collectValues();
			$newProfile = Bitrix\Sale\Internals\UserPropsTable::createObject();
			foreach ($profile as $key => $value) {
				if ($key != "ID") {
					$newProfile->set($key, $value);
				}
			}
		} else {
			$newProfile = Bitrix\Sale\Internals\UserPropsTable::createObject();
			$newProfile["NAME"] = "Новый профиль";
			$newProfile["USER_ID"] = $GLOBALS["USER"]->GetId();
		}

		$newProfile["PERSON_TYPE_ID"] = $profileTypeId;
		$newProfile["DATE_UPDATE"] = new Bitrix\Main\Type\DateTime();
		$newProfile->save();

		$propCollection = \Bitrix\Sale\Internals\UserPropsValueTable::createCollection();
		foreach ($profileToSave as $key => $value) {
			foreach ($this->orderProperties as $orderProp) {
				if ($orderProp->getPersonTypeId() != $profileTypeId) {
					continue;
				}

				if ($orderProp->getCode() !== $key) {
					continue;
				}

				if ($orderProp["IS_PROFILE_NAME"]) {
					$newProfile["NAME"] = $value;
					$newProfile->save();
				}

				$newProp = \Bitrix\Sale\Internals\UserPropsValueTable::createObject();
				$newProp['USER_PROPS_ID'] = $newProfile["ID"];
				$newProp['ORDER_PROPS_ID'] = $orderProp->getId();
				$newProp['NAME'] = $orderProp->getName();
				$newProp['VALUE'] = $value;
				$propCollection[] = $newProp;
				// $newProp->save();
			}
		}

		$propCollection->save();

		return [
			"props" => array_map(function ($p) {return $p->collectValues();}, $propCollection->getAll()),
			"profile" => $newProfile->collectValues(),
			"typeId" => $profileTypeId,
			"typeName" => self::getProfileTypeName($profileTypeId),
		];
	}

	public static function getProfileTypeName($tid = 0) {
		$type = "Непонятный тип покупателя";
		switch ($tid) {
			case LEGAL_ENTITY_CUSTOMER_ID:
				$type = "Юридическое лицо";
				break;
			case INDIVIDUAL_CUSTOMER_ID:
				$type = "Физическое лицо";
				break;
		}

		return $type;
	}

	public function deleteProfileAction(int $profileId) {
		$this->getUserProps();

		foreach ($this->userProfileProps as $prop) {
			if ($prop->getUserPropsId() == $profileId) {
				$prop->delete();
			}
		}

		$this->userProfiles->getByPrimary($profileId)->delete();

		return "Профиль удален";
	}

	public function changePasswordAction(string $password) {
		$user = new CUser;

		$res = $user->Update($GLOBALS['USER']->GetId(), [
			"PASSWORD" => $password,
			"CONFIRM_PASSWORD" => $password,
		]);

		if (!$res) {
			$this->errorCollection[] = new Error($user->LAST_ERROR);
		}
	}

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
}