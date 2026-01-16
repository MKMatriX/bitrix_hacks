<?
use \Bitrix\Main\Application;
use \Bitrix\Main\Entity\Base;


$moduleName = 'mkmatrix.main';

if (!$USER->IsAdmin()) {
    return;
    die();
}

if (!\Bitrix\Main\Loader::includeModule($moduleName)) {
    return;
    die();
}
// include_once(__DIR__ . '/default_option.php');
$arDefaultValues['default'] = [];

$arAllOptions = array(
    // ["login", "Логин", "", ["text", 20]],
    // ["password", "Пароль", "", ["text", 20]],
    // ["orgId", "GUID организации", "", ["text", 20]],
);

$arButtons = [
    [
        "NAME" => "REINSTALL_EVENTS",
        "TEXT" => "Переустановить события"
    ],
    [
        "NAME" => "REINSTALL_TABLES",
        "TEXT" => "Переустановить таблицы"
    ],
];

$aTabs = array(
    // [
    //     "DIV" => "edit1",
    //     "TAB" => "Настройки",
    //     "ICON" => "ib_settings",
    //     "TITLE" => "Настройки",
    // ],
    [
        "DIV" => "edit2",
        "TAB" => "Утилиты",
        "ICON" => "ib_settings",
        "TITLE" => "Служебные утилиты",
    ],
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);


if ($_SERVER["REQUEST_METHOD"] == "POST" &&
    ($_POST['Update'] || $_POST['Apply'] || $_POST['RestoreDefaults']) > 0 &&
    check_bitrix_sessid()
) {
    if (strlen($_POST['RestoreDefaults']) > 0) {
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

    if (strlen($_POST['Update']) > 0 && strlen($_REQUEST["back_url_settings"]) > 0) {
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
$installInstance = \CModule::CreateModuleObject($moduleName);
try {
    if (strlen($_POST["REINSTALL_EVENTS"])) {
        $message = "События переустановлены";
        $installInstance->unInstallAllModuleEvents();
        $installInstance->installEvents();
    } elseif (strlen($_POST["REINSTALL_TABLES"])) {
        $message = "Таблицы переустановлены: <br/>";
        $message .= implode("<br/>", $installInstance->getModuleTables());
        $installInstance->reinstallTables();
    }
} catch (Exception $exception) {
    $message = $exception->getMessage();
}


$tabControl->Begin();
?>
<form method="post" action="<?
echo $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($mid) ?>&amp;lang=<?
echo LANGUAGE_ID ?>">
    <?
    $tabControl->BeginNextTab(); ?>
    <?
    foreach ($arAllOptions as $arOption):
        $val = COption::GetOptionString($moduleName, $arOption[0], $arOption[2]);
        $type = $arOption[3];
        ?>
        <tr>
            <td width="40%" nowrap <?
            if ($type[0] == "textarea") echo 'class="adm-detail-valign-top"' ?>>
                <label for="<?
                echo htmlspecialcharsbx($arOption[0]) ?>"><?
                    echo $arOption[1] ?>:</label>
            <td width="60%">
                <?
                if ($type[0] == "checkbox"):?>
                    <input type="checkbox" id="<?
                    echo htmlspecialcharsbx($arOption[0]) ?>" name="<?
                    echo htmlspecialcharsbx($arOption[0]) ?>" value="Y"<?
                    if ($val == "Y") {
                        echo " checked";
                    } ?>>
                <?
                elseif ($type[0] == "text"):?>
                    <input type="text" size="<?
                    echo $type[1] ?>" maxlength="255" value="<?
                    echo htmlspecialcharsbx($val) ?>" name="<?
                    echo htmlspecialcharsbx($arOption[0]) ?>">
                <?
                elseif ($type[0] == "textarea"):?>
                    <textarea rows="<?
                    echo $type[1] ?>" cols="<?
                    echo $type[2] ?>" name="<?
                    echo htmlspecialcharsbx($arOption[0]) ?>"><?
                        echo htmlspecialcharsbx($val) ?></textarea>
                <?
                elseif ($type[0] == "selectbox"):?>
                    <select multiple name="<?
                    echo htmlspecialcharsbx($arOption[0]) ?>[]">
                        <?
                        $val = explode(",", $val);
                        foreach ($type[1] as $key => $value) {
                            ?>
                            <option value="<?= $key ?>"<?= (in_array(
                            $key,
                            $val
                        )) ? " selected" : "" ?>><?= $value ?></option><?
                        }
                        ?>
                    </select>
                <?
                endif ?>
            </td>
        </tr>
    <?
    endforeach ?>

    <tr>
        <? foreach ($arButtons as $key => $button): ?>
            <td colspan="2">
                <input type="submit" name="<?=$button["NAME"]?>" value="<?=$button["TEXT"]?>"/>
            </td>
        <? endforeach; ?>
    </tr>

    <?
    if (strlen($message)): ?>
        <tr>
            <td>
                Сообщение:
            </td>
            <td>
                <?= $message ?>
            </td>
        </tr>
    <?
    endif; ?>

    <?
    $tabControl->Buttons(); ?>
    <input type="submit" name="Update" value="Сохранить" class="adm-btn-save">
    <input type="submit" name="Apply" value="Применить">
    <?
    if (strlen($_REQUEST["back_url_settings"]) > 0): ?>
        <input type="button" name="Cancel" value="Отмена" onclick="window.location='<?
        echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"])) ?>'">
        <input type="hidden" name="back_url_settings" value="<?= htmlspecialcharsbx($_REQUEST["back_url_settings"]) ?>">
    <?
    endif ?>
    <input type="submit" name="RestoreDefaults" OnClick="return confirm('Вернуть настройки по умолчанию')"
           value="По умлочанию">
    <?= bitrix_sessid_post(); ?>
    <?
    $tabControl->End(); ?>
</form>