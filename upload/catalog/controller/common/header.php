<?php
class ControllerCommonHeader extends Controller {
	public function index() {
            
            if($this->session->data['user_id']){
 print_r('Admin is logged in');
}else{
 print_r('Admin is not logged in');
}
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$data['title'] = $this->document->getTitle();
		$data['cache'] = time();
		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['robots'] = $this->document->getRobots();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');
                
                $data['mobile'] = $this->sincromaster->document->isMobile();
                $data['desktop'] = $this->sincromaster->document->isDesktop();
                $data['classes'] = $this->sincromaster->document->getClasses();

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$this->load->language('common/header');

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), strstr(ucfirst($this->customer->getFirstName()), " ", true), $this->url->link('account/logout', '', true));
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
                $data['account_edit'] = $this->url->link('account/edit', '', true);
                $data['address'] = $this->url->link('account/address', '', true);
               
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');

		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');

		return $this->load->view('common/header', $data);
	}
      public function device_detect() {
		try {
			$device = $this->input(self::POST, 'device');
			$session_device = self::get($this->session->data, 'j3_device');

			$reload = false;

			if ($device !== $session_device) {
				$this->session->data['j3_device'] = $device;
				$reload = true;
			}

			$this->renderJson(self::SUCCESS, array(
				'device' => $device,
				'reload' => $this->url->link('common/home'),
			));
		} catch (\Exception $e) {
			$this->renderJson(self::SUCCESS, array(
				'error' => $e->getMessage(),
			));
		}
	}  
        protected function renderJson($status, $data = array()) {
		$output = json_encode(array(
			'status'   => $status,
			'response' => $data,
			'request'  => array(
				'url'  => $this->request->server['REQUEST_URI'],
				'get'  => $this->request->get,
				'post' => $this->request->post,
			),
		));

		$output = str_replace('&amp;', '&', $output);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($output);
	}

	protected function input($method, $variable, $default = null) {
		$value = null;

		if ($method === self::GET) {
			$value = self::get($this->request->get, $variable);
		}

		if ($method === self::POST) {
			$value = self::get($this->request->post, $variable);
		}

		if ($method === self::FILE) {
			$value = self::get($this->request->files, $variable);
		}

		if ($value === null && $default !== null) {
			$value = $default;
		}

		if ($value === null) {
			throw new \Exception(sprintf($this->language->get('error_input_not_found'), $method, $variable));
		}

		return $value;
	}
        public static function get($array, $key, $default = null) {
		foreach (explode('.', $key) as $k) {
			if (!is_array($array) || !isset($array[$k])) {
				return $default;
			}

			$array = $array[$k];
		}

		return $array;
	}

}
