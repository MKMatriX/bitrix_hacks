<?php
// namespace MKMatriX\export;

$moduleJsFolder = "/local/modules/mkmatrix.export/js/";
// $moduleCssFolder = "/local/modules/mkmatrix.main/css/";

CJSCore::RegisterExt("mk_export", [
	"js" => $moduleJsFolder. "export.js",
]);

CJSCore::RegisterExt("mk_import", [
	"js" => $moduleJsFolder. "import.js",
	"rel" => ["mk_global_events"]
]);

CJSCore::RegisterExt("mk_basket", [
	"js" => $moduleJsFolder. "basket.js",
	"rel" => ["mk_global_events"]
]);

CJSCore::Init([
	"mk_export",
	"mk_import",
	"mk_basket",
]);