<?php
class ControllerApiattributeproduct extends Controller {

	public function attribute_group() {
		
		$this->load->language('api/attribute_group');

		$json = array();


		
			$vozvrat_json = array();
			
			$image_f=  file_get_contents('php://input');

			$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
			
			file_put_contents($nameZip, $image_f);
			

			
			$zipArc = zip_open($nameZip);
			
			if (is_resource($zipArc)) {
			
			$languages = $this->getLanguages();
	
			$first_option_value = true;
			$sql_option_value = "INSERT INTO `".DB_PREFIX."attribute_group` (`attribute_group_id`, `sort_order`) VALUES ";
			
			$first_option_value_description = true;			
			$sql_option_value_description = "INSERT INTO `".DB_PREFIX."attribute_group_description` (`attribute_group_id`, `language_id`, `name`) VALUES ";
	
			$sql = "Select max(`attribute_group_id`) as `attribute_group_id` from `".DB_PREFIX."attribute_group`;";
			$result = $this->db->query( $sql );
			
			$attribute_group_id_max = 0;
			foreach ($result->rows as $row) {				
				$attribute_group_id_max  = (int)$row['attribute_group_id'];				
			}
			  	
			  while ($zip_entry = zip_read($zipArc)) {
			  	if (zip_entry_open($zipArc, $zip_entry, "r")) {
			  			
			  			$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			
			$options_array= json_decode($dump);
			
			
			
			
			

			
			foreach ($options_array as $option) {
			
				$name= urldecode($this->db->escape($option->{'name'}));
				$attribute_group_id= $option->{'attribute_group_id'};
				

				$sort_order = $option->{'sort_order'};
				$language_id_u= $option->{'language_id'};
			
				if ($attribute_group_id== 0) {
				
					$attribute_group_id_max = $attribute_group_id_max + 1;				
					$attribute_group_id= $attribute_group_id_max ;
					$insert = 1;		
															
				}else {														
					$insert = 0;
					
					//$sql_first_delete_path .= ($first_delete_path ) ? "" : ",";
					//$sql_second_delete_path .= ($first_delete_path ) ? "" : ",";
					
					//$sql_first_delete_path .= " $category_id ";
					//$sql_second_delete_path .= " $category_id ";	
					//$first_delete_path = false;
																						
				};
			
				$sql_option_value .= ($first_option_value) ? "" : ",";
				$sql_option_value .= " ( $attribute_group_id, $sort_order ) ";
			
				$first_option_value = false;
				
				foreach ($languages as $language) {
				
					$language_code = $language['code'];
					$language_id = $language['language_id'];
									
					if ($language_code == $language_id_u) {
					
						$sql_option_value_description .= ($first_option_value_description) ? "" : ",";
						$sql_option_value_description .= " ( $attribute_group_id, $language_id, '$name') ";
			
						$first_option_value_description = false;
					}
				}
			
				//$sql_category .= ($first_category ) ? "" : ",";
				//$sql_category .= " ( $category_id, $parent_id ) ";
				//$first_category = false;
				
				$vozvrat_json[$option->{'ref'}]= $attribute_group_id; 
			}
	}}
	
			if (!$first_option_value) {								
			
				$sql_option_value .=" ON DUPLICATE KEY UPDATE  ";
				$sql_option_value .= "`sort_order`= VALUES(`sort_order`)";
												
				$sql_option_value .=";";
				$this->db->query($sql_option_value);
			}
				
			if (!$first_option_value_description ) {								
			
				$sql_option_value_description .=" ON DUPLICATE KEY UPDATE  ";
				$sql_option_value_description .= "`name`= VALUES(`name`)";
												
				$sql_option_value_description .=";";
				$this->db->query($sql_option_value_description );
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
	
	public function deleteatributes() {
		
		$this->load->language('api/deleteatributes');

		$json = array();


		
			$sql = "TRUNCATE TABLE `".DB_PREFIX."attribute`;";
			$this->db->query( $sql );
			$sql = "TRUNCATE TABLE `".DB_PREFIX."attribute_description`;";
			$this->db->query( $sql );
			
			$sql = "TRUNCATE TABLE `".DB_PREFIX."attribute_group`;";
			$this->db->query( $sql );
			
			$sql = "TRUNCATE TABLE `".DB_PREFIX."attribute_group_description`;";
			$this->db->query( $sql );

			$sql = "TRUNCATE TABLE `".DB_PREFIX."product_attribute`;";
			$this->db->query( $sql );
			
			
				
			$json['success'] = 'attribute udaleni' ;
			
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		
		
	}
	public function attribute_add() {
		
		$this->load->language('api/attribute_add');

		$json = array();


		
			$vozvrat_json = array();
			
			
			$image_f=  file_get_contents('php://input');

			$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
			
			file_put_contents($nameZip, $image_f);
			

			
			$zipArc = zip_open($nameZip);
			
			if (is_resource($zipArc)) {
			
			$languages = $this->getLanguages();
	
			$first_option_value = true;
			$sql_option_value = "INSERT INTO `".DB_PREFIX."attribute` (`attribute_id`, `attribute_group_id`,`sort_order`) VALUES ";
			
			$first_option_value_description = true;			
			$sql_option_value_description = "INSERT INTO `".DB_PREFIX."attribute_description` (`attribute_id`, `language_id`, `name`) VALUES ";
	
			$sql = "Select max(`attribute_id`) as `attribute_id` from `".DB_PREFIX."attribute`;";
			$result = $this->db->query( $sql );
			
			$attribute_id_max = 0;
			foreach ($result->rows as $row) {				
				$attribute_id_max  = (int)$row['attribute_id'];				
			}
			  	
			  	while ($zip_entry = zip_read($zipArc)) {
			  		if (zip_entry_open($zipArc, $zip_entry, "r")) {
			  			
			  			$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			
			$options_array= json_decode($dump);
			
			
			
			
			

			
			foreach ($options_array as $option) {
			
				$name= urldecode($this->db->escape($option->{'name'}));
				$attribute_id= $option->{'attribute_id'};
				$attribute_group_id= $option->{'attribute_group_id'};
				

				$sort_order = $option->{'sort_order'};
				$language_id_u= $option->{'language_id'};
			
				if ($attribute_id== 0) {
				
					$attribute_id_max  = $attribute_id_max  + 1;				
					$attribute_id = $attribute_id_max  ;
					$insert = 1;		
															
				}else {														
					$insert = 0;
					
					//$sql_first_delete_path .= ($first_delete_path ) ? "" : ",";
					//$sql_second_delete_path .= ($first_delete_path ) ? "" : ",";
					
					//$sql_first_delete_path .= " $category_id ";
					//$sql_second_delete_path .= " $category_id ";	
					//$first_delete_path = false;
																						
				};
			
				$sql_option_value .= ($first_option_value) ? "" : ",";
				$sql_option_value .= " ( $attribute_id ,$attribute_group_id, $sort_order ) ";
			
				$first_option_value = false;
				
				foreach ($languages as $language) {
				
					$language_code = $language['code'];
					$language_id = $language['language_id'];
									
					if ($language_code == $language_id_u) {
					
						$sql_option_value_description .= ($first_option_value_description) ? "" : ",";
						$sql_option_value_description .= " ( $attribute_id, $language_id, '$name') ";
			
						$first_option_value_description = false;
					}
				}
			
				//$sql_category .= ($first_category ) ? "" : ",";
				//$sql_category .= " ( $category_id, $parent_id ) ";
				//$first_category = false;
				
				$vozvrat_json[$option->{'name'}]= $attribute_id ; 
			}
	}}
	
			if (!$first_option_value) {								
			
				$sql_option_value .=" ON DUPLICATE KEY UPDATE  ";
				$sql_option_value .= "`attribute_group_id`= VALUES(`attribute_group_id`),";
				$sql_option_value .= "`sort_order`= VALUES(`sort_order`)";
												
				$sql_option_value .=";";
				$this->db->query($sql_option_value);
			}
				
			if (!$first_option_value_description ) {								
			
				$sql_option_value_description .=" ON DUPLICATE KEY UPDATE  ";
				$sql_option_value_description .= "`name`= VALUES(`name`)";
												
				$sql_option_value_description .=";";
				$this->db->query($sql_option_value_description );
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
	
	protected function getLanguages() {
		$query = $this->db->query( "SELECT * FROM `".DB_PREFIX."language` WHERE `status`=1 ORDER BY `code`" );
		return $query->rows;
	}
}	