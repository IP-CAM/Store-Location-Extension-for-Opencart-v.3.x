<?php

/**
 * Created by Basheir Hassan.
 * User: basheir
 * Date: 6 يون، 2018 م
 * Time: 1:57 ص
 * Version 1.6.1
 */
class ControllerEventOcTelegram extends Controller {

    public function newOrderAlert(&$route, &$args, &$output) {

        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('module_oc_telegram');

        /* if (!isset($setting['module_oc_telegram_status'])) {
          return false;
          } */

        if (isset($args[0])) {
            $order_id = $args[0];
        } else {
            $order_id = 0;
        }
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $this->load->model('account/customer');
        $this->load->model('catalog/vendedores');
        $customer_info = $this->model_account_customer->getCustomer($order_info['customer_id']);
        //$vendedor_info = $this->model_catalog_vendedores->getVendedor();
        
        $codigo_vendedor = json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_cod_vendedor_order')]; // COD. VENDEDOR 
        $codigo_cliente = json_decode($customer_info['custom_field'], true)[$this->config->get('module_feasso_cod_cliente_order')]; // COD. CLIENTE
        $vendedor_info = $this->model_catalog_vendedores->getVendedores($codigo_vendedor);
        
        if($codigo_vendedor == $vendedor_info['code'] ){
                  /*  $log = new Log('telegram.log');

            print_r($vendedor_info['code']);
            $log->write('Código vendedor: ' .$vendedor_info['code'] . '<br/>Código Cliente:' .$codigo_cliente );*/
           $message = '<b>'.$vendedor_info['name'].'</b> seu cliente <b>'.$customer_info['firstname'].' - '.$codigo_cliente.'</b> fez um novo pedido:<b>'.$order_info['invoice_no'].'</b>';
           $this->sendMessagetoTelegam($message);
        }
        if (isset($setting['module_oc_telegram_order_alert'])) {

            $this->load->model('account/order');
            if (count($this->model_account_order->getOrderHistories($order_id)) <= 1) {
                //$message = $this->replaceMessage($setting['module_oc_telegram_order_meassage'], $order_info);
                //                    $message .= $this->buldArray($order_info);
               $message = $vendedor_info['name'].' seu cliente'.$codigo_cliente.' fez um novo pedido:'.$order_info['invoice_no'] ;
                $this->sendMessagetoTelegam($message);

                if (isset($setting['module__oc_telegram_product_alert'])) {
                    $order_products = $this->model_checkout_order->getOrderProducts($order_id);
                    $products = $this->bulidProducts($order_products);
                    $this->sendMessagetoTelegam($products);
                }
            }
        }
    }

    public function newCustemerAlert(&$route, &$data, &$output) {

        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('module_oc_telegram');

        if (!isset($setting['module_oc_telegram_status'])) {
            return false;
        }
        if (!$setting['module_oc_telegram_status']) {
            return false;
        }
        if (isset($setting['module_oc_t_new_customer_alert'])) {

            $message = $this->replaceMessage($setting['module_oc_telegram_new_custemer_meassage'], $data[0]);
            $this->sendMessagetoTelegam($message);
        }
    }

    public function sendReturnProductAlert(&$data, &$output) {

        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('module_oc_telegram');

        if (!isset($setting['module_oc_telegram_status'])) {
            return false;
        }
        if (!$setting['module_oc_telegram_status']) {
            return false;
        }
        if (isset($setting['module_oc_telegram_return_order_alert'])) {
            $order_id = $data[0];
            $message = $setting['module_oc_telegram_return_order_message'] . "#";
            $this->sendMessagetoTelegam($message);
        }
    }

    //Send  message To notificationTelegram

    public function sendMessagetoTelegam($msg) {

        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('module_oc_telegram');

        if (!isset($setting['module_oc_telegram_status'])) {
            return false;
        }
        if (!$setting['module_oc_telegram_status']) {
            return false;
        }

        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('module_oc_telegram');

        //print_r($setting);
        $botToken = $setting['module_oc_telegram_boot_token'];
        $website = "https://api.telegram.org/bot" . $botToken;
        $chatIds = $setting['module_oc_telegram_chat_ids'];  //Receiver Chat Id

        if (is_array($chatIds)) {
            foreach ($chatIds as $val) {
                $this->initMessage($botToken, $val, $msg);
            }
        } else {
            $this->initMessage($botToken, $chatIds, $msg);
        }
    }

    private function initMessage($botToken, $chatID, $msg) {

        $website = "https://api.telegram.org/bot" . $botToken;

        $params = [
            'chat_id' => $chatID,
            'text' => html_entity_decode($msg),
            'parse_mode' => 'HTML'
        ];
        $ch = curl_init($website . '/sendMessage');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
    }

    public function buldArray($arr) {

        if (is_array($arr)) {
            $dataAttributes = array_map(function ($value, $key) {
                return @"$key ---> $value  \n";
            }, array_values($arr), array_keys($arr));

            return $dataAttributes = implode(' ', $dataAttributes);
        }
    }

    public function replaceMessage($string, $arr) {

        return $str = preg_replace_callback('/{(\w+)}/', function ($match) use ($arr) {
            return $arr[$match[1]];
        }, $string);
    }

    protected function bulidProducts($products) {

        $message = "";
        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('module_oc_telegram');

        foreach ($products as $product) {

            $search = array('{order_product_id}', '{order_id}', '{product_id}', '{name}', '{model}', '{quantity}', '{price}', '{total}', '{tax}', '{reward}', '{product_url}');
            $replace = array($product['order_product_id'], $product['order_id'], $product['product_id'], $product['name'], $product['model'], $product['quantity'], ((int) $product['price'] + 0), $product['total'], $product['tax'], $product['reward'], $this->getProductDetalies($product['product_id']));
            $subject = $setting['module_oc_telegram_product_meassage'];
            $message .= str_replace($search, $replace, $subject);
            $message .= PHP_EOL;
        }
        return $message;
    }

    protected function getProductDetalies($product_id) {
        $url = $this->url->link('product/product', 'product_id=' . $product_id);
        return "\n" . str_replace("&amp;", "&", $url);
    }

}
