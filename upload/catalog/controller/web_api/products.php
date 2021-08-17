<?php

use \WebAPI;

class ControllerWebApiProducts extends Controller {

    private $cat;

    public function __construct($registry) {
        parent::__construct($registry);

        $this->cat = json_encode($this->webapi->ProductBycategorySlider());
    }

    public function products() {
        //$this->init();
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        $json = array('success' => true, 'products' => array());

# -- $_GET params ------------------------------

        if (isset($this->request->get['category'])) {
            $category_id = $this->request->get['category'];
        } else {
            $category_id = 0;
        }

# -- End $_GET params --------------------------

        $products = $this->model_catalog_product->getProducts(array(
            'filter_category_id' => $category_id
        ));

        foreach ($products as $product) {

            if ($product['image']) {
                $image = $this->model_tool_image->resize($product['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
            } else {
                $image = false;
            }

            if ((float) $product['special']) {
                $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
            } else {
                $special = false;
            }

            $json['products'][] = array(
                'id' => $product['product_id'],
                'name' => $product['name'],
                'description' => $product['description'],
                'pirce' => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))),
                'href' => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                'thumb' => $image,
                'special' => $special,
                'rating' => $product['rating']
            );
        }

        if ($this->debug) {
            echo '<pre>';
            print_r($json);
        } else {
            $this->response->setOutput(json_encode($json));
        }
    }

    public function product() {
        $this->init();
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        $json = array('success' => true);

# -- $_GET params ------------------------------

        if (isset($this->request->get['id'])) {
            $product_id = $this->request->get['id'];
        } else {
            $product_id = 0;
        }

# -- End $_GET params --------------------------

        $product = $this->model_catalog_product->getProduct($product_id);

# product image
        if ($product['image']) {
            $image = $this->model_tool_image->resize($product['image'], $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
        } else {
            $image = '';
        }

#additional images
        $additional_images = $this->model_catalog_product->getProductImages($product['product_id']);
        $images = array();

        foreach ($additional_images as $additional_image) {
            // $images[] = $this->model_tool_image->resize($additional_image, $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'));
        }

#specal
        if ((float) $product['special']) {
            $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
        } else {
            $special = false;
        }

#discounts
        $discounts = array();
        $data_discounts = $this->model_catalog_product->getProductDiscounts($product['product_id']);

        foreach ($data_discounts as $discount) {
            $discounts[] = array(
                'quantity' => $discount['quantity'],
                'price' => $this->currency->format($this->tax->calculate($discount['price'], $product['tax_class_id'], $this->config->get('config_tax')))
            );
        }

#options
        $options = array();

        foreach ($this->model_catalog_product->getProductOptions($product['product_id']) as $option) {
            if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image') {
                $option_value_data = array();

                foreach ($option['option_value'] as $option_value) {
                    if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
                        if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float) $option_value['price']) {
                            $price = $this->currency->format($this->tax->calculate($option_value['price'], $product['tax_class_id'], $this->config->get('config_tax')));
                        } else {
                            $price = false;
                        }

                        $option_value_data[] = array(
                            'product_option_value_id' => $option_value['product_option_value_id'],
                            'option_value_id' => $option_value['option_value_id'],
                            'name' => $option_value['name'],
                            'image' => $this->model_tool_image->resize($option_value['image'], 50, 50),
                            'price' => $price,
                            'price_prefix' => $option_value['price_prefix']
                        );
                    }
                }

                $options[] = array(
                    'product_option_id' => $option['product_option_id'],
                    'option_id' => $option['option_id'],
                    'name' => $option['name'],
                    'type' => $option['type'],
                    'option_value' => $option_value_data,
                    'required' => $option['required']
                );
            } elseif ($option['type'] == 'text' || $option['type'] == 'textarea' || $option['type'] == 'file' || $option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
                $options[] = array(
                    'product_option_id' => $option['product_option_id'],
                    'option_id' => $option['option_id'],
                    'name' => $option['name'],
                    'type' => $option['type'],
                    'option_value' => $option['option_value'],
                    'required' => $option['required']
                );
            }
        }

#minimum
        if ($product['minimum']) {
            $minimum = $product['minimum'];
        } else {
            $minimum = 1;
        }

        $json['product'] = array(
            'id' => $product['product_id'],
            'seo_h1' => $product['seo_h1'],
            'name' => $product['name'],
            'manufacturer' => $product['manufacturer'],
            'model' => $product['model'],
            'reward' => $product['reward'],
            'points' => $product['points'],
            'image' => $image,
            'images' => $images,
            'price' => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))),
            'special' => $special,
            'discounts' => $discounts,
            'options' => $options,
            'minimum' => $minimum,
            'rating' => (int) $product['rating'],
            'description' => html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8'),
            'attribute_groups' => $this->model_catalog_product->getProductAttributes($product['product_id'])
        );

        if ($this->debug) {
            echo '<pre>';
            print_r($json);
        } else {
            $this->response->setOutput(json_encode($json));
        }
    }

    /**
     * 
     */
    private function init() {

        $this->response->addHeader('Content-Type: application/json');

        if (!$this->config->get('web_api_status')) {
            $this->error(10, 'API is disabled');
        }

        if ($this->config->get('web_api_key') && (!isset($this->request->get['key']) || $this->request->get['key'] != $this->config->get('web_api_key'))) {
            $this->error(20, 'Invalid secret key');
        }
    }

    /**
     * Error message responser
     *
     * @param string $message  Error message
     */
    private function error($code = 0, $message = '') {

# setOutput() is not called, set headers manually
        header('Content-Type: application/json');

        $json = array(
            'success' => false,
            'code' => $code,
            'message' => $message
        );

        if ($this->debug) {
            echo '<pre>';
            print_r($json);
        } else {
            echo json_encode($json);
        }

        exit();
    }

    public function themeColorSchema() {

        $params = array(
            'primaryColor' => '1d60bc',
            'secondaryColor' => '131d28',
            'fontColor' => '131d28'
        );

        $json_return = json_encode($params);

        return $json_return;
    }

    /* public function getBanners() {

      $this->load->model('catalog/product');

      $this->load->model('tool/image');

      $add_banners = array();

      $results = $this->model_catalog_product->getLatestProducts(3);

      if ($results) {
      foreach ($results as $result) {
      if ($result['image']) {
      $image = $this->model_tool_image->resize($result['image'], 400, 400);
      } else {
      $image = $this->model_tool_image->resize('placeholder.png', 400, 400);
      }

      if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
      $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
      } else {
      $price = false;
      }

      if (!is_null($result['special']) && (float) $result['special'] >= 0) {
      $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
      $tax_price = (float) $result['special'];
      } else {
      $special = false;
      $tax_price = (float) $result['price'];
      }

      if ($this->config->get('config_tax')) {
      $tax = $this->currency->format($tax_price, $this->session->data['currency']);
      } else {
      $tax = false;
      }

      if ($this->config->get('config_review_status')) {
      $rating = $result['rating'];
      } else {
      $rating = false;
      }

      $add_banners[] = array(
      'product_id' => $result['product_id'],
      'thumb' => $image,
      'name' => $result['name'],
      'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
      'price' => $price,
      'special' => $special,
      'tax' => $tax,
      'rating' => $rating,
      'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
      );
      }
      }
      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($add_banners));
      } */

    public function getBanners() {

        $this->load->model('design/banner');
        $this->load->model('tool/image');

        $add_banners = array();

        $results = $this->model_design_banner->getBanner(2);

        foreach ($results as $result) {
            if (is_file(DIR_IMAGE . $result['image'])) {
                $add_banners[] = array(
                    'name' => $result['title'],
                    'link' => $result['link'],
                    'image_url' => $this->model_tool_image->resize($result['image'], 1142, 500)
                );
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($add_banners));
    }

    public function getHomeProduct() {

        $this->load->model('catalog/product');

        $this->load->model('tool/image');

        $products = array();

        $results = $this->model_catalog_product->getLatestProducts(3);

        if ($results) {
            foreach ($results as $result) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], 400, 400);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 400, 400);
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $price = false;
                }

                if (!is_null($result['special']) && (float) $result['special'] >= 0) {
                    $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    $tax_price = (float) $result['special'];
                } else {
                    $special = false;
                    $tax_price = (float) $result['price'];
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format($tax_price, $this->session->data['currency']);
                } else {
                    $tax = false;
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $result['rating'];
                } else {
                    $rating = false;
                }

                $products[] = array(
                    'product_id' => $result['product_id'],
                    'thumb' => $image,
                    'name' => $result['name'],
                    'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                    'price' => $price,
                    'special' => $special,
                    'tax' => $tax,
                    'rating' => $rating,
                    'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
                );
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($products));
    }

    public function getNewProducts() {

        $this->load->model('catalog/product');

        $this->load->model('tool/image');

        $products = array();

        $results = $this->model_catalog_product->getLatestProducts(100);

        if ($results) {
            foreach ($results as $result) {

                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], 200, 200);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 200, 200);
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $price = false;
                }

                if (!is_null($result['special']) && (float) $result['special'] >= 0) {
                    $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    $tax_price = (float) $result['special'];
                } else {
                    $special = false;
                    $tax_price = (float) $result['price'];
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format($tax_price, $this->session->data['currency']);
                } else {
                    $tax = false;
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $result['rating'];
                } else {
                    $rating = false;
                }

                /* $products = "{\"product_id\": ". $result['product_id'] .",
                  \"thumb\": ". $image . ",
                  \"name\": " . $result['name'] . ",
                  \"description\": " . utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..'. ",
                  \"price\": \"" . $price . "\",
                  \"special\": \"" . $special . "\"

                  }"; */

                $products[] = array(
                    'entity_id' => $result['product_id'],
                    'image' => $image,
                    'name' => $result['name'],
                    'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                    'price' => $price,
                    'special' => $special,
                    'tax' => $tax,
                    'rating' => $rating,
                    'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
                );
            }
        }
//print_r($products);
        $this->response->setOutput(json_encode($products));
//echo json_encode(["data"=>$products], JSON_UNESCAPED_UNICODE); 
// $this->response->setOutput(json_encode($products));
// return ;
    }

    public function getProductDetailById() {

        $this->load->language('product/product');
        $this->load->model('catalog/product');
        $product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

        $products = array();

        if ($product_info) {

            if ($product_info['quantity'] <= 0) {

                $stock = $product_info['stock_status'];
            } elseif ($this->config->get('config_stock_display')) {
                $stock = $product_info['quantity'];
            } else {
                $stock = 'Em estoque';
            }

            $this->load->model('tool/image');

            if ($product_info['image']) {
                $image = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
            } else {
                $image = '';
            }

            if ($product_info['image']) {
                $data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
            } else {
                $data['thumb'] = '';
            }
//if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
// } else {
//    $price = false;
// }

            if ((float) $product_info['special']) {
                $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $special = false;
            }

            if ($this->config->get('config_tax')) {
                $tax = $this->currency->format((float) $product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
            } else {
                $tax = false;
            }

            /* $data['reviews'] = sprintf($this->language->get('text_reviews'), (int) $product_info['reviews']);
              $data['rating'] = (int) $product_info['rating'];



              $results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

              foreach ($results as $result) {
              if ($result['image']) {
              $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
              } else {
              $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
              }

              if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
              $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
              } else {
              $price = false;
              }

              if ((float) $result['special']) {
              $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
              } else {
              $special = false;
              }

              if ($this->config->get('config_tax')) {
              $tax = $this->currency->format((float) $result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
              } else {
              $tax = false;
              }

              if ($this->config->get('config_review_status')) {
              $rating = (int) $result['rating'];
              } else {
              $rating = false;
              } */

            $products = array(
                'entity_id' => $product_info['product_id'],
                'image' => $image,
                'name' => $product_info['name'],
                'description' => utf8_substr(trim(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                'price' => $price,
                'special' => $special,
                'tax' => $tax,
                'sku' => $product_info['sku'],
                'model' => $product_info['model'],
                'stock' => $stock,
                'quantity' => $product_info['quantity'],
                'minimum' => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
                'manufacturer' => $product_info['manufacturer']
            );

            $this->model_catalog_product->updateViewed($this->request->get['product_id']);
        }
        $this->response->setOutput(json_encode($products));
    }

    public function getRelated() {

        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $products = array();

        $results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

        foreach ($results as $result) {
            if ($result['image']) {
                $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
            }

            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $price = false;
            }

            if ((float) $result['special']) {
                $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $special = false;
            }

            if ($this->config->get('config_tax')) {
                $tax = $this->currency->format((float) $result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
            } else {
                $tax = false;
            }

            if ($this->config->get('config_review_status')) {
                $rating = (int) $result['rating'];
            } else {
                $rating = false;
            }

            $products[] = array(
                'entity_id' => $result['product_id'],
                'image' => $image,
                'name' => $result['name'],
                'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                'price' => $price,
                'special' => $special,
                'tax' => $tax,
                'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
                'rating' => $rating,
                'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
            );
        }
        $this->response->setOutput(json_encode($products));
    }

    public function GetGallery() {
        $this->load->model('catalog/product');

        $this->load->model('tool/image');

        /* if ($product_info['image']) {
          $data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
          } else {
          $data['popup'] = '';
          }

          if ($product_info['image']) {
          $data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
          } else {
          $data['thumb'] = '';
          } */

        $images = array();

        $results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);

        foreach ($results as $result) {
            $images[] = array(
                'url' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
                'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
            );
        }
        $this->response->setOutput(json_encode($images));
    }

    public function getSales() {
        
    }

    public function ProductBycategoryAirsoft() {
        print_r($this->cat->category_id);
        $data['category_infoid'] = $this->cat->category_id;

        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('tool/image');

        $products = array();

        $data['category_info'] = $this->model_catalog_category->getCategory(62);
        //echo "<pre>";print_r($data['category_info']);exit;
        if ($data['category_info']) {
            $data['heading_title'] = $data['category_info']['name'];
        } else {
            $data['heading_title'] = '';
        }

        $data['carousel_id'] = $setting['category_id']; //$this->friendlyURL($data['heading_title']);

        if ($data['category_info']) {
            $data['catimag'] = $this->model_tool_image->resize($data['category_info']['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
        } else {
            $data['catimag'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
        }


        $data['category_link'] = $this->url->link('product/category', 'path=' . $setting['category_id'], 'SSL');

        $results = $this->model_catalog_product->getProductsByCategoryRandom(62, 5);
        //echo $setting['category_id'];
        foreach ($results as $result) {

            if ($result['image']) {
                $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            }

            if ((float) $result['price']) {
                $mrp_percentage = round(100 - (($result['price'] * 100) / $result['price']));
            } else {
                $mrp_percentage = false;
            }

            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $price = false;
            }

            if ((float) $result['special']) {
                $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $special = false;
            }

            if ($this->config->get('config_review_status')) {
                $rating = $result['rating'];
            } else {
                $rating = false;
            }
            $products[] = array(
                'customer_group_id' => 2,
                'name' => utf8_substr(strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')), 0, 50),
                'quantity' => $result['quantity'],
                'entity_id' => $result['product_id'],
                'model' => $result['model'],
                'description' => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
                'thumb' => $image,
                'price' => $price,
                'rating' => $rating,
                'special' => $special,
                'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'], 'SSL')
            );
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($products));
    }

    public function ProductBycategoryAutomacao() {
        print_r($this->cat->category_id);
        $data['category_infoid'] = $this->cat->category_id;

        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('tool/image');

        $products = array();

        $data['category_info'] = $this->model_catalog_category->getCategory(65);
        //echo "<pre>";print_r($data['category_info']);exit;
        if ($data['category_info']) {
            $data['heading_title'] = $data['category_info']['name'];
        } else {
            $data['heading_title'] = '';
        }

        $data['carousel_id'] = $setting['category_id']; //$this->friendlyURL($data['heading_title']);

        if ($data['category_info']) {
            $data['catimag'] = $this->model_tool_image->resize($data['category_info']['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
        } else {
            $data['catimag'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
        }


        $data['category_link'] = $this->url->link('product/category', 'path=' . $setting['category_id'], 'SSL');

        $results = $this->model_catalog_product->getProductsByCategoryRandom(65, 5);
        //echo $setting['category_id'];
        foreach ($results as $result) {

            if ($result['image']) {
                $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            }

            if ((float) $result['price']) {
                $mrp_percentage = round(100 - (($result['price'] * 100) / $result['price']));
            } else {
                $mrp_percentage = false;
            }

            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $price = false;
            }

            if ((float) $result['special']) {
                $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $special = false;
            }

            if ($this->config->get('config_review_status')) {
                $rating = $result['rating'];
            } else {
                $rating = false;
            }
            $products[] = array(
                'customer_group_id' => 2,
                'name' => utf8_substr(strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')), 0, 50),
                'quantity' => $result['quantity'],
                'entity_id' => $result['product_id'],
                'model' => $result['model'],
                'description' => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
                'thumb' => $image,
                'price' => $price,
                'rating' => $rating,
                'special' => $special,
                'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'], 'SSL')
            );
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($products));
    }

    public function ProductBycategoryTecnologia() {
        print_r($this->cat->category_id);
        $data['category_infoid'] = $this->cat->category_id;

        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('tool/image');

        $products = array();

        $data['category_info'] = $this->model_catalog_category->getCategory(64);
        //echo "<pre>";print_r($data['category_info']);exit;
        if ($data['category_info']) {
            $data['heading_title'] = $data['category_info']['name'];
        } else {
            $data['heading_title'] = '';
        }

        $data['carousel_id'] = $setting['category_id']; //$this->friendlyURL($data['heading_title']);

        if ($data['category_info']) {
            $data['catimag'] = $this->model_tool_image->resize($data['category_info']['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
        } else {
            $data['catimag'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
        }


        $data['category_link'] = $this->url->link('product/category', 'path=' . $setting['category_id'], 'SSL');

        $results = $this->model_catalog_product->getProductsByCategoryRandom(64, 5);
        //echo $setting['category_id'];
        foreach ($results as $result) {

            if ($result['image']) {
                $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            }

            if ((float) $result['price']) {
                $mrp_percentage = round(100 - (($result['price'] * 100) / $result['price']));
            } else {
                $mrp_percentage = false;
            }

            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $price = false;
            }

            if ((float) $result['special']) {
                $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $special = false;
            }

            if ($this->config->get('config_review_status')) {
                $rating = $result['rating'];
            } else {
                $rating = false;
            }
            $products[] = array(
                'customer_group_id' => 2,
                'name' => utf8_substr(strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')), 0, 50),
                'quantity' => $result['quantity'],
                'entity_id' => $result['product_id'],
                'model' => $result['model'],
                'description' => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
                'thumb' => $image,
                'price' => $price,
                'rating' => $rating,
                'special' => $special,
                'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'], 'SSL')
            );
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($products));
    }

    public function getCategoryProducts() {

        $this->load->language('product/category');

        $this->load->model('catalog/category');

        $this->load->model('catalog/product');

        $this->load->model('tool/image');

        if (isset($this->request->get['category_id'])) {
            $category_id = $this->request->get['category_id'];
        } else {
            $category_id = '';
        }


        $category_info = $this->model_catalog_category->getCategory($category_id);
           // print_r($category_info);

        if ($category_info) {
              
            $data['categories'] = array();

            $results = $this->model_catalog_category->getCategories($category_id);

            foreach ($results as $result) {
                $filter_data = array(
                    'filter_category_id' => $result['category_id'],
                    'filter_sub_category' => true
                );

                $data['categories'][] = array(
                    'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
                    'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
                );
            }

            $products = array();

            $filter_data = array(
                'filter_category_id' => $category_id,
                //'filter_filter' => $filter,
                //'sort' => $sort,
                //'order' => $order,
                //'start' => ($page - 1) * $limit,
                //'limit' => $limit
            );

            $product_results = $this->model_catalog_product->getProducts($filter_data);

            foreach ($product_results as $result) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $price = false;
                }

                if ((float) $result['special']) {
                    $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $special = false;
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format((float) $result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
                } else {
                    $tax = false;
                }

                if ($this->config->get('config_review_status')) {
                    $rating = (int) $result['rating'];
                } else {
                    $rating = false;
                }

                $products[] = array(
                    'entity_id' => $result['product_id'],
                    'thumb' => $image,
                    'name' => $result['name'],
                    'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                    'price' => $price,
                    'special' => $special,
                    'tax' => $tax,
                    'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating' => $result['rating'],
                );
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($products));
    }
    public function getFiltersByCategory() {
        
        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string) $this->request->get['path']);
        } else {
            $parts = array();
        }

        $category_id = end($parts);

        $this->load->model('catalog/category');
         $data['category'] = $this->load->controller('extension/module/category');

        $category_info = $this->model_catalog_category->getCategory($category_id);

        if ($category_info) {
            $this->load->language('extension/module/filter');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $data['action'] = str_replace('&amp;', '&', $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url));

            if (isset($this->request->get['filter'])) {
                $data['filter_category'] = explode(',', $this->request->get['filter']);
            } else {
                $data['filter_category'] = array();
            }

            $this->load->model('catalog/product');

            $filters = array();

            $filter_groups = $this->model_catalog_category->getCategoryFilters($category_id);
            
           // print_r($parts);

            if ($filter_groups) {
                foreach ($filter_groups as $filter_group) {
                    $children_data = array();

                    foreach ($filter_group['filter'] as $filter) {
                        $filter_data = array(
                            'filter_category_id' => $category_id,
                            'filter_filter' => $filter['filter_id']
                        );
                        $data['filter_name'] = $filter_group['name'];
                        $children_data[] = array(
                            'filter_id' => $filter['filter_id'],
                            'name' => $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : '')
                        );
                    }

                    $filters['filters'][] = array(
                        'filter_group_id' => $filter_group['filter_group_id'],
                        'name' => $filter_group['name'],
                        'filter' => $children_data
                    );
                }

                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($filters));
            }
        }
        
    }
    public function loadFilters() {
        
        
    }
    public function getSortOptions() {
        
    }

}
