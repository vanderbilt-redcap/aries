XDRO = {}

$(function() {
	XDRO.demographics = JSON.parse(XDRO.demographics)
	XDRO.demo_index = XDRO.demographics.length
	$("#demo_instance").text(XDRO.demographics.length + " / " + XDRO.demographics.length)
	$("#date_admitted").datepicker()
	// $("#date_admitted").datepicker("show")
	
	if (XDRO.demographics.length < 2){
		$("button#prev_demo_inst").attr('disabled', true)
		$("button#next_demo_inst").attr('disabled', true)
	}
})

// events
$('body').on('click', '#prev_demo_inst', function (e) {
	XDRO.demo_index -= 1
	if (XDRO.demo_index == 1)
		$(this).attr('disabled', true)
	$("button#next_demo_inst").removeAttr('disabled')
	
	$("#demo_instance").text(XDRO.demo_index + " / " + XDRO.demographics.length)
	XDRO.update_demographics(XDRO.demo_index)
})
$('body').on('click', '#next_demo_inst', function (e) {
	XDRO.demo_index += 1
	if (XDRO.demo_index == XDRO.demographics.length)
		$(this).attr('disabled', true)
	$("button#prev_demo_inst").removeAttr('disabled')
	
	$("#demo_instance").text(XDRO.demo_index + " / " + XDRO.demographics.length)
	XDRO.update_demographics(XDRO.demo_index)
})
$('body').on('click', '#header_radio_1', function (e) {
	$("#metrics").modal('show')
})
$('body').on('click', '.dropdown-menu a', function() {
	let dd = $(this).parent().siblings("button");
	dd.text($(this).text());
	$(".btn:first-child").val($(this).text());
})
$('body').on('click', '.modal-content .form-check-input', function() {
	// if all Patient Match radios have one of their pair selected, enable save button, otherwise disable Save button
	if (// are radios selected?
		($("#match_radio_0").is(":checked") || $("#match_radio_1").is(":checked"))
		&& ($("#aware_radio_0").is(":checked") || $("#aware_radio_1").is(":checked"))
		&& ($("#already_radio_0").is(":checked") || $("#already_radio_1").is(":checked"))
		) {
		$("#save-metrics").removeAttr('disabled')
	} else {
		$("#save-metrics").attr('disabled', true)
	}
})



XDRO.update_demographics = function(demo_index) {
	var demographics = XDRO.demographics[demo_index-1]
	
	$("#demographics tbody td[data-field]").each(function(i, e) {
		var fieldname = $(e).attr('data-field')
		$(e).text(demographics[fieldname])
	})
	$("#demographics #last_change_time").text(String(demographics["patient_last_change_time"]))
}

XDRO.save_patient_match = function() {
	// send patient match modal data to server to save as an instance in 'Metrics' form (repeating)
	var data = {}
	data.record_id = this.getParameter('rid')
	data.match = $("#match_radio_1").is(":checked") ? true : false
	data.contact_prior = $("#aware_radio_1").is(":checked") ? true : false
	data.contact = $("#already_radio_1").is(":checked") ? true : false
	
	var facility_name = $("#facility-dd").val()
	var facility_index = null;
	$("#facility-dd").next("div").children("a").each(function(i, e) {
		if ($(e).html() == facility_name) {
			facility_index = $(e).attr("value")
			return;
		}
	})
	data.facility = facility_index
	
	var date_admitted = $("#date_admitted").datepicker("getDate")
	data.date_admitted = null
	if (date_admitted)
		data.date_admitted = date_admitted.toISOString().split('T')[0]
	console.log('data', data)
	
	$.ajax({
		type: "POST",
		url: XDRO.patient_match_ajax,
		data: data,
		success: function(response) {
			console.log('response', response)
		},
		dataType: 'json',
		cache: false
	})
}

// see: https://stackoverflow.com/questions/979975/how-to-get-the-value-from-the-get-parameters
XDRO.getParameter = function(name) {
	function parse_query_string(query) {
		var vars = query.split("&");
		var query_string = {};
		for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split("=");
			var key = decodeURIComponent(pair[0]);
			var value = decodeURIComponent(pair[1]);
			// If first entry with this name
			if (typeof query_string[key] === "undefined") {
				query_string[key] = decodeURIComponent(value);
			// If second entry with this name
			} else if (typeof query_string[key] === "string") {
				var arr = [query_string[key], decodeURIComponent(value)];
				query_string[key] = arr;
			// If third or later entry with this name
			} else {
				query_string[key].push(decodeURIComponent(value));
			}
		}
		return query_string;
	}
	var query = window.location.search.substring(1);
	var qs = parse_query_string(query);
	if (qs[name])
		return qs[name]
	return null
}