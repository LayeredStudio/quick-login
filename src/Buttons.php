<?php
namespace Layered\QuickLogin;

use Layered\QuickLogin\Provider;
use WP_User;

class Buttons {

	protected $options;

	public static function start() {
		return new static;
	}

	public function __construct() {
		$this->options = get_option('quick-login');

		add_action('quick_login_separator', [$this, 'separator']);
		add_action('quick_login_heading', [$this, 'heading']);
		add_action('quick_login_label', [$this, 'label']);
		add_action('wp_enqueue_scripts', [$this, 'globalAssets']);

		// on login forms
		add_action('login_enqueue_scripts', [$this, 'loginAssets']);
		if ($this->options['login-form'] === 'top') {
			add_action('woocommerce_login_form_start', [$this, 'onWooCommerceForms'], 1, 300);
		} elseif ($this->options['login-form'] === 'bottom') {
			add_action('woocommerce_login_form_end', [$this, 'onWooCommerceForms']);
		}

		// on register forms
		if ($this->options['register-form'] === 'top') {
			add_action('woocommerce_register_form_start', [$this, 'onWooCommerceForms']);
		} elseif ($this->options['register-form'] === 'bottom') {
			add_action('woocommerce_register_form_end', [$this, 'onWooCommerceForms']);
		}

		// on comment forms
		if (!is_user_logged_in()) {
			add_action('comment_form_top', [$this, 'onCommentsForm']);
		}
		add_action('comment_form_must_log_in_after', [$this, 'onCommentsForm']);

		// in shortcode
		add_shortcode('quick-login', [$this, 'shortcode']);
	}

	public function globalAssets() {
		wp_enqueue_style('quick-login', plugins_url('assets/quick-login.css', dirname(__FILE__)), [], 0.5);
	}

	public function separator($separator = '') {
		return '<div class="quick-login-separator"><span>' . __('or', 'quick-login') . '</span></div>';
	}

	public function heading($heading = '') {
		return __('Login with:', 'quick-login');
	}

	public function label($label = '') {
		return __('Continue with <strong>%s</strong>', 'quick-login');
	}

	public function onWooCommerceForms() {
		$form = in_array(current_action(), ['woocommerce_register_form_start', 'woocommerce_register_form_end']) ? 'register' : 'login';
		echo self::renderLogins([
			'style'		=>	$this->options[$form . '-style'],
			'separator'	=>	$this->options[$form . '-form'] == 'bottom' ? 'top' : 'bottom'
		]);
	}

	public function onCommentsForm() {
		if ($this->options['comment-form'] === 'top') {
			echo self::renderLogins(['style' => $this->options['comment-style']]);
		}
	}

	public function shortcode(array $atts) {
		$atts = shortcode_atts([
			'style'			=>	'button',
			'separator'		=>	'no',
			'heading'		=>	apply_filters('quick_login_heading', '')
		], $atts, 'quick-login');

		return self::renderLogins($atts);
	}

	public function loginAssets() {
		wp_enqueue_style('quick-login', plugins_url('assets/quick-login.css', dirname(__FILE__)), [], 0.5);
		wp_register_script('quick-login', plugins_url('assets/quick-login.js', dirname(__FILE__)), ['jquery'], 0.5);
		wp_localize_script('quick-login', 'QuickLogin', [
			'login'				=>	$this->options['login-form'],
			'loginButtons'		=>	self::renderLogins([
				'style' 	=>	$this->options['login-style'],
				'separator'	=>	$this->options['login-form'] == 'bottom' ? 'top' : 'bottom'
			]),
			'register'			=>	$this->options['register-form'],
			'registerButtons'	=>	self::renderLogins([
				'style'		=>	$this->options['register-style'],
				'separator'	=>	$this->options['register-form'] == 'bottom' ? 'top' : 'bottom',
				'heading'	=>	__('Register with:', 'quick-login')
			])
		]);

		wp_enqueue_script('quick-login');
	}

	public static function renderLogins(array $options = []) {
		$options = array_merge([
			'style'			=>	'button',
			'separator'		=>	'no',
			'heading'		=>	apply_filters('quick_login_heading', ''),
			'label'			=>	apply_filters('quick_login_label', ''),
			'rel'			=>	apply_filters('quick_login_link_rel', 'nofollow')
		], $options);

		$providers = quickLoginProviders(['status' => 'enabled']);

		$html = '';

		if (!count($providers)) {
			return $html;
		}

		if (!in_array($options['style'], ['button', 'icon'])) {
			$options['style'] = 'button';
		}

		if ($options['separator'] === 'top') {
			$html .= '<div class="quick-login-clear"></div>';
			$html .= apply_filters('quick_login_separator', '');
		}

		$html .= '<div class="quick-login-buttons">';
		$html .= '<p class="quick-login-label"><label>' . $options['heading'] . '</label></p>';

		foreach ($providers as $provider) {
			$label = sprintf($options['label'], $provider->getLabel());

			$html .= '<a href="' . $provider->getLoginUrl() . '" rel="' . esc_attr($options['rel']) . '" class="quick-login-' . $options['style'] . ' quick-login-provider-' . $provider->getId() . '" style="--quick-login-color: ' . $provider->getColor() . '" title="' . esc_attr(wp_strip_all_tags($label)) . '">';
			$html .= $provider->getIcon();
			if ($options['style'] === 'button') {
				$html .= '<span>' . $label . '</span>';
			}
			$html .= '</a>';
		}

		$html .= '</div>';

		if ($options['separator'] === 'bottom') {
			$html .= '<div class="quick-login-clear"></div>';
			$html .= apply_filters('quick_login_separator', '');
		}

		return $html;
	}

	public static function renderLinkedAccounts(WP_User $user) {
		$providers = quickLoginProviders(['status' => 'enabled']);
		$fieldLabels = [
			'id'			=>	__('User ID', 'quick-login'),
			'user_email'	=>	__('Email', 'quick-login'),
			'user_login'	=>	__('Username', 'quick-login'),
			'display_name'	=>	__('Name', 'quick-login'),
			'first_name'	=>	__('First Name', 'quick-login'),
			'last_name'		=>	__('Last Name', 'quick-login'),
			'description'	=>	__('Description', 'quick-login'),
			'locale'		=>	__('Language', 'quick-login')
		];
		?>
		<?php foreach ($providers as $provider) : ?>
			<?php
			$providerInfo = get_user_meta($user->ID, $provider->getId() . '_info', true);
			?>
			<div class="quick-login-user-provider quick-login-user-provider-<?php echo $providerInfo ? 'linked' : 'unlinked' ?>" style="--quick-login-color: <?php echo $provider->getColor() ?>">
				<div class="quick-login-user-provider-heading">
					<?php if ($providerInfo) : ?>
						<a href="<?php echo add_query_arg(['quick-login-unlink' => $provider->getId(), 'user_id' => $user->ID], site_url('/wp-login.php')) ?>"><?php _e('Unlink', 'quick-login') ?></a>
					<?php endif ?>
					<?php echo $provider->getIcon() ?> <?php echo $provider->getLabel() ?>
				</div>
				<div class="quick-login-user-provider-content">
					<?php if ($providerInfo) : ?>
						<?php $userData = $provider->convertFields($providerInfo['user']); ?>

						<span class="quick-login-user-provider-more">&darr;</span>
						<div class="quick-login-user-provider-user">
							<?php if ($userData['user_url']) : ?><a href="<?php echo $userData['user_url'] ?>" target="_blank" class="quick-login-user-provider-profile"><?php else : ?><span class="quick-login-user-provider-profile"><?php endif ?>
								<?php if ($userData['avatar']) : ?>
									<img src="<?php echo $userData['avatar'] ?>" alt="<?php echo $userData['display_name'] ?>" width="24">
								<?php endif ?>
								<?php echo $userData['user_login'] ?: $userData['user_email'] ?: $userData['display_name'] ?>
							<?php if ($userData['user_url']) : ?></a><?php else : ?></span><?php endif ?>
						</div>

						<ul>
							<?php foreach ($userData as $field => $value) : ?>
								<?php if ($value && isset($fieldLabels[$field])) : ?>
									<li><strong><?php echo $fieldLabels[$field] ?></strong>: <?php echo $value ?></li>
								<?php endif ?>
							<?php endforeach ?>
							<li><strong>Scope</strong>: <?php echo implode(', ', $providerInfo['scope']) ?></li>
						</ul>
					<?php elseif ($user->ID == get_current_user_id()) : ?>
						<a href="<?php echo $provider->getLoginUrl() ?>"><?php _e('Link account', 'quick-login') ?></a>
					<?php else : ?>
						<i><?php _e('not linked', 'quick-login') ?></i>
					<?php endif ?>
				</div>
			</div>
		<?php endforeach ?>

		<script>
		jQuery('.quick-login-user-provider-more').click(function() {
			jQuery(this).closest('.quick-login-user-provider').toggleClass('expanded');
		});
		</script>
		<?php
	}

}
