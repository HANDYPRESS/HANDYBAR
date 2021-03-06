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
* REQUIRE CLASS
*
**/
if ( !class_exists('HANDYLOG')) {
require_once( HANDYBAR_DIR . 'inc/simple_html_dom.php' );
}


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
	add_action( 'init', array( $this, 'update' ) );

	//adminbar admin style
	add_action( 'admin_enqueue_scripts', array( $this, 'adminbar_admin' ), 999999 );

	//adminbar site style
	add_action( 'wp_enqueue_scripts', array( $this, 'adminbar_site' ), 999999 );

	//ob start
	add_action( 'admin_head', array( $this, 'head_ob_start') );

	//ob end
	add_action( 'admin_footer', array( $this, 'footer_ob_end') );

	//Save profile
	add_action('personal_options_update', array( $this, 'update_extra_profile_fields' ) );
	add_action('edit_user_profile_update', array( $this, 'update_extra_profile_fields' ) );

	//remove body class
	add_filter( 'body_class', array( $this, 'body_class') );

	//textdomain
	add_action( 'init', array( $this, 'textDomain' ) );

	//activation
	register_activation_hook( HANDYBAR_FILE, array( $this, 'activate' ) );

	//deactivate
	register_deactivation_hook( HANDYBAR_FILE, array( $this, 'deactivate' ) );

	//uninstall
	register_uninstall_hook( HANDYBAR_FILE, array( $this, 'uninstall' ) );

}

/**
*
* body_class
*
* @desc remove front end body class 'admin-bar' to prevent extra admin bar style from theme (e.g. style.css)
*
**/
public function body_class( $wp_classes ) {

	global $current_user;

	if ( is_admin_bar_showing() && get_user_meta( $current_user->ID, 'toolbar_tiny', true ) == "checked" ) {

		$arr_id = array_search('admin-bar',$wp_classes);

		unset( $wp_classes[$arr_id] );

		$wp_classes[] = 'toolbar-' . get_user_meta($current_user->ID, 'toolbar_pos',true);

	}

	return $wp_classes;

}

/**
*
* adminbar_admin
*
* @desc
*
**/
public function adminbar_admin() {

	if ( is_admin_bar_showing() ) {
		wp_enqueue_style( 'HANDYPRESS_HANDYBAR_ADMIN', HANDYBAR_URL . 'css/HANDYBAR-ADMIN.css', array( 'admin-bar' ), false, 'all' );
	}

}

/**
*
* adminbar_site
*
* @desc
*
**/
public function adminbar_site() {

	global $current_user;

	if ( is_admin_bar_showing() && get_user_meta( $current_user->ID, 'toolbar_tiny', true ) == "checked" ) {

		wp_enqueue_style( 'HANDYPRESS_HANDYBAR_SITE', HANDYBAR_URL . 'css/HANDYBAR-SITE.css', array( 'admin-bar' ), false, 'all' );

	}

}

/**
*
* head_ob_start
*
* @desc
*
**/
public function head_ob_start() {

  global $pagenow;

  if ( $pagenow == 'profile.php' ){
			ob_start( array( $this, 'add_personal_options' ) );
	}

}

/**
*
* add_personal_options
*
* @desc
*
**/
public function add_personal_options( $subject ) {

	global $pagenow;

	if ( isset($subject) && $pagenow == 'profile.php' ) {

		global $current_user;

		$subject = str_get_html($subject);

		$toolbar_tiny = get_user_meta( $current_user->ID, 'toolbar_tiny', true );

		$toolbar_pos = get_user_meta( $current_user->ID, 'toolbar_pos', true );

		$toolbar_pos_topLeft = '';
		$toolbar_pos_topRight = '';
		$toolbar_pos_bottomLeft = '';
		$toolbar_pos_bottomRight = '';
		$toolbar_pos_default = '';

		switch ($toolbar_pos) {
			case 'topLeft':
				$toolbar_pos_topLeft = 'checked';
			break;
			case 'topRight':
				$toolbar_pos_topRight = "checked";
			break;
			case 'bottomLeft':
				$toolbar_pos_bottomLeft = "checked";
			break;
			case 'bottomRight':
				$toolbar_pos_bottomRight = "checked";
			break;
			default:
				$toolbar_pos_default = "checked";
			break;
		}

		$row = '';

		$row .= '<tr>';

			$row .= '<th scope="row">Toolbar Tiny</th>';

				$row .= '<td>';
					$row .= '<label for="toolbar_tiny"><input name="toolbar_tiny" id="toolbar_tiny" type="checkbox" '.$toolbar_tiny.'> Enable the tiny adminbar</label>';
				$row .= '</td>';

		$row .= '</tr>';

		$row .= '<tr>';

			$row .= '<th scope="row">Toolbar Position (frontend)</th>';

			$row .= '<td>';
				$row .= '<input type="radio" name="toolbar_pos" value="default" '.$toolbar_pos_default.' style="margin-left:0px;">Default</input>';
				$row .= '<input type="radio" name="toolbar_pos" value="topLeft" '.$toolbar_pos_topLeft.' style="margin-left:10px;">Top Left</input>';
				$row .= '<input type="radio" name="toolbar_pos" value="topRight" '.$toolbar_pos_topRight.' style="margin-left:10px;">Top Right</input>';
				//$row .= '<input type="radio" name="toolbar_pos" value="bottomLeft" '.$toolbar_pos_bottomLeft.' style="margin-left:10px;">Bottom Left</input>';
				//$row .= '<input type="radio" name="toolbar_pos" value="bottomRight" '.$toolbar_pos_bottomRight.' style="margin-left:10px;">Bottom Right</input>';
			$row .= '</td>';

		$row .= '</tr>';

		$subject->find('#admin_bar_front', 0)->parent->parent->parent->parent->innertext = $subject->find('#admin_bar_front', 0)->parent->parent->parent->parent->innertext . $row;

	}

	return $subject;

}

/**
*
* footer_ob_end
*
* @desc
*
**/
public function footer_ob_end() {

	global $pagenow;

	if ( $pagenow == 'profile.php' ){
		ob_end_flush();
	}

}

/**
*
* update_extra_profile_fields
*
* @desc
*
**/
public function update_extra_profile_fields( $user_id ) {

	if ( current_user_can('edit_user',$user_id) ){

		if ( $_POST['toolbar_tiny'] ){
			$toolbar_tiny = "checked";
		}else{
			$toolbar_tiny = "";
		}

		update_user_meta( $user_id, 'toolbar_tiny', $toolbar_tiny );

		update_user_meta( $user_id, 'toolbar_pos', $_POST['toolbar_pos'] );

	}

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
public function update(){

	global $current_user;

	$plugin_version = '0.1.1';

	if( get_option( 'HANDYBAR_plugin_version' ) !== $plugin_version ) {

		add_user_meta( $current_user->ID, 'toolbar_tiny', "checked" );
		add_user_meta( $current_user->ID, 'toolbar_pos', "checked" );

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
