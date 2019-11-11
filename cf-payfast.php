<?php
/**
 * Plugin Name: Caldera Forms PayFast
 * Description: PayFast payment processor for Caldera Forms.
 * Plugin URI: https://yoohooplugins.com
 * Author: Yoohoo Plugins
 * Author URI: https://yoohooplugins.com
 * Version: 1.4
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cf-payfast
 * Domain Path: languages
 * Network: false
 *
 *
 * Caldera Forms PayFast is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Caldera Forms PayFast is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Caldera Forms PayFast. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

defined( 'ABSPATH' ) or exit;

// define constants
define( 'CF_PAYFAST_PATH',  plugin_dir_path( __FILE__ ) );
define( 'CF_PAYFAST_URL',  plugin_dir_url( __FILE__ ) );
define( 'CF_PAYFAST_VER', '1.4' );

// Add language text domain
add_action( 'init', 'cf_payfast_load_plugin_textdomain' );

// filter to add processor to regestered processors array
add_filter( 'caldera_forms_get_form_processors', 'cf_payfast_register' );

// Setup payfast Redirect
add_action( 'caldera_forms_submit_start_processors', 'cf_payfast_express_set_transient', 10, 3 );

// Perform payfast Redirect
add_filter( 'caldera_forms_submit_return_redirect', 'cf_payfast_redirect', 10, 3 );


// pull in the functions file
include CF_PAYFAST_PATH . 'includes/functions.php';
