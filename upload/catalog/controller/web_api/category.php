<?php

class ControllerWebApiCategory extends Controller {

    private $debug = false;

    public function categories() {
   
//$this->init();
        $this->load->model('catalog/category');
       // $json = array('success' => true);

# -- $_GET params ------------------------------

        if (isset($this->request->get['parent'])) {
            $parent = $this->request->get['parent'];
        } else {
            $parent = 0;
        }

        if (isset($this->request->get['level'])) {
            $level = $this->request->get['level'];
        } else {
            $level = 1;
        }

# -- End $_GET params --------------------------


        $categories['store_categories'] = $this->getCategoriesTree($parent, $level);

        if ($this->debug) {
            echo '<pre>';
            print_r($categories);
        } else {
            $this->response->setOutput(json_encode($categories));
        }
    }

    public function category() {
        $this->init();
        $this->load->model('catalog/category');
        $this->load->model('tool/image');

        $category = array('success' => true);

# -- $_GET params ------------------------------

        if (isset($this->request->get['id'])) {
            $category_id = $this->request->get['id'];
        } else {
            $category_id = 0;
        }

# -- End $_GET params --------------------------

        $categores = $this->model_catalog_category->getCategory($category_id);

        $category = array(
            'id' => $categores['category_id'],
            'name' => $categores['name'],
            'description' => $categores['description'],
            'href' => $this->url->link('product/category', 'category_id=' . $category['category_id'])
        );

        if ($this->debug) {
            echo '<pre>';
            print_r($category);
        } else {
            $this->response->setOutput(json_encode($category));
        }
    }

    /**
     * Generation of category tree
     * 
     * @param  int    $parent  Prarent category id
     * @param  int    $level   Depth level
     * @return array           Tree
     */
    private function getCategoriesTree($parent = 0, $level = 1) {
        $this->load->model('catalog/category');
        $this->load->model('tool/image');

        $result = array();

        $categories = $this->model_catalog_category->getCategories($parent);

        if ($categories && $level > 0) {
            $level--;

            foreach ($categories as $category) {

                if ($category['image']) {
                    $image = $this->model_tool_image->resize($category['image'], $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
                } else {
                    $image = false;
                }

                $result[] = array(
                    'cat_id' => $category['category_id'],
                    'parent_id' => $category['parent_id'],
                    'cat_name' => $category['name'],
                    'image' => $image,
                    'href' => $this->url->link('product/category', 'category_id=' . $category['category_id']),
                    'categories' => $this->getCategoriesTree($category['category_id'], $level)
                );
            }

            return $result;
        }
    }

}
