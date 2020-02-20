<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";
require_once APP_PATH_DOCROOT . 'ProjectGeneral' . DIRECTORY_SEPARATOR. 'header.php';

$rid = $_GET['rid'];
if (empty($rid)) {
	$data = reset(reset(\REDCap::getData($module->getProjectId(), 'array', $rid)));
}

?>
<link rel="stylesheet" href="<?=$module->getUrl('css/record.css')?>"/>

<div id='header' class='row'>
	<div class='logo column'>
		<span id='xdro-title'>xdro</span>
		<img id='tdh-logo' src="<?=$module->getUrl('res/tdh-logo.png')?>"></img>
	</div>
	<div class='header-info column'>
		<div id='patient-match' class='bluefont'>
			<span>Is this patient a match?</span><span class='symbol-font'><?=$data['match'] ? "X" : ""?></span> YES <span class='symbol-font'><?=$data['match'] ? "" : "X"?></span> NO
		</div>
		<p id='ip-blurb' class='bluefont pt-2'>Please consider an Infectious Disease consult and make sure facility Infection Preventionist is aware.</p>
		<br>
		<div id='registry-title'><h1>Extensively Drug Resistant Organism Registry</h1></div>
		<span><b>RESULT DATE:</b> <?=$data["resulted_dt"]?></span>
		<div id='test-results'>
			<span><b>TEST RESULTS</b></span>
			<span class='redfont'><b>ALERT: <?=$data["disease"]?></b></span>
			<span><b>ORGANISM: </b><i><?=$data["resultedtest_val_cd_desc"]?></i></span>
		</div>
	</div>
</div>
<div id='record' class='row'>
	<div class='column'>
		<div id='patient-info'>
			<table class='mb-3 mt-1 simpletable'>
				<tr><th>FIRST NAME: </th><td><?=$data["patient_first_nm"]?></td></tr>
				<tr><th>LAST NAME: </th><td><?=$data["patient_last_nm"]?></td></tr>
				<tr><th>DATE OF BIRTH: </th><td><?=$data["patient_dob"]?></td></tr>
				<tr><th>GENDER: </th><td><?=$data["curr_sex_cd"]?></td></tr>
				<tr><th>ADDRESS: </th><td><?=$data["street_addr_1"] . $data["street_addr_2"] . "<br>&emsp;" . $data["city_desc"] . ", " . $data["state_desc"] . " " . $data["zip_cd"]?></td></tr>
				<tr><th>COUNTY: </th><td><?=$data["county"]?></td></tr>
				<tr><th>RESIDENCE: </th><td><?=$data["patient_state"]?></td></tr>
				<tr><th>JURISDICTION: </th><td><?=$data["jurisdiction_nm"]?></td></tr>
			</table>
			<span class='px-3 mb-3'>Click <a href='' data-toggle='modal' data-target='.modal'><b>HERE</b></a> for more demographic information</span>
		</div>
		<div id='lab-info' class='mb-3 mt-3'>
			<table class='mb-3 mt-1 simpletable'>
				<tr><th>ORDERING FACILITY: </th><td><?=$data["ordering_facility"]?></td></tr>
				<tr><th>ORDERING PROVIDER: </th><td><?=$data["providername"] . "<br>&emsp;" . $data["provider_address"] . "<br>&emsp;" . $data["providerphone"]?></td></tr>
				<tr><th>PERFORMING FACILITY: </th><td class='redfont'><?=$data["performing_facility"] . "<br>&emsp;" . $data["performing_address"]?></td></tr>
				<tr><th>REPORTING FACILITY: </th><td class='redfont'><?=$data["reporting_facility"] . "<br>&emsp;" . $data["reportername"] . "<br>&emsp;" . $data["reporterphone"]?></td></tr>
			</table>
		
			<span><b>ORDERED TEST: </b> <?=$data["ordered_test_nm"]?></span>
			<span><b>SPECIMEN SOURCE: </b> <?=$data["specimen_src"]?></span>
			<span><b>RESULTED TEST: </b> <?=$data["resulted_lab_test_cd_desc"]?></span>
			<span><b>DATE SPECIMEN COLLECTED: </b> <?=$data["specimen_collection_dt"]?></span>
			<span><b>STATUS: </b> <?=$data["lab_test_status"]?></span>
		</div>
		<div id='action-items' class='bluefont'>
			<span class='text-center d-block mb-2 mt-1'><b><u>Action Items for <?=$data["condition_alert"]?>:</b></u></span>
			<ul>
				<li class='mb-1'><?=$data['alert_1']?></li>
				<li class='mb-1'><?=$data['alert_2']?></li>
				<li class='mb-1'><?=$data['alert_3']?></li>
				<li class='mb-1'><?=$data['alert_4']?></li>
			</ul>
		</div>
	</div>
	<div class='column'>
		<span id='please-note' class='bluefont d-block text-right mt-3 mb-3'><li><b>Please note other tests performed on this patient.</b></li></span>
		<div id='resistances' class='text-right mt-1 mb-3'>
			<span><b>RESISTANCE MECHANISMS & CARBAPENEMASE PRODUCTION:</b></span>
			<table class='text-left'>
				<tbody>
					<?php
						$mechs = [
							'mcim' => 'mCIM',
							'carbanp' => 'CARBA NP',
							'kpc' => 'KPC',
							'ndm' => 'NDM',
							'oxa48' => 'OXA-48',
							'vim' => 'VIM',
							'imp' => 'IMP',
							'oxa23' => 'OXA-23',
							'mcr1' => 'MCR-1',
							'mcr2' => 'MCR-2'
						];
						foreach ($mechs as $field => $abbrev) {
							if ($data[$field] == "POSITIVE") {
								$class = " class='redfont font-weight-bold'";
								// $b_tag_start = "<b>";
								// $b_tag_end = "</b>";
							}
							echo "
							<tr$class>
								<th$class>$abbrev:</th>
								<td class='pr-2'>" . $data[$field] . "</td>
							</tr>";
							unset($class);
							unset($b_tag_start);
							unset($b_tag_end);
						}
					?>
				</tbody>
			</table>
		</div>
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
							$test = $data["lab_test_nm_$i"];
							$result = $data["lab_result_txt_val_$i"];
							$sir = $data["sir_$i"];
							$mic_kb = $data["numeric_result_val_$i"];
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
<div class="modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Patient Demographics</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p><b>DEMOGRAPHIC INFORMATION AS OF: </b><?=$data['age_reported_time']?></p>
				<table class='simpletable'>
					<tbody>
							<tr><th>FIRST NAME:</th><td><?=$data['patient_first_nm']?></td></tr>
							<tr><th>LAST NAME:</th><td><?=$data['patient_last_nm']?></td></tr>
							<tr><th>DATE OF BIRTH:</th><td><?=$data['patient_dob']?></td></tr>
							<tr><th>CURRENT GENDER:</th><td><?=$data['curr_sex_cd']?></td></tr>
							<tr><th>PHONE:</th><td><?=$data['patient_phone_home']?></td></tr>
							<tr><th>ADDRESS:</th><td><?=$data['street_addr_1']?><br><?=$data['street_addr_2']?></td></tr>
							<tr><th>CITY:</th><td><?=$data['city_desc']?></td></tr>
							<tr><th>STATE:</th><td><?=$data['state_desc']?></td></tr>
							<tr><th>ZIP:</th><td><?=$data['zip_cd']?></td></tr>
							<tr><th>COUNTY:</th><td><?=$data['county']?></td></tr>
							<tr><th>RESIDENCE:</th><td><?=$data['patient_state']?></td></tr>
							<tr><th>JURISDICTION:</th><td><?=$data['jurisdiction_nm']?></td></tr>
							<tr><th>PREVIOUS ADDRESS LISTED:</th><td><?=$data['patient_first_nm']?></td></tr>
							<tr><th>SOCIAL SECURITY:</th><td><?=$data['patient_ssn']?></td></tr>
							<tr><th>RACE:</th><td><?=$data['race_concatenated_desc_txt']?></td></tr>
							<tr><th>ETHNICITY:</th><td><?=$data['ethnic_group_ind_desc']?></td></tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save changes</button>
			</div>
		</div>
	</div>
</div>
<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';