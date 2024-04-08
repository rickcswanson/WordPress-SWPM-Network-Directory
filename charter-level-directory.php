<?php
/*
 Plugin Name: Global Network Level Directory
 Description: A membership directory that pulls all members created by the Simple Membership Plugin across all subdomains in a multisite network and displays them in a list including their attributes.
 Version: 1.0
 Author: Rick Swanson | Digital Dialect, Inc.
 */

// Add shortcode for displaying the directory
add_shortcode('global_charter_directory', 'display_global_charter_directory');

function display_global_charter_directory($atts) {
    global $wpdb;
    
    // Get prefix for the main site tables
    $table_prefix = $wpdb->get_blog_prefix(1); // Main site's ID is always 1
    
    // Query all subdomains
    $subdomains = $wpdb->get_results("SELECT blog_id, domain FROM {$wpdb->blogs}", ARRAY_A);

    $all_members = array();

    // Loop through each subdomain
    foreach ($subdomains as $subdomain) {
        $blog_id = $subdomain['blog_id'];
        $domain = $subdomain['domain'];

        // Switch to subdomain's database
        switch_to_blog($blog_id);

        // Get prefix for the current subdomain's tables
        $subdomain_table_prefix = $wpdb->get_blog_prefix($blog_id);

        // Construct the table name for the Simple Membership Plugin
        $members_table_name = $subdomain_table_prefix . 'swpm_members_tbl';

        // Query the database to get the members for the current subdomain
        $members_query = "SELECT * FROM $members_table_name";
        $members = $wpdb->get_results($members_query);

        // Check for database errors
        if (!$members && $wpdb->last_error) {
            return 'Error retrieving members for ' . $domain . ': ' . $wpdb->last_error;
        }

        // Loop through each member to retrieve custom data
        // THIS IS CUSTOM DATA THAT USES THE FORM BUILDER ADD ON FOR SIMPLE MEMBERSHIP PLUGIN. THE FIELD IDS USED ARE DEPENDENT ON HOW THE INFORMATION IS STORED IN THE RESEPCTIVE DATABASES
        foreach ($members as $member) {
            // Get custom data for the current member
            $phone_type = get_custom_data($member->member_id, 61);
            $employee_status = get_custom_data($member->member_id, 92);
            $badge_number = get_custom_data($member->member_id, 83);

            // Add custom data to the member object
            $member->phone_type = !empty($phone_type) ? $phone_type : '------';
            $member->employee_status = !empty($employee_status) ? $employee_status : '------';
            $member->badge_number = !empty($badge_number) ? $badge_number : '------';

            // Merge current subdomain's member with all members
            $all_members[] = $member;
        }

        // Restore the main site's database
        restore_current_blog();
    }

    // Load HTML template
    ob_start();
    include(plugin_dir_path(__FILE__) . 'global-network-level-directory-template.php');
    $output = ob_get_clean();
    
    return $output;
}

function get_custom_data($user_id, $field_id) {
    global $wpdb;
    $custom_table_name = $wpdb->prefix . 'swpm_form_builder_custom'; // Replace with your custom table name

    $custom_data_query = $wpdb->prepare("
        SELECT value
        FROM $custom_table_name
        WHERE user_id = %d AND field_id = %d
    ", $user_id, $field_id);

    $custom_data = $wpdb->get_var($custom_data_query);
    return !empty($custom_data) ? $custom_data : '------';
}

function get_all_membership_level_names($level) {
    // Define a mapping of numeric values to membership level names
    $membership_levels = array(
        1 => 'Membership Level',
        2 => 'Membership Level'
        // Add more mappings as needed
    );

    // Check if the given level exists in the mapping
    if (isset($membership_levels[$level])) {
        return $membership_levels[$level];
    } else {
        return 'Unknown'; // Default value if level is not found
    }
}
?>
