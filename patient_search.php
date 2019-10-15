<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";
require_once APP_PATH_DOCROOT . 'ProjectGeneral' . DIRECTORY_SEPARATOR. 'header.php';
?>
<link rel="stylesheet" href="<?=$module->getUrl('search.css')?>"/>
<script type="text/javascript" src="<?=$module->getUrl('search.js')?>"></script>

<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';