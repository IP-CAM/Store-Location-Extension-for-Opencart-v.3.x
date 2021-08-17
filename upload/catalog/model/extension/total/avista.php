<?php
class ModelExtensionTotalAVista extends Model {
	public function getTotal($total) {

$subtotal = $this->cart->getSubTotalDesconto()['total'];
 
$desconto = $this->cart->getSubTotalDesconto()['porcento'];

$com_desconto = $this->cart->getSubTotalDesconto()['descontado'];

		if ($this->config->get('total_avista_status')) {
			$methods_aplicaveis = explode(",", $this->config->get('total_avista_methods'));

			if (isset($this->session->data['payment_method']['code'])) $paymethod = $this->session->data['payment_method']['code'];

			if (isset($paymethod) AND ($com_desconto > 0) AND ($this->cart->getFornecedor() == '11')) {
				if (in_array($paymethod, $methods_aplicaveis) AND ($desconto != '0')) {
					$this->load->language('extension/total/avista');
					
					$float = ($desconto<10) ? '0.0'.str_replace(array(',','.'),'',$desconto) : '0.'.str_replace(array(',','.'),'',$desconto). '<br>';
					$percent = $subtotal * $float;
					$total['totals'][] = array(
						'code'		 => 'avista',
						'title'      => 'Desconto de ' . $desconto. '% '. 'concedido com sucesso.',
						'value'      => $percent*-1,
						'sort_order' => $this->config->get('total_avista_sort_order')
					);

					$total['total'] -= $percent;
				}
			}
		}
	}
}
?>