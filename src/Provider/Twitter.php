<?php
namespace Layered\QuickLogin\Provider;

use League\OAuth1\Client\Server\User;
use Layered\QuickLogin\Provider;

class Twitter extends Provider {

	public function __construct() {
		$this->oAuthVersion = 'oAuth1';
		$this->id = 'twitter';
		$this->label = 'Twitter';
		$this->color = '#4ab3f4';
		$this->icon = '<svg aria-labelledby="simpleicons-twitter-icon" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M23.954 4.569c-.885.389-1.83.654-2.825.775 1.014-.611 1.794-1.574 2.163-2.723-.951.555-2.005.959-3.127 1.184-.896-.959-2.173-1.559-3.591-1.559-2.717 0-4.92 2.203-4.92 4.917 0 .39.045.765.127 1.124C7.691 8.094 4.066 6.13 1.64 3.161c-.427.722-.666 1.561-.666 2.475 0 1.71.87 3.213 2.188 4.096-.807-.026-1.566-.248-2.228-.616v.061c0 2.385 1.693 4.374 3.946 4.827-.413.111-.849.171-1.296.171-.314 0-.615-.03-.916-.086.631 1.953 2.445 3.377 4.604 3.417-1.68 1.319-3.809 2.105-6.102 2.105-.39 0-.779-.023-1.17-.067 2.189 1.394 4.768 2.209 7.557 2.209 9.054 0 13.999-7.496 13.999-13.986 0-.209 0-.42-.015-.63.961-.689 1.8-1.56 2.46-2.548l-.047-.02z"/></svg>';
		$this->scope = [];

		$this->userSettings = array(
			'consumerKey'		=>	array(
				'name'		=>	__('API key', 'quick-login'),
				'required'	=>	true,
				'type'		=>	'text',
				'default'	=>	''
			),
			'consumerSecret'	=>	array(
				'name'		=>	__('API secret key', 'quick-login'),
				'required'	=>	true,
				'type'		=>	'text',
				'default'	=>	''
			)
		);
	}

	public function instructions() {
		?>
		<p><strong>Twitter Login</strong> requires credentials for a Twitter App. <button class="button button-small quick-login-provider-instructions-btn">Show instructions ↕</button></p>
		<ol class="quick-login-provider-instructions">
			<li>Create (or edit) a Twitter app on <a href="https://developer.twitter.com/en/apps" target="_blank">Twitter Apps page</a>
				<ul>
					<li>Fill <strong>Name</strong>, <strong>Description</strong> and <strong>Website</strong> with site's info</li>
					<li>Enable <strong>Enable Sign in with Twitter</strong> option</li>
					<li>Fill <strong>Callback URL</strong> with <code><?php echo site_url('/wp-login.php') ?></code></li>
					<li><strong>Important!</strong> Fill in the <strong>Privacy Policy URL</strong> and <strong>Terms of Service URL</strong> fields</li>
					<li>On app's <strong>Permissions</strong> tab, select <strong>Read only</strong> for Access type</li>
					<li>On app's <strong>Permissions</strong> tab, enable <strong>Request email addresses from users</strong>. Required for user sign-in verification</li>
				</ul>
			</li>
			<li>Navigate to <strong>Keys and tokens</strong> app's tab
				<ul>
					<li>Copy and fill below the <strong>API key</strong> field</li>
					<li>Copy and fill below the <strong>API secret key</strong> field</li>
				</ul>
			</li>
		</ol>
		<?php
	}

	protected function getServer() {
		return new \League\OAuth1\Client\Server\Twitter([
			'identifier'	=>	$this->getOption('consumerKey'),
			'secret'		=>	$this->getOption('consumerSecret'),
			'callback_uri'	=>	site_url('/wp-login.php?quick-login=twitter')
		]);
	}

	public function convertFields(User $user) {
		return [
			'id'			=>	$user->uid,
			'user_login'	=>	$user->nickname,
			'user_email'	=>	$user->email,
			'display_name'	=>	$user->name,
			'first_name'	=>	$user->firstName,
			'last_name'		=>	$user->lastName,
			'description'	=>	$user->description,
			'user_url'		=>	'https://twitter.com/' . $user->nickname,
			'locale'		=>	'', // TODO process $user->extra['lang'] to 'locale'
			'avatar'		=>	$user->imageUrl
		];
	}

}
