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
		add_action('comment_form_top', [$this, 'onCommentsForm']);
		add_action('comment_form_must_log_in_after', [$this, 'onCommentsForm']);

		// in shortcode
		add_shortcode('quick-login', [$this, 'shortcode']);
	}

	public function globalAssets() {
		wp_enqueue_style('quick-login', plugins_url('assets/quick-login.css', dirname(__FILE__)), [], 0.1);
	}

	public function separator(string $separator = '') {
		return '<div class="quick-login-separator"><span>' . __('or', 'quick-login') . '</span></div>';
	}

	public function heading(string $heading = '') {
		return __('Sign in with:', 'quick-login');
	}

	public function label(string $label = '') {
		return __('Sign in with <strong>%s</strong>', 'quick-login');
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
		wp_enqueue_style('quick-login', plugins_url('assets/quick-login.css', dirname(__FILE__)), [], 0.1);
		wp_register_script('quick-login', plugins_url('assets/quick-login.js', dirname(__FILE__)), ['jquery'], 0.1);
		wp_localize_script('quick-login', 'QuickLogin', [
			'login'				=>	$this->options['login-form'],
			'loginButtons'		=>	self::renderLogins([
				'style' 	=>	$this->options['login-style'],
				'separator'	=>	$this->options['login-style'] == 'bottom' ? 'top' : 'bottom'
			]),
			'register'			=>	$this->options['register-form'],
			'registerButtons'	=>	self::renderLogins([
				'style'		=>	$this->options['register-style'],
				'separator'	=>	$this->options['register-style'] == 'bottom' ? 'top' : 'bottom'
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
			'rel'			=>	apply_filters('quick_login_link_rle', 'nofollow')
		], $options);

		$providers = apply_filters('quick_login_providers', []);
		$providers = array_filter($providers, function(Provider $provider) {
			return $provider->getOption('status') === 'enabled';
		});

		$html = '';

		if (!count($providers)) {
			return $html;
		}

		if (!in_array($options['style'], ['button', 'icon'])) {
			$options['style'] = 'button';
		}

		if ($options['separator'] === 'top') {
			$html .= apply_filters('quick_login_separator', '');
			$html .= '<div class="quick-login-clear"></div>';
		}

		$html .= '<div class="quick-login-buttons">';
		$html .= '<p class="quick-login-label"><label>' . $options['heading'] . '</label></p>';

		foreach ($providers as $provider) {
			$label = sprintf($options['label'], $provider->getLabel());

			$html .= '<a href="' . $provider->getLoginUrl() . '" rel="' . esc_attr($options['rel']) . '" class="quick-login-' . $options['style'] . ' quick-login-' . $provider->getId() . '" style="--quick-login-color: ' . $provider->getColor() . '" title="' . esc_attr(wp_strip_all_tags($label)) . '">';
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

}
