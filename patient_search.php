<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";
require_once APP_PATH_DOCROOT . 'ProjectGeneral' . DIRECTORY_SEPARATOR. 'header.php';
?>
<link rel="stylesheet" href="<?=$module->getUrl('search.css')?>"/>
<script type="text/javascript" src="<?=$module->getUrl('search.js')?>"></script>

<div id='header' class='row'>
	<div class='logo'>
		<span id='xdro-title'>xdro</span>
		<img id='tdh-logo' src="<?=$module->getUrl('tdh-logo.png')?>"></img>
	</div>
	<div id='registry-title'>
		<h1>Extensively Drug Resistant Organism Registry</h1>
	</div>
</div>

<div id="search" class='row col-9'>
	<div id='search-info' class='col-4'>
		<h5><b>Search Query</b></h5>
		<p>Begin typing to search the registry data,<br> then click an item in the list to navigate to that record for further investigation.</p>
	</div>
	<div id="search-input" class='col-8'>
		
	</div>
</div>

<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';