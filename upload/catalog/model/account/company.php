<?php

class ModelAccountCompany extends Model {

    public function addCompany($customer_id, $data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "company` SET customer_id = '" . (int) $customer_id . "', razao_social = '" . $this->db->escape($data['razao_social']) . "', nome_fantasia = '" . $this->db->escape($data['nome_fantasia']) . "', responsavel = '" . $this->db->escape($data['responsavel']) . "', cnpj = '" . $this->db->escape($data['cnpj']) . "', ie = '" . $this->db->escape($data['ie']) . "', rua = '" . $this->db->escape($data['rua']) . "', bairro = '" . $this->db->escape($data['bairro']) . "', cep = '" . $this->db->escape($data['cep']) . "', cidade = '" . $this->db->escape($data['cidade']) . "', zone_id = '" . (int) $data['zone_id'] . "', country_id = '" . (int) $data['country_id'] . "'");

        $company_id = $this->db->getLastId();

        if (!empty($data['default'])) {
            $this->db->query("UPDATE `" . DB_PREFIX . "customer` SET company_id = '" . (int) $company_id . "' WHERE customer_id = '" . (int) $customer_id . "'");
        }
        return $company_id;
    }

    public function editCompany($company_id, $data) {
        $this->db->query("UPDATE `" . DB_PREFIX . "company` SET razao_social = '" . $this->db->escape($data['razao_social']) . "', nome_fantasia = '" . $this->db->escape($data['nome_fantasia']) . "', responsavel = '" . $this->db->escape($data['responsavel']) . "', cnpj = '" . $this->db->escape($data['cnpj']) . "', ie = '" . $this->db->escape($data['ie']) . "', rua = '" . $this->db->escape($data['rua']) . "', bairro = '" . $this->db->escape($data['bairro']) . "', cep = '" . $this->db->escape($data['cep']) . "', cidade = '" . $this->db->escape($data['cidade']) . "', zone_id = '" . (int) $data['zone_id'] . "', country_id = '" . (int) $data['country_id'] . "' WHERE address_id  = '" . (int) $address_id . "' AND customer_id = '" . (int) $this->customer->getId() . "'");

        if (!empty($data['default'])) {
            $this->db->query("UPDATE `" . DB_PREFIX . "customer` SET company_id = '" . (int) $company_id . "' WHERE customer_id = '" . (int) $this->customer->getId() . "'");
        }
    }

    public function deleteCompany($company_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "company` WHERE company_id = '" . (int) $company_id . "' AND customer_id = '" . (int) $this->customer->getId() . "'");
    }

    public function getCompany($company_id) {
        $company_query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "company` WHERE company_id = '" . (int) $company_id . "' AND customer_id = '" . (int) $this->customer->getId() . "'");

        if ($company_query->num_rows) {
            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int) $company_query->row['country_id'] . "'");

            if ($country_query->num_rows) {
                $country = $country_query->row['name'];
                $iso_code_2 = $country_query->row['iso_code_2'];
                $iso_code_3 = $country_query->row['iso_code_3'];
                $company_format = $country_query->row['company_format'];
            } else {
                $country = '';
                $iso_code_2 = '';
                $iso_code_3 = '';
                $company_format = '';
            }

            $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int) $address_query->row['zone_id'] . "'");

            if ($zone_query->num_rows) {
                $zone = $zone_query->row['name'];
                $zone_code = $zone_query->row['code'];
            } else {
                $zone = '';
                $zone_code = '';
            }

            $company_data = array(
                'company_id' => $address_query->row['address_id'],
                'firstname' => $address_query->row['firstname'],
                'lastname' => $address_query->row['lastname'],
                'company' => $address_query->row['company'],
                'address_1' => $address_query->row['address_1'],
                'address_2' => $address_query->row['address_2'],
                'postcode' => $address_query->row['postcode'],
                'city' => $address_query->row['city'],
                'zone_id' => $address_query->row['zone_id'],
                'zone' => $zone,
                'zone_code' => $zone_code,
                'country_id' => $address_query->row['country_id'],
                'country' => $country,
                'iso_code_2' => $iso_code_2,
                'iso_code_3' => $iso_code_3,
                'company_format' => $company_format
            );

            return $company_data;
        } else {
            return false;
        }
    }

    public function getCompanies() {
        $company_data = array();

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "company` WHERE customer_id = '" . (int) $this->customer->getId() . "'");

        foreach ($query->rows as $result) {
            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int) $result['country_id'] . "'");

            if ($country_query->num_rows) {
                $country = $country_query->row['name'];
                $iso_code_2 = $country_query->row['iso_code_2'];
                $iso_code_3 = $country_query->row['iso_code_3'];
                //$company_format = $country_query->row['company_format'];
            } else {
                $country = '';
                $iso_code_2 = '';
                $iso_code_3 = '';
               // $company_format = '';
            }

            $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int) $result['zone_id'] . "'");

            if ($zone_query->num_rows) {
                $zone = $zone_query->row['name'];
                $zone_code = $zone_query->row['code'];
            } else {
                $zone = '';
                $zone_code = '';
            }

            $company_data[$result['company_id']] = array(
                'company_id' => $result['company_id'],
                'razao_social' => $result['razao_social'],
                'nome_fantasia' => $result['nome_fantasia'],
                'responsavel' => $result['responsavel'],
                'cnpj' => $result['cnpj'],
                'ie' => $result['ie'],
                'rua' => $result['rua'],
                'bairro' => $result['bairro'],
                'cidade' => $result['cidade'],
                'cep' => $result['cep'],
                'zone_id' => $result['zone_id'],
                'zone' => $zone,
                'zone_code' => $zone_code,
                'country_id' => $result['country_id'],
                'country' => $country,
                'iso_code_2' => $iso_code_2,
                'iso_code_3' => $iso_code_3
                //'company_format' => $company_format
            );
        }

        return $company_data;
    }

    public function getTotalCompany() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "company` WHERE customer_id = '" . (int) $this->customer->getId() . "'");

        return $query->row['total'];
    }

}
