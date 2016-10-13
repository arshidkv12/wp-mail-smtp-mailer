<?php
/*
Plugin Name: WP Mail Smtp Mailer
Plugin URI: http://www.htmlcontactform.net/
Description: Reconfigures the wp_mail() function to use SMTP instead of mail() function and add password encryption.
Text Domain: wp-mail-smtp-mailer
Version: 1.0.0
Author: Arshid
Author URI: http://www.htmlcontactform.net/
*/


require_once 'encryption.class.php';

//Plugin activation 
function WPMS_plugin_activate() {

	$dir_path   = plugin_dir_path( __FILE__ );
	$encryption = new WPMS_encryption($dir_path);

	$encryption->generate_salt();
    
    add_option( 'WPMS_mail_data','' , '', 'yes' );

}
register_activation_hook( __FILE__, 'WPMS_plugin_activate' );

//Plugin deactivation 
function WPMS_plugin_deactivation() {
	
	$dir_path   = plugin_dir_path( __FILE__ );
	$encryption = new WPMS_encryption($dir_path);

	$encryption->delete_salt();

	delete_option( 'WPMS_mail_data' );
	delete_option( 'WPMS_mail_flag' );
	 
}
register_deactivation_hook( __FILE__, 'WPMS_plugin_deactivation' );


 
// Add settings link on plugin page
function WPMS_settings_link($links) { 
  $settings_link = "<a href='options-general.php?page=wp-mail-smtp-mailer'>".__('Settings','wp-mail-smtp-mailer')."</a>"; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'WPMS_settings_link' );

//plugin load
function WPMS_plugin_load(){

	load_plugin_textdomain( 'wp-mail-smtp-mailer', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' ); 
	
	require_once 'WPMS_settings.class.php';
	
	$dir_path  		 = plugin_dir_path( __FILE__ );

	$WPMS_settings   = new WPMS_settings($dir_path);
	
}
add_action('plugins_loaded', 'WPMS_plugin_load');




add_action( 'phpmailer_init', 'WPMS_php_mailer' );
function WPMS_php_mailer( $phpmailer ) {

	$option = get_option('WPMS_mail_data','');

	if ($option['encrypt'] == '1'){

		$dir_path    = plugin_dir_path( __FILE__ );

		require_once $dir_path. '/salt.php';

		$encryption  = new WPMS_encryption($dir_path);

		$option['host'] 	= $encryption->data_decrypt($option['host'], $WPMS_salt);
		$option['username'] = $encryption->data_decrypt($option['username'], $WPMS_salt);
		$option['password']	= $encryption->data_decrypt($option['password'], $WPMS_salt);
	}

    $phpmailer->isSMTP();     
    $phpmailer->Host = $option['host'];
    $phpmailer->SMTPAuth = true; // Force it to use Username and Password to authenticate
    $phpmailer->Port = $option['port'];
    $phpmailer->Username = $option['username'];
    $phpmailer->Password = $option['password'];

    // Additional settings…
    if( $option['SMTPSecure'] != 'none' ){

     $phpmailer->SMTPSecure = $option['SMTPSecure'];// Choose SSL or TLS, if necessary for your server
    } 
    if( $option['From'] != '' ){

    	$phpmailer->From = $option['From'];
    }
    if( $option['FromName'] != '' ){
    	
    	$phpmailer->FromName = $option['FromName'];
    }
    
     
}