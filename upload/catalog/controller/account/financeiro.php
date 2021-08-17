<?php
//use \Objectdata\Api;

 ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class ControllerAccountFinanceiro extends Controller {
    
   // private $api_user;
   // private $api_pass;
   // private $api_url = 'http://feasso.objectdata.com.br/api/';
   // private $cliente_url = 'http://feasso.objectdata.com.br/api/cliente/';
    
   /* public function __construct() {
       $this->api_user = $this->config->get('module_feasso_user_objectdata');
        $this->api_pass = $this->config->get('module_feasso_password_objectdata');
    }*/

    public function index() {
        
       

        /*if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/financeiro', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }*/

        $this->load->language('account/financeiro');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_financeiro'),
            'href' => $this->url->link('account/financeiro', '', true)
        );
        
        $this->load->model('account/customer');
        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
        $codigo_cliente = json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_cod_cliente_customer')];// CAMPO PERSONALIZADO COD. CLIENTE
        $limite_cliente = json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_customer_credito_limite')];// CAMPO PERSONALIZADO LIMITE CREDITO

        // $teste = 6328;
       // $this->sincromaster->document->isMobile();
       
        
       
        //$codigo_cliente = 8076;
        //$codigo_cliente = 6328;
        //$codigo_cliente = 7423;
      //  print_r($codigo_cliente);

        $url = "http://feasso.objectdata.com.br/api/financeiro/titulo/?cliente_id=" . $codigo_cliente . "&pago=false&order=financeiro_titulo.data_vencimento" ;

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
            CURLOPT_HTTPHEADER => array("charset: UTF-8", "Content-Type: application/json", "login: webapi", "senha: feasso2007"),));
        $sessao = curl_exec($curl);
        $json = json_decode($sessao, true);
        $sessao = $json['session'];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url, //ok
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array("Content-Type: application/json", "charset: UTF-8", "session: " . $sessao),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response, true);
        
	$limite = $this->cart->hasLimite();
       
        $data['financeiros'] = array();
        foreach ($response as $result) {
            if($result['descricao'] == ''){
              $this->response->redirect($this->url->link('account/account', '', true));
            }
             if(!$result['data_cancelado'] AND ($result['descricao'] != '36204') ){
            $total += $result['valor'];
            $data['financeiros'][] = array(
                //'emissao' => date($this->language->get('date_format_short'), strtotime($result['data_emissao'])),
                'pedido' => html_entity_decode($result['descricao'], ENT_QUOTES, 'UTF-8'),
                'vencimento' => date($this->language->get('date_format_short'), strtotime($result['data_vencimento'])),
                'devendo' => (strtotime("now") <= strtotime($result['data_vencimento']) ? 0 : 1),
                'valor' => $this->currency->format($result['valor'], $this->config->get('config_currency'))
            );
            }
        }
        if($limite_cliente == 34){
        $data['total'] = $this->currency->format($limite-$total, $this->config->get('config_currency'));
        }


        $data['continue'] = $this->url->link('account/account', '', true);
        $data['nav_tabs'] = $this->load->controller('account/nav_tabs');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/financeiro', $data));
    }
    
    /*public function getCreditoLimite($codigo_cliente) {
        
        $headers = array(
            'charset: UTF-8',
            'Content-Type: application/json',
            'login:' . $this->api_user,
            'senha:' . $this->api_pass
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => $headers,));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $json = json_decode($response, true);
            $sessao = $json['session'];
        }

        $crl = curl_init();
        curl_setopt_array($crl, array(
            CURLOPT_URL => $this->cliente_url . $codigo_cliente,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array("Content-Type: application/json", "charset: UTF-8", "session:" . $sessao),));
        $json = curl_exec($crl);
        $retorno = array(json_decode($json, true));
        foreach ($retorno as $credito) {
            $credito_retorno = $credito['limite_credito'];
        }
        //echo $sessao;
        return $credito_retorno;
        curl_close($crl);
    }*/
    public function getCreditoLimite($codigo_cliente) {
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
       // print_r($retorno['limite_credito']);
	return $credito_retorno;
	curl_close($crl);

	
 }	

}
