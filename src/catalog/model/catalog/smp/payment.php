<?php

class ModelCatalogSmpPayment extends Model
{

    publick function GetOrderPaymentDetails($order_id)
	{
		/*
		$sql_payment_details = "SELECT * FROM `" . DB_PREFIX . "lqp_list` WHERE order_id = $order_id";
		$query = $this->db->query($sql_payment_details);
		$rows = $query->rows;

		if (count($rows) == 0) {
			return null;
		} else {
			return $rows;
		}
		*/

		$payment_details = []; // short form for array
		
		return $payment_details;

	}



}

?>