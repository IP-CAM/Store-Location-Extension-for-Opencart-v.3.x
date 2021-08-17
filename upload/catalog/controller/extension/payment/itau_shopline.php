<?php
class ControllerExtensionPaymentItauShopline extends Controller {
    const EXTENSION = 'payment_itau_shopline';

    public function index() {
        $data = $this->load->language('extension/payment/itau_shopline');

        $data['instrucoes'] = $this->language->get('text_mensagem');
        if ($this->config->get(self::EXTENSION . '_instrucoes_id')) {
            $this->load->model('catalog/information');
            $information_info = $this->model_catalog_information->getInformation($this->config->get(self::EXTENSION . '_instrucoes_id'));

            if ($information_info) {
                $data['instrucoes'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');
            }
        }

        $data['texto_botao'] = $this->config->get(self::EXTENSION . '_texto_botao');

        $data['alerta'] = '';
        if (isset($this->session->data['itau_shopline_erro'])) {
            $data['alerta'] = $this->session->data['itau_shopline_erro'];
        }

        return $this->load->view('extension/payment/itau_shopline', $data);
    }

    public function transacao() {
        $json = array();

        $this->load->language('extension/payment/itau_shopline');

        if ($this->validar_basico()) {
            $erros_cadastro = $this->validar_cadastro();

            if (empty($erros_cadastro)) {
                $order_id = $this->session->data['order_id'];

                $this->load->model('checkout/order');
                $order_info = $this->model_checkout_order->getOrder($order_id);

                if ($order_info) {
                    $this->load->model('extension/payment/itau_shopline');

                    $codemp = strtoupper(trim($this->config->get(self::EXTENSION . '_codigo_site')));
                    $chave_criptografia = strtoupper(trim($this->config->get(self::EXTENSION . '_chave_criptografia')));
                    $remover = array($this->config->get(self::EXTENSION . '_prefixo'), $this->config->get(self::EXTENSION . '_sufixo'));
                    $pedido = str_replace($remover, '', $order_id);
                    $valor = number_format($order_info['total'], 2, ',', '');

                    $custom_razao_id = $this->config->get(self::EXTENSION . '_custom_razao_id');
                    $custom_cnpj_id = $this->config->get(self::EXTENSION . '_custom_cnpj_id');
                    $custom_cpf_id = $this->config->get(self::EXTENSION . '_custom_cpf_id');
                    $custom_numero_id = $this->config->get(self::EXTENSION . '_custom_numero_id');
                    $custom_complemento_id = $this->config->get(self::EXTENSION . '_custom_complemento_id');

                    $razao_coluna = $this->config->get(self::EXTENSION . '_razao_coluna');
                    $cnpj_coluna = $this->config->get(self::EXTENSION . '_cnpj_coluna');
                    $cpf_coluna = $this->config->get(self::EXTENSION . '_cpf_coluna');
                    $numero_fatura_coluna = $this->config->get(self::EXTENSION . '_numero_fatura_coluna');
                    $complemento_fatura_coluna = $this->config->get(self::EXTENSION . '_complemento_fatura_coluna');

                    $colunas = array();
                    $colunas_info = array();

                    $campos = $this->campos();

                    if (in_array($custom_razao_id, $campos) && $custom_razao_id == 'C') { array_push($colunas, $razao_coluna); }
                    if (in_array($custom_cnpj_id, $campos) && $custom_cnpj_id == 'C') { array_push($colunas, $cnpj_coluna); }
                    if (in_array($custom_cpf_id, $campos) && $custom_cpf_id == 'C') { array_push($colunas, $cpf_coluna); }
                    if ($custom_numero_id == 'C') { array_push($colunas, $numero_fatura_coluna); }
                    if ($custom_complemento_id == 'C') { array_push($colunas, $complemento_fatura_coluna); }

                    if (count($colunas)) {
                        $colunas_info = $this->model_extension_payment_itau_shopline->getOrder($colunas, $order_id);
                    }

                    $sacado = '';
                    if (in_array($custom_razao_id, $campos)) {
                        $sacado = $this->campo_valor($order_info['custom_field'], $custom_razao_id, $colunas_info, $razao_coluna);
                        $sacado = trim($sacado);
                    }

                    if (empty($sacado)) {
                        $sacado = trim($order_info['firstname'] . ' ' . $order_info['lastname']);
                    }

                    $documento = '';
                    if (in_array($custom_cnpj_id, $campos)) {
                        $documento = $this->campo_valor($order_info['custom_field'], $custom_cnpj_id, $colunas_info, $cnpj_coluna);
                        $documento = trim($documento);
                    }

                    if (empty($documento)) {
                        if (in_array($custom_cpf_id, $campos)) {
                            $documento = $this->campo_valor($order_info['custom_field'], $custom_cpf_id, $colunas_info, $cpf_coluna);
                        }
                    }

                    $numero = $this->campo_valor($order_info['payment_custom_field'], $custom_numero_id, $colunas_info, $numero_fatura_coluna);
                    $complemento = $this->campo_valor($order_info['payment_custom_field'], $custom_complemento_id, $colunas_info, $complemento_fatura_coluna);

                    $documento = preg_replace("/[^0-9]/", '', $documento);
                    $numero = preg_replace("/[^0-9]/", '', $numero);

                    $sacado = utf8_substr($this->sanitize_string($sacado), 0, 40);
                    if (strlen($documento) == 11) {
                        $codigoInscricao = '01';
                    } else if (strlen($documento) == 14) {
                        $codigoInscricao = '02';
                    }
                    $numeroInscricao = $documento;
                    $cepSacado = preg_replace("/[^0-9]/", '', $order_info['payment_postcode']);
                    $endereco = trim($order_info['payment_address_1'] . ' ' . $numero . ' ' . $complemento);
                    $enderecoSacado = utf8_substr($this->sanitize_string($endereco), 0, 40);
                    $bairroSacado = utf8_substr($this->sanitize_string($order_info['payment_address_2']), 0, 15);
                    $cidadeSacado = utf8_substr($this->sanitize_string($order_info['payment_city']), 0, 15);
                    $estadoSacado = $order_info['payment_zone_code'];
                    $vencimento = $this->config->get(self::EXTENSION . '_vencimento');
                    $dataVencimento = date('dmY', strtotime('+' . $vencimento . ' days'));
                    $obsAd1 = (!$this->config->get(self::EXTENSION . '_observacao1')) ? '' : utf8_substr($this->sanitize_string($this->config->get(self::EXTENSION . '_observacao1')), 0, 60);
                    $obsAd2 = (!$this->config->get(self::EXTENSION . '_observacao2')) ? '' : utf8_substr($this->sanitize_string($this->config->get(self::EXTENSION . '_observacao2')), 0, 60);
                    $obsAd3 = (!$this->config->get(self::EXTENSION . '_observacao3')) ? '' : utf8_substr($this->sanitize_string($this->config->get(self::EXTENSION . '_observacao3')), 0, 60);
                    $observacao = '3';
                    $urlRetorno = 'itau/shopline/retorno';

                    require_once(DIR_SYSTEM . 'library/itau_shopline/itau.php');
                    $itau = new Itau();
                    $data['dc'] = $itau->cadastro($codemp, $pedido, $valor, $observacao, $chave_criptografia, $sacado, $codigoInscricao, $numeroInscricao, $enderecoSacado, $bairroSacado, $cepSacado, $cidadeSacado, $estadoSacado, $dataVencimento, $urlRetorno, $obsAd1, $obsAd2, $obsAd3);

                    $itau_shopline_url = HTTPS_SERVER . 'itau/shopline/pagar?id=' . base64_encode($pedido);

                    if (isset($this->session->data['itau_shopline_instrucoes'])) {
                        unset($this->session->data['itau_shopline_instrucoes']);
                    }
                    $this->session->data['itau_shopline_instrucoes'] = sprintf($this->language->get('text_instrucoes'), $itau_shopline_url);

                    $comment = $this->language->get('text_mensagem') . "\n\n";
                    $comment .= sprintf($this->language->get('text_pagar'), $itau_shopline_url);

                    $this->model_extension_payment_itau_shopline->addTransaction($order_id, $data['dc']);

                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get(self::EXTENSION . '_situacao_aguardando_id'), $comment, true);

                    $json['redirect'] = $this->url->link('checkout/success', '', true);
                } else {
                    $json['error'] = $this->language->get('error_order_id');
                }
            } else {
                $json['error'] = sprintf($this->language->get('error_validacao'), $erros_cadastro);
            }
        } else {
            $json['error'] = $this->language->get('error_permissao');
        }

        if (isset($json['error']) && !empty($json['error'])) { $this->session->data['itau_shopline_erro'] = $json['error']; }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function pagar() {
        $this->redirecionar();
    }

    public function boleto() {
        $this->redirecionar();
    }

    private function redirecionar() {
        if (isset($this->request->get['id']) && !empty($this->request->get['id'])) {
            $order_id = base64_decode($this->request->get['id']);

            $this->load->model('extension/payment/itau_shopline');
            $transaction_info = $this->model_extension_payment_itau_shopline->getTransaction($order_id);

            if ($transaction_info) {
                echo '<!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8" />
                    <title>Carregando ambiente seguro</title>
                    <style>body { color: #000000; font-family: Arial, Helvetica, sans-serif; }</style>
                </head>
                <body>
                    <script type="text/javascript"><!--
                    window.onload = function(){ document.forms[0].submit(); }
                    //--></script>
                    <center><h3>Carregando ambiente seguro de pagamento, aguarde...</h3></center>
                    <form action="https://shopline.itau.com.br/shopline/shopline.aspx" method="post">
                    <input type="hidden" name="DC" value="' . $transaction_info['dc'] . '" />
                    </form>
                </body>
                </html>';
            } else {
                $this->response->redirect($this->url->link('error/not_found'));
            }
        } else {
            $this->response->redirect($this->url->link('error/not_found'));
        }
    }

    public function retorno() {
        $dc = isset($this->request->get['DC']) ? $this->request->get['DC'] : '';
        $dc = empty($dc) ? isset($this->request->get['dc']) ? $this->request->get['dc'] : '' : '';

        if (strlen($dc) > 0) {
            $chave_criptografia = trim(strtoupper($this->config->get(self::EXTENSION . '_chave_criptografia')));

            require_once(DIR_SYSTEM . 'library/itau_shopline/itau.php');
            $itau = new Itau();
            $itau->revelar($dc, $chave_criptografia);
            $codemp = $itau->retornaCodEmp;
            $pedido = ltrim($itau->retornaPedido, '0');

            if (strlen($pedido) > 0) {
                $chave = $this->config->get(self::EXTENSION . '_chave');
                $dados['chave'] = $chave[$this->config->get('config_store_id')];
                $dados['debug'] = $this->config->get(self::EXTENSION . '_debug');
                $dados['dc'] = $itau->consulta($codemp, $pedido, '1', $chave_criptografia);

                require_once(DIR_SYSTEM . 'library/itau_shopline/shopline.php');
                $shopline = new Shopline();
                $shopline->setParametros($dados);
                $resposta = $shopline->getSonda();

                if ($resposta) {
                    if ($codemp == $resposta->CodEmp && $pedido == $resposta->Pedido) {
                        $sitPag = $resposta->sitPag;
                        if (!empty($sitPag)) {
                            $dados['valor'] = $resposta->Valor;
                            $dados['tippag'] = $resposta->tipPag;
                            $dados['sitpag'] = $resposta->sitPag;
                            $dados['dtpag'] = $resposta->dtPag;
                            $dados['codaut'] = $resposta->codAut;
                            $dados['numid'] = $resposta->numId;
                            $dados['compvend'] = $resposta->compVend;
                            $dados['tipcart'] = $resposta->tipCart;
                            $dados['order_id'] = $resposta->Pedido;

                            switch ($dados['sitpag']) {
                                case "00":
                                    $situacao = $this->config->get(self::EXTENSION . '_situacao_pago_id');
                                    break;
                                case "01":
                                    $situacao = $this->config->get(self::EXTENSION . '_situacao_aguardando_id');
                                    break;
                                case "02":
                                    $situacao = $this->config->get(self::EXTENSION . '_situacao_aguardando_id');
                                    break;
                                case "03":
                                    $situacao = $this->config->get(self::EXTENSION . '_situacao_aguardando_id');
                                    break;
                                case "04":
                                    if ($dados['tippag'] == '02') {
                                        $situacao = $this->config->get(self::EXTENSION . '_situacao_gerado_id');
                                    } else {
                                        $situacao = $this->config->get(self::EXTENSION . '_situacao_aguardando_id');
                                    }
                                    break;
                                case "05":
                                    $situacao = $this->config->get(self::EXTENSION . '_situacao_aguardando_id');
                                    break;
                                case "06":
                                    $situacao = $this->config->get(self::EXTENSION . '_situacao_aguardando_id');
                                    break;
                            }

                            $this->load->model('checkout/order');
                            $order_info = $this->model_checkout_order->getOrder($pedido);

                            if ($order_info['order_status_id'] != $situacao) {
                                $this->load->language('extension/payment/itau_shopline');

                                switch ($dados['tippag']) {
                                    case "00":
                                        $comentario = $this->language->get('text_indefinido');
                                        break;
                                    case "01":
                                        $comentario = $this->language->get('text_tef_cdc');
                                        break;
                                    case "02":
                                        $comentario = $this->language->get('text_boleto');
                                        break;
                                    case "03":
                                        $comentario = $this->language->get('text_cartao');
                                        break;
                                }

                                $this->load->model('extension/payment/itau_shopline');
                                $this->model_extension_payment_itau_shopline->editTransaction($dados);

                                $this->model_checkout_order->addOrderHistory($pedido, $situacao, $comentario, true);
                            }
                        }
                        $this->response->redirect($this->url->link('checkout/success', '', true));
                    }
                } else {
                    $this->response->redirect($this->url->link('error/not_found'));
                }
            } else {
                $this->response->redirect($this->url->link('error/not_found'));
            }
        } else {
            $this->response->redirect($this->url->link('error/not_found'));
        }
    }

    private function validar_basico() {
        if (
            isset($this->session->data['order_id']) &&
            isset($this->session->data['payment_method']['code']) &&
            $this->session->data['payment_method']['code'] == 'itau_shopline'
        ) {
            return true;
        }

        return false;
    }

    private function atualizar_pedido() {
        $order_data['custom_field'] = array();

        if ($this->customer->isLogged()) {
            $this->load->model('account/customer');
            $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

            $order_data['custom_field'] = json_decode($customer_info['custom_field'], true);
        } else if (isset($this->session->data['guest'])) {
            $order_data['custom_field'] = $this->session->data['guest']['custom_field'];
        }

        $order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());

        if ($this->cart->hasShipping()) {
            $order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array());
        } else {
            $order_data['shipping_custom_field'] = array();
        }

        $this->load->model('extension/payment/itau_shopline');
        $this->model_extension_payment_itau_shopline->editOrder($order_data, $this->session->data['order_id']);
    }

    private function campos() {
        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getGroupId();
        } elseif (isset($this->session->data['guest']['customer_group_id'])) {
            $customer_group_id = $this->session->data['guest']['customer_group_id'];
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $this->load->model('account/custom_field');
        $custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

        $fields = array();
        foreach ($custom_fields as $custom_field) {
            array_push($fields, $custom_field['custom_field_id']);
        }

        return $fields;
    }

    private function campo_valor($custom_data, $field_key, $collumn_data, $field_collumn) {
        $field_value = '';

        if ($field_key == 'C') {
            if (isset($collumn_data[$field_collumn]) && !empty($collumn_data[$field_collumn])) {
                $field_value = $collumn_data[$field_collumn];
            }
        } else if (!empty($field_key) && is_array($custom_data)) {
            foreach ($custom_data as $key => $value) {
                if ($field_key == $key) { $field_value = $value; }
            }
        }

        return $field_value;
    }

    private function validar_cadastro() {
        $this->load->language('extension/payment/itau_shopline_validacao');

        $this->atualizar_pedido();

        $custom_razao_id = $this->config->get(self::EXTENSION . '_custom_razao_id');
        $custom_cnpj_id = $this->config->get(self::EXTENSION . '_custom_cnpj_id');
        $custom_cpf_id = $this->config->get(self::EXTENSION . '_custom_cpf_id');
        $custom_numero_id = $this->config->get(self::EXTENSION . '_custom_numero_id');

        $razao_coluna = $this->config->get(self::EXTENSION . '_razao_coluna');
        $cnpj_coluna = $this->config->get(self::EXTENSION . '_cnpj_coluna');
        $cpf_coluna = $this->config->get(self::EXTENSION . '_cpf_coluna');
        $numero_coluna = $this->config->get(self::EXTENSION . '_numero_fatura_coluna');

        $colunas = array();
        $colunas_info = array();

        $campos = $this->campos();

        if (in_array($custom_razao_id, $campos) && $custom_razao_id == 'C') { array_push($colunas, $razao_coluna); }
        if (in_array($custom_cnpj_id, $campos) && $custom_cnpj_id == 'C') { array_push($colunas, $cnpj_coluna); }
        if (in_array($custom_cpf_id, $campos) && $custom_cpf_id == 'C') { array_push($colunas, $cpf_coluna); }
        if ($custom_numero_id == 'C') { array_push($colunas, $numero_coluna); }

        $order_id = $this->session->data['order_id'];

        if (count($colunas)) {
            $this->load->model('extension/payment/itau_shopline');
            $colunas_info = $this->model_extension_payment_itau_shopline->getOrder($colunas, $order_id);
        }

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $erros = array();

        $razao = '';
        if (in_array($custom_razao_id, $campos)) {
            $razao = $this->campo_valor($order_info['custom_field'], $custom_razao_id, $colunas_info, $razao_coluna);
            $razao = trim($razao);
        }

        if (empty($razao)) {
            $nome = trim($order_info['firstname'] . ' ' . $order_info['lastname']);
            if (empty($nome)) {
                $erros['nome'] = $this->language->get('error_nome');
            }
        }

        $documento = '';
        if (in_array($custom_cnpj_id, $campos)) {
            $documento = $this->campo_valor($order_info['custom_field'], $custom_cnpj_id, $colunas_info, $cnpj_coluna);
            $documento = trim($documento);
        }

        if (in_array($custom_cpf_id, $campos) && empty($documento)) {
            $documento = $this->campo_valor($order_info['custom_field'], $custom_cpf_id, $colunas_info, $cpf_coluna);
            $documento = trim($documento);
        }

        $documento = preg_replace("/[^0-9]/", '', $documento);
        $documento = strlen($documento);
        if ($documento == 14 || $documento == 11) {
        } else {
            $erros['documento'] = $this->language->get('error_documento');
        }

        $telefone = strlen(preg_replace("/[^0-9]/", '', trim($order_info['telephone'])));
        if ($telefone < 10 || $telefone > 11) {
            $erros['telefone'] = $this->language->get('error_telefone');
        }

        $cep = preg_replace("/[^0-9]/", '', trim($order_info['payment_postcode']));
        if (strlen($cep) != 8) {
            $erros['cep'] = $this->language->get('error_pagamento_cep');
        }

        $endereco = $this->sanitize_string($order_info['payment_address_1']);
        if (empty($endereco)) {
            $erros['endereco'] = $this->language->get('error_pagamento_endereco');
        }

        $numero = $this->campo_valor($order_info['payment_custom_field'], $custom_numero_id, $colunas_info, $numero_coluna);
        $numero = preg_replace("/[^0-9]/", '', $numero);
        if (strlen($numero) < 1) {
            $erros['numero'] = $this->language->get('error_pagamento_numero');
        }

        $bairro = $this->sanitize_string($order_info['payment_address_2']);
        if (empty($bairro)) {
            $erros['bairro'] = $this->language->get('error_pagamento_bairro');
        }

        $cidade = $this->sanitize_string($order_info['payment_city']);
        if (empty($cidade)) {
            $erros['cidade'] = $this->language->get('error_pagamento_cidade');
        }

        $estado = $this->sanitize_string($order_info['payment_zone_code']);
        if (empty($estado)) {
            $erros['estado'] = $this->language->get('error_pagamento_estado');
        }

        if (count($erros) > 0) {
            $resultado = '';

            foreach ($erros as $key => $value) {
                $resultado .= $value;
            }

            return $resultado;
        } else {
            return '';
        }
    }

    private function sanitize_string($string) {
        $substituir = array('&amp;', '&');
        $string = str_replace($substituir, 'E', $string);

        $remover = array('(', ')', 'º', 'ª', '|');
        $string = str_replace($remover, '', $string);

        if ($string !== mb_convert_encoding(mb_convert_encoding($string, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32'))
            $string = mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));

        $string = htmlentities($string, ENT_NOQUOTES, 'UTF-8');
        $string = preg_replace('`&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\1', $string);
        $string = html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
        $string = preg_replace(array('`[^a-z0-9]`i','`[-]+`'), ' ', $string);

        $string = preg_replace('/[\n\t\r]/', ' ', $string);
        $string = preg_replace('/( ){2,}/', '$1', $string);
        $string = trim($string);

        return strtoupper($string);
    }
}