
// call this on init or when user/facility data changes to update user_admin page elements
XDRO.refresh = function() {
	// clear forms
	$(".form-control").val("")
	
	// users table
	var users = $("#users table").DataTable()
	users.clear()
	if (XDRO.users)
		users.rows.add(XDRO.users)
	users.draw()
	
	// add data-id values to each tr in #users table
	// $("#users tr").each(
	
	// facilities
	$(".fac-list").empty()
	if (XDRO.facilities) {
		XDRO.facilities.forEach(function(facility) {
			$(".fac-list").append("<option value='" + facility.id + "'>" + facility.name + "</option>")
		})
	}
}

XDRO.show_error = function(msg) {
	alert(msg)
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
			if (response.responseJSON.error) {
				XDRO.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				if (!XDRO.users)
					XDRO.users = []
				XDRO.users.push(response.responseJSON.user)
				XDRO.refresh()
			}
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
			if (response.responseJSON.error) {
				XDRO.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				if (!XDRO.facilities)
					XDRO.facilities = []
				XDRO.facilities.push(response.responseJSON.facility)
				XDRO.refresh()
			}
		}
	})
}

XDRO.change_email = function() {
	
}

XDRO.reset_password = function() {
	
}

XDRO.delete_user = function() {
	
}

// XDRO.save_data = function() {
	// console.log('sending xdro data to ajax_address: ' + ajax_address)
	// $.ajax({
		// url: ajax_address,
		// dataType: 'json',
		// data: {
			// data: {
				// users: XDRO.users,
				// facilities: XDRO.facilities
			// }
		// },
		// method: "POST",
		// complete: function(response) {
			// XDRO.response = response
			// console.log('response', response)
		// }
	// })
// }

$(function() {
	$("#users table").DataTable({
		pageLength: 20,
		columns: [
			{data: "username"},
			{data: "first_name"},
			{data: "last_name"},
			{data: "email"},
			{data: "date_added"}
		],
		createdRow: function(row, data, dataIndex) {
			$(row).attr('data-id', data.id)
		}
	});
	
	XDRO.refresh()
	$(".dataTables_empty").text("There are currently no users! You may add users using the form below.")
})

$("body").on("click", "#users tr", function() {
	$("#users tr").removeClass('selected')
	$(this).addClass('selected')
})

// de-select table rows when clicking outside of #users card
$(document).mouseup(function (e){
	var container = $("#users");
	if (!container.is(e.target) && container.has(e.target).length === 0){
		$("#users tr").removeClass('selected')
	}
}); 