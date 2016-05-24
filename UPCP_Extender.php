<?php

/*
  Plugin Name: UPCP Extender
  Plugin URI: https://github.com/Alinea-Interactive/UPCP-Extender
  Description: UPCP extender (http://www.etoilewebdesign.com/plugins/ultimate-product-catalog/)
  Version: 1.0.0
  Author: Alinea Interactive
  Author URI: http://www.alinea.co
  License: GPL V3
 */

class UPCP_Extender {
	private static $instance = null;
	private $plugin_path;
	private $plugin_url;
    	private $text_domain = '';

	/**
	 * Creates or returns an instance of this class.
	 */
	public static function get_instance() {
		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
	 */
	private function __construct() {
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );

		load_plugin_textdomain( $this->text_domain, false, $this->plugin_path . '\lang' );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_styles' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );

		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
        
        add_action( 'admin_menu', array($this, 'admin_menu') );
        
        //ajax
        add_action( 'wp_ajax_get_eUPCP_query1', array($this, 'get_eUPCP_query1') );

		$this->run_plugin();
	}

	public function get_plugin_url() {
		return $this->plugin_url;
	}

	public function get_plugin_path() {
		return $this->plugin_path;
	}

    /**
     * Place code that runs at plugin activation here.
     */
    public function activation() {

	}

    /**
     * Place code that runs at plugin deactivation here.
     */
    public function deactivation() {

	}

    /**
     * Enqueue and register admin JavaScript files here.
     */
    public function admin_register_scripts($hook) {
    
        if( 'toplevel_page_UPCP-extender' == $hook ) {

        wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js'); 
        }
	}

    /**
     * Enqueue and register admin CSS files here.
     */
    public function admin_register_styles($hook) {
    
        if( 'toplevel_page_UPCP-extender' == $hook ) {

            wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css');
            wp_enqueue_style('UPCP-admin', plugin_dir_url(__FILE__) . '/UPCP-extender.css');            
        }
	}
    
    /**
     * Enqueue and register JavaScript files here.
     */
    public function register_scripts() {

	}

    /**
     * Enqueue and register CSS files here.
     */
    public function register_styles() {

	}

    /**
     * Place code for your plugin's functionality here.
     */
    private function run_plugin() {

	}
    
    public function admin_menu() {
        add_menu_page('AwardBox UPCP Extender', 'UPCP Extender', 'manage_options', 'UPCP-extender', array($this, 'display_admin_page') );
    }
    
    public function display_admin_page() {
        require_once('admin_pages.php');
    }
    
    public function get_eUPCP_query1() {
        global $wpdb; // this is how you get access to the database

        //clean post data
        foreach( array('draw', 'start', 'length', 'search', 'order', 'columns') as $index => $param ) {

            $$param = isset( $_POST[$param] )
                      ? $_POST[$param]
                      : null;
        }
       
        $sql = <<< EOFSQL
select 
    count(*)

from 
    cert_UPCP_Catalogues as c

left join {$wpdb->prefix}UPCP_Catalogue_Items as ci on ci.Catalogue_ID = c.Catalogue_ID
left join {$wpdb->prefix}UPCP_Categories      as ca on ca.Category_ID  = ci.Category_ID
left join {$wpdb->prefix}UPCP_Items           as it on it.Category_ID  = ca.Category_ID
EOFSQL;

        $query_results = array( 
            'draw' => $draw,
            'recordsTotal' => $wpdb->get_var($sql)
        );
       
        //clean start
        $start = empty($start)
                 ? 0
                 : intval($start);
                 
        $admin_url = get_admin_url();
        $sql = <<< EOFSQL
select 
    concat( '<a href="$admin_url?page=UPCP-options&Action=UPCP_Catalogue_Details&Selected=Catalogue&Catalogue_ID=', c.Catalogue_ID, '">', c.Catalogue_Name, '</a>'),
    concat( '<a href="$admin_url?page=UPCP-options&Action=UPCP_Category_Details&Selected=Category&Category_ID=', ca.Category_ID, '">', ca.Category_Name, '</a>'),
    concat( '<a href="$admin_url?page=UPCP-options&Action=UPCP_Item_Details&Selected=Product&Item_ID=', it.Item_ID, '">', it.Item_Name, '</a>'),    
    concat('<img class="UPCP_Extender_product_image" src="', it.Item_Photo_URL, '">')
from 
    cert_UPCP_Catalogues as c

left join {$wpdb->prefix}UPCP_Catalogue_Items as ci on ci.Catalogue_ID = c.Catalogue_ID
left join {$wpdb->prefix}UPCP_Categories      as ca on ca.Category_ID  = ci.Category_ID
left join {$wpdb->prefix}UPCP_Items           as it on it.Category_ID  = ca.Category_ID
EOFSQL;

        if( $start || $length ) {
        
            $sql .= "\nLIMIT $start";
            
            if( $length ) {
                $sql .= ",$length";
            }             
        }
        
        $query_results['data'] = $wpdb->get_results($sql, ARRAY_N);         
        $query_results['recordsFiltered'] = count( $query_results['data'] );
        
        if( !empty($wpdb->last_error) ){
            $query_results['error'] = $wpdb->last_error;
        }
        
        echo json_encode($query_results);
        wp_die();
    }
}

UPCP_Extender::get_instance();
