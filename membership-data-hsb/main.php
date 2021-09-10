<?php
/*
Plugin Name: Membership Data
Plugin URI: https://www.adralberta.com/
Description: Displays a table of the memberships summary
Author: HarinSBal
Version: 1.0
*/

require_once(dirname(__FILE__)."/membershipDashboardTable.php");
require_once(dirname(__FILE__)."/exportSubmenuPage.php");

add_action('admin_menu', "add_admin_menu_item_membership_summary_plugin_hsb");

function add_admin_menu_item_membership_summary_plugin_hsb(){
    add_menu_page( 'Memberships Summary Table', //page title
    'Membership Data', //menu title
    'manage_options', //capability
    'membership_summary_plugin_hsb', //parent slug
    'create_and_display_memberships_summary_table_hsb', //callback
    'dashicons-portfolio' //icon
    );
    add_submenu_page( 'membership_summary_plugin_hsb', //parent slug
    'Memberships Summary Table', //page title
    'Membership Data', //menu title
    'manage_options', //capability
    'membership_summary_plugin_hsb', //submenu slug
    'create_and_display_memberships_summary_table_hsb' //callback
    );
    add_submenu_page( 'membership_summary_plugin_hsb', 
    'Export membership data',
    'Export Membership Data', 
    'manage_options', 
    'export_csv_hsb', 
    'export_membership_data_submenu_page_hsb'
    );

}

add_action('admin_enqueue_scripts', 'enqueue_style_hsb');
function enqueue_style_hsb(){
    wp_enqueue_style('tablehsb', plugins_url("/css/tablehsb.css", __FILE__));
}
 
function create_and_display_memberships_summary_table_hsb(){
    // echo 'hello';
    display_membership_summary_table_hsb();
}

//export data
function export_membership_data_submenu_page_hsb() {
    render_export_submenu_page_html_hsb();
}

add_action('admin_init','membership_data_download_csv_hsb');
?>