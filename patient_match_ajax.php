<?php
/*
	This script receives data as JSON from patient_record.php -- this data is added as a new repeated instance of the associated record's "Metrics" form
	
	Execution strategy:
	Validate record_id
		Halt and return error if needed
	If needed, validate other data properties according to business logic
		Halt and return error if needed
	
	If data is valid, add repeated instance and return success message
	
*/

// connect to REDCap
require_once (APP_PATH_TEMP . "../redcap_connect.php");
$module->llog('connected');
/*
	function definitions
*/

function get_next_instance($data, $record_id, $form_name) {
	global $module;
	$next_instance = 1;
	$pid = $module->getProjectId();
	$record_id_field = $module->framework->getRecordIdField($pid);
	
	foreach($data as $i => $obj) {
		if ($obj->$record_id_field !== $record_id || $obj->redcap_repeat_instrument != $form_name) {
			continue;
		}
		$module->llog("obj: " . print_r($obj, true));
		$next_instance = max($next_instance, (int)($obj->redcap_repeat_instance) + 1);
	}
	
	return $next_instance;
}

function validate_data() {
	// data.record_id
	// data.match
	// data.contact_prior
	// data.contact
	// data.facility
	// data.date_admitted
	
	global $module;
	global $json;
	global $pid;
	global $record_data;
	global $record_id_field;
	
	// ensure record ID not empty
	if (empty($_POST['record_id'])) {
		$json->errors[] = "Empty record ID POST parameter";
	}

	// ensure record ID given exists in project // send error if not
	$found_rid = false;
	foreach($record_data as $i => $obj) {
		if ($obj->$record_id_field == $_POST['record_id']) {
			$found_rid = true;
			break;
		}
	}
	if (!$found_rid) {
		$json->errors[] = "Record ID given does not exist in XDRO Registry (REDCap project)";
	}
	
	// validate match/contact booleans
	if (!($_POST['match'] === "true" or $_POST['match'] === "false")) {
		$json->errors[] = "'match' value not true or false";
	}
	if (!($_POST['contact_prior'] === "true" or $_POST['contact_prior'] === "false")) {
		$json->errors[] = "'contact_prior' value not true or false";
	}
	if (!($_POST['contact'] === "true" or $_POST['contact'] === "false")) {
		$json->errors[] = "'contact' value not true or false";
	}
	
	// validate admission date
	$date = \DateTime::createFromFormat("Y-m-d", $_POST["date_admitted"]);
	if ($date === false || array_sum($date->getLastErrors())){
		$json->errors[] = "'date_admitted' is invalid (value: " . print_r($_POST["date_admitted"], true);
	}
	
	if (!empty($json->errors)) {
		exit(json_encode($json));
	}
}

/*
	process data
*/

$pid = $module->getProjectId();
$module->nlog();

// make object that will hold our response
$json = new \stdClass();
$json->errors = [];

// sanitize POST params
$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$module->llog("\$_POST: " . print_r($_POST, true));

// fetch record/instance data
$record_id_field = $module->framework->getRecordIdField($pid);
$params = [
	"project_id" => $pid,
	"return_format" => "json",
	"fields" => [$record_id_field, "match"]	// match is there so we can count metrics instances later
];
$record_data = json_decode(\REDCap::getData($params));

validate_data();

// data valid, save new metrics instance in this patient's record
$obj = new \stdClass();
$obj->$record_id_field = $_POST["record_id"];
$obj->match = $_POST["match"] === "true" ? "1" : "0";
$obj->contact_prior = $_POST["contact_prior"] === "true" ? "1" : "0";
$obj->contact = $_POST["contact"] === "true" ? "1" : "0";
$obj->facility = $_POST["facility"];
$obj->date_admitted = $_POST["date_admitted"];
$obj->redcap_repeat_instrument = "metrics";
$obj->redcap_repeat_instance = get_next_instance($record_data, $_POST['record_id'], "metrics");

$module->llog("obj: " . print_r($obj, true));

$results = \REDCap::saveData($pid, "json", json_encode([$obj]));
$module->llog("results: " . print_r($results, true));

$json->success = true;
$module->llog('success');
exit(json_encode($json));