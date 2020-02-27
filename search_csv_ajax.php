<?php
/*
	This script receives a .csv from the uploader. It checks the file, and if OK, imports each row as a record to REDCap
	
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

$FIELDS = [
	'patientid',
	'patient_dob',
	'patient_first_name',
	'patient_last_name',
	'patient_current_sex',
	'patient_street_address_1'
];

// $module->llog("post: " . print_r($_POST, true));
// $module->llog("files: " . print_r($_FILES, true));

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

function checkFile($file_param_name) {
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
	if (preg_match("/[^A-Za-z0-9. ()-_]/", $_FILES[$file_param_name]["name"])) {
		$json->errors[] = "File names can only contain alphabet, digit, period, space, hyphen, and parentheses characters.";
		$json->errors[] = "	Allowed characters: A-Z a-z 0-9 . ( ) - _";
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

function removeBomUtf8($s){
	if(substr($s,0,3)==chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'))){
		return substr($s,3);
	}else{
		return $s;
	}
}

// check workbook file, load if ok
try {
	checkFile("client_file");
	$upload_path = $_FILES["client_file"]["tmp_name"];
	
	// at this point, we know checkFile didn't exit with errors, let's read data
	// see: https://stackoverflow.com/questions/5813168/how-to-import-csv-file-in-php
	// $row = 1;
	$data = [];
	if (($handle = fopen($upload_path, "r")) !== FALSE) {
		while (($line = fgetcsv($handle)) !== FALSE) {
			$data[] = $line;
		}
		fclose($handle);
	}
	
	if (empty($data)) {
		$json->errors[] = "Uploaded CSV file is empty.";
		exit(json_encode($json));
	}
	
	global $FIELDS;
	$header_map = [];
	$found_at_least_one_usable_header = false;
	$module->llog("row 0: " . print_r($data[0], true));
	foreach($data[0] as $col => $header) {
		$module->llog("col/header : $col / $header");
		if ($col === 0)
			$header = removeBomUtf8($header);
		// $key = array_search(strtolower($header), $FIELDS);
		// if ($key !== FALSE) {
			// $module->llog("\$header found in col: $col");
			// $found_at_least_one_usable_header = true;
			// $header_map[$header] = $col;
		// }
		foreach($FIELDS as $fieldname) {
			// $hex1 = bin2hex($fieldname);
			// $hex2 = bin2hex($header);
			// $module->llog("compare $hex1 | $hex2");
			$module->llog("strtolower($fieldname) == strtolower($header) : " . strval(strtolower($fieldname) == strtolower($header)));
			// $module->llog("strcasecmp($fieldname, $header) : " . strcasecmp($fieldname, $header));
			// if (strcasecmp($fieldname, $header) == 0) {
			if (strtolower($fieldname) == strtolower($header)) {
				// $module->llog("\$header_map[$header] = $col");
				$header_map[$header] = $col;
				$found_at_least_one_usable_header = true;
			}
		}
	}
	
	if (!$found_at_least_one_usable_header) {
		$json->errors[] = "The CSV uploaded must contain at least one of the following values (case insensitive) as a column value: (" . implode(", ", $FIELDS) . ")";
		$json->errors[] = "These are the column values that the XDRO module found: (" . implode(", ", $data[0]) . ")";
		exit(json_encode($json));
	}
	
	// cleanup
	unlink($upload_path);
	
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
	$json->errors[] = "There was an error reading the file uploaded: $e";
	exit(json_encode($json));
}

// get all patient info
$params = [
	"project_id" => $_GET['pid'],
	// "return_format" => "json",
	"return_format" => "array",
	"fields" => [
		'patientid',
		'patient_dob',
		'patient_first_name',
		'patient_last_name',
		'patient_current_sex',
		'patient_street_address_1',
		'patient_last_change_time'
	]
];
// $records = json_decode(\REDCap::getData($params), true);
$records = \REDCap::getData($params);
$records = $module->squish_demographics($records);

$module->llog("header_map: " . print_r($header_map, true));
exit("{}");
$json->rows = [];		// each row will get query, results
foreach ($data as $i => $row) {
	// skip header row of course
	if ($i === 0)
		continue;
	
	$module->llog("processing file row: $i" . "\n -- row: " . print_r($row, true));
	$query_array = [];
	foreach($header_map as $header => $key) {
		if (!empty($row[$key]))
			$query_array[$header] = $row[$key];
	}
	
	$row = new stdClass();
	$row->query = $query_array;
	$row->results = [];
	
	$module->llog("\$query_array: " . print_r($query_array, true));
	foreach($records as $record) {
		$module->score_record_by_array($record, $query_array);
		$module->llog("record: " . print_r($record, true));
		if ($record["score"] > 0.2)
			$row->results[] = $record;
	}
}

$json->success = true;
exit(json_encode($json));