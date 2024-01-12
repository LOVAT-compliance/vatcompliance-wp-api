<?php

class LovatTaxCalculation
{
	/**
	 * init hooks to calculate
	 */
	public function init_hooks()
	{
		// Calculate Tax
		add_action('woocommerce_after_calculate_totals', array($this, 'calculate_tax'), 20);
	}

	/**
	 * Calculate tax
	 *
	 * @throws Exception
	 */
	public function calculate_tax()
	{
		if (defined('DOING_AJAX') && DOING_AJAX) {
			//if get data from ajax
			$helper = new Lovat_Helper();
			$optionValue = $helper->get_lovat_option_value();
			$totalPrice = WC()->cart->get_total('edit');

			$departureCountry = $helper->convertCountry($optionValue->country); //convert from iso2 to iso3
			if (is_null($departureCountry)) {
				$departureCountry = $optionValue->country;
			}

			$requestArray = array(
				'transaction_datetime' => $helper->dateFormat(date('Y-m-d H:i:s')),
				'currency' => get_option('woocommerce_currency'),
				'transaction_sum' => $totalPrice,
				'departure_country' => $departureCountry,
				'departure_zip' => $optionValue->departureZip,
			);

			if (!empty($_POST['billing_postcode']) && !empty($_POST['billing_country'])) {
				//order calculated
				$requestArray["arrival_country"] = $helper->convertCountry($_POST['billing_country']); //convert from iso2 to iso3
				$requestArray["arrival_zip"] = $_POST['billing_postcode'];
			} else {
				//data from ajax cart calculated
				$requestArray["arrival_country"] = $helper->convertCountry($_POST['s_country']); //convert from iso2 to iso3
				$requestArray["arrival_zip"] = $_POST['s_postcode'];
			}

			if (!empty($requestArray['arrival_country']) && !empty($requestArray['arrival_zip'])) {
				//do request to get vat
				$request = new Lovat_Api_Requests('POST', $requestArray, $optionValue->access_token);
				$taxPrice = $request->do_request();

				if (!empty($taxPrice) && $taxPrice > 0) {
					$newTotalPrice = $taxPrice + $totalPrice;
					WC()->cart->set_subtotal_tax($taxPrice);
					WC()->cart->set_cart_contents_tax($taxPrice);
					WC()->cart->set_cart_contents_taxes(array('total' => $taxPrice));
					WC()->cart->set_total_tax($taxPrice);
					WC()->cart->set_total($newTotalPrice);
					WC()->cart->set_cart_contents_total($newTotalPrice);

					// Set custom label for Sales Tax if user is from the USA
					if ($requestArray['arrival_country'] === 'USA') {
						add_filter('woocommerce_countries_tax_or_vat', array($this, 'change_sales_tax_label'), 10, 3);
					}
				}
			}
		}
	}

	/**
	 * Change sales tax label for users from the USA
	 *
	 * @return string
	 */
	public function change_sales_tax_label()
	{
		return __('Sales Tax', 'woocommerce');
	}
}