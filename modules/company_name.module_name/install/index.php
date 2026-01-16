<?php

use Bitrix\Main\ModuleManager;
// change company name and module name though all the file
use CompanyName\ModuleName\Events\Handler;

if (class_exists('company_name_module_name')) {
	return;
}

class company_name_module_name extends \CModule
{
	public function __construct()
	{
		$this->MODULE_ID = "company_name.module_name";

		$this->MODULE_VERSION = '1.0.0';
		$this->MODULE_VERSION_DATE = '2026-01-01 00:00:00';

		$this->MODULE_NAME = 'Название модуля';
		$this->MODULE_DESCRIPTION = 'Описание модуля';

		$this->MODULE_GROUP_RIGHTS = 'Y';

		$this->PARTNER_NAME = "Авторство модуля";
		$this->PARTNER_URI = "https://ссылка/на.разработчиков";
	}

	public function DoInstall()
	{
		ModuleManager::registerModule($this->MODULE_ID); // после этого модулем можно пользоваться
		// $this->installFiles(); // если хоти скопировать файлы, например компоненты или js
		// $this->installDB(); // если хоти что-то записать в базу
		$this->installEvents(); // события весьма удобно хранить в модулях
		// $this->installAgents(); // зачем агенты если есть крон, но пусть будут
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
			// ['iblock', 'OnBeforeIBlockElementAdd'], // в формате [модуль, событие]
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

	/**
	 * Это функция которая удалит потерянные события, т.е. которые мы завели в базе, но удалили в коде
	 * Вызывается по кнопке в админке модуля
	 *
	 * @return void
	 */
	public function unInstallAllModuleEvents() {
		$con = \Bitrix\Main\Application::getConnection();

		$strSql = "DELETE FROM b_module_to_module " .
			"WHERE FROM_MODULE_ID='" . $this->MODULE_ID . "' " .
			"OR TO_MODULE_ID='" . $this->MODULE_ID . "' ";

		$con->queryExecute($strSql);

		// Так как все кешируется, то вызываем удаление несуществующего события для сброса кеша.
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