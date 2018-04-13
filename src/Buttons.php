<?php
namespace Layered\QuickLogin;

use Layered\QuickLogin\Provider;

class Buttons {

	protected $options;

	public static function start() {
		return new static;
	}

	public function __construct() {
		$this->options = get_option('quick-login');

		add_action('quick_login_separator', [$this, 'separator']);
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
		add_action('comment_form_top', [$this, 'onCommentsForm']);
		add_action('comment_form_must_log_in_after', [$this, 'onCommentsForm']);

		// in shortcode
		add_shortcode('quick-login', [$this, 'shortcode']);
	}

	public function separator(string $separator = '') {
		return '<div class="quick-login-separator"><span>' . __('or', 'quick-login') . '</span></div>';
	}

	public function onWooCommerceForms() {
		$form = in_array(current_action(), ['woocommerce_register_form_start', 'woocommerce_register_form_end']) ? 'register' : 'login';
		echo self::renderLogins($this->options[$form . '-style'], $this->options[$form . '-form'] == 'bottom' ? 'top' : 'bottom');
	}

	public function onCommentsForm() {
		if ($this->options['comment-form'] === 'top') {
			echo self::renderLogins($this->options['comment-style']);
		}
	}

	public function shortcode(array $atts) {
		$atts = shortcode_atts([
			'style'			=>	'button',
			'separator'		=>	'no'
		], $atts, 'quick-login');

		return self::renderLogins($atts['style'], $atts['separator']);
	}

	public function globalAssets() {
		wp_enqueue_style('quick-login', plugins_url('assets/quick-login.css', dirname(__FILE__)));
	}

	public function loginAssets() {
		wp_enqueue_style('quick-login', plugins_url('assets/quick-login.css', dirname(__FILE__)));
		wp_register_script('quick-login', plugins_url('assets/quick-login.js', dirname(__FILE__)), ['jquery']);
		wp_localize_script('quick-login', 'QuickLogin', [
			'login'				=>	$this->options['login-form'],
			'register'			=>	$this->options['register-form'],
			'loginButtons'		=>	self::renderLogins($this->options['login-style']),
			'registerButtons'	=>	self::renderLogins($this->options['register-style'])
		]);

		wp_enqueue_script('quick-login');
	}

	public static function renderLogins(string $style = 'button', string $separatorPosition = null) {
		$providers = apply_filters('quick_login_providers', []);
		$providers = array_filter($providers, function(Provider $provider) {
			return $provider->getOption('status') === 'enabled';
		});

		$html = '';

		if (!count($providers)) {
			return $html;
		}

		if ($separatorPosition === 'top') {
			$html .= apply_filters('quick_login_separator', '');
		}

		$html .= '<div class="quick-login-buttons">';

		if (!in_array($style, ['button', 'icon'])) {
			$style = 'button';
		}

		$html .= '<p class="quick-login-label"><label>' . __('Sign in with:', 'quick-login') . '</label></p>';

		foreach ($providers as $provider) {
			$label = sprintf(__('Sign in with <strong>%s</strong>', 'quick-login'), $provider->getLabel());

			$html .= '<a href="' . $provider->getLoginUrl() . '" rel="nofollow" class="quick-login-' . $style . ' quick-login-' . $provider->getId() . '" style="--quick-login-color: ' . $provider->getColor() . '" title="' . esc_attr(wp_strip_all_tags($label)) . '">';
			$html .= $provider->getIcon();
			if ($style === 'button') {
				$html .= '<span>' . $label . '</span>';
			}
			$html .= '</a>';
		}

		$html .= '</div>';

		if ($separatorPosition === 'bottom') {
			$html .= apply_filters('quick_login_separator', '');
		}

		return $html;
	}

}
