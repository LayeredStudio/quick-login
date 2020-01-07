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


	// WP Admin -> Settings -> Quick Login: Display or hide setup instructions
	$('.quick-login-provider-instructions-btn').click(function() {
		$('.quick-login-provider-instructions').slideToggle();
	});


	// WP Admin -> Users: Filter User list by connected provider
	$('.js-quick-login-filter-provider').change(function() {
		$(this).closest('form').submit();
	});


	// WP Admin: Hide "No providers enabled" notice
	$('.notice-quick-login-enable-providers').on('click', '.notice-dismiss', function() {
		$.post(ajaxurl, {
			action:	'quick-login-dismiss-notice',
			notice:	'enable-providers',
		})
	})


});
