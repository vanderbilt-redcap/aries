<?php
namespace Vanderbilt\XDRO;

class XDRO extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
	}
	
	// given a user supplied string, search for records in our patient registry that might match
	function autocomplete_search() {
		// file_put_contents("C:/vumc/log.txt", "logging patient search predict:\n");
		function llog($text) {
			// file_put_contents("C:/vumc/log.txt", "$text\n", FILE_APPEND);
		}
		
		$searchString = $_GET['searchString'];
		
		if (empty($searchString)) {
			echo ("[]");
			return;
		}
		
		// tokenize query
		$tokens = explode(' ', $searchString);
		
		llog("tokens:\n" . print_r($tokens, true) . "\n");
		
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
		
		llog("found records:\n" . print_r($records, true) . "\n");
		
		foreach ($tokens as $token) {
			llog("processing token $token:\n");
			
			// let's determine if this token is a valid date
			try {
				$date = new \DateTime($token);
			} catch (\Exception $e) {
				$date = null;
			}
			
			$clean_token = strtolower(preg_replace('/[\W\d]/', '', $token));
			llog("clean($token) = $clean_token\n");
			
			// add to record's score if it has a first or last name similar to token
			foreach ($records as &$record) {
				$first_nm = strtolower(preg_replace('/[\W\d]/', '', $record["patient_first_nm"]));
				$last_nm = strtolower(preg_replace('/[\W\d]/', '', $record["patient_last_nm"]));
				llog("$first_nm, $last_nm, $clean_token\n");
				if (strpos($first_nm, $clean_token) !== false and !$record['first_name_scored']) {
					llog("$first_nm $last_nm matched first name with token $clean_token" . "\n");
					$record['first_name_scored'] = true;
					$record['score']++;
				}
				if (strpos($last_nm, $clean_token) !== false and !$record['last_name_scored']) {
					llog("$first_nm $last_nm matched last name with token $clean_token" . "\n");
					$record['last_name_scored'] = true;
					$record['score']++;
				}
				if (!empty($date)) {
					llog("processing token $token as date:\n");
					$mdyDateString = $date->format("m/d/Y");
					
					if ($record['patient_dob'] == $mdyDateString and !$record['dob_scored']) {
						llog("$first_nm $last_nm matched first name with token $token" . "\n");
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
		
		llog("removed records with score < 0:\n" . print_r($records, true) . "\n\n");
		
		if (empty($records)) {
			echo "[]";
			return;
		}
		
		// sort remaining records by score descending
		usort($records, function($a, $b) {
			return $b['score'] - $a['score'];
		});
		
		llog("sorted remaining records by score:\n" . print_r($records, true) . "\n\n");
		
		echo(json_encode($records));
	}
}

if ($_GET['action'] == 'predictPatients') {
	$module = new XDRO();
	$module->autocomplete_search();
}