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
 * Version:             1.0.5
 * Author URI:          http://www.paneon.de
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

$currentDir = "/wp-content/plugins/iro-connection";
$currentDir = plugin_dir_path(__FILE__);

// Include Parsedown Formatter
require_once( $currentDir.'vendor/Parsedown.php');


/*
 *---------------------------------------------------------------
 * Defines
 *---------------------------------------------------------------
 */
define('IRO_JOB_TYPE_HIDDEN', 0);
define('IRO_JOB_TYPE_NORMAL', 1);
define('IRO_JOB_TYPE_ARCHIVE', 2);

define("IRO_FORMATTER_BASIC", 1);
define("IRO_FORMATTER_SIMPLE", 2);

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require 'plugin-update-checker/plugin-update-checker.php';

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'http://www.paneon.de/wp-content/plugins/iro-connection/metadata.json',
    __FILE__
);


require_once( $currentDir.'public/class-iro.php' );

register_activation_hook( __FILE__, array( 'iRO_Connection', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'iRO_Connection', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'iRO_Connection', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Shortcodes
 *----------------------------------------------------------------------------*/

include_once ('public/iro_shortcodes.php');

/*----------------------------------------------------------------------------*
 * Filter Box Widget
 *----------------------------------------------------------------------------*/

include_once ('public/iRO_Widget.php');

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

    require_once( $currentDir.'admin/class-iro-admin.php' );
    add_action( 'plugins_loaded', array( 'iRO_Connection_Admin', 'get_instance' ) );

}
