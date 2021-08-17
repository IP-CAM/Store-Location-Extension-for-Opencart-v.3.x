<?php
class ModelExtensionModuleSjcateoryproductpdf extends Model {
public function getSjcateoryproductpdfByCategoryId($category_id,$dproduct, $data = array()) {
  
if($dproduct){
    $where = ' ';
}else{
    $where = 'AND p.status = 1';
}
	$sql = "
			SELECT pd.name,p2m.name as model,p.price,p.image,pd.description as sjdesc
			FROM " . DB_PREFIX . "product p 
				LEFT JOIN " . DB_PREFIX . "product_description pd 
					ON (p.product_id = pd.product_id)
				LEFT JOIN " . DB_PREFIX . "product_to_category p2c 
					ON (p.product_id = p2c.product_id)  
                             LEFT JOIN " . DB_PREFIX . "manufacturer p2m 
					ON (p.manufacturer_id = p2m.manufacturer_id)            
			WHERE 
				pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
				AND p2c.category_id = '" . (int)$category_id . "'
                                $where   
		"; 
	
		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$sql .= " AND LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%'";
		}

		if (isset($data['filter_model']) && !is_null($data['filter_model'])) {
			$sql .= " AND LCASE(p.model) LIKE '%" . $this->db->escape(strtolower($data['filter_model'])) . "%'";
		}
		
		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity >= '" . $this->db->escape($data['filter_quantity']) . "'";
		}
		
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
			'p.status',
			'p.sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY p.date_added";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " DESC";
		}
	


	if(isset($data['limit'])){
			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . 0 . "," . (int)$data['limit'];
		}	 
		
	//var_dump($sql);
		$query = $this->db->query($sql);
								  
		return $query->rows;
                
                
                
	}  
        
        
        
        public function getSjcateoryproductpdfByCategoryName($category_id) {

		$sql = "
			SELECT category_id,name
			FROM " . DB_PREFIX . "category_description 
			WHERE category_id = '" . (int)$category_id . "' 
		"; 
		 
		
	//var_dump($sql);
		$query = $this->db->query($sql);
								  
		return $query->rows;
                
                
                
	}  
       
}
