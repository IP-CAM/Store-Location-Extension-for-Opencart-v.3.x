<?php  
class ControllerExtensionModulePdfcatalog extends Controller {

	public function index() {
		$this->load->language('extension/module/pdf_catalog');	
		//$data=array();
		
		
		$this->load->model('catalog/manufacturer');
		$this->load->model('catalog/pdf_catalog');
		$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_theme') . '/stylesheet/pdf_catalog.css');
                
		$data['pdf_catalog_href'] = HTTP_SERVER . 'index.php?route=product/pdf_catalog&category_id=';

		
		if($this->config->get('pdf_catalog_display_categories')){
                    
			if($this->config->get('module_pdf_catalog_display_subcategories') == 0){
				$categories = $this->model_catalog_pdf_catalog->getMaincategories();
           }else{
				$categories = $this->model_catalog_pdf_catalog->getCategories(0);
			}
     
		}
		$data['categories']= $categories;
		$data['id']= $this->language->get('heading_title');
		
		return $this->load->view('extension/module/pdf_catalog', $data);


	
	}
}

