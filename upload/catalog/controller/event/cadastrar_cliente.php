<?php

class ControllerEventCadastrarCliente extends Controller {

// catalog/model/account/customer/addCustomer/after
    public function index(&$route, &$args, &$output) {
        
        if ($this->config->get('module_feasso_gerar_castastro')) {
            $userAPI = $this->config->get('module_feasso_user_objectdata');
        $passAPI = $this->config->get('module_feasso_password_objectdata');
            
            $this->load->model('account/customer');
            
        
            $customer_info = $this->model_account_customer->getCustomer($output);


            $fields = [
                'nome_extendido' => json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_cadastro_razao')],
                'nome_resumido' => $args[0]['lastname'],
                'cpf_cnpj'  => json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_cadastro_cnpj')],
                'inscricao_estadual' => json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_cadastro_inscricao')],
                'ramo_atividade_id' => 17
                //'simples_nacional' => json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_cadastro_simples_nacional')],
                //'telefone' => $args[0]['telephone']
            ];
            
           // $this->GerarCadastro($fields);
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

        $crl = curl_init();
        curl_setopt_array($crl, array(
            CURLOPT_URL => "http://feasso.objectdata.com.br/api/cliente",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => array("Content-Type: application/json", "charset: UTF-8", "session:" . $sessao),));
        $json = curl_exec($crl);
        $retorno = array(json_decode($json, true));
        $log = new Log('clientes.log');
        //$log->write('cleintes: ' . print_r($fields).$sessao); 
        $log->write('cleintes: ' . print_r($fields,true).'<br>'.$sessao); 
         
        curl_close($crl);
        }
    }

    public function GerarCadastro($fields) {
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

        $crl = curl_init();
        curl_setopt_array($crl, array(
            CURLOPT_URL => "http://feasso.objectdata.com.br/api/cliente/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => array("Content-Type: application/json", "charset: UTF-8", "session:" . $sessao),));
        $json = curl_exec($crl);
        $retorno = array(json_decode($json, true));
        $log = new Log('clientes.log');
        $log->write('cleintes: ' . print_r($fields));
       
        curl_close($crl);
    }

}
