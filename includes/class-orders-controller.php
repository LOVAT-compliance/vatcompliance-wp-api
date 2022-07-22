<?php

class Orders_Controller extends WP_REST_Controller
{
	const LIMIT = 1000;

	protected $namespace = 'v1';

	protected $rest_base = 'orders';

	public function register_routes()
	{
		register_rest_route($this->namespace, $this->rest_base, array(
				'methods' => \WP_REST_Server::READABLE,
				'callback' => array($this, 'get_woocommerce_orders'),
				'permission_callback' => array($this, 'get_orders_permission_check'),
				'args' => $this->get_import_collection_params(),
			)
		);
	}

	public function get_import_collection_params()
	{
		$params = array();
		$params['from'] = array(
			'description' => __('date from which you want to start the orders.', 'lovat'),
			'required' => true,
			'type' => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['to'] = array(
			'description' => __('the date until which you want to receive orders.', 'lovat'),
			'required' => true,
			'type' => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['p'] = array(
			'description' => __('offset for pagination.', 'lovat'),
			'required' => true,
			'type' => 'integer',
			'default' => 1,
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

	public function get_woocommerce_orders($data)
	{
		$dateFrom = $data['from'];
		$dateTo = $data['to'];
		$p = $data['p'];

		$dateValidationResult = $this->validationApiGetDataFromTo($dateFrom, $dateTo);
		if ($dateValidationResult === false) {
			return new WP_Error('bad_request',
				__("Problem with data, please complete required parameters such as 'from' and 'to' or make sure the date format is correct", 'rest-tutorial'),
				array(
					'status' => 400,
				)
			);
		} else {
			$offset = $data['p'] > 1 ? ($data['p'] - 1) * self::LIMIT : 0;

			$arrayOrdersArguments = array(
				'status' => ['completed', 'refunded'],
				'date_modified' => $dateValidationResult['from'] . '...' . $dateValidationResult['to'],
				'limit' => self::LIMIT,
				'offset' => $offset,
			);

			$orders = wc_get_orders($arrayOrdersArguments);

			if (!empty($orders)) {
				$helper = new Lovat_Helper();
				$lovatData = json_decode($helper->get_lovat_option_value());
				$orderClass = get_class(new Automattic\WooCommerce\Admin\Overrides\Order());
				$lovatDataArray = array();

				foreach ($orders as $data) {
					if (get_class($data) != $orderClass) continue;

					switch ($data->status) {
						case 'completed':
							$dateCompletedGmt = $data->get_date_completed();
							break;
						case 'refunded':
							$refunds = $data->get_refunds();
							$dateCompletedGmt = $refunds[0]->get_date_created()->format('Y-m-d G:i:s');
					}

					$lovatDataArray[$data->get_id()] = $data->data;
					$lovatDataArray[$data->get_id()]['departure_address'] = $lovatData->country;
					$lovatDataArray[$data->get_id()]['departure_zip'] = $lovatData->departureZip;
					$lovatDataArray[$data->get_id()]['date_completed_gmt'] = $dateCompletedGmt;

					if (!empty($data->get_taxes())) {
						foreach ($data->get_taxes() as $key => $taxes) {
							$lovatDataArray[$data->get_id()]['rate_code'] = $taxes->get_rate_code();
							$lovatDataArray[$data->get_id()]['rate_id'] = $taxes->get_rate_id();
							$lovatDataArray[$data->get_id()]['label'] = $taxes->get_label();
						}
					}

				}

				$remainingData = $this->remainingAmount($p);

				if (!empty($lovatDataArray)) {
					return array(
						'remaining_data' => $remainingData,
						'orders_data' => $lovatDataArray
					);
				}
			}

			return new WP_Error('HTTP_NOT_FOUND',
				__("Could not find data for your request", 'rest-tutorial'),
				array(
					'status' => 200,
				)
			);
		}
	}

	public function get_orders_permission_check()
	{
		$result = apply_filters('lovat_api_check_authentication', null);
		return $result;
	}

	public function validationApiGetDataFromTo($from, $to)
	{
		if ($this->is_Date($from) != false and $this->is_Date($to) != false) {
			$from = new \DateTime($from);
			$to = new \DateTime($to);
			return [
				'from' => $from->format('Y-m-d h:i:s'),
				'to' => $to->format('Y-m-d h:i:s')
			];
		}
		return false;
	}

	public function is_Date($str)
	{
		return strtotime($str);
	}

	public function remainingAmount($p)
	{
		$count = wc_orders_count('completed') + wc_orders_count('refunded');
		$remainingData = $count - ($p * self::LIMIT);

		if ($remainingData < 0) {
			$remainingData = 0;
		}

		$returnArray = [
			'remaining_data' => $remainingData,
			'count' => $count,
			'limit' => self::LIMIT,
			'offset' => $p
		];

		return $returnArray;
	}
}