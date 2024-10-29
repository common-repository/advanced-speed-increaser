<?php
/*
Plugin Name: Advanced Speed Increaser
Plugin URI: https://wordpress.org/plugins/advanced-speed-increaser/
Description: Advanced Speed Increaser is a light weighted plugin to speed up wordpress website by using GZIP compression and setting header expiration for images. Including Database cleanup and optimization tool to make your wordpress website lighter and faster.
Version: 2.2.1
Author: Animesh
Author URI: http://www.thelogicalcoder.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


*/
define( 'TWASI_VERSION', '2.2.1' );
define( 'TWASI_PLUGIN', __FILE__ );
define( 'TWASI_PLUGIN_BASENAME', plugin_basename( TWASI_PLUGIN ) );

// Register the activation hook to install
register_activation_hook( __FILE__, 'tw_asi_install' );
register_deactivation_hook( __FILE__, 'tw_asi_uninstall' );

add_action('admin_menu', 'tw_asi_menu');

function tw_asi_menu() {
	add_menu_page( 'Advanced Speed Increaser', 'Advanced Speed Increaser', 'administrator', 'tw-asi', 'tw_asi_html_page', plugins_url('icon20x20.png',__FILE__ ) );
}

function tw_asi_html_page() {
	global $wpdb;
	include('admin-setting.php');
}

if( !function_exists( 'tw_asi_install' ) ) {
	function tw_asi_install() {
		$status = true;
		$status = tw_asi_write_file(ABSPATH . '.htaccess');
		if($status) {
			update_option( 'tw_asi_status', 1);
		} else {
			update_option( 'tw_asi_status', 0);
		}
		return $status;
	}
	function tw_asi_uninstall() {
		$status = tw_asi_erase_file(ABSPATH . '.htaccess');
		if($status) {
			update_option( 'tw_asi_status', 0);
		} else {
			update_option( 'tw_asi_status', 1);
		}
		return $status;
	}
	function tw_asi_write_file( $file_path ){
		$status = true;
		$insertion = '<IfModule mod_deflate.c>
AddOutputFilterByType DEFLATE text/plain
AddOutputFilterByType DEFLATE text/html
AddOutputFilterByType DEFLATE text/xml
AddOutputFilterByType DEFLATE text/css
AddOutputFilterByType DEFLATE application/xml
AddOutputFilterByType DEFLATE application/xhtml+xml
AddOutputFilterByType DEFLATE application/rss+xml
AddOutputFilterByType DEFLATE application/javascript
AddOutputFilterByType DEFLATE application/x-javascript
AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
AddOutputFilterByType DEFLATE application/x-font-opentype
AddOutputFilterByType DEFLATE application/x-font-otf
AddOutputFilterByType DEFLATE application/x-font-truetype
AddOutputFilterByType DEFLATE application/x-font-ttf
AddOutputFilterByType DEFLATE font/opentype
AddOutputFilterByType DEFLATE font/otf
AddOutputFilterByType DEFLATE font/ttf
AddOutputFilterByType DEFLATE image/svg+xml
AddOutputFilterByType DEFLATE image/x-icon
# Remove browser bugs (only needed for really old browsers)
BrowserMatch ^Mozilla/4 gzip-only-text/html
BrowserMatch ^Mozilla/4\.0[678] no-gzip
BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
Header append Vary User-Agent
</IfModule>
#Header Expire
<IfModule mod_expires.c>
ExpiresActive On
ExpiresDefault A0
ExpiresByType image/gif A2592000
ExpiresByType image/png A2592000
ExpiresByType image/jpg A2592000
ExpiresByType image/jpeg A2592000
ExpiresByType image/ico A2592000
ExpiresByType text/css A2592000
ExpiresByType text/javascript A2592000
ExpiresByType application/javascript A2592000
#Turn Off ETags
Header unset ETag
FileETag None
</IfModule>
';
		insert_with_markers( $file_path, "Advanced Speed Increaser", $insertion );
		return $status;
	}
	function tw_asi_erase_file($filename){
		$status = true;
		extract_from_markers( $filename, "Advanced Speed Increaser" );
		return $status;
	}
}

// Add settings link on plugin page
function tw_asi_settings_link ($links) { 
	$settings_link = '<a href="admin.php?page=tw-asi">'.__('Settings', 'tw_asi-settings').'</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 
}
$tw_asi_plugin = TWASI_PLUGIN_BASENAME; 
add_filter("plugin_action_links_$tw_asi_plugin", 'tw_asi_settings_link' );


function tw_asi_getSizes($size){
	$size_r = '';
	if($size==0){
		$size_r = "0";
	}
	else if($size<1024){
		$size_r = $size. ' B';
	}
	else if($size>=1024 && $size<(1024*1024)){
		$size_r = round($size/(1024),2). ' KB';
	}
	else{
		$size_r = round($size/(1024*1024),2). ' MB';
	}
	return $size_r;
}

function tw_asi_table_optimize($tables){
	global $wpdb;
	$status = true;
	$sql_opt = "OPTIMIZE TABLE ".$tables;
	$wpdb->query($sql_opt);
	return $status;
}

function tw_asi_table_remove($type){
	global $wpdb;
	$status = true;
	if($type==1){
		$sql = "DELETE FROM `$wpdb->posts` WHERE post_type = 'revision'";
		$wpdb->query( $sql );
	}
	else if($type==2){
		$sql = "DELETE FROM `$wpdb->posts` WHERE post_status = 'auto-draft'";
		$wpdb->query( $sql );
	}
	else if($type==3){
		$sql = "DELETE FROM `$wpdb->posts` WHERE post_status = 'trash'";
		$wpdb->query( $sql );
		$sql = "DELETE asi FROM  `$wpdb->postmeta`  asi LEFT JOIN  `$wpdb->posts`  wp ON wp.ID = asi.post_id WHERE wp.ID IS NULL";
		$wpdb->query( $sql );
	}
	else if($type==4){
		$sql = "DELETE FROM `$wpdb->comments` WHERE comment_approved = 'spam'";
		$wpdb->query( $sql );
	}
	else if($type==5){
		$sql = "DELETE FROM `$wpdb->comments` WHERE comment_approved = 'trash'";
		$wpdb->query( $sql );
	}
	else if($type==6){
		$sql = "DELETE FROM `$wpdb->options` WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'";
		$wpdb->query( $sql );
	}
	
	return $status;
}

function tw_asi_minify_html ($buffer) {
	$initial = strlen($buffer);
	$tw_asi_html1 = get_option( 'tw_asi_html1', 0 );

	if($tw_asi_html1==1){	
		$search = array(
			'/\>[^\S ]+/s',  // strip whitespaces after tags, except space
			'/[^\S ]+\</s',  // strip whitespaces before tags, except space
			'/(\s)+/s'       // shorten multiple whitespace sequences
		);
	
		$replace = array(
			'>',
			'<',
			'\\1'
		);
				  
		$buffer = preg_replace($search, $replace, $buffer);
	}
	$final = strlen($buffer);
	$savings = round((($initial-$final)/$initial*100), 3);
	
	if ($savings != 0) {
			$buffer .= PHP_EOL . '<!--' . PHP_EOL . 'Minify HTML By, Advanced Speed Increaser '. TWASI_VERSION .' - https://wordpress.org/plugins/advanced-speed-increaser/' . PHP_EOL . 'Total size saved: ' . $savings . '% | Size before compression: ' . $initial . ' bytes | Size after compression: ' . $final . ' bytes. ***' . PHP_EOL . '-->';
		}
	
	return $buffer;
}

function tw_asi_minify() {
    ob_start('tw_asi_minify_html');
}
add_action('get_header', 'tw_asi_minify');

function tw_asi_admin_register_head() {
    $siteurl = get_option('siteurl');
    $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/assets/css/admin.css';
    echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
}
add_action('admin_head', 'tw_asi_admin_register_head');

function tw_asi_admin_message() {
	$tw_asi_msg = get_option( 'tw_asi_msg', 1 );
	if($tw_asi_msg!=1){	
		echo '<div id="tw_asi_admin_message" class="notice-warning settings-error notice is-dismissible">
		<p><strong>Checkout our new features <a href="admin.php?page=tw-asi&first=1">Advanced Speed Increaser</a></strong> </p>
		</div>';
	}
}

// Now we set that function up to execute when the admin_notices action is called
add_action( 'admin_notices', 'tw_asi_admin_message' );


