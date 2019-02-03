<?php
/*
Plugin Name: Quick Login
Plugin URI: https://wordpress.layered.studio/plugins/quick-login
Description: Let your visitors sign in with their existing accounts! Supports Twitter, Facebook, Google, WordPress.com and LinkedIn
Version: 0.8
Text Domain: quick-login
Author: Layered
Author URI: https://wordpress.layered.studio
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

require plugin_dir_path(__FILE__) . 'vendor/autoload.php';


// default options

add_filter('default_option_quick-login', function($default) {
	return [
		'login-form'		=>	'top',
		'login-style'		=>	'button',
		'register-form'		=>	'bottom',
		'register-style'	=>	'button',
		'comment-form'		=>	'top',
		'comment-style'		=>	'icon'
	];
});


// default providers

add_filter('quick_login_providers', function(array $providers) {

	$providers['facebook'] = new \Layered\QuickLogin\Provider\Facebook;
	$providers['twitter'] = new \Layered\QuickLogin\Provider\Twitter;
	$providers['google'] = new \Layered\QuickLogin\Provider\Google;
	$providers['wordpresscom'] = new \Layered\QuickLogin\Provider\WordPressCom;
	$providers['linkedin'] = new \Layered\QuickLogin\Provider\LinkedIn;

	return $providers;
});


// start the plugin

add_action('plugins_loaded', '\Layered\QuickLogin\Login::start');
add_action('plugins_loaded', '\Layered\QuickLogin\Buttons::start');
add_action('plugins_loaded', '\Layered\QuickLogin\Admin::start');


/* Helper functions */

function quickLoginButtons(array $options = []) {
	return \Layered\QuickLogin\Buttons::renderLogins($options);
}

function quickLoginProviders(array $options = []) {
	$options = wp_parse_args($options, [
		'status'	=>	'any'
	]);
	$providers = apply_filters('quick_login_providers', []);

	if ($options['status'] !== 'any') {
		$providers = array_filter($providers, function(\Layered\QuickLogin\Provider $provider) use($options) {
			return $provider->getOption('status') === $options['status'];
		});
	}

	return $providers;
}
