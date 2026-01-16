<?php

$moduleJsFolder = "/local/modules/mkmatrix.main/js/";
$moduleCssFolder = "/local/modules/mkmatrix.main/css/";

CJSCore::RegisterExt("mk_global_events", [
	"js" =>$moduleJsFolder. "global_events.js",
]);

CJSCore::RegisterExt("mk_ajax_forms", [
	"js" => $moduleJsFolder. "ajax_forms.js",
	"css" => $moduleCssFolder. "ajax_forms.css",
	"rel" => ["mk_utils", "mk_global_events"]
]);

CJSCore::RegisterExt("mk_callback", [
	"js" => $moduleJsFolder. "callback.js",
	"rel" => ["mk_utils", "mk_ajax_forms"]
]);

CJSCore::RegisterExt("mk_personal_data", [
	"js" => $moduleJsFolder. "personal_data.js",
]);

CJSCore::RegisterExt("mk_utils", [
	"js" => $moduleJsFolder. "custom_utils.js",
]);

CJSCore::RegisterExt("mk_user_list_items", [
	"js" => $moduleJsFolder. "user_list_items.js",
	"rel" => ["mk_global_events"]
]);
