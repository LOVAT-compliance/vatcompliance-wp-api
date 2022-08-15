<?php

class Lovat
{
	protected static $_instance = null;
	public $authentication;

	/**
	 * @return Lovat|null
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Lovat constructor.
	 */
	public function __construct()
	{
		$this->includes();
		//calculate tax
		if ((new Lovat_Helper())->is_tax_calculation_enabled()) {
			$this->update_tax_options();
			(new LovatTaxCalculation())->init_hooks();
		}
	}

	/**
	 * include classes
	 */
	public function includes()
	{
		//includes
		include_once(LOVAT_API_PLUGIN_DIR . '/includes/class-server.php');
		include_once(LOVAT_API_PLUGIN_DIR . '/includes/class-lovat-api-authentication.php');
		$this->authentication = new Lovat_Api_Authentication();

		//admin
		include_once(LOVAT_API_PLUGIN_DIR . '/admin/lovat-admin.php');
	}

	public function update_tax_options()
	{
		// If is enabled and user disables taxes we re-enable them
		update_option('woocommerce_calc_taxes', 'yes');

		// Users can set either billing or shipping address for tax rates but not shop
		update_option('woocommerce_tax_based_on', 'shipping');

		// Rate calculations assume tax not included
		update_option('woocommerce_prices_include_tax', 'no');

		// Use no special handling on shipping taxes, our API handles that
		update_option('woocommerce_shipping_tax_class', '');

		// API handles rounding precision
		update_option('woocommerce_tax_round_at_subtotal', 'no');

		// Rates are calculated in the cart assuming tax not included
		update_option('woocommerce_tax_display_shop', 'excl');

		// Returns one total amount, not line item amounts
		update_option('woocommerce_tax_display_cart', 'excl');

		// Returns one total amount, not line item amounts
		update_option('woocommerce_tax_total_display', 'single');
	}
}