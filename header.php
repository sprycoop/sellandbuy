<?php
/* The Header */
?>

<!DOCTYPE html>

<html <?php language_attributes(); ?>>
    <head>
    
    <!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-86727406-2"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    
      gtag('config', 'UA-86727406-2');
    </script>

    
        <meta property="fb:app_id" content="295341000839187" />
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,user-scalable=0"/>       
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php esc_url(bloginfo('pingback_url')); ?>">    
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">   
        <link href='https://fonts.googleapis.com/css?family=Teko' rel='stylesheet'>
        <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'> 
        <link rel="stylesheet" href="/wp-content/themes/dashstore-child/mobile_header/css/bootstrap.min.css" type="text/css">
        <link href="/wp-content/themes/dashstore-child/mobile_header/css/font-awesome.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="/wp-content/themes/dashstore-child/mobile_header/css/style.css">
        <link rel="stylesheet" href="/wp-content/themes/dashstore-child/mobile_header/css/responsive.css">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
        
        <?php wp_enqueue_script('custom-theme-scripts', get_stylesheet_directory_uri() . '/js/custom-theme-scripts.js', array('jquery'), null, true); ?>
        
        <?php wp_head(); ?>

    </head>


    <body <?php body_class(); ?> itemscope="itemscope" itemtype="http://schema.org/WebPage">

        <?php
        // Show linebreak in debug log
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        write_log("======" . $actual_link . "=====================================================================================================================================");

        // Redirect failed payments
        redirect_failed_payments();

        // Main wrapper start
        if (function_exists('dash_site_wrapper_start'))
            dash_site_wrapper_start();
        ?>

        <?php // Login form   ?>

        <div id="header-login-form-greyout"></div>
        <div id="header-login-form-outer">
            <div id="header-login-form" class="top-nav-container header-login-form">
                <form name="loginform" id="loginform" action="<?php echo site_url(); ?>/wp-login.php" method="post">
                    
                    <p class="login-username">
                        <label for="user_login"><?php _e("Username or Email Address", "surfsnb"); ?></label>
                        <input type="text" name="log" id="user_login" class="input" value="" size="20" placeholder="Username">
                    </p>
                    <p class="login-password">
                        <label for="user_pass"><?php _e("Password", "surfsnb"); ?></label>
                        <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" placeholder="Password">
                    </p>
                    <p class="login-remember"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever"><?php _e("Remember Me", "surfsnb"); ?></label></p>
                    <p class="login-submit">
                        <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary" value="Log In">
                        <input type="hidden" name="redirect_to" value="<?php echo $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
                    </p>
                    <div class="fb-login-button">
                    	<?php $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>
                        <a href="<?php echo wp_login_url(); ?>?loginSocial=facebook&redirect_uri=<?php echo $actual_link; ?>" data-plugin="nsl" data-action="connect" data-redirect="current" data-provider="facebook" data-popupwidth="475" data-popupheight="175">
                            <img src="https://dev.sellandbuy.online/wp-content/uploads/2016/12/fb_38.png" alt="" />
                        </a>
                    </div>
                </form>
            </div>	
        </div>

        <?php
        // Header 

        global $detect;
        ?>

        <div class="mobile">
            <?php
            $user_id = get_current_user_id();
            global $wpdb;
            $table_name = $wpdb->prefix . 'users';
            $user_meta_table_name = $wpdb->prefix . 'usermeta';
            $post_table_name = $wpdb->prefix . 'posts';
            $getuserDetails = $wpdb->get_row("SELECT * FROM $table_name WHERE ID= $user_id ");
            $checkNotificationCount = $wpdb->get_row("SELECT count(*) AS notifications FROM $post_table_name WHERE post_status = 'publish' AND post_type='notifications' AND post_author = $user_id ");
            if ($checkNotificationCount->notifications == '0') {
                $notification = '2';
            } else {
                $notification = $checkNotificationCount->notifications;
            }
            $getProfilePic = $wpdb->get_row("SELECT $post_table_name.guid FROM $user_meta_table_name LEFT JOIN $post_table_name ON $post_table_name.ID =$user_meta_table_name.meta_value  WHERE $user_meta_table_name.user_id = $user_id AND $user_meta_table_name.meta_key = 'profile_picture'");
            $profile_picture = $getProfilePic->guid;
            global $post;
            $post_slug = $post->post_name;
            $post_data = get_post($post->post_parent);
            $parent_slug = $post_data->post_name;
            
            ?>
            <?php
            if ($post_slug == 'map-search' || $parent_slug == 'products' || $parent_slug == 'help' || $post_slug == 'news' || $post_slug == 'cart' || $parent_slug == 'my-account' || $parent_slug == 'my-shop' || $parent_slug == 'az' || $GLOBALS['pagenow'] == 'wp-login.php' || is_product() || is_home() || is_single() || $post_slug == 'checkout') {
                $blue_class = 'mobile-bg-scroll map_page_class';
                $map_header_class = 'map-header-class';
                $location_class1 = 'location_mobile_class';
            } else {
                $blue_class = '';
                $map_header_class = '';
                $location_class1 = '';
            }
            ?>
            <?php
            if (is_product()) {
                $headerClass = 'map-header-class';
                $blue_class = 'mobile-bg-scroll map_page_class';
                $location_class = 'location_mobile_class';
            } else {
                $headerClass = '';
                $blue_class = '';
                $location_class = '';
            }
            ?>
            <header class="<?= $map_header_class . ' ' . $headerClass; ?>  site-mobile-header site-header responsive-mobile-menu"<?php if (function_exists('dash_custom_header_bg')) dash_custom_header_bg(); ?> itemscope="itemscope" itemtype="http://schema.org/WPHeader"><!-- Site's Header -->

                <form id="location-searchm" class="one" method="GET" action="<?php _e("/catalog/", "surfsnb"); ?>">

                    <div class="header-top"><!-- Header top section -->
                        <div class="container">
                            <div class="row">

                                <div class="width-100 <?= $blue_class ?>">

                                    <?php if ($post_slug == 'catalog' || $post_slug == 'map' || $post_slug == 'map-search' || $parent_slug == 'help' || $post_slug == 'news' || $post_slug == 'cart' || $parent_slug == 'products' || $parent_slug == 'my-account' || $parent_slug == 'my-shop' || $GLOBALS['pagenow'] == 'wp-login.php' || is_product() || $post_slug == 'checkout') { ?>
                                        <div id="header-logo-mobile-1" class="top-nav-container header-logo">
                                            <a href="javascript:void(0)" rel="home" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar" title="Surf Sell &amp; Buy" itemprop="url" onclick="addbodyClass()">
												<img class="blue-logo" src="<?php echo get_stylesheet_directory_uri(); ?>/img/mobile_logo_blue.png" alt="Easily sell and buy your surfgear online!" itemprop="logo" style="display:block;">
                                            </a>
                                        </div> 
                                    <?php } else {
                                        ?>
                                        <div id="header-logo-mobile-1" class="top-nav-container header-logo">
                                            <a href="javascript:void(0)" rel="home" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar" title="Surf Sell &amp; Buy" itemprop="url" onclick="addbodyClass()">
                                                <img class="white-logo" src="<?php echo get_stylesheet_directory_uri(); ?>/img/mobile_logo_white.png" alt="Easily sell and buy your surfgear online!" itemprop="logo">
                                                <img class="blue-logo" src="<?php echo get_stylesheet_directory_uri(); ?>/img/mobile_logo_blue.png" alt="Easily sell and buy your surfgear online!" itemprop="logo">
                                            </a>
                                        </div> 
                                        <?php
                                    }
                                    // Logo mobile  
//                                    echo '<pre>';
//                                    print_r($_SERVER);
//                                    echo '</pre>';
                                    ?>

									<?php if ( $GLOBALS['pagenow'] === 'wp-login.php' ) { ?>
                                    
                                    <?php show_header_menu(); ?>
                                    
                                    <?php } else { ?>
                                
                                    <!--[borlabs cache start: NbJz4aQ07unrVXy3]-->
                                    show_header_menu_mobile();
                                    <!--[borlabs cache end: NbJz4aQ07unrVXy3]-->
                                    
                                    <?php } ?>

                                    <div class="homepage-button-wrapper">
                                        <div class="button-add-alert sprykeyword">
											
											<?php if ( $GLOBALS['pagenow'] === 'wp-login.php' ) { ?>
                                            
                                            <?php mobile_header_keyword_search(); ?>
                                            
                                            <?php } else { ?>
                                            
                                            <!--[borlabs cache start: NbJz4aQ07unrVXy3]--> 
                                            echo mobile_header_keyword_search();
                                            <!--[borlabs cache end: NbJz4aQ07unrVXy3]-->
                                            
                                            <?php } ?>
                                            							
                                            <span id="banner_search_icon"  class="custom-location-submit check">
                                                <i class="fa fa-fw" aria-hidden="true" title="search"></i>
                                            </span>  
                                        </div>
                                    </div>
                                    <input style="display:none"  type="submit" value="submit">                                   


                                    <div id="navbar" class="navbar-collapse collapse">
                                        <div class="menu-top-bar">
                                            <div class="home-icon">
                                                <a href="/" title=""><span class="glyphicon glyphicon-home"></span> <strong>Home</strong></a>
                                            </div>
                                            <div class="add-product-1">
                                                <a href="<?php echo site_url() . __("/my-shop/product/edit/", "surfsnb"); ?>" class=""><span class="glyphicon glyphicon-plus-sign"></span> Products</a><strong><a href="<?php echo site_url() . __("/my-shop/product/edit/", "surfsnb"); ?>">Add a product</a></strong>
                                            </div>
                                            <div class="add-alert-1">
                                                <a href="<?php echo site_url() . __("/my-shop/add-alert/", "surfsnb"); ?>" class=""><span class="glyphicon glyphicon-bell"></span>Alert</a><strong> <a href="<?php echo site_url() . __("/my-shop/add-alert/", "surfsnb"); ?>">Add an alert</a></strong>
                                            </div>
                                            <?php echo do_shortcode('[wpmenucart]'); ?>
                                        </div>
                                        <ul class="nav navbar-nav">
                                            <?php
                                            // Login link 
                                            if (!is_user_logged_in()) {
                                                ?>
                                                <li>

                                                    <div id="header-login" class="dropdown" style="display:block;">
                                                        <div class="item header-login-link menu-gray-text"><?php _e("Login", "surfsnb"); ?></div>
                                                    </div>

                                                </li>
                                                <li>

                                                    <div id="header-login" class="dropdown" style="display:block;">
                                                        <div class="item header-register-link menu-gray-text"><?php _e("Register", "surfsnb"); ?></div>
                                                    </div>

                                                </li>
                                                <?php
                                            }

                                            if (is_user_logged_in()) {
                                                ?>
                                                <li class="dropdown">
                                                    <a href="javascript:void(0)" class="dropdown-toggle" onclick="showMyProfile()">
                                                        <?php if (!empty($profile_picture)) { ?>
                                                            <img class="user-img" src="<?= $profile_picture; ?>" alt="">
                                                        <?php } else { ?>
                                                            <img class="user-img" src="/wp-content/uploads/2016/12/person-default.png" alt="">
                                                        <?php } ?>
                                                        <?= $getuserDetails->user_nicename; ?> 
                                                        <span class="caret"></span> <span class="red-bg">
                                                            <?= $notification; ?>
                                                        </span>
                                                    </a>
                                                    <ul class="dropdown-menu" id="showMyProfile">
                                                        <li><a href="/my-account/profile/">My profile</a></li>
                                                        <li><a href="/my-account/notifications/">My Notifications</a></li>
                                                        <li><a href="/my-account/my-requests/">My Requests</a></li>
                                                        <li><a href="/my-account/my-offers-buying/">My Offers</a></li>
<!--                                                            <li><a href="/my-account/wishlist/">What Interest me</a></li>-->
                                                        <li><a href="<?php echo wp_logout_url(); ?>">Logout</a></li>
                                                    </ul>
                                                </li>
                                                <li class="dropdown">
                                                    <a href="javascript:void(0)" class="dropdown-toggle" onclick="showShop()">My Shop <span class="caret"></span></a>
                                                    <ul class="dropdown-menu" id="showShop">
                                                        <li><a href="/my-shop/product/edit/">Add a Product</a></li>
                                                        <li><a href="/my-shop/product/">Products</a></li>
                                                        <li><a href="/my-shop/received-requests/">Requests</a></li>
                                                        <li><a href="/my-shop/my-offers-selling/">Offers</a></li>
                                                        <li><a href="/shops/admin/ratings/">Ratings</a></li>
                                                        <li><a href="/my-shop/settings/#payment">Payout Details</a></li>
                                                    </ul>
                                                </li>
                                            <?php } ?>

                                            <li class="dropdown">
                                                <a style="width:55px;" href="javascript:void(0)" class="dropdown-toggle" onclick="helpMenu()">Help<span class="caret"></span></a>

                                                <span class="menu-gray-text">Find some help.</span>
                                                <ul class="dropdown-menu" id="helpMenu">
                                                    <li><a href="/help/order-process/">How does it work?</a></li>
                                                    <li><a href="/help/add-a-product/">How to add a product?</a></li>
                                                    <!--<li><a href="/help/advertising-fees/">Advertising fees?</a></li>-->
                                                    <li><a href="/help/how-can-i-claim-my-money/">How Can I claim my money?</a></li>
                                                    <li><a href="/help/withdrawal-form/">Withdrawl form?</a></li>
                                                    <li><a href="/help/leave-feedback/">Leave Feedback?</a></li>
                                                    <li><a href="/help/chart-surfsnb/">Chart Surfsnb?</a></li>
                                                    <li><a href="/help/conditions-of-use-and-sale/">Conditions of use & sale?</a></li>
                                                    <li><a href="/help/about-us/">About Us?</a></li>
                                                    <li><a href="/help/contact-us/">Contact Us?</a></li>							

                                                </ul>
                                            </li>
                                            <li><a href="/news/" class="news-box">News</a><span class="menu-gray-text">All news about sellandbuy.online!</span></li>
                                            <li><a href="http://snow.sellandbuy.online/"><img src="/wp-content/themes/dashstore-child/mobile_header/images/snowsnb_logo.png" alt=""> Snowsnb, the platform to sell and buy snow equlpement.</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="header-extra" class="header-extra mobile-header-extra frontpage_image">
                        <div class="header-primary-nav"><!-- Primary nav -->
                            <div class="container" style="width:100%;">
                                <div class="row">

                                    <nav class="primary-nav" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement"> 

                                        <?php
                                        if ($post_slug != 'map-search') {
                                            ?>
                                            <div class="text-outer <?= $location_class; ?> <?= $location_class1; ?>">    

                                                <div class="text-header"><?php _e("SELL & BUY<BR>YOUR SECOND HAND OR<BR>NEW EQUIPMENT", "surfsnb"); ?></div>    

                                                <div class="homepage-button-wrapper">
                                                    <div class="button-add-product <?= $location_class; ?>"><input type="text" name="location" id="locationm" class="three banner_search_location " placeholder="Search location..."><span id="banner_location_icon" class="custom-locationm-submit check2"><i class="fa fa-map-marker"></i></span></div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </nav>

                                </div>
                            </div>
                        </div><!-- end of Primary nav -->
                    </div>
                </form>
            </header><!-- end of Site's Header -->
        </div>
        
        
        <div class="desktop">
            <header class="site-desktop-header site-header"<?php if (function_exists('dash_custom_header_bg')) dash_custom_header_bg(); ?> itemscope="itemscope" itemtype="http://schema.org/WPHeader"><!-- Site's Header -->
                <div class="header-top"><!-- Header top section -->
                    <div class="container">
                        <div class="row">
                            <div class="width-100">

                                <?php // Logo              ?>
                                <div id="header-logo" class="top-nav-container header-logo">
                                    <div class="logo-wrapper"><!-- Logo & hgroup -->
                                        <div class="container">
                                            <div class="row">
                                                <?php get_template_part('partials/logo-group'); ?>
                                            </div>
                                        </div>
                                    </div><!-- end of Logo & hgroup -->
                                </div>

                                <?php // Logo mobile              ?>
                                <div id="header-logo-mobile" class="top-nav-container header-logo">
                                    <div class="logo-wrapper"><!-- Logo & hgroup -->
                                        <div class="container">
                                            <div class="row">
                                                <?php get_template_part('partials/logo-group'); ?>
                                            </div>
                                        </div>
                                    </div><!-- end of Logo & hgroup -->
                                </div>       

                                <?php // Short links           ?>
                                <div id="products-help-news">
                                    <div class="item"><a href="<?php _e("/catalog/", "surfsnb"); ?>"><?php _e("Products", "surfsnb"); ?></a></div>
                                    <?php wp_nav_menu(array('theme_location' => 'header-help')); ?>
                                    <div class="item"><a href="<?php _e("/news/", "surfsnb"); ?>"><?php _e("News", "surfsnb"); ?></a></div>
                                </div>

                                <?php // Search              ?>
                                <div class="top-nav-container header-search top-nav-searchbox search-normal" style="height:70px;float:left;overflow:hidden;width: 200px;">
                                    <div class="top-nav-search" class=" suppa_menu suppa_menu_dropdown suppa_menu_1" style="height:37px;">                                             
                                        <div id="search-normal">
                                            <form id="search_box" class="isp_search_box_form" name="search_box" method="GET" action="/catalog/" style="float:none;">
                                                <input type="text" name="search_keyword" class="isp_search_box_input" placeholder="<?php _e("Search....", "surfsnb"); ?>" autocomplete="Off" autocorrect="off" autocapitalize="off" style="outline: none; width: 100% !important; height:2.3rem;" id="isp_search">
                                                <a href="javascript:void(0)"><i class="fa fa-search"></i></a>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ( $GLOBALS['pagenow'] === 'wp-login.php' ) { ?>
                                
                                <?php show_header_menu(); ?>
                                
                                <?php } else { ?>
                                
                                <!--[borlabs cache start: NbJz4aQ07unrVXy3]--> 
								show_header_menu();
                                <!--[borlabs cache end: NbJz4aQ07unrVXy3]-->
								
                                <?php } ?>
                                
                                <?php
                                // Login link 
                                if (!is_user_logged_in()) {
                                    ?>
                                    <div id="header-login">
                                        <div class="item header-register-link"><?php _e("Register", "surfsnb"); ?></div>
                                        <div class="item">&nbsp; &nbsp; |&nbsp; &nbsp; </div>
                                        <div class="item header-login-link"><?php _e("Login", "surfsnb"); ?></div>
                                    </div>
                                <?php } ?>

                                <?php // Login form moved outside of header           ?>

                                <?php // Add product button             ?>
                                <div id="add-button" class="add-product">
                                    <a href="<?php echo site_url() . __("/my-shop/product/edit/", "surfsnb"); ?>" class="suppa_top_level_link suppa_menu_position_left suppa_top_links_has_arrow">
                                        <div class="add_a_product_text">
                                            <div class="add-icon-outer"><img src="<?php echo site_url(); ?>/wp-content/uploads/add-icon.png" class="add-icon"></div>
                                            <div class="add-product-outer"><?php _e("Product", "surfsnb"); ?></div>
                                        </div>
                                    </a>
                                </div>

                                <?php // Add alert button              ?>
                                <div id="add-button" class="add-alert">
                                    <a href="<?php echo site_url() . __("/my-shop/add-alert/", "surfsnb"); ?>" class="suppa_top_level_link suppa_menu_position_left suppa_top_links_has_arrow">
                                        <div class="add_a_product_text">
                                            <div class="add-icon-outer"><img src="<?php echo site_url(); ?>/wp-content/uploads/add-alert.png" class="add-icon"></div>
                                            <div class="add-product-outer"><?php _e("Alert", "surfsnb"); ?></div>
                                        </div>
                                    </a>
                                </div>                        

                                <?php // Search mobile              ?>
                                <div class="top-nav-searchbox" style="height:70px;float:left;overflow:hidden;">
                                    <div class="top-nav-search" class=" suppa_menu suppa_menu_dropdown suppa_menu_1">                                             						
                                        <div id="search-mobile-top-icon">
                                            <img src="<?php echo site_url(); ?>/wp-content/uploads/2017/08/search-25-white.png" class="search-mobile-icon" />
                                        </div>	
                                    </div>
                                </div>

								<?php // Cart              ?>
                                <div class="top-nav-container header-search wpmenucart-display-standard" style="height:70px;float:left;overflow:hidden;margin-left:-5px;">
                                    <div class="top-nav-search" class="suppa_menu suppa_menu_dropdown suppa_menu_1">
                                        <?php echo do_shortcode('[wpmenucart]'); ?>
                                    </div>
                                </div>

								<?php if ( $GLOBALS['pagenow'] === 'wp-login.php' ) { ?>
                                
                                <?php show_header_menu(); ?>
                                
                                <?php } else { ?>
                            
                                <!--[borlabs cache start: NbJz4aQ07unrVXy3]-->
                                show_header_menu_mobile();
                                <!--[borlabs cache end: NbJz4aQ07unrVXy3]-->
                                
                                <?php } ?>

                            </div>

                            <?php // Search mobile              ?>
                            <div class="search-mobile-outer width-50">
                                <div class="top-nav-container header-mobile-nav top-mobile-main" style="float:left;">
                                    <form class="isp_search_box_form" name="isp_search_box" method="GET" action="/catalog/" style="float:none;">
                                        <input type="text" name="search_keyword" class="isp_search_box_input" placeholder="Search..." autocomplete="Off" autocorrect="off" autocapitalize="off" style="outline: none; width: 100% !important; height:2.3rem;" id="isp_search_mobile"></input>
                                    </form>
                                    <div class="search_icon_when_search_open">
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/12/close.png" class="search-mobile-icon-close" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </header><!-- end of Site's Header -->
        </div> <!-- end desktop -->

        <?php
        if (is_front_page())
            do_action('woocommerce_archive_description');

// Get term id from category pages
        $term_id = $wp_query->get_queried_object()->term_id;

// If not a category page
        if ($term_id == "") {

            // Might be a product
            global $post;
            $terms = get_the_terms($post->ID, 'product_cat');
            if ($terms) {
                foreach ($terms as $term) {
                    $term_id = $term->term_id;
                    break;
                }
            }
        }

        if (function_exists("get_term_top_most_parent")) {
            $parent = get_term_top_most_parent($term_id, 'product_cat');
        }
        $vendor_shop = urldecode(get_query_var('vendor_shop'));
        $vendor_id = WCV_Vendors::get_vendor_id($vendor_shop);

        $url = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $product_groups = array("windsurf", "kitesurf", "stand-up-paddle", "surf", "surfwear", "actioncam");
        foreach ($product_groups as $product_group) {
            if (strpos($url, $product_group) !== false) {
                $is_product = true;
            }
        }

        if (is_front_page()) {
            ?>

            <div class="desktop">
                <div id="header-extra" class="header-extra header-extra-desktop frontpage_image">
                    <div class="header-primary-nav"><!-- Primary nav -->
                        <div class="container" style="width:100%;">
                            <div class="row">
                                <nav class="primary-nav" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
                                    <img src="/wp-content/uploads/banner-2018-cut.jpg" class="frontpage_image_img">
                                    <div class="text-outer">
                                        <div class="text-header"><?php _e("SELL & BUY<BR>YOUR SECOND HAND OR<BR>NEW EQUIPMENT", "surfsnb"); ?></div>
                                        <div class="text-sub"><?php _e("Private or pro, add or find your windsurf,<br>kitesurf, stand up paddle, surf, surfwear or action cameras", "surfsnb"); ?></div>
                                    </div>
                                    <div class="homepage-button-outer"> 
                                        <div class="homepage-button-wrapper"><form id="location-search" class="two" method="GET" action="<?php _e("/catalog/", "surfsnb"); ?>">
                                                <div class="button-add-product"><input type="text" name="location" id="location" class="four banner_search_location " placeholder="Search location..."><span id="banner_location_icon" class="custom-location-submit check3"><i class="fa fa-map-marker"></i></span></div>
                                                <div class="button-add-alert"><input type="text" name="search_keyword"  class="banner_keyword_search" placeholder="Keyword search..."><span id="banner_search_icon"  class="custom-location-submit check4"><i class="fa fa-fw" aria-hidden="true" title="search"></i></span></div><input style="display:none"  type="submit" value="submit"></form>
                                        </div>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </div><!-- end of Primary nav -->
                </div>
            </div> <!-- End mobile -->

            <?php
        } elseif ($vendor_id) {
            ?>
            <div id="header-extra" class="header-extra">
                <style>
                    .wcv-header-container,
                    .wcv-store-address-container {
                        display:none !important;
                    }

                    /* Hide the filters */
                    #filters-sidebar {
                        display:none !important;
                    }
                </style>
                <div style="width:100%;">
                    <?php
                    $store_banner = '<img src="' . site_url() . '/wp-content/uploads/2017/08/Bannière-windsurf.jpg" alt="" class="wcv-store-banner" />';
                    echo $store_banner;
                    wp_nav_menu(array('theme_location' => 'primary-nav'));
                    ?>
                </div>
                <?php
                $user_info = get_userdata($vendor_id);
                $shop_name = get_user_meta($vendor_id, "pv_shop_name", true);
                if (empty($shop_name)) {
                    $shop_name = ucfirst($user_info->first_name) . "'s Shop";
                }
                ?>
                <div id="shop_header_main">
                    <div class="shop_header align-left" style="	text-align:right !important;"><h1><?php echo $shop_name; ?></h1></div>
                    <div class="shop_header align-right" style="padding:0 20px 0 40px !important;">
                        <div id="sellerInfoMain">
                            <div id="personalInfo">
                                <div id="personalInfoProfilePic" style="float:left;height:80px;width:100px;">
                                    <?php
                                    global $product;
                                    $user = get_user_by('id', $product->post->post_author);
                                    $user_id = $user->ID;
                                    $attachement_id = get_user_meta($user_id, "profile_picture", true);
                                    if ($attachement_id) {
                                        $avatar = wp_get_attachment_image($attachement_id, array(80, 80));
                                    } else {
                                        $avatar = '<img src="' . site_url() . '/wp-content/uploads/2016/12/person-flat.png" width="80" height="80" alt="" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 photo avatar-default">';
                                    }
                                    echo "<a href='/shops/" . $user->user_login . "'>" . $avatar . "</a>"; ///////////// CHANGE TO SHOP LOGO?????
                                    ?>      
                                </div>
                                <div style="float:left;width:400px;color:#FFFFFF;" class="shop-header-link-color">
                                    <?php
                                    $vendor_id = $user_id;
                                    $average_rate = WCVendors_Pro_Ratings_Controller::get_ratings_average($vendor_id);
                                    $user_meta = get_userdata($vendor_id);
                                    ?>
                                    <h3>
                                        <?php
                                        if ($user_meta->user_mp_status == "business") {
                                            if ($user_meta->pv_shop_name == "") {
                                                echo "<a href='" . __("/shops/", "surfsnb") . "" . $user_meta->user_login . "'>" . ucfirst($user->user_firstname) . " <img src='" . site_url() . "/wp-content/uploads/2017/04/PRO-SHOP.png' style='height:40px;width:40px;'></a>";
                                            } else {
                                                echo "<a href='" . __("/shops/", "surfsnb") . "" . $user_meta->user_login . "'>" . ucfirst($user_meta->pv_shop_name) . " <img src='" . site_url() . "/wp-content/uploads/2017/04/PRO-SHOP.png' style='height:40px;width:40px;'></a>";
                                            }
                                        } else {
                                            echo "<a href='/shops/" . $user->user_login . "' style='color:white;'>" . ucfirst($user->user_firstname) . "</a>";
                                        }
                                        ?>
                                        <a href="../ratings/" target="_blank" title="<?php _e("Click to view all reviews for this seller", "surfsnb"); ?>">
                                            <span class="stars">
                                                <?php
                                                //echo "dsfd: ".$average_rate."<br>";
                                                if ($average_rate > 0 AND $average_rate <= 1) {
                                                    ?>
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" style="height:20px;width:20px;" />
                                                    <?php
                                                } elseif ($average_rate > 1 AND $average_rate <= 2) {
                                                    ?>
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <?php
                                                } elseif ($average_rate > 2 AND $average_rate <= 3) {
                                                    ?>
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <?php
                                                } elseif ($average_rate > 3 AND $average_rate <= 4) {
                                                    ?>
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <?php
                                                } elseif ($average_rate > 4 AND $average_rate <= 5 OR $average_rate == 0) {
                                                    ?>
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                                    <?php
                                                } else {
                                                    
                                                }
                                                ?>
                                            </span>
                                        </a>
                                    </h3>
                                    <h4 style="margin-top:-10px;"><?php echo ucfirst(get_user_meta($user_id, "billing_city", true)) . ", " . get_user_meta($user_id, "billing_country", true); ?></h4>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                        </div>
                    </div>
                </div> <!-- Shop header main -->

                <div id="sellerInfoMainFront" style="margin-bottom:30px;padding:0 15px;">
                    <div class="sellerInfoStoreName"><h1><?php echo $shop_name; ?></h1></div>
                    <div id="personalInfo">
                        <div id="personalInfoProfilePic" style="float:left;width:120px;">
                            <?php
                            global $product;
                            $user = get_user_by('id', $product->post->post_author);
                            $user_id = $user->ID;
                            $attachement_id = get_user_meta($user_id, "profile_picture", true);
                            if ($attachement_id) {
                                $avatar = wp_get_attachment_image($attachement_id, array(100, 100));
                            } else {
                                $avatar = '<img src="' . site_url() . '/wp-content/uploads/2016/12/person-flat.png" width="100" height="100" alt="" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 photo avatar-default">';
                            }

                            echo "<a href='" . __("/shops/", "surfsnb") . "" . $user->user_login . "'>" . $avatar . "</a>"; ///////////// CHANGE TO SHOP LOGO?????
                            ?>      
                        </div>
                        <div style="float:left;margin:0 auto;">
                            <?php
                            $vendor_id = $user_id;
                            $average_rate = WCVendors_Pro_Ratings_Controller::get_ratings_average($vendor_id);
                            $user_meta = get_userdata($vendor_id);
                            ?>
                            <h3>
                                <?php
                                if ($user_meta->user_mp_status == "business_user") {
                                    echo "<a href='" . __("/shops/", "surfsnb") . "" . $user->user_login . "'>" . ucfirst($user_meta->company_name) . "</a>";
                                } else {
                                    echo "<a href='" . __("/shops/", "surfsnb") . "" . $user->user_login . "'>" . ucfirst($user->user_firstname) . "</a>";
                                }
                                ?>
                                <span class="stars">
                                    <?php
                                    //echo "dsfd: ".$average_rate."<br>";
                                    if ($average_rate > 0 AND $average_rate <= 1) {
                                        ?>
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" style="height:20px;width:20px;" />
                                        <?php
                                    } elseif ($average_rate > 1 AND $average_rate <= 2) {
                                        ?>
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <?php
                                    } elseif ($average_rate > 2 AND $average_rate <= 3) {
                                        ?>
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <?php
                                    } elseif ($average_rate > 3 AND $average_rate <= 4) {
                                        ?>
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <?php
                                    } elseif ($average_rate > 4 AND $average_rate <= 5 OR $average_rate == 0) {
                                        ?>
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <img src="<?php echo site_url(); ?>/wp-content/uploads/2016/11/star.png" class="star" />
                                        <?php
                                    } else {
                                        
                                    }
                                    ?>
                                </span>
                            </h3>
                            <h4><?php echo ucfirst(get_user_meta($user_id, "billing_city", true)) . ", " . get_user_meta($user_id, "billing_country", true); ?></h4>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                </div>
            </div>
            <?php
        }

// Loop over al products and add a location				# make sure to also set what to loop!!!!
        if ($_GET['loop'] == "YES") {
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1
            );
            $loop = new WP_Query($args);
            $count = $loop->post_count;
            write_log("Count: " . $count);
            if ($loop->have_posts()) {
                while ($loop->have_posts()) : $loop->the_post();
                    $productID = get_the_ID();

                    if (!empty($_GET['location'])) {

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
                    }

                    if (!empty($_GET['price'])) {
                        $product = new WC_Product($productID);
                        $price = $product->get_regular_price();
                        $sale_price = $product->get_sale_price();
                        write_log("Price: " . $price);
                        write_log("Sale Price: " . $sale_price);

                        if (strpos($price, '.00') !== false || strpos($sale_price, '.00') !== false) {
                            $new_price = explode(".", $price);
                            $new_sale_price = explode(".", $sale_price);
                            write_log("Contains .00 ");
                            update_post_meta($productID, '_regular_price', $new_sale_price[0]);
                            write_log($productID . " / Sale Price copied to Regular price, new price is: " . $new_sale_price[0]);
                        }

                        if ($sale_price > 0) {
                            write_log("Contains .00 ");
                            delete_post_meta($productID, '_sale_price');
                            write_log($productID . " / Sale Price deleted");
                        }
                    }

                endwhile;
            }
        }

        global $post;
        $post_slug = $post->post_name;
        ?>

        <div class="site-main container <?php echo "page-" . $slug; ?>"><!-- Content wrapper -->

            <?php
// Language error in french
            if ($_GET['language_error'] == "1") {
                ?><div class="woocommerce-error"><?php _e("This page is currently only available in English", "surfsnb"); ?></div><?php }
			?>
                
			<?php 
			// For Home Page		
			if (is_page(2760)) { ?>
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        initializeAutocomplete('location');
						initializeAutocomplete('locationm');
                        jQuery(window).on("scroll", function () {
                            var scrollHeight = parseInt(jQuery(".site-header").height());
                            var scrollPosition = jQuery(window).scrollTop();
                            <?php $url = wp_upload_dir(); ?>
							var url = "<?php echo $url['baseurl']; ?>";
                            if (scrollPosition > scrollHeight) {
								jQuery(".site-logo").find('img').attr('src', url+'/2018/04/logo_round_20181.png');
                                jQuery(".site-logo").find('img').height("67");
                            } else {
                                jQuery(".site-logo").find('img').attr('src', url+'/2018/04/logo_round_20181.png');
                                jQuery(".site-logo").find('img').height("140");
                            }
                        });
                    });
                    function initializeAutocomplete(id) {
                        var element = document.getElementById(id);
                        var options = {
                            /*componentRestrictions: {country: "fr"},*/
                            types: ['geocode']
                        };
                        if (element) {
                            autocomplete = new google.maps.places.Autocomplete(element, options);
                            google.maps.event.addListener(autocomplete, 'place_changed', onPlaceChanged);
                        }
                    }

                    function onPlaceChanged() {

                        var place = this.getPlace();
                        for (var i in place.address_components) {
                            var component = place.address_components[i];
                            for (var j in component.types) {  // Some types are ["country", "political"]
                                var type_element = document.getElementById(component.types[j]);
                                jQuery("#oostal_code").val(component.long_name);

                                if (type_element) {
                                    type_element.value = component.long_name;
                                }
                            }
                        }
                    }



                </script>
            <?php } else { ?>   
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        <?php $url = wp_upload_dir(); ?>
						var url = "<?php echo $url['baseurl']; ?>";
						jQuery(".site-logo").find('img').attr('src', url+'/2018/04/logo_round_20181.png');
                        jQuery(".site-logo").find('img').height("67");
                    });
                </script>         
            <?php } ?>

            <style>
                .header-help .suppa_menu_dropdown > .suppa_submenu a .suppa_item_title { color: #3e3e3e;}
                .header-help .suppa_submenu{ background-color: #ffffff;}
                .header-help .suppa_menu_dropdown > .suppa_submenu a{ border-bottom:none;}

                /****************23-04-2018***********/

	            .page-id-2760 .entry-content .main-categories-images .col-md-2 {
                    /*padding-left: 4px;*/
                    /*padding-right: 4px;*/
                }
                .header-help .suppa_top_level_link .suppa_item_title {

                    color: #808080;
                }
            </style>            