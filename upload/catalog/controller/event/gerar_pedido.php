<?php

class ControllerEventGerarPedido extends Controller {
// catalog/model/checkout/order/addOrderHistory/after
// catalog/model/checkout/order/addOrder/after
    public function GerarPedido(&$route, &$args, &$output) {
        //ADD 01.06.2020 ----------------- INÍCIO -------------------
        if($this->config->get('module_feasso_gerar_pedido')){
        $userAPI = $this->config->get('module_feasso_user_objectdata');
        $passAPI = $this->config->get('module_feasso_password_objectdata');

        if (isset($args[0])) {
            $order_id = $args[0];
        } else {
            $order_id = 0;
        }


        $this->load->model('account/customer');
        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
        $log = new Log('objectdata.log');

        $codigo_vendedor = json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_cod_vendedor_order')]; // COD. VENDEDOR 
        $codigo_cliente = json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_cod_cliente_order')]; // COD. CLIENTE
        $log->write('Código vendedor: ' .print_r($args,true) . '<br/>Código Cliente:' . $order_id );

        $tabela = ((($this->customer->getGroupId() == $this->config->get('module_feasso_information_pages_airsoft_m1_group_id')) OR ($this->customer->getGroupId() == $this->config->get('module_feasso_information_pages_informatica_m1_group_id'))) ? "preco1" : "preco2");
        $frete_terceiros = '';
        $frete = $this->session->data['shipping_method']['code'];
        if ($frete == "pickup.pickup") {
            $frete_terceiros = 'A COMBINAR';
        }// RETIRADA

        $campos = '';
        $desconto = $this->cart->getSubTotalDesconto()['porcento'];

        $observacao = '((((( PEDIDO AUTOMÁTICO API ))))) / Pedido Loja Virtual  / Desconto ' . $desconto . '%' . " / " . $frete_terceiros . ' / *EM NEGOCIAÇÃO.';
        $n = 0;
        $campos = "{\"categoria\": \"corporativo\",
                    \"tipo_pedido\": \"reserva\",
                    \"vendedor_id\": " . $codigo_vendedor . ",
                    \"cliente_id\": " . $codigo_cliente . ",
                    \"observacao\": \"" . $observacao . "\",
                    \"tipo_preco\": \"" . $tabela . "\",
                    \"items\": [" . $this->cart->getApiProdutos() . "]}";


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
            CURLOPT_HTTPHEADER => array("charset: UTF-8", "Content-Type: application/json", "login: $userAPI", "senha: $passAPI"),));
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
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://feasso.objectdata.com.br/api/pedido/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $campos,
            CURLOPT_HTTPHEADER => array("Content-Type: application/json", "charset: UTF-8", "session: " . $sessao),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        $this->db->query("UPDATE " . DB_PREFIX . "order SET invoice_no = '" .$response['numero'] . "' WHERE order_id = '" . (int)$order_id . "'");

       // $this->OrderUpdate($pedido_id,$order_id,$codigo_vendedor);
       //$this->getOrder($response,$order_id,$sessao);

	}
    }
    public function OrderUpdate($pedido_id,$order_id,$codigo_vendedor) {
        
    //$this->db->query("UPDATE " . DB_PREFIX . "order SET obejctdata_id = '" . (int)$pedido_id . "' WHERE order_id = '" . (int)$order_id . "'");
     $this->db->query("UPDATE " . DB_PREFIX . "order SET obejctdata_id = '" . (int)$pedido_id . "' WHERE order_id = '" . (int)$order_id . "'");
     $this->db->query("UPDATE " . DB_PREFIX . "order_product SET vendedor_id = '" . (int)$codigo_vendedor  . "' WHERE order_id = '" . (int)$order_id . "'");

      // $this->db->query("UPDATE " . DB_PREFIX . "order SET invoice_no = '" . (int)$response . "' WHERE order_id = '" . (int)$order_id . "'");
       $log = new Log('pedidos.log');
       $log->write('ID Pedido Object:'.$pedido_id .'Pedido oc'.$pedido_id.'Vendedor id'.$codigo_vendedor);
        
    }
   /* public function getOrder($response,$order_id ,$sessao) {

 $curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "http://feasso.objectdata.com.br/api/pedido/?numero=".$response,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array("Content-Type: application/json","charset: UTF-8","session: ".$sessao),
));
$vendedor = curl_exec($curl);
curl_close($curl);

$result = json_decode($vendedor, true); 
$this->db->query("UPDATE " . DB_PREFIX . "order_product SET vendedor_id = '" . (int)$result['vendedor_id'] . "' WHERE order_id = '" . (int)$order_id . "'");

$log = new Log('pedidos-vendedor.log');
$log->write('ID vendedor Object:'.$result['vendedor_id'] .'pedido id',$response);

}*/

}
