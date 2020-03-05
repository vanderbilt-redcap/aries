<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";

// $fac1 = [
	// "id" => 1,
	// "name" => "Hospital A"
// ];

// $fac2 = [
	// "id" => 2,
	// "name" => "Hospital B"
// ];

// $facs = [$fac1, $fac2];

// $q = $module->framework->createQuery();
// $q->add("insert

$data = $module->framework->getSystemSetting('auth_data');
echo "<pre>" . print_r(json_decode($data), true) . "</pre>";