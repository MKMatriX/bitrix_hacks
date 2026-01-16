<?
define('NOT_CHECK_PERMISSIONS', true);
define('NOT_CHECK_FILE_PERMISSIONS', true);

define("LOGIN", "myLogin");
define("EMAIL", "myEmail@example.org");
define("NAME", "Имя");
define("LAST_NAME", "Фамилия");
define("PASSWORD", "Please_don't_keep_your_password_public_or_in_git_or_backups");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


if ($USER->IsAuthorized()) {
	echo "Был авторизован под " . $USER->GetLogin() . "<br/>" ;
}

// Для начала ищем пользователя с нашим логином или имейлом, учитывая что на сайтах иногда имейл используется как логин
$user = \Bitrix\Main\UserTable::getRow([
	'select' => ['ID', 'LOGIN'],
	'filter' => [
		[
			'LOGIC' => 'OR',
			['LOGIN' => LOGIN],
			['LOGIN' => EMAIL],
			'EMAIL' => EMAIL,
		]
	],
]);

if (!is_null($user)) {
	// Смотрим входит ли он в группу администраторов (1)
	$group = \Bitrix\Main\UserGroupTable::getRow([
		'filter' => [
			'USER_ID' => $user['ID'],
			'GROUP_ID' => 1,
			// 'GROUP.ACTIVE'=>'Y' // Вообще если группа администраторов не активна, то у вас проблемы посерьезней авторизации
		],
		'select' => ['GROUP_ID'], // выбираем идентификатор группы и символьный код группы
	]);

	if (is_null($group)) {
		// Если наш пользователь не админ, то добавляем его в админов.
		\Bitrix\Main\UserGroupTable::add([
			"USER_ID" => $user["ID"],
			"GROUP_ID" => 1,
		]);

		echo "Добавили пользователя в админов <br>";
	}

	// Авторизуемся.
	$USER->Authorize($user['ID'], true);
	echo "Авторизовались под {$user["LOGIN"]} <br>";
} else {
	// Если пользователя нету, то создаем его, сразу как админа.
	$user = new CUser;
	$arFields = [
		"NAME"              => NAME,
		"LAST_NAME"         => LAST_NAME,
		"EMAIL"             => EMAIL,
		"LOGIN"             => LOGIN,
		"PASSWORD"          => PASSWORD,
		"CONFIRM_PASSWORD"  => PASSWORD,
		"LID"               => "ru",
		"ACTIVE"            => "Y",
		"GROUP_ID"          => [1],
	];

	$id = $user->Add($arFields);
	if ($id > 0) {
		$USER->Authorize($id, true);
		echo "Авторизовались под новым юзером ({$id}) <br>";
	} else {
		echo $user->LAST_ERROR;
	}
}
?>

<div>
	<a href="/">Главная</a>
</div>
<div>
	<a href="/bitrix/">Админка</a>
</div>

<?
CMain::FinalActions();
?>