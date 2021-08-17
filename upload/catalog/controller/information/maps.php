<?php

class ControllerInformationMaps extends Controller {

    public function index() {
        $this->load->language('information/maps');

        $this->load->model('account/address');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/maps')
        );

       
        $this->load->model('tool/image');

        //settings
    
        $setting = array(
            'api_key' => $this->config->get('store_location_api_key'),
            'default_lat' => $this->config->get('store_location_default_lat'),
            'default_lng' => $this->config->get('store_location_default_lng'),
            'cluster' => $this->config->get('store_location_cluster'),
            'zoom' => $this->config->get('store_location_map_zoom'),
            "zoom_li" => $this->config->get('store_location_zoom_li')
        
        );

        $config_stores = json_encode($setting);
        $data['config_stores'] = json_decode($config_stores, true);

        //Get the categories
        $all_categories = array();

        $results = $this->db->query("SELECT id,category_name as name,icon FROM " . DB_PREFIX . "store_categories WHERE status = 1");

        foreach ($results->rows as $_result) {
            $all_categories[$_result['id']] = $_result;
        }
        $data['store_location_categories'] = json_encode($all_categories);

        $data['categories'] = array();

        foreach ($results->rows as $result) {
            $data['categories'][] = array(
                'id' => $result['id'],
                'name' => $result['name'],
            );
        }
        

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/maps', $data));
    }

    public function stores() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $category_data = file_get_contents("php://input");
            $category_data_json = json_decode($category_data, true);

            if (isset($category_data_json['categories'])) {
                $category = $category_data_json['categories'];
            } else {
                $category = '';
            }

            $stores = (isset($_GET['stores'])) ? $_GET['stores'] : null;

            $bound = '';
            $clause = '';

            $extra_sql = '';
            $country_field = '';

            if ($category) {

                $load_categories = $category;
                // $load_categories = explode(',', $category);

                $the_categories = array();

                foreach ($load_categories as $_c) {

                    if (is_numeric($_c)) {

                        $the_categories[] = $_c;
                    }
                }

                $the_categories = implode(',', $the_categories);
                $category_clause = " AND sc.category_id IN (" . $the_categories . ')';
                $clause = " AND sc.category_id IN ('" . $the_categories . "')";
            }
            ///if marker param exist
            if ($stores) {

                $stores = explode(',', $stores);

                //only number
                $store_ids = array();
                foreach ($stores as $m) {

                    if (is_numeric($m)) {
                        $store_ids[] = $m;
                    }
                }

                if ($store_ids) {

                    $store_ids = implode(',', $store_ids);
                    $clause .= " AND s.id IN ({$store_ids})";
                }
            }

            $sql = "SELECT * FROM " . DB_PREFIX . "store_location_stores as s 
					LEFT JOIN " . DB_PREFIX . "store_location_logos ON (logo_id = s.id)
					LEFT JOIN " . DB_PREFIX . "store_location_categories sc ON (s.id = sc.store_id)
					$extra_sql
					WHERE (status is NULL || status = 1) AND (`lat` != '' AND `lng` != '') {$bound} {$clause}
					GROUP BY s.id ORDER BY title ";

            $sql .= " LIMIT 10000";

            $query = $this->db->query($sql);
            // print_r($sql);
            $all_results = $query->rows;

 

            foreach ($all_results as $result) {
                $json[] = array(
                    'address' => $result['street'],
                    'category' => $result['category_id'],
                    'city' => $result['city'],
                    'id' => $result['id'],
                    'lat' => $result['lat'],
                    'lng' => $result['lng'],
                    'phone' => $result['telephone'],
                    'title' => $result['title'],
                    'zipcode' => $result['postal_code'],
                    'email' => $result['email'],
                    'link' => $result['website']
                );
            }

            echo json_encode($json);
            die;
        }
    }

}
