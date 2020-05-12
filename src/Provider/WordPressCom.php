<?php
namespace Layered\QuickLogin\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Layered\QuickLogin\Provider;

class WordPressCom extends Provider {

	public function __construct() {
		$this->oAuthVersion = 'oAuth2';
		$this->id = 'wordpresscom';
		$this->label = 'WordPress.com';
		$this->color = '#21759B';
		$this->icon = '<svg aria-labelledby="simpleicons-wordpress-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M21.469 6.825c.84 1.537 1.318 3.3 1.318 5.175 0 3.979-2.156 7.456-5.363 9.325l3.295-9.527c.615-1.54.82-2.771.82-3.864 0-.405-.026-.78-.07-1.11m-7.981.105c.647-.03 1.232-.105 1.232-.105.582-.075.514-.93-.067-.899 0 0-1.755.135-2.88.135-1.064 0-2.85-.15-2.85-.15-.585-.03-.661.855-.075.885 0 0 .54.061 1.125.09l1.68 4.605-2.37 7.08L5.354 6.9c.649-.03 1.234-.1 1.234-.1.585-.075.516-.93-.065-.896 0 0-1.746.138-2.874.138-.2 0-.438-.008-.69-.015C4.911 3.15 8.235 1.215 12 1.215c2.809 0 5.365 1.072 7.286 2.833-.046-.003-.091-.009-.141-.009-1.06 0-1.812.923-1.812 1.914 0 .89.513 1.643 1.06 2.531.411.72.89 1.643.89 2.977 0 .915-.354 1.994-.821 3.479l-1.075 3.585-3.9-11.61.001.014zM12 22.784c-1.059 0-2.081-.153-3.048-.437l3.237-9.406 3.315 9.087c.024.053.05.101.078.149-1.12.393-2.325.609-3.582.609M1.211 12c0-1.564.336-3.05.935-4.39L7.29 21.709C3.694 19.96 1.212 16.271 1.211 12M12 0C5.385 0 0 5.385 0 12s5.385 12 12 12 12-5.385 12-12S18.615 0 12 0"/></svg>';
		$this->scope = ['auth'];

		$this->userSettings = array(
			'clientId'		=>	array(
				'name'		=>	__('Client ID', 'quick-login'),
				'required'	=>	true,
				'type'		=>	'text',
				'default'	=>	''
			),
			'clientSecret'	=>	array(
				'name'		=>	__('Client Secret', 'quick-login'),
				'required'	=>	true,
				'type'		=>	'text',
				'default'	=>	''
			)
		);
	}

	public function instructions() {
		?>
		<p><strong>WordPress.com Login</strong> requires credentials for a WordPress.com app. <button class="button button-small quick-login-provider-instructions-btn">Show instructions ↕</button></p>
		<ol class="quick-login-provider-instructions">
			<li>Create (or edit) a WordPress.com app on <a href="https://developer.wordpress.com/apps/" target="_blank">WordPress.com Apps page</a>
				<ul>
					<li>If creating, click on <strong>Create New Application</strong> button</li>
					<li>If editing, click <strong>Manage Application -> Manage Settings</strong> on a specific app</li>
					<li>Fill <strong>Name</strong>, <strong>Description</strong>, <strong>Icon</strong> and <strong>Website URL</strong> with site specific info</li>
					<li>Fill <strong>Redirect URLs</strong> with <code><?php echo site_url('/wp-login.php?quick-login=wordpresscom') ?></code></li>
					<li>Update or Save app</li>
				</ul>
			</li>
			<li>On app page, scroll down to <strong>OAuth Information</strong> section
				<ul>
					<li>Copy and fill below the <strong>Client ID</strong> field</li>
					<li>Copy and fill below the <strong>Client secret</strong> field</li>
				</ul>
			</li>
		</ol>
		<?php
	}

	protected function getClient() {
		return new \Layered\OAuth2\Client\Provider\WordPressCom([
			'clientId'					=>	$this->getOption('clientId'),
			'clientSecret'				=>	$this->getOption('clientSecret'),
			'redirectUri'				=>	site_url('/wp-login.php?quick-login=wordpresscom')
		]);
	}

	public function convertFields(ResourceOwnerInterface $user) {
		return [
			'id'			=>	$user->getId(),
			'user_login'	=>	$user->getUsername(),
			'user_email'	=>	$user->getEmail(),
			'display_name'	=>	$user->getDisplayName(),
			'first_name'	=>	'',
			'last_name'		=>	'',
			'description'	=>	'',
			'user_url'		=>	$user->getProfileUrl(),
			'locale'		=>	'', // TODO process $user->getLanguage() to 'locale'
			'avatar'		=>	$user->getAvatarUrl()
		];
	}

}
