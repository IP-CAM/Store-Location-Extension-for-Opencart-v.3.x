<?php
class ModelCatalogDrivers extends Model {
	public function getDriver($driver_id) {
		$query = $this->db->query("SELECT filename, mask,file_manual, mask_manual FROM " . DB_PREFIX . "drivers  WHERE driver_id ='" . (int)$driver_id . "'");

		return $query->row;
	}
	public function getDrivers($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "drivers";
		//$sql = "SELECT * FROM `" . DB_PREFIX . "drivers` d LEFT JOIN " . DB_PREFIX . "product p ON d.product_id = p.product_id '";

		if (!empty($data['filter_name'])) {
			$sql .= " AND d.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
		if (!empty($data['filter_tags'])) {
			$sql .= " AND d.tags LIKE '" . $this->db->escape($data['filter_tags']) . "%'";
		}
		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}
		

		$sort_data = array(
			'name',
			'tags',
			'mask'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}
	public function getDriverByProductId($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "drivers  WHERE product_id ='" . (int)$product_id . "'");

		return $query->rows;
	}
	public function AddDriverTotalDownloadsByProductId($driver_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "drivers SET total_driver_downloads = (total_driver_downloads + 1)  WHERE driver_id = '" . (int)$driver_id . "'");
	}
	public function AddDriverManualTotalDownloadsByProductId($driver_id) {

		$this->db->query("UPDATE " . DB_PREFIX . "drivers SET total_manual_downloads = (total_manual_downloads + 1)  WHERE driver_id = '" . (int)$driver_id . "'");
	}
}
