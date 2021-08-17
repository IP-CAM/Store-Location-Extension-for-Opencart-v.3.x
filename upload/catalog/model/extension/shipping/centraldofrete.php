<?php
class ModelExtensionShippingCentraldofrete extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/centraldofrete');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_centraldofrete_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_centraldofrete_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
		
		$stotal = str_replace(',' , '.', $this->cart->getTotal());
		$medidas = array();
		
		
		foreach ($this->cart->getProducts() as $product) {
			if ($product['shipping']) {
				
			$medidas[] = array(
				'quantity' => $product['quantity'], 
				'width' =>  number_format($this->length->convert(str_replace(',' , '.', $product['width']), $product['length_class_id'], $this->config->get('config_length_class_id')), 2, '.', ''), 
				'height' => number_format($this->length->convert(str_replace(',' , '.', $product['height']), $product['length_class_id'], $this->config->get('config_length_class_id')), 2, '.', ''), 
				'length' => number_format($this->length->convert(str_replace(',' , '.', $product['length']), $product['length_class_id'], $this->config->get('config_length_class_id')), 2, '.', ''), 
				'weight' => $this->weight->convert(str_replace(',' , '.', $product['weight']), $product['weight_class_id'], $this->config->get('config_weight_class_id'))
			);

			}
		}
		
		$postcode = preg_replace("/[^0-9]/", "", $address['postcode']);
		$postcode2 = preg_replace("/[^0-9]/", "", $this->config->get('shipping_centraldofrete_postcode'));
		$token = $this->config->get('shipping_centraldofrete_api');
		$cargo = array_filter($this->config->get('shipping_centraldofrete_cargo'));
		
		if (!$this->customer->isLogged()) {
			$doc = null;
			$nome = null;
		} else {
			$cid = $this->customer->getId();
			$cpf = $this->getDoc($cid);
			$cpfid = $this->config->get('shipping_centraldofrete_doc');
			$doc = $cpf[$cpfid];
			$nome = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
		}
		
		$header = array('Authorization:'. $token,'Content-Type: application/json;charset=UTF-8',);
		$json_convert  = json_encode(array('from' => $postcode2, 'to' => $postcode, 'cargo_types' => $cargo, 
		'invoice_amount' => $stotal,'volumes' => $medidas,'recipient' => array('document' => '', 'name'=> '')
		));
		
		if ($this->config->get('shipping_centraldofrete_url') == 1) {
		$url     = 'https://api.centraldofrete.com/v1/quotation';
		} else  {
		$url     = 'https://sandbox.centraldofrete.com/v1/quotation';
		}
        
		$access_token = $this->getToken($url, $json_convert, $header);
		//print_r($json_convert);
		if (array_key_exists("error", $access_token)) {
		$at = false;
		$this->log->write('ERROR: CENTRAL DO FRETE API API - '. $access_token['error']);
		$codigo = '';
		} else {
		$at = true;	
		$codigo = $access_token['code'];
		}
		
		if ($this->config->get('shipping_centraldofrete_url') == 1) {
		$url2    = 'https://api.centraldofrete.com/v1/quotation/'.$codigo;
		} else  {
		$url2    = 'https://sandbox.centraldofrete.com/v1/quotation/'.$codigo;
		}
	
		$quote_data = array();
		
		if ($status && $at) {
			$get_frete = $this->getFrete($url2, $header);

			if ($this->config->get('shipping_centraldofrete_res') == 0) {
			$title = $get_frete['shipping_carrier'];
		    $code =  $get_frete['shipping_carrier'];
			if ($this->config->get('shipping_centraldofrete_cost') > 0 && $this->config->get('shipping_centraldofrete_type') == 1 ) {
			$st = $get_frete['price'];
		    $st2 = ($this->config->get('shipping_centraldofrete_cost')/100)* $st;
			$cost = $get_frete['price'] + $st2;	
			} else if ($this->config->get('shipping_centraldofrete_cost') > 0 && $this->config->get('shipping_centraldofrete_type') == 0 ) {
			$cost = $get_frete['price'] + str_replace(',', '.',$this->config->get('shipping_centraldofrete_cost'));	
			} else {
			$cost = $get_frete['price'];
			}
			if ($this->config->get('shipping_centraldofrete_day') > 0) {
			$des = $get_frete['delivery_time'] + $this->config->get('shipping_centraldofrete_day');
			} else { $des = $get_frete['delivery_time']; }	
			
			$quote_data[$code] = array(
				'code'         => 'centraldofrete.' .$code,
				'title'        => $title .' - '.$codigo,
				'cost'         => $cost ,
				'tax_class_id' => $this->config->get('shipping_centraldofrete_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_centraldofrete_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']). $this->language->get('text_delivery') . $des . $this->language->get('text_days')
			);
			}
			if ($this->config->get('shipping_centraldofrete_res') == 1) {
			$res = array();
			foreach ($get_frete as  $frete) {
            $res[] = array('id' => $frete['id'], 'price' => $frete['price'], 'carrier' => $frete['shipping_carrier'], 'delivery_time' => $frete['delivery_time']);
			foreach ($res as $fretes) {
		    $title = $fretes['carrier'];
		    $code =  $fretes['carrier'];
			if ($this->config->get('shipping_centraldofrete_cost') > 0 && $this->config->get('shipping_centraldofrete_type') == 0 ) {	
			$cost = $fretes['price'] + str_replace(',', '.',$this->config->get('shipping_centraldofrete_cost'));	
			} elseif ($this->config->get('shipping_centraldofrete_cost') > 0 && $this->config->get('shipping_centraldofrete_type') == 1 ) {
			$st = $fretes['price'];
		    $st2 = ($this->config->get('shipping_centraldofrete_cost')/100)* $st;
			$cost = $fretes['price'] + $st2;	
			} else {
			$cost = $fretes['price'];
			}
			if ($this->config->get('shipping_centraldofrete_day') > 0) {
			$des = $fretes['delivery_time'] + $this->config->get('shipping_centraldofrete_day');
			} else { $des = $fretes['delivery_time']; }	
			}
			$quote_data[$code] = array(
				'code'         => 'centraldofrete.'.$code,
				'title'        => $title .' - '.$codigo,
				'cost'         => $cost ,
				'tax_class_id' => $this->config->get('shipping_centraldofrete_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_centraldofrete_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']). $this->language->get('text_delivery') . $des . $this->language->get('text_days')
			);
			}
			}
		}
		
		$method_data = array();

		if ($quote_data) {
			$method_data = array(
				'code'       => 'centraldofrete',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_centraldofrete_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
	
	public function getToken($url, $json_convert, $header) {
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL, $url);
    curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
    curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST,           true );
    curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $json_convert);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
    $response = curl_exec($soap_do); 
    curl_close($soap_do);
    
	$retornou = json_decode($response,true);
    return  $retornou;
    }
	
	public function getFrete($url2, $header) {
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL, $url2);
    curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
    curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
    $response = curl_exec($soap_do); 
    curl_close($soap_do);

    $retornou = json_decode($response,true);
	if($this->config->get('shipping_centraldofrete_res') == 0){
    return  $retornou['prices'][0];
	} else  {
	return  $retornou['prices'];	
	}
    } 
	
	public function getHeight() {
		$height = 0;

		foreach ($this->cart->getProducts() as $product) {
			if ($product['shipping']) {
				$height += $this->length->convert($product['height'] * $product['quantity'], $product['length_class_id'], $this->config->get('config_length_class_id'));
			}
		}

		return $height;
	}
	
	public function getWidth() {
		$width = 0;

		foreach ($this->cart->getProducts() as $product) {
			if ($product['shipping']) {
				$width += $this->length->convert($product['width'] * $product['quantity'], $product['length_class_id'], $this->config->get('config_length_class_id'));
			}
		}

		return $width;
	}
	
	public function getLength() {
		$length = 0;

		foreach ($this->cart->getProducts() as $product) {
			if ($product['shipping']) {
				$length += $this->length->convert($product['length'] * $product['quantity'], $product['length_class_id'], $this->config->get('config_length_class_id'));
			}
		}

		return $length;
	}
	
	public function getDoc($cid) {
		$document = '';
        $customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$cid . "' AND status = '1'");
		if ($customer_query->num_rows) {
			$document = json_decode($customer_query->row['custom_field'], true);
		}

		return $document;
	}
}