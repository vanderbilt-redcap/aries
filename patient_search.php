<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";

session_start();
if (!$module->userIsAuthenticated()) {
	if ($redcap_username = $_SESSION['username']) {	// try to auth if redcap user
		$module->authenticateREDCapUser($redcap_username);
		if (!$module->userIsAuthenticated())
			header("location: " . $module->getUrl('sign_in.php') . "&NOAUTH");
	} else {
		header("location: " . $module->getUrl('sign_in.php') . "&NOAUTH");
	}
}

$pid = $module->getProjectId();
$fa_path = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css";
$row_query_fields = [
	"patientid",
	"patient_dob",
	"patient_first_name",
	"patient_last_name",
	"patient_current_sex",
	"patient_street_address_1"
];


// $module->llog("patient_search \$_POST: " . print_r($_POST, true));
// $module->llog("patient_search \$_GET: " . print_r($_GET, true));

// handle queries if exist
$query = db_escape($_GET['query']);
if (!empty($query))
	$search_results = json_encode($module->search($query));
unset($query);


$query = new \stdClass();
$fieldsAdded = 0;
foreach($row_query_fields as $i => $fieldname) {
	$value = db_escape($_GET[$fieldname]);
	if (!empty($value)) {
		$query->$fieldname = $value;
		$fieldsAdded++;
	}
	unset($value);
}
if ($fieldsAdded > 0) {
	$search_results = json_encode($module->structured_search($query));
}


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>ARIES | REDCap</title>
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
	
	<!-- font awesome icons -->
	<link rel="stylesheet" href="<?=$fa_path?>">
	
	<!--[if IE 9]>
	<link rel="stylesheet" type="text/css" href="/redcap/redcap_v9.5.14/Resources/css/bootstrap-ie9.min.css">
	<script type="text/javascript">$(function(){ie9FormFix()});</script>
	<!--<![endif]-->
</head>
<body>

<!-- jquery 3.4 -->
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>

<!-- bootstrap 4.4 -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

<!-- datatables 1.10 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>

<!-- page js/css -->
<link rel="stylesheet" href="<?=$module->getUrl('css/search.css')?>"/>
<link rel="stylesheet" href="<?=$module->getUrl('css/search_datatable.css')?>"/>
<script type="text/javascript" src="<?=$module->getUrl('js/search.js')?>"></script>
<script type="text/javascript">
	ARIES.moduleAddress = "<?=$module->getUrl('ARIES.php')?>"
	ARIES.recordAddress = "<?=$module->getUrl('patient_record.php') . '&NOAUTH'?>"
	<?php
	if (!empty($search_results)) {
		?>;ARIES.search_results = JSON.parse(<?php echo("'$search_results');");
	}
	if ($fieldsAdded > 0) {
		?>;ARIES.use_file_interface = true<?php
	}
	?>
</script>

<!-- page contents -->
<div id="main">
<div id='header' class=''>
	<div class='logo'>
		<span id='aries-title'>aries</span>
		<img id='tdh-logo' src="<?=$module->getUrl('res/tdh-logo.png')?>"></img>
	</div>
	<div id='registry-title'>
		<h1>Antibiotic Resistance Information Exchange System</h1>
	</div>
</div>

<div id="file-queries">
	<div class="alert alert-primary dark-border m-3" role="alert">
		<p>File uploaded: <span class="filename"></span></p>
		<p>Showing results for row <span class="records"><?php echo ($_GET['query_row'] + 1); ?></span></p>
	</div>
</div>

<div id="search" class=''>
	<div id='search-info' class='col-4'>
		<h5><b>Search Query</b></h5>
		<p>Begin typing to search the registry data,<br> then click an item in the list to navigate to that record for further investigation.</p>
	</div>
	<form id="search-input" class='col-8'>
		<input style="display:none;" name="NOAUTH" value="1">
		<input style="display:none;" name="prefix" value="aries">
		<input style="display:none;" name="page" value="patient_search">
		<input style="display:none;" name="pid" value="<?php echo($pid); ?>">
		<div class='col-8'>
			<input type='text' name='query' id="query" class='w-100'>
			<div id="autocomplete"></div>
		</div>
		<div class='mx-2 px-2 col-2'>
			<!-- <button id="submit-search" type="submit" class="btn btn-primary">Search</button> -->
			<input type="submit" value="Search">
		</div>
		<div id="search-feedback" class='mr-3 pr-3 col-2'>
			<div class="spinner">
				<img src='<?=$module->getUrl('res/spinner.png')?>'>
			</div>
			<span class='ml-2 search-indicator'>Searching</span>
		</div>
	</form>
</div>

<div id="error_alert">
	<div class="alert alert-primary dark-border mt-3" role="alert">
		Your query for "<span></span>" yielded 0 results.
	</div>
</div>

<div id="file-search" class=''>
	<div id='file-search-info' class='col-4'>
		<h5><b>Upload Patient List</b></h5>
		<p>Click the 'Browse' button to choose a file to upload, then click 'Search'.</p>
		<div class="alert alert-dark ml-3 w-75 dark-border" role="alert">
			A CSV is a type of file. You can save any spreadsheet in Excel as a CSV. When 'Saving As,' underneath the field for 'File name', there is a field for 'Save as type' which will open a drop-down menu. Select CSV (Comma delimited) and save.
		</div>
	</div>
	<form method="post" enctype="multipart/form-data" id="file-search-input" class='col-8'>
		<input style="display:none;" name="NOAUTH" value="1">
		<input style="display:none;" name="prefix" value="aries">
		<input style="display:none;" name="page" value="patient_search">
		<input style="display:none;" name="pid" value="<?php echo($pid); ?>">
		<div class="input-group col-8">
			<div class="custom-file">
				<input type="file" class="custom-file-input" id="upload_csv" name="upload_csv" aria-describedby="upload_csv">
				<label class="custom-file-label" for="upload_csv">Upload a CSV</label>
			</div>
			<div id="autocomplete"></div>
		</div>
		<div class='mx-2 px-2 col-2'>
			<button id="file-submit-search" type="button" class="btn btn-primary" onclick="ARIES.submit_row_query()">Search</button>
			<!--  <input type="submit" value="Search"> -->
		</div>
		<div id="file-search-feedback" class='mr-3 pr-3 col-2'>
			<div class="spinner">
				<img src='<?=$module->getUrl('res/spinner.png')?>'>
			</div>
			<span class='ml-2 search-indicator'>Searching</span>
		</div>
	</form>
</div>

<div id='results'>
	<table class='display cell-border'>
		<thead>
			<tr>
				<th>Record ID</th>
				<th>Name</th>
				<th>Date of Birth</th>
				<th>Sex</th>
				<th>Current Address</th>
				<th>Relevance</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<div id='match-listed'>
	<b><span> NO MATCH LISTED</span></b>
</div>

<div id='footer-link'>
	<div>
		<span class='pb-3'>Click <b><a href='http://www.tn.gov/hai/aries'>HERE</a></b> for educational materials about ARIES organisms</span>
		<a href='http://www.tn.gov/hai/aries'>http://www.tn.gov/hai/aries</a>
	</div>
</div>
</div>
</body>
</html>