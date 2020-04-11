<?php
namespace Layered\QuickLogin\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Layered\QuickLogin\Provider;

class Facebook extends Provider {

	public function __construct() {
		$this->oAuthVersion = 'oAuth2';
		$this->id = 'facebook';
		$this->label = 'Facebook';
		$this->color = '#3B5998';
		$this->icon = '<svg aria-labelledby="simpleicons-facebook-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.676 0H1.324C.593 0 0 .593 0 1.324v21.352C0 23.408.593 24 1.324 24h11.494v-9.294H9.689v-3.621h3.129V8.41c0-3.099 1.894-4.785 4.659-4.785 1.325 0 2.464.097 2.796.141v3.24h-1.921c-1.5 0-1.792.721-1.792 1.771v2.311h3.584l-.465 3.63H16.56V24h6.115c.733 0 1.325-.592 1.325-1.324V1.324C24 .593 23.408 0 22.676 0"/></svg>';
		$this->scope = ['email'];

		$this->userSettings = array(
			'clientId'		=>	array(
				'name'		=>	__('App ID', 'quick-login'),
				'required'	=>	true,
				'type'		=>	'text',
				'default'	=>	''
			),
			'clientSecret'	=>	array(
				'name'		=>	__('App Secret', 'quick-login'),
				'required'	=>	true,
				'type'		=>	'text',
				'default'	=>	''
			)
		);
	}

	public function instructions() {
		?>
		<p><strong>Facebook Login</strong> requires credentials for a Facebook App. <button class="button button-small quick-login-provider-instructions-btn">Show instructions ↕</button></p>

		<?php if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') : ?>
			<div class="notice notice-error">
				<p><?php _e('Hey! HTTPS in not enabled for this site, Facebook Login may not work as expected', 'quick-login') ?></p>
			</div>
		<?php endif ?>

		<ol class="quick-login-provider-instructions">
			<li>Create (or edit) a Facebook app on <a href="https://developers.facebook.com/apps/" target="_blank">Facebook Apps page</a>
				<ul>
					<li>On <strong>Settings -> Basic</strong> page, fill and save site specific data: Name, Icon, Category, Privacy Policy URL</li>
					<li>On left sidebar, click <strong>Products (+)</strong>. From products page, choose <strong>Facebook Login</strong> -> Web</li>
					<li>Fill site url <code><?php echo site_url() ?></code></li>
					<li>Info - <i>Facebook SDK for Javascript</i> is not required for Quick Login, rest of steps presented on Facebook can be skipped</li>
					<li>Navigate to <strong>Facebook Login -> Settings</strong> page</li>
					<li>Add <code><?php echo site_url('/wp-login.php?quick-login=facebook') ?></code> in <strong>Valid OAuth Redirect URIs</strong> field</li>
					<li>Navigate to <strong>App Review</strong> page</li>
					<li>Switch the toggle to <strong>On</strong> to make your app live</li>
				</ul>
			</li>
			<li>Navigate to <strong>Settings -> Basic</strong> page
				<ul>
					<li>Copy and fill below the <strong>App ID</strong> field</li>
					<li>Copy and fill below the <strong>App Secret</strong> field</li>
				</ul>
			</li>
		</ol>
		<?php
	}

	protected function getClient() {
		return new \League\OAuth2\Client\Provider\Facebook([
			'clientId'					=>	$this->getOption('clientId'),
			'clientSecret'				=>	$this->getOption('clientSecret'),
			'redirectUri'				=>	site_url('/wp-login.php?quick-login=facebook'),
			'graphApiVersion'			=>	'v6.0'
		]);
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
			'user_url'		=>	$user->getLink(),
			'locale'		=>	'',
			'avatar'		=>	$user->getPictureUrl()
		];
	}

}
