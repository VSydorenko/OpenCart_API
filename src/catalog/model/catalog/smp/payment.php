<?php

use Cardinity\Method\Payment\Get;

class ModelCatalogSmpPayment extends Model
{

    public function getOrderPaymentDetails($order_id, $payment_code, $payment_method)
	{
		if ($payment_code == 'wayforpay') {
			$payment_details = $this->Wayforpay($order_id);
		} elseif ($payment_code == 'lqp') {
			$payment_details = $this->Liqpay($order_id);
		} else {
			$payment_details = []; // short form for array
		}
		return $payment_details;
	}

	protected function Wayforpay(int $order_id) 
	{
		$data = [];
		return $data;
	}

	protected function Liqpay(int $order_id)
	{
		$data = [];
		return $data;
	}

}

?>