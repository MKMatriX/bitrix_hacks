<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\DB\MssqlConnection;
use Bitrix\Main\Entity;

/**
 * Данный скрипт служит для создания моделей из хайлоад блоков
 * Делает он это не очень хорошо, например вставляет текущюю дату вместо
 * кода который бы брал "сейчас", однако все же экономит кучу времени
 * ну и нужно пхп версии 8+ или даже 8.3+
 * также заменить мой неймспейс на нейспейс вашей компании
 * Вообще у меня в планах было добавить кнопку создания файлов через этот скрипт
 * но, увы, руки не дошли.
 */

if(!CModule::IncludeModule("highloadblock")) {
	throw new Main\SystemException("Модуль highloadblock не установлен!");
}

function getUtmFieldTable($entityName, $hlblock, $uField, $hlClass) {
	/** @global CUserTypeManager $USER_FIELD_MANAGER */
	global $USER_FIELD_MANAGER;

	$utmClassName = $entityName.'Utm'.Main\Text\StringHelper::snake2camel($uField['FIELD_NAME']);
	$utmTableName = $hlblock['TABLE_NAME'].'_'.mb_strtolower($uField['FIELD_NAME']);

	$entityName = $utmClassName . 'Table';
	$fullEntityName = $entityName;
	$namespace = "MKMatriX\Main";
	$classCode = PHP_EOL;
	$classCodeEnd = PHP_EOL;

	$classCode .= "to file: local/module/mkmatrix.main/lib/" . mb_strtolower($utmClassName) . ".php" . PHP_EOL;
	$classCode .= '
&lt;?
namespace MKMatriX\Main;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;


';

	$fullEntityName = '\\'.$namespace.'\\'.$fullEntityName;
	$parentClass = DataManager::class;
	$classCode = $classCode."class {$entityName} extends ".$parentClass." {" . PHP_EOL;
	$classCodeEnd = $classCodeEnd . '}'.$classCodeEnd;

	$classCode .= '	public static function getTableName(){return '.var_export($utmTableName, true).';}' . PHP_EOL;

	// generate entity & data manager
	$fieldsMap = [];

	// add ID
	$fieldsMap[] = "
			(new Fields\IntegerField('ID', []))
				->configurePrimary(true)->configureAutocomplete(true)";

	$params = [
		'required' => $uField['MANDATORY'] == 'Y'
	];
	$field = $USER_FIELD_MANAGER->getEntityField($uField, 'VALUE');
	$fieldClass = str_replace('Bitrix\\Main\\ORM\\', '', $field::class);
	$configs = "";
	$configIdent = "				";
	$fieldString = "
			(new {$fieldClass}('{$field->getName()}', []))";

	if ($configs !== "") {
		$fieldString .= trim($configs);
	}

	if ($uField['USER_TYPE']['BASE_TYPE'] === 'file') {
		$fileLinkName = str_replace("UF_", "", $field->getName());
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$concat = $helper->getConcatFunction("'/upload/'", "%s", "'/'", "%s");
		// $concat = str_replace("'", "\\'", $concat);

		$fieldString .= ",
			(new Fields\Relations\Reference(
				'{$fileLinkName}',
				\Bitrix\Main\FileTable::class,
				\Bitrix\Main\ORM\Query\Join::on('this.{$field->getName()}', 'ref.ID')
			))->configureJoinType('left'),
			(new Fields\ExpressionField(
				'{$fileLinkName}_SRC',
				\"{$concat}\",
				['{$fileLinkName}.SUBDIR', '{$fileLinkName}.FILE_NAME']
			))";
	}

	if ($uField['USER_TYPE']['BASE_TYPE'] === 'enum') {
		$enumFieldName = str_replace("UF_", "", $field->getName()) . "_ENUM";

		$fieldString .= ",
			(new Fields\Relations\Reference(
				'{$enumFieldName}',
				\MKMatriX\Main\Fixes\FieldEnumTable::class,
				\Bitrix\Main\ORM\Query\Join::on('this.{$field->getName()}', 'ref.ID')
			))->configureJoinType('left')";
	}

	$fieldsMap[] = $fieldString;

	// $entity->addField($field);
	foreach ($USER_FIELD_MANAGER->getEntityReferences($uField, $field) as $reference) {
		/** @var Bitrix\Main\ORM\Fields\Relations\Reference $reference */
		$refName = str_replace("UF_", "", $field->getName());
		$refName = str_replace("_REF", "", $refName);

		$refTable = "\MKMatriX\Main" . $reference->getDataType() . "Table";

		// echo "<pre> ", print_r($refTable, true), "</pre>";
		// echo "<pre> ", print_r($reference, true), "</pre>";


		$fieldString = "
			(new Fields\Relations\Reference(
				'{$refName}',
				{$refTable}::class,
				\Bitrix\Main\ORM\Query\Join::on('this.". $field->getName() ."', 'ref.ID')
			))->configureJoinType('left')";
		$fieldsMap[] = $fieldString;
		// 	$entity->addField($reference);
	}

	// $fieldString = "
	// 		(new Fields\Relations\Reference(
	// 			'PARENT_{$uField["FIELD_NAME"]}',
	// 			" . $hlClass. "::class,
	// 			[
	// 				'=this.VALUE_ID' => 'ref.ID',
	// 				'=this.FIELD_ID' => ['?i', {$uField['ID']}]
	// 			]
	// 		))->configureJoinType('left')";
	// $fieldsMap[] = $fieldString;

	$fieldString = "
			(new Fields\Relations\Reference(
				'OBJECT',
				" . $hlClass. "::getEntity(),
				\Bitrix\Main\ORM\Query\Join::on('this.ID', 'ref.ID')
			))->configureJoinType('left')";
	$fieldsMap[] = $fieldString;

	$aliasFields = [];
	$fieldString = "
			(new Fields\ExpressionField(
				'{$uField["FIELD_NAME"]}_SINGLE',
				'%s',
				'\\MKMatriX\\Main\\{$utmClassName}:OBJECT.VALUE',
				[
					'data_type' => \\".get_class($field)."::class,
				]
			))";
	$aliasFields[] = $fieldString;

	$fieldString = "
			(new Fields\ArrayField(
				'{$uField["FIELD_NAME"]}',
			))->configureSerializationPhp()";
	$aliasFields[] = $fieldString;


	$classCode .= '
	public static function getMap() {
		return ['
			.implode(",", $fieldsMap) . ',
		];
	}' . PHP_EOL;

	return [$classCode . $classCodeEnd, $aliasFields];
}

function printHlMap($hl) {
	/** @global CUserTypeManager $USER_FIELD_MANAGER */
	global $USER_FIELD_MANAGER;

	$USER_FIELD_MANAGER->CleanCache();

	$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::resolveHighloadblock($hl);

	if (empty($hlblock)) {
		throw new Main\SystemException(sprintf(
			"Invalid highloadblock description '%s'.",
			mydump($rawBlock)
		));
	}


	// generate entity & data manager
	$fieldsMap = [];
	$additionalTables = [];

	// add ID
	$fieldsMap[] = "
			(new Fields\IntegerField('ID', []))
				->configurePrimary(true)->configureAutocomplete(true)";

	// build datamanager class
	$entityName = $hlblock['NAME'];
	$entityDataClass = $hlblock['NAME'].'Table';
	$entityTableName = $hlblock['TABLE_NAME'];

	$uFields = $USER_FIELD_MANAGER->getUserFields(
		\Bitrix\Highloadblock\HighloadBlockTable::compileEntityId($hlblock['ID'])
	);

	$names = \Bitrix\Main\UserFieldLangTable::query()
		->setSelect([
			"_ID" => "USER_FIELD.ID",
			"_CODE" => "USER_FIELD.FIELD_NAME",
			"NAME" => "EDIT_FORM_LABEL",
			"HELP_MESSAGE",
		])
		->setOrder(["USER_FIELD.SORT" => "ASC"])
		->where("LANGUAGE_ID", "ru")
		->where("USER_FIELD.ENTITY_ID", "HLBLOCK_" . $hlblock['ID'])
		->whereNot("USER_FIELD.FIELD_NAME", "ID")
		->exec()->fetchAll();
	$names = array_combine(array_column($names, "_CODE"), $names);

	$utmTables = [];

	foreach ($uFields as $uField) {
		if ($uField['MULTIPLE'] == 'N') {
			// just add single field
			$params = [
				'required' => $uField['MANDATORY'] == 'Y'
			];
			// echo "<pre> ", print_r($uField, true), "</pre>";
			/** @var Entity\DatetimeField|Entity\FloatField|Entity\IntegerField|Entity\StringField|mixed $field */
			$field = $USER_FIELD_MANAGER->getEntityField($uField, $uField['FIELD_NAME'], $params);
			// echo "<pre> ", print_r($field, true), "</pre>";
			$fieldClass = str_replace('Bitrix\\Main\\ORM\\', '', $field::class);
			$configs = "";
			$configIdent = "				";
			$ruTitle = $names[$field->getName()]["NAME"];
			if ($ruTitle) {
				$configs .= $configIdent . "->configureTitle('" . $ruTitle . "')\n";
			}
			if ($field->isPrimary()) {
				$configs .= $configIdent . "->configurePrimary(true)\n";
			}
			if ($field->isRequired()) {
				$configs .= $configIdent . "->configureRequired(true)\n";
			}
			if ($field->isUnique()) {
				$configs .= $configIdent . "->configureUnique(true)\n";
			}
			if ($field->isAutocomplete()) {
				$configs .= $configIdent . "->configureAutocomplete(true)\n";
			}
			// if ($field->isNullable()) {
			// 	$configs .= $configIdent . "->configureNullable(true)\n";
			// }
			if ($field->isBinary()) {
				$configs .= $configIdent . "->configureBinary(true)\n";
			}
			if ($field->isSerialized()) {
				$configs .= $configIdent . "->configureSerialized()\n";
			}
			$defaultValue = $field->getDefaultValue();
			if (isset($defaultValue) && $defaultValue !== "") {
				if ($field instanceof Bitrix\Main\ORM\Fields\IntegerField && $defaultValue != 0) {
					$configs .= $configIdent . "->configureDefaultValue(\"{$defaultValue}\")\n";
				}
				if (!($field instanceof Bitrix\Main\ORM\Fields\IntegerField)) {
					// var_dump($defaultValue);
					$configs .= $configIdent . "->configureDefaultValue(\"{$defaultValue}\")\n";
				}
			}

			$fieldString = "
			(new {$fieldClass}('{$field->getName()}'))";

			if ($configs !== "") {
				$fieldString .= "\n" . $configIdent . trim($configs);
			}

			if ($uField['USER_TYPE']['BASE_TYPE'] === 'file') {
				$fileLinkName = str_replace("UF_", "", $field->getName());
				$connection = \Bitrix\Main\Application::getConnection();
				$helper = $connection->getSqlHelper();
				$concat = $helper->getConcatFunction("'/upload/'", "%s", "'/'", "%s");
				// $concat = str_replace("'", "\\'", $concat);

				$fieldString .= ",
			(new Fields\Relations\Reference(
				'{$fileLinkName}',
				\Bitrix\Main\FileTable::class,
				\Bitrix\Main\ORM\Query\Join::on('this.{$field->getName()}', 'ref.ID')
			))->configureJoinType('left'),
			(new Fields\ExpressionField(
				'{$fileLinkName}_SRC',
				\"{$concat}\",
				['{$fileLinkName}.SUBDIR', '{$fileLinkName}.FILE_NAME']
			))";
			}

			if ($uField['USER_TYPE']['BASE_TYPE'] === 'enum') {
				$enumFieldName = str_replace("UF_", "", $field->getName()) . "_ENUM";

				$fieldString .= ",
			(new Fields\Relations\Reference(
				'{$enumFieldName}',
				\MKMatriX\Main\Fixes\FieldEnumTable::class,
				\Bitrix\Main\ORM\Query\Join::on('this.{$field->getName()}', 'ref.ID')
			))->configureJoinType('left')";
			}

			$fieldsMap[] = $fieldString;

			// $entity->addField($field);
			foreach ($USER_FIELD_MANAGER->getEntityReferences($uField, $field) as $reference) {
				/** @var Bitrix\Main\ORM\Fields\Relations\Reference $reference */
				$refName = str_replace("UF_", "", $field->getName());
				$refName = str_replace("_REF", "", $refName);

				$refTable = "\MKMatriX\Main" . $reference->getDataType() . "Table";

				// echo "<pre> ", print_r($refTable, true), "</pre>";
				// echo "<pre> ", print_r($reference, true), "</pre>";


				$fieldString = "
			(new Fields\Relations\Reference(
				'{$refName}',
				{$refTable}::class,
				\Bitrix\Main\ORM\Query\Join::on('this.". $field->getName() ."', 'ref.ID')
			))->configureJoinType('left')";
				$fieldsMap[] = $fieldString;
			// 	$entity->addField($reference);
			}
		} else {
			// build utm entity
			// echo "Множественные поля не поддерживаются ";
			// echo "<pre> ", print_r($uField, true), "</pre>";
			List($additionalTable, $additionalFields) = getUtmFieldTable($entityName, $hlblock, $uField, $entityDataClass);
			$additionalTables[] = $additionalTable;
			foreach ($additionalFields as $key => $value) {
				$fieldsMap[] = $value;
			}
		}
	}
	foreach ($additionalTables as $key => $value) {
		print_r($value);
	}

	// make with an empty map
	$eval = '
to file: local/module/mkmatrix.main/lib/' . strtolower($hlblock['NAME']) . '.php
&lt;?
namespace MKMatriX\Main;

use \Bitrix\Main\ORM\Data\DataManager;
// use \Bitrix\Highloadblock\DataManager; // need to import hl module for that, but supports multiple fields right
use \Bitrix\Main\ORM\Fields;


class '.$entityDataClass.' extends DataManager
{
	public static function getTableName()
	{
		return '.var_export($entityTableName, true).';
	}

	public static function getMap()
	{
		return ['
			.implode(",", $fieldsMap) . ',
		];
	}

	public static function getHighloadBlock()
	{
		return [
			"ID" => "' . $hlblock['ID']. '",
			"NAME" => "' . $hlblock['NAME']. '",
			"TABLE_NAME" => "' . $hlblock['TABLE_NAME']. '",
		];
	}
}
';

	return $eval;
}

$hls = \Bitrix\Highloadblock\HighloadBlockTable::query()
	->setSelect(["ID"])
	->setOrder(["NAME" => "ASC"])
	->exec()->fetchAll();

$hlIds = array_column($hls, "ID");

foreach ($hlIds as $id) {
	// printHlMap($id);
	echo "<pre> ", printHlMap($id), "</pre>";
	echo "<hr/>";
}



CMain::FinalActions();
?>