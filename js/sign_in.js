
XDRO.sign_in = function() {
	var username = $("#username").val()
	var password = $("#password").val()
	
	console.log('un, password', username, password)
	if (!username) {
		alert("Please enter a username before attempting to sign in")
		return
	}
	
	if (!password) {
		alert("Please enter a password before attempting to sign in")
		return
	}
	
	var data = {
		username: username,
		password: password
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
				alert(response.responseJSON.error)
			} else if (response.responseJSON.success) {
				if (response.responseJSON.authenticated == true)
					window.location = search_address
			}
		}
	})
}

XDRO.forgot_password = function() {
	
}

$(function() {
	
})