<?php

class Lovat_Helper
{
	/**
	 * @return bool
	 */
	public function is_tax_calculation_enabled()
	{
		$option = json_decode(get_option('lovat_departure_country'));
		if ($option->calculate_tax == 'on') {
			return true;
		}

		return false;
	}

	/**
	 * @param int $userId
	 * @return array|false|mixed|object|void|null
	 */
	public function isset_token_by_user(int $userId)
	{
		//get data from cache
		$data = wp_cache_get(ISSET_TOKEN_BY_USER . $userId);
		if (!empty($data)) {
			return $data;
		}

		global $wpdb;
		$issetUserToken = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}lovat_api_keys WHERE user_id = {$userId}");

		wp_cache_set(ISSET_TOKEN_BY_USER . $userId, $issetUserToken, '', 0); //clear cache
		return $issetUserToken;
	}

	/**
	 * @return array|false|mixed|object|null
	 */
	public function generated_keys()
	{
		//get data from cache
		$data = wp_cache_get(LOVAT_GENERATED_KEYS);
		if (!empty($data)) {
			return $data;
		}

		global $wpdb;
		$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}lovat_api_keys");
		wp_cache_set(LOVAT_GENERATED_KEYS, $result, '', 0); //clear cache
		return $result;
	}

	/**
	 * @return false|mixed|null
	 */
	public function get_lovat_option_value()
	{
		$data = wp_cache_get(LOVAT_CACHE_OPTION_VALUE);
		if (!empty($data)) {
			return $data;
		}

		$option = json_decode(get_option('lovat_departure_country'));
		if (empty($option)) return null;

		wp_cache_set(LOVAT_CACHE_OPTION_VALUE, $option, '', 0);
		return $option;
	}

	/**
	 * @param $country
	 * @return mixed
	 */
	public function convertCountry($country)
	{
		$countries = json_decode(file_get_contents(LOVAT_API_PLUGIN_DIR . '/countries.json'), true);
		if (!empty($countries[$country])) {
			return $countries[$country];
		}

		return null;
	}
}