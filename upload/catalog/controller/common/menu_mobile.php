<?php

class ControllerCommonMenuMobile extends Controller {

    public function index() {
        $this->load->language('common/menu_mobile');
        $this->load->language('common/header');

        // Menu
        $this->load->model('catalog/category');

        $this->load->model('catalog/product');
        $data['text_items'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0));

        $data['text_logged_user'] = sprintf($this->language->get('text_logged_user'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
        $data['logged'] = $this->customer->isLogged();
        $data['text_welcome_user'] = sprintf($this->language->get('text_welcome_user'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));            
		$data['contact'] = $this->url->link('information/contact');


        $data['categories'] = array();


        $categories = $this->model_catalog_category->getCategories(0);
        $i = 1;
        foreach ($categories as $category) {
            if ($category['top']) {
                // Level 2
                $children_data = array();

                $children = $this->model_catalog_category->getCategories($category['category_id']);

                foreach ($children as $child) {

                    // Level 3
                    $grandchildren_data = array();

                    $grandchildren = $this->model_catalog_category->getCategories($child['category_id']);

                    foreach ($grandchildren as $grandchild) {

                        $grandchild_filter_data = array(
                            'filter_category_id' => $grandchild['category_id'],
                            'filter_sub_category' => true
                        );

                        $grandchildren_data[] = array(
                            //'name'  => $grandchild['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($grandchild_filter_data) . ')' : ''),

                            'name' => $grandchild['name'] . ' (' . $this->model_catalog_product->getTotalProducts($grandchild_filter_data) . ')',
                            'href' => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $grandchild['category_id'])
                        );
                    }


                    $filter_data = array(
                        'filter_category_id' => $child['category_id'],
                        'filter_sub_category' => true
                    );

                    $children_data[] = array(
                        'name' => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
                        'href' => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
                        'children' => $grandchildren_data,
                    );
                }

                // Level 1
                $data['categories'][] = array(
                    'name' => $category['name'],
                    'children' => $children_data,
                    'column' => $category['column'] ? $category['column'] : 1,
                    'href' => $this->url->link('product/category', 'path=' . $category['category_id']),
                    'count_menu' => $i++
                );
            }
        }

        return $this->load->view('common/mobile/menu', $data);
    }

}
