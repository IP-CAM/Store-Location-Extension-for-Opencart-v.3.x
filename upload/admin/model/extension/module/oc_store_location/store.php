<?php

class ModelExtensionModuleOcStoreLocationStore extends Model {

    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "store_categories` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`category_name` VARCHAR(255) DEFAULT NULL,, 
			`status` tinyint(1) NOT NULLL, 
			`date_added`DATETIME NULL DEFAULT NULL, 
			`date_modified` DATETIME NULL DEFAULT NULL,
			 PRIMARY KEY (`id`))
        ");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "store_location_categories` (
			`id` INT(11) NOT NULL,
			`category_id` int(11) NOT NULL, 
			`store_id` int(11) NOT NULL 
			 )
        ");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "store_location_stores` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`title` VARCHAR(255) DEFAULT NULL,, 
			`description` text DEFAULT NULL, 
			`date_added`DATETIME NULL DEFAULT NULL, 
			`date_modified` DATETIME NULL DEFAULT NULL,
                        `street` text DEFAULT NULL,
                        `city` varchar(100) DEFAULT NULL,
                        `state` varchar(100) DEFAULT NULL,
                        `postal_code` varchar(50) DEFAULT NULL,
                        `country_id` int(11) DEFAULT NULL,
                        `lat` varchar(50) DEFAULT NULL,
                        `lng` varchar(50) DEFAULT NULL,
                        `telephone` varchar(50) DEFAULT NULL,
                        `fax` varchar(50) DEFAULT NULL,
                        `email` varchar(100) DEFAULT NULL,
                        `website` varchar(255) DEFAULT NULL,
                        `description_2` text DEFAULT NULL,
                        `status` tinyint(4) DEFAULT NULL,
                        `date_added` timestamp NULL DEFAULT current_timestamp(),
                        `date_modified` datetime DEFAULT NULL
			 PRIMARY KEY (`id`))
        ");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "store_categories`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "store_location_categories`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "store_location_stores`");

    }

    public function addStore($data) {

        $this->db->query("INSERT INTO `" . DB_PREFIX . "store_location_stores` SET title = '" . $this->db->escape($data['title']) . "', description = '" . $this->db->escape($data['description']) . "', street = '" . $this->db->escape($data['street']) . "', city = '" . $this->db->escape($data['city']) . "', state = '" . $this->db->escape($data['state']) . "',postal_code = '" . $this->db->escape($data['postal_code']) . "', country_id = '" . $this->db->escape($data['country_id']) . "', lat = '" . $this->db->escape($data['lat']) . "', lng = '" . $this->db->escape($data['lng']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', email = '" . $this->db->escape($data['email']) . "', website = '" . $this->db->escape($data['website']) . "', status = '" . $this->db->escape($data['status']) . "', date_added = NOW()");

        $id = $this->db->getLastId();

        if (isset($data['store_category'])) {
            foreach ($data['store_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "store_location_categories SET category_id = '" . (int) $category_id . "', store_id = '" . (int) $id . "'");
            }
        }

        return $id;
    }

    public function editStore($id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "store_location_stores SET title = '" . $this->db->escape($data['title']) . "', description = '" . $this->db->escape($data['description']) . "', street = '" . $this->db->escape($data['street']) . "', city = '" . $this->db->escape($data['city']) . "', state = '" . $this->db->escape($data['state']) . "',postal_code = '" . $this->db->escape($data['postal_code']) . "',country_id = '" . $this->db->escape($data['country_id']) . "', lat = '" . $this->db->escape($data['lat']) . "', lng = '" . $this->db->escape($data['lng']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', email = '" . $this->db->escape($data['email']) . "', website = '" . $this->db->escape($data['website']) . "', description_2 = '" . $this->db->escape($data['description_2']) . "', status = '" . $this->db->escape($data['status']) . "', date_modified = NOW() WHERE id = '" . (int) $id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "store_location_categories WHERE store_id = '" . (int) $id . "'");
        if (isset($data['store_category'])) {
            foreach ($data['store_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "store_location_categories SET category_id = '" . (int) $category_id . "', store_id = '" . (int) $id . "'");
            }
        }
    }

    public function deleteStore($id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "store_location_stores WHERE id = '" . (int) $id . "'");

        $this->cache->delete('store_location_stores');
    }

    public function getStore($id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "store_location_stores sls LEFT JOIN " . DB_PREFIX . "store_location_categories slc ON (sls.id = slc.store_id) LEFT JOIN " . DB_PREFIX . "store_location_markers slm ON (sls.marker_id = slm.id) WHERE sls.id = '" . (int) $id . "'");

        return $query->row;
    }

    public function getStoreByCategoryId($id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "store_categories  sls LEFT JOIN " . DB_PREFIX . "store_location_categories slc ON (sls.id = slc.category_id) WHERE slc.store_id = '" . (int) $id . "'");

        return $query->row;
    }

    public function getStores($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "store_location_stores ";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "title LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_email'])) {
            $implode[] = "email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
        }

        if (!empty($data['filter_category_id'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "store_categories sc ON (sc.id = " . $this->db->escape($data['filter_category_id']) . ")";

            //$implode[] = "LEFT JOIN " . DB_PREFIX . "store_categories WHERE id = '" . $this->db->escape($data['filter_category_id']) . "'";
            // $implode[] = "id IN (SELECT id FROM " . DB_PREFIX . "store_categories WHERE id = '" . $this->db->escape($data['filter_category_id']) . "')";
        }



        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $implode[] = "status = '" . (int) $data['filter_status'] . "'";
        }


        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $sort_data = array(
            'title',
            'c.email',
            'customer_group',
            'c.status',
            'c.ip',
            'c.date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY title";
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
        print_r($sql);

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalStores($data = array()) {
        $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "store_location_stores";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "title LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_email'])) {
            $implode[] = "email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
        }

        if (!empty($data['filter_category_id'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "store_categories sc ON (sc.id = " . $this->db->escape($data['filter_category_id']) . ")";
        }



        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $implode[] = "status = '" . (int) $data['filter_status'] . "'";
        }

        if (!empty($data['filter_date_added'])) {
            $implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

}
