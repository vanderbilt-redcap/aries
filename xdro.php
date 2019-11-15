<?php
namespace Vanderbilt\XDRO;

class XDRO extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
	}
	
	// given a user supplied string, search for records in our patient registry that might match
	function autocomplete_search() {
		$log_filepath = "C:/vumc/log.txt";
		file_put_contents($log_filepath, "logging patient search predict:\n");
		$searchString = $_GET['searchString'];
		
		if (empty($searchString)) {
			echo ("[]");
			return;
		}
		
		// tokenize query
		$tokens = explode(' ', $searchString);
		
		file_put_contents($log_filepath, "tokens:\n" . print_r($tokens, true) . "\n\n", FILE_APPEND);
		
		// get all records (only some fields though)
		$params = [
			"project_id" => $_GET['pid'],
			"return_format" => "json",
			"fields" => ['record_id', 'patient_dob', 'patient_first_nm', 'patient_last_nm', 'curr_sex_cd', 'street_addr_1']
		];
		$records = json_decode(\REDCap::getData($params), true);
		
		// add search score value to each record
		foreach ($records as &$record) {
			$record['score'] = 0;
		}
		
		file_put_contents($log_filepath, "found records:\n" . print_r($records, true) . "\n\n", FILE_APPEND);
		// echo "[]";
		// return;
		
		foreach ($tokens as $token) {
			file_put_contents($log_filepath, "processing token $token:\n", FILE_APPEND);
			
			// let's determine if this token is a valid date
			try {
				$date = new \DateTime($token);
			} catch (\Exception $e) {
				$date = null;
			}
			
			$clean_token = strtolower(preg_replace('/[\W\d]/', '', $token));
			file_put_contents($log_filepath, "clean($token) = $clean_token\n", FILE_APPEND);
			
			// add to record's score if it has a first or last name similar to token
			foreach ($records as &$record) {
				$first_nm = strtolower(preg_replace('/[\W\d]/', '', $record["patient_first_nm"]));
				$last_nm = strtolower(preg_replace('/[\W\d]/', '', $record["patient_last_nm"]));
				file_put_contents($log_filepath, "$first_nm, $last_nm, $clean_token\n", FILE_APPEND);
				if (strpos($first_nm, $clean_token) !== false and !$record['first_name_scored']) {
					file_put_contents($log_filepath, "$first_nm $last_nm matched first name with token $clean_token" . "\n", FILE_APPEND);
					$record['first_name_scored'] = true;
					$record['score']++;
				}
				if (strpos($last_nm, $clean_token) !== false and !$record['last_name_scored']) {
					file_put_contents($log_filepath, "$first_nm $last_nm matched last name with token $clean_token" . "\n", FILE_APPEND);
					$record['last_name_scored'] = true;
					$record['score']++;
				}
				if (!empty($date)) {
					file_put_contents($log_filepath, "processing token $token as date:\n", FILE_APPEND);
					$mdyDateString = $date->format("m/d/Y");
					
					if ($record['patient_dob'] == $mdyDateString and !$record['dob_scored']) {
						file_put_contents($log_filepath, "$first_nm $last_nm matched first name with token $token" . "\n", FILE_APPEND);
						$record['dob_scored'] = true;
						$record['score']++;
					}
				}
			}
		}
		
		// remove records with zero score
		$records = array_filter($records, function($record) {
			return $record['score'] != 0;
		});
		
		file_put_contents($log_filepath, "removed records with score < 0:\n" . print_r($records, true) . "\n\n", FILE_APPEND);
		
		if (empty($records)) {
			echo "[]";
			return;
		}
		
		// sort remaining records by score descending
		usort($records, function($a, $b) {
			return $b['score'] - $a['score'];
		});
		
		file_put_contents($log_filepath, "sorted remaining records by score:\n" . print_r($records, true) . "\n\n", FILE_APPEND);
		
		// // return formatted results
		// $results = [];
		
		// // format results from record data
		// foreach ($records as $record) {
			// $results[] = [
				// "first_name" => $record["patient_first_nm"],
				// "last_name" => $record["patient_last_nm"],
				// "record_id" => $record["record_id"],
				// "dob" => $record["patient_dob"],
				// "sex" => $record["curr_sex_cd"],
				// "address" => $record["street_addr_1"]
			// ];
		// }
		
		echo(json_encode($records));
	}
}

if ($_GET['action'] == 'predictPatients') {
	$module = new XDRO();
	$module->autocomplete_search();
}