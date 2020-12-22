
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
	
	ARIES.refresh()
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
ARIES.refresh = function() {
	// clear forms
	$(".form-control").val("")
	
	// users table
	var users = $("#users table").DataTable()
	users.clear()
	if (ARIES.users)
		users.rows.add(ARIES.users)
	users.draw()
	
	// add data-id values to each tr in #users table
	// $("#users tr").each(
	
	// facilities
	$(".fac-list").empty()
	if (ARIES.facilities) {
		ARIES.facilities.forEach(function(facility) {
			$(".fac-list").append("<option value='" + facility.id + "'>" + facility.name + "</option>")
		})
	}
}

ARIES.show_error = function(msg) {
	alert(msg)
}

ARIES.disable_buttons = function() {
	$("#users button, #cards button").attr('disabled', true)
}

ARIES.enable_buttons = function() {
	$("#users button, #cards button").removeAttr('disabled')
}


// user facing

ARIES.add_user = function() {
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
	
	ARIES.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			ARIES.enable_buttons()
			ARIES.response = response
			// console.log('response', response)
			if (response.responseJSON.error) {
				ARIES.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				if (!ARIES.users)
					ARIES.users = []
				ARIES.users.push(response.responseJSON.user)
				ARIES.refresh()
			}
		}
	})
}

ARIES.change_email = function() {
	var user_id = $("#users table tr.selected").attr('data-id')
	var new_email = $("#change_email input").val()
	
	if (!new_email || new_email == "") {
		ARIES.show_error("Must provide a non-empty value for new user email address")
		return
	}
	
	var data = {
		id: user_id,
		value: new_email,
		action: 'change_email'
	}
	
	ARIES.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			ARIES.enable_buttons()
			ARIES.response = response
			// console.log('response', response)
			if (response.responseJSON.error) {
				ARIES.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				// remove user with this ID
				var i = ARIES.users.length
				while (i--) {
					if (ARIES.users[i].id == user_id)
						ARIES.users[i].email = new_email
				}
				
				ARIES.refresh()
			}
		}
	})
}

ARIES.reset_password = function() {
	var user_id = $("#users table tr.selected").attr('data-id')
	
	if (user_id === undefined)
		return;
	
	var data = {
		user: {id: user_id},
		action: 'reset_password'
	}
	
	ARIES.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			ARIES.enable_buttons()
			ARIES.response = response
			// console.log('response', response)
			if (response.responseJSON.error) {
				ARIES.show_error(response.responseJSON.error)
			}
		}
	})
}

ARIES.delete_user = function() {
	var user_id = $("#users table tr.selected").attr('data-id')
	
	var data = {
		id: user_id,
		action: 'delete_user'
	}
	
	ARIES.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			ARIES.enable_buttons()
			ARIES.response = response
			// console.log('response', response)
			if (response.responseJSON.error) {
				ARIES.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				// remove user with this ID
				var i = ARIES.users.length
				while (i--) {
					if (ARIES.users[i].id == user_id)
						ARIES.users.splice(i, 1)
				}
				
				ARIES.refresh()
			}
		}
	})
}

// facility

ARIES.add_facility = function() {
	var facility = {
		name: $("#add-facility").val()
	}
	
	var data = {
		facility: facility,
		action: 'add_facility'
	}
	
	ARIES.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			ARIES.enable_buttons()
			ARIES.response = response
			// console.log('response', response)
			if (response.responseJSON.error) {
				ARIES.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				if (!ARIES.facilities)
					ARIES.facilities = []
				ARIES.facilities.push(response.responseJSON.facility)
				ARIES.refresh()
			}
		}
	})
}

ARIES.remove_facility = function() {
	var fac_id = $("#facilities .fac-list").val()
	
	var data = {
		id: fac_id,
		action: 'remove_facility'
	}
	
	ARIES.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			ARIES.enable_buttons()
			ARIES.response = response
			// console.log('response', response)
			if (response.responseJSON.error) {
				ARIES.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				// remove facility with this ID
				var i = ARIES.facilities.length
				while (i--) {
					if (ARIES.facilities[i].id == fac_id)
						ARIES.facilities.splice(i, 1)
				}
				
				ARIES.refresh()
			}
		}
	})
}

ARIES.rename_facility = function() {
	var new_fac_name = $("#rename input").val()
	var fac_id = $("#facilities .fac-list").val()
	
	if (!new_fac_name || new_fac_name == "") {
		ARIES.show_error("Must provide a non-empty value for new facility name")
		return
	}
	
	var data = {
		id: fac_id,
		value: new_fac_name,
		action: 'rename_facility'
	}
	
	ARIES.disable_buttons()
	$.ajax({
		url: ajax_address,
		dataType: 'json',
		data: data,
		method: "POST",
		complete: function(response) {
			ARIES.enable_buttons()
			ARIES.response = response
			// console.log('response', response)
			if (response.responseJSON.error) {
				ARIES.show_error(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				// rename facility with this ID
				var i = ARIES.facilities.length
				while (i--) {
					if (ARIES.facilities[i].id == fac_id)
						ARIES.facilities[i].name = new_fac_name
				}
				
				ARIES.refresh()
			}
		}
	})
}



