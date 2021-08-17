<?php
class ControllerExtensionPaymentFaturado extends Controller {
    private $valorTotal = 0;

    public function index() {
        $data = $this->load->language('extension/payment/faturado');

        $data['exibir_parcelas'] = $this->config->get('payment_faturado_exibir_parcelas');
        $data['exibir_valor'] = $this->config->get('payment_faturado_exibir_valor');
        $data['exibir_juros'] = $this->config->get('payment_faturado_exibir_juros');

        $data['cor_normal_texto'] = $this->config->get('payment_faturado_cor_normal_texto');
        $data['cor_normal_fundo'] = $this->config->get('payment_faturado_cor_normal_fundo');
        $data['cor_normal_borda'] = $this->config->get('payment_faturado_cor_normal_borda');
        $data['cor_efeito_texto'] = $this->config->get('payment_faturado_cor_efeito_texto');
        $data['cor_efeito_fundo'] = $this->config->get('payment_faturado_cor_efeito_fundo');
        $data['cor_efeito_borda'] = $this->config->get('payment_faturado_cor_efeito_borda');

        if ($this->config->get('payment_faturado_information_id')) {
            $this->load->model('catalog/information');
            $information_info = $this->model_catalog_information->getInformation($this->config->get('payment_faturado_information_id'));

            if ($information_info) {
                $data['text_instrucoes'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');
            } else {
                $data['text_instrucoes'] = $this->language->get('text_instrucoes');
            }
        } else {
            $data['text_instrucoes'] = $this->language->get('text_instrucoes');
        }

        if ($this->config->get('payment_faturado_exibir_parcelas')) {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            $parcela = 1;
            $total = $order_info['total'] * $order_info['currency_value'];
            $parcelas = explode(',', $this->config->get('payment_faturado_parcelas'));

            if (is_array($parcelas)) {
                foreach ($parcelas as $config) {
                    $dados = explode(':', $config);
                    if ($total >= $dados[0]) {
                        if (isset($dados[1])) {
                            $parcela = $dados[1];
                        }
                    }
                }
            }

            $parcelamento = $this->getParcelas($parcela);
            $data['parcelamento'] = json_encode($parcelamento);
        }

        $data['continue'] = $this->url->link('checkout/success');

        return $this->load->view('extension/payment/faturado', $data);
    }

    public function confirm() {
        if (isset($this->session->data['order_id']) && $this->session->data['payment_method']['code'] == 'faturado') {
            $this->language->load('extension/payment/faturado');

            if (isset($this->request->post['parcelas'])) {
                $comentario = $this->language->get('entry_parcelas') . $this->request->post['parcelas'];
            } else {
                $comentario = $this->language->get('text_instrucoes');
            }

            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_faturado_situacao_aguardando_id'), $comentario, true);
        }
    }

    private function getParcelas($parcelas) {
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $this->valorTotal = $order_info['total'] * $order_info['currency_value'];
        $total = $this->valorTotal;
        $sem_juros = $this->config->get('payment_faturado_sem_juros');
        $juros = $this->config->get('payment_faturado_juros');

        $parcelamento = array();
        for ($i = 1; $i <= $parcelas; $i++) {
            if ($i <= $sem_juros) {
                $valorParcela = ($total/$i);
                $parcelamento[] = array(
                    'parcela' => $i,
                    'valor' => $this->currency->format($valorParcela, $order_info['currency_code'], '1.00', true),
                    'juros' => 0,
                    'total' => $this->currency->format($total, $order_info['currency_code'], '1.00', true)
                );
            } else {
                $total = $this->getParcelar($i);
                $parcelamento[] = array(
                    'parcela' => $i,
                    'valor' => $this->currency->format($total['valorParcela'], $order_info['currency_code'], '1.00', true),
                    'juros' => $juros,
                    'total' => $this->currency->format($total['valorTotal'], $order_info['currency_code'], '1.00', true)
                );
            }
        }

        return $parcelamento;
    }

    private function getParcelar($parcelas) {
        $fator = $this->config->get('payment_faturado_juros')*0.01;
        $calculo = $this->config->get('payment_faturado_calculo');

        if ($calculo) {
            $valorParcela = ($this->valorTotal*pow((1+$fator),$parcelas)*$fator)/(pow((1+$fator),$parcelas)-1);
        } else {
            $valorParcela = ($this->valorTotal*pow((1+$fator),$parcelas))/$parcelas;
        }

        $valorParcela = round($valorParcela, 2);
        $valorTotal = $parcelas*$valorParcela;

        return array(
            'valorParcela' => $valorParcela,
            'valorTotal' => $valorTotal
        );
    }
}