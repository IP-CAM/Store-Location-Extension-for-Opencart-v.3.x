<?php

class ModelExtensionTotalDescontoFeasso extends Model {

    public function getTotal($total) {

        $this->load->model('account/customer');
        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
        $desabilitar_desconto_cliente = json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_customer_desabilitar_desconto')]; // CAMPO PERSONALIZADO COD. CLIENTE
        if ($desabilitar_desconto_cliente != 37) {

            $total_para_desconto = 0;
            $informatica_m1 = '4'; //INFORMATICA M1 ( 4 )	
            $informatica_m3 = '5'; //INFORMATICA M3 ( 5 )
            $subtotal = $this->cart->getSubTotalDesconto()['total'];

            $desconto = $this->cart->getSubTotalDesconto()['porcento'];

            $com_desconto = $this->cart->getSubTotalDesconto()['descontado'];

            foreach ($this->cart->getProducts() as $product) {
                if (($this->customer->getGroupId() == $informatica_m1) OR ($this->customer->getGroupId() == $informatica_m3)) {// Verifica se o grupo é de INFORMÁTICA M1/M3			
                    if (substr($product['name'], -1) != '*') {
                        $product_name = $product['name'];
                    }//Add 15.05.2019 Soma total somente dos itens sem asterisco, todos airsofts tem asterisco.
                } else {//Quando é Grupo de Airsoft	
                    if ((substr($product['name'], -2) != '**') AND (substr($product['name'], -1) != '#')) {
                        $product_name = $product['name'];
                    }
                }// Total geral sem restrição de asterisco.
            }

            if (($desconto) AND ($product_name)) {
                if ($this->config->get('total_desconto_feasso_status')) {
                    if (isset($com_desconto) > 0 AND ($this->cart->getFornecedor() == '11')) {
                        $this->load->language('extension/total/avista');

                        $float = ($desconto < 10) ? '0.0' . str_replace(array(',', '.'), '', $desconto) : '0.' . str_replace(array(',', '.'), '', $desconto) . '<br>';
                        $percent = $subtotal * $float;

                        $total['totals'][] = array(
                            'code' => 'desconto_feasso',
                            'title' => 'Desconto de ' . $desconto . '% ' . 'concedido com sucesso.',
                            'value' => $percent * -1,
                            'sort_order' => $this->config->get('total_desconto_feasso_sort_order')
                        );

                        $total['total'] -= $percent;
                        // print_R($total['total']);
                    }
                }
            }
        }
    }

}
