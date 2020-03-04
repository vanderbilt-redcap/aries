XDRO = {
	facilities: [
		{
			id: 1,
			name: "Facility A"
		},
		{
			id: 2,
			name: "Facility B"
		},
		{
			id: 3,
			name: "Facility C"
		}
	]
}

// call this on init or when user/facility data changes to update user_admin page elements
XDRO.refresh = function() {
	// facilities
	$(".fac-list").empty()
	XDRO.facilities.forEach(function(facility) {
		$(".fac-list").append("<option value='" + facility.id + "'>" + facility.name + "</option>")
	})
}

XDRO.add_user = function() {
	
}

XDRO.add_facility = function() {
	
}

XDRO.change_email = function() {
	
}

XDRO.reset_password = function() {
	
}

XDRO.delete_user = function() {
	
}

XDRO.save_data = function() {
	
}

$(function() {
	$("#users table").DataTable({
		pageLength: 20
	});
	
	$(".dataTables_empty").text("There are currently no users! You may add users using the form below.")
	
	XDRO.refresh()
})