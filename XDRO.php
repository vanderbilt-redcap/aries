<?php
namespace Vanderbilt\XDRO;

class XDRO extends \ExternalModules\AbstractExternalModule {
	public $log_desc = "XDRO Module";
	
	public function __construct() {
		parent::__construct();
	}
	
	// given a user supplied string, search for records in our patient registry that might match
	function autocomplete_search() {
		$searchString = $_GET['searchString'];
		
		if (empty($searchString)) {
			echo ("[]");
			return;
		}
		
		// tokenize query
		$tokens = explode(' ', $searchString);
		
		// $this->rlog("tokens:\n" . print_r($tokens, true) . "\n");
		
		// get all records (only some fields though)
		$params = [
			"project_id" => $_GET['pid'],
			"return_format" => "json",
			"fields" => ['patientid', 'patient_dob', 'patient_first_name', 'patient_last_name', 'patient_current_sex_d', 'patient_street_address_1_d']
		];
		$records = json_decode(\REDCap::getData($params), true);
		
		// add search score value to each record
		foreach ($records as &$record) {
			$record['score'] = 0;
		}
		
		// $this->rlog("found records:\n" . print_r($records, true) . "\n");
		
		foreach ($tokens as $token) {
			// $this->rlog("processing token $token:\n");
			
			// let's determine if this token is a valid date
			try {
				$date = new \DateTime($token);
			} catch (\Exception $e) {
				$date = null;
			}
			
			$clean_token = strtolower(preg_replace('/[\W\d]/', '', $token));
			// $this->rlog("clean($token) = $clean_token\n");
			
			// add to record's score if it has a first or last name similar to token
			foreach ($records as &$record) {
				$first_nm = strtolower(preg_replace('/[\W\d]/', '', $record["patient_first_name"]));
				$last_nm = strtolower(preg_replace('/[\W\d]/', '', $record["patient_last_name"]));
				// $this->rlog("$first_nm, $last_nm, $clean_token\n");
				if (strpos($first_nm, $clean_token) !== false and !$record['first_name_scored']) {
					// $this->rlog("$first_nm $last_nm matched first name with token $clean_token" . "\n");
					$record['first_name_scored'] = true;
					$record['score']++;
				}
				if (strpos($last_nm, $clean_token) !== false and !$record['last_name_scored']) {
					// $this->rlog("$first_nm $last_nm matched last name with token $clean_token" . "\n");
					$record['last_name_scored'] = true;
					$record['score']++;
				}
				if (!empty($date)) {
					// $this->rlog("processing token $token as date:\n");
					$mdyDateString = $date->format("m/d/Y");
					
					if ($record['patient_dob'] == $mdyDateString and !$record['dob_scored']) {
						// $this->rlog("$first_nm $last_nm matched first name with token $token" . "\n");
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
		
		// $this->rlog("removed records with score < 0:\n" . print_r($records, true) . "\n\n");
		
		if (empty($records)) {
			echo "[]";
			return;
		}
		
		// sort remaining records by score descending
		usort($records, function($a, $b) {
			return $b['score'] - $a['score'];
		});
		
		$this->llog("sorted remaining records by score:\n" . print_r($records, true) . "\n\n");
		
		echo(json_encode($records));
	}

	private function nlog() {
		if (file_exists("C:/vumc/log.txt")) {
			file_put_contents("C:/vumc/log.txt", "constructing XDRO instance\n");
		}
	}
	
	private function llog($text) {
		if (file_exists("C:/vumc/log.txt")) {
			file_put_contents("C:/vumc/log.txt", "$text\n", FILE_APPEND);
		}
	}
	
	private function rlog($msg) {
		\REDCap::logEvent("XDRO Module", $msg);
	}
}

if ($_GET['action'] == 'predictPatients') {
	$module = new XDRO();
	$module->autocomplete_search();
}
