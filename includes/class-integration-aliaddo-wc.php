<?php

use Saulmoralespa\Aliaddo\Client;

class Integration_Aliaddo_WC
{
    private static ?Client $aliaddo = null;

    private static $integration_settings = null;

    const SKU_SHIPPING = 'S-P-W';

    const SKU_FEES = 'S-P-F';

    public static function test_token(string $value)
    {
        try {
            $aliaddo = new Client($value);
            $aliaddo->getSellers();
            return true;
        }catch (Exception $e){
            return false;
        }
    }

    public static function get_instance(): ?Client
    {
        if(isset(self::$integration_settings) && isset(self::$aliaddo)) return self::$aliaddo;

        self::$integration_settings = get_option('woocommerce_wc_aliaddo_integration_settings', null);

        if(!isset(self::$integration_settings)) return null;

        self::$integration_settings = (object)self::$integration_settings;

        if(self::$integration_settings->enabled === 'no') return null;

        self::$aliaddo = new Client(self::$integration_settings->token);

        return self::$aliaddo;
    }

    public static function sync_products(): void
    {
        if (!self::get_instance()) return;

        try {

        }catch (Exception $e){
            error_log($e->getMessage());
        }

    }

    public static function sync_products_to_aliaddo(array $ids): void
    {
        if (!self::get_instance()) return;

        foreach ( $ids as $post_id ) {
            $product = wc_get_product($post_id);
            if(!$product->get_sku() || $product->meta_exists('_sync_aliaddo') ) continue;

            try {

                if(!self::$integration_settings->category_id) throw new Exception('No se ha seleccionado una categoría para los productos');
                if(!self::$integration_settings->unit_measurement_id) throw new Exception('No se ha seleccionado una unidad de medida para los productos');
                if(!self::$integration_settings->tax_id) throw new Exception('No se ha seleccionado un impuesto para los productos');
                if(!self::$integration_settings->warehouse_id) throw new Exception('No se ha seleccionado una bodega para los productos');

                $description_without_html = wp_strip_all_tags($product->get_description(), true);
                $manage_stock = $product->get_manage_stock();

                $data_product = [
                    "code" => $product->get_sku(),
                    "name" => $product->get_name(),
                    "categoryId" => self::$integration_settings->category_id,
                    "unitMeasurementId" => self::$integration_settings->unit_measurement_id,
                    "image" => wp_get_attachment_url($product->get_image_id()),
                    "description" => substr($description_without_html, 0,50),
                    "isForBuy" => true,
                    "isForSell" => true,
                    "hasInventoryControl" => $manage_stock,
                    "cost" => wc_format_decimal($product->get_price(), 0),
                    "priceSell" => wc_format_decimal($product->get_sale_price(), 0)
                ];

                if($manage_stock){
                    $data_product = [
                        ...$data_product,
                        "initialStock" => [
                            [
                                "warehouseId" => self::$integration_settings->warehouse_id,
                                "stock" => $product->get_stock_quantity(),
                            ]
                        ]
                    ];
                }

                if(self::$integration_settings->tax_id){
                    $data_product = [
                        ...$data_product,
                        "taxes" => [
                            [
                                "id" => self::$integration_settings->tax_id
                            ]
                        ]
                    ];
                }

                if(self::$integration_settings->withhold_id){
                    $data_product = [
                        ...$data_product,
                        "withholdings" => [
                            [
                                "id" => self::$integration_settings->withhold_id
                            ]
                        ]
                    ];
                }

                self::get_instance()->createProduct($data_product);
                $product->add_meta_data('_sync_aliaddo', true);
                $product->save();
            }catch (Exception $exception){
                integration_aliaddo_wc_iaw()->log($exception->getMessage());
            }
        }
    }

    public static function generate_invoice($order_id, $previous_status, $next_status): void
    {
        if (!self::get_instance() || wc_get_order_status_name($next_status) !== wc_get_order_status_name(self::$integration_settings->order_status_generate_invoice)) return;

        $order = wc_get_order($order_id);

        if($order->meta_exists('_invoice_number_aliaddo')) return;

        $dni_field = 'cedula';
        $dni = get_post_meta( $order_id, "_billing_$dni_field", true ) ?: get_post_meta( $order_id, "_shipping_$dni_field", true);
        $first_name = $order->get_billing_first_name() ?: $order->get_shipping_first_name();
        $last_name = $order->get_billing_last_name() ?: $order->get_shipping_last_name();
        $phone = $order->get_billing_phone() ?: $order->get_shipping_phone();
        $address = $order->get_billing_address_1() ?: $order->get_shipping_address_1();
        $full_name = $order->get_formatted_billing_full_name() ?: $order->get_formatted_shipping_full_name();

        $state = $order->get_billing_state() ?: $order->get_shipping_state();
        $city = $order->get_billing_city() ?: $order->get_shipping_city();
        $states_dane = include(dirname(__FILE__) . '/states-dane.php');
        $state_code = $states_dane[$state] ?? null;
        $city_code = self::get_code_city($state, $city);

        try {

            if(!$dni) throw new Exception('No se ha ingresado un documento de identidad');
            if(!$state_code) throw new Exception('Departamento no encontrado');
            if(!self::$integration_settings->warehouse_id) throw new Exception('No se ha seleccionado una bodega para los productos');
            if(!self::$integration_settings->seller_id) throw new Exception('No se ha seleccionado un vendedor para las facturas');
            if(!self::$integration_settings->branch_id) throw new Exception('No se ha seleccionado una sucursal para las facturas');

            $data_client = [
                "kind" => "Person", //Company, Person
                "identificationType" => "13", //31 NIT, 13 Cédula
                "identification" => $dni,
                //"identificationCheck" => "4", //optional for NIT
                "firstName" => $first_name,
                //"secondName" => "David",
                "firstSurname" => $last_name, //first last name
                //"secondSurname" => "Gomez", //second last name
                //"companyName" => "Tinto puro", //required if kind is Company
                "phoneMobile" => $phone,
                "phoneWork" => $phone,
                "isCustomer" => true, // default false
                "isSupplier" => false, // default false
                "isEmployee" => false, // default false
                "isSeller" => false, // default false
                "emails" => [
                    [
                        "email" => $order->get_billing_email(),
                        "isMain" => false // default false
                    ]
                ],
                "addresses" => [
                    [
                        "name" => $full_name,
                        "address" => $address,
                        "countryCode" => "CO",
                        "region" => $state_code,
                        "city" => $city_code, //chia
                        //"postalCode" => "545510",
                        //"neighborhood" => "Barrio 1",
                        "phone" => $phone,
                        "isForBilling" => true,
                        "isForShipping" => false,
                        "isDefault" => true
                    ]
                ]
            ];

            $queries = [
                "kind" => "Person",
                "identification" => $dni
            ];

            $clients = self::get_instance()->getClients($queries);

            if(empty($clients)){
                $client = self::get_instance()->createClient($data_client);
            }else {
                $client = array_pop($clients);
            }

            $queries = [
                "code" => self::SKU_SHIPPING
            ];

            $product_shipping = [
                "code" => self::SKU_SHIPPING,
                "name" => "Envío",
                "categoryId" => self::$integration_settings->category_id,
                "unitMeasurementId" => self::$integration_settings->unit_measurement_id,
                "description" => "Envío"
            ];

            $products = self::get_instance()->getProducts($queries);

            if(empty($products)){
                self::get_instance()->createProduct($product_shipping);
            }

            $queries = [
                "code" => self::SKU_FEES
            ];

            $product_fees = [
                "code" => self::SKU_FEES,
                "name" => "Cuotas",
                "categoryId" => self::$integration_settings->category_id,
                "unitMeasurementId" => self::$integration_settings->unit_measurement_id,
                "description" => "Cuotas"
            ];

            $products = self::get_instance()->getProducts($queries);

            if(empty($products)){
                self::get_instance()->createProduct($product_fees);
            }

            $items = [];

            if(self::$integration_settings->tax_id){
                $tax = self::get_instance()->getTaxById(self::$integration_settings->tax_id);
            }
            $rate = $tax["rate"] ?? 0;

            foreach ($order->get_items() as $item){
                /**
                 * @var WC_Product|bool $product
                 */
                $product = $item->get_product();
                $description_without_html = wp_strip_all_tags($product->get_description(), true);
                $subtotal = $item->get_subtotal() / $item->get_quantity();
                $unit_value_before_tax = wc_format_decimal(round($subtotal / (1 + $rate / 100)));
                $discount = ($item->get_subtotal() - $item->get_total()) / $item->get_quantity();
                $price_regular = $unit_value_before_tax;
                $items[] = [
                    "unitValueBeforeTax" => $price_regular,
                    "itemCode" => $product->get_sku(),
                    "quantity" => $item->get_quantity(),
                    "warehouseId" => self::$integration_settings->warehouse_id,
                    "description" => substr($description_without_html, 0,50),
                    "discountAmount" => 0, //int32
                    "discountIsPercent" => false //true percentage, false amount
                ];

                if(self::$integration_settings->tax_id){
                    $items[count($items) - 1]["taxes"] = [
                        [
                            "id" => self::$integration_settings->tax_id
                        ]
                    ];
                }

                if(self::$integration_settings->withhold_id){
                    $items[count($items) - 1]["withholdings"] = [
                        [
                            "id" => self::$integration_settings->withhold_id
                        ]
                    ];
                }
            }

            if($order->get_shipping_total()){
                $unit_value_before_tax = round($order->get_shipping_total() / (1 + $rate / 100));
                $unit_value_before_tax = wc_format_decimal($unit_value_before_tax, 0);
                $items[] = [
                    "unitValueBeforeTax" => $unit_value_before_tax,
                    "itemCode" => self::SKU_SHIPPING,
                    "quantity" => 1,
                    "taxes" => [
                        [
                            "id" => self::$integration_settings->tax_id
                        ]
                    ],
                ];
            }

            if($order->get_total_fees()){
                $unit_value_before_tax = round($order->get_total_fees()  / (1 + $rate / 100));
                $unit_value_before_tax = wc_format_decimal($unit_value_before_tax, 0);
                $items[] = [
                    "unitValueBeforeTax" => $unit_value_before_tax,
                    "itemCode" => self::SKU_FEES,
                    "quantity" => 1,
                    "taxes" => [
                        [
                            "id" => self::$integration_settings->tax_id
                        ]
                    ],
                ];
            }

            $data_invoice = [
                "personId" => $client["id"], //id client
                "date" => wp_date('Y-m-d'),
                "dueDate" => wp_date('Y-m-d', strtotime('+1 day')),
                "paymentFormCode" => "CN", //CR Crédito, CN Contado
                "paymentMeanCode" => "10", //10 Efectivo, 48 Tarjeta de crédito, 49 Tarjeta débito
                "costCenterId" => self::$integration_settings->cost_center_id, //id cost center
                "personIdSeller" => self::$integration_settings->seller_id, //id seller
                "branchId" => self::$integration_settings->branch_id, //id branch
                "details" => $items,
                "currencyCode" => $order->get_currency(),
                "exchangeRate" => 0, //use if currencyCode is different to COP
                "accountCodePayment" => "", //use if paymentFormCode is CN
                "observation" => "",
                "customerNote" => "",
                "termsAndConditions" => ""
            ];
            integration_aliaddo_wc_iaw()->log($data_invoice);
            $invoice = self::get_instance()->createInvoice($data_invoice);
            $order->add_order_note(sprintf(__( 'Factura Aliaddo ID: %s' ), $invoice['id']));
            $order->add_meta_data('_invoice_number_aliaddo', $invoice["id"]);
            $order->save_meta_data();
        }catch (Exception $exception){
            integration_aliaddo_wc_iaw()->log($exception->getMessage());
        }
    }

    public static function get_categories(): array
    {
        $categories = [];
        if (!self::get_instance()) return $categories;

        try {
            $categories = self::$aliaddo->getCategories();
        }catch (Exception $exception){
            integration_aliaddo_wc_iaw()->log($exception->getMessage());
        }

        return $categories;
    }

    public static function get_measurements_units(): array
    {
        $unit_measurements = [];
        if (!self::get_instance()) return $unit_measurements;

        try {
            $unit_measurements = self::$aliaddo->getMeasuringUnits();
        }catch (Exception $exception){
            integration_aliaddo_wc_iaw()->log($exception->getMessage());
        }

        return $unit_measurements;
    }

    public static function get_taxes(): array
    {
        $taxes = [];
        if (!self::get_instance()) return $taxes;

        try {
            $taxes = self::$aliaddo->getTaxes();
        }catch (Exception $exception){
            integration_aliaddo_wc_iaw()->log($exception->getMessage());
        }

        return $taxes;
    }

    public static function get_withholdings(): array
    {
        $withholdings = [];
        if (!self::get_instance()) return $withholdings;

        try {
            $withholdings = self::$aliaddo->getWithholdings();
        }catch (Exception $exception){
            integration_aliaddo_wc_iaw()->log($exception->getMessage());
        }

        return $withholdings;
    }

    public static function get_warehouses()
    {
        $warehouses = [];
        if (!self::get_instance()) return $warehouses;

        try {
            $warehouses = self::$aliaddo->getWarehouses();
        }catch (Exception $exception){
            integration_aliaddo_wc_iaw()->log($exception->getMessage());
        }

        return $warehouses;
    }

    public static function get_cost_centers()
    {
        $cost_centers = [];
        if (!self::get_instance()) return $cost_centers;

        try {
            $queries = [
                "page" => 1,
                "itemPerPage" => 20
            ];
            $cost_centers = self::$aliaddo->getCostCenters($queries);
        }catch (Exception $exception){
            integration_aliaddo_wc_iaw()->log($exception->getMessage());
        }

        return $cost_centers;
    }

    public static function get_sellers()
    {
        $sellers = [];
        if (!self::get_instance()) return $sellers;

        try {
            $sellers = self::$aliaddo->getSellers();
        }catch (Exception $exception){
            integration_aliaddo_wc_iaw()->log($exception->getMessage());
        }

        return $sellers;
    }

    public static function get_branches()
    {
        $branches = [];
        if (!self::get_instance()) return $branches;

        try {
            $branches = self::$aliaddo->getBranches();
        }catch (Exception $exception){
            integration_aliaddo_wc_iaw()->log($exception->getMessage());
        }

        return $branches;
    }

    public static function get_code_city($state, $city, $country = 'CO'): bool|string
    {
        $name_state = self::name_destination($country, $state);
        $address = "$city - $name_state";
        $cities = include dirname(__FILE__) . '/cities.php';
        $address  = self::clean_string($address);
        $cities = self::clean_cities($cities);
        $destine = array_search($address, $cities);

        if ($destine && strlen($destine) === 4)
            $destine = "0" . $destine;

        return $destine;
    }

    public static function name_destination($country, $state_destination): string
    {
        $countries_obj = new WC_Countries();
        $country_states_array = $countries_obj->get_states();

        $name_state_destination = '';

        if (!isset($country_states_array[$country][$state_destination]))
            return $name_state_destination;

        $name_state_destination = $country_states_array[$country][$state_destination];
        return self::clean_string($name_state_destination);
    }

    public static function clean_string(string $string):string
    {
        $not_permitted = array("á", "é", "í", "ó", "ú", "Á", "É", "Í",
            "Ó", "Ú", "ñ");
        $permitted = array("a", "e", "i", "o", "u", "A", "E", "I", "O",
            "U", "n");
        $text = str_replace($not_permitted, $permitted, $string);
        return mb_strtolower($text);
    }

    public static function clean_cities($cities)
    {
        foreach ($cities as $key => $value) {
            $cities[$key] = self::clean_string($value);
        }

        return $cities;
    }
}