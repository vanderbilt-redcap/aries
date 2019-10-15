<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";
require_once APP_PATH_DOCROOT . 'ProjectGeneral' . DIRECTORY_SEPARATOR. 'header.php';
?>
<link rel="stylesheet" href="<?=$module->getUrl('record.css')?>"/>
<script type="text/javascript" src="<?=$module->getUrl('record.js')?>"></script>

<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';