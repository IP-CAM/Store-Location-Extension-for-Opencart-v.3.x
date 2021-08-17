<?php

class ModelExtensionModuleOcStoreLocationCategory extends Model {
    public function addCategory($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "store_categories SET category_name = '" . $this->db->escape($data['category_name']) . "', status = '" . (int) $data['status'] . "', date_modified = NOW(), date_added = NOW()");

        $id = $this->db->getLastId();

        if (isset($data['icon'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "store_categories SET icon = '" . $this->db->escape($data['icon']) . "' WHERE id = '" . (int) $id . "'");
        }

        return $id;
    }

    public function editCategory($id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "store_categories SET category_name = '" . $this->db->escape($data['category_name']) . "', status = '" . (int) $data['status'] . "', date_modified = NOW() WHERE id = '" . (int) $id . "'");

        if (isset($data['icon'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "store_categories SET icon = '" . $this->db->escape($data['icon']) . "' WHERE id = '" . (int) $id . "'");
        }
    }

    public function deleteCategory($id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "store_categories WHERE category_id = '" . (int) $id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "store_location_categories WHERE id = '" . (int) $id . "'");
    }

    public function getCategory($id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "store_categories WHERE id = '" . (int) $id . "'");

        return $query->row;
    }

    public function getCategories($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "store_categories";

        if (!empty($data['filter_name'])) {
            $sql .= " AND category_name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND status = '" . (int) $data['filter_status'] . "'";
        }

        $sql .= " GROUP BY id";

        $sort_data = array(
            'category_name',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY category_name";
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

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalCategories($data = array()) {
        $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "store_categories";

        if (!empty($data['filter_name'])) {
            $sql .= "category_name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND status = '" . (int) $data['filter_status'] . "'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getStoreCategories($id) {
        $store_category_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store_location_categories WHERE store_id = '" . (int) $id . "'");

        foreach ($query->rows as $result) {
            $store_category_data[] = $result['category_id'];
        }

        return $store_category_data;
    }

}
