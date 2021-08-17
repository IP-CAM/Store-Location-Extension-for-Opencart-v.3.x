<?php

use \WebAPI;

class ControllerWebApiSetting extends Controller {
      private $conf;
      
       public function __construct($registry){
        parent::__construct($registry);

        $this->conf = $this->webapi->loadInfoData();
    }
    public function colorScheme() {
//print_r( $this->webapi->loadInfoData());
        $params = array(
            'primaryColor' => preg_replace('/(\'|")/', "",$this->conf->primary_color),
            'secondaryColor' => addslashes($this->conf->secondary_color),
            'fontColor' => addslashes($this->conf->font_color)
        );

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($params,true));
    }

    public function info() {
      //  print_r($this->conf->font_color);
        if ($this->request->server['HTTPS']) {
            $server = $this->config->get('config_ssl');
        } else {
            $server = $this->config->get('config_url');
        }

        if (is_file(DIR_IMAGE . $this->conf->logo)) {
            $logo = $server . 'image/' . $this->conf->logo;
        } else {
            $logo = '';
        }

        $params = array(
            'logo' => $logo,
            'name' => $this->conf->name
        );

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($params));
    }

}
