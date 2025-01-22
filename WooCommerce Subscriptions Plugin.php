<?php
/**
 * Plugin Name: WooCommerce Subscriptions Plugin
 * Plugin URI: https://denverdoran.com
 * Description: A WooCommerce plugin for managing subscriptions.
 * Version: 1.0.0
 * Author: Denver Doran
 * Author URI: https://denverdoran.com https://kreativebirds.com
 * License: GPL2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu for toggling the plugin on/off.
 */
function subscription_plugin_admin_menu() {
    add_menu_page(
        __('Subscription Plugin Settings', 'woocommerce'),
        __('Subscriptions', 'woocommerce'),
        'manage_options',
        'subscription-plugin-settings',
        'subscription_plugin_settings_page',
        'dashicons-admin-plugins',
        81
    );
}
add_action('admin_menu', 'subscription_plugin_admin_menu');

/**
 * Display the settings page.
 */
function subscription_plugin_settings_page() {
    if (isset($_POST['toggle_subscription_plugin'])) {
        update_option('subscription_plugin_enabled', $_POST['toggle_subscription_plugin'] === 'on');
    }
    $is_enabled = get_option('subscription_plugin_enabled', true);
    ?>
    <div class="wrap">
        <h1><?php _e('Subscription Plugin Settings', 'woocommerce'); ?></h1>
        <form method="post">
            <label>
                <input type="checkbox" name="toggle_subscription_plugin" <?php checked($is_enabled, true); ?>>
                <?php _e('Enable Subscription Plugin', 'woocommerce'); ?>
            </label>
            <br><br>
            <button type="submit" class="button button-primary">Save Settings</button>
        </form>
    </div>
    <?php
}

/**
 * Disable plugin functionality if toggled off.
 */
if (!get_option('subscription_plugin_enabled', true)) {
    return;
}

/**
 * Register a custom product type for subscriptions.
 */
function register_subscription_product_type() {
    class WC_Product_Subscription extends WC_Product {
        public function __construct($product) {
            parent::__construct($product);
            $this->product_type = 'subscription';
        }
    }
}
add_action('init', 'register_subscription_product_type');

/**
 * Add subscription to product types dropdown in admin.
 */
function add_subscription_product($types) {
    $types['subscription'] = __('Subscription', 'woocommerce');
    return $types;
}
add_filter('product_type_selector', 'add_subscription_product');

/**
 * Add custom fields for subscription details (e.g., billing interval).
 */
function add_subscription_fields() {
    global $post;
    echo '<div class="options_group subscription-product-fields">';

    // Billing interval field
    woocommerce_wp_select([
        'id' => '_billing_interval',
        'label' => __('Billing Interval', 'woocommerce'),
        'options' => [
            'weekly' => __('Weekly', 'woocommerce'),
            'monthly' => __('Monthly', 'woocommerce'),
            'annually' => __('Annually', 'woocommerce'),
        ],
    ]);

    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'add_subscription_fields');

/**
 * Save custom fields for subscription products.
 */
function save_subscription_fields($post_id) {
    $billing_interval = isset($_POST['_billing_interval']) ? sanitize_text_field($_POST['_billing_interval']) : '';

    update_post_meta($post_id, '_billing_interval', $billing_interval);
}
add_action('woocommerce_process_product_meta', 'save_subscription_fields');

/**
 * Handle recurring payments.
 */
function process_recurring_payments($order_id) {
    $order = wc_get_order($order_id);

    // Example logic for handling payment retries
    if ($order->get_status() === 'failed') {
        // Retry logic (can be customized based on payment gateway API)
    }
}
add_action('woocommerce_order_status_failed', 'process_recurring_payments');

/**
 * Add subscription management to "My Account" page.
 */
function add_subscription_management_to_my_account() {
    add_rewrite_endpoint('subscriptions', EP_ROOT | EP_PAGES);
    add_action('woocommerce_account_subscriptions_endpoint', 'display_subscription_management');
}
add_action('init', 'add_subscription_management_to_my_account');

function display_subscription_management() {
    echo '<h2>' . __('My Subscriptions', 'woocommerce') . '</h2>';
    // Query and display user subscriptions (custom query logic needed)
}

/**
 * Email notifications for subscription events.
 */
function send_subscription_email_notifications($subscription_id, $event_type) {
    // Example email notification logic
    $to = 'customer@example.com';
    $subject = __('Subscription Update', 'woocommerce');
    $message = __('Your subscription has been updated.', 'woocommerce');

    wp_mail($to, $subject, $message);
}
add_action('subscription_event', 'send_subscription_email_notifications', 10, 2);

/**
 * Enqueue custom CSS for subscription product fields.
 */
function enqueue_subscription_plugin_styles() {
    wp_enqueue_style('subscription-plugin-styles', plugin_dir_url(__FILE__) . 'assets/css/styles.css');
}
add_action('admin_enqueue_scripts', 'enqueue_subscription_plugin_styles');

/**
 * Add CSS file to the plugin directory.
 */
// Add the following CSS to a new file at assets/css/styles.css
// .subscription-product-fields {
//     background-color: #f9f9f9;
//     border: 1px solid #ddd;
//     padding: 15px;
//     margin-top: 15px;
// }
// .subscription-product-fields label {
//     font-weight: bold;
// }
