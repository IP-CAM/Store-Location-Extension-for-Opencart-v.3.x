<?php
class ModelExtensionPaymentFaturado extends Model {
    public function getMethod($address, $total) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_faturado_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('payment_faturado_total') > 0 && $this->config->get('payment_faturado_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('payment_faturado_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $currencies = array('BRL');
        $currency_code = $this->session->data['currency'];
        if (!in_array(strtoupper($currency_code), $currencies)) {
            $status = false;
        }

        if (!in_array($this->config->get('config_store_id'), $this->config->get('payment_faturado_stores'))) {
            $status = false;
        }

        if (!in_array($this->customer->getGroupId(), $this->config->get('payment_faturado_customer_groups'))) {
            $status = false;
        }

        $safe = $this->getSafeCustomer();
        if (($this->config->get('payment_faturado_cliente_confiavel')) && (!$safe)) {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            if (strlen(trim($this->config->get('payment_faturado_imagem'))) > 0) {
                $title = '<img src="' . HTTPS_SERVER . 'image/' . $this->config->get('payment_faturado_imagem') . '" alt="' . $this->config->get('payment_faturado_titulo') . '" title="' . $this->config->get('payment_faturado_titulo') . '" />';
            } else {
                $title = $this->config->get('payment_faturado_titulo');
            }

            $method_data = array( 
                'code'  => 'faturado',
                'title' => $title,
                'terms' => '',
                'sort_order' => $this->config->get('payment_faturado_sort_order')
            );
        }

        return $method_data;
    }

    public function getSafeCustomer() {
        $customer_id = $this->customer->getId();

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$customer_id . "' AND safe = '1'");

        return $query->row;
    }
}