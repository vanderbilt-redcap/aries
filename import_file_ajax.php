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
// connect to REDCap
require_once (APP_PATH_TEMP . "../redcap_connect.php");
$pid = $module->getProjectId();
$module->nlog();

// make object that will hold our response
$json = new \stdClass();
$json->errors = [];

$module->llog("post: " . print_r($_POST, true));
$module->llog("files: " . print_r($_FILES, true));

/*
	function definitions
*/
function number_to_column($c) {	// thanks Derrick: https://icesquare.com/wordpress/example-code-to-convert-a-number-to-excel-column-letter/
	// converts 1 -> A, 26 -> Z, 27-> AA, 800 -> ADT, etc
    $c = intval($c);
    if ($c <= 0) return '';

    $letter = '';
             
    while($c != 0){
       $p = ($c - 1) % 26;
       $c = intval(($c - $p) / 26);
       $letter = chr(65 + $p) . $letter;
    }
    
    return $letter;
        
}

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
		$json->errors[] = "Please attach a workbook file before clicking 'Upload'.";
		exit(json_encode($json));
		return;
	}
	
	if ($_FILES[$file_param_name]["error"] !== 0) {
		$json->errors[] = "An error occured while uploading your workbook: " . $_FILES[$file_param_name]["error"] . ". Please try again.";
	}

	// have file, so check name, size
	if (preg_match("/[^A-Za-z0-9. ()-]/", $_FILES[$file_param_name]["name"])) {
		$json->errors[] = "File names can only contain alphabet, digit, period, space, hyphen, and parentheses characters.";
		$json->errors[] = "	Allowed characters: A-Z a-z 0-9 . ( ) -";
	}
	if (strlen($_FILES[$file_param_name]["name"]) > 127) {
		$json->errors[] = "Uploaded file has a name that exceeds the limit of 127 characters.";
	}
	if ($maxsize !== -1) {
		if ($_FILES[$file_param_name]["size"] > $maxsize) {
			$fileReadable = humanFileSize($_FILES[$file_param_name]["size"], "MB");
			$serverReadable = humanFileSize($maxsize, "MB");
			$json->errors[] = "Uploaded file size ($fileReadable) exceeds server maximum upload size of $serverReadable.";
		}
	}
	
	if (count($json->errors) > 0) {
		exit(json_encode($json));
	}
	
	return true;
}

function get_assoc_form($column_name) {
	// given "patient_ssn" would return "xdro_registry" -- return form name that a field belongs to
	$forms = [
		"PATIENT_FIRST_NAME" => "xdro_registry",
		"PATIENT_LAST_NAME" => "xdro_registry",
		"PATIENT_DOB" => "xdro_registry",
		"PATIENT_CURRENT_SEX" => "xdro_registry",
		"PATIENT_STREET_ADDRESS_1" => "xdro_registry",
		"PATIENT_STREET_ADDRESS_2" => "xdro_registry",
		"PATIENT_CITY" => "xdro_registry",
		"PATIENT_STATE" => "xdro_registry",
		"PATIENT_ZIP" => "xdro_registry",
		"PATIENT_COUNTY" => "xdro_registry",
		"JURISDICTION_NM" => "xdro_registry",
		"ordering_facility" => "xdro_registry",
		"ORDERING_PROVIDER_NM" => "xdro_registry",
		"PROVIDER_ADDRESS" => "xdro_registry",
		"PROVIDER_PHONE" => "xdro_registry",
		"reporting_facility" => "xdro_registry",
		"reporterName" => "xdro_registry",
		"reporterPhone" => "xdro_registry",
		"ordered_test_nm" => "xdro_registry",
		"SPECIMEN_DESC" => "xdro_registry",
		"lab_test_nm" => "xdro_registry",
		"SPECIMEN_COLLECTION_DT" => "xdro_registry",
		"LAB_TEST_STATUS" => "xdro_registry",
		"resulted_dt" => "xdro_registry",
		"DISEASE" => "xdro_registry",
		"PATIENT_LAST_CHANGE_TIME" => "xdro_registry",
		"PATIENT_SSN" => "xdro_registry",
		"PATIENT_RACE_CALCULATED" => "xdro_registry",
		"PATIENT_ETHNICITY" => "xdro_registry",
		"PATIENT_FIRST_NAME" => "demographics",
		"PATIENT_LAST_NAME" => "demographics",
		"PATIENT_DOB" => "demographics",
		"PATIENT_CURRENT_SEX" => "demographics",
		"PATIENT_PHONE_HOME" => "demographics",
		"PATIENT_STREET_ADDRESS_1" => "demographics",
		"PATIENT_STREET_ADDRESS_2" => "demographics",
		"PATIENT_CITY" => "demographics",
		"PATIENT_STATE" => "demographics",
		"PATIENT_ZIP" => "demographics",
		"PATIENT_COUNTY" => "demographics",
		"JURISDICTION_NM" => "demographics",
		"lab_test_nm" => "antimicrobial_susceptibilities_and_resistance_mech",
		"coded_result_val_desc" => "antimicrobial_susceptibilities_and_resistance_mech",
		"interpretation_flg" => "antimicrobial_susceptibilities_and_resistance_mech",
		"numeric_result_val" => "antimicrobial_susceptibilities_and_resistance_mech",
		"result_units" => "antimicrobial_susceptibilities_and_resistance_mech",
		"test_method_cd" => "antimicrobial_susceptibilities_and_resistance_mech"
	];
	
	if (!empty($forms[$column_name])) {
		return [1, $forms[$column_name]];
	} else {
		return [0, "no associated form for column $column_name"];
	}
}

function get_assoc_field($column_name) {
	// use this to convert data file column names to REDCap project field names (e.g., given "ORDERING_PROVIDER_NM", returns "providername"
	$fields = [
		"PATIENT_FIRST_NAME" => "patient_first_name",
		"PATIENT_LAST_NAME" => "patient_last_name",
		"PATIENT_DOB" => "patient_dob",
		"PATIENT_CURRENT_SEX" => "patient_current_sex",
		"PATIENT_STREET_ADDRESS_1" => "patient_street_address_1",
		"PATIENT_STREET_ADDRESS_2" => "patient_street_address_2",
		"PATIENT_CITY" => "patient_city",
		"PATIENT_STATE" => "patient_state",
		"PATIENT_ZIP" => "patient_zip",
		"PATIENT_COUNTY" => "patient_county",
		"JURISDICTION_NM" => "jurisdiction_nm",
		"ordering_facility" => "ordering_facility",
		"ORDERING_PROVIDER_NM" => "providername",
		"PROVIDER_ADDRESS" => "provider_address",
		"PROVIDER_PHONE" => "providerphone",
		"reporting_facility" => "reporting_facility",
		"reporterName" => "reportername",
		"reporterPhone" => "reporterphone",
		"ordered_test_nm" => "ordered_test_nm",
		"SPECIMEN_DESC" => "specimen_desc",
		"lab_test_nm" => "lab_test_nm",
		"SPECIMEN_COLLECTION_DT" => "specimen_collection_dt",
		"LAB_TEST_STATUS" => "lab_test_status",
		"resulted_dt" => "resulted_dt",
		"DISEASE" => "disease",
		"PATIENT_LAST_CHANGE_TIME" => "patient_last_change_time",
		"PATIENT_SSN" => "patient_ssn",
		"PATIENT_RACE_CALCULATED" => "patient_race_calculated",
		"PATIENT_ETHNICITY" => "patient_ethnicity",
		"PATIENT_FIRST_NAME" => "patient_first_name_d",
		"PATIENT_LAST_NAME" => "patient_last_name_d",
		"PATIENT_DOB" => "patient_dob_d",
		"PATIENT_CURRENT_SEX" => "patient_current_sex_d",
		"PATIENT_PHONE_HOME" => "patient_phone_home",
		"PATIENT_STREET_ADDRESS_1" => "patient_street_address_1_d",
		"PATIENT_STREET_ADDRESS_2" => "patient_street_address_2_d",
		"PATIENT_CITY" => "patient_city_d",
		"PATIENT_STATE" => "patient_state_d",
		"PATIENT_ZIP" => "patient_zip_d",
		"PATIENT_COUNTY" => "patient_county_d",
		"JURISDICTION_NM" => "jurisdiction_nm_d",
		"lab_test_nm" => "lab_test_nm_2",
		"coded_result_val_desc" => "coded_result_val_desc",
		"interpretation_flg" => "interpretation_flg",
		"numeric_result_val" => "numeric_result_val",
		"result_units" => "result_units",
		"test_method_cd" => "test_method_cd"
	];
	
	if (!empty($fields[$column_name])) {
		return [1, $fields[$column_name]];
	} else {
		return [0, "no associated field for column $column_name"];
	}
}

function import_data_row($row) {
	global $module;
	global $headers;
	global $headers_flipped;
	global $records;
	
	// goal is to create an array for each form that needs importing, then $module->saveData or $module->saveInstanceData
	$module->llog("row: " . print_r($row, true));
	
	/*
		get patientid value (which acts as record ID)
			will either be in column "patientID" or "PATIENT_LOCAL_ID"
		make form arrays as applicable
			one for: xdro_registry (non-instanced), demographics, antimicrobial_susceptibilities_and_resistance_mech
		save them w/ saveData or saveInstanceData
		collect/collate save results for response to client
	*/
	$data = [];
	$data["xdro_registry"] = [];
	$data["demographics"] = [];
	$data["antimicrobial_susceptibilities_and_resistance_mech"] = [];
	$data["errors"] = [];
	
	foreach ($row as $i => $value) {
		if ($value == "NULL")
			continue;
		
		$column_name = $headers[$i];
		
		// either add error to return array or set assoc_form to be form string value
		$assoc_form = get_assoc_form($column_name);
		if (!$assoc_form[0])
			$ret[] = [false, $assoc_form[1]];
		$assoc_form = $assoc_form[1];
		
		
		$assoc_field = get_assoc_field($column_name);
		if (!$assoc_field[0])
			$ret[] = [false, $assoc_field[1]];
		$assoc_form = $assoc_field[1];
		
		// return errors if we've collected any
		if (count($ret) > 0) {
			$module->llog("ret: " . print_r($ret, true));
			return $ret;
		}
		
		$module->llog("\$column_name: $column_name");
		$module->llog("\$assoc_form: $assoc_form");
		$module->llog("\$assoc_field: $assoc_field");
		$data[$assoc_form][$assoc_field] = $value;
	}
	
	$module->llog("data for row: " . print_r($data, true));
	// do insert/update via $module->saveData / saveInstanceData
}

// use PHPSpreadsheet (php 5.6 | 7.x version)
require "libs/PhpSpreadsheet/vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// check workbook file, load if ok
try {
	checkWorkbookFile("client_file");
	$upload_path = $_FILES["client_file"]["tmp_name"];
	
	// at this point, we know checkWorkbookFile didn't exit with errors
	$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
	$reader->setReadDataOnly(TRUE);
	$workbook = $reader->load($upload_path);
	
	// cleanup
	unlink($upload_path);
	
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
	$json->errors[] = "There was an error reading the file uploaded: $e";
	exit(json_encode($json));
}

$rid_field = $module->framework->getRecordIdField($pid);
$getdata = json_decode(\REDCap::getData($pid, 'json', null, [$rid_field, "patient_first_name_d"]));
$records = [];
foreach($getdata as $rec) {
	$records[$rec->$rid_field] = $rec;
}

$module->llog("records: " . print_r($records, true));
// exit("{}");

$sheet = $workbook->getActiveSheet();
$headers = [];
foreach ($sheet->getRowIterator() as $i => $row) {
	if ($i == 1) {
		// build headers array for future referencing
		$cell_iter = $row->getCellIterator();
		foreach($cell_iter as $cell) {
			$headers[] = $cell->getValue();
		}
		$headers_flipped = array_flip($headers);
		// $module->llog("headers: " . print_r($headers, true));
	} else {
		$range = "A$i:" . number_to_column(count($headers)) . "$i";
		import_data_row(reset($sheet->rangeToArray($range)));
	}
}

$json->success = true;
exit(json_encode($json));