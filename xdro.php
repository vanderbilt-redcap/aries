<?php
namespace Vanderbilt\XDRO;

class XDRO extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
	}
	
	// given a user supplied string, search for records in our patient registry that might match
	function autocomplete_search($search_string) {
		return 'abc';
	}
}

if ($_GET['action'] == 'predictPatients') {
	$module = new XDRO();
	
	$log_filepath = "C:/vumc/log.txt";
	file_put_contents($log_filepath, "logging patient search predict:\n");
	$searchString = $_GET['searchString'];
	
	if (empty($searchString)) {
		echo ("[]");
		return;
	}
	
	// tokenize query
	$tokens = explode(' ', $searchString);
	
	// gather record_ids that have a matching name or dob
	$record_ids = [];
	
	// get all records (only some fields)
	$params = [
		"project_id" => $_GET['pid'],
		"return_format" => "json",
		"fields" => ['record_id', 'patient_dob', 'patient_first_nm', 'patient_last_nm', 'curr_sex_cd', 'street_addr_1']
	];
	$records = json_decode(\REDCap::getData($params), true);
	
	file_put_contents($log_filepath, "foudn records:\n" . print_r($records, true));
	echo "[]";
	return;
	
	foreach ($tokens as $token) {
		// let's determine if this token is a valid date
		try {
			$date = new \DateTime($token);
		} catch (\Exception $e) {
			$date = null;
		}
		
		if ($date) {
			$mdyDateString = $date->format("m/d/Y");
			
			// prepare parameters
			$params = [
				"project_id" => $_GET['pid'],
				"filterLogic" => "[patient_dob]='$mdyDateString'"
			];
			
			// see if any matching records
			$records = \REDCap::getData($params);
			
			// collect record IDs into $record_ids
			foreach ($records as $rid => $record) {
				file_put_contents($log_filepath, "record $rid matched " . $params["filterLogic"] . "\n", FILE_APPEND);
				$record_ids[] = (int) $rid;
			}
		} else {
			// prepare parameters
			$params = [
				"project_id" => $_GET['pid'],
				"filterLogic" => "[patient_first_nm]='$token' or [patient_last_nm]='$token'"
			];
			
			// see if any matching records
			$records = \REDCap::getData($params);
			
			// collect record IDs into $record_ids
			foreach ($records as $rid => $record) {
				file_put_contents($log_filepath, "record $rid matched " . $params["filterLogic"] . "\n", FILE_APPEND);
				$record_ids[] = (int) $rid;
			}
		}
	}
	
	file_put_contents($log_filepath, "record ids: " . print_r($record_ids, true) . "\n", FILE_APPEND);
	
	// filter duplicate record IDs out of $record_ids
	$record_ids = array_unique($record_ids);
	
	
	// return formatted results
	$results = [];
	
	if (empty($record_ids)) {
		echo "[]";
		return;
	}
	
	$records = json_decode(\REDCap::getData($_GET['pid'], 'json', $record_ids), true);
	foreach ($records as $rid => $record) {
		$results[] = [
			"first_name" => $record["patient_first_nm"],
			"last_name" => $record["patient_last_nm"],
			"record_id" => $record["record_id"],
			"dob" => $record["patient_dob"],
			"sex" => $record["curr_sex_cd"],
			"address" => $record["street_addr_1"]
		];
	}
	
	echo(json_encode($results));
}