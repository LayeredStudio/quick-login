<?php
namespace Layered\QuickLogin\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Layered\QuickLogin\Provider;

class Google extends Provider {

	public function __construct() {
		$this->oAuthVersion = 'oAuth2';
		$this->id = 'google';
		$this->label = 'Google';
		$this->color = '#4285F4';
		$this->icon = '<svg aria-labelledby="simpleicons-google-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z"/></svg>';
		$this->scope = ['https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile'];

		$this->userSettings = array(
			'clientId'		=>	array(
				'name'			=>	__('Client ID', 'quick-login'),
				'placeholder'	=>	__('Your project\'s client ID', 'quick-login'),
				'required'		=>	true,
				'type'			=>	'text',
				'default'		=>	''
			),
			'clientSecret'	=>	array(
				'name'			=>	__('Client secret', 'quick-login'),
				'placeholder'	=>	__('Your project\'s client secret', 'quick-login'),
				'required'		=>	true,
				'type'			=>	'text',
				'default'		=>	''
			),
			'hostedDomain'	=>	array(
				'name'			=>	__('Hosted Domain', 'quick-login'),
				'placeholder'	=>	__('Ex: company.com - restrict authentication to a hosted G Suite domain', 'quick-login'),
				'required'		=>	false,
				'type'			=>	'text',
				'default'		=>	''
			)
		);
	}

	public function instructions() {
		?>
		<p><strong>Google Login</strong> requires credentials for a Google Cloud Project. <button class="button button-small quick-login-provider-instructions-btn">Show instructions ↕</button></p>
		<ol class="quick-login-provider-instructions">
			<li>Create (or edit) a Project on <a href="https://console.cloud.google.com/apis/credentials?project=_" target="_blank">Google Cloud Console</a></li>
			<li>Navigate to <strong>APIs &amp; Services -> Credentials</strong> page
				<ul>
					<li>On <strong>OAuth consent screen</strong> tab fill your site specific info</li>
					<li>On <strong>Credentials</strong> tab create (or edit) a <strong>OAuth Client ID</strong></li>
					<li>If creating, click on <strong>Create credentials -> OAuth Client ID</strong>. Select <strong>Web application</strong> as Type</li>
					<li>If editing, select a client in <strong>OAuth 2.0 client IDs</strong> section</li>
					<li>Fill <strong>Authorised redirect URIs</strong> with <code><?php echo site_url('/wp-login.php?quick-login=google') ?></code></li>
				</ul>
			</li>
			<li>Navigate to <strong>Credentials -> OAuth 2.0 client IDs -> <i>Your OAuth Client ID</i></strong>
				<ul>
					<li>Copy and fill below the <strong>Client ID</strong> field</li>
					<li>Copy and fill below the <strong>Client secret</strong> field</li>
				</ul>
			</li>
		</ol>
		<?php
	}

	protected function getClient() {
		$options = [
			'clientId'					=>	$this->getOption('clientId'),
			'clientSecret'				=>	$this->getOption('clientSecret'),
			'redirectUri'				=>	site_url('/wp-login.php?quick-login=google'),
			'include_granted_scopes'	=>	true,
		];

		if ($this->getOption('hostedDomain')) {
			$options['hostedDomain'] = $this->getOption('hostedDomain');
		}

		return new \League\OAuth2\Client\Provider\Google($options);
	}

	public function convertFields(ResourceOwnerInterface $user) {
		return [
			'id'			=>	$user->getId(),
			'user_login'	=>	'',
			'user_email'	=>	$user->getEmail(),
			'display_name'	=>	$user->getName(),
			'first_name'	=>	$user->getFirstName(),
			'last_name'		=>	$user->getLastName(),
			'description'	=>	'',
			'user_url'		=>	'',
			'locale'		=>	str_replace('-', '_', $user->getLocale()),
			'avatar'		=>	$user->getAvatar()
		];
	}

}
