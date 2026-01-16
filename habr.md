# Фантастический Битрикс и где он обитает

Привет друг. Меня зовут Разработчик. Вид веб-разработка, подвид битриксоидус. Это не тот кем я хотел когда-то стать, но десять лет сделали это свершившимся фактом. [Я устал](https://habr.com/ru/articles/979954/) быть им. Но перед тем как закрывать эту главу моей жизни хочу подвести итог. Оставить что-то для обучения нейросетей, кремниевых и углеродных.

## Область применения и повадки.

>Этот раздел не столько для программистов, сколько для людей, которые им платят.

Где же применять битрикс? Ответ прост и однозначен: для интернет магазинов в России. В общем там где у фирмы уже есть 1Ска или когда-нибудь будет. Для всего остального применение битрикса - очень плохая идея. Для импортозамещения, для госуслуг, для внутренних порталов.

Почему же тогда битрикс хорош для магазинов с 1С? Потому что он и правда хорош. Не в плане кода, но в плане самого своего существования как интернет магазин. Тут вам и обратная совместимость, что весьма плохо для кода, однако прекрасно, когда старый код, десятилетней давности продолжает работать без переписывания. Тут вам и интеграция с 1С, которую нужно допиливать напильником, о чем будет пара ласковых позднее, однако из коробки. А еще из коробки вам идет привычная админка, куча созданных "моделей", и возможность управления всем этим вообще без программиста. Хотите размеры и цвета? Они идут из коробки. Хотите разные цены для разных групп покупателей? Из коробки. Хотите акции в стиле 2+1? Вилку в подарок к коле и бургеру? Набор суши из существующих? Физ. Юр. лица со стандартными полями? Все есть. Даже есть фигня для "согласия на обработку персональных данных", хотя не то чтобы я ей пользовался, ибо я разработчик, мне плевать на ваши ПД.

Если просуммировать это все, то чтобы развернуть интернет магазин, при наличии 1С, нужно просто ***купить лицензию*** битрикса, хостинг, домен, установить битрикс с шаблоном интернет магазина, поставить модуль обмена для 1С, настроить модуль и на этом в целом все. При полном отсутствии знаний, но наличии рук и головы делов на пару недель, хотя можно управиться за несколько часов. Конечно стиль магазина захочется поменять, и если не разобраться с платными шаблонами придется платить дизайнеру-верстальщику-программисту-тестировщику и закладывать миллионный бюджет. Однако на старте можно обойтись без.

## Интеграция с 1С

Я обещал пару ласковых про обмен с 1С? Ну что же. Дело в том, что ~~1Сники не люди~~ как правило, вид базы данных товаров для внутренних нужд и нужд магазина - разный. Как правило товарные предложения, те самые размеры и цвета для футболок, размер тары для кока-колы и т.п. в 1С отсутствуют или заведены не по гайдлайнам. Да и вообще, на сайте хочется что-то **в стиле** того, что в 1С, а не в точности.

Как обойти?

Ну по-хорошему переписывать модуль на стороне 1С. Только этого никогда не произойдет. Специалисты по 1С это не совсем разработчики, и лезть в код не хотят. Можно конечно после приема данных для обмена, а они в формате XML, как-то сторонним скриптом приводить их к нужному виду, но это плохая идея.

Поэтому самый простой подход, это оставить оба модуля стандартными. И со стороны 1С и со стороны битрикса. Тут вам и рабочие обновления, и техподдержка которую оплачивает купленная лицензия. А чтобы это все на сайте выглядело как надо. Ну просто не используйте на сайте данные из 1С. Ладно-ладно, поясню. Вы записываете данные из 1С в один инфоблок, а для каталога используете другой.

Можно это делать через эвенты, например в дурно пахнущем стиле разместить в init.php
```php
	AddEventHandler("iblock", "OnAfterIBlockElementAdd", "OnAfterIBlockElementUpdateHandler");
	AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "OnAfterIBlockElementUpdateHandler");

	function OnAfterIBlockElementUpdateHandler(&$arFields) {
		$itemIBlockId = $arFields["IBLOCK_ID"];
		if ($itemIBlockId === CATALOG_1C_ID) {
			$dbItem = CIBlockElement::GetList(
				[],
				["=ID"=> $arFields["ID"]],
				false,
				false,
				[
					// тут берем все что нам нужно
				]
			);
			if ($arItem = $dbItem->Fetch()) {
				// тут добавляем или обновляем это в нашем основном каталоге
				// заодно делим на торговые предложения и просто товары
				// главное не забыть перенести XML_ID чтобы заказы в 1С отдавались правильно
			}
		}
	}
```
Пахнет это весьма дурно, ведь это запросы в цикле. Однако работает.

Конечно если мы уже сделали это для маленького магазина, а он вырос, товаров стало больше, а обмен начали запускать много раз на дню. То нужно оптимизировать. Большая часть кода останется такой же, однако часть запросов мы вынесем из цикла. В зависимости от того насколько нужно оптимизировать.

И вынесем это в какой-то отдельный скрипт, который например 1Ска будет вызывать после обмена.

Кстати в старом ядре нельзя было массово обновлять и добавлять элементы инфоблоков. В новом есть коллекции, так что все не так страшно. Однако ничто не мешает хотя бы для связанных таблиц (не элементов и их свойств) использовать более быстрые вещи.

Как пример для остатков в магазине

```php
	$dbStores = StoreProductTable::getList([
		"filter" => ["PRODUCT_ID" => $productIds]
	])->fetchCollection();

	foreach ($dbStores as $store) {
		// тут мы магазину с соответствующими STORE_ID и PRODUCT_ID задаем AMOUNT
		$store["AMOUNT"] = $someAmount;

		// не забывая что это может иметь вид
		$dbStores->remove($store);
		$store->delete();

		// или
		$store = StoreProductTable::createObject();
		$store["PRODUCT_ID"] = $productId;
		$store["AMOUNT"] = $amount["AMOUNT"];
		$store["STORE_ID"] = $amount["STORE_ID"];
		$$dbStores->add($store);
	}

	// после чего естественно
	$dbStores->save(true);
```

Это все сэкономит кучу времени на доступе к ссд.

И не знаю работают ли групповые операции с инфоблоками на новом ядре, но если нет, то сейчас будут грязные хаки. Но для начала надо рассказать немного про то, как же хранятся товары. А хранятся они в куче таблиц, та же `StoreProductTable` в коде выше это одна из них, и идти по всем ним мне не хватит компетенции. Обычно я просто нахожу где хранится нужная мне информация, тогда когда она нужна. Но вот если взять не товары, а просто элементы инфоблоков. То тут нужно кое-что знать.

Есть два стула. Точнее два типа инфоблоков, 1.0 и 2.0. Их различие в том, где они хранят свойство, старые инфоблоки, которые еще и идут по умолчанию, хранят свойства в общей таблице `b_iblock_element_property`, а новые в отдельных таблицах для каждого инфоблока с названиями в стиле `b_iblock_element_prop_m1` и `b_iblock_element_prop_s1`, где 1 это идешник инфоблока.

Структура `b_iblock_element_property` и `b_iblock_element_prop_mXXX` схожа. Ид элемента, ид свойства, и четыре столбца полезной нагрузки, описание, значение (чистое, числовое, и enum или списковое) еще тип значения для 1.0. А вот `b_iblock_element_prop_sXXX` совершенно другой зверь, тут в начале ИД элемента, а столбцы это уже `PROPERTY_XXX` и `DESCRIPTION_XXX`. Что естественно открывает кучу возможностей для ускорения фильтрации, создания индексов и т.п. для не множественных свойств инфоблоков 2.0.

Сами элементы **всех** инфоблоков хранятся в `b_iblock_element` где `IBLOCK_ID` определяет их инфоблок. Кстати в этой таблице есть `IBLOCK_SECTION_ID` и перед тем как работать с ним напрямую, рекомендую взглянуть на таблицу `b_iblock_section_element` ибо у одного элемента может быть много разделов, хотя и только один канонический.

Мой дорогой друг, ты уже догадался какой хак можно со всем этим сделать?

Ага, можно *просто* поменять `IBLOCK_ID` в записи элемента. Правда API тебе этого сделать не даст, и не просто так. Ведь все это обсуждение вида таблиц свойств и было для того, чтобы ты глянул на вершинку айсберга. В общем тебе придется на лету редактировать вид этих таблиц. И если для множественных можно будет хоть как-то использовать модели в стиле

```php
	public static function getMultiplePropsDataClass($iblockId) {
		$className = 'MProps' . $iblockId;

		if (class_exists($className . "Table")) {
			return $className . "Table";
		}

		$entityMProps = \Bitrix\Main\Entity\Base::compileEntity(
			$className,
			[
				'ID' => ['data_type' => 'integer', 'primary' => true],
				'IBLOCK_ELEMENT_ID' => ['data_type' => 'integer'],
				'IBLOCK_PROPERTY_ID' => ['data_type' => 'integer'],
				'VALUE' => ['data_type' => 'string'],
				'VALUE_ENUM' => ['data_type' => 'string'],
				'VALUE_NUM' => ['data_type' => 'float'],
				'DESCRIPTION' => ['data_type' => 'string'],
			],
			['table_name' => sprintf('b_iblock_element_prop_m%s', $iblockId)]
		);

		return $entityMProps->getDataClass();
	}
```

То для *простых* одиночных свойств 2.0 придется...

```php
	public static function getSinglePropsDataClass($iblockId) {
		$className = 'SProps' . $iblockId;

		if ($iblockId == self::$CATALOG_SKU_IBLOCK_ID) {
			$className .= "_" . self::$spClassTail; // тут увеличение счетчика если мы хотим создавать новую модель на лету
		}

		if (class_exists($className . "Table")) {
			return $className . "Table";
		}

		$props = PropertyTable::query()
			->setSelect(["ID", "MULTIPLE", "PROPERTY_TYPE"])
			->where("IBLOCK_ID", $iblockId)
			->where("MULTIPLE", "N")
			->where("VERSION", 2)
			->exec()->fetchAll();

		$sProps = [];
		foreach ($props as $prop) {
			$key = "PROPERTY_" . $prop["ID"];
			$type = $prop["PROPERTY_TYPE"] == PropertyTable::TYPE_NUMBER ? 'float' : 'string';
			$sProps[$key] = ['data_type' => $type];
			// вообще тут еще DESCRIPTION
		}
		// не объявлять эту штуку как примари
		// // кстати я совсем не помню почему, но когда-то это было мне важно.
		$sProps['IBLOCK_ELEMENT_ID'] = new \Bitrix\Main\Entity\IntegerField('IBLOCK_ELEMENT_ID');

		$entitySProps = \Bitrix\Main\Entity\Base::compileEntity(
			$className,
			$sProps,
			['table_name' => sprintf('b_iblock_element_prop_s%s', $iblockId)]
		);

		return $entitySProps->getDataClass();
	}

	// вдогонку
	// кто-то может спросить зачем так извращаться?)
	// ну без primary нельзя в битре удалять
	// а primary нельзя задавать ибо его нельзя менять
	// что как бы грустно и не понятно зачем
	// вот и приходится извращаться когда это primary надо менять

	$query = "DELETE FROM " . $spdc::getTableName() . " ";
	$query .= "WHERE `IBLOCK_ELEMENT_ID` = " . $elementId;
	$query .= ";";

	$connection = \Bitrix\Main\Application::getConnection();
	$connection->query($query);
```

И не стоит забывать, что этот хак придется делать еще и с торговыми предложениями. Зато нам не надо копировать все свойства товара, ведь ид элемента остается таким же. Ну и нам открывается возможность вносить изменения в базу за количество запросов сравнимое с числом таблиц, а не числом элементов. Впрочем есть и много минусов.

<details>
	<summary>
	Мне кстати лень адаптировать код создания всех этих таблиц под статью, да и не так уж часто создавались свойства чтобы это оптимизировать, но если интересно:
	</summary>

```php
	private static function copyProp($propId) {
		if (!\CModule::IncludeModule("iblock")) {
			ShowError("Модуль iblock не установлен!");
			return;
		}

		// да-да, если у вас почему-то свойства обновляются часто, то нужно и это делать через групповой запрос.
		$prop = PropertyTable::getById($propId)->fetchRaw();

		if (!is_array($prop)) {
			throw new \Exception("no parent", 1);
		}
		$prop["IBLOCK_ID"] = self::$CATALOG_SKU_IBLOCK_ID;
		$prop["XML_ID"] .= "_copy";
		$prop["VERSION"] = (int) $prop["VERSION"];
		unset($prop["TIMESTAMP_X"]);
		unset($prop["ID"]);

		$res = PropertyTable::add($prop);
		if (!$res->isSuccess()) {
			throw new \Exception(implode($res->getErrorMessages()), 1);
		}

		$id = $res->getId(); // new prop id

		if ($prop["PROPERTY_TYPE"] == PropertyTable::TYPE_LIST && $prop["LIST_TYPE"] == PropertyTable::LISTBOX) {
			self::copyEnumRows($propId, $id);
		}


		/*
		Создаем колонки в бд
		SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
		WHERE table_name = 'b_iblock_element_prop_s22' AND COLUMN_NAME = 'PROPERTY_427';

		ALTER TABLE b_iblock_element_prop_s23
		ADD PROPERTY_ZXC decimal(18,4);

		Ну и конечно тут остается еще TODO: оптимизация, собирание запросов в кучу
		*/
		$query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS ";
		$query .= "WHERE table_name = 'b_iblock_element_prop_s" . self::$CATALOG_IBLOCK_ID . "' ";
		$query .= "AND COLUMN_NAME = 'PROPERTY_". $propId ."' ";
		$query .= ";";

		$connection = \Bitrix\Main\Application::getConnection();
		$res = $connection->query($query);
		$columnType = $res->fetchRaw()["COLUMN_TYPE"];

		$query = "ALTER TABLE b_iblock_element_prop_s" . self::$CATALOG_SKU_IBLOCK_ID . " " ;
		$query .= "ADD PROPERTY_". $id ." ". $columnType ."; ";

		$connection = \Bitrix\Main\Application::getConnection();
		$connection->query($query);

		// Повторяем для описаний свойств
		$query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS ";
		$query .= "WHERE table_name = 'b_iblock_element_prop_s" . self::$CATALOG_IBLOCK_ID . "' ";
		$query .= "AND COLUMN_NAME = 'DESCRIPTION_". $propId ."' ";
		$query .= ";";

		$connection = \Bitrix\Main\Application::getConnection();
		$res = $connection->query($query);
		$columnType = $res->fetchRaw()["COLUMN_TYPE"];

		if (strlen($columnType)) {
			$query = "ALTER TABLE b_iblock_element_prop_s" . self::$CATALOG_SKU_IBLOCK_ID . " " ;
			$query .= "ADD DESCRIPTION_". $id ." ". $columnType ."; ";

			$connection = \Bitrix\Main\Application::getConnection();
			$connection->query($query);
		}

		// у нас изменилась таблица, поэтому старый класс больше не подойдет
		self::setScuPropsTableChange();

		// далее идет работа с таблицей где свойство описывается.
		// не знаю почему в битре информация по свойствам разнесена в несколько таблиц, но вот так.

		// Это чтобы свойство участвовало в выборе у предложения
		$featureCollection = PropertyFeatureTable::query()
			->setSelect(["*"])
			->where("PROPERTY_ID", $id)
			->where("MODULE_ID", "catalog")
			->exec()->fetchCollection();

		if (is_null($featureCollection)) {
			$featureCollection = PropertyFeatureTable::createCollection();
		}

		foreach ($featureCollection as $feature) {
			if ($feature["FEATURE_ID"] == PropertyCatalogFeature::FEATURE_ID_OFFER_TREE_PROPERTY) {
				$treeFeature = $feature;
			}
			if ($feature["FEATURE_ID"] == PropertyCatalogFeature::FEATURE_ID_BASKET_PROPERTY) {
				$basketFeature = $feature;
			}
		}
		if (!$treeFeature) {
			$treeFeature = PropertyFeatureTable::createObject();
			$treeFeature["FEATURE_ID"] = PropertyCatalogFeature::FEATURE_ID_OFFER_TREE_PROPERTY;
			$treeFeature["PROPERTY_ID"] = $id;
			$treeFeature["MODULE_ID"] = "catalog";

			$featureCollection[] = $treeFeature;
		}
		if (!$basketFeature) {
			$basketFeature = PropertyFeatureTable::createObject();
			$basketFeature["FEATURE_ID"] = PropertyCatalogFeature::FEATURE_ID_BASKET_PROPERTY;
			$basketFeature["PROPERTY_ID"] = $id;
			$basketFeature["MODULE_ID"] = "catalog";

			$featureCollection[] = $basketFeature;
		}

		$treeFeature["IS_ENABLED"] = "Y";
		$basketFeature["IS_ENABLED"] = "Y";

		$featureCollection->save();

		// TODO: add/update Prop Id to cache
		return $id;
	}
```
</details>

## Модели

Обратная совместимость битрикса это палка о двух концах. Старое ядро было до того как ORM стало популярным. Это значит там все идет без моделей. Также это значит что для инфоблоков и пользователей полноценного ORM нету. Серьезно, для самых частых вещей ORM нарушает обратную совместимость, а пользователях вас вообще пошлют при попытке сохранить пользователя. Раньше код для общения с базой лежал в /bitrix/modules/moduleName/classes/general\|mysql и там функции типа вездесущего getList составляли текст SQL запроса.

Из плюсов этого подхода битрикс мог менять структуру базы данных как хотел, незаметно для разработчиков. Однако годы показали что изменять структуру базы данных не особо хочется да и ORM это прикольно.

И поэтому, читающий друг, я поделюсь с тобой парой ласковых про ORM битрикса.

### ORM для инфоблоков.

Про это есть куча статей, тот же [mr.cappuccino](https://mrcappuccino.ru/blog/post/iblock-elements-bitrix-d7) просто обязателен к добавлению в закладки. Так что особо останавливаться на инфоблоках я не буду. Но обозначу некоторые моменты. Хотя бы то, что для работы ORM надо будет включить это все в настройках, нагуглишь где. Также xDebug может сыграть с тобой злую шутку, ведь если ты ошибешься в названии свойства и захочешь получить что-то чего в базе нету, то он уйдет в бесконечную вложенность. В общем на проде все работает, а локалка падает. И для кучи типов свойств придется гуглить как их получить. Что-то не нагуглится в принципе.

А еще дорогой друг вот тебе возможность работать с разделами через ORM.

```php
	use \Bitrix\Main\Entity\Query;
	use \Bitrix\Iblock\Model\Section;

	$arSections = (new Query(Section::compileEntityByIblock(IBLOCK_ID)))
		->setSelect([
			"ID",
			"NAME"
			"UF_PROPERTY_WORKS_TOO",
		])
		->where("DEPTH_LEVEL", 1)
		->where("UF_PROPERTY_WORKS_TOO", true)
		->setLimit(20)
		->setCacheTtl(60)
		->exec()->fetchAll();
```

Это гуглится несколько сложнее, и не каждый раз ведь получать разделы через элементы, верно?

### ORM для таблиц

Чаще, конечно ORM встречается если ты полезешь за пределы интернет магазина. В общем в какой-то момент разработчики битры устали придумывать новый порядок полей в getList для нового модуля и завели `\Bitrix\Main\ORM\Data\DataManager` и в результате, если у тебя есть таблица в базе, то можно описать ее модель как во взрослых фреймворках. Выглядеть это будет примерно так:

```php
	use Bitrix\Main\ORM\Data\DataManager;
	use Bitrix\Main\ORM\Fields;

	class SomeNameEndingOnWordTable extends DataManager
	{
		public static function getTableName()
		{
			return 'sql_table_name';
		}

		// А тут возвращается массив полей
		public static function getMap()
		{
			return [
				// какой-то ключ, в битре почти всегда ID
				(new Fields\IntegerField('ID', []))
					->configurePrimary(true)->configureAutocomplete(true),

				// типы полей включают, но не ограничиваются
				(new Fields\StringField('STRING_COL_NAME', [])),
				(new Fields\TextField('TEXT_COL_NAME', [])),
				(new Fields\IntegerField('INT_COL_NAME', [])),
				(new Fields\DatetimeField('AND_OTHER_TYPES', [])),

				// связь 1:1 и N:1
				(new Fields\IntegerField('COL_WITH_EXTERNAL_ID', [])),
				(new Fields\Relations\Reference(
					'PROP_NAME_FOR_OTHER_MODEL',
					OtherModelTable::class,
					\Bitrix\Main\ORM\Query\Join::on('this.COL_WITH_EXTERNAL_ID', 'ref.ID')
				))->configureJoinType('left'),

				// связь 1:N может принимать разный вид, про это позднее в ORM для хайлоадов
				(new Fields\Relations\OneToMany(
					"NAME",
					OtherModelTable::class,
					"REFERENCE_NAME"
				))->configureJoinType('inner')
					->configureCascadeDeletePolicy(
						Fields\Relations\CascadePolicy::FOLLOW
					)


				// Для файлов заодно можно сразу добавлять вычисление SRC прямо в модель
				(new Fields\IntegerField('UF_FILE', [])),
				(new \Bitrix\Main\ORM\Fields\Relations\Reference(
					"FILE",
					\Bitrix\Main\FileTable::class,
					\Bitrix\Main\ORM\Query\Join::on('this.UF_FILE', 'ref.ID')
				))->configureJoinType('left'),
				(new \Bitrix\Main\ORM\Fields\ExpressionField(
					'FILE_SRC',
					"CONCAT('/upload/', %s, '/', %s)",
					array('FILE.SUBDIR', 'FILE.FILE_NAME')
				)),
			];
		}
	}

	// используется это, например, в виде query

	SomeNameEndingOnWordTable::query()
		->setSelect([
			// "*", // если надо все, но без референсов и вычисляемых полей
			"ID",
			"ALIAS" => "STRING_COL_NAME",
			"PROP_NAME_FOR_OTHER_MODEL.SOME_PROP",
			"FILE_SRC",
		])
		->where("ID", 123)
		->setOrder(["ID" => "ASC"])
		->setLimit(20) // да-да, если я выбираю ИД 123, то мне вернут одно значение, не души)
		->exec()->fetchAll();
```

### ORM для хайлоадов

Ну а главное место где блеск и нищета ORM заметней всего, это хайлоады. Да-да, хайлоады, ты правильно прочитал. Раз хайлоады это таблицы, то ничто, ~~кроме здравого смысла,~~ не мешает нам описать их в таком виде. Точнее именно это делает битрикс когда ты делаешь что-то в стиле:

```php
	$rsData = \Bitrix\Highloadblock\HighloadBlockTable::getList(array(
		'filter' => ['NAME'=>'HighloadName']
	));
	if ( !($arData = $rsData->fetch()) ){
		// $this->AbortResultCache();
		ShowError("хайлоадблок не найден");
		return;
	}
	$Entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arData);
	$DataClass = $Entity->getDataClass();
```

По факту битрикс на лету составляет эти модели из базы, так что можно сократить пару запросов, лишиться возможности не следить за изменением базы, а заодно расширить эти модели чем-то, что нужно проекту. Хотя, вообще говоря, в `$DataClass` строка - название класса, так что никто не мешает просто `extend`нуть его под свои нужды. Хотя на мой вкус модели приятней, ведь тогда при расхождении модели в коде и в базе можно пнуть виновного.

Так что если описать хайлоад как мы делали выше, то много чего будет работать, и даже не нужно будет подключать модуль хайлоадов. Однако кое-что, конечно пойдет не так, мы ведь про битрикс говорим. И тут стоит поглядеть как битрикс хранит множественные свойства в хайлоадах. Хотя нет, сначала надо упомянуть что без множественных свойств всегда можно обойтись, связь 1:N - храним внешний идентификатор там где N, ну а M:N через дополнительную таблицу (хайлоад) также как упомянутые сверху `b_iblock_element_property`, `StoreProductTable`, `b_iblock_section_element` в общем как везде где надо соединить две плоские таблицы как много-к-многим, просто добавь третью.

Однако заводить отдельный хайлоад для каждого множественного свойства не очень удобно, и есть хак. Вместо `Bitrix\Main\ORM\Data\DataManager` использовать `\Bitrix\Highloadblock\DataManager`. Но, мой милый друг, это битрикс и за все приходится платить. Во-первых тебе нужно будет поглядеть, на то, какие таблицы у тебя в итоге появятся, ибо множественное свойство для хайлоада это отдельная таблица, видимо для скорости фильтрации. Во-вторых значение этого свойства дублируется и в сам хайлоад в сериализованном массиве, видимо для скорости чтения. А заодно часть твоих полей, например для файлов начнет работать по другому, а именно не как числа, а так как работают хайлоады, обратная совместимость - это палка о двух концах.

В общем описание этого поля будет выглядеть как-то так:

```php
	// все это в том самом getMap
	(new Fields\ArrayField(
		'UF_FILES',
	))->configureSerializationPhp(),
	(new Fields\Relations\OneToMany(
		"FILES_UTM",
		AlbumsUtmUfFilesTable::class,
		"OBJECT"
	))
		->configureJoinType('inner')
		->configureCascadeDeletePolicy(
			Fields\Relations\CascadePolicy::FOLLOW
		)
```

А ну да, еще битрикс для хайлоада создает функцию

```php
	public static function getHighloadBlock()
	{
		return [
			"ID" => "123",
			"NAME" => "HlEntityName",
			"TABLE_NAME" => "sql_table_name",
		];
	}
```

Не знаю зачем, я просто сразу прописал ее создание в самописном создавателе моделей из хайлоадов и не парился.

Конечно, в модели можно вставлять валидаторы, дефолтные значения, эвенты в конце концов, которые, конечно, не будут работать через стандартный подход, если ты не извратишься и не напишешь стандартный эвент вызывающий твои кастомные. Документации на это все не особо много, и ее понятность оставляет желать лучшего (битрикс). Но к счастью всегда есть исходники. Просто залезь в код всех этих fields ну или посмотри в модулях битры как ими пользуются те, кто этот битрикс, так сказать, создавал.

<details>
	<summary>
		Пока не забыл, еще один хак. Он посвящен свойствам типа список, и их валидации. Почему-то в битре она несколько жестче чем надо, поэтому я зачастую переписываю ее.
	</summary>

```php
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Main\ORM\Data\DataManager;
	use Bitrix\Main\ORM\Fields;

	// наверное мне стоило наследовать класс и там что-то менять, но я обхожусь так.
	// А ну и локализацию можно выкинуть, не то чтобы она мешала.

	Loc::loadMessages(__FILE__);

	/**
	 * Class FieldEnumTable
	 *
	 * Fields:
	 * <ul>
	 * <li> ID int mandatory
	 * <li> USER_FIELD_ID int optional
	 * <li> VALUE string(255) mandatory
	 * <li> DEF bool ('N', 'Y') optional default 'N'
	 * <li> SORT int optional default 500
	 * <li> XML_ID string(255) mandatory
	 * </ul>
	 **/

	class FieldEnumTable extends DataManager
	{
		/**
		 * Returns DB table name for entity.
		 *
		 * @return string
		 */
		public static function getTableName()
		{
			return 'b_user_field_enum';
		}

		/**
		 * Returns entity map definition.
		 *
		 * @return array
		 */
		public static function getMap()
		{
			return [
				new Fields\IntegerField(
					'ID',
					[
						'primary' => true,
						'autocomplete' => true,
						'title' => Loc::getMessage('FIELD_ENUM_ENTITY_ID_FIELD')
					]
				),
				new Fields\IntegerField(
					'USER_FIELD_ID',
					[
						'title' => Loc::getMessage('FIELD_ENUM_ENTITY_USER_FIELD_ID_FIELD')
					]
				),
				new Fields\StringField(
					'VALUE',
					[
						'required' => true,
						'validation' => [__CLASS__, 'validateValue'],
						'title' => Loc::getMessage('FIELD_ENUM_ENTITY_VALUE_FIELD')
					]
				),
				new Fields\BooleanField(
					'DEF',
					[
						'values' => ['N', 'Y'],
						'default' => 'N',
						'title' => Loc::getMessage('FIELD_ENUM_ENTITY_DEF_FIELD')
					]
				),
				new Fields\IntegerField(
					'SORT',
					[
						'default' => 500,
						'title' => Loc::getMessage('FIELD_ENUM_ENTITY_SORT_FIELD')
					]
				),
				new Fields\StringField(
					'XML_ID',
					[
						'required' => true,
						'validation' => [__CLASS__, 'validateXmlId'],
						'title' => Loc::getMessage('FIELD_ENUM_ENTITY_XML_ID_FIELD')
					]
				),
			];
		}

		/**
		 * Returns validators for VALUE field.
		 *
		 * @return array
		 */
		public static function validateValue()
		{
			return [
				new Fields\Validators\LengthValidator(null, 255),
			];
		}

		/**
		 * Returns validators for XML_ID field.
		 *
		 * @return array
		 */
		public static function validateXmlId()
		{
			return [
				new Fields\Validators\LengthValidator(null, 255),
			];
		}
	}
```
</details>


## Компоненты

Поговорим о компонентах. Мой эрудированный друг, я надеюсь, ты уже знаешь о том, где хранятся компоненты, о структуре файлов компонентов, чем `class.php` отличается от `component.php`, и чем `result_modifier.php` отличается от `component_epilog.php`.

В общем не о совсем же банальностях говорить после всех этих лет. Так что поговорим о фишечках.

### Наследование
И первая фишечка - это банальное наследование. Большинство компонентов сейчас - это классы. Классы можно наследовать. Все ведь очевидно. Взять, например, такой компонент как оформление заказа. Это огромный компонент за глубокое изучение которого платить никто не хочет. А вам дали задание в стиле "хотим чтобы при регистрации запоминались не только фамилия и имя, но еще и отчество". Простая вроде просьба. Однако сесть на оба стула приятней, чем отредактировать оформление заказа. Впрочем, а что если скопировать не весь компонент, а просто:

```php
	CBitrixComponent::includeComponentClass("bitrix:sale.order.ajax")

	class MySaleOrderAjax extends \SaleOrderAjax
	{
		// ну а тут мы поискав new CUser и выбрав тот который с ->Add перепишем содержащий его метод
		protected function registerAndLogIn($userProps)
		{
			// причем можно даже не особо переписывать, ведь создавать события можно на лету,
			// и тут будет будет событие на добавление пользователя которое нужно только в этом месте.
			// а за ним вызов оригинального метода.
		}
	}
```

<details>
	<summary>
		Ну или список заказов, куда захотели добавить разные сортировки и фильтрацию, да и вообще почему бы не сделать его обновляемым через аякс. Это все конечно если мы верим что список заказов это очень важная фигня. Но если за это платят, то почему бы и нет.
	</summary>

	И ведь мы не делаем особо много лишних движений. Мы взяли готовый компонент, не стали выкидывать из него лишнее, но дописали нужное. Кстати про то, почему он будет работать через аяксы будет ниже, ведь для этого еще надо дописать соответствующий js. Скорее друг тут тебе стоит взглянуть на `prepareFilter` ибо именно туда мы допиливаем отсутствующую в стандартном компоненте фильтрацию.

```php
	use Bitrix\Sale;
	use	Bitrix\Sale\Cashbox\CheckManager;
	use Bitrix\Main\Engine\ActionFilter\Authentication;
	use Bitrix\Main\Engine\ActionFilter\Csrf;
	use Bitrix\Main\Engine\Contract\Controllerable;
	use Bitrix\Main\Errorable;
	use Bitrix\Main\ErrorCollection;

	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

	BitrixComponent::includeComponentClass("bitrix:sale.personal.order.list");

	class CBitrixPersonalOrderListComponentEx extends CBitrixPersonalOrderListComponent
		implements Controllerable, Errorable
	{
		/**
		 * Sort field for query
		 *
		 * @var string field
		 */
		public $sortBy = false;

		/**
		 * Sort direction for query
		 *
		 * @var string order: asc or desc
		 */
		public $sortOrder = false;


		/** @var ErrorCollection */
		public $errorCollection;

		public function getErrors() {
			return $this->errorCollection->toArray();
		}

		public function getErrorByCode($code) {
			return $this->errorCollection->getErrorByCode($code);
		}

		public function onPrepareComponentParams($arParams) {
			$this->errorCollection = new ErrorCollection();

			if ($arParams["SAVE_IN_SESSION"] != "Y") {
				$arParams["SAVE_IN_SESSION"] = "N";
			}

			return parent::onPrepareComponentParams($arParams);
		}

		/**
		 * Перегружаем фильтрацию ибо у нас столько фильтров просто нету
		 */
		public function prepareFilter () {
			global $USER;

			$arFilter = array();
			if ($this->arParams["ID"] > 0) {
				$arFilter["ID"] = $this->arParams["ID"];
			}
			$arFilter["USER_ID"] = $USER->GetID();
			$arFilter["LID"] = SITE_ID;

			if (isset($_REQUEST['EXTERNAL'])) {
				if (is_array($_REQUEST["EXTERNAL"]) && !empty($_REQUEST["EXTERNAL"])) {
					$arFilter["EXTERNAL_ORDER"] = [];
					if (in_array(0, $_REQUEST["EXTERNAL"])) {
						$arFilter["EXTERNAL_ORDER"][] = "Y";
					}
					if (in_array(1, $_REQUEST["EXTERNAL"])) {
						$arFilter["EXTERNAL_ORDER"][] = "N";
					}
				}
			}

			if (isset($_REQUEST['STATUS'])) {
				if (is_array($_REQUEST["STATUS"]) && !empty($_REQUEST["STATUS"])) {
					// $allowedStatuses = array_column($this->arResult["STATUSES"], "ID");
					$statuses = array_intersect($_REQUEST["STATUS"], ["N", "F", "DF", "DN"]);
					if (!empty($statuses)) {
						$arFilter["STATUS_ID"] = $_REQUEST['STATUS'];
					}
				}
			}

			// Добавляем фильтр по дате заказа
			if (isset($_REQUEST['DATE']) && mb_strlen($_REQUEST['DATE']) > 0) {
				$date = $_REQUEST['DATE'];
				// "10.09.2024 - 21.09.2024"
				// "10.09.2024"
				$parts = explode(" - ", $date);
				if (count($parts) > 1) {
					$dateFrom = new \Bitrix\Main\Type\DateTime($parts[0], "d.m.Y");
					$dateTo = new \Bitrix\Main\Type\DateTime($parts[1], "d.m.Y");
				} else {
					$dateFrom = new \Bitrix\Main\Type\DateTime($date, "d.m.Y");
					$dateTo = new \Bitrix\Main\Type\DateTime($date, "d.m.Y");
				}
				$dateTo->add("1 day");

				$arFilter[">=DATE_INSERT"] = $dateFrom;
				$arFilter["<=DATE_INSERT"] = $dateTo;
			}

			// И фильтр по сумме цены заказа.
			if (isset($_REQUEST['PRICE_FROM']) && ((float) $_REQUEST['PRICE_FROM'] > 0)) {
				$arFilter[">=PRODUCTS_SUM"] = (float) $_REQUEST['PRICE_FROM'];
			}

			if (isset($_REQUEST['PRICE_TO']) && ((float) $_REQUEST['PRICE_TO'] > 0)) {
				$arFilter["<=PRODUCTS_SUM"] = (float) $_REQUEST['PRICE_TO'];
			}

			$this->filter = $arFilter;
		}

		// это метод выбирающий какие параметры этого компонента шифровать для использования в аяксе
		protected function listKeysSignedParameters()
		{
			return [
				"PATH_TO_DETAIL",
				"PATH_TO_CANCEL",
				"PATH_TO_CATALOG",
				"PATH_TO_COPY",
				"PATH_TO_BASKET",
				"PATH_TO_PAYMENT",
				"SAVE_IN_SESSION",
				"ORDERS_PER_PAGE",
				"SET_TITLE",
				"ID",
				"NAV_TEMPLATE",
				"ACTIVE_DATE_FORMAT",
				"HISTORIC_STATUSES",
				"ALLOW_INNER",
				"ONLY_INNER_FULL",
				"CACHE_GROUPS",
				"CACHE_TIME",
				"CACHE_TYPE",
				"DEFAULT_SORT",
				"RESTRICT_CHANGE_PAYSYSTEM",
				"REFRESH_PRICES",
			];
		}

		// метод который определит какие методы будут доступны через аякс и что проверить перед их вызовом
		public function configureActions(): array {
			return [
				'getResult' => [
					'prefilters' => [
						new Csrf(),
						new Authentication(),
					],
				],
			];
		}

		// доступный через аякс метод, позволяющий обновить лишь список заказов
		public function getResultAction(string $by, string $order) {
			$_REQUEST["by"] = $by;
			$_REQUEST["order"] = $order;
			$_GET = [
				"by" => $by,
				"order" => $order,
			];
			// $_SERVER["REQUEST_URI"] = "/history/"; // сюда нужный урл.
			$this->arParams["AJAX_MODE"] = "Y";
			ob_start();
			$this->executeComponent(); // вызываем основную часть компонента
			return ob_get_clean(); // возвращаем получившийся html
		}
	}
```
</details>

### Аякс в компонентах.

Если мой любознательный друг ты разворачивал предыдущий спойлер, то увидел `implements Controllerable, Errorable`. Это, мой коллега по несчастью, фигня для вызова методов компонента из фронтэнда. Если глянуть совсем простой js:

```javascript
	let body = new FormData()

	// обязательно проверь что работает не под админом BX.bitrix_sessid()
	// ну или вставь сессию каким-то другим способом.
	// ну или не вставляй если xss в данном месте не страшна
	// хотя лучше все же вставь, xss может сейчас не страшна, но потом...
	body.append("sessid", window.BX.bitrix_sessid())
	// ну и конечно тебе надо добавить в body все нужные в методе переменные.

	// тут вместо className должен быть компонент в стиле bitrix:sale.personal.order.list
	var className = "className"
	// а тут название метода без слова Action, например в случае со списком заказов выше getResult
	var action = "methodName"

	let getParams = `?mode=class&c=${className}&action=${action}`

	// из await тебе придется сделать это внутри async функции, ну или переходи на коллбеки через .then
	let res = await fetch(
		"/bitrix/services/main/ajax.php" + getParams,
		{
				method: "POST",
				body
		}
	).then(r => r.json())

	if (res.status === "error") {
		console.error(res)
	}

	// а тут работа с полезной нагрузкой из res.data
```

На стороне бека все как в примере выше. Ладно не все. Там на стороне бека есть еще шифровка параметров компонента, чтобы переслать их обратно. Да ты правильно понял, параметры компонента идут от юзера. Да в зашифрованном виде, но придумавший это человек, наверное, пришел с курсов. В общем эти шифрованные параметры в шаблоне можно добавить как

```javascript
body.append('signedParameters', '<?= $this->getComponent()->getSignedParameters() ?>')
```

И тогда компонент их расшифрует и у тебя появится к ним доступ. В общем не делай так, и если что-то пошло не так, то сразу переводи стрелки на разработчиков битры. И точно не делай так в местах где замешаны деньги.

На бэке тебе потребуется сказать что класс `implements Controllerable, Errorable` объявить все, что нужно объявить после этого:

```php
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;

class ComponentClassName extends CBitrixComponent implements Controllerable, Errorable {
	public $errorCollection;

	public function getErrors() {
		return $this->errorCollection->toArray();
	}

	public function getErrorByCode($code) {
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * ajax/fetch configuration
	 * Тут ты говоришь какие методы доступны из фронта
	 * и что нужно проверить перед их вызовом
	 * в данном случае
	 * Csrf - это sessid защита от xss, умоляю если не знаешь про xss - узнай
	 * Authentication - очевидно что авторизованность пользователя
	 * Ну а вообще смотри Bitrix\Main\Engine\ActionFilter для готовых или своих заготовок
	 * @return array
	 */
	public function configureActions(): array {
		return [
			'ajaxMethodInThisClass' => [
				'prefilters' => [
					new Csrf(),
					new Authentication(),
				],
			],
		];
	}

	/**
	 * Сам метод будет оканчивать на слово Action,
	 * но ты, друг, конечно волен поменять это слово в меру своей неизмерной фантазии
	 * кстати тип аргументов и правда проверяется, и аякс вернет ошибку если ты с ним ошибешься
	 * впрочем не то чтобы число и строка сильно отличались
	 */
	public function ajaxMethodInThisClassAction(int $id, bool $checked) {
		// return и throw пойдут в js так что аккуратней.
		// а еще можно добавлять ошибки в $errorCollection что тоже пойдет в js
		// иначе зачем нам этот Errorable и бонус $errorCollection в том
		// что он не throw и там мы можем ошибки копить и сообщать о нескольких сразу
		// юзеры ведь любят получать уведомления о том насколько они не правы.
	}
}
```

Я как-то сделал обертку вокруг html форм, чтобы подписав класс `js-ajax-form` я мог запихнуть `data-component` и `data-method` отправлять форму по аяксу сразу в компонент, без лишнего js для каждой такой формы. Но там как-то многовато кода, а мой эндшпиль хоть и малюсенький по моим меркам, уже весьма длинный.

Да, я знаю что я использовал `fetch`, а не `$.ajax`, засуди меня\) Старая привычка.

Стоит упомянуть, что отправлять подобные запросы можно не только к компонентам, но еще и к модулям, и может еще к чему. Правда, с модулями придется повозиться, ведь там описание проверок из `configureActions` находится в куда менее очевидных местах. А еще нужен файл `.settings.php`, где ты опишешь какой файл\(ы\) открывать и наследовать там придется `\Bitrix\Main\Engine\Controller` где есть свои `getDefaultPreFilters` включающие `ActionFilter\Authentication` что означает по умолчанию это не работает если не авторизовался. В общем мороки много, выигрыша не очень, но иногда оно полезно. Как пример, если ты сделал общую модель Юзер:ЭлементИнфоблока наследуешь от нее модели в стиле список желаемого, список покупок, прочитать далее, а в модуль занес работу со всем этим. Ну и в js модуля написал либу которая со всем этим работает. Да, новый друг, десять лет - это очень долго.

### Малоизвестное про общеизвестное в компонентах

Окей, я не хотел говорить про такие вещи как `StartResultCache` и `IncludeComponentTemplate`. Если ты, прекрасный друг, пишешь на битриксе то ты писал эти слова слишком много раз.

Однако знал ли ты, что `IncludeComponentTemplate` позволяет выбирать папку откуда брать шаблон? Мой гениальный друг, ты уже догадался, как это можно использовать? Например сделать папку `views` где хранятся все шаблоны всех компонентов, чтобы сделать битрикс хоть немного похожим на взрослые фреймворки. Ну или посмотри на это в совмещении с наследованием. Ты сделал базовый CRUD компонент для абстрактной таблицы или хайлоада или инфоблока, и хочешь отнаследовать его для какого-то конкретного? Что же у тебя будет конструкция в стиле:

```php
	public function template($templatePage = "", $customTemplatePath = "") {
		$templatePath = $_SERVER["DOCUMENT_ROOT"] . "/" . $this->getPath();
		$templatePath .= "/templates/" . $this->getTemplateName() . "/";
		$templatePath .= $templatePage ?? "template";
		$templatePath .= ".php";

		if (file_exists($templatePath)) {
			$this->includeComponentTemplate($templatePage, $customTemplatePath);
		} else {
			// $customPath = parent::getPath();
			$customPath = "/local/components/namespace/your.abstract.component.name/templates/";
			$customPath .= $this->getTemplateName();

			$this->includeComponentTemplate($templatePage, $customPath);
		}
	}
```


Как ты заметил `includeComponentTemplate` принимает два аргумента, первый - это имя файла вместо `template.php`. Второй - это папка где его брать. И оба дают кучу возможностей. То же наследование шаблонов позволило мне сделать половину компонентов вообще без них. Без этого копирования кода и потом правок во всех скопированных местах.

Кстати, ты не устал от огромных шаблонов на 1000+ строк? Я устал. Сначала я начал использовать `include` и `require`, это сделало код куда приятней, но все еще дурно пахло. А потом я подумал что можно использовать `includeComponentTemplate` да еще и задавать массив используемых данных. Как делает `catalog.item` внутри `catalog.section`, но без отдельного компонента. Получилось что-то весьма простое, но полезное:

```php
	public function render ($template, $result = null) {
		if ($result !== null) {
			$oldResult = $this->arResult;
			$this->arResult = $result + ["_" => $oldResult];
		}
		$this->template($template);
		if ($result !== null) {
			$this->arResult = $oldResult;
		}
	}
```

Ладно сознаюсь, я назвал метод не `render`, а `includeComponentTemplateWithResult`. И он использует предыдущий метод `template`, но его можно заменить на `includeComponentTemplate`, просто перестанет работать наследование шаблонов. Да и сохранение оригинального результата можно выкинуть. Однако всякие шаблоны потом красиво распихиваются на файлы. Впрочем, это не последний шаг. Ведь, раз мы можем вызывать методы компонента из шаблона, то почему бы сразу не использовать компонент как контроллер, которым он и должен быть. Что мешает в шаблоне написать что-то в стиле:

```php
	<?
	foreach ($arResult["ITEMS"] as $item) {
		$component->showListItem($item);
	}
	?>

	// а в компоненте

	public function showListItem($item) {
		$this->render("item/list", $item);
	}
```

Да в первый аргумент `includeComponentTemplate` можно пихать пути с папками, очень удобно. Размер файлов компонентов сокращается до половины экрана. Наследование позволяет менять только те элементы, которые действительно отличаются, а заодно экономит время на правке багов и вообще позволяет сделать легкое улучшение всего интерфейса в предсказуемом и понятно ключе.

Про `StartResultCache`. Эта та функция, которую запихивают в `if ($this->StartResultCache()) {/* тут кешируемый код */}`. Уже одно запихивание этого в `if` толсто намекает что метод вернет `true` или `false`. Если `true` то все просто, выполнится весь код. А что если `false`. Учитывая, что мне самому потребовалось много времени чтобы это принять, сейчас буду говорить очевидное. Ну, во-первых очевидно что код внутри `if` не выполнится. Однако при этом все равно произойдет много вещей. Выведется закешированный шаблон, который хранится где-то в бд и фс, заполнится `$arResult` подключатся правильно подключенные css, js и frame. В общем куча всего, но код внутри if не выполнится. Это значит все что не относится к вышеперечисленному не сработает. Самое банальное - это установка title и хлебных крошек. Кстати этот кеш еще можно переводить на разные движки бд, но сам особо не копался.

Еще у функции `StartResultCache` есть аргументы. Первый это `$cacheTime` если он `false`, то берется `intval($this->arParams["CACHE_TIME"] ?? 0);`. Далее идет интересный `$additionalCacheID` и собственно он позволяет делать отдельный кеш для каждого пользователя или каждой фазы луны, хотя можно просто пихать это в параметры компонента. Ну и третий это `$cachePath`, чтобы делать отдельные папки кеша каких-то весьма распухающих компонентов. Что конечно потребуется, если ты, друг, начнешь делать кеш зависимым от пользователя, фазы луны, или фильтра в каталоге.

И, молодой друг, если ты только начинаешь работать с битриксом, то пока не привыкнешь к кешу, держи его включенным, в том числе для себя, и сбрасывай после каждого изменения кода кешируемого кода, а потом тести без сброса. По мере твоего роста это перестанет быть нужным, но если ты только начал, то это кодекс вежливости. И не грусти, даже богам Эльдорадо говорили: `'To err is human'`.


## Модули

>В названии модуля **должна быть одна разделительная точка**. Серьезно. Твой модуль должен иметь название вида `foo.bar`, обычно это `companyname.modulename`. Иначе магии не произойдет.

Что можно с ними делать, кроме как копировать из проекта в проект? А можно на самом деле много чего. Например держать модели, про которые я рассказывал выше. Да и вообще держать классы, файлы которых будут автоматически подключаться через правильные `namespace`. А еще в них удобно хранить события и агенты. Или с их помощью добавлять что-то в админку. А еще хранить js и css, подключаемый через методы `CJSCore::Init([...])`. Ну и конечно держать твой класс с набором полезных функций и утилит. В общем это способ разгрузить файл `php_interface/init.php`, и при этом разместить все это в какой-то легкой для понимания структуре.

### install/index.php

Как же сделать модуль? Ну для начала создать папку в `local/modules` или `bitrix/modules`, но лучше всегда в `local`, чтобы ваш код не мешался с кодом битры. Далее внутри `install/index.php` с классом чье имя соответствует названию модуля и наследуется от `\CModule`. Что-то в стиле `class foo_bar extends \CModule {}`. Затем несколько методов и у вас готовый модуль.

```php
	class company_name_module_name extends \CModule {
		public function __construct() {
			$this->MODULE_ID = "company_name.module_name";

			$this->MODULE_VERSION = '1.0.0';
			$this->MODULE_VERSION_DATE = '2026-01-01 00:00:00';

			$this->MODULE_NAME = 'Название модуля';
			$this->MODULE_DESCRIPTION = 'Описание модуля';

			$this->MODULE_GROUP_RIGHTS = 'Y'; // сам не гуглил зачем.

			$this->PARTNER_NAME = "Авторство модуля";
			$this->PARTNER_URI = "https://ссылка/на.разработчиков";
		}

		public function DoInstall() {
			ModuleManager::registerModule($this->MODULE_ID); // после этого модулем можно пользоваться
		}

		public function DoUninstall() {
			ModuleManager::unRegisterModule($this->MODULE_ID);
		}
	}
```

Потом вам нужно будет установить ваш модуль. Это в админке Marketplace -> установленные решения. И если вы не видите там вашего модуля, то не забудьте про точку в середине названия папки вашего модуля.

### include.php

Еще вероятно потребуется файл `include.php` в корне модуля. Это аналог `init.php`, но только для вашего модуля. Не уверен, что он обязателен, однако туда можно запихать, например, пути к css и js.

```php
	$moduleJsFolder = "/local/modules/foo.bar/js/";
	$moduleCssFolder = "/local/modules/foo.bar/css/";

	CJSCore::RegisterExt("my_global_events", [
		"js" =>$moduleJsFolder. "global_events.js",
	]);

	CJSCore::RegisterExt("my_utils", [
		"js" => $moduleJsFolder. "custom_utils.js",
	]);

	CJSCore::RegisterExt("my_ajax_forms", [
		"js" => $moduleJsFolder. "ajax_forms.js",
		"css" => $moduleCssFolder. "ajax_forms.css",
		"rel" => ["my_utils", "my_global_events"]
	]);
```

И потом подключать их как `CJSCore::Init(["my_ajax_forms", ... ]);`. Ну или подключать другие модули, если ваш на них завязан.

### options.php

Еще есть файл `options.php`. Это файл, который позволит выводить что-то в админке, а именно в настройках модулей -> твой модуль. Вообще я выложу на гитхаб свои заготовки, но там скопированные из битриксовых модулей спагетти. В общем, если хочешь какие-то свои настройки модуля, подсмотри как это делает модуль битры. Ну или не подсмотри, а сделай что-то свое и имеющее смысл. Я туда часто размещаю кнопку удаления всех эвентов модуля, выполняющую метод, который я разметил в `install/index.php`:

```php
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
```

Ну и конечно кнопки по установке эвентов, установки таблиц или хайлоадов, или установку веб форм для форм обратной связи.

### lib/classname.php

Теперь мы подобрались к тому месту ради чего вообще вся эта возня с модулями. До папочки `lib`. Фишка этой папки в том, что разместив в ней файлик с нужным наименованием, мы можем подключить его без `include`. Например у нас есть модуль `foo.bar` и мы сделали в папке `lib` файлик `utils.php`, в него `namespace Foo\Bar;` и далее `class Utils {...}`. И все. Вжуш, шмагия. Теперь если мы установили модуль в админке, то на нашем проекте мы можем:

```php
	if (!CModule::IncludeModule("foo.bar")) {
		ShowError("Модуль foo.bar не установлен!");
		return;
	}

	Foo\Bar\Utils::someMethod(); // все это ради этой строчки.
```

### Итого

Как правило у меня есть на проекте модуль mycompany.main в котором все нужные хайлоады описаны как модели, описаны пути к css и js, и лежит код событий. Зачастую я подключаю этот модуль в `init.php`, но не обязательно. Также в этом модуле у меня бинарник wkHtmlToPdf, ведь почему-то всем нужно генерировать pdf и плевать, что это можно делать через печать страницы. В утилиты я скидываю всякие обертки для надоевших функций, типа ресайза изображений, вывода числительных форм слова и прочее, прочее.

В общем, креативный друг, ты ограничен своей фантазией. Еще кстати эти модули по идее можно закидывать на маркетплейс ну или хотя бы на гитхаб и массово обновлять, но до этого я не дошел.

## Таблицы

В битриксе есть куча sql таблиц. Рассматривать их все - не ко мне. Однако за эти годы кое-что отложилось, и не поделиться этим с моим дорогим другом было бы жадностью.

К счастью битрик следовал каким-то правилам наименований таблиц. Например они как правило начинаются на `b_`, если какой-то извращенец не указал при установке что-то другое, и это не хайлоад. Да таблицы хайлоадов не имеют этой приставки, что весьма странно. Также после `b_` почти всегда идет `module_name_`, но опять-таки это не относится к главному модулю да и название модуля не всегда узнаваемо. Таблицы связей обычно имееют в названии обе сущности. Такие себе правила, но имеем что имеем.

---

Таблица `b_file`. Тут хранятся ~~файлы~~, пути к файлам, ведь файлы в бд ~~хранят разве что 1Сники~~ хранить плохо. Пути к файлам хранятся в двух колонках `SUBDIR` и `FILE_NAME`, и если глянешь выше в модели то увидишь вычисляемое поле `CONCAT('/upload/', %s, '/', %s)` с подставлением этих колонок. Тут же у файла есть и оригинальное название и внешний идешник.

Еще туда же таблицы `b_file_hash` и `b_file_duplicate`. В первой очевидно хеш и размер в придачу, а вторая по первой ищет дубликаты, чтобы возможно удалять их. Но не рассчитывай на это из коробки, или наоборот рассчитывай, обновления они такие. В общем если на локалке перестали заливаться *некоторые* картинки, и при этом это те картинки, которые кто-то уже заливал на проде, то эти таблицы это место куда тебе пойти.

---

Таблица `b_user`. Вот сюда идут пользователи. Тут имена, соленые хеши паролей, явки. При этом работать с этой таблицей через d7 битры можно только на чтение, спасибо и на этом. Таблица `b_user_group` связывает юзеров и группы (`b_group`), хочешь добавить себя в админов, то тебе в нее.

---

Сразу стоит отличить таблицу `b_user_field` вопреки ожиданиям эта таблица не связывает пользователей и поля. Эта таблица описывает так называемые *пользовательские свойства*. А это весьма много чего. Например это свойства для пользователей, а еще для хайлоадов, блогов, форумов, разделов инфоблоков. Наверное все свойства, кроме свойств элементов инфоблоков. В общем такая табличка для которой колонки других табличек это строка. `ENTITY_ID` это как раз юзер/хайлоад/раздел в общем то к чему добавлено. А `FIELD_NAME` это название свойства. Причем все эти `FIELD_NAME` начинаются с `UF_`. Ну битрикс же. Врочем, взволнованный друг, не напрягайся, все равно эта таблица нужна для грязных хаков. Когда производительность из коробки оказывается недостаточной и нужно ускорить что-то конкретное. Ну или если ты делаешь утилиту, которая делает модели из хайлоадов.

---

Таблицы `b_iblock`, `b_iblock_element`, `b_iblock_property`, `b_iblock_section`. Это инфоблоки, элементы инфоблоков, свойства инфоблоков, разделы. Значения свойств как я писал выше зависят от версии инфоблока и хранятся либо в `b_iblock_element_property` либо в `b_iblock_element_prop_[s|m]123`, врочем сами эти таблицы тебе не особо нужны, все это опять таки для хаков, оптимизаций, проверок. Еще есть `b_iblock_iproperty` в них всякие настройки SEO. В любом случае запомни это слово **iproperty** ведь оно будет нужно в `component_epilog.php`. Упомянутая выше `b_iblock_section_element` позволяет соединять элементы и разделы как M:N.

`b_iblock_property_enum` Нужна для свойств типа список для инфоблоков. Остальные списки в другом месте.
`b_iblock_property_feature` Это таблица для маленьких галочках внизу свойств свойств (я не ошибся) называемых в стиле "показывать в детлке", "показывать в списке", "добавлять в корзину". Не очень понятно зачем была нужна отдельная таблица и почему что-то относящееся к каталогу находится вне его модуля. А точно, Битрикс.

Однако если ты ищешь что-то что делает из элемента инфоблока товар, то этого пока не было.

`b_catalog_product` **ID** в этой таблице совпадает с **ID** в `b_iblock_element`. И это та таблица, которая расширяет элемент инфоблока полями для товара. Тут тип цены, тип количества, да и само количество, тип налогов. В общем таблица помогающия битриксу продавать разные редакции с разной функциональностью.

`b_catalog_price` Тут цены. Ведь один товар - много цен.
`b_catalog_store_product` Тут остатки, если используем склады. Сами склады в `b_catalog_store`.

В общем в `b_catalog_*` много чего относящегося к каталогу. Валюты, налоги, инфоблоки, типы цен. И еще куча таблиц, где вероятно не будет записей.

Казалось бы вот теперь инфоблок стал каталогом и все для магазина готово, но как бы не так. Еще есть `b_sale`.

`b_sale_fuser` Это попытка идентифицировать не авторизованных, да и авторизованных пользователей. Вы ведь можете добавлять в корзину товары без регистрации на сайте, и корзина не сбрасывается. Ну вот значит вы в подобной таблице.

`b_sale_basket` упомянул корзину, и вот первое на что наткнешься. Вот только это не корзина, а товар в корзине. Кстати тут можно менять его цену, скидку, количество и кучу другой фигни. Даже имя товара тут есть, вероятно для оптимизационных целей.

А сама корзина это заказ: `b_sale_order`. Если взять аналогию с физическим супермаркетом, то fuser - человек, order - тележка, basket - молоко в тележке. Да названия не очень очевидные, а также связи внутри избыточные, но это ведь битрикс. В таблице заказа есть такие колонки как статус оплаты, отмены, доставки. Однако свойства заказа не там. Точнее комментарий здесь, а остальное ниже.

`b_sale_order_props` и `b_sale_user_props_value` Хранят свойства заказа и их значения. Туда ваше имя, телефон, инн. Все кроме комментария, который выше. Значения свойств привязаны к профилям покупателей.

`b_sale_user_props` Профили покупателей. Это то, что позволяет проще заказывать на разные адреса, людей или компаний не заводя отдельных пользователей для этого всего. Проще говоря папки для свойств заказа пользователя, если это просто. Заодно тут же определяется тип лица юридическое или физическое. Еще есть конечно таблица этих лиц, но я как-то не встречал чего-то третьего.

`b_sale_discount` очень интересный зверь. Тут скидки. И хранятся они в виде PHP кода. Внутри может быть что-то в стиле

```php
	function (&$arOrder){
		\Bitrix\Sale\Discount\Actions::applyToDelivery($arOrder, [
			'VALUE' => -100.0,
			'UNIT' => 'P',
		]);
	};
```

Ты правильно понял, озадаченный друг. Мы можем делать любые скидки. Конечно черт бы побрал придумавшего хранить код в бд. Но сама возможность делать скидки произвольным кодом приятна. Правда делать это стоит правильно, нифига не через редактирование бд. А через создание своих классов и возможности настройки через админку. Заодно есть на настолько гибкая обертка, включающая в себя порядок применения и сочетания с другими акциями. Если у тебя задание сделать скидку по знаку зодиака, то результатом будет код позволяющий вставлять код в эту таблицу.

`b_sale_order_delivery` доставки, ведь на один заказ много доставок из коробки.
`b_sale_order_payment` оплаты и с ними как с доставками, никто не мешает оплатить один заказ пятью кредитками, бонусными баллами и наличкой. Да хоть телом, если законодательство не против, а оно обычно против ведь процент ндс своровать не получится.

`b_sale_status` статусы заказов, примечательно что ID не цифровой. Заодно есть цвета ).

Если `b_catalog_*` позволяет продавать, то `b_sale_*` позволяет покупать. Тут еще больше таблиц, и опять-таки на большинстве проектов большая часть из них не будет иметь ни строчки.

А еще где-то там же есть всякие местоположения, они ведь нужны для расчета стоимости доставки. Но я никогда не получал заданий заполнить их вручную, во всяком случае за разумную плату.

---

`b_search_content`, `b_search_stem` Нужны для поиска. Поиск по всему сайту пытается включить все возможные урлы, страницы, сущности. Лучше бы конечно это все разделили. Ведь обычно нужен только поиск по товарам и категориям. Часто для улучшения работы поиска пишется эвент чтобы искалось только по нужным полям:

<details>
	<summary>
		Кастомный индекс для каталога состоящий только из названия и ISO + ISO без спец символов
	</summary>

```php
	// AddEventHandler("search", "BeforeIndex", "onBeforeSearchIndex");
	function onBeforeSearchIndex($arFields) {
		if ($arFields["MODULE_ID"] == "iblock" && $arFields["PARAM2"] == CATALOG_IBLOCK_ID) {
			if (!CModule::IncludeModule("iblock")) {
				return $arFields;
			}

			$el = CIBlockElement::GetList(
				"",
				[
					"ID" => $arFields["ITEM_ID"],
					"IBLOCK_ID" => CATALOG_IBLOCK_ID
				],
				false,
				false,
				[
					"NAME",
					"PROPERTY_ISO"
				]
			)->Fetch();

			if (!$el || !strlen($el["PROPERTY_ISO_VALUE"])) {
				return $arFields;
			}

			$prop = $el["PROPERTY_ISO_VALUE"];
			$noSpProp = preg_replace('/[^A-Za-zА-Яа-я0-9]/', '', $prop);
			if ($prop != $noSpProp) {
				$prop = $prop . " " . $noSpProp;
			}

			$arFields["BODY"] = $prop;
			$arFields["TITLE"] = $el["NAME"];
		}
	}
```

	Все это служит для наполнения таблицы `b_search_content` нужной для поиска информацией. А эвентом, потому что не только программисты запускают переиндексацию. Хотя можно вместо нее написать что-то свое, с учетом знания таблиц. Или можно использовать что-то другое для поиска. Вообще часто в итоге я отказывался от поиска битры в пользу простых фильтраций. А еще с поиском когда-то был бак, ядро использовало колонку `rang` без взятия в кавычки, а mysql в какой-то версии добавил это как ключевое слово. Фиксится обновлением битры или правкой ядра.
</details>

---

`b_xml_tree_import_1c` сюда читаются файлы импорта из 1с. Лезть не понадобится, если прочитать раздел про импорт. Но так, для общего образования.

---

`b_option` тут настройки. Нет не так. Тут Настройки. В общем куча галочек, настроек модулей, полей в админке. Все что не вынесли куда-то лежит тут. Распределено по модулям, а далее все в куче. Одна строка - одна настройка. Тут и управление округлениями в магазине, настройки криптографии, настройки импорта из 1С, и даже настройки твоего, друг, модуля. Все просто: модуль -> название -> значение.

---

`b_module` список модулей. Могу ошибаться, но вроде установлен модуль или нет определяется именно наличем записи тут. Впрочем не стоит забывать про кеш, если делаешь хаки.

`b_module_to_module` а вот тут события. Точнее связи этих событий. В общем если ты сделал свой модуль, то на стандартное событие ты навешаешь событие своего модуля и после этого оно считается созданным.

`b_agent` агенты. Но вообще есть cron ну или какой-либо MQ.

---

`b_event` А тут отправленная почта, и не отправленная. В общем если почта не уходит, то эта таблица следит за тем, чтобы в итоге твой ящик взорвался от спама, когда все починишь.

`b_event_message` тут почтовые шаблоны.

`b_event_type` а тут типы почтовых шаблонов.

## Профили покупателей

Пользователь и покупатель это разные вещи и они всегда связаны как "один пользователь" - "много покупателей". У меня ушло несколько лет чтобы объяснить это менеджерам, и я все равно справился не до конца.

Зато зная про эти аякс методы ты, мой несравненный друг, можешь легко написать свой компонент, который будет легко управлять профилями. Не говоря о том, что зная в каких таблицах все это хранится можно использовать orm битры. Ищем `b_sale_user_props` и узнаем что ему соответствует класс `Bitrix\Sale\Internals\UserPropsTable` и также с `b_sale_user_props_value` находим класс `Bitrix\Sale\Internals\UserPropsValueTable` названия к счастью почти очевидные.

А да чтобы установить какой-то профиль профилем по умолчанию, нужно сделать его дату обновления максимальной. Что-то в стиле:

```php
	// беру все профили пользователя для примера
	$userProfiles = Bitrix\Sale\Internals\UserPropsTable::getList([
		"filter" => ['USER_ID' => $GLOBALS["USER"]->GetId()],
		"order" => ['DATE_UPDATE' => 'desc'],
	])->fetchCollection();

	// достаю нужный из коллекции
	$profile = $userProfiles->getByPrimary($someProfileId);

	// Синтаксический сахар на твой вкус
	// $profile->setDateUpdate(new Bitrix\Main\Type\DateTime());
	$profile['DATE_UPDATE'] = new Bitrix\Main\Type\DateTime();

	$profile->save();
```

В целом ничего сложного, если и правда взглянуть на таблицы.

## Компонент оформления заказа

Я говорю про страшный и ужасный `sale.order.ajax`. Обычно на редактирование этого компонента уходит где-то половина времени на весь проект. Ведь в этот компонент запихнули почти все. Кроме разве что выбора местоположения. Не очень последовательно, но кто запретит. И если мой бедный друг, тебе дали задание изменить оформление заказа, готовься, это будет специфично. Но лучше попытайся просто изменить верстку стандартного. В большинстве случаев это решит проблему куда быстрее и проще.

И так. Слон в комнате: это реактивный компонент. И я говорю не про react или vue. Я говорю про адскую смесь нативного js c js от битры. И ты ведь уже заметил что для js битры документации еще меньше чем для php? Продолжим, в шаблоне ты найдешь файлик `order_ajax.js` и кстати, если после его редактирования ничего не меняется, то значит загружается его минифицированная версия, ее можно удалить, ну или обновлять после каждой правки.

В этом файлике почти весь фронт оформления заказа. При этом сразу подготовлю тебя. В нем много, как бы сказать, много `BX.create()`. И если ты не испугался, то напрасно. Не знаю кто придумал эту обертку над `document.createElement` и почему бы не сделать что-то в стиле emmet. Да и зачем везде использовать `document.createElement`, видимо для поддержки какой-то версии ишачка. Но он там есть, и его там много. Пара строчек html будут создавать десятком строк js. Отчасти именно поэтому этот файлик такой большой.

Проходить по всему что в нем есть я не хочу, да и не могу, можно написать свой такой же где-то в 1к строчек, покрывающий 90%. Но если тебе нужны все 100%, а точнее если надо ставить всякие плагины на битру, типа сторонних доставок, оплат и т.п. То стандартный, с минимальной кастомизацией наше все. Даже классы зачастую лучше оставить прежними. Увы. Однако не запускать же тебя туда без ориентиров.

И первый ориентир: `sendRequest` очевидно отправляет что-то на сервер. Вызывается чуть реже чем всегда. Оформил заказ, сменил профиль или просто выбрал платежную систему, не важно. У нас ведь возможны очень гибкие скидки, вот и приходится постоянно отправлять запрос, и почти всегда после этого перестроить html. А еще купоны, местоположения, прочее.

Второй ориентир: `refreshOrder` тут и идет построение html, точнее тут из ответа сервера задается `this.result`, но все равно дальше будет `editOrder` который много раз вызовет `editSection`, которые и правда отвечают за html, шучу, там тоже матрешка.

Третий ориентир: `editSection` вообще в обычном `template.php` есть html выделенный комментариями в стиле `<!--	DELIVERY BLOCK	-->` а далее что-то вида `<div id="bx-soa-delivery" data-visited="false" class="bx-soa-section bx-active" ... >...</div>` ну вот с этим и работает этот `editSection` в него кидают эти дивы и по айдишнику он определяет что это за раздел и что с ним делать. Там внутри в свиче набор вызовов методов вида `this.editPropsBlock(active)` (данный отвечает за свойства заказа).

Четвертый: `show` и `fade` они тоже для перехода, впрочем если тебе надо одну страницу, то не сказать что они тебе нужны. ;)

Кстати если твое задание вывести все сразу на одной странице, то придется полезть во все эти разделы и сделать их всегда активными, вроде просто, но разделов несколько, как и способов менять их активность.

Отдельно стоит отметить местоположения. Для них для начала загляни в `getDeliveryLocationInput`.

А еще можешь просто в браузере находить классы, и искать их потом в коде. Файл монструозный, но ориентироваться в нем можно, я проверял.

## Корзина

Не знаю почему, но корзина использует пару других технологий. Маленькая корзина `sale.basket.basket.line` использует композит, а значит в первую загрузку нифига не загружает, но отправляет потом аяксик, который загрузит все потом. А `sale.basket.basket` использует js шаблоны и mustache. Не знаю зачем такой зоопарк, точнее знаю - битрикс. В общем скучно не будет.

Можно конечно дополнить это одним зверем. Не зря ведь я объяснял про модули, и аякс компоненты. Так вот есть еще и аякс модули, я про них упоминал. Туда можно запихнуть корзину и отдавать json. Заодно использовать его чтобы делать всякие штуки типа вместо кнопки "Купить" сделать кнопку "В корзине ндцать штук". Ну и всякие желаемые для динамических иконок тоже. Иначе придется делать кеш зависимым от пользователя, а это такое себе. Ну или доставать все эти данные из базы отдельно и заменять через регулярки, html это ведь строка. Кстати для работы всех этих кнопок в компоненте маленькой корзины есть js эвент `OnBasketChange`.

## Прочее

Чем больше я пишу, тем меньше мне хочется сказать. Кажется битрикс наконец-то отпускает меня. Бежит прочь из моей головы. Позволяет опускать кучу подробностей и тем.

Тем не менее еще есть моменты, которые хочется упомянуть. Такие как комплексный компонент каталога. Именно сам этот компонент. И самое банальное как добавить в него жадные пути. А делается это через копирование компонента и далее как и с умным фильтром придется добавить строчку `$engine->addGreedyPart("#YOUR_GREEDY_PATH#");` для не жадных путей все можно делать через параметры компонента. А еще скопировав можно несколько менять логику определения страниц и 404. Иногда полезно. И вообще без копирования сложно, если надо совместить поиск и фильтр на одной странице. Сам компонент делится на весьма большой if else, в зависимости от того будут ли пути вида /foo/bar/ или ?section=foo&element=bar. Стоит понять это и компонент становится раза в два проще.

Стандартный js разделов, итемов и элементов каталога можно использовать, но придется весьма много чего выпиливать, и много к чему добавлять адишники. Внутри у них есть что-то в стиле `if (this.errorCode === 0) {}` к которому неплохо бы добавить else для того чтобы все же узнавать об ошибках. Хотя порой проще написать свой, но у меня почему-то так и не дошли руки сделать что-то, чем не стыдно было бы поделиться.

Отдельно отмечу сложности совмещения этого js с карусельками. Он биндится по id. И если это изменить на классы, то тебе, мой смелый друг, обоснованно укажут на утечку памяти. Впрочем можно убрать оповещение увеличивая максимальный счетчик эвентов с каждым новым вызовом new, чего делать конечно не стоит, но если дедлайн, дизайн и быстродействие несовместимы, то что поделаешь.

Еще бы рассказать про bitrixVM несколько страшных историй, но кажется я заблокировал воспоминания об этих кошмарах.

Зато в битриксе есть чат. Это конечно не телеграм, но так как тот же чат используется в битрикс24, то он вполне не настолько стар как форумы, блоги и соцсети времен стены. Для работы с чатом придется освоить push & pull, и там с js есть свои фишки, в силу того что let тогда не использовали. Впрочем тогда это не мешало и асинхронность в коллбеках пугала только джунов.

Еще есть возможность делать мобильные приложения. Ну ты понимаешь обертки вокруг того же html+css+js. Конечно в них есть кое-что помимо js, но лучше просто не делать мобильные приложения таким образом. И вообще html только для браузеров. А остальное делать нативно, и пофиг что придется писать одно и то же на пригоршне стеков и языков. Но если очень хочется сэкономить, то деваться некуда, пусть и выйдет в итоге куда дороже.

# Эпилог.

Друг. Спасибо что выслушал. У меня был непростой год. А у людей, которые меня окружают он был несравнимо сложнее. Берегись галер и мест где каждый твой день/час расписан под задачки. Увольняйся если из-за работы ты начал курить или еще как-то губить здоровье. Уважай труд других людей. Пока друг.