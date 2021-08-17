<?php

class ControllerInformationAutoAtendimento extends Controller {

    public function index() {
        $this->load->language('information/autoatendimento');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $auto_conf = $this->model_setting_setting->getSetting('module_autoatendimento');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/autoatendimento')
        );

        //if (isset($auto_conf['title'])) {
        // $data['title'] = html_entity_decode($auto_conf['module_autoatendimento']['title'], ENT_QUOTES, 'UTF-8');
        //$data['description'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
        $this->load->model('tool/image');
        $data['atendimentos'] = array();

        foreach ($auto_conf['module_autoatendimento'] as $auto) {

            if ($auto['image']) {
                $image = $this->model_tool_image->resize($auto['image'], 100, 100);
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', 100, 100);
            }

            $data['atendimentos'][] = array(
                'title' => $auto['title'],
                'id' => $this->tirarAcentos(strtolower($auto['title'])),
                'description' => $auto['description'],
                'image' => $image,
                'sort_order' => $auto['sort_order']
            );
        }
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/autoatendimento', $data));
        // }
    }
  public  function tirarAcentos($string){
  $string =  preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$string);
  $char = array(' & ', 'ª ', '  (', ') ', '(', ')', ' - ', ' / ', ' /', '/ ', '/', ' | ', ' |', '| ', ' | ', '|', '_', '.', ' ');
  return strtolower(str_replace($char, '-', $string));

}

}
