XDRO = {}

$(function() {
	XDRO.demographics = JSON.parse(XDRO.demographics)
	
	console.log("demographics", XDRO.demographics)
	// console.log("
})

$('body').on('click', '#modal_link', function (e) {
	$("#demo_instance").text(XDRO.demographics.instance_current + " / " + XDRO.demographics.instance_count)
})
$('body').on('click', '#prev_demo_inst', function (e) {
	var inst = XDRO.demographics.instance_current
	if (inst == 1)
		return
	
	// var url = XDRO.moduleAddress + "&action=get_demographics&record=" + XDRO.record_id + "&form=demographics&instance=" + (inst - 1)
	// console.log('sending ajax to ' + url)
	// $.ajax({
		// url: url
	// }).done(function(response) {
		// response.instance = inst
		// XDRO.update_demographics(response)
	// })
})
$('body').on('click', '#next_demo_inst', function (e) {
	var inst = XDRO.demographics.instance_current
	if (inst == XDRO.demographics.instance_count)
		return
	
	// var url = XDRO.moduleAddress + "&action=get_demographics&record=" + XDRO.record_id + "&form=demographics&instance=" + (inst + 1)
	// console.log('sending ajax to ' + url)
	// $.ajax({
		// url: url
	// }).done(function(response) {
		// response.instance = inst
		// XDRO.update_demographics(response)
	// })
})

XDRO.update_demographics = function(demographics) {
	console.log('updating demographics modal')
	$("#demographics tbody td[data-field]").each(function(i, e) {
		var fieldname = $(e).attr('data-field')
		e.text(demographics[fieldname])
	})
}