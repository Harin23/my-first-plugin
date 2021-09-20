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
    'export_csv_submenuslug_hsb', 
    'export_membership_data_submenu_page_hsb'
    );
}

add_action('admin_enqueue_scripts', 'enqueue_style_table_page_hsb');
function enqueue_style_table_page_hsb($hook){
    if('toplevel_page_membership_summary_plugin_hsb' === $hook){
        wp_enqueue_style('table_styles_hsb', plugins_url("/css/tablehsb.css", __FILE__));
    }
}

add_action('admin_enqueue_scripts', 'enqueue_script_export_submenu_hsb');
function enqueue_script_export_submenu_hsb($hook){
    if('membership-data_page_export_csv_submenuslug_hsb' === $hook){
        wp_enqueue_script('quarterButtons_jsfile_hsb', plugins_url("js/quarterButtonshsb.js", __FILE__));
        wp_enqueue_style('export_styles_hsb', plugins_url("/css/exporthsb.css", __FILE__));
    }
}
 
function create_and_display_memberships_summary_table_hsb(){
    display_membership_summary_table_hsb();
}

function export_membership_data_submenu_page_hsb() {
    render_export_submenu_page_html_hsb();
}

add_action('admin_init','membership_data_download_csv_hsb');

?>