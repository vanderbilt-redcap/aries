<?php
require_once (APP_PATH_TEMP . "../redcap_connect.php");
require_once APP_PATH_DOCROOT . 'ProjectGeneral' . DIRECTORY_SEPARATOR. 'header.php';
?>
<script type='text/javascript' src="//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<link rel='stylesheet' href='//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css'>

<script type="text/javascript">
	XDRO = {
		import_ajax_url: "<?php echo $module->getUrl('import_file_ajax.php'); ?>"
	}
</script>

<div class="main">
	<div class="card">
		<div class="card-body">
			<h5 class="card-title mb-3">Lab/Patient Data File Import</h5>
			<p>Import patients or lab results by uploading an Excel workbook or .csv file.</p>
			<p class="mb-3">The XDRO module will iterate over the rows in the workbook and create demographic or lab instrument instances as applicable.</p>
			<div class="input-group">
				<div class="custom-file">
					<input type="file" class="custom-file-input" id="import_file" aria-describedby="import_file">
					<label class="custom-file-label" for="import_file">Choose import file</label>
				</div>
				<div class="input-group-append">
					<button class="btn btn-outline-primary" type="button" id="submit_file">Import</button>
				</div>
			</div>
		</div>
	</div>

	<div id="ignored_cols" class="card my-3">
		<div class="card-body">
			<h5 class="card-title mb-3">Ignored Columns</h5>
			<p class="mb-3">The following columns were ignored during the import process<br> as they do not have fields in the XDRO registry project.</p>
			<ul>
			</ul>
		</div>
	</div>

	<div role="alert" id="notes"></div>
	<table id="results">
		<thead>
			<th>Row #</th>
			<th>Patient ID</th>
			<th>Form</th>
			<th>Action</th>
		</thead>
		<tbody>
		</tbody>
	</table>
	
</div>
<script type="text/javascript" src="<?=$module->getUrl('js/import_file.js')?>"></script>
<link rel="stylesheet" href="<?=$module->getUrl('css/import_file.css')?>"/>

<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';