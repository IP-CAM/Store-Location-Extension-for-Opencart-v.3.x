<?php
class ModelCatalogVendedores extends Model {
	public function getVendedores($code) {
		//$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendedores ");
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "vendedores` WHERE `code` = '" . (int)$code . "'");

		return $query->row;
	}
        
        public function getVendedor() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendedores");

		return $query->row;
	}

}
