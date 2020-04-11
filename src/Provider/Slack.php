<?php
namespace Layered\QuickLogin\Provider;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Layered\QuickLogin\Provider;

class Slack extends Provider {

	public function __construct() {
		$this->oAuthVersion = 'oAuth2';
		$this->id = 'slack';
		$this->label = 'Slack';
		$this->color = '#4A154B';
		$this->icon = '<svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/></svg>';
		$this->scope = ['identity.basic', 'identity.email', 'identity.avatar'];

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
		<p><strong>Slack Login</strong> requires credentials for a Slack App. <button class="button button-small quick-login-provider-instructions-btn">Show instructions ↕</button></p>
		<ol class="quick-login-provider-instructions">
			<li>Create (or edit) an App on <a href="https://api.slack.com/apps" target="_blank">Slack API page</a></li>
			<li>Open the app page
				<ul>
					<li>Navigate to <strong>OAuth & Permissions</strong> section</li>
					<li>In <strong>Redirect URLs</strong> section add <code><?php echo site_url('/wp-login.php?quick-login=slack') ?></code> as a new URL</li>
				</ul>
			</li>
			<li>Navigate to <strong>Basic information</strong>
				<ul>
					<li>Scroll to <strong>App Credentials</strong> section</li>
					<li>Copy and fill below the <strong>Client ID</strong> field</li>
					<li>Copy and fill below the <strong>Client secret</strong> field</li>
				</ul>
			</li>
		</ol>
		<?php
	}

	protected function getClient() {
		return $provider = new GenericProvider([
			'clientId'					=>	$this->getOption('clientId'),
			'clientSecret'				=>	$this->getOption('clientSecret'),
			'redirectUri'				=>	site_url('/wp-login.php?quick-login=slack'),
			'urlAuthorize'				=>	'https://slack.com/oauth/authorize',
			'urlAccessToken'			=>	'https://slack.com/api/oauth.access',
			'urlResourceOwnerDetails'	=>	'https://slack.com/api/users.identity',
			'responseResourceOwnerId'	=>	'user.id'
		]);
	}

	public function convertFields(ResourceOwnerInterface $user) {
		$userData = $user->toArray();

		return [
			'id'			=>	$userData['user']['id'],
			'user_login'	=>	'',
			'user_email'	=>	$userData['user']['email'],
			'display_name'	=>	$userData['user']['name'],
			'first_name'	=>	'',
			'last_name'		=>	'',
			'description'	=>	'',
			'user_url'		=>	'',
			'locale'		=>	'',
			'avatar'		=>	$userData['user']['image_512']
		];
	}

}
