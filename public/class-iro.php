<?php
/**
 * iRO Connection
 *
 * @package   Paneon_iRO
 * @author    Alexander Pape <a.pape@paneon.de>
 * @license   GPL-2.0+
 */

class iRO_Connection {

    /**
     * Plugin version
     *
     * @var     string
     */
    const VERSION = '1.0.6';

    const API_DOMAIN = 'http://api-dev.paneon.de';

    /**
     * Unique identifier
     *
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * plugin file.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_slug = 'iro';

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    private $pageTitle = "";

    public static $jsonFields = array(
        'job_intro','position','location', 'industry',
        'job_description','job_candidate','job_desirability',
        //'job_resume',
        'contact_name'
    );

    private $jsFiles = array(
        'iro_js_filter' => '/iro-connection/assets/js/joblist.js'
    );

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     *
     * @since     1.0.0
     */
    protected function __construct() {

        // Load plugin text domain
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

        // Activate plugin when new blog is added
        add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

        // Load public-facing style sheet and JavaScript.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        /*
         * Define custom functionality.
         * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
         */
        add_action( 'init',                     array($this, 'register_custom_post_type' ) );

        // Rewrite for Job Detail
        //add_action('query_vars',                array($this, 'add_query_vars'));
        add_action('init',                      array($this, 'add_rewrite_rules'));
        add_action('template_redirect',         array($this, 'add_template_redirect'));

        /*
         *  Add Page template for Joblist
         */

        // Add a filter to the attributes metabox to inject template into the cache.
        add_filter(
            'page_attributes_dropdown_pages_args',
             array( $this, 'register_project_templates' )
        );
        // Add a filter to the save post to inject out template into the page cache
        add_filter(
            'wp_insert_post_data',
            array( $this, 'register_project_templates' )
        );
        // Add a filter to the template include to determine if the page has our
        // template assigned and return it's path
        add_filter(
            'template_include',
            array( $this, 'view_project_template')
        );

        // Change the Title on the Jobdetail page
        //add_filter( 'wp_title', 'change_wp_title', 10, 2 );

        // Add your templates to this array.
        $this->templates = array(
            'joblist-blocks.php'    => 'iRO Jobliste',
            'joblist.php'           => 'iRO Jobliste - Tabelle',
        );

    }

    /**
     * Adds the query variable "job_id" and "job_lang"
     *
     * @param $qvars
     * @return array
     */
    public function add_query_vars( $qvars ) {
        $qvars[] = 'job_id';
        $qvars[] = 'job_lang';

        return $qvars;

    }

    /**
     * Adds a rewrite rule for job detail pages
     *
     * @param $rules
     * @return array
     */
    public function add_rewrite_rules($rules){

        add_rewrite_rule(
            'job/([A-z]+)/(\d+)(.*)$',
            'index.php?pagename=jobdetail&job_lang=$matches[1]&job_id=$matches[2]',
            "top");

        add_rewrite_tag('%job_id%', '[0-9]+');
        add_rewrite_tag('%job_lang%', '[A-z]+');

        if ( ! isset( $rules['job/(.+)/(\d+)(.*)'] ) ) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }

        return $rules;
    }

    /**
     * @since 1.0.1
     * @return string
     */
    public static function get_api_domain(){
        return self::API_DOMAIN;
    }

    /**
     * @since 1.0.1
     * @return string
     */
    public static function get_serial(){
        return get_option('iro_connection_serial');
    }

    public function add_template_redirect(){
        global $wp_query, $wpdb, $wp_title;

        if (!session_id()) {
            session_start();
        }

        if(get_query_var('job_id')){
            $wp_query->is_404 = false;

            $page_name_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name ='jobdetail'");
            get_post($page_name_id);

            $jobId = get_query_var('job_id');
            $iroSerial = get_option('iro_connection_serial');

            $curlUrl = self::API_DOMAIN.'/data/'.$iroSerial.'/job-detail/'.$jobId;

            $curlHandle = curl_init($curlUrl);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

            $requestData = curl_exec($curlHandle);

            curl_close($curlHandle);

            $jsonData = json_decode($requestData, true);

            $iro_job = array();

            if(isset($jsonData['result'])){
                $iro_job = $jsonData['result'];

                if(!empty($iro_job['google_title'])){
                    $this->pageTitle = $iro_job['google_title'];
                }
                else {
                    $this->pageTitle = $iro_job['position'];
                }

                foreach(self::$jsonFields as $fieldName){
                    if(empty($iro_job[$fieldName])){
                        $iro_job[$fieldName] = "";
                    }
                }

                $wp_title = $this->pageTitle . " | " . $wp_title;
                //wp_title();
            }

            //include_once(plugin_dir_path(__FILE__).'views/jobdetail.php');
            //exit();
        }
    }

    public function change_wp_title($title, $sep){

        if ( !empty($this->pageTitle) ) {
            $title .= $this->pageTitle;
        }

        return $title;
    }

    /**
     * Return the plugin slug.
     *
     * @since    1.0.0
     *
     * @return    String Plugin slug variable.
     */
    public function get_plugin_slug() {
        return $this->plugin_slug;
    }

    public function register_project_templates( $atts ) {

        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

        // Retrieve the cache list.
        // If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
            $templates = array();
        }

        // New cache, therefore remove the old one
        wp_cache_delete( $cache_key , 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $templates, $this->templates );

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        return $atts;

    }

    /**
     * Checks if the template is assigned to the page
     */
    public function view_project_template( $template ) {

        global $post;

        if (!isset($this->templates[get_post_meta(
            $post->ID, '_wp_page_template', true
        )] ) ) {

            return $template;

        }

        $file = plugin_dir_path(__FILE__).'views/'. get_post_meta(
                $post->ID, '_wp_page_template', true
            );

        // Just to be safe, we check if the file exist first
        if( file_exists( $file ) ) {
            return $file;
        }
        else { echo $file; }

        return $template;

    }

    public static function shortcodeJobsCount($atts, $content = ""){
        $type = "";

        extract(
            shortcode_atts(
                array(
                    'type' => 'open',
                ),
                $atts )
        );

        return '<span class="jsIroJobCount">'.iRO_Connection::getJobsCount($type).'</span>';

    }

    public static function getJobDetail($jobId = 0){

        $iroSerial = get_option('iro_connection_serial');

        $curlUrl = self::API_DOMAIN.'/data/'.$iroSerial.'/job-detail/'.$jobId;

        $curlHandle = curl_init($curlUrl);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

        $requestData = curl_exec($curlHandle);

        curl_close($curlHandle);

        $jsonData = json_decode($requestData, true);

        $iro_job = array();

        if(isset($jsonData['result'])){
            $iro_job = $jsonData['result'];

            foreach(self::$jsonFields as $fieldName){
                if(empty($iro_job[$fieldName])){
                    $iro_job[$fieldName] = "";
                }
            }

        }

        return $iro_job;
    }

    /**
     * @param string $type
     * @return array
     */
    public static function getJobs($type = 'open'){

        $iroSerial = get_option('iro_connection_serial');

        $joblist = array();

        try{
            /*
             * Load Jobs from API
             */
            if($type == "archive" || $type == "archiv"){
                $curlUrl = self::API_DOMAIN.'/data/'.$iroSerial.'/jobs/desc/archiv';
            }
            else {
                $curlUrl =  self::API_DOMAIN.'/data/'.$iroSerial.'/jobs/desc';
            }

            $curlHandle = curl_init($curlUrl);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

            $requestData = curl_exec($curlHandle);

            curl_close($curlHandle);

            $jsonData = json_decode($requestData, true);

            if(isset($jsonData['results'])){
                $joblist = $jsonData['results'];
            }

        }
        catch(Exception $exception){

        }

        return $joblist;
    }

    public static function getJobsCount($type = 'open'){

        $joblistResults = self::getJobs($type);

        return count($joblistResults);

    }

    public static function allowSimpleHtml($text){
        // FM 12 liefert decodierte Entities
        $text = html_entity_decode($text);

        /*
         * Text mit htmlentities anwenden,
         */
        $text = htmlspecialchars_decode( htmlentities($text, ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES);

        // Tags entfernen die nicht erlaubt sind.
        $text = strip_tags($text, '<p><a><b><i><br><ul><li><ol><hr>');

        $text = str_replace("&amp;", "&", $text);

        return $text;
    }

    public static function formatText($text, $formatter = IRO_FORMATTER_SIMPLE){

        if($formatter == IRO_FORMATTER_BASIC){
            return iRO_Connection::allowSimpleHtml($text);
        }
        if($formatter == IRO_FORMATTER_SIMPLE){
            return iRO_Connection::simpleFormatting($text);
        }

    }

    public static function simpleFormatting($text){

        // FM 12 liefert encodierte Entities
        $text = html_entity_decode($text);


        $textLines = explode("\n",$text);
        $formattedText = '';

        foreach($textLines as $line){
            // Kein Listenpunkt
            if(preg_match("/^!(.*)$/",$line,$match)){
                //$line = preg_replace("/^!(.*)$/","\n<li style=\"list-style-type:none\">$1</li>",$line);
                $line = "\n<li style=\"list-style-type:none\">$match[1]</li>";
            }
            // Unterberschrift
            elseif(preg_match("/^(.*):$/",$line,$match)){
                //$line = preg_replace("/^(.*):$/","</ul>\n<b>$1:</b>\n<ul>",$line);
                $line = "</ul>\n<b>$match[1]:</b>\n<ul>";
            }
            // Listenpunkt
            elseif(preg_match("/^(.*)$/",$line,$match)){
                //$line = preg_replace("/^(.*)$/","\n<li>$1</li>",$line);
                $line = "\n<li>$match[1]</li>";
            }

            // Korrekturen
            if(preg_match("/<li><b>(.*):<\/b><\/li>/",$line,$match)){
                $line = "</ul>\n<b>$match[1]:</b>\n<ul>";
            }
            $formattedText .= $line;
        }

        // Leere Zeile
        $formattedText = str_replace("<li><\/li>",'<li style="list-style-type:none;">&nbsp;</li>',$formattedText);
        $formattedText = str_replace("<li></li>",'<li style="list-style-type:none;">&nbsp;</li>',$formattedText);

        return '<ul>'.$formattedText.'</ul>';
    }

    /**
     * Makes a call to the API that will check if the cache needs to be refreshed
     */
    public static function check_cache($type){

        $iroSerial = get_option('iro_connection_serial');

        self::do_post_request(
            self::API_DOMAIN.'/data/'.$iroSerial.'/check-cache/jobs/'.$type
        );
    }

    protected static function do_post_request($url, $data = array(), $optional_headers = null,$getresponse = false) {
        $params = array('http' => array(
            'method' => 'GET',
            'content' => $data
        ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            return false;
        }
        if ($getresponse){
            $response = stream_get_contents($fp);
            return $response;
        }
        return true;
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses
     *                                       "Network Activate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       activated on an individual blog.
     */
    public static function activate( $network_wide ) {

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {

            if ( $network_wide  ) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ( $blog_ids as $blog_id ) {

                    switch_to_blog( $blog_id );
                    self::single_activate();
                }

                restore_current_blog();

            } else {
                self::single_activate();
            }

        } else {
            self::single_activate();
        }

        add_action( 'wp_loaded', 'iro_plugin_flush_rules' );

    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses
     *                                       "Network Deactivate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       deactivated on an individual blog.
     */
    public static function deactivate( $network_wide ) {

        if ( function_exists( 'is_multisite' ) && is_multisite() ) {

            if ( $network_wide ) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ( $blog_ids as $blog_id ) {

                    switch_to_blog( $blog_id );
                    self::single_deactivate();

                }

                restore_current_blog();

            } else {
                self::single_deactivate();
            }

        } else {
            self::single_deactivate();
        }

        add_action( 'wp_loaded', 'iro_plugin_flush_rules');

    }

    /**
     * Fired when a new site is activated with a WPMU environment.
     *
     * @since    1.0.0
     *
     * @param    int    $blog_id    ID of the new blog.
     */
    public function activate_new_site( $blog_id ) {

        if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
            return;
        }

        switch_to_blog( $blog_id );
        self::single_activate();
        restore_current_blog();

    }

    /**
     * Get all blog ids of blogs in the current network that are:
     * - not archived
     * - not spam
     * - not deleted
     *
     * @since    1.0.0
     *
     * @return   array|false    The blog ids, false if no matches.
     */
    private static function get_blog_ids() {

        global $wpdb;

        // get an array of blog ids
        $sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

        return $wpdb->get_col( $sql );

    }

    /**
     * Fired for each blog when the plugin is activated.
     *
     * @since    1.0.0
     */
    private function single_activate() {

    }

    /**
     * Fired for each blog when the plugin is deactivated.
     *
     * @since    1.0.0
     */
    private function single_deactivate() {
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        $domain = $this->plugin_slug;
        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

        load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

    }

    /**
     * Register and enqueue public-facing style sheet.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        //wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/iroconnection.css', __FILE__ ), array(), self::VERSION );
    }

    /**
     * Register and enqueues public-facing JavaScript files.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        foreach($this->jsFiles as $plugin_slug => $plugin_url){
            wp_deregister_script( $plugin_slug );
            wp_register_script(
                $plugin_slug,
                plugins_url('/iro-connection/assets/js/joblist.js')
            );
            wp_enqueue_script( $plugin_slug );
        }

    }

    /**
     * NOTE:  Actions are points in the execution of a page or process
     *        lifecycle that WordPress fires.
     *
     *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
     *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
     *
     * @since    1.0.0
     */
    public function register_custom_post_type() {

        register_post_type('irojob', array(
            'labels' => array(
                'name'                => _x( 'Jobs', 'Post Type General Name', 'iro' ),
                'singular_name'       => _x( 'Job', 'Post Type Singular Name', 'iro' ),
                'menu_name'           => __( 'iRO Jobs', 'iro' ),
                'parent_item_colon'   => __( '', 'iro' ),
                'all_items'           => __( 'All Jobs', 'iro' ),
                'view_item'           => __( 'View Job', 'iro' ),
                'add_new_item'        => __( 'Add New Job Position', 'iro' ),
                'add_new'             => __( 'Add New', 'iro' ),
                'edit_item'           => __( 'Edit Job', 'iro' ),
                'update_item'         => __( 'Update Job', 'iro' ),
                'search_items'        => __( 'Search Jobs', 'iro' ),
                'not_found'           => __( 'Not found', 'iro' ),
                'not_found_in_trash'  => __( 'Not found in Trash', 'iro' ),
            ),
            'label'               => __( 'irojob', 'iro' ),
            'description'         => __( 'iRO Job Position', 'iro' ),

            // Frontend
            'has_archive'        => true,
            'public'             => false,
            'taxonomies'        => array(''),
            'publicly_queryable' => true,
            //'rewrite' => array('slug' => 'job'),

            // Admin
            'capability_type' => 'post',
            'menu_icon'     => 'dashicons-businessman',
            'menu_position' => 10,
            'query_var'     => true,
            'show_in_menu'  => false,
            'show_ui'       => false,
            'supports'      => array(
                'title',
                'job_id',
                'start_date',
                'position_name',
                'industry',
                'location',
                'contact',
                'contact_mail',
                'lang',
                'full_text',
                // Google
                'google_title',
                'google_desc',

                // Detail Daten
                "job_intro",
                "job_link",
                "job_description",
                "job_candidate",
                "job_resume",
                "job_features",
            ),
        ));
    }

    /**
     * NOTE:  Filters are points of execution in which WordPress modifies data
     *        before saving it or sending it to the browser.
     *
     *        Filters: http://codex.wordpress.org/Plugin_API#Filters
     *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
     *
     * @since    1.0.0
     */
    public function filter_method_name() {
        //
    }

}

function iro_plugin_flush_rules(){
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}
