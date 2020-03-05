
// call this on init or when user/facility data changes to update user_admin page elements
XDRO.refresh = function() {
	// users table
	var users = $("#users table").DataTable()
	users.clear()
	users.rows.add(XDRO.users)
	users.draw()
	
	// facilities
	$(".fac-list").empty()
	XDRO.facilities.forEach(function(facility) {
		$(".fac-list").append("<option value='" + facility.id + "'>" + facility.name + "</option>")
	})
}

XDRO.add_user = function() {
	var user = {
		first_name: $("#first-name").val(),
		last_name: $("#last-name").val(),
		username: $("#username").val(),
		email: $("#email").val(),
		date_added: moment().format("YYYY-MM-DD")
	}
	
	var data = {
		user: user,
		action: 'add_user'
	}
	
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			XDRO.response = response
			console.log('response', response)
		}
	})
}

XDRO.add_facility = function() {
	var facility = {
		name: $("#add-facility").val()
	}
	
	var data = {
		facility: facility,
		action: 'add_facility'
	}
	
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			XDRO.response = response
			console.log('response', response)
		}
	})
}

XDRO.change_email = function() {
	
}

XDRO.reset_password = function() {
	
}

XDRO.delete_user = function() {
	
}

XDRO.save_data = function() {
	console.log('sending xdro data to ajax_address: ' + ajax_address)
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: {
			data: {
				users: XDRO.users,
				facilities: XDRO.facilities
			}
		},
		method: "POST",
		complete: function(response) {
			XDRO.response = response
			console.log('response', response)
		}
	})
}

$(function() {
	$("#users table").DataTable({
		data: XDRO.users,
		pageLength: 20,
		columns: [
			{data: "username"},
			{data: "first_name"},
			{data: "last_name"},
			{data: "email"},
			{data: "date_added"}
		]
	});
	
	$(".dataTables_empty").text("There are currently no users! You may add users using the form below.")
	
	XDRO.refresh()
})