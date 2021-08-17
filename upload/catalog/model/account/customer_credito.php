<?php
class ModelAccountCustomerCredito extends Model {
	public function addCredito($key, $data) {
		if (isset($data['customer_id'])) {
			$customer_id = $data['customer_id'];
		} else {
			$customer_id = 0;
		}
		if (isset($data['limite'])) {
			$limite = $data['limite'];
		} else {
			$limite = "";
		}

		$log = new Log('credito_cliente.log');
		$log->write('Limite Cliente:'. $limite);
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_credito WHERE customer_id = '" . (int)$customer_id . "'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_credito` SET `customer_id` = '" . (int)$customer_id . "',`limite` = '" . $this->db->escape($limite) . "', `key` = '" . $this->db->escape($key) . "', `data` = '" . $this->db->escape(json_encode($data)) . "', `date_added` = NOW()");
	}
}