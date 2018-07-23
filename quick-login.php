<?php
/*
Plugin Name: Quick Login
Plugin URI: https://wordpress.layered.studio/quick-login
Description: Let your visitors log in with existing accounts! Supports Twitter, Facebook, Google, WordPress.com and LinkedIn
Version: 0.5
Text Domain: quick-login
Author: Layered
Author URI: https://layered.studio
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


// start the plugin

add_action('plugins_loaded', '\Layered\QuickLogin\Login::start');
add_action('plugins_loaded', '\Layered\QuickLogin\Buttons::start');
add_action('plugins_loaded', '\Layered\QuickLogin\Admin::start');


/* Template Tags */

function quickLoginButtons(array $options = []) {
	return Layered\QuickLogin\Buttons::renderLogins($options);
}
