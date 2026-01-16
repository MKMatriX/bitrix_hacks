<?
use \Bitrix\Main\Application;
use \Bitrix\Main\Entity\Base;
use \MKMatriX\SKU\UtilsIblock;


$moduleName = 'mkmatrix.sku';

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

if (defined("CATALOG_IBLOCK_ID")) {
    $catalogIblockId = CATALOG_IBLOCK_ID;
} else {
    $catalogIblockId = 1;
}

if (defined("CATALOG_SKU_IBLOCK_ID")) {
	$catalogSkuIblockId = CATALOG_SKU_IBLOCK_ID;
} else {
    $catalogSkuIblockId = 2;
}

$arAllOptions = array(
    ["linkPropCode", "Код свойства с XML_ID родителя (текстовое)", "TORGOVOE_PREDLOZHENIE", ["text", 20]],
    ["skuLinkPropCode", "Код свойства для привязки родителя (элемент)", "CML2_LINK", ["text", 20]],
    ["CATALOG_IBLOCK_ID", "Ид ИБ каталога", $catalogIblockId, ["number", 20]],
    ["CATALOG_SKU_IBLOCK_ID", "Ид ИБ предложений каталога", $catalogSkuIblockId, ["number", 20]],
);


$arButtons = [
    [
        "NAME" => "REINSTALL_EVENTS",
        "TEXT" => "Переустановить события"
    ],

    [
        "NAME" => "ON",
        "TEXT" => "Включить"
    ],
    [
        "NAME" => "OFF",
        "TEXT" => "Выключить"
    ],
    [
        "NAME" => "LIST",
        "TEXT" => "Список элементов для переноса"
    ],
    [
        "NAME" => "MOVE_ALL",
        "TEXT" => "Перенести все"
    ],
];

$arSingleTabOptions = [
	["SINGLE_ELEMENT_ID", "ИД элемента для переноса", 0, ["number", 20]],
];

$arSingleTabButtons = [
    [
        "NAME" => "FAKE_MOVE_SINGLE",
        "TEXT" => "Создать свойства из одного элемента"
    ],
    [
        "NAME" => "MOVE_SINGLE",
        "TEXT" => "Переместить один элемент"
    ],
];


$aTabs = [
    [
        "DIV" => "edit1",
        "TAB" => "Настройки",
        "ICON" => "ib_settings",
        "TITLE" => "Настройки",
    ],
    [
        "DIV" => "edit2",
        "TAB" => "Действия",
        "ICON" => "ib_settings",
        "TITLE" => "Действия",
    ],
    [
        "DIV" => "edit3",
        "TAB" => "Одиночный перенос",
        "ICON" => "ib_settings",
        "TITLE" => "Одиночный перенос",
    ],
];

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
try {
    CModule::IncludeModule("sale"); // если не подключается автоматом
    CModule::IncludeModule("mkmatrix.sku");
    UtilsIblock::setOptions();
    UtilsIblock::checkOptions();

    /** @var mkmatrix_sku $installInstance класс установщика модуля*/
    $installInstance = \CModule::CreateModuleObject($moduleName);

    if (strlen($_POST["REINSTALL_EVENTS"])) {
        $message = "События переустановлены";
        $installInstance->unInstallAllModuleEvents();
        $installInstance->installEvents();

    } elseif (strlen($_POST["ON"])) {
        $message = "Автоперенос включен";
        $installInstance->unInstallAllModuleEvents();
        $installInstance->installEvents();
    } elseif (strlen($_POST["OFF"])) {
        $message = "Автоперенос выключен";
        $installInstance->unInstallAllModuleEvents();
    } elseif (strlen($_POST["LIST"])) {
        $wrongIds = UtilsIblock::findWrongElements();

        if (is_array($wrongIds) && count($wrongIds)) {
            $elements = \Bitrix\Iblock\ElementTable::query()
                ->setSelect(["ID", "NAME", "CODE"])
                ->where("ID", "in", $wrongIds)
                ->exec()->fetchAll();

            foreach ($elements as $item) {
                $href = '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=';
                $href .= UtilsIblock::$CATALOG_IBLOCK_ID;
                $href .= '&type=catalog&ID=';
                $href .= $item["ID"];

                $message .= '<a href="' . $href .'">';
                $message .= "[" . $item["ID"] . "] ";
                $message .= $item["NAME"];
                $message .= '</a></br>';
            }
        } else {
            $message = "Элементы для переноса не найдены.";
        }

    } elseif (strlen($_POST["MOVE_ALL"])) {
        $ids = UtilsIblock::moveAllElements();
        if (is_array($ids) && count($ids)) {
            $message = "Элементы перенесены";
        } else {
            $message = "Элементы для переноса не найдены.";
        }
    } elseif (strlen($_POST["FAKE_MOVE_SINGLE"])) {
        $id = (int) $_POST["SINGLE_ELEMENT_ID"];
        if (!($id > 0)) {
            throw new \Exception("Не указан элемент для переноса", 1);
        }
        $wrongIds = UtilsIblock::findWrongElements();

        if (!in_array($id, $wrongIds)) {
            throw new \Exception("Данный элемент не подлежит переносу", 1);
        }

        \MKMatriX\SKU\UtilsIblock::moveElement($id, true);

        $message = "Свойства созданы из элемента " . $id;
    } elseif (strlen($_POST["MOVE_SINGLE"])) {
        $id = (int) $_POST["SINGLE_ELEMENT_ID"];
        if (!($id > 0)) {
            throw new \Exception("Не указан элемент для переноса", 1);
        }
        $wrongIds = UtilsIblock::findWrongElements();

        if (!in_array($id, $wrongIds)) {
            throw new \Exception("Данный элемент не подлежит переносу", 1);
        }

        \MKMatriX\SKU\UtilsIblock::moveElement($id);

        $href = '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=';
        $href .= UtilsIblock::$CATALOG_SKU_IBLOCK_ID;
        $href .= '&type=catalog&ID=';
        $href .= $id;

        $message .= '<a href="' . $href .'">';
        $message .= "[" . $id . "] ";
        $message .= "Элемент";
        $message .= '</a>';
        $message .= " перенесен";
    }

} catch (Exception $exception) {
    $message = $exception->getMessage();
    if ($USER->GetLogin() == "MKMatriX") {
         $message .= "<br/>";
         $message .= $exception->getTraceAsString();
    }
}

ob_start();
    if (strlen($message)) { ?>
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


$tabControl->Begin();
?>
<form method="post" action="<?
echo $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($mid) ?>&amp;lang=<?
echo LANGUAGE_ID ?>">
    <? $tabControl->BeginNextTab(); ?>

        <? foreach ($arAllOptions as $arOption):
            $val = COption::GetOptionString($moduleName, $arOption[0], $arOption[2]);
            $type = $arOption[3];
            ?>
            <tr>
                <td
                    width="40%"
                    nowrap
                    <?= ($type[0] == "textarea")? 'class="adm-detail-valign-top"' : ''?>
                >
                    <label for="<?= htmlspecialcharsbx($arOption[0]) ?>">
                        <?= $arOption[1] ?>:
                    </label>
                </td>
                <td width="60%">
                    <?
                    if ($type[0] == "checkbox"):?>
                        <input
                            type="checkbox"
                            id="<?= htmlspecialcharsbx($arOption[0]) ?>"
                            name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                            value="Y"<?
                            if ($val == "Y") {
                                echo " checked";
                            } ?>
                        />
                    <?
                    elseif ($type[0] == "text"):?>
                        <input
                            type="text"
                            size="<?= $type[1] ?>"
                            maxlength="255"
                            value="<?= htmlspecialcharsbx($val) ?>"
                            name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                        />
                    <?
                    elseif ($type[0] == "number"):?>
                        <input
                            type="number"
                            size="<?= $type[1] ?>"
                            maxlength="255"
                            value="<?= htmlspecialcharsbx($val) ?>"
                            name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                        />
                    <?
                    elseif ($type[0] == "textarea"):?>
                        <textarea
                            rows="<?= $type[1] ?>"
                            cols="<?= $type[2] ?>"
                            name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                        ><?= htmlspecialcharsbx($val) ?></textarea>
                    <?
                    elseif ($type[0] == "selectbox"):?>
                        <select
                            multiple
                            name="<?= htmlspecialcharsbx($arOption[0]) ?>[]">
                            <?
                            $val = explode(",", $val);
                            foreach ($type[1] as $key => $value) {
                                ?>
                                <option
                                    value="<?= $key ?>"<?= (in_array( $key, $val )) ? " selected" : "" ?>
                                ><?= $value ?></option><?
                            }
                            ?>
                        </select>
                    <?
                    endif ?>
                </td>
            </tr>
        <? endforeach ?>

        <? if ($tabControl->tabs[$tabControl->tabIndex - 1]["DIV"] == $tabControl->GetSelectedTab()): ?>
            <?=$errorsHtml?>
        <? endif; ?>

    <? $tabControl->BeginNextTab(); ?>
        <? foreach ($arButtons as $key => $button): ?>
            <tr>
                <td colspan="2">
                    <input type="submit" name="<?=$button["NAME"]?>" value="<?=$button["TEXT"]?>"/>
                </td>
            </tr>
        <? endforeach; ?>

        <? if ($tabControl->tabs[$tabControl->tabIndex - 1]["DIV"] == $tabControl->GetSelectedTab()): ?>
            <?=$errorsHtml?>
        <? endif; ?>

    <? $tabControl->BeginNextTab(); ?>
        <? foreach ($arSingleTabOptions as $arOption):
            $val = 0;
            $type = $arOption[3];
            ?>
            <tr>
                <td
                    width="40%"
                    nowrap
                    <?= ($type[0] == "textarea")? 'class="adm-detail-valign-top"' : ''?>
                >
                    <label for="<?= htmlspecialcharsbx($arOption[0]) ?>">
                        <?= $arOption[1] ?>:
                    </label>
                </td>
                <td width="60%">
                    <?
                    if ($type[0] == "checkbox"):?>
                        <input
                            type="checkbox"
                            id="<?= htmlspecialcharsbx($arOption[0]) ?>"
                            name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                            value="Y"<?
                            if ($val == "Y") {
                                echo " checked";
                            } ?>
                        />
                    <?
                    elseif ($type[0] == "text"):?>
                        <input
                            type="text"
                            size="<?= $type[1] ?>"
                            maxlength="255"
                            value="<?= htmlspecialcharsbx($val) ?>"
                            name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                        />
                    <?
                    elseif ($type[0] == "number"):?>
                        <input
                            type="number"
                            size="<?= $type[1] ?>"
                            maxlength="255"
                            value="<?= htmlspecialcharsbx($val) ?>"
                            name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                        />
                    <?
                    elseif ($type[0] == "textarea"):?>
                        <textarea
                            rows="<?= $type[1] ?>"
                            cols="<?= $type[2] ?>"
                            name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                        ><?= htmlspecialcharsbx($val) ?></textarea>
                    <?
                    elseif ($type[0] == "selectbox"):?>
                        <select
                            multiple
                            name="<?= htmlspecialcharsbx($arOption[0]) ?>[]">
                            <?
                            $val = explode(",", $val);
                            foreach ($type[1] as $key => $value) {
                                ?>
                                <option
                                    value="<?= $key ?>"<?= (in_array( $key, $val )) ? " selected" : "" ?>
                                ><?= $value ?></option><?
                            }
                            ?>
                        </select>
                    <?
                    endif ?>
                </td>
            </tr>
        <? endforeach ?>

        <? foreach ($arSingleTabButtons as $key => $button): ?>
            <tr>
                <td colspan="2">
                    <input type="submit" name="<?=$button["NAME"]?>" value="<?=$button["TEXT"]?>"/>
                </td>
            </tr>
        <? endforeach; ?>

        <? if ($tabControl->tabs[$tabControl->tabIndex - 1]["DIV"] == $tabControl->GetSelectedTab()): ?>
            <?=$errorsHtml?>
        <? endif; ?>

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