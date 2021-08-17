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
       

        $data['continue'] = $this->url->link('common/home');
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $map_url = '//maps.googleapis.com/maps/api/js?libraries=places,drawing';

        if ($this->config->get('store_location_api_key') && $this->config->get('store_location_api_key')) {
            $map_url .= '&key=' . $this->config->get('store_location_api_key');
        }
        /*
          //map language and region
          if (isset($this->config->get('store_location_map_language')) && $this->config->get('store_location_map_language')) {
          $map_url .= '&language=' . $this->config->get('store_location_map_language');
          }
          if (isset($this->config->get('store_location_map_region')) && $this->config->get('store_location_map_region')) {
          $map_url .= '&region=' . $this->config->get('store_location_map_region');
          } */

        $data['asl_google_maps'] = $map_url;
        $data['display_list'] = $this->config->get('store_location_display_list');
        $data['prompt_location'] = $this->config->get('store_location_prompt_location');
        $data['geo_button'] = ($this->config->get('store_location_geo_button') == '1') ? $this->language->get('Current Location') : $this->language->get('Search Location');
        $data['head_title'] = $this->config->get('store_location_head_title');
        $data['panel_order'] = ($this->config->get('store_location_map_top')) ? $this->config->get('store_location_map_top') : '2';
        $data['search_type_class'] = ($this->config->get('store_location_search_type') == '1') ? 'asl-search-name' : 'asl-search-address';
        $data['geo_btn_class'] = ($this->config->get('store_location_geo_button') == '1') ? 'asl-geo icon-direction-outline' : 'icon-search';

        // $data['url'] = $this->store();

        $data['config_stores'] = json_encode(array(
            'api_key' => $this->config->get('store_location_api_key'),
            'default_lat' => $this->config->get('store_location_default_lat'),
            'default_lng' => $this->config->get('store_location_default_lng'),
            'time_format' => $this->config->get('store_location_time_format')
        ));
        $data['URL_PATH'] = $this->config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER . '/';
        $data['ajax_url'] = $this->url->link('information/maps/stores');
        $this->load->model('tool/image');

        //settings
        $styles1 = "[{
                                'category': 'userPosition',
                                'colorBackground': '#33ccff',
                                'colorText': '#fff'
                            },{'featureType':'administrative','elementType':'labels.text.fill','stylers':[{'color':'#6195a0'}]},{'featureType':'administrative.province','elementType':'geometry.stroke','stylers':[{'visibility':'off'}]},{'featureType':'landscape','elementType':'geometry','stylers':[{'lightness':'0'},{'saturation':'0'},{'color':'#f5f5f2'},{'gamma':'1'}]},{'featureType':'landscape.man_made','elementType':'all','stylers':[{'lightness':'-3'},{'gamma':'1.00'}]},{'featureType':'landscape.natural.terrain','elementType':'all','stylers':[{'visibility':'off'}]},{'featureType':'poi','elementType':'all','stylers':[{'visibility':'off'}]},{'featureType':'poi.park','elementType':'geometry.fill','stylers':[{'color':'#bae5ce'},{'visibility':'on'}]},{'featureType':'road','elementType':'all','stylers':[{'saturation':-100},{'lightness':45},{'visibility':'simplified'}]},{'featureType':'road.highway','elementType':'all','stylers':[{'visibility':'simplified'}]},{'featureType':'road.highway','elementType':'geometry.fill','stylers':[{'color':'#fac9a9'},{'visibility':'simplified'}]},{'featureType':'road.highway','elementType':'labels.text','stylers':[{'color':'#4e4e4e'}]},{'featureType':'road.arterial','elementType':'labels.text.fill','stylers':[{'color':'#787878'}]},{'featureType':'road.arterial','elementType':'labels.icon','stylers':[{'visibility':'off'}]},{'featureType':'transit','elementType':'all','stylers':[{'visibility':'simplified'}]},{'featureType':'transit.station.airport','elementType':'labels.icon','stylers':[{'hue':'#0a00ff'},{'saturation':'-77'},{'gamma':'0.57'},{'lightness':'0'}]},{'featureType':'transit.station.rail','elementType':'labels.text.fill','stylers':[{'color':'#43321e'}]},{'featureType':'transit.station.rail','elementType':'labels.icon','stylers':[{'hue':'#ff6c00'},{'lightness':'4'},{'gamma':'0.75'},{'saturation':'-68'}]},{'featureType':'water','elementType':'all','stylers':[{'color':'#eaf6f8'},{'visibility':'on'}]},{'featureType':'water','elementType':'geometry.fill','stylers':[{'color':'#c7eced'}]},{'featureType':'water','elementType':'labels.text.fill','stylers':[{'lightness':'-49'},{'saturation':'-53'},{'gamma':'0.79'}]}]";
        $styles2 = "[{featureType:\"landscape\",elementType:\"all\",stylers:[{visibility:\"on\"},{color:\"#f3f4f4\"}]},{featureType:\"landscape.man_made\",elementType:\"geometry\",stylers:[{weight:.9},{visibility:\"off\"}]},{featureType:\"poi.park\",elementType:\"geometry.fill\",stylers:[{visibility:\"on\"},{color:\"#83cead\"}]},{featureType:\"road\",elementType:\"all\",stylers:[{visibility:\"on\"},{color:\"#ffffff\"}]},{featureType:\"road\",elementType:\"labels\",stylers:[{visibility:\"off\"}]},{featureType:\"road.highway\",elementType:\"all\",stylers:[{visibility:\"on\"},{color:\"#fee379\"}]},{featureType:\"road.arterial\",elementType:\"all\",stylers:[{visibility:\"on\"},{color:\"#fee379\"}]},{featureType:\"water\",elementType:\"all\",stylers:[{visibility:\"on\"},{color:\"#7fc8ed\"}]}]";

        $setting = array(
            'api_key' => $this->config->get('store_location_api_key'),
            'default_lat' => $this->config->get('store_location_default_lat'),
            'default_lng' => $this->config->get('store_location_default_lng'),
            'time_format' => $this->config->get('store_location_time_format'),
            'cluster' => $this->config->get('store_location_cluster'),
            'prompt_location' => $this->config->get('store_location_prompt_location'),
            'map_type' => $this->config->get('store_location_map_default'),
            'distance_unit' => $this->config->get('store_location_distance_unit'),
            'zoom' => $this->config->get('store_location_map_zoom'),
            'show_categories' => '1',
            'additional_info' => '1',
            'distance_slider' => '1',
            'layout' => '0',
            'map_layout' => $styles1,
            'infobox_layout' => '0',
            'advance_filter' => "1",
            "color_scheme" => "0",
            "time_switch" => "1",
            "category_marker" => "0",
            "load_all" => "1",
            "head_title" => "Number Of Shops",
            "font_color_scheme" => "1",
            "template" => "0",
            "color_scheme_1" => "0",
            "display_list" => "1",
            "full_width" => "0",
            "time_format" => "0",
            "category_title" => $this->config->get('store_location_category_title'),
            "no_item_text" => "Nenhum lojista encontrado",
            "zoom_li" => $this->config->get('store_location_zoom_li'), 
            "single_cat_select" => "0",
            "country_restrict" => "",
            "google_search_type" => "",
            "color_scheme_2" => "0",
            "analytics" => "0",
            "sort_by_bound" => "0",
            "scroll_wheel" => "0",
            "mobile_optimize" => null,
            "mobile_load_bound" => null,
            "search_type" => "0",
            "search_destin" => "0",
            "full_height" => "",
            "map_language" => $this->config->get('store_location_map_language'),
            "map_region" => $this->config->get('store_location_map_region'),
            "sort_by" => "",
            "distance_control" => "0",
            "dropdown_range" => "20,40,60,80,*100",
            "target_blank" => "1",
            "fit_bound" => "1",
            "info_y_offset" => "",
            "cat_sort" => "name_",
            "geo_button" => "1",
            "week_hours" => "0",
            "user_center" => "0",
            "smooth_pan" => "0",
            "search_zoom" => "14",
            "stores_limit" => "1000",
            "radius_circle" => "0",
            "cat_in_grid" => "1",
            "first_load" => "1",
            "map_top" => "0",
            "direction_redirect" => $this->config->get('store_location_direction_redirect'),
            "color_scheme_3" => "0",
            "category_bound" => "1",
            "admin_notify" => "0",
            "notify_email" => "",
            "URL" => HTTP_SERVER . 'image/catalog/storelocation/',
            "PLUGIN_URL" => "index.php?route=information/maps",
            "map_id" => "1",
            "search_2" => "",
            "hour" => "1",
            "active_marker" => "active.png",
        );

        //For Translation	
        $words = array(
            'direction' => 'Traçar Rota',
            'zoom' => 'Zoom',
            'detail' => 'Website',
            'select_option' => 'Select Option',
            'search' => 'Search',
            'all_selected' => 'All selected',
            'none' => 'None',
            'none_selected' => 'None Selected',
            'reset_map' => 'Reset Map',
            'reload_map' => 'Scan Area',
            'selected' => 'selected',
            'current_location' => 'Localização atual',
            'your_cur_loc' => 'A sua localização atual',
            /* Template words */
            'Miles' => 'Miles',
            'Km' => 'Km',
            'phone' => 'Phone',
            'fax' => 'Fax',
            'directions' => 'Traçar Rota',
            'distance' => 'Distância',
            'read_more' => 'Read more',
            'hide_more' => 'Hide Details',
            'select_distance' => 'Select Distance',
            'none_distance' => 'None',
            'cur_dir' => 'Current+Location',
            'radius_circle' => 'Radius Circle',
                /*
                  'carry_out' 			=> __('Carry out:','asl_locator'),
                  'dine_in' 			=> __('Dine In:','asl_locator'),
                  'delivery' 			=> __('Delivery:','asl_locator'),
                 */
        );

        $setting['words'] = $words;
        $setting['days'] = array('sun' => 'Sun', 'mon' => 'Mon', 'tue' => 'Tues', 'wed' => 'Wed', 'thu' => 'Thur', 'fri' => 'Fri', 'sat' => 'Sat');
        $data['config_stores'] = json_encode($setting);

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
            );*/
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

        $category = (isset($_GET['category'])) ? $_GET['category'] : null;
        $stores = (isset($_GET['stores'])) ? $_GET['stores'] : null;

        $bound = '';
        $clause = '';

        $extra_sql = '';
        $country_field = '';

        if ($category) {

            $load_categories = explode(',', $category);
            $the_categories = array();

            foreach ($load_categories as $_c) {

                if (is_numeric($_c)) {

                    $the_categories[] = $_c;
                }
            }

            $the_categories = implode(',', $the_categories);
            $category_clause = " AND category_id IN (" . $the_categories . ')';
            $clause = " AND " . DB_PREFIX . "store_location_categories.`category_id` IN (" . $the_categories . ")";
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
                $clause .= " AND s.id` IN ({$store_ids})";
            }
        }


        $sql = "SELECT s.`id`, `title`,  `description`, `street`,  `city`,  `state`, `postal_code`, `lat`,`lng`,`telephone`,  `fax`,`email`,`website`,`logo_id`," . DB_PREFIX . "store_location_logos.`path`,`marker_id`,`description_2`,`open_hours`,
					group_concat(sc.category_id) as categories FROM " . DB_PREFIX . "store_location_stores as s 
					LEFT JOIN " . DB_PREFIX . "store_location_logos ON (logo_id = s.id)
					LEFT JOIN " . DB_PREFIX . "store_location_categories sc ON (s.id = sc.store_id)
					$extra_sql
					WHERE (status is NULL || status = 1) AND (`lat` != '' AND `lng` != '') {$bound} {$clause}
					GROUP BY s.`id` ORDER BY `title` ";

        $sql .= " LIMIT 10000";

        $query = $this->db->query($sql);
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

        echo json_encode($all_results);
        die;
        //return $all_results;
    }

}
