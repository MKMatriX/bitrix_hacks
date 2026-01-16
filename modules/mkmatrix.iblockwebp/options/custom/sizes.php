<?
use \MKMatriX\Iblockwebp\SizesTable;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\Base;
if (!Application::getConnection()->isTableExists(Base::getInstance(\MKMatriX\Iblockwebp\SizesTable::class)->getDBTableName())) {
	Base::getInstance(\MKMatriX\Iblockwebp\SizesTable::class)->createDBTable();
}



$sizes = SizesTable::query()
	->setSelect(["*"])
	->exec()->fetchCollection() ?? SizesTable::createCollection();

$fields = SizesTable::getMap();
$fields = array_filter($fields, fn ($field) => $field->getName() !== "UF_SORT");
$fields = array_filter($fields, fn ($field) => $field->getName() !== "UF_DESCRIPTION");

if (mb_strlen($_POST["SAVE_SIZES"])) {
	$postSizes = $_POST["size"];

	$size = $postSizes["new"];
	if ($size["HEIGHT"] > 0 && $size["WIDTH"] > 0) {
		$objSize = SizesTable::createObject();

		foreach ($fields as $field) {
			$code = $field->getName();
			if ($code === "ID") {
				continue;
			}
			$objSize[$code] = $size[$code];
		}

		$objSize->save();
		$sizes[] = $objSize;
	}
	unset($postSizes["new"]);

	foreach ($postSizes as $id => $size) {
		$objSize = $sizes->getByPrimary((int) $id);
		if (!is_null($objSize)) {
			foreach ($fields as $field) {
				$code = $field->getName();
				if ($code === "ID") {
					continue;
				}
				$objSize[$code] = $size[$code];
			}

			$objSize->save();
		}
	}
}

if ($_POST["delsize"]) {
	foreach ($delsize as $id => $btnName) {
		$delSize = $sizes->getByPrimary((int) $id);
		if (!is_null($delSize)) {
			$sizes->removeByPrimary((int) $id);
			$delSize->delete();
		}
	}
}

$imageModuleHeadRow = function ($size = []) use ($fields) {
	$html = "";
	$html .= "<tr>";
	foreach ($fields as $key => $field) {
		$html .= "<td>";
		$html .= $field->getTitle();
		$html .= "</td>";
	}

	$html .= "<td>";
	$html .= "Действия";
	$html .= "</td>";

	$html .= "</tr>";

	return $html;
};

$imageModuleRow = function ($size = []) use ($fields) {
	$id = $size["ID"] ?? "new";
	$html = "";
	$html .= "<tr>";
	foreach ($fields as $key => $field) {
		$value = $size[$field->getName()] ?? $field->getParameter("default_value");

		if ($field->getParameter("primary")) {
			$html .= "<td>";
			$html .= $id;
			$html .= "</td>";
			continue;
		}

		$html .= "<td>";
		$html .= '<input type="text" name="size['. $id .']['.$field->getName().']" value="'.$value.'">';
		$html .= "</td>";
	}

	$html .= "<td>";
	if ($id !== "new") {
		$html .= '<input class="adm-btn-red" type="submit" name="delsize['.$id.']" value="Удалить">';
	}
	$html .= "</td>";

	$html .= "</tr>";

	return $html;
};

if (!is_null($sizes)) {
	echo $imageModuleHeadRow();
	echo $imageModuleRow();
	foreach ($sizes as $size) {
		echo $imageModuleRow($size);
	}
}



?>