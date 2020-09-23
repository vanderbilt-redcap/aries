<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";

// $module->nlog();
// $module->llog(print_r($module->auth_data, true));
$json = new stdClass();

// make objects, not arrays
$data = json_decode(json_encode($_POST));

$json->error = "Couldn't find user with username '{$data->username}'";
// see if there is a user with this user/pass
foreach($module->auth_data->users as $i => $user) {
	if ($user->username == $data->username) {
		$module->llog('found user');
		if (password_verify($data->password, $user->pw_hash)) {
			$json->authenticated = true;
			unset($json->error);
		} else {
			$json->error = "Found username but password doesn't match, please try again.";
			$json->authenticated = false;
		}
	}
}

if (empty($json->error))
	$json->success = true;

exit(json_encode($json));