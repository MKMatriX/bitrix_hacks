<?
define('NOT_CHECK_PERMISSIONS', true);
define('NOT_CHECK_FILE_PERMISSIONS', true);

// if (isset($_REQUEST["AJAX_CALL"]) && $_REQUEST["AJAX_CALL"] === "Y") {
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
// } else {
// 	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
// }

if ($USER->IsAuthorized()) {
	echo "Авторизован как " . $USER->GetLogin() . "<br/>" ;
}


$user = \Bitrix\Main\UserTable::getRow([
	'select' => ['ID', 'LOGIN'],
	'filter' => [
		[
			'LOGIC' => 'OR',
			['LOGIN' => 'myLogin'],
			['LOGIN' => 'if_login@is.email'],
			'EMAIL' => 'or_just@by.email',
		]
	],
]);

if (!is_null($user)) {
	echo "Перевторизован как " . $user['LOGIN'] . "<br/>" ;
	$USER->Authorize($user['ID'], true);
} else {
	die("Can't find masters user( aborting....");
}

$allGroups = \Bitrix\Main\GroupTable::query()
	->setSelect([
		"ID",
		"NAME",
		"STRING_ID",
	])
	->where("ACTIVE", true)
	->exec()->fetchAll();

$userGroups = \Bitrix\Main\UserTable::query()
		->where("ID", $GLOBALS["USER"]->getId())
		->setSelect([
			"G_ID" => "GROUPS.GROUP_ID"
		])
		->exec()->fetchAll();
$userGroups = array_column($userGroups, "G_ID");


foreach ($allGroups as $key => $g) {
	$inGroup = in_array($g["ID"], $userGroups);
	$allGroups[$key]["IN"] = $inGroup;
}

$identedAllGroups = array_combine(
	array_column($allGroups, "ID"),
	$allGroups
);

$id = (int) $_GET["id"];
if ($id) {
	$group = \Bitrix\Main\UserGroupTable::query()
		->where('USER_ID', $user['ID'])
		->where('GROUP_ID', $id)
		->setSelect(['GROUP_ID'])
		->exec()->fetchObject();


	if (is_null($group)) {
		\Bitrix\Main\UserGroupTable::add([
			"USER_ID" => $user["ID"],
			"GROUP_ID" => $id,
		]);

		echo "Добавили его в " . $identedAllGroups[$id]["NAME"] . " <br>";
	} else {
		$group->delete();
		echo "Убрали его из " . $identedAllGroups[$id]["NAME"] . " <br>";
	}
}


?>
<div style="width: 800px">
	<div>
		<a href="/scripts/groups.php" style="color: black">
			Ничего не делать.
		</a>
	</div>
	<?
	foreach ($allGroups as $key => $g) {
		$g["IN"] = $g["ID"] == $id ? !$g["IN"] : $g["IN"];
		?>
		<div>
			<a href="?id=<?=$g["ID"]?>" style="color: <?= $g["IN"]? "green": "red"?>; display: flex; justify-content: space-around;">
				<span><?=$g["NAME"]?></span>
				<strong><?=$g["STRING_ID"]?></strong>
			</a>
		</div>
		<?
	}
	?>
</div>
<?

CMain::FinalActions();