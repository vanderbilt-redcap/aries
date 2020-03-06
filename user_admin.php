<?php
require_once str_replace("temp" . DIRECTORY_SEPARATOR, "", APP_PATH_TEMP) . "redcap_connect.php";
require_once APP_PATH_DOCROOT . 'ProjectGeneral' . DIRECTORY_SEPARATOR. 'header.php';
?>

<script type="text/javascript">
	XDRO = JSON.parse('<?=$module->auth_data_raw?>')
	var ajax_address = "<?=$module->getUrl('user_admin_ajax.php')?>"
</script>

<script type="text/javascript" src="<?=$module->getUrl('js/moment.js')?>"></script>

<link rel="stylesheet" href="<?=$module->getUrl('css/user_admin.css')?>"/>
<script type="text/javascript" src="<?=$module->getUrl('js/user_admin.js')?>"></script>


<div class="row users">
	<div id="users" class="col-auto card p-3">
		<h5 class="card-title mb-3">Users Table</h5>
		<table>
			<thead>
				<tr>
					<th>Username</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email Address</th>
					<th>Date Added</th>
				</tr>
			</thead>
		</table>
		<div class="user-actions">
			<button type="button" class="btn btn-primary">Assign Facilities</button>
			<button type="button" class="btn btn-primary">Change Email</button>
			<button type="button" class="btn btn-primary">Reset Password</button>
			<button type="button" class="btn btn-danger">Delete User</button>
		</div>
	</div>
</div>

<div id="cards" class="row">
	<div id="add-user" class="card col-4">
		<h5 class="card-title mt-3">Add New User</h5>
		<form class="needs-validation" novalidate>
			<div class="form-group" class="mb-3">
				<label for="first-name">What is the new user's first name?</label>
				<input type="text" class="form-control" id="first-name" placeholder="John" required>
			</div>
			<div class="form-group" class="mb-3">
				<label for="last-name">What is the new user's last name?</label>
				<input type="text" class="form-control" id="last-name" placeholder="Smith" required>
			</div>
			<div class="form-group" class="mb-3">
				<label for="email">What is the new user's email address?</label>
				<input type="text" class="form-control" id="email" placeholder="john@hospital.org" required>
			</div>
			<div class="form-group" class="mb-3">
				<label for="username">Pick a username for this user</label>
				<input type="text" class="form-control" id="username" placeholder="john_smith_1" required>
			</div>
			<button type="button" class="btn btn-primary mb-3" onclick="XDRO.add_user()">Create New User</button>
		</form>
	</div>
	<div id="facilities" class="card col-4">
		<h5 class="card-title mt-3">Add/Remove Facilities</h5>
		<select class="fac-list custom-select mb-3" multiple>
		</select>
		<div class="facility-actions mb-3">
			<button type="button" class="btn btn-primary">Rename</button>
			<button type="button" class="btn btn-danger">Remove</button>
		</div>
		<div class="alert alert-primary mb-3" role="alert">
			There are 6 users associated with the selected facility.
		</div>
		<div class="form-group" class="mb-3">
			<label for="add-facility">Enter a facility name</label>
			<input type="text" class="form-control mb-3" id="add-facility" placeholder="ABC Medical" required>
			<button type="submit" class="btn btn-primary" onclick="XDRO.add_facility()">Add Facility</button>
		</div>
	</div>
</div>

<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';