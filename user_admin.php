<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral' . DIRECTORY_SEPARATOR. 'header.php';

// $module->setSystemSetting("auth_data", "{}");

// $module->nlog();
// $module->llog("auth data:\n" . print_r($module->auth_data, true));
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
			<button type="button" class="btn btn-primary" onclick="$('#change_email.modal').modal('show')">Change Email</button>
			<button type="button" class="btn btn-primary" onclick="XDRO.reset_password()">Reset Password</button>
			<button type="button" class="btn btn-danger" onclick="XDRO.delete_user()">Delete User</button>
		</div>
	</div>
</div>

<div id="cards" class="row">
	<div id="add-user" class="card col-4">
		<h5 class="card-title mt-3">Add New User</h5>
		<form class="needs-validation" novalidate>
			<div class="form-group" class="mb-3">
				<label for="first-name">What is the new user's first name?</label>
				<input type="text" class="form-control" id="first-name" placeholder="John" autocomplete="off" required>
			</div>
			<div class="form-group" class="mb-3">
				<label for="last-name">What is the new user's last name?</label>
				<input type="text" class="form-control" id="last-name" placeholder="Smith" autocomplete="off" required>
			</div>
			<div class="form-group" class="mb-3">
				<label for="email">What is the new user's email address?</label>
				<input type="text" class="form-control" id="email" placeholder="john@hospital.org" autocomplete="off" required>
			</div>
			<div class="form-group" class="mb-3">
				<label for="username">Pick a username for this user</label>
				<input type="text" class="form-control" id="username" placeholder="john_smith_1" autocomplete="off" required>
			</div>
			<button type="button" class="btn btn-primary mb-3" onclick="XDRO.add_user()">Create New User</button>
		</form>
	</div>
	<div id="facilities" class="card col-4">
		<h5 class="card-title mt-3">Add/Remove Facilities</h5>
		<select class="fac-list custom-select mb-3" size="5">
		</select>
		<div class="facility-actions mb-3">
			<button type="button" class="btn btn-primary" onclick="$('#rename.modal').modal('show')">Rename</button>
			<button type="button" class="btn btn-danger" onclick="XDRO.remove_facility()">Remove</button>
		</div>
		<div class="form-group" class="mb-3">
			<label for="add-facility">Enter a facility name</label>
			<input type="text" class="form-control mb-3" id="add-facility" placeholder="ABC Medical" autocomplete="off" required>
			<button type="submit" class="btn btn-primary" onclick="XDRO.add_facility()">Add Facility</button>
		</div>
	</div>
</div>

<div id="rename" class="modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Rename Facility</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group" class="mb-3">
					<label for="facility_new_name">Facility name</label>
					<input type="text" class="form-control" id="facility_new_name" placeholder="Facility ABC" autocomplete="off" required>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="XDRO.rename_facility()">Save</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#rename input').val('')">Cancel</button>
			</div>
		</div>
	</div>
</div>

<div id="change_email" class="modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Change User Email</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group" class="mb-3">
					<label for="user_new_email">User's email address</label>
					<input type="text" class="form-control" id="user_new_email" placeholder="john@example.org" autocomplete="off" required>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="XDRO.change_email()">Save</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#change_email input').val('')">Cancel</button>
			</div>
		</div>
	</div>
</div>

<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';