<?php

use Bitrix\Main\ModuleManager;
use MKMatriX\Main\Events\Handler;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\Base;



if (class_exists('mkmatrix_iblockwebp')) {
	return;
}

class mkmatrix_iblockwebp extends \CModule
{
	public function __construct()
	{
		$this->MODULE_ID = "mkmatrix.iblockwebp";

		$this->MODULE_VERSION = '1.0.0';
		$this->MODULE_VERSION_DATE = '2023-10-13 08:00:00';

		$this->MODULE_NAME = 'МК: webp';
		$this->MODULE_DESCRIPTION = 'перевод картинок инфоблока в формат webp';

		$this->MODULE_GROUP_RIGHTS = 'Y';

		$this->PARTNER_NAME = "Кучеров Матвей Николаевич";
		$this->PARTNER_URI = "https://career.habr.com/matvey-kucherov";
	}


	public function DoInstall()
	{
		ModuleManager::registerModule($this->MODULE_ID);

		if (!Application::getConnection()->isTableExists(Base::getInstance(\MKMatriX\Iblockwebp\SizesTable::class)->getDBTableName())) {
			Base::getInstance(\MKMatriX\Iblockwebp\SizesTable::class)->createDBTable();
		}


		// $this->installFiles();
		// $this->installDB();
		$this->installEvents();
		// $this->installAgents();
	}

	public function DoUninstall()
	{
		ModuleManager::unRegisterModule($this->MODULE_ID);

		// $this->unInstallFiles();
		// $this->unInstallDB();
		$this->unInstallEvents();
		// $this->unInstallAgents();
	}

	public function getEvents() {
		return [
			// ['iblock', 'OnBeforeIBlockElementAdd'],
			// ['iblock', 'OnBeforeIBlockElementUpdate'],
		];
	}

	public function installEvents() {
		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$events = $this->getEvents();

		foreach ($events as $event) {
			$eventManager->registerEventHandler(
				$event[0],
				$event[1],
				$this->MODULE_ID,
				Handler::class,
				$event[0] . "_" . $event[1]
			);
		}
	}

	public function unInstallEvents() {
		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$events = $this->getEvents();

		foreach ($events as $event) {
			$eventManager->unRegisterEventHandler(
				$event[0],
				$event[1],
				$this->MODULE_ID,
				Handler::class,
				$event[0] . "_" . $event[1]
			);
		}
	}

	public function unInstallAllModuleEvents() {
		$con = \Bitrix\Main\Application::getConnection();

		$strSql = "DELETE FROM b_module_to_module " .
			"WHERE FROM_MODULE_ID='" . $this->MODULE_ID . "' " .
			"OR TO_MODULE_ID='" . $this->MODULE_ID . "' ";

		$con->queryExecute($strSql);

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler(
			"no_matter",
			"just_to_drop",
			$this->MODULE_ID,
			Handler::class,
			"some_cache"
		);
	}
}
