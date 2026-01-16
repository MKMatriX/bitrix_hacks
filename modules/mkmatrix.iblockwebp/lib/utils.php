<?php
namespace MKMatriX\Iblockwebp;

class Utils {
	/**
	 * @param mixed $picture
	 * @param string $sizes "widthxheight" like "100x100"
	 * @param bool $exact exact or proportional
	 *
	 * @return [type]
	 */
	public static function thumbSrc($picture, string $sizes, $exact = false) {
		if ($picture == NO_PHOTO) {
			return NO_PHOTO;
		}

		$sizes = explode("x", $sizes);
		$res = \CFile::ResizeImageGet(
			$picture,
			["width" => $sizes[0], "height" => $sizes[1]],
			$exact? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL,
			false
		);

		if ($res === false || !strlen($res["src"])) {
			return NO_PHOTO;
		}

		return $res["src"];
	}

	public static function makeSrcSet($picture, string $sizes, string $alt = "", $exact = false) {
		$src = "";

		if ($picture == NO_PHOTO) {
			$src = NO_PHOTO;
		}

		$sizes = explode("x", $sizes);
		$res = \CFile::ResizeImageGet(
			$picture,
			["width" => $sizes[0], "height" => $sizes[1]],
			$exact? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL,
			false
		);

		if ($res === false || !strlen($res["src"])) {
			$src = NO_PHOTO;
			return '<img src="' . $src . '" alt="' . $alt . '">';
		}
		$src = $res["src"];
		$destination = self::src2webp($src);

		$src = str_replace("%2F", "/", urlencode($src));
		$html = '<source srcset="' . $destination . '" type="image/webp">';
		$html .= '<source srcset="' . $src . '" type="image/jpeg">';
		$html .= '<img src="' . $src . '" alt="' . $alt . '">';

		return $html;
	}

	private static function src2path(string $src) {
		return $_SERVER["DOCUMENT_ROOT"] . $src;
	}

	private static function path2src(string $path) {
		$path = str_replace($_SERVER["DOCUMENT_ROOT"], "", $path);
		$path = urldecode($path);
		return $path;
	}

	private static function path2webp(string $path) {
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		if (strtolower($ext) == "webp") {
			return $path;
		}

		if (!file_exists($path)) {
			return $path;
		}

		$destination = $path . ".webp";
		if (!file_exists($destination)) {
			\WebPConvert\WebPConvert::convert(
				$path,
				$destination
			);
		} else {
			return $destination;
		}

		return $destination;
	}

	private static function src2webp(string $src) {
		$path = self::src2path($src);
		$webp = self::path2webp($path);
		if ($webp === false) {
			return $webp;
		}

		$webpSrc = self::path2src($webp);
		return $webpSrc;
	}

	public static function seoImgList($i = 0, $mini = false) {
		$fullArray = [
			"фото",
			"изображение",
			"картинка",
			"фотография",
			"рисунок",
			"снимок",
		];
		$miniArray = [
			"мини изображение",
			"мини фото",
			"мини картинка",
			"мини фотография",
			"мини снимок",
			"мини рисунок",
			"миниатюра"
		];

		$fullGen = function ($i) {
			return "снимок №{$i}";
		};

		$miniGen = function ($i) {
			return "мини №{$i}";
		};

		$usedArray = &$fullArray;
		$usedGen = &$fullGen;

		if ($mini) {
			$usedArray = &$miniArray;
			$usedGen = &$miniGen;
		}

		$count = count($usedArray);
		if ($i < $count) {
			return $usedArray[$i];
		}

		return $usedGen($i + 1 - $count);
	}

	public static function getAllIblockPictures($iblockId) {
		if(!\CModule::IncludeModule("iblock")) {
			ShowError("Модуль iblock не установлен!");
			return;
		}

		$iblockTablePicts = self::getElementTablePicturesIds($iblockId);


		$files = self::getFiles($iblockTablePicts);

		foreach ($files as $key => $file) {
			$files[$key]["CONVERTED"] = self::changeFile($file["ID"]);
		}

		return $files;
	}

	private static function getElementTablePicturesIds($iblockId) {
		$elements = \Bitrix\Iblock\ElementTable::query()
			->setSelect(["DETAIL_PICTURE", "PREVIEW_PICTURE"])
			->where("IBLOCK_ID", $iblockId)
			->exec()->fetchAll();

		$detailPicture = array_column($elements, "DETAIL_PICTURE");
		$previewPicture = array_column($elements, "PREVIEW_PICTURE");

		unset($elements);

		$allPictures = self::addToPictureArray($detailPicture, $previewPicture);

		return $allPictures;
	}

	private static function addToPictureArray($a, $b) {
		$allPictures = array_merge(array_values($a), array_values($b));
		$allPictures = array_unique($allPictures);
		$allPictures = array_values(array_filter($allPictures, "is_numeric"));
		return $allPictures;
	}

	private static function getFiles($fileIds = []) {
		$query = Override\FileTable::query()
			->setSelect([
				"ID",
				"PATH" => new \Bitrix\Main\ORM\Fields\ExpressionField(
					"PATH",
					"CONCAT('" . ($_SERVER["DOCUMENT_ROOT"] . "/upload/") . "', %s, '/', %s)",
					["SUBDIR", "FILE_NAME"]
				)
			])
			->where(\Bitrix\Main\ORM\Query\Query::filter()
				->logic("or")
				->where(\Bitrix\Main\ORM\Query\Query::filter()
					->where("CONTENT_TYPE", "image/png")
				)
				->where(\Bitrix\Main\ORM\Query\Query::filter()
					->where("CONTENT_TYPE", "image/jpeg")
				)
				// image/webp
			);

		if (is_array($fileIds) && !empty($fileIds)) {
			$query->where("ID", "in", $fileIds);
		}

		$files = $query->exec()->fetchAll();

		$files = array_filter($files, function ($file) {
			return file_exists($file["PATH"]);
		});

		return $files;
	}

	private static function getFilesWithoutWebp() {
		$files = self::getFiles();

		$files = array_filter($files, function ($file) {
			return !file_exists($file["PATH"] . ".webp");
		});

		return $files;
	}


	public static function changeFile($fileId) {
		$fileObj = Override\FileTable::query()
			->setSelect([
				"*",
				// "PATH" => new \Bitrix\Main\ORM\Fields\ExpressionField(
				// 	"PATH",
				// 	"CONCAT('" . ($_SERVER["DOCUMENT_ROOT"] . "/upload/") . "', %s, '/', %s)",
				// 	["SUBDIR", "FILE_NAME"]
				// )
			])
			->where("ID", $fileId)
			->fetchObject();

		if (is_null($fileObj)) {
			return false;
		}

		$path =  $_SERVER["DOCUMENT_ROOT"] . "/upload/" . $fileObj["SUBDIR"] . "/" . $fileObj["FILE_NAME"];

		\WebPConvert\WebPConvert::convert(
			$path,
			$path . ".webp"
		);

		if (!file_exists($path . ".webp")) {
			return false;
		}

		$fileObj["FILE_NAME"] .= ".webp";
		$fileObj["CONTENT_TYPE"] = "image/webp";
		/** \Bitrix\Main\Entity\UpdateResult $res */
		$res = $fileObj->save();

		if ($res->isSuccess()) {
			unlink($path);
			return true;
		}
		return false;
	}

	public static function addWebpVersionOfFile() {
		$files = self::getFilesWithoutWebp();

		foreach ($files as $file) {
			self::path2webp($file["PATH"]);
		}
	}

	public static function getSizedVersion($file, $sizeCode, $exact = false) {
		$size = SizesTable::getSize($sizeCode);

		if (is_numeric($file)) {
			$file = \CFile::GetByID($file)->Fetch();
		}

		if (!is_array($file) || !$file["ID"] > 0) {
			return NO_PHOTO;
		}

		if (!file_exists($_SERVER["DOCUMENT_ROOT"] . urldecode($file["SRC"]))) {
			return NO_PHOTO;
		}

		// if (!array_key_exists("FILE_NAME", $file) || $file["FILE_NAME"] == '') {

		// }

		$res = \CFile::ResizeImageGet(
			$file,
			["width" => $size["WIDTH"], "height" => $size["HEIGHT"]],
			$exact? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL,
			false
		);

		$src = $res["src"];
		if ($size["FORMAT"] === "webp") {
			$ext = pathinfo($src, PATHINFO_EXTENSION);
			if (strtolower($ext) !== "webp") {
				$src = self::src2webp($src);
			}
		}

		return $src;
	}
}