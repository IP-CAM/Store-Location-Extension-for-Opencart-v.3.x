<?php
class ControllerExtensionModuleCustomerStore extends Controller {
	public function index() {
        if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customer_store', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		$this->load->language('extension/module/customer_store');


		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/customer_store', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_customer_store'),
			'href' => $this->url->link('account/customer_store', '', true)
		);

        $this->load->model('extension/module/customer_store');


		$data['customer_stores'] = array();

		$download_total = $this->model_extension_module_customer_store->getTotalCustomerStores();

		$results = $this->model_extension_module_customer_store->getCustomerStore($this->customer->getId());

		foreach ($results as $result) {
			//if (file_exists(DIR_DOWNLOAD . $result['filename'])) {
				
				$data['customer_stores'][] = array(
					'store_id'   => $result['store_id'],
                    'url'   => $result['url'],
                    'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
                    'check'       => $this->url->link('extension/module/customer_store/check', 'store_id=' . $result['store_id'], true),
					'href'       => $this->url->link('account/download/download', 'download_id=' . $result['download_id'], true)
				);
			//}
		}

		$pagination = new Pagination();
		$pagination->total = $download_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		$pagination->url = $this->url->link('account/download', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($download_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($download_total - 10)) ? $download_total : ((($page - 1) * 10) + 10), $download_total, ceil($download_total / 10));
		
		$data['continue'] = $this->url->link('account/account', '', true);
		$data['add'] = $this->url->link('extension/module/customer_store/add', '', true);


        $data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/module/customer_store', $data));

	}
    public function add() {
		$this->load->language('extension/module/customer_store');

		$this->document->setTitle($this->language->get('heading_title'));


        $this->response->setOutput($this->load->view('extension/module/customer_store_form', $data));
	
	}
    public function addStore() {
		$this->load->language('extension/module/customer_store');
        $this->load->model('extension/module/customer_store');

        $json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->request->post['url'] == "") {
                $json['error'] = $this->language->get('error_url');
			}
      
        $customer_info = $this->model_extension_module_customer_store->GetCustomerStoreByUrl($this->request->post['url']);

		if ($customer_info) {
			$json['error'] = $this->language->get('error_store');
		}
		
			if (!isset($json['error'])) {
                $this->load->model('extension/module/customer_store');
               $this->model_extension_module_customer_store->addCustomerStore($this->request->post);
				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    public function check() {
		$this->load->language('extension/module/customer_store');
        $this->load->model('extension/module/customer_store');

        $json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->request->post['store_id']) {
                $store_id = $this->request->post['store_id'];
			}else{
                $store_id = '';
            }
               $this->load->model('extension/module/customer_store');
               $this->model_extension_module_customer_store->checkUrl($store_id);
			
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}