<?php
/*
Plugin Name: Star Collector Review Widgets
Description: Showcase best review ratings widget from top auto-curated review platforms: Google reviews, Facebook, Yelp & 397+ other review platforms for any niche
Version: 1.4
Author: <a href="https://www.reviewise.co" target="_blank">RevieWise.co</a>
Author URI: https://www.reviewise.co/
Plugin URI: https://www.reviewise.co/
Text Domain: star-collector-review-widgets
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Include the helper functions
// Activation Hook
register_activation_hook(__FILE__, 'SCRPlugin_your_plugin_activate');

// Deactivation Hook
register_deactivation_hook(__FILE__, 'SCRPlugin_your_plugin_deactivate');
require_once(plugin_dir_path(__FILE__) . 'helperFunctions.php');

//------------------API token table Creation/Delete-------------------------


// Hook for creating the tables on plugin activation
register_activation_hook(__FILE__, 'SCRPlugin_activate');

// Function to create the tables on activation
function SCRPlugin_activate() {
     if (get_option('SCRPlugin_activation_completed') !== '1') {
    global $wpdb;

    $table_name_tokens = $wpdb->prefix . 'SCRPlugin_api_settings';
    $table_name_searches = $wpdb->prefix . 'SCRPlugin_brand_settings';
    $table_name_display_settings = $wpdb->prefix . 'SCRPlugin_widget_display_settings';
    $table_name_filter_settings = $wpdb->prefix . 'SCRPlugin_widget_filter_settings';
    $table_name_trustworthiness_settings = $wpdb->prefix . 'SCRPlugin_trustworthiness_settings';
    $table_name_ratings = $wpdb->prefix . 'SCRPlugin_ratings';


    $charset_collate = $wpdb->get_charset_collate();

    // Create start_collector_api table
    $sql_tokens = "CREATE TABLE $table_name_tokens (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        api text NOT NULL,
        test varchar(255) DEFAULT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Create start_collector_brand table
    $sql_searches = "CREATE TABLE $table_name_searches (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        brand_name varchar(255) NOT NULL,
        extra_brand_identifiers varchar(255) NOT NULL,
        extra_review_platforms tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
   // Create start_collector_widget_display_settings table with default values
    $sql_display_settings = "CREATE TABLE $table_name_display_settings (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        display_position int NOT NULL DEFAULT 3 COMMENT '1: middle left, 2: middle right, 3: bottom left, 4: bottom right',
        display_mode int NOT NULL DEFAULT 1 COMMENT '1: light colored, 2: dark colored',
        display_style int NOT NULL DEFAULT 1 COMMENT '1: A, 2: B, 3: C, 4: D, 5: E, 6: F, G',
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    // Create start_collector_widget_filter_settings table
    $sql_filter_settings = "CREATE TABLE $table_name_filter_settings (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        min_rating_1_5 decimal(3,1) NOT NULL DEFAULT 3.5,
        min_rating_1_10 decimal(3,1) NOT NULL DEFAULT 6.5,
        min_reviews int NOT NULL DEFAULT 5,
        all_posts tinyint(1) DEFAULT 1 COMMENT '0: No, 1: Yes',
        all_pages tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: No, 1: Yes',
        specific_url_checkbox tinyint(1) DEFAULT NULL COMMENT '0: No, 1: Yes',
        specific_url longtext DEFAULT NULL,
        exclude_review_platform_domains longtext DEFAULT NULL,
        exclude_admin_domains longtext DEFAULT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Create start_collector_trustworthiness_settings table
    $sql_trustworthiness_settings = "CREATE TABLE $table_name_trustworthiness_settings (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        reviewise_badge tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: badge hide, 1: badge show',
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    // Create start_collector_ratings table
       $sql_ratings = "CREATE TABLE $table_name_ratings (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        rating varchar(255) NOT NULL,
        rating_out_of int DEFAULT NULL,
        review text NOT NULL,
        source varchar(255) NOT NULL,
        favicon longtext DEFAULT NULL,
        brand_id mediumint(9) NOT NULL,
        status tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: Unchecked, 1: checked',
        PRIMARY KEY  (id),
        FOREIGN KEY (brand_id) REFERENCES {$wpdb->prefix}SCRPlugin_brand_settings(id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_tokens);
    dbDelta($sql_searches);
    dbDelta($sql_display_settings);
    dbDelta($sql_filter_settings);
    dbDelta($sql_trustworthiness_settings);
    dbDelta($sql_ratings);
    
     $displayCount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name_display_settings");
     if($displayCount == 0){
       // Insert default values into start_collector_widget_display_settings
    $wpdb->query("INSERT INTO $table_name_display_settings (display_position, display_mode, display_style) VALUES (3, 1, 1)");
     }
     
     $filterCount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name_filter_settings");
     if($filterCount == 0){
    // Insert default values into start_collector_widget_filter_settings
    $wpdb->query("INSERT INTO $table_name_filter_settings (min_rating_1_5, min_rating_1_10, min_reviews, all_posts, all_pages, specific_url_checkbox, exclude_review_platform_domains, exclude_admin_domains) VALUES (3.5, 6.5, 5, 1, 1, 0, 'indeed.com,glassdoor.com', 'trustpilot.com')");
     }
    
    $trustCount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name_trustworthiness_settings");
    if($trustCount == 0){
    // Insert default values into start_collector_widget_display_settings
    $wpdb->query("INSERT INTO $table_name_trustworthiness_settings (reviewise_badge) VALUES (0)");
    }
 }
}



// Hook for deleting the tables on plugin deactivation
register_uninstall_hook(__FILE__, 'SCRPlugin_uninstall');

// Function to delete the tables on deactivation
function SCRPlugin_uninstall() {
    if (current_user_can('activate_plugins')) {
        global $wpdb;

        $table_name_tokens = $wpdb->prefix . 'SCRPlugin_api_settings';
        $table_name_ratings = $wpdb->prefix . 'SCRPlugin_ratings';
        $table_name_display_settings = $wpdb->prefix . 'SCRPlugin_widget_display_settings';
        $table_name_filter_settings = $wpdb->prefix . 'SCRPlugin_widget_filter_settings';
        $table_name_trustworthiness_settings = $wpdb->prefix . 'SCRPlugin_trustworthiness_settings';
        $table_name_brand_settings = $wpdb->prefix . 'SCRPlugin_brand_settings';

        // Drop the tables
        $wpdb->query("DROP TABLE IF EXISTS $table_name_tokens");
        $wpdb->query("DROP TABLE IF EXISTS $table_name_ratings");
        $wpdb->query("DROP TABLE IF EXISTS $table_name_display_settings");
        $wpdb->query("DROP TABLE IF EXISTS $table_name_filter_settings");
        $wpdb->query("DROP TABLE IF EXISTS $table_name_trustworthiness_settings");
        $wpdb->query("DROP TABLE IF EXISTS $table_name_brand_settings");
    }
}

//------------------API token table Creation/Delete-------------------------


//------------------Register JS & CSS-------------------------

function SCRPlugin_enqueue_jquery() {
    // Enqueue jQuery and jQuery UI
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_style('dashicons');
    
    // Enqueue your custom script
    wp_enqueue_script('star-collector-script', plugin_dir_url(__FILE__) . 'js/star-collector-script.js', array('jquery'), '1.0', true);

    // Enqueue CSS
    wp_enqueue_style('star-collector-style', plugin_dir_url(__FILE__) . 'css/star-collector-style.css', array(), '1.0', 'all');
    
    $brandRatingData = SCRPlugin_brandRatingTable();
    $widgetDisplayData = SCRPlugin_widgetDisplaySettingsTable();
     $trustworthinessData = SCRPlugin_trustworthinessSettingsTable();
    // Pass PHP values to your script
    wp_localize_script('star-collector-script', 'pluginData', array(
        'imagesFolder' => esc_url(plugin_dir_url(__FILE__)) . 'images/', // Escaped URL
        'widgetDisplayPosition' => intval($widgetDisplayData->display_position), // Escaped as integer
        'widgetDisplayMood' => intval($widgetDisplayData->display_mode), // Escaped as integer
        'widgetDisplayStyle' => intval($widgetDisplayData->display_style), // Escaped as integer
        'brandRatingData' => array_map('sanitize_text_field', (array) $brandRatingData), // Escaped as text field
        'ajax_url' => esc_url(admin_url('admin-ajax.php')), // Escaped URL
        'trustworthinessId' => intval($trustworthinessData->id), // Escaped as integer
    ));
}
add_action('admin_enqueue_scripts', 'SCRPlugin_enqueue_jquery');


function SCRPlugin_enqueue_jquery_frontend() {
    wp_enqueue_script('jquery');
    // Enqueue your custom script
    wp_enqueue_script('star-collector-rater2', plugin_dir_url(__FILE__) . 'js/rater2.js', array('jquery'), '1.0', true);
    wp_enqueue_script('star-collector-rater', plugin_dir_url(__FILE__) . 'js/rater.js', array('jquery'), '1.0', true);
     wp_enqueue_script('star-collector-frontend-script', plugin_dir_url(__FILE__) . 'js/star-collector-frontend-script.js', array('jquery'), '1.0', true);
    wp_enqueue_script('star-collector-one-star', plugin_dir_url(__FILE__) . 'js/star-collector-one-star.js', array('jquery'), '1.0', true);

    // Enqueue CSS
    wp_enqueue_style('star-collector-frontend-style', plugin_dir_url(__FILE__) . 'css/star-collector-frontend-style.css', array(), '1.0', 'all');
}

// Use wp_enqueue_scripts instead of admin_enqueue_scripts
add_action('wp_enqueue_scripts', 'SCRPlugin_enqueue_jquery_frontend');


//------------------Register JS & CSS-------------------------


//------------------Setting WP-------------------------

function SCRPlugin_settings_link($links) {
    $settings_link = '<a href="admin.php?page=SCRPlugin_admin_page#settings-tab">' . esc_html__('Settings', 'star-collector-review-widgets') . '</a>';
    array_unshift($links, $settings_link);
    $_SESSION['active_tab'] = filter_var('settings-tab', FILTER_SANITIZE_SPECIAL_CHARS);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'SCRPlugin_settings_link');

function SCRPlugin_add_plugin_row_meta($meta, $file, $data, $status) {
    if (plugin_basename(__FILE__) === $file) {
        $plugin_slug = 'star-collector-review-widgets';
        $plugin_info = get_transient('plugin_info_' . $plugin_slug);
        $imageFolder = esc_url(plugin_dir_url(__FILE__)) . 'images/';
        $imagePath = $imageFolder . 'blue-star.png';

        $view_details_url = esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=' . $plugin_slug . '&TB_iframe=true&width=772&height=667'));
        $view_details_link = '<a href="' . esc_url($view_details_url) . '" class="thickbox" aria-label="' . esc_attr__('Star Collector Review Widgets', 'star-collector-review-widgets') . '">' . esc_html__('View Details', 'star-collector-review-widgets') . '</a>';
        $rating_link = '<a href="https://wordpress.org/support/plugin/star-collector-review-widgets/reviews/?filter=5#new-post" target="_blank" aria-label="' . esc_attr__('Star Collector Review Widgets', 'star-collector-review-widgets') . '">' . esc_html__('Rate ★★★★★', 'star-collector-review-widgets') . '</a>';
        

        $meta[] = $rating_link;
    }
    return $meta;
}

// Hook into the 'plugin_row_meta' filter
add_filter('plugin_row_meta', 'SCRPlugin_add_plugin_row_meta', 10, 4);


//------------------Setting WP-------------------------


//------------------Plugin Tab admin page-------------------------

// Add admin menu item
function SCRPlugin_menu() {
    $imageFolder = esc_url(plugin_dir_url(__FILE__) . 'images/');
    $reviseIcon = esc_url($imageFolder . 'reviewise-white-new.svg');
   
    add_menu_page(
        esc_html__('Star Collector Review', 'star-collector-review-widgets'),
        esc_html__('Star Collector Review Widgets', 'star-collector-review-widgets'),
        'manage_options',
        'SCRPlugin_admin_page',
        'SCRPlugin_admin_page_callback',
        $reviseIcon,
        80
    );
}

// Hook to add menu
add_action('admin_menu', 'SCRPlugin_menu');


// Callback function for the admin page
function SCRPlugin_admin_page_callback() {
        // Validate and sanitize the activeTab session variable
        session_start();
        $activeTab = isset($_SESSION['active_tab']) ? filter_var($_SESSION['active_tab'], FILTER_SANITIZE_SPECIAL_CHARS) : 'settings-tab';
    ?>
    
    <div class="wrap star-collector-review">
         <h1><?php echo esc_html__('Star Collector Review Widgets', 'star-collector-review-widgets'); ?></h1>
         <div class=" scr-mb-2">
            <?php 
            // call all tables
            $apiData = SCRPlugin_apiSettingsTable();
            $widgetDisplayData = SCRPlugin_widgetDisplaySettingsTable();
            $widgetFilterData = SCRPlugin_widgetfilterSettingsTable();
            $trustworthinessData = SCRPlugin_trustworthinessSettingsTable();
            $brandData = SCRPlugin_brandSettingsTable();
            $brandRatingData = SCRPlugin_brandRatingTable();
    
    if ($apiData === null) {
    $testData = '<span id="error" class="red">' . esc_html__('ERROR > No API Data Found', 'star-collector-review-widgets') . '</span>';
    $testIcon = '<i class="dashicons dashicons-no-alt red"></i>'; // Indicate an error with a different icon
    $brandTestData = '<span class="red">' . esc_html__('ERROR > No API Data Found', 'star-collector-review-widgets') . '</span>';
} else {
    $testData = $apiData->test == null ? 
        '<span id="error" class="green-star">' . esc_html__('CONNECTED > All Good!', 'star-collector-review-widgets') . '</span>' : 
        '<span id="error" class="red">' . esc_html__('ERROR > ', 'star-collector-review-widgets') . esc_html($apiData->test) . '</span>';

    $testIcon = $apiData->test == null ? 
        '<i class="dashicons dashicons-yes-alt green-star"></i>' : 
        '<i class="dashicons yellow-star dashicons-warning"></i>';

    $brandTestData = $apiData->test == null ? 
        '<span class="green-star">' . esc_html__('YES > All Good!', 'star-collector-review-widgets') . '</span>' : 
        '<span class="red">' . esc_html__('ERROR > Invalid API key', 'star-collector-review-widgets') . '</span>';
}
    
    
            ?>
            
            
            <!-- First set of content -->
                <div class="">
               <h2 class="scr-mt-2"><b><?php echo esc_html__('PLUGIN STATUS:', 'star-collector-review-widgets'); ?></b></h2>
    <div>
        - API Key Connection: <?php echo isset($apiData->api) ? wp_kses_post($testData) : '<span id="error" class="red">' . esc_html__('DISCONNECTED > API Key Missing.', 'star-collector-review-widgets') . '</span>'; ?>
    </div>
    <div>
        - Brand Settings Configured: <?php echo isset($brandData->brand_name) ? wp_kses_post($brandTestData) : '<span class="red">' . esc_html__('NO > Configure your Brand Settings.', 'star-collector-review-widgets') . '</span>'; ?>
    </div>
    <div>
        - Trustworthiness Enhanced: <?php echo ($trustworthinessData->reviewise_badge == 1) ? '<span id="error2" class="green-star">' . esc_html__('YES > All Good!', 'star-collector-review-widgets') . '</span>' : '<span class="red" id="error2">' . esc_html__('NO > Enable to Enhance Credibility and Trustworthiness.', 'star-collector-review-widgets') . '</span>'; ?>
    </div>
            </div>	
        </div>
        
         <?php 
       
        if(empty($apiData)){
         ?>
        <div class="star-collector-review">
            <span class="red"><?php echo esc_html__('Please Fill out API Settings tab information to access other tabs', 'star-collector-review-widgets'); ?></span>
        </div>
         <?php } ?>
                <h2 class="nav-tab-wrapper">
                    <a class="nav-tab <?php echo ($activeTab === 'settings-tab') ? 'nav-tab-active' : ''; ?>" data-tab="settings-tab" href="#">
                        <?php echo esc_html__('API Settings', 'star-collector-review-widgets'); ?> 
                        <?php echo isset($apiData) ? wp_kses_post($testIcon) : '<i class="dashicons yellow-star dashicons-warning"></i>'; ?>
                    </a>
                    <?php
                    if ($apiData AND $apiData->test == null) { ?>
                        <a class="nav-tab <?php echo ($activeTab === 'brand-tab') ? 'nav-tab-active' : ''; ?>" data-tab="brand-tab" href="#">
                            <?php echo esc_html__('Brand Settings', 'star-collector-review-widgets'); ?> 
                            <?php echo isset($brandData) ? '<i class="dashicons green-star dashicons-yes-alt"></i>' : '<i class="dashicons yellow-star dashicons-warning"></i>'; ?>
                        </a>
                        <a class="nav-tab <?php echo ($activeTab === 'display-tab') ? 'nav-tab-active' : ''; ?>" data-tab="display-tab" href="#">
                            <?php echo esc_html__('Display Settings', 'star-collector-review-widgets'); ?> 
                            <?php echo isset($widgetDisplayData) ? '<i class="dashicons green-star dashicons-yes-alt"></i>' : '<i class="dashicons yellow-star dashicons-warning"></i>'; ?>
                        </a>
                        <a class="nav-tab <?php echo ($activeTab === 'filters-tab') ? 'nav-tab-active' : ''; ?>" data-tab="filters-tab" href="#">
                            <?php echo esc_html__('Filter Settings', 'star-collector-review-widgets'); ?> 
                            <?php echo isset($widgetFilterData) ? '<i class="dashicons green-star dashicons-yes-alt"></i>' : '<i class="dashicons yellow-star dashicons-warning"></i>'; ?>
                        </a>
                    <?php } else { ?>
                        <a class="nav-tab <?php echo ($activeTab === 'settings-tab') ? 'nav-tab-active' : ''; ?>" data-tab="settings-tab" href="#">
                            <?php echo esc_html__('Brand Settings', 'star-collector-review-widgets'); ?> 
                            <i class="dashicons yellow-star dashicons-warning"></i>
                        </a>
                        <a class="nav-tab <?php echo ($activeTab === 'settings-tab') ? 'nav-tab-active' : ''; ?>" data-tab="settings-tab" href="#">
                            <?php echo esc_html__('Display Settings', 'star-collector-review-widgets'); ?> 
                            <i class="dashicons yellow-star dashicons-warning"></i>
                        </a>
                        <a class="nav-tab <?php echo ($activeTab === 'settings-tab') ? 'nav-tab-active' : ''; ?>" data-tab="settings-tab" href="#">
                            <?php echo esc_html__('Filter Settings', 'star-collector-review-widgets'); ?> 
                            <i class="dashicons yellow-star dashicons-warning"></i>
                        </a>
                    <?php } ?>
                </h2>

<!--setting tab-->
     <div class="tab-content <?php echo ($activeTab === 'settings-tab') ? '' : 'hide-class'; ?>" id="settings-tab">
    <form method="post" action="" id="star-collector-all-settings-form">
        <?php wp_nonce_field('my-action'); ?>
       <!-- Tab 1 API settings -->
      <div class="star-collector-review" id="api-content">
            <h3 class="scr-mb-2 scr-mt-2"><b><?php echo esc_html__('API Settings', 'star-collector-review-widgets'); ?></b></h3>
            
            <p><?php echo esc_html__('Get your Free API Key from SerpApi, Follow these simple steps in our guide:', 'star-collector-review-widgets'); ?></p>
            
            <b><a href="https://www.reviewise.co/free-online-tools/review-widget/#SerpApi-Free-Plan" target="_blank" style="font-size: 15px;"><?php echo esc_html__('Sign up for a Free SerpApi Plan, and Copy / Paste your API Key', 'star-collector-review-widgets'); ?></a></b>
            
            <br /><br />
            <div class="star-collector-review ">
                <label for="api-key"><b><?php echo esc_html__('Your SerpApi API Key:', 'star-collector-review-widgets'); ?></b> <span class="red">*</span></label>
                <input type="text" name="api" id="api-key" class="full-width-input" value="<?php echo isset($apiData->api) ? esc_attr($apiData->api) : null; ?>" required>
                <p><?php echo esc_html__('Note: SerpApi Free Plan offers 100 free credits per month, the plugin will use 1 credit per day to refresh review ratings stats.', 'star-collector-review-widgets'); ?></p>
            </div>
            <input type='hidden' name="api_id" value="<?php echo isset($apiData->id) ? esc_attr($apiData->id) : null; ?>">
            <input type='hidden' name="api_form_set" value="1">
            <input type='hidden' name="activate_tab" value="settings-tab">
        </div>
            
             
           
            <div class="form-actions">
                <input type="submit" name="all_settings" class="star-collector-review-button button button-primary" value="<?php echo esc_attr__('Update API Settings', 'star-collector-review-widgets'); ?>">
            </div>
             <hr style="width: 100%;  margin-top:12px;" />
                    
                    <!-- Tab Trustworthiness -->
                    <?php SCRPlugin_trustworthiness_form($trustworthinessData); ?>
            </form>
           
</div>


<!--display setting tab-->
<div class="tab-content <?php echo ($activeTab === 'display-tab') ? '' : 'hide-class'; ?>" id="display-tab">
    <form method="post" action="" id="star-collector-display-form">
        <?php wp_nonce_field('my-action'); ?>
                <div class="star-collector-review" id="tab3-content">
                <h3 class="scr-mb-2 scr-mt-2"><b><?php echo esc_html__('Widget Display Settings', 'star-collector-review-widgets'); ?></b></h3>
                 
                <p><?php echo esc_html__('Select your Widget Display to Showcase your Review Ratings Stats from Top Curated Review Platforms.', 'star-collector-review-widgets'); ?></p>

                  
              <div class="star-collector-flex-container">
                  
            <div class="star-collector-flex-item-3-columns star-collector-review">
                <?php
                    // If there is no stored value, default to 3
                    if (empty($widgetDisplayData)) {
                        $widgetDisplayPosition = '3';
                        $widgetDisplayMood = '1';
                        $widgetDisplayStyle = '1';
                    }else{
                        $widgetDisplayPosition = $widgetDisplayData->display_position;
                        $widgetDisplayMood = $widgetDisplayData->display_mode;
                        $widgetDisplayStyle = $widgetDisplayData->display_style;
                    }
                    ?>
                    <p><strong><?php echo esc_html__('Widget Display Position:', 'star-collector-review-widgets'); ?></strong><span class="red">*</span></p>
                    <?php
                        // Define radio button values and labels
                    $displayPositionButtons = array(
                        '1' => esc_html__('Middle Left', 'star-collector-review-widgets'),
                        '2' => esc_html__('Middle Right', 'star-collector-review-widgets'),
                        '3' => esc_html__('Bottom Left', 'star-collector-review-widgets'),
                        '4' => esc_html__('Bottom Right', 'star-collector-review-widgets'),
                    );

                    
                        // Loop through each radio button
                        foreach ($displayPositionButtons as $value => $label) {
                            ?>
                            <label>
                                <input type="radio" name="display_position" value="<?php echo esc_attr($value); ?>" <?php echo ($value == $widgetDisplayPosition) ? 'checked' : ''; ?> required>
                                <?php echo esc_html($label); ?>
                            </label>
                            <br>
                            <?php
                        }
                        
                        ?>
                    
            </div>

            <!-- Second set of content -->
            <div class="star-collector-flex-item-3-columns">
                <?php 
                        $displayMoodButtons = array(
                            '1' => esc_html__('For Light Colored Website', 'star-collector-review-widgets'),
                            '2' => esc_html__('For Contrasty / Dark Colored Website', 'star-collector-review-widgets'),
                        );
                        ?>
                        <p><strong><?php echo esc_html__('Widget Display Mode:', 'star-collector-review-widgets'); ?></strong> <span class="red">*</span></p>
                        <?php
                         // Loop through each radio button
                        foreach ($displayMoodButtons as $value => $label) {
                            ?>
                            <label>
                                <input type="radio" name="display_mode" value="<?php echo esc_attr($value); ?>" <?php echo ($value == $widgetDisplayMood) ? 'checked' : ''; ?> required>
                                <?php echo esc_html($label); ?>
                            </label>
                            <br>
                            <?php
                        }
                        $imageFolder = esc_url(plugin_dir_url(__FILE__) . 'images/');
                        $imagePath = $imageFolder . 'widget-preview-img-3.png';
                        $imagePath2 = $imageFolder . 'widget-style-preview-1.png';
                        $imagePath3 = $imageFolder . 'widget-style-design-1.png';
                        ?>
              
            </div>
            
            <div class="star-collector-flex-item-3-columns">
                 <p><strong><?php echo esc_html__('Widget Display Style:', 'star-collector-review-widgets'); ?></strong><span class="red">*</span></p>
                 <div class="displayStyleButtons">
                 <?php
                   $counter = 0;
                 if($widgetDisplayPosition == 1 OR $widgetDisplayPosition == 2){
                       // Define radio button values and labels
                        $displayStyleButtons = array(
                            '1' => esc_html__('Style A', 'star-collector-review-widgets'),
                            '2' => esc_html__('Style B', 'star-collector-review-widgets'),
                            '3' => esc_html__('Style C', 'star-collector-review-widgets'),
                            '4' => esc_html__('Style D', 'star-collector-review-widgets'),
                            '5' => esc_html__('Style E', 'star-collector-review-widgets'),
                            '6' => esc_html__('Style F', 'star-collector-review-widgets'),
                            '7' => esc_html__('Style G', 'star-collector-review-widgets'),
                        );
                 }else{
                       // Define radio button values and labels
                       $displayStyleButtons = array(
                            '1' => esc_html__('Style A', 'star-collector-review-widgets'),
                            '2' => esc_html__('Style B', 'star-collector-review-widgets'),
                            '3' => esc_html__('Style C', 'star-collector-review-widgets'),
                            '4' => esc_html__('Style D', 'star-collector-review-widgets'),
                            '5' => esc_html__('Style E', 'star-collector-review-widgets'),
                            '6' => esc_html__('Style F', 'star-collector-review-widgets'),
                            '7' => esc_html__('Style G', 'star-collector-review-widgets'),
                        );
                 }
                      
                    
                        // Loop through each radio button
                        foreach ($displayStyleButtons as $value => $label) {
                            if($widgetDisplayPosition == 1 OR $widgetDisplayPosition == 2){
                                if($value != 7){
                                    $disabled = 'disabled';
                                    $value = '';
                                }else{
                                    $disabled = '';
                                    $value;
                                }
                            }else{
                                if($value == 7){
                                    $disabled = 'disabled';
                                    $value = '';
                                    
                                }else{
                                    $disabled = '';
                                    $value;
                                }
                            }

                            if($value == 7) {
                                $class = 'widget-display-styleG-img';
                            }
                            else {
                                $class = 'widget-display-style-img';
                            }
                            ?>
                            <label>
                                <input type="radio" name="display_style" value="<?php echo esc_attr($value); ?>" <?php echo ($value == $widgetDisplayStyle) ? 'checked' : ''; ?> required <?php echo $disabled ?>>
                                <?php echo esc_html($label); ?>
                            </label>
                            <br>
                            <?php
                        } ?>
                 </div>
            </div>
            
        </div>
        
        
         <div class="star-collector-flex-container scr-mt-2 scr-mb-2">
            <!-- First set of content -->
            <div class="star-collector-flex-item-3-columns">
                <img id="widget-preview" src="<?php echo esc_url($imagePath); ?>" alt="<?php esc_attr_e('Widget Preview', 'star-collector-review-widgets'); ?>" class="img-size">
            </div>

            <!-- Second set of content -->
            <div class="star-collector-flex-item-3-columns">
                 <div style="position: absolute;">
                    <img id="widget-style-preview" src="<?php echo esc_url($imagePath2); ?>" alt="<?php esc_attr_e('Widget Preview Style', 'star-collector-review-widgets'); ?>" style="transform: scale(0.5); transform-origin: left top;">
                </div>
            </div>
             <!-- third set of content -->
            <div class="star-collector-flex-item-3-columns">
                <div style="position: absolute;">
                    <img id="widget-style-design" src="<?php echo esc_url($imagePath3); ?>" alt="<?php esc_attr_e('Widget Preview Style', 'star-collector-review-widgets'); ?>" style="transform: scale(0.5); transform-origin: left top;">
                </div>
            </div>
        </div>
        
        
            <input type='hidden' name="widget_display_id" value="<?php echo isset($widgetDisplayData->id) ? esc_attr($widgetDisplayData->id) : ''; ?>">
            <input type='hidden' name="display_form_set" value="1">
            <input type='hidden' name="activate_tab" value="display-tab">
             
             
             <div class="form-actions">
                <input type="submit" name="all_settings" class="star-collector-review-button button button-primary" value="<?php esc_attr_e('Update Display Settings', 'star-collector-review-widgets'); ?>">
            </div>
                    
            </div>
    </form>
    <hr />
       <!-- Tab Trustworthiness -->
           <?php SCRPlugin_trustworthiness_form($trustworthinessData); ?>
</div>


<!--filter setting tab-->
<div class="tab-content <?php echo ($activeTab === 'filters-tab') ? '' : 'hide-class'; ?>" id="filters-tab">
    <form method="post" action="" id="star-collector-filters-form">
        <?php wp_nonce_field('my-action'); ?>
  <div class="star-collector-review" id="tab4-content">
                <h3 class="scr-mb-2 scr-mt-2"><b><?php esc_html_e('Widget Filter Settings', 'star-collector-review-widgets'); ?></b></h3>
                 
                <p><?php esc_html_e('Showcase Only your Best Review Ratings Stats with Custom Filters:', 'star-collector-review-widgets'); ?></p>

                  
                <div class="star-collector-flex-container star-collector-review">
                    <div class="form-field">
                        <label for="min-rat-5"><b><?php esc_html_e('Minimum Ratings: (Scale of 1 to 5)', 'star-collector-review-widgets'); ?></b> <span class="red">*</span></label>
                        <input type="text" name="minimum_ratings_5" id="min-rat-5" value="<?php echo isset($widgetFilterData->min_rating_1_5) ? esc_attr($widgetFilterData->min_rating_1_5) : ''; ?>" pattern="^[1-5](\.\d)?$" required>
                        <small><?php esc_html_e('Enter a number from 1 to 5, with up to 1 decimal place', 'star-collector-review-widgets'); ?></small>
                    </div>

                    <div class="form-field">
                        <label for="min-rat-10"><b><?php esc_html_e('Minimum Ratings: (Scale of 1 to 10)', 'star-collector-review-widgets'); ?></b> <span class="red">*</span></label>
                        <input type="text" name="minimum_ratings_10" id="min-rat-10" value="<?php echo isset($widgetFilterData->min_rating_1_10) ? esc_attr($widgetFilterData->min_rating_1_10) : ''; ?>" pattern="^[1-9](\.\d)?$|^10$" required>
                        <small><?php esc_html_e('Enter a number from 1 to 10, with up to 1 decimal place', 'star-collector-review-widgets'); ?></small>
                    </div>
                    
                    <div class="form-field">
                        <label for="min-count"><b><?php esc_html_e('Minimum Reviews Count:', 'star-collector-review-widgets'); ?></b> <span class="red">*</span></label>
                        <input type="text" name="minimum_count" id="min-count" value="<?php echo isset($widgetFilterData->min_reviews) ? esc_attr($widgetFilterData->min_reviews) : ''; ?>" pattern="^[1-9]\d{0,2}$|^1000$" required>
                        <small><?php esc_html_e('Enter a whole number from 1 to 1000', 'star-collector-review-widgets'); ?></small>
                    </div>
                       
                    </div>
                    
                    <div class="star-collector-flex-container star-collector-review scr-mt-2">
                        <?php
                        // Assuming you have fetched values from the database for the checkboxes
                        $allPostsChecked = ($widgetFilterData->all_posts == 1) ? 'checked' : '';
                        $allPagesChecked = ($widgetFilterData->all_pages == 1) ? 'checked' : '';
                        $specificUrlsValue = ($widgetFilterData->specific_url_checkbox) ? 'checked' : '';
                        
                        // Check if any of the checkboxes have a value to determine if 'required' attribute should be added
                        $isRequired = ($widgetFilterData->all_posts == 1 || $widgetFilterData->all_pages == 1 || $widgetFilterData->specific_url_checkbox == 1) ? '' : 'required';
                        
                        ?>
                       <div class="star-collector-flex-item">
                            <p class="scr-mt-2 "><b><?php esc_html_e('Show Review Widget On:', 'star-collector-review-widgets'); ?> <span class="red">*</span></b></p>
                            <p><label>
                                <input type="checkbox" name="all_posts" value="" class="requiredCheckbox" <?php echo esc_attr($allPostsChecked); echo esc_attr($isRequired); ?>> <?php esc_html_e('All Posts', 'star-collector-review-widgets'); ?>
                            </label></p>
                            <p><label>
                                <input type="checkbox" name="all_pages" value="" class="requiredCheckbox" <?php echo esc_attr($allPagesChecked); echo esc_attr($isRequired); ?>> <?php esc_html_e('All Pages', 'star-collector-review-widgets'); ?>
                            </label></p>
                            <p><label>
                                <input type="checkbox" name="specific_url_checkbox" class="requiredCheckbox" <?php echo esc_attr($specificUrlsValue); echo esc_attr($isRequired); ?>> <?php esc_html_e('Specific URLs (Comma Separated):', 'star-collector-review-widgets'); ?>
                            </label><br /><br />
                            <textarea name="specific_url" rows="4" cols="50"><?php echo isset($widgetFilterData->specific_url) ? esc_textarea($widgetFilterData->specific_url) : ''; ?></textarea><br />
                            </p>
                        </div>

                        <div class="star-collector-flex-item">
                            <p class="scr-mt-2"><b><?php esc_html_e('Exclude Review Platform Domains (Comma Separated):', 'star-collector-review-widgets'); ?></b></p>
                            <textarea name="exclude_review_platform_domains" rows="4" cols="50" placeholder="<?php esc_attr_e('indeed.com glassdoor.com', 'star-collector-review-widgets'); ?>"><?php echo isset($widgetFilterData->exclude_review_platform_domains) ? esc_textarea($widgetFilterData->exclude_review_platform_domains) : ''; ?></textarea>
                            <textarea name="exclude_admin_domains" rows="4" cols="50" class="hide-class"><?php echo isset($widgetFilterData->exclude_admin_domains) ? esc_textarea($widgetFilterData->exclude_admin_domains) : ''; ?></textarea>
                        </div>
            
                    </div>
                    <input type='hidden' name="widget_filter_id" value="<?php echo isset($widgetFilterData->id) ? esc_attr($widgetFilterData->id) : ''; ?>">
                    <input type='hidden' name="filter_form_set" value="1">
                    <input type='hidden' name="activate_tab" value="filters-tab">
                    
                    <div class="form-actions">
                        <input type="submit" name="all_settings" class="star-collector-review-button button button-primary" value="<?php esc_attr_e('Update Filter Settings', 'star-collector-review-widgets'); ?>">
                    </div>
            </div>  
    </form>
    
    <hr />
          <!-- Tab Trustworthiness -->
           <?php SCRPlugin_trustworthiness_form($trustworthinessData); ?>
</div>


<!--brand tab -->
 <div class="tab-content <?php echo ($activeTab === 'brand-tab') ? '' : 'hide-class'; ?>" id="brand-tab">
     <?php
                        // Assuming you have fetched values from the database for the checkboxes
                        if($brandData){
                        $includePlatformsCheckbox = ($brandData->extra_review_platforms == 1) ? 'checked' : '';
                        }else{
                            $includePlatformsCheckbox = 'checked';
                        }
                        
                        
                            if ($brandRatingData) {
                            $valueDisplay = '';
                        
                            // Loop through ratings and display checkboxes
                            foreach ($brandRatingData as $rating) {
                                // Sanitize $rating_id
                                $rating_id = filter_var($rating->id, FILTER_SANITIZE_NUMBER_INT);

                                // Sanitize $rating_value, $ratingOutOf_value, $review_value, $source_value
                                $rating_value = htmlspecialchars($rating->rating, ENT_QUOTES, 'UTF-8');
                                $ratingOutOf_value = htmlspecialchars($rating->rating_out_of, ENT_QUOTES, 'UTF-8');
                                $review_value = htmlspecialchars($rating->review, ENT_QUOTES, 'UTF-8');
                                $source_value = htmlspecialchars($rating->source, ENT_QUOTES, 'UTF-8');

                                // Sanitize $favicon_value
                                $favicon_value = filter_var($rating->favicon, FILTER_SANITIZE_URL);

                        
                                // Add a comma before appending values if $valueDisplay is not empty
                                if (!empty($valueDisplay)) {
                                    $valueDisplay .= ', ';
                                }
                        
                                $valueDisplay .= $rating_value.'|'.$ratingOutOf_value.'|'.$review_value.'|'.$source_value.'|'.$favicon_value;
                            }
                        
                            $valueDisplay;
                        }
                        
                        ?>
                        
            <div class=" scr-mt-2" id="tab2-content">
                <h3 class="scr-mb-2"><b><?php esc_html_e('Brand Settings', 'star-collector-review-widgets'); ?></b></h3>
                <p><?php esc_html_e('Enter your Brand Details to Collect your Review Ratings Stats from Top Curated Review Platforms:', 'star-collector-review-widgets'); ?></p>
                
              <form method="post" action="" id="star-collector-form" class="star-collector-review">
                    <?php wp_nonce_field('my-action'); ?>
                    <div class="form-field">
                        <label for="brandname"><b><?php echo esc_html__('Your Brand Name:', 'star-collector-review-widgets'); ?></b> <span class="red">*</span></label>
                        <input type="text" name="brandname" id="brandname" value="<?php echo isset($brandData->brand_name) ? esc_attr($brandData->brand_name) : ''; ?>" required>
                        <small><?php echo esc_html__('E.g. The Ludlow Hotel', 'star-collector-review-widgets'); ?></small>
                    </div>

                    <div class="form-field">
                        <label for="identifiers"><b><?php echo esc_html__('Your Brand Location:', 'star-collector-review-widgets'); ?></b></label>
                        <input type="text" name="identifiers" id="identifiers" value="<?php echo isset($brandData->extra_brand_identifiers) ? esc_attr($brandData->extra_brand_identifiers) : ''; ?>">
                        <small><?php echo esc_html__('E.g. New York', 'star-collector-review-widgets'); ?></small>
                    </div>
                    
                    <div class="form-field">
                        <label for="include-platforms">
                            <input type="checkbox" style="display: inline-block;" name="include_platforms" id="include-platforms" value="rating" <?php echo $includePlatformsCheckbox ? 'checked' : ''; ?>>
                            <?php echo esc_html__('Find Extra Review Platforms.', 'star-collector-review-widgets'); ?>
                        </label>
                    </div>
                
                    <div class="form-actions">
                        <input type="submit" name="submit" class="star-collector-review-button button button-primary" value="<?php echo esc_attr__('Step 1. Search for Review Platforms + Apply Filter Settings', 'star-collector-review-widgets'); ?>">
                    </div>
                </form>

                <form method="post" action="" id="star-collector-api-form" class="star-collector-review">
                    <?php wp_nonce_field('my-action'); ?>
                    
                     <div id="ratings" class="star-collector-review hide-class">
                     <hr />
                    <h3><?php esc_html_e('All Platform Ratings', 'star-collector-review-widgets'); ?></h3>
                </div>
                <div id="api-response" style="width:100%">
                     <?php SCRPlugin_display_brand_ratings_checkbox(); ?>
                </div>
                 <div id="api-response2" style="width:100%">
                      <input type='hidden' name="all_checkbox_values" id='ratingValues' value="<?php echo isset($valueDisplay) ? esc_attr($valueDisplay) : ''; ?>">
                 </div>
                
                <input type='hidden' name="brand_id" value="<?php echo isset($brandData->id) ? esc_attr($brandData->id) : ''; ?>">
                
                <input type='hidden' name="get_brandname" id='getBrandname' value="<?php echo isset($brandData->brand_name) ? esc_attr($brandData->brand_name) : ''; ?>">
                                
                <input type='hidden' name="get_identifiers" id='getIdentifiers' value="<?php echo isset($brandData->extra_brand_identifiers) ? esc_attr($brandData->extra_brand_identifiers) : ''; ?>">
                                
                <input type='hidden' name="get_include_platforms" id='getIncludePlatforms' value="<?php echo isset($brandData->extra_review_platforms) ? esc_attr($brandData->extra_review_platforms) : ''; ?>">
                                
               <div class="star-collector-form-btn hide-class">
                    <input type="submit" name="brand_submit" id="star-collector-form-btn" class="star-collector-review-button button button-primary" value="<?php esc_attr_e('Step 2. Update Brand Settings', 'star-collector-review-widgets'); ?>">
                    <br /><small class="red"><?php esc_html_e('Select up to 8 Review Platforms to display in review widget', 'star-collector-review-widgets'); ?></small>
                </div>

                </form>
            </div>
            
             <hr />

            <!-- Tab Trustworthiness -->
           <?php SCRPlugin_trustworthiness_form($trustworthinessData); ?>
</div>
        
    </div>
    <?php
}


//------------------Plugin Tab admin page-------------------------


//------------------FetchData and Response-------------------------
add_action('wp_ajax_SCRPlugin_fetch_data', 'SCRPlugin_fetch_data_callback');

function SCRPlugin_fetch_data_callback() {
    if (isset($_POST['formData'])) {
        // Sanitize the 'formData' input
        $sanitized_form_data = filter_var($_POST['formData'], FILTER_SANITIZE_STRING);
        // Parse the sanitized query string into an array
        parse_str($sanitized_form_data, $form_data);

        // Verify nonce
        if (isset($form_data['_wpnonce']) && wp_verify_nonce($form_data['_wpnonce'], 'my-action')) {

            // Get form data
            $brandname = sanitize_text_field($form_data['brandname']);
            $identifiers = sanitize_text_field($form_data['identifiers']);
            $include_platforms = isset($form_data['include_platforms']) ? sanitize_text_field($form_data['include_platforms']) : '';

            // Validate brandname (assuming it should be a non-empty string)
            if (empty($brandname)) {
                $brandname = ''; // Handle invalid case as needed
            }

            // Validate identifiers (assuming it should be a non-empty string)
            if (empty($identifiers)) {
                $identifiers = ''; // Handle invalid case as needed
            }

            // Validate include_platforms (assuming it should be a non-empty string if set)
            if (isset($form_data['include_platforms']) && empty($include_platforms)) {
                $include_platforms = ''; // Handle invalid case as needed
            }

            // Set platforms value based on checkbox
            $platforms = $include_platforms === 'rating' ? 'rating' : '';

            // Make API call
            $apiTableData = SCRPlugin_apiSettingsTable(); // call all tables
            $apiKey = $apiTableData->api;
            $api_url = "https://serpapi.com/search.json?api_key={$apiKey}&google_domain=google.com&num=100&q={$brandname}+{$identifiers}+%22{$platforms}%22";
            // Use wp_remote_get to make the API call
            $response = wp_remote_get($api_url);
        
            if(is_wp_error($response)) {
                
                $response = wp_remote_get($api_url);
                
                if(is_wp_error($response)) {
                    $test = 'Api response error';
                }else{
                    if (!is_wp_error($response) && $response['response']['code'] === 200) {
                        
                        $api_data = json_decode($response['body'], true);
                        $top_10_ratings = SCRPlugin_getTopRatings($api_data);
                        echo wp_json_encode($top_10_ratings); // Return JSON response
                        
                    }else{
                        echo wp_kses_post(SCRPlugin_apiError($response));
                    }
                }
                
            }else{
            
                if (!is_wp_error($response) && $response['response']['code'] === 200) {
                    
                    $api_data = json_decode($response['body'], true);
                    $top_10_ratings = SCRPlugin_getTopRatings($api_data);
                    echo wp_json_encode($top_10_ratings); // Return JSON response
                    
                }else{
                    echo wp_kses_post(SCRPlugin_apiError($response));
                }
            
            }
        }
        wp_die(); // Always die in functions echoing Ajax content
    }
}

function SCRPlugin_getTopRatings($api_data) {
    
    $wedgetFilterData =  SCRPlugin_widgetFilterSettingsTable();
    
    $ratingArray = [];
    $imageFolder = esc_url(plugin_dir_url(__FILE__)) . 'images/';
    // Sanitize API data
    $api_data = SCRPlugin_sanitize_api_data($api_data);
    
    $organicResults =  $api_data['organic_results'];
    // Filter out records without the top array in rich_snippet
    $organicResults = array_filter($organicResults, function ($result) {
        return isset($result['rich_snippet']['top']);
    });
    
    $uniqueSources = [];
    
    foreach($organicResults as $result){
        $extensionData = null;
        if (isset($result['rich_snippet']['top']['extensions']) && is_array($result['rich_snippet']['top']['extensions'])) {
            foreach ($result['rich_snippet']['top']['extensions'] as $extension) {
                if (preg_match('/^((?:\d+(?:\.\d+)?%?)|(?:\d+(?:\.\d+)?\/10))\s*\((\d+(?:,\d+)*)\)/', $extension, $matches)) {
                    $extensionData = $extension;
                    break;
                }
            }
        }

        if ($extensionData) {
            // Parse the extension data
            if (preg_match('/^((?:\d+(?:\.\d+)?%?)|(?:\d+(?:\.\d+)?\/10))\s*\((\d+(?:,\d+)*)\)/', $extensionData, $matches)) {
                $ratingStr = $matches[1];
                $review = filter_var(str_replace(',', '', $matches[2]), FILTER_SANITIZE_NUMBER_INT);

                // Convert rating to numeric value
                if (strpos($ratingStr, '%') !== false) {
                    $rating = floatval($ratingStr) / 20; // Convert percentage to 5-point scale
                    $ratingOutOf = 5;
                } elseif (strpos($ratingStr, '/10') !== false) {
                    list($numerator, $denominator) = explode('/', $ratingStr);
                    $rating = floatval($numerator);
                    $ratingOutOf = 10;
                } else {
                    $rating = floatval($ratingStr);
                    $ratingOutOf = ($rating > 5) ? 10 : 5;
                }

                // Sanitize and filter the rating
                $rating = filter_var($rating, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                // Ensure rating is between 0 and 10
                $rating = max(0, min(10, $rating));

                // Convert to 1 decimal place
                $rating = number_format($rating, 1);
            } else {
                $rating = null;
                $review = null;
                $ratingOutOf = null;
            }
        } else {
            $rating = null;
            $review = null;
            $ratingOutOf = null;
        }
        
        if($rating){
            $favicon = $result['favicon'] ? esc_url($result['favicon']) : $imageFolder . 'no-image.png'; 
            
            if (($ratingOutOf == 5 && $rating < $wedgetFilterData->min_rating_1_5) || 
                ($ratingOutOf == 10 && $rating < $wedgetFilterData->min_rating_1_10)) {
                continue;
            }
            
            // Sanitize $review
            $review = $review !== null ? htmlspecialchars($review, ENT_QUOTES, 'UTF-8') : null;

            // Sanitize $votes
            $votes = null;

            if ((!empty($review) && $review >= $wedgetFilterData->min_reviews) || (!empty($votes) && $votes >= $wedgetFilterData->min_reviews)) {
                $sourceKey = strtolower($result['source']);
                if (!isset($uniqueSources[$sourceKey]) || $uniqueSources[$sourceKey]['review'] < $review || $uniqueSources[$sourceKey]['votes'] < $votes) {
                    $uniqueSources[$sourceKey] = [
                        "source" => sanitize_text_field($result['source']),
                        "rating" => $rating,
                        "review" => $review,
                        "votes" => $votes,
                        "favicon" => $favicon,
                        "rating_out_of" => $ratingOutOf,
                    ];
                }
            }
        }
    }
    
    // Extract the excluded domains and remove unnecessary characters
    $excludePlatformDomains = explode(',', $wedgetFilterData->exclude_review_platform_domains);
    $array1_filtered = array_filter($excludePlatformDomains);

    $excludeAdminDomains = explode(',', $wedgetFilterData->exclude_admin_domains);
    $array2_filtered = array_filter($excludeAdminDomains);
    $excludeDomains = array_merge($array1_filtered, $array2_filtered);
    $excludeSources = [];

    foreach ($excludeDomains as $domain) {
        $domain = trim($domain);
        $domainWithoutProtocols = preg_replace('/^(https?:\/\/)?(www\d?\.)?/i', '', $domain);
        $mainDomain = strtok($domainWithoutProtocols, '.');
        $excludeSources[] = strtolower($mainDomain);
    }
    // Add a filter to remove sources matching the excluded domains
    $uniqueSources = array_filter($uniqueSources, function ($source) use ($excludeSources) {
        $sourceName = strtolower($source['source']);
        foreach ($excludeSources as $excludeSource) {
            if (strpos($sourceName, $excludeSource) !== false) {
                return false;
            }
        }
        return true;
    });
    
    // Convert the associative array back to indexed array
    $ratingArray = array_values($uniqueSources);
    // Obtain a list of columns
    foreach ($ratingArray as $key => $row) {
        $sortRating[$key] = floatval($row['rating']);
        // Validate
        if (!is_float($sortRating[$key])) {
            $sortRating[$key] = 0.0;
        }
    }

    // Sort the data by rating
    array_multisort($sortRating, SORT_DESC, $ratingArray);

    $topRatedRecords = $ratingArray;

    //google image
    // Sanitize $googleRating
    $googleRating = isset($api_data['knowledge_graph']['rating']) 
        ? filter_var($api_data['knowledge_graph']['rating'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) 
        : null;
    // Check if $googleRating is null
    if ($googleRating === null) {
        $googleRating = 0.0; // Set to 0.0 if null
    }

    // Sanitize $googleReview
    $googleReview = isset($api_data['knowledge_graph']['review_count']) 
        ? filter_var($api_data['knowledge_graph']['review_count'], FILTER_SANITIZE_NUMBER_INT) 
        : null;

    // Check if $googleReview is null
    if ($googleReview === null) {
        $googleReview = 0; // Set to 0 if null
    }

$googleImage = $imageFolder . 'google-icon.png';

if((!empty($googleRating) && $googleRating >= $wedgetFilterData->min_rating_1_5) && (!empty($googleReview) && $googleReview >= $wedgetFilterData->min_reviews)){
$knowledgeArr = [
                "source" => 'Google',
                "rating" =>  $googleRating,
                "review" => $googleReview,
                "votes" => null,
                'favicon' => esc_url($googleImage),
                "rating_out_of" => 5,
        ];
        
       array_unshift($topRatedRecords, $knowledgeArr);
}
       
return $topRatedRecords;

}

//------------------FetchData and Response-------------------------


//------------------Store All Settings Table-------------------------
// Hook the function to the init action
add_action('init', 'SCRPlugin_store_all_settings');

function SCRPlugin_store_all_settings() {
    // Process the form submission
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['all_settings'])) {

        // Verify nonce
        if (isset( $_POST['_wpnonce'] ) && wp_verify_nonce(sanitize_text_field(wp_unslash( $_POST['_wpnonce'] ) ), 'my-action' ) ) {
            // The nonce is valid, proceed with processing the form data
            // api form set
            if(isset($_REQUEST['api_form_set'])){
                // Prepare data for the first table (star_collector_api_settings)
                $api = isset($_POST['api']) ? sanitize_text_field($_POST['api']) : '';
                $apiId = isset($_POST['api_id']) ? absint($_POST['api_id']) : 0;

                // Validate API (Example: Ensure it's not empty)
                if (empty($api)) {
                    // Handle validation failure (e.g., set default value or error message)
                    $api = ''; // or handle error
                }

                // Validate API ID (Example: Ensure it's a positive integer)
                if ($apiId <= 0) {
                    // Handle validation failure (e.g., set default value or error message)
                    $apiId = 0; // or handle error
                }

                $brandname = 'The Ludlow Hotel';
                $identifiers = 'New York';

                // Set platforms value based on checkbox
                $platforms = 'rating';

                // Make API call
            
                $api_url = "https://serpapi.com/search.json?api_key={$api}&google_domain=google.com&num=100&q={$brandname}+{$identifiers}+%22{$platforms}%22";
                // Use wp_remote_get to make the API call
                $response = wp_remote_get($api_url);
                
                if (is_wp_error($response)) {
                    
                    $response = wp_remote_get($api_url);
                    
                    if (is_wp_error($response)) {
                        $test = 'Api response error';
                    }else{
                        if (!is_wp_error($response) && $response['response']['code'] === 200) {
                            $test = null;
                        } else {
                            $test = SCRPlugin_apiError($response);
                        }
                    }
                    
                }else{
                        // Display the API response
                    if (!is_wp_error($response) && $response['response']['code'] === 200) {
                        $test = null;
                    } else {
                        $test = SCRPlugin_apiError($response);
                    }
                    
                }
                
                // Prepare data for the first table (star_collector_api_settings)
                $apiSettingsData = array('api' => $api, 'test' => $test);

                SCRPlugin_insertOrUpdateData('SCRPlugin_api_settings', $apiSettingsData, $apiId);
            
            }
        
            // display form set
            if(isset($_REQUEST['display_form_set'])){
                // prepare data for table (star_collector_widget_display_settings)
                $displayPosition = isset($_POST['display_position']) ? sanitize_text_field($_POST['display_position']) : '';
                $displayMode = isset($_POST['display_mode']) ? sanitize_text_field($_POST['display_mode']) : '';
                $displayStyle = isset($_POST['display_style']) ? sanitize_text_field($_POST['display_style']) : '';
                $displayId = isset($_POST['widget_display_id']) ? absint($_POST['widget_display_id']) : 0;
                $widgetDisplayData = array('display_position' => $displayPosition, 'display_mode' => $displayMode, 'display_style' => $displayStyle);

                // validation
                if (empty($displayPosition)) {
                    $displayPosition = ''; // or handle error
                }
                if (empty($displayMode)) {
                    $displayMode = ''; // or handle error
                }
                if (empty($displayStyle)) {
                    $displayStyle = ''; // or handle error
                }
                if ($displayId <= 0) {
                    // Handle validation failure (e.g., set default value or error message)
                    $displayId = 0; // or handle error
                }


                SCRPlugin_insertOrUpdateData('SCRPlugin_widget_display_settings', $widgetDisplayData, $displayId);
            }
            
        
            if(isset($_REQUEST['filter_form_set'])){
                // prepare data for table (star_collector_widget_filter_settings)
                $minimumRatings_5 = isset($_POST['minimum_ratings_5']) ? sanitize_text_field($_POST['minimum_ratings_5']) : '';
                $minimumRatings_10 = isset($_POST['minimum_ratings_10']) ? sanitize_text_field($_POST['minimum_ratings_10']) : '';
                $minimumCount = isset($_POST['minimum_count']) ? absint($_POST['minimum_count']) : 0;
                $excludeReview = isset($_POST['exclude_review_platform_domains']) ? sanitize_text_field($_POST['exclude_review_platform_domains']) : '';
                $excludeAdminReview = isset($_POST['exclude_admin_domains']) ? sanitize_text_field($_POST['exclude_admin_domains']) : '';
                $filterId = isset($_POST['widget_filter_id']) ? absint($_POST['widget_filter_id']) : 0;

                // Validate minimum ratings (assuming they should be positive numbers)
                if (empty($minimumRatings_5)) {
                    // Handle validation failure (e.g., set default value or error message)
                    $minimumRatings_5 = 0.0; // or handle error
                }

                // Validate minimum ratings (assuming they should be positive numbers)
                if (empty($minimum_ratings_10)) {
                    // Handle validation failure (e.g., set default value or error message)
                    $minimum_ratings_10 = 0.0; // or handle error
                }

                // Validate minimum count (assuming it should be a positive integer)
                if ($minimumCount <= 0) {
                    // Handle validation failure (e.g., set default value or error message)
                    $minimumCount = 1; // or handle error
                }

                // Validate $excludeReview
                if (empty($excludeReview)) {
                    // Handle validation failure (e.g., set default value or error message)
                    $excludeReview = ''; // or handle error
                }

                // Validate $excludeAdminReview
                if (empty($excludeAdminReview)) {
                    // Handle validation failure (e.g., set default value or error message)
                    $excludeAdminReview = ''; // or handle error
                }

                // Validate filter ID (assuming it should be a positive integer)
                if ($filterId <= 0) {
                    // Handle validation failure (e.g., set default value or error message)
                    $filterId = 0; // or handle error
                }
                
                if (isset($_POST['all_posts'])) {
                    $allPosts = 1;
                } else {
                    $allPosts = 0;
                }
                
                if (isset($_POST['all_pages'])) {
                $allPages = 1;
                } else {
                    $allPages = 0;
                }
            
                if (isset($_POST['specific_url_checkbox'])) {
                    $specificUrlCheckbox = 1;
                    $specificUrl = isset($_POST['specific_url']) ? esc_url_raw($_POST['specific_url']) : '';
                } else {
                    $specificUrlCheckbox = 0;
                    $specificUrl = null;
                }
                $widget_display_data = array(
                    'min_rating_1_5' => $minimumRatings_5,
                    'min_rating_1_10' => $minimumRatings_10,
                    'min_reviews' => $minimumCount,
                    'all_posts' => $allPosts,
                    'all_pages' => $allPages,
                    'specific_url_checkbox' => $specificUrlCheckbox,
                    'specific_url' => $specificUrl,
                    'exclude_review_platform_domains' => $excludeReview,
                    'exclude_admin_domains' =>$excludeAdminReview
                );

                SCRPlugin_insertOrUpdateData('SCRPlugin_widget_filter_settings', $widget_display_data, $filterId);
            }
            
            // Store the active tab in a session variable
            $_SESSION['active_tab'] = isset($_POST['activate_tab']) ? sanitize_text_field($_POST['activate_tab']) : '';
        }
    }
}

    //------------------Store All Settings Table-------------------------


//------------------Store Brand Settings Data-------------------------
// Hook the function to the init action
add_action('init', 'SCRPlugin_store_brand_settings');
// Process the form submission
function SCRPlugin_store_brand_settings() {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['brand_submit'])) {
        global $wpdb;

        if (isset( $_POST['_wpnonce'] ) && wp_verify_nonce(sanitize_text_field(wp_unslash( $_POST['_wpnonce'] ) ), 'my-action' ) ) {
            // prepare data for table (star_collector_widget_display_settings)
            $brandName = isset($_POST['get_brandname']) ? sanitize_text_field($_POST['get_brandname']) : '';
            $identifiers = isset($_POST['get_identifiers']) ? sanitize_text_field($_POST['get_identifiers']) : '';
            $includePlatforms = isset($_POST['get_include_platforms']) ? sanitize_text_field($_POST['get_include_platforms']) : '';
            $brandId = isset($_POST['brand_id']) ? absint($_POST['brand_id']) : 0;

            if (empty($brandName)) {
                // Handle validation failure (e.g., set default value or error message)
                $brandName = ''; // or handle error
            }
            if (empty($identifiers)) {
                // Handle validation failure (e.g., set default value or error message)
                $identifiers = ''; // or handle error
            }
            if (empty($includePlatforms)) {
                // Handle validation failure (e.g., set default value or error message)
                $includePlatforms = ''; // or handle error
            }
            if ($brandId <= 0) {
                // Handle validation failure (e.g., set default value or error message)
                $brandId = 0; // or handle error
            }

            $brandData = array(
                'brand_name' => $brandName, 
                'extra_brand_identifiers' => $identifiers,
                'extra_review_platforms' => $includePlatforms,
                );

            $insertOrUpdate = SCRPlugin_insertOrUpdateData('SCRPlugin_brand_settings', $brandData, $brandId);
            
                // Get all checkbox values
                $allCheckboxValues = isset($_POST['all_checkbox_values']) ? sanitize_text_field($_POST['all_checkbox_values']) : '';

                $allCheckboxValuesArr = explode(', ', $allCheckboxValues);
                
                // Get checked checkboxes using this function in helper file to sanitize data
                if (isset($_POST['rating_checkbox']) && is_array($_POST['rating_checkbox'])) {
                    $checkedValues = array_map(function($value) {
                        // Explode the string by pipe character to get individual values
                        $parts = explode('|', $value);
                        
                        // Sanitize each part individually without trimming whitespace
                        $sanitized_parts = array_map(function ($part) {
                            return wp_kses($part, array()); // Using wp_kses for basic sanitization without trimming whitespace
                        }, $parts);
                
                        // Implode the sanitized parts back into a string separated by pipe character
                        return implode('|', $sanitized_parts);
                    }, $_POST['rating_checkbox']);
                } else {
                    $checkedValues = [];
                }
                
                
                // Get unchecked checkboxes by subtracting checked values from all values
                $uncheckedValues = array_diff($allCheckboxValuesArr, $checkedValues);
                
                // Add '1' at the end of each checked value
                $checkedValuesWithSuffix = array_map(function ($value) {
                    return $value . '|1';
                }, $checkedValues);
                
                // Add '0' at the end of each unchecked value
                $uncheckedValuesWithSuffix = array_map(function ($value) {
                    return $value . '|0';
                }, $uncheckedValues);
                
                
                // Merge both arrays
                $records = array_merge($checkedValuesWithSuffix, $uncheckedValuesWithSuffix);
                
                $table = $wpdb->prefix . 'SCRPlugin_ratings';
            
        // Check if a record with the same brand_id exists
                $existingRecord = $wpdb->get_row(
                    $wpdb->prepare("SELECT * FROM $table WHERE brand_id = %d", $insertOrUpdate)
            );
            
            if ($existingRecord) {
                $wpdb->delete($table ,array('brand_id' => $existingRecord->brand_id),array('%d') );
            }
            
            foreach ($records as $record) {
                
                // Define the regular expression pattern to match the fields
                $pattern = '/^([^|]+)\|([^|]+)\|([^|]+)\|(.+?)\|([^|]+)\|([^|]+)$/';
                
                // Perform the regular expression match
                preg_match($pattern, $record, $matches);
                
                
                // Split the record into rating, review, and source
                list(, $rating, $ratingOutOf, $review, $source, $favicon, $status) = $matches;
                
                // Prepare data for insertion
                $brandData = array(
                    'rating'   => $rating,
                    'rating_out_of'   => $ratingOutOf,
                    'review'   => $review,
                    'source'   => $source,
                    'favicon'   => $favicon,
                    'brand_id' => $insertOrUpdate,
                    'status' => $status,
                );
                // Insert the record into the database
                SCRPlugin_insertOrUpdateData('SCRPlugin_ratings', $brandData, null);
            }
            // Store the active tab in a session variable
            // Setting the session variable with sanitization
            $_SESSION['active_tab'] = filter_var('brand-tab', FILTER_SANITIZE_SPECIAL_CHARS);
        }        
    }
}

//------------------Store Brand Settings Data-------------------------

//------------------Ajax Call for trusthworthiness data saved-------------------------
// Hook to add your AJAX action
add_action('wp_ajax_SCRPlugin_update_trustworthiness', 'SCRPlugin_update_trustworthiness');

// Function to handle the AJAX request
function SCRPlugin_update_trustworthiness() {
    // Check if the request came from a valid source

    // Get the values from the AJAX request
    $reviewiseBadge = isset($_POST['rewiseBadge']) ? absint($_POST['rewiseBadge']) : 0;
    $trustworthinessId = isset($_POST['trustworthinessId']) ? absint($_POST['trustworthinessId']) : null;
    $trustworthinessData = array('reviewise_badge' => $reviewiseBadge);

    if ($reviewiseBadge <= 0) {
        // Handle validation failure (e.g., set default value or error message)
        $reviewiseBadge = 0; // or handle error appropriately
    }
    // Validate trustworthiness ID (assuming it should be a positive integer or null)
    if (!is_null($trustworthinessId) && $trustworthinessId <= 0) {
        // Handle validation failure (e.g., set default value or error message)
        $trustworthinessId = 0; // or handle error appropriately
    }
    
    SCRPlugin_insertOrUpdateData('SCRPlugin_trustworthiness_settings', $trustworthinessData, $trustworthinessId);
}

//------------------Ajax Call for trusthworthiness data saved-------------------------


//------------------Cron job -------------------------

// Activation function
function SCRPlugin_your_plugin_activate() {
    // Schedule cron job on activation
    if (!wp_next_scheduled('your_cron_job')) {
        // Schedule the cron job to run daily at midnight (12AM)
        wp_schedule_event(strtotime('midnight'), 'daily', 'your_cron_job');
    }
}

// Deactivation function
function SCRPlugin_your_plugin_deactivate() {
    // Remove scheduled cron job on deactivation
    wp_clear_scheduled_hook('your_cron_job');
}

// Cron job callback function
function SCRPlugin_your_cron_job_callback() {

    $wedgetFilterData =  SCRPlugin_widgetFilterSettingsTable();
    $brandData = SCRPlugin_brandSettingsTable();
    $apiTableData = SCRPlugin_apiSettingsTable();
    $trustworthinessData = SCRPlugin_trustworthinessSettingsTable();
    $data = [
        'wedgetFilterData' => $wedgetFilterData,
        'brandData' => $brandData,
        ]; 
    
    
    if($brandData){
    // Make API call
        // $apiKey = $apiTableData->api;
        $apiKey = isset($apiTableData->api) ? sanitize_text_field($apiTableData->api) : '';
        $brandName = isset($brandData->brand_name) ? sanitize_text_field($brandData->brand_name) : '';
        $extraBrandIdentifiers = isset($brandData->extra_brand_identifiers) ? sanitize_text_field($brandData->extra_brand_identifiers) : '';
        $platforms = $brandData->extra_review_platforms == 1 ? 'rating' : '';

        // Validate API Key (example: check if not empty)
        if (empty($apiKey)) {
            // Handle validation failure (e.g., set default value or error message)
            $apiKey = ''; // or handle error appropriately
        }
        // Validate Brand Name (example: check if not empty)
        if (empty($brandName)) {
            // Handle validation failure (e.g., set default value or error message)
            $brandName = ''; // or handle error appropriately
        }
        // Validate Extra Brand Identifiers (example: check if not empty)
        if (empty($extraBrandIdentifiers)) {
            // Handle validation failure (e.g., set default value or error message)
            $extraBrandIdentifiers = ''; // or handle error appropriately
        }
        // Validate Platforms (example: check if the value is as expected)
        if ($platforms !== 'rating' && $platforms !== '') {
            // Handle validation failure (e.g., set default value or error message)
            $platforms = ''; // or handle error appropriately
        }
        
        $api_url = "https://serpapi.com/search.json?api_key={$apiKey}&google_domain=google.com&num=100&q={$brandName}+{$extraBrandIdentifiers}+%22{$platforms}%22";
        // Use wp_remote_get to make the API call
        $response = wp_remote_get($api_url);
        
        
        if(is_wp_error($response)) {
            
            $response = wp_remote_get($api_url);
            
            if(is_wp_error($response)) {
                $test = 'Api response error';
            }else{
                if (!is_wp_error($response) && $response['response']['code'] === 200) {
                    $test = SCRPlugin_apiSuccess($response, $data);
                }else{
                    $test = SCRPlugin_apiError($response);
                }
            }
            
        }else{
            
            
             if (!is_wp_error($response) && $response['response']['code'] === 200) {
                    $test = SCRPlugin_apiSuccess($response, $data);
                }else{
                    $test = SCRPlugin_apiError($response);
                }
            
        }
        
        
    // Prepare data for the first table (star_collector_api_settings)
    $apiSettingsData = array('test' => $test);

    SCRPlugin_insertOrUpdateData('SCRPlugin_api_settings', $apiSettingsData, $apiTableData->id);
    }
    
}

// Hook into the 'your_cron_job' action
add_action('your_cron_job', 'SCRPlugin_your_cron_job_callback');

// Custom cron schedule for every day at midnight
function SCRPlugin_your_custom_cron_schedule($schedules) {
    $schedules['daily'] = array(
        'interval' => 24 * 60 * 60, // 24 hours in seconds
        'display'  =>  __('Every 24 Hours at Midnight', 'star-collector-review-widgets'),
    );
    return $schedules;
}
add_filter('cron_schedules', 'SCRPlugin_your_custom_cron_schedule');


//------------------Cron job -------------------------


//------------------Frontend Widgets -------------------------


// Check if the user is accessing the website from a mobile device
function SCRPlugin_isMobileDevice() {
    // Validate and sanitize the user agent string
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING);

        // Define a regex pattern for mobile devices
        $mobile_device_pattern = "/(android|iphone|ipad|ipod|blackberry|windows phone)/i";

        // Check if the user agent matches the mobile device pattern
        $is_mobile_device = preg_match($mobile_device_pattern, $user_agent) ? true : false;

        return $is_mobile_device;
    } else {
        return false; // HTTP_USER_AGENT is not set or empty
    }
}


// Add the floating div to the footer
function SCRPlugin_floating_widget() {
    $apiData = SCRPlugin_apiSettingsTable();
    $widgetDisplayData = SCRPlugin_widgetDisplaySettingsTable();
    $brandData = SCRPlugin_brandSettingsTable();
    $brandRatingData = SCRPlugin_brandRatingTable();
    $widgetFilterData = SCRPlugin_widgetfilterSettingsTable();
    $trustworthinessData = SCRPlugin_trustworthinessSettingsTable();
    
    if(!empty($apiData)){
    if(!empty($brandData) AND !empty($brandRatingData)){
    
    // Widget Display setup variable
    $backgroundColor = $widgetDisplayData->display_mode == 1 ? 'background-light' : 'background-dark';
    // $imageFolder = plugin_dir_url(__FILE__) . 'images/';
     $imageFolder = esc_url(plugin_dir_url(__FILE__)) . 'images/';
    $reviewise = $imageFolder.'reviewise.png';
    $reviewiseWhite = $imageFolder.'reviewise-white.png';
    $verifiedTick = $imageFolder.'tick.png';
    $backgroundColorImage = $widgetDisplayData->display_mode == 1 ? $reviewise : $reviewiseWhite;
    $backgroundColorFooter = $widgetDisplayData->display_mode == 1 ? 'verified-by-light' : 'verified-by-dark';
    //santized results
    $results = SCRPlugin_getSelectedRatings();
    // $brandName = $brandData->brand_name;
    $brandName = isset($brandData->brand_name) ? esc_html($brandData->brand_name) : '';
    $style = '';
    $ratingCount = [];

    //review variable
    $reviewValues = array_column($results, 'review');
    $sumReviews = array_sum($reviewValues);
    // $sumReviews = number_format($sumReviews);
    $sumReviews = number_format_i18n($sumReviews);
    
    //rating variable
    foreach($results as $rating){
        if($rating->rating_out_of == 10){
           $ratingVal = $rating->rating / 2;
           $ratingVal = floatval($ratingVal);
           array_push($ratingCount, $ratingVal);
           
        }else{
            $ratingVal = $rating->rating;
            $ratingVal = floatval($ratingVal);
            array_push($ratingCount, $ratingVal);
        } 
    }
    $sumRating = array_sum($ratingCount);
    $averageRating = $sumRating/count($ratingCount);
    $averageRating = round($averageRating, 1);
    
    // $displayStyleHanlder = $widgetDisplayData->display_style;
    $displayStyleHanlder = intval($widgetDisplayData->display_style);
  
     if (SCRPlugin_isMobileDevice()) {
       $displayStyleHanlder = 7;
    }
     // Map database values to corresponding class names
     switch ($displayStyleHanlder) {
        case 1:
             wp_localize_script('star-collector-frontend-script', 'ratingData', array(
            'averageRating' => $averageRating,
            'star' => 5,
            'font' => 20,
        ));
            
            $class_name = 'floating-divA';
            $style .= '
                        <div class="brand-name">' . esc_html($brandName) . '</div>
                        <div class="divider"></div>
                        <div class="rating-score">' . esc_html($averageRating) . '/5</div>
                        <div id="rater"></div>
                        <div class="review-count">' . esc_html($sumReviews) . ' ' . esc_html__('reviews', 'star-collector-review-widgets') . '</div>
                        <div class="divider"></div>';
                    
                    foreach ($results as $result) {
                        $style .= '<img src="' . esc_url($result->favicon) . '" class="social" title="' . esc_attr($result->source) . '">';
                    }
            break;
        case 2:
            
            $class_name = 'floating-divB';
            $style = '<div class="brand-name">' . esc_html($brandName) . '</div>';
            $style .= '<div class="review-count">' . esc_html($sumReviews) . ' ' . esc_html__('reviews', 'star-collector-review-widgets') . '</div>';
            $style .= '<table>';
            $i = 1;
            $ratingDataArray = array(); 
            foreach ($results as $result) {
                 if($result->rating_out_of == 10){
        $ratingVal = $result->rating / 2;
    } else {
        $ratingVal = $result->rating;
    } 

    $ratingDataArray[] = array(
        'averageRating' => $ratingVal,
        'ratingId' => 'rater-' . $i,
        'increment' => $i,
        'star' => 1,
        'font' => 18,
    );
                $style .= '<tr>';
                $style .= '<td class="rating-score">' . esc_html($result->rating) . '/' . esc_html($result->rating_out_of) . '</td>';
                $style .= '<td>';
                $style .= '<div id="rater-' . esc_html($i) . '"></div>';
                $style .= '</td>';
                $style .= '<td>';
                $style .= '<img src="' . esc_url($result->favicon) . '" class="social" title="' . esc_html($result->source) . '">';
                $style .= '</td>';
                $style .= '<td>';
                $style .= '<span class="amount" title="' . esc_html($result->source) . '">' . esc_html($result->source) . '</span>';
                $style .= '</td>';
                $style .= '</tr>';
                $i++;
            }
            $style .= '</table>';
            wp_localize_script('star-collector-one-star', 'ratingData', array(
            'ratingDataArray' => $ratingDataArray,
        ));
            break;
        case 3:
            $class_name = 'floating-divC';
            $style = '<div class="brand-name">' . esc_html($brandName) . '</div>';
            $style .= '<div class="review-count">' . esc_html($sumReviews) . ' ' . esc_html__('reviews', 'star-collector-review-widgets') . '</div>';
            $style .= '<table>';
            $style .= '<table>';
            $i = 1;
            $ratingDataArray = array(); 

            foreach ($results as $result) {
                
                if($result->rating_out_of == 10){
                    $ratingVal = $result->rating / 2;
                } else {
                    $ratingVal = $result->rating;
                } 
            
                $ratingDataArray[] = array(
                    'averageRating' => $ratingVal,
                    'ratingId' => 'rater-' . $i,
                    'increment' => $i,
                    'star' => 1,
                    'font' => 18,
                );
                
                $style .= '<tr>';
                $style .= '<td class="rating-score">' . esc_html($result->rating) . '/' . esc_html($result->rating_out_of) . '</td>';
                $style .= '<td><div id="rater-' . esc_html($i) . '"></div></td>';
                $style .= '<td><img src="' . esc_url($result->favicon) . '" class="social" title="' . esc_html($result->source) . '"></td>';
                $style .= '<td class="amount">' . esc_html($result->review) . '</td>';
                $style .= '</tr>';
                $i++;
            }
           
            $style .= '</table>';
            wp_localize_script('star-collector-one-star', 'ratingData', array(
            'ratingDataArray' => $ratingDataArray,
        ));
            
            break;
        case 4:
            $class_name = 'floating-divD';
            $style = '<div class="brand-name">' . esc_html($brandName) . '</div>';
            $style .= '<div class="review-count">' . esc_html($sumReviews) . ' ' . esc_html__('reviews', 'star-collector-review-widgets') . '</div>';
            $style .= '<table>';
            $i = 1;
            $ratingDataArray = array(); 

            foreach ($results as $result) {
                
                if($result->rating_out_of == 10){
                    $ratingVal = $result->rating / 2;
                } else {
                    $ratingVal = $result->rating;
                } 
            
                $ratingDataArray[] = array(
                    'averageRating' => $ratingVal,
                    'ratingId' => 'rater-' . $i,
                    'increment' => $i,
                    'star' => 5,
                    'font' => 17,
                );
                
                $style .= '<tr>';
                
                // Social Icons Column
                $style .= '<td>';
                $style .= '<div class="socials">';
                $style .= '<img src="' . esc_url($result->favicon) . '" class="social" title="' . esc_html($result->source) . '">';
                $style .= '</div>';
                $style .= '</td>';
                
                // Amount Column
                $style .= '<td>';
                $style .= '<div class="amount" title="' . esc_html($result->source) . '">' . esc_html($result->source) . '</div>';
                $style .= '</td>';
                
                // Stars Column
                $style .= '<td><div id="rater-' . esc_html($i) . '"></div></td>';
                
                // Rating Score Column
                $style .= '<td class="rating-score">' . esc_html($result->rating) . '/' . esc_html($result->rating_out_of) . '</td>';
                
                $style .= '</tr>';
                 $i++;
            }

            $style .= '</table>';
            
             wp_localize_script('star-collector-one-star', 'ratingData', array(
            'ratingDataArray' => $ratingDataArray,
        ));
            
            break;
        case 5:
            $class_name = 'floating-divE';
            $style = '<div class="brand-name">' . esc_html($brandName) . '</div>';
            $style .= '<div class="review-count">' . esc_html($sumReviews) . ' ' . esc_html__('reviews', 'star-collector-review-widgets') . '</div>';
            $style .= '<table>';
             $i = 1;
            $ratingDataArray = array(); 

            foreach ($results as $result) {
                
                if($result->rating_out_of == 10){
                    $ratingVal = $result->rating / 2;
                } else {
                    $ratingVal = $result->rating;
                } 
            
                $ratingDataArray[] = array(
                    'averageRating' => $ratingVal,
                    'ratingId' => 'rater-' . $i,
                    'increment' => $i,
                    'star' => 5,
                    'font' => 17,
                );
                
                $style .= '<tr>';
                
                // Social Icons Column
                $style .= '<td>';
                $style .= '<div class="socials">';
                $style .= '<img src="' . esc_url($result->favicon) . '" class="social" title="' . esc_html($result->source) . '">';
                $style .= '</div>';
                $style .= '</td>';
                
                // Amount Column
                $style .= '<td class="rating-score">' . esc_html($result->rating) . '/' . esc_html($result->rating_out_of) . '</td>';
                
                // Stars Column
                $style .= '<td><div id="rater-' . esc_html($i) . '"></div></td>';
                
                // Rating Score Column
                $style .= '<td>';
                $style .= '<div class="rating-score">' . esc_html($result->review) . '</div>';
                $style .= '</td>';
                
                $style .= '</tr>';
                $i++;
            }

            $style .= '</table>';
             wp_localize_script('star-collector-one-star', 'ratingData', array(
            'ratingDataArray' => $ratingDataArray,
            ));
            break;
        case 6:
            $class_name = 'floating-divF';
            $style = '<div class="brand-name">' . esc_html($brandName) . '</div>';
            $style .= '<div class="review-count">' . esc_html($sumReviews) . ' ' . esc_html__('reviews', 'star-collector-review-widgets') . '</div>';
            $style .= '<table>';
            $i = 1;
            $ratingDataArray = array(); 

            foreach ($results as $result) {
                
                if($result->rating_out_of == 10){
                    $ratingVal = $result->rating / 2;
                } else {
                    $ratingVal = $result->rating;
                } 
            
                $ratingDataArray[] = array(
                    'averageRating' => $ratingVal,
                    'ratingId' => 'rater-' . $i,
                    'increment' => $i,
                    'star' => 5,
                    'font' => 16,
                );
                $style .= '<tr>';
                
                // Social Icons Column
                $style .= '<td>';
                $style .= '<div class="socials">';
                $style .= '<img src="' . esc_url($result->favicon) . '" class="social" title="' . esc_html($result->source) . '">';
                $style .= '</div>';
                $style .= '</td>';
                
                // Amount Column
                 $style .= '<td class="rating-score">' . esc_html($result->rating) . '/' . esc_html($result->rating_out_of) . '</td>';
                
                // Stars Column
               $style .= '<td><div id="rater-' . esc_html($i) . '"></div></td>';
                
                $style .= '</tr>';
                $i++;
            }
            $style .= '</table>';
             wp_localize_script('star-collector-one-star', 'ratingData', array(
            'ratingDataArray' => $ratingDataArray,
            ));
            break;
        case 7:
             wp_localize_script('star-collector-frontend-script', 'ratingData', array(
            'averageRating' => $averageRating,
            'star' => 1,
            'font' => 22,
            ));
            
            $class_name = 'floating-divG';
            $style .= '<div class="brand-name">' . esc_html($brandName) . '</div>';
            $style .= '<div class="divider"></div>';
            $style .= '<div class="rating-score">' . esc_html($averageRating) . '/5</div>';
            $style .= '<div id="rater"></div>';
            $style .= '<div class="review-count">' . esc_html($sumReviews) . ' ' . esc_html__('reviews', 'star-collector-review-widgets') . '</div>';
            $style .= '<div class="divider"></div>';
            foreach ($results as $result) {
                     $style .= '<img src="' . esc_url($result->favicon) . '" class="social" title="' . esc_html($result->source) . '">';
                 }
            break;
        default:
             wp_localize_script('star-collector-frontend-script', 'ratingData', array(
            'averageRating' => $averageRating,
            'star' => 5,
            'font' => 20,
        ));
            
            $class_name = 'floating-divA';
                    $style .= '<div class="brand-name">' . esc_html($brandName) . '</div>';
                    $style .= '<div class="divider"></div>';
                    $style .= '<div class="rating-score">' . esc_html($averageRating) . '/5</div>';
                    $style .= '<div id="rater"></div>';
                    $style .= '<div class="review-count">' . esc_html($sumReviews) . ' ' . esc_html__('reviews', 'star-collector-review-widgets') . '</div>';
                    $style .= '<div class="divider"></div>';
                    
                    foreach ($results as $result) {
                        $style .= '<img src="' . esc_url($result->favicon) . '" class="social" title="' . esc_html($result->source) . '">';
                    }
                    
            break;
    }
            
            // Map database values to corresponding class names
        switch ($widgetDisplayData->display_position) {
        case 1:
            $position_class = 'position-middle-left';
            break;
        case 2:
            $position_class = 'position-middle-right';
            break;
        case 3:
            $position_class = 'position-bottom-left';
            break;
        case 4:
            $position_class = 'position-bottom-right';
            break;
        default:
            $position_class = 'position-bottom-left'; // Default to 'bottom-left' if the value is not found
            break;
    }
    
    // Check if we are on a single post page
        $is_single_post = is_single();
        
        //trustworthiness checks
         $reviewise_badge = $trustworthinessData->reviewise_badge == 1 ? '<div class="' . esc_attr($backgroundColorFooter) . '"><img src="' . esc_url($verifiedTick) . '" class="verified-tick">' . esc_html__('Verified by', 'star-collector-review-widgets') . ' <a href="https://www.reviewise.co" target="_blank"><img src="' . esc_url($backgroundColorImage) . '" class="verified-by-img" alt="' . esc_attr__('review management software', 'star-collector-review-widgets') . '"></a></div>' : '';


    // for post
      if ($is_single_post && $widgetFilterData && $widgetFilterData->all_posts == 1) {
    ?>
    <div class="star-collector-review">
        <div class="<?php echo esc_attr($class_name); ?> <?php echo esc_attr($position_class); ?> <?php echo esc_attr($backgroundColor); ?>">
            <div class="main">
                <?php echo wp_kses_post($style); ?>
            </div>
            <?php echo wp_kses_post($reviewise_badge); ?>
        </div>
        </div>
    <?php
    }
        
      // for pages
     if (!$is_single_post  &&  $widgetFilterData && $widgetFilterData->all_pages == 1) {
    ?>
    <div class="star-collector-review">
        <div class="<?php echo esc_attr($class_name); ?> <?php echo esc_attr($position_class); ?> <?php echo esc_attr($backgroundColor); ?>">
            <div class="main">
                <?php echo wp_kses_post($style); ?>
            </div>
            <?php echo wp_kses_post($reviewise_badge); ?>
        </div>
        </div>
    <?php
    }
    
     $specific_urls = $widgetFilterData->specific_url ? explode(',', str_replace(' ', '', $widgetFilterData->specific_url)) : array();
    
    // Using server variables with sanitization
    $protocol = is_ssl() ? 'https://' : 'http://';
    // Sanitize and validate HTTP_HOST
    $host = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_STRING);
    // Sanitize and validate REQUEST_URI
    $uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    // Construct current_page_url
    $current_page_url = $protocol . $host . $uri;

    $is_specific_url = !empty($specific_urls) && in_array($current_page_url, $specific_urls);
      // for specific pages
    if ($is_specific_url) {
    ?>
    <div class="star-collector-review">
        <div class="<?php echo esc_attr($class_name); ?> <?php echo esc_attr($position_class); ?> <?php echo esc_attr($backgroundColor); ?>">
            <div class="main">
                <?php echo wp_kses_post($style); ?>
            </div>
            <?php echo wp_kses_post($reviewise_badge); ?>
            </div>
        </div>
        </div>
    <?php
    }
    }
    }
}

add_action('wp_footer', 'SCRPlugin_floating_widget');

//------------------Frontend Widgets -------------------------


//------------------Plugin Online/Offline Signal -------------------------
add_action('admin_bar_menu', 'SCRPlugin_custom_admin_bar_logo', 999);

function SCRPlugin_custom_admin_bar_logo($wp_admin_bar) {
    $apiData = SCRPlugin_apiSettingsTable();
        $imageFolder = esc_url(plugin_dir_url(__FILE__) . 'images/');
        if ($apiData) {
            $status = $testData = $apiData->test == null ? $imageFolder . 'online.svg' : $imageFolder . 'offline.svg';
            $statusText = $testData = $apiData->test == null ? __('SerpApi (Online) > SCRW', 'star-collector-review-widgets') : __('SerpApi (Error) > SCRW', 'star-collector-review-widgets');
        } else {
            $status = $imageFolder . 'offline.svg';
            $statusText = __('SerpApi (Error) > SCRW', 'star-collector-review-widgets');
        }
        $url = esc_url(admin_url('admin.php?page=SCRPlugin_admin_page#settings-tab'));
        // Adding the custom image to the admin bar
        $wp_admin_bar->add_node(array(
            'id' => 'custom-admin-logo',
            'title' => '<div class="custom-admin-logo-container"><a href="' . $url . '" class="custom-admin-logo-link"><img src="' . $status . '" style="max-height: 20px; max-width: 20px;margin-right: 5px;"> ' . esc_html($statusText) . '</a></div>',
            'meta' => array(
                'class' => 'custom-admin-logo',
            ),
        ));
        
}

//------------------Plugin Online/Offline Signal -------------------------