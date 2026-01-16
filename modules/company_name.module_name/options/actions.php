<?

if ($_SERVER["REQUEST_METHOD"] == "POST" &&
	($_POST['Update'] || $_POST['Apply'] || $_POST['RestoreDefaults']) > 0 &&
	check_bitrix_sessid()
) {
	if (mb_strlen($_POST['RestoreDefaults']) > 0) {
		$arDefValues = $arDefaultValues['default'];
		foreach ($arDefValues as $key => $value) {
			COption::RemoveOption($moduleName, $key);
		}
	} else {
		foreach ($arAllOptions as $arOption) {
			$name = $arOption[0];
			$val = $_REQUEST[$name];

			COption::SetOptionString($moduleName, $name, $val, $arOption[1]);
		}
	}

	if (mb_strlen($_POST['Update']) > 0 && mb_strlen($_REQUEST["back_url_settings"]) > 0) {
		LocalRedirect($_REQUEST["back_url_settings"]);
	} else {
		LocalRedirect(
			$APPLICATION->GetCurPage() . "?mid=" . urlencode($mid) . "&lang=" . urlencode(
				LANGUAGE_ID
			) . "&back_url_settings=" . urlencode($_REQUEST["back_url_settings"]) . "&" . $tabControl->ActiveTabParam()
		);
	}
}

$message = "";
try {
	$installInstance = \CModule::CreateModuleObject($moduleName);

	if (mb_strlen($_POST["BUTTON_NAME"])) {
		// Знаю что это выглядит некрасиво
		// но вот так я когда-то написал это, судите меня
		// А по делу, тут код если нажали на кнопку BUTTON_NAME
		$message = "Сообщение";
	} elseif (mb_strlen($_POST["ANOTHER_BUTTON_NAME"])) {
		// тут код если нажали на кнопку ANOTHER_BUTTON_NAME
		$message = "Сообщение";
	}
} catch (Exception $exception) {
	$message = $exception->getMessage();
	if ($USER->GetLogin() == "Тут ваш логин, чтобы знать чуть больше при ошибке") {
		$message .= "<br/>";
		$message .= $exception->getTraceAsString();
	}
}

ob_start();
	if (mb_strlen($message)) { ?>
        <tr>
            <td width="20%">
                Сообщение:
            </td>
            <td width="80%">
                <?= $message ?>
            </td>
        </tr><?
	}
$errorsHtml = ob_get_clean();