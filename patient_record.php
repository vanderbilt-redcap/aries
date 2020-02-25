<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";
require_once APP_PATH_DOCROOT . 'ProjectGeneral' . DIRECTORY_SEPARATOR. 'header.php';

$module->nlog();
$rid = $_GET['rid'];
$module->llog("fetching patient_record for rid: $rid");

if (empty($rid)) {
	// TODO what to show when wrong Record ID or missing??
} else {
	$pid = $module->getProjectId();
	$project = new \Project($pid);
	$eid = $project->firstEventId;
	$record = \REDCap::getData($pid, 'array', $rid);
	$module->llog("data: " . print_r($record, true));
	
	// allow for easier access to specific form values
	$xdro_registry = $record[$rid][$eid];
	
	$demo_inst_count = count($record[$rid]["repeat_instances"][$eid]["demographics"]);
	$last_demo_index = max(array_keys($record[$rid]["repeat_instances"][$eid]["demographics"]));
	$demographics = $record[$rid]["repeat_instances"][$eid]["demographics"][$last_demo_index];
	
	$anti_inst_count = count($record[$rid]["repeat_instances"][$eid]["antimicrobial_susceptibilities_and_resistance_mech"]);
	$last_anti_index = max(array_keys($record[$rid]["repeat_instances"][$eid]["antimicrobial_susceptibilities_and_resistance_mech"]));
	$labs = $record[$rid]["repeat_instances"][$eid]["antimicrobial_susceptibilities_and_resistance_mech"][$last_anti_index];
	
	$all_demographics = json_encode($record[$rid]["repeat_instances"][$eid]["demographics"]);
	
	$address_td = "";
	if (!empty($demographics["patient_street_address_1"]))
		$address_td .= $demographics["patient_street_address_1"];
	if (!empty($demographics["patient_street_address_2"]))
		$address_td .= "<br>" . $demographics["patient_street_address_2"];
	$address_td .= "<br>" . $demographics["patient_city"] . ", " . $demographics["patient_state"] . " " . $demographics["patient_zip"];
}

?>
<link rel="stylesheet" href="<?=$module->getUrl('css/record.css')?>"/>
<script type="text/javascript" src="<?=$module->getUrl('js/patient_record.js')?>"></script>
<script type="text/javascript" >
	XDRO.pid = "<?php echo $pid;?>";
	XDRO.rid = "<?php echo $rid;?>";
	XDRO.eid = "<?php echo $eid;?>";
	XDRO.demographics = '<?php echo $all_demographics;?>';
</script>

<div id='header' class='row'>
	<div class='logo column'>
		<span id='xdro-title'>xdro</span>
		<img id='tdh-logo' src="<?=$module->getUrl('res/tdh-logo.png')?>"></img>
	</div>
	<div class='header-info column'>
		<div id='patient-match' class='bluefont'>
			<span>Is this patient a match?</span><span class='symbol-font'><?=$xdro_registry['match'] ? "X" : ""?></span> YES <span class='symbol-font'><?=$xdro_registry['match'] ? "" : "X"?></span> NO
		</div>
		<p id='ip-blurb' class='bluefont pt-2'>Please consider an Infectious Disease consult and make sure facility Infection Preventionist is aware.</p>
		<br>
		<div id='registry-title'><h1>Extensively Drug Resistant Organism Registry</h1></div>
		<span><b>RESULT DATE:</b> <?=$labs["resulted_dt"]?></span>
		<div id='test-results'>
			<span><b>TEST RESULTS</b></span>
			<span class='redfont'><b>ALERT: <?=$labs["disease"]?></b></span>
			<span><b>ORGANISM: </b><i><?=$labs["lab_test_nm"]?></i></span>
		</div>
	</div>
</div>
<div id='record' class='row'>
	<div class='column'>
		<div id='patient-info'>
			<table class='mb-3 mt-1 simpletable'>
				<tr><th>FIRST NAME: </th><td><?=$demographics["patient_first_name"]?></td></tr>
				<tr><th>LAST NAME: </th><td><?=$demographics["patient_last_name"]?></td></tr>
				<tr><th>DATE OF BIRTH: </th><td><?=$xdro_registry["patient_dob"]?></td></tr>
				<tr><th>GENDER: </th><td><?=$demographics["patient_current_sex"]?></td></tr>
				<tr><th>ADDRESS: </th><td><?=$address_td?></td></tr>
				<tr><th>COUNTY: </th><td><?=$demographics["patient_county"]?></td></tr>
				<tr><th>RESIDENCE: </th><td><?=$demographics["patient_state"]?></td></tr>
				<tr><th>JURISDICTION: </th><td><?=$demographics["jurisdiction_nm"]?></td></tr>
			</table>
			<span class='px-3 mb-3'>Click <a href='' id="modal_link" data-toggle='modal' data-target='.modal'><b>HERE</b></a> for more demographic information</span>
		</div>
		<div id='lab-info' class='mb-3 mt-3'>
			<table class='mb-3 mt-1 simpletable'>
				<tr><th>ORDERING FACILITY: </th><td><?=$labs["ordering_facility"]?></td></tr>
				<tr><th>ORDERING PROVIDER: </th><td><?=$labs["providername"] . "<br>&emsp;" . $labs["provider_address"] . "<br>&emsp;" . $labs["providerphone"]?></td></tr>
				<tr><th>PERFORMING FACILITY: </th><td class='redfont'></td></tr>
				<tr><th>REPORTING FACILITY: </th><td class='redfont'><?=$labs["reporting_facility"] . "<br>&emsp;" . $labs["reportername"] . "<br>&emsp;" . $labs["reporterphone"]?></td></tr>
			</table>
		
			<span><b>ORDERED TEST: </b> <?=$labs["ordered_test_nm"]?></span>
			<span><b>SPECIMEN SOURCE: </b> <?=$labs["specimen_desc"]?></span>
			<span><b>RESULTED TEST: </b> <?=$labs["lab_test_nm"]?></span>
			<span><b>DATE SPECIMEN COLLECTED: </b> <?=$labs["specimen_collection_dt"]?></span>
			<span><b>STATUS: </b> <?=$labs["lab_test_status"]?></span>
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
						for ($i = 1; $i <= 30; $i++) {
							$test = $xdro_registry["lab_test_nm_$i"];
							$result = $xdro_registry["lab_result_txt_val_$i"];
							$sir = $xdro_registry["sir_$i"];
							$mic_kb = $xdro_registry["numeric_result_val_$i"];
							if ($mic_kb == ">=4") {
								$class = " class='redfont font-weight-bold'";
							}
							if (!empty($test) or !empty($result) or !empty($sir) or !empty($mic_kb))
								echo "
					<tr$class>
						<td>$test</td>
						<td>$result</td>
						<td>$sir</td>
						<td>$mic_kb</td>
					</tr>";
							unset($test, $result, $sir, $mic_kb, $class);
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
				<p><b>DEMOGRAPHIC INFORMATION AS OF: </b><?=$demographics['patient_last_change_time']?></p>
				<table class='simpletable'>
					<tbody>
							<tr><th>FIRST NAME:</th><td data-field="patient_first_name_d"><?=$demographics['patient_first_name']?></td></tr>
							<tr><th>LAST NAME:</th><td data-field="patient_last_name_d"><?=$demographics['patient_last_name']?></td></tr>
							<tr><th>DATE OF BIRTH:</th><td data-field="patient_dob_d"><?=$demographics['patient_dob']?></td></tr>
							<tr><th>CURRENT GENDER:</th><td data-field="patient_current_sex_d"><?=$demographics['patient_current_sex']?></td></tr>
							<tr><th>PHONE:</th><td data-field="patient_phone_home"><?=$demographics['patient_phone_home']?></td></tr>
							<tr><th>ADDRESS:</th><td data-field="street_addr_1_d"><?=$demographics['patient_street_address_1']?></td></tr>
							<tr><th></th><td data-field="street_addr_2_d"><?=$demographics['patient_street_address_2']?></td></tr>
							<tr><th>CITY:</th><td data-field="patient_city_d"><?=$demographics['patient_city']?></td></tr>
							<tr><th>STATE:</th><td data-field="patient_state_d"><?=$demographics['patient_state']?></td></tr>
							<tr><th>ZIP:</th><td data-field="patient_zip_d"><?=$demographics['patient_zip']?></td></tr>
							<tr><th>COUNTY:</th><td data-field="patient_county_d"><?=$demographics['patient_county']?></td></tr>
							<tr><th>RESIDENCE:</th><td data-field="patient_state_d"><?=$demographics['patient_state']?></td></tr>
							<tr><th>JURISDICTION:</th><td data-field="jurisdiction_nm_d"><?=$demographics['jurisdiction_nm']?></td></tr>
							<tr><th>SOCIAL SECURITY:</th><td><?=$xdro_registry['patient_ssn']?></td></tr>
							<tr><th>RACE:</th><td><?=$xdro_registry['patient_race_calculated']?></td></tr>
							<tr><th>ETHNICITY:</th><td><?=$xdro_registry['patient_ethnicity']?></td></tr>
					</tbody>
				</table>
				<div class="mt-3" id="demo_buttons">
					<button id="prev_demo_inst" type="button" class="btn btn-primary">Back</button>
					<span id="demo_instance"></span>
					<button id="next_demo_inst" type="button" class="btn btn-primary">Next</button>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';