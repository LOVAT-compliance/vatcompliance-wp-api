<?php

class Lovat_Admin
{
	private static $errors;
	private static $success;
	private static $warning;

	/**
	 * init
	 */
	public static function init()
	{
		// Add menu
		add_action('admin_menu', array(__class__, 'add_menu'));
		// Enqueue the scripts and styles
		if (self::get_current_admin_url()) {
			add_action('admin_enqueue_scripts', array(__class__, 'enqueue_scripts'));
			add_action('admin_init', array(__class__, 'save_settings'));
		}
	}

	/**
	 * add lovat menu
	 */
	public static function add_menu()
	{
		add_options_page('Lovat', 'Api Settings', 'manage_options', 'icon_title', array(__class__, 'lovat_settings_page'));
		add_menu_page('Lovat', 'Api Settings', 'administrator', __FILE__, array(__class__, 'lovat_settings_page'), LOVAT_API_URL . 'admin/images/logo_lovat.png');
	}

	/**
	 * enqueue scripts
	 */
	public static function enqueue_scripts()
	{
		wp_enqueue_script('admin-jquery-js', LOVAT_API_URL . 'admin/js/jquery-2.0.3.min.js', array('jquery'), LOVAT_API_PLUGIN_VERSION, false);
		wp_enqueue_style('admin-datatables', LOVAT_API_URL . 'admin/css/datatables.css', array(), LOVAT_API_PLUGIN_VERSION);
		wp_enqueue_style('admin-style', LOVAT_API_URL . 'admin/css/style.css', array(), LOVAT_API_PLUGIN_VERSION);
		wp_enqueue_script('admin-datatables-js', LOVAT_API_URL . 'admin/js/datatables.js', array('jquery'), LOVAT_API_PLUGIN_VERSION, true);
		wp_enqueue_script('admin-datatables-call-js', LOVAT_API_URL . 'admin/js/datatables-call.js', array('jquery'), LOVAT_API_PLUGIN_VERSION, true);
	}

	/**
	 * include settings page
	 */
	public static function lovat_settings_page()
	{
		$user = wp_get_current_user();
		$helper = new Lovat_Helper();

		$arrayKeys = $helper->generated_keys();
		$arrayCountries = require LOVAT_API_PLUGIN_DIR . '/includes/countries.php';
		$lovatData = $helper->get_lovat_option_value();
		if (!is_null($helper->isset_token_by_user($user->ID))) self::add_warning('You have already generated a token. When you click on the "Generate key" button, you will UPDATE it.');
		include(LOVAT_API_PLUGIN_DIR . '/admin/views/api_settings.php');
	}

	/**
	 * Save settings
	 */
	public static function save_settings()
	{
		global $wpdb;

		if (!empty($_POST['generate-key'])) {
			if (wc_current_user_has_role('administrator')) {
				$bearerToken = self::create_key();
				$user = wp_get_current_user();
				$issetUserToken = (new Lovat_Helper)->isset_token_by_user($user->ID);

				if (!is_null($issetUserToken)) {
					$wpdb->update(
						$wpdb->prefix . 'lovat_api_keys',
						array('token' => $bearerToken),
						array('user_id' => $user->ID)
					);

					self::add_success('The key has been successfully updated. New key : ' . $bearerToken);
				} else {
					$wpdb->insert(
						$wpdb->prefix . 'lovat_api_keys',
						array('user_id' => $user->ID, 'token' => $bearerToken),
						array('%s', '%s',)
					);

					self::add_success('The key was successfully generated. Key : ' . $bearerToken);
				}
			} else self::add_error('Only a user with the administrator role can generate a key.');

			//clear cache
			wp_cache_delete(LOVAT_GENERATED_KEYS);
			wp_cache_delete(ISSET_TOKEN_BY_USER . $user->ID);
		}

		if (!empty($_POST['save-departure-country'])) {
			$country = '';
			$calculateTax = 'off';

			if (!empty($_POST['departure-select-country'])) {
				$country = $_POST['departure-select-country'];
			}

			if (!empty($_POST['calculate_tax'])) {
				$calculateTax = 'on';
			}

			$departureZip = $_POST['departure_zip'];
			$accessToken = $_POST['access_token'];

			$lovatOptions = json_encode(array(
				'country' => $country,
				'departureZip' => $departureZip,
				'access_token' => $accessToken,
				'calculate_tax' => $calculateTax
			));

			update_option('lovat_departure_country', $lovatOptions);
			wp_cache_delete(LOVAT_CACHE_OPTION_VALUE); // clear cache
			self::add_success('Shipping country and zip saved successfully');
		}
	}

	/**
	 * @param $text
	 */
	public static function add_error($text)
	{
		self::$errors = $text;
	}

	/**
	 * @param $text
	 */
	public static function add_success($text)
	{
		self::$success = $text;
	}

	/**
	 * @param $text
	 */
	public static function add_warning($text)
	{
		self::$warning = $text;
	}

	/**
	 * @return string
	 */
	public static function show_error_message()
	{
		if (!is_null(self::$errors))
			return '<div class="lovat-alert danger-alert" role="alert">' . self::$errors . '
				<a class="close-lovat-alert">&times;</a>
			</div>';
	}

	/**
	 * @return string
	 */
	public static function show_success_message()
	{
		if (!is_null(self::$success))
			return '<div class="lovat-alert success-alert" role="alert">' . self::$success . '
				<a class="close-lovat-alert">&times;</a>
			</div>';
	}

	/**
	 * @return string
	 */
	public static function show_warning_message()
	{
		if (!is_null(self::$warning))
			return '<div class="lovat-alert warning-alert" role="alert">' . self::$warning . '
				<a class="close-lovat-alert">&times;</a>
  			</div>';
	}

	/**
	 * @return string
	 */
	public static function create_key()
	{
		return 'lt_' . wc_rand_hash();
	}

	/**
	 * @return bool
	 */
	public static function get_current_admin_url()
	{
		$uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
		$uri = preg_replace('|^.*/wp-admin/|i', '', $uri);

		if (!$uri) return false;
		if (strpos($uri, 'lovat-admin.php')) return true;

		return false;
	}
}

Lovat_Admin::init();