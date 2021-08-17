<?php
class ControllerInformationDrivers extends Controller {
	public function index() {
		$this->load->language('information/drivers');

		$this->load->model('catalog/drivers');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		
			$this->document->setTitle($this->language->get('heading_title'));

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('information/drivers')
			);
			$data['drivers'] = array();
	
			$results = $this->model_catalog_drivers->getDrivers();
	
			foreach ($results as $result) {

				if ($result['mask_manual']) {
					$manual = strip_tags(html_entity_decode($result['mask_manual'], ENT_QUOTES, 'UTF-8'));

				} else {
					$manual = '';
				}
			
				$data['drivers'][] = array(
					'driver_id'    => $result['driver_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'system'      => strip_tags(html_entity_decode($result['os'], ENT_QUOTES, 'UTF-8')),
					'mask'        => strip_tags(html_entity_decode($result['mask'], ENT_QUOTES, 'UTF-8')),
					'tags'        => strip_tags(html_entity_decode($result['tags'], ENT_QUOTES, 'UTF-8')),
					'manual'      => $manual,
				    'code_manual' => $this->url->link('information/drivers/download_manual', 'driver_id=' . $result['driver_id'], true),
					'code'       => $this->url->link('information/drivers/download', 'driver_id=' . $result['driver_id'], true)
	
				);
			}
			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/drivers', $data));
		
	
  }
 
public function download() {
	
	$this->load->model('catalog/drivers');

	if (isset($this->request->get['driver_id'])) {
		$driver_id = $this->request->get['driver_id'];
	} else {
		$driver_id = 0;
	}
	$drivers_info = $this->model_catalog_drivers->getDriver($driver_id);
    $this->model_catalog_drivers->AddDriverTotalDownloadsByProductId($driver_id);

	if ($drivers_info) {
		$file = DIR_DOWNLOAD . $drivers_info['filename'];
		$mask = basename($drivers_info['mask']);

		if (!headers_sent()) {
			if (file_exists($file)) {
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));

				if (ob_get_level()) {
					ob_end_clean();
				}

				readfile($file, 'rb');

				exit();
			} else {
				exit('Error: Could not find file ' . $file . '!');
			}
		} else {
			exit('Error: Headers already sent out!');
		}
	} else {
		$this->response->redirect($this->url->link('information/drivers', '', true));
	}
}
public function download_manual() {
	
	$this->load->model('catalog/drivers');

	if (isset($this->request->get['driver_id'])) {
		$driver_id = $this->request->get['driver_id'];
	} else {
		$driver_id = 0;
	}

	$drivers_info = $this->model_catalog_drivers->getDriver($driver_id);
    $this->model_catalog_drivers->AddDriverManualTotalDownloadsByProductId($driver_id);

	if ($drivers_info) {
		$file = DIR_DOWNLOAD . $drivers_info['file_manual'];
		$mask = basename($drivers_info['mask_manual']);

		if (!headers_sent()) {
			if (file_exists($file)) {
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));

				if (ob_get_level()) {
					ob_end_clean();
				}

				readfile($file, 'rb');

				exit();
			} else {
				exit('Error: Could not find file ' . $file . '!');
			}
		} else {
			exit('Error: Headers already sent out!');
		}
	} else {
		$this->response->redirect($this->url->link('information/drivers', '', true));
	}
}

}