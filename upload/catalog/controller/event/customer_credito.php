<?php
class ControllerEventCustomerCredito extends Controller {
		
	// model/account/customer/deleteLoginAttempts/after
	public function credito(&$route, &$args, &$output) {
            	$customer_info = $this->model_account_customer->getCustomerByEmail($args[0]);
                $limite_cliente = json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_customer_credito_limite')];// CAMPO PERSONALIZADO LIMITE CREDITO

		if (isset($this->request->get['route']) && ($this->request->get['route'] == 'account/login' || $this->request->get['route'] == 'checkout/login/save') && $limite_cliente == 34) {
			//$customer_info = $this->model_account_customer->getCustomerByEmail($args[0]);
			$codigo_cliente = json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_cod_cliente_order')];// CAMPO PERSONALIZADO COD. CLIENTE
            
			$limite = $this->getCredito($codigo_cliente);
                      

		
			if ($customer_info) {
				$this->load->model('account/customer_credito');
	
				$credito_data = array(
					'customer_id' => $customer_info['customer_id'],
					'name'        => $customer_info['firstname'] . ' ' . $customer_info['lastname'],
					'cliente_id'  => $codigo_cliente,
					'limite'       => $limite

				);
	
				$this->model_account_customer_credito->addCredito('credito', $credito_data);
			}
		}	
	}

 public function getCredito($codigo_cliente){
	$userAPI = $this->config->get('module_feasso_user_objectdata');
	$passAPI = $this->config->get('module_feasso_password_objectdata');

	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://feasso.objectdata.com.br/api/",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_HTTPHEADER => array("charset: UTF-8","Content-Type: application/json","login: $userAPI","senha: $passAPI"),));
	  $response = curl_exec($curl);
	  $err = curl_error($curl);
	
	//curl_close($curl);
	
	if ($err) {
	 // echo "cURL Error #:" . $err;
	} else {
		$json = json_decode($response, true); 
		$sessao = $json['session'];
	//echo $sessao;
	}

	$crl = curl_init();
	curl_setopt_array($crl, array(
	  CURLOPT_URL => "http://feasso.objectdata.com.br/api/cliente/".$codigo_cliente,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 60,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array("Content-Type: application/json","charset: UTF-8","session:".$sessao),));
	$json = curl_exec($crl);
	$retorno = array(json_decode($json, true)); 
	foreach($retorno as  $credito){
	   $credito_retorno = $credito['limite_credito'];
	
	}
	return $credito_retorno;
	curl_close($crl);
	
 }	
 
}