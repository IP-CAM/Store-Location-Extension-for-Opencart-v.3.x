<?php

class ControllerInformationContact extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('information/contact');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        );



        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/contact', $data));
    }

    public function consumidor() {

        $this->load->language('information/contact');

        $this->load->model('setting/setting');
        $contact_recipients_conf = $this->config->get('module_contact_recipients');

        $data['recipients'] = array();

        foreach ($contact_recipients_conf as $recipients) {

            $data['recipients'][] = array(
                'name' => $recipients['departament'],
                'value' => t5f_sanitize_filename($recipients['departament']),
                'sort_order' => $recipients['sort_order']
            );
        }


        $data['button_submit'] = $this->language->get('button_submit');

        $data['action'] = $this->url->link('information/contact/send', '', true);


        $this->response->setOutput($this->load->view('information/contact/consumidor', $data));
    }

    public function send() {
        $this->load->language('information/contact');

        $json = [];
        $this->load->model('setting/setting');
        $contact_recipients_conf = $this->config->get('module_contact_recipients');
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            // Custom field validation
            $this->load->model('account/custom_field');

            $custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

            foreach ($custom_fields as $custom_field) {
                if ($custom_field['location'] == 'links_uteis') {
                    if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                        $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                    } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
                        $json['error'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                    }
                }
            }
            if ($this->request->post['departamento'] == '') {
                $json['error'] = $this->language->get('error_departamento');
            }

            if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
                $json['error'] = $this->language->get('error_name');
            }

            if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
                $json['error'] = $this->language->get('error_email');
            }

            if ((utf8_strlen($this->request->post['enquiry']) < 10) || (utf8_strlen($this->request->post['enquiry']) > 3000)) {
                $json['error'] = $this->language->get('error_enquiry');
            }

            // Captcha
            if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array) $this->config->get('config_captcha_page'))) {
                $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

                if ($captcha) {
                    $json['error'] = $captcha;
                }
            }

            if (!isset($json['error'])) {
                // Custom Fields
                $this->load->model('account/custom_field');
                $custom_fields = $this->model_account_custom_field->getCustomFields();

                if (isset($this->request->post['custom_field']['links_uteis'])) {
                    $custom_field_contact = $this->request->post['custom_field']['links_uteis'];
                } else {
                    $custom_field_contact = '';
                }
                if (isset($this->request->post['departamento'])) {
                    $departamento = $this->request->post['departamento'];
                } else {
                    $departamento = '';
                }


                $mail = new Mail($this->config->get('config_mail_engine'));
                foreach ($contact_recipients_conf as $recipients) {
                 if ($departamento == t5f_sanitize_filename($recipients['departament'])) {

                $email_content = "<strong>Tipo de Manifestação:  </strong>" . $recipients['departament'];
                 }
                }
                $email_content .= "<br><br><strong>" . $this->language->get('entry_name') . ":</strong>" . $this->request->post['name'];
                $email_content .= "<br><br><strong>" . $this->language->get('entry_email') . ": </strong> <a href='mailto:" . $this->request->post['email'] . "' > " . $this->request->post['email'] . " </a>";
                $email_content .= "<br><br><strong>" . $this->language->get('entry_enquiry') . ": </strong>" . nl2br($this->request->post['enquiry']);
                foreach ($custom_fields as $custom_field) {
                    if ($custom_field['location'] == 'links_uteis') {
                       $email_content .= "<br><br><strong>" . $custom_field['name'] . ": </strong>" . $custom_field_contact[$custom_field['custom_field_id']] . "";
                    }
                }


                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                foreach ($contact_recipients_conf as $recipients) {
                    if ($departamento == t5f_sanitize_filename($recipients['departament'])) {
                        $mail->setTo($recipients['to']);
                    }
                }

                $mail->setFrom($this->request->post['email']);
                $mail->setReplyTo($this->request->post['email']);
                $mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
                $mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['name']), ENT_QUOTES, 'UTF-8'));
                $mail->setHtml($email_content, ENT_QUOTES, 'UTF-8');

                $mail->send();
                // Send to additional alert emails if new account email is enabled
                $emails = explode(',', $this->config->get('module_contact_recipients_alert_email'));
                foreach ($emails as $email) {
                    if (utf8_strlen($email) > 0 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $mail->setTo($email);
                        $mail->send();
                    }
                }
                $json['success'] = $this->language->get('text_message');
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validate() {


        return !$this->error;
    }

    public function revenda() {

        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }
    }

    public function success() {
        $this->load->language('information/contact');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        );

        $data['text_message'] = $this->language->get('text_message');

        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('common/success', $data));
    }

}
