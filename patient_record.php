<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";
// require_once APP_PATH_DOCROOT . 'ProjectGeneral' . DIRECTORY_SEPARATOR. 'header.php';

$module->nlog();
$rid = $_GET['rid'];
$module->llog("fetching patient_record for rid: $rid");

function sort_demographics($a, $b) {
	// global $module;
	// $module->llog("\$a[\"patient_last_change_time\"]: " . print_r($a["patient_last_change_time"], true));
	if (strtotime($a["patient_last_change_time"]) > strtotime($b["patient_last_change_time"]))
		return 1;
	if (strtotime($a["patient_last_change_time"]) < strtotime($b["patient_last_change_time"]))
		return -1;
	return 0;
}

if (empty($rid)) {
	// TODO what to show when wrong Record ID or missing??
} else {
	$pid = $module->getProjectId();
	$project = new \Project($pid);
	$eid = $project->firstEventId;
	$record = \REDCap::getData($pid, 'array', $rid);
	
	// allow for easier access to specific form values
	$xdro_registry = $record[$rid][$eid];
	
	// sort demographics by [patient_last_change_time]
	// $module->llog("before sort:");
	// foreach($record[$rid]["repeat_instances"][$eid]["demographics"] as $demo) {
		// $module->llog($demo["patient_last_change_time"]);
	// }
	usort($record[$rid]["repeat_instances"][$eid]["demographics"], "sort_demographics");
	// $module->llog("after sort:");
	// foreach($record[$rid]["repeat_instances"][$eid]["demographics"] as $demo) {
		// $module->llog($demo["patient_last_change_time"]);
	// }
	
	$last_demo_index = max(array_keys($record[$rid]["repeat_instances"][$eid]["demographics"]));
	$last_demo = $record[$rid]["repeat_instances"][$eid]["demographics"][$last_demo_index];
	
	$anti_inst_count = count($record[$rid]["repeat_instances"][$eid]["antimicrobial_susceptibilities_and_resistance_mech"]);
	$last_anti_index = max(array_keys($record[$rid]["repeat_instances"][$eid]["antimicrobial_susceptibilities_and_resistance_mech"]));
	$last_lab = $record[$rid]["repeat_instances"][$eid]["antimicrobial_susceptibilities_and_resistance_mech"][$last_anti_index];
	
	$all_labs = $record[$rid]["repeat_instances"][$eid]["antimicrobial_susceptibilities_and_resistance_mech"];
	$all_demographics = $record[$rid]["repeat_instances"][$eid]["demographics"];
	
	$address_td = "";
	if (!empty($last_demo["patient_street_address_1"]))
		$address_td .= $last_demo["patient_street_address_1"];
	if (!empty($last_demo["patient_street_address_2"]))
		$address_td .= "<br>" . $last_demo["patient_street_address_2"];
	$address_td .= "<br>" . $last_demo["patient_city"] . ", " . $last_demo["patient_state"] . " " . $last_demo["patient_zip"];
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>XDRO | REDCap</title>
	<meta name="googlebot" content="noindex, noarchive, nofollow, nosnippet">
	<meta name="robots" content="noindex, noarchive, nofollow">
	<meta name="slurp" content="noindex, noarchive, nofollow, noodp, noydir">
	<meta name="msnbot" content="noindex, noarchive, nofollow, noodp">
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="expires" content="0">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--[if IE 9]>
	<link rel="stylesheet" type="text/css" href="/redcap/redcap_v9.5.14/Resources/css/bootstrap-ie9.min.css">
	<script type="text/javascript">$(function(){ie9FormFix()});</script>
	<!--<![endif]-->
</head>
<body>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

<link rel="stylesheet" href="<?=$module->getUrl('css/record.css')?>"/>
<script type="text/javascript" src="<?=$module->getUrl('js/patient_record.js')?>"></script>
<script type="text/javascript" >
	XDRO.pid = "<?php echo $pid;?>";
	XDRO.rid = "<?php echo $rid;?>";
	XDRO.eid = "<?php echo $eid;?>";
	XDRO.demographics = '<?php echo json_encode($all_demographics);?>';
</script>
<div id="main">
<div id='header' class='row'>
	<div class='logo column'>
		<span id='xdro-title'>xdro</span>
		<img id='tdh-logo' src="<?=$module->getUrl('res/tdh-logo.png')?>"></img>
	</div>
	<div class='header-info column'>
		<div id='patient-match' class='bluefont'>
			<span>Is this patient a match?</span>
			<div class="form-check">
				<input class="form-check-input" type="radio" name="header_radios" id="header_radio_1" value="1">
				<label class="form-check-label" for="header_radios">Yes</label>
			</div>
			<div class="form-check">
				<input class="form-check-input" type="radio" name="header_radios" id="header_radio_0" value="0">
				<label class="form-check-label" for="header_radios">No</label>
			</div>
		</div>
		<p id='ip-blurb' class='bluefont pt-2'>Please consider an Infectious Disease consult and make sure facility Infection Preventionist is aware.</p>
		<div id='registry-title'><h1>Extensively Drug Resistant Organism Registry</h1></div>
		<span><b>RESULT DATE:</b> <?=$last_lab["resulted_dt"]?></span>
		<div id='test-results'>
			<span><b>TEST RESULTS</b></span>
			<span class='redfont'><b>ALERT: <?=$last_lab["disease"]?></b></span>
			<span><b>ORGANISM: </b><i><?=$last_lab["lab_test_nm"]?></i></span>
		</div>
	</div>
</div>
<div id='record'>
	<div class='column'>
		<div id='patient-info'>
			<table class='mb-3 mt-1 simpletable'>
				<tr><th>FIRST NAME: </th><td><?=$last_demo["patient_first_name"]?></td></tr>
				<tr><th>LAST NAME: </th><td><?=$last_demo["patient_last_name"]?></td></tr>
				<tr><th>DATE OF BIRTH: </th><td><?=$xdro_registry["patient_dob"]?></td></tr>
				<tr><th>GENDER: </th><td><?=$last_demo["patient_current_sex"]?></td></tr>
				<tr><th>ADDRESS: </th><td><?=$address_td?></td></tr>
				<tr><th>COUNTY: </th><td><?=$last_demo["patient_county"]?></td></tr>
				<tr><th>RESIDENCE: </th><td><?=$last_demo["patient_state"]?></td></tr>
				<tr><th>JURISDICTION: </th><td><?=$last_demo["jurisdiction_nm"]?></td></tr>
			</table>
			<span class='px-3 mb-3'>Click <a href='' id="modal_link" data-toggle='modal' data-target='.modal'><b>HERE</b></a> for more demographic information</span>
		</div>
		<div id='lab-info' class='mb-3 mt-3'>
			<table class='mb-3 mt-1 simpletable'>
				<tr><th>ORDERING FACILITY: </th><td><?=$last_lab["ordering_facility"]?></td></tr>
				<tr><th>ORDERING PROVIDER: </th><td><?=$last_lab["providername"] . "<br>&emsp;" . $last_lab["provider_address"] . "<br>&emsp;" . $last_lab["providerphone"]?></td></tr>
				<tr><th>PERFORMING FACILITY: </th><td class='redfont'></td></tr>
				<tr><th>REPORTING FACILITY: </th><td class='redfont'><?=$last_lab["reporting_facility"] . "<br>&emsp;" . $last_lab["reportername"] . "<br>&emsp;" . $last_lab["reporterphone"]?></td></tr>
			</table>
		
			<span><b>ORDERED TEST: </b> <?=$last_lab["ordered_test_nm"]?></span>
			<span><b>SPECIMEN SOURCE: </b> <?=$last_lab["specimen_desc"]?></span>
			<span><b>RESULTED TEST: </b> <?=$last_lab["lab_test_nm"]?></span>
			<span><b>DATE SPECIMEN COLLECTED: </b> <?=$last_lab["specimen_collection_dt"]?></span>
			<span><b>STATUS: </b> <?=$last_lab["lab_test_status"]?></span>
		</div>
		<div id='action-items' class='bluefont'>
			<span class='text-center d-block mb-2 mt-1'><b><u>Action Items for <?=$xdro_registry["condition_alert"]?>:</b></u></span>
			<ul>
				<li class='mb-1'><?=$xdro_registry['alert_1']?></li>
				<li class='mb-1'><?=$xdro_registry['alert_2']?></li>
				<li class='mb-1'><?=$xdro_registry['alert_3']?></li>
				<li class='mb-1'><?=$xdro_registry['alert_4']?></li>
			</ul>
		</div>
	</div>
	<div class='column'>
		<span id='please-note' class='bluefont d-block text-right mt-3 mb-3'><li><b>Please note other tests performed on this patient.</b></li></span>
		<div class='captions text-right mb-1'>
			<span><i>S/I/R interpretation are baased on CLSI breakpoints.</i></span><br>
			<span><i>MIC (μg/ml) results are directly from lab report.</i></span><br>
			<span><i>Kirby-Bauer (KB) disk diffusion susceptibility test result if applicable.</i></span>
		</div>
		<div id='susceptibilities' class='mb-3'>
			<table>
				<thead>
					<tr>
						<th colspan='4'><b>ANTIMICROBIAL SUSCEPTIBILITIES</b></th>
					</tr>
					<tr>
						<th></th>
						<th><b>Result</b></th>
						<th><b>S/I/R Interpretation</b></th>
						<th><b>MIC (μg/ml) or KB</b></th>
					</tr>
				</thead>
				<tbody>
					<?php
						///////////
						foreach ($all_labs as $lab_i => $lab) {
							$test = $lab["lab_test_nm_2"];
							if (empty($test))
								continue;
							
							$result = $lab["coded_result_val_desc"];
							$sir = $lab["interpretation_flg"];
							$val_1 = $lab["numeric_result_val"];
							$val_2 = $lab["result_units"];
							$val_3 = $lab["test_method_cd"];
							$val = "";
							if (!empty($val_1))
								$val .= "<br>" . $val_1;
							if (!empty($val_2))
								$val .= "<br>" . $val_2;
							if (!empty($val_3))
								$val .= "<br>" . $val_3;
							
							if ($sir == "R" || strpos($val, ">=4") != FALSE) {
								$class = " class='redfont font-weight-bold'";
							}
							echo "
					<tr$class>
						<td>$test</td>
						<td>$result</td>
						<td>$sir</td>
						<td>$val</td>
					</tr>";
							unset($test, $result, $sir, $val_1, $val_2, $val_3, $val, $class);
						}
						
					?>
				</tbody>
			</table>
		</div>
		<div id='footer-link' class='text-center bluefont'>
			<span>Click <a href="http://www.tn.gov/hai/xdro"><b>HERE</b></a> for educational materials about XDRO organisms</span>
			<br>
			<a href="http://www.tn.gov/hai/xdro">http://www.tn.gov/hai/xdro</a>
		</div>
	</div>
</div>
<div id="demographics" class="modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Patient Demographics</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p><b>DEMOGRAPHIC INFORMATION AS OF: </b><span id="last_change_time"><?=$last_demo['patient_last_change_time']?></span></p>
				<table class='simpletable'>
					<tbody>
							<tr><th>FIRST NAME:</th><td data-field="patient_first_name"><?=$last_demo['patient_first_name']?></td></tr>
							<tr><th>LAST NAME:</th><td data-field="patient_last_name"><?=$last_demo['patient_last_name']?></td></tr>
							<tr><th>DATE OF BIRTH:</th><td><?=$xdro_registry['patient_dob']?></td></tr>
							<tr><th>CURRENT GENDER:</th><td data-field="patient_current_sex"><?=$last_demo['patient_current_sex']?></td></tr>
							<tr><th>PHONE:</th><td data-field="patient_phone_home"><?=$last_demo['patient_phone_home']?></td></tr>
							<tr><th>ADDRESS:</th><td data-field="patient_street_address_1"><?=$last_demo['patient_street_address_1']?></td></tr>
							<tr><th></th><td data-field="patient_street_address_2"><?=$last_demo['patient_street_address_2']?></td></tr>
							<tr><th>CITY:</th><td data-field="patient_city"><?=$last_demo['patient_city']?></td></tr>
							<tr><th>STATE:</th><td data-field="patient_state"><?=$last_demo['patient_state']?></td></tr>
							<tr><th>ZIP:</th><td data-field="patient_zip"><?=$last_demo['patient_zip']?></td></tr>
							<tr><th>COUNTY:</th><td data-field="patient_county"><?=$last_demo['patient_county']?></td></tr>
							<tr><th>RESIDENCE:</th><td data-field="patient_state"><?=$last_demo['patient_state']?></td></tr>
							<tr><th>JURISDICTION:</th><td data-field="jurisdiction_nm"><?=$last_demo['jurisdiction_nm']?></td></tr>
							<tr><th>SOCIAL SECURITY:</th><td><?=$xdro_registry['patient_ssn']?></td></tr>
							<tr><th>RACE:</th><td><?=$xdro_registry['patient_race_calculated']?></td></tr>
							<tr><th>ETHNICITY:</th><td><?=$xdro_registry['patient_ethnicity']?></td></tr>
					</tbody>
				</table>
				<div class="mt-3" id="demo_buttons">
					<button id="prev_demo_inst" type="button" class="btn btn-primary">Older <i class="fa fa-arrow-left"></i></button>
					<span id="demo_instance"></span>
					<button id="next_demo_inst" type="button" class="btn btn-primary" disabled>Newer <i class="fa fa-arrow-right"></i></button>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div id="metrics" class="modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Patient Match</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-8">
						<span>Is this patient a match?</span>
					</div>
					<div class="col-4">
						<div class="form-check">
							<input class="form-check-input" type="radio" name="match_radios" id="match_radio_1" value="1">
							<label class="form-check-label" for="match_radios">Yes</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="match_radios" id="match_radio_0" value="0">
							<label class="form-check-label" for="match_radios">No</label>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-8">
						<span>Were you aware that this patient should be on contact precautions prior to searching the registry?</span>
					</div>
					<div class="col-4">
						<div class="form-check">
							<input class="form-check-input" type="radio" name="aware_radios" id="aware_radio_1" value="1">
							<label class="form-check-label" for="aware_radios">Yes</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="aware_radios" id="aware_radio_0" value="0">
							<label class="form-check-label" for="aware_radios">No</label>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-8">
						<span>Is this patient already on contact precautions?</span>
					</div>
					<div class="col-4">
						<div class="form-check">
							<input class="form-check-input" type="radio" name="already_radios" id="already_radio_1" value="1">
							<label class="form-check-label" for="already_radios">Yes</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="already_radios" id="already_radio_0" value="0">
							<label class="form-check-label" for="already_radios">No</label>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-8">
						<span>What facility is this patient admitted to?</span>
					</div>
					<div class="col-4">
						<div class='dropdown'>
							<button class='btn btn-outline-primary dropdown-toggle' type='button' id='facility-dd' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>Facility</button>
							<div class='dropdown-menu' aria-labelledby='facility-dd'>
								<a class='dropdown-item' href='#'>Facility A</a>
								<a class='dropdown-item' href='#'>Facility B</a>
								<a class='dropdown-item' href='#'>Facility C</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button id="save-metrics" type="button" class="btn btn-primary" data-dismiss="modal">Save</button>
			</divS
		</div>
	</div>
</div>

</div>
</body>
</html>

