<?php

$docs = "<p><a target='_blank' href='https://aliaddo.readme.io/reference/autenticaci%C3%B3n-por-api-key'>Credenciales Aliaddo API</a></p>";

$options_default = [
    '' => 'Ninguno',
];

$categories = $this->get_data_options('wc_aliaddo_integration', 'Integration_Aliaddo_WC::get_categories', function($new_option, $option){
    $new_option[$option["id"]] = "{$option["name"]}";
    return $new_option;
});

$measurements_units = $this->get_data_options('wc_aliaddo_integration', 'Integration_Aliaddo_WC::get_measurements_units', function($new_option, $option){
    $new_option[$option["id"]] = "{$option["name"]}";
    return $new_option;
});

$taxes = $this->get_data_options('wc_aliaddo_integration', 'Integration_Aliaddo_WC::get_taxes', function($new_option, $option){
    $new_option[$option["id"]] = "{$option["name"]}";
    return $new_option;
});
$taxes = [...$options_default, ...$taxes];

$withholdings = $this->get_data_options('wc_aliaddo_integration', 'Integration_Aliaddo_WC::get_withholdings', function($new_option, $option){
    $new_option[$option["id"]] = "{$option["name"]}";
    return $new_option;
});
$withholdings = [...$options_default, ...$withholdings];

$warehouses = $this->get_data_options('wc_aliaddo_integration', 'Integration_Aliaddo_WC::get_warehouses', function($new_option, $option){
    $new_option[$option["id"]] = "{$option["name"]}";
    return $new_option;
});

$cost_centers = $this->get_data_options('wc_aliaddo_integration', 'Integration_Aliaddo_WC::get_cost_centers', function($new_option, $option){
    $new_option[$option["id"]] = "{$option["name"]}";
    return $new_option;
});

$cost_centers = [...$options_default, ...$cost_centers];

$sellers = $this->get_data_options('wc_aliaddo_integration', 'Integration_Aliaddo_WC::get_sellers', function($new_option, $option){
    $new_option[$option["id"]] = "{$option["name"]}";
    return $new_option;
});

$branches = $this->get_data_options('wc_aliaddo_integration', 'Integration_Aliaddo_WC::get_branches', function($new_option, $option){
    $new_option[$option["id"]] = "{$option["name"]}";
    return $new_option;
});


return apply_filters('wc_aliaddo_integration_settings', [
    'enabled' => array(
        'title' => __('Activar/Desactivar'),
        'type' => 'checkbox',
        'label' => __('Activar Aliaddo'),
        'default' => 'no'
    ),
    'debug' => array(
        'title'       => __( 'Depurador' ),
        'label'       => __( 'Habilitar el modo de desarrollador' ),
        'type'        => 'checkbox',
        'default'     => 'no',
        'description' => __( 'Enable debug mode to show debugging information in woocommerce - status' ),
        'desc_tip' => true
    ),
    'api'  => array(
        'title' => __( 'Credenciales API' ),
        'type'  => 'title',
        'description' => $docs
    ),
    'token' => array(
        'title' => __( 'Token' ),
        'type'  => 'password',
        'description' => __( 'token para el entorno de producción' ),
        'desc_tip' => true
    ),
    'inventory' => array(
        'title' => __( 'Inventario' ),
        'type'  => 'title'
    ),
    'category_id' => array(
        'title' => __( 'Categoría de los productos' ),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'options'  => $categories,
        'default' => '',
        'description' => __( 'La categoría que desea asociar a los productos' ),
        'desc_tip' => false
    ),
    'unit_measurement_id' => array(
        'title' => __( 'Unidad de medida de los productos' ),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'options'  => $measurements_units,
        'default' => '',
        'description' => __( 'La unidad de medida que desea asociar a los productos' ),
        'desc_tip' => false
    ),
    'warehouse_id' => array(
        'title' => __( 'Bodega' ),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'options'  => $warehouses,
        'default' => '',
        'description' => __( 'La bodega que desea asociar a los productos' ),
        'desc_tip' => false
    ),
    'taxes' => array(
        'title' => __( 'Impuestos' ),
        'type'  => 'title'
    ),
    'tax_id' => array(
        'title' => __( 'Impuesto' ),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'options'  => $taxes,
        'default' => '',
        'description' => __( 'El impuesto que desea asociar a los productos' ),
        'desc_tip' => false
    ),
    'withhold_id' => array(
        'title' => __( 'Retención' ),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'options'  => $withholdings,
        'default' => '',
        'description' => __( 'La retención que desea asociar a los productos' ),
        'desc_tip' => false
    ),
    'invoice' => array(
        'title' => __( 'Factura' ),
        'type'  => 'title'
    ),
    'cost_center_id' => array(
        'title' => __( 'Centro de costos' ),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'options'  => $cost_centers,
        'default' => '',
        'description' => __( 'El centro de costos que desea asociar a las facturas' ),
        'desc_tip' => false
    ),
    'seller_id' => array(
        'title' => __( 'Vendedor' ),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'options'  => $sellers,
        'default' => '',
        'description' => __( 'El vendedor que desea asociar a las facturas' ),
        'desc_tip' => false
    ),
    'branch_id' => array(
        'title' => __( 'Sucursal' ),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'options'  => $branches,
        'default' => '',
        'description' => __( 'La sucursal que desea asociar a las facturas' ),
        'desc_tip' => false
    ),
    'order_status_generate_invoice' => array(
        'title' => __( 'Estado del pedido' ),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'options'  => wc_get_order_statuses(),
        'default' => 'wc-processing',
        'description' => __( 'El estado del pedido en el que se genera la factura' ),
        'desc_tip' => false
    )
]);

