<?php

class Lovat_Api_Requests
{
	protected $url = 'https://merchant.vatcompliance.co/api/1/tax_rate/';
	protected $method;
	protected $data;
	protected $key;

	/**
	 * Lovat_Api_Requests constructor.
	 * @param $method
	 * @param $data
	 * @param $key
	 */
	public function __construct($method, $data, $key)
	{
		$this->method = $method;
		$this->data = $data;
		$this->key = $key;
	}

	/**
	 * request to lovat api
	 */
	public function do_request()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url . $this->key);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-type: application/json',
		));

		if ($this->method == 'POST') {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->data));
		}

		$result = json_decode(curl_exec($ch));
		curl_close($ch);

		if (!empty($result->vat) || $result->vat == 0) {
			return $result->vat;
		}

		return null;
	}
}