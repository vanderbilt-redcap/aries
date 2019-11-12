$(function() {
	$("#search-input input").on('input', XDRO.predictPatients)
})

XDRO = {}
XDRO.shrinkSearch = function() {
	$("#search-info").html("<h6><b>Results for:</b></h6>")
	$("#search-info").css('padding', '20px 8px')
	$("#search-info").removeClass('col-4').addClass('col-2')
	$("#search-input").removeClass('col-8').addClass('col-10')
	$(".result-instructions").css('visibility', 'visible')
}

XDRO.predictPatients = function() {
	var searchBar = $("#search-input input")
	var searchString = searchBar.val()
	$.ajax({
		url: XDRO.moduleAddress + "&action=predictPatients&searchString=" + encodeURI(searchString),
		dataType: 'json',
		complete: function(response) {
			XDRO.response = response
			if (response.responseJSON.length > 0) {
				XDRO.showPredictions(response.responseJSON)
			}
		}
	})
}

XDRO.showPredictions = function(predictions) {
	console.log('predictions', predictions)
	var items = ""
	
	predictions.forEach(function(patient) {
		items += '<span>"' + patient.first_name + ' ' + patient.last_name + '" in Record ID # ' + patient.record_id + ' (DOB: ' + patient.dob + ', ' + patient.sex + ', ' + patient.address + ')</span>'
	})
	
	$("#autocomplete").html(items)
	$("#autocomplete").css('display', 'flex')
	
	var search = $("#search-input input")
	$("#autocomplete").css('top', search.position().top + search.height() + 'px')
	$("#autocomplete").css('left', search.position().left + 'px')
}