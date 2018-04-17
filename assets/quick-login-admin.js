jQuery(function($) {

	// Logins preview
	$('.quick-login-form-preview input').change(function() {
		let form = $(this).closest('.quick-login-form-preview');
		let position = form.find('.quick-login-position:checked').val();
		let style = form.find('.quick-login-style:checked').val();

		form.find('.preview-login')
			.removeClass('preview-position-top preview-position-bottom preview-position-no preview-style-icon preview-style-button')
			.addClass('preview-position-' + position + ' preview-style-' + style);
	});

	// Display or hide setup instructions
	$('.quick-login-provider-instructions-btn').click(function() {
		$('.quick-login-provider-instructions').slideToggle();
	});

});
