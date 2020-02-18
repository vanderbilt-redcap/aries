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

<div>
	<div class="input-group w-25">
		<div class="custom-file">
			<input type="file" class="custom-file-input" id="import_file" aria-describedby="import_file">
			<label class="custom-file-label" for="import_file">Choose import file</label>
		</div>
		<div class="input-group-append">
			<button class="btn btn-outline-primary" type="button" id="submit_file">Import</button>
		</div>
	</div>
</div>
<div role="alert" id="notes"></div>
<table id="results">
	<thead>
		<th>Row #</th>
		<th>Notes</th>
	</thead>
	<tbody>
	</tbody>
</table>

<script type="text/javascript" src="<?=$module->getUrl('js/import_file.js')?>"></script>
<link rel="stylesheet" href="<?=$module->getUrl('css/import_file.css')?>"/>

<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';