<?php

class ControllerExtensionModuleOcStoreLocationStore extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('extension/module/oc_store_location/store');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/oc_store_location/store');
        $this->document->addStyle('view/javascript/oc_store_location/css/oc_store_location.css');
        //$this->document->addScript('view/javascript/oc_store_location/js/oc_store_location.js');
        $this->getList();
    }

    public function add() {
        $this->load->language('setting/store');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/oc_store_location/store');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_module_oc_store_location_store->addStore($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_email'])) {
                $url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_category_id'])) {
                $url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
            }

            if (isset($this->request->get['filter_ip'])) {
                $url .= '&filter_ip=' . $this->request->get['filter_ip'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/module/oc_store_location/store', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function edit() {
        $this->load->language('extension/module/oc_store_location/store');
        $this->load->model('extension/module/oc_store_location/store');

        $this->document->setTitle($this->language->get('heading_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_module_oc_store_location_store->editStore($this->request->get['id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');
            $url = '';

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_email'])) {
                $url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_category_id'])) {
                $url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
            }

            if (isset($this->request->get['filter_ip'])) {
                $url .= '&filter_ip=' . $this->request->get['filter_ip'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }


            $this->response->redirect($this->url->link('extension/module/oc_store_location/store', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete() {
        $this->load->language('extension/module/oc_store_location/store');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/module/oc_store_location/store');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $id) {
                $this->model_extension_module_oc_store_location_store->deleteStore($id);
            }
            $url = '';

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_email'])) {
                $url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_category_id'])) {
                $url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
            }

            if (isset($this->request->get['filter_ip'])) {
                $url .= '&filter_ip=' . $this->request->get['filter_ip'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/module/oc_store_location/store', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = '';
        }

        if (isset($this->request->get['filter_email'])) {
            $filter_email = $this->request->get['filter_email'];
        } else {
            $filter_email = '';
        }

        if (isset($this->request->get['filter_category_id'])) {
            $filter_category_id = $this->request->get['filter_category_id'];
        } else {
            $filter_category_id = '';
        }

        if (isset($this->request->get['filter_status'])) {
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = '';
        }

       
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_email'])) {
            $url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_category_id'])) {
            $url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

               if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/oc_store_location/store', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['add'] = $this->url->link('extension/module/oc_store_location/store/add', 'user_token=' . $this->session->data['user_token'], true);
        $data['delete'] = $this->url->link('extension/module/oc_store_location/store/delete', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];

        $data['stores'] = array();

        $filter_data = array(
            'filter_name' => $filter_name,
            'filter_email' => $filter_email,
            'filter_category_id' => $filter_category_id,
            'filter_status' => $filter_status,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $store_total = $this->model_extension_module_oc_store_location_store->getTotalStores($filter_data);

        $results = $this->model_extension_module_oc_store_location_store->getStores($filter_data);
        // print_r($results);

        foreach ($results as $result) {
            
            $category_name = $this->model_extension_module_oc_store_location_store->getStoreByCategoryId($result['id']);
            
            $data['stores'][] = array(
                'id' => $result['id'],
                'title' => $result['title'],
                'category' => $category_name['category_name'],
                'street' => $result['street'],
                'email' => $result['email'],
                'telephone' => $result['telephone'],
                'status' => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'edit' => $this->url->link('extension/module/oc_store_location/store/edit', 'user_token=' . $this->session->data['user_token'] . '&id=' . $result['id'], true)
            );
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array) $this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_email'])) {
            $url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_category_id'])) {
            $url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_name'] = $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
        $data['sort_email'] = $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token'] . '&sort=c.email' . $url, true);
        $data['sort_customer_group'] = $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token'] . '&sort=customer_group' . $url, true);
        $data['sort_status'] = $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token'] . '&sort=c.status' . $url, true);
        $data['sort_ip'] = $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token'] . '&sort=c.ip' . $url, true);
        $data['sort_date_added'] = $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token'] . '&sort=c.date_added' . $url, true);

        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_email'])) {
            $url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_category_id'])) {
            $url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $store_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($store_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($store_total - $this->config->get('config_limit_admin'))) ? $store_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $store_total, ceil($store_total / $this->config->get('config_limit_admin')));

        $data['filter_name'] = $filter_name;
        $data['filter_email'] = $filter_email;
        $data['filter_category_id'] = $filter_category_id;
        $data['filter_status'] = $filter_status;
  

        $this->load->model('extension/module/oc_store_location/category');
        $data['categories'] = $this->model_extension_module_oc_store_location_category->getCategories();

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/oc_store_location/store_list', $data));
    }

    protected function getForm() {

        $this->document->addStyle('view/javascript/oc_store_location/css/oc_store_location.css');
        $this->document->addScript('view/javascript/oc_store_location/js/oc_store_location.js');

        $data['text_form'] = !isset($this->request->get['id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/oc_store_location/store', 'user_token=' . $this->session->data['user_token'], true)
        );

        if (!isset($this->request->get['id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_settings'),
                'href' => $this->url->link('extension/module/oc_store_location/store/add', 'user_token=' . $this->session->data['user_token'], true)
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_settings'),
                'href' => $this->url->link('extension/module/oc_store_location/store/edit', 'user_token=' . $this->session->data['user_token'] . '&id=' . $this->request->get['id'], true)
            );
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (!isset($this->request->get['id'])) {
            $data['action'] = $this->url->link('extension/module/oc_store_location/store/add', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/oc_store_location/store/edit', 'user_token=' . $this->session->data['user_token'] . '&id=' . $this->request->get['id'], true);
        }

        $data['cancel'] = $this->url->link('extension/module/oc_store_location/store', 'user_token=' . $this->session->data['user_token'], true);

        if (isset($this->request->get['id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $store_info = $this->model_extension_module_oc_store_location_store->getStore($this->request->get['id']);
        }

        $data['user_token'] = $this->session->data['user_token'];
        
        $this->load->model('localisation/country');
        $this->load->model('extension/module/oc_store_location/category');
        
        $data['countries'] = $this->model_localisation_country->getCountries();

        $data['categories'] = $this->model_extension_module_oc_store_location_category->getCategories();

        $data['config_stores'] = json_encode(array(
            'api_key' => $this->config->get('store_location_api_key'),
            'default_lat' => $this->config->get('store_location_default_lat'),
            'default_lng' => $this->config->get('store_location_default_lng')
        ));

       // $data['new_marker'] = $this->url->link('extension/module/oc_store_location/markers/add', 'user_token=' . $this->session->data['user_token'], true);

        if (isset($this->request->post['title'])) {
            $data['title'] = $this->request->post['title'];
        } elseif (!empty($store_info)) {
            $data['title'] = $store_info['title'];
        } else {
            $data['title'] = '';
        }
        if (isset($this->request->post['description'])) {
            $data['description'] = $this->request->post['description'];
        } elseif (!empty($store_info)) {
            $data['description'] = $store_info['description'];
        } else {
            $data['description'] = '';
        }
        if (isset($this->request->post['street'])) {
            $data['street'] = $this->request->post['street'];
        } elseif (!empty($store_info)) {
            $data['street'] = $store_info['street'];
        } else {
            $data['street'] = '';
        }
        if (isset($this->request->post['city'])) {
            $data['city'] = $this->request->post['city'];
        } elseif (!empty($store_info)) {
            $data['city'] = $store_info['city'];
        } else {
            $data['city'] = '';
        }
        if (isset($this->request->post['state'])) {
            $data['state'] = $this->request->post['state'];
        } elseif (!empty($store_info)) {
            $data['state'] = $store_info['state'];
        } else {
            $data['state'] = '';
        }
        if (isset($this->request->post['postal_code'])) {
            $data['postal_code'] = $this->request->post['postal_code'];
        } elseif (!empty($store_info)) {
            $data['postal_code'] = $store_info['postal_code'];
        } else {
            $data['postal_code'] = '';
        }
        if (isset($this->request->post['country_id'])) {
            $data['country_id'] = $this->request->post['country_id'];
        } elseif (!empty($store_info)) {
            $data['country_id'] = $store_info['country_id'];
        } else {
            $data['country_id'] = $this->config->get('config_country_id');
        }
       
        if (isset($this->request->post['lat'])) {
            $data['lat'] = $this->request->post['lat'];
        } elseif (!empty($store_info)) {
            $data['lat'] = $store_info['lat'];
        } else {
            $data['lat'] = 0.0;
        }
        if (isset($this->request->post['lng'])) {
            $data['lng'] = $this->request->post['lng'];
        } elseif (!empty($store_info)) {
            $data['lng'] = $store_info['lng'];
        } else {
            $data['lng'] = 0.0;
        }
        if (isset($this->request->post['telephone'])) {
            $data['telephone'] = $this->request->post['telephone'];
        } elseif (!empty($store_info)) {
            $data['telephone'] = $store_info['telephone'];
        } else {
            $data['telephone'] = '';
        }
        if (isset($this->request->post['fax'])) {
            $data['fax'] = $this->request->post['fax'];
        } elseif (!empty($store_info)) {
            $data['fax'] = $store_info['fax'];
        } else {
            $data['fax'] = '';
        }
        if (isset($this->request->post['email'])) {
            $data['email'] = $this->request->post['email'];
        } elseif (!empty($store_info)) {
            $data['email'] = $store_info['email'];
        } else {
            $data['email'] = '';
        }
        if (isset($this->request->post['website'])) {
            $data['website'] = $this->request->post['website'];
        } elseif (!empty($store_info)) {
            $data['website'] = $store_info['website'];
        } else {
            $data['website'] = '';
        }

        // Categories
        $this->load->model('extension/module/oc_store_location/category');

        if (isset($this->request->post['store_category'])) {
            $categories = $this->request->post['store_category'];
        } elseif (isset($this->request->get['id'])) {
            $categories = $this->model_extension_module_oc_store_location_category->getStoreCategories($this->request->get['id']);
        } else {
            $categories = array();
        }

        $data['store_categories'] = array();

        foreach ($categories as $category_id) {
            $category_info = $this->model_extension_module_oc_store_location_category->getCategory($category_id);
            if ($category_info) {
                $data['store_categories'][] = array(
                    'category_id' => $category_info['id'],
                    'name' => $category_info['category_name']
                );
            }
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($store_info)) {
            $data['status'] = $store_info['status'];
        } else {
            $data['status'] = '';
        }


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/oc_store_location/store_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'extension/module/oc_store_location/store')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'extension/module/oc_store_location/store')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function autocomplete() {
        $json = array();

        if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_email']) || isset($this->request->get['filter_cliente'])) {
            if (isset($this->request->get['filter_name'])) {
                $filter_name = $this->request->get['filter_name'];
            } else {
                $filter_name = '';
            }

            if (isset($this->request->get['filter_email'])) {
                $filter_email = $this->request->get['filter_email'];
            } else {
                $filter_email = '';
            }

            if (isset($this->request->get['filter_cliente'])) {
                $filter_cliente = $this->request->get['filter_cliente'];
            } else {
                $filter_cliente = '';
            }

            $this->load->model('customer/customer');

            $filter_data = array(
                'filter_name' => $filter_name,
                'filter_email' => $filter_email,
                'filter_cliente' => $filter_cliente,
                'start' => 0,
                'limit' => 10
            );

            $results = $this->model_customer_customer->getCustomers($filter_data);

            foreach ($results as $result) {
                if (isset(json_decode($result['custom_field'], true)[$this->config->get('module_oc_store_location_custom_field_website')])) {
                    $site = json_decode($result['custom_field'], true)[$this->config->get('module_oc_store_location_custom_field_website')];
                } else {
                    $site = '';
                }

                $address_info = $this->model_customer_customer->getAddresses($result['customer_id']);

                foreach ($address_info as $address) {
                    $address_id = $address['address_id'];
                    $address_1 = $address['address_1'];
                    $address_2 = $address['address_2'];
                    $city = $address['city'];
                    $postcode = $address['postcode'];
                    $country_id = $address['country_id'];
                    $zone = $address['zone'];
                }
                $json[] = array(
                    'customer_id' => $result['customer_id'],
                    'customer_group_id' => $result['customer_group_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'customer_group' => $result['customer_group'],
                    'firstname' => $result['firstname'],
                    'lastname' => $result['lastname'],
                    'email' => $result['email'],
                    'telephone' => $result['telephone'],
                    'website' => $site,
                    'street' => $address_1,
                    'city' => $city,
                    'state' => $zone,
                    'postal_code' => $postcode,
                    'custom_field' => json_decode($result['custom_field'], true),
                        //'address' => $this->model_customer_customer->getAddresses($result['customer_id'])
                );
            }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
