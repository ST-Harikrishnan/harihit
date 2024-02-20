<?php
/*
Plugin Name: Merge Users
Description: Plugin to merge user accounts.
Version: 1.0
Author: Bart WWU
*/

// Activation hook
register_activation_hook(__FILE__, 'merge_users_activation');

function merge_users_activation()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Create the main table for merging users
    $table_name = $wpdb->prefix . 'merge_details';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        prev_username varchar(255) NOT NULL,
        current_username varchar(255) NOT NULL,
        merge_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Create the meta table to store additional details
    $meta_table_name = $wpdb->prefix . 'merge_details_meta';
    $meta_sql = "CREATE TABLE IF NOT EXISTS $meta_table_name (
        metaid mediumint(9) NOT NULL AUTO_INCREMENT,
        prev_username varchar(255) NOT NULL,
        current_username varchar(255) NOT NULL,
        prev_course_count int NOT NULL,
        prev_certificate_count int NOT NULL,
        prev_group_count int NOT NULL,
        current_course_count int NOT NULL,
        current_certificate_count int NOT NULL,
        current_group_count int NOT NULL,
        PRIMARY KEY  (metaid)
    ) $charset_collate;";
    dbDelta($meta_sql);
}


// Deactivation hook
register_deactivation_hook(__FILE__, 'merge_users_deactivation');

function merge_users_deactivation()
{
    // Deactivation code, if needed
}

function add_dashboard_icon()
{
    add_menu_page(
        'User Merge ',
        'User Merger ',
        'manage_options',
        'user-merger-plugin',
        'merge_menu_page',
        'dashicons-groups',
        20
    );

    add_submenu_page(
        'user-merger-plugin', // Parent slug
        'Deleted Users', // Page title
        'Deleted Users', // Menu title
        'manage_options', // Capability
        'deleted-users-page', // Menu slug
        'deleted_users_page_content' // Callback function
    );
}
add_action('admin_menu', 'add_dashboard_icon');



function enqueue_merge_users_scripts_and_styles() {
    wp_enqueue_script('jquery');

    wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), false, true);

    wp_enqueue_style('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css');
    wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@10', array(), false, true);

    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array('jquery'), false, true);

    wp_enqueue_style('merge-users-custom-css', plugin_dir_url(__FILE__) . 'mergestyle.css');
}

add_action('admin_enqueue_scripts', 'enqueue_merge_users_scripts_and_styles');

include( plugin_dir_path( __FILE__ ) . 'user-merger.php');
include( plugin_dir_path( __FILE__ ) . 'delete-users.php');