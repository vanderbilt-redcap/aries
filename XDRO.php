<?php
namespace Vanderbilt\XDRO;
class XDRO extends \ExternalModules\AbstractExternalModule {

	public $log_desc = "XDRO Module";
	
	public function __construct() {
		parent::__construct();
		$this->nlog();
		$this->auth_data_raw = $this->framework->getSystemSetting('auth_data');
		if (empty($this->auth_data_raw)) {
			$this->auth_data_raw = "{}";
		}
		$this->auth_data = json_decode($this->auth_data_raw);
		
		$pid = $this->framework->getProjectId();
		if (!empty($pid))
			$this->project = new \Project($pid);
	}
	
	// given a user supplied string, search for records in our patient registry that might match
	function search($query_string, $limit = null) {
		// $searchString = $_GET['searchString'];
		$searchString = $query_string;
		
		if (empty($searchString)) {
			return [];
		}
		
		// tokenize query
		$tokens = explode(' ', $searchString);
		
		// $this->rlog("tokens:\n" . print_r($tokens, true) . "\n");
		
		// get all records (only some fields though)
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
		$records = $this->squish_demographics($records);
		
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
			return [];
		}
		
		// sort remaining records by score descending
		usort($records, function($a, $b) {
			return $b['score'] - $a['score'];
		});
		
		if (empty($limit)) {
			return $records;
		} else {
			return array_slice($records, 0, $limit);
		}
		return [];
	}
	
	function structured_search($query_obj) {
		// get all records (only some fields though)
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
		$records = \REDCap::getData($params);
		$records = $this->squish_demographics($records);
		
		// add relevance score to each record
		foreach ($records as &$record) {
			$this->score_record_by_array($record, (array) $query_obj);
		}
		
		// remove records with zero score
		$records = array_filter($records, function($record) {
			return $record['score'] != 0;
		});
		
		if (empty($records)) {
			return [];
		}
		
		// sort remaining records by score descending
		usort($records, function($a, $b) {
			return $a['score'] < $b['score'] ? 1: -1;
		});
		
		if (empty($limit)) {
			return $records;
		} else {
			return array_slice($records, 0, $limit);
		}
		return [];
	}
	
	function score_record_by_array(&$record, $tokens_arr) {
		// final score should be a relevance score of [0, 1] where 0 is not relevant and 1 is exact match
		$score = 0;
		$scores = [];
		$sum = 0;
		
		// compare query fields with record field values to update score
		foreach($record as $field => $value) {
			if (empty($tokens_arr[$field]))
				continue;
			
			// patient_current_sex should score is 0 or 1, matched exactly or not
			$a = strtolower(strval($value));
			$b = strtolower(strval($tokens_arr[$field]));
			if ($field == 'patient_current_sex') {
				if ($a == $b) {
					$similarity = 1;
				} else {
					$similarity = 0;
				}
			} else {
				$lev_dist = levenshtein($a, $b);
				if ($lev_dist == -1) {
					$similarity = 0;
				} else {
					$len = max(strlen($a), strlen($b));
					$similarity = ($len - $lev_dist) / $len;
				}
			}
			
			$scores[$field] = $similarity;
			$score += $similarity;
			$this->llog("$field similarity ($a vs $b) = $similarity");
			$sum++;
		}
		
		if ($sum > 0)
			$score = $score / $sum;
		
		$record["score"] = $score;	// score should be in range [0, 1], 0 if no matching params, 1 if all match exactly
		$record["scores"] = $scores;
		// $this->llog("\$record after inserting score: " . print_r($record, true));
	}
	
	function score_record_by_string(&$record, $tokens_str) {
		$score = 0;
		$sum = 0;
		
		// tokenize query
		$tokens = explode(' ', $tokens_str);
		
		foreach($tokens as $token) {
			// if it's a date, compare to dob
			try {
				$date = new \DateTime($token);
			} catch (\Exception $e) {
				$date = null;
			}
			if (!empty($date)) {
				// $this->llog("processing token $token as date:\n");
				$mdyDateString = $date->format("m/d/Y");
				
				if ($record['patient_dob'] == $mdyDateString) {
					$record['dob_scored'] = true;
					$record['score']++;
				}
			}
		}
		
		foreach($record as $field => $value) {
			$a = strtolower(strval($value));
			if (empty($tokens_arr[$field]))
				continue;
			$b = strtolower(strval($tokens_arr[$field]));
			$lev_dist = levenshtein($a, $b);
			if ($lev_dist == -1)
				break;
			$len = max(strlen($a), strlen($b));
			$similarity = ($len - $lev_dist) / $len;
			$score += $similarity;
			// $this->llog("similarity for $a vs $b: $similarity");
			$sum++;
		}
		
		if ($sum > 0)
			$score = $score / $sum;
		
		$record["score"] = $score;	// score should be in range [0, 1], 0 if no matching params, 1 if all match exactly
		// $this->llog("\$record after inserting score: " . print_r($record, true));
	}
	
	//	return array of flat arrays -- each flat array is a $record that also has values from latest demographics instance
	function squish_demographics($records) {
		$ret_array = [];
		$eid = $this->getFirstEventId();
		foreach($records as $rid => $record) {
			// first lets find the most recent demographics instance
			$last_instance = null;
			$last_instance_date = null;
			foreach($record["repeat_instances"][$eid]["demographics"] as $demo_index => $demo) {
				if (empty($last_instance_date)) {
					$last_instance = $demo;
					$last_instance_date = $demo["patient_last_change_time"];
				} elseif (strtotime($demo["patient_last_change_time"]) > strtotime($last_instance_date)) {
					$last_instance = $demo;
					$last_instance_date = $demo["patient_last_change_time"];
				}
			}
			if (empty($last_instance))
				continue;
			
			// add values to $record array
			$flat_array = [];
			foreach($record[$eid] as $key => $val) {
				$flat_array[$key] = $val;
				// $this->llog("setting \$flat_array[$key] = $val");
			}
			foreach($last_instance as $key => $val) {
				if (!empty($val))
					$flat_array[$key] = $val;
				// $this->llog("setting \$flat_array[$key] = $val");
			}
			$ret_array[] = $flat_array;
		}
		return $ret_array;
	}
	
	function getFieldLabels($field) {
		$labels = [];
		
		$label_pattern = "/(\d+),?\s?(.+?)(?=\x{005c}\x{006E}|$)/";
		$label_string = $this->project->metadata[$field]["element_enum"];
		preg_match_all($label_pattern, $label_string, $matches);
		if (!empty($matches[2]))
			return $matches[2];
	}
	
	function save_auth_data() {
		$this->framework->setSystemSetting('auth_data', json_encode($this->auth_data));
		$this->llog("saved auth_data: " . print_r($this->auth_data, true));
	}
	
	function get_next_user_id() {
		$maxid = 1;
		foreach($this->auth_data->users as $user) {
			$maxid = max($user->id + 1, $maxid);
		}
		return $maxid;
	}
	
	function get_next_facility_id() {
		$maxid = 1;
		foreach($this->auth_data->facilities as $fac) {
			$maxid = max($fac->id + 1, $maxid);
		}
		return $maxid;
	}
	
	function nlog() {
		if (file_exists("C:/vumc/log.txt")) {
			file_put_contents("C:/vumc/log.txt", "constructing XDRO instance\n");
		}
	}
	
	function llog($text) {
		if (file_exists("C:/vumc/log.txt")) {
			file_put_contents("C:/vumc/log.txt", "$text\n", FILE_APPEND);
		}
	}
	
	function rlog($msg) {
		\REDCap::logEvent("XDRO Module", $msg);
	}
}

$module = new XDRO();

if ($_GET['action'] == 'predictPatients') {
	$module = new XDRO();
	$query = filter_var($_GET['searchString'], FILTER_SANITIZE_STRING);
	$recs = $module->search($query, 7);	// limit to 7 records for autocomplete
	echo(json_encode($recs));
}
