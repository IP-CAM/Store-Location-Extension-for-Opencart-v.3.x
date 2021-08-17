<?php

require_once DIR_SYSTEM . 'config/si/tcpdf/tcpdf.php';

    class OCPDF extends TCPDF {
        public function Header() {
                    $logo_file = $this->logo_file ;
                    $header_details_address = $this->header_details_address;
                     $header_details_email = $this->header_details_email;
                     
                    
                    
$header_html = <<<EOD

<table  border="0" style="font-size:11px;">
<tr>
     <td>
             <img src ="$logo_file" height="150px">
     </td align="left">
     <td>
     </td>
     <td align="right">
            
     </td>       
</tr>
</table>
<br/>
<hr/>

EOD;

                    // Print text using writeHTMLCell()
                    $this->writeHTMLCell(0, 0, '', '', $header_html, 0, 1, 0, true, '', true);
                }

                // Page footer
                public function Footer() {
                    // Position at 15 mm from bottom
                    $this->SetY(-15);
                    // Set font
                    $this->SetFont('helvetica', 'I', 8);
                    // Page number
                    $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                }
                
                public function setImageFile($logo_file)
                {
                    $this->logo_file = $logo_file ;
                }
                
                public function setStoreheader_details($header_details_address ,$header_details_email)
                {
                    $this->header_details_address=$header_details_address;
                    $this->header_details_email = $header_details_email;
                }
            }

class ControllerExtensionModuleSjcateoryproductpdf extends Controller {
            private $modulePath;
            public function __construct($registry) {
            
                parent::__construct($registry);
                $this->config->load('si/sjcateoryproductpdf');
                $this->modulePath = $this->config->get('sjcateoryproductpdf_path');

            }

            public function index($settings){
                $object = json_decode(json_encode($settings), FALSE);
//                echo '<pre>';
//                print_r($settings);
//                echo '</pre>';
//                exit();
                ///
                if (isset($this->request->get['path'])) {
                    $product_id = (int)$this->request->get['path'];
                } else {
                    $product_id = 0;
                }
    //                $category = empty($this->request->get['path']) ? 0 : (int) array_pop(explode('_', $this->request->get['path']));
                
                if(isset($this->request->get['path'])) {
    $path = $this->request->get['path'];
    $cats = explode('_', $path);
    $cat_id = $cats[count($cats) - 1];
}
                
                $this->document->addStyle('catalog/view/theme/default/stylesheet/sjcateoryproductpdf.css');
                //$data['moduleData'] = $settings['sjcateoryproductpdf'];
                $data['status'] = (isset($settings['status'])) ? $settings['status'] : false;
                $data['language_id'] = $this->config->get('config_language_id');
                $data['pdffile'] = HTTPS_SERVER . 'index.php?route=extension/module/sjcateoryproductpdf/downloadproductpdf&path='.$cat_id.'&price='.$object->price.'&dproduct='.$object->disabledproduct.'&desc='.$object->description.'&manf='.$object->manufacturer.'&isize='.$object->imagesize;
                return $this->load->view($this->modulePath, $data);
            }
    
    public function downloadproductpdf(){
     
            if (isset($this->request->get['path'])) {
                $category_id = (int)$this->request->get['path'];
            } else {
                $category_id = 0;
            }
            
            if ($this->request->get['dproduct']) {
                $dproduct = 1;
            } else {
                $dproduct = 0;
            }
            
            
             $this->load->model('extension/module/sjcateoryproductpdf');
            $product_infos = $this->model_extension_module_sjcateoryproductpdf->getSjcateoryproductpdfByCategoryId($category_id,$dproduct);
                        $product_cate = $this->model_extension_module_sjcateoryproductpdf->getSjcateoryproductpdfByCategoryName($category_id);
                  
//            echo '<pre>';
//           print_r($product_cate[0]['name']);
//                  echo '</pre>';
//                  exit();
//            $product_url = $this->url->link('product/product', 'product_id=' . $this->request->get['product_id']);
//            foreach ($product_infos as $product_info) {
//            $product_name[] = $product_info['name'];
//}
//            $product_name = $product_info['name'];
            //$manufacturer = $product_info['manufacturer'];
            //$manufacturer_link = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
            //$model = $product_info['model'];
            //$reward = $product_info['reward'];
            //$points = $product_info['points'];
//            $description = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
            /*
            if ($product_info['quantity'] <= 0) {
                $stock = $product_info['stock_status'];
            } elseif ($this->config->get('config_stock_display')) {
                $stock = $product_info['quantity'];
            } else {
                $stock = $this->language->get('text_instock');
            }
            */
            $this->load->model('tool/image');
            
            /*
            if ($product_info['image']) {
                $popup = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
            } else {
                $popup = '';
            }
            */
//            if ($product_info['image']) {
//                $thumb = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
//            } else {
//                $thumb = '';
//            }

            /*$data['images'] = array();
            $results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
            $imgs = '';
            foreach ($results as $result) {
                $data['images'][] = array(
                    'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
                    'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
                );
                //$img = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'));
                //$imgs .= '<img src='.$img.'/>';
            }*/
            
//            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
//                $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
//            } else {
//                $price = false;
//            }

            /*if ((float)$product_info['special']) {
                $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $special = false;
            }*/

            /*if ($this->config->get('config_tax')) {
                $tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
            } else {
                $tax = false;
            }*/
            /*
            $discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);
            $data['discounts'] = array();
            foreach ($discounts as $discount) {
                $data['discounts'][] = array(
                    'quantity' => $discount['quantity'],
                    'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
                );
            }*/
        
            if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
                    $server = $this->config->get('config_ssl');
                } else {
                    $server = $this->config->get('config_url');
                }
        
            if ($this->config->get('config_logo') && file_exists(DIR_IMAGE . $this->config->get('config_logo'))) {
                   $logo = $server . 'image/' . $this->config->get('config_logo');
                } else {
                   $logo = '';
                }
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////    
            $header_details_address = "<u><b>".$this->config->get('config_name')."</b></u><br/>".
                               nl2br($this->config->get('config_address'));
                              
            $header_details_email = "<b>Telephone:</b> <br/>".$this->config->get('config_telephone')."<br/>".
                                    "<b>Email:</b><br/>".$this->config->get('config_email');
        
            $page_url =  $this->url->link('product/product', 'product_id=' . $category_id);
        
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $pdf = new OCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->setImageFile($logo);
            $pdf->setStoreheader_details($header_details_address,$header_details_email);

             $author = $this->config->get('pdf_catalog_author');
            $title = $this->config->get('pdf_catalog_title');
            $subject = $this->config->get('pdf_catalog_subject');
            $keywords = $this->config->get('pdf_catalog_description');
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($author);
            $pdf->SetTitle($title);
            $pdf->SetSubject($subject);
            $pdf->SetKeywords($keywords);

            // remove default header/footer
            $pdf->setPrintHeader(true);
            $pdf->setPrintFooter(true);

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
            $pdf->setFooterData(array(0,64,0), array(0,64,128));

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // set some language-dependent strings (optional)
            if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
                require_once(dirname(__FILE__).'/lang/eng.php');
                $pdf->setLanguageArray($l);
            }

            // set default font subsetting mode
            $pdf->setFontSubsetting(true);
            $pdf->AddPage();
            $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));


            
// Set content to print
//$product_content_html = <<<EOD
//<br/>
//<table border="0">
//<tr>
//      <td>
//           <div style="font-size:30px;text-align:center;padding-top:30px;">
//             <span >$product_name</span>
//            
//       
//            </div>
//      </td>
//</tr>
//<tr>
//      <td>
//      <div  style="text-align:center;font-size:10px;padding-top:5px;">
//            <a href = "$page_url" target="_blank">
//            $page_url
//            </a>
//      </div>        
//      </td>
//</tr>
//<tr>
//       <td>
//       <div style="border-bottom:2px solid #0000FF;padding-bottom:55px;color:#0000FF;font-family: Verdana, Geneva, sans-serif;font-size:22px;"> Product Image </div>
//       <br/>
//       <img src ="$thumb" height="290px" align="center">
//       </td>
//      
//</tr>   
//<tr>
//     <td>
//          <div style="border-bottom:2px solid #0000FF;padding-bottom:55px;color:#0000FF;font-family: Verdana, Geneva, sans-serif;font-size:22px;"> Description </div>
//          <br/>
//     </td>
//</tr>   
//</table>
//EOD;
 if($this->request->get['isize']){
   $image_width = $this->request->get['isize'];  
 }else{
     $image_width = '150';
 }

$image_height = '150';

$html = <<<EOF
<!-- EXAMPLE OF CSS STYLE -->
<style>
      .grid-container {
 
}
.grid-title{ 
font-size:15px;
        color:#404040;
}
        .grid-price{ 
font-size:11px;
        color:red;
}
        table {


}

tr {
          border:2px solid #e6e6e6;
  text-align:center;
             padding:10px;
}
  
</style>

EOF;
$html .= '<table>';
$html .= '<span style="font-size:30px;text-align:center;margin-bottom:200px;">'.$product_cate[0]['name'].'</span><br>';
$html .= '<br>';

$count=1;
foreach($product_infos as $key => $product_info) {
      

  if ($count%3 == 1)
    {  
    $html .= '<tr>';
    }
     $html .= '<td class="grid-item">' .'<img src="'.$thumb = $this->model_tool_image->resize($product_info['image'], $image_width, $image_height).'"><br><span class="grid-title">'. $product_info['name'].'</span>';
   
     if($this->request->get['manf']){
     $html .= '<br><span class="grid-title">'. $product_info['model'].'</span>'; }
        if($this->request->get['price']){
        $html .= '<br><span class="grid-price">'. $product_info['price'].'</span>';  }
       if($this->request->get['desc']){
       $html .= '<br><span class="grid-title">'.html_entity_decode($product_info['sjdesc'], ENT_QUOTES, 'UTF-8').'</span>';}
 
 $html .= '<span class="grid-price"></div></td>';
     
  if ($count%3 == 0)
    {
           $html .= '</tr>';
    }
    
 $count++;
}
if ($count%3 != 1)  $html .= '</tr>';
$html .= '</table>';
//echo $html;
//foreach($product_infos as $key => $product_info) {
//    $html .= '<img src="'.$product_info['image'].'"  width="50" height="50">';
//}


            $pdf->writeHTML($html, true, false, true, false, '');
//            $pdf->writeHTMLCell(0, 0, '', '', $product_content_html, 0, 1, 0, true, '', true);
             ob_clean();
            return $pdf->Output($this->config->get('config_name') .'.pdf', 'D');





        
    }
    
}
