<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class pago_controller extends CI_Controller {

    private $_guestProfile;

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            //user is already logged in
            redirect('ingresar');
        } else {
            $this->load->library('grocery_CRUD');
            $this->_guestProfile = $this->session->userdata('logged_in');
        }
    }
   
    public function output($view, $output = null) {
        $this->load->view($view, $output);
    }

    public function pagar_factura () {
        
        //https://www.mercadopago.com.ar/developers/es/solutions/payments/basic-checkout/test/basic-sandbox/
        $this->load->library('MercadoPago');

        $mp = new MercadoPago("4991808988306545", "rX9g6Jrcxptc9j8m4eCHZTkb7u232zRb");
        //$mp->init("4991808988306545", "rX9g6Jrcxptc9j8m4eCHZTkb7u232zRb");

        //sandbox
        $mp->init("3115132961098984", "RtA4XoGwSl8mmY4reKCmKTv8Q98yqAcI");
        $mp->sandbox_mode(TRUE);

        $preference_data = array(
            "items" => array(
                array(
                    "id" => "Code",
                    "title" => "Pago de Cuota Social",
                    "currency_id" => "ARS",
                    "picture_url" =>"https://www.mercadopago.com/org-img/MP3/home/logomp3.gif",
                    "description" => "Cuota Social",
                    "category_id" => "Categori A",
                    "quantity" => 1,
                    "unit_price" => 250.50
                )
            ),
            "payer" => array(
                "name" => "Gustavo Mena",
                "surname" => "gmena",
                "email" => "gmena@email.com",
                "date_created" => "2014-07-28T09:50:37.521-04:00",
                "phone" => array(
                    "area_code" => "351",
                    "number" => "4444-4444"
                ),
                "identification" => array(
                    "type" => "DNI",
                    "number" => "12345678"
                ),
                "address" => array(
                    "street_name" => "San Martin",
                    "street_number" => 23,
                    "zip_code" => "5000"
                )
            ),
            "back_urls" => array(
                "success" => "http://local.sistema.comprobantes365.com/pago_controller/pago_ok",
                "failure" => "http://local.sistema.comprobantes365.com/pago_controller/pago_error",
                "pending" => "http://local.sistema.comprobantes365.com/pago_controller/pago_pendiente"
            ),
            "auto_return" => "approved",
            "notification_url" => "http://local.sistema.comprobantes365.com/pago_controller/ipn",
            "external_reference" => "Referencia_1234",
            "expires" => false,
            "expiration_date_from" => null,
            "expiration_date_to" => null
        );

        
        $preference = $mp->create_preference($preference_data);

        $data = array('preference'=>$preference);

        $this->output('pagos/pagar_factura.php', $data);
    }

    public function pago_ok() {
        echo "<pre>";
        print_r($_GET);

        echo "Pago OK";
    }

    public function pago_error() {
        echo "<pre>";
        print_r($_GET);
        
        echo "Error en Pago";
    }

    public function pago_pendiente() {
        echo "<pre>";
        print_r($_GET);

        echo "Pago Pendiente";
    }

    public function ipn() {
        $this->load->library('MercadoPago');

        $mp = new MercadoPago("4991808988306545", "rX9g6Jrcxptc9j8m4eCHZTkb7u232zRb");
        
        //sandbox
        $mp->init("3115132961098984", "RtA4XoGwSl8mmY4reKCmKTv8Q98yqAcI");
        $mp->sandbox_mode(TRUE);

        // Get the payment and the corresponding merchant_order reported by the IPN.
        if($_GET["topic"] == 'payment') {
            $payment_info = $mp->get("/collections/notifications/" . $_GET["id"]);
            $merchant_order_info = $mp->get("/merchant_orders/" . $payment_info["response"]["collection"]["merchant_order_id"]);
        // Get the merchant_order reported by the IPN.
        } else if($_GET["topic"] == 'merchant_order'){
            $merchant_order_info = $mp->get("/merchant_orders/" . $_GET["id"]);
        }

        if ($merchant_order_info["status"] == 200) {
            // If the payment's transaction amount is equal (or bigger) than the merchant_order's amount you can release your items 
            $paid_amount = 0;

            foreach ($merchant_order_info["response"]["payments"] as  $payment) {
                if ($payment['status'] == 'approved'){
                    $paid_amount += $payment['transaction_amount'];
                }   
            }

            if($paid_amount >= $merchant_order_info["response"]["total_amount"]){
                if(count($merchant_order_info["response"]["shipments"]) > 0) { // The merchant_order has shipments
                    if($merchant_order_info["response"]["shipments"][0]["status"] == "ready_to_ship"){
                        print_r("Totally paid. Print the label and release your item.");

                        $data = array(
                           'titulo' => 'Totally paid. Print the label and release your item.'
                        );

                        $this->db->insert('alertas', $data); 
                    }
                } else { // The merchant_order don't has any shipments
                    print_r("Totally paid. Release your item.");
                    $data = array(
                           'titulo' => 'Totally paid. Release your item.'
                    );

                    $this->db->insert('alertas', $data); 
                }
            } else {
                print_r("Not paid yet. Do not release your item.");
                
                $data = array(
                   'titulo' => 'Not paid yet. Do not release your item.'
                );

                $this->db->insert('alertas', $data); 
            }
        }
    }

    public function listado_pagos() {
        //https://www.mercadopago.com.ar/developers/es/solutions/payments/basic-checkout/test/basic-sandbox/
        $this->load->library('MercadoPago');

        $mp = new MercadoPago("4991808988306545", "rX9g6Jrcxptc9j8m4eCHZTkb7u232zRb");
        
        //sandbox
        $mp->init("3115132961098984", "RtA4XoGwSl8mmY4reKCmKTv8Q98yqAcI");
        $mp->sandbox_mode(TRUE);
        
        // Sets the filters you want
        $filters = array(
            "site_id" => "MLA", // Argentina: MLA; Brasil: MLB
            //"external_reference" => "Bill001"
        );

        // Search payment data according to filters
        $searchResult = $mp->search_payment($filters);

        $data = array(
            'searchResult'=>$searchResult);

        $this->output('pagos/listado_pagos.php', $data);

    }



}