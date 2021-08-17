<?php
class ControllerInformationNovidades extends Controller {
	public function index() {
		$this->load->language('information/novidades');
		$this->document->setTitle($this->language->get('heading_title'));


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/novidades')
		);

        if ($this->config->get('config_cookie_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_cookie_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_account_id'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}


			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/novidades', $data));
		
		
	}

	public function send() {
        $this->load->language('information/novidades');
        $this->load->model('catalog/novidades');

        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
           
           
            if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
                $json['error'] = $this->language->get('error_name');
            }


            if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
                $json['error'] = $this->language->get('error_email');
            }
            if ($this->model_catalog_novidades->getTotalCustomersByEmail($this->request->post['email'])) {
                $json['error'] = $this->language->get('error_exists');
            }
            if ($this->request->post['type'] == '') { 
                $json['error'] = $this->language->get('error_type');

            } 
           // Agree to terms
		if ($this->config->get('config_cookie_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_cookie_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$json['error'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

            if (!isset($json['error'])) {
                $this->load->model('catalog/novidades');

				$this->model_catalog_novidades->addNovidades($this->request->post);
           
                $json['success'] = $this->language->get('text_message');
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
