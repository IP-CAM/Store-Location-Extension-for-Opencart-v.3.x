<?php

class ControllerWebApiCustomer extends Controller {

    private $error = array();

    public function login() {

        $this->load->model('account/customer');

        // Login override for admin users
        if (!empty($this->request->get['token'])) {
            $this->customer->logout();
            $this->cart->clear();

            unset($this->session->data['order_id']);
            unset($this->session->data['payment_address']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['shipping_address']);
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['comment']);
            unset($this->session->data['coupon']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);

            $customer_info = $this->model_account_customer->getCustomerByToken($this->request->get['token']);

            if ($customer_info && $this->customer->login($customer_info['email'], '', true)) {
                // Default Addresses
                $this->load->model('account/address');

                if ($this->config->get('config_tax_customer') == 'payment') {
                    $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                }

                if ($this->config->get('config_tax_customer') == 'shipping') {
                    $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                }

                return new Action('extension/restapi/account/account');

                //$this->response->redirect($this->url->link('extension/restapi/account/account', '', true));
            }
        }

        if ($this->customer->isLogged()) {
            return new Action('extension/restapi/account/account');
            //$this->response->redirect($this->url->link('extension/restapi/account/account', '', true));
        }

        $this->load->language('account/login');

        $this->document->setTitle($this->language->get('heading_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            // Unset guest
            unset($this->session->data['guest']);

            // Default Shipping Address
            $this->load->model('account/address');

            if ($this->config->get('config_tax_customer') == 'payment') {
                $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
            }

            if ($this->config->get('config_tax_customer') == 'shipping') {
                $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
            }

            // Wishlist
            if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
                $this->load->model('account/wishlist');

                foreach ($this->session->data['wishlist'] as $key => $product_id) {
                    $this->model_account_wishlist->addWishlist($product_id);

                    unset($this->session->data['wishlist'][$key]);
                }
            }

            // Add to activity log
            $this->load->model('account/activity');

            $activity_data = array(
                'customer_id' => $this->customer->getId(),
                'name' => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
            );

            $this->model_account_activity->addActivity('login', $activity_data);

            // Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
            if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
                return new Action('extension/restapi/account/account');
            } else {

                return new Action('extension/restapi/account/account');
            }
        }

        
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
            $data['error'] = $this->error['warning'];
        } else {
            $data['error'] = '';
        }


        // Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
        if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
            $data['redirect'] = $this->request->post['redirect'];
        } elseif (isset($this->session->data['redirect'])) {
            $data['redirect'] = $this->session->data['redirect'];

            unset($this->session->data['redirect']);
        } else {
            $data['redirect'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['email'])) {
            $data['email'] = $this->request->post['email'];
        } else {
            $data['email'] = '';
        }

        if (isset($this->request->post['password'])) {
            $data['password'] = $this->request->post['password'];
        } else {
            $data['password'] = '';
        }

        if (isset($_SERVER['HTTP_ORIGIN'])) {
            $this->response->addHeader("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            $this->response->addHeader('Access-Control-Allow-Credentials: ' . 'true');
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    protected function validate() {
        // Check how many login attempts have been made.
        $login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

        if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
            $this->error['warning'] = $this->language->get('error_attempts');
        }

        // Check if customer has been approved.
        $customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

        if ($customer_info && !$customer_info['status']) {
            $this->error['warning'] = $this->language->get('error_approved');
        }

        if (!$this->error) {
            if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
                $this->error['warning'] = $this->language->get('error_login');

                $this->model_account_customer->addLoginAttempt($this->request->post['email']);
            } else {
                $this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
            }
        }

        return !$this->error;
    }

    public function loginss() {

        if (isset($_SERVER['HTTP_ORIGIN'])) {

            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");

            header('Access-Control-Allow-Credentials: true');

            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }


        $data = file_get_contents("php://input");

        if (isset($data)) {

            $request = json_decode($data);

            $email = $request->email;

            $password = $request->password;
            print_r($email);
        }
        $customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) OR password = '" . $this->db->escape(md5($password)) . "') AND status = '1'");

        if ($customer_query->num_rows > 0) {
            $this->session->data['customer_id'] = $customer_query->row['customer_id'];

            $response = 'Your Login success';
        } else {

            $response = 'Your Login Email or Password is invalid';
        }

        echo json_encode($response);
    }

    protected function validateLogin() {
        $this->load->language('api/login');

        // Delete old login so not to cause any issues if there is an error
        unset($this->session->data['api_id']);

        $keys = array(
            'email',
            'password'
        );

        foreach ($keys as $key) {
            if (!isset($this->request->post[$key])) {
                $this->request->post[$key] = '';
            }
        }

        $json = array();

        $this->load->model('account/api');

        $api_info = $this->model_account_api->login($this->request->post['email'], $this->request->post['password']);

        if ($api_info) {
            $this->session->data['api_id'] = $api_info['api_id'];

            $json['cookie'] = $this->session->getId();

            $json['success'] = $this->language->get('text_success');
        } else {
            $json['error'] = $this->language->get('error_login');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function logins(): void {
        $this->load->language('account/login');
        $this->session->data['login_token'] = substr(bin2hex(openssl_random_pseudo_bytes(26)), 0, 26);

        $keys = json_decode(file_get_contents('php://input'), true);

        $json = [];

        /* $keys = [
          'email',
          'password'

          ]; */

        foreach ($keys as $key) {
            if (!isset($this->request->post[$key])) {
                $this->request->post[$key] = '';
                //  print_r($key);
            }
        }

        if (!isset($this->request->get['login_token']) || !isset($this->session->data['login_token']) || ($this->session->data['login_token'] != $this->request->get['login_token'])) {
            $json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
        }

        // Check how many login attempts have been made.
        $this->load->model('account/customer');

        $login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

        if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
            $json['error']['warning'] = $this->language->get('error_attempts');
        }

        // Check if customer has been approved.
        $customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

        if ($customer_info && !$customer_info['status']) {
            $json['error']['warning'] = $this->language->get('error_approved');
        } else {
            if (!$this->customer->login($keys['email'], html_entity_decode($keys['password'], ENT_QUOTES, 'UTF-8'))) {
                $json['error']['warning'] = $this->language->get('error_login');

                $this->model_account_customer->addLoginAttempt($keys['email']);
            }
        }

        if (!$json) {
            // Add customer details into session
            $this->session->data['customer'] = [
                'customer_id' => $customer_info['customer_id'],
                'customer_group_id' => $customer_info['customer_group_id'],
                'firstname' => $customer_info['firstname'],
                'lastname' => $customer_info['lastname'],
                'email' => $customer_info['email'],
                'telephone' => $customer_info['telephone'],
                'custom_field' => $customer_info['custom_field']
            ];

            // Default Shipping Address
            $this->load->model('account/address');

            $address_info = $this->model_account_address->getAddress($this->customer->getAddressId());

            if ($this->config->get('config_tax_customer') && $address_info) {
                $this->session->data[$this->config->get('config_tax_customer') . '_address'] = $address_info;
            }

            // Wishlist
            if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
                $this->load->model('account/wishlist');

                foreach ($this->session->data['wishlist'] as $key => $product_id) {
                    $this->model_account_wishlist->addWishlist($product_id);

                    unset($this->session->data['wishlist'][$key]);
                }
            }

            // Log the IP info
            $this->model_account_customer->addLogin($this->customer->getId(), $this->request->server['REMOTE_ADDR']);

            // Create customer token
            $this->session->data['customer_token'] = token(26);

            $this->model_account_customer->deleteLoginAttempts($keys['email']);

            // Unset guest
            unset($this->session->data['guest']);

            // Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
            if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false)) {
                $json['redirect'] = str_replace('&amp;', '&', $this->request->post['redirect']) . '&customer_token=' . $this->session->data['customer_token'];
            } else {
                $json['redirect'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'], true);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function token(): void {
        $this->load->language('account/login');

        if (isset($this->request->get['email'])) {
            $email = $this->request->get['email'];
        } else {
            $email = '';
        }

        if (isset($this->request->get['login_token'])) {
            $token = $this->request->get['login_token'];
        } else {
            $token = '';
        }

        // Login override for admin users
        $this->customer->logout();
        $this->cart->clear();

        unset($this->session->data['order_id']);
        unset($this->session->data['payment_address']);
        unset($this->session->data['payment_method']);
        unset($this->session->data['payment_methods']);
        unset($this->session->data['shipping_address']);
        unset($this->session->data['shipping_method']);
        unset($this->session->data['shipping_methods']);
        unset($this->session->data['comment']);
        unset($this->session->data['coupon']);
        unset($this->session->data['reward']);
        unset($this->session->data['voucher']);
        unset($this->session->data['vouchers']);

        $this->load->model('account/customer');

        $customer_info = $this->model_account_customer->getCustomerByEmail($email);

        if ($customer_info && $customer_info['token'] && $customer_info['token'] == $token && $this->customer->login($customer_info['email'], '', true)) {
            // Default Addresses
            $this->load->model('account/address');

            $address_info = $this->model_account_address->getAddress($customer_info['address_id']);

            if ($this->config->get('config_tax_customer') && $address_info) {
                $this->session->data[$this->config->get('config_tax_customer') . '_address'] = $address_info;
            }

            $this->model_account_customer->editToken($email, '');

            $this->response->redirect($this->url->link('account/account', 'language=' . $this->config->get('config_language')));
        } else {
            $this->session->data['error'] = $this->language->get('error_login');

            $this->model_account_customer->editToken($email, '');

            $this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language')));
        }
    }

    public function recover() {
        $this->load->language('account/forgotten');
        $this->load->model('account/customer');

        $json = [];

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!isset($this->request->post['email'])) {
                $json['error'] = $this->language->get('error_email');
            } elseif (!$this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
                $json['error'] = $this->language->get('error_email');
            }
            // Check if customer has been approved.
            $customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

            if ($customer_info && !$customer_info['status']) {
                $json['error'] = $this->language->get('error_approved');
            }

            if (!isset($json['error'])) {
                $this->load->model('account/customer');

                $this->model_account_customer->editCode($this->request->post['email'], token(40));

                $json['success'] = $this->language->get('text_success');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
