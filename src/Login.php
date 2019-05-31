<?php
namespace Layered\QuickLogin;

use Layered\QuickLogin\Provider;

class Login {

	public static function start() {
		return new static;
	}

	public function __construct() {
		add_action('init', [$this, 'checkAuth']);
		add_filter('illegal_user_logins', [$this, 'blacklistedLogins']);
	}

	public function checkAuth() {
		$providers = quickLoginProviders();

		if (isset($_REQUEST['quick-login']) && isset($providers[$_REQUEST['quick-login']])) {
			if (isset($_REQUEST['redirect_to'])) {
				set_transient('quick-login-redirect', $_REQUEST['redirect_to'], 600);
			}

			$providers[$_REQUEST['quick-login']]->doAuth();
		}

		if (isset($_REQUEST['quick-login-unlink']) && isset($providers[$_REQUEST['quick-login-unlink']])) {
			if ($_REQUEST['user_id'] == get_current_user_id() || current_user_can('edit_users')) {
				$provider = $providers[$_REQUEST['quick-login-unlink']];
				delete_user_meta($_REQUEST['user_id'], $provider->getId() . '_id');
				delete_user_meta($_REQUEST['user_id'], $provider->getId() . '_info');
				do_action('quick_login', get_user_by('id', $_REQUEST['user_id']), 'unlink', $provider);
				$message = sprintf(__('%s account is unlinked', 'quick-login'), $provider->getLabel());
			} else {
				wp_die(__('Not authorised to unlink user accounts', 'quick-login'));
			}

			$redirectUrl = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : wp_get_referer();

			wp_redirect(add_query_arg(['quick-login-alert' => urlencode($message)], $redirectUrl));
			exit;
		}
	}

	public function blacklistedLogins(array $logins) {

		$logins = array_merge($logins, [
			'admin',
			'hello',
			'office',
			'mail',
			'contact',
			'info',
			'privacy',
			'support',
			'sales',
			'billing',
			'list',
			'noreply',
			'no-reply'
		]);

		return array_unique($logins);
	}

	public static function doAuth(Provider $provider, $token, $providerUserData) {
		$user = false;
		$data = $provider->convertFields($providerUserData);
		$action = 'login';

		if (is_user_logged_in()) {
			$user = wp_get_current_user();
			$action = 'link';
		}

		// check linked user by provider Id
		if (!$user) {
			$users = get_users([
				'count_total'	=>	false,
				'number'		=>	1,
				'meta_key'		=>	$provider->getId() . '_id',
				'meta_value'	=>	$data['id']
			]);

			if ($users) {
				$user = $users[0];
			}
		}

		// check by email
		if (!$user && $data['user_email']) {
			$user = get_user_by('email', $data['user_email']);
		}

		// unknown user and unknown email
		if (!$user && empty($data['user_email'])) {
			$errorMessage = sprintf(__('This account is not linked to our site. Please <strong>Login</strong> or <strong>Register</strong> first, then link your %s account.', 'quick-login'), $provider->getLabel());

			wp_redirect($provider->getLoginUrl([
				'error'	=>	urlencode($errorMessage)
			]));
			exit;
		}

		// Register user
		if (!$user) {
			$action = 'register';

			// use Name as username
			if (empty($data['user_login']) && $data['display_name']) {
				$data['user_login'] = str_replace(' ', '', $data['display_name']);
			}

			// use Email name as username 
			if (empty($data['user_login'])) {
				$data['user_login'] = current(explode('@', $data['user_email']));
			}

			$data['user_login'] = sanitize_user(strtolower($data['user_login']), true);

			if (empty($data['user_login']) || in_array($data['user_login'], apply_filters('illegal_user_logins', []))) {
				$data['user_login'] = sanitize_user($provider->getId() . '-' . uniqid());
			}

			$usernameTmp = $data['user_login'];

			$index = 1;
			while (username_exists($data['user_login'])) {
				$data['user_login'] = $usernameTmp . $index++;
			}

			$userId = function_exists('wc_create_new_customer') ? wc_create_new_customer($data['user_email'], $data['user_login'], wp_generate_password()) : register_new_user($data['user_login'], $data['user_email']);

			if (is_wp_error($userId)) {
				wp_redirect($provider->getLoginUrl([
					'error'		=>	urlencode($userId->get_error_message())
				]));
				exit;
			}

			$user = get_user_by('id', $userId);
			update_user_option($user->ID, 'default_password_nag', false, false);

			$userData = [
				'ID'			=>	$user->ID,
				'display_name'	=>	$data['display_name'],
				'first_name'	=>	$data['first_name'],
				'last_name'		=>	$data['last_name'],
				'description'	=>	$data['description'],
				'user_url'		=>	$data['user_url']
			];

			if ($data['locale'] && in_array($data['locale'], get_available_languages())) {
				$userData['locale'] = $data['locale'];
			}

			wp_update_user($userData);

			if (class_exists('WooCommerce')) {
				if ($data['first_name']) {
					add_user_meta($user->ID, 'billing_first_name', $data['first_name'], true);
					add_user_meta($user->ID, 'shipping_first_name', $data['first_name'], true);
				}
				if ($data['last_name']) {
					add_user_meta($user->ID, 'billing_last_name', $data['last_name'], true);
					add_user_meta($user->ID, 'shipping_last_name', $data['last_name'], true);
				}
			}
		}

		update_user_meta($user->ID, $provider->getId() . '_id', $data['id']);
		update_user_meta($user->ID, $provider->getId() . '_info', [
			'date'	=>	new \DateTime,
			'user'	=>	$providerUserData,
			'token'	=>	$token,
			'scope'	=>	$provider->getScope()
		]);

		wp_set_auth_cookie($user->ID, true);
		do_action('wp_login', $user->user_login, $user);
		do_action('quick_login', $user, $action, $provider);

		wp_redirect(wp_validate_redirect(get_transient('quick-login-redirect') ?: site_url()));
		exit;
	}

}
