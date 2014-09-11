<?php
/*
Plugin Name: HANDYBAR
Plugin URI: http://www.handypress.io
Description: A very small plugin tips to display the Wordpress Admin Bar at the top of the scrolling page (not fixed)
Version: 0.1.0
Author: Yannick Armspach
Author Email: Yannick Armspach <yannick.armspach@gmail.com>
*/

/**
*
* CONSTANT VAR
*
**/
define( 'HANDYBAR_FILE', __FILE__ );
define( 'HANDYBAR_DIR', plugin_dir_path( HANDYBAR_FILE ) );
define( 'HANDYBAR_URL', plugin_dir_url( HANDYBAR_FILE ) );


/**
*
* HANDYBAR CLASS
*
**/

class HANDYBAR {

/**
*
* CONSTRUCT
*
* @desc wordpress action and filter
*
**/
function __construct() {

	//Init plugin
	add_action( 'init', array( $this, 'update' ), 999999 );

	//Init plugin
	add_action( 'init', array( $this, 'init' ), 999999 );

	//textdomain
	add_action( 'init', array( $this, 'textDomain' ), 999999 );

	//activation
	register_activation_hook( HANDYBAR_FILE, array( $this, 'activate' ) );

	//deactivate
	register_deactivation_hook( HANDYBAR_FILE, array( $this, 'deactivate' ) );

	//uninstall
	register_uninstall_hook( HANDYBAR_FILE, array( $this, 'uninstall' ) );

}

/**
*
* INIT
*
* @desc Init
*
**/
public function init(){

	add_action( 'admin_footer', array( $this, 'adminbar' ), 99999999999999 );

}

public function adminbar() {

	wp_enqueue_style( 'HANDYPRESS_HANDYBAR', HANDYBAR_URL . '/css/HANDYBAR.css', false, false, 'screen' );

}


/**
**
** TEXT DOMAINE
**
** Set language
**
*/
public function textDomain() {

	$domain = 'HANDYBAR';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

  load_textdomain( $domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo' );

  load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( HANDYBAR_FILE ) ) . '/lang/' );

}


/**
**
** ACTIVATE
**
** @desc Check Wordpress version on plugin activation
**
*/
public function activate( $network_wide ) {

	if ( version_compare( get_bloginfo( 'version' ), '3.0', '<' ) ) {

   	 	deactivate_plugins( HANDYBAR_FILE  );

    	wp_die( __('WordPress 3.0 and higher required. The plugin has now disabled itself. Upgrade!','HANDYBAR') );

	}

}

/**
*
* UPDATE
*
*
**/
public function update( ){

	$plugin_version = '0.1.0';

	if( get_option( 'HANDYBAR_plugin_version' ) !== $plugin_version ) {


		update_option( 'HANDYBAR_plugin_version', $plugin_version );

	}

}

/**
*
* DESACTIVATE PLUGIN
*
**/
public function deactivate( $network_wide ) {

}

/**
*
* UNINSTALL PLUGIN
*
**/
public function uninstall( $network_wide ) {

}


}

$HANDYBAR = new HANDYBAR();

?>
