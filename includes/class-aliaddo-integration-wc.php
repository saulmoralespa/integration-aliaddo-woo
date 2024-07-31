<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


class WC_Aliaddo_Integration extends  WC_Integration
{
    public string $debug;

    public function __construct()
    {
        $this->id = 'wc_aliaddo_integration';
        $this->method_title = __( 'Integration Aliaddo Woocommerce');
        $this->method_description = __( 'Integration Aliaddo for Woocommerce');

        $this->init_form_fields();
        $this->init_settings();

        $this->debug = $this->get_option( 'debug' );

        add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_form_fields(): void
    {
        $this->form_fields = include(dirname(__FILE__) . '/admin/settings.php');
    }

    public function admin_options(): void
    {
        ?>
        <h3><?php echo $this->method_title; ?></h3>
        <p><?php echo $this->method_description; ?></p>
        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table>
        <?php
    }

    public function validate_password_field($key, $value) :string
    {
        if($key === 'token' && $value){
            $status = Integration_Aliaddo_WC::test_token($value);
            if(!$status){
                WC_Admin_Settings::add_error("Token inválido");
                $value = '';
            }
        }

        return $value;
    }

    public function get_data_options(string $section, string $method, callable $callback)
    {
        $data = isset($_GET['section']) && $_GET['section'] === $section ? $method() : [];
        return array_reduce($data, $callback, []);
    }
}