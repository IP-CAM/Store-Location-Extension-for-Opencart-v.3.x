<?php

class ControllerAccountNavTabs extends Controller {

    public function index() {
        //$this->load->language('extension/module/account');
        $this->load->language('account/nav_tabs');

        if (isset($this->request->get['route'])) {
            $data['route'] = $this->request->get['route'];
        } else {
            $data['route'] = '';
        }

        $data['logged'] = $this->customer->isLogged();
        $data['register'] = $this->url->link('account/register', '', true);
        $data['login'] = $this->url->link('account/login', '', true);
        $data['logout'] = $this->url->link('account/logout', '', true);
        $data['forgotten'] = $this->url->link('account/forgotten', '', true);
        $data['account'] = $this->url->link('account/account', '', true);
        $data['edit'] = $this->url->link('account/edit', '', true);
        $data['password'] = $this->url->link('account/password', '', true);
        $data['address'] = $this->url->link('account/address', '', true);
        $data['wishlist'] = $this->url->link('account/wishlist');
        $data['order'] = $this->url->link('account/order', '', true);
        $data['download'] = $this->url->link('account/download', '', true);
        $data['reward'] = $this->url->link('account/reward', '', true);
        $data['return'] = $this->url->link('account/return', '', true);
        $data['transaction'] = $this->url->link('account/transaction', '', true);
        $data['newsletter'] = $this->url->link('account/newsletter', '', true);
        $data['recurring'] = $this->url->link('account/recurring', '', true);

        // $this->response->setOutput($this->load->view('account/nav-tabs', $data));

        return $this->load->view('account/nav_tabs', $data);
    }

}
