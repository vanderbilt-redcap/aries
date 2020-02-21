XDRO = {
	demographics: {
		instance_current: 1
	}
}

$('body').on('click', '#modal_link', function (e) {
	$("#demo_instance").text(XDRO.demographics.instance_current + " / " + XDRO.demographics.instance_count)
})
$('body').on('click', '#prev_demo_inst', function (e) {
	var inst = XDRO.demographics.instance_current
	if (inst == 1)
		return
	
	var url = XDRO.moduleAddress + "&action=get_demographics&record=" + XDRO.record_id + "&form=demographics&instance=" + (inst - 1)
	console.log('sending ajax to ' + url)
	$.ajax({
		url: url
	}).done(function(response) {
		response.instance = inst
		XDRO.update_demographics(response)
	})
})
$('body').on('click', '#next_demo_inst', function (e) {
	// console.log('abc')
	var inst = XDRO.demographics.instance_current
	if (inst == XDRO.demographics.instance_count)
		return
	
	// console.log('def')
	var url = XDRO.moduleAddress + "&action=get_demographics&record=" + XDRO.record_id + "&form=demographics&instance=" + (inst + 1)
	console.log('sending ajax to ' + url)
	$.ajax({
		url: url
	}).done(function(response) {
		response.instance = inst
		XDRO.update_demographics(response)
	})
})

XDRO.update_demographics = function(data) {
	// console.log('updating demographics modal')
	var data = JSON.parse(data)
	$("#demographics tbody td[data-field]").each(function(i, e) {
		var fieldname = $(e).attr('data-field')
		e.text(data[fieldname])
	})
}