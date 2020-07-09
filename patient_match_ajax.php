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
$pid = $module->getProjectId();
$module->nlog();

// make object that will hold our response
$json = new \stdClass();
$json->errors = [];

$module->llog("\$_POST: " . print_r($_POST, true));

/*
	function definitions
*/

function get_next_instance($record, $form_name) {
	global $module;
	$pid = $module->framework->getProjectId();
	$eid = $module->getFirstEventId($pid);
	
	if (empty($record["repeat_instances"][$eid][$form_name])) {
		return 1;
	} else {
		return max(array_keys($record["repeat_instances"][$eid][$form_name])) + 1;
	}
}


// $json->success = true;
exit(json_encode($json));



