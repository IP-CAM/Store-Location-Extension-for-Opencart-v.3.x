<?php

class ControllerExtensionModuleOcStoreLocation extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('extension/module/oc_store_location');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_oc_store_location', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/oc_store_location', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/oc_store_location', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $this->load->model('customer/custom_field');

        $data['custom_fields'] = $this->model_customer_custom_field->getCustomFields();
        $data['link_custom_field'] = $this->url->link('customer/custom_field', 'user_token=' . $this->session->data['user_token'], true);

        $data['maps_zoom'] = range(2, 20);
        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $config_array = array(
            'api_key',
            'map_default',
            'default_lat',
            'default_lng',
            'cluster',
            'map_zoom',
            'zoom_click',
        );
        foreach ($config_array as $config_key) {
            if (isset($this->request->post['module_oc_store_location_' . $config_key])) {
                $data[$config_key] = $this->request->post['module_oc_store_location_' . $config_key];
            } else {
                $data[$config_key] = $this->config->get('module_oc_store_location_' . $config_key);
            }
        }
        if (isset($this->request->post['module_oc_store_location_default_lat'])) {
            $data['default_lat'] = $this->request->post['module_oc_store_location_default_lat'];
        } elseif ($this->config->has('module_oc_store_location_default_lat')) {
            $data['default_lat'] = $this->config->get('module_oc_store_location_default_lat');
        } else {
            $data['default_lat'] = -23.539812;
        }
        if (isset($this->request->post['module_oc_store_location_default_lng'])) {
            $data['default_lng'] = $this->request->post['module_oc_store_location_default_lng'];
        } elseif ($this->config->has('module_oc_store_location_default_lng')) {
            $data['default_lng'] = $this->config->get('module_oc_store_location_default_lng');
        } else {
            $data['default_lng'] = -46.635178;
        }
        if (isset($this->request->post['module_oc_store_location_zoom_li'])) {
            $data['zoom_click'] = $this->request->post['module_oc_store_location_zoom_li'];
        } elseif ($this->config->has('store_location_zoom_li')) {
            $data['zoom_click'] = $this->config->get('module_oc_store_location_zoom_li');
        } else {
            $data['zoom_click'] = 13;
        }
        if (isset($this->request->post['module_oc_store_location_search_zoom'])) {
            $data['search_zoom'] = $this->request->post['module_oc_store_location_search_zoom'];
        } elseif ($this->config->has('module_oc_store_location_search_zoom')) {
            $data['search_zoom'] = $this->config->get('module_oc_store_location_search_zoom');
        } else {
            $data['search_zoom'] = 12;
        }
        if (isset($this->request->post['module_oc_store_location_website'])) {
            $data['website'] = $this->request->post['module_oc_store_location_website'];
        } else {
            $data['website'] = $this->config->get('module_oc_store_location_website');
        }
        if (isset($this->request->post['module_oc_store_location_status'])) {
            $data['status'] = $this->request->post['module_oc_store_location_status'];
        } else {
            $data['status'] = $this->config->get('module_oc_store_location_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/oc_store_location', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/oc_store_location')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install() {
        $this->load->model('extension/module/oc_store_location/store');
        $this->model_extension_module_oc_store_location_store->install();
        $this->load->model('setting/event');
        $this->model_setting_event->addEvent('admin_menu_oc_store_location', 'admin/view/common/column_left/before', 'extension/module/oc_store_location/menuStoreLocation', 1, -10);
    }

    public function uninstall() {
        $this->load->model('extension/module/oc_store_location/store');
        $this->model_extension_module_oc_store_location_store->uninstall();
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('admin_menu_oc_store_location');
    }

    public function menuStoreLocation(&$route, &$data) {
        $this->load->language('extension/module/oc_store_location');

        $store_location = array();

        if ($this->user->hasPermission('access', 'extension/module/oc_store_location/store')) {
            $store_location[] = array(
                'name' => $this->language->get('text_stores'),
                'href' => $this->url->link('extension/module/oc_store_location/store', 'user_token=' . $this->session->data['user_token'], true),
                'children' => array()
            );
        }

        if ($this->user->hasPermission('access', 'extension/module/oc_store_location/category')) {
            $store_location[] = array(
                'name' => $this->language->get('text_category'),
                'href' => $this->url->link('extension/module/oc_store_location/category', 'user_token=' . $this->session->data['user_token'], true),
                'children' => array()
            );
        }
        if ($this->user->hasPermission('access', 'extension/module/oc_store_location')) {
            $store_location[] = array(
                'id' => 'menu-store-location-setting',
                'icon' => 'fa fa-users fw',
                'name' => $this->language->get('text_setting'),
                'href' => $this->url->link('extension/module/oc_store_location', 'user_token=' . $this->session->data['user_token'], TRUE),
                'children' => array()
            );
        }
        if ($store_location) {
            $data['menus'][] = array(
                'id' => 'menu-store-location',
                'icon' => 'fa-map',
                'name' => $this->language->get('text_maps'),
                'href' => '',
                'children' => $store_location
            );
        }
    }
}
