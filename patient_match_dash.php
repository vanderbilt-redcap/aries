<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral' . DIRECTORY_SEPARATOR. 'header.php';

$pid = $module->getProjectId();
$project = new \Project($pid);
$eid = $project->firstEventId;
$ridfield = \REDCap::getRecordIdField();
$patient_info = [];

//------------------------------
//	get dob, name, sex info for each patient ($patient_info->patientid = info object)
//------------------------------

$params = [
	"project_id" => $pid,
	"return_format" => 'array',
	"fields" => [
		"$ridfield",
		'redcap_repeat_instrument',
		'redcap_repeat_instance',
		'disease',
		'date_admitted',
		'facility',
		'patient_dob',
		'patient_last_name',
		'patient_first_name',
		'patient_current_sex'
	]
];
$data = \REDCap::getData($params);
$table_data = [];
$facility_labels = $module->getFieldLabels('facility');
$module->llog('facility_labels: ' . print_r($facility_labels, true));

foreach($data as $pati_id => $record) {
	$disease_string = "";
	foreach($record['repeat_instances'][$eid]['antimicrobial_susceptibilities_and_resistance_mech'] as $inst) {
		if (empty($disease_string)) {
			$disease_string = $inst['disease'];
		} else {
			$disease_string .= ', ' . $inst['disease'];
		}
	}
	
	$last_demo_index = max(array_keys($record["repeat_instances"][$eid]["demographics"]));
	$last_demo = $record["repeat_instances"][$eid]["demographics"][$last_demo_index];
	
	foreach($record['repeat_instances'][$eid]['metrics'] as $inst) {
		// $dob = date("m/d/Y", strtotime($record[$eid]['patient_dob']));
		$dob = $record[$eid]['patient_dob'];
		// $admit_date = date("m/d/Y", strtotime($inst['date_admitted']));
		$admit_date = $inst['date_admitted'];
		$table_data[] = [
			$admit_date,
			$last_demo['patient_last_name'],
			$last_demo['patient_first_name'],
			$dob,
			substr($last_demo['patient_current_sex'], 0, 1),
			$facility_labels[$inst['facility']],
			$disease_string
		];
	}
}

$table_json = json_encode($table_data);

?>

<script type='text/javascript'>
	XDRO = {};
	XDRO.table_data = JSON.parse('<?php echo $table_json; ?>');
	$(document).ready(
		function() {
			// Setup - add a text input to each footer cell
			$('#match-table thead tr').clone(true).appendTo( '#match-table thead' );
			$('#match-table thead tr:eq(1) th').each( function (i) {
				var title = $(this).text();
				$(this).html( '<input type="text" placeholder="Search '+title+'" />' );
		 
				$( 'input', this ).on( 'keyup change', function () {
					if ( XDRO.datatable.column(i).search() !== this.value ) {
						XDRO.datatable
							.column(i)
							.search( this.value )
							.draw();
					}
				} );
			} );
		
			XDRO.datatable = $('#match-table').DataTable({
				data: XDRO.table_data,
				pageLength: 25,
				order: [[0, 'desc']],
				columnDefs: [
					{className: 'dt-center', targets: '_all'}
				]
			});
		}
	);
</script>

<h4 id='dt-title'>Patient Match Dashboard</h4>
<div class='row'>
	<table id="match-table" class="display">
		<thead>
			<tr>
				<th>Date Matched</th>
				<th>Last Name</th>
				<th>First Name</th>
				<th>DOB</th>
				<th>Sex</th>
				<th>Facility</th>
				<th>Condition</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';