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
 * Version:             1.0.11
 * Author URI:          http://www.paneon.de
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$currentDir = "/wp-content/plugins/iro-connection";
$currentDir = plugin_dir_path(__FILE__);

/**
 * Premium Feature
 */
$useAlgolia = false;

if ($useAlgolia) {
    include_once('./vendor/algolia/algoliasearch-client-php/algoliasearch.php');
}

// Include Parsedown Formatter
require_once('./vendor/Parsedown.php');


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
define("IRO_FORMATTER_MARKDOWN", 3);

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
require './plugin-update-checker/plugin-update-checker.php';

$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/Macavity/iro-connection/',
    __FILE__,
    'iro-connection'
);

//Optional: If you're using a private repository, specify the access token like this:
// $myUpdateChecker->setAuthentication('your-token-here');

//Optional: Set the branch that contains the stable release.
//$myUpdateChecker->setBranch('master');


/*----------------------------------------------------------------------------*
 * iRO_Connection
 *----------------------------------------------------------------------------*/
require_once('public/class-iro.php');

register_activation_hook(__FILE__, array('iRO_Connection', 'activate'));
register_deactivation_hook(__FILE__, array('iRO_Connection', 'deactivate'));

add_action('plugins_loaded', array('iRO_Connection', 'get_instance'));

/*----------------------------------------------------------------------------*
 * Shortcodes
 *----------------------------------------------------------------------------*/

include_once('public/iro_shortcodes.php');

/*----------------------------------------------------------------------------*
 * Filter Box Widget
 *----------------------------------------------------------------------------*/

include_once('public/iRO_Widget.php');

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)) {

    require_once( './admin/class-iro-admin.php');
    add_action('plugins_loaded', array('iRO_Connection_Admin', 'get_instance'));

}