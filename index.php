<?php
/**
 * iRO Connection Plugin
 *
 * @package             Paneon_iRO
 *
 * Plugin Name:         iRO Connection
 * Plugin URI:          http://www.heads2hunt.de
 * Description:         Plugin for displaying positions from an iRO Database
 * Author:              Alexander Pape <a.pape@paneon.de>
 * Version:             1.0.4
 * Author URI:          http://www.paneon.de
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require 'plugin-update-checker/plugin-update-checker.php';

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'http://www.paneon.de/wp-content/plugins/iro-connection/metadata.json',
    __FILE__
);

$currentDir = plugin_dir_path(__FILE__);

require_once( $currentDir.'public/class-iro.php' );

register_activation_hook( __FILE__, array( 'iRO_Connection', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'iRO_Connection', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'iRO_Connection', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Shortcodes
 *----------------------------------------------------------------------------*/

include_once ('public/iro_shortcodes.php');

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

    require_once( $currentDir.'admin/class-iro-admin.php' );
    add_action( 'plugins_loaded', array( 'iRO_Connection_Admin', 'get_instance' ) );

}


