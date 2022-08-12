<?php
spl_autoload_register(function () {
	/* Include Controllers */
	include_once('includes/class-orders-controller.php');
	include_once('includes/class-lovat-api-requests.php');
	include_once('includes/class-lovat-tax-calculation.php');
	include_once('helper/helper.php');
});