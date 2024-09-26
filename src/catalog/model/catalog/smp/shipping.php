<?php

class ModelCatalogSmpShipping extends Model
{
    
    public function getOrderShippingDetails(int $order_id, string $shipping_method, string $shipping_code, string $shipping_address_1, string $shipping_city) : array
    {
      
        $shipping_details = array('department_ref' => '', 'city_ref' => '');

        if ($shipping_code == "novaposhta.department") {
            $this->getNovaPoshtaShippingData($shipping_details, $shipping_address_1, $shipping_city);
        }


        return $shipping_details;

    }

    private function getNovaPoshtaShippingData(array &$shipping_details, $shipping_address_1, $shipping_city) : void {
        
        $query_text = "SELECT Ref FROM `" . DB_PREFIX . "novaposhta_cities` WHERE Description LIKE '" . $shipping_city . "'";
        $query = $this->db->query($query_text);
        if ($query->num_rows > 0) {
            $shipping_details['city_ref'] = $query->row['Ref'];    
        }    

        $query_text = "SELECT Ref FROM `" . DB_PREFIX . "novaposhta_departments` WHERE Description LIKE '" . $shipping_address_1 . "'";
        if (!empty($shipping_details['city_ref'])) {            
            $query_text .= " AND CityRef = '" . $shipping_details['city_ref'] . "'";
        }

        $query = $this->db->query($query_text);
        if ($query->num_rows > 0) {
            $shipping_details['department_ref'] = $query->row['Ref'];
        }
    }
}

?>
