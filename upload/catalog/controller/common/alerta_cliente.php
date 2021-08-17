<?php

class ControllerCommonAlertaCliente extends Controller {

    public function index() {

        $this->load->model('account/customer');
        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
		$vendedor = json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_vendedor_externo')];// CAMPO PERSONALIZADO COD. CLIENTE
        $vendedor_id = json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_cod_vendedor_customer')];// CAMPO PERSONALIZADO COD. CLIENTE
        if ($vendedor == 30) {

            $this->load->model('catalog/vendedores');
            $vendedores_info = $this->model_catalog_vendedores->getVendedores($vendedor_id);
            
            if($vendedores_info['code'] == $vendedor_id){
                $this->load->language('common/alerta_cliente');
                $data['text_vendedor'] = sprintf($this->language->get('text_vendedor'), $vendedores_info['name'], $vendedores_info['email'],$vendedores_info['telefone']);


                return $this->load->view('common/alerta_cliente_footer', $data);
            }
        }
    }

}
