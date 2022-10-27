<?php
class Controllerapismpextension extends Controller {

	protected $use_table_seo_url = true ;
	
	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->use_table_seo_url = version_compare(VERSION,'3.0','>=') ? true : true ;
	}

	public function smp_GetProducts() {
		
		$query = ("SELECT
		p.product_id,
		p.model AS product_code,
		p.sku,
		p.image AS main_image,
		p.status,
		pd.name,
		pd.description,
		pd.tag,
		pd.meta_title,
		pd.meta_description,
		pd.meta_keyword,
		pd.meta_h1,
		p2c.category_id,
		l.code AS language_code
		FROM " . DB_PREFIX . "product p
		LEFT JOIN " . DB_PREFIX . "product_description pd 
		ON (p.product_id = pd.product_id)
		LEFT JOIN " . DB_PREFIX . "product_to_category p2c
		ON (p.product_id = p2c.product_id AND p2c.main_category = '1') 
		LEFT JOIN " . DB_PREFIX . "language l 
		ON (pd.language_id = l.language_id) 
		WHERE l.status = '1'
		ORDER BY p.product_id");

		$query = $this->db->query($query);
		foreach ($query->rows as $result) {
			if ($result['main_image']) {
				$image = $this->smp_GetImageAddress($result['main_image']);
			} else {
				$image = '';
			}

			$data[] = array(
                'product_id' => $result['product_id'],
                'product_code' => $result['product_code'],
				'sku' => $result['sku'],
				'image' => $image,
				'status' => $result['status'],
				'name' => $result['name'],
				'description' => $result['description'],
				//'description2' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0),
				'description' => utf8_substr(trim(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0),
				'tag' => $result['tag'],
				'meta_title' => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword' => $result['meta_keyword'],
				'meta_h1' => $result['meta_h1'],
				'category_id' => $result['category_id'],
				'language_code' => $result['language_code'],
				'image_path' => $result['main_image'],
			);
			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	public function smp_GetCategories() {

		$query = $this->db->query("SELECT 
		c.category_id,
		c.parent_id,
		cd.name,
		cd.description,
		cd.meta_title,
		cd.meta_description,
		cd.meta_keyword,
		cd.meta_h1,
		l.code AS language_code
		FROM " . DB_PREFIX . "category c
		LEFT JOIN " . DB_PREFIX . "category_description cd 
		ON (c.category_id = cd.category_id) 
		LEFT JOIN " . DB_PREFIX . "category_to_store c2s
		ON (c.category_id = c2s.category_id)
		LEFT JOIN " . DB_PREFIX . "language l
		ON (cd.language_id = l.language_id) 
		WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  
		ORDER BY c.sort_order, c.parent_id");

		foreach ($query->rows as $result) {
			
			$data[] = array(
				'category_id' => $result['category_id'],
				'parent_id' => $result['parent_id'],
				'name' => $result['name'],
				'description'=> utf8_substr(trim(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0),
				'meta_title' => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword' => $result['meta_keyword'],
				'meta_h1' => $result['meta_h1'],
				'language_code' => $result['language_code'],
			);

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));

	}

	public function smp_GetAdditionalProductImages() {

		$query = $this->db->query("SELECT
		pi.product_id, 
		pi.image,
		pi.product_image_id 
		FROM " . DB_PREFIX . "product_image pi 
		LEFT JOIN " . DB_PREFIX . "product p 
		ON (pi.product_id = p.product_id) 
		ORDER BY p.product_id,  p.sort_order ASC, pi.sort_order ASC");
		$results = $query->rows;
		
		foreach ($query->rows as $result) {
			
			$data[] = array(
				'product_id' => $result['product_id'],
				'image' => $this->smp_GetImageAddress($result['image']),
				'product_image_id' => $result['product_image_id'],
				'image_path' => $result['image'],
			);

		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));

	}

	public function smp_GetSomething() {

		//$query = $this->db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = DATABASE()");
		
		$table_name = $this->request->get['tn'];
		//$query = $this->db->query("SELECT * FROM " . $table_name ."");
		//$query = $this->db->query("SHOW COLUMNS FROM ". $table_name . "");
		//$query = $this->db->query("SELECT * FROM oc_category_path ORDER BY category_id DESC");
		
		
		$return_array = array();
		
		$query = $this->db->query("SELECT * FROM oc_ocfilter_option ORDER BY sort_order DESC");
		$result = $query->rows;
		$return_array['ocfilter_option'] = $result;

		$query = $this->db->query("SHOW COLUMNS FROM oc_ocfilter_option");
		$result = $query->rows;
		$return_array['col_ocfilter_option'] = $result;

		/*
		$query = $this->db->query("SELECT * FROM oc_product_option_value WHERE product_id = 731");
		$result = $query->rows;
		$return_array['product_option_value'] = $result;

		$query = $this->db->query("SHOW COLUMNS FROM oc_product_option_value");
		$result = $query->rows;
		$return_array['col_product_option_value'] = $result;
		*/
		//$tmp_seo = $this->getCategorysSEOKeywords();

		//$result = $this->getProductOption();
		
		//$query = $this->db->query("SELECT image FROM oc_product WHERE product_id = 730");
		//$result = $query->rows;
		//$return_array['main_image'] = $result;
		//$query = $this->db->query("SELECT * FROM oc_product_image WHERE product_id IN (730, 731)");
		//$result = $query->rows;
		

		//$query = $this->db->query("SELECT * FROM oc_category");
		//$query = $this->db->query("SHOW COLUMNS FROM oc_product_image");
		//$result = $query->rows;		

		$this->response->addHeader('Content-Type: application/json');
		//$this->response->setOutput(json_encode($result));
		$this->response->setOutput(json_encode($return_array));
		
	}

	public function smp_GetImageAddress($filename) {

        if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$image_old = $filename;
        list($width, $height, $image_type) = getimagesize(DIR_IMAGE . $image_old);
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
				 
			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) { 
				return DIR_IMAGE . $image_old;
			}
						
			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new Image(DIR_IMAGE . $image_old);
				$image->resize($width, $height);
				$image->save(DIR_IMAGE . $image_new);
			} else {
				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
			}
		}
		
		$image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
		
		if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $image_new;
		} else {
			return $this->config->get('config_url') . 'image/' . $image_new;
		}

    }

	public function proverka() {
		
		$this->load->language('api/proverka');

		$json = array();


			$json['success'] = "Token correct" ;
			
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		
		
	}

	public function otkl_tovar() {	
		$this->load->language('api/otkl_tovar');

		$json = array();

      $query = $this->db->query( "UPDATE `".DB_PREFIX."product` set status = 0" );
      
			$json['success'] = "otkl_tovar" ;
			
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		
	}
    
	public function clear_casche() {
  
  		$this->load->language('api/proverka');
		$json = array();

    	$files = glob(DIR_CACHE.'*'); // get all file names
    	foreach($files as $file){ // iterate files
      		if(is_file($file))
        	unlink($file); // delete file
    	}

		$json['success'] = "clear casche" ;
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
  
	}

	public function get_param() {
		
		$this->load->language('api/get_param');

		$json = array();
		$json['success'] = ini_get('upload_max_filesize') ;
		//$json['memory_limit'] = ini_get("memory_limit") ;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	public function get_group_customers() {
		
		$this->load->language('api/get_group_customers');

		$json = array();

		$query = $this->db->query( "SELECT customer_group_id, name FROM `".DB_PREFIX."customer_group_description`" );
		$json['success'] = $query->rows ;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	public function get_status_order() {
		
		$this->load->language('api/get_status_order');

		$json = array();

		$query = $this->db->query( "SELECT * FROM `".DB_PREFIX."stock_status`" );
		$json['success'] = $query->rows ;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}

	/* Получение списка статусов для заказов покупателей
	*/
	public function get_order_statuses() {
		
		$this->load->language('api/get_status');

		$lang_code = $this->request->get['lang'];
		$lang_id = 1;
		
		if (!is_null($lang_code)) {
			$lang_query = $this->db->query("SELECT language_id FROM `" .DB_PREFIX."language` WHERE code = '" . $lang_code . "'");
			foreach ($lang_query->rows as $l_row) {
				$lang_id = $l_row['language_id'];
			}
		}

		$json = array();
		$query = $this->db->query( "SELECT order_status_id, name FROM `".DB_PREFIX."order_status` WHERE language_id = $lang_id ORDER BY order_status_id ASC");
		$json['success'] = $query->rows ;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
  
	public function images_del() {
		
		$this->load->language('api/images_del');

		$json = array();
		$vozvrat_json = array();
		$image_f=  file_get_contents('php://input');
		$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
		file_put_contents($nameZip, $image_f);
		$zipArc = zip_open($nameZip);
			
		if (is_resource($zipArc)) {
						
			$first_product_image = true;
			$sql_product_image = "DELETE FROM `".DB_PREFIX."product_image` WHERE product_image_id in (";
									  	
			while ($zip_entry = zip_read($zipArc)) {
			  	if (zip_entry_open($zipArc, $zip_entry, "r")) {
			  			
					$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
					
					$options_array= json_decode($dump);
			
					foreach ($options_array as $product_image_id) {
									
						$sql_product_image .= ($first_product_image ) ? "" : ",";
						$sql_product_image .= " $product_image_id ";
						$first_product_image = false;
					}		
				}
			}	 
			
			if (!$first_product_image ) {								
												
				$sql_product_image .=");";
				$this->db->query($sql_product_image );
			}
			
			zip_close($zipArc);
			unlink($nameZip);
			$json['success'] = 'udal images del';
		
		} else {
			$json['error']   = 'zip not archive' ;				
		}	
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
			
	}
  
	public function images_add() {
		
		$this->load->language('api/images_add');

		$json = array();
		$vozvrat_json = array();
		$image_f=  file_get_contents('php://input');
		$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
		file_put_contents($nameZip, $image_f);
			
		$zipArc = zip_open($nameZip);
			
		if (is_resource($zipArc)) {
			
			$language_id= $this->getDefaultLanguageId();
	
			$first_product_image = true;
			$sql_product_image = "INSERT INTO `".DB_PREFIX."product_image` (`product_image_id`, `product_id`,`image`) VALUES ";
			
			$first_download = true;			
			$sql_download = "INSERT INTO `".DB_PREFIX."download` (`download_id`, `filename`, `mask`, `date_added`) VALUES ";
			
			$first_download_description = true;			
			$sql_download_description = "INSERT INTO `".DB_PREFIX."download_description` (`download_id`, `language_id`, `name`) VALUES ";
			
			$first_product_to_download = true;			
			$sql_product_to_download = "INSERT INTO `".DB_PREFIX."product_to_download` (`product_id`, `download_id`) VALUES ";
	
	
			$first_delete_path = true;
			$sql_first_delete_path = "DELETE FROM `".DB_PREFIX."product_to_download` WHERE download_id IN (";
	
			$sql = "Select max(`product_image_id`) as `product_image_id` from `".DB_PREFIX."product_image`;";
			$result = $this->db->query( $sql );
			
			$product_image_id_max = 0;
			foreach ($result->rows as $row) {				
				$product_image_id_max = (int)$row['product_image_id'];				
			}
			
			$sql = "Select max(`download_id`) as `download_id` from `".DB_PREFIX."download`;";
			$result = $this->db->query( $sql );
			$download_id_max = 0;
			foreach ($result->rows as $row) {				
				$download_id_max = (int)$row['download_id'];				
			}
			  	
			while ($zip_entry = zip_read($zipArc)) {
			  	if (zip_entry_open($zipArc, $zip_entry, "r")) {
			  			
					$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
					$options_array= json_decode($dump);
			
					foreach ($options_array as $option) {
			
						$name= $option->{'name'};
						$product_image_id= $option->{'product_image_id'};
						$product_id= $option->{'product_id'};
						$product_to_image= $option->{'product_to_image'};
				
						$mask = urldecode($this->db->escape($option->{'mask'}));
						$image= $option->{'name'};
						$namef= urldecode($option->{'namef'});
						$download= $option->{'download'};
						$language_id_u= $option->{'language_id'};
			
			
						if ($download== 0) {
							if ($product_to_image== 1) {
								if ($product_image_id== 0) {
						
									$product_image_id_max = $product_image_id_max + 1;				
									$product_image_id= $product_image_id_max ;
									$insert = 1;		
																	
								} else {														
									$insert = 0;
							
									//$sql_first_delete_path .= ($first_delete_path ) ? "" : ",";
									//$sql_second_delete_path .= ($first_delete_path ) ? "" : ",";
							
									//$sql_first_delete_path .= " $category_id ";
									//$sql_second_delete_path .= " $category_id ";	
									//$first_delete_path = false;
																								
								};
					
								$sql_product_image .= ($first_product_image ) ? "" : ",";
								$sql_product_image .= " ( $product_image_id,$product_id, '$image') ";
								$first_product_image = false;
					
								$vozvrat_json[$option->{'ref'}]= $product_image_id;
							} else {
          
						 		$vozvrat_json[$option->{'ref'}]= -10;

							}

						} else {
							if ($product_image_id== 0) {
						
								$download_id_max = $download_id_max + 1;				
								$product_image_id = $download_id_max ;
								$insert = 1;		
																	
							} else {														
								$insert = 0;
							
								$sql_first_delete_path .= ($first_delete_path ) ? "" : ",";
								//$sql_second_delete_path .= ($first_delete_path ) ? "" : ",";
							
								$sql_first_delete_path .= " $product_image_id";
								//$sql_second_delete_path .= " $category_id ";	
								$first_delete_path = false;
							};

							$sql_download .= ($first_download ) ? "" : ",";
							$sql_download .= " ( $product_image_id,'$name', '$mask',NOW()) ";
							$first_download = false;
						
							$sql_download_description .= ($first_download_description ) ? "" : ",";
							$sql_download_description .= " ( $product_image_id,$language_id, '$namef') ";
							$first_download_description = false;
					
							$sql_product_to_download.= ($first_product_to_download ) ? "" : ",";
							$sql_product_to_download.= " ( $product_id,$product_image_id) ";
							$first_product_to_download = false;
					
							$vozvrat_json[$option->{'ref'}]= $product_image_id;
					
						}
					}
				}
			}
			
			if (!$first_delete_path ) {
				$sql_first_delete_path .=");";
				$this->db->query($sql_first_delete_path );
			}
			
			if (!$first_download ) {								
			
				$sql_download .=" ON DUPLICATE KEY UPDATE  ";
				$sql_download .= "`filename`= VALUES(`filename`),";
				$sql_download .= "`date_added`= NOW(),";
				$sql_download .= "`mask`= VALUES(`mask`)";
												
				$sql_download .=";";
				$this->db->query($sql_download );
				
				$sql_download_description .=" ON DUPLICATE KEY UPDATE  ";
				$sql_download_description .= "`language_id`= VALUES(`language_id`),";
				$sql_download_description .= "`name`= VALUES(`name`)";
												
				$sql_download_description .=";";
				$this->db->query($sql_download_description );
				
				
				//$sql_product_to_download.=" ON DUPLICATE KEY UPDATE  ";
				//$sql_product_to_download.= "`download_id `= VALUES(`download_id `)";
												
				$sql_product_to_download.=";";
				$this->db->query($sql_product_to_download);
			}

			if (!$first_product_image ) {								
			
				$sql_product_image .=" ON DUPLICATE KEY UPDATE  ";
				$sql_product_image .= "`product_id`= VALUES(`product_id`),";
				$sql_product_image .= "`image`= VALUES(`image`)";
												
				$sql_product_image .=";";
				$this->db->query($sql_product_image );
			}
			
			zip_close($zipArc);
			unlink($nameZip);
			$json['success'] = $vozvrat_json ;
		
		} else {
			
			$json['error']   = 'zip not archive' ;

		}	
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}

	public function AddProductImagesDescriptions() {

		$this->response->addHeader('Content-Type: application/json');

		$return_array = array();
		$return_data = array();

		$image_f =  file_get_contents('php://input');
		$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
		file_put_contents($nameZip, $image_f);
		$zipArc = zip_open($nameZip);

		$dirname_array = array(); // храним признак создания подкаталогов для товаров

		if (!is_resource($zipArc)) {
			$return_array['error'] = 'problem with opening zip-file!';
			$this->response->setOutput(json_encode($return_array));
			return null;
		}

		while ($zip_entry = zip_read($zipArc)) {
			
			if (zip_entry_open($zipArc, $zip_entry, "r")) {
				
				$product_not_delete = array();
				$image_not_delete = array();

				$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
				$descriptions_array = json_decode(htmlspecialchars_decode($dump));

				foreach ($descriptions_array as $description) {
				
					$return_description = array();

					$ref = $description->{'ref'}; // УИД ссылки присоединенного файла 1С
					$path = $description->{'path'}; // Имя подкаталога для размещения изображений товара. На каждый товар свой каталог
					$main_image = $description->{'main_image'}; // Признак главного изображения, 1 или 0.
					$product_id = $description->{'product_id'}; // идентификатор товара
					$product_image_id = $description->{'product_image_id'}; // идентификатор изображения
					$image_name = $description->{'image_name'}; // наименование изображения из справочника присоединенных файлов
					$server_path = $description->{'server_path'}; // полный путь к файлу, пустая строка если новый 
					$extension = $description->{'extension'}; // расширение файла изображения "png","bmp"...

					$new_path = 'catalog/' . $path . '/' . $ref . '.' . $extension; // новый путь к файлу относительно папки "image" в корне папки сайта
					$full_path = DIR_IMAGE . 'catalog/' . $path . '/' . $ref . '.' . $extension; // Полный физ. путь для создания подкаталога

					if ($main_image == 1) {
						
						$current_path = $this->GetProductMainImagePath($product_id);
						
						if ($current_path == '' or $current_path == null) { // У товара отсутствет основное изображение
							
							$this->CreateProductImageDir($dirname_array, $full_path); // предварительно создаем папку для изображений товара
							$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($new_path) . "' WHERE product_id = '" . (int)$product_id . "'");
							
						} elseif ($current_path != $new_path) {

							$this->CreateProductImageDir($dirname_array, $new_path); // предварительно создаем папку для изображений товара
							$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($new_path) . "' WHERE product_id = '" . (int)$product_id . "'");
							
							if (file_exists(DIR_IMAGE . $current_path)) {
								unlink(DIR_IMAGE . $current_path); // удаляем старое главное изображение
							}	
						}

						$return_description['product_image_id'] = 0;
						$return_description['server_path'] = $new_path;
						$return_data[$ref] = $return_description;

					} else { // Ветка вторичных изображений
						
						if ($product_image_id != 0) { // это существующее изображение
							
							$return_description['product_image_id'] = $product_image_id;
							$return_description['server_path'] = $new_path;
							$return_data[$ref] = $return_description;

							$current_path = $this->GetAdditionalProductImagePath($product_id, $product_image_id);

							if ($current_path == $new_path) { // пути совпадают
								
								$product_not_delete[$product_id] = true;
								$image_not_delete[] = $product_image_id;
							
							} elseif ($current_path == '') { // в карточке товара изображение есть, но пустое, обновляем путь
								
								$this->CreateProductImageDir($dirname_array, $full_path); // предварительно создаем папку для изображений товара
								$this->db->query("UPDATE " . DB_PREFIX . "product_image SET image = '" . $this->db->escape($new_path) . "' WHERE product_image_id = '" . (int)$product_image_id . "' AND product_id = '" . (int)$product_id . "'");
								
								$product_not_delete[$product_id] = true;
								$image_not_delete[] = $product_image_id;

							} elseif (is_null($current_path)) { // на стороне 1С есть идентификаторы, а на стороне сайта удалили изображение
								#$logger->write("\n There are no record of additional image, product_id = " . $product_id . " product_image_id = " . $product_image_id . "\n\n");
							}


						} else { // это новое вторичное изображение
							
							//$this->CreateProductImageDir($dirname_array, $new_path); // предварительно создаем папку для изображений товара
							$product_image_id_max = $this->GetMaxProductimageId();
							$product_image_id_max++;
							$sql_product_image = "INSERT INTO `".DB_PREFIX."product_image` (`product_image_id`, `product_id`, `image`) VALUES (" . $product_image_id_max . ", " . $product_id . ", '" . $this->db->escape($new_path) . "')";
							$this->db->query($sql_product_image);

							$product_not_delete[$product_id] = true;
							$image_not_delete[] = $product_image_id_max;

							$return_description['product_image_id'] = $product_image_id_max;
							$return_description['server_path'] = $new_path;
							$return_data[$ref] = $return_description;

						}
					}
				}
			}
		}

		// удаляем все вторичные изображения 
		$first_product = true;
		$first_image = true;

		$sql_image_delete = "SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id IN(";
		
		foreach ($product_not_delete as $pd_id => $val) {
			
			$sql_image_delete .= ($first_product) ? "" : ", ";
			$sql_image_delete .= $pd_id;
			$first_product = false;

		}

		$sql_image_delete .= ") AND product_image_id NOT IN(";

		foreach ($image_not_delete as $img_id) {
			
			$sql_image_delete .= ($first_image) ? "" : ", ";
			$sql_image_delete .= $img_id;
			$first_image = false;

		}

		$sql_image_delete .= ")";

		if (!$first_product and !$first_image) {

			$query = $this->db->query($sql_image_delete);
		
			$rows = $query->rows;
			foreach ($rows as $row) {
			
				$delete_path = $row['image'];
				$delete_pd_id = $row['product_id'];
				$delete_img_id = $row['product_image_id'];

				if (file_exists(DIR_IMAGE . $delete_path)) {
					$this->db->query("DELETE FROM `".DB_PREFIX."product_image` WHERE product_image_id = " . $delete_img_id . " AND product_id = " . $delete_pd_id . "");
					unlink(DIR_IMAGE . $delete_path);
				}
			}
		}	
	
		zip_close($zipArc);
		unlink($nameZip);
		$return_array['success'] = $return_data;

		$this->response->setOutput(json_encode($return_array));

	}

	protected function GetAdditionalProductImagePath($product_id, $product_image_id) {

		$image_path = '';

		$query = $this->db->query("SELECT image FROM " . DB_PREFIX . "product_image WHERE product_id = " . $product_id . " AND product_image_id =" . $product_image_id . "");
		
		if ($query->num_rows > 0) {
			foreach ($query->rows as $row) {
				$image_path = $row['image'];	
			}
			return $image_path;

		} else {
			return null;
		}
	}

	protected function GetMaxProductimageId() {

		$sql = "Select max(`product_image_id`) as `product_image_id` from `".DB_PREFIX."product_image`;";
		$result = $this->db->query( $sql );
		$product_image_id_max = 0;
				
		foreach ($result->rows as $row) {				
			$product_image_id_max = (int)$row['product_image_id'];				
		}

		return $product_image_id_max;

	}

	protected function GetProductMainImagePath($product_id) {
			
		$query = $this->db->query("SELECT image FROM " . DB_PREFIX . "product WHERE product_id = " . $product_id . "");
		
		foreach ($query->rows as $img) {
			$img_path = $img['image'];
		}

		return $img_path;

	}

	protected function CreateProductImageDir(&$product_directories, &$path_to_image) {

		$product_dir = dirname($path_to_image);
              
        if (!isset($product_directories[$product_dir])) {
              
			if(!file_exists($product_dir)){
                mkdir($product_dir, 0777, true);
            }

        	$product_directories[$product_dir] = true;
		}
	}

	public function AddProductImagesFiles() {

		$json = array();
		$image_f = file_get_contents('php://input');
		$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
		file_put_contents($nameZip, $image_f);
		$zipArc = zip_open($nameZip);
		
		if (is_resource($zipArc)) {
			
			while ($zip_entry = zip_read($zipArc)) {
			  		
				if (zip_entry_open($zipArc, $zip_entry, "r")) {
			  			
			  		$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			  		$image_data = json_decode(htmlspecialchars_decode($dump));
				 				
					$ref= $image_data->{'ref'};
					$extension = $image_data->{'extension'};
					$path = $image_data->{'path'};
					$image_f = base64_decode($image_data->{'image_data'});
					$namef_with_dir = DIR_IMAGE . 'catalog/' . $path . '/' . $ref . '.' . $extension;            									
					$dirname_namef = dirname($namef_with_dir);
              
                	if(!file_exists($dirname_namef)){
                  		mkdir($dirname_namef, 0777, true);
                	}
						
					file_put_contents($namef_with_dir, $image_f);

			  	}
			}
			  	
			zip_close($zipArc);
			unlink($nameZip);
			$json['success'] = 'images upload' ;
		} else {
			$json['error']   = 'zip not archive' ;				
		}
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	

	}

	public function AddUpdateOcFilter() {

		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();

		$this->response->addHeader('Content-Type: application/json');

		$return_array = array();
		$return_data = array();
		$ocfilters_f = file_get_contents('php://input');
		$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
		file_put_contents($nameZip, $ocfilters_f);
		$zipArc = zip_open($nameZip);

		if (!is_resource($zipArc)) {
			$return_array['error'] = 'problem with opening zip-file!';
			$this->response->setOutput(json_encode($return_array));
			return null;
		}

		while ($zip_entry = zip_read($zipArc)) {
			
			if (zip_entry_open($zipArc, $zip_entry, "r")) {
				
				$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
				$ocfilters_array = json_decode(htmlspecialchars_decode($dump));	

				foreach ($ocfilters_array as $ocfilter) {
					
					$ocfilter_return = array(); // Для возвращаемых данных значений фильтра;

					$ref_1c = $ocfilter->{'ref_1c'};
					$option_id = $ocfilter->{'option_id'};
					$type = $ocfilter->{'type'};
					$keyword = urldecode($ocfilter->{'keyword'});
					$selectbox = $ocfilter->{'selectbox'};
					$grouping = $ocfilter->{'grouping'};
					$color = $ocfilter->{'color'};
					$image = $ocfilter->{'image'};
					$status = $ocfilter->{'status'};
					$sort_order = $ocfilter->{'sort_order'};

					$description = $this->object_to_array($ocfilter->{'description'});
					$categories = $this->object_to_array($ocfilter->{'categories'});
					$stores = $this->object_to_array($ocfilter->{'stores'});
					$values = $this->object_to_array($ocfilter->{'values'});

					if ($option_id == 0) {
						
						$max_ocfilter_id = $this->GetMaxOcFilterId();
						$max_ocfilter_id++;
						$option_id = $max_ocfilter_id;

					}

					/*SET status = '" . (isset($data['status']) ? (int)$data['status'] : 0) . "', 
					sort_order = '" . (int)$data['sort_order'] . "', 
					type = '" . $this->db->escape($data['type']) . "', 
					selectbox = '" . (isset($data['selectbox']) ? (int)$data['selectbox'] : 0) . "', 
					color = '" . (isset($data['color']) ? (int)$data['color'] : 0) . "', 
					image = '" . (isset($data['image']) ? (int)$data['image'] : 0) . "'";*/

					$sql_ocfilter_option = "INSERT INTO `" . DB_PREFIX . "ocfilter_option` ( 
						`option_id`, 
						`type`, 
						`keyword`, 
						`selectbox`, 
						`grouping`, 
						`color`, 
						`image`, 
						`status`, 
						`sort_order`) VALUES (";
					$sql_ocfilter_option .= "$option_id, '$type', '$keyword', $selectbox, $grouping, $color, $image, $status, $sort_order)";
					$sql_ocfilter_option .= " ON DUPLICATE KEY UPDATE `status`= VALUES(`status`)";
					
					$this->db->query($sql_ocfilter_option);
					
					// Загрузка описаний
					$sql_ocfilter_option_description = "INSERT INTO `" . DB_PREFIX . "ocfilter_option_description` ( 
						`option_id`, 
						`language_id`, 
						`name`, 
						`postfix`, 
						`description`) VALUES ";
					
					$first_description = true;

					foreach ($description as $lang_desc) {
						
						$language_id = (int)$languages[$lang_desc['language_code']]['language_id'];
						$name = urldecode($this->db->escape($lang_desc['name']));
						$postfix = urldecode($this->db->escape($lang_desc['postfix']));
						$desc = urldecode($this->db->escape($lang_desc['description']));

						$sql_ocfilter_option_description .= ($first_description) ? "" : " , ";
						$sql_ocfilter_option_description .= "($option_id, $language_id, '$name', '$postfix', '$desc')";

						$first_description = false;

					}

					$sql_ocfilter_option_description .= " ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `postfix` = VALUES(`postfix`), `description` = VALUES(`description`)";

					if (!$first_description) {
						$this->db->query($sql_ocfilter_option_description);
					}

					// Привязка категорий
					$this->db->query("DELETE FROM " . DB_PREFIX . "ocfilter_option_to_category WHERE option_id = '" . (int)$option_id . "'");
					foreach ($categories as $category_id) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "ocfilter_option_to_category` SET option_id = '" . (int)$option_id . "', category_id = '" . (int)$category_id . "'");
					}

					// Привязка магазина
					foreach ($stores as $store_id) {
						$store_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ocfilter_option_to_store` WHERE option_id = $option_id AND store_id = $store_id");
						if ($store_query->num_rows == 0) {
							$this->db->query("INSERT INTO `" . DB_PREFIX . "ocfilter_option_to_store` SET option_id = '" . (int)$option_id . "', store_id = '" . (int)$store_id . "'");
						}
					}

					$ocfilter_return['option_id'] = $option_id;
					$ocfilter_return['values'] = array();

					// Загрузка значений фильтра
					foreach ($values as $ocfilter_value) {
						
						$value_ref_1c = $ocfilter_value['ref_1c'];
						$value_id = $ocfilter_value['value_id'];
						$value_keyword = $ocfilter_value['keyword']; // Уточнить, что это
						$value_color = $ocfilter_value['color']; // Если эначения фильтра определенные цвета - код цвета
						$value_image = $ocfilter_value['image']; // Путь к файлу картинки значения фильтра
						$value_sort_order = $ocfilter_value['sort_order'];
						$value_description = $ocfilter_value['description'];

						if ($value_id == 0) {
							$max_ocfilter_value_id = $this->GetMaxOcFilterValueId();
							$max_ocfilter_value_id++;
							$value_id = $max_ocfilter_value_id;
						}

						$sql_ocfilter_option_value = "INSERT INTO `" . DB_PREFIX . "ocfilter_option_value` ( 
							`value_id`, 
							`option_id`, 
							`keyword`, 
							`color`, 
							`image`, 
							`sort_order`) VALUES ";
						$sql_ocfilter_option_value .= "($value_id, $option_id, '$value_keyword', '$value_color', '$value_image', $value_sort_order)";
						$sql_ocfilter_option_value .= " ON DUPLICATE KEY UPDATE `sort_order` = VALUES(`sort_order`)";

						$this->db->query($sql_ocfilter_option_value);

						$ocfilter_return['values'][$value_ref_1c] = $value_id;
						
						//Загрузка переводов для значений фильтра
						$sql_ocfilter_option_value_description = "INSERT INTO `" . DB_PREFIX . "ocfilter_option_value_description` ( 
							`value_id`, 
							`option_id`, 
							`language_id`, 
							`name`) VALUES ";
						
						$first_value_description = true;

						foreach ($value_description as $val_desc) {
							
							$language_id = (int)$languages[$val_desc['language_code']]['language_id'];
							$value_name = urldecode($this->db->escape($val_desc['name']));

							$sql_ocfilter_option_value_description .= ($first_value_description) ? "" : " , ";
							$sql_ocfilter_option_value_description .= "($value_id, $option_id, $language_id, '$value_name')";

							$first_value_description = false;

						}

						$sql_ocfilter_option_value_description .= " ON DUPLICATE KEY UPDATE `name` = VALUES(`name`)";

						if (!$first_value_description) {							
							$this->db->query($sql_ocfilter_option_value_description);
						}

					}

					$return_data[$ref_1c] = $ocfilter_return;

				}
			}
		}

		zip_close($zipArc);
		unlink($nameZip);
		$return_array['success'] = $return_data;
		$this->response->setOutput(json_encode($return_array));

	}

	protected function GetMaxOcFilterId()
	{
		
		$max_ocfilter_id = 0;
		$query = $this->db->query("Select max(`option_id`) as `option_id` from `".DB_PREFIX."ocfilter_option`;");
		foreach ($query->rows as $row) {
			$max_ocfilter_id = (int)$row['option_id'];
		}

		return $max_ocfilter_id;

	}

	protected function GetMaxOcFilterValueId()
	{
		$max_ocfilter_value_id = 0;
		$query = $this->db->query("Select max(`value_id`) as `value_id` from `".DB_PREFIX."ocfilter_option_value`;");
		foreach ($query->rows as $row) {
			$max_ocfilter_value_id = (int)$row['value_id'];
		}

		return $max_ocfilter_value_id;

	}

	public function AddUpdateOcFilterToProduct()
	{
		
		$this->response->addHeader('Content-Type: application/json');
		
		$return_array = array();
		$ocfilters_f = file_get_contents('php://input');
		$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
		file_put_contents($nameZip, $ocfilters_f);
		$zipArc = zip_open($nameZip);

		if (!is_resource($zipArc)) {
			$return_array['error'] = 'problem with opening zip-file!';
			$this->response->setOutput(json_encode($return_array));
			return null;
		}

		while ($zip_entry = zip_read($zipArc)) {
			
			if (zip_entry_open($zipArc, $zip_entry, "r")) {
				
				$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
				$ocfilters_array = json_decode(htmlspecialchars_decode($dump));
				
				foreach ($ocfilters_array as $product_data) {
					
					$product_id = $product_data->{'product_id'};
					$filter_data = $this->object_to_array($product_data->{'filter_data'});

					$this->db->query("DELETE FROM " . DB_PREFIX . "ocfilter_option_value_to_product WHERE product_id = '" . (int)$product_id . "'");

					foreach ($filter_data as $ocfilter_value) {
						
						$option_id = $ocfilter_value['option_id'];
						$value_id = $ocfilter_value['value_id'];

						$this->db->query("INSERT INTO " . DB_PREFIX . "ocfilter_option_value_to_product SET 
							product_id = '" . (int)$product_id . "', 
							option_id = '" . (int)$option_id . "', 
							value_id = '" . (string)$value_id . "'");
					}
				}
			}
		}

		zip_close($zipArc);
		unlink($nameZip);
		$return_array['success'] = 'OCFilter to products updated successfuly!';
		$this->response->setOutput(json_encode($return_array));

	}

	public function images_go() {
		
		$this->load->language('api/images_go');

		$json = array();
		//$json = '';
		$image_f=  file_get_contents('php://input');
		$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
		file_put_contents($nameZip, $image_f);
		$zipArc = zip_open($nameZip);
		$dirname_array = array();
		
		if (is_resource($zipArc)) {
			
			while ($zip_entry = zip_read($zipArc)) {
			  		
				if (zip_entry_open($zipArc, $zip_entry, "r")) {
			  			
			  		$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			  			
			  		$options_array = json_decode(htmlspecialchars_decode($dump));
				 				
					foreach ($options_array as $option) {
						
						$namef= $option->{'namef'};
						$image_f = base64_decode($option->{'imagef'});
						$namef_with_dir = DIR_IMAGE.'catalog/'.$namef;
            			$dirname_namef = dirname($namef_with_dir);
              
              			if (!isset($dirname_array[$dirname_namef])) {
              
                			if(!file_exists($dirname_namef)){
                  				mkdir($dirname_namef, 0777, true);
                			}

                			$dirname_array[$dirname_namef] = true;
                		
						}							
						
						file_put_contents($namef_with_dir, $image_f);

					}
			  	}
			}
			  	
			zip_close($zipArc);
			unlink($nameZip);
			$json['success'] = 'images upload' ;
		} else {
			$json['error']   = 'zip not archive' ;				
		}
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
			
	}
	
	public function deleteAkcii() {
		
		$this->load->language('api/deleteAkcii');

		$json = array();
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_discount`;";
		$this->db->query( $sql );
		$json['success'] = 'Akcii udaleni' ;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	public function deleteimg() {
		
		$this->load->language('api/deleteimg');

		$json = array();
		
		$sql = "TRUNCATE TABLE `".DB_PREFIX."download`;";
		$this->db->query( $sql );
		
		$sql = "TRUNCATE TABLE `".DB_PREFIX."download_description`;";
		$this->db->query( $sql );
		
		$query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = DATABASE() AND table_name  = '".DB_PREFIX."download_report';";
		$result = $this->db->query($query);
			 
		if (count($result->rows)==1) { 
   			$sql = "TRUNCATE TABLE `".DB_PREFIX."download_report`;";
			$this->db->query( $sql ); 
		}
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_to_download`;";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_image`;";
		$this->db->query( $sql );
			
		$json['success'] = 'files udaleni' ;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
			
	}
	
	public function deleteSpecials() {
		
		$this->load->language('api/deleteSpecials');

		$json = array();
		
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_special`;";
		$this->db->query( $sql );
			
		$json['success'] = 'Specials udaleni' ;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	public function update_price() {
		
		$this->load->language('api/update_price');
     
    	$type_option  = $this->request->get['type_option'];
     
		$json = array();

		$image_f=  file_get_contents('php://input');
		$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
			
		file_put_contents($nameZip, $image_f);
			
      	$option_vid = array();
		$option_id= 0;
			
		$zipArc = zip_open($nameZip);
			
		if (is_resource($zipArc)) {
			
			$first_product = true;
			$sql_product = "INSERT INTO `".DB_PREFIX."product` (`product_id`,`quantity`,`price`,`stock_status_id`,`date_modified`,`date_available`,`status`) VALUES ";
				
			$first_product_option_value = true; 
			$sql_product_option_value = "INSERT INTO `".DB_PREFIX."product_option_value` (`product_option_id`,`product_id`,`option_id`,`option_value_id`,`quantity`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`) VALUES ";	
			  
        	$first_del_product_option_value= true;
			$sql_del_product_option_value = "DELETE FROM `".DB_PREFIX."product_option_value` WHERE product_id IN (";
        
        	$first_delete_product_option = true;
			$sql_delete_product_option = "DELETE FROM `".DB_PREFIX."product_option` WHERE product_id IN (";
        
        	$first_product_option = true; 
			$sql_product_option = "INSERT INTO `".DB_PREFIX."product_option` (`product_option_id`,`product_id`,`option_id`,`value`,`required`) VALUES ";
        
        	$sql_delete_product_special= "DELETE FROM `".DB_PREFIX."product_special` WHERE product_id IN ("; 
			$first_delete_product_special= true;

			$sql_product_special  = "INSERT INTO `".DB_PREFIX."product_special` (`product_id`,`customer_group_id`,`priority`,`price`,`date_start`,`date_end`) VALUES "; 		
			$first_product_special = true;
				
			$sql_delete_product_discount= "DELETE FROM `".DB_PREFIX."product_discount` WHERE product_id IN ("; 
			$first_delete_product_discount= true;

			$sql_product_discount  = "INSERT INTO `".DB_PREFIX."product_discount` (`product_id`,`customer_group_id`,`quantity`,`priority`,`price`,`date_start`,`date_end`) VALUES "; 		
			$first_product_discount = true;
			  
        	$ProductOption= $this->getProductOption();
        
        	$sql = "Select max(`product_option_id`) as `product_option_id` from `".DB_PREFIX."product_option`;";
			$result = $this->db->query( $sql );
			$product_option_id= 0;
			
			foreach ($result->rows as $row) {				
				$product_option_id= (int)$row['product_option_id'];
				$product_option_id++;				
			}
      
        	while ($zip_entry = zip_read($zipArc)) {
			  	
				if (zip_entry_open($zipArc, $zip_entry, "r")) {
			  			
			  		$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
					$products_id_array = json_decode($dump);
		
					foreach ($products_id_array as $parent) {
			
						$product_id= $parent->{'product_id'};
						$quantity= $parent->{'quantity'};
						$price= $parent->{'price'};
						$stock_status_id= $parent->{'stock_status_id'};
        				$status= $parent->{'status'};
        				$date_available= $parent->{'date_available'};		
			
						$sql_product .= ($first_product ) ? "" : ",";
						$sql_product .= " ( $product_id, $quantity, $price, $stock_status_id, NOW(),'$date_available',$status) ";
						$first_product  = false;
				
						$options = $parent->{'options'};

						if (empty($parent->{'specials'})) {
							$specials_empty = true;
						} else { 
							$specials_empty = false;
							$specials = $parent->{'specials'};
						};
				
						if (empty($parent->{'akcii'})) {
							$akcii_empty = true;
						} else { 
							$akcii_empty = false;
							$akcii = $parent->{'akcii'};
						};
				
        				if (!empty($type_option)) {            
          					if (count($options) !== 0) {
          
            					$sql_del_product_option_value .= ($first_del_product_option_value) ? "" : ",";
    					
    							$sql_del_product_option_value .= " $product_id ";
    					
    							$first_del_product_option_value= false;
            				} else {
          
            					$sql_delete_product_option .= ($first_delete_product_option ) ? "" : ",";
  								$sql_delete_product_option .= " $product_id ";
  								$first_delete_product_option = false;
							} 
						}
        
        				foreach ($options as $option) {
			
               				$option_id= $option->{'option_id'};
               
               				if (isset($ProductOption[$product_id][$option_id]) ) {
               					$option_vid[$option_id] = $ProductOption[$product_id][$option_id];
               				}
               
               				if ((!isset($ProductOption[$product_id][$option_id])) and (!isset($option_vid[$option_id]))) {
  						                                                    
        						$sql_product_option .= ($first_product_option ) ? "" : ","; 
        						$sql_product_option .= " ($product_option_id,$product_id,$option_id,'',1) ";
        						$first_product_option = false;
        							
        						$product_option_id_t = $product_option_id;
        						$product_option_id++;
                    
                      			$option_vid[$option_id] = $product_option_id_t;
                						
  							} else {
  								$product_option_id_t = $option_vid[$option_id];
  							}
      
							$option_value_id= $option->{'option_value_id'};
							$quantity= $option->{'quantity'};
							$subtract= $option->{'subtract'};
							$price= $option->{'price'};
							$price_prefix= $option->{'price_prefix'};
							$points= $option->{'points'};
							$points_prefix= $option->{'points_prefix'};
							$weight= $option->{'weight'};
							$weight_prefix= $option->{'weight_prefix'};
              
							$sql_product_option_value .= ($first_product_option_value ) ? "" : ",";
					 
							$sql_product_option_value .= "  ($product_option_id_t, $product_id, $option_id, $option_value_id ,$quantity,$subtract,$price,'$price_prefix',$points,'$points_prefix', $weight,'$weight_prefix') ";
																					
							$first_product_option_value = false;
              			
						}
				
        				$sql_delete_product_special .= ($first_delete_product_special ) ? "" : ",";
						$sql_delete_product_special .= " $product_id ";
						$first_delete_product_special= false;
        
						if (!$specials_empty) {
				
							if (count($specials) == 0) {
					
							} else {
								foreach ($specials as $special) {
			
									$customer_group_id= $special->{'customer_group_id'};
									$priority= $special->{'priority'};
									$date_start= $special->{'date_start'};
									$price= $special->{'price'};
									$date_end= $special->{'date_end'};

									$sql_product_special .= ($first_product_special ) ? "" : ",";
					 				$sql_product_special .= "  ($product_id, $customer_group_id,  $priority, $price,'$date_start','$date_end') ";
							
									$first_product_special = false;
								}
							}
						}
				
        				$sql_delete_product_discount .= ($first_delete_product_discount ) ? "" : ",";
						$sql_delete_product_discount .= " $product_id ";
						$first_delete_product_discount = false;

        				if (!$akcii_empty) {
				
							if (count($akcii) == 0) {
						
							} else {
								foreach ($akcii as $special) {
			
									$customer_group_id= $special->{'customer_group_id'};
									$priority= $special->{'priority'};
									$date_start= $special->{'date_start'};
									$price= $special->{'price'};
									$date_end= $special->{'date_end'};
									$quantity= $special->{'quantity'};

									$sql_product_discount .= ($first_product_discount ) ? "" : ",";
					 				$sql_product_discount .= "  ($product_id, $customer_group_id, $quantity,  $priority, $price,'$date_start','$date_end') ";
									$first_product_discount= false;
								}
							}
						}//akcii
					}
				}
			}
			
			if (!$first_delete_product_special ) {
			
				$sql_delete_product_special .=");";
				$this->db->query($sql_delete_product_special );
				
				$sql = "Select max(`product_special_id`) as `product_special_id` from `".DB_PREFIX."product_special`;";
				$result = $this->db->query( $sql );
				$product_special_id= 0;
				
				foreach ($result->rows as $row) {				
					$product_special_id= (int)$row['product_special_id'];
					$product_special_id++;					
				}
				
				$sql = "ALTER TABLE `".DB_PREFIX."product_special` AUTO_INCREMENT = $product_special_id ;";
				$this->db->query($sql);
			}
			
			if (!$first_product_special ) {				
							
				$sql_product_special .=";";
				$this->db->query($sql_product_special );				
			}
			
			if (!$first_delete_product_discount ) {
			
				$sql_delete_product_discount .=");";
				$this->db->query($sql_delete_product_discount );
				
				$sql = "Select max(`product_discount_id`) as `product_discount_id` from `".DB_PREFIX."product_discount`;";
				$result = $this->db->query( $sql );
				$product_discount_id= 0;
				
				foreach ($result->rows as $row) {				
					$product_discount_id= (int)$row['product_discount_id'];
					$product_discount_id++;					
				}

				$sql = "ALTER TABLE `".DB_PREFIX."product_discount` AUTO_INCREMENT = $product_discount_id;";
				$this->db->query($sql);
			}
			
			if (!$first_product_discount ) {			
				$sql_product_discount .=";";
				$this->db->query($sql_product_discount );				
			}
			
			if (!$first_product ) {
			
				$sql_product .=" ON DUPLICATE KEY UPDATE  ";
				$sql_product .= "`quantity`= VALUES(`quantity`),";
				$sql_product .= "`price`= VALUES(`price`),";
				$sql_product .= "`stock_status_id`= VALUES(`stock_status_id`),";
				$sql_product .= "`date_modified`= NOW(),";
        		$sql_product .= "`date_available`= VALUES(`date_available`),";
        		$sql_product .= "`status`= VALUES(`status`)";

				$sql_product .=";";
				$this->db->query($sql_product);
			}
			
			if (!$first_delete_product_option) {
				$sql_delete_product_option .=");";
				$this->db->query($sql_delete_product_option );
			}
      
    		if (!$first_product_option ) {
				$sql_product_option .=";";
				$this->db->query($sql_product_option );
			}
      
      		if (!$first_del_product_option_value) {
				
				$sql_del_product_option_value .=");";
				$this->db->query($sql_del_product_option_value );
        		
				$sqlproduct_option_value_id = "Select max(`product_option_value_id`) as `product_option_value_id` from `".DB_PREFIX."product_option_value`;";
				$resultproduct_option_value = $this->db->query( $sqlproduct_option_value_id );
				$product_option_idproduct_option_value= 1;
			
				foreach ($resultproduct_option_value ->rows as $row) {				
					$product_option_idproduct_option_value= (int)$row['product_option_value_id'];
					$product_option_idproduct_option_value++;				
				}

				$sqlproduct_option_value_id = "ALTER TABLE `".DB_PREFIX."product_option_value` AUTO_INCREMENT=$product_option_idproduct_option_value;";
				$this->db->query( $sqlproduct_option_value_id );

			}
      
			if (!$first_product_option_value ) {
			
				$sql_product_option_value .=" ON DUPLICATE KEY UPDATE  ";
				$sql_product_option_value .= "`quantity`= VALUES(`quantity`),";
				$sql_product_option_value .= "`subtract`= VALUES(`subtract`),";
				$sql_product_option_value .= "`price`= VALUES(`price`),";
				$sql_product_option_value .= "`price_prefix`= VALUES(`price_prefix`),";
				$sql_product_option_value .= "`points`= VALUES(`points`),";
				$sql_product_option_value .= "`points_prefix`= VALUES(`points_prefix`),";
				$sql_product_option_value .= "`weight`= VALUES(`weight`),";				
				$sql_product_option_value .= "`weight_prefix`= VALUES(`weight_prefix`)";
				$sql_product_option_value .=";";
				$this->db->query($sql_product_option_value);
			}
			
			zip_close($zipArc);
			unlink($nameZip);
			$json['success'] = 'update_price complete' ;
		
		} else {
			$json['error']   = 'zip not archive' ;				
		}	
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}

	public function update_order_status() {
		
		$this->load->language('api/update_order_status');

		$json = array();

		$image_f=  file_get_contents('php://input');
		$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
			
		file_put_contents($nameZip, $image_f);
		$zipArc = zip_open($nameZip);
			
		if (is_resource($zipArc)) {
			
			$first_order_history = true;
			$sql_order_history = "INSERT INTO `".DB_PREFIX."order_history` (`order_id`,`order_status_id`,`date_added`) VALUES ";
				
			$first_order = true;
			$sql_order = "INSERT INTO `".DB_PREFIX."order` (`order_id`,`order_status_id`) VALUES ";
			
			while ($zip_entry = zip_read($zipArc)) {
			  	
				if (zip_entry_open($zipArc, $zip_entry, "r")) {
			  			
			  		$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			  			
					$order_id_array = json_decode($dump);
		
					foreach ($order_id_array as $parent) {
			
						$order_id = $parent->{'order_id'};
						$order_status_id = $parent->{'order_status_id'};				
			
						$sql_order_history .= ($first_order_history ) ? "" : ",";
						$sql_order_history .= " ( $order_id,$order_status_id, NOW()) ";
						$first_order_history = false;
				
						$sql_order .= ($first_order ) ? "" : ",";
						$sql_order .= " ( $order_id,$order_status_id) ";
						$first_order = false;
					}
				}
			}
			
			if (!$first_order) {
			
				$sql_order .=" ON DUPLICATE KEY UPDATE  ";
				$sql_order .= "`order_status_id`= VALUES(`order_status_id`)";
				$sql_order .=";";
				$this->db->query($sql_order );
			}
			
			if (!$first_order_history ) {
				$sql_order_history .=";";
				$this->db->query($sql_order_history );
			}
			
			zip_close($zipArc);
			unlink($nameZip);
			$json['success'] = 'update_order_status complete' ;
		
		} else {
			$json['error']   = 'zip not archive' ;				
		}
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}

	public function get_orders() {
		
		$this->load->language('api/get_order');
    	
		$json = array();
		
		$last_order_id = (int)$this->request->get['last_id'];
		$date_start = $this->request->get['date_start'];

		$sql = "SELECT 
		order_table.order_id, 
		order_table.invoice_no, 
		order_table.invoice_prefix, 
		order_table.store_id, 
		order_table.store_name, 
		order_table.store_url, 
		order_table.customer_id, 
		order_table.customer_group_id, 
		order_table.firstname, 
		order_table.lastname, 
		order_table.email, 
		order_table.telephone, 
		order_table.fax, 
		order_table.custom_field, 
		order_table.payment_firstname, 
		order_table.payment_lastname, 
		order_table.payment_company, 
		order_table.payment_address_1, 
		order_table.payment_address_2, 
		order_table.payment_city, 
		order_table.payment_postcode, 
		order_table.payment_country, 
		order_table.payment_country_id, 
		order_table.payment_zone, 
		order_table.payment_zone_id, 
		order_table.payment_address_format, 
		order_table.payment_custom_field, 
		order_table.payment_method, 
		order_table.payment_code, 
		order_table.shipping_firstname, 
		order_table.shipping_lastname, 
		order_table.shipping_company, 
		order_table.shipping_address_1, 
		order_table.shipping_address_2, 
		order_table.shipping_city, 
		order_table.shipping_postcode, 
		order_table.shipping_country, 
		order_table.shipping_country_id, 
		order_table.shipping_zone, 
		order_table.shipping_zone_id, 
		order_table.shipping_address_format, 
		order_table.shipping_custom_field, 
		order_table.shipping_method, 
		order_table.shipping_code, 
		order_table.comment, 
		order_table.total, 
		order_table.order_status_id, 
		order_table.affiliate_id, 
		order_table.commission, 
		order_table.marketing_id, 
		order_table.date_added, 
		order_table.date_modified, 
		order_total_table.title AS title_ship, 
		sum(IFNULL(order_total_table.value,0)) AS value_ship, 
		sum(IFNULL(customer_reward_table.points,0)) AS customer_reward_points 
		FROM `".DB_PREFIX."order` AS order_table ";
		
		$sql .= " LEFT JOIN `".DB_PREFIX."customer_reward` AS customer_reward_table ON customer_reward_table.customer_id = order_table.customer_id";
		
		$sql .= " LEFT JOIN `".DB_PREFIX."order_total` AS order_total_table 
		ON order_total_table.order_id = order_table.order_id 
		AND order_total_table.code = 'shipping'";
		
		/* 
		//Для рос. СДЭКА)
		$sql .= " LEFT JOIN `".DB_PREFIX."order_total` AS order_total_bbcod 
		ON order_total_bbcod.order_id = order_table.order_id 
		AND (order_total_bbcod.code = 'bb_cod' OR order_total_bbcod.code = 'cod_cdek_total')";
		*/
		
		$sql .= "WHERE order_table.order_id > $last_order_id";

		if (!empty($date_start)) {
			$sql .= " AND DATE_FORMAT(order_table.date_added, '%Y-%m-%d') >= '$date_start'";
		}

		$sql .= " GROUP BY 
		order_table.order_id, 
		order_table.invoice_no, 
		order_table.invoice_prefix, 
		order_table.store_id, 
		order_table.store_name, 
		order_table.store_url, 
		order_table.customer_id, 
		order_table.customer_group_id, 
		order_table.firstname, 
		order_table.lastname, 
		order_table.email, 
		order_table.telephone, 
		order_table.fax, 
		order_table.custom_field, 
		order_table.payment_firstname, 
		order_table.payment_lastname, 
		order_table.payment_company, 
		order_table.payment_address_1, 
		order_table.payment_address_2, 
		order_table.payment_city, 
		order_table.payment_postcode, 
		order_table.payment_country, 
		order_table.payment_country_id, 
		order_table.payment_zone, 
		order_table.payment_zone_id, 
		order_table.payment_address_format, 
		order_table.payment_custom_field, 
		order_table.payment_method, 
		order_table.payment_code, 
		order_table.shipping_firstname, 
		order_table.shipping_lastname, 
		order_table.shipping_company, 
		order_table.shipping_address_1, 
		order_table.shipping_address_2, 
		order_table.shipping_city, 
		order_table.shipping_postcode, 
		order_table.shipping_country, 
		order_table.shipping_country_id, 
		order_table.shipping_zone, 
		order_table.shipping_zone_id, 
		order_table.shipping_address_format, 
		order_table.shipping_custom_field, 
		order_table.shipping_method, 
		order_table.shipping_code, 
		order_table.comment, 
		order_table.total, 
		order_table.order_status_id, 
		order_table.affiliate_id, 
		order_table.commission, 
		order_table.marketing_id, 
		order_table.date_added, 
		order_table.date_modified ";

		$sql .= "ORDER BY order_table.order_id ASC";

		$data_array = array();

		$query = $this->db->query( $sql );

		foreach ($query->rows as $row) {
			
			$order_id = (int)$row['order_id'];
			$order_data = $this->object_to_array($row);
			$products_list = $this->GetOrderProducts($order_id);
			$order_data['products_list'] = $products_list;
			$data_array[] = $order_data;

		}

		$json['success'] = $data_array;

		$this->response->addHeader('Content-Type: application/json');
		//$this->response->setCompression(9);
		$this->response->setOutput(json_encode($json));

	}
	
	protected function GetOrderProducts($order_id) 
	{

		$products_data = array();

		$sql_order_products = "SELECT 
		order_product_tbl.order_product_id, 
		order_product_tbl.order_id, 
		order_product_tbl.product_id, 
		order_product_tbl.name, 
		order_product_tbl.model, 
		order_product_tbl.quantity, 
		order_product_tbl.price, 
		order_product_tbl.total, 
		order_product_tbl.tax, 
		order_product_tbl.reward,
		IF (product_tbl.product_id IS NULL, TRUE, FALSE) AS product_not_exist
		FROM `".DB_PREFIX."order_product` AS order_product_tbl 
		LEFT JOIN `".DB_PREFIX."product` AS product_tbl ON product_tbl.product_id = order_product_tbl.product_id 
		WHERE order_product_tbl.order_id = $order_id 
		ORDER BY order_product_tbl.order_product_id ASC";

		$query = $this->db->query($sql_order_products);
		
		foreach ($query->rows as $row) {

			$order_product_id = (int)$row['order_product_id'];	
			$product_id = (int)$row['product_id'];		

			$order_line_data = $this->object_to_array($row);

			$order_product_options_data = $this->GetOrderProductsOptions($order_id, $order_product_id, $product_id);

			$order_line_data['options_data'] = $order_product_options_data;

			$products_data[] = $order_line_data;

		}

		return $products_data;		

	}

	protected function GetOrderProductsOptions($order_id, $order_product_id, $product_id)
	{

		$sql_order_options = "SELECT 
		order_opt.order_option_id, 
		order_opt.order_id, 
		order_opt.order_product_id, 
		order_opt.product_option_id, 
		order_opt.product_option_value_id, 
		order_opt.name, 
		order_opt.value, 
		order_opt.type, 
		IF (pd_opt.product_id IS NULL, TRUE, FALSE) AS pd_opt_not_exist
		FROM `" .DB_PREFIX. "order_option` AS order_opt 
		LEFT JOIN `" .DB_PREFIX. "product_option_value` AS pd_opt 
		ON order_opt.product_option_value_id = pd_opt.product_option_value_id 
		AND	order_opt.product_option_id = pd_opt.product_option_id 
		AND pd_opt.product_id = $product_id 
		WHERE order_opt.order_id = $order_id AND order_opt.order_product_id = $order_product_id 
		ORDER BY order_opt.order_option_id ASC";

		$query = $this->db->query($sql_order_options);
		$rows = $query->rows;

		return $rows;

	}

	public function deleteNoms() {
		
		$this->load->language('api/deleteNoms');

		$json = array();

		$sql = "TRUNCATE TABLE `".DB_PREFIX."product`;";
		$this->db->query( $sql );
		
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_attribute`;";
		$this->db->query( $sql );
		
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_description`;";
		$this->db->query( $sql );

		$sql = "DELETE FROM `".DB_PREFIX."seo_url` WHERE `query` LIKE 'product_id=%';\n";
		$this->db->query( $sql );

		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_discount`;";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_filter`;";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_image`;";
		$this->db->query( $sql );

		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_option`";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_option_value`";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_recurring`";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_related`";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_reward`";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_special`";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_to_category`";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_to_download`";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_to_layout`";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_to_store`";
		$this->db->query( $sql );
			
		$json['success'] = 'noms udaleni' ;
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
			
	}

	public function deleteCategories() {
		
		$this->load->language('api/deleteCategories');

		$json = array();

		$sql = "TRUNCATE TABLE `".DB_PREFIX."category`;";
		$this->db->query( $sql );
		
		$sql = "TRUNCATE TABLE `".DB_PREFIX."category_description`;";
		$this->db->query( $sql );
		
		$sql = "TRUNCATE TABLE `".DB_PREFIX."category_to_store`;";
		$this->db->query( $sql );

		$sql = "DELETE FROM `".DB_PREFIX."seo_url` WHERE `query` LIKE 'category_id=%';\n";
		$this->db->query( $sql );

		$sql = "TRUNCATE TABLE `".DB_PREFIX."category_to_layout`;";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."category_filter`;";
		$this->db->query( $sql );
			
		$sql = "TRUNCATE TABLE `".DB_PREFIX."product_to_category`;";
		$this->db->query( $sql );

		$sql = "TRUNCATE TABLE `".DB_PREFIX."category_path`";
		$this->db->query( $sql );
			
		$json['success'] = 'Categories udaleni' ;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	// НЕ ИСПОЛЬЗУЕТСЯ
	public function category_continue() {
		
		$this->load->language('api/category_continue');

		$json = array();

		$pahts_array= json_decode(htmlspecialchars_decode($this->request->post['pahts']));
		$parents_array= json_decode(htmlspecialchars_decode($this->request->post['parents']));
		
		$first_path = true;
		$sql_path = "INSERT INTO `".DB_PREFIX."category_path` (`category_id`,`path_id`,`level`) VALUES ";		
		
		$first_category = true;
		$sql_category = "INSERT INTO `".DB_PREFIX."category` (`category_id`, `parent_id`) VALUES ";
		
		foreach ($pahts_array as $path) {
					
			$category_id= $path->{'category_id'};
						
			$paths = $this->object_to_array($path->{'path_arr'});
			
			foreach ($paths as $path_str) {
						
				$path_id = $path_str['path_id'];	
				$level= $path_str['level'];
						
				$sql_path .= ($first_path) ? "" : ",";
						
				$sql_path .= " ($category_id,$path_id,$level) ";
				$first_path = false;

			}	
		}
		
		foreach ($parents_array as $parent) {
			
			$category_id= $parent->{'category_id'};
			$parent_id = $parent->{'parent_id'};
			
			$sql_category .= ($first_category ) ? "" : ",";
			$sql_category .= " ( $category_id, $parent_id ) ";
			$first_category = false; 
		}
			
		if (!$first_category ) {
			
			$sql_category .=" ON DUPLICATE KEY UPDATE  ";
			$sql_category .= "`parent_id`= VALUES(`parent_id`)";

			$sql_category .=";";
			$this->db->query($sql_category );
		}

		if (!$first_path ) {
			
			$sql_path .=" ON DUPLICATE KEY UPDATE  ";
			$sql_path .= "`level`= VALUES(`level`)";
			$sql_path .=";";
			$this->db->query($sql_path );
		}

		$json['success'] = 'Category continue' ;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}
		
	public function category_add() {
		
		$this->load->language('api/category_add');
		
		// query texts to custom log-file:
		//$logger = new Log('cat_query_text.log');

		$json = array();
		
		$cat_ids = array(); // Соответствие между 1С-ным УИД-ом и "category_id" загружаемой/обновляемой категории. 

		$image_f =  file_get_contents('php://input'); // чтение файла в строку
		$nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip'; // получение адреса для сохранения

		file_put_contents($nameZip, $image_f); // размещение файла по адресу
		$zipArc = zip_open($nameZip); // открытие zip-архива
			
		if (is_resource($zipArc)) {
			
			$available_store_ids = $this->getAvailableStoreIds(); // доступные идентификаторы магазинов (0 - по-умолчанию)
			$languages = $this->getLanguages(); // доступные языки
				
			$url_alias_ids = array();
			
			//if (!$this->use_table_seo_url) {
				$url_alias_ids = $this->getCategorysSEOKeywords();
			//}
			
			$sql = "SHOW COLUMNS FROM `".DB_PREFIX."category_description` LIKE 'meta_title'";
			$query = $this->db->query( $sql );
			
			$exist_meta_title = ($query->num_rows > 0) ? true : false;
			
			$image_exist= (int)$this->request->get['img']; // признак загрузки изображений категорий
			$seo_update= (int)$this->request->get['seo_update']; // признак обновления SEO
			
			$first_category = true;
			$sql_category = "INSERT INTO `".DB_PREFIX."category` (
				`category_id`, 
				`image`, 
				`parent_id`, 
				`top`, 
				`column`, 
				`date_added`, 
				`date_modified`, 
				`status`) VALUES ";
			
			$first_category_description = true;
			
			if ($exist_meta_title) {
				$sql_category_description = "INSERT INTO `".DB_PREFIX."category_description` (
					`category_id`, 
					`language_id`, 
					`name`, 
					`description`, 
					`meta_title`, 
					`meta_description`,
					`meta_keyword`) VALUES ";
			} else {
				$sql_category_description = "INSERT INTO `".DB_PREFIX."category_description` (
					`category_id`, 
					`language_id`, 
					`name`, 
					`description`, 
					`meta_description`, 
					`meta_keyword`) VALUES ";					
			}
			
			$first_category_to_store = true;
			$sql_category_to_store = "INSERT INTO `".DB_PREFIX."category_to_store` (`category_id`,`store_id`) VALUES "; 
			
			$first_category_to_layout = true;
			$sql_category_to_layout = "INSERT INTO `".DB_PREFIX."category_to_layout` (`category_id`,`store_id`,`layout_id`) VALUES ";
			
			$sql = "Select max(`category_id`) as `category_id` from `".DB_PREFIX."category`;";
			$result = $this->db->query( $sql );
			$category_id_max = 0;
			
			foreach ($result->rows as $row) {				
				$category_id_max = (int)$row['category_id'];				
			}

			//$i = 1;
			
			$first_delete_path = true;
			$sql_first_delete_path = "DELETE FROM `".DB_PREFIX."category_path` WHERE category_id IN (";
			$sql_second_delete_path = "DELETE FROM `".DB_PREFIX."category_path` WHERE path_id IN (";
			
			$first_url_alias = true;
			$first_url_aliasUPDATE = true;

			$sql_url_alias =       "INSERT INTO `".DB_PREFIX."seo_url` (`keyword`,`query`,`store_id`,`language_id`) VALUES ";
			$sql_url_aliasUPDATE = "INSERT INTO `".DB_PREFIX."seo_url` (`seo_url_id`,`keyword`,`query`,`store_id`,`language_id`) VALUES ";
			
			while ($zip_entry = zip_read($zipArc)) {
			  		
				if (zip_entry_open($zipArc, $zip_entry, "r")) {
			  			
			  		$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

					$data_array= json_decode($dump);

					$category_desc_values = array(); //В этом массиве накапливаем значения описаний для категорий с "parent_id" <> 0 или "category_id" = 0
					
					$category_path = array(); // Для создания записей в таблице "category_path"

					foreach ($data_array as $category){
				
						//$category_id_source = $category->{'category_id'}; // для проверки при формировании текста запроса к таблице "category_description"

						$category_id = $category->{'category_id'};
						$cat_ref_1c = $category->{'ref'}; // 1С-ный УИД категории
						$parent_ref_1c = $category->{'parent_ref'}; // 1С-ный УИД категории-родителя

						if ($category_id == 0) {
				
							$category_id_max = $category_id_max + 1;				
							$category_id  = $category_id_max ;
							$insert = 1;		
															
						} else {														
						
							$insert = 0;
					
							$sql_first_delete_path .= ($first_delete_path ) ? "" : ",";
							$sql_second_delete_path .= ($first_delete_path ) ? "" : ",";
						
							$sql_first_delete_path .= " $category_id ";
							$sql_second_delete_path .= " $category_id ";	
							$first_delete_path = false;
						};
						
						$cat_ids[$cat_ref_1c] = $category_id;

						//$names = $this->object_to_array($data->{'names'});
						// extract the category details
						
						$image= $category->{'image'};
						
						$parent_id = $category->{'parent_id'};
						
						if ($parent_id == 0 and $parent_ref_1c <> '' ) {
							$parent_id = $cat_ids[$parent_ref_1c];
						}
						
						//category_path
						$category_path[$category_id] = $parent_id;

						$top = $category->{'top'};
					
						$column = $category->{'column'};
						//$sort_order = $category->{'sort_order'};
						$date_added = $category->{'date_added'};
						$date_modified = $category->{'date_modified'};
						$names = $this->object_to_array($category->{'name'});
						$descriptions = $this->object_to_array($category->{'description'});
					
						if ($exist_meta_title) {
							$meta_titles = $this->object_to_array($category->{'meta_title'});
						}
					
						$meta_descriptions = $this->object_to_array($category->{'meta_description'});
						$meta_keywords = $this->object_to_array($category->{'meta_keyword'});
							
						$keywords = $this->object_to_array($category->{'seo_keyword'});	
					
						$store_ids = $this->object_to_array($category->{'store_ids'});
						$layout = $this->object_to_array($category->{'layout'});
						$status = $this->object_to_array($category->{'status'});
						
						// generate and execute SQL for inserting the category
						$sql_category .= ($first_category ) ? "" : ",";
						$sql_category .= " ( $category_id,   ";				
						$sql_category .= "'$image',";				
						$sql_category .= " $parent_id, $top, $column,'$date_added',";
						$sql_category .= "'$date_modified',";
						$sql_category .= " $status) ";

						$first_category = false;
						$store_id = 0;
				
						foreach ($languages as $language) {
						
							$language_code = $language['code'];
							$language_id = $language['language_id'];
							$name = isset($names[$language_code]) ? urldecode($this->db->escape($names[$language_code])) : '';
							$description = isset($descriptions[$language_code]) ? urldecode($this->db->escape($descriptions[$language_code])) : '';
						
							if ($exist_meta_title) {
								$meta_title = isset($meta_titles[$language_code]) ? urldecode($this->db->escape($meta_titles[$language_code])) : '';
							}
						
							$meta_description = isset($meta_descriptions[$language_code]) ? urldecode($this->db->escape($meta_descriptions[$language_code])) : '';
							$meta_keyword = isset($meta_keywords[$language_code]) ? urldecode($this->db->escape($meta_keywords[$language_code])) : '';
						
							if ($exist_meta_title) {
								
								//$sql_category_description .= ($first_category_description ) ? "" : ","; // Не нужно
								//$sql_category_description .= " ( $category_id, $language_id, '$name', '$description', '$meta_title', '$meta_description', '$meta_keyword' ) ";
							
								$desc_value = " ( $category_id, $language_id, '$name', '$description', '$meta_title', '$meta_description', '$meta_keyword' ) ";

							} else {
								
								//$sql_category_description .= ($first_category_description ) ? "" : ","; // Не нужно
								//$sql_category_description .= " ( $category_id, $language_id, '$name', '$description', '$meta_description', '$meta_keyword' ) ";

								$desc_value = " ( $category_id, $language_id, '$name', '$description', '$meta_description', '$meta_keyword' ) ";
							}
					
							//if ($parent_id > 0 or $category_id_source == 0) {
								$category_desc_values[] = $desc_value;
							//}

							if(isset($keywords[$language_code]) and (($seo_update==1) or ($insert == 1))) {
							
								$keyword= isset($keywords[$language_code]) ? urldecode($this->db->escape($keywords[$language_code])) : '';
																	
								if (isset($url_alias_ids[$category_id][$store_id][$language_id ])) {										
											
									$url_alias_id = $url_alias_ids[$category_id][$store_id][$language_id];
											
									$sql_url_aliasUPDATE .= ($first_url_aliasUPDATE  ) ? "" : ",";
									$sql_url_aliasUPDATE .= " ('$url_alias_id','$keyword','category_id=$category_id',$store_id,$language_id )";
									$first_url_aliasUPDATE  = false;
								
								} else {
									
									$sql_url_alias .= ($first_url_alias  ) ? "" : ",";
									$sql_url_alias .= " ('$keyword','category_id=$category_id',$store_id,$language_id )";
									$first_url_alias  = false;

								}
							}
					
							$first_category_description = false;

						}
				
						foreach ($store_ids as $store_id) {
							
							if (in_array((int)$store_id,$available_store_ids)) {
						
								$sql_category_to_store .= ($first_category_to_store ) ? "" : ",";
								$sql_category_to_store .= " ($category_id,$store_id) ";
								$first_category_to_store = false;
							
							}
						}

						$layouts = array();
						foreach ($layout as $layout_part) {
							
							$next_layout = explode(':',$layout_part);
							
							if ($next_layout===false) {
								
								$next_layout = array( 0, $layout_part );
							
							} else if (count($next_layout)==1) {
								$next_layout = array( 0, $layout_part );
							}
							
							if ( (count($next_layout)==2) && (in_array((int)$next_layout[0],$available_store_ids)) && (is_string($next_layout[1])) ) {
								
								$store_id = (int)$next_layout[0];
								$layout_name = $next_layout[1];
								
								if (isset($layout_ids[$layout_name])) {
									$layout_id = (int)$layout_ids[$layout_name];
									
									if (!isset($layouts[$store_id])) {
										$layouts[$store_id] = $layout_id;
									}
								}
							}
						}

						foreach ($layouts as $store_id => $layout_id) {
				
							$sql_category_to_layout .= ($first_category_to_layout) ? "" : ",";
							$sql_category_to_layout .= " ($category_id,$store_id,$layout_id) ";
							$first_category_to_layout = false;

						}
			
						//$vozvrat_json[$category->{'ref'}]= $category_id;
						//$i++;
		
					}//перебор переданного массива
			
					/*if (!$first_delete_path) {
					
						$sql_first_delete_path .=");";
						
						$this->db->query($sql_first_delete_path ); // Временно!
						//$logger->write("\n" . $sql_first_delete_path . "\n\n");
					
						$sql_second_delete_path .=");";
						//$logger->write("\n" . $sql_second_delete_path . "\n\n");

						$this->db->query($sql_second_delete_path);
					}*/
			
					if (!$first_category ) {
					
						$sql_category .=" ON DUPLICATE KEY UPDATE  ";
						
						if ($image_exist==1) {
							//$sql_category .= "`image`= VALUES(`image`),";
						}	
						
						$sql_category .= "`parent_id`= VALUES(`parent_id`),";
						//$sql_category .= "`top`= VALUES(`top`),";
						//$sql_category .= "`column`= VALUES(`column`),";
						//$sql_category .= "`sort_order`= VALUES(`sort_order`),";
						//$sql_category .= "`date_added`= VALUES(`date_added`),";
						//$sql_category .= "`date_modified`= VALUES(`date_modified`),";
						$sql_category .= "`status`= VALUES(`status`)";
						$sql_category .=";";
						
						$this->db->query($sql_category ); // Временно!
						//$logger->write("\n" . $sql_category . "\n\n");

					}
			
					if (!$first_category_description ) {
					
						// Если есть данные в массиве описаний, тогда есть смысл выполнять этот запрос
						if (count($category_desc_values) > 0 ) {
							
							foreach ($category_desc_values as $desc_value) {
								
								$sql_category_description .= $desc_value;
								$sql_category_description .= ",";
								
							}
						
							$sql_category_description = rtrim($sql_category_description, ",");							
						
							if ($exist_meta_title) {
					
								$sql_category_description .=" ON DUPLICATE KEY UPDATE  ";
								$sql_category_description .= "`name`= VALUES(`name`)";
								$sql_category_description .= ",`description`= VALUES(`description`)";
							
								//if ($seo_update==1) {
									$sql_category_description .= ",`meta_title`= VALUES(`meta_title`)";
									$sql_category_description .= ",`meta_description`= VALUES(`meta_description`)";
									$sql_category_description .= ",`meta_keyword`= VALUES(`meta_keyword`)";
								//}
			
								$sql_category_description .=";";
							
								$this->db->query($sql_category_description ); // Временно!
								//$logger->write("\n" . $sql_category_description . "\n\n");
						
							} else {
						
								$sql_category_description .=" ON DUPLICATE KEY UPDATE  ";
								$sql_category_description .= "`name`= VALUES(`name`)";
								$sql_category_description .= ",`description`= VALUES(`description`)";
							
								//if ($seo_update==1) {
									$sql_category_description .= ",`meta_description`= VALUES(`meta_description`)";
									$sql_category_description .= ",`meta_keyword`= VALUES(`meta_keyword`)";
								//}
			
								$sql_category_description .=";";
							
								$this->db->query($sql_category_description ); // Временно!
								//$logger->write("\n" . $sql_category_description . "\n\n");
							}
						}
					}
					
					// Обновление путей для категорий
					foreach ($category_path as $cat_id => $par_id) {
						$this->addOrRepairCategoryPath($cat_id, $par_id);
					}

				}
			}
			
			if (!$first_url_aliasUPDATE ) {								
			
				$sql_url_aliasUPDATE .=" ON DUPLICATE KEY UPDATE  ";
				$sql_url_aliasUPDATE .= "`keyword`= VALUES(`keyword`)";								
				$sql_url_aliasUPDATE .=";";
				
				$this->db->query($sql_url_aliasUPDATE); // Временно!
				//$logger->write("\n" . $sql_url_aliasUPDATE . "\n\n");

			}
			 
			if (!$first_url_alias) {								
			
				$sql_url_alias .=";";
				
				$this->db->query($sql_url_alias); // Временно!
				//$logger->write("\n" . $sql_url_alias . "\n\n");

			}
			
			if (!$first_category_to_store ) {
			
				$sql_category_to_store .=" ON DUPLICATE KEY UPDATE  ";
				$sql_category_to_store .= "`store_id`= VALUES(`store_id`)";
	
				$sql_category_to_store .=";";

				$this->db->query($sql_category_to_store ); // Временно!
				//$logger->write("\n Query: \n" . $sql_category_to_store . "\n\n");

			}

			if (!$first_category_to_layout ) {
			
				$sql_category_to_layout .=" ON DUPLICATE KEY UPDATE  ";
				$sql_category_to_layout .= "`layout_id`= VALUES(`layout_id`)";
				$sql_category_to_layout .=";";
				
				$this->db->query($sql_category_to_layout ); // Временно!
				//$logger->write("\n Query: \n" . $sql_category_to_layout . "\n\n");

			}
			
			$json['success'] = $cat_ids;
						
			zip_close($zipArc);
			unlink($nameZip);
			
		} else {

			$json['error'] = 'zip error category add' ;
			
		}
															
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	public function add() {

		//$logger = new Log('ProductOptions_query_texts.log');

        $this->load->language('api/product');

        #$type_option  = $this->request->get['type_option'];

		$seo_update   = (int)$this->request->get['seo_update'];
        $description_update = (int)$this->request->get['description_update'];

        $json_return = array();
        #$vozvrat_json = array();

        $image_f = file_get_contents('php://input');
        $nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
        file_put_contents($nameZip, $image_f);
        $zipArc = zip_open($nameZip);

        if (is_resource($zipArc)) {
			
            $languages = $this->getLanguages();
			
			// get list of the field names, some are only available for certain OpenCart versions
			$query = $this->db->query( "DESCRIBE `".DB_PREFIX."product`" );
			$product_fields = array();
			
			foreach ($query->rows as $row) {
				$product_fields[] = $row['Field'];
			}
			
			// Opencart versions from 2.0 onwards also have product_description.meta_title
			$sql = "SHOW COLUMNS FROM `".DB_PREFIX."product_description` LIKE 'meta_title'";
			$query = $this->db->query( $sql );
			$exist_meta_title = ($query->num_rows > 0) ? true : false;
			
			// Opencart versions from 2.0 onwards also have product_description.meta_title
			$sql = "SHOW COLUMNS FROM `".DB_PREFIX."product_description` LIKE 'meta_h1'";
			$query = $this->db->query( $sql );
			$exist_meta_h1 = ($query->num_rows > 0) ? true : false;
			
			// some older versions of OpenCart use the 'product_tag' table
			$exist_table_product_tag = false;
			$query = $this->db->query( "SHOW TABLES LIKE '".DB_PREFIX."product_tag'" );
			$exist_table_product_tag = ($query->num_rows > 0);
			
			// get pre-defined store_ids
			$available_store_ids = $this->getAvailableStoreIds();
			
			// get pre-defined layouts
			$layout_ids = $this->getLayoutIds();
			
			// find existing manufacturers, only newly specified manufacturers will be added
			$manufacturers = $this->getManufacturers();
			
			// get weight classes
			$weight_class_ids = $this->getWeightClassIds();
			
			// get length classes
			$length_class_ids = $this->getLengthClassIds();
					
			// save old url_alias_ids
			$url_alias_ids = array();
			//if (!$this->use_table_seo_url) {
				$url_alias_ids = $this->getProductSEOKeywords();
			//}
			

			$sql = "Select max(`product_id`) as `product_id` from `".DB_PREFIX."product`;";
			$result = $this->db->query( $sql );
			$product_id_max = 0;
			
			foreach ($result->rows as $row) {				
				$product_id_max  = (int)$row['product_id'];				
			}
			
			// generate and execute SQL for inserting the product
			$sql_product  = "INSERT INTO `".DB_PREFIX."product` (`product_id`,`quantity`,`sku`,`upc`,";
				
			$sql_product  .= in_array('ean',$product_fields) ? "`ean`," : "";
			$sql_product  .= in_array('jan',$product_fields) ? "`jan`," : "";
			$sql_product  .= in_array('isbn',$product_fields) ? "`isbn`," : "";
			$sql_product  .= in_array('mpn',$product_fields) ? "`mpn`," : "";

			$sql_product  .= "`location`,`stock_status_id`,`model`,`manufacturer_id`,`shipping`,`price`,`points`,`date_added`,`date_modified`,`date_available`,`weight`,`weight_class_id`,`status`,";
			$sql_product  .= "`tax_class_id`,`length`,`width`,`height`,`length_class_id`,`subtract`,`minimum`) VALUES ";
			
			//код обновления
			$sql_productDUPLICATE  =" ON DUPLICATE KEY UPDATE  ";
			
			$first_product_to_layout = true;
			$sql_product_to_layout = "INSERT INTO `".DB_PREFIX."product_to_layout` (`product_id`,`store_id`,`layout_id`) VALUES ";
			
			$first_product_related = true;
			$sql_product_related = "INSERT INTO `".DB_PREFIX."product_related` (`product_id`,`related_id`) VALUES ";
			
			$firstsql_product_to_store  = true;
			$sql_product_to_store = "INSERT INTO `".DB_PREFIX."product_to_store` (`product_id`,`store_id`) VALUES ";

			$first_url_alias  = true;
			$sql_url_alias = "INSERT INTO `".DB_PREFIX."seo_url` (`keyword`,`query`,`store_id`,`language_id`) VALUES ";

			$first_url_aliasUPDATE  = true;
			$sql_url_aliasUPDATE = "INSERT INTO `".DB_PREFIX."seo_url` (`seo_url_id`,`keyword`,`query`,`store_id`,`language_id`) VALUES ";
			
			$first_category_id = true;

			$sql_del_category_id = "DELETE FROM `".DB_PREFIX."product_to_category` WHERE product_id IN (";
			$sql_category_id = "INSERT INTO `".DB_PREFIX."product_to_category` (`product_id`,`category_id`, `main_category`) VALUES ";
			
			$first_product_attribute = true; 
			$sql_product_attribute = "INSERT INTO `".DB_PREFIX."product_attribute` (`product_id`,`attribute_id`,`language_id`,`text`) VALUES ";
			
			$first_product_filter = true; 
			$sql_product_filter = "INSERT INTO `".DB_PREFIX."product_filter` (`product_id`,`filter_id`) VALUES ";
			
			#$first_product_option = true;
			#$sql_product_option = "INSERT INTO `".DB_PREFIX."product_option` (`product_option_id`,`product_id`,`option_id`,`value`,`required`) VALUES ";			
			
			#$first_product_option_value = true; 
			#$sql_product_option_value = "INSERT INTO `".DB_PREFIX."product_option_value` (`product_option_id`,`product_id`,`option_id`,`option_value_id`,`quantity`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`) VALUES ";
			
			#$first_del_product_option_value= true;
			#$sql_del_product_option_value = "DELETE FROM `".DB_PREFIX."product_option_value` WHERE product_id IN (";
			
			$first_del_product_attribute= true;
			$sql_del_product_attribute = "DELETE FROM `".DB_PREFIX."product_attribute` WHERE product_id IN (";
			
			$first_del_product_filter= true;
			$sql_del_product_filter = "DELETE FROM `".DB_PREFIX."product_filter` WHERE product_id IN (";

			#$first_delete_product_option = true;
			#$sql_delete_product_option = "DELETE FROM `".DB_PREFIX."product_option` WHERE product_id IN (";

			$first_delete_path = true;
			$sql_first_delete_path = "DELETE FROM `".DB_PREFIX."product_to_category` WHERE product_id IN (";

			$first_delete_product_special = true;
			$sql_delete_product_special = "DELETE FROM `".DB_PREFIX."product_special` WHERE product_id IN (";

			$first_product_special = true;
			$sql_product_special  = "INSERT INTO `".DB_PREFIX."product_special` (`product_id`,`customer_group_id`,`priority`,`price`,`date_start`,`date_end`) VALUES ";

			$first_delete_product_discount = true;
			$sql_delete_product_discount = "DELETE FROM `".DB_PREFIX."product_discount` WHERE product_id IN (";

			$first_product_discount = true;
			$sql_product_discount  = "INSERT INTO `".DB_PREFIX."product_discount` (`product_id`,`customer_group_id`,`quantity`,`priority`,`price`,`date_start`,`date_end`) VALUES ";

			$first_product_description = true;
			$first_product_tag  = true;
			if ($exist_table_product_tag) {
                
				if ($exist_meta_title) {
                    $sql_product_description  = "INSERT INTO `".DB_PREFIX."product_description` (`product_id`, `language_id`, `name`, `description` , 'meta_title', 'meta_description', 'meta_keyword', 'tag')" ;
                } else {
                    $sql_product_description  = "INSERT INTO `".DB_PREFIX."product_description` (`product_id`, `language_id`, `name`, `description`, `meta_description`, `meta_keyword`" ;
                }
                if ($exist_meta_h1) {
                    $sql_product_description  .= ", `meta_h1`";
                }

                $sql_product_description  .= ") VALUES ";
                $sql_product_tag  = "INSERT INTO `".DB_PREFIX."product_tag` (`product_id`,`language_id`,`tag`) VALUES " ;

            } else {
                
				if ($exist_meta_title) {
                    $sql_product_description  = "INSERT INTO `".DB_PREFIX."product_description` (`product_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`, `tag`" ;
                } else {
                    $sql_product_description  = "INSERT INTO `".DB_PREFIX."product_description` (`product_id`, `language_id`, `name`, `description`, `meta_description`, `meta_keyword`, `tag`";
                }

                if ($exist_meta_h1) {
                    $sql_product_description  .= ", `meta_h1`";
                }

                $sql_product_description  .= ") VALUES ";
            }

			/*
			$sql = "Select max(`product_option_id`) as `product_option_id` from `".DB_PREFIX."product_option`;";
			$result = $this->db->query( $sql );
			$product_option_id = 0;

			foreach ($result->rows as $row) {
				$product_option_id = (int)$row['product_option_id'];
				$product_option_id++;
			}

			$ProductOption = $this->getProductOption();
			*/

			$pack_number = 1;
			  	
            while ($zip_entry = zip_read($zipArc)) {

                if (zip_entry_open($zipArc, $zip_entry, "r")) {

            		$dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

                    $data_array= json_decode($dump);

					$product_options_data = array(); // Для обработки опций продукта

                    foreach ($data_array as $data){

						$product_ref = $data->{'ref'};

                        $product_id = $data->{'product_id'};

                        if ($product_id == 0) {
                            $product_id_max = $product_id_max + 1;
                            $product_id = $product_id_max;
                            $insert = 1;
                        } else {
                            $insert = 0;
                            $sql_first_delete_path .= ($first_delete_path ) ? "" : ",";
                            $sql_first_delete_path .= " $product_id ";
                            $first_delete_path = false;
                        }
						
                        $categories = $data->{'categories'};
                        $options = $data->{'options'};
                        $attributes= $data->{'attributes'};
                        $filters= $data->{'filters'};
                        $attributes_udal= $data->{'attributes_udal'};
                        $filters_udal= $data->{'filters_udal'};

                        $quantity = $data->{'quantity'};
                        $model = urldecode($this->db->escape($data->{'model'}));
                        $manufacturer_name = urldecode($this->db->escape($data->{'manufacturer_name'}));
                        $manufacturer_image = urldecode($this->db->escape($data->{'manufacturer_image'}));
                        $manufacturer_description = urldecode($this->db->escape($data->{'manufacturer_description'}));
                        $keyword_manufacturer= urldecode($this->db->escape($data->{'keyword_manufacturer'}));
                        $language_id_u= $data->{'language_id_u'};

                        #$image = $data->{'image'};
                        $shipping = $data->{'shipping'};
                        $price = trim($data->{'price'});
                        $points = $data->{'points'};
                        $date_added = $data->{'date_added'};
                        $date_modified = $data->{'date_modified'};
                        $date_available = $data->{'date_available'};
                        $weight = (int)$data->{'weight'};
                        $weight_unit = $data->{'weight_unit'};
                        $status = $data->{'status'};
                        $tax_class_id = $data->{'tax_class_id'};
                        $stock_status_id = $data->{'stock_status_id'};
				
                        $descriptions = $this->object_to_array($data->{'descriptions'});
                        $meta_titles = $this->object_to_array($data->{'meta_titles'});
                        $meta_h1s   = $this->object_to_array($data->{'meta_h1'});
                        $meta_descriptions = $this->object_to_array($data->{'meta_descriptions'});
                        $names = $this->object_to_array($data->{'names'});
                        $meta_keywords = $this->object_to_array($data->{'meta_keywords'});
                        $tags = $this->object_to_array($data->{'tags'});
                        $keywords = $this->object_to_array($data->{'seo_keyword'});
				
                        $length = (int)$data->{'length'};
                        $width = (int)$data->{'width'};
                        $height = (int)$data->{'height'};
	
				        //$sort_order = $data->{'sort_order'};
	
                        $measurement_unit = $data->{'measurement_unit'};
                        $sku = urldecode($this->db->escape($data->{'sku'}));
                        $upc = urldecode($this->db->escape($data->{'upc'}));
                        $ean = urldecode($this->db->escape($data->{'ean'}));
                        $jan = urldecode($this->db->escape($data->{'jan'}));
                        $isbn = urldecode($this->db->escape($data->{'isbn'}));
                        $mpn =  urldecode($this->db->escape($data->{'mpn'}));

                        $location = urldecode($this->db->escape($data->{'location'}));
                        $store_ids = $data->{'store_ids'};
                        $related_ids = $data->{'related_ids'};

                        $layout = $data->{'layout'};
                        $subtract = $data->{'subtract'};
                        $minimum = $data->{'minimum'};

                        if (empty($data->{'specials'})) {
                            $specials_empty = true;
                        }else{
                            $specials_empty = false;
                            $specials = $data->{'specials'};
                        }

                        if (empty($data->{'akcii'})) {
                            $akcii_empty = true;
                        }else{
                            $akcii_empty = false;
                            $akcii = $data->{'akcii'};
                        }
				
                        // extract the product details
                        $weight_class_id = (isset($weight_class_ids[$weight_unit])) ? $weight_class_ids[$weight_unit] : 0;
                        $length_class_id = (isset($length_class_ids[$measurement_unit])) ? $length_class_ids[$measurement_unit] : 0;

                        if ($manufacturer_name) {
                            $this->storeManufacturerIntoDatabase( $manufacturers, $manufacturer_name, $store_ids, $available_store_ids,$keyword_manufacturer,$languages,$language_id_u,$manufacturer_image,$manufacturer_description );
                            $manufacturer_id = $manufacturers[$manufacturer_name]['manufacturer_id'];
                        } else {
                            $manufacturer_id = 0;
                        }

                        if ($pack_number > 1) {
                            $sql_product  .=",";
                        }
				
                        $sql_product  .= "($product_id,$quantity,'$sku','$upc',";
                        $sql_product  .= in_array('ean',$product_fields) ? "'$ean'," : "";
                        $sql_product  .= in_array('jan',$product_fields) ? "'$jan'," : "";
                        $sql_product  .= in_array('isbn',$product_fields) ? "'$isbn'," : "";
                        $sql_product  .= in_array('mpn',$product_fields) ? "'$mpn'," : "";

                        $sql_product  .= "'$location',$stock_status_id,'$model',$manufacturer_id,$shipping,$price,$points,";
                        $sql_product  .= "'$date_added',";
                        $sql_product  .= "'$date_modified',";
                        $sql_product  .= "'$date_available',";
                        $sql_product  .= "$weight,$weight_class_id,$status,";
                        $sql_product  .= "$tax_class_id,$length,$width,$height,'$length_class_id','$subtract','$minimum')";


                        $store_id = 0;

                        foreach ($languages as $language) {

                            $language_code = $language['code'];
                            $language_id = $language['language_id'];

                            $name = isset($names[$language_code]) ? urldecode($this->db->escape($names[$language_code])) : '';
                            $description = isset($descriptions[$language_code]) ? urldecode($this->db->escape($descriptions[$language_code])) : '';
                            if ($exist_meta_title) {
                                $meta_title = isset($meta_titles[$language_code]) ? urldecode($this->db->escape($meta_titles[$language_code])) : '';
                            }
                            if ($exist_meta_h1) {
                                $meta_h1 = isset($meta_h1s[$language_code]) ? urldecode($this->db->escape($meta_h1s[$language_code])) : '';
                            }
                            $meta_description = isset($meta_descriptions[$language_code]) ? urldecode($this->db->escape($meta_descriptions[$language_code])) : '';
                            $meta_keyword = isset($meta_keywords[$language_code]) ? urldecode($this->db->escape($meta_keywords[$language_code])) : '';
                            $tag = isset($tags[$language_code]) ? urldecode($this->db->escape($tags[$language_code])) : '';
					
					        if ($exist_table_product_tag) {
					
                                $sql_product_description  .= ($first_product_description ) ? "" : ",";
                                if ($exist_meta_title) {
                                    $sql_product_description  .= " ( $product_id, $language_id, '$name', '$description', '$meta_title', '$meta_description', '$meta_keyword'";
                                } else {
                                    $sql_product_description  .= " ( $product_id, $language_id, '$name', '$description', '$meta_description', '$meta_keyword'";
                                }
                                if ($exist_meta_h1) {
                                    $sql_product_description  .= ", '$meta_h1'";
                                }
                                $sql_product_description  .= ")";
						
                                if (($seo_update == 1) or ($insert== 1)) {
                                    $sql_product_tag  .= ($first_product_tag  ) ? "" : ",";
                                    $sql_product_tag  .= " ($product_id, $language_id, '$tag') ";
                                    $first_product_tag  = false;
                                }

					        } else {
					
                                $sql_product_description  .= ($first_product_description ) ? "" : ",";
                                if ($exist_meta_title) {
                                    $sql_product_description  .= " ( $product_id, $language_id, '$name', '$description', '$meta_title', '$meta_description', '$meta_keyword', '$tag' ";
                                } else {
                                    $sql_product_description  .= " ( $product_id, $language_id, '$name', '$description',  '$meta_description', '$meta_keyword', '$tag' " ;
                                }
                                if ($exist_meta_h1) {
                                    $sql_product_description  .= ", '$meta_h1'";
                                }
                                $sql_product_description  .= ")";

                                if(isset($keywords[$language_code]) and (($seo_update == 1) or ($insert== 1))){
                                    $keyword= isset($keywords[$language_code]) ? urldecode($this->db->escape($keywords[$language_code])) : '';
                                    if (isset($url_alias_ids[$product_id][$store_id][$language_id ])) {
                                        $url_alias_id = $url_alias_ids[$product_id][$store_id][$language_id];
                                        $sql_url_aliasUPDATE .= ($first_url_aliasUPDATE  ) ? "" : ",";
                                        $sql_url_aliasUPDATE .= " ('$url_alias_id','$keyword','product_id=$product_id',$store_id,$language_id )";
                                        $first_url_aliasUPDATE  = false;
                                    }else{
                                        $sql_url_alias .= ($first_url_alias  ) ? "" : ",";
                                        $sql_url_alias .= " ('$keyword','product_id=$product_id',$store_id,$language_id )";
                                        $first_url_alias  = false;
                                    }
                                }
                            } //if ($exist_table_product_tag)

					        $first_product_description = false;
					
                            foreach ($attributes as $attribute_str) {
                                $attribute_id = $attribute_str->{'attribute_id'};
                                $attribute_lang_id = $attribute_str->{'language'};
                                $text = urldecode($this->db->escape($attribute_str->{'text'}));
                                if ($language_code == $attribute_lang_id) {
                                    $sql_product_attribute .= ($first_product_attribute ) ? "" : ",";
                                    $first_product_attribute = false;
                                    $sql_product_attribute .= " ($product_id,$attribute_id,$language_id,'$text') ";
                                }
                            }

                        } //foreach ($languages as $language)

                        foreach ($filters as $filter_str) {
                            $filter_id= $filter_str->{'filter_id'};
                            $sql_product_filter .= ($first_product_filter ) ? "" : ",";
                            $first_product_filter = false;
                            $sql_product_filter .= " ($product_id,$filter_id) ";
                        }
						
						if ($pack_number > 1) {
							$sql_del_category_id .= ", ";
						}
						$sql_del_category_id .= "$product_id";

                        $countcategories = count($categories);
                        if ($countcategories > 0) {
                            $count_main_category = 1;
                            foreach ($categories as $category_id) {
                                $sql_category_id .= ($first_category_id ) ? "" : ",";
                                $main_category  =  $category_id->{'main'};
                                $category_id_id =  $category_id->{'id'};
                                $sql_category_id .= " ($product_id, $category_id_id, $main_category) ";
                                $first_category_id = false;
                                $count_main_category = $count_main_category + 1;
                            }
                        }

                        foreach ($store_ids as $store_id) {
                            if (in_array((int)$store_id,$available_store_ids)) {
                                $sql_product_to_store .= ($firstsql_product_to_store  ) ? "" : ",";
                                $sql_product_to_store .= " ($product_id,$store_id)";
                                $firstsql_product_to_store  = false;
                            }
                        }

                        $layouts = array();
                        foreach ($layout as $layout_part) {
                            $next_layout = explode(':',$layout_part);
                            if ($next_layout===false) {
                                $next_layout = array( 0, $layout_part );
                            } else if (count($next_layout)==1) {
                                $next_layout = array( 0, $layout_part );
                            }
                            if ( (count($next_layout)==2) && (in_array((int)$next_layout[0],$available_store_ids)) && (is_string($next_layout[1])) ) {
                                $store_id = (int)$next_layout[0];
                                $layout_name = $next_layout[1];
                                if (isset($layout_ids[$layout_name])) {
                                    $layout_id = (int)$layout_ids[$layout_name];
                                    if (!isset($layouts[$store_id])) {
                                        $layouts[$store_id] = $layout_id;
                                    }
                                }
                            }
                        }

                        foreach ($layouts as $store_id => $layout_id) {
                            $sql_product_to_layout .= ($first_product_to_layout ) ? "" : ",";
                            $sql_product_to_layout .= " ($product_id,$store_id,$layout_id)";
                            $first_product_to_layout = false;
                        }

                        if (count($related_ids) > 0) {
                            foreach ($related_ids as $related_id) {
                                $sql_product_related .= ($first_product_related ) ? "" : ",";
                                $first_product_related = false;
                                $sql_product_related .= "($product_id,$related_id)";
                            }
                        }

                        if (count($attributes_udal) > 0) {
                            $sql_del_product_attribute .= ($first_del_product_attribute) ? "" : ",";
                            $first_del_product_attribute= false;
                            $sql_del_product_attribute .= " $product_id ";
                        }

                        if (count($filters_udal) > 0) {
                            $sql_del_product_filter .= ($first_del_product_filter) ? "" : ",";
                            $first_del_product_filter= false;
                            $sql_del_product_filter .= " $product_id ";
                        }

						$pd_opt_data = array();
						$pd_opt_data['ref'] = $product_ref;
						$pd_opt_data['product_id'] = $product_id;
						$pd_opt_data['product_options'] = $options;
						$product_options_data[] = $pd_opt_data;
							
						/*
                        if (!empty($type_option)) {
                            if (count($options) == 0) {
                                $sql_delete_product_option .= ($first_delete_product_option ) ? "" : ",";
                                $sql_delete_product_option .= " $product_id ";
                                $first_delete_product_option = false;
                            } else {

                                $option_id = 0;
                                $option_vid = array();

                                foreach ($options as $option) {
                                    $option_id = $option->{'option_id'};
                                    if (isset($ProductOption[$product_id][$option_id]) ) {
                                        $option_vid[$option_id] = $ProductOption[$product_id][$option_id];
                                    }

                                    if ((!isset($ProductOption[$product_id][$option_id])) and (!isset($option_vid[$option_id]))) {
                                        $sql_product_option .= ($first_product_option ) ? "" : ",";
                                        $sql_product_option .= " ($product_option_id,$product_id,$option_id,'',1) ";
                                        $first_product_option = false;
                                        $product_option_id_t = $product_option_id;
                                        $product_option_id++;
                                        $option_vid[$option_id] = $product_option_id_t;
                                    } else {
                                        $product_option_id_t = $option_vid[$option_id];
                                    }

                                    $option_value_id = $option->{'option_value_id'};
                                    $quantity = $option->{'quantity'};
                                    $subtract = $option->{'subtract'};
                                    $price = $option->{'price'};
                                    $price_prefix = $option->{'price_prefix'};
                                    $points = $option->{'points'};
                                    $points_prefix = $option->{'points_prefix'};
                                    $weight = $option->{'weight'};
                                    $weight_prefix = $option->{'weight_prefix'};

                                    $sql_product_option_value .= ($first_product_option_value ) ? "" : ",";
                                    $sql_product_option_value .= "  ($product_option_id_t, $product_id, $option_id, $option_value_id ,$quantity,$subtract,$price,'$price_prefix',$points,'$points_prefix', $weight,'$weight_prefix') ";

                                    $first_product_option_value = false;
                                } //foreach ($options as $option)
                            } //(count($options) == 0)

                            $sql_del_product_option_value .= ($first_del_product_option_value) ? "" : ",";
                            $sql_del_product_option_value .= " $product_id ";
                            $first_del_product_option_value= false;

                        } //if (!empty($type_option))
						*/

                        $sql_delete_product_special .= ($first_delete_product_special ) ? "" : ",";
                        $sql_delete_product_special .= " $product_id ";
                        $first_delete_product_special= false;
        
                        if (!$specials_empty) {
                            if (count($specials) > 0) {
                                foreach ($specials as $special) {
                                    $customer_group_id= $special->{'customer_group_id'};
                                    $priority= $special->{'priority'};
                                    $date_start= $special->{'date_start'};
                                    $price= $special->{'price'};
                                    $date_end= $special->{'date_end'};

                                    $sql_product_special .= ($first_product_special ) ? "" : ",";
                                    $sql_product_special .= "  ($product_id, $customer_group_id,  $priority, $price,'$date_start','$date_end') ";

                                    $first_product_special = false;
                                }
                            }
                        }

                        $sql_delete_product_discount .= ($first_delete_product_discount ) ? "" : ",";
                        $sql_delete_product_discount .= " $product_id ";
                        $first_delete_product_discount= false;
        
                        if (!$akcii_empty) {
                            if (count($akcii) > 0) {
                                foreach ($akcii as $special) {
                                    $customer_group_id= $special->{'customer_group_id'};
                                    $priority= $special->{'priority'};
                                    $date_start= $special->{'date_start'};
                                    $price= $special->{'price'};
                                    $date_end= $special->{'date_end'};
                                    $quantity= $special->{'quantity'};

                                    $sql_product_discount .= ($first_product_discount ) ? "" : ",";
                                    $sql_product_discount .= "  ($product_id, $customer_group_id, $quantity,  $priority, $price,'$date_start','$date_end') ";

                                    $first_product_discount = false;
                                }
                            }
                        }

                        #$vozvrat_json[$data->{'ref'}] = $product_id;
                        $pack_number++;

                    } //foreach ($data_array as $data) // Конец перебора массива продуктов
                } //if (zip_entry_open($zipArc, $zip_entry, "r"))
            } //while ($zip_entry = zip_read($zipArc))

            //код обновления
            $sql_productDUPLICATE   .= "`quantity`= VALUES(`quantity`),";
            $sql_productDUPLICATE   .= "`sku`= VALUES(`sku`),";
            $sql_productDUPLICATE   .= "`upc`= VALUES(`upc`),";

            $sql_productDUPLICATE   .= in_array('ean',$product_fields) ? "`ean` = VALUES(`ean`)," : "";
            $sql_productDUPLICATE   .= in_array('jan',$product_fields) ? "`jan` = VALUES(`jan`)," : "";
            $sql_productDUPLICATE   .= in_array('isbn',$product_fields) ? "`isbn` = VALUES(`isbn`)," : "";
            $sql_productDUPLICATE   .= in_array('mpn',$product_fields) ? "`mpn` = VALUES(`mpn`)," : "";

            $sql_productDUPLICATE   .= "`location`= VALUES(`location`),";
            $sql_productDUPLICATE   .= "`stock_status_id`= VALUES(`stock_status_id`),";
            $sql_productDUPLICATE   .= "`model`= VALUES(`model`),";
            $sql_productDUPLICATE   .= "`manufacturer_id`= VALUES(`manufacturer_id`),";
            #$sql_productDUPLICATE   .= "`image`= VALUES(`image`),";
            $sql_productDUPLICATE   .= "`shipping`= VALUES(`shipping`),";
            $sql_productDUPLICATE   .= "`price`= VALUES(`price`),";
            $sql_productDUPLICATE   .= "`points`= VALUES(`points`),";

            $sql_productDUPLICATE   .= "`date_added`= '$date_added',";
            $sql_productDUPLICATE   .= "`date_modified`= '$date_modified',";
            $sql_productDUPLICATE   .= "`date_available`= '$date_available',";

            $sql_productDUPLICATE   .= "`weight`= VALUES(`weight`),";
            $sql_productDUPLICATE   .= "`weight_class_id`= VALUES(`weight_class_id`),";
            $sql_productDUPLICATE   .= "`status`= VALUES(`status`),";
            $sql_productDUPLICATE   .= "`tax_class_id`= VALUES(`tax_class_id`),";
            $sql_productDUPLICATE   .= "`length`= VALUES(`length`),";
            $sql_productDUPLICATE   .= "`width`= VALUES(`width`),";
            $sql_productDUPLICATE   .= "`height`= VALUES(`height`),";
            $sql_productDUPLICATE   .= "`length_class_id`= VALUES(`length_class_id`),";
            //$sql_productDUPLICATE   .= "`sort_order`= VALUES(`sort_order`),";
            $sql_productDUPLICATE   .= "`subtract`= VALUES(`subtract`),";
            $sql_productDUPLICATE   .= "`minimum`= VALUES(`minimum`)";

            $sql_product  .=$sql_productDUPLICATE;

            $sql_product  .=";";
            $this->db->query($sql_product);

            if (!$first_delete_product_special ) {
                $sql_delete_product_special .=");";
                $this->db->query($sql_delete_product_special );

                $sql = "Select max(`product_special_id`) as `product_special_id` from `".DB_PREFIX."product_special`;";
                $result = $this->db->query( $sql );
                $product_special_id= 0;

                foreach ($result->rows as $row) {
                    $product_special_id= (int)$row['product_special_id'];
                    $product_special_id++;
                }

                $sql = "ALTER TABLE `".DB_PREFIX."product_special` AUTO_INCREMENT = $product_special_id ;";
                $this->db->query($sql);
            }

            if (!$first_delete_product_discount ) {

                $sql_delete_product_discount .=");";
                $this->db->query($sql_delete_product_discount );

                $sql = "Select max(`product_discount_id`) as `product_discount_id` from `".DB_PREFIX."product_discount`;";
                $result = $this->db->query( $sql );
                $product_discount_id= 0;

                foreach ($result->rows as $row) {
                    $product_discount_id= (int)$row['product_discount_id'];
                    $product_discount_id++;
                }

                $sql = "ALTER TABLE `".DB_PREFIX."product_discount` AUTO_INCREMENT = $product_discount_id;";
                $this->db->query($sql);
            }

            if (!$first_delete_path) {
                $sql_first_delete_path .=");";
                $this->db->query($sql_first_delete_path );
            }

			/*
            if (!$first_delete_product_option) {
                $sql_delete_product_option .=");";
                //$this->db->query($sql_delete_product_option ); //Временно!
				$logger->write("\n" . $sql_delete_product_option . "\n\n");
            }
			*/

            if (!$first_del_product_attribute) {
                $sql_del_product_attribute .=");";
                $this->db->query($sql_del_product_attribute );
            }

            if (!$first_del_product_filter) {
                $sql_del_product_filter .=");";
                $this->db->query($sql_del_product_filter );
            }

            if (!$first_product_attribute) {
                $sql_product_attribute .=";";
                $this->db->query($sql_product_attribute);
            }

            if (!$first_product_filter) {
                $sql_product_filter .=";";
                $this->db->query($sql_product_filter);
            }

			/*
            if (!$first_product_option ) {
                $sql_product_option .=";";
                //$this->db->query($sql_product_option ); //Временно!
				$logger->write("\n" . $sql_product_option . "\n\n");
            }

            if (!$first_del_product_option_value) {
                $sql_del_product_option_value .=");";
                //$this->db->query($sql_del_product_option_value ); //Временно!
				$logger->write("\n" . $sql_del_product_option_value . "\n\n");
            }

            if (!$first_product_option_value ) {
                $sqlproduct_option_value_id = "Select max(`product_option_value_id`) as `product_option_value_id` from `".DB_PREFIX."product_option_value`;";
                $resultproduct_option_value = $this->db->query( $sqlproduct_option_value_id );
                $product_option_idproduct_option_value = 1;

                foreach ($resultproduct_option_value ->rows as $row) {
                    $product_option_idproduct_option_value = (int)$row['product_option_value_id'];
                    $product_option_idproduct_option_value++;
                }
                $sqlproduct_option_value_id = "ALTER TABLE `".DB_PREFIX."product_option_value` AUTO_INCREMENT=$product_option_idproduct_option_value;";
                $this->db->query($sqlproduct_option_value_id);

                $sql_product_option_value .=";";
                //$this->db->query($sql_product_option_value); //Временно!
				$logger->write("\n" . $sql_product_option_value . "\n\n");
            }
			*/

            if (!$first_product_special ) {
                $sql_product_special .=";";
                $this->db->query($sql_product_special );
            }

            if (!$first_product_discount ) {
                $sql_product_discount .=";";
                $this->db->query($sql_product_discount );
            }

            if (!$first_product_description ) {
                $sql_product_descriptionDUPLICATE =" ON DUPLICATE KEY UPDATE  ";
                if ($exist_table_product_tag) {
                    if ($exist_meta_title) {
                        $sql_product_descriptionDUPLICATE.= "`name`= VALUES(`name`)";
                        if ($description_update == 1) {
                            $sql_product_descriptionDUPLICATE.= ",`description`= VALUES(`description`)";
                        }
                        if (($seo_update == 1) or ($insert== 1)) {
                            $sql_product_descriptionDUPLICATE.= ",`meta_title`= VALUES(`meta_title`),";
                            $sql_product_descriptionDUPLICATE.= "`meta_description`= VALUES(`meta_description`),";
                            $sql_product_descriptionDUPLICATE.= "`meta_keyword`= VALUES(`meta_keyword`)";
                        }
                    } else {
                        $sql_product_descriptionDUPLICATE.= "`name`= VALUES(`name`)";
                        if ($description_update == 1) {
                            $sql_product_descriptionDUPLICATE.= ",`description`= VALUES(`description`)";
                        }
                        if (($seo_update == 1) or ($insert== 1)) {
                            $sql_product_descriptionDUPLICATE.= ",`meta_description`= VALUES(`meta_description`),";
                            $sql_product_descriptionDUPLICATE.= "`meta_keyword`= VALUES(`meta_keyword`)";
                        }
                    }
                } else {
                    if ($exist_meta_title) {
                        $sql_product_descriptionDUPLICATE.= "`name`= VALUES(`name`)";
                        if ($description_update == 1) {
                            $sql_product_descriptionDUPLICATE.= ",`description`= VALUES(`description`)";
                        }
                        if (($seo_update == 1) or ($insert== 1)) {
                            $sql_product_descriptionDUPLICATE.= ",`meta_title`= VALUES(`meta_title`),";
                            $sql_product_descriptionDUPLICATE.= "`meta_description`= VALUES(`meta_description`),";
                            $sql_product_descriptionDUPLICATE.= "`meta_keyword`= VALUES(`meta_keyword`),";
                            $sql_product_descriptionDUPLICATE.= "`tag`= VALUES(`tag`)";
                        }
                    } else {
                        $sql_product_descriptionDUPLICATE.= "`name`= VALUES(`name`)";
                        if ($description_update == 1) {
                            $sql_product_descriptionDUPLICATE.= ",`description`= VALUES(`description`)";
                        }
                        if (($seo_update == 1) or ($insert== 1)) {
                            $sql_product_descriptionDUPLICATE.= ",`meta_description`= VALUES(`meta_description`),";
                            $sql_product_descriptionDUPLICATE.= "`meta_keyword`= VALUES(`meta_keyword`),";
                            $sql_product_descriptionDUPLICATE.= "`tag`= VALUES(`tag`)";
                        }
                    }
                }
                if ($exist_meta_h1) {
                    if (($seo_update == 1) or ($insert== 1)) {
                        $sql_product_descriptionDUPLICATE.= ",`meta_h1`= VALUES(`meta_h1`)";
                    }
                }
                $sql_product_description  .=$sql_product_descriptionDUPLICATE;
                $sql_product_description  .=";";
                $this->db->query($sql_product_description  );
            }

            if (!$first_product_tag  ) {
                $sql_product_tag  .=" ON DUPLICATE KEY UPDATE  ";
                $sql_product_tag  .= "`tag`= VALUES(`tag`)";
                $sql_product_tag  .=";";
                $this->db->query($sql_product_tag  );
            }

            if (!$first_category_id) {
                
				$sql_del_category_id .= ")";
				$this->db->query($sql_del_category_id);
				
				$sql_category_id .=";";
                $this->db->query($sql_category_id );
            }

            if (!$first_url_aliasUPDATE  ) {
                $sql_url_aliasUPDATE .=" ON DUPLICATE KEY UPDATE  ";
                $sql_url_aliasUPDATE .= "`keyword`= VALUES(`keyword`)";
                $sql_url_aliasUPDATE .=";";
                $this->db->query($sql_url_aliasUPDATE );
            }

            if (!$first_url_alias  ) {
                $sql_url_alias .=";";
                $this->db->query($sql_url_alias );
            }

            if (!$firstsql_product_to_store  ) {
                $sql_product_to_store .=" ON DUPLICATE KEY UPDATE  ";
                $sql_product_to_store .= "`store_id`= VALUES(`store_id`)";
                $sql_product_to_store .=";";
                $this->db->query($sql_product_to_store );
            }

            if (!$first_product_to_layout ) {
                $sql_product_to_layout .=";";
                $this->db->query($sql_product_to_layout );
            }

            if (!$first_product_related) {
                $sql_product_related .=";";
                $this->db->query($sql_product_related );
            }

            $sql = "TRUNCATE TABLE `".DB_PREFIX."category_filter`;";
            $this->db->query( $sql );

            $sql = "Select product_to_category.category_id,product_filter.filter_id from `".DB_PREFIX."product_to_category` as product_to_category inner join `".DB_PREFIX."product_filter` as product_filter on product_filter.product_id = product_to_category .product_id GROUP BY product_to_category.category_id,product_filter.filter_id;";
            $result = $this->db->query( $sql );

            $first_category_filter = true;

            $sql_category_filter = "INSERT INTO `".DB_PREFIX."category_filter` (`category_id`,`filter_id`) VALUES ";

            foreach ($result->rows as $row) {
                $category_id= (int)$row['category_id'];
                $filter_id  = (int)$row['filter_id'];

                $sql_category_filter .= ($first_category_filter ) ? "" : ",";
                $sql_category_filter .= "  ($category_id, $filter_id ) ";
                $first_category_filter = false;
            }

            if (!$first_category_filter ) {
                $sql_category_filter .=";";
                $this->db->query($sql_category_filter );
            }

			$product_options_result = $this->addOrDeleteProductOptions($product_options_data);

            #$json['success'] = $vozvrat_json ;
			$json_return['success'] = $product_options_result ;

            zip_close($zipArc);
            unlink($nameZip);

        }//if (is_resource($zipArc))

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json_return));

    }//function add()

	protected function object_to_array($data) {

    	if (is_array($data) || is_object($data)) {
        	
			$result = array();
        	
			foreach ($data as $key => $value) {
            	$result[$key] = $this->object_to_array($value);
        	}
       		
			return $result;
   		}
    	
		return $data;
	}

	protected function getLanguages() {
		$query = $this->db->query( "SELECT * FROM `".DB_PREFIX."language` WHERE `status`=1 ORDER BY `code`" );
		return $query->rows;
	}
	
	protected function getAvailableStoreIds() {
		
		$sql = "SELECT store_id FROM `".DB_PREFIX."store`;";
		$result = $this->db->query( $sql );
		$store_ids = array(0);
		foreach ($result->rows as $row) {
			if (!in_array((int)$row['store_id'],$store_ids)) {
				$store_ids[] = (int)$row['store_id'];
			}
		}
		return $store_ids;
	}
	
	protected function getLayoutIds() {
		$result = $this->db->query( "SELECT * FROM `".DB_PREFIX."layout`" );
		$layout_ids = array();
		foreach ($result->rows as $row) {
			$layout_ids[$row['name']] = $row['layout_id'];
		}
		return $layout_ids;
	}
	
	protected function getManufacturers() {
		// find all manufacturers already stored in the database
		$manufacturer_ids = array();
		$sql  = "SELECT ms.manufacturer_id, ms.store_id, m.`name` FROM `".DB_PREFIX."manufacturer_to_store` ms ";
		$sql .= "INNER JOIN `".DB_PREFIX."manufacturer` m ON m.manufacturer_id=ms.manufacturer_id";
		$result = $this->db->query( $sql );
		$manufacturers = array();
		foreach ($result->rows as $row) {
			$manufacturer_id = $row['manufacturer_id'];
			$store_id = $row['store_id'];
			$name = $row['name'];
			if (!isset($manufacturers[$name])) {
				$manufacturers[$name] = array();
			}
			if (!isset($manufacturers[$name]['manufacturer_id'])) {
				$manufacturers[$name]['manufacturer_id'] = $manufacturer_id;
			}
			if (!isset($manufacturers[$name]['store_ids'])) {
				$manufacturers[$name]['store_ids'] = array();
			}
			if (!in_array($store_id,$manufacturers[$name]['store_ids'])) {
				$manufacturers[$name]['store_ids'][] = $store_id;
			}
		}
		return $manufacturers;
	}
	
	protected function getWeightClassIds() {
		// find the default language id
		$language_id = $this->getDefaultLanguageId();
		
		// find all weight classes already stored in the database
		$weight_class_ids = array();
		$sql = "SELECT `weight_class_id`, `unit` FROM `".DB_PREFIX."weight_class_description` WHERE `language_id`=$language_id;";
		$result = $this->db->query( $sql );
		if ($result->rows) {
			foreach ($result->rows as $row) {
				$weight_class_id = $row['weight_class_id'];
				$unit = $row['unit'];
				if (!isset($weight_class_ids[$unit])) {
					$weight_class_ids[$unit] = $weight_class_id;
				}
			}
		}

		return $weight_class_ids;
	}
		
	protected function getLengthClassIds() {
		// find the default language id
		$language_id = $this->getDefaultLanguageId();
		
		// find all length classes already stored in the database
		$length_class_ids = array();
		$sql = "SELECT `length_class_id`, `unit` FROM `".DB_PREFIX."length_class_description` WHERE `language_id`=$language_id;";
		$result = $this->db->query( $sql );
		if ($result->rows) {
			foreach ($result->rows as $row) {
				$length_class_id = $row['length_class_id'];
				$unit = $row['unit'];
				if (!isset($length_class_ids[$unit])) {
					$length_class_ids[$unit] = $length_class_id;
				}
			}
		}

		return $length_class_ids;
	}

	protected function getProductUrlAliasIds() {
		$sql  = "SELECT url_alias_id, SUBSTRING( query, CHAR_LENGTH('product_id=')+1 ) AS product_id ";
		$sql .= "FROM `".DB_PREFIX."url_alias` ";
		$sql .= "WHERE query LIKE 'product_id=%'";
		$query = $this->db->query( $sql );
		$url_alias_ids = array();
		foreach ($query->rows as $row) {
			$url_alias_id = $row['url_alias_id'];
			$product_id = $row['product_id'];
			$url_alias_ids[$product_id] = $url_alias_id;
		}
		return $url_alias_ids;
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

	protected function getCategorysSEOKeywords() {
		$sql  = "SELECT * FROM `".DB_PREFIX."seo_url` ";
		$sql .= "WHERE query LIKE 'category_id=%' ";
		$query = $this->db->query( $sql );
		$seo_keywords = array();
		foreach ($query->rows as $row) {
			$category_id= (int)substr($row['query'],12);
			$store_id = $row['store_id'];
			$language_id = $row['language_id'];			
			$url_alias_id = $row['seo_url_id'];
			
			$seo_keywords[$category_id][$store_id][$language_id] = $url_alias_id;
		}
		
		return $seo_keywords;
	}

	protected function getProductSEOKeywords() {
		$sql  = "SELECT * FROM `".DB_PREFIX."seo_url` ";
		$sql .= "WHERE query LIKE 'product_id=%' ";
		$query = $this->db->query( $sql );
		$seo_keywords = array();
		foreach ($query->rows as $row) {
			$product_id= (int)substr($row['query'],11);
			$store_id = $row['store_id'];
			$language_id = $row['language_id'];			
			$url_alias_id = $row['seo_url_id'];
			
			$seo_keywords[$product_id][$store_id][$language_id] = $url_alias_id;
		}
		
		return $seo_keywords;
	}
  
	protected function getProductOption() {
		$sql  = "SELECT * FROM `".DB_PREFIX."product_option` ";
		$query = $this->db->query( $sql );
		$seo_keywords = array();
		foreach ($query->rows as $row) {
			$product_id= $row['product_id'];
			$option_id= $row['option_id'];
      
			$product_option_id= $row['product_option_id'];
			
			$seo_keywords[$product_id][$option_id] = $product_option_id;
		}
		
		return $seo_keywords;
	}	
	
	protected function storeManufacturerIntoDatabase( &$manufacturers, $name, &$store_ids, &$available_store_ids,$keyword_manufacturer, &$language_ids, $language_id_u,&$manufacturer_image,&$manufacturer_description ) {
		foreach ($store_ids as $store_id) {
			if (!in_array( $store_id, $available_store_ids )) {
				continue;
			}
			if (!isset($manufacturers[$name]['manufacturer_id'])) {
				
				$name_name = $this->db->escape($name);
				$this->db->query("INSERT INTO ".DB_PREFIX."manufacturer SET name = '".$this->db->escape($name)."', image='$manufacturer_image', sort_order = '0'");
				$manufacturer_id = $this->db->getLastId();
				if (!isset($manufacturers[$name])) {
					$manufacturers[$name] = array();
				}
				
				$manufacturers[$name]['manufacturer_id'] = $manufacturer_id;
                
      		}else{
      
         		$manufacturer_id = $manufacturers[$name]['manufacturer_id'];
         		$this->db->query("INSERT INTO ".DB_PREFIX."manufacturer  (`manufacturer_id`,`image`)  VALUES ($manufacturer_id,'$manufacturer_image')  ON DUPLICATE KEY UPDATE `image`= VALUES(`image`); ");
      
      		}
      
      		$manufacturer_description = false;
      
      		//$query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = DATABASE() AND table_name  = '".DB_PREFIX."manufacturer_description';";
			//$result = $this->db->query($query);
			 
			$sql= "SHOW COLUMNS FROM `".DB_PREFIX."manufacturer_description` LIKE 'name'";
			$query = $this->db->query( $sql );
			$exist_name = ($query->num_rows > 0) ? true : false;
				
			$sql= "SHOW COLUMNS FROM `".DB_PREFIX."manufacturer_description` LIKE 'meta_h1'";
			$query = $this->db->query( $sql );
			$exist_meta_h1 = ($query->num_rows > 0) ? true : false;
			 
			//if (count($result->rows)==1) { 
   				//		$manufacturer_description = true;
			//	}

      
      		foreach ($language_ids as $language) {
					
				$language_code = $language['code'];
				$language_id = $language['language_id'];
				
				if ($language_code == $language_id_u) {
				
					$this->db->query("INSERT INTO `".DB_PREFIX."seo_url` (`query`,`keyword`, `store_id`,`language_id`) VALUES ( 'manufacturer_id=$manufacturer_id','$keyword_manufacturer',$store_id,$language_id)");
					if ($manufacturer_description) {
							
						$sql= "INSERT INTO `".DB_PREFIX."manufacturer_description` (`language_id`,`manufacturer_id`,`description`,`meta_description`,`meta_keyword`,`meta_title`";
							
						if ($exist_name) {	
							$sql.=",`name` ";
						}
						
						if ($exist_meta_h1) {	
							$sql.=",`meta_h1` ";
						}
							
						$sql.=") VALUES ($language_id, $manufacturer_id, '$name_name','$name_name','$name_name','$name_name' ";
						
						if ($exist_name) {	
							$sql.=",'$name_name' ";
						}
						
						if ($exist_meta_h1) {	
							$sql.=",'$name_name' ";
						}
						
						$sql.=")";
							
						$this->db->query($sql);
						
						
					}
				}
			}
            
			if (!isset($manufacturers[$name]['store_ids'])) {
				$manufacturers[$name]['store_ids'] = array();
			}

			if (!in_array($store_id,$manufacturers[$name]['store_ids'])) {
				$manufacturer_id = $manufacturers[$name]['manufacturer_id'];
				$sql = "INSERT INTO `".DB_PREFIX."manufacturer_to_store` SET manufacturer_id='".(int)$manufacturer_id."', store_id='".(int)$store_id."'";
				$this->db->query( $sql );
				$manufacturers[$name]['store_ids'][] = $store_id;
			}
		}
	}

	protected function addOrRepairCategoryPath($category_id, $parent_id) {

		// Редактирование путей для существующей категории:
		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE path_id = '" . (int)$category_id . "' ORDER BY level ASC");

		if ($query->rows) {
			foreach ($query->rows as $category_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' AND level < '" . (int)$category_path['level'] . "'");

				$path = array();

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_path['category_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', level = '" . (int)$level . "'");
		}
	}

	protected function getMaxProductOptionId() {

		$sql = "Select max(`product_option_id`) as `product_option_id` from `".DB_PREFIX."product_option`;";
		$result = $this->db->query( $sql );
		$product_option_id = 0;
		
		foreach ($result->rows as $row) {
			$product_option_id = (int)$row['product_option_id'];
			//$product_option_id++;
		}

		return $product_option_id;

	}

	protected function getMaxProductOptionValueId() {

		$sql_product_option_value_id = "Select max(`product_option_value_id`) as `product_option_value_id` from `".DB_PREFIX."product_option_value`;";
		$result_product_option_value = $this->db->query( $sql_product_option_value_id );
		$product_option_value_id = 1;
		
		foreach ($result_product_option_value->rows as $row) {
			$product_option_value_id = (int)$row['product_option_value_id'];
			//$product_option_idproduct_option_value++;
		}
		
		return $product_option_value_id;
		
	}

	protected function addOrDeleteProductOptions($product_options_data) {

		/*
			ВАЖНО!
			1. Алгоритм написан исходя из того, что в 1С у товара может быть только 1 группа опций (группа опций OpenCart = категории номенклатуры 1С).
			Соответственно, для каждой опции товара значения "option_id", "product_option_id" будет одно и то же.
			2. Если в админке OpenCart товару назначили дополнительные группы опций и сами опции - они будут удалены.
			Останутся только те, что выбраны в карточке товара в 1С. 
		*/
		
		//$logger = new Log('addOrDeleteProductOptions.log');
		
		//$logger->write("\n" . json_encode($product_options_data) . "\n\n");

		$return_data_arr = array();
		
		$sql_product_option = "INSERT INTO `".DB_PREFIX."product_option` (`product_option_id`,`product_id`,`option_id`,`value`,`required`) VALUES ";
		 
		$sql_product_option_value = "INSERT INTO `".DB_PREFIX."product_option_value` 
		(`product_option_value_id`,
		`product_option_id`,
		`product_id`,
		`option_id`,
		`option_value_id`,
		`quantity`,
		`subtract`,
		`price`,
		`price_prefix`,
		`points`,
		`points_prefix`,
		`weight`,
		`weight_prefix`) VALUES ";
		
		
		foreach ($product_options_data as $pd_data) {
			
			$max_pd_option_id = $this->getMaxProductOptionId();
			$max_pd_option_id++;

			$max_pd_option_value_id = $this->getMaxProductOptionValueId();
			$max_pd_option_value_id++;

			$first_product_option_value = true;
			$sql_product_option_value_part2 = "";
		
			$return_product_data = array();
			$return_options = array();
		
			$pd_options_not_delete = array(); // Удаляем все опции кроме тех, что в массиве
		
			$product_id = $pd_data['product_id'];
			$product_ref = $pd_data['ref'];
			$options = $pd_data['product_options'];

			$return_product_data['product_id'] = $product_id;
			$return_product_data['ref'] = $product_ref;
			
			$options_quantity = count($options);

			if ($options_quantity == 0) {
				
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		
			} else {
				
				$sql_product_option_value_delete = "DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "' AND product_option_value_id NOT IN (";
		
				foreach ($options as $option_data) {

					$return_option_data = array();
					
					$option_ref = $option_data->{'ref'};
					
					$option_id = $option_data->{'option_id'};
					$product_option_id = $option_data->{'product_option_id'};
					
					if ($product_option_id == 0) {
						$product_option_id = $pd_options_not_delete[$option_id];
						
						if (!isset($product_option_id)) {
							$product_option_id = $max_pd_option_id;
							$max_pd_option_id++;	
						}
					}

					$pd_options_not_delete[$option_id] = $product_option_id;
					
					$option_value_id = $option_data->{'option_value_id'};
					$product_option_value_id = $option_data->{'product_option_value_id'};
		
					if ($product_option_value_id == 0) {
						$product_option_value_id = $max_pd_option_value_id;
						$max_pd_option_value_id++;
					}
					
					$quantity = $option_data->{'quantity'};
					$subtract = $option_data->{'subtract'};
					$price = $option_data->{'price'};
					$price_prefix = $option_data->{'price_prefix'};
					$points = $option_data->{'points'};
					$points_prefix = $option_data->{'points_prefix'};
					$weight = $option_data->{'weight'};
					$weight_prefix = $option_data->{'weight_prefix'};
		
					$sql_product_option_value_part2 .= ($first_product_option_value) ? "" : ",";
					$sql_product_option_value_part2 .= " ($product_option_value_id, $product_option_id, $product_id, $option_id, $option_value_id ,$quantity,$subtract,$price,'$price_prefix',$points,'$points_prefix', $weight,'$weight_prefix') ";
					
					$sql_product_option_value_delete .= ($first_product_option_value) ? "" : ",";
					$sql_product_option_value_delete .= $product_option_value_id;
									
					$first_product_option_value = false;
					
					///////////////////////////////////////////////////////////////////////////////
					$return_option_data['ref'] = $option_ref;
					$return_option_data['product_option_id'] = $product_option_id;
					$return_option_data['product_option_value_id'] = $product_option_value_id;
					$return_options[] = $return_option_data;
				} // Цикл по опциям конкретного товара
		
				$sql_product_option_delete = "DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND product_option_id NOT IN (";
		
				// Удаление и добавление групп опций товара:
				$first_product_option = true;
				$sql_product_option_part2 = "";
				
				foreach ($pd_options_not_delete as $opt_id => $pd_opt_id) {
					$sql_product_option_part2 .= ($first_product_option) ? "" : ",";
					$sql_product_option_part2 .= " ($pd_opt_id, $product_id, $opt_id, '', 1) ";
					
					$sql_product_option_delete .= $pd_opt_id;
					$sql_product_option_delete .= ($first_product_option) ? "" : ",";
					
					$first_product_option = false;	
				}
				
				$sql_product_option_delete .= ");";
				
				$sql_product_option_part2 .= " ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);";
				$sql_product_option_result = $sql_product_option . $sql_product_option_part2;
				
				if (!$first_product_option) {
				
					$this->db->query($sql_product_option_result); 
					//$logger->write("\n" . $sql_product_option_result . "\n\n");
		
					$this->db->query($sql_product_option_delete);
					//$logger->write("\n" . $sql_product_option_delete . "\n\n");
		
				}
				////////////////////////////////////////////////////////////////////
		
				// Удаление и добавление значений опций товаров
				$sql_product_option_value_result = $sql_product_option_value . $sql_product_option_value_part2;
				$sql_product_option_value_result .= " ON DUPLICATE KEY UPDATE `quantity` = VALUES(`quantity`) , `price` = VALUES(`price`);";
		
				$sql_product_option_value_delete .= ");";
		
				if (!$first_product_option_value) {
				
					$this->db->query($sql_product_option_value_delete);
					//$logger->write("\n" . $sql_product_option_value_delete . "\n\n");
					
					$this->db->query($sql_product_option_value_result);
					//$logger->write("\n" . $sql_product_option_value_result . "\n\n");
		
				}

			}

			$return_product_data['product_options'] = $return_options;
			$return_data_arr[] = $return_product_data;
		
		}

		return $return_data_arr;
		
	}

	public function option_value_add() {
		
		$this->load->language('api/option_value_add');

		$json = array();

		$vozvrat_json = array();
			
        $image_f = file_get_contents('php://input');

	    $nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';
			
	    file_put_contents($nameZip, $image_f);
			
    	$zipArc = zip_open($nameZip);
			
	    if (is_resource($zipArc)) {
			
			$languages = $this->getLanguages();
	
			$first_option_value = true;
			$sql_option_value = "INSERT INTO `".DB_PREFIX."option_value` (`option_value_id`, `option_id`, `image`, `sort_order`) VALUES ";
			
			$first_option_value_description = true;			
			$sql_option_value_description = "INSERT INTO `".DB_PREFIX."option_value_description` (`option_value_id`, `language_id`, `option_id`,`name`) VALUES ";
	
			$sql = "Select max(`option_value_id`) as `option_value_id` from `".DB_PREFIX."option_value`;";
			$result = $this->db->query( $sql );
			$option_value_id_max = 0;
			foreach ($result->rows as $row) {				
				$option_value_id_max = (int)$row['option_value_id'];				
			}
			
			$option_id= 0;

			while ($zip_entry = zip_read($zipArc)) {

			  	if (zip_entry_open($zipArc, $zip_entry, "r")) {
			  			
			        $dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			
			        $options_array= json_decode($dump);
			
        			foreach ($options_array as $option) {
			
                        $option_id = $option->{'option_id'};
				        $option_value_id = $option->{'option_value_id'};

				        $names = (array) $option->{'name'};

				        $image = $option->{'image'};

				        $sort_order = $option->{'sort_order'};

                        if ($option_value_id == 0) {

                            $option_value_id_max = $option_value_id_max + 1;
                            $option_value_id = $option_value_id_max ;
                            $insert = 1;

                        }else {

                            $insert = 0;

                        };
			
                        $sql_option_value .= ($first_option_value) ? "" : ",";
                        $sql_option_value .= " ( $option_value_id, $option_id, '$image', $sort_order ) ";

                        $first_option_value = false;
				
                        foreach ($languages as $language) {

                            $language_code = $language['code'];
                            $language_id = $language['language_id'];

                            $name = isset($names[$language_code]) ? urldecode($this->db->escape($names[$language_code])) : '';

                            $sql_option_value_description .= ($first_option_value_description) ? "" : ",";
                            $sql_option_value_description .= " ( $option_value_id, $language_id, $option_id, '$name') ";

                            $first_option_value_description = false;

                        }
			
                        $vozvrat_json[$option->{'ref'}]= $option_value_id;

			        }
	            }
	        }
	
			if (!$first_option_value) {								
			
				$sql_option_value .=" ON DUPLICATE KEY UPDATE  ";
				$sql_option_value .= "`option_id`= VALUES(`option_id`),";
				$sql_option_value .= "`image`= VALUES(`image`),";
				$sql_option_value .= "`sort_order`= VALUES(`sort_order`)";
												
				$sql_option_value .=";";
				$this->db->query($sql_option_value);
			}
				
			if (!$first_option_value_description ) {								
			
				$sql_option_value_description .=" ON DUPLICATE KEY UPDATE  ";
				$sql_option_value_description .= "`option_id`= VALUES(`option_id`),";
				$sql_option_value_description .= "`name`= VALUES(`name`)";
												
				$sql_option_value_description .=";";
				$this->db->query($sql_option_value_description );
			}
	
			zip_close($zipArc);
			unlink($nameZip);
			$json['success'] = $vozvrat_json ;			

	    } else {

		    $json['error'] = 'zip error option add' ;

	    }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

	}

	public function option_add() {

		$this->load->language('api/option_add');

		$json = array();

		$vozvrat_json = array();

        $image_f = file_get_contents('php://input');

	    $nameZip = DIR_CACHE . $this->request->get['nameZip'].'.zip';

	    file_put_contents($nameZip, $image_f);

    	$zipArc = zip_open($nameZip);

	    if (is_resource($zipArc)) {

			$languages = $this->getLanguages();

			$first_option = true;
			$sql_option = "INSERT INTO `".DB_PREFIX."option` (`option_id`, `type`, `sort_order`) VALUES ";

			$first_option_description = true;
			$sql_option_description = "INSERT INTO `".DB_PREFIX."option_description` (`option_id`, `language_id`, `name`) VALUES ";

			$sql = "Select max(`option_id`) as `option_id` from `".DB_PREFIX."option`;";
			$result = $this->db->query( $sql );
			$option_id_max = 0;
			foreach ($result->rows as $row) {
				$option_id_max = (int)$row['option_id'];
			}

			$option_id= 0;

			while ($zip_entry = zip_read($zipArc)) {

			  	if (zip_entry_open($zipArc, $zip_entry, "r")) {

			        $dump = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

			        $options_array= json_decode($dump);

        			foreach ($options_array as $option) {

                        $option_id = $option->{'option_id'};

				        $names = (array) $option->{'name'};

				        $sort_order = $option->{'sort_order'};

				        $option_type = 'select';

                        if ($option_id == 0) {

                            $option_id_max = $option_id_max + 1;
                            $option_id = $option_id_max ;
                            $insert = 1;

                        } else {

                            $insert = 0;

                        };

                        $sql_option .= ($first_option) ? "" : ",";
                        $sql_option .= " ( $option_id, '$option_type', $sort_order ) ";

                        $first_option = false;

                        foreach ($languages as $language) {

                            $language_code = $language['code'];
                            $language_id = $language['language_id'];

                            $name = isset($names[$language_code]) ? urldecode($this->db->escape($names[$language_code])) : '';

							$query_desc =
							"SELECT * FROM " . DB_PREFIX . "option_description WHERE option_id = '" . $option_id . "' AND language_id = '" .  $language_id ."'";
							
							$result_desc = $this->db->query($query_desc);
							$rows_desc = $result_desc->rows;

							if (count($rows_desc) > 0 ) {
								continue;
							} else {
								$sql_option_description .= ($first_option_description) ? "" : ",";
								$sql_option_description .= " ( $option_id, $language_id, '$name') ";
	
								$first_option_description = false;
							}
                        }

                        $vozvrat_json[$option->{'ref'}]= $option_id;

			        }
	            }
	        }

			if (!$first_option) {

				$sql_option .=" ON DUPLICATE KEY UPDATE  ";
				$sql_option .= "`type`= VALUES(`type`),";
				$sql_option .= "`sort_order`= VALUES(`sort_order`)";

				$sql_option .=";";
				$this->db->query($sql_option);
			}

			if (!$first_option_description ) {

				$sql_option_description .=" ON DUPLICATE KEY UPDATE  ";
				$sql_option_description .= "`name`= VALUES(`name`)";

				$sql_option_description .=";";
				$this->db->query($sql_option_description );
			}

			zip_close($zipArc);
			unlink($nameZip);
			$json['success'] = $vozvrat_json ;

	    } else {

		    $json['error'] = 'zip error option add' ;

	    }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

	}

	public function deleteOptions() {
		
		$this->load->language('api/deleteOptions');

		$json = array();

        $sql = "TRUNCATE TABLE `".DB_PREFIX."option_value`;";
        $this->db->query( $sql );
        $sql = "TRUNCATE TABLE `".DB_PREFIX."option_value_description`;";
        $this->db->query( $sql );

        $sql = "TRUNCATE TABLE `".DB_PREFIX."product_option`;";
        $this->db->query( $sql );

        $sql = "TRUNCATE TABLE `".DB_PREFIX."product_option_value`;";
        $this->db->query( $sql );

        $json['success'] = 'Options have been deleted' ;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

	}
	
    public function get_option() {
		
        $this->load->language('api/get_option');

        $json = array();

        $result = null;

        $languages = $this->getLanguages();

        $language_id_u = (int)$this->request->post['param'];

        foreach ($languages as $language) {
				
            $language_code = $language['code'];
            $language_id = $language['language_id'];
									
            if ($language_code == $language_id_u) {
            
                $sql = "SELECT oc_option.option_id,oc_option_desc.name FROM `".DB_PREFIX."option` as oc_option ";
                $sql .= " inner JOIN `".DB_PREFIX."option_description` as oc_option_desc on oc_option_desc.option_id = oc_option.option_id and oc_option_desc.language_id = $language_id ;";

                $query = $this->db->query($sql);

                $result = $query->rows;

			}

		}
    
		$json['success'] =  $result;
			
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

	}

}