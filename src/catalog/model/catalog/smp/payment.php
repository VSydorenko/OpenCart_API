<?php

use Cardinity\Method\Payment\Get;

class ModelCatalogSmpPayment extends Model
{

    public function getOrderPaymentDetails($order_id, $payment_code, $payment_method)
	{
		if ($payment_code == 'wayforpay') {
			$payment_details = $this->getWayforpayPaymentData($order_id);
		} elseif ($payment_code == 'lqp') {
			$payment_details = $this->getLiqpayPaymentData($order_id);
		} else {
			$payment_details = []; // short form for array
		}
		
		return $payment_details;

	}

	protected function getWayforpayPaymentData(int $order_id) 
	{

		$data = [];
		return $data;

	}

	protected function getLiqpayPaymentData(int $order_id)
	{

		$data = [];
		return $data;

	}

}

?>