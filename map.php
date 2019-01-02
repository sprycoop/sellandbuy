<?php
/*
  Template Name: Sprycoop Map
 */

//die('hlo');

get_header();
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>

    img.product_featured_image {
        height: 210px;
    }
    .loader {
        position: fixed;
        z-index: 999;
        width: 100%;
        margin: auto;
        text-align: center;
        top: 49%;
    }
    .loader img {
        width: 125px;
    }
    .sub_cat_boards {
        border-top: 1px solid;
    }
    #showFilter {
        width: 100%;
        padding: 15px;
    }
    a.load_more_data button {
        background: #2d8bbb !important;
    }
    a.load_more_data button:hover {
        color: #fff !important;
    }
    .no_product_found {
        border: 1px solid #000;
        padding: 26px;
        width: 50%;
        text-align: center;
        border-radius: 4px;
        position: absolute;
        left: 20%;
        top: 20%;
        box-shadow: 5px 10px #888888;
    }
    .loader.popups {
        left: 16%;
        top: 63%;
    }
    .right_side_count {
        right: 0px;
        position: absolute;
        top: -46px;
    }
    .container_new {
        display: block;
        position: relative;
        line-height: 26px;
        padding-left: 35px;
        margin-bottom: 12px;
        cursor: pointer;
        font-size: 14px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        color:#333;
        user-select: none;
    }

    /* Hide the browser's default radio button */
    .container_new input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    /* Create a custom radio button */
    .checkmark_new {
        position: absolute;
        top: 0;
        left: 0;
        height: 25px;
        width: 25px;
        background-color: #fff;
        border: 1px solid #666;
        border-radius: 50%;
    }

    /* On mouse-over, add a grey background color */
    .container_new:hover input ~ .checkmark_new {
        background-color: #fff;
    }

    /* When the radio button is checked, add a blue background */
    .container_new input:checked ~ .checkmark_new {
        background-color: #fff;
    }

    /* Create the indicator (the dot/circle - hidden when not checked) */
    .checkmark_new:after {
        content: "";
        position: absolute;
        display: none;
    }

    /* Show the indicator (dot/circle) when checked */
    .container_new input:checked ~ .checkmark_new:after {
        display: block;
    }

    /* Style the indicator (dot/circle) */
    .container_new .checkmark_new:after {
        top: 6px;
        left: 6px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #333;
    }
    .product-description h2 {height: 40px !important;}
</style>
<link rel="stylesheet" href="/wp-content/themes/dashstore-child/css/style.css">
<link rel="stylesheet" href="/wp-content/themes/dashstore-child/css/style-live.css">
<link rel="stylesheet" href="/wp-content/themes/dashstore-child/css/bootstrap-slider.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<!--<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAAIxlNoTA-_i04glqIbxD9QvJbC_y5z_E&callback=initMap" type="text/javascript"></script>-->
<?php
global $wpdb;
global $wp_query;
global $post;
global $product;
$seller = get_terms(array(
    'taxonomy' => 'pa_seller',
    'hide_empty' => false,
        ));

$termsTable = $wpdb->prefix . 'terms';


$post_table = $wpdb->prefix . 'posts';
$post_meta_table = $wpdb->prefix . 'postmeta';

$current_language = ICL_LANGUAGE_CODE;
$category_name = $_REQUEST['category'];
if (!($category_name)) {
    ?>
    <script>
        jQuery(document).ready(function () {
            // alert('hello');
            showCatalogModal();
        });
    </script>
    <?php
}
$limit = 12;
$pageNo = $_REQUEST['page_no'];
if ($_REQUEST['page_no']) {
    $newLimit = $limit - 1;
    $offset = ($pageNo * $limit) - $newLimit;
} else {
    $offset = 0;
}


if ($current_language == 'en') {
    $slug_name = $category_name;
} else if ($current_language == 'fr') {
    $slug_name = $category_name . '-' . $current_language . "-2";
} else {
    $slug_name = $category_name . '-' . $current_language;
}
$term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
$tax_query = array('relation' => 'AND', array(
        'taxonomy' => 'product_cat',
        'field' => 'term_id', //This is optional, as it defaults to 'term_id'
        'terms' => $term_id,
        ));
if ($_REQUEST['search_keyword']) {
    $wordExplode = explode(" ", $_REQUEST['search_keyword']);
    $array_reversewordExplode = array_reverse($wordExplode);

    $wordcount = count($wordExplode);



    $jpWordFirst = $wordExplode[0];
    $jpWordFirstReverse = $array_reversewordExplode[0];

    $jpWordSecond = $wordExplode[1];
    $jpWordSecondReverse = $array_reversewordExplode[1];
    if ($wordExplode[2]) {
        $jpWordthird = $wordExplode[2];
        $jpWordThirdReverse = $array_reversewordExplode[2];
    } else {
        $jpWordthird = "";
        $jpWordThirdReverse = "";
    }
    if ($wordExplode[3]) {
        $jpWordFourth = $wordExplode[3];
        $jpWordFourthReverse = $array_reversewordExplode[3];
    } else {
        $jpWordFourth = "";
        $jpWordFourthReverse = "";
    }
    if ($wordExplode[4]) {
        $jpWordFifth = $wordExplode[4];
        $jpWordFifthReverse = $array_reversewordExplode[4];
    } else {
        $jpWordFifth = "";
        $jpWordFifthReverse = "";
    }
    if ($wordExplode[5]) {
        $jpWordSixth = $wordExplode[5];
        $jpWordSixthReverse = $wordExplode[5];
    } else {
        $jpWordSixth = "";
        $jpWordSixthReverse = "";
    }
}
if (isset($_REQUEST['pa_seller'])) {
    $val = explode(",", $_REQUEST['pa_seller']);

    $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
}
if (isset($_REQUEST['brand'])) {

    // $val = explode(",", $_REQUEST['brand']);
    $brandReplace = str_replace(" ", "-", $_REQUEST['brand']);
    $val = explode(",", $_REQUEST['$brandReplace']);

    $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
}
if (isset($_REQUEST['pa_year'])) {

    $val = explode(",", $_REQUEST['pa_year']);

    $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
}

if ($_REQUEST['pa_mast_size']) {
    $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $_REQUEST['pa_mast_size'], 'operator' => 'IN');
}
if ($_REQUEST['pa_carbon_number']) {
    $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $_REQUEST['pa_carbon_number'], 'operator' => 'IN');
}
if ($_REQUEST['pa_blade_size']) {
    $tax_query[] = array('taxonomy' => 'pa_blade-size-cm�', 'field' => 'slug', 'terms' => $_REQUEST['pa_blade_size'], 'operator' => 'IN');
}
if ($_REQUEST['pa_surface']) {
    $tax_query[] = array('taxonomy' => 'pa_surface-m�', 'field' => 'slug', 'terms' => $_REQUEST['pa_surface'], 'operator' => 'IN');
}
if ($_REQUEST['pa_boom_size']) {
    $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $_REQUEST['pa_boom_size'], 'operator' => 'IN');
}
if ($_REQUEST['pa_volume']) {
    $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $_REQUEST['pa_volume'], 'operator' => 'IN');
}

if ($_REQUEST['pa_size']) {
    $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $_REQUEST['pa_size'], 'operator' => 'IN');
}

if ($_REQUEST['pa_blade_size_in']) {
    $tax_query[] = array('taxonomy' => 'pa_blade-sizein�', 'field' => 'slug', 'terms' => $_REQUEST['pa_blade_size_in'], 'operator' => 'IN');
}
if ($_REQUEST['pa_size_number']) {
    $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $_REQUEST['pa_size_number'], 'operator' => 'IN');
}
if ($_REQUEST['pa_length_cm']) {
    $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $_REQUEST['pa_length_cm'], 'operator' => 'IN');
}
if ($_REQUEST['pa_length_feet']) {
    $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $_REQUEST['pa_length_feet'], 'operator' => 'IN');
}
if ($_REQUEST['pa_thickness_mm']) {
    $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $_REQUEST['pa_thickness_mm'], 'operator' => 'IN');
}
if ($_REQUEST['pa_width_cm']) {
    $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $_REQUEST['pa_width_cm'], 'operator' => 'IN');
}
if ($_REQUEST['pa_width_inches']) {
    $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $_REQUEST['pa_width_inches'], 'operator' => 'IN');
}
if ($_REQUEST['$pa_kitebars_size_m']) {
    $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $_REQUEST['$pa_kitebars_size_m'], 'operator' => 'IN');
}
if ($_REQUEST['pa_mast_size_cm']) {
    $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $_REQUEST['pa_mast_size_cm'], 'operator' => 'IN');
}

if ($_REQUEST['pa_condition']) {
    $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $_REQUEST['pa_condition'], 'operator' => 'IN');
}
if ($_REQUEST['pa_warranty']) {
    $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $_REQUEST['pa_warranty'], 'operator' => 'IN');
}
if ($_REQUEST['pa_damage']) {
    $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $_REQUEST['pa_damage'], 'operator' => 'IN');
}
if ($_REQUEST['pa_repair']) {
    $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $_REQUEST['pa_repair'], 'operator' => 'IN');
}


if ($_REQUEST['price']) {
    ?><script> console.log("Map Price");</script> <?php
    $val = explode("-", $_REQUEST['price']);
    $from = $val[0];
    $to = $val[1];

    $meta_query = array(
        array(
            'key' => '_price',
            'value' => array($from, $to),
            'type' => 'numeric',
            'compare' => 'BETWEEN'
        )
    );
    if ($_REQUEST['search_keyword']) {
        ?><script> console.log("Map Price + Keyword");</script> <?php
        $pageargs = array(
            's' => $_REQUEST['search_keyword'],
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($_REQUEST['location']) {
        ?><script> console.log("Map Price + Location");</script> <?php
        $pageargs = array(
            's' => $_REQUEST['search_keyword'],
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            ) // meta_query is overwritten below with location geo query
        );

        $address = $_REQUEST['location'];
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {
            $args['order'] = 'ASC';
            $pageargs['order'] = 'ASC';
            $pageargs['orderby'] = 'distance';
            $args['orderby'] = 'distance';
            $zoomlevel = 5;
            //print_r($geo['results'][0]['address_components']);
            foreach ($geo['results'][0]['address_components'] as $checklocality) {
                //echo $checklocality['types'][0];
                if ($checklocality['types'][0] == 'locality') {
                    $zoomlevel = 10;
                }
            }

            $latitude = $geo['results'][0]['geometry']['location']['lat'];
            $longitude = $geo['results'][0]['geometry']['location']['lng'];
            $areacalculatedval = $latitude;
            $arealngcalculatedval = $longitude;
            $range = 500;
            $bbox = get_bounding_box_deg($latitude, $longitude, $range);
            $area[] = $bbox[1];
            $area[] = $bbox[0];
            $arealng[] = $bbox[2];
            $arealng[] = $bbox[3];

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
                ),
                array('key' => '_price',
                    'value' => array($from, $to),
                    'type' => 'numeric',
                    'compare' => 'BETWEEN'
                )
            );
            $args['geo_query'] = array(
                'lat_field' => 'lat', // this is the name of the meta field storing latitude
                'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                'distance' => $range, // this is the maximum distance to search
                'units' => 'km'       // this supports options: miles, mi, kilometers, km
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
                ),
                array('key' => '_price',
                    'value' => array($from, $to),
                    'type' => 'numeric',
                    'compare' => 'BETWEEN'
                )
            );
            $pageargs['geo_query'] = array(
                'lat_field' => 'lat', // this is the name of the meta field storing latitude
                'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                'distance' => $range, // this is the maximum distance to search
                'units' => 'km'       // this supports options: miles, mi, kilometers, km
            );
        }
    } else {
        $pageargs = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    }
} else if ($_REQUEST['search_keyword'] && empty($_REQUEST['location'])) {
    ?><script> console.log("Map Keyword + No Location");</script> <?php
    $pageargs = array(
        's' => $_REQUEST['search_keyword'],
        'post_type' => 'product',
        'post_status' => 'publish',
        'offset' => $offset,
        'posts_per_page' => $limit,
        'tax_query' => array(
            $tax_query
        )
    );
} else if ($_REQUEST['search_keyword'] && $_REQUEST['location']) {
    ?><script> console.log("Map Keyword + Location");</script> <?php
    $pageargs = array(
        's' => $_REQUEST['search_keyword'],
        'post_type' => 'product',
        'post_status' => 'publish',
        'offset' => $offset,
        'posts_per_page' => $limit,
        'tax_query' => array(
            $tax_query
        )
    );
    $address = $_REQUEST['location'];
    $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
    $geo = json_decode($geo, true);
    if ($geo['status'] = 'OK') {
        $args['order'] = 'ASC';
        $pageargs['order'] = 'ASC';
        $pageargs['orderby'] = 'distance';
        $args['orderby'] = 'distance';
        $zoomlevel = 5;
        //print_r($geo['results'][0]['address_components']);
        foreach ($geo['results'][0]['address_components'] as $checklocality) {
            //echo $checklocality['types'][0];
            if ($checklocality['types'][0] == 'locality') {
                $zoomlevel = 10;
            }
        }

        $latitude = $geo['results'][0]['geometry']['location']['lat'];
        $longitude = $geo['results'][0]['geometry']['location']['lng'];
        $areacalculatedval = $latitude;
        $arealngcalculatedval = $longitude;
        $range = 500;
        $bbox = get_bounding_box_deg($latitude, $longitude, $range);
        $area[] = $bbox[1];
        $area[] = $bbox[0];
        $arealng[] = $bbox[2];
        $arealng[] = $bbox[3];

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
        $args['geo_query'] = array(
            'lat_field' => 'lat', // this is the name of the meta field storing latitude
            'lng_field' => 'lng', // this is the name of the meta field storing longitude 
            'latitude' => $latitude, // this is the latitude of the point we are getting distance from
            'longitude' => $longitude, // this is the longitude of the point we are getting distance from
            'distance' => $range, // this is the maximum distance to search
            'units' => 'km'       // this supports options: miles, mi, kilometers, km
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
        $pageargs['geo_query'] = array(
            'lat_field' => 'lat', // this is the name of the meta field storing latitude
            'lng_field' => 'lng', // this is the name of the meta field storing longitude 
            'latitude' => $latitude, // this is the latitude of the point we are getting distance from
            'longitude' => $longitude, // this is the longitude of the point we are getting distance from
            'distance' => $range, // this is the maximum distance to search
            'units' => 'km'       // this supports options: miles, mi, kilometers, km
        );
    }
} else if ($_REQUEST['location'] && $_REQUEST['location'] != '') {
    ?><script> console.log("Map Price");</script> <?php
    $pageargs = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'offset' => $offset,
        'posts_per_page' => $limit,
        'tax_query' => array(
            $tax_query
        )
    );
    $address = $_REQUEST['location'];
    $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
    $geo = json_decode($geo, true);
    if ($geo['status'] = 'OK') {
        $args['order'] = 'ASC';
        $pageargs['order'] = 'ASC';
        $pageargs['orderby'] = 'distance';
        $args['orderby'] = 'distance';
        $zoomlevel = 5;
        //print_r($geo['results'][0]['address_components']);
        foreach ($geo['results'][0]['address_components'] as $checklocality) {
            //echo $checklocality['types'][0];
            if ($checklocality['types'][0] == 'locality') {
                $zoomlevel = 10;
            }
        }

        $latitude = $geo['results'][0]['geometry']['location']['lat'];
        $longitude = $geo['results'][0]['geometry']['location']['lng'];
        $areacalculatedval = $latitude;
        $arealngcalculatedval = $longitude;
        $range = 500;
        $bbox = get_bounding_box_deg($latitude, $longitude, $range);
        $area[] = $bbox[1];
        $area[] = $bbox[0];
        $arealng[] = $bbox[2];
        $arealng[] = $bbox[3];

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
        $args['geo_query'] = array(
            'lat_field' => 'lat', // this is the name of the meta field storing latitude
            'lng_field' => 'lng', // this is the name of the meta field storing longitude 
            'latitude' => $latitude, // this is the latitude of the point we are getting distance from
            'longitude' => $longitude, // this is the longitude of the point we are getting distance from
            'distance' => $range, // this is the maximum distance to search
            'units' => 'km'       // this supports options: miles, mi, kilometers, km
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
        $pageargs['geo_query'] = array(
            'lat_field' => 'lat', // this is the name of the meta field storing latitude
            'lng_field' => 'lng', // this is the name of the meta field storing longitude 
            'latitude' => $latitude, // this is the latitude of the point we are getting distance from
            'longitude' => $longitude, // this is the longitude of the point we are getting distance from
            'distance' => $range, // this is the maximum distance to search
            'units' => 'km'       // this supports options: miles, mi, kilometers, km
        );
    }
} else {
    $pageargs = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'offset' => $offset,
        'posts_per_page' => $limit,
        'tax_query' => array(
            $tax_query
        )
    );
}



//echo '<pre>';
//print_r($pageargs);
//echo '</pre>';
//if ($_REQUEST['location'] && $_REQUEST['location'] != '') {
//    
//    $allproducts = new WP_Query($pageargs);
//} else {
//    $allproducts = new WP_Query($pageargs);
//}

$allproducts = new WP_Query($pageargs);

$explodeprice = explode("-", $_REQUEST['price']);
$minimum_price_selected = trim($explodeprice[0], "'");
$maximum_price_selected = trim($explodeprice[1], "'");





$all_categories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=0');
$product_count = $allproducts->found_posts;
$query_string = "SELECT MIN(cast(FLOOR(br_prices.meta_value) as decimal)) as min_price, MAX(cast(CEIL(br_prices.meta_value) as decimal)) as max_price FROM iewZNddPposts  INNER JOIN iewZNddPpostmeta as br_prices ON (iewZNddPposts.ID = br_prices.post_id) WHERE iewZNddPposts.post_type = 'product' AND iewZNddPposts.post_status = 'publish' AND br_prices.meta_key = '_price' AND br_prices.meta_value > 0";

$prices = $wpdb->get_row($query_string);


$total_no_of_pages = ceil($product_count / $limit);

if ($_REQUEST['page_no']) {
    $current_page_selecetd = $_REQUEST['page_no'];
} else {
    $current_page_selecetd = '1';
}
$startPage = $current_page_selecetd - 4;
$endPage = $current_page_selecetd + 4;
if ($startPage <= 0) {
    $endPage -= ($startPage - 1);
    $startPage = 1;
}
if ($endPage > $total_no_of_pages) {
    $endPage = $total_no_of_pages;
}
$allFilters = wc_get_attribute_taxonomies();

//echo '<pre>';
//print_r($allFilters);
//echo '</pre>';
//foreach ($array as $attribute) {
//    echo $name = $attribute->attribute_name . "<br>";
//}
//global $woocommerce;
//$attr_tax = $woocommerce->get_attribute_taxonomy_names(); 
//foreach( $attr_tax as $tax ) :
//    echo $woocommerce->attribute_taxonomy_name( $tax->attribute_name );
//endforeach;
?>

<div class="loader" style='display: none;'>
    <img src="https://www.pedul.com/images/loading.gif" alt="loader"/>
</div>
<section class="map-side">
    <div class="lft-side" style="width:100% !important;">
        <div class="mystrap">
            <div class="myfix">
                <div class="col-sm-3 col-md-3 mymargin">
                    <ul class="nav nav-pills mypills">
                        <!--<li class="active"><a href="#tab_a" data-toggle="modal" data-target="#squarespaceModal">CATALOG</a></li>-->
                        <li class="active"><a href="javascript:void(0)" onclick="showCatalogModal()">CATALOG</a></li>
                        <li><a href="javascript:void(0)" class="showFilter">FILTER</a></li>
                    </ul>
                </div>
                <!-- tab content -->            
                <div class="col-sm-6 col-md-6 mymargin" id="mysteprd">
                    <div class="col-sm-6 col-md-6 mymargin">
                        <div id="custom-search-input2">

                            <input type="text" class="search-query2 form-control" name="search_keywords" placeholder="Try a Key word, modal, a brand" onkeyup="seachKeyword()" value="<?= $_REQUEST['search_keyword']; ?>"/>                     

                        </div>
                    </div>
                    <div class="col-sm-6 col-md-6 mymargin">
                        <div id="custom-search-input2">
                            <div id="locationField">
                                <input type="text" class="search-query3 form-control" id="autocomplete" placeholder="Everywhere, try a location"  onFocus="geolocate()" value="<?= $_REQUEST['location']; ?>"/> 
                                <input type="hidden" name="latitude" id="latitude" value="<?= $latitude; ?>"/>
                                <input type="hidden" name="longitude" id="longitude" value="<?= $longitude; ?>"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3 col-md-3 mymargin">
                    <strong style=" color:#333; float: right;line-height: 36px;"> Coming Soon<span class="coming text-right"><img src="https://surf.sellandbuy.online//wp-content/themes/dashstore-child/img/map-icon.png" alt="map-icon" width="25"> Map</span></strong>
                </div>
            </div>

            <div class="col-sm-12 col-md-12 tab-content">
                <div class="tab-pane active" id="tab_a">

                    <div class="row">
                        <div class="filter_part" id="showFilter" style="display:none;">
                            <ul class="nav nav-pills mypills" id="myfilter">
                                <!--<li><a data-toggle="modal" data-target="#squarespaceModal7" href="javascript:void(0)">PRICE</a></li>-->
                                <li><a href="javascript:void(0)" onclick="showPriceModal()">PRICE</a></li>

                                <?php
                                foreach ($allFilters as $allFilter):

                                    $hide_attributes_list = "16,23,25,22,26,28,38,36,13,21,24,37,12,17,15,19,18,39,14,20,";

                                    if (in_array($allFilter->attribute_id, explode(',', $hide_attributes_list))) {
                                        $no_display = "style=display:none;";
                                    } else {
                                        $no_display = "";
                                    }
                                    ?>
                                    <li <?= $no_display; ?>><a href="javascript:void(0)" onclick="showFilterModal('<?= $allFilter->attribute_name; ?>')"><?= $allFilter->attribute_label; ?></a></li>
                                <?php endforeach; ?>

                                <!--<li><a data-toggle="modal" data-target="#squarespaceModal2" href="#">SELLER</a></li>-->
                                <!--                                <li><a href="javascript:void(0)" onclick="showSellerModal()">SELLER</a></li>
                                                                <li><a data-toggle="modal" data-target="#squarespaceModal3" href="javascript:void(0)">BRAND</a></li>
                                                                <li><a href="javascript:void(0)" onclick="showBrandModal()">BRAND</a></li>
                                                                <li><a data-toggle="modal" data-target="#squarespaceModal4" href="#">YEAR</a></li>
                                                                <li><a href="javascript:void(0)" onclick="showYearModal()">YEAR</a></li>-->
                                <!--<li><a data-toggle="modal" data-target="#squarespaceModal5" href="#">SIZE</a></li>-->

                                <?php if ($category_name == 'windsurf' || $category_name == 'kitesurf' || $category_name == 'stand-up-paddle' || $category_name == 'surf' || $category_name == 'surfwear' || $category_name == 'action-camera') { ?>
                                    <li class="categorySizeModal"><a href="javascript:void(0)" onclick="noSizeModal()">SIZE</a></li>

                                <?php } else { ?>
                                    <li class="categorySizeModal"><a href="javascript:void(0)" onclick="showSizeModal()">SIZE</a></li>

                                <?php } ?>


                                <li class="ajaxCategorySizeModal" style="display:none;"><a href="javascript:void(0)" onclick="showSizeModal()">SIZE</a></li>

                                <li class="ajaxCategoryModal" style="display:none;"><a href="javascript:void(0)" onclick="noSizeModal()">SIZE</a></li>

                                <li><a href="javascript:void(0)" onclick="showConditionModal()">CONDITION</a></li> 
                                <li><a href="javascript:void(0)" onclick="resetFilter()">RESET FILTER</a></li>

                            </ul>
                            <div class="row">       
                                <!-- Filtered Product show here -->
                            </div>
                        </div>
                        <?php if ($product_count > 0) { ?>
                            <div class="mylatest products" id="original_product">
                                <?php
                                $test = array();

                                foreach ($allproducts->posts as $fetchAllProduct):


                                    $product = new WC_Product($fetchAllProduct->ID);
                                    $markerarray[$fetchAllProduct->post_author] = $fetchAllProduct->ID;
                                    $attributes = $product->get_attributes();

                                    foreach ($attributes as $attribute) {
                                        $name = $attribute->get_name();
                                        if ($attribute->is_taxonomy()) {
                                            $terms = wp_get_post_terms($product->get_id(), $name, 'all');
                                            foreach ($terms as $term) {
                                                $single_term = esc_html($term->name);
                                                $tax_terms[$name][$term->term_id] = esc_html($term->name) . '<span class="count">(' . $term->count . ')</span>';
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">
                                        <div class="span12">
                                            <div id="myCarousel_<?= $fetchAllProduct->ID; ?>" class="carousel slide" data-interval="false">
                                                <!-- Carousel items -->                                 
                                                <div class="carousel-inner">
                                                    <div class="item active">
                                                        <div class="row-fluid">
                                                            <div class="myproduct">
                                                                <?php
                                                                $post_thumbnail_id = get_post_thumbnail_id($product->get_id());
                                                                $image = wp_get_attachment_image_src($post_thumbnail_id, 'medium'); // Danny	
                                                                if ($image[0]) {
                                                                    ?>
                                                                    <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image" target="_blank"></a>

                                                                <?php } else { ?>
                                                                    <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image" target="_blank"></a>
                                                                <?php } ?>

                                                            </div>
                                                        </div>
                                                        <!--/row-fluid-->                                    
                                                    </div>

                                                    <!--/item-->   
                                                    <?php
                                                    $attachment_ids = $product->get_gallery_image_ids();
                                                    if ($attachment_ids) {
                                                        foreach ($attachment_ids as $productGallery):
                                                            ?>
                                                            <div class="item">
                                                                <div class="row-fluid">
                                                                    <div class="myproduct">
                                                                        <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny     ?>
                                                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image" target="_blank"></a>                                             
                                                                    </div>
                                                                </div>
                                                                <!--/row-fluid-->                                    
                                                            </div>
                                                            <?php
                                                        endforeach;
                                                    }
                                                    ?>
                                                    <div class="product-description">
                                                        <h2><a href="<?= $product->get_permalink(); ?>" target="_blank" >
                                                                <?php
                                                                echo $fetchAllProduct->post_title;
                                                                ?>
                                                            </a></h2>
                                                        <?php
                                                        $product_condition = array_shift(wc_get_product_terms($fetchAllProduct->ID, 'pa_condition', array('fields' => 'names')));
                                                        if ($product_condition == 'Very good condition') {
                                                            $condition_class = 'color:#0C3';
                                                        } else if ($product_condition == 'New') {
                                                            $condition_class = 'color:#0C3';
                                                        } else if ($product_condition == 'Decent condition') {
                                                            $condition_class = 'color:#FC0';
                                                        } else if ($product_condition == 'Good condition') {
                                                            $condition_class = 'color:#0C3';
                                                        } else if ($product_condition == 'Bad condition') {
                                                            $condition_class = 'color:#FC0';
                                                        } else {
                                                            $condition_class = 'color:#fc5a04';
                                                        }
                                                        ?>
                                                        <?php
                                                        if ($product_condition) {
                                                            ?>
                                                            <span class="good" style="<?= $condition_class; ?>"><?= $product_condition; ?></span>
                                                        <?php } else { ?>
                                                            <span class="good" style="<?= $condition_class; ?>">Condition Not Available</span>
                                                        <?php } ?>
                                                        <?php ?>
                                                            <p><?= number_format("$product->price", 2); ?>&euro;</p>
                                                    </div>
                                                    <!--/item-->                                    

                                                    <!--/item-->                                 
                                                </div>
                                                <!--/carousel-inner-->  
                                                <?php
                                                if ($attachment_ids) {
                                                    ?>
                                                    <a class="right carousel-control" href="#myCarousel_<?= $fetchAllProduct->ID; ?>" data-slide="next"><i class="fa fa-angle-right"></i></a>   
                                                    <a class="left carousel-control" href="#myCarousel_<?= $fetchAllProduct->ID; ?>" data-slide="prev"><i class="fa fa-angle-left"></i></a>                                 
                                                <?php } ?>
                                            </div>  
                                            <!--/myCarousel-->                            
                                        </div>
                                    </div>
                                <?php endforeach; ?>


                                <div class="col-sm-12 col-md-12 text-center">
                                    <ul class="pagination" id="mypagies"> 
                                        <?php
                                        if ($current_page_selecetd == '1' || $current_page_selecetd == '') {
                                            $page_class = 'disabled';
                                        } else {
                                            $page_class = '';
                                        }
                                        ?>

                                        <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData('1', '<?= $category_name; ?>')"><i class="fa fa-angle-left"></i><i class="fa fa-angle-left"></i></a></li>
                                        <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData(<?= $current_page_selecetd - 1; ?>, '<?= $category_name; ?>')"><i class="fa fa-angle-left"></i></a></li>
                                        <?php
                                        for ($page = $startPage; $page <= $endPage; $page++) {
                                            if (empty($current_page_selecetd)) {
                                                $page_default = 1;
                                            }
                                            if ($page == $current_page_selecetd) {
                                                $activeClass = 'disabled';
                                            } else if ($page == $page_default) {
                                                $activeClass = 'disabled';
                                            } else {
                                                $activeClass = '';
                                            }
                                            ?>





                                            <li class="<?= $activeClass; ?>"><a href="javascript:void(0)" onclick="paginationData(<?= $page; ?>, '<?= $category_name; ?>')"><?= $page; ?><span class="sr-only">(current)</span></a></li>
                                        <?php } ?>
                                        <?php
                                        if ($current_page_selecetd == $total_no_of_pages) {
                                            $end_page_class = 'disabled';
                                        } else {
                                            $end_page_class = '';
                                        }
                                        ?>
                                        <?php
                                        if ($current_page_selecetd == '') {
                                            $current_page_selecetd_pagination = '1';
                                        } else {
                                            $current_page_selecetd_pagination = $current_page_selecetd;
                                        }
                                        ?>
                                        <li class="<?= $end_page_class; ?>"><a class="lp2" href="javascript:void(0)" onclick="paginationData(<?= $current_page_selecetd_pagination + 1; ?>, '<?= $category_name; ?>')"><i class="fa fa-angle-right"></i></a></li>
                                        <li class="<?= $end_page_class; ?>"><a class="lp2" href="javascript:void(0)" onclick="paginationData(<?= $total_no_of_pages; ?>, '<?= $category_name; ?>')"><i class="fa fa-angle-right"></i><i class="fa fa-angle-right"></i></a></li>
                                    </ul>
                                    <?php
                                    $lastCount = $offset + ($limit - 1);
                                    if ($lastCount > $product_count) {
                                        $last_page_count = $product_count;
                                    } else {
                                        $last_page_count = $lastCount;
                                    }
                                    if ($offset > 0) {
                                        $newOffset = $offset;
                                        $new_last_page_count = $last_page_count;
                                    } else {
                                        $newOffset = '1';
                                        $new_last_page_count = $last_page_count + 1;
                                    }
                                    ?>
                                    <p><?= $newOffset; ?>-<?= $last_page_count; ?> of <?= $product_count; ?> products available</p> 
                                </div>

                            </div>

                        <?php } else { ?>
                            <div class="mylatest" id="original_product">
                                <div class="no_product_found">Please open the catalog and select a category						</div>
                            </div> 
                        <?php } ?>
                        <div id="ajax_product_record"></div>
                        <?php
                        $totallatlng = array();
                        foreach ($markerarray as $markerarraykey => $markerarrayvalue) :
                            $postal_code = get_user_meta($markerarraykey, 'billing_postcode', true);
                            $detailaddress[] = get_user_meta($markerarraykey, 'billing_address_1', true);
                            $detailaddress[] = get_user_meta($markerarraykey, 'billing_city', true);
                            $detailaddress[] = get_user_meta($markerarraykey, 'billing_state', true);
                            $detailaddress[] = $woocommerce->countries->countries[get_user_meta($markerarraykey, 'billing_country', true)];
                            $detailaddressresult = array_filter($detailaddress);
                            $address = implode("+", $detailaddressresult);
                            $detailaddress = array();
                            $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyAXz4tTNJ8PtN8unz_PcQzSynqWLFFm-7M');
                            $geo = json_decode($geo, true);
                            if ($geo['status'] == 'OK') {
                                $latitude = $geo['results'][0]['geometry']['location']['lat'];
                                $longitude = $geo['results'][0]['geometry']['location']['lng'];
                                $totallatlng[] = array($latitude, $longitude);
                                /* ?><script>console.log('<?php echo "Lat: " . $latitude . " Long: " . $longitude; ?>');</script><?php */
                                //	print_r(getCentroid($triangle));
                                $image = wp_get_attachment_image_src(get_post_thumbnail_id($markerarrayvalue), 'single-post-thumbnail');
                                //write_log("Check img: ".print_r($image,1));
                                $price = get_post_meta($markerarrayvalue, '_regular_price', true);
                                $sale = get_post_meta($markerarrayvalue, '_sale_price', true);

                                // Get condition
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
                                    $maiprice = $price . $currency;
                                elseif ($price) :
                                    $maiprice = $price . $currency;
                                endif;
                                $test[] = array("latitude" => $latitude,
                                    "longitude" => $longitude,
                                    "title" => get_the_title($markerarrayvalue),
                                    "photo" => $image[0],
                                    "url" => get_the_permalink($markerarrayvalue),
                                    "maiprice" => $maiprice, "withoutcurrencyprice" => $price,
                                    "condition" => $conditionNr,
                                    "conditionS" => $conditionStyle,
                                    "authorid" => $markerarraykey,
                                    "ID" => $markerarrayvalue);

                                //  write_log("Test 3: ".print_r($test,1));
                            }
                        endforeach;
                        if (empty($totallatlng)) {
                            $ip = $_SERVER['REMOTE_ADDR'];
                            $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
                            $address = $details->city . '+' . $details->region . '+' . $details->country;
                            $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyAXz4tTNJ8PtN8unz_PcQzSynqWLFFm-7M');
                            $geo = json_decode($geo, true);
                            if ($geo['status'] = 'OK') {
                                $latitude = $geo['results'][0]['geometry']['location']['lat'];
                                $longitude = $geo['results'][0]['geometry']['location']['lng'];
                                $totallatlng[] = array($latitude, $longitude);
                                /* ?><script>console.log('<?php echo "Lat: " . $latitude . " Long: " . $longitude; ?>');</script><?php */
                            }
                        }
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="rgt-side" style="display:none;"> 
        <div class="rgt-side2" > 
            <div class="product_count_class"><?= $product_count; ?> products found!</div>	
            <p class="new_product_count_class"></p>
            <!--<img src="https://surf.sellandbuy.online//wp-content/themes/dashstore-child/img/map.jpg" alt="map">--> 
            <div id="map" style="width:500px; height:800px"></div>
        </div>
    </div>
</section>  
<div class="modal fade mymodal categoriesModal" id="squarespaceModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-right">
                <a class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span></a>
            </div>
            <div class="modal-body">

                <div id="catalog_html"></div>


            </div>

        </div>
    </div>
</div>
<div class="modal fade mymodal seller_modal pa_seller_resetModal" id="squarespaceModal2" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-right">
                <a class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span></a>
                <h4 class="modal-title col-xs-12 col-sm-12 col-md-12">Seller</h4>
            </div>
            <div id="ajax_show_seller"></div>

        </div>
    </div>
</div>
<div class="modal fade mymodal priceModal price_resetModal" id="squarespaceModal7" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-right">
                <a class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span></a>
                <h4 class="modal-title col-xs-12 col-sm-12 col-md-12">Price</h4>
            </div>
            <?php ?>
            <div id="ajax_price_range"> </div>
        </div>
    </div>
</div>
<div class="modal fade mymodal brandModal brand_modal brand_resetModal" id="squarespaceModal3" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-right">
                <a class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span></a>
                <h4 class="modal-title col-sm-12 col-md-12">Brand</h4>

            </div>
            <div id="ajax_show_brand"> </div>

        </div>
    </div>
</div>
<div class="modal fade mymodal yearModal years_modal pa_year_resetModal" id="squarespaceModal4" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-right">
                <a class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span></a>
                <h4 class="modal-title col-sm-12 col-md-12">Year</h4>
            </div>
            <div id="ajax_show_years"></div>



        </div>
    </div>
</div>
<div class="modal fade mymodal" id="squarespaceModal5" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-right">
                <a class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span></a>
                <h4 class="modal-title col-sm-12 col-md-12">Size</h4>
            </div>
            <div id="size_modal_show"></div>

        </div>
    </div>
</div>
<div class="modal fade mymodal conditionModal pa_condition_resetModal" id="squarespaceModal6" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-right">
                <a class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span></a>
                <h4 class="modal-title col-sm-12 col-md-12">Condition</h4>
            </div>

            <div id="ajax_condition_modal"></div>

        </div>
    </div>
</div>
<div class="modal fade mymodal pa_size_resetModal" id="sizeMessageModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-right">
                <a class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span></a>
                <h4 class="modal-title col-sm-12 col-md-12">Size</h4>
            </div>
            <div class="modal-body">
                <br>
                <div class="row">
                    <p>Please select any subcategories to view size filters</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="myfooter">

                </div>
            </div>

        </div>
    </div>
</div>
<div class="container mmpes22">
    <div id="myBtn"><img src="https://surf.sellandbuy.online//wp-content/themes/dashstore-child/img/map-icon.png" alt="map-icon" width="50"></div>
    <div id='map' class='main-map'> </div>
    <!-- The Modal -->
    <div id="myModal" class="modal2">

        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <p><img src="https://surf.sellandbuy.online//wp-content/themes/dashstore-child/img/map.jpg" alt="map"></p>
        </div>

    </div>
</div>

<script>
//    jQuery(function ($) {
//        var min_price = '<?php echo $prices->min_price; ?>';
//        var max_price = '<?php echo $prices->max_price; ?>';
//        jQuery("#slider-range").slider({
//            range: true,
//            min: parseInt(min_price),
//            max: parseInt(max_price),
//            values: [100,150],
//            slide: function (event, ui) {
//
//                jQuery("#original_amount").hide();
//                jQuery("#ajax_amount").html('<h3>&euro;' + ui.values[ 0 ] + '- &euro;' + ui.values[ 1 ] + '</h3>');
//                jQuery("#amount").val(ui.values[ 0 ] + "-" + ui.values[ 1 ]);
//            }
//        });
//        $("#amount").val("$" + $("#slider-range").slider("values", 0) +
//                " - $" + $("#slider-range").slider("values", 1));
//    });
</script>
<script>
    jQuery(window).scroll(function () {
        if (jQuery(this).scrollTop() > 0) {
            jQuery('#showFilter').addClass('filter-scrolled');
        } else {
            jQuery('#showFilter').removeClass('filter-scrolled');
        }
    });</script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!--<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAAIxlNoTA-_i04glqIbxD9QvJbC_y5z_E&callback=initMap" type="text/javascript"></script>-->

<!--<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs&callback=myMap" type="text/javascript"></script>-->
<script>

    jQuery(document).ready(function () {

        jQuery("body").on("mouseenter", ".products li", function () {

            var id = jQuery(this).data('author');
            var authicon = 'https://dev.sellandbuy.online/wp-content/uploads/2018/03/pin.png?i=' + id + '&orgPrice=' + jQuery(this).data('price');
            for (var i = 0; i < allMarkers.length; i++) {
                if (id === allMarkers[i].id) {
                    allMarkers[i].setIcon('https://dev.sellandbuy.online/wp-content/uploads/2018/03/pin-orange.png?i=' + id);
                    var label = allMarkers[i].getLabel();
                    // Set localstorage if not set yet
                    localStorage['orgPriceOnLabel'] = label.text;
                    // Trim price
                    var price = jQuery(this).data('price');
                    price = price.toString().replace('.00', '');
                    label.color = "white";
                    label.text = "�" + price + ",-";
                    allMarkers[i].setLabel(label);
                    if (allMarkers[i].getZIndex()) {
                        oldZIndex = allMarkers[i].getZIndex();
                    } else {
                        oldZIndex = 1;
                    }
                    allMarkers[i].setZIndex(9999);
                    break;
                } else {
                    //console.log("ERROR: No markers found");	
                }
            }

        }).on("mouseleave", ".products", function () {


            var id = jQuery(this).data('author');
            var authicon = 'https://dev.sellandbuy.online/wp-content/uploads/2018/03/pin-orange.png?i=' + id;
            for (var i = 0; i < allMarkers.length; i++) {
                if (id === allMarkers[i].id) {
                    allMarkers[i].setIcon('https://dev.sellandbuy.online/wp-content/uploads/2018/03/pin.png?i=' + id);
                    var label = allMarkers[i].getLabel();
                    label.color = "black";
                    label.text = localStorage['orgPriceOnLabel'];
                    allMarkers[i].setLabel(label);
                    if (oldZIndex > 0) {
                    } else {
                        var oldZIndex = 1;
                    } /* Set oldZIndex if not set */
                    allMarkers[i].setZIndex(oldZIndex);
                    break;
                }
            }

            localStorage.clear();
        });
        filtersButtons('hide');
        markerClick = false;
        initMap();
        initializeAutocomplete('location');


    });

    var highestZIndex = 0;
    var markers = new Array();
    var allMarkers = [];
    var ZIndex = 0;
    // Returns if a value is an object
    function isNotObject(value) {
        return value && typeof value !== 'object' && value.constructor !== Object;
    }

    var getCentroid = function (coord) {
        //console.log("Coord: " + coord);
        if (isNotObject(coord)) {
            var center = coord.reduce(function (x, y) {
                return [x[0] + y[0] / coord.length, x[1] + y[1] / coord.length]
            }, [0, 0])
            return center;
        } else {
            var center = coord[0].reduce(function (x, y) {
                return [x[0] + y[0] / coord[0].length, x[1] + y[1] / coord[0].length]
            }, [0, 0])
            return center;
        }
    }
    function myMap() {
        google.maps.event.addListener(map, "click", function (event) {
            infowindow.close();
        });
        markerClick = false;
        var totallatlng = <?php echo json_encode($totallatlng); ?>;
        if (totallatlng != null && centeroflat != null) {
            var centeroflat = getCentroid(totallatlng);
            searchlat = centeroflat[0];
            searchlon = centeroflat[1];
        } else {
            searchlat = '<?php echo $latitude; ?>';
            searchlon = '<?php echo $longitude; ?>';
        }
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: parseFloat(searchlat), lng: parseFloat(searchlon)},
            zoom: 3.25,
            mapTypeControl: false,
            streetViewControl: false
        });
        var bounds = new google.maps.LatLngBounds();
        var products = <?php echo json_encode($test); ?>;
        var infowindow = [],
                marker = [];
        if (totallatlng != null) {
            for (i in products) {
                ZIndex = ZIndex + 1;
                //	console.log("ZIndex 2: "+ZIndex);
                //console.log("Count: "+ZIndex);
                var product = products[i];
                infowindow = new google.maps.InfoWindow({content: '', maxWidth: 300});
                var image = {
                    url: 'https://dev.sellandbuy.online/wp-content/uploads/2018/03/pin.png?i=' + (product.authorid),
                    size: new google.maps.Size(50, 35),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(25, 35)
                };
                if (product.photo == "") {
                    product.photo = "https://dev.sellandbuy.online/wp-content/uploads/2016/10/placeholder_new.png"
                }
                var marker = new google.maps.Marker({
                    id: product.authorid,
                    position: new google.maps.LatLng(product.latitude, product.longitude),
                    map: map,
                    icon: image,
                    zIndex: ZIndex,
                    labelClass: "labels", // the CSS class for the label 
                    label: {
                        text: "�" + product.withoutcurrencyprice + ",-",
                        color: "#000000",
                        fontSize: "14px",
                        fontWeight: "bold",
                    },
                    htmlContent: '<div class="infowindow">' +
                            '<ul><li><img class="infowindowImg" src="' + product.photo + '"/></li></ul>' +
                            '<h3><a href="' + product.url + '" target="_blank">' + product.title + '</a></h3>' +
                            '<p class="' + product.conditionS + '">' + product.condition + '</p>' +
                            '<h2>' + product.maiprice + '</strong></h2>' +
                            '</div>'
                });
                //console.log("Product ID: " + product.ID);

                allMarkers.push(marker);
                marker.addListener('click', function () {
                    infowindow.setContent(this.htmlContent);
                    infowindow.open(map, this);
                    markerClick = true;
                });
                google.maps.event.addListener(infowindow, 'domready', function () {

                    // Reference to the DIV which receives the contents of the infowindow using jQuery
                    var iwOuter = jQuery('.gm-style-iw');
                    /* The DIV we want to change is above the .gm-style-iw DIV.
                     * So, we use jQuery and create a iwBackground variable,
                     * and took advantage of the existing reference to .gm-style-iw for the previous DIV with .prev().
                     */
                    var iwBackground = iwOuter.prev();
                    // Remove the background shadow DIV
                    iwBackground.children(':nth-child(2)').css({'display': 'none'});
                    // Remove the white background DIV
                    iwBackground.children(':nth-child(4)').css({'display': 'none'});
                    // Moves the infowindow 115px to the right.
                    //iwOuter.parent().parent().css({left: '30px'});

                    // Moves the shadow of the arrow 76px to the left margin 
                    iwBackground.children(':nth-child(1)').attr('style', function (i, s) {
                        return s + 'left: 76px !important;'
                    });
                    // Moves the arrow 76px to the left margin 
                    iwBackground.children(':nth-child(3)').attr('style', function (i, s) {
                        return s + 'left: 76px !important;'
                    });
                    // Changes the desired color for the tail outline.
                    // The outline of the tail is composed of two descendants of div which contains the tail.
                    // The .find('div').children() method refers to all the div which are direct descendants of the previous div. 
                    iwBackground.children(':nth-child(3)').find('div').children().css({'box-shadow': 'rgba(72, 181, 233, 0.6) 0px 1px 6px', 'z-index': '1'}); // Taking advantage of the already established reference to

                    // Taking advantage of the already established reference to
                    // div .gm-style-iw with iwOuter variable.
                    // You must set a new variable iwCloseBtn.
                    // Using the .next() method of JQuery you reference the following div to .gm-style-iw.
                    // Is this div that groups the close button elements.
                    var iwCloseBtn = iwOuter.next();
                    // Apply the desired effect to the close button
                    iwCloseBtn.css({
                        opacity: '1', // by default the close button has an opacity of 0.7
                        right: '38px',
                        top: '3px', // button repositioning
                        border: '7px solid #FFFFFF', // increasing button border and new color
                        'border-radius': '13px', // circular effect
                        'box-shadow': '0 0 5px #FFFFFF', // 3D effect to highlight the button
                        'box-sizing': 'content-box'
                    });
                    // The API automatically applies 0.7 opacity to the button after the mouseout event.
                    // This function reverses this event to the desired value.
                    iwCloseBtn.mouseout(function () {
                        jQuery(this).css({opacity: '1'});
                    });
                    // Close info windows on click on map
                    google.maps.event.addListener(map, "click", function (event) {
                        infowindow.close();
                    });
                });
                markers.push(marker);
                // assign a custom variable to this marker containing the zIndex  
                marker.set("myZIndex", marker.getZIndex());
                marker.set('labelClass', 'labels');
                // add mouseover and mouseout events to second marker  
                google.maps.event.addListener(marker, "mouseover", function () {
                    getHighestZIndex();
                    this.setOptions({zIndex: highestZIndex + 1});
                });
                google.maps.event.addListener(marker, "mouseout", function () {
                    this.setOptions({zIndex: this.get("myZIndex")});
                });
                /* google.maps.event.addListener(map, 'idle', function() {
                 if(markerClick){
                 markerClick = false;
                 }
                 });*/

                var position = new google.maps.LatLng(51.1657, 10.4515);
                bounds.extend(position);
            }
            if (totallatlng.length > 1) {
                //map.fitBounds(bounds);
            }
        }

        /*google.maps.event.addListener(map, 'dragend', function(event) {
         var c = map.getCenter();
         jQuery("#latitude").val(c.lat());
         jQuery("#longtitude").val(c.lng());
         document.getElementById('latitudediv').innerHTML  = c.lat();  
         });*/

        google.maps.event.addListener(map, 'zoom_changed', function (event) {
            jQuery("#currentzoom").val(map.getZoom());
        });
    }

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
        var address = place.name;
        if (address.indexOf('Crete') > -1) {
            geocoder = new google.maps.Geocoder();
            if (geocoder) {
                geocoder.geocode({
                    'address': address
                }, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        var lat = results[0].geometry.location.lat();
                        var lng = results[0].geometry.location.lng();
                        jQuery("#latitude").val(lat);
                        jQuery("#longtitude").val(lng);
                        jQuery("#latitudediv").html(lat);
                        jQuery("#longtitudediv").html(lng);
                    }
                });
            }
        } else {
            var lat = place.geometry.location.lat();
            var lng = place.geometry.location.lng();
            jQuery("#latitude").val(lat);
            jQuery("#longtitude").val(lng);
            jQuery("#latitudediv").html(lat);
            jQuery("#longtitudediv").html(lng);
        }
        //alert("Latitude: " + lat + "\nLongitude: " + lng);
        //  var lat = place.geometry.location.lat();
        // var lng = place.geometry.location.lng();

        document.getElementById('locality').value = '';
        for (var i in place.address_components) {
            var component = place.address_components[i];
            for (var j in component.types) {  // Some types are ["country", "political"]
                var type_element = document.getElementById(component.types[j]);
                // console.log(component);
                if (type_element) {
                    type_element.value = component.long_name;
                }
            }
        }
    }

































    function getUrlVars()
    {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for (var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }
    function showBrands(category_name) {
        jQuery(".loading_more_data").show();
        jQuery(".load_more_data").hide();
        var url = jQuery(location).attr("href");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "showBrands", "category_name": category_name, "url": url},
            success: function (response) {
                jQuery(".loading_more_data").hide();
                jQuery("#original_brands").hide();
                jQuery("#ajax_load_more_brands").html(response);
            }
        });
    }
    function showYear(category_name) {

        jQuery(".loading_more_data").show();
        jQuery(".load_more_data").hide();
        var url = jQuery(location).attr("href");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "showYears", "category_name": category_name, "url": url},
            success: function (response) {
                jQuery(".loading_more_data").hide();
                jQuery("#original_years").hide();
                jQuery("#ajax_load_more_years").html(response);
            }
        });
    }
    function showSize(category_name) {

        jQuery(".loading_more_data").show();
        jQuery(".load_more_data").hide();
        var url = jQuery(location).attr("href");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "showSizes", "category_name": category_name, "url": url},
            success: function (response) {
                jQuery(".loading_more_data").hide();
                jQuery("#original_size").hide();
                jQuery("#ajax_load_more_size").html(response);
            }
        });
    }
    function showConditions(category_name) {

        jQuery(".loading_more_data").show();
        jQuery(".load_more_data").hide();
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "showConditions", "category_name": category_name},
            success: function (response) {
                jQuery(".loading_more_data").hide();
                jQuery("#original_conditions").hide();
                jQuery("#ajax_load_more_conditions").html(response);
            }
        });
    }
    function showSeller(category_name) {

        jQuery(".loading_more_data").show();
        jQuery(".load_more_data").hide();
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "showSeller", "category_name": category_name},
            success: function (response) {
                jQuery(".loading_more_data").hide();
                jQuery("#original_conditions").hide();
                jQuery("#ajax_load_more_conditions").html(response);
            }
        });
    }
    function applyPriceRange(category_name) {

        jQuery(".loader").show();
        var amount = jQuery("input[name=apply_price_range]").val();
        var pageURL = jQuery(location).prop("href");
        var params = getUrlVars()['price'];
        window.history.pushState({}, document.location, pageURL + "&price=" + amount);
        var url = window.location.href;

        //clean_uri = url.split('&price')[0];
        var page_params = getUrlVars()['page_no'];
        if (page_params && params) {
            clean_uri = url.split('&page_no')[0];
            console.log("clean_uri 2: " + clean_uri);
            clean_uri = url.split('&price')[0];
            console.log("clean_uri 1: " + clean_uri);
        } else if (page_params) {
            clean_uri = url.split('&price')[0];
            console.log("clean_uri 1: " + clean_uri);
            clean_uri = url.split('&page_no')[0];
            console.log("clean_uri 2: " + clean_uri);
        } else {
            clean_uri = url.split('&price')[0];
        }
        window.history.replaceState({}, document.location, clean_uri + "&price=" + amount);
        jQuery(".priceModal").modal("hide");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"amount": amount, "action": "filterPriceRange", "category_name": category_name, "url": url},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }

    function productPriceFilter(page, category_name, min_amount, max_amount) {
        console.log("In hereeeeeeee");
        jQuery(".loader").show();
        jQuery(".priceModal").modal("hide");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "filterPagePriceRange", "category_name": category_name, 'page': page, 'min_amount': min_amount, 'max_amount': max_amount},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }


    function applySellerFilter(category_name) {

        var seller = [];
        var seller_name = [];
        jQuery.each(jQuery("input[name='seller[]']:checked"), function () {
            seller.push(jQuery(this).val());
        });
        jQuery.each(jQuery("input[name='seller[]']:checked"), function () {
            seller_name.push(jQuery(this).attr('data-seller'));
        });
        var checked_value = seller.join(',');
        var checked_value_name = seller_name.join(',');
        var pageURL = jQuery(location).prop("href");
        var params = getUrlVars()['pa_seller'];
        var page_params = getUrlVars()['page_no'];
        window.history.pushState({}, document.location, pageURL + "&pa_seller=" + checked_value_name);
        var url = window.location.href;
        var page_params = getUrlVars()['page_no'];

        if (page_params) {
            clean_uri = url.split('&pa_seller')[1];
            clean_uri = url.split('&page_no')[0];
        } else {
            clean_uri = url.split('&pa_seller')[0];
        }
        window.history.replaceState({}, document.location, clean_uri + "&pa_seller=" + checked_value_name);
        jQuery("#squarespaceModal2").modal("hide");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"checked_value": checked_value, "action": "sellerFilter", "category_name": category_name, "url": url},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }
    function applyBrandFilter(category_name) {

        var brand = [];
        var brand_name = [];
        jQuery.each(jQuery("input[name='selected_brand[]']:checked"), function () {
            brand.push(jQuery(this).val());
        });
        jQuery.each(jQuery("input[name='selected_brand[]']:checked"), function () {
            brand_name.push(jQuery(this).attr('data-brand'));
        });
        var checked_value = brand.join(',');
        var checked_value_name = brand_name.join(',');
        var pageURL = jQuery(location).prop("href");
        var params = getUrlVars()['brand'];
        window.history.pushState({}, document.location, pageURL + "&brand=" + checked_value_name);
        var url = window.location.href;
        var page_params = getUrlVars()['page_no'];
        if (page_params) {
            clean_uri = url.split('&brand')[1];
            clean_uri = url.split('&page_no')[0];
        } else {
            clean_uri = url.split('&brand')[0];
        }

        window.history.replaceState({}, document.location, clean_uri + "&brand=" + checked_value_name);
        jQuery(".brandModal").modal("hide");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"checked_value": checked_value, "action": "brandFilter", "category_name": category_name, "url": url},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }
    function applyYearFilter(category_name) {
        var year = [];
        var year_name = [];
        jQuery.each(jQuery("input[name='selected_years[]']:checked"), function () {
            year.push(jQuery(this).val());
        });
        jQuery.each(jQuery("input[name='selected_years[]']:checked"), function () {
            year_name.push(jQuery(this).attr('data-year'));
        });
        var checked_value = year.join(',');
        var checked_value_name = year_name.join(',');
        var pageURL = jQuery(location).prop("href");
        var params = getUrlVars()['pa_year'];
        var url = jQuery(location).attr("href");
        window.history.pushState({}, document.location, pageURL + "&pa_year=" + checked_value_name);
        var url = window.location.href;
        var page_params = getUrlVars()['page_no'];
        if (page_params) {
            clean_uri = url.split('&pa_year')[1];
            clean_uri = url.split('&page_no')[0];
        } else {
            clean_uri = url.split('&pa_year')[0];
        }

        window.history.replaceState({}, document.location, clean_uri + "&pa_year=" + checked_value_name);
        //window.history.replaceState({}, document.location, clean_uri + "&pa_year=" + checked_value_name);
        jQuery(".yearModal").modal("hide");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"checked_value": checked_value, "action": "yearFilter", "category_name": category_name, 'url': url},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }
    function applySizeFilter(category_name, attribute_key) {


        var checked_name = jQuery("input[name=" + attribute_key + "]:checked").attr('data-size-name');
        var checked_value = jQuery("input[name=" + attribute_key + "]:checked").attr('data-size');
        var pageURL = jQuery(location).prop("href");
        var params = getUrlVars()['pa_size'];
        var url = jQuery(location).attr("href");
        window.history.pushState({}, document.location, pageURL + "&" + checked_name + "=" + checked_value);
        var url = window.location.href;


        var page_params = getUrlVars()['page_no'];
        if (page_params) {
            clean_uri = url.split("&" + checked_name)[1];
            clean_uri = url.split('&page_no')[0];
        } else {
            clean_uri = url.split("&" + checked_name)[0];
        }
        window.history.replaceState({}, document.location, clean_uri + "&" + checked_name + "=" + checked_value);
        //window.history.pushState({}, document.title, "?category=" + category_name + "&" + checked_size_name + "=" + checked_size_value);
        //jQuery("#squarespaceModal5").modal("hide");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "showAjaxSizeModal", "category_name": category_name, "url": url},
            success: function (data) {
                jQuery("#ajax_load_more_size").html(data);
                jQuery("#original_size").hide();
            }
        });
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"checked_value": checked_value, "action": "sizeFilter", "category_name": category_name, "url": url},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }
    function applyConditionsFilter(category_name, attribute_key) {
        var condition = [];
        var checked_name = jQuery("input[name=" + attribute_key + "]:checked").attr('data-condition-name');
        var checked_value = jQuery("input[name=" + attribute_key + "]:checked").attr('data-condition');
        var pageURL = jQuery(location).prop("href");
        var params = getUrlVars()['pa_condition'];
        var url = jQuery(location).attr("href");
        window.history.pushState({}, document.location, pageURL + "&" + checked_name + "=" + checked_value);
        var url = window.location.href;
        clean_uri = url.split("&" + checked_name)[0];
        page_clean_uri = url.split('&page_no')[0];
        var page_params = getUrlVars()['page_no'];
        if (page_params) {
            clean_uri = url.split("&" + checked_name)[1];
            clean_uri = url.split('&page_no')[0];
        } else {
            clean_uri = url.split("&" + checked_name)[0];
        }
        window.history.replaceState({}, document.location, clean_uri + "&" + checked_name + "=" + checked_value);
        //jQuery("#squarespaceModal6").modal("hide");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "showAjaxConditionModal", "category_name": category_name, "url": url},
            success: function (data) {
                jQuery("#ajax_load_more_conditions").html(data);
                jQuery("#original_conditions").hide();
            }
        });
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"checked_value": checked_value, "action": "conditionsFilter", "category_name": category_name, "url": url},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }

    // Get the modal
    var modal2 = document.getElementById('myModal');
    // Get the button that opens the modal     var btn = document.getElementById("myBtn");
    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];
    // When the user clicks the button, open the modal 
    btn.onclick = function () {
        modal2.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function () {
        modal2.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (event.target == modal2) {
            modal2.style.display = "none";
        }
    }
</script>
<script>

    jQuery(".fetch_products").on("click", function () {


    });
    jQuery("#ex2").slider({
    });
    jQuery(function () {
        jQuery(".dropdown").hover(
                function () {
                    jQuery('.dropdown-menu', this).stop(true, true).fadeIn("fast");
                    jQuery(this).toggleClass('open');
                    jQuery('b', this).toggleClass("caret caret-up");
                },
                function () {
                    jQuery('.dropdown-menu', this).stop(true, true).fadeOut("fast");
                    jQuery(this).toggleClass('open');
                    jQuery('b', this).toggleClass("caret caret-up");
                });
    });
    jQuery(".showFilter").on("click", function () {

        jQuery("#showFilter").toggle();
    });
    function paginationData(page_no, category) {

//        var url = document.location.href + "&page_no=" + page_no;
        //        document.location = url;         var category_name = '<?php echo $category_name; ?>';
        //window.history.pushState({}, document.title, "?category=" + category_name + "&page_no=" + page_no);

        var pageURL = jQuery(location).prop("href");
        // var params = getUrlVars()['condition'];         var url = jQuery(location).attr("href");
        window.history.pushState({}, document.location, pageURL + "&page_no=" + page_no);
        var url = window.location.href;
        clean_uri = url.split('&page_no')[0];
        window.history.replaceState({}, document.location, clean_uri + "&page_no=" + page_no);
        jQuery(".loader").show();
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"page_no": page_no, "category": category, "action": "pagination_data", "url": url},
            success: function (response) {
                jQuery('html, body').animate({
                    scrollTop: jQuery("body").offset().top
                }, 2000);
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }
    function locationPaginationData(page_no, category, location) {
//        var url = document.location.href + "&page_no=" + page_no;
        //        document.location = url;         var category_name = '<?php echo $category_name; ?>';
        window.history.pushState({}, document.title, "?category=" + category + "&location=" + location + "&page_no=" + page_no);
        jQuery(".loader").show();
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"page_no": page_no, "category": category, "location": location, "action": "location_pagination_data", "url": url},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }

    function seachKeyword() {
        var keyword = jQuery("input[name=search_keywords]").val();
        var keyword_length = keyword.length;
        var category = getUrlVars()["category"];
        if (keyword_length == '0') {
            var pageURL = jQuery(location).prop("href");

            var location_search = jQuery(".search-query3").val();
            var url = window.location.href;
            clean_uri = url.split('&search_keyword')[0];
            page_clean_uri = url.split('&page_no')[0];
            window.history.replaceState({}, document.location, clean_uri + "&location=" + location_search);
            jQuery(".loader").show();
            jQuery.ajax({
                type: "POST",
                url: "/wp-content/themes/dashstore-child/ajax_request.php",
                data: {"keyword": keyword, "action": "search_keyword", "url": url, "category_name": category, "location": location_search},
                success: function (response) {
                    jQuery(".loader").hide();
                    jQuery("#original_product").hide();
                    jQuery("#ajax_product_record").html(response);
                }
            });
        } else {
            var pageURL = jQuery(location).prop("href");
            window.history.pushState({}, document.location, pageURL + "&search_keyword=" + keyword);
            var location_search = jQuery(".search-query3").val();
            var url = window.location.href;
            clean_uri = url.split('&search_keyword')[0];
            //  page_clean_uri = url.split('&page_no')[0];
            var page_params = getUrlVars()['page_no'];

            if (page_params) {
                clean_uri = url.split('&search_keyword')[1];
                clean_uri = url.split('&page_no')[0];
            } else {
                clean_uri = url.split('&search_keyword')[0].split('&location')[0];
            }

            jQuery(".loader").show();

            if (location_search) {
                window.history.replaceState({}, document.location, clean_uri + "&search_keyword=" + keyword + "&location=" + location_search);

                jQuery.ajax({
                    type: "POST",
                    url: "/wp-content/themes/dashstore-child/ajax_request.php",
                    data: {"keyword": keyword, "action": "search_keyword", "url": url, "category_name": category, "location": location_search},
                    success: function (response) {
                        jQuery(".loader").hide();
                        jQuery("#original_product").hide();
                        jQuery("#ajax_product_record").html(response);
                    }
                });
            } else {
                window.history.replaceState({}, document.location, clean_uri + "&search_keyword=" + keyword);

                jQuery.ajax({
                    type: "POST",
                    url: "/wp-content/themes/dashstore-child/ajax_request.php",
                    data: {"keyword": keyword, "action": "search_keyword", "url": url, "category_name": category},
                    success: function (response) {
                        jQuery(".loader").hide();
                        jQuery("#original_product").hide();
                        jQuery("#ajax_product_record").html(response);
                    }
                });
            }


        }
    }


    function showChildCategories(category_id, category_name) {
        window.history.pushState({}, document.title, "?category=" + category_name);
        if (category_name == 'windsurf' || category_name == 'kitesurf' || category_name == 'stand-up-paddle' || category_name == 'surf' || category_name == 'surfwear' || category_name == 'action-camera') {
            jQuery(".categorySizeModal").hide();
            jQuery(".ajaxCategorySizeModal").hide();
            jQuery(".ajaxCategoryModal").show();
        } else {
            jQuery(".categorySizeModal").hide();
            jQuery(".ajaxCategorySizeModal").show();
            jQuery(".ajaxCategoryModal").hide();
        }
        jQuery("#popup_loader").show();
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"category_id": category_id, "action": "showChildCategories"},
            success: function (response) {
                jQuery("#popup_loader").hide();
                jQuery(".sub_cat_boards").show();
                jQuery(".sub_cat_boards").html(response);
                jQuery.ajax({
                    type: "POST",
                    url: "/wp-content/themes/dashstore-child/ajax_request.php",
                    data: {"category": category_id, "action": "fetchCategories"},
                    success: function (response) {


                        jQuery(".loader").hide();
                        jQuery("#original_product").hide();
                        jQuery("#ajax_product_record").html(response);
                    }
                });
            }
        });
    }
    function showSubChildCategories(category_id, category_name) {
        jQuery("#popup_loader").show();
        window.history.pushState({}, document.title, "?category=" + category_name);
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"category_id": category_id, "action": "showSubChildCategories"},
            success: function (response) {
                if (response == '0') {
                    jQuery("#squarespaceModal").modal("hide");
                    jQuery("#popup_loader").hide();
                } else {
                    jQuery("#squarespaceModal").modal("hide");
                    jQuery("#popup_loader").hide();
                    jQuery(".sub_child_cat_boards").show();
                    jQuery(".sub_child_cat_boards").html(response);
                    jQuery.ajax({
                        type: "POST",
                        url: "/wp-content/themes/dashstore-child/ajax_request.php",
                        data: {"category": category_id, "action": "fetchCategories"},
                        success: function (response) {
                            jQuery(".loader").hide();
                            jQuery("#original_product").hide();
                            jQuery("#ajax_product_record").html(response);
                        }
                    });
                }
            }
        });
    }
    function showLastChildCategories(category_id, category_name) {

        window.history.pushState({}, document.title, "?category=" + category_name);
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"category": category_id, "action": "fetchCategories"},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#squarespaceModal").modal("hide");
                jQuery("#ajax_product_record").html(response);
            }
        });
    }


</script> 

<script type="text/javascript">



    function showCatalogModal() {
        var category = getUrlVars()["category"];
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "catalogModal", "category": category},
            success: function (response) {
                jQuery("#squarespaceModal").modal("show");
                jQuery("#catalog_html").html(response);
            }
        });
    }
//    function fetchData(category, category_id, category_name) {
//        var category_url = getUrlVars()["category"];
//        var location = getUrlVars()["location"];
//        jQuery(".sub_cat_boards").hide();
//        jQuery("#" + category_url).removeClass('active_catalog');
//        jQuery("#" + category_name).addClass('active_catalog');
//        jQuery(".sub_child_cat_boards").hide();
//        //jQuery(".loader").show();
//        window.history.pushState({}, document.title, "?category=" + category_name);
//        if (category_name == 'windsurf' || category_name == 'kitesurf' || category_name == 'stand-up-paddle' || category_name == 'surf' || category_name == 'surfwear' || category_name == 'action-camera') {
//            jQuery(".categorySizeModal").hide();
//            jQuery(".ajaxCategorySizeModal").hide();
//            jQuery(".ajaxCategoryModal").show();
//        } else {
//            jQuery(".categorySizeModal").hide();
//            jQuery(".ajaxCategorySizeModal").show();
//            jQuery(".ajaxCategoryModal").hide();
//        }
//        if (category_id == 'windsurf_cat') {
//            jQuery("#" + category_id).show();
//            jQuery("#kitesurf_cat").hide();
//            jQuery("#sup_cat").hide();
//            jQuery("#surf_cat").hide();
//            jQuery("#surfwear_cat").hide();
//            jQuery("#camera_cat").hide();
//        } else if (category_id == 'kitesurf_cat') {
//            jQuery("#" + category_id).show();
//            jQuery("#windsurf_cat").hide();
//            jQuery("#sup_cat").hide();
//            jQuery("#surf_cat").hide();
//            jQuery("#surfwear_cat").hide();
//            jQuery("#camera_cat").hide();
//        } else if (category_id == 'sup_cat') {
//            jQuery("#" + category_id).show();
//            jQuery("#windsurf_cat").hide();
//            jQuery("#kitesurf_cat").hide();
//            jQuery("#surf_cat").hide();
//            jQuery("#surfwear_cat").hide();
//            jQuery("#camera_cat").hide();
//        } else if (category_id == 'surf_cat') {
//            jQuery("#" + category_id).show();
//            jQuery("#windsurf_cat").hide();
//            jQuery("#kitesurf_cat").hide();
//            jQuery("#sup_cat").hide();
//            jQuery("#surfwear_cat").hide();
//            jQuery("#camera_cat").hide();
//        } else if (category_id == 'surfwear_cat') { 
//            jQuery("#" + category_id).show();
//            jQuery("#windsurf_cat").hide();
//            jQuery("#kitesurf_cat").hide();
//            jQuery("#sup_cat").hide();
//            jQuery("#surf_cat").hide();
//            jQuery("#camera_cat").hide();
//        } else {
//            jQuery("#" + category_id).show();
//            jQuery("#windsurf_cat").hide();
//            jQuery("#kitesurf_cat").hide();
//            jQuery("#sup_cat").hide();
//            jQuery("#surf_cat").hide();
//            jQuery("#surfwear_cat").hide();
//        }
//
//
////        jQuery.ajax({
////            type: "POST",
////            url: "/wp-content/themes/dashstore-child/ajax_request.php",
////            data: {"category": category, "action": "fetchSubCategories"},
////            success: function (result) {
////                jQuery(".Boards").html(result);
////            }
//        //        });
//        jQuery.ajax({
//            type: "POST",
//            url: "/wp-content/themes/dashstore-child/ajax_request.php",
//            data: {"category": category, "action": "fetchCategories", "location": location},
//            success: function (response) {
//                jQuery(".product_count_class").hide();
//                jQuery(".loader").hide();
//                jQuery("#original_product").hide();
//                jQuery("#ajax_product_record").html(response);
//            }
//        });
//    }
    function fetchData(category, category_id, category_name) {
        var category_url = getUrlVars()["category"];
        var location = getUrlVars()["location"];
        jQuery(".sub_cat_boards").hide();
        jQuery("#" + category_url).removeClass('active_catalog');
        jQuery("#" + category_name).addClass('active_catalog');
        jQuery(".sub_child_cat_boards").hide();
        //jQuery(".loader").show();
        window.history.pushState({}, document.title, "?category=" + category_name);
        if (category_name == 'windsurf' || category_name == 'kitesurf' || category_name == 'stand-up-paddle' || category_name == 'surf' || category_name == 'surfwear' || category_name == 'action-camera') {
            jQuery(".categorySizeModal").hide();
            jQuery(".ajaxCategorySizeModal").hide();
            jQuery(".ajaxCategoryModal").show();
        } else {
            jQuery(".categorySizeModal").hide();
            jQuery(".ajaxCategorySizeModal").show();
            jQuery(".ajaxCategoryModal").hide();
        }
        if (category_id == 'windsurf_cat') {
            jQuery("#" + category_id).show();
            jQuery("#kitesurf_cat").hide();
            jQuery("#sup_cat").hide();
            jQuery("#surf_cat").hide();
            jQuery("#surfwear_cat").hide();
            jQuery("#camera_cat").hide();
        } else if (category_id == 'kitesurf_cat') {
            jQuery("#" + category_id).show();
            jQuery("#windsurf_cat").hide();
            jQuery("#sup_cat").hide();
            jQuery("#surf_cat").hide();
            jQuery("#surfwear_cat").hide();
            jQuery("#camera_cat").hide();
        } else if (category_id == 'sup_cat') {
            jQuery("#" + category_id).show();
            jQuery("#windsurf_cat").hide();
            jQuery("#kitesurf_cat").hide();
            jQuery("#surf_cat").hide();
            jQuery("#surfwear_cat").hide();
            jQuery("#camera_cat").hide();
        } else if (category_id == 'surf_cat') {
            jQuery("#" + category_id).show();
            jQuery("#windsurf_cat").hide();
            jQuery("#kitesurf_cat").hide();
            jQuery("#sup_cat").hide();
            jQuery("#surfwear_cat").hide();
            jQuery("#camera_cat").hide();
        } else if (category_id == 'surfwear_cat') { 
            jQuery("#" + category_id).show();
            jQuery("#windsurf_cat").hide();
            jQuery("#kitesurf_cat").hide();
            jQuery("#sup_cat").hide();
            jQuery("#surf_cat").hide();
            jQuery("#camera_cat").hide();
        } else {
            jQuery("#" + category_id).show();
            jQuery("#windsurf_cat").hide();
            jQuery("#kitesurf_cat").hide();
            jQuery("#sup_cat").hide();
            jQuery("#surf_cat").hide();
            jQuery("#surfwear_cat").hide();
        }


//        jQuery.ajax({
//            type: "POST",
//            url: "/wp-content/themes/dashstore-child/ajax_request.php",
//            data: {"category": category, "action": "fetchSubCategories"},
//            success: function (result) {
//                jQuery(".Boards").html(result);
//            }
        //        });
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"category": category, "action": "fetchCategories", "location": location},
            success: function (response) {
                jQuery(".product_count_class").hide();
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }
    function resetFilter() {
        var cat_name = getUrlVars()['category'];
        bootbox.confirm({
            message: "Are you sure to reset all filter?",
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if (result == true) {
                    location.href = "https://surf.sellandbuy.online/map/?category=" + cat_name;
                }
            }
        });

    }

    function showBrand() {
        var brand = [];
        var brand_name = [];
        jQuery.each(jQuery("input[name='selected_brand[]']:checked"), function () {
            brand.push(jQuery(this).val());
        });
        jQuery.each(jQuery("input[name='selected_brand[]']:checked"), function () {
            brand_name.push(jQuery(this).attr('data-brand'));
        });
        var checked_value = brand.join(',');
        var checked_value_name = brand_name.join(',');
        var category = getUrlVars()["category"];
        jQuery("#squarespaceModal3").modal("show");
        jQuery("#ajax_load_more_brands").hide();
        jQuery("#original_brands").show();
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "showBrand", "category_name": category},
            success: function (response) {

                jQuery("#original_brands").html(response);
            },
            error: function (response) {
                jQuery("#squarespaceModal3").modal("hide");
            }
        });
    }
//    function showYearModal() {
//        var category = getUrlVars()["category"];
//        var url = jQuery(location).attr("href");
//        jQuery("#squarespaceModal4").modal("show");
//        jQuery.ajax({
//            type: "POST",
//            url: "/wp-content/themes/dashstore-child/ajax_request.php",
//            data: {"action": "showYearModal", "category_name": category, "url": url},
//            success: function (response) {
//
//                jQuery("#ajax_show_year").html(response);
//            },
//            error: function (response) {
//                jQuery("#squarespaceModal4").modal("hide");
//            }
//        });
//    }
//    function showBrandModal() {
//        var category = getUrlVars()["category"];
//        var url = jQuery(location).attr("href");
//        jQuery("#squarespaceModal3").modal("show");
//        jQuery.ajax({
//            type: "POST",
//            url: "/wp-content/themes/dashstore-child/ajax_request.php",
//            data: {"action": "showBrand", "category_name": category, "url": url},
//            success: function (response) {
//
//                jQuery("#ajax_show_brand").html(response);
//            },
//            error: function (response) {
//                jQuery("#squarespaceModal3").modal("hide");
//            }
//        });
//    }
    function showSizeModal() {
        var category = getUrlVars()["category"];
        var url = jQuery(location).attr("href");
        jQuery("#squarespaceModal5").modal("show");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "showSizeModal", "category_name": category, "url": url},
            success: function (response) {

                jQuery("#size_modal_show").html(response);
            },
            error: function (response) {
                jQuery("#squarespaceModal5").modal("hide");
            }
        });
    }
    function showConditionModal() {
        var category = getUrlVars()["category"];
        var url = jQuery(location).attr("href");
        jQuery("#squarespaceModal6").modal("show");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "showConditionModal", "category_name": category, "url": url},
            success: function (response) {

                jQuery("#ajax_condition_modal").html(response);
            },
            error: function (response) {
                jQuery("#squarespaceModal6").modal("hide");
            }
        });
    }
    function showPriceModal() {
        var category = getUrlVars()["category"];
        var price = getUrlVars()["price"];
        var url = jQuery(location).attr("href");
        jQuery("#squarespaceModal7").modal("show");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "showPriceModal", "category_name": category, "url": url, "price": price},
            success: function (response) {

                jQuery("#ajax_price_range").html(response);
//                var min_price = '<?php echo $prices->min_price; ?>';
//
//                var max_price = '<?php echo $prices->max_price; ?>';
                var min_price = jQuery("input[name=minimum_price]").val();
                var max_price = jQuery("input[name=maximum_price]").val();
                jQuery("#slider-range").slider({
                    range: true,
                    min: parseInt(min_price),
                    max: parseInt(max_price),
                    values: [parseInt(min_price), parseInt(max_price)],
                    //values: [180, 380],
                    slide: function (event, ui) {
                        console.log(ui.values);
                        jQuery("#original_amount").hide();
                        jQuery("#ajax_amount").html('<h3>&euro;' + ui.values[ 0 ] + '- &euro;' + ui.values[ 1 ] + '</h3>');
                        jQuery("#amount").val(ui.values[ 0 ] + "-" + ui.values[ 1 ]);
                    }
                });
                //jQuery("#amount").val( jQuery("#slider-range").slider("values", 0) +"-" + jQuery("#slider-range").slider("values", 1));
            },
            error: function (response) {
                jQuery("#squarespaceModal7").modal("hide");
            }
        });
    }
//    function showSellerModal() {
//        var category = getUrlVars()["category"];
//        var url = jQuery(location).attr("href");
//        jQuery("#squarespaceModal2").modal("show");
//        jQuery.ajax({
//            type: "POST",
//            url: "/wp-content/themes/dashstore-child/ajax_request.php",
//            data: {"action": "showSellerModal", "category_name": category, "url": url},
//            success: function (response) {
//
//                jQuery("#ajax_seller_modal").html(response);
//            },
//            error: function (response) {
//                jQuery("#squarespaceModal2").modal("hide");
//            }
//        });
//    }



</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs&sensor=false&libraries=places&language=en&callback=myMap"></script> 


<script>



    var autocomplete = new google.maps.places.Autocomplete(document.getElementById('autocomplete'));
    google.maps.event.addListener(autocomplete, 'place_changed', onPlaceChanged);
    function onPlaceChanged() {
        var category_name = getUrlVars()["category"];
        var place = this.getPlace();
        //        var country = place.address_components['3']['long_name'];        
        var address = place.formatted_address;
        // var address = place.name;         
        var pageURL = jQuery(location).prop("href");
        // var params = getUrlVars()['brand'];

        window.history.pushState({}, document.title, pageURL + "&location=" + address);
        var url = window.location.href;
        clean_uri = url.split('&location')[0];
        window.history.replaceState({}, document.title, clean_uri + "&location=" + address);
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            dataType: "json",
            data: {"category_name": category_name, "address": address, "action": "mapShow", "url": url},
            success: function (response) {
                locationProducts(address);
                myMap();
            }
        });
        if (address.indexOf('Crete') > -1) {
            geocoder = new google.maps.Geocoder();
            if (geocoder) {
                geocoder.geocode({
                    'address': address
                }, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        var lat = results[0].geometry.location.lat();
                        var lng = results[0].geometry.location.lng();
                        jQuery("#latitude").val(lat);
                        jQuery("#longitude").val(lng);
                        jQuery("#latitudediv").html(lat);
                        jQuery("#longtitudediv").html(lng);
                    }
                });
            }
        } else {
            var lat = place.geometry.location.lat();
            var lng = place.geometry.location.lng();
            jQuery("#latitude").val(lat);
            jQuery("#longitude").val(lng);
            jQuery("#latitudediv").html(lat);
            jQuery("#longtitudediv").html(lng);
        }


        document.getElementById('locality').value = '';
        for (var i in place.address_components) {
            var component = place.address_components[i];
            for (var j in component.types) {  // Some types are ["country", "political"]
                var type_element = document.getElementById(component.types[j]);
                // console.log(component);
                if (type_element) {
                    type_element.value = component.long_name;
                }
            }
        }
    }



    var locationmobile = new google.maps.places.Autocomplete(document.getElementById('locationmobile'));
    google.maps.event.addListener(locationmobile, 'place_changed', onPlaceChanged);
    function onPlaceChanged() {
        var category_name = getUrlVars()["category"];
        var place = this.getPlace();
        //        var country = place.address_components['3']['long_name'];        
        var address = place.formatted_address;
        // var address = place.name;         
        var pageURL = jQuery(location).prop("href");
        // var params = getUrlVars()['brand'];

        window.history.pushState({}, document.title, pageURL + "&location=" + address);
        var url = window.location.href;
        clean_uri = url.split('&location')[0];
        window.history.replaceState({}, document.title, clean_uri + "&location=" + address);
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            dataType: "json",
            data: {"category_name": category_name, "address": address, "action": "mapShow", "url": url},
            success: function (response) {
                locationProducts(address);
                myMap();
            }
        });
        if (address.indexOf('Crete') > -1) {
            geocoder = new google.maps.Geocoder();
            if (geocoder) {
                geocoder.geocode({
                    'address': address
                }, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        var lat = results[0].geometry.location.lat();
                        var lng = results[0].geometry.location.lng();
                        jQuery("#latitude").val(lat);
                        jQuery("#longitude").val(lng);
                        jQuery("#latitudediv").html(lat);
                        jQuery("#longtitudediv").html(lng);
                    }
                });
            }
        } else {
            var lat = place.geometry.location.lat();
            var lng = place.geometry.location.lng();
            jQuery("#latitude").val(lat);
            jQuery("#longitude").val(lng);
            jQuery("#latitudediv").html(lat);
            jQuery("#longtitudediv").html(lng);
        }


        document.getElementById('locality').value = '';
        for (var i in place.address_components) {
            var component = place.address_components[i];
            for (var j in component.types) {  // Some types are ["country", "political"]
                var type_element = document.getElementById(component.types[j]);
                // console.log(component);
                if (type_element) {
                    type_element.value = component.long_name;
                }
            }
        }
    }

    function locationProducts(address) {
        jQuery(".loader").show();
        var url = window.location.href;
        var category_name = getUrlVars()["category"];
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"address": address, "action": "locationProducts", "category_name": category_name, "url": url},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }

    function Ionslider(minumumprice, maximumprice) {
        jQuery("#example_id").ionRangeSlider({
            type: "double",
            min: parseInt(minumumprice),
            max: parseInt(maximumprice),
            from: parseInt(minumumprice),
            to: parseInt(maximumprice),
            prefix: "�"
        });
    }
    function noSizeModal() {
        jQuery('#sizeMessageModal').modal('show');
    }
    function noConditionModal() {
        bootbox.alert("Please select any subcategories to view conditions filters");
    }

//    function resetButton(modal_name, category_name) {
//        if (modal_name == 'seller') {
//            var url = window.location.href;
//            clean_uri = url.split("&pa_seller")[0];
//            window.history.replaceState({}, document.location, clean_uri);
//            var new_url = window.location.href;
//            jQuery("#squarespaceModal2").modal("hide");
//            jQuery.ajax({
//                type: "POST",
//                url: "/wp-content/themes/dashstore-child/ajax_request.php",
//                data: {"action": "reset", "category_name": category_name, "url": new_url},
//                success: function (response) {
//                    jQuery(".loader").hide();
//                    jQuery("#original_product").hide();
//                    jQuery("#ajax_product_record").html(response);
//                }
//            });
//        }
//
//        if (modal_name == 'brand') {
//            var years_param = getUrlVars()['pa_year'];
//            if (years_param) {
//                var year = [];
//                var year_name = [];
//                jQuery.each(jQuery("input[name='selected_years[]']:checked"), function () {
//                    year.push(jQuery(this).val());
//                });
//                jQuery.each(jQuery("input[name='selected_years[]']:checked"), function () {
//                    year_name.push(jQuery(this).attr('data-year'));
//                });
//
//                var checked_value_name = year_name.join(',');
//
//                var url = jQuery(location).attr("href");
//                clean_uri = url.split("&brand")['0'];
//                window.history.replaceState({}, document.location, clean_uri + "&pa_year=" + checked_value_name);
//            }
//
//            var seler_param = getUrlVars()['pa_seller'];
//            if (seler_param) {
//                var seller = [];
//                var seller_name = [];
//                jQuery.each(jQuery("input[name='seller[]']:checked"), function () {
//                    seller.push(jQuery(this).val());
//                });
//                jQuery.each(jQuery("input[name='seller[]']:checked"), function () {
//                    seller_name.push(jQuery(this).attr('data-seller'));
//                });
//
//                var checked_value_name = seller_name.join(',');
//                var url = jQuery(location).attr("href");
//                clean_uri = url.split("&brand")['0'];
//                window.history.replaceState({}, document.location, clean_uri + "&pa_seller=" + checked_value_name);
//            }
//
//            var price_params = getUrlVars()['price'];
//            if (price_params) {
//                var amount = jQuery("input[name=apply_price_range]").val();
//                var url = jQuery(location).attr("href");
//                clean_uri = url.split("&brand")['0'];
//                window.history.replaceState({}, document.location, clean_uri + "&price=" + amount);
//
//            }
//
////       var size_params = getUrlVars()['pa_size'];
////       
////        if('size_params'){
////           
////         var checked_name = jQuery("input[name=" + attribute_key + "]:checked").attr('data-size-name');
////        var checked_value = jQuery("input[name=" + attribute_key + "]:checked").attr('data-size');
////        
////        var url = jQuery(location).attr("href");
////            clean_uri = url.split("&brand")['0'];
////           window.history.replaceState({}, document.location, clean_uri + "&checked_name=" + checked_name);
////        
////       }
//
//            var condition_params = getUrlVars()['pa_condition'];
//            if (condition_params) {
//                var condition = [];
//                var params = getUrlVars()['pa_condition'];
//                var checked_name = jQuery("input[name=" + attribute_key + "]:checked").attr('data-condition-name');
//                var checked_value = jQuery("input[name=" + attribute_key + "]:checked").attr('data-condition');
//
//                var url = jQuery(location).attr("href");
//                clean_uri = url.split("&brand")['0'];
//                window.history.replaceState({}, document.location, clean_uri + "&" + pa_condition + "=" + checked_value);
//                // window.history.replaceState({}, document.location, clean_uri + "&pa_condition=" + checked_value);
//
//            } else {
//
//                var url = jQuery(location).attr("href");
//                clean_uri = url.split("&brand")['0'];
//                window.history.replaceState({}, document.location, clean_uri);
//
//                var new_url = window.location.href;
//                jQuery(".brandModal").modal("hide");
//                jQuery.ajax({
//                    type: "POST",
//                    url: "/wp-content/themes/dashstore-child/ajax_request.php",
//                    data: {"action": "reset", "category_name": category_name, "url": new_url},
//                    success: function (response) {
//                        jQuery(".loader").hide();
//                        jQuery("#original_product").hide();
//                        jQuery("#ajax_product_record").html(response);
//                    }
//                });
//            }
//        }
//
//        if (modal_name == 'pa_year') {
//
//            var url = window.location.href;
//            clean_uri = url.split("&pa_year")[0];
//            window.history.replaceState({}, document.location, clean_uri);
//            var new_url = window.location.href;
//            jQuery(".yearModal").modal("hide");
//            jQuery.ajax({
//                type: "POST",
//                url: "/wp-content/themes/dashstore-child/ajax_request.php",
//                data: {"action": "reset", "category_name": category_name, "url": new_url},
//                success: function (response) {
//                    jQuery(".loader").hide();
//                    jQuery("#original_product").hide();
//                    jQuery("#ajax_product_record").html(response);
//                }
//            });
//        }
//
//        if (modal_name == 'price') {
//
//            var url = window.location.href;
//            clean_uri = url.split("&price")[0];
//            window.history.replaceState({}, document.location, clean_uri);
//            var new_url = window.location.href;
//            jQuery(".priceModal").modal("hide");
//            jQuery.ajax({
//                type: "POST",
//                url: "/wp-content/themes/dashstore-child/ajax_request.php",
//                data: {"action": "reset", "category_name": category_name, "url": new_url},
//                success: function (response) {
//                    jQuery(".loader").hide();
//                    jQuery("#original_product").hide();
//                    jQuery("#ajax_product_record").html(response);
//                }
//            });
//        }
//
//        if (modal_name == 'pa_size') {
//            var url = window.location.href;
//            clean_uri = url.split("&pa_size")[0];
//            window.history.replaceState({}, document.location, clean_uri);
//            var new_url = window.location.href;
//            jQuery("#squarespaceModal5").modal("hide");
//            jQuery.ajax({
//                type: "POST",
//                url: "/wp-content/themes/dashstore-child/ajax_request.php",
//                data: {"action": "reset", "category_name": category_name, "url": new_url},
//                success: function (response) {
//                    jQuery(".loader").hide();
//                    jQuery("#original_product").hide();
//                    jQuery("#ajax_product_record").html(response);
//                }
//            });
//        }
//        if (modal_name == 'pa_condition') {
//            var url = window.location.href;
//            clean_uri = url.split("&pa_condition")[0];
//            window.history.replaceState({}, document.location, clean_uri);
//            var new_url = window.location.href;
//            jQuery("#squarespaceModal6").modal("hide");
//            jQuery.ajax({
//                type: "POST",
//                url: "/wp-content/themes/dashstore-child/ajax_request.php",
//                data: {"action": "reset", "category_name": category_name, "url": new_url},
//                success: function (response) {
//                    jQuery(".loader").hide();
//                    jQuery("#original_product").hide();
//                    jQuery("#ajax_product_record").html(response);
//                }
//            });
//        }
//    }
    function resetButton(modal_name, category_name) {
        var url = window.location.href;
        clean_uri = url.split("&" + modal_name)[0];
        window.history.replaceState({}, document.location, clean_uri);
        var new_url = window.location.href;
        jQuery("." + modal_name + "_resetModal").modal("hide");
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "reset", "category_name": category_name, "url": new_url},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });

    }

    function applySizeButton() {
        jQuery("#squarespaceModal5").modal('hide');
    }
    function applyConditionButton() {
        jQuery("#squarespaceModal6").modal('hide');
    }

    function resetSizeButton(category_name) {
        var url = window.location.href;
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "resetSizeModal", "category_name": category_name, "url": url},
            success: function (data) {
                jQuery("#ajax_load_more_size").html(data);
                //jQuery("#ajax_new_load_more_size").html(data);
                jQuery("#original_size").hide();
            }
        });
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "resetSizeButton", "category_name": category_name, "url": url},
            success: function (response) {
                jQuery(".loader").hide();
                jQuery("#original_product").hide();
                jQuery("#ajax_product_record").html(response);
            }
        });
    }

    function showFilterModal(attribute_name) {

        var category = getUrlVars()["category"];
        var url = jQuery(location).attr("href");
        var capitalize_attribute_name = attribute_name.toLowerCase().replace(/\b[a-z]/g, function (letter) {

            jQuery("." + attribute_name + "_modal").modal("show");
            return letter.toUpperCase();
        });

        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/ajax_request.php",
            data: {"action": "show" + capitalize_attribute_name + "Modal", "category_name": category, "url": url},
            success: function (response) {

                jQuery("#ajax_show_" + attribute_name).html(response);
            },
            error: function (response) {
                jQuery("." + attribute_name + "_modal").modal("hide");
            }
        });

    }


</script>
<?php get_footer(); ?> 
