<?php
namespace Layered\QuickLogin\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Layered\QuickLogin\Provider;

class LinkedIn extends Provider {

	public $email;

	public function __construct() {
		$this->oAuthVersion = 'oAuth2';
		$this->id = 'linkedin';
		$this->label = 'LinkedIn';
		$this->color = '#0077B5';
		$this->icon = '<svg aria-labelledby="simpleicons-linkedin-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';
		$this->scope = ['r_liteprofile', 'r_emailaddress'];

		$this->userSettings = array(
			'clientId'		=>	array(
				'name'		=>	__('Client ID', 'quick-login'),
				'required'	=>	true,
				'type'		=>	'text',
				'default'	=>	''
			),
			'clientSecret'	=>	array(
				'name'		=>	__('Client secret', 'quick-login'),
				'required'	=>	true,
				'type'		=>	'text',
				'default'	=>	''
			)
		);
	}

	public function instructions() {
		?>
		<p><strong>LinkedIn Login</strong> requires credentials for a LinkedIn App. <button class="button button-small quick-login-provider-instructions-btn">Show instructions ↕</button></p>
		<ol class="quick-login-provider-instructions">
			<li>Create (or edit) an App on <a href="https://www.linkedin.com/developer/apps" target="_blank">LinkedIn Developers page</a></li>
			<li>Navigate to app page
				<ul>
					<li>Select the <strong>Auth</strong> tab</li>
					<li>In <strong>Permissions</strong> section enable the following: <code>r_liteprofile</code> and <code>r_emailaddress</code></li>
					<li>In <strong>OAuth 2.0 settings</strong> add the following Redirect URL: <code><?php echo site_url('/wp-login.php?quick-login=linkedin') ?></code></li>
				</ul>
			</li>
			<li>Select the <strong>Authentication</strong> tab
				<ul>
					<li>Scroll to <strong>Application credentials</strong> section</li>
					<li>Copy and fill below the <strong>Client ID</strong> field</li>
					<li>Copy and fill below the <strong>Client secret</strong> field</li>
				</ul>
			</li>
		</ol>
		<?php
	}

	protected function getClient() {
		return new \League\OAuth2\Client\Provider\LinkedIn([
			'clientId'					=>	$this->getOption('clientId'),
			'clientSecret'				=>	$this->getOption('clientSecret'),
			'redirectUri'				=>	site_url('/wp-login.php?quick-login=linkedin')
		]);
	}

	public function convertFields(ResourceOwnerInterface $user) {
		return [
			'id'			=>	$user->getId(),
			'user_login'	=>	$user->getAttribute('vanityName'),
			'user_email'	=>	$user->getEmail(),
			'display_name'	=>	$user->getFirstName() . ' ' . $user->getLastName(),
			'first_name'	=>	$user->getFirstName(),
			'last_name'		=>	$user->getLastName(),
			'description'	=>	$user->getAttribute('localizedHeadline'),
			'user_url'		=>	$user->getUrl(),
			'locale'		=>	'',
			'avatar'		=>	$user->getImageUrl()
		];
	}

}
