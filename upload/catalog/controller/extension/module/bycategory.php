<?php
class ControllerExtensionModuleBycategory extends Controller {
public function index($setting) {
	//echo "<pre>";print_r($setting);exit;
	$this->load->model('catalog/product');
	$this->load->model('catalog/category');
	$this->load->model('tool/image');
	
		$data['products'] = array();
print_r($setting['category_id']);
		$data['category_info'] = $this->model_catalog_category->getCategory($setting['category_id']);
		//echo "<pre>";print_r($data['category_info']);exit;
		if($data['category_info']){
			$data['heading_title'] = $data['category_info']['name'];
		}else{
			$data['heading_title'] = '';
		}

		$data['carousel_id'] = $setting['category_id'];//$this->friendlyURL($data['heading_title']);
		
		if($data['category_info']){
			$data['catimag'] = $this->model_tool_image->resize($data['category_info']['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
		}else{
			$data['catimag'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
		}


		$data['category_link'] = $this->url->link('product/category', 'path=' . $setting['category_id'],'SSL');

		$results = $this->model_catalog_product->getProductsByCategoryRandom($setting['category_id'],$setting['limit']);
		//echo $setting['category_id'];
		foreach ($results as $result) {
	
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 200, 200);
			}


			if ((float)$result['price']) {
				$mrp_percentage = round(100 - (($result['price']*100) / $result['price']));
			} else {
				$mrp_percentage = false;
			}
			
			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')),$this->session->data['currency']);
			} else {
				$price = false;
			}
					
			if ((float)$result['special']) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')),$this->session->data['currency']);
			} else {
				$special = false;
			}
			
			if ($this->config->get('config_review_status')) {
				$rating = $result['rating'];
			} else {
				$rating = false;
			}
			$data['products'][] = array(
                                'customer_group_id' => 2,
				'name' 			=> utf8_substr(strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')), 0, 50),
				'quantity'     	=> $result['quantity'],
				'product_id' 	=> $result['product_id'],
				'model' 		=> $result['model'],
				'description' 	=> html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
				'thumb'   	 	=> $image,
				'price'   	 	=> $price,
				'rating'     	=> $rating,
				'special' 	 	=> $special,
				'href'     	 	=> $this->url->link('product/product', 'product_id=' . $result['product_id'],'SSL')
			);
		}
			return $this->load->view('extension/module/bycategory', $data);
	}
    
function friendlyURL($string){
	$string = preg_replace("`\[.*\]`U","",$string);
	$string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$string);
	$string = htmlentities($string, ENT_COMPAT, 'utf-8');
	$string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $string );
	$string = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $string);
	return strtolower(trim($string, '-'));
}
}
?>