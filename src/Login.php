<?php
namespace Layered\QuickLogin;

use Layered\QuickLogin\Provider;

class Login {

	public static function start() {
		return new static;
	}

	public function __construct() {
		add_filter('quick_login_providers', [$this, 'defaultProviders']);
		add_action('init', [$this, 'checkAuth']);
	}

	public function defaultProviders(array $providers) {
		$providers['facebook'] = new \Layered\QuickLogin\Provider\Facebook;
		$providers['twitter'] = new \Layered\QuickLogin\Provider\Twitter;
		$providers['google'] = new \Layered\QuickLogin\Provider\Google;
		$providers['wordpresscom'] = new \Layered\QuickLogin\Provider\WordPressCom;

		return $providers;
	}

	public function checkAuth() {
		$providers = apply_filters('quick_login_providers', []);

		if (isset($_REQUEST['quick-login']) && isset($providers[$_REQUEST['quick-login']])) {
			if (isset($_REQUEST['redirect_to'])) {
				set_transient('quick-login-redirect', $_REQUEST['redirect_to'], 600);
			}

			$providers[$_REQUEST['quick-login']]->doAuth();
		}
	}

	public static function doAuth(Provider $provider, array $data) {
		$user = false;

		// check linked user by provider Id
		$users = get_users([
			'count_total'	=>	false,
			'number'		=>	1,
			'meta_key'		=>	$provider->getId() . '_id',
			'meta_value'	=>	$data['id']
		]);

		// check linked user by provider Id (support for other login plugins)
		if (!count($users)) {
			$users = get_users([
				'count_total'	=>	false,
				'number'		=>	1,
				'meta_key'		=>	$provider->getId() . '_login_id',
				'meta_value'	=>	$data['id']
			]);

			if ($users) {
				add_user_meta($users[0]->ID, $provider->getId() . '_id', $data['id']);
			}
		}

		if ($users) {
			$user = $users[0];
		}

		// check by email
		if (!$user && $data['email'] && ($user = get_user_by('email', $data['email']))) {
			add_user_meta($user->ID, $provider->getId() . '_id', $data['id']);
		}

		// register user
		if (!$user) {

			if (empty($data['username'])) {
				if ($data['email']) {
					$emailParts = explode('@', $data['email']);
					$data['username'] = $emailParts[0];
				} else {
					$data['username'] = $data['display_name'];
				}
			}

			$data['username'] = sanitize_user($data['username'], true);

			if (!validate_username($data['username'])) {
				$data['username'] = sanitize_user($provider->getId() . '_' . uniqid());
			}

			$usernameTmp = $data['username'];

			$index = 1;
			while (username_exists($data['username'])) {
				$data['username'] = $usernameTmp . $index++;
			}

			if (empty($data['email'])) {
				$data['email'] = $data['id'] . '@' . $provider->getId() . '.unknown';
			}

			$userData = [
				'user_login'	=>	$data['username'],
				'user_email'	=>	$data['email'],
				'user_pass'		=>	wp_generate_password(8),
				'display_name'	=>	$data['display_name'],
				'first_name'	=>	$data['first_name'],
				'last_name'		=>	$data['last_name'],
				'description'	=>	$data['description']
			];

			$userId = wp_insert_user($userData);
			do_action('woocommerce_created_customer', $userId, $userData, true);

			$user = get_user_by('id', $userId);
			add_user_meta($user->ID, $provider->getId() . '_id', $data['id']);

			if (class_exists('WooCommerce')) {
				if ($data['first_name']) {
					add_user_meta($user->ID, 'billing_first_name', $data['first_name']);
					add_user_meta($user->ID, 'shipping_first_name', $data['first_name']);
				}
				if ($data['last_name']) {
					add_user_meta($user->ID, 'billing_last_name', $data['last_name']);
					add_user_meta($user->ID, 'shipping_last_name', $data['last_name']);
				}
			}
		}

		wp_set_auth_cookie($user->ID, true);
		do_action('wp_login', $user->user_login, $user);

		wp_redirect(wp_validate_redirect(get_transient('quick-login-redirect') ?: site_url()));
		exit;
	}


}
