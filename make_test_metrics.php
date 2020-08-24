<?php

$pid = $module->getProjectId();
$ridfield = \REDCap::getRecordIdField();

//------------------------------
//	make 1-2 match instances for each record with random values
//------------------------------

// $data = json_decode(\REDCap::getData($pid, 'json', null, [$ridfield, 'contact_prior', 'contact', 'facility', 'date_admitted']));
// $newdata = [];
// foreach ($data as $rid => $record) {
	// if (mt_rand(0, 1))
		// continue;
	
	// $instance = new \stdClass();
	// $instance->patientid = $record->patientid;
	// $instance->redcap_repeat_instrument = "metrics";
	// $instance->redcap_repeat_instance = "1";
	// $instance->contact = (string) mt_rand(0, 1);
	// $instance->contact_prior = (string) mt_rand(0, 1);
	// $instance->facility = (string) mt_rand(0, 1);
	
	// $random_date = date("Y-m-d", mt_rand());
	// $instance->date_admitted = $random_date;
	// $newdata[] = $instance;
	
	// if (mt_rand(0, 1) == '0')
		// continue;
	
	// $instance = new \stdClass();
	// $instance->patientid = $record->patientid;
	// $instance->redcap_repeat_instrument = "metrics";
	// $instance->redcap_repeat_instance = "2";
	// $instance->contact = (string) mt_rand(0, 1);
	// $instance->contact_prior = (string) mt_rand(0, 1);
	// $instance->facility = (string) mt_rand(0, 1);
	
	// $random_date = date("Y-m-d", mt_rand());
	// $instance->date_admitted = $random_date;
	// $newdata[] = $instance;
// }

// $module->llog("newdata: " .print_r($newdata, true));
// $save_results = \REDCap::saveData($pid, 'json', json_encode($newdata));

// echo("<pre>");
// // echo print_r($data, true);
// print_r($save_results);
// echo"<br>";
// echo"<br>";
// $data = \REDCap::getData($pid, 'json', null, [$ridfield, 'contact_prior', 'contact', 'facility', 'date_admitted']);
// // print_r($data);
// echo("</pre>");


//------------------------------
//	add a random [disease] value (from set) to each antimicrobial form instance for each patient
//------------------------------

// $params = [
	// "project_id" => $pid,
	// "return_format" => 'json',
	// // "records" => ['PSN11905738TN01'],
	// "fields" => ['disease', 'specimen_collection_dt', 'redcap_repeat_instance', 'redcap_repeat_instrument', "$ridfield"],
	// "filterLogic" => "[specimen_collection_dt] <> ''"
// ];
// $data = json_decode(\REDCap::getData($params));

// $diseases = [
	// 'VRSA', 'VISA', 'C. AURIS', 'HDR', 'CRE', 'ENTEROBACTER', 'S. AUREUS', 'C. DIFFICILE', 'ENTEROCOCCUS', 'M. TUBERCULOSIS', 'M. GENITALIUM', 'STREPTOCOCCUS', 'CAMPYLOBACTER', 'KLEBSIELLA', 'ACINETOBACTER', 'P. AERUGINOSA'
// ];

// foreach ($data as $i => $instance) {
	// if (mt_rand(0, 100) > 85) {
		// $instance->disease = $diseases[mt_rand(0, count($diseases) - 1)];
	// } else {
		// $instance->disease = "";
	// }
// }

// $save_results = \REDCap::saveData($pid, 'json', json_encode($data), 'overwrite');
// echo("<pre>");
// print_r($save_results);
// echo("</pre>");