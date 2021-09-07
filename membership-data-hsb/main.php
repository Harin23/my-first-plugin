<?php
/*
Plugin Name: Membership Data
Plugin URI: https://www.adralberta.com/
Description: Displays a table of the memberships summary
Author: HarinSBal
Version: 1.0
*/

add_action('admin_menu', "add_admin_menu_item_membership_summary_plugin_hsb");

function add_admin_menu_item_membership_summary_plugin_hsb(){
    $membership_dashboard_table_hsb=add_menu_page( 'Memberships Summary Table', 
    'Membership Data', 
    'manage_options', 
    'membership_summary_plugin', 
    "create_and_display_memberships_summary_table_hsb", 
    'dashicons-portfolio' 
    );
}

add_action('admin_enqueue_scripts', 'enqueue_style_hsb');
function enqueue_style_hsb(){
    wp_enqueue_style('tablehsb', plugins_url("/css/tablehsb.css", __FILE__));
}
 
function create_and_display_memberships_summary_table_hsb(){
    // echo 'hello';
    require_once(dirname(__FILE__)."/membershipDashboardTable.php");
    display_membership_summary_table_hsb();
}

?>