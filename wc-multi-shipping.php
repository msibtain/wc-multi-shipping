<?php
/*
Plugin Name: WooCommerce Multi Shipping
Plugin URI: https://github.com/msibtain/wc-multi-shipping
Description: WooCommerce Multi Shipping plugin
Author: msibtain
Version: 1.2.0
Author URI: https://github.com/msibtain/wc-multi-shipping
*/

class WC_Multi_Shipping
{
    private $shipping_total_cost;

    function __construct()
    {
        add_action( 'init', [$this, 'es_wc_session_enabler'] );
        add_action( 'woocommerce_cart_calculate_fees', [$this, 'es_add_all_shipping'], 20, 1 );
        //add_filter( 'woocommerce_cart_needs_shipping', [$this, 'es_cart_needs_shipping'] );
        add_filter( 'woocommerce_cart_ready_to_calc_shipping', [$this, 'es_cart_needs_shipping_v2'] );
        
        add_action( 'wp_footer', [$this, 'es_footer_code'] );
    }

    function es_wc_session_enabler() {
        if ( is_user_logged_in() || is_admin() )
            return;
    
        if ( isset(WC()->session) && ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }
    }

    function es_cart_needs_shipping($needs_shipping)
    {
        $needs_shipping = false;
        return $needs_shipping;
    }

    function es_cart_needs_shipping_v2($needs_shipping)
    {
        if( is_cart() ) { return false; } return $show_shipping;
    }

    function es_add_all_shipping($cart)
    {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

        $all_shippings = [];
        $all_shippings_cost = 0;
        foreach ( WC()->cart->get_shipping_packages() as $package_id => $package ) {
            
            if ( WC()->session->__isset( 'shipping_for_package_'.$package_id ) ) {
                
                foreach ( WC()->session->get( 'shipping_for_package_'.$package_id )['rates'] as $shipping_rate_id => $shipping_rate ) {

                    $rate_id     = $shipping_rate->get_id();
                    $method_id   = $shipping_rate->get_method_id();
                    $instance_id = $shipping_rate->get_instance_id();
                    $label_name  = $shipping_rate->get_label();
                    $cost        = $shipping_rate->get_cost();
                    $tax_cost    = $shipping_rate->get_shipping_tax();
                    $taxes       = $shipping_rate->get_taxes();
                    
                    $all_shippings[] = $label_name;
                    $all_shippings_cost += $cost;

                    //if ( WC()->cart->get_cart_contents_count() <= 1 ) break;  
                    
                    $cart->add_fee( "Shipping - " . $label_name, $cost );

                }
            }

            

        }

        //$lblAllShipping = "Shipping - " . implode(", ", $all_shippings);
        //$cart->add_fee( $lblAllShipping, $all_shippings_cost );

    }

    function es_footer_code()
    {
        ?>
        <style>
            .woocommerce-shipping-totals.shipping {
                display:none;
            }
        </style>
        <?php
    }
}

new WC_Multi_Shipping();

if (!function_exists('p_r')){function p_r($s){echo "<code><pre>";print_r($s);echo "</pre></code>";}}
if (!function_exists('write_log')){ function write_log ( $log )  { if ( is_array( $log ) || is_object( $log ) ) { error_log( print_r( $log, true ) ); } else { error_log( $log ); }}}