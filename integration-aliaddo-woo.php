<?php
/**
 * Plugin Name: Integration Aliaddo Woocommerce
 * Description: Integración del sistama contable y de facturación Aliaddo para Woocoommerce
 * Version: 0.0.1
 * Author: Saul Morales Pacheco
 * Author URI: https://saulmoralespa.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * WC tested up to: 8.9
 * WC requires at least: 8.9
 * Requires Plugins: woocommerce,departamentos-y-ciudades-de-colombia-para-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(!defined('INTEGRATION_ALIADDO_WC_IAW_VERSION')){
    define('INTEGRATION_ALIADDO_WC_IAW_VERSION', '0.0.1');
}

add_action( 'plugins_loaded', 'integration_aliaddo_wc_iaw_init');

function integration_aliaddo_wc_iaw_init(): void
{
    integration_aliaddo_wc_iaw()->run_aliaddo();
}

function integration_aliaddo_wc_iaw_notices($notice): void
{
    ?>
    <div class="error notice">
        <p><?php echo esc_html( $notice ); ?></p>
    </div>
    <?php
}

function integration_aliaddo_wc_iaw(){
    static $plugin;
    if (!isset($plugin)){
        require_once('includes/class-integration-aliaddo-wc-plugin.php');
        $plugin = new Integration_Aliaddo_WC_Plugin(__FILE__, INTEGRATION_ALIADDO_WC_IAW_VERSION);
    }
    return $plugin;
}