<?php

class ControllerApiTabelas extends Controller {

    public function index() {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
     

        $this->PrimeiroLaco();
       // $this->SegundoLaco();
    }

    public function PrimeiroLaco() {
 

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://feasso.objectdata.com.br/api/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array("charset: UTF-8", "Content-Type: application/json", "login: webapi", "senha: feasso2007"),));
            $sessao = curl_exec($curl);
             $json = json_decode($sessao, true);
             $sessao = $json['session'];
            
        $page = 0;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://feasso.objectdata.com.br/api/produto/?&offset=" . $page,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array("Content-Type: application/json", "charset: UTF-8", "session: " . $sessao),));
        $response = curl_exec($curl);
        curl_close($curl);
        $json = json_decode($response, true);

//-------------------------------------Paginação 0 ao 10 ---------------------------------//
        for ($i = 0; $i < 10; $i++) {
            if (isset($json[$i]['id'])) {
//------------------ Puxa e compara os valores do objectdata com o opencart -------------//
            
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE sku = '" . $json[$i]['id'] . "' ");
                  if ($query->num_rows > 0) {
                            foreach($query->row as $row){
                            $id = $row["product_id"];
                        }
                    }
                        $queryM1_airsoft = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $id . "' AND customer_group_id = 2"); // AIRSOFT M1
                        //$queryM1_airsoft = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special ps INNER JOIN " . DB_PREFIX . "product p ON p.product_id = ps.product_id WHERE p.sku = '" . $json[$i]['id'] . "' AND ps.customer_group_id = '2'"); // AIRSOFT M1

                        if ($queryM1_airsoft->num_rows > 0) {
                            while ($rowM1_airsoft = $queryM1_airsoft->row) {
                                $M1_airsoft = $rowM3_airsoft["price"];
                              
                            }
                        }
                        $queryM3_airsoft = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $id . "' AND customer_group_id = 3"); // AIRSOFT M3
                       // $queryM3_airsoft = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special ps INNER JOIN " . DB_PREFIX . "product p ON p.product_id = ps.product_id WHERE p.sku = '" . $json[$i]['id'] . "' AND ps.customer_group_id = '3'"); // AIRSOFT M3

                        if ($queryM3_airsoft->num_rows > 0) {
                            while ($rowM3_airsoft = $queryM3_airsoft->rows) {
                                $M3_airsoft = $rowM3_airsoft["price"];
                            }
                        }
                        $queryM1_info = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $id . "' AND customer_group_id = 4"); // INFORMATICA M1
                        //$queryM1_info = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special ps INNER JOIN " . DB_PREFIX . "product p ON p.product_id = ps.product_id WHERE p.sku = '" . $json[$i]['id'] . "' AND ps.customer_group_id = '4'"); // INFORMATICA M1

                        if ($queryM1_info->num_rows > 0) {
                            while ($rowM1_info = $queryM1_info->rows) {
                                $M1_info = $rowM1_info["price"];
                            }
                        }
                        $queryM3_info = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $id . "' AND customer_group_id = 5"); // INFORMATICA M3
                      //  $queryM3_info = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special ps INNER JOIN " . DB_PREFIX . "product p ON p.product_id = ps.product_id WHERE p.sku = '" . $json[$i]['id'] . "' AND ps.customer_group_id = '5'"); // AIRSOFT M3

                        if ($queryM3_info->num_rows > 0) {
                            foreach ($queryM3_info->rows as $rowM3_info){
                            //while ($rowM3_info = $queryM3_info->row) {
                                $M3_info = $rowM3_info["price"];
                            }
                        }

                        $m1_object = $json[$i]['preco'];
                        $m3_object = round(( $m1_object + (($m1_object * 10) / 100)), 2);

                        $m1_opencart_airsoft = $M1_airsoft; // AIRSOFT M1
                        $m1_opencart_info = $M1_info; // INFORMATICA M1
                        $m3_opencart_airsoft = $M3_airsoft; // AIRSOFT M3
                        $m3_opencart_info = $M3_info; // INFORMATICA M3
                        
                        if (($m1_object != $m1_opencart_airsoft) OR ($m3_object != $m3_opencart_airsoft) OR ($m1_object != $m1_opencart_info) OR ($m3_object != $m3_opencart_info)) {
                            echo 'id (Objectdata) = ' . $json[$i]['id'] . ' | Objectdat M1 = ' . $m1_object . ' | Objectdat M3 = ' . $m3_object . '<br>';
                            if (isset($M1_airsoft) AND isset($M3_airsoft)) {
                                echo 'id (Opencart)   = ' . $id . ' | AIRSOFT M1 = ' . $m1_opencart_airsoft . ' | AIRSOFT M3 = ' . $m3_opencart_airsoft . '<br>';
                            }
                            if (isset($M1_info) AND isset($M3_info)) {
                                echo 'id (Opencart)   = ' . $id . ' | INFORM M1 = ' . $m1_opencart_info . '    | INFORM M3 = ' . $m3_opencart_info . '<br><br>';
                            }
//------------------ Atualização de tabela -------------//
                          //$teste=   $this->currency->format($this->tax->calculate($m3_opencart_info,  $this->config->get('config_tax')), $this->session->data['currency']);
                             $teste=  (float) str_replace(',', '.', $m1_opencart_airsoft);

                            print_r($teste.'<br/>');

                            if ($m1_object != $m1_opencart_airsoft) {
                               $this->db->query("UPDATE " . DB_PREFIX . "product_special ps INNER JOIN " . DB_PREFIX . "product p ON p.product_id = ps.product_id SET ps.price = '" . $m1_object . "' WHERE p.sku ='" . $json[$i]['id']. "' AND ps.customer_group_id = '2'"); // AIRSOFT M1

                               // $this->db->query("UPDATE " . DB_PREFIX . "product_special SET price = '" . $m1_object . "' WHERE product_id = '" . $id . "' AND customer_group_id = 2"); // AIRSOFT M1
                            }
                            if ($m3_object != $m3_opencart_airsoft) {
                                $this->db->query("UPDATE " . DB_PREFIX . "product_special ps INNER JOIN " . DB_PREFIX . "product p ON p.product_id = ps.product_id SET ps.price = '" . $m3_object . "' WHERE p.sku = '" . $json[$i]['id'] . "' AND ps.customer_group_id = '2'"); // AIRSOFT M3

                                //$this->db->query("UPDATE " . DB_PREFIX . "product_special SET price = '" . $m3_object . "' WHERE product_id = '" . $id . "' AND customer_group_id = 3"); // AIRSOFT M3
                            }
                            if ($m1_object != $m1_opencart_info) {
                               $this->db->query("UPDATE " . DB_PREFIX . "product_special ps INNER JOIN " . DB_PREFIX . "product p ON p.product_id = ps.product_id SET ps.price = '" . $m1_object . "' WHERE p.sku = '" . $json[$i]['id'] . "' AND ps.customer_group_id= '4'");  // INFORMATICA M1

                               // $this->db->query("UPDATE " . DB_PREFIX . "product_special SET price = '" . $m1_object . "' WHERE product_id = '" . $id . "' AND customer_group_id = 4"); // INFORMATICA M1
                            }
                            if ($m3_object != $m3_opencart_info) {
                                $this->db->query("UPDATE " . DB_PREFIX . "product_special ps INNER JOIN " . DB_PREFIX . "product p ON p.product_id = ps.product_id SET ps.price = '" . $m3_object . "' WHERE p.sku = '" . $json[$i]['id'] . "' AND ps.customer_group_id = '5'");  // INFORMATICA M3

                               //$this->db->query("UPDATE " . DB_PREFIX . "product_special SET price = '" . $m3_object . "' WHERE product_id = '" . $id . "' AND customer_group_id = 5"); // INFORMATICA M3
//------------------------------------------------------//
                            }
                        } 
//------------------ Puxa e compara os valores do objectdata com o opencart -------------//
                    
                
            }
        }


//-------------------------------------Paginação 0 ao 10 ---------------------------------//
    }

    public function SegundoLaco() {

//-------------------------------------Paginação 10 ao 35 ---------------------------------//

        for ($x = 1; $x < 35; $x++) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://feasso.objectdata.com.br/api/",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array("charset: UTF-8", "Content-Type: application/json", "login: webapi", "senha: feasso2007"),));
            $sessao = curl_exec($curl);
            $json = json_decode($sessao, true);
            $sessao = $json['session'];
            $page = 0;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://feasso.objectdata.com.br/api/produto/?&offset=" . (($page + 10) * $x),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array("Content-Type: application/json", "charset: UTF-8", "session: " . $sessao),));
            $response = curl_exec($curl);
            curl_close($curl);
            $json = json_decode($response, true);

            for ($i = 0; $i < 10; $i++) {
                if (isset($json[$i]['id'])) {
//------------------ Puxa e compara os valores do objectdata com o opencart -------------//
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE sku = '" . $json[$i]['id'] . "' ");

                    if ($query->num_rows > 0) {
                            foreach($query->row as $row){
                            $id = $row["product_id"];
                        }
                    }
                            $queryM1_airsoft = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $id . "' AND customer_group_id = 2"); // AIRSOFT M1
                            if ($queryM1_airsoft->num_rows > 0) {
                                while ($rowM1_airsoft = $queryM1_airsoft->rows) {
                                    $M1_airsoft = $rowM1_airsoft["price"];
                                }
                            }
                            $queryM3_airsoft = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $id . "' AND customer_group_id = 3"); // AIRSOFT M3
                            if ($queryM3_airsoft->num_rows > 0) {
                                while ($rowM3_airsoft = $queryM3_airsoft->row) {
                                    $M3_airsoft = $rowM3_airsoft["price"];
                                }
                            }
                            $queryM1_info = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $id . "' AND customer_group_id = 4"); // INFORMATICA M1
                            if ($queryM1_info->num_rows > 0) {
                                while ($rowM1_info = $queryM1_info->row) {
                                    $M1_info = $rowM1_info["price"];
                                }
                            }
                            $queryM3_info = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $id . "' AND customer_group_id = 5"); // INFORMATICA M3
                            $resultM3_info = mysqli_query($db, $queryM3_info);
                            if ($queryM3_info->num_rows > 0) {
                                while ($rowM3_info = $queryM3_info->row) {
                                    $M3_info = $rowM3_info["price"];
                                }
                            }

                            $m1_object = $json[$i]['preco'];
                            $m3_object = round(( $m1_object + (($m1_object * 10) / 100)), 2);

                            $m1_opencart_airsoft = $M1_airsoft; // AIRSOFT M1
                            $m1_opencart_info = $M1_info; // INFORMATICA M1
                            $m3_opencart_airsoft = $M3_airsoft; // AIRSOFT M3
                            $m3_opencart_info = $M3_info; // INFORMATICA M3

                            if (($m1_object != $m1_opencart_airsoft) OR ($m3_object != $m3_opencart_airsoft) OR ($m1_object != $m1_opencart_info) OR ($m3_object != $m3_opencart_info)) {
                                echo 'id (Objectdata) = ' . $json[$i]['id'] . ' | Objectdat M1 = ' . $m1_object . ' | Objectdat M3 = ' . $m3_object . '<br>';
                                if (isset($M1_airsoft) AND isset($M3_airsoft)) {
                                    echo 'id (Opencart)   = ' . $id . ' | AIRSOFT M1 = ' . $m1_opencart_airsoft . ' | AIRSOFT M3 = ' . $m3_opencart_airsoft . '<br>';
                                }
                                if (isset($M1_info) AND isset($M3_info)) {
                                    echo 'id (Opencart)   = ' . $id . ' | INFORM M1 = ' . $m1_opencart_info . '    | INFORM M3 = ' . $m3_opencart_info . '<br><br>';
                                }
//------------------ Atualização de tabela -------------//
                                if ($m1_object != $m1_opencart_airsoft) {
                                    $this->db->query("UPDATE " . DB_PREFIX . "product_special SET price = '" . $m1_object . "' WHERE product_id = '" . $id . "' AND customer_group_id = 2"); // AIRSOFT M1
                                    if ($m3_object != $m3_opencart_airsoft) {
                                        $this->db->query("UPDATE " . DB_PREFIX . "product_special SET price = '" . $m3_object . "' WHERE product_id = '" . $id . "' AND customer_group_id = 3"); // AIRSOFT M3
                                        if ($m1_object != $m1_opencart_info) {
                                            $this->db->query("UPDATE " . DB_PREFIX . "product_special SET price = '" . $m1_object . "' WHERE product_id = '" . $id . "' AND customer_group_id = 4"); // INFORMATICA M1
                                            if ($m3_object != $m3_opencart_info) {
                                                $this->db->query("UPDATE " . DB_PREFIX . "product_special SET price = '" . $m3_object . "' WHERE product_id = '" . $id . "' AND customer_group_id = 5"); // INFORMATICA M3
//------------------------------------------------------//
                                            }
                                        }
                                    }
//------------------ Puxa e compara os valores do objectdata com o opencart -------------//
                                }
                            }

//-------------------------------------Paginação 10 ao 35 ---------------------------------//
                    
                }
            }
        }
    }

}
