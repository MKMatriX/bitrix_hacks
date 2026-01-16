<? $tabControl->Begin(); ?>
<form
	method="post"
	action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($mid) ?>&amp;lang=<?= LANGUAGE_ID ?>"
>
	<?
	// для каждой вкладки
	$tabControl->BeginNextTab();
	?>

		<? include "html_options.php" ?>

		<? include "html_buttons.php" ?>

		<?
		// вывод ошибок для текущей вкладки
		if ($tabControl->tabs[$tabControl->tabIndex - 1]["DIV"] == $tabControl->GetSelectedTab()) {
			echo $errorsHtml;
		}
		?>

	<?
	$tabControl->Buttons(); ?>
	<input type="submit" name="Update" value="Сохранить" class="adm-btn-save">
	<input type="submit" name="Apply" value="Применить">
	<?
	if (strlen($_REQUEST["back_url_settings"]) > 0): ?>
		<input
			type="button"
			name="Cancel"
			value="Отмена"
			onclick="window.location='<?= htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"])) ?>'"
		>
		<input
			type="hidden"
			name="back_url_settings"
			value="<?= htmlspecialcharsbx($_REQUEST["back_url_settings"]) ?>"
		>
	<?
	endif ?>
	<input
		type="submit"
		name="RestoreDefaults"
		OnClick="return confirm('Вернуть настройки по умолчанию')"
		value="По умлочанию"
	>
	<?= bitrix_sessid_post(); ?>
	<? $tabControl->End(); ?>
</form>