<?php 
class ControllerAccountMultiLogin extends Controller {
	public function index() {

		$this->language->load('account/multi_login');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_logout'),
			'href' => $this->url->link('account/multi_login', '', true)
		);
		
		if (isset($this->request->get['time'])) {
			$time = gmdate("i", $this->request->get['time']);
		} else {
			$time = '0';
		}
		$data['text_message'] = sprintf($this->language->get('text_message'), $time);

		$data['continue'] = $this->url->link('common/home');


		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));

		
	}
}
