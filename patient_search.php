<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";
require_once APP_PATH_DOCROOT . 'ProjectGeneral' . DIRECTORY_SEPARATOR. 'header.php';
?>
<link rel="stylesheet" href="<?=$module->getUrl('css/search.css')?>"/>
<script type="text/javascript" src="<?=$module->getUrl('js/search.js')?>"></script>
<script type="text/javascript">
	XDRO.moduleAddress = "<?=$module->getUrl('XDRO.php')?>"
	XDRO.recordAddress = "<?=$module->getUrl('patient_record.php')?>"
</script>

<div id='header' class=''>
	<div class='logo'>
		<span id='xdro-title'>xdro</span>
		<img id='tdh-logo' src="<?=$module->getUrl('res/tdh-logo.png')?>"></img>
	</div>
	<div id='registry-title'>
		<h1>Extensively Drug Resistant Organism Registry</h1>
	</div>
</div>

<div id="search" class=''>
	<div id='search-info' class='col-4'>
		<h5><b>Search Query</b></h5>
		<p>Begin typing to search the registry data,<br> then click an item in the list to navigate to that record for further investigation.</p>
	</div>
	<div id="search-input" class='col-8'>
		<div class='col-8'>
			<input type='text' name='user-query' class='col-12'>
			<div id="autocomplete"></div>
		</div>
		<div class='mx-2 px-2 col-2'>
			<button id="submit-search" type="button" class="btn btn-primary" onclick="XDRO.submit_manual_query()">Search</button>
		</div>
		<div id="search-feedback" class='mr-3 pr-3 col-2'>
			<div class="spinner">
				<img src='<?=$module->getUrl('res/spinner.png')?>'>
			</div>
			<span class='ml-2 search-indicator'>Searching</span>
		</div>
	</div>
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
	<div id="file-search-input" class='col-8'>
		<div class="input-group col-8">
			<div class="custom-file">
				<input type="file" class="custom-file-input" id="upload_csv" aria-describedby="upload_csv">
				<label class="custom-file-label" for="upload_csv">Upload a CSV</label>
			</div>
			<div id="autocomplete"></div>
		</div>
		<!--<div class='col-8'>
			<input type='text' name='user-query' class='col-12'>
			<div id="autocomplete"></div>
		</div>-->
		<div class='mx-2 px-2 col-2'>
			<button id="file-submit-search" type="button" class="btn btn-primary">Search</button>
		</div>
		<div id="file-search-feedback" class='mr-3 pr-3 col-2'>
			<div class="spinner">
				<img src='<?=$module->getUrl('res/spinner.png')?>'>
			</div>
			<span class='ml-2 search-indicator'>Searching</span>
		</div>
	</div>
</div>

<div id='results'>
	<table>
		<thead>
			<tr>
				<th>Record ID</th>
				<th>Name</th>
				<th>Date of Birth</th>
				<th>Gender</th>
				<th>Current Address</th>
				<th>Match?</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>1001</td>
				<td>Samuel Wilson</td>
				<td>1770-04-19</td>
				<td>Male</td>
				<td>2201 West End Ave</td>
				<td></td>
			</tr>
			<tr>
				<td>1251</td>
				<td>Samuel Wilson</td>
				<td>1770-04-19</td>
				<td>Male</td>
				<td>221b Baker St</td>
				<td></td>
			</tr>
		</tbody>
	</table>
</div>

<div id='match-listed'>
	<b><span> NO MATCH LISTED</span></b>
</div>

<div id='footer-link'>
	<div>
		<span class='pb-3'>Click <b><a href='http://www.tn.gov/hai/xdro'>HERE</a></b> for educational materials about XDRO organisms</span>
		<a href='http://www.tn.gov/hai/xdro'>http://www.tn.gov/hai/xdro</a>
	</div>
</div>

<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';