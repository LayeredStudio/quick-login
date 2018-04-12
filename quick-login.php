<?php
/*
Plugin Name: Quick Social Login
Plugin URI: https://wordpress.layered.studio
Description: Quick social login for WordPress sites
Version: 0.1
Text Domain: layered
Author: Layered
Author URI: https://layered.studio
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

include 'vendor/autoload.php';


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

function quickLoginButtons(string $style = 'button') {
	return \Layered\QuickLogin\Buttons::renderLogins($style);
}
