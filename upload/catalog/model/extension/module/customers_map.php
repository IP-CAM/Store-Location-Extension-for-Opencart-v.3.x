<?php
/**
 * (c) 2017 AGriboed
 * http://v1rus.ru || alexv1rs@gmail.com
 **/
 
class ModelExtensionModuleCustomersMap extends Model{
    
    public function getAddressByCustomerGroup_id($customer_group_id){
         $sql="SELECT a.*, c.name as country,cu.custom_field,cu.telephone, cu.firstname as customer_firstname, cu.lastname as customer_lastname, cu.address_id as current_address FROM " .
            DB_PREFIX . "address a LEFT JOIN " . DB_PREFIX .
            "country c ON(a.country_id=c.country_id) 
            LEFT JOIN " . DB_PREFIX .
            "customer cu ON (a.customer_id=cu.customer_id) WHERE cu.customer_group_id = '" . (int)$customer_group_id . "' and cu.status = 1";

        $query=$this->db->query($sql);
        //print_r($customer_group_id);

        return $query->rows;
    }
    public function getAllAddresses()
    {
        $sql="SELECT a.*, c.name as country, cu.firstname as customer_firstname, cu.lastname as customer_lastname, cu.address_id as current_address FROM `" .
            DB_PREFIX . "address` a LEFT JOIN " . DB_PREFIX .
            "country c ON a.`country_id`=c.`country_id` 
            LEFT JOIN " . DB_PREFIX .
            "customer cu ON a.`customer_id`=cu.`customer_id`";

        $query=$this->db->query($sql);

        return $query->rows;
    }

    public function getAddressLine($address_id=0)
    {
        $sql="SELECT a.address_id id, a.address_1, a.address_2, a.city, c.name as country  FROM `" .
            DB_PREFIX . "address` a LEFT JOIN " . DB_PREFIX .
            "country c ON a.`country_id`=c.`country_id` WHERE a.address_id='{$address_id}'";

        $query=$this->db->query($sql);

        return $query->row;
    }

    public function updateLatLng($address_id=0, $lat='', $lng=''){
        $this->db->query("UPDATE `" . DB_PREFIX . "address` SET lat = '{$lat}', lng = '{$lng}' WHERE address_id = '" . (int)$address_id . "'");
    }

    public function install()
    {
        $this->db->query("
        ALTER TABLE `" . DB_PREFIX .
            "address` ADD `lat` VARCHAR(10) NOT NULL AFTER `custom_field`, ADD `lng` VARCHAR(10) NOT NULL AFTER `lat`;");
    }

    public function uninstall()
    {
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "address`
  DROP `lat`,
  DROP `lng`;");
    }
    public function getCustomer($customer_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row;
	}

}