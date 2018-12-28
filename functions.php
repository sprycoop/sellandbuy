<?php
/*
 * functions.php
 * 
 */

require_once( __DIR__ . '/includes/functions-custom-post-types.php');

// Get google API key from settings
$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

// Dont load emoji script
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

function my_theme_enqueue_styles() {
    //wp_enqueue_style('parent-style', get_stylesheet_directory_uri() . '/style.css');    Not needed as the theme already does it properly
    wp_enqueue_style('danny-style', get_stylesheet_directory_uri() . '/style-danny.css');
    wp_enqueue_style('responsive-style', get_stylesheet_directory_uri() . '/style-responsive.css');
    wp_enqueue_script('bootstrap', get_stylesheet_directory_uri() . '/mobile_header/js/bootstrap.min.js', array('jquery'));
}

// Random string for coupons
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Menu location
// Add Your Menu Locations
function register_my_menus() {
    register_nav_menus(
            array(
                'menu_myaccount' => __('My Account Menu'),
                'menu_main' => __('Main Menu'),
                'header-language-nav' => __('Top Menu Language'),
                'header-mobile-nav' => __('Top Menu Mobile'),
                'header-help' => __('Top Menu Help'),
            )
    );
}

add_action('init', 'register_my_menus');

// Get the top parent of a category
function get_term_top_most_parent($term_id, $taxonomy) {
    $parent = get_term_by('id', $term_id, $taxonomy);
    while ($parent->parent != 0) {
        $parent = get_term_by('id', $parent->parent, $taxonomy);
    }
    return $parent;
}

function redirect_failed_payments() {
    $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    if (strpos($actual_link, 'cancel_order') !== false) {

        $order = new WC_Order($_GET['order_id']);
        if ($order->status == "cancelled") {
            $order->update_status('wc-pending', 'order_note'); // order note is optional, if you want to  add a note to order
        }
        $order_key = $order->get_order_key();

        $link = site_url() . "/my-account/view-order/?order=" . $_GET['order_id'] . "&v=vdr&payment=failed";
        header('Location: ' . $link);
    }
}

// Write log
if (!function_exists('write_log')) {

    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

}

// Load header menu
function show_header_menu() {
    // HEADER TOP MENU
    ?>
    <div class="top-nav-container header-top-nav top-nav-main header-user-menu">
        <?php
        wp_nav_menu(array('theme_location' => 'header-top-nav'));
        ?>
    </div>
    <?php
}

// Load header menu
function show_header_menu_mobile() {
    // HEADER TOP MENU MOBILE
    ?>
    <div class="top-nav-container header-mobile-nav top-mobile-main header-user-menu-mobile">

        <?php
        wp_nav_menu(array('theme_location' => 'header-mobile-nav'));
        ?>
    </div>
    <?php
}

function show_register_login_link() {
    if (!is_user_logged_in()) {
        ?>
        <div id="header-login">
            <div class="item header-register-link"><?php _e("Register", "surfsnb"); ?></div>
            <div class="item">&nbsp; &nbsp; |&nbsp; &nbsp; </div>
            <div class="item header-login-link"><?php _e("Login", "surfsnb"); ?></div>
        </div>
        <?php
    }
}

function show_register_login_link_mobile() {
    if (!is_user_logged_in()) {
        ?>
        <li>

            <div id="header-login" style="display:block;">
                <div class="item header-register-link"><?php _e("Register", "surfsnb"); ?></div>
                <div class="item">&nbsp; &nbsp; |&nbsp; &nbsp; </div>
                <div class="item header-login-link"><?php _e("Login", "surfsnb"); ?></div>
            </div>

        </li>
        <?php
    }
}

// Get language code by post ID
function langcode_post_id($post_id) {
    global $wpdb;

    $query = $wpdb->prepare('SELECT language_code FROM ' . $wpdb->prefix . 'icl_translations WHERE element_id="%d"', $post_id);
    $query_exec = $wpdb->get_row($query);

    return $query_exec->language_code;
}

// Checks if a post is the original or not
function wpml_is_original($post_id = 0, $type = 'post_post') {
    global $post, $sitepress;

    $output = array();

    // use current post if post_id is not provided
    $p_ID = $post_id == 0 ? $post->ID : $post_id;

    $el_trid = $sitepress->get_element_trid($p_ID, $type);
    $el_translations = $sitepress->get_element_translations($el_trid, $type);

    if (!empty($el_translations)) {
        $is_original = FALSE;
        foreach ($el_translations as $lang => $details) {
            if ($details->original == 1 && $details->element_id == $p_ID) {
                $is_original = TRUE;
            }
            if ($details->original == 1) {
                $original_ID = $details->element_id;
            }
        }
        $output['is_original'] = $is_original;
        $output['original_ID'] = $original_ID;
    }
    return $output;
}

// FROM HERE THE FUNCTIONS ARE INDEPENDANT AND DON'T INFLUENCE THE THEME, CAN BE DISABLED FOR SPEED TESTS.

function mobile_header_keyword_search() {
    if (wp_is_mobile()) {

        $keyword_val = (isset($_REQUEST["keyword"])) ? $_REQUEST["keyword"] : "";

        echo '<input class="banner_keyword_search" id="keyword" name="keyword" placeholder="Keyword search..." type="text" value="' . $keyword_val . '"/>';
    } else {

        echo '<input class="banner_keyword_search" name="keyword" placeholder="Keyword search..." type="text" value="' . $keyword_val . '"/>';
    }
}

// Duplicate products
add_action('wcv_save_product', 'wpml_duplicate_on_publish', 10, 1);

//add_action( 'save_post', 'wpml_duplicate_on_publish' ); // Also on normal post save
function wpml_duplicate_on_publish($post_id) {

    // Add lat/lng fields to product
	write_log("First we add the lat/lng fields to the product");
	
	$productID = $post_id;

	$vendor_id = get_post_field('post_author', $productID); //write_log("Vendor ID: ".$vendor_id);
	$org_street_address = get_user_meta($vendor_id, 'billing_address_1', true);
	$org_postcode = get_user_meta($vendor_id, 'billing_postcode', true);
	$org_city = get_user_meta($vendor_id, 'billing_city', true);
	$org_state = get_user_meta($vendor_id, 'billing_state', true);
	$org_country = get_user_meta($vendor_id, 'billing_country', true);
	$org_country = WC()->countries->countries[$org_country];

	$street_address = str_replace(" ", "+", $org_street_address); //write_log("Address: ".$street_address); //google doesn't like spaces in urls, but who does? 
	$city = str_replace(" ", "+", $org_city); //write_log("City: ".$city);
	$state = str_replace(" ", "+", $org_state); //write_log("State: ".$state);
	$url = "https://maps.googleapis.com/maps/api/geocode/json?address=$street_address,+$city,+$state&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs";
	$google_api_response = wp_remote_get($url);

	$results = json_decode($google_api_response['body']); //grab our results from Google
	$results = (array) $results; //cast them to an array

	write_log("Maps Array Result " . $productID . ": " . print_r($results, 1));

	$status = $results["status"]; //easily use our status
	$location_all_fields = (array) $results["results"][0];
	$location_geometry = (array) $location_all_fields["geometry"];
	$location_lat_long = (array) $location_geometry["location"];
	echo "<!-- GEOCODE RESPONSE ";
	var_dump($location_lat_long);
	echo " -->";
	if ($status == 'OK') {
		$latitude = $location_lat_long["lat"];
		$longitude = $location_lat_long["lng"];
	} else {
		$latitude = '';
		$longitude = '';
	}

	if ($location_all_fields["formatted_address"] !== "") {
		$address = $location_all_fields["formatted_address"];
		write_log("Address: " . $address);
	} else {
		$address = $org_street_address . ", " . $org_postcode . " " . $org_city . ", " . $org_country;
	}

	$value = array(
		'address' => $address,
		'lat' => $latitude,
		'lng' => $longitude,
		'zoom' => 16
	);

	write_log("Maps Array " . $productID . ": " . print_r($value, 1));

	//$value = array("address" => $address, "lat" => $lat, "lng" => $lng, "zoom" => $zoom);
	update_field("field_5a62fe5f92629", $value, $productID);

	// Update seperate lat/lng fields that are used for search on location / distance
	update_post_meta($productID, "lat", $latitude);
	update_post_meta($productID, "lng", $longitude);
	
	// Start duplicating products
	write_log("Going to duplicate");

    global $sitepress, $iclTranslationManagement, $post;

    // don't save for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    write_log("Still going 1 - Post type: " . get_post_type($post_id));

    // only save for products
    if (get_post_type($post_id) && get_post_type($post_id) != 'product') {
        return $post_id;
    }

    write_log("Still going 2");

    // Change to alert when title is Alert
    $post = get_post($post_id);
    $post_title = get_the_title($post);
    if ($post_title == "Alert") {

        // Update post_type
        set_post_type($post_id, "alerts");

        write_log("It's an alert");
    } else {

        remove_action('wcv_save_product', 'wpml_duplicate_on_publish');

        write_log("Stopped the wcv_save_product action for now: " . get_post_modified_time($post_id) . " and " . get_post_time($post_id));

        // Only create duplicate posts when a new post is created
        if (get_post_modified_time($post_id) == get_post_time($post_id)) {

            write_log("Start - Making duplicates now");

            // Make duplicates of the psot
            do_action('wpml_make_post_duplicates', $post_id);

            write_log("End - Making duplicates now");
        }

        // Sync all data to translations
        do_action("my_product_update", $post_id);
    }
}

// Update all translations from front-end product edit form WCV
add_action("my_product_update", "my_product_update");

function my_product_update($post_id) {

    // Get post
    $post = get_post($post_id);

    //write_log("Post ID: ".$post_id);

    if ($post->post_type == "product") {

        // Get Array with ID's of translations
        global $sitepress;
        $trid = $sitepress->get_element_trid($post_id);
        $translation = $sitepress->get_element_translations($trid);

        foreach ($translation as $key => $data) {

            //$translation_id = $data->element_id;
            $new_post_id = $data->element_id;

            // Only for translations, not the original post
            if ($new_post_id != $post_id) {

                // get all current post terms ad set them to the new post draft
                $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
                foreach ($taxonomies as $taxonomy) {
                    $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                    wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                }

                remove_action('wcv_save_product', 'my_product_update');

                // Update title and content
                $content_post = get_post($post_id);
                $my_post = array(
                    'ID' => $new_post_id,
                    'post_title' => get_the_title($post_id), //$post->post_title
                    'post_content' => $content_post->post_content,
                        //'post_author' 	=> $post_author, WORK AUTOMATICALLY
                );
                wp_update_post($my_post);

                // Update min accepted offer price
                $offer_accepted_price = get_field("wcv_custom_product_minimum_accepted_offers", $post_id);
                if ($offer_accepted_price !== "") {
                    update_field("field_57becc6db14e4", $offer_accepted_price, $new_post_id);
                }

                // Update the featured image
                $thumbnail_id = get_post_thumbnail_id($post_id);
                set_post_thumbnail($new_post_id, $thumbnail_id);

                // Update the product galery
                $product_id = $post_id;
                $product = new WC_product($product_id);
                $attachment_ids = $product->get_gallery_image_ids();
                $i = 0;
                foreach ($attachment_ids as $attachment_id) {
                    if ($i == 0) {
                        $gallery .= $attachment_id;
                    } else {
                        $gallery .= "," . $attachment_id;
                    }
                    $i++;
                }

                if ($gallery !== "") {
                    update_post_meta($new_post_id, "_product_image_gallery", $gallery);
                }

                add_action('wcv_save_product', 'my_product_update');
            }
        }
    }
}

function show_comments_new($productID) {

    if ($productID) {
        $postIDD = $productID;
    } else {
        $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $postIDD = url_to_postid($url);
    }

    $current_user = wp_get_current_user();

    $args = array(
        'include_unapproved' => $current_user->ID,
        'post_id' => $postIDD,
        'status' => 'approve'
    );

    // The Query
    global $sitepress;
    remove_filter('comments_clauses', array($sitepress, 'comments_clauses'), 10, 2);
    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query($args);
    //When you are done with displaying comments add filter again
    add_filter('comments_clauses', array($sitepress, 'comments_clauses'), 10, 2);   // COPY THIS TO SELLER-VIEW-ORDER PAGE???

    if ($comments) :
        ?>

        <ol class='commentlist'>
        <?php wp_list_comments($args, $comments); ?>
        </ol>

        <?php
        if (get_comment_pages_count() > 1 && get_option('page_comments')) :
            echo '<nav class="woocommerce-pagination">';
            paginate_comments_links(apply_filters('woocommerce_comment_pagination_args', array(
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
                'type' => 'list',
            )));
            echo '</nav>';
        endif;
        ?>

    <?php else : ?>

        <p class="woocommerce-noreviews"><?php _e('There are no questions yet.', 'woocommerce'); ?></p>

    <?php
    endif;
}

// Don't show the comment form if the current user is the seller OR when the product has been sold OR when not logged in
function hide_comment_form() {
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $postID = url_to_postid($url);
    $current_user = wp_get_current_user();
    $item = get_post($postID);
    $vendor = $item->post_author;
    //echo "Test7: <br>itemID: ".$itemID."<br><pre>".print_r($item,1)."</pre><br>Vendor: ".$vendor."<br>Current User: ".$current_user->ID;
    if ($current_user->ID == $vendor) {

        //echo "<span style='color:Red;'>Hide comment_form</span>";
        ?>
        <script>
            jQuery("#review_form_wrapper").css({display: "none"});
        </script>
        <?php
    }

    // If user is not logged in
    if (empty($current_user->ID)) {
        ?>
        <script>
            jQuery("#review_form_wrapper").css({display: "none"});
        </script>
        <?php
    }

    // If user is logged in
    if (!empty($current_user->ID)) {
        ?>
        <script>
            jQuery("#need_to_login_to_comment").css({display: "none"});
        </script>
        <?php
    }

    // If product has no stock
    global $product;
    $product = WC()->product_factory->get_product($item, $args);
    if ($product->get_stock_quantity() <= 0) {
        ?>
        <script>
            jQuery("#review_form_wrapper").css({display: "none"});
        </script>
        <?php
    }

    // If product has stock
    if ($product->get_stock_quantity() > 0) {
        ?>
        <script>
            jQuery("#product_sold").css({display: "none"});
        </script>
        <?php
    }
}

// Add attribute Country and Seller
add_action('wcv_save_product', 'on_post_publish', 20);

function on_post_publish($post_id) {

    global $woocommerce;

    $post = get_post($post_id);
    $author_id = $post->post_author;
    $usermeta = get_user_meta($author_id);
    $country_code = $usermeta['billing_country'][0];
    $country = $woocommerce->countries->countries[$country_code];

    //echo "Usermeta: <pre>".print_r($usermeta,1)."</pre></br>";
    //echo "Country: ".$country."</br>";

    $user_meta = get_userdata($author_id);
    if ($user_meta->user_mp_status == "business") {
        $seller_type = "Pro Shop";
    } else {
        $seller_type = "Privat";
    }

    $product_attributes = get_post_meta($post_id, '_product_attributes');
    $product_attributes = $product_attributes[0];

    //echo "<pre>".print_r($product_attributes,1)."</pre>";

    $custom_attributes = array();
    if (isset($country)) {
        $custom_attributes = array('pa_country' => array('name' => 'pa_country',
                'value' => $country,
                'position' => 0,
                'is_visible' => 1,
                'is_variation' => 1,
                'is_taxonomy' => 1
            ),
            'pa_seller' => array('name' => 'pa_seller',
                'value' => $seller_type,
                'position' => 0,
                'is_visible' => 1,
                'is_variation' => 1,
                'is_taxonomy' => 1
        ));
    }

    foreach ($product_attributes as $key => $value) {

        $custom_attributes[$key] = $value;
    }

    //echo "<pre>".print_r($custom_attributes,1)."</pre>";

    $term_taxonomy_ids = wp_set_object_terms($post_id, $country, 'pa_country', true);
    $term_taxonomy_ids = wp_set_object_terms($post_id, $seller_type, 'pa_seller', true);

    update_post_meta($post_id, '_product_attributes', $custom_attributes);

    //return $id;
}

/* OTHER FUNCTIONS */

// Enqueue JS for custom page templates
function enqueue_page_template_js() {
	
	global $post;
    $post_slug = $post->post_name;
	
    // Seller-view-order
    if (is_page_template('page-templates-surfsnb/sellerViewOrder.php')) {
        wp_enqueue_script('enqueue_sellervieworder_js', get_stylesheet_directory_uri() . '/js/sellerViewOrder.js', array('jquery'), null, true);
    }

    // View-order
    if (is_page_template('page-templates-surfsnb/viewOrder.php')) {
        wp_enqueue_script('enqueue_sellervieworder_js', get_stylesheet_directory_uri() . '/js/viewOrder.js', array('jquery'), null, true);
    }
	
	// Front page
    if (is_front_page()) {
        wp_enqueue_script('enqueue_googlemaps_js', 'https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs&libraries=places');
    }
	
	// Map page
    if (is_page_template('demo.php')) {
        wp_enqueue_script('enqueue_googlemaps_js', 'https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs&libraries=places');
    }
	
	// Checkout page
    if(is_checkout()){	
		//wp_dequeue_script( 'woo-country-select');
		//wp_deregister_script('woo-country-select');
		
		//wp_dequeue_script( 'wc-country-select');
		//wp_deregister_script('wc-country-select');
		
		wp_enqueue_script('enqueue_googlemaps_js', 'https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs&libraries=places');
	}
	
	// Maps test
    if(is_page_template('page-test.php')){
		wp_enqueue_script('enqueue_googlemaps_js', 'https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs&libraries=places');
	}
	
	// Profile
    if($post_slug == "profile"){
		wp_enqueue_script('enqueue_googlemaps_js', 'https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs&libraries=places');
	}
}

add_action('wp_enqueue_scripts', 'enqueue_page_template_js');

// Show different button if item is already in the cart
//
// Change the add to cart text on single product pages
//
add_filter('woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text');

function woo_custom_cart_button_text() {

    foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
        $_product = $values['data'];

        if (get_the_ID() == $_product->get_id()) {
            return __('Already in cart', 'woocommerce');
        }
    }

    return __('Make a request', 'woocommerce');
}

//
// Change the add to cart text on product archives
//
/* add_filter('woocommerce_product_add_to_cart_text', 'woo_archive_custom_cart_button_text');

  function woo_archive_custom_cart_button_text() {

  global $woocommerce;

  // Iterate over real cart and work with subscription products (if any)
  foreach ($woocommerce->cart->cart_contents as $cart_item_key => $values) {
  $_product = $values['data'];

  if (get_the_ID() == $_product->get_id()) {
  return __('Already in cart', 'woocommerce');
  }
  }

  return __('Request to buy', 'woocommerce');
  } */

// Check if product is in cart
function woo_in_cart($product_id) {
    global $woocommerce;

    foreach ($woocommerce->cart->get_cart() as $key => $val) {
        $_product = $val['data'];

        if ($product_id == $_product->get_id()) {
            return true;
        }
    }

    return false;
}

// Replace name in menu
function give_profile_name() {
    $user = wp_get_current_user();
    $name = $user->display_name;
    return $name;
}

add_shortcode('profile_name', 'give_profile_name');

add_filter('wp_nav_menu_objects', 'my_dynamic_menu_items');

function my_dynamic_menu_items($menu_items) {

    foreach ($menu_items as $menu_item) {

        // Change ratings url
        if ('#ratings#' == $menu_item->url) {
            $menu_item->url = site_url() . "/shops/" . $current_user->user_login . "/ratings/";
        }

        // Add redirect_to to Login, but not to register
        if (strpos($menu_item->url, 'login') !== false AND strpos($menu_item->url, 'register') == false) {

            // Set link
            $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            // Check if not exists
            if (empty($_GET['redirect_to']) AND strpos($actual_link, 'login') == false) {

                $query = parse_url($menu_item->url, PHP_URL_QUERY);

                // Returns a string if the URL has parameters or NULL if not
                if ($query) {
                    $menu_item->url .= '&redirect_to=' . $actual_link;
                } else {
                    $menu_item->url .= '?redirect_to=' . $actual_link;
                }
            }
        }

        // Change logout url
        if (strpos($menu_item->url, 'logout') !== false) {

            $menu_item->url = wp_logout_url();
        }

        // Change profile name
        if ('#profile_name#' == $menu_item->title) {
            global $shortcode_tags;
            if (isset($shortcode_tags['profile_name'])) {
                // Or do_shortcode(), if you must.
                $user_id = get_current_user_id();
                $user_data = get_userdata($user_id);
                $attachement_id = get_user_meta($user_id, "profile_picture", true);
                if ($attachement_id) {
                    $avatar = wp_get_attachment_image($attachement_id, array(40, 40));
                } else {
                    $avatar = '<img src="' . site_url() . '/wp-content/uploads/2016/12/person-default.png" style="margin-top:-5px;" alt="" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 photo avatar-default">';
                }

                // Notifications
                $notifications = 0;

                // Basic data filled in
                $billing_city = get_user_meta($user_id, "billing_city", true);
                if ($billing_city == "") { // Should be == normally and != for testing	
                    $notifications++;
                }

                // Profile picture
                if (!$attachement_id) { // Should be with ! normally and without for testing
                    $notifications++;
                }

                $current_user = wp_get_current_user();
                ;
                $args = array('post_type' => 'notifications',
                    'posts_per_page' => '-1',
                    'author' => $current_user->ID,
                );
                $posts = get_posts($args);
                $notification_count = count($posts);
                $notifications = $notifications + $notification_count;

                if ($notifications < 10) {
                    $notification_style = "notification_number";
                } else {
                    $notification_style = "notification_number_double";
                }

                $menu_item->title = "<div class='menu-profile-pic'>" . $avatar . "</div>"
                        . "<div class='menu-profile-name'>" . call_user_func($shortcode_tags['profile_name']) . "</div>"
                        . "<div style='float:left;width:25px;margin-top: 2px;margin-right: 5px;'><img src='/wp-content/uploads/2016/12/orange-dot.png' class='orange_dot'>"
                        . "<div class='" . $notification_style . "'>" . $notifications . "</div></div>";
            }
        }
    }

    return $menu_items;
}

// De-register admin css from WooCommerce
add_action('wp_print_styles', 'my_deregister_styles', 100);

function my_deregister_styles() {
    wp_deregister_style('woocommerce_admin_styles');
}

// Remove element with value
function removeElementWithValue($array, $excludes, $key) {
    foreach ($array as $subKey => $subArray) {
        //echo "<pre>".print_r($subArray,1)."</pre>";
        foreach ($excludes as $exclude) {
            if ($subArray->$key == $exclude) {
                unset($array[$subKey]);
            }
        }
    }
    return $array;
}

// Number of thumbnails in Product gallery
add_filter('woocommerce_product_thumbnails_columns', 'xx_thumb_cols');

function xx_thumb_cols() {
    return 4; // .last class applied to every 4th thumbnail
}

// Avatar
function shortcode_user_avatar($atts, $content = null) {
    extract(shortcode_atts(
                    array('id' => '0',), $atts
            )
    );

    return get_avatar($user_id, 96); // display the specific user_id's avatar  
}

add_shortcode('avatar', 'shortcode_user_avatar');

// allow users to add images to their home page
function allow_own_attachments($user_caps, $req_caps, $args, $UserObj) {
    if (empty($args[2])) {
        return $user_caps; // nothing to check
    }
    $post = get_post($args[2]); // post_id was passed here
    if ($post->post_author == $UserObj->ID) { // this is my post
        foreach ((array) $req_caps as $cap) {
            if (empty($user_caps[$cap]))
                $user_caps[$cap] = true;
        }
    }
    $user_caps[‘edit_post’] = true; // tested by wp_ajax_upload_attachment()
    return $user_caps;
}

add_filter("user_has_cap", "allow_own_attachments", 10, 4);
// end
// Manage stock
add_action('save_post_product', 'myWoo_savePost', 10, 3);

function myWoo_savePost($postID, $post, $update) {
    if (!$update) {

        //  $update is false if we're creating a new post
        update_post_meta($post->ID, '_manage_stock', 'yes');
        update_post_meta($post->ID, '_stock', '1');
    }
}

// Remove breadcrumbs on single product pages
add_action('init', 'jk_remove_wc_breadcrumbs');

function jk_remove_wc_breadcrumbs() {
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
}

// Remove quantity
// @desc Remove in all product type
function wc_remove_all_quantity_fields($return, $product) {
    return true;
}

add_filter('woocommerce_is_sold_individually', 'wc_remove_all_quantity_fields', 10, 2);

// goes in theme functions.php or a custom plugin. Replace the image filename/path with your own :)
add_action('init', 'custom_fix_thumbnail');

function custom_fix_thumbnail() {
    add_filter('woocommerce_placeholder_img_src', 'custom_woocommerce_placeholder_img_src');

    function custom_woocommerce_placeholder_img_src($src) {
        $upload_dir = wp_upload_dir();
        $uploads = untrailingslashit($upload_dir['baseurl']);
        $src = site_url() . '/wp-content/uploads/2016/10/placeholder_new.png';

        return $src;
    }

}

// Change lost password URL
add_filter('lostpassword_url', 'my_lost_password_page', 10, 2);

function my_lost_password_page($lostpassword_url, $redirect) {
    return home_url('/recover-password/?redirect_to=' . $redirect);
}

function array_swap(&$array, $swap_a, $swap_b) {
    list($array[$swap_a], $array[$swap_b]) = array($array[$swap_b], $array[$swap_a]);
}

// Change the entry title of the endpoints that appear in My Account Page - WooCommerce 2.6
// Using the_title filter
function wpb_woo_endpoint_title($title, $id) {
    if (is_wc_endpoint_url('orders') && in_the_loop()) {
        $title = __("My requests", "surfsnb");
    }
    return $title;
}

add_filter('the_title', 'wpb_woo_endpoint_title', 10, 2);

// SSL
function ssl_srcset($sources) {
    foreach ($sources as &$source) {
        $source['url'] = set_url_scheme($source['url'], 'https');
    }
    return $sources;
}

add_filter('wp_calculate_image_srcset', 'ssl_srcset');

// Add shipping to cart
add_filter('woocommerce_cart_needs_shipping', '__return_true');

// Ship to a different address closed by default
add_filter('woocommerce_ship_to_different_address_checked', '__return_false');

//
// Add a 1% surcharge to your cart / checkout
// change the $percentage to set the surcharge to a value to suit
// Uses the WooCommerce fees API
//
// Add to theme functions.php
//
add_action('woocommerce_cart_calculate_fees', 'woocommerce_custom_surcharge');

function woocommerce_custom_surcharge() {
    global $woocommerce;

    if (is_admin() && !defined('DOING_AJAX'))
        return;

    $percentage = 0.03;
    $surcharge = ( $woocommerce->cart->cart_contents_total + $woocommerce->cart->shipping_total ) * $percentage;
    $woocommerce->cart->add_fee('SurfSnB Fee 3%', $surcharge, true, '');
}

// Only allow to buy from 1 vendor at a time
add_filter('woocommerce_add_cart_item_data', 'woo_custom_add_to_cart');

function woo_custom_add_to_cart($cart_item_data) {

    // Only when $_POST add-to-cart is set. From the shipping_calculator we don't need to check anymore
    if (empty($_POST['add-to-cart']))
        return false;

    global $woocommerce;

    $items = $woocommerce->cart->get_cart(); //getting cart items

    $_product = array();

    foreach ($items as $item => $values) {
        $_product[] = $values['data']->post;
    }

    if (isset($_product[0]->ID)) { //getting first item from cart
        $prodId = (int) $_POST['add-to-cart'];
        $product_in_cart_vendor_id = get_post_field('post_author', $_product[0]->ID);
        $product_added_vendor_id = get_post_field('post_author', $prodId);

        if ($product_in_cart_vendor_id !== $product_added_vendor_id) {
            $woocommerce->cart->empty_cart();
        }

        return $cart_item_data;
    }
}

// Multidimensional array
function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

// Set product field
function set_product_field_in_order($order_id, $productID, $currentFieldSlug, $input, $field_key, $result) {

    $currentField = get_field($currentFieldSlug, $order_id);

    // Take the post ID of the english post
    $productID = icl_object_id($productID, 'product', false, 'en');

    // Check if field already contains
    if (strpos($currentField, (string) $productID) !== false) {
        // Replace
        $posStart = strpos($currentField, (string) $productID);
        $posEnd = strpos($currentField, (string) $productID, $posStart) + strpos($currentField, ',', $posStart) - $posStart;
        if ($posEnd == -1) {

            // Change
            $posEnd = strpos($currentField, ',', $posStart) - $posStart;

            // Still zero
            if ($posEnd == 0) {

                // Then just take the length as posEnd
                $posEnd = $posStart + strlen($currentField);
            }
        }

        $characters = $posEnd - $posStart + 1;

        // Find part to remove
        $toReplace = substr($currentField, $posStart, $characters);

        // Remove
        $currentField = str_replace($toReplace, "", $currentField);

        // Now add
        if (empty($currentField)) {
            $currentField = $productID . "|" . $input . ",";
        } else {
            $currentField = $currentField . $productID . "|" . $input . ",";
        }
        // Doesn't contain yet
    } else {
        // Just add
        if (empty($currentField)) {
            $currentField = $productID . "|" . $input . ",";
        } else {
            $currentField = $currentField . $productID . "|" . $input . ",";
        }
    }
    update_field($field_key, $currentField, $order_id);

    if ($result == true) {
        //echo "ProductID: ".$productID."<br>";
        //echo "PosStart: ".$posStart."<br>";
        //echo "PosEnd: ".$posEnd."<br>";
        //echo "Characters: ".$characters."<br>";
        //echo "ToReplace: ".$toReplace."<br>";
        //echo "Return: ".$currentField."<br><Br><br><Br>";
    }
}

//function get_product_status($getFromSlug, $order_id, $productID, $echo){
function get_product_field_from_order($getFromSlug, $order_id, $productID, $echo) {

    $getFromString = get_field($getFromSlug, $order_id);


    //echo "Order ID: ".$order_id."<br>";
    //echo "Product ID: ".$productID."<br>";
    //echo "getFromSlug: ".$getFromSlug."<br>";
    //echo "getFromString: ".$getFromString."<br>";
    // Take the post ID of the english post		
    $productID = icl_object_id($productID, 'product', false, 'en');

    if (empty($productID)) {

        // Product doesn't exist anymore
        //_e("For some reason this product doesn't exist anymore", "surfsnb");	
    } else {

        //echo "Product ID: ".$productID."<br>";
        // Only get value if productID exists in string
        if (strpos($getFromString, (string) $productID) !== false) {

            // Length of the $productID number
            $orderIDlength = strlen($productID);   //echo "Length: ".$orderIDlength."<br>";
            //echo "Length: ".$getFromString."<br>";

            if (!empty($getFromString)) {
                // Take orderID length and skip the -
                $skipNrCharactersFromFront = $orderIDlength + 1;
            } else {
                $skipNrCharactersFromFront = 0;
            }

            $posStart = strpos($getFromString, (string) $productID) + $skipNrCharactersFromFront;
            $posEnd = strpos($getFromString, (string) $productID, ( $posStart - $skipNrCharactersFromFront)
                    ) + strpos($getFromString, ',', $posStart
                    ) - ($posStart - $orderIDlength);
            // If postEnd equal to -1
            if ($posEnd == -1) {

                // Change
                $posEnd = strpos($getFromString, ',', $posStart) - $posStart;

                // Still zero
                if ($posEnd == 0) {

                    //echo "this one<br>";				
                    // Then just take the length as posEnd
                    $posEnd = $posStart + strlen($getFromString);
                }
            }

            $characters = $posEnd - $posStart + 1;
            $return = substr($getFromString, $posStart, $characters);

            if ($echo == true) {
                //echo "GetFromString: ".$getFromString."<br>";
                //echo "ProductID: ".$productID."<br>";
                //echo "PosStart: ".$posStart."<br>";
                //echo "PosEnd: ".$posEnd."<br>";
                //echo "Characters: ".$characters."<br>";
                //echo "Result: ".$return."<br><br>"; 
            }
        }
    }

    return $return;
}

// Add parent categories as well on post save
add_action('save_post', 'assign_parent_terms', 10, 2);

function assign_parent_terms($post_id, $post) {

    if ($post->post_type != 'product')
        return $post_id;

    // get all assigned terms   
    $terms = wp_get_post_terms($post_id, 'product_cat');
    foreach ($terms as $term) {
        while ($term->parent != 0 && !has_term($term->parent, 'product_cat', $post)) {
            // move upward until we get to 0 level terms
            wp_set_post_terms($post_id, array($term->parent), 'product_cat', true);
            $term = get_term($term->parent, 'product_cat');
        }
    }
}

function get_order_statusses_seller($order_id) {

    global $woocommerce, $post;

    $order = new WC_Order($order_id);

    //echo "Order: <pre>".print_r($order,1)."</pre>";

    if ($order->get_status() == 'cancelled') {

        // Order status is cancelled
        $notice_text = __("The request has been cancelled", "surfsnb");
    } else {

        // Product confirmation, shipping proposal
        $items = $order->get_items();
        $count = 0;
        $total = 0;
        foreach ($items as $item) {
            //echo "<pre>".print_r($item,1)."</pre>";
            //$seller_confirmation = get_field("status",$item["product_id"]);
            $seller_confirmation = get_product_field_from_order("product_status", $order_id, $item["product_id"], false);
            //$shipping_split = get_field("shipping_split",$item["product_id"]);
            $shipping_split = get_product_field_from_order("product_shipping_split", $order_id, $item["product_id"], false);
            //$shipping_fee = get_field("shipping_fee",$item["product_id"]);
            $shipping_fee = get_product_field_from_order("product_shipping_fee", $order_id, $item["product_id"], false);

            //echo "Sellers confirmation ".$item["product_id"].": ".$seller_confirmation."<br>";
            if ($seller_confirmation == "Confirm" AND ( shipping_method_is_pickup($order_id) || shipping_method_is_express($order_id) )) {
                $count++;
            } elseif ($seller_confirmation == "Confirm" AND ! empty($shipping_split) AND ! empty($shipping_fee)) {
                $count++;
            }
            $total++;
        }

        if ($count == 0) {

            if (shipping_method_is_pickup($order_id)) {
                $notice_text = __("Please accept/deny this request. You can make a pick-up appointment after the buyer has paid.", "surfsnb");
            } elseif (shipping_method_is_express($order_id)) {
                $shipping_method_array = get_shipping_method($order_id);
                $shipping_method = $shipping_method_array['name'] . " €" . $shipping_method_array['total'];
                $notice_text = sprintf(__("Please accept/deny this request. The buyer has already choosen for a shipping method: %s", "surfsnb"), $shipping_method);
            } else {
                $notice_text = __("Please accept/deny this request! You can do a shipment proposal after.", "surfsnb");
            }
        } else {

            // Sellers confirmed all/part of the products
            // Shipment proposal acceptance
            $items = $order->get_items();
            $count = 0;
            $total = 0;
            foreach ($items as $item) {

                $shipping_status = "";
                $shipping_status = get_product_field_from_order("product_shipping_status", $order_id, $item["product_id"], false);

                if (shipping_method_is_pickup($order_id) || shipping_method_is_express($order_id)) {
                    $count++;
                } elseif ($shipping_status == "Accept") {
                    $count++;
                }
                $total++;
            }

            if ($count == 0) {

                // Sellers confirmed all/part of the products
                $notice_text = __("Waiting for the buyer to accept your shipment proposal", "surfsnb");
            } else {

                // Proceed to payment

                if ($order->get_status() != 'processing' AND $order->get_status() != 'completed') {

                    // Sellers confirmed all/part of the products but not paid yet
                    $notice_text = __("Awaiting payment", "surfsnb");
                } else {

                    // Product sent
                    $items = $order->get_items();
                    $count = 0;
                    $total = 0;
                    foreach ($items as $item) {
                        //echo "<pre>".print_r($item,1)."</pre>";

                        $product_sent = "";
                        //$product_sent = get_field("sent",$item["product_id"]);	
                        $product_sent = get_product_field_from_order("product_sent", $order_id, $item["product_id"], false);

                        //echo "Product sent ".$item["product_id"].": ".$product_sent."<br>";

                        if (shipping_method_is_pickup($order_id)) {
                            $count++;
                        } elseif ($product_sent == "Sent") {
                            $count++;
                        }
                        $total++;
                    }

                    if ($count == 0) {

                        // No products have been sent yet
                        $org_date = get_field("pickup_date", $order_id);
                        $pickup_date = date('d-m-Y', strtotime($org_date));
                        if (shipping_method_is_express($order_id) && empty($org_date)) {
                            $notice_text = __("Please choose a pickup date below", "surfsnb");
                        } elseif (shipping_method_is_express($order_id) && isset($org_date)) {
                            $notice_text = sprintf(__("The order will be picked up on %s. Please prepare the shipment: <a href='#download_labels'>Click here for instructions</a>", "surfsnb"), $pickup_date);
                        } else {
                            $notice_text = __("Please proceed to shipment", "surfsnb");
                        }
                    } else {

                        // One or more products have been sent by the sellers
                        // Product received
                        $items = $order->get_items();
                        $count = 0;
                        $sent = 0;
                        foreach ($items as $item) {
                            //echo "<pre>".print_r($item,1)."</pre>";


                            $product_sent = "";
                            //$product_sent = get_field("sent",$item["product_id"]);	
                            $product_sent = get_product_field_from_order("product_sent", $order_id, $item["product_id"], false);

                            $product_received = "";
                            $product_received = get_field("received", $item["product_id"]);
                            $product_received = get_product_field_from_order("product_received", $order_id, $item["product_id"], false);

                            //echo "Product received ".$item["product_id"].": ".$product_received."<br>";

                            if (shipping_method_is_pickup($order_id)) {
                                $sent++;
                            } elseif ($product_sent == "Sent") {
                                $sent++;
                            }

                            if ($product_received == "Received") {
                                $count++;
                            }
                        }

                        //echo "Sent: ".$sent."<Br>";
                        //echo "Received: ".$count."<Br>";

                        if ($count != $sent AND $count >= 0) {

                            // None or not all products have been received yet
                            //echo "<span style='color:green;'><img src='/wp-content/uploads/2016/11/53638.png' style='height:20px;width:20px;margin-right:20px;' /><b>Please confirm when you've received the products</b></span>";
                            $notice_text = __("Waiting for the buyer to confirm receipt of your product(s)", "surfsnb");
                        } elseif ($count == $sent) {

                            // All have been received

                            $items = $order->get_items();
                            $count = 0;
                            $total = 0;
                            foreach ($items as $item) {
                                //echo "<pre>".print_r($item,1)."</pre>";

                                $payment_claimed = "";
                                //$payment_claimed = get_field("payment",$item["product_id"]);	
                                $payment_claimed = get_product_field_from_order("product_payment_claim", $order_id, $item["product_id"], false);

                                //echo "Payment claimed ".$item["product_id"].": ".$payment_claimed."<br>";

                                if ($payment_claimed == "Claimed") {
                                    $count++;
                                }
                                $total++;
                            }

                            if ($count == 0) {

                                // Payment to claim

                                $notice_text = __("Claim your payment", "surfsnb");
                            } else {

                                // Payment claimed

                                if ($order->get_status() != 'completed') {

                                    // Awaiting commission to be paid out

                                    $notice_text = __("Your payment claim is in process", "surfsnb");
                                } else {

                                    // Order complete

                                    $notice_text = __("Thank you for trusting in Surfsnb. Your money is on the way to your bank account.", "surfsnb");
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    echo "<span style='color:black;'><img src='https://cdn3.iconfinder.com/data/icons/photo-camera-ui/512/accept-complete-ok-good-128.png' style='height:20px;width:20px;margin-right:20px;' /><b>" . $notice_text . "</b></span><br>";
    $notice_text = "";
}

function get_order_statusses($order_id) {

    global $woocommerce, $post;

    $order = new WC_Order($order_id);

    // Failed order notice
    failed_order_notice($_GET['payment']);

    if ($order->get_status() == 'cancelled') {

        // Order status is cancelled
        $notice_text = __("The request has been cancelled", "surfsnb");
    } else {

        // Product confirmation, shipping proposal

        $items = $order->get_items();
        $count = 0;
        $total = 0;
        foreach ($items as $item) {
            //echo "<pre>".print_r($item,1)."</pre>";
            //$seller_confirmation = get_field("status",$item["product_id"]);
            $seller_confirmation = get_product_field_from_order("product_status", $order_id, $item["product_id"], false);
            //$shipping_split = get_field("shipping_split",$item["product_id"]);
            $shipping_split = get_product_field_from_order("product_shipping_split", $order_id, $item["product_id"], false);
            //$shipping_fee = get_field("shipping_fee",$item["product_id"]);
            $shipping_fee = get_product_field_from_order("product_shipping_fee", $order_id, $item["product_id"], false);

            //echo "Sellers confirmation ".$item["product_id"].": ".$seller_confirmation."<br>";

            if (( shipping_method_is_pickup($order_id) || shipping_method_is_express($order_id) ) && $seller_confirmation == "Confirm") {
                $count++;
            } elseif ($seller_confirmation == "Confirm" && !empty($shipping_split) && !empty($shipping_fee)) {
                $count++;
            }
            $total++;
        }

        if ($count == 0) {

            if (shipping_method_is_express($order_id)) {
                $notice_text = __("Waiting for the seller's confirmation", "surfsnb");
            } elseif (shipping_method_is_pickup($order_id)) {
                $notice_text = __("Waiting for the seller's acceptance. After you've paid you will receive all details from the seller to make a pick-up appointment.", "surfsnb");
            } else {
                $notice_text = __("Waiting for the seller's acceptance and shipment proposal", "surfsnb");
            }

            // Sellers didn't confirm any of the products yet
        } else {

            // Sellers confirmed all/part of the products
            // Shipment proposal acceptance
            $items = $order->get_items();
            $count = 0;
            $total = 0;
            foreach ($items as $item) {
                //echo "<pre>".print_r($item,1)."</pre>";

                $shipping_status = "";
                //$shipping_status = get_field("shipping_status",$item["product_id"]);	
                $shipping_status = get_product_field_from_order("product_shipping_status", $order_id, $item["product_id"], false);

                //echo "Shipping status ".$item["product_id"].": ".$shipping_status."<br>";

                if (shipping_method_is_pickup($order_id) || shipping_method_is_express($order_id)) {
                    $count++;
                } elseif ($shipping_status == "Accept") {
                    $count++;
                }
                $total++;
            }

            if ($count == 0) {

                // Sellers confirmed all/part of the products
                $notice_text = __("Please accept/deny the shipment proposal(s)", "surfsnb");
            } else {

                // Proceed to payment

                if ($order->get_status() != 'processing' AND $order->get_status() != 'completed') {

                    // Sellers confirmed all/part of the products but not paid yet
                    $notice_text = __("Please proceed to payment", "surfsnb");
                } else {

                    // Product sent
                    $items = $order->get_items();
                    $count = 0;
                    $total = 0;
                    foreach ($items as $item) {
                        //echo "<pre>".print_r($item,1)."</pre>";

                        $product_sent = "";
                        //$product_sent = get_field("sent",$item["product_id"]);	
                        $product_sent = get_product_field_from_order("product_sent", $order_id, $item["product_id"], false);

                        //echo "Product sent ".$item["product_id"].": ".$product_sent."<br>";

                        if (shipping_method_is_pickup($order_id)) {
                            $count++;
                        } elseif ($product_sent == "Sent") {
                            $count++;
                        }
                        $total++;
                    }

                    if ($count == 0) {

                        $org_date = get_field("pickup_date", $order_id);
                        $pickup_date = date('d-m-Y', strtotime($org_date));
                        if (shipping_method_is_pickup($order_id)) {

                            $notice_text = __("Please click the received button once you've picked up the product(s) from the seller", "surfsnb");
                        } elseif (shipping_method_is_express($order_id) && empty($org_date)) {

                            $notice_text = __("Waiting for the seller to choose a pick-up date", "surfsnb");
                        } elseif (shipping_method_is_express($order_id) && isset($org_date)) {

                            $notice_text = sprintf(__("The shipment will be picked up on %s", "surfsnb"), $pickup_date);
                        } else {

                            // No products have been sent yet
                            $notice_text = __("Waiting for the seller to send the product(s)", "surfsnb");
                        }
                    } else {

                        // One or more products have been sent by the sellers
                        // Product received
                        $items = $order->get_items();
                        $count = 0;
                        $sent = 0;
                        foreach ($items as $item) {
                            //echo "<pre>".print_r($item,1)."</pre>";


                            $product_sent = "";
                            //$product_sent = get_field("sent",$item["product_id"]);	
                            $product_sent = get_product_field_from_order("product_sent", $order_id, $item["product_id"], false);

                            $product_received = "";
                            //$product_received = get_field("received",$item["product_id"]);	
                            $product_received = get_product_field_from_order("product_received", $order_id, $item["product_id"], false);

                            //echo "Product received ".$item["product_id"].": ".$product_received."<br>";

                            if (shipping_method_is_pickup($order_id)) {
                                $sent++;
                            } elseif ($product_sent == "Sent") {
                                $sent++;
                            }

                            if ($product_received == "Received") {
                                $count++;
                            }
                        }

                        //echo "Sent: ".$sent."<Br>";
                        //echo "Received: ".$count."<Br>";

                        if ($count == $sent) {

                            // All have been received
                            if ($order->get_status() == 'processing') {

                                // Status processing
                                $notice_text = __("You've received all products. Thanks for buying through Surfsnb!", "surfsnb");
                            } else {

                                // Status completed
                                $notice_text = __("The seller has received your payment. Thanks for buying through Surfsnb!", "surfsnb");
                            }

                            $actions = apply_filters('woocommerce_my_account_my_orders_actions', $actions, $order);

                            //echo "<pre>".print_r($actions,1)."</pre>";
                            // Please leave some feedback
                            if ($actions['leave_feedback']['name'] == "Leave Feedback") {
                                $notice_text = __("Thanks for buying through Surfsnb! Please leave some feedback about the seller.", "surfsnb");
                            }
                        } else if ($count >= 0) {

                            // None or not all products have been received yet
                            $notice_text = __("Please confirm when you've received the product(s)", "surfsnb");
                        }
                    }
                }
            }
        }
    }

    echo "<span style='color:black;'><img src='/wp-content/uploads/2016/11/53638.png' style='height:20px;width:20px;margin-right:20px;' /><b>" . $notice_text . "</b></span>";
    $notice_text = "";
}

// Returns a locale from a country code that is provided.
function country_code_to_locale($country_code, $language_code = '') {
    // Locale list taken from:
    // http://stackoverflow.com/questions/3191664/
    // list-of-all-locales-and-their-short-codes
    $locales = array('af-ZA',
        'am-ET',
        'ar-AE',
        'ar-BH',
        'ar-DZ',
        'ar-EG',
        'ar-IQ',
        'ar-JO',
        'ar-KW',
        'ar-LB',
        'ar-LY',
        'ar-MA',
        'arn-CL',
        'ar-OM',
        'ar-QA',
        'ar-SA',
        'ar-SY',
        'ar-TN',
        'ar-YE',
        'as-IN',
        'az-Cyrl-AZ',
        'az-Latn-AZ',
        'ba-RU',
        'be-BY',
        'bg-BG',
        'bn-BD',
        'bn-IN',
        'bo-CN',
        'bs-Cyrl-BA',
        'bs-Latn-BA',
        'ca-ES',
        'cs-CZ',
        'da-DK',
        'de-AT',
        'de-CH',
        'de-DE',
        'de-LI',
        'de-LU',
        'dsb-DE',
        'dv-MV',
        'el-GR',
        'en-029',
        'en-AU',
        'en-BZ',
        'en-CA',
        'en-GB',
        'en-IE',
        'en-IN',
        'en-JM',
        'en-MY',
        'en-NZ',
        'en-PH',
        'en-SG',
        'en-TT',
        'en-US',
        'en-ZA',
        'en-ZW',
        'es-AR',
        'es-BO',
        'es-CL',
        'es-CO',
        'es-CR',
        'es-DO',
        'es-EC',
        'es-ES',
        'es-GT',
        'es-HN',
        'es-MX',
        'es-NI',
        'es-PA',
        'es-PE',
        'es-PR',
        'es-PY',
        'es-SV',
        'es-US',
        'es-UY',
        'es-VE',
        'et-EE',
        'eu-ES',
        'fa-IR',
        'fi-FI',
        'fil-PH',
        'fo-FO',
        'fr-BE',
        'fr-CA',
        'fr-CH',
        'fr-FR',
        'fr-LU',
        'fr-MC',
        'fy-NL',
        'ga-IE',
        'gd-GB',
        'gl-ES',
        'gsw-FR',
        'gu-IN',
        'ha-Latn-NG',
        'he-IL',
        'hi-IN',
        'hr-BA',
        'hr-HR',
        'hsb-DE',
        'hu-HU',
        'hy-AM',
        'id-ID',
        'ig-NG',
        'ii-CN',
        'is-IS',
        'it-CH',
        'it-IT',
        'iu-Cans-CA',
        'iu-Latn-CA',
        'ja-JP',
        'ka-GE',
        'kk-KZ',
        'kl-GL',
        'km-KH',
        'kn-IN',
        'kok-IN',
        'ko-KR',
        'ky-KG',
        'lb-LU',
        'lo-LA',
        'lt-LT',
        'lv-LV',
        'mi-NZ',
        'mk-MK',
        'ml-IN',
        'mn-MN',
        'mn-Mong-CN',
        'moh-CA',
        'mr-IN',
        'ms-BN',
        'ms-MY',
        'mt-MT',
        'nb-NO',
        'ne-NP',
        'nl-BE',
        'nl-NL',
        'nn-NO',
        'nso-ZA',
        'oc-FR',
        'or-IN',
        'pa-IN',
        'pl-PL',
        'prs-AF',
        'ps-AF',
        'pt-BR',
        'pt-PT',
        'qut-GT',
        'quz-BO',
        'quz-EC',
        'quz-PE',
        'rm-CH',
        'ro-RO',
        'ru-RU',
        'rw-RW',
        'sah-RU',
        'sa-IN',
        'se-FI',
        'se-NO',
        'se-SE',
        'si-LK',
        'sk-SK',
        'sl-SI',
        'sma-NO',
        'sma-SE',
        'smj-NO',
        'smj-SE',
        'smn-FI',
        'sms-FI',
        'sq-AL',
        'sr-Cyrl-BA',
        'sr-Cyrl-CS',
        'sr-Cyrl-ME',
        'sr-Cyrl-RS',
        'sr-Latn-BA',
        'sr-Latn-CS',
        'sr-Latn-ME',
        'sr-Latn-RS',
        'sv-FI',
        'sv-SE',
        'sw-KE',
        'syr-SY',
        'ta-IN',
        'te-IN',
        'tg-Cyrl-TJ',
        'th-TH',
        'tk-TM',
        'tn-ZA',
        'tr-TR',
        'tt-RU',
        'tzm-Latn-DZ',
        'ug-CN',
        'uk-UA',
        'ur-PK',
        'uz-Cyrl-UZ',
        'uz-Latn-UZ',
        'vi-VN',
        'wo-SN',
        'xh-ZA',
        'yo-NG',
        'zh-CN',
        'zh-HK',
        'zh-MO',
        'zh-SG',
        'zh-TW',
        'zu-ZA',);

    foreach ($locales as $locale) {
        $locale_region = locale_get_region($locale);
        $locale_language = locale_get_primary_language($locale);
        $locale_array = array('language' => $locale_language,
            'region' => $locale_region);

        if (strtoupper($country_code) == $locale_region &&
                $language_code == '') {
            return locale_compose($locale_array);
        } elseif (strtoupper($country_code) == $locale_region &&
                strtolower($language_code) == $locale_language) {
            return locale_compose($locale_array);
        }
    }

    return null;
}

function change_language_to_profile_language($language_user_id) {

    // Change language to profile language
    global $sitepress;

    $old_locale_code = $sitepress->get_current_language();
    if ($old_locale_code == "en") {
        $old_locale_code = "gb";
    };
    $old_locale = country_code_to_locale($old_locale_code);

    $new_locale_code = get_user_meta($language_user_id, "icl_admin_language", true);
    if ($new_locale_code == "") {
        $new_locale_code = "en";
    };
    if ($new_locale_code == "en") {
        $new_locale_code_changed = "gb";
    } else {
        $new_locale_code_changed = $new_locale_code;
    };
    $new_locale = country_code_to_locale($new_locale_code_changed);

    //echo "Old locale code: ".$old_locale_code."<br>";
    //echo "Old locale: ".$old_locale."<br>";
    //echo "New locale code: ".$new_locale_code."<br>";
    //echo "New locale: ".$new_locale."<br>";
    // Switch language
    $sitepress->switch_lang($new_locale_code);

    return $old_locale_code;
}

function change_language_to_profile_language_back($old_locale_code) {
    if ($old_locale_code == "gb") {
        $old_locale_code = "en";
    }
    global $sitepress;
    $sitepress->switch_lang($old_locale_code);
}

function send_email($itemID, $current_user, $onderwerp, $header, $content, $link, $link_url, $getcustomer, $type) {

    // Get e-mail
    if ($getcustomer == true) {
        $order = new WC_Order($itemID);
        $orderVendorID = $order->get_customer_id();
        $orderVendorMeta = get_userdata($orderVendorID);
        $orderVendorName = $orderVendorMeta->display_name;
        $email = $orderVendorMeta->user_email;
        $language_user_id = $orderVendorID;
    } elseif ($getcustomer == false && $type == "comment") {
        // Get Comment Author Email
        $item = get_comment($itemID);
        $itemAuthor = get_comment_author($itemID);
        $email = $item->comment_author_email;
        $itemAuthor = get_user_by('email', $email);
        $itemAuthorID = $itemAuthor->ID;
        $language_user_id = $itemAuthorID;
    } else {
        // Get Item Author Email
        $item = get_post($itemID);
        $itemAuthorID = $item->post_author;
        $itemAuthor = get_userdata($itemAuthorID);
        $email = $itemAuthor->user_email;
        $language_user_id = $itemAuthorID;
    }

    // Change language to profile language
    //change_language_to_profile_language( $language_user_id );
    //global $sitepress;
    //echo "Current lang: ".$sitepress->get_current_language()."<br>";
    //$itemTitle = $item->post_title;
    //echo "Item Author ID: ".$itemAuthorID."<br>";
    // E-mail headers
    setlocale(LC_ALL, 'en_US');
    $datum = strftime("%A %d %B %Y", strTOTime(date("Y-m-d H:i:s")));
    $datum_kort = date("d-m-Y");
    //$headers[] = 'From: SurfSnB.com <info@sellandbuy.online>' . "\r\n";
    $headers[] = 'BCC: archive@sellandbuy.online' . "\r\n";
    $headers[] = 'Content-Type: text/html; charset=UTF-8';

    // HTML Template openen
    $template = file_get_contents(site_url() . "/templates/mailStandard.htm");

    $template = str_replace("{mailStandard_header}", $header, $template);
    $template = str_replace("{mailStandard_content}", $content, $template);
    $template = str_replace("{mailStandard_link}", $link, $template);
    $template = str_replace("{voornaam}", $current_user->user_firstname, $template);
    $template = str_replace("{achternaam}", $current_user->user_lastname, $template);
    $template = str_replace("{achternaam_stripped}", preg_replace('/\s+/', '', $current_user->user_lastname), $template);
    $template = str_replace("{onderwerp}", $onderwerp, $template);
    $template = str_replace("{datum}", $datum, $template);
    $template = str_replace("{datum_kort}", $datum_kort, $template);


    // Email versturen
    wp_mail($email, $onderwerp, $template, $headers);

    // Add notification, like the email
    $my_post = array(
        'post_title' => $onderwerp,
        'post_content' => '',
        'post_status' => 'publish',
        'post_author' => $language_user_id, // Save for the one that the email is been sent to
        'post_type' => 'notifications'
    );

    // Insert the post into the database.
    $notification_id = wp_insert_post($my_post);

    // Set the redirect product
    update_field("field_58ece6c78971a", $link_url, $notification_id);

    // Change language back to viewing language
    //if($old_locale_code == "gb"){ $old_locale_code = "en"; }
    //$sitepress->switch_lang($old_locale_code);	
}

// Send email to archive
add_filter('woocommerce_email_recipient_customer_processing_order', 'your_email_recipient_filter_function', 10, 2);

function your_email_recipient_filter_function($recipient, $object) {
    $recipient = $recipient . ', archive@sellandbuy.online';
    return $recipient;
}

// Hold comment
function show_message_function($comment_ID, $comment_approved) {
    if (1 === $comment_approved) {
        //function logic goes here
        wp_set_comment_status($comment_ID, "hold");
    }
}

add_action('comment_post', 'show_message_function', 10, 2);

// Send email on comment approval
add_action('comment_unapproved_to_approved', 'approve_comment_callback', 10);

function approve_comment_callback($comment) {

    //$comment = get_comment($comment_ID);
    $parentID = $comment->comment_parent;
    $parent = get_post($itemID);

    $productID = $comment->comment_post_ID;
    $productTitle = get_the_title($productID);

    // Only send e-mails and add notification if it's the main post
    global $sitepress;
    $trid = $sitepress->get_element_trid($productID);
    $translation = $sitepress->get_element_translations($trid);
    $ids = array();
    foreach ($translation as $key => $data) {
        $ids[] = $data->element_id; // THIS DOESNT GIVE THE CORRECT POST IDS??
    }
    $mainPost = min($ids);
    if ($productID == $mainPost) {

        // Check whether the seller or buyer add a comment
        $comment_author = get_user_by('email', $comment->comment_author_email);
        $comment_author_ID = $comment_author->ID;

        $product = get_post($productID);
        $product_author_ID = $product->post_author;

        // The seller of the product added a comment
        if ($comment_author_ID == $product_author_ID) {

            // It is the parent comment
            if ($parentID == 0) {

                // Send e-mail to the buyer
                // Get all users that ordered this product
                global $wpdb;
                $produto_id = $productID; // Product ID
                $consulta = "SELECT order_id " .
                        "FROM {$wpdb->prefix}woocommerce_order_itemmeta woim " .
                        "LEFT JOIN {$wpdb->prefix}woocommerce_order_items oi " .
                        "ON woim.order_item_id = oi.order_item_id " .
                        "WHERE meta_key = '_product_id' AND meta_value = %d " .
                        "GROUP BY order_id;";

                $order_ids = $wpdb->get_col($wpdb->prepare($consulta, $produto_id));

                $orderss = implode("-", $order_ids);

                // Send all of them an e-mail that there is a new question by the seller
                foreach ($order_ids as $order_id) {

                    //$order = new WC_Order( $order_id );
                    $itemID = $order_id;
                    $orderVendorID = get_post_meta($order_id, "_customer_user", true);
                    $orderVendorMeta = get_userdata($orderVendorID);
                    $orderVendorName = $orderVendorMeta->display_name;
                    $email = $orderVendorMeta->user_email;
                    $language_user_id = $orderVendorID;

                    // Only send if WC Order, not WCV Order and if email is not empty
                    if (get_post_type($order_id) != 'shop_order_vendor' AND $email != "") {

                        $new_locale_code = get_user_meta($language_user_id, "icl_admin_language", true);
                        $productURL = get_permalink(icl_object_id($productID, 'product', false, $new_locale_code));

                        $onderwerp = apply_filters('wpml_translate_single_string', 'The seller of ', 'surfsnb_orderProcess', md5('The seller of '), $new_locale_code) . $productTitle . apply_filters('wpml_translate_single_string', ' has asked a question', 'surfsnb_orderProcess', md5(' has asked a question'), $new_locale_code);
                        $header = $productTitle;
                        $content = apply_filters('wpml_translate_single_string', 'The seller of ', 'surfsnb_orderProcess', md5('The seller of '), $new_locale_code) . $productTitle . apply_filters('wpml_translate_single_string', ' has asked a question. This question might be for you.', 'surfsnb_orderProcess', md5(' has asked a question. This question might be for you.'), $new_locale_code);
                        $link_url = $productURL;
                        $link = "<a href='" . $productURL . "#tab-reviews'>" . apply_filters('wpml_translate_single_string', 'Click here to view the product!', 'surfsnb_orderProcess', md5('Click here to view the product!'), $new_locale_code) . "</a>";

                        send_email($itemID, $current_user, $onderwerp, $header, $content, $link, $link_url, true, "");
                    }
                }

                // It is not the parent comment - Answer to a question (by the seller)
            } else {

                $itemID = $parentID;
                $item = get_comment($itemID);
                $email = $item->comment_author_email;
                $current_user = wp_get_current_user();
                $language_user_id = $item->user_id;

                $new_locale_code = get_user_meta($language_user_id, "icl_admin_language", true);
                $productURL = get_permalink(icl_object_id($productID, 'product', false, $new_locale_code));

                $onderwerp = apply_filters('wpml_translate_single_string', 'Someone answered to your question on', 'surfsnb_orderProcess', md5('Someone answered to your question on'), $new_locale_code) . " " . $productTitle;
                $header = $productTitle;
                $content = apply_filters('wpml_translate_single_string', 'Someone answered to your question on', 'surfsnb_orderProcess', md5('Someone answered to your question on'), $new_locale_code) . " " . $productTitle;
                $link_url = $productURL;
                $link = "<a href='" . $productURL . "#tab-reviews'>" . apply_filters('wpml_translate_single_string', 'Click here to view the product!', 'surfsnb_orderProcess', md5('Click here to view the product!'), $new_locale_code) . "</a>";

                send_email($itemID, $current_user, $onderwerp, $header, $content, $link, $link_url, false, "comment");
            }
        } else {

            if ($parentID == 0) { // Send e-mail to the seller
                $itemID = $productID;
                $item = get_post($itemID);
                $itemAuthorID = $item->post_author;
                $itemAuthor = get_userdata($itemAuthorID);
                $email = $itemAuthor->user_email;
                $current_user = wp_get_current_user();
                $language_user_id = $itemAuthor->ID;

                $new_locale_code = get_user_meta($language_user_id, "icl_admin_language", true);
                $productURL = get_permalink(icl_object_id($productID, 'product', false, $new_locale_code));

                //do_action( 'wpml_register_single_string', 'surfsnb', 'Someone asked a question about your', 'Someone asked a question about your' );

                $onderwerp = apply_filters('wpml_translate_single_string', 'Someone asked a question about your', 'surfsnb_orderProcess', md5('Someone asked a question about your'), $new_locale_code) . " " . $productTitle;
                $header = $productTitle;
                $content = apply_filters('wpml_translate_single_string', 'Someone asked a question about your', 'surfsnb_orderProcess', md5('Someone asked a question about your'), $new_locale_code) . " " . $productTitle;
                $link_url = $productURL;
                $link = "<a href='" . $productURL . "#tab-reviews'>" . apply_filters('wpml_translate_single_string', 'Click here to view the product!', 'surfsnb_orderProcess', md5('Click here to view the product!'), $new_locale_code) . "</a>";

                send_email($itemID, $current_user, $onderwerp, $header, $content, $link, $link_url, false, "");
            }
        }
    }
}

// Add standard shop name
add_action('user_register', 'save_shop_name', 10, 1);

function save_shop_name($user_id) {

    if (isset($_POST['first_name'])) {
        $shop_name = $_POST['first_name'] . "'s Shop";
        update_user_meta($user_id, 'pv_shop_name', $shop_name);
    }
}

// Add .js script if "Enable threaded comments" is activated in Admin
// Codex: {@link https://developer.wordpress.org/reference/functions/wp_enqueue_script/}
function wpse218049_enqueue_comments_reply() {
    wp_enqueue_script('comment-reply', 'wp-includes/js/comment-reply', array(), false, true);
}

add_action('wp_enqueue_scripts', 'wpse218049_enqueue_comments_reply');

function wpse71451_enqueue_comment_reply() {
    if (get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}

// Hide shipping and seller info tabs
remove_filter('woocommerce_product_tabs', array('WCV_Vendor_Shop', 'seller_info_tab')); // Need to unset because we don't use the Seller Info Tab
remove_action('woocommerce_product_tabs', array($wcvendors_pro->wcvendors_pro_shipping_controller, 'shipping_panel_tab'), 11, 2);

add_filter('woocommerce_product_tabs', 'woo_rename_tabs', 98);

function woo_rename_tabs($tabs) {

    //echo "<pre>".print_r($tabs,1)."</pre>";

    $tabs['reviews']['title'] = __('Ask a question');    // Rename the reviews tab
    $tabs['description']['title'] = __('More Information');
    $tabs['additional_information']['title'] = __('Specifications'); // Rename the additional information tab
    //$tabs['wcv_shipping_tab']['title'] = __( 'Shipping info', 'Surfsnb' );	// Rename the additional information tab
    //$tabs['seller_info']['title'] = __( 'Seller reviews', 'Surfsnb' );	// Rename the additional information tab
    $tabs['vendor_ratings_tab']['title'] = __('Seller reviews'); // Rename the ratings tab		DONE FROM SETTINGS WCVENDORS / VENDOR RATINGS	

    return $tabs;
}

add_filter('woocommerce_product_tabs', array('WCV_Vendor_Shop', 'seller_info_tab'));

// GET RELATED PRODUCTS FROM DIRECT CATEGORY
add_filter('woocommerce_product_related_posts', 'woocommerce_get_direct_related_products');

function woocommerce_get_direct_related_products() {
    global $woocommerce, $product;

    // Related products are found from category
    $cats_array = array(0);

    // Get categories
    $terms = wp_get_post_terms($product->get_id(), 'product_cat');

    //Select only the category which doesn't have any children
    foreach ($terms as $term) {
        $children = get_term_children($term->term_id, 'product_cat');
        if (!sizeof($children))
            $cats_array[] = $term->term_id;
    }

    echo "Cats: <pre>" . print_r($cats_array, 1) . "</pre>";

    // Don't bother if none are set
    if (sizeof($cats_array) == 1)
        return array();

    // Meta query
    $meta_query = array();
    $meta_query[] = $woocommerce->query->visibility_meta_query();
    $meta_query[] = $woocommerce->query->stock_status_meta_query();

    $limit = 5;

    // Get the posts
    return array(
        'orderby' => 'rand',
        'posts_per_page' => $limit,
        'post_type' => 'product',
        'fields' => 'ids',
        'meta_query' => $meta_query,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'id',
                'terms' => $cats_array
            )
        )
    );
}

add_filter('user_contactmethods', 'hide_profile_fields', 10, 1);

function hide_profile_fields($contactmethods) {
    unset($contactmethods['url']);
    unset($contactmethods['twitter']);
    unset($contactmethods['facebook']);
    unset($contactmethods['google']);
    return $contactmethods;
}

// remove personal options block
if (is_admin()) {
    remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
    add_action('personal_options', 'prefix_hide_personal_options');
}

function prefix_hide_personal_options() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $("#your-profile .form-table:first, #your-profile h3:first").remove();
        });
    </script>
    <?php
}

function remove_website_row_wpse_94963_css() {
    echo '	<style>
				tr.user-url-wrap{ display: none; } 
				tr.user-nickname-wrap{ display: none; }
				tr.user-googleplus-wrap{ display: none; }
				tr.user-description-wrap{ display: none; }
			</style>';
}

add_action('admin_head-user-edit.php', 'remove_website_row_wpse_94963_css');
add_action('admin_head-profile.php', 'remove_website_row_wpse_94963_css');

// Add new user value
function function_new_user($user_id) {

    add_user_meta($user_id, '_new_user', '1');
}

add_action('user_register', 'function_new_user');

// On first login
function function_check_login_redirect($user_login, $user) {
    $logincontrol = get_user_meta($user->ID, '_new_user', 'TRUE');
    if ($logincontrol == 1) {
        //set the user to old
        update_user_meta( $user->ID, '_new_user', '0' );
		
        // Set display_name to user_firstname
        $current_user = get_userdata($user->ID);
        $user_firstname = $current_user->user_firstname;
        wp_update_user(array('ID' => $user->ID, 'display_name' => $user_firstname));

        //Do the redirects or whatever you need to do for the first login
        wp_redirect(__("../profile/?lang=en&message=new_user", "surfsnb"));

        exit;
    }
}
add_action('wp_login', 'function_check_login_redirect', 10, 2);

// MANGOPAY USER ADDING - After user_registration login as admin so we can approve vendor
add_action('wppb_edit_profile_success', 'my_profile_update', 20, 3);

function my_profile_update($http_request, $form_name, $user_id) {

    $logincontrol = get_user_meta($user_id, '_new_user', 'TRUE');
    if ($logincontrol) {
        //set the user to old
        update_user_meta($user_id, '_new_user', '0');

        // Change Display Name
        $user = get_userdata($user_id);
        $userdata = array(
            'ID' => $user_id,
            'display_name' => $user->user_firstname,
        );
        wp_update_user($userdata);

        $user_meta = get_user_meta($user_id);

        // If FB Login add MangoPay necessary fields
        if (empty($user_meta->user_nationality)) {
            add_user_meta($user_id, 'user_nationality', "FR");
        }
        if (empty($user_meta->billing_country)) {
            add_user_meta($user_id, 'billing_country', "FR");
        }
        if (empty($user_meta->user_birthday)) {
            add_user_meta($user_id, 'user_birthday', "January 1, 1990");
        }
        if (empty($user_meta->user_mp_status)) {
            add_user_meta($user_id, 'user_mp_status', "individual");
        }

        // First and last name
        if (is_null($user_meta->billing_first_name)) {
            update_user_meta($user_id, 'billing_first_name', $user->user_firstname);
        }
        if (is_null($user_meta->billing_last_name)) {
            update_user_meta($user_id, 'billing_last_name', $user->user_lastname);
        }
    }
}

// REMOVE TRANSLATIONS FROM WC VENDORS PRO PRODUCT PAGE
add_action('wcv_delete_post', 'remove_translations');

function remove_translations($id) {

    global $sitepress;
    $post_id = $id;
    $trid = $sitepress->get_element_trid($post_id);
    $translation = $sitepress->get_element_translations($trid);
    foreach ($translation as $key => $data) {
        $post_id = $data->element_id;
        wp_delete_post($post_id);
    }
}

// Update all translations from front-end product edit form WCV
add_action('updated_post_meta', 'my_product_update_postmeta', 10, 4);

function my_product_update_postmeta($meta_id, $post_id, $meta_key, $meta_value) {

    $post = get_post($post_id);

    if ($post->post_type == "product") {

        // Get Array with ID's of translations
        global $sitepress;
        $post_id = $post->ID;
        $trid = $sitepress->get_element_trid($post_id);
        $translation = $sitepress->get_element_translations($trid);
        foreach ($translation as $key => $data) {

            $translation_id = $data->element_id;

            update_post_meta($translation_id, $meta_key, $meta_value);
        }
    }
}

// Fix translated Dashboard
function catch_dashboard() {

    // WC Vendors Pro
    if (!class_exists('WCVendors_Pro'))
        return false;

    // Get the dashboard page id, from WC Vendors PRO option
    $dashboard_page_id = WCVendors_Pro::get_option('dashboard_page_id');

    // Get the original or translated ID form WPML, as appropriate, and chek if both are equal
    $is_dashboard = (get_the_ID() == icl_object_id($dashboard_page_id));

    return $is_dashboard;
}

// Filter form WC Vendors plugin
add_filter('wcv_view_dashboard', 'catch_dashboard');

add_filter('body_class', function($classes) {
    if (catch_dashboard()) {
        $classes[] = 'wcvendors wcvendors-pro wcv-pro-dashboard';
        $classes[] = 'vendor-pro-dashboard';
        $classes[] = 'woocommerce';
        if (current_user_can('administrator')) {
            $classes[] = 'administrator';
        }
    }
    return $classes;
});

add_filter('wcv_product_description', 'custom_wcv_product_description_placeholder');

function custom_wcv_product_description_placeholder($args) {
    $args['placeholder'] = __('Please add a complete product description here', 'surfsnb');
    return $args;
}

add_filter('wcv_product_price', 'custom_wcv_product_price_label');

function custom_wcv_product_price_label($args) {
    $args['label'] = __('Price', 'surfsnb');
    return $args;
}

add_filter('yith_wcwl_add_to_cart_label', 'woo_custom_wishlist_button_text'); // 2.1 +

function woo_custom_wishlist_button_text() {
    global $product;
    return __('Request to buy', 'surfsnb');
}

function child_comment_counter($id) {
    global $wpdb;
    $query = "SELECT COUNT(comment_post_id) AS count FROM `wp_comments` WHERE `comment_approved` = 1 AND `comment_parent` = " . $id;
    $children = $wpdb->get_row($query);
    return $children->count;
}

// Cron job to reset products views > wcmvp_product_view_count
function hits_set_zero_func() {

    $args = array('post_type' => 'product', 'posts_per_page' => '-1');
    $products = get_posts($args);
    foreach ($products as $product) {
        update_post_meta($product->ID, 'wcmvp_product_view_count', '0');
    }
}
add_action('hits_set_zero', 'hits_set_zero_func');

// Redirect back to order after comment from order page
function wpse_58613_comment_redirect($location) {
    if (isset($_POST['my_redirect_to'])) // Don't use "redirect_to", internal WP var
        $location = $_POST['my_redirect_to'];

    return $location;
}

add_filter('comment_post_redirect', 'wpse_58613_comment_redirect');

// Add YITH Product Filter Count
add_filter('yith_wcan_force_show_count', '__return_true');

add_filter('user_has_cap', 'user16975_refine_role', 10, 3);

function user16975_refine_role($allcaps, $cap, $args) {
    global $pagenow;

    $user = wp_get_current_user();
    if ($user->ID != 0 && $user->roles[0] == 'subscriber' && is_admin()) {
        // deny access to WP backend
        $allcaps['read'] = false;
    }

    return $allcaps;
}

add_action('admin_page_access_denied', 'user16975_redirect_dashbord');

function user16975_redirect_dashbord() {
    wp_redirect(site_url());
    die();
}

function my_login_logo() {
	
	$upload_dir   = wp_upload_dir();
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
	?>
	<style type="text/css">
		#login h1 a, .login h1 a {
			background-image: url(<?php echo $image[0]; ?>);
		}
	</style>
	<?php
}
add_action('login_enqueue_scripts', 'my_login_logo');

add_filter( 'login_headerurl', 'custom_loginlogo_url' );
function custom_loginlogo_url($url) {
    return site_url();
}

// Handle failed login to stay
add_action('wp_login_failed', 'my_front_end_login_fail');  // hook failed login

function my_front_end_login_fail($username) {
	$referrer = $_SERVER['HTTP_REFERER'];  // where did the post submission come from?
	// if there's a valid referrer, and it's not the default log-in screen
	if (!empty($referrer) && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin')) {
		wp_redirect($referrer . '?login=failed');  // let's append some information (login=failed) to the URL for the theme to use
		exit;
	}
}

// Forces all new registrations to your site on the My Account page to be a Vendor, rather than the Customer role
add_filter('woocommerce_new_customer_data', 'custom_woocommerce_new_customer_data');

function custom_woocommerce_new_customer_data($data) {
	$data['role'] = 'vendor'; // the new default role
	return $data;
}

// Redirect after password reset
function wpse_lost_password_redirect($user) {

	wp_set_current_user($user->ID);
	wp_set_auth_cookie($user->ID);

	wp_redirect(__("../profile/?message=password_updated", "surfsnb"));
	exit;
}
add_action('after_password_reset', 'wpse_lost_password_redirect');

// Change register form notice
add_action('login_footer', 'add_reg_passmsg');
function add_reg_passmsg() {
	?>
	<script type="text/javascript">
		if (jQuery("#reg_passmail").length) {
			document.getElementById("reg_passmail").innerHTML = "You'll receive a confirmation e-mail shortly after registration.";
		}
	</script>
	<?php
}

// Keep query string when switching language
    add_filter('icl_ls_languages', 'wpml_ls_filter');

    function wpml_ls_filter($languages) {
        global $sitepress;
        if ($_SERVER["QUERY_STRING"]) {
            if (strpos(basename($_SERVER['REQUEST_URI']), $_SERVER["QUERY_STRING"]) !== false) {
                foreach ($languages as $lang_code => $language) {
                    $languages[$lang_code]['url'] = $languages[$lang_code]['url'] . '?' . $_SERVER["QUERY_STRING"];
                }
            }
        }
        return $languages;
    }

    function no_self_ping(&$links) {
        $home = site_url();
        foreach ($links as $l => $link)
            if (0 === strpos($link, $home))
                unset($links[$l]);
    }

    add_action('pre_ping', 'no_self_ping');

    function _wpse206466_can_view() {
        // or any other admin level capability
        return current_user_can('manage_options');
    }

// Refirect away from WP admin
    add_action('init', 'blockusers_init');

    function blockusers_init() {
        if (is_admin() && !current_user_can('administrator') &&
                !( defined('DOING_AJAX') && DOING_AJAX )) {
            wp_redirect(site_url());
            exit;
        }
    }

    add_action('after_setup_theme', 'yourtheme_setup');

    function yourtheme_setup() {
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
    }

// @snippet       Change No. of Thumbnails per Row @ Product Gallery | WooCommerce
    add_filter('woocommerce_single_product_image_gallery_classes', 'bbloomer_5_columns_product_gallery');

    function bbloomer_5_columns_product_gallery($wrapper_classes) {
        $columns = 5; // change this to 2, 3, 5, etc. Default is 4.
        $wrapper_classes[2] = 'woocommerce-product-gallery--columns-' . absint($columns);
        return $wrapper_classes;
    }

    add_filter('query', 'fix_ajax_query');

    function fix_ajax_query($query) {
        $pattern = '/^\s*(START TRANSACTION|COMMIT|ROLLBACK)/i';
        return preg_match($pattern, $query) ? '' : $query;
    }

// MERGE COMMENTS
// This plugin merges comments from all translations of the posts and pages, so that they all 
// are displayed on each other. Comments are internally still attached to the post or page 
// they were made on. Edited by Danny (Surfsnb) to disallow products that are duplicates

function sort_merged_comments($a, $b) {
	return $a->comment_ID - $b->comment_ID;
}

function merge_comments($comments, $post_ID) {
	global $sitepress;
	remove_filter('comments_clauses', array($sitepress, 'comments_clauses'));
	// get all the languages for which this post exists

	$languages = icl_get_languages('skip_missing=1');
	$post = get_post($post_ID);
	$type = $post->post_type;

	// Check if any of the translations is a duplicate
	$trid = $sitepress->get_element_trid($post_ID);
	$translations = $sitepress->get_element_translations($trid);
	foreach ($translations as $key => $data) {
		$translation_id = $data->element_id;
		$_icl_lang_duplicate_of = get_post_meta($translation_id, '_icl_lang_duplicate_of', true);
		if ($_icl_lang_duplicate_of != "") {
			$duplicate = true;
		}
	}

	foreach ($languages as $code => $l) {

		// in $comments are already the comments from the current language
		if (!$l['active'] AND $duplicate != true) {
			$otherID = icl_object_id($post_ID, $type, false, $l['language_code']);
			$othercomments = get_comments(array('post_id' => $otherID, 'status' => 'approve', 'order' => 'DESC'));
			$comments = array_merge($comments, $othercomments);
		}
	}

	// Reset duplicate
	$duplicate = "";

	if ($languages) {
		// if we merged some comments in we need to reestablish an order
		usort($comments, 'sort_merged_comments');
	}

	//
	add_filter('comments_clauses', array($sitepress, 'comments_clauses'));

	return $comments;
}

function merge_comment_count($count, $post_ID) {
	// get all the languages for which this post exists
	$languages = icl_get_languages('skip_missing=1');
	$post = get_post($post_ID);
	$type = $post->post_type;

	foreach ($languages as $l) {
		// in $count is already the count from the current language
		if (!$l['active']) {
			$otherID = icl_object_id($post_ID, $type, false, $l['language_code']);
			if ($otherID) {
				// cannot use call_user_func due to php regressions
				if ($type == 'page') {
					$otherpost = get_page($otherID);
				} else {
					$otherpost = get_post($otherID);
				}
				if ($otherpost) {
					// increment comment count using translation post comment count.
					$count = $count + $otherpost->comment_count;
				}
			}
		}
	}
	return $count;
}

add_filter('comments_array', 'merge_comments', 100, 2);
add_filter('get_comments_number', 'merge_comment_count', 100, 2);

function show_comments() {

	$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$postID = url_to_postid($url);
	$current_user = wp_get_current_user();
	$args = array(
		'post_id' => $postID,
		'status' => 'approve',
		'include_unapproved' => $current_user->ID
	);
	$comments = get_comments($args);
	$commment_count = count($comments); //echo "Comments: ".$commment_count;

	if ($commment_count > 0) :
		?>

		<ol class='commentlist'>
		<?php wp_list_comments(apply_filters('woocommerce_product_review_list_args', array('callback' => 'woocommerce_comments')), $comments); ?>
		</ol>

		<?php
		if (get_comment_pages_count() > 1 && get_option('page_comments')) :
			echo '<nav class="woocommerce-pagination">';
			paginate_comments_links(apply_filters('woocommerce_comment_pagination_args', array(
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'type' => 'list',
			)));
			echo '</nav>';
		endif;
		?>

	<?php else : ?>

		<p class="woocommerce-noreviews"><?php _e('There are no questions yet.', 'woocommerce'); ?></p>

	<?php
	endif;
}

// Flush cache when post is saved 
function clearCache($post_ID) {

	if (function_exists("w3tc_pgcache_flush")) {
		w3tc_pgcache_flush();
		//w3tc_pgcache_flush_post($post_id); 
	}
	return $post_ID;
}

add_action("save_post", "clearCache");
add_action("delete_post", "clearCache");

// check register form for errors
add_filter('registration_errors', 'myplugin_registration_errors', 10, 3);

function myplugin_registration_errors($errors, $sanitized_user_login, $user_email) {
//global $errors;
//if (! is_wp_error($errors)) $errors = new WP_Error();
	// Username
	if (empty($sanitized_user_login)) {
		$errors->add('user_login_error', __('<strong>ERROR</strong>: The username field is empty', 'surfsnb'));
		return $errors;
	} elseif (username_exists($sanitized_user_login)) {
		$errors->add('user_login_error', __('<strong>ERROR</strong>: A user with that username already exists. <a href="' . wp_lostpassword_url() . '">Recover your password</a>', 'surfsnb'));
		//'<strong>ERROR</strong>: A user with that email already exists. <a href="' . wp_lostpassword_url() . '">Recover your password</a>', 'mydomain'
		return $errors;
	}

	// E-mail empty
	if (empty($user_email)) {
		$errors->add('user_login_error', __('<strong>ERROR</strong>: The e-mail field is empty or the email address isn&#8217;t correct.', 'surfsnb'));

		return $errors;
	} elseif (email_exists($user_email)) {
		$errors->add('user_login_error', __('<strong>ERROR</strong>: A user with that email already exists. <a href="' . wp_lostpassword_url() . '">Recover your password</a>', 'surfsnb'));
		//'<strong>ERROR</strong>: A user with that email already exists. <a href="' . wp_lostpassword_url() . '">Recover your password</a>', 'mydomain'
		return $errors;
	}

	if (!isset($_POST['ag_login_accept'])) {
		$errors->add('tos_aggree_error', __('<strong>ERROR</strong>: Please accept the Conditions of Use & Sale', 'mydomain'));
		return $errors;
	}
//end with returning $errors!!
	return $errors;
}

// Redirect to home on logout
add_action('wp_logout', 'auto_redirect_after_logout');

function auto_redirect_after_logout() {
	wp_redirect(site_url());
	exit();
}

// Add a failed order email notification
function sp_failed_order_email_notification($order_id) {
	$order = wc_get_order($order_id);
	$to = get_option('admin_email');
	$subject = 'An order failed, action required';
	$message = sprintf(__('%1$s went to the `failed` order status. This may require action to follow up.', 'text-domain'), '<a class="link" href="' . admin_url('post.php?post=' . $order_id . '&action=edit') . '">' . sprintf(__('Order #%s', 'woocommerce'), $order->get_order_number()) . '</a>');
	$headers[] = 'From: Me Myself <me@example.net>';
	$headers[] = 'Cc: Some Name <name@email.com>'; // Possible CC
	$headers[] = 'Content-Type: text/html;';
	$headers[] = 'charset=UTF-8';
	wp_mail($to, $subject, $message, $headers);
}

add_action('woocommerce_order_status_failed', 'sp_failed_order_email_notification');

// Send order paid and feel free to contact the seller e-mails ALSO FOR BANK_WIRE TRANSACTIONS
function custom_processing($order_id, $old_status, $new_status) {

	$order = new WC_Order($order_id);
	$current_user = wp_get_current_user();

	if ($new_status == "processing") {

		// NEW Send an email to seller
		// Take the product, so we can get send the owner of the product an email
		$products = $order->get_items();
		foreach ($products as $product) {
			$productID = $product['product_id'];
		}

		$itemID = $productID;
		$item = get_post($itemID);
		$itemTitle = $item->post_title;
		$itemAuthor = get_post_field('post_author', $itemID);
		$itemAuthorID = $item->post_author;
		$language_user_id = $itemAuthorID;
		$new_locale_code = get_user_meta($language_user_id, "icl_admin_language", true);

		if (shipping_method_is_express($order_id)) {

			$onderwerp = apply_filters('wpml_translate_single_string', 'A request has been paid', 'surfsnb_orderProcess', md5('A request has been paid'), $new_locale_code);
			$header = apply_filters('wpml_translate_single_string', 'A request has been paid', 'surfsnb_orderProcess', md5('A request has been paid'), $new_locale_code);
			$content = apply_filters('wpml_translate_single_string', 'Request #', 'surfsnb_orderProcess', md5('Request #'), $new_locale_code) . $order_id . apply_filters('wpml_translate_single_string', ' has been paid. Please go to the request and choose a date for the carrier to pick-up the product(s).', 'surfsnb_orderProcess', md5(' has been paid. Please go to the request and choose a date for the carrier to pick-up the product(s).'), $new_locale_code);
			$link_url = apply_filters('wpml_translate_single_string', site_url() . '/my-shop/', 'surfsnb_orderProcess', md5(site_url() . '/my-shop/'), $new_locale_code) . "seller-view-order/?order=" . $order_id . "&v=vdr";
			$link = "<a href='" . $link_url . "'>" . apply_filters('wpml_translate_single_string', 'Click here to go to the request!', 'surfsnb_orderProcess', md5('Click here to go to the request!'), $new_locale_code) . "</a>";
		} elseif (shipping_method_is_pickup($order_id)) {

			$onderwerp = apply_filters('wpml_translate_single_string', 'A request has been paid', 'surfsnb_orderProcess', md5('A request has been paid'), $new_locale_code);
			$header = apply_filters('wpml_translate_single_string', 'A request has been paid', 'surfsnb_orderProcess', md5('A request has been paid'), $new_locale_code);
			$content = apply_filters('wpml_translate_single_string', 'Request #', 'surfsnb_orderProcess', md5('Request #'), $new_locale_code) . $order_id . apply_filters('wpml_translate_single_string', ' has been paid. You can now arrange a pick-up date with the buyer.', 'surfsnb_orderProcess', md5(' has been paid. You can now arrange a pick-up date with the buyer.'), $new_locale_code);
			$link_url = apply_filters('wpml_translate_single_string', site_url() . '/my-shop/', 'surfsnb_orderProcess', md5(site_url() . '/my-shop/'), $new_locale_code) . "seller-view-order/?order=" . $order_id . "&v=vdr";
			$link = "<a href='" . $link_url . "'>" . apply_filters('wpml_translate_single_string', 'Click here to go to the request!', 'surfsnb_orderProcess', md5('Click here to go to the request!'), $new_locale_code) . "</a>";
		} else {

			$onderwerp = apply_filters('wpml_translate_single_string', 'A request has been paid', 'surfsnb_orderProcess', md5('A request has been paid'), $new_locale_code);
			$header = apply_filters('wpml_translate_single_string', 'A request has been paid', 'surfsnb_orderProcess', md5('A request has been paid'), $new_locale_code);
			$content = apply_filters('wpml_translate_single_string', 'Request #', 'surfsnb_orderProcess', md5('Request #'), $new_locale_code) . $order_id . apply_filters('wpml_translate_single_string', ' has been paid. Please ship the product(s) as soon as possible.', 'surfsnb_orderProcess', md5(' has been paid. Please ship the product(s) as soon as possible.'), $new_locale_code);
			$link_url = apply_filters('wpml_translate_single_string', site_url() . '/my-shop/', 'surfsnb_orderProcess', md5(site_url() . '/my-shop/'), $new_locale_code) . "seller-view-order/?order=" . $order_id . "&v=vdr";
			$link = "<a href='" . $link_url . "'>" . apply_filters('wpml_translate_single_string', 'Click here to go to the request!', 'surfsnb_orderProcess', md5('Click here to go to the request!'), $new_locale_code) . "</a>";
		}

		send_email($itemID, $current_user, $onderwerp, $header, $content, $link, $link_url, false, "");


		// Send Feel free to contact the seller e-mail
		$itemID = $order->get_id();
		$item = get_post($itemID);

		$itemTitle = $item->post_title;
		$itemAuthorID = get_post_field('post_author', $productID);
		$itemAuthorMeta = get_userdata($itemAuthorID);
		$itemAuthorName = $itemAuthorMeta->user_firstname . " " . $itemAuthorMeta->user_lastname;
		$itemAuthorEmail = $itemAuthorMeta->user_email;
		$itemAuthorPhone = get_user_meta($itemAuthorID, 'billing_phone', true);
		$orderVendorID = $order->get_customer_id();
		$language_user_id = $orderVendorID;
		$new_locale_code = get_user_meta($language_user_id, "icl_admin_language", true);

		$onderwerp = apply_filters('wpml_translate_single_string', 'Feel free to contact the seller', 'surfsnb_orderProcess', md5('Feel free to contact the seller'), $new_locale_code);
		$header = apply_filters('wpml_translate_single_string', 'Feel free to contact the seller', 'surfsnb_orderProcess', md5('Feel free to contact the seller'), $new_locale_code);
		$content = apply_filters('wpml_translate_single_string', '<h3>Contact details:</h3>Name: ', 'surfsnb_orderProcess', md5('<h3>Contact details:</h3>Name: '), $new_locale_code) . $itemAuthorName . "<br>" . apply_filters('wpml_translate_single_string', 'Phone: ', 'surfsnb_orderProcess', md5('Phone: '), $new_locale_code) . $itemAuthorPhone . "<br>" . apply_filters('wpml_translate_single_string', 'E-mail: ', 'surfsnb_orderProcess', md5('E-mail: '), $new_locale_code) . $itemAuthorEmail;
		$link_url = apply_filters('wpml_translate_single_string', site_url() . '/my-account/', 'surfsnb_orderProcess', md5(site_url() . '/my-account/'), $new_locale_code) . "view-order/?order=" . $order_id . "&v=vdr";
		$link = "<a href='" . $link_url . "'>" . apply_filters('wpml_translate_single_string', 'Click here to go to the request!', 'surfsnb_orderProcess', md5('Click here to go to the request!'), $new_locale_code) . "</a>";

		send_email($itemID, $current_user, $onderwerp, $header, $content, $link, $link_url, true, "");
	}
}

add_action('woocommerce_order_status_changed', 'custom_processing', 10, 3);

// Debug to console
function debug($data) {
	$output = $data;
	if (is_array($output))
		$output = implode(',', $output);

	echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}

// Get all translations of a string
function get_translation($string, $textdomain, $language) {

	global $sitepress;

	$current_lang = $sitepress->get_current_language();

	$current_user = wp_get_current_user();

	//$sitepress->switch_lang( $language, true ); // Switch to temp language
	//update_user_meta( $current_user->ID, "icl_admin_language", "$language" );
	//$translation = __( "$string", "$textdomain" ); // Get the translated string
	$translation = apply_filters('wpml_translate_single_string', $string, $textdomain, $string, 'fr');
	//$sitepress->switch_lang( $current_lang ); // Switch back to original language

	return $translation;
}

// Add notification, like the email
function add_notification($onderwerp, $language_user_id, $link_url) {
	$my_post = array(
		'post_title' => $onderwerp,
		'post_content' => '',
		'post_status' => 'publish',
		'post_author' => $language_user_id, // Save for the one that the email is been sent to
		'post_type' => 'notifications'
	);

	// Insert the post into the database.
	$notification_id = wp_insert_post($my_post);

	// Set the redirect product
	update_field("field_58ece6c78971a", $link_url, $notification_id);
}

function wc_customer_bought_product_custom($customer_email, $user_id, $product_id) {
	global $wpdb;

	$transient_name = 'wc_cbp_' . md5($customer_email . $user_id . WC_Cache_Helper::get_transient_version('orders'));

	if (false === ( $result = get_transient($transient_name) )) {
		$customer_data = array($user_id);

		if ($user_id) {
			$user = get_user_by('id', $user_id);

			if (isset($user->user_email)) {
				$customer_data[] = $user->user_email;
			}
		}

		if (is_email($customer_email)) {
			$customer_data[] = $customer_email;
		}

		$customer_data = array_map('esc_sql', array_filter(array_unique($customer_data)));
		$statuses = array_map('esc_sql', wc_get_order_statuses());

		//echo "<pre>".print_r($statuses,1)."</pre>";

		if (sizeof($customer_data) == 0) {
			return false;
		}

		$result = $wpdb->get_col(" 
	SELECT im.meta_value FROM {$wpdb->posts} AS p 
	INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id 
	INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id 
	INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id 
	WHERE p.post_status IN ( 'wc-" . implode("', 'wc-", $statuses) . "' ) 
	AND im.meta_key IN ( '_product_id', '_variation_id' ) 
	AND im.meta_value != 0 
");
		$result = array_map('absint', $result);

		set_transient($transient_name, $result, DAY_IN_SECONDS * 30);
	}
	return in_array(absint($product_id), $result);
}

// WooCommerce Check if User Has Purchased Product
add_action('woocommerce_after_shop_loop_item', 'product_is_in_request', 30);

function product_is_in_request($product_id) {
	if (is_user_logged_in()) {
		$current_user = wp_get_current_user();

		global $sitepress;
		$translated_ids = Array();
		if (!isset($sitepress))
			return;
		$post_id = $product_id; // Your original product ID
		$trid = $sitepress->get_element_trid($post_id, 'post_product');
		$translations = $sitepress->get_element_translations($trid, 'product');
		foreach ($translations as $lang => $translation) {
			//$translated_ids[] = $translation->element_id;
			if (wc_customer_bought_product_custom($current_user->user_email, $current_user->ID, $translation->element_id)) {
				$in_order = true;
			}
		}

		if ($in_order) {
			return true;
		}
	}
}

// Show the PDF download file for KYC
add_action('wcvendors_settings_after_form', 'add_pdf');

function add_pdf() {

	$pdf = "https://www.mangopay.com/terms/shareholder-declaration/Shareholder_Declaration-EN.pdf";
	echo "<div id='add_pdf' style='display:none;'><br><br><img src='" . site_url() . "/wp-content/uploads/pdf-flat.png' style>&nbsp; &nbsp;<a href='" . $pdf . "' target='_blank'>" . __("Click here to download the SHAREHOLDER DECLARATION.", "surfsnb") . "</a></div>";
	?>
	<script>
		jQuery(document).ready(function () {
			if (jQuery("#kyc_div_global").is(":visible")) {
				jQuery("#add_pdf").show();
			}

			jQuery(".store").on("click", function () {
				jQuery("#add_pdf").hide();
			});

			jQuery(".payment").on("click", function () {
				jQuery("#add_pdf").show();
			});
		});
	</script>
	<?php
}

// Show the PDF download file for KYC
add_action('send_feedback_notification', 'send_feedback_not');

function send_feedback_not($feedback) {

	// Send e-mail to notify the seller of a product has received feedback
	$itemID = $feedback['product_id'];
	//echo "Item ID: ".$itemID."<br>";
	$item = get_post($itemID);
	$itemTitle = $item->post_title;
	$itemAuthor = get_post_field('post_author', $itemID);

	// Change language to profile language
	$item = get_post($itemID);
	$itemAuthorID = $item->post_author;
	$language_user_id = $itemAuthorID;
	change_language_to_profile_language($language_user_id);

	$onderwerp = __("You've received feedback on your order", "surfsnb");
	$header = __("Feedback", "surfsnb");
	$content = __("Someone has left you some feedback.", "surfsnb");
	$user_meta = get_userdata($language_user_id);
	$link_url = site_url() . "/shops/" . $user_meta->user_login . "/ratings/";
	$link = "<a href='" . $link_url . "'>" . __('Click here to go to your feedback overview!', 'surfsnb_orderProcess') . "</a>";

	// Change language back to viewing language
	if ($old_locale_code == "gb") {
		$old_locale_code = "en";
	}
	global $sitepress;
	$sitepress->switch_lang($old_locale_code);

	send_email($itemID, $current_user, $onderwerp, $header, $content, $link, $link_url, false, "");
}

function wc_bulk_stock_after_process_qty_action($id, $new_quantity) {
	global $sitepress;

	//$new_quantity = get_post_meta($id, '_stock', true);

	if (is_numeric($new_quantity)) {

		$new_stock_status = ($new_quantity > 0) ? "instock" : "outofstock";
		update_post_meta($id, '_stock', $new_quantity);
		wc_update_product_stock_status($id, $new_stock_status);
		/*  Use the TRID to find the translated Product ids  */
		$trid = $sitepress->get_element_trid($id, 'post_product');
		if (is_numeric($trid)) {
			$translations = $sitepress->get_element_translations($trid, 'post_product');

			if (is_array($translations)) {
				/* Loop through each existing translation */
				foreach ($translations as $translation) {
					if (!isset($translation->element_id) || $translation->element_id == $id) {
						continue;
					}
					/*  set the stock status and quantity in the translated by updating the post-meta info */
					update_post_meta($translation->element_id, '_stock', $new_quantity);
					wc_update_product_stock_status($translation->element_id, $new_stock_status);
				}
			}
		}
		$echo = "updated";
		return $echo;
	}
}

function debug_to_alert($data) {
	$output = $data;
	if (is_array($output))
		$output = implode(',', $output);

	echo "<script>alert( 'Debug Objects: " . $output . "' );</script>";
}

// credit: ChromeOrange - https://gist.github.com/ChromeOrange/10013862
add_filter('woocommerce_package_rates', 'patricks_sort_woocommerce_available_shipping_methods', 10, 2);

function patricks_sort_woocommerce_available_shipping_methods($rates, $package) {
	//  if there are no rates don't do anything
	if (!$rates) {
		return;
	}

	// get an array of prices
	$prices = array();
	foreach ($rates as $rate) {

		if ($rate->cost > 0) {
			$price = $rate->cost;
		} else {
			$price = 9999;
		}

		$prices[] = $price;
	}

	// use the prices to sort the rates
	array_multisort($prices, $rates);

	// return the rates
	return $rates;
}

// Get shipping method id
function get_shipping_method_id($order_id) {
	// An instance of 
	$order = wc_get_order($order_id);
	// Iterating through order shipping items
	foreach ($order->get_items('shipping') as $item_id => $shipping_item_obj) {
		//$order_item_name           = $shipping_item_obj->get_name();
		//$order_item_type           = $shipping_item_obj->get_type();
		//$shipping_method_title     = $shipping_item_obj->get_method_title();
		$shipping_method_id = $shipping_item_obj->get_instance_id(); // The method ID

		return $shipping_method_id;
		//$shipping_method_total     = $shipping_item_obj->get_total();
		//$shipping_method_total_tax = $shipping_item_obj->get_total_tax();
		//$shipping_method_taxes     = $shipping_item_obj->get_taxes();
	}
}

// Get shipping method id
function get_shipping_method($order_id) {
	// An instance of 
	$order = wc_get_order($order_id);
	// Iterating through order shipping items
	$shipping_method = array();
	foreach ($order->get_items('shipping') as $item_id => $shipping_item_obj) {
		$shipping_method['name'] = $shipping_item_obj->get_name();
		$shipping_method['type'] = $shipping_item_obj->get_type();
		$shipping_method['title'] = $shipping_item_obj->get_method_title();
		$shipping_method['total'] = $shipping_item_obj->get_total();
		$shipping_method['total_tax'] = $shipping_item_obj->get_total_tax();
		$shipping_method['taxed'] = $shipping_item_obj->get_taxes();
		$shipping_method['id'] = get_shipping_method_id($order_id);
	}

	return $shipping_method;
}

// Check if shippinhg method is pickup
function shipping_method_is_pickup($order_id) {

	$shipping_method_id = get_shipping_method_id($order_id);

	//write_log("shipping_method_id: " . $shipping_method_id);

	if ($shipping_method_id == "5") {
		return true;
	}
}

// Check if shippinhg method is express
function shipping_method_is_express($order_id) {

	$shipping_method = get_shipping_method($order_id);
	$shipping_method_name = $shipping_method['name'];
	$shipping_method_id = get_shipping_method_id($order_id);

	if ($shipping_method_id !== "4" && 
		$shipping_method_id !== "5" && 
		strpos($shipping_method_name, 'proposal') == false && 
		strpos($shipping_method_name, 'ipping') == false
	) {
		return true;
	}
}

// Failed order notice
function failed_order_notice($payment) {
	if ($payment == "failed") {
		?>
		<div class="woocommerce-error"><?php _e("The payment has failed, please try again", "surfsnb"); ?></div>
		<?php
	}
}

// Get language user id for order
function get_language_user_id($order_id) {
	$order = new WC_Order($order_id);
	$orderVendorID = $order->get_customer_id();
	$language_user_id = $orderVendorID;
	return $language_user_id;
}

function show_proceed_to_pay_button($order_id) {
	?>        
	<form method="POST" name="form1" class="proceed_to_payment">

	<?php
	$order = new WC_Order($order_id);
	$order_key = $order->get_order_key();

	////// TEMPORARELY send French custmers to english checkout because of WPML bug /////////
	if (ICL_LANGUAGE_CODE == "en") {
		$url = site_url() . "/checkout/pay/" . $order_id . "/?pay_for_order=true&key=" . $order_key . "";
	} elseif (ICL_LANGUAGE_CODE == "fr") {
		$url = site_url() . "/fr/finaliser/payer/" . $order_id . "/?pay_for_order=true&key=" . $order_key . "";
	} else {
		$url = __(site_url() . "/checkout/pay/", "surfsnb") . $order_id . "/?pay_for_order=true&key=" . $order_key . "";
	}
	//$url = site_url()."/checkout/pay/".$order_id."/?pay_for_order=true&key=".$order_key."";
	?>

		<input type="hidden" name="payment_url" id="payment_url" value="<?php echo $url; ?>" />
		<button type="submit" name="proceed_to_payment" class="proceed" value="Proceed to payment" <?php /* onclick="check();" */ ?> style="float:left;margin-right:20px;"><?php _e('Proceed to payment', 'surfsnb_orderProcess'); ?></button>

	</form>
	<?php
}

// Show line breaks in the content > Might cause shortcodes not to work anymore!
remove_filter('the_content', 'wpautop');
remove_filter('the_excerpt', 'wpautop');

add_filter('the_content', 'nl2br');
add_filter('the_excerpt', 'nl2br');

// Hide editor for custom post types
add_action('init', function() {
	remove_post_type_support('offers', 'editor');
	remove_post_type_support('alerts', 'editor');
}, 99);

// Only show in stock product in recent products
add_filter('woocommerce_shortcode_products_query', function( $query_args, $atts, $loop_name ) {
	if ($loop_name == 'recent_products') {
		$query_args['meta_query'] = array(
			array(
				'key' => '_stock_status',
				'value' => 'instock',
				'compare' => '=',
			),
			'relation' => 'OR',
			array(
				'key' => '_stock_status',
				'value' => '',
				'compare' => 'NOT EXISTS'
			),
		);
	}
	return $query_args;
}, 10, 3);

// Save shipping_date
function save_shipping_date() {

	if (isset($_POST['pickup_date_submit'])) {

		// Set pickup date to order
		update_field('field_5a2a8fab22879', $_POST['pickup_date'], $_POST['order_id']);

		// Send email to admin to book shipment
		// Send e-mail that payment has been requested
		// E-mail headers
		$onderwerp = __('Book MFB shipment for client', 'surfsnb_orderProcess');
		$email = get_bloginfo('admin_email');
		setlocale(LC_ALL, 'nld');
		$datum = strftime("%A %d %B %Y", strTOTime(date("Y-m-d H:i:s")));
		$datum_kort = date("d-m-Y");
		//$headers[] = 'From: SurfSnB.com <info@sellandbuy.online>' . "\r\n";
		$headers[] = 'BCC: archive@sellandbuy.online' . "\r\n";
		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		// HTML Template openen		
		$template = file_get_contents("./templates/mailStandard.htm");

		$template = str_replace("{mailStandard_header}", __('Book MFB shipment', 'surfsnb_orderProcess'), $template);
		$template = str_replace("{mailStandard_content}", "Order: " . $_POST['order_id'] . "<br>Pick-up date: " . date('d-m-Y', strtotime($_POST['pickup_date'])), $template);
		$template = str_replace("{mailStandard_link}", "<a href='" . site_url() . "/wp-admin/post.php?post=" . $_POST['order_id'] . "&action=edit'> Click here to go to the order page (Admin only)</a>", $template);

		// Email versturen
		wp_mail($email, $onderwerp, $template, $headers);
	}
}

// end my_theme_send_email
add_action('init', 'save_shipping_date');

// get order id from url
function get_order_id() {
	$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$remove_http = str_replace('http://', '', $url);
	$split_url = explode('/', $remove_http);

	// If still empty
	if ($order_id == "") {
		$order_id = $_GET['order'];
	}

	// If still empty
	if ($order_id == "") {
		$order_id = $split_url[2];
	}

	return $order_id;
}

// Load load
function load_loader() {
	?>
	<div id="loader2" style="position: fixed; left: calc(-50vw + 50%); text-align: center;top:70px;z-index:9999;height: 100%; background-color: rgb(204, 204, 204);opacity: 0.6; width: 100vw !important;display:none;"><img src="<?php echo site_url(); ?>/wp-content/uploads/2017/05/loading.gif" style="width:250px;position: fixed;top: 40%;left: 50%;" /></div>
	<?php
}

// remove Order Notes from checkout field in Woocommerce
add_filter('woocommerce_checkout_fields', 'alter_woocommerce_checkout_fields');

function alter_woocommerce_checkout_fields($fields) {
	unset($fields['order']['order_comments']);
	return $fields;
}

function add_shipping_method_to_order() {

	// Get order ID
	$order_id = $_GET['order'];
	if (empty($order_id)) {
		$order_id = $_GET['lang'];
	}
	$order = wc_get_order($order_id);

	global $wpdb;

	// Add shipping method to this request 
	if (isset($_POST['add_shipping_method_to_order']) || $_POST['shipping_status'] == "Accept") {

		// In case of buyer accepting a shipment proposal based on express offer
		if ($_POST['shipping_status'] == "Accept") {
			foreach ($order->get_items() as $item_id => $item) {
				$product = apply_filters('woocommerce_order_item_product', $order->get_product_from_item($item), $item);
				$productID = $product->get_id();
				if ($productID > 0) {
					break; // Only take the first productID	
				}
			}
			$calc_shipping_method = get_field("product_shipping_express_option", $order_id);
			$shipping_method_total = get_product_field_from_order("product_shipping_fee_show", $order_id, $productID, false);

			// In case of buyer add the whole express fee to his order
		} else {
			$calc_shipping_method = $_POST['calc_shipping_method'];
			$shipping_method_total = $_POST['shipping_method_total_' . $calc_shipping_method];
		}

		WC()->shipping->load_shipping_methods();
		$shipping_methods = WC()->shipping->get_shipping_methods();

		// Remove all other shipping items		
		$wpdb->query("DELETE FROM iewZNddPwoocommerce_order_items WHERE order_id='" . $order_id . "' AND order_item_type='shipping'");

		// Add new shipping item
		if (strpos($calc_shipping_method, ':') !== false) {
			$array = explode(":", $calc_shipping_method);
			$selected_shipping_method = $shipping_methods[$array[0]];
			//echo "<pre>".print_r($shipping_methods,1)."</pre>";
		} else {
			$selected_shipping_method = $shipping_methods[$calc_shipping_method];
		}

		// When Express option, then we need to save which express option as well
		update_field("field_5a74705961a66", $_POST['calc_shipping_method'], $order_id); //product_shipping_express_option

		$shipping = new WC_Order_Item_Shipping();
		$shipping->set_props(array(
			'method_id' => $selected_shipping_method->id,
			'method_title' => $selected_shipping_method->title,
			'total' => wc_format_decimal($shipping_method_total),
			'taxes' => array(),
			'calc_tax' => 'per_order'
		));
		$order->add_item($shipping);

		// Update order fee
		foreach ($order->get_items('fee') as $item_id => $item_fee) {

			$new_fee = ( wc_format_decimal($order->get_subtotal()) + wc_format_decimal($shipping_method_total) ) * 0.03;
			$item_fee->set_total($new_fee);
		}

		// Update totals
		$order->calculate_totals();

		// Save
		$order_id = $order->save();
	}
}

function submit_update_dimensions() {
	if (isset($_POST['submit_update_dimensions'])) {

		update_post_meta($_POST['product_id'], '_weight', $_POST['_weight']);
		update_post_meta($_POST['product_id'], '_length', $_POST['_length']);
		update_post_meta($_POST['product_id'], '_width', $_POST['_width']);
		update_post_meta($_POST['product_id'], '_height', $_POST['_height']);

		$product = wc_get_product($_POST['product_id']);
		//	write_log("####### PRODUCT ID: ".$_POST['product_id']." ".$product->get_title()." #######");
		// Set session to refresh offers
		do_action('woocommerce_custom_update_product_sizes', $_POST['product_id']);
	}
}

// MFB Refresh offers > Set $_SESSION['mfb_refresh_offers']
function mfb_refresh_offers_2($productID) {

	global $woocommerce;

	// Mark that we're coming from update_size or profile_update
	$_SESSION['not_regular_add_to_cart'] = true;
	//	write_log("Set not_regular_add_to_cart session");
	// Set $_SESSION to get new offers from API
	$i++;
	$_SESSION['mfb_refresh_offers'] = true;
	//	write_log("mfb_refresh_offers set to true");
	// Remove the product first
	foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
		if ($cart_item['product_id'] == $productID) {
			//remove single product
			WC()->cart->remove_cart_item($cart_item_key);
			//	write_log("Product removed from the cart: ".$cart_item_key);
		}
	}

	// Add to cart to enable shipping_calculator for products with no sizes
	WC()->cart->add_to_cart($productID);
	//	write_log("And added to the cart again");
}

add_action('woocommerce_custom_update_product_sizes', 'mfb_refresh_offers_2', 10, 1);
add_action('wppb_edit_profile_success', 'mfb_refresh_offers_2');

function mfb_refresh_offers($productID) {

	// Set $_SESSION to get new offers from API
	$_SESSION['mfb_refresh_offers'] = true;
}

add_action('woocommerce_add_to_cart', 'mfb_refresh_offers');
add_action('woocommerce_cart_item_removed', 'mfb_refresh_offers');

// Calculate Shipping Fee Show
function calculate_shipping_fee_show($order_id, $shippingSplit, $shippingFeeTotal) {

	$shippingTotal = 0;
	if ($shippingFeeTotal < 10000) {
		if ($shippingSplit == "shared") {
			$shippingTotal = $shippingFeeTotal / 2;
		} elseif ($shippingSplit == "all_buyer") {
			$shippingTotal = $shippingFeeTotal;
		} elseif ($shippingSplit == "all_seller") {
			$shippingTotal = 0;
		}
	}

	return $shippingTotal;
}

///////


add_action('wp_ajax_filteredproducts', 'filteredproducts');
add_action('wp_ajax_nopriv_filteredproducts', 'filteredproducts');

function filteredproducts() {
	
	global $wpdb, $woocommerce;
	$currency = get_woocommerce_currency_symbol();
	$slug = $_POST["slug"];
	$subcategory = $_POST["sub"];
	$attribute_name = $_POST["attribute_name"];
	$mapsearch = $_POST["mapsearch"];
	$keysearch = $_POST["keysearch"];
	$cptaLimit = 8;
	$cptataxname = 'product_cat';
	$cptaCatName = $slug;
	$cptaType = "product";
	$comma_separated_val = "null";
	$comma_separated_keys = "null";
	$irsfrompaginate = "null";
	$irstopaginate = "null";
	$keyword = "null";
	$irsfrom = $_REQUEST['irsfrom'];
	$irsto = $_REQUEST['irsto'];
	$areacalculatedval = "null";
	$arealngcalculatedval = "null";
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => $cptaLimit,
		'post_status' => 'publish',
		'product_cat' => $slug
	);
	$args['meta_query'] = array(
		array(
			'key' => '_stock_status',
			'value' => 'instock',
			'compare' => '=',
		),
		'relation' => 'OR',
		array(
			'key' => '_stock_status',
			'value' => '',
			'compare' => 'NOT EXISTS'
		),
	);
	$pageargs = array(
		'post_type' => 'product',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'product_cat' => $slug
	);
	$pageargs['meta_query'] = array(
		array(
			'key' => '_stock_status',
			'value' => 'instock',
			'compare' => '=',
		),
		'relation' => 'OR',
		array(
			'key' => '_stock_status',
			'value' => '',
			'compare' => 'NOT EXISTS'
		),
	);

	if ($attribute_name != "") {
		$attribute_value = $_POST['attribute_value'];

		if (is_array($attribute_value)) {
			$comma_separated_val = implode(":", $attribute_value);
			$comma_separated_keys = implode(":", array_keys($attribute_value));
			$tax_query = array('relation' => 'AND');
			foreach ($attribute_value as $key => $val) {
			   
				$tax_query[] = array(
					'taxonomy' => $key,
					'field' => 'slug',
					'terms' => array($val)
				);
			}/**/
			$args['tax_query'] = $tax_query; //array('relation' => 'AND',$mailarray);	
			$pageargs['tax_query'] = $tax_query; //array('relation' => 'AND',$mailarray);				
		}
		if ($irsfrom != '') {
			$irsfrompaginate = $irsfrom;
			$irstopaginate = $irsto;
			$irs[] = $irsfrom;
			$irs[] = $irsto;
			$args['meta_query'] = array('relation' => 'AND',
				array('key' => '_regular_price',
					'value' => $irs,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => '_stock_status',
					'value' => 'instock'
				)
			);
			$pageargs['meta_query'] = array('relation' => 'AND',
				array('key' => '_regular_price',
					'value' => $irs,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => '_stock_status',
					'value' => 'instock'
				)
			);
		}
	}

	// Location Search
	if ($mapsearch != '') {
		$longtitude = $_POST["longtitude"];
		$latitude = $_POST["latitude"];
		$range = 500;
		$bbox = get_bounding_box_deg($latitude, $longtitude, $range);

		// Change order for loop to use on the left
		$args['order'] = 'ASC';
		$args['orderby'] = 'distance';

		// Change meta query
		$area[] = $bbox[1];
		$area[] = $bbox[0];
		$arealng[] = $bbox[2];
		$arealng[] = $bbox[3];
		$areacalculatedval = $latitude;
		$arealngcalculatedval = $longtitude;
		$irsfrom = $_REQUEST['irsfrom'];
		$irsto = $_REQUEST['irsto'];

		if ($irsfrom != '') {
			$irsfrompaginate = $irsfrom;
			$irstopaginate = $irsto;
			$irs[] = $irsfrom;
			$irs[] = $irsto;
			$args['meta_query'] = array('relation' => 'AND',
				array('key' => 'lat',
					'value' => $area,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => 'lng',
					'value' => $arealng,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => '_stock_status',
					'value' => 'instock',
				),
				array('key' => '_regular_price', 'value' => $irs, 'type' => 'numeric', 'compare' => 'BETWEEN'),
				array('key' => '_regular_price', 'value' => $irs, 'type' => 'numeric', 'compare' => 'BETWEEN')
			);
			$pageargs['meta_query'] = array('relation' => 'AND',
				array('key' => 'lat',
					'value' => $area,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => 'lng',
					'value' => $arealng,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => '_stock_status',
					'value' => 'instock'
				),
				array('key' => '_regular_price',
					'value' => $irs,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => '_regular_price',
					'value' => $irs,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				)
			);
		} else {
			$args['meta_query'] = array('relation' => 'AND',
				array('key' => 'lat',
					'value' => $area,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => 'lng',
					'value' => $arealng,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => '_stock_status',
					'value' => 'instock'
				)
			);
			$pageargs['meta_query'] = array('relation' => 'AND',
				array('key' => 'lat',
					'value' => $area,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => 'lng',
					'value' => $arealng,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => '_stock_status',
					'value' => 'instock'
				)
			);
			// Geo query
		}
		$args['geo_query'] = array(
			'lat_field' => 'lat', // this is the name of the meta field storing latitude
			'lng_field' => 'lng', // this is the name of the meta field storing longitude 
			'latitude' => $latitude, // this is the latitude of the point we are getting distance from
			'longitude' => $longtitude, // this is the longitude of the point we are getting distance from
			'distance' => $range, // this is the maximum distance to search
			'units' => 'km'       // this supports options: miles, mi, kilometers, km
		);
		$pageargs['geo_query'] = array(
			'lat_field' => 'lat', // this is the name of the meta field storing latitude
			'lng_field' => 'lng', // this is the name of the meta field storing longitude 
			'latitude' => $latitude, // this is the latitude of the point we are getting distance from
			'longitude' => $longtitude, // this is the longitude of the point we are getting distance from
			'distance' => $range, // this is the maximum distance to search
			'units' => 'km'       // this supports options: miles, mi, kilometers, km
		);
	}
	// Keyword Search
	if ($keysearch != '') {
		$keyword = $_POST["keyword"];

		//$wordcount = str_word_count($keyword);

		$wordExplode = explode(" ", $keyword);

		$wordcount = count($wordExplode);

		if ($wordcount > 1) {
			//if ($wordExplode[0] == 'jp' || $wordExplode[0] == 'JP') {
			$jpWordFirst = $wordExplode[0];
			$jpWordSecond = $wordExplode[1];
			if ($wordExplode[2]) {
				$jpWordthird = $wordExplode[2];
			} else {
				$jpWordthird = "";
			}
			if ($wordExplode[3]) {
				$jpWordFourth = $wordExplode[3];
			} else {
				$jpWordFourth = "";
			}
			if ($wordExplode[4]) {
				$jpWordFifth = $wordExplode[4];
			} else {
				$jpWordFifth = "";
			}
			if ($wordExplode[4]) {
				$jpWordSixth = $wordExplode[5];
			} else {
				$jpWordSixth = "";
			}

			$search_query = 'SELECT ID FROM iewZNddPposts WHERE post_type = "product" AND post_status = "publish" AND  post_title LIKE "%' . $jpWordFirst . '%' . $jpWordSecond . '%' . $jpWordthird . '%' . $jpWordFourth . '%' . $jpWordFifth . '%' . $jpWordSixth . '%" ORDER BY ID DESC ';


			$results = $wpdb->get_results($search_query);
			foreach ($results as $key => $array) {
				$resultquote_ids[] = $array->ID;
			}
//                } else {
//                    $search_query = 'SELECT ID FROM iewZNddPposts WHERE post_type = "product" AND post_status = "publish" AND  post_title LIKE "%' . $keyword . '%" ORDER BY ID DESC';
//                    $results = $wpdb->get_results($search_query);
//                    foreach ($results as $key => $array) {
//                        $quote_ids_full[] = $array->ID;
//                    }
//                    //write_log("Total resultquote_ids: " . print_r($quote_ids_full, 1));
//                    $fullquery = explode(" ", $keyword);
//                    foreach ($fullquery as $fullqueryvalue) {
//                        $search_query = 'SELECT ID FROM iewZNddPposts WHERE post_type = "product" AND post_status = "publish" AND  post_title LIKE "%' . $keyword . '%" ORDER BY ID DESC';
//                        $results = $wpdb->get_results($search_query);
//                        foreach ($results as $key => $array) {
//                            $quote_ids_word[] = $array->ID;
//                        }
//                        if (empty($quote_ids_full)) {
//                            $resultquote_ids = array_unique($quote_ids_word);
//                        } else {
//                            $resultquote_ids = array_unique(array_merge($quote_ids_full, $quote_ids_word));
//                        }
//                    }
//                }
		} else {

			$search_query = 'SELECT ID FROM iewZNddPposts WHERE post_type = "product" AND post_status = "publish" AND  post_title LIKE "%' . $keyword . '%" ORDER BY ID DESC';
			$results = $wpdb->get_results($search_query);

			foreach ($results as $key => $array) {
				$resultquote_ids[] = $array->ID;
			}
		}


		$args['post__in'] = $resultquote_ids;
		$pageargs['post__in'] = $resultquote_ids;
		$args['orderby'] = 'post__in';
		$pageargs['orderby'] = 'post__in';
	}

	// Loop to get all products in category LIMITED at 24
	$loop = new WP_Query($args);
	$pagelimitcount = $loop->post_count;

	// Loop to get all products matching category
	$cloop = new WP_Query($pageargs);
	$count = $cloop->post_count;


	/* 	write_log("Total LIMITED: ".print_r($pagelimitcount,1));
	  write_log("Total wordcount: ".print_r($wordcount,1));
	  write_log("Total pageargs: ".print_r($pageargs,1)); */
	if ($pagelimitcount > 0) {
		$i = 0;
		while ($loop->have_posts()) :
			$loop->the_post();
			$items .= load_template_part('content', 'product');
			$markerarray[$loop->post->post_author] = $loop->post->ID;
			$i++;

			// Stop the while after 24 products
			if ($i > 24) {
				break;
			}
		endwhile;
		wp_reset_query();
		if ($cloop->found_posts == '728') {
			$totalcount = "Please have a look on similiar products.No";
		} else {
			$totalcount = $cloop->found_posts;
		}

		// Use cloop to put all products on the map
		while ($cloop->have_posts()) :
			$cloop->the_post();
			global $product;
			// $totalcount = $i;
			if (load_template_part('content', 'product') != '') {
				$i++;
				$ceilfloorpricce[] = get_post_meta(get_the_ID(), '_regular_price', true);
			}
			//if (!array_key_exists($cloop->post->post_author, $markerarray)) {
			//}
			if ($subcategory != '') {
				$attributes = $product->get_attributes();

				foreach ($attributes as $attribute) {
					$name = $attribute->get_name();
					if ($attribute->is_taxonomy()) {

						$terms = wp_get_post_terms($product->get_id(), $name, 'all');

						foreach ($terms as $term) {
							$single_term = esc_html($term->name);
							$tax_terms[$name][$term->slug] = esc_html($term->name);
						}
					}
				}
			}

		endwhile;
		wp_reset_query();

		// Sort the array
		//write_log("Tax terms: ".print_r($tax_terms,1));
		foreach ($tax_terms as $key => $tax_term) {
			/* if($key == "pa_volume-liters"){
			  asort($tax_term);
			  $tax_terms[$key] = $tax_term;
			  } else { */
			asort($tax_term);
			$tax_terms[$key] = $tax_term;
			//}
		}

		// Only show the last added product from the vendor
		$totallatlng = array();
		foreach ($markerarray as $markerarraykey => $markerarrayvalue) {
			$postal_code = get_user_meta($markerarraykey, 'billing_postcode', true);
			$detailaddress[] = get_user_meta($markerarraykey, 'billing_address_1', true);
			$detailaddress[] = get_user_meta($markerarraykey, 'billing_city', true);
			$detailaddress[] = get_user_meta($markerarraykey, 'billing_state', true);
			$detailaddress[] = $woocommerce->countries->countries[get_user_meta($markerarraykey, 'billing_country', true)];
			$detailaddressresult = array_filter($detailaddress);
			$address = implode("+", $detailaddressresult);
			$detailaddress = array();
			$geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
			$detailaddress = array();
			$geo = json_decode($geo, true);
			if ($geo['status'] == 'OK') {
				$latitude = $geo['results'][0]['geometry']['location']['lat'];
				$longitude = $geo['results'][0]['geometry']['location']['lng'];
				$lastlat = $latitude;
				$lastlng = $longitude;
				$totallatlng[] = array($lastlat, $lastlng);
				$image = wp_get_attachment_image_src(get_post_thumbnail_id($markerarrayvalue), 'single-post-thumbnail');
				$price = get_post_meta($markerarrayvalue, '_regular_price', true);
				$sale = get_post_meta($markerarrayvalue, '_sale_price', true);
				$conditionNr = array_shift(wc_get_product_terms($markerarrayvalue, 'pa_condition', array('fields' => 'names'))); //write_log("Condition: ".$conditionNr);
				if ($conditionNr == "New") {
					$conditionStyle = "sn";
				}
				if ($conditionNr == "Very good condition") {
					$conditionStyle = "s5";
				}
				if ($conditionNr == "Good condition") {
					$conditionStyle = "s4";
				}
				if ($conditionNr == "Decent condition") {
					$conditionStyle = "s3";
				}
				if ($conditionNr == "Bad condition") {
					$conditionStyle = "s2";
				}
				if ($conditionNr == "Very bad condition") {
					$conditionStyle = "s1";
				}
				if ($sale) :
					$maiprice = $currency . $price;
				elseif ($price) :
					$maiprice = $currency . $price;
				endif;

				$test[] = array("latitude" => $latitude,
					"longitude" => $longitude,
					"title" => get_the_title($markerarrayvalue),
					"photo" => get_the_post_thumbnail_url($markerarrayvalue),
					"url" => get_the_permalink($markerarrayvalue),
					"postal_code" => $postal_code, "maiprice" => $maiprice,
					"withoutcurrencyprice" => $price,
					"condition" => $conditionNr,
					"conditionS" => $conditionStyle,
					"authorid" => $markerarraykey
				);
			}
		}
		if (empty($totallatlng)) {
			$ip = $_SERVER['REMOTE_ADDR'];
			$details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
			$address = $details->city . '+' . $details->region . '+' . $details->country;
			$geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
			$geo = json_decode($geo, true);
			if ($geo['status'] = 'OK') {
				$latitude = $geo['results'][0]['geometry']['location']['lat'];
				$longitude = $geo['results'][0]['geometry']['location']['lng'];
				$totallatlng[] = array($latitude, $longitude);
			}
		}/**/
		//write_log("ip 1: ".print_r($_SERVER,1));

		if ($subcategory != '') {
			$totalfilter .= '<div class="heading">';
			foreach ($tax_terms as $tax_terms_attribute_key => $tax_terms_attribute_val) {

				if ($tax_terms_attribute_key == "pa_brand") {

					foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) {
						if ($attribute_value) {
							if (in_array($tax_terms_attribute_single_key, $attribute_value)) {
								$pa_brand_check = "checked";
							} else {
								$pa_brand_check = "";
							}
						}
						$pa_brand .= '<li><input type="radio" ' . $pa_brand_check . '  data-type="' . $tax_terms_attribute_key . '" id="' . $tax_terms_attribute_single_key . '" name="' . $tax_terms_attribute_key . '" data-slug="' . $tax_terms_attribute_single_key . '"  class="filtercheckbox" value="0"> <label for="' . $tax_terms_attribute_single_key . '">&nbsp; ' . $tax_terms_attribute_val_single . '</label>
				</li>';
					}
				} elseif ($tax_terms_attribute_key == "pa_seller") {
					foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) {
						if ($attribute_value) {
							if (in_array($tax_terms_attribute_single_key, $attribute_value)) {
								$pa_seller_check = "checked";
							} else {
								$pa_seller_check = "";
							}
						}
						$pa_seller .= '<li><input type="radio" ' . $pa_seller_check . ' data-type="' . $tax_terms_attribute_key . '" id="' . $tax_terms_attribute_single_key . '" name="' . $tax_terms_attribute_key . '" data-slug="' . $tax_terms_attribute_single_key . '"  class="filtercheckbox" value="0"> <label for="' . $tax_terms_attribute_single_key . '">&nbsp; ' . $tax_terms_attribute_val_single . '</label>
				</li>';
					}
				} elseif ($tax_terms_attribute_key == "pa_years") {
					foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) {
						if ($attribute_value) {
							if (in_array($tax_terms_attribute_single_key, $attribute_value)) {
								$pa_years_check = "checked";
							} else {
								$pa_years_check = "";
							}
						}
						$pa_years .= '<li><input type="radio" ' . $pa_years_check . ' data-type="' . $tax_terms_attribute_key . '" id="' . $tax_terms_attribute_single_key . '" name="' . $tax_terms_attribute_key . '" data-slug="' . $tax_terms_attribute_single_key . '"  class="filtercheckbox" value="0"> <label for="' . $tax_terms_attribute_single_key . '">&nbsp; ' . $tax_terms_attribute_val_single . '</label>
				</li>';
					}
				} elseif ($tax_terms_attribute_key == "pa_blade-size-cm²" || $tax_terms_attribute_key == "pa_blade-sizein²" || $tax_terms_attribute_key == "pa_carbon-number" || $tax_terms_attribute_key == "pa_length-cm" || $tax_terms_attribute_key == "pa_length-feet" || $tax_terms_attribute_key == "pa_size-number" || $tax_terms_attribute_key == "pa_size-xssmlxlxxl" || $tax_terms_attribute_key == "pa_surface-m²" || $tax_terms_attribute_key == "pa_thickness-mm" || $tax_terms_attribute_key == "pa_volume-liters" || $tax_terms_attribute_key == "pa_width-cm" || $tax_terms_attribute_key == "pa_width-inches" || $tax_terms_attribute_key == "pa_boom-size-cm" || $tax_terms_attribute_key == "pa_kitebars-size-m" || $tax_terms_attribute_key == "pa_length-cm" || $tax_terms_attribute_key == "pa_mast-size-cm") {
					$pa_boom .= '<span class="brand-full-name">' . wc_attribute_label($tax_terms_attribute_key) . '</span><br>';
					foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) {
						if ($attribute_value) {
							if (in_array($tax_terms_attribute_single_key, $attribute_value)) {
								$pa_boom_check = "checked";
							} else {
								$pa_boom_check = "";
							}
						}
						$pa_boom .= '<li><input type="radio" ' . $pa_boom_check . ' data-type="' . $tax_terms_attribute_key . '" id="' . $tax_terms_attribute_single_key . '" name="' . $tax_terms_attribute_key . '" data-slug="' . $tax_terms_attribute_single_key . '"  class="filtercheckbox" value="0"> <label for="' . $tax_terms_attribute_single_key . '">&nbsp; ' . $tax_terms_attribute_val_single . '</label>
				</li>';
					}
				} elseif ($tax_terms_attribute_key == "pa_condition" || $tax_terms_attribute_key == "pa_warranty" || $tax_terms_attribute_key == "pa_damage" || $tax_terms_attribute_key == "pa_repair") {
					$pa_condition .= '<span class="brand-full-name">' . wc_attribute_label($tax_terms_attribute_key) . '</span><br>';
					foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) {
						if ($attribute_value) {
							if (in_array($tax_terms_attribute_single_key, $attribute_value)) {
								$checked = "checked";
							} else {
								$checked = "";
							}
						}
						$pa_condition .= '<li><input type="radio" ' . $checked . ' data-type="' . $tax_terms_attribute_key . '" id="' . $tax_terms_attribute_single_key . '" name="' . $tax_terms_attribute_key . '" data-slug="' . $tax_terms_attribute_single_key . '"  class="filtercheckbox" value="0"> <label for="' . $tax_terms_attribute_single_key . '">&nbsp; ' . $tax_terms_attribute_val_single . '</label>
				</li>';
					}
				}
			}
			$totalfilter .= '</div>';
			$json_arr = array("items" => $items, "minimuum" => min($ceilfloorpricce), "maximuum" => max($ceilfloorpricce), "pa_brand" => $pa_brand, "pa_seller" => $pa_seller, "pa_years" => $pa_years, "pa_boom" => $pa_boom, "pa_condition" => $pa_condition, "totalitems" => $totalcount . " products found!", "marker" => $test, "lastlat" => $lastlat, "lastlng" => $lastlng, "totallatlng" => array_unique($totallatlng, SORT_REGULAR));
		} else {
			$json_arr = array("items" => $items, "minimuum" => min($ceilfloorpricce), "maximuum" => max($ceilfloorpricce), "totalitems" => $totalcount . " products found!", "marker" => $test, "totallatlng" => array_unique($totallatlng, SORT_REGULAR));
		}
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
		$details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
		$address = $details->city . '+' . $details->region . '+' . $details->country;
		$geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
		$geo = json_decode($geo, true);
		if ($geo['status'] = 'OK') {
			$latitude = $geo['results'][0]['geometry']['location']['lat'];
			$longitude = $geo['results'][0]['geometry']['location']['lng'];
			$totallatlng[] = array($latitude, $longitude);
		}
		$json_arr = array("items" => '', "totalitems" => "No products found!", "marker" => '', "totallatlng" => $totallatlng);
	}



	$cpta_Query = new WP_Query($pageargs);
	$cpta_Count = count($cpta_Query->posts);
	$cpta_Paginationlist = $cpta_Count / $cptaLimit;
	$last = ceil($cpta_Paginationlist);
	if ($last > 1) {
		$lpm1 = $last - 1;
		$cptaNumber = 1;
		$adjacents = "2";
		if ($cptaNumber > 1) {
			$cptaprev = $cptaNumber - 1;
		}
		if ($cptaNumber < $last) {
			$cptanext = $cptaNumber + 1;
		}
		if ($cptaNumber > 1)
			$pagination .= "<li class='pagitext'><a href='javascript:void(0);' onclick='javascript:cptaajaxPagination($cptaprev,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\")'>Prev</a></li>";
		if ($last < 7 + ($adjacents * 2)) {

			for ($cpta = 1; $cpta <= ceil($cpta_Paginationlist); $cpta++) {
				if ($cptaNumber == $cpta) {
					$active = "active";
				} else {
					$active = "";
				}
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='$active' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($cpta,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$cpta</a></li>";
			}
		} elseif ($last > 5 + ($adjacents * 2)) {
			if ($cptaNumber < 1 + ($adjacents * 2)) {

				for ($cpta = 1; $cpta <= 4; $cpta++) {
					if ($cptaNumber == $cpta) {
						$active = "active";
					} else {
						$active = "";
					}
					$pagination .= "<li><a href='javascript:void(0);' id='post' class='$active' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($cpta,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$cpta</a></li>";
				}

				$pagination .= "<li>......................</li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($lpm1,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$lpm1</a></li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($last,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$last</a></li>";
			} elseif ($last - ($adjacents * 2) > $cptaNumber && $cptaNumber > ($adjacents * 2)) {
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination(1,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>1</a></li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination(2,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>2</a></li>";
				$pagination .= "<li>......................</li>";

				for ($cpta = $cptaNumber - $adjacents; $cpta <= $cptaNumber; $cpta++) {
					if ($cptaNumber == $cpta) {
						$active = "active";
					} else {
						$active = "";
					}
					$pagination .= "<li><a href='javascript:void(0);' id='post' class='$active' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($cpta,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$cpta</a></li>";
				}
				$pagination .= "<li>......................</li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($lpm1,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$lpm1</a></li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($last,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$last</a></li>";
			} else {
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination(1,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>1</a></li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination(2,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>2</a></li>";

				$pagination .= "<li>......................</li>";
				for ($cpta = $last - (2 + ($adjacents * 2)); $cpta <= $last; $cpta++) {
					if ($cptaNumber == $cpta) {
						$active = "active";
					} else {
						$active = "";
					}
					$pagination .= "<li><a href='javascript:void(0);' id='post' class='$active' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($cpta,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$cpta</a></li>";
				}
			}
		}
		if ($cptaNumber < $last - 1)
			$pagination .= "<li class='pagitext'><a href='javascript:void(0);' onclick='javascript:cptaajaxPagination($cptanext,$cptaLimit,\"$slug\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\")'>Next</a></li>";
	}
	$json_arr['pagination'] = $pagination;
	header("Content-Type: application/json", true);
	echo json_encode($json_arr);


	die();
}

add_action('wp_ajax_cptapagination', 'cptapagination_callback');
add_action('wp_ajax_nopriv_cptapagination', 'cptapagination_callback');

function cptapagination_callback() {
	global $wpdb, $woocommerce;
	$cptaNumber = absint($_POST['number']);
	$cptaLimit = absint($_POST['limit']);
	$cptaType = sanitize_text_field($_POST['cptapost']);
	$cptaCatName = sanitize_text_field($_POST['cptacatname']);
	$cptataxname = sanitize_text_field($_POST['cptataxname']);
	$comma_separated_val = "null";
	$comma_separated_keys = "null";
	$irsfrompaginate = "null";
	$irstopaginate = "null";
	$areacalculatedval = "null";
	$arealngcalculatedval = "null";
	$keyword = "null";
	$from = $_POST['from'];
	$filtervalue = $_POST['filtervalue'];
	$keywordsearch = $_POST['keywordvalue'];
	$latitudevalue = $_POST['latitudevalue'];
	$lngvlaue = $_POST['lngvlaue'];
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => $cptaLimit,
		'post_status' => 'publish',
		'product_cat' => $cptaCatName,
		'order' => 'DESC',
		'orderby' => 'ID');
	$pageargs = array(
		'post_type' => 'product',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'product_cat' => $cptaCatName,
		'order' => 'DESC',
		'orderby' => 'ID');
	$args['meta_query'] = array(
		array(
			'key' => '_stock_status',
			'value' => 'instock',
			'compare' => '=',
		),
		'relation' => 'OR',
		array(
			'key' => '_stock_status',
			'value' => '',
			'compare' => 'NOT EXISTS'
		),
	);
	$pageargs['meta_query'] = array(
		array(
			'key' => '_stock_status',
			'value' => 'instock',
			'compare' => '=',
		),
		'relation' => 'OR',
		array(
			'key' => '_stock_status',
			'value' => '',
			'compare' => 'NOT EXISTS'
		),
	);

	if ($cptaNumber == "1") {
		$cptaOffsetValue = 0;
		$args['offset'] = $cptaOffsetValue;
		if ($filtervalue != '' && $filtervalue != 'null') {
			$comma_separated_val = $_POST['filtervalue'];
			$comma_separated_keys = $_POST['filterkey'];
			$attributeval = explode(":", $comma_separated_val);
			$attributekey = explode(":", $comma_separated_keys);
			$tax_query = array('relation' => 'AND');
			foreach ($attributeval as $key => $val) {
				$tax_query[] = array(
					'taxonomy' => $attributekey[$key],
					'field' => 'slug',
					'terms' => array($val)
				);
			}/**/
			$args['tax_query'] = $tax_query; //array('relation' => 'AND',$mailarray);	
			$pageargs['tax_query'] = $tax_query; //array('relation' => 'AND',$mailarray);				
		}
		if ($from != '' && $from != 'null') {
			$from = $_POST['from'];
			$to = $_POST['to'];
			$irsfrompaginate = $from;
			$irstopaginate = $to;
			$irs[] = $from;
			$irs[] = $to;
			$args['meta_query'] = array('relation' => 'AND',
				array('key' => '_regular_price',
					'value' => $irs,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => '_stock_status',
					'value' => 'instock'
				)
			);
			$pageargs['meta_query'] = array('relation' => 'AND',
				array('key' => '_regular_price',
					'value' => $irs,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
				),
				array('key' => '_stock_status',
					'value' => 'instock'
				)
			);
		}
		if ($keywordsearch != '' && $keywordsearch != 'null') {
			$keyword = $keywordsearch;
			$search_query = 'SELECT ID FROM iewZNddPposts WHERE post_type = "product" AND post_status = "publish" AND post_title LIKE "%' . $keywordsearch . '%"';
			$results = $wpdb->get_results($search_query);
			foreach ($results as $key => $array) {
				$quote_ids[] = $array->ID;
			}
			$args['post__in'] = $quote_ids;
			$pageargs['post__in'] = $quote_ids;
		}
		if ($latitudevalue != '' && $latitudevalue != 'null') {
			$longtitude = $lngvlaue;
			$latitude = $latitudevalue;
			$range = 500;
			$bbox = get_bounding_box_deg($latitude, $longtitude, $range);

			// Change order for loop to use on the left
			$args['order'] = 'ASC';
			$args['orderby'] = 'distance';

			// Change meta query
			$area[] = $bbox[1];
			$area[] = $bbox[0];
			$arealng[] = $bbox[2];
			$arealng[] = $bbox[3];
			$areacalculatedval = $latitudevalue;
			$arealngcalculatedval = $lngvlaue;
			if ($from != '' && $from != 'null') {
				$from = $_POST['from'];
				$to = $_POST['to'];
				$irsfrompaginate = $from;
				$irstopaginate = $to;
				$irs[] = $from;
				$irs[] = $to;
				$args['meta_query'] = array('relation' => 'AND',
					array('key' => 'lat',
						'value' => $area,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => 'lng',
						'value' => $arealng,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					),
					array('key' => '_regular_price', 'value' => $irs, 'type' => 'numeric', 'compare' => 'BETWEEN'),
					array('key' => '_regular_price', 'value' => $irs, 'type' => 'numeric', 'compare' => 'BETWEEN')
				);
				$args['meta_query'] = array('relation' => 'AND',
					array('key' => 'lat',
						'value' => $area,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => 'lng',
						'value' => $arealng,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					),
					array('key' => '_regular_price', 'value' => $irs, 'type' => 'numeric', 'compare' => 'BETWEEN'),
					array('key' => '_regular_price', 'value' => $irs, 'type' => 'numeric', 'compare' => 'BETWEEN')
				);
				$args['meta_query'] = array('relation' => 'AND',
					array('key' => '_regular_price',
						'value' => $irs,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					)
				);
				$pageargs['meta_query'] = array('relation' => 'AND',
					array('key' => '_regular_price',
						'value' => $irs,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					)
				);
			} else {
				$args['meta_query'] = array(
					'relation' => 'AND',
					array('key' => 'lat',
						'value' => $area,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => 'lng',
						'value' => $arealng,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					)
				);
				$pageargs['meta_query'] = array(
					'relation' => 'AND',
					array('key' => 'lat',
						'value' => $area,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => 'lng',
						'value' => $arealng,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					)
				);
				// Geo query
			}
			$args['geo_query'] = array(
				'lat_field' => 'lat', // this is the name of the meta field storing latitude
				'lng_field' => 'lng', // this is the name of the meta field storing longitude 
				'latitude' => $latitude, // this is the latitude of the point we are getting distance from
				'longitude' => $longtitude, // this is the longitude of the point we are getting distance from
				'distance' => $range, // this is the maximum distance to search
				'units' => 'km'       // this supports options: miles, mi, kilometers, km
			);
			$pageargs['geo_query'] = array(
				'lat_field' => 'lat', // this is the name of the meta field storing latitude
				'lng_field' => 'lng', // this is the name of the meta field storing longitude 
				'latitude' => $latitude, // this is the latitude of the point we are getting distance from
				'longitude' => $longtitude, // this is the longitude of the point we are getting distance from
				'distance' => $range, // this is the maximum distance to search
				'units' => 'km'       // this supports options: miles, mi, kilometers, km
			);
		}
	} else {
		$cptaOffsetValue = ($cptaNumber - 1) * $cptaLimit;
		$args['offset'] = $cptaOffsetValue;
		if ($filtervalue != '' && $filtervalue != 'null') {
			$comma_separated_val = $_POST['filtervalue'];
			$comma_separated_keys = $_POST['filterkey'];
			$attributeval = explode(":", $comma_separated_val);
			$attributekey = explode(":", $comma_separated_keys);
			$tax_query = array('relation' => 'AND');
			foreach ($attributeval as $key => $val) {
				$tax_query[] = array(
					'taxonomy' => $attributekey[$key],
					'field' => 'slug',
					'terms' => array($val)
				);
			}/**/
			$args['tax_query'] = $tax_query; //array('relation' => 'AND',$mailarray);	
			$pageargs['tax_query'] = $tax_query; //array('relation' => 'AND',$mailarray);				
		}
		if ($from != '' && $from != 'null') {
			$from = $_POST['from'];
			$to = $_POST['to'];
			$irsfrompaginate = $from;
			$irstopaginate = $to;
			$irs[] = $from;
			$irs[] = $to;
			$args['meta_query'] = array(
				'relation' => 'AND',
				array('key' => '_regular_price',
					'value' => $irs,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
			));
			$pageargs['meta_query'] = array(
				'relation' => 'AND',
				array('key' => '_regular_price',
					'value' => $irs,
					'type' => 'numeric',
					'compare' => 'BETWEEN'
			));
		}
		if ($keywordsearch != '' && $keywordsearch != 'null') {
			$keyword = $keywordsearch;
			$search_query = 'SELECT ID FROM iewZNddPposts WHERE post_type = "product" AND post_status = "publish" AND post_title LIKE "%' . $keywordsearch . '%"';
			$results = $wpdb->get_results($search_query);
			foreach ($results as $key => $array) {
				$quote_ids[] = $array->ID;
			}
			$args['post__in'] = $quote_ids;
			$pageargs['post__in'] = $quote_ids;
		}
		if ($latitudevalue != '' && $latitudevalue != 'null') {
			$longtitude = $lngvlaue;
			$latitude = $latitudevalue;
			$range = 1000;
			$bbox = get_bounding_box_deg($latitude, $longtitude, $range);

			// Change order for loop to use on the left
			$args['order'] = 'ASC';
			$args['orderby'] = 'distance';

			// Change meta query
			$area[] = $bbox[1];
			$area[] = $bbox[0];
			$arealng[] = $bbox[2];
			$arealng[] = $bbox[3];
			$areacalculatedval = $latitudevalue;
			$arealngcalculatedval = $lngvlaue;
			if ($from != '' && $from != 'null') {
				$from = $_POST['from'];
				$to = $_POST['to'];
				$irsfrompaginate = $from;
				$irstopaginate = $to;
				$irs[] = $from;
				$irs[] = $to;
				$args['meta_query'] = array('relation' => 'AND',
					array('key' => 'lat',
						'value' => $area,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => 'lng',
						'value' => $arealng,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					),
					array('key' => '_regular_price', 'value' => $irs, 'type' => 'numeric', 'compare' => 'BETWEEN'),
					array('key' => '_regular_price', 'value' => $irs, 'type' => 'numeric', 'compare' => 'BETWEEN')
				);
				$args['meta_query'] = array('relation' => 'AND',
					array('key' => 'lat',
						'value' => $area,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => 'lng',
						'value' => $arealng,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					),
					array('key' => '_regular_price', 'value' => $irs, 'type' => 'numeric', 'compare' => 'BETWEEN'),
					array('key' => '_regular_price', 'value' => $irs, 'type' => 'numeric', 'compare' => 'BETWEEN')
				);
				$args['meta_query'] = array('relation' => 'AND',
					array('key' => '_regular_price',
						'value' => $irs,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					)
				);
				$pageargs['meta_query'] = array('relation' => 'AND',
					array('key' => '_regular_price',
						'value' => $irs,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					)
				);
			} else {
				$args['meta_query'] = array(
					'relation' => 'AND',
					array('key' => 'lat',
						'value' => $area,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => 'lng',
						'value' => $arealng,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					)
				);
				$pageargs['meta_query'] = array(
					'relation' => 'AND',
					array('key' => 'lat',
						'value' => $area,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => 'lng',
						'value' => $arealng,
						'type' => 'numeric',
						'compare' => 'BETWEEN'
					),
					array('key' => '_stock_status',
						'value' => 'instock'
					)
				);
				// Geo query
			}
			$args['geo_query'] = array(
				'lat_field' => 'lat', // this is the name of the meta field storing latitude
				'lng_field' => 'lng', // this is the name of the meta field storing longitude 
				'latitude' => $latitude, // this is the latitude of the point we are getting distance from
				'longitude' => $longtitude, // this is the longitude of the point we are getting distance from
				'distance' => $range, // this is the maximum distance to search
				'units' => 'km'       // this supports options: miles, mi, kilometers, km
			);
			$pageargs['geo_query'] = array(
				'lat_field' => 'lat', // this is the name of the meta field storing latitude
				'lng_field' => 'lng', // this is the name of the meta field storing longitude 
				'latitude' => $latitude, // this is the latitude of the point we are getting distance from
				'longitude' => $longtitude, // this is the longitude of the point we are getting distance from
				'distance' => $range, // this is the maximum distance to search
				'units' => 'km'       // this supports options: miles, mi, kilometers, km
			);
		}
	}

	$loop = new WP_Query($args);
	$count = $loop->post_count;
	$cloop = new WP_Query($pageargs);
	if ($count > 0) {

		// Use loop for the left
		$i = 0;
		while ($loop->have_posts()) : $loop->the_post();
			global $product;
			$items .= load_template_part('content', 'product');
			$markerarray[$loop->post->post_author] = $loop->post->ID;
			$i++;

			// Stop the while after 24 products
			if ($i > 24) {
				break;
			}
		endwhile;
		$totallatlng = array();
		foreach ($markerarray as $markerarraykey => $markerarrayvalue) {
			$postal_code = get_user_meta($markerarraykey, 'billing_postcode', true);
			$detailaddress[] = get_user_meta($markerarraykey, 'billing_address_1', true);
			$detailaddress[] = get_user_meta($markerarraykey, 'billing_city', true);
			$detailaddress[] = get_user_meta($markerarraykey, 'billing_state', true);
			$detailaddress[] = $woocommerce->countries->countries[get_user_meta($markerarraykey, 'billing_country', true)];
			$detailaddressresult = array_filter($detailaddress);
			$address = implode("+", $detailaddressresult);
			$detailaddress = array();
			$geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
			$geo = json_decode($geo, true);
			if ($geo['status'] == 'OK') {
				$latitude = $geo['results'][0]['geometry']['location']['lat'];
				$longitude = $geo['results'][0]['geometry']['location']['lng'];
				// $words = explode(' ', $value['address']);
				//$lastWord = array_pop($words);
				//	$totalcountry[$lastWord] = $lastWord;
				$lastlat = $latitude;
				$lastlng = $longitude;
				$totallatlng[] = array($lastlat, $lastlng);
				$image = wp_get_attachment_image_src(get_post_thumbnail_id($markerarrayvalue), 'single-post-thumbnail');
				$price = get_post_meta($markerarrayvalue, '_regular_price', true);
				$sale = get_post_meta($markerarrayvalue, '_sale_price', true);
				$conditionNr = array_shift(wc_get_product_terms($markerarrayvalue, 'pa_condition', array('fields' => 'names'))); //write_log("Condition: ".$conditionNr);
				if ($conditionNr == "New") {
					$conditionStyle = "sn";
				}
				if ($conditionNr == "Very good condition") {
					$conditionStyle = "s5";
				}
				if ($conditionNr == "Good condition") {
					$conditionStyle = "s4";
				}
				if ($conditionNr == "Decent condition") {
					$conditionStyle = "s3";
				}
				if ($conditionNr == "Bad condition") {
					$conditionStyle = "s2";
				}
				if ($conditionNr == "Very bad condition") {
					$conditionStyle = "s1";
				}
				if ($sale) :
					$maiprice = $currency . $price;
				elseif ($price) :
					$maiprice = $currency . $price;
				endif;

				$test[] = array("latitude" => $latitude,
					"longitude" => $longitude,
					"title" => get_the_title($markerarrayvalue),
					"photo" => get_the_post_thumbnail_url($markerarrayvalue),
					"url" => get_the_permalink($markerarrayvalue),
					"postal_code" => $postal_code, "maiprice" => $maiprice,
					"withoutcurrencyprice" => $price,
					"condition" => $conditionNr,
					"conditionS" => $conditionStyle,
					"authorid" => $markerarraykey
				);
			}
		}
		if (empty($totallatlng)) {
			$ip = $_SERVER['REMOTE_ADDR'];
			$details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
			$address = $details->city . '+' . $details->region . '+' . $details->country;
			$geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
			$geo = json_decode($geo, true);
			if ($geo['status'] = 'OK') {
				$latitude = $geo['results'][0]['geometry']['location']['lat'];
				$longitude = $geo['results'][0]['geometry']['location']['lng'];
				$totallatlng[] = array($latitude, $longitude);
			}
		}
		//	write_log("MarkerArray 2: ".print_r($test,1));

		$json_arr = array("items" => $items, "totalitems" => $count . " properties found!", "marker" => $test, "totallatlng" => array_unique($totallatlng, SORT_REGULAR));
	} else {

		$json_arr = array("items" => '', "totalitems" => "No products found!", "marker" => '', "totallatlng" => '');
	}


	$cpta_Query = new WP_Query($pageargs);
	$cpta_Count = count($cpta_Query->posts);
	$cpta_Paginationlist = $cpta_Count / $cptaLimit;
	$last = ceil($cpta_Paginationlist);
	if ($last > 1) {
		$lpm1 = $last - 1;
		$adjacents = "2";
		if ($cptaNumber > 1) {
			$cptaprev = $cptaNumber - 1;
		}
		if ($cptaNumber < $last) {
			$cptanext = $cptaNumber + 1;
		}
		if ($cptaNumber > 1)
			$pagination .= "<li class='pagitext'><a href='javascript:void(0);' onclick='javascript:cptaajaxPagination($cptaprev,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\")'>Prev</a></li>";
		if ($last < 7 + ($adjacents * 2)) {

			for ($cpta = 1; $cpta <= ceil($cpta_Paginationlist); $cpta++) {
				if ($cptaNumber == $cpta) {
					$active = "active";
				} else {
					$active = "";
				}
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='$active' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($cpta,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$cpta</a></li>";
			}
		} elseif ($last >= 7 + ($adjacents * 2)) {
			if ($cptaNumber < 1 + ($adjacents * 2)) {

				for ($cpta = 1; $cpta <= 4 + ($adjacents * 2); $cpta++) {
					if ($cptaNumber == $cpta) {
						$active = "active";
					} else {
						$active = "";
					}
					$pagination .= "<li><a href='javascript:void(0);' id='post' class='$active' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($cpta,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$cpta</a></li>";
				}

				$pagination .= "<li>......................</li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($lpm1,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$lpm1</a></li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($last,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$last</a></li>";
			} elseif ($last - ($adjacents * 2) > $cptaNumber && $cptaNumber > ($adjacents * 2)) {
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination(1,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>1</a></li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination(2,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>2</a></li>";
				$pagination .= "<li>......................</li>";

				for ($cpta = $cptaNumber - $adjacents; $cpta <= $cptaNumber; $cpta++) {
					if ($cptaNumber == $cpta) {
						$active = "active";
					} else {
						$active = "";
					}
					$pagination .= "<li><a href='javascript:void(0);' id='post' class='$active' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($cpta,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$cpta</a></li>";
				}
				$pagination .= "<li>......................</li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($lpm1,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$lpm1</a></li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($last,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$last</a></li>";
			} else {
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination(1,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>1</a></li>";
				$pagination .= "<li><a href='javascript:void(0);' id='post' class='' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination(2,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>2</a></li>";

				$pagination .= "<li>......................</li>";
				for ($cpta = $last - (2 + ($adjacents * 2)); $cpta <= $last; $cpta++) {
					if ($cptaNumber == $cpta) {
						$active = "active";
					} else {
						$active = "";
					}
					$pagination .= "<li><a href='javascript:void(0);' id='post' class='$active' data-posttype='$cptaType'  data-taxname='$cptataxname'  data-cattype='$cptaCatName' onclick='cptaajaxPagination($cpta,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\");'>$cpta</a></li>";
				}
			}
		}
		if ($cptaNumber < $last - 1)
			$pagination .= "<li class='pagitext'><a href='javascript:void(0);' onclick='javascript:cptaajaxPagination($cptanext,$cptaLimit,\"$cptaCatName\",$irsfrompaginate,$irstopaginate,\"$comma_separated_val\",\"$comma_separated_keys\",\"$keyword\",\"$areacalculatedval\",\"$arealngcalculatedval\")'>Next</a></li>";
	}
	$json_arr['pagination'] = $pagination;
	header("Content-Type: application/json", true);
	echo json_encode($json_arr);
	die();
}

function my_acf_google_map_api($api) {

	$api['key'] = 'AIzaSyD7aKS5iShOUdXTPqYIIeJZULw7lcgQFg0';

	return $api;
}

add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');

// Need below function because in case of multiple products to add to the cart, the shipping_calculator was called already after the first product was added to cart. Now it is only called after the both products have been added.
function add_to_cart_without_call($product_id = 0, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array()) {
	try {
		$product_id = absint($product_id);
		$variation_id = absint($variation_id);

		// Ensure we don't add a variation to the cart directly by variation ID.
		if ('product_variation' === get_post_type($product_id)) {
			$variation_id = $product_id;
			$product_id = wp_get_post_parent_id($variation_id);
		}

		$product_data = wc_get_product($variation_id ? $variation_id : $product_id);
		$quantity = apply_filters('woocommerce_add_to_cart_quantity', $quantity, $product_id);

		if ($quantity <= 0 || !$product_data || 'trash' === $product_data->get_status()) {
			return false;
		}

		// Load cart item data - may be added by other plugins.
		$cart_item_data = (array) apply_filters('woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id);

		// Generate a ID based on product ID, variation ID, variation data, and other cart item data.
		$cart_id = WC()->cart->generate_cart_id($product_id, $variation_id, $variation, $cart_item_data);

		// Find the cart item key in the existing cart.
		$cart_item_key = WC()->cart->find_product_in_cart($cart_id);

		// Force quantity to 1 if sold individually and check for existing item in cart.
		if ($product_data->is_sold_individually()) {
			$quantity = apply_filters('woocommerce_add_to_cart_sold_individually_quantity', 1, $quantity, $product_id, $variation_id, $cart_item_data);
			$found_in_cart = apply_filters('woocommerce_add_to_cart_sold_individually_found_in_cart', $cart_item_key && WC()->cart->cart_contents[$cart_item_key]['quantity'] > 0, $product_id, $variation_id, $cart_item_data, $cart_id);

			if ($found_in_cart) {
				/* translators: %s: product name */
				throw new Exception(sprintf('<a href="%s" class="button wc-forward">%s</a> %s', wc_get_cart_url(), __('View cart', 'woocommerce'), sprintf(__('You cannot add another "%s" to your cart.', 'woocommerce'), $product_data->get_name())));
			}
		}

		if (!$product_data->is_purchasable()) {
			throw new Exception(__('Sorry, this product cannot be purchased.', 'woocommerce'));
		}

		// Stock check - only check if we're managing stock and backorders are not allowed.
		if (!$product_data->is_in_stock()) {
			throw new Exception(sprintf(__('You cannot add &quot;%s&quot; to the cart because the product is out of stock.', 'woocommerce'), $product_data->get_name()));
		}

		if (!$product_data->has_enough_stock($quantity)) {
			/* translators: 1: product name 2: quantity in stock */
			throw new Exception(sprintf(__('You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).', 'woocommerce'), $product_data->get_name(), wc_format_stock_quantity_for_display($product_data->get_stock_quantity(), $product_data)));
		}

		// Stock check - this time accounting for whats already in-cart.
		if ($product_data->managing_stock()) {
			$products_qty_in_cart = WC()->cart->get_cart_item_quantities();

			if (isset($products_qty_in_cart[$product_data->get_stock_managed_by_id()]) && !$product_data->has_enough_stock($products_qty_in_cart[$product_data->get_stock_managed_by_id()] + $quantity)) {
				throw new Exception(sprintf(
						'<a href="%s" class="button wc-forward">%s</a> %s', wc_get_cart_url(), __('View Cart', 'woocommerce'), sprintf(__('You cannot add that amount to the cart &mdash; we have %1$s in stock and you already have %2$s in your cart.', 'woocommerce'), wc_format_stock_quantity_for_display($product_data->get_stock_quantity(), $product_data), wc_format_stock_quantity_for_display($products_qty_in_cart[$product_data->get_stock_managed_by_id()], $product_data))
				));
			}
		}

		// If cart_item_key is set, the item is already in the cart.
		if ($cart_item_key) {
			$new_quantity = $quantity + WC()->cart->cart_contents[$cart_item_key]['quantity'];
			set_quantity($cart_item_key, $new_quantity, false);
		} else {
			$cart_item_key = $cart_id;

			// Add item after merging with $cart_item_data - hook to allow plugins to modify cart item.
			WC()->cart->cart_contents[$cart_item_key] = apply_filters('woocommerce_add_cart_item', array_merge($cart_item_data, array(
				'key' => $cart_item_key,
				'product_id' => $product_id,
				'variation_id' => $variation_id,
				'variation' => $variation,
				'quantity' => $quantity,
				'data' => $product_data,
					)), $cart_item_key);
		}

		//do_action( 'woocommerce_add_to_cart', $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );

		return $cart_item_key;
	} catch (Exception $e) {
		if ($e->getMessage()) {
			wc_add_notice($e->getMessage(), 'error');
		}
		return false;
	}
}

function get_different_counts($order) {

	global $order;
	$order_id = $order->get_id();

	$countConfirmedItems = 0;
	$totalConfirmedDenied = 0;
	$totalProductInOrder = 0;
	$countConfirmedShipmentItems = 0;
	foreach ($order->get_items() as $item_id => $item) {
		$product = apply_filters('woocommerce_order_item_product', $order->get_product_from_item($item), $item);
		
		$productID = $product->get_id();

		// Count total products in order
		$totalProductInOrder++;

		//Add 1 to the number of accepted items
		$orderProductStatusForItem = get_product_field_from_order("product_status", $order_id, $productID, false);
		if ($orderProductStatusForItem == "Confirm") {
			// Count the number of denied/accepted products
			$totalConfirmedDenied++;
			$countConfirmedItems++;
		} elseif ($orderProductStatusForItem == "Deny") {
			// Count the number of denied/accepted products
			$totalConfirmedDenied++;
		}

		//Add 1 to the number of accepted items
		$productShippingStatus = get_product_field_from_order("product_shipping_status", $order_id, $productID, false);
		if ($productShippingStatus == "Accept") {
			$countConfirmedShipmentItems++;
		}
	}

	$counts = array("countConfirmedItems" => $countConfirmedItems,
		"totalConfirmedDenied" => $totalConfirmedDenied,
		"totalProductInOrder" => $totalProductInOrder,
		"countConfirmedShipmentItems" => $countConfirmedShipmentItems
	);

	return $counts;
}

add_action('widgets_init', 'theme_slug_widgets_init');

function theme_slug_widgets_init() {
	register_sidebar(array(
		'name' => __('Footer Social Items', 'footer_social_items'),
		'id' => 'footer_social_items', // ID should be LOWERCASE  ! ! !
		'description' => '',
		'class' => '',
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h2 class="widgettitle">',
		'after_title' => '</h2>'));
}

function load_template_part($template_name, $part_name = null) {
	ob_start();
	wc_get_template_part($template_name, $part_name);
	$var = ob_get_contents();
	ob_end_clean();
	return $var;
}

const R = 6371;

function get_bounding_box_deg($lat, $lng, $range) {
	// latlng in degrees, $range in km
	return array_map(rad2deg, get_bounding_box_rad(deg2rad($lat), deg2rad($lng), $range));
}

function get_bounding_box_rad($lat, $lng, $range) {
	// latlng in radians, $range in km
	$latmin = get_destination_lat_rad($lat, $lng, $range, 0);
	$latmax = get_destination_lat_rad($lat, $lng, $range, deg2rad(180));
	$lngmax = get_destination_lng_rad($lat, $lng, $range, deg2rad(90));
	$lngmin = get_destination_lng_rad($lat, $lng, $range, deg2rad(270));
	// return approx bounding latlng in radians
	return array($latmin, $latmax, $lngmin, $lngmax);
}

function get_destination_lat_rad($lat1, $lng1, $d, $brng) {
	return asin(sin($lat1) * cos($d / R) +
			cos($lat1) * sin($d / R) * cos($brng));
}

function get_destination_lng_rad($lat1, $lng1, $d, $brng) {

	$lat2 = get_destination_lat_rad($lat1, $lng1, $d, $brng);
	return $lng1 + atan2(sin($brng) * sin($d / R) * cos($lat1), cos($d / R) - sin($lat1) * sin($lat2));
}

function my_acf_save_post($post_id) {

	// get new value
	if (isset($_POST['fields'])) {

		$value = get_field('latitude');
		update_post_meta($post_id, 'lat', $value[lat]);
		update_post_meta($post_id, 'lng', $value[lng]);
	}
	// do something
}

add_action('acf/save_post', 'my_acf_save_post', 20);

function get_nearby_locations($lat, $long, $distance) {
	global $wpdb;

	// Radius of the earth 3959 miles or 6371 kilometers.
	$earth_radius = 3959;

	$sql = $wpdb->prepare("
SELECT DISTINCT
p.ID,
p.post_title,
p.post_author,
map_lat.meta_value as locLat,
map_lng.meta_value as locLong,
( %d * acos(
cos( radians( %s ) )
* cos( radians( map_lat.meta_value ) )
* cos( radians( map_lng.meta_value ) - radians( %s ) )
+ sin( radians( %s ) )
* sin( radians( map_lat.meta_value ) )
) )
AS distance
FROM $wpdb->posts p
INNER JOIN $wpdb->postmeta map_lat ON p.ID = map_lat.post_id
INNER JOIN $wpdb->postmeta map_lng ON p.ID = map_lng.post_id
WHERE 1 = 1
AND p.post_type = 'product'
AND p.post_status = 'publish'
AND map_lat.meta_key = 'lat'
AND map_lng.meta_key = 'lng'
HAVING distance < %s
ORDER BY distance ASC", $earth_radius, $lat, $lng, $lat, $distance
	);

	// Uncomment to echo, paste into phpMyAdmin, and debug.
	// echo $sql;

	$nearbyLocations = $wpdb->get_results($sql);

	if ($nearbyLocations) {
		return $nearbyLocations;
	}
}

function redirect_search() {
	if (is_search() && !empty($_GET['s'])) {
		wp_redirect(home_url("/map-search/?keyword=") . urlencode(get_query_var('s')));
		exit();
	}
}

add_action('template_redirect', 'redirect_search');

function getCentroid($coord) {
	$centroid = array_reduce($coord, function ($x, $y) use ($coord) {
		$len = count($coord);
		return array($x[0] + $y[0] / $len, $x[1] + $y[1] / $len);
	}, array(0, 0));
	return $centroid;
}

// Function to update the "Let the seller send me " line in order
function update_shipping_in_order($order_id, $shippingTotal){
	global $wpdb;
	
	// Tweak $shippingTotal for express orders
	/*if(empty($shippingTotal) && shipping_method_is_express($order_id)){
		$shipping_method = get_shipping_method($order_id);
		$shippingTotal = $shipping_method['total'];
	}*/
	
	$myrows = "";
	$myrows = $wpdb->get_results( "SELECT * FROM iewZNddPwoocommerce_order_items WHERE order_id = '".$order_id."' AND order_item_type = 'shipping'" );
	//echo "Rows: <pre>".print_r($myrows,1)."</pre>";
	foreach($myrows as $row){
		// Check if the shipping line is "Let the seller send me..."
		if (strpos($row->order_item_name, 'proposal') !== false) {
			// Get order item id
			$order_item_id = ""; 
			$order_item_id = $row->order_item_id;	
		}
	}
	
	// Transform to 2 decimals
	$shippingTotal = number_format((float)$shippingTotal, 2, '.', '');
	
	// Add line
	$wpdb->update( 
		'iewZNddPwoocommerce_order_itemmeta', 
		array( 'meta_value' 		=> $shippingTotal ), 
		array( 'order_item_id' 		=> $order_item_id,
			   'meta_key' 			=> "cost" )
	);

	// Update Final Shipping Total
	update_post_meta($order_id, "_order_shipping", $shippingTotal); // Order amount
	update_field( "field_57b9f43909285", $shippingTotal, $order_id ); // Custom Field amount

	// Now also update the Surfsnb Fee
	update_fee_in_order($order_id);
	
	// Now also update the Order Totals
	update_totals_in_order($order_id);

}

function update_fee_in_order($order_id){
	
	global $wpdb;
	
	$order = wc_get_order($order_id);
	
	$productTotal = $order->get_subtotal();
	$shippingTotal = $order->get_shipping_total();  write_log("shippingTotal from update_fee_in_order: ".$shippingTotal);
	$discount_total = $order->get_discount_total();
	
	$orderSubtotal = $productTotal + $shippingTotal - $discount_total;
	$surfsnbFee = $orderSubtotal * 0.03;
	$surfsnbFee = number_format((float)$surfsnbFee, 2, '.', '');   write_log("surfsnbFee from update_fee_in_order: ".$surfsnbFee);
	
	$myrows = "";
	$myrows = $wpdb->get_results( "SELECT * FROM iewZNddPwoocommerce_order_items WHERE order_id = '".$order_id."' AND order_item_name = 'SurfSnB Fee 3%'" );
	$order_item_id = ""; $order_item_id = $myrows[0]->order_item_id;
	
	// Update the fee
	$wpdb->update( 
		'iewZNddPwoocommerce_order_itemmeta', 
		array(	'meta_value' 	=> $surfsnbFee ), 
		array(	'order_item_id' => $order_item_id,
				'meta_key' 		=> '_line_total' )
	);
	$wpdb->update( 
		'iewZNddPwoocommerce_order_itemmeta', 
		array(	'meta_value' 	=> $surfsnbFee ), 
		array(	'order_item_id' => $order_item_id,
				'meta_key' 		=> '_line_subtotal' )
	);
}

function update_totals_in_order($order_id){
	
	$order = wc_get_order($order_id);
	
	$productTotal = $order->get_subtotal();
	$shippingTotal = $order->get_shipping_total(); //write_log("Shipping_total: ".$shippingTotal);
	$discount_total = $order->get_discount_total();

	// Add them together and deduct the discount total
	$orderTotal = $productTotal + $shippingTotal - $discount_total;
	$surfsnbFee = $orderTotal * 0.03;
	$finalOrderTotal = $orderTotal + $surfsnbFee;
	
	// Update Final Order Total
	update_post_meta($order_id, "_order_total", $finalOrderTotal); // Order amount
	update_field( "field_57b2f9f01b3da", $orderTotal, $order_id ); // Custom Field amount

	$order->calculate_totals();
		
}

// De-register some thumbnail sizes
add_action('init', 'remove_plugin_image_sizes');
function remove_plugin_image_sizes() {
	remove_image_size('medium_large');
	remove_image_size('large');
	remove_image_size('post-thumbnail');
	remove_image_size('wpsm_testi_small');
	
	remove_image_size('dash-sidebar-thumb');
	remove_image_size('dash-single-product-thumbs');
	remove_image_size('dash-recent-posts-thumb');
	remove_image_size('dash-carousel-medium');
	remove_image_size('dash-carousel-large');
	
	remove_image_size('dash-cat-thumb');
	remove_image_size('dash-vendor-main-logo');
	remove_image_size('dash-vendor-logo-icon');
	
	remove_image_size('shop_catalog');
	remove_image_size('shop_single');
	remove_image_size('shop_thumbnail');
	
}

function wp_all_export_csv_rows($articles, $options, $export_id) {
  foreach ($articles as $key => $article) {
	// get user date
	$user_updated = $article["my_last_update"];
	// get export date
	$export_date = $article["my_export_date"];
        // The two lines commented out below can be used for debugging. They save the data to WP Options so that you can see what the function is outputting.
	//$option_key = 'some_debug_' . $article['id'];
	//update_option( $option_key, 'Last updated: ' . date( "Y-m-d H:i:s", $user_updated ) . ' | Export Date: ' . date( "Y-m-d H:i:s", $export_date ) );
	if ($user_updated < $export_date) {
	  unset($articles[$key]);
		}
    }
    return $articles; // Return the array of records to export
}
add_filter('wp_all_export_csv_rows', 'wp_all_export_csv_rows', 10, 3);

//add_filter( 'template_include', 'portfolio_page_template', 99 );

function portfolio_page_template( $template ) {

	echo $template;
}

// Add theme logo
add_theme_support( 'custom-logo' );
