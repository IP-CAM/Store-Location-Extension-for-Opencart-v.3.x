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
        //Get the categories
        $all_markers = array();

        $markers_results = $this->db->query("SELECT id,marker_name as name,icon FROM " . DB_PREFIX . "store_location_markers WHERE status = 1");

        foreach ($markers_results->rows as $_result) {
            /* if ($_result['icon']) {
              $icon = $this->model_tool_image->resize($_result['icon'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
              } else {
              $icon = '';
              }
              $all_markers[$_result['id']] = array(
              'id' => $_result['id'],
              'name' => $_result['name'],
              'icon' => $icon
              ); */
            $all_markers[$_result['id']] = $_result;
        }
        $data['store_location_markers'] = json_encode($all_markers);

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
            $retorno_data = json_decode($category_data, true);

            if (isset($retorno_data['categories'])) {
                $category = $retorno_data['categories'];
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

            $days_in_words = array('sun' => 'Sun', 'mon' => 'Mon', 'tue' => 'Tues', 'wed' => 'Wed', 'thu' => 'Thur', 'fri' => 'Fri', 'sat' => 'Sat');
            $days = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');

            foreach ($all_results as $aRow) {

                if ($aRow->open_hours) {

                    $days_are = array();
                    $open_hours = json_decode($aRow->open_hours);

                    foreach ($days as $day) {

                        if (!empty($open_hours->$day)) {

                            $days_are[] = $days_in_words[$day];
                        }
                    }

                    $aRow->days_str = implode(', ', $days_are);
                }
            }

            foreach ($all_results as $result) {
                $dados[] = array(
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

            echo json_encode($dados);
            die;
            //return $all_results;
        }
    }

    /**
     * @function getDistance()
     * Calculates the distance between two address
     * 
     * @params
     * $addressFrom - Starting point
     * $addressTo - End point
     * $unit - Unit type
     * 
     * @author CodexWorld
     * @url https://www.codexworld.com
     *
     */
    public function getDistance($addressFrom, $addressTo, $unit = '') {
        // Google API key
        $apiKey = $this->config->get('store_location_api_key');

        // Change address format
        $formattedAddrFrom = str_replace(' ', '+', $addressFrom);
        $formattedAddrTo = str_replace(' ', '+', $addressTo);

        // Geocoding API request with start address
        $geocodeFrom = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $formattedAddrFrom . '&sensor=false&key=' . $apiKey);
        $outputFrom = json_decode($geocodeFrom);
        if (!empty($outputFrom->error_message)) {
            return $outputFrom->error_message;
        }

        // Geocoding API request with end address
        $geocodeTo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $formattedAddrTo . '&sensor=false&key=' . $apiKey);
        $outputTo = json_decode($geocodeTo);
        if (!empty($outputTo->error_message)) {
            return $outputTo->error_message;
        }

        // Get latitude and longitude from the geodata
        $latitudeFrom = $outputFrom->results[0]->geometry->location->lat;
        $longitudeFrom = $outputFrom->results[0]->geometry->location->lng;
        $latitudeTo = $outputTo->results[0]->geometry->location->lat;
        $longitudeTo = $outputTo->results[0]->geometry->location->lng;

        // Calculate distance between latitude and longitude
        $theta = $longitudeFrom - $longitudeTo;
        $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) + cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        // Convert unit and return distance
        $unit = strtoupper($unit);
        if ($unit == "K") {
            return round($miles * 1.609344, 2) . ' km';
        } elseif ($unit == "M") {
            return round($miles * 1609.344, 2) . ' meters';
        } else {
            return round($miles, 2) . ' miles';
        }
    }

}
