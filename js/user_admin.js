
// doc level

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

XDRO.disable_buttons = function() {
	$("#users button, #cards button").attr('disabled', true)
}

XDRO.enable_buttons = function() {
	$("#users button, #cards button").removeAttr('disabled')
}


// user facing

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
	
	XDRO.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			XDRO.enable_buttons()
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

XDRO.change_email = function() {
	var user_id = $("#users table tr.selected").attr('data-id')
	var new_email = $("#change_email input").val()
	
	if (!new_email || new_email == "") {
		XDRO.show_error("Must provide a non-empty value for new user email address")
		return
	}
	
	var data = {
		id: user_id,
		value: new_email,
		action: 'change_email'
	}
	
	XDRO.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			XDRO.enable_buttons()
			XDRO.response = response
			console.log('response', response)
			if (response.responseJSON.error) {
				XDRO.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				// remove user with this ID
				var i = XDRO.users.length
				while (i--) {
					if (XDRO.users[i].id == user_id)
						XDRO.users[i].email = new_email
				}
				
				XDRO.refresh()
			}
		}
	})
}

XDRO.reset_password = function() {
	var user_id = $("#users table tr.selected").attr('data-id')
	
	if (user_id === undefined)
		return;
	
	var data = {
		user: {id: user_id},
		action: 'reset_password'
	}
	
	XDRO.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			XDRO.enable_buttons()
			XDRO.response = response
			console.log('response', response)
			if (response.responseJSON.error) {
				XDRO.show_error(response.responseJSON.error)
			}
		}
	})
}

XDRO.delete_user = function() {
	var user_id = $("#users table tr.selected").attr('data-id')
	
	var data = {
		id: user_id,
		action: 'delete_user'
	}
	
	XDRO.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			XDRO.enable_buttons()
			XDRO.response = response
			console.log('response', response)
			if (response.responseJSON.error) {
				XDRO.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				// remove user with this ID
				var i = XDRO.users.length
				while (i--) {
					if (XDRO.users[i].id == user_id)
						XDRO.users.splice(i, 1)
				}
				
				XDRO.refresh()
			}
		}
	})
}

// facility

XDRO.add_facility = function() {
	var facility = {
		name: $("#add-facility").val()
	}
	
	var data = {
		facility: facility,
		action: 'add_facility'
	}
	
	XDRO.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			XDRO.enable_buttons()
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

XDRO.remove_facility = function() {
	var fac_id = $("#facilities .fac-list").val()
	
	var data = {
		id: fac_id,
		action: 'remove_facility'
	}
	
	XDRO.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			XDRO.enable_buttons()
			XDRO.response = response
			console.log('response', response)
			if (response.responseJSON.error) {
				XDRO.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				// remove facility with this ID
				var i = XDRO.facilities.length
				while (i--) {
					if (XDRO.facilities[i].id == fac_id)
						XDRO.facilities.splice(i, 1)
				}
				
				XDRO.refresh()
			}
		}
	})
}

XDRO.rename_facility = function() {
	var new_fac_name = $("#rename input").val()
	var fac_id = $("#facilities .fac-list").val()
	
	if (!new_fac_name || new_fac_name == "") {
		XDRO.show_error("Must provide a non-empty value for new facility name")
		return
	}
	
	var data = {
		id: fac_id,
		value: new_fac_name,
		action: 'rename_facility'
	}
	
	XDRO.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			XDRO.enable_buttons()
			XDRO.response = response
			console.log('response', response)
			if (response.responseJSON.error) {
				XDRO.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				// rename facility with this ID
				var i = XDRO.facilities.length
				while (i--) {
					if (XDRO.facilities[i].id == fac_id)
						XDRO.facilities[i].name = new_fac_name
				}
				
				XDRO.refresh()
			}
		}
	})
}



