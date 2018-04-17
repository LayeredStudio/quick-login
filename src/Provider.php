<?php
namespace Layered\QuickLogin;

use Layered\QuickLogin\Login;

abstract class Provider {

	private $options;
	protected $oAuthVersion;
	protected $userSettings;
	protected $id;
	protected $scope;
	protected $color;
	protected $label;
	protected $icon;

	public function getUserSettings() {
		return $this->userSettings;
	}

	public function getId() {
		return $this->id;
	}

	public function getColor() {
		return apply_filters('quick_login_provider_color', $this->color, $this->id);
	}

	public function getLabel() {
		return apply_filters('quick_login_provider_label', $this->label, $this->id);
	}

	public function getIcon() {
		return apply_filters('quick_login_provider_icon', $this->icon, $this->id);
	}

	public function getScope() {
		return apply_filters('quick_login_provider_scope', $this->scope, $this->id);
	}

	public function getOption($key = null, $default = '') {

		if (!is_array($this->options)) {
			$this->options = get_option('quick-login-' . $this->getId() . '-provider', array(
				'status'		=>	'needs-setup',
				'client_id'		=>	'',
				'client_secret'	=>	'',
				'priority'		=>	100
			));
		}

		return $key ? (isset($this->options[$key]) ? $this->options[$key] : $default) : $this->options;
	}

	public function updateOptions(array $newOptions) {
		$this->options = array_merge($this->getOption(), $newOptions);
		update_option('quick-login-' . $this->getId() . '-provider', $this->options);

		return $this->options;
	}

	public function getLoginUrl() {
		global $wp;

		return add_query_arg([
			'quick-login'	=>	$this->getId(),
			'redirect_to'	=>	isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : home_url(add_query_arg($_GET, $wp->request))
		], site_url('/wp-login.php'));
	}

	public function doAuth() {
		if ($this->oAuthVersion === 'oAuth1') {
			$this->doOAuth1();
		} elseif ($this->oAuthVersion === 'oAuth2') {
			$this->doOAuth2();
		}
	}

	protected function doOAuth1() {
		$server = $this->getServer();

		if (isset($_REQUEST['denied'])) {
			add_filter('wp_login_errors', function($errors) {
				$errors->add('denied', sprintf('%s - %s', $this->getLabel(), __('sign in cancelled', 'quick-login')));
				return $errors;
			});
		} elseif (isset($_REQUEST['error'])) {
			add_filter('wp_login_errors', function($errors) {
				$errors->add('error', sprintf(__('%s error - %s', 'quick-login'), $this->getLabel(), $_REQUEST['error']));
				return $errors;
			});
		} elseif (!isset($_GET['oauth_token']) || !isset($_GET['oauth_verifier'])) {
			$temporaryCredentials = $server->getTemporaryCredentials();
			set_transient('quick-login-oauth1-temporary-credentials', $temporaryCredentials, 600);
			$server->authorize($temporaryCredentials);
		} elseif (!($temporaryCredentials = get_transient('quick-login-oauth1-temporary-credentials'))) {
			add_filter('wp_login_errors', function($errors) {
				$errors->add('error', sprintf(__('%s error - %s', 'quick-login'), $this->getLabel(), 'Invalid oAuth1 state, what you trying to do?'));
				return $errors;
			});
		} else {
			try {
				$tokenCredentials = $server->getTokenCredentials($temporaryCredentials, $_GET['oauth_token'], $_GET['oauth_verifier']);
				$userDetails = $server->getUserDetails($tokenCredentials);

				Login::doAuth($this, [
					'id'			=>	$server->getUserUid($tokenCredentials),
					'email'			=>	$userDetails->email,
					'username'		=>	$userDetails->nickname,
					'display_name'	=>	$userDetails->name,
					'first_name'	=>	$userDetails->firstName,
					'last_name'		=>	$userDetails->lastName,
					'description'	=>	$userDetails->description
				]);
			} catch (\Exception $e) {
				add_filter('wp_login_errors', function($errors) use($e) {
					$errors->add('error', sprintf('%s - %s', $this->getLabel(), $e->getMessage()));
					return $errors;
				});
			}
		}
	}

	protected function doOAuth2() {
		$client = $this->getClient();

		if (isset($_REQUEST['error'])) {
			add_filter('wp_login_errors', function($errors) {
				$errors->add('error', sprintf('%s - %s', $this->getLabel(), isset($_REQUEST['error_description']) ? $_REQUEST['error_description'] : $_REQUEST['error']));
				return $errors;
			});
		} elseif (!isset($_REQUEST['code'])) {
			$authUrl = $client->getAuthorizationUrl([
				'scope'	=>	$this->getScope()
			]);
			set_transient('quick-login-state', $client->getState(), 600);
			wp_redirect($authUrl);
			exit;
		} elseif (!isset($_REQUEST['state']) || ($_REQUEST['state'] !== get_transient('quick-login-state'))) {
			add_filter('wp_login_errors', function($errors) {
				$errors->add('error', sprintf(__('%s error - %s', 'quick-login'), $this->getLabel(), 'Invalid oAuth2 state, what you trying to do?'));
				return $errors;
			});
		} else {
			try {
				$token = $client->getAccessToken('authorization_code', ['code' => $_GET['code']]);
				$user = $client->getResourceOwner($token);

				Login::doAuth($this, [
					'id'			=>	$user->getId(),
					'email'			=>	$user->getEmail(),
					'display_name'	=>	$user->getName(),
					'username'		=>	method_exists($user, 'getUsername') ? $user->getUsername() : '',
					'first_name'	=>	method_exists($user, 'getFirstName') ? $user->getFirstName() : '',
					'last_name'		=>	method_exists($user, 'getLastName') ? $user->getLastName() : '',
					'description'	=>	''
				]);
			} catch (\Exception $e) {
				add_filter('wp_login_errors', function($errors) use($e) {
					$errors->add('error', sprintf('%s - %s', $this->getLabel(), $e->getMessage()));
					return $errors;
				});
			}
		}
	}

}
