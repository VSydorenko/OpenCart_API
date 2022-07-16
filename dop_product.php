<?php

class ControllerApidopproduct extends Controller {

  public function customer_add() {
		
		$this->load->language('api/customer_add');

		$json = array();
    $vozvrat_json = array();
    
    $image_f=  file_get_contents('php://input');

		$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
			
		file_put_contents($nameZip, $image_f);
			

			
		$zipArc = zip_open($nameZip);
			
		if (is_resource($zipArc)) {
    
    
      $first_customer = true;
			$sql_customer = "insert into `".DB_PREFIX."customer` (`customer_id`,`customer_group_id`,`store_id`,`language_id`,`firstname`,`lastname`,`email`,`telephone`,`password`,`salt`,`status`,`date_added`) VALUES ";
            
      $sql = "Select max(`customer_id`) as `customer_id` from `".DB_PREFIX."customer`;";
			$result = $this->db->query( $sql );
			$customer_id_max = 0;
			foreach ($result->rows as $row) {				
				$customer_id_max = (int)$row['customer_id'];				
			}
    
    
      $language_id= $this->getDefaultLanguageId();
      
    while ($zip_entry = zip_read($zipArc)) {
			  	if (zip_entry_open($zipArc, $zip_entry, "r")) {
			  			
			$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			
			$options_array= json_decode($dump);
    	
      foreach ($options_array as $option) {
			
				$firstname= $option->{'firstname'};
        $lastname= $option->{'lastname'};
        $email= $option->{'email'};
        $telephone= $option->{'telephone'};
        $date_added= $option->{'date_added'};
        $customer_id= $option->{'customer_id'};
        $password= $option->{'password'};
        $customer_group_id= $option->{'customer_group_id'};
												

        $sql_customer .= ($first_customer) ? "" : ",";
				
        if ($customer_id == 0) {
        
        	$customer_id_max = $customer_id_max + 1;				
					$customer_id  = $customer_id_max ;

        }
        $sault = 'kuku';
        $md5 =md5('$password'.$sault);
        $md5sault = md5($sault);
        $sql_customer .= " ( $customer_id,$customer_group_id,0,$language_id,'$firstname','$lastname','$email','$telephone','$md5','$md5sault',1,'$date_added' ) ";
			
				$first_customer = false;
        
        $struct = array();        
        $struct['customer_id']= $customer_id;        
        $vozvrat_json[$option->{'ref'}]=$struct ;
        
                 
			}
  }
  }
      

      
      if (!$first_customer) {
      
        $sql_customer .=" ON DUPLICATE KEY UPDATE  ";

				$sql_customer .= "`customer_group_id`= VALUES(`customer_group_id`),";
				
				$sql_customer .= "`firstname`= VALUES(`firstname`),";
				
				$sql_customer .= "`lastname`= VALUES(`lastname`),";
        $sql_customer .= "`telephone`= VALUES(`telephone`),";

				$sql_customer .= "`email`= VALUES(`email`)";								
	
				$this->db->query($sql_customer);
			}
      		
      zip_close($zipArc);
			unlink($nameZip);
			$json['success'] = $vozvrat_json ;
		
    }else{
				$json['error']   = 'zip not archive' ;				
		}			
			
      
      $this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
  }
    
  public function delete_customers() {
		
		$this->load->language('api/delete_customers');

		$json = array();


		
			$sql = "TRUNCATE TABLE `".DB_PREFIX."customer`;";
			$this->db->query( $sql );
			//$sql = "TRUNCATE TABLE `".DB_PREFIX."customer_group`;";
			//$this->db->query( $sql );
			//$sql = "TRUNCATE TABLE `".DB_PREFIX."customer_group_description`;";
			//$this->db->query( $sql );


				
			$json['success'] = 'customers udaleni' ;
			
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		
		
	}
  
 
    
  protected function getDefaultLanguageId() {
		$code = $this->config->get('config_language');
		$sql = "SELECT language_id FROM `".DB_PREFIX."language` WHERE code = '$code'";
		$result = $this->db->query( $sql );
		$language_id = 1;
		if ($result->rows) {
			foreach ($result->rows as $row) {
				$language_id = $row['language_id'];
				break;
			}
		}
		return $language_id;
	}

}