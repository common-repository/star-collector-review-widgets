<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//------------------insert or update data in the database-------------------------
 //function to insert or update data in the database
function SCRPlugin_insertOrUpdateData($table, $data, $where) {
    global $wpdb;
   
    $table = $wpdb->prefix . $table;
     
    if ($where) {
       $wpdb->update($table, $data, array('id' => $where));
      return $where;
    } else {
        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }
}
//------------------insert or update data in the database-------------------------


//------------------fetch API Settings data-------------------------

function SCRPlugin_apiSettingsTable() {
    global $wpdb;
    $table_name_api_settings = $wpdb->prefix . 'SCRPlugin_api_settings';
   
   // Prepare and execute the query
    $query = $wpdb->prepare("SELECT * FROM $table_name_api_settings LIMIT %d", 1);
    $result = $wpdb->get_row($query);

    // Sanitize each field in the result
    if ($result) {
        $result->api = sanitize_text_field($result->api);
        $result->test = sanitize_text_field($result->test);

        // Validate each field
        if (!empty($result->api) && !preg_match('/^[a-zA-Z0-9_]+$/', $result->api)) {
            // Invalid API field, handle error
            echo "";
        }

        if (!empty($result->test) && !preg_match('/^[a-zA-Z0-9_]+$/', $result->test)) {
            // Invalid test field, handle error
            echo "";
        }
        else {
            $result->test = '';
        }
    }
    return $result;
}

//------------------fetch API Settings data-------------------------


//------------------fetch Widget Display Settings data-------------------------

function SCRPlugin_widgetDisplaySettingsTable() {
    global $wpdb;
    $table_name_display_settings = $wpdb->prefix . 'SCRPlugin_widget_display_settings';
    
    // Prepare and execute the query
    $query = $wpdb->prepare("SELECT * FROM $table_name_display_settings LIMIT %d", 1);
    $result = $wpdb->get_row($query);
    
    // Sanitize each field in the result
     if ($result) {
        $result->display_position = intval($result->display_position);
        $result->display_mode = intval($result->display_mode);
        $result->display_style = intval($result->display_style);

        // Validate
        if (!is_int($result->display_position)) {
            $result->display_position = 1;
        }

        if (!is_int($result->display_mode)) {
            $result->display_mode = 1;
        }

        if (!is_int($result->display_style)) {
            $result->display_style = 1;
        }
    }
    return $result;
}

//------------------fetch Widget Display Settings data-------------------------


//------------------fetch Widget Filter Settings data-------------------------

function SCRPlugin_widgetFilterSettingsTable() {
    global $wpdb;
    $table_name_filter_settings = $wpdb->prefix . 'SCRPlugin_widget_filter_settings';
    
    // Prepare and execute the query
    $query = $wpdb->prepare("SELECT * FROM $table_name_filter_settings LIMIT %d", 1);
    $result = $wpdb->get_row($query);
    
    // Sanitize each field in the result
    if ($result) {
        $result->min_rating_1_5 = floatval($result->min_rating_1_5); // Sanitize as float
        $result->min_rating_1_10 = floatval($result->min_rating_1_10); // Sanitize as float
        $result->min_reviews = intval($result->min_reviews); // Sanitize as integer
        $result->all_posts = intval($result->all_posts); // Sanitize as integer (0 or 1)
        $result->all_pages = intval($result->all_pages); // Sanitize as integer (0 or 1)
        $result->specific_url_checkbox = intval($result->specific_url_checkbox); // Sanitize as integer (0 or 1)
        $result->specific_url = sanitize_textarea_field($result->specific_url); // Sanitize as textarea
        $result->exclude_review_platform_domains = sanitize_textarea_field($result->exclude_review_platform_domains); // Sanitize as textarea
        $result->exclude_admin_domains = sanitize_textarea_field($result->exclude_admin_domains); // Sanitize as textarea

         // Validate each field
        if (!is_float($result->min_rating_1_5)) {
            $result->min_rating_1_5 = 0.0; // Default value if invalid
        }

        if (!is_float($result->min_rating_1_10)) {
            $result->min_rating_1_10 = 0.0; // Default value if invalid
        }

        if (!is_int($result->min_reviews)) {
            $result->min_reviews = 0; // Default value if invalid
        }

        if (!in_array($result->all_posts, [0, 1], true)) {
            $result->all_posts = 0; // Default value if invalid
        }

        if (!in_array($result->all_pages, [0, 1], true)) {
            $result->all_pages = 0; // Default value if invalid
        }

        if (!in_array($result->specific_url_checkbox, [0, 1], true)) {
            $result->specific_url_checkbox = 0; // Default value if invalid
        }
    }
    return $result;
}

//------------------fetch Widget Filter Settings data-------------------------


//------------------fetch Trustworthiness Settings data-------------------------

function SCRPlugin_trustworthinessSettingsTable() {
    global $wpdb;
    $table_name_trustworthiness_settings = $wpdb->prefix . 'SCRPlugin_trustworthiness_settings';
    
     // Prepare and execute the query
    $query = $wpdb->prepare("SELECT * FROM $table_name_trustworthiness_settings LIMIT %d", 1);
    $result = $wpdb->get_row($query);
    
     // Sanitize each field in the result
     if ($result) {
        $result->reviewise_badge = intval($result->reviewise_badge);

        // Validate the reviewise_badge field
        if (!is_int($result->reviewise_badge)) {
            $result->reviewise_badge = 0; // Default value if invalid
        }
    }

    return $result;
}

//------------------fetch Trustworthiness Settings data-------------------------

//------------------fetch Brand Settings data-------------------------

function SCRPlugin_brandSettingsTable() {
    global $wpdb;
    $table_name_brand_settings = $wpdb->prefix . 'SCRPlugin_brand_settings';
    
     // Prepare and execute the query
    $query = $wpdb->prepare("SELECT * FROM $table_name_brand_settings LIMIT %d", 1);
    $result = $wpdb->get_row($query);
    
     // Sanitize each field in the result
    if ($result) {
        $result->brand_name = sanitize_text_field($result->brand_name); // Sanitize as text field
        $result->extra_brand_identifiers = sanitize_text_field($result->extra_brand_identifiers); // Sanitize as text field
        $result->extra_review_platforms = intval($result->extra_review_platforms); // Sanitize as integer (0 or 1)

        // Validate brand_name
        if (empty($result->brand_name)) {
            $result->brand_name = ''; // Handle empty case as needed
        }
        // Validate extra_brand_identifiers
        if (empty($result->extra_brand_identifiers)) {
            $result->extra_brand_identifiers = ''; // Handle empty case as needed
        }
        // Validate extra_review_platforms
        if (!is_int($result->extra_review_platforms)) {
            $result->extra_review_platforms = 0;
        }
    }
    return $result;
}

//------------------fetch Brand Settings data-------------------------

//------------------fetch Brand Rating data-------------------------

function SCRPlugin_brandRatingTable() {
    global $wpdb;
    $table_name_rating_settings = $wpdb->prefix . 'SCRPlugin_ratings';
    
    $results = $wpdb->get_results("SELECT * FROM $table_name_rating_settings");
    
    if ($results) {
        foreach ($results as $result) {
            $result->rating = sanitize_text_field($result->rating); // Sanitize as text field
            $result->rating_out_of = intval($result->rating_out_of); // Sanitize as integer
            $result->review = sanitize_textarea_field($result->review); // Sanitize as textarea field
            $result->source = sanitize_text_field($result->source); // Sanitize as text field
            $result->favicon = esc_url_raw($result->favicon); // Sanitize as URL
            $result->brand_id = intval($result->brand_id); // Sanitize as integer
            $result->status = intval($result->status); // Sanitize as integer

            // Validate rating (assuming rating should be a non-empty string)
            if (empty($result->rating)) {
                $result->rating = ''; // Handle invalid case as needed
            }

            // Validate rating_out_of
            if (!is_int($result->rating_out_of)) {
                $result->rating_out_of = 0;
            }

            // Validate review
            if (empty($result->review)) {
                $result->review = ''; // Handle invalid case as needed
            }

            // Validate source
            if (empty($result->source)) {
                $result->source = ''; // Handle invalid case as needed
            }

            // Validate favicon (assuming favicon should be a valid URL)
            if (!filter_var($result->favicon, FILTER_VALIDATE_URL)) {
                $result->favicon = ''; // Handle invalid URL case
            }

            // Validate brand_id
            if (!is_int($result->brand_id)) {
                $result->extra_review_platforms = 0;
            }

            if (!is_int($result->status)) {
                $result->extra_review_platforms = 0;
            }
        }
    }
    return $results;
}

//------------------fetch Brand Rating data-------------------------


//------------------Create checkboxes for rating-------------------------
function SCRPlugin_display_brand_ratings_checkbox() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'SCRPlugin_ratings';

    // Retrieve data from the table
    $ratings = $wpdb->get_results("SELECT id, rating, review, source, favicon, status, rating_out_of FROM $table_name", ARRAY_A);
    // Check if there are ratings
    if ($ratings) {
        // Loop through ratings and display checkboxes
        foreach ($ratings as $rating) {
            $rating_id = intval($rating['id']); // Sanitize as integer
            $rating_value = sanitize_text_field($rating['rating']); // Sanitize as text field
            $review_value = sanitize_textarea_field($rating['review']); // Sanitize as textarea field
            $source_value = sanitize_text_field($rating['source']); // Sanitize as text field
            $favicon_value = esc_url($rating['favicon']); // Sanitize as URL
            $status_value = intval($rating['status']); // Sanitize as integer
            $ratingOutOf_value = intval($rating['rating_out_of']); // Sanitize as integer

            // Validate rating_id
            if (!is_int($rating_id)) {
                $rating_id = 1; 
            }

            // Validate rating_value (assuming it should be a non-empty string)
            if (empty($rating_value)) {
                $rating_value = ''; // Handle invalid case as needed
            }

            // Validate review_value (assuming it should be a non-empty string)
            if (empty($review_value)) {
                $review_value = ''; // Handle invalid case as needed
            }

            // Validate source_value (assuming it should be a non-empty string)
            if (empty($source_value)) {
                $source_value = ''; // Handle invalid case as needed
            }

            // Validate favicon_value (assuming favicon should be a valid URL)
            if (!filter_var($favicon_value, FILTER_VALIDATE_URL)) {
                $favicon_value = ''; // Handle invalid URL case
            }

            // Validate status_value
            if (!is_int($status_value)) {
                $status_value = 0; 
            }

            // Validate ratingOutOf_value
            if (!is_int($ratingOutOf_value)) {
                $ratingOutOf_value = 5; 
            }

            // Check if the rating is selected (you'll need to modify this part based on your logic)
            $checked = $status_value ==1 ? 'checked' : ''; // You need to set this based on your logic
            $valData = $rating_value.'|'.$ratingOutOf_value.'|'.$review_value.'|'.$source_value.'|'.$favicon_value;
            // Display the checkbox
             echo '<div style="margin-bottom: 10px;"><label style="display: flex; align-items: center;">' .
            '<img src="' . esc_url($favicon_value) . '" style="width: 15px; margin-left: 5px; margin-right: 5px;" alt="' . esc_attr__('Logo', 'star-collector-review-widgets') . '">' .
            '<input style="margin-top: 0px;" type="checkbox" class="ratingCheckbox" name="rating_checkbox[]" value="' . esc_attr($valData) . '" ' . $checked . '> ' .
            esc_html($source_value . " - " . __('Rating', 'star-collector-review-widgets') . ": $rating_value/$ratingOutOf_value - " . __('Reviews', 'star-collector-review-widgets') . ": $review_value") .
            '</label></div>';
        }
    } else {
            echo esc_html__('No review platforms found yet / Review your filter settings.', 'star-collector-review-widgets');
    }
}

//------------------Create checkboxes for rating-------------------------


function SCRPlugin_trustworthiness_form($trustworthinessData){
    ?>
     <!-- Tab 5 Trustworthiness -->
            <div class="star-collector-review" id="tab5-content">
                 <h3 class=""><b>Trustworthiness Settings</b></h3>
                   <?php
                        // Assuming you have fetched values from the database for the checkboxes
                       $reviewiseBadge = ($trustworthinessData->reviewise_badge == 1) ? 'checked' : '';
                        ?>
                  
                  <input type="checkbox" name="reviewise_badge" id="reviewise-badge" class="reviewise-badge" value="" <?php echo wp_kses_post($reviewiseBadge); ?>>
                    <label for="reviewise-badge"><?php esc_html_e("Enable 'Verified By RevieWise' Badge link to Enhance Credibility and Trustworthiness.", 'star-collector-review-widgets'); ?></label>
                       
                    <input type='hidden' name="trustworthiness_id" value="<?php echo isset($trustworthinessData->id) ? esc_attr($trustworthinessData->id) : ''; ?>">
            </div>
            
            <?php
}


//------------------Create checkboxes for rating-------------------------


//------------------Getting selected rating from the list-------------------------

function SCRPlugin_getSelectedRatings(){
global $wpdb;

$table_name = $wpdb->prefix . 'SCRPlugin_ratings';

// Query to retrieve rows with status value 1
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_name WHERE status = %d",
        1
    )
);

 // Sanitize each field in the results
    foreach ($results as $result) {
        $result->rating = floatval($result->rating); // Sanitize as float
        $result->rating_out_of = intval($result->rating_out_of); // Sanitize as integer
        $result->review = sanitize_text_field($result->review); // Sanitize as text field
        $result->source = sanitize_text_field($result->source); // Sanitize as text field
        $result->favicon = esc_url($result->favicon); // Sanitize as URL
        $result->status = intval($result->status); // Sanitize as integer

        // Validate rating
        if (!is_float($result->rating)) {
            $result->rating = 0.0; // Default value or handle invalid case
        }

        // Validate rating_out_of
        if (!is_int($result->rating_out_of)) {
            $result->rating_out_of = 5; 
        }

        // Validate review (assuming review should be a non-empty string)
        if (empty($result->review)) {
            $result->review = ''; // Handle invalid case as needed
        }

        // Validate source (assuming source should be a non-empty string)
        if (empty($result->source)) {
            $result->source = ''; // Handle invalid case as needed
        }

        // Validate favicon (assuming favicon should be a valid URL)
        if (!filter_var($result->favicon, FILTER_VALIDATE_URL)) {
            $result->favicon = ''; // Handle invalid URL case
        }

        // Validate status
        if (!is_int($result->status)) {
            $result->status = 0; 
        }
    }

return $results;

}

//------------------Getting selected rating from the list-------------------------


//------------------Common function for api rating-------------------------


function SCRPlugin_apiSuccess($response, $data){
     $wedgetFilterData = $data['wedgetFilterData'];
     $brandData = $data['brandData'];
     $api_data = json_decode($response['body'], true);
             
    $ratingArray = [];
    $imageFolder = esc_url(plugin_dir_url(__FILE__) . 'images/');
    
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
            $favicon = isset($result['favicon']) ? esc_url($result['favicon']) : $imageFolder . 'no-image.png';
            
            if (($ratingOutOf == 5 && $rating < $wedgetFilterData->min_rating_1_5) || ($ratingOutOf == 10 && $rating < $wedgetFilterData->min_rating_1_10)) {
                continue;
            }

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
    $sortRating[$key] = filter_var($row['rating'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

// Sort the data by rating
array_multisort($sortRating, SORT_DESC, $ratingArray);


  $topRatedRecords =$ratingArray;

//google image
if (isset($api_data['knowledge_graph'])) {
// Sanitize and validate the Google rating as a float
if (isset($api_data['knowledge_graph']['rating'])) {
    $googleRating = filter_var($api_data['knowledge_graph']['rating'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    if (filter_var($googleRating, FILTER_VALIDATE_FLOAT) === false) {
        $googleRating = null; // or handle the invalid value as needed
    }
} else {
    $googleRating = null; // or handle the missing value as needed
}

// Sanitize and validate the Google review count as an integer
if (isset($api_data['knowledge_graph']['review_count'])) {
    $googleReview = filter_var($api_data['knowledge_graph']['review_count'], FILTER_SANITIZE_NUMBER_INT);
    if (filter_var($googleReview, FILTER_VALIDATE_INT) === false) {
        $googleReview = null; // or handle the invalid value as needed
    }
} else {
    $googleReview = null; // or handle the missing value as needed
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
        }
        
        
          global $wpdb;

foreach ($topRatedRecords as $topRatedRecord) {
    // Assuming $topRatedRecord['source'] contains the source value you want to check
    $source = sanitize_text_field($topRatedRecord['source']);

    // Check if a record with the given source already exists
    $existingRecord = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}SCRPlugin_ratings WHERE source = %s", $source),
        ARRAY_A
    );
    
    if ($existingRecord) {
        // Record exists, update review, votes, and rating
        // Sanitize and validate the review as a string
        if (isset($topRatedRecord['review'])) {
            $newReview = sanitize_text_field($topRatedRecord['review']);
            
        } else {
            $newReview = null; // or handle the missing value as needed
        }

        // Sanitize and validate the votes as an integer
        if (isset($topRatedRecord['votes'])) {
            $newVotes = filter_var($topRatedRecord['votes'], FILTER_SANITIZE_NUMBER_INT);
            if (filter_var($newVotes, FILTER_VALIDATE_INT) === false) {
                $newVotes = null; // or handle the invalid value as needed
            }
        } else {
            $newVotes = null; // or handle the missing value as needed
        }

        // Sanitize and validate the rating as a float
        if (isset($topRatedRecord['rating'])) {
            $newRating = filter_var($topRatedRecord['rating'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            if (filter_var($newRating, FILTER_VALIDATE_FLOAT) === false) {
                $newRating = null; // or handle the invalid value as needed
            }
        } else {
            $newRating = null; // or handle the missing value as needed
        }

        // Sanitize and validate the favicon as a URL string
        if (isset($topRatedRecord['favicon'])) {
            $newFavicon = filter_var($topRatedRecord['favicon'], FILTER_SANITIZE_URL);
            if (filter_var($newFavicon, FILTER_VALIDATE_URL) === false) {
                $newFavicon = null; // or handle the invalid value as needed
            }
        } else {
            $newFavicon = null; // or handle the missing value as needed
        }

        // Update review, votes, and rating in a single line
        $wpdb->update("{$wpdb->prefix}SCRPlugin_ratings",array(
            'review' => is_null($newReview) ? $newVotes : $newReview,
            'rating' => $newRating,
            'favicon' => $newFavicon,
            ),
            array('source' => $source),
            array(
                '%s', // review
                '%s', // rating
                '%s', // favicon
            ),
            array('%s') // source
        );
    } else {


        // Sanitizing for HTML and other contexts
        $rating = filter_var($topRatedRecord['rating'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $review = is_null($topRatedRecord['review']) ? filter_var($topRatedRecord['votes'], FILTER_SANITIZE_NUMBER_INT) : htmlspecialchars($topRatedRecord['review'], ENT_QUOTES, 'UTF-8');
        $rating_out_of = filter_var($topRatedRecord['rating_out_of'], FILTER_SANITIZE_NUMBER_INT);
        $source = htmlspecialchars($source, ENT_QUOTES, 'UTF-8');
        $favicon = filter_var($topRatedRecord['favicon'], FILTER_SANITIZE_URL);
        $brand_id = filter_var($brandData->id, FILTER_SANITIZE_NUMBER_INT);

        // Validate rating (assuming it should be a float within a specific range, e.g., 0.0 to 5.0)
        if (!is_numeric($rating)) {
            $rating = 0.0; // Default value or handle invalid case
        }
        // Validate review (assuming it should be a non-empty string if it's not a vote count)
        if (!is_null($topRatedRecord['review']) && empty($review)) {
            $review = ''; // Handle invalid case as needed
        }
        // Validate rating_out_of (assuming it should be within a specific range, e.g., 1 to 10)
        if ($rating_out_of < 1 || $rating_out_of > 10) {
            $rating_out_of = 1; // Default value or handle out-of-range case
        }
        // Validate source (assuming it should be a non-empty string)
        if (empty($source)) {
            $source = ''; // Handle invalid case as needed
        }
        // Validate favicon (assuming it should be a valid URL)
        if (!filter_var($favicon, FILTER_VALIDATE_URL)) {
            $favicon = ''; // Handle invalid URL case
        }
        // Validate brand_id (assuming it should be a positive integer)
        if ($brand_id <= 0) {
            $brand_id = 1; // Default value or handle invalid case
        }

        
        // Record does not exist, create it in the database
        $wpdb->insert("{$wpdb->prefix}SCRPlugin_ratings",array(
        'rating' => $rating,
        'review' => $review,
        'rating_out_of' => $rating_out_of,
        'source' => $source,
        'favicon' => $favicon,
        'brand_id' => $brand_id,
    ),
    array(
        '%s', // rating
        '%s', // review
        '%d', // rating_out_of
        '%s', // source
        '%s', // favicon
        '%d'  // brand_id
    )
);
    }
}
// After processing API data, check for records in the database that are not present in the API data
    
    $placeholders = array_fill(0, count($topRatedRecords), '%s');
    $placeholder_string = implode(', ', $placeholders);
    $placeholders_values = array_column($topRatedRecords, 'source');
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}SCRPlugin_ratings WHERE source NOT IN ($placeholder_string)",
        $placeholders_values
    ));
    
              $test = null;
}


function SCRPlugin_apiError($response){
     if (is_wp_error($response)) {
            $test = 'Api response error';
        }else{
             $response = json_decode($response['body'], true);
            if($response){
                $test = $response['error'];
            }else{
                $test = 'Unknown Error!';
            }
        }
        return $test;
}

//------------------Common function for api rating-------------------------


//------------------Sanitize API-------------------------

function SCRPlugin_sanitize_api_data($data) {
    if (is_array($data)) {
        foreach ($data as &$value) {
            $value = SCRPlugin_sanitize_api_data($value); // Recursively sanitize nested arrays
        }
        return $data;
    } else {
        return is_string($data) ? sanitize_text_field($data) : $data; // Sanitize strings only
    }
}

//------------------Sanitize API-------------------------



//------------------Sanitize Checkbox-------------------------
function SCRPlugin_sanitize_checked_value($value) {
    // Explode the string by pipe character to get individual values
    $parts = explode('|', $value);
    
      // Sanitize each part individually without trimming whitespace
    $sanitized_parts = array_map(function ($part) {
        return wp_kses($part, array()); // Using wp_kses for basic sanitization without trimming whitespace
    }, $parts);

    // Implode the sanitized parts back into a string separated by pipe character
    return implode('|', $sanitized_parts);
}
//------------------Sanitize Checkbox-------------------------