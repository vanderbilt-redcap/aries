<?php
namespace Vanderbilt\XDRO;

class XDRO extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
	}
	
	// given a user supplied string, search for records in our patient registry that might match
	function patient_search($search_string) {
		return 'abc';
	}
}

if ($_GET['action'] == 'predictPatients') {
	// file_put_contents("C:/log.txt", "logging patient search predict:\n");
	$searchString = $_GET['searchString'];
	
	// tokenize query
	$tokens = explode(' ', $searchString);
	
	// gather record_ids that have a matching name or dob
	$record_ids = [];
	
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
				$record_ids[] = (int) $rid;
			}
		}
	}
	
	// filter duplicate record IDs out of $record_ids
	$record_ids = array_unique($record_ids);
	
	// return formatted results
	$results = [];
	
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
	
	exit(json_encode($results));
}