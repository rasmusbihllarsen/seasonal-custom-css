<?php
/**
 * Plugin Name: Seasonal Custom CSS
 * Plugin URI: http://www.rasmusbihllarsen.com
 * Description: This plugin lets you add Custom CSS to your site, at specific times a year. Do you want some Christmas-theme in December? No problem!
 * Version: 1.0.1
 * Author: Rasmus Bihl Larsen
 * Author URI: http://www.rasmusbihllarsen.com
 * License: GPL2
 *
 * Copyright 2015  rasmusbihllarsen  (email : hello@rasmusbihllarsen.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined( 'ABSPATH' ) or die();

register_activation_hook( __FILE__, 'create_scc_tables' );

function create_scc_tables() {
  global $wpdb;

  $table_name = $wpdb->base_prefix . 'seasonal_custom_css';
  
  $sql = "CREATE TABLE $table_name (
      id int(11) NOT NULL AUTO_INCREMENT,
      name varchar(255) DEFAULT NULL,
      custom_css text DEFAULT NULL,
      from_date date DEFAULT NULL,
      to_date date DEFAULT NULL,
      UNIQUE KEY id (id)
    );";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  
  $wpdb->insert( 
      $table_name, 
      array( 
        'name' => 'My Custom CSS', 
        'custom_css' => '.my-class{background:red;color:blue!important;}', 
        'from_date' => current_time( 'mysql' ), 
        'to_date' => current_time( 'mysql' ), 
      ) 
  );
}

function seasonal_custom_admin_menu() {
	add_options_page( 'Seasonal CSS', 'Seasonal CSS', 'manage_options', 'seasonal-custom-css', 'scc_admin_options' );
}
add_action( 'admin_menu', 'seasonal_custom_admin_menu' );

function scc_admin_options() {
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
  
  include('includes/admin-view.php');
}

function add_seasonal_css() {
  global $wpdb;
  
  $css_results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->base_prefix . 'seasonal_custom_css' );
  
  $sc_css = "";
  
  foreach($css_results as $css){
    $today_date = date("md");
    
    if($today_date <= date("md", strtotime($css->to_date)) && $today_date >= date("md", strtotime($css->from_date))){
      //$custom = str_replace(";", "!important;", $css->custom_css);
      //$custom = str_replace(array("!important!important;", "!important !important;"), "!important;", $custom);

      $sc_css .= strip_tags($css->custom_css);
    }
  }

  echo '<!-- Seasonal CSS -->
  <style>
  '.$sc_css.'
  </style>';
}
add_filter('wp_head', 'add_seasonal_css');

include_once('updater.php');
if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
	$config = array(
		'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
		'proper_folder_name' => 'rbl-seasonal-custom-css', // this is the name of the folder your plugin lives in
		'api_url' => 'https://api.github.com/repos/rasmusbihllarsen/rbl-seasonal-custom-css', // the GitHub API url of your GitHub repo
		'raw_url' => 'https://raw.github.com/rasmusbihllarsen/rbl-seasonal-custom-css/master', // the GitHub raw url of your GitHub repo
		'github_url' => 'https://github.com/rasmusbihllarsen/rbl-seasonal-custom-css', // the GitHub url of your GitHub repo
		'zip_url' => 'https://github.com/rasmusbihllarsen/rbl-seasonal-custom-css/zipball/master', // the zip url of the GitHub repo
		'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
		'requires' => '3.0', // which version of WordPress does your plugin require?
		'tested' => '3.3', // which version of WordPress is your plugin tested up to?
		'readme' => 'README.md', // which file to use as the readme for the version number
		'access_token' => '', // Access private repositories by authorizing under Appearance > GitHub Updates when this example plugin is installed
	);
	new WP_GitHub_Updater($config);
}
?>
