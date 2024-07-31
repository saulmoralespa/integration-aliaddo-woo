<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Integration_Aliaddo_WC_Plugin
{
    /**
     * Absolute plugin path.
     *
     * @var string
     */
    public string $plugin_path;
    /**
     * Absolute plugin URL.
     *
     * @var string
     */
    public string $plugin_url;
    /**
     * assets plugin.
     *
     * @var string
     */
    public string $assets;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public string $includes_path;
    /**
     * Absolute path to plugin lib dir
     *
     * @var string
     */
    public string $lib_path;
    /**
     * @var bool
     */
    private bool $bootstrapped = false;
    /**
     * @var WC_Logger
     */
    private WC_Logger $logger;

    public function __construct(
        private string $file,
        private string $version
    )
    {
        $this->plugin_path = trailingslashit(plugin_dir_path($this->file));
        $this->plugin_url = trailingslashit(plugin_dir_url($this->file));
        $this->assets = $this->plugin_url . trailingslashit('assets');
        $this->includes_path = $this->plugin_path . trailingslashit('includes');
        $this->lib_path = $this->plugin_path . trailingslashit('lib');
        $this->logger = new WC_Logger();
    }

    public function run_aliaddo(): void
    {
        try {
            if ($this->bootstrapped) {
                throw new Exception('Integration Aliaddo Woocommerce can only be called once');
            }
            $this->run();
            $this->bootstrapped = true;
        } catch (Exception $e) {
            if (is_admin() && !defined('DOING_AJAX')) {
                add_action('admin_notices', function () use ($e) {
                    integration_aliaddo_wc_iaw_notices($e->getMessage());
                });
            }
        }
    }

    private function run(): void
    {
        if (!class_exists('\Saulmoralespa\Aliaddo\Client')){
            require_once($this->lib_path . 'vendor/autoload.php');
        }

        if (!class_exists('WC_Aliaddo_Integration')) {
            require_once($this->includes_path . 'class-aliaddo-integration-wc.php');
            add_filter('woocommerce_integrations', array($this, 'add_integration'));
        }

        if (!class_exists('Integration_Aliaddo_WC')) {
            require_once($this->includes_path . 'class-integration-aliaddo-wc.php');
        }

        add_filter('plugin_action_links_' . plugin_basename($this->file), array($this, 'plugin_action_links'));
        add_filter('bulk_actions-edit-product', array($this, 'sync_bulk_actions'), 20);
        add_filter('handle_bulk_actions-edit-product', array($this, 'sync_bulk_action_edit_product'), 10, 3);

        add_action('woocommerce_order_status_changed', array('Integration_Aliaddo_WC', 'generate_invoice'), 10, 3);
    }

    public function add_integration(array $integrations): array
    {
        $integrations[] = 'WC_Aliaddo_Integration';
        return $integrations;
    }

    public function plugin_action_links(array $links): array
    {
        $links[] = '<a href="' . admin_url('admin.php?page=wc-settings&tab=integration&section=wc_aliaddo_integration') . '">' . 'Configuraciones' . '</a>';
        return $links;
    }

    public function sync_bulk_actions(array $bulk_actions): array
    {
        $settings = get_option('woocommerce_wc_aliaddo_integration_settings');

        if(isset($settings['token']) &&
            $settings['enabled'] === 'yes'
        ){
            $bulk_actions['integration_aliaddo_sync'] = 'Sincronizar productos Aliaddo';
        }
        return $bulk_actions;
    }

    public function sync_bulk_action_edit_product($redirect_to, $action, array $post_ids) :string
    {
        if ($action !== 'integration_aliaddo_sync') return $redirect_to;

        Integration_Aliaddo_WC::sync_products_to_aliaddo($post_ids);

        return $redirect_to;
    }

    public function log($message): void
    {
        $message = (is_array($message) || is_object($message)) ? print_r($message, true) : $message;
        $this->logger->add('integration-aliaddo', $message);
    }
}