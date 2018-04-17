jQuery(function($) {

	['login', 'register'].forEach(function(form) {
		if ($('#' + form + 'form').length) {
			var formEl = $('#' + form + 'form');

			if (QuickLogin[form] === 'top') {
				formEl.prepend(QuickLogin[form + 'Buttons']);
			} else if (QuickLogin[form] === 'bottom') {
				formEl.append(QuickLogin[form + 'Buttons']);
			}
		}
	});

});
