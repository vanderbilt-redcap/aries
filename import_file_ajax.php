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
	/*
		read uploaded workbook file data
	*/
	try {
	} catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
		// \REDCap::logEvent("DPP import failure", "PhpSpreadsheet library errors -> " . print_r($e, true) . "\n", null, $rid, $eid, PROJECT_ID);
		exit(json_encode([
			"error" => true,
			"notes" => [
				"There was an issue loading the workbook. Make sure it is an .xlsx file with a worksheet named 'DPP Sessions'.",
				"If you believe your file is a valid DPP Workbook file, please contact your REDCap administrator."
			]
		]));
	}
	return $workbook;
}




// connect to REDCap db
require_once (APP_PATH_TEMP . "../redcap_connect.php");

// import PHPSpreadsheet (php 5.6 version)
require "libs/PhpSpreadsheet/vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

try {
	$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
	$reader->setReadDataOnly(TRUE);
	$workbook = $reader->load($_FILES[$file_param_name]["tmp_name"]);
	unlink($_FILES[$file_param_name]["tmp_name"]);
} catch (\Exception $e) {
	$json->errors[] = "There was an error reading the file uploaded: $e";
	exit(json_encode($json));
}

// $info = [
	// "files" => print_r($_FILES, true),
	// "post" => print_r($_POST, true)
// ];

// $module->llog('info:\n' . print_r($info, true));

$json->success = true;
exit(json_encode($json));