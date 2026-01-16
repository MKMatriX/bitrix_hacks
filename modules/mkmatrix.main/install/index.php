<?php

use Bitrix\Main\ModuleManager;
use MKMatriX\Main\Events\Handler;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\Base;



if (class_exists('mkmatrix_main')) {
	return;
}

class mkmatrix_main extends \CModule {
	public function __construct() {
		$this->MODULE_ID = "mkmatrix.main";

		$this->MODULE_VERSION = '0.0.1';
		$this->MODULE_VERSION_DATE = '2022-03-24 09:06:00';

		$this->MODULE_NAME = 'МК: Основной модуль';
		$this->MODULE_DESCRIPTION = 'Типичные компоненты, js библиотеки, функции';

		$this->MODULE_GROUP_RIGHTS = 'Y';

		$this->PARTNER_NAME = "Кучеров Матвей Николаевич";
		$this->PARTNER_URI = "https://career.habr.com/matvey-kucherov";
	}

	/**
	 * Смотреть тела событий в /modules/mkmatrix.main/lib/events/handler.php
	 * И в файле functions.php, в той же папке.
	 *
	 * @return void
	 */
	public function getEvents() {
		return [
			['main', 'OnBeforeEventAdd'], // тут модифицируется вид списка элементов для письма о новом заказе.
			// ['iblock', 'OnBeforeIBlockElementAdd'],
			// ['iblock', 'OnBeforeIBlockElementUpdate'],
			['sale', 'OnSaleOrderSaved'], // тут создается счет в pdf для юр.лиц.
		];
	}

	public function getModuleTables() {
		return [
			MKMatriX\Main\FavoriteTable::class,
			MKMatriX\Main\CompareTable::class,
		];
	}

	public function DoInstall() {
		ModuleManager::registerModule($this->MODULE_ID);
		// $this->installFiles();
		// $this->installDB();
		$this->installEvents();
		// $this->installAgents();
	}

	public function DoUninstall() {
		ModuleManager::unRegisterModule($this->MODULE_ID);

		// $this->unInstallFiles();
		// $this->unInstallDB();
		$this->unInstallEvents();
		// $this->unInstallAgents();
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

	public function reinstallTables() {
		$connection = Application::getConnection();
		foreach ($this->getModuleTables() as $tableClassName) {
			$instance = Base::getInstance($tableClassName);
			$tableName = $instance->getDBTableName();
			$tableExisits = $connection->isTableExists($tableName);

			if ($tableExisits) {
				$connection->dropTable($tableName);
			}

			$instance->createDBTable();
		}
	}
}
