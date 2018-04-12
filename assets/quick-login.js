jQuery(function($) {

	['login', 'register'].forEach(function(form) {
		if ($('#' + form + 'form').length) {
			var formEl = $('#' + form + 'form');

			if (QuickLogin[form] === 'top') {
				formEl.prepend('<div class="quick-login-separator"><span>or</span></div>');
				formEl.prepend(QuickLogin[form + 'Buttons']);
			} else if (QuickLogin[form] === 'bottom') {
				formEl.append('<div class="quick-login-clear"></div>');
				formEl.append('<div class="quick-login-separator"><span>or</span></div>');
				formEl.append(QuickLogin[form + 'Buttons']);
			}
		}
	});

});
