<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";

$module->nlog();
$json = new stdClass();

// make objects, not arrays
$data = json_decode(json_encode($_POST));
$action = $data->action;

if ($action == 'add_user') {
	$email = filter_var($data->user->email, FILTER_SANITIZE_EMAIL);
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$json->error = "User's email address ($email) is not valid.";
		exit(json_encode($json));
	}
	
	try {
		$data->user->id = $module->get_next_user_id();
		\REDCap::logEvent("XDRO Module", "Adding user: " . print_r($data->user, true));
		
		$new_pw = bin2hex(openssl_random_pseudo_bytes(8));
		$data->user->pw_hash = password_hash($new_pw, PASSWORD_DEFAULT);
		
		$module->auth_data->users[] = $data->user;
		
		// send email to new user
		$email_sent = \REDCap::email($email, "carl.w.reed@vumc.org", "TN Department of Health - XDRO New User", "
Hello {$data->user->first_name},<br>
<br>
You have been registered as a new user for the Tennessee Department of Health's XDRO Registry.<br>
Your username is {$data->user->username}<br>
Your password is {$new_pw}<br>
<br>
You can sign in to the registry by visiting the following URL:<br>
http://localhost/redcap/external_modules/?prefix=xdro&page=sign_in&pid=68");
		
		$module->save_auth_data();
	} catch (\Exception $e) {
		$json->error = $e;
		\REDCap::logEvent("XDRO Module", "Error occurred when adding new user: " . print_r($data, true) . " -- (exception): " . print_r($e, true));
	}
	
	if (!$email_sent) {
		$json->error = "Failed to send email with password to newly registered user.";
		exit(json_encode($json));
	}
	
	unset($data->user->pw_hash);
	$json->user = $data->user;
} elseif ($action == 'delete_user') {
	// $module->llog("current users array: \n" . print_r($module->auth_data->users, true));
	foreach($module->auth_data->users as $i => $user) {
		if ($user->id == $data->id) {
			unset($user->pw_hash);
			\REDCap::logEvent("XDRO Module", "Deleting user: " . print_r($user, true));
			unset($module->auth_data->users[$i]);
		}
		// compact indices
		$module->auth_data->users = array_values($module->auth_data->users);
	}
	$module->save_auth_data();
} elseif ($action == 'assign_facilities') {
	
} elseif ($action == 'change_email') {
	$module->llog("current users array: \n" . print_r($module->auth_data->users, true));
	foreach($module->auth_data->users as $i => $user) {
		if ($user->id == $data->id) {
			$old_email = $facility->email;
			$module->auth_data->users[$i]->email = $data->value;
			\REDCap::logEvent("XDRO Module", "Changed user email from '$old_email' to '{$data->value}' for username {$user->username}");
		}
	}
	$module->save_auth_data();
} elseif ($action == 'reset_password') {
	
} elseif ($action == 'add_facility') {
	try {
		$data->facility->id = $module->get_next_facility_id();
		$module->auth_data->facilities[] = $data->facility;
		$module->save_auth_data();
	} catch (\Exception $e) {
		$json->error = $e;
		\REDCap::logEvent("XDRO Module", "Error occurred when adding new user: " . print_r($data, true) . " -- (exception): " . print_r($e, true));
	}
	
	$json->facility = $data->facility;
} elseif ($action == 'remove_facility') {
	// $module->llog("current facs array: \n" . print_r($module->auth_data->facilities, true));
	foreach($module->auth_data->facilities as $i => $facility) {
		if ($facility->id == $data->id) {
			\REDCap::logEvent("XDRO Module", "Deleting facility: " . print_r($facility, true));
			unset($module->auth_data->facilities[$i]);
		}
		
		// compact indices
		$module->auth_data->facilities = array_values($module->auth_data->facilities);
	}
	$module->save_auth_data();
} elseif ($action == 'rename_facility') {
	// $module->llog("current facs array: \n" . print_r($module->auth_data->facilities, true));
	foreach($module->auth_data->facilities as $i => $facility) {
		if ($facility->id == $data->id) {
			$old_name = $facility->name;
			$module->auth_data->facilities[$i]->name = $data->value;
			\REDCap::logEvent("XDRO Module", "Renamed facility from '$old_name' to '{$data->value}'");
		}
	}
	$module->save_auth_data();
}

if (empty($json->error))
	$json->success = true;

exit(json_encode($json));