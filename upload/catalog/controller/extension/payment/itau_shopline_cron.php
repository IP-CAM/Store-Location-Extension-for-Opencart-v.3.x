<?php
class ControllerExtensionPaymentItauShoplineCron extends Controller {
    const EXTENSION = 'payment_itau_shopline';

    public function index() {
        $this->load->language('extension/payment/itau_shopline_cron');

        if (isset($this->request->get['key'])) {
            if ($this->config->get(self::EXTENSION . '_chave_cron') == $this->request->get['key']) {
                $this->debug($this->language->get('text_cron_iniciada'));

                $this->load->model('extension/payment/itau_shopline');
                $transactions = $this->model_extension_payment_itau_shopline->getTransactions();
                foreach ($transactions as $transaction) {
                    $this->consultar($transaction['order_id']);
                }

                $this->debug($this->language->get('text_cron_encerrada'));
            } else {
                $this->debug($this->language->get('error_cron_invalida'));
                $this->response->redirect($this->url->link('error/not_found'));
            }
        } else {
            $this->debug($this->language->get('error_cron_negada'));
            $this->response->redirect($this->url->link('error/not_found'));
        }
    }

    private function consultar($order_id) {
        $codemp = strtoupper(trim($this->config->get(self::EXTENSION . '_codigo_site')));
        $remover = array($this->config->get(self::EXTENSION . '_prefixo'), $this->config->get(self::EXTENSION . '_sufixo'));
        $pedido = str_replace($remover, '', $order_id);
        $chave_criptografia = trim(strtoupper($this->config->get(self::EXTENSION . '_chave_criptografia')));

        require_once(DIR_SYSTEM . 'library/itau_shopline/itau.php');
        $itau = new Itau;

        $chave = $this->config->get(self::EXTENSION . '_chave');
        $dados['chave'] = $chave[$this->config->get('config_store_id')];
        $dados['debug'] = $this->config->get(self::EXTENSION . '_debug');
        $dados['dc'] = $itau->consulta($codemp, $pedido, '1', $chave_criptografia);

        require_once(DIR_SYSTEM . 'library/itau_shopline/shopline.php');
        $shopline = new Shopline();
        $shopline->setParametros($dados);
        $resposta = $shopline->getSonda();

        if ($resposta) {
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
                $dados['order_id'] = $order_id;

                $this->load->model('checkout/order');
                $order_info = $this->model_checkout_order->getOrder($order_id);

                $this->load->model('extension/payment/itau_shopline');

                switch ($dados['sitpag']) {
                    case "00":
                        $situacao = $this->language->get('text_pago');
                        $order_status_id = $this->config->get(self::EXTENSION . '_situacao_pago_id');
                        break;
                    case "01":
                    case "03":
                        $now = $this->model_extension_payment_itau_shopline->getDateTimeDataBase();
                        $now = strtotime(date('Y-m-d H:i:s', strtotime($now)));
                        $expire = strtotime('+60 minutes', strtotime($order_info['date_added']));
                        if ($now > $expire) {
                            $situacao = $this->language->get('text_expirado');
                            $order_status_id = $this->config->get(self::EXTENSION . '_situacao_cancelado_id');
                        } else {
                            $situacao = $this->language->get('text_aguardando');
                            $order_status_id = $this->config->get(self::EXTENSION . '_situacao_aguardando_id');
                        }
                        break;
                    case "04":
                        if ($dados['tippag'] == '02') {
                            $situacao = $this->language->get('text_gerado');
                            $order_status_id = $this->config->get(self::EXTENSION . '_situacao_gerado_id');
                        } else {
                            $situacao = $this->language->get('text_aguardando');
                            $order_status_id = $this->config->get(self::EXTENSION . '_situacao_aguardando_id');
                        }
                        break;
                    case "05":
                        $situacao = $this->language->get('text_compensando');
                        $order_status_id = $this->config->get(self::EXTENSION . '_situacao_compensando_id');
                        break;
                    case "06":
                        $situacao = $this->language->get('text_nao_compensado');
                        $order_status_id = $this->config->get(self::EXTENSION . '_situacao_nao_compensado_id');
                        break;
                }

                if ($order_info['order_status_id'] != $order_status_id && $dados['sitpag'] != "02") {
                    $this->load->language('extension/payment/itau_shopline');

                    $transaction_info = $this->model_extension_payment_itau_shopline->getTransaction($order_id);

                    switch ($dados['tippag']) {
                        case "00":
                            $comentario = $this->language->get('text_tipo_pagamento') . $this->language->get('text_nao_escolhido') . "\n\n";
                            break;
                        case "01":
                            $comentario = $this->language->get('text_tipo_pagamento') . $this->language->get('text_tef_cdc') . "\n\n";
                            break;
                        case "02":
                            $comentario = $this->language->get('text_tipo_pagamento') . $this->language->get('text_boleto') . "\n\n";
                            if ($dados['sitpag'] == '04' && $transaction_info['sitpag'] != '04') {
                                $url_boleto = HTTPS_SERVER . 'itau/shopline/boleto?id=' . base64_encode($order_id);
                                $comentario .= sprintf($this->language->get('text_imprimir'), $url_boleto) . "\n\n";
                            }
                            break;
                        case "03":
                            $comentario = $this->language->get('text_tipo_pagamento') . $this->language->get('text_credito') . "\n\n";
                            break;
                    }
                    $comentario .= $this->language->get('text_situacao') . $situacao;

                    $this->model_extension_payment_itau_shopline->editTransaction($dados);

                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comentario, true);

                    if ($this->config->get(self::EXTENSION . '_email_cron')) {
                        $mail = new Mail();
                        $mail->protocol = $this->config->get('config_mail_protocol');
                        $mail->parameter = $this->config->get('config_mail_parameter');
                        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                        $mail->setTo($this->config->get('config_email'));
                        $mail->setFrom($this->config->get('config_email'));
                        $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
                        $mail->setSubject(html_entity_decode(sprintf($this->language->get('text_email_subject'), $order_id), ENT_QUOTES, 'UTF-8'));
                        $mail->setText(html_entity_decode(sprintf($this->language->get('text_email_content'), $order_id, $situacao), ENT_QUOTES, 'UTF-8'));
                        $mail->send();
                    }
                }
            }
        }
    }

    private function debug($log) {
        if (defined('DIR_LOGS')) {
            if ($this->config->get(self::EXTENSION . '_debug')) {
                $file = DIR_LOGS . 'itau_shopline.log';
                $handle = fopen($file, 'a');
                fwrite($handle, date('d/m/Y H:i:s (T)') . "\n");
                fwrite($handle, print_r($log, true) . "\n");
                fclose($handle);
            }
        }
    }
}