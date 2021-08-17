<?php
class ControllerExtensionModuleSMService extends Controller {
	public function index($setting) {
	print_r($setting['module_sm_service']);
		if (isset($setting['module_sm_service'])) {
			$data['title'] = html_entity_decode($setting['module_sm_service_description'][$this->config->get('config_language_id')]['title'], ENT_QUOTES, 'UTF-8');
			//$data['description'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
		
		$data['services'] = array();

        foreach ($setting['module_sm_service'] as $service) {

            $data['services'][] = array(
			    'title' => $service['title'],
				'description' => $service['description'],
                'icon' => $service['icon'],
                'sort_order' => $service['sort_order']
            );
        }

			
			return $this->load->view('extension/module/service', $data);
		}
	}
}