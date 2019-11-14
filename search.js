$(function() {
	$("#search-input input").on('input', XDRO.predictPatients)
	$("#autocomplete").on('mousedown', 'span', function(e) {
		var span = $(e.target)
		if (span.hasClass('predict-name')) {
			span = span.parent('span')
		}
		var rid = span.attr('data-rid')
		if (rid) {
			window.location.href = XDRO.recordAddress + "&rid=" + rid
		}
	})
	$("#search-input input").on('blur', function() {$("#autocomplete").hide()})
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
	
	$("#search-feedback").css('visibility', 'visible')
	
	$.ajax({
		url: XDRO.moduleAddress + "&action=predictPatients&searchString=" + encodeURI(searchString),
		dataType: 'json',
		complete: function(response) {
			XDRO.response = response
			// console.log('response', response)
			if (response.responseJSON && response.responseJSON.length > 0) {
				XDRO.showPredictions(response.responseJSON)
			} else {
				// no results so hide predictions
				$("#autocomplete").css('display', 'none')
			}
			
			$("#search-feedback").css('visibility', 'hidden')
		}
	})
}

XDRO.showPredictions = function(predictions) {
	// console.log('predictions', predictions)
	var items = ""
	
	predictions.forEach(function(patient) {
		items += '<span data-rid="' + patient.record_id + '">"<span class="predict-name">' + patient.patient_first_nm + ' ' + patient.patient_last_nm + '</span>" in Record ID # <b>' + patient.record_id + '</b> (<i>DOB: ' + patient.patient_dob + ', ' + patient.curr_sex_cd + ', ' + patient.street_addr_1 + '</i>)</span>'
	})
	
	$("#autocomplete").html(items)
	$("#autocomplete").css('display', 'flex')
	
	var search = $("#search-input input")
	$("#autocomplete").css('top', search.position().top + search.height() + 'px')
	$("#autocomplete").css('left', search.position().left + 'px')
}