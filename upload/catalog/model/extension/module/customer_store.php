<?php
class ModelExtensionModuleCustomerStore extends Controller {
	public function getCustomerStores($store_id) {
		
			$query = $this->db->query("SELECT filename, mask FROM `" . DB_PREFIX . "customer_store`  WHERE customer_id = '" . (int)$this->customer->getId() . "'");

			return $query->row;
		
	}

	public function getCustomerStore($customer_id) {
		
        $query = $this->db->query("SELECT DISTINCT store_id, url , filename FROM `" . DB_PREFIX . "customer_store` WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->rows;
    
}

	public function getTotalCustomerStores() {
	
			$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_store`  WHERE customer_id = '" . (int)$this->customer->getId() . "'");

			return $query->row['total'];
		
	}

	public function addCustomerStore($data) {
		$mask= sha1(uniqid(mt_rand(), true));
        $filename =  token(32).'-'.t5f_sanitize_filename($data['url']);
        $output  = '' . "\n";
         $output .= 'feasso-brasil'.$filename;
        $file = fopen(DIR_DOWNLOAD . $filename .'feasso-brasil.html', 'w');
        $filename_html = $filename .'feasso-brasil.html';
 
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_store SET customer_id = '" . (int)$this->customer->getId() . "', url = '" . $this->db->escape($data['url']) . "', filename = '" . $this->db->escape($filename_html) . "', mask = '" . $this->db->escape($mask) . "',   date_added = NOW()");
       // $this->db->query("INSERT INTO " . DB_PREFIX . "customer_store SET filename = '" . $this->db->escape($filename_html) . "', mask = '" . $this->db->escape($mask) . "', url = '" . $this->db->escape($url) . "', customer_id = '" . (int)$this->customer->getId() . "', date_added = NOW()");

      
        fwrite($file, $output);
        fclose($file);


	}
    public function GetCustomerStoreByUrl($url){
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "customer_store` WHERE LCASE(`url`) = '" . $this->db->escape($url) . "'");

		return $query->row;
	}

    public function checkUrl($store_id){

        $query = $this->db->query("SELECT url, filename FROM `" . DB_PREFIX . "customer_store`  WHERE store_id = '" . (int)$store_id . "'");
        foreach ($query->rows as $result) {
          $url = $result['url'].$result['filename'];
          print_r($url);
            if($this->check_file_exist($url)){
                echo "file not found";
            $this->db->query("UPDATE " . DB_PREFIX . "customer_store SET status = '1' WHERE store_id = '" . (int)$store_id . "'");
            } else {
                echo "file not found";

            }

        }    
       
		//$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "customer_store` WHERE LCASE(`url`) = '" . $this->db->escape($url) . "'");

		return $query->row;
	}

    public function check_file_exist($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
     
        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        curl_close($ch);
        return $status;
    }
}
