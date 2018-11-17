<?php
namespace Layered\QuickLogin;

use Layered\QuickLogin\Provider;

class Login {

	public static function start() {
		return new static;
	}

	public function __construct() {
		add_action('init', [$this, 'checkAuth']);
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

		// register user
		if (!$user) {
			$action = 'register';

			if (empty($data['user_login'])) {
				if ($data['user_email']) {
					$emailParts = explode('@', $data['user_email']);
					$data['user_login'] = $emailParts[0];
				} else {
					$data['user_login'] = $data['display_name'];
				}
			}

			$data['user_login'] = sanitize_user($data['user_login'], true);

			if (!validate_username($data['user_login'])) {
				$data['user_login'] = sanitize_user($provider->getId() . '_' . uniqid());
			}

			$usernameTmp = $data['user_login'];

			$index = 1;
			while (username_exists($data['user_login'])) {
				$data['user_login'] = $usernameTmp . $index++;
			}

			if (empty($data['user_email'])) {
				$data['user_email'] = $data['id'] . '@' . $provider->getId() . '.unknown';
			}

			$userData = [
				'user_login'	=>	$data['user_login'],
				'user_email'	=>	$data['user_email'],
				'user_pass'		=>	wp_generate_password(8),
				'display_name'	=>	$data['display_name'],
				'first_name'	=>	$data['first_name'],
				'last_name'		=>	$data['last_name'],
				'description'	=>	$data['description'],
				'user_url'		=>	$data['user_url']
			];

			if ($data['locale'] && in_array($data['locale'], get_available_languages())) {
				$userData['locale'] = $data['locale'];
			}

			$userId = wp_insert_user($userData);
			do_action('woocommerce_created_customer', $userId, $userData, true);

			$user = get_user_by('id', $userId);

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
