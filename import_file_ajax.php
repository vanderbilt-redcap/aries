<?php
/*
	This script receives an .xlsx from the uploader. It checks the file, and if OK, imports each row as a record to REDCap
	
	validate uploaded file
		file exists
		no error uploading
		contains ok characters
		filename < 127 chars
		file size doesn't exceed max file upload size
	create worksheet object
		load uploaded workbook file
	
	todo make sure we accept xlsx and csv
*/

// make object that will hold our response
$json = new \stdClass();
$json->errors = [];

/*
	helper function definitions
*/
function file_upload_max_size() {					// from: https://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
  static $max_size = -1;

  if ($max_size < 0) {
    // Start with post_max_size.
    $post_max_size = parse_size(ini_get('post_max_size'));
    if ($post_max_size > 0) {
      $max_size = $post_max_size;
    }

    // If upload_max_size is less, then reduce. Except if upload_max_size is
    // zero, which indicates no limit.
    $upload_max = parse_size(ini_get('upload_max_filesize'));
    if ($upload_max > 0 && $upload_max < $max_size) {
      $max_size = $upload_max;
    }
  }
  return $max_size;
}

function parse_size($size) {
  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
  if ($unit) {
    // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
  }
  else {
    return round($size);
  }
}

function humanFileSize($size,$unit="") {			// from: https://stackoverflow.com/questions/15188033/human-readable-file-size
  if( (!$unit && $size >= 1<<30) || $unit == "GB")
    return number_format($size/(1<<30),2)."GB";
  if( (!$unit && $size >= 1<<20) || $unit == "MB")
    return number_format($size/(1<<20),2)."MB";
  if( (!$unit && $size >= 1<<10) || $unit == "KB")
    return number_format($size/(1<<10),2)."KB";
  return number_format($size)." bytes";
}

function checkWorkbookFile($file_param_name) {
	global $json;
	
	$maxsize = file_upload_max_size();
	
	if (empty($_FILES[$file_param_name])) {
		$json->$errors[] = "Please attach a workbook file and then click 'Upload'.";
		exit(json_encode($json));
		return;
	}
	
	if ($_FILES[$file_param_name]["error"] !== 0) {
		$json->$errors[] = "An error occured while uploading your workbook: " . $_FILES[$file_param_name]["error"] . ". Please try again.";
	}

	// have file, so check name, size
	if (preg_match("/[^A-Za-z0-9. ()-]/", $_FILES[$file_param_name]["name"])) {
		$json->$errors[] = "File names can only contain alphabet, digit, period, space, hyphen, and parentheses characters.";
		$json->$errors[] = "	Allowed characters: A-Z a-z 0-9 . ( ) -";
	}
	if (strlen($_FILES[$file_param_name]["name"]) > 127) {
		$json->$errors[] = "Uploaded file has a name that exceeds the limit of 127 characters.";
	}
	if ($maxsize !== -1) {
		if ($_FILES[$file_param_name]["size"] > $maxsize) {
			$fileReadable = humanFileSize($_FILES[$file_param_name]["size"], "MB");
			$serverReadable = humanFileSize($maxsize, "MB");
			$json->$errors[] = "Uploaded file size ($fileReadable) exceeds server maximum upload size of $serverReadable.";
		}
	}
	
	if (count($json->errors) > 0) {
		exit(json_encode($json));
	}
	
	return true;
}

function get_assoc_form($field_name) {
	// given "patient_ssn" would return "xdro_registry" -- return form name that a field belongs to
	
}

function get_assoc_field($column_name) {
	// use this to convert data file column names to REDCap project field names (e.g., given "ORDERING_PROVIDER_NM", returns "providername"
	
}

// use PHPSpreadsheet (php 5.6 version)
require "libs/PhpSpreadsheet/vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// load uploaded workbook file into memory
try {
	$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
	$reader->setReadDataOnly(TRUE);
	$workbook = $reader->load($_FILES[$file_param_name]["tmp_name"]);
	unlink($_FILES[$file_param_name]["tmp_name"]);
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
	$json->errors[] = "There was an error reading the file uploaded: $e";
	exit(json_encode($json));
}

// declare mapping arrays -- these help us map the import column names to REDCap project field names
// $form_from_field = [
	// "patientid" => "xdro_registry",
	// "patient_first_name" => "xdro_registry",
	// "patient_last_name" => "xdro_registry",
	// "patient_dob" => "xdro_registry",
	// "patient_current_sex" => "xdro_registry",
	// "patient_street_address_1" => "xdro_registry",
	// "patient_street_address_2" => "xdro_registry",
	// "patient_city" => "xdro_registry",
	// "patient_state" => "xdro_registry",
	// "patient_zip" => "xdro_registry",
	// "patient_county" => "xdro_registry",
	// "jurisdiction_nm" => "xdro_registry",
	// "ordering_facility" => "xdro_registry",
	// "providername" => "xdro_registry",
	// "provider_address" => "xdro_registry",
	// "providerphone" => "xdro_registry",
	// "reporting_facility" => "xdro_registry",
	// "reportername" => "xdro_registry",
	// "reporterphone" => "xdro_registry",
	// "ordered_test_nm" => "xdro_registry",
	// "specimen_desc" => "xdro_registry",
	// "lab_test_nm" => "xdro_registry",
	// "specimen_collection_dt" => "xdro_registry",
	// "lab_test_status" => "xdro_registry",
	// "resulted_dt" => "xdro_registry",
	// "disease" => "xdro_registry",
	// "patient_last_change_time" => "xdro_registry",
	// "patient_first_name_d" => "xdro_registry",
	// "patient_last_name_d" => "xdro_registry",
	// "patient_dob_d" => "xdro_registry",
	// "patient_current_sex_d" => "xdro_registry",
	// "patient_phone_home" => "xdro_registry",
	// "patient_street_address_1_d" => "xdro_registry",
	// "patient_street_address_2_d" => "xdro_registry",
	// "patient_city_d" => "xdro_registry",
	// "patient_state_d" => "xdro_registry",
	// "patient_zip_d" => "xdro_registry",
	// "patient_county_d" => "xdro_registry",
	// "jurisdiction_nm_d" => "xdro_registry",
	// "patient_ssn" => "xdro_registry",
	// "patient_race_calculated" => "xdro_registry",
	// "patient_ethnicity" => "xdro_registry",
	// "lab_test_nm_2" => "antimicrobial_susceptibilities_and_resistance_mech",
	// "coded_result_val_desc" => "antimicrobial_susceptibilities_and_resistance_mech",
	// "interpretation_flg" => "antimicrobial_susceptibilities_and_resistance_mech",
	// "numeric_result_val" => "antimicrobial_susceptibilities_and_resistance_mech",
	// "result_units" => "antimicrobial_susceptibilities_and_resistance_mech",
	// "test_method_cd" => "antimicrobial_susceptibilities_and_resistance_mech"
// ];

// connect to REDCap db
require_once (APP_PATH_TEMP . "../redcap_connect.php");



$json->success = true;
exit(json_encode($json));