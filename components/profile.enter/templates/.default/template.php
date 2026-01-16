<? if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

?>

<?

if ($arParams["SMS"]) {
	// авторизация
	require_once("auth_sms.php");
	require_once("auth_step_2.php");
} else {
	// авторизация
	require_once("auth.php");

	// восстановление пароля
	require_once("restore.php");

	// регистрация
	require_once("register.php");

	// смена пароля
	if ($arResult["SHOW_CHANGE_PASSWORD"]) {
		require_once("change.password.php");
	}
}
?>