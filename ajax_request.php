<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
if ($_REQUEST['action'] == 'pagination_data') {

    global $wpdb;
    global $wp_query;
    global $post;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';
    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category'];
    $pageNo = $_REQUEST['page_no'];
    $limit = 12;
	
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
    $checked_value = $_REQUEST['checked_value'];

    //$val = explode(",", $checked_value);
    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);
    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));

    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        // $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }

    if ($pa_size) {

        $val = explode(",", $pa_size);
        $tax_size_query = array('relation' => 'AND');
        foreach ($val as $sizeValue):

            $value[] = $sizeValue;
            $getTaxonomy = $wpdb->get_var("SELECT taxonomy FROM iewZNddPterm_taxonomy WHERE term_id =$sizeValue ");

            //foreach ($getTaxonomy as $getTaxonomies):
            $tax_size_query[] = array(
                'taxonomy' => "$getTaxonomy",
                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                'terms' => $value,
                'operator' => 'IN'
            );
            // endforeach;
            // $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'term_id', 'terms' => $sizeValue, 'operator' => 'IN');
        endforeach;
    }

    if ($price) {
        ?><script> console.log("Price"); </script> <?php
		// die('hlo');
        $val = explode("-", $price);
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
        if ($search_keyword) {
			?><script> console.log("Price+Keyword"); </script> <?php
            $arguments = array(
                's' => $search_keyword,
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
        } else if ($location) {
			?><script> console.log("Price+Location"); </script> <?php
          //  die('ehhehe');
            $arguments = array(
                's' => $search_keyword,
				'post_type' => 'product',
                'post_status' => 'publish',
                'offset' => $offset,
                'posts_per_page' => $limit,
                'tax_query' => array(
                    $tax_query
                ),
                'meta_query' => array(
                    $meta_query
                ) // This query will be overwritten below in the location
            );
            $address = $location;
            $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
            $geo = json_decode($geo, true);
            if ($geo['status'] = 'OK') {

                $arguments['order'] = 'ASC';
                $arguments['orderby'] = 'distance';

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



                $arguments['meta_query'] = array(
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
                $arguments['geo_query'] = array(
                    'lat_field' => 'lat', // this is the name of the meta field storing latitude
                    'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                    'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                    'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                    'distance' => $range, // this is the maximum distance to search
                    'units' => 'km'       // this supports options: miles, mi, kilometers, km
                );
            }
        } else {
            $arguments = array(
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
    } else if ($search_keyword && $location) {
		?><script> console.log("Keyword+Location"); </script> <?php
		$arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
        $address = $location;
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {

            $arguments['order'] = 'ASC';
            $arguments['orderby'] = 'distance';

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



            $arguments['meta_query'] = array(
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
            );
            $arguments['geo_query'] = array(
                'lat_field' => 'lat', // this is the name of the meta field storing latitude
                'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                'distance' => $range, // this is the maximum distance to search
                'units' => 'km'       // this supports options: miles, mi, kilometers, km
            );
        }
    } else if ($search_keyword) {
        ?><script> console.log("Keyword"); </script> <?php
		$arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else if ($location) {
        ?><script> console.log("Location"); </script> <?php
		$arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
        $address = $location;
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {

            $arguments['order'] = 'ASC';
            $arguments['orderby'] = 'distance';

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



            $arguments['meta_query'] = array(
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
            $arguments['geo_query'] = array(
                'lat_field' => 'lat', // this is the name of the meta field storing latitude
                'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                'distance' => $range, // this is the maximum distance to search
                'units' => 'km'       // this supports options: miles, mi, kilometers, km
            );
        }
    } else {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    }

    $allproducts = new WP_Query($arguments);
	//echo "<pre>".$allproducts->request."</pre>";
	//echo "<pre>".print_r($arguments,1)."</pre>";
    $product_count = $allproducts->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);

    if ($pageNo) {
        $current_page_selecetd = $pageNo;
    } else {
        $current_page_selecetd = 1;
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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny   ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             
                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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

                if ($page == $current_page_selecetd) {
                    $activeClass = 'active';
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
        ?>
        <p><?= $offset; ?>-<?= $last_page_count; ?> of <?= $product_count; ?> products available</p> 
    </div>
    <?php
}

if ($_REQUEST['action'] == 'search_keyword') {

    global $wpdb;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';
    $limit = 12;
    $pageNo = $_REQUEST['page_no'];
    if ($_REQUEST['page_no']) {
        $newLimit = $limit - 1;
        $offset = ($pageNo * $limit) - $newLimit;
    } else {
        $offset = 0;
    }
    $keyword = $_REQUEST["keyword"];
    $wordExplode = explode(" ", $keyword);
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
    $category_name = $_REQUEST['category_name'];


    $current_language = ICL_LANGUAGE_CODE;



    if ($current_language == 'en') {
        $slug_name = $category_name;
    } else if ($current_language == 'fr') {
        $slug_name = $category_name . '-' . $current_language . "-2";
    } else {
        $slug_name = $category_name . '-' . $current_language;
    }

    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
    $checked_value = $_REQUEST['checked_value'];

    //$val = explode(",", $checked_value);
    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);
    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));
    if ($brand) {

        // $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }
    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }
    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }


    if ($price) {
        $val = explode("-", $price);
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
        $arguments = array(
            's' => $keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($location) {
        $arguments = array(
            's' => $keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
        $address = $location;
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {

            $arguments['order'] = 'ASC';
            $arguments['orderby'] = 'distance';

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



            $arguments['meta_query'] = array(
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
            $arguments['geo_query'] = array(
                'lat_field' => 'lat', // this is the name of the meta field storing latitude
                'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                'distance' => $range, // this is the maximum distance to search
                'units' => 'km'       // this supports options: miles, mi, kilometers, km
            );
        }
    } else {
        $arguments = array(
            's' => $keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    }



    $fetchAllProducts = new WP_Query($arguments);

    //  echo $search_query = 'SELECT ID FROM iewZNddPposts WHERE post_type = "product" AND post_status = "publish" AND  post_title LIKE "%' . $jpWordFirst . '%' . $jpWordSecond . '%' . $jpWordthird . '%' . $jpWordFourth . '%' . $jpWordFifth . '%' . $jpWordSixth . '%" ORDER BY ID DESC ';
    //$fetchAllProducts = new WP_Query(array('s' => $keyword, 'post_type' => 'product', 'post_status' => 'publish', 'orderby' => 'ID', 'order' => 'DESC', 'posts_per_page' => $limit, 'offset' => $offset));
    $product_count = $fetchAllProducts->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);
//    $fetchAllProducts = $wpdb->get_results("SELECT $post_table.ID,$post_table.post_title,$post_table.guid FROM $post_table WHERE $post_table.post_type='product' AND $post_table.post_status='publish' AND ($post_table.post_title LIKE '%$jpWordFirst%$jpWordSecond%$jpWordthird%$jpWordFourth%$jpWordFifth%$jpWordSixth%' OR $post_table.post_title LIKE '%$jpWordFirstReverse%$jpWordSecondReverse%$jpWordThirdReverse%$jpWordFourthReverse%$jpWordFifthReverse%$jpWordSixthReverse%') ORDER BY $post_table.ID DESC LIMIT 12");
    foreach ($fetchAllProducts->posts as $fetchAllProduct):

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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny   ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
    <?php endforeach;
    ?>
    <?php
    if ($pageNo) {
        $current_page_selecetd = $pageNo;
    } else {
        $current_page_selecetd = 1;
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
    ?>



    <div class="col-sm-12 col-md-12 text-center">
        <ul class="pagination" id="mypagies"> 
        <!--    <li class="disabled"><a class="lp1" href="#"><i class="fa fa-angle-left"></i></a></li>-->
            <?php
            //echo $endPage;
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

                if ($page == $current_page_selecetd || $page == '1') {
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
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>
    </div>
    <?php
}

if ($_REQUEST['action'] == 'fetchCategories') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';


    $category_name = $_REQUEST['category'];
    $categoryName = $wpdb->get_var("SELECT slug FROM $termsTable WHERE term_id ='$category_name' ");

    $limit = 12;

    if ($_REQUEST['page_no']) {
        $newLimit = $limit - 1;
        $offset = ($pageNo * $limit) - $newLimit;
    } else {
        $offset = 0;
    }




    $term_id = $category_name;
    $pageargs = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'offset' => $offset,
        'posts_per_page' => $limit,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                'terms' => $term_id,
            )
        )
    );

    if ($_REQUEST['location']) {
        $address = $_REQUEST['location'];
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyAXz4tTNJ8PtN8unz_PcQzSynqWLFFm-7M');
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


        $fetchAllProducts = new WP_Query($pageargs);
    } else {
        $fetchAllProducts = new WP_Query($pageargs);
    }
    $product_count = $fetchAllProducts->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);

    foreach ($fetchAllProducts->posts as $fetchAllProduct):

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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny   ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
    <?php
    $current_page_selecetd = $_REQUEST['page_no'];
    $startPage = $current_page_selecetd - 4;
    $endPage = $current_page_selecetd + 4;
    if ($startPage <= 0) {
        $endPage -= ($startPage - 1);
        $startPage = 1;
    }
    if ($endPage > $total_no_of_pages) {
        $endPage = $total_no_of_pages;
    }
    ?>
    <div class="col-sm-12 col-md-12 text-center">
        <ul class="pagination" id="mypagies"> 
            <?php
            if ($current_page_selecetd == '1' || $current_page_selecetd == '') {
                $page_class = 'disabled';
            } else {
                $page_class = '';
            }
            ?>
            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData('1', '<?= $categoryName; ?>')"><i class="fa fa-angle-left"></i><i class="fa fa-angle-left"></i></a></li>
            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData(<?= $current_page_selecetd - 1; ?>, '<?= $categoryName; ?>')"><i class="fa fa-angle-left"></i></a></li>
            <?php
            for ($page = $startPage; $page <= $endPage; $page++) {

                if ($page == $current_page_selecetd || $page == '1') {
                    $activeClass = 'disabled';
                } else {
                    $activeClass = '';
                }
                ?>
                <li class="<?= $activeClass; ?>"><a href="javascript:void(0)" onclick="paginationData(<?= $page; ?>, '<?= $categoryName; ?>')"><?= $page; ?><span class="sr-only">(current)</span></a></li>
            <?php } ?>

            <li><a class="lp2" href="javascript:void(0)" onclick="paginationData(<?= $current_page_selecetd + 1; ?>, '<?= $categoryName; ?>')"><i class="fa fa-angle-right"></i></a></li>
            <li><a class="lp2" href="javascript:void(0)" onclick="paginationData(<?= $total_no_of_pages; ?>, '<?= $categoryName; ?>')"><i class="fa fa-angle-right"></i><i class="fa fa-angle-right"></i></a></li>
        </ul>
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>
    </div>
    <div class="right_side_count"><?= $product_count; ?> Products Found!</div>
    <?php
}

if ($_REQUEST['action'] == 'fetchSubCategories') {

    $category_name = $_REQUEST['category'];
    $subcategories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $category_name);
    ?>
    <ul class="myBoards">
        <?php
        foreach ($subcategories as $fetchSubCategory):
            ?>

            <li class=""><a href="javascript:void(0)" onclick="showChildCategories(<?= $fetchSubCategory->term_id; ?>)"><?= $fetchSubCategory->name; ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php
}

if ($_REQUEST['action'] == 'showChildCategories') {
    $category_selected = $_REQUEST['category'];
    $category_explode = explode("-", $_REQUEST['category']);
    $count_category = count($category_explode);


    $category_id = $_REQUEST['category_id'];

    $subcategories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $category_id);
    ?>
    <ul class="myBoards">
        <?php
        foreach ($subcategories as $fetchSubCategory):
            ?>

            <li class=""><a href="javascript:void(0)" onclick="showSubChildCategories(<?= $fetchSubCategory->term_id; ?>, '<?= $fetchSubCategory->slug; ?>')"><?= $fetchSubCategory->name; ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php
}

if ($_REQUEST['action'] == 'showSubChildCategories') {

    $category_selected = $_REQUEST['category'];
    $category_explode = explode("-", $_REQUEST['category']);
    $count_category = count($category_explode);



    $category_id = $_REQUEST['category_id'];

    $subcategories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $category_id);
    ?>
    <ul class="myBoards">
        <?php
        foreach ($subcategories as $fetchSubCategory):
            ?>

            <li class=""><a href="javascript:void(0)" onclick="showLastChildCategories(<?= $fetchSubCategory->term_id; ?>, '<?= $fetchSubCategory->slug; ?>')"><?= $fetchSubCategory->name; ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php
}

if ($_REQUEST['action'] == 'filterPriceRange') {
    $amount = $_REQUEST['amount'];
    $explodeAmount = explode("-", $amount);
    $min_amount = $explodeAmount[0];
    $max_amount = $explodeAmount[1];

    global $wpdb;
    global $wp_query;
    global $post;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];


    $limit = 12;
    $pageNo = $_REQUEST['page_no'];
    if ($_REQUEST['page_no']) {
        $newLimit = $limit - 1;
        $offset = ($pageNo * $limit) - $newLimit;
    } else {
        $offset = 0;
    }

    $newLimit = $limit - 1;
    $offset = ($pageNo * $limit) - $newLimit;


//    if ($current_language == 'en') {
//        $slug_name = $category_name;
//    } else if ($current_language == 'fr') {
//        $slug_name = $category_name . '-' . $current_language . "-2";
//    } else {
//        $slug_name = $category_name . '-' . $current_language;
//    }
//
//    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
//
//
//    $price_range_arguments = array(
//        'post_type' => 'product',
//        'post_status' => 'publish',
//        'offset' => $offset,
//        'posts_per_page' => $limit,
//        'tax_query' => array(
//            array(
//                'taxonomy' => 'product_cat',
//                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
//                'terms' => $term_id,
//            )
//        ),
//        'meta_query' => array(
//            array(
//                'key' => '_price',
//                'value' => array($min_amount, $max_amount),
//                'type' => 'numeric',
//                'compare' => 'BETWEEN'
//            )
//        )
//    );
    if ($current_language == 'en') {
        $slug_name = $category_name;
    } else if ($current_language == 'fr') {
        $slug_name = $category_name . '-' . $current_language . "-2";
    } else {
        $slug_name = $category_name . '-' . $current_language;
    }

    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
    $checked_value = $_REQUEST['checked_value'];

    //$val = explode(",", $checked_value);
    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);
    if (!empty($brand) && !empty($pa_year) && !empty($pa_seller) && !empty($condition) && !empty($pa_surface) && !empty($pa_boom_size) && !empty($pa_carbon_number) && !empty($pa_mast_size) && !empty($pa_volume) && !empty($pa_length) && !empty($pa_size) && !empty($pa_size_number) && !empty($pa_warranty) && !empty($pa_damage) && !empty($pa_repair)) {
        $tax_query = array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
        );
    } else {
        $tax_query = array('relation' => 'AND', array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                'terms' => $term_id,
        ));
    }


    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        //  $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);
        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($condition) {
        $val = explode(",", $condition);
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $val = explode(",", $pa_surface);
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $val = explode(",", $pa_boom_size);
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $val = explode(",", $pa_carbon_number);
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $val = explode(",", $pa_mast_size);
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $val = explode(",", $pa_volume);
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_length) {
        $val = explode(",", $pa_length);
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_size) {
        $val = explode(",", $pa_size);
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $val = explode(",", $pa_size_number);
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $val = explode(",", $pa_warranty);
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $val = explode(",", $pa_damage);
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $val = explode(",", $pa_repair);
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($price) {
        $val = explode("-", $price);
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
        if($location){
			$arguments = array(
                's' => $search_keyword,
				'post_type' => 'product',
                'post_status' => 'publish',
                'offset' => $offset,
                'posts_per_page' => $limit,
                'tax_query' => array(
                    $tax_query
                ),
                'meta_query' => array(
                    $meta_query
                ) // This query will be overwritten below in the location
            );
            $address = $location;
            $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
            $geo = json_decode($geo, true);
            if ($geo['status'] = 'OK') {

                $arguments['order'] = 'ASC';
                $arguments['orderby'] = 'distance';

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



                $arguments['meta_query'] = array(
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
                $arguments['geo_query'] = array(
                    'lat_field' => 'lat', // this is the name of the meta field storing latitude
                    'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                    'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                    'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                    'distance' => $range, // this is the maximum distance to search
                    'units' => 'km'       // this supports options: miles, mi, kilometers, km
                );
            }			
		} else if ($search_keyword) {
            $arguments = array(
                's' => $search_keyword,
                'post_type' => 'product',
                'post_status' => 'publish',
                //'offset' => $offset,
                'posts_per_page' => $limit,
                'tax_query' => array(
                    $tax_query
                ),
                'meta_query' => array(
                    $meta_query
                )
            );
        } else {
            $arguments = array(
                'post_type' => 'product',
                'post_status' => 'publish',
//        'offset' => $offset,
                'posts_per_page' => $limit,
                'tax_query' => array(
                    $tax_query
                ),
                'meta_query' => array(
                    $meta_query
                )
            );
        }
    } else {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
//        'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            ),
        );
    }


    $product_price = new WP_Query($arguments);

    $product_count = $product_price->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);

    foreach ($product_price->posts as $fetchAllProduct):
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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny   ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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

    <?php
    if ($pageNo) {
        $current_page_selecetd = $pageNo;
    } else {
        $current_page_selecetd = 1;
    }
    $startPage = $current_page_selecetd - 6;
    $endPage = $current_page_selecetd + 6;
    if ($startPage <= 0) {
        $endPage -= ($startPage - 1);
        $startPage = 1;
    }
    if ($endPage > $total_no_of_pages) {
        $endPage = $total_no_of_pages;
    }
    ?>

    <div class="col-sm-12 col-md-12 text-center">
        <ul class="pagination" id="mypagies"> 
        <!-- <li class="disabled"><a class="lp1" href="#"><i class="fa fa-angle-left"></i></a></li>-->
            <?php
            if ($current_page_selecetd == '1' || $current_page_selecetd == '') {
                $page_class = 'disabled';
            } else {
                $page_class = '';
            }
            ?>
            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData('1', '<?= $categoryName; ?>')"><i class="fa fa-angle-left"></i><i class="fa fa-angle-left"></i></a></li>
            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData(<?= $current_page_selecetd - 1; ?>, '<?= $categoryName; ?>')"><i class="fa fa-angle-left"></i></a></li>
            <?php
            for ($page = $startPage; $page <= $endPage; $page++) {

                if ($page == $current_page_selecetd || $page == '1') {
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
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>
    </div>
    <?php
}

if ($_REQUEST['action'] == 'filterPagePriceRange') {
    global $wpdb;
    global $wp_query;
    global $post;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];
    $min_amount = $_REQUEST['min_amount'];
    $max_amount = $_REQUEST['max_amount'];
    $limit = 12;

    if ($_REQUEST['page']) {
        $pageNo = $_REQUEST['page'];
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
    $checked_value = $_REQUEST['checked_value'];

    //$val = explode(",", $checked_value);
    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);
    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));


    if ($brand) {

        //  $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($condition) {
        $val = explode(",", $condition);
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }
    if ($price) {
        $val = explode("-", $price);
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
        $arguments = array(
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
    } else {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    }
    $product_price = new WP_Query($arguments);

    $product_count = $product_price->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);

    foreach ($product_price->posts as $fetchAllProduct):
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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny    ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
            <!--<li class="disabled"><a class="lp1" href="#"><i class="fa fa-angle-left"></i></a></li>-->
            <?php
            if ($current_page_selecetd == '1' || $current_page_selecetd == '') {
                $page_class = 'disabled';
            } else {
                $page_class = '';
            }
            ?>
            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData('1', '<?= $categoryName; ?>')"><i class="fa fa-angle-left"></i><i class="fa fa-angle-left"></i></a></li>
            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData(<?= $current_page_selecetd - 1; ?>, '<?= $categoryName; ?>')"><i class="fa fa-angle-left"></i></a></li>
            <?php
            for ($page = 1; $page <= $total_no_of_pages; $page++) {
                if ($page == $pageNo) {
                    $activeClass = 'active';
                } else {
                    $activeClass = '';
                }
                ?>
                <li class="<?= $activeClass; ?>"><a href="javascript:void(0)" onclick="productPriceFilter(<?= $page; ?>, '<?= $category_name; ?>', '<?= $min_amount ?>', '<?= $max_amount; ?>')"><?= $page; ?><span class="sr-only">(current)</span></a></li>
            <?php } ?>

            <li><a class="lp2" href="#"><i class="fa fa-angle-right"></i></a></li>
        </ul>
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>
    </div>
    <?php
}

if ($_REQUEST['action'] == 'sellerFilter') {

    global $wpdb;
    global $wp_query;
    global $post;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    $limit = 12;
    $pageNo = $_REQUEST['page_no'];




    if ($current_language == 'en') {
        $slug_name = $category_name;
    } else if ($current_language == 'fr') {
        $slug_name = $category_name . '-' . $current_language . "-2";
    } else {
        $slug_name = $category_name . '-' . $current_language;
    }

    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
    $checked_value = $_REQUEST['checked_value'];

    //$val = explode(",", $checked_value);
    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);
    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This sis optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));
    if ($pageNo) {
        $newLimit = $limit - 1;
        $offset = ($pageNo * $limit) - $newLimit;
    } else {
        $offset = 0;
    }
    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        // $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }

    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }


    if ($price) {
        $val = explode("-", $price);
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
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if($location){
		$arguments = array(
			's' => $search_keyword,
			'post_type' => 'product',
			'post_status' => 'publish',
			'offset' => $offset,
			'posts_per_page' => $limit,
			'tax_query' => array(
				$tax_query
			),
			'meta_query' => array(
				$meta_query
			) // This query will be overwritten below in the location
		);
		$address = $location;
		$geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
		$geo = json_decode($geo, true);
		if ($geo['status'] = 'OK') {

			$arguments['order'] = 'ASC';
			$arguments['orderby'] = 'distance';

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



			$arguments['meta_query'] = array(
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
			$arguments['geo_query'] = array(
				'lat_field' => 'lat', // this is the name of the meta field storing latitude
				'lng_field' => 'lng', // this is the name of the meta field storing longitude 
				'latitude' => $latitude, // this is the latitude of the point we are getting distance from
				'longitude' => $longitude, // this is the longitude of the point we are getting distance from
				'distance' => $range, // this is the maximum distance to search
				'units' => 'km'       // this supports options: miles, mi, kilometers, km
			);
		}			
	} else if ($search_keyword) {
        $arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    }
    $product_seller = new WP_Query($arguments);
    $product_count = $product_seller->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);
    foreach ($product_seller->posts as $fetchAllProduct):

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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny    ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
    <?php
    if ($pageNo) {
        $current_page_selecetd = $pageNo;
    } else {
        $current_page_selecetd = 1;
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
    ?>

    <div class="col-sm-12 col-md-12 text-center">
        <ul class="pagination" id="mypagies"> 
           <!-- <li class="disabled"><a class="lp1" href="#"><i class="fa fa-angle-left"></i></a></li>-->
            <?php
            if ($current_page_selecetd == '1' || $current_page_selecetd == '') {
                $page_class = 'disabled';
            } else {
                $page_class = '';
            }
            ?>
            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData('1', '<?= $categoryName; ?>')"><i class="fa fa-angle-left"></i><i class="fa fa-angle-left"></i></a></li>
            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData(<?= $current_page_selecetd - 1; ?>, '<?= $categoryName; ?>')"><i class="fa fa-angle-left"></i></a></li>
            <?php
            for ($page = $startPage; $page <= $endPage; $page++) {

                if ($page == $current_page_selecetd || $page == '1') {
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
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>
    </div>
    <?php
}
if ($_REQUEST['action'] == 'brandFilter') {

    global $wpdb;
    global $wp_query;
    global $post;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    $limit = 12;






    if ($current_language == 'en') {
        $slug_name = $category_name;
    } else if ($current_language == 'fr') {
        $slug_name = $category_name . '-' . $current_language . "-2";
    } else {
        $slug_name = $category_name . '-' . $current_language;
    }

    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
    $checked_value = $_REQUEST['checked_value'];

    //$val = explode(",", $checked_value);
    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);
    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));
    if ($pageNo) {
        $pageNo = $pageNo;
        $newLimit = $limit - 1;
        $offset = ($pageNo * $limit) - $newLimit;
    } else {
        $offset = 0;
    }
    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        //$val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {

        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }

    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }


    if ($price) {
        $val = explode("-", $price);
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
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if($location){
		$arguments = array(
			's' => $search_keyword,
			'post_type' => 'product',
			'post_status' => 'publish',
			'offset' => $offset,
			'posts_per_page' => $limit,
			'tax_query' => array(
				$tax_query
			),
			'meta_query' => array(
				$meta_query
			) // This query will be overwritten below in the location
		);
		$address = $location;
		$geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
		$geo = json_decode($geo, true);
		if ($geo['status'] = 'OK') {

			$arguments['order'] = 'ASC';
			$arguments['orderby'] = 'distance';

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



			$arguments['meta_query'] = array(
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
			$arguments['geo_query'] = array(
				'lat_field' => 'lat', // this is the name of the meta field storing latitude
				'lng_field' => 'lng', // this is the name of the meta field storing longitude 
				'latitude' => $latitude, // this is the latitude of the point we are getting distance from
				'longitude' => $longitude, // this is the longitude of the point we are getting distance from
				'distance' => $range, // this is the maximum distance to search
				'units' => 'km'       // this supports options: miles, mi, kilometers, km
			);
		}			
	} else if ($search_keyword) {
        $arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    }



    $product_seller = new WP_Query($arguments);

    $product_count = $product_seller->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);
    foreach ($product_seller->posts as $fetchAllProduct):

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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny    ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
    <?php
    if ($pageNo) {
        $current_page_selecetd = $pageNo;
    } else {
        $current_page_selecetd = 1;
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
    ?>

    <div class="col-sm-12 col-md-12 text-center">
        <ul class="pagination" id="mypagies"> 

        <!--    <li class="disabled"><a class="lp1" href="#"><i class="fa fa-angle-left"></i></a></li>-->


            <?php
            //echo $endPage;
            if ($current_page_selecetd == '1' || $current_page_selecetd == '') {
                $page_class = 'disabled';
            } else if ($current_page_selecetd == $pageNo) {
                $page_class = 'disabled';
            } else {
                $page_class = '';
            }
            ?>

            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData('1', '<?= $category_name; ?>')"><i class="fa fa-angle-left"></i><i class="fa fa-angle-left"></i></a></li>


            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData(<?= $current_page_selecetd - 1; ?>, '<?= $category_name; ?>')"><i class="fa fa-angle-left"></i></a></li>
            <?php
            for ($page = $startPage; $page <= $endPage; $page++) {

                if ($page == $current_page_selecetd || $page == '1') {
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
            } else if ($current_page_selecetd == $pageNo) {
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
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>
    </div>
    <?php
}
if ($_REQUEST['action'] == 'yearFilter') {

    global $wpdb;
    global $wp_query;
    global $post;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    $limit = 12;




    if ($current_language == 'en') {
        $slug_name = $category_name;
    } else if ($current_language == 'fr') {
        $slug_name = $category_name . '-' . $current_language . "-2";
    } else {
        $slug_name = $category_name . '-' . $current_language;
    }

    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
    $checked_value = $_REQUEST['checked_value'];

    //$val = explode(",", $checked_value);
    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);
    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));

    if ($pageNo) {
        $newLimit = $limit - 1;
        $offset = ($pageNo * $limit) - $newLimit;
    } else {
        $offset = 0;
    }

    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        // $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($checked_value) {
        $val = explode(",", $checked_value);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'term_id', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }

    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }


    if ($price) {
        $val = explode("-", $price);
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
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if($location){
		$arguments = array(
			's' => $search_keyword,
			'post_type' => 'product',
			'post_status' => 'publish',
			'offset' => $offset,
			'posts_per_page' => $limit,
			'tax_query' => array(
				$tax_query
			),
			'meta_query' => array(
				$meta_query
			) // This query will be overwritten below in the location
		);
		$address = $location;
		$geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
		$geo = json_decode($geo, true);
		if ($geo['status'] = 'OK') {

			$arguments['order'] = 'ASC';
			$arguments['orderby'] = 'distance';

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



			$arguments['meta_query'] = array(
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
			$arguments['geo_query'] = array(
				'lat_field' => 'lat', // this is the name of the meta field storing latitude
				'lng_field' => 'lng', // this is the name of the meta field storing longitude 
				'latitude' => $latitude, // this is the latitude of the point we are getting distance from
				'longitude' => $longitude, // this is the longitude of the point we are getting distance from
				'distance' => $range, // this is the maximum distance to search
				'units' => 'km'       // this supports options: miles, mi, kilometers, km
			);
		}			
	} else if ($search_keyword) {
        $arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    }
    $product_seller = new WP_Query($arguments);

    $product_count = $product_seller->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);
    foreach ($product_seller->posts as $fetchAllProduct):

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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny    ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
    <?php
    if ($pageNo) {
        $current_page_selecetd = $pageNo;
    } else {
        $current_page_selecetd = 1;
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
    ?>

    <div class="col-sm-12 col-md-12 text-center">
        <ul class="pagination" id="mypagies"> 
          <!--  <li class="disabled"><a class="lp1" href="#"><i class="fa fa-angle-left"></i></a></li>-->

            <?php
            //echo $endPage;
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

                if ($page == $current_page_selecetd || $page == '1') {
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
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>

    </div>
    <?php
}
if ($_REQUEST['action'] == 'sizeFilter') {

    global $wpdb;
    global $wp_query;
    global $post;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    $limit = 12;





    if ($current_language == 'en') {
        $slug_name = $category_name;
    } else if ($current_language == 'fr') {
        $slug_name = $category_name . '-' . $current_language . "-2";
    } else {
        $slug_name = $category_name . '-' . $current_language;
    }

    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
    $checked_value = $_REQUEST['checked_value'];

    //$val = explode(",", $checked_value);
    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);

    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));

    if ($pageNo) {
        $newLimit = $limit - 1;
        $offset = ($pageNo * $limit) - $newLimit;
    } else {
        $offset = 0;
    }
    if ($brand) {

        // $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }

    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }




    if ($price) {

        $val = explode("-", $price);
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
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if($location){
		$arguments = array(
			's' => $search_keyword,
			'post_type' => 'product',
			'post_status' => 'publish',
			'offset' => $offset,
			'posts_per_page' => $limit,
			'tax_query' => array(
				$tax_query
			),
			'meta_query' => array(
				$meta_query
			) // This query will be overwritten below in the location
		);
		$address = $location;
		$geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
		$geo = json_decode($geo, true);
		if ($geo['status'] = 'OK') {

			$arguments['order'] = 'ASC';
			$arguments['orderby'] = 'distance';

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



			$arguments['meta_query'] = array(
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
			$arguments['geo_query'] = array(
				'lat_field' => 'lat', // this is the name of the meta field storing latitude
				'lng_field' => 'lng', // this is the name of the meta field storing longitude 
				'latitude' => $latitude, // this is the latitude of the point we are getting distance from
				'longitude' => $longitude, // this is the longitude of the point we are getting distance from
				'distance' => $range, // this is the maximum distance to search
				'units' => 'km'       // this supports options: miles, mi, kilometers, km
			);
		}			
	} else if ($search_keyword) {
        $arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else {

        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    }


//    $arguments = array(
//        'post_type' => 'product',
//        'post_status' => 'publish',
//        'offset' => $offset,
//        'posts_per_page' => $limit,
//        'tax_query' => array(
//            $tax_query
//        )
//    );
    $product_seller = new WP_Query($arguments);

    $product_count = $product_seller->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);
    foreach ($product_seller->posts as $fetchAllProduct):
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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny    ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
        <?php
        if ($pageNo) {
            $current_page_selecetd = $pageNo;
        } else {
            $current_page_selecetd = 1;
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
        ?>

        <ul class="pagination" id="mypagies"> 
          <!--  <li class="disabled"><a class="lp1" href="#"><i class="fa fa-angle-left"></i></a></li>-->

            <?php
            //echo $endPage;
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

                if ($page == $current_page_selecetd || $page == '1') {
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
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>
    </div>
    <?php
}
if ($_REQUEST['action'] == 'conditionsFilter') {

    global $wpdb;
    global $wp_query;
    global $post;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    $limit = 12;
    $pageNo = $_REQUEST['page_no'];



    if ($current_language == 'en') {
        $slug_name = $category_name;
    } else if ($current_language == 'fr') {
        $slug_name = $category_name . '-' . $current_language . "-2";
    } else {
        $slug_name = $category_name . '-' . $current_language;
    }

    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
    $checked_value = $_REQUEST['checked_value'];

    //$val = explode(",", $checked_value);
    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);
    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));
    if ($pageNo) {
        $newLimit = $limit - 1;
        $offset = ($pageNo * $limit) - $newLimit;
    } else {
        $offset = 0;
    }
    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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

    if ($brand) {

        //  $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }


    if ($price) {

        $val = explode("-", $price);
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
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if($location){
		$arguments = array(
			's' => $search_keyword,
			'post_type' => 'product',
			'post_status' => 'publish',
			'offset' => $offset,
			'posts_per_page' => $limit,
			'tax_query' => array(
				$tax_query
			),
			'meta_query' => array(
				$meta_query
			) // This query will be overwritten below in the location
		);
		$address = $location;
		$geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
		$geo = json_decode($geo, true);
		if ($geo['status'] = 'OK') {

			$arguments['order'] = 'ASC';
			$arguments['orderby'] = 'distance';

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



			$arguments['meta_query'] = array(
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
			$arguments['geo_query'] = array(
				'lat_field' => 'lat', // this is the name of the meta field storing latitude
				'lng_field' => 'lng', // this is the name of the meta field storing longitude 
				'latitude' => $latitude, // this is the latitude of the point we are getting distance from
				'longitude' => $longitude, // this is the longitude of the point we are getting distance from
				'distance' => $range, // this is the maximum distance to search
				'units' => 'km'       // this supports options: miles, mi, kilometers, km
			);
		}			
	} else if ($search_keyword) {
        $arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else if($location){
		$arguments = array(
			's' => $search_keyword,
			'post_type' => 'product',
			'post_status' => 'publish',
			'offset' => $offset,
			'posts_per_page' => $limit,
			'tax_query' => array(
				$tax_query
			),
			'meta_query' => array(
				$meta_query
			) // This query will be overwritten below in the location
		);
		$address = $location;
		$geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
		$geo = json_decode($geo, true);
		if ($geo['status'] = 'OK') {

			$arguments['order'] = 'ASC';
			$arguments['orderby'] = 'distance';

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



			$arguments['meta_query'] = array(
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
			$arguments['geo_query'] = array(
				'lat_field' => 'lat', // this is the name of the meta field storing latitude
				'lng_field' => 'lng', // this is the name of the meta field storing longitude 
				'latitude' => $latitude, // this is the latitude of the point we are getting distance from
				'longitude' => $longitude, // this is the longitude of the point we are getting distance from
				'distance' => $range, // this is the maximum distance to search
				'units' => 'km'       // this supports options: miles, mi, kilometers, km
			);
		}			
	} else if ($search_keyword) {
        $arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else  {

        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            //'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    }

    $product_seller = new WP_Query($arguments);

    $product_count = $product_seller->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);
    foreach ($product_seller->posts as $fetchAllProduct):
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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny    ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
    <?php
    if ($pageNo) {
        $current_page_selecetd = $pageNo;
    } else {
        $current_page_selecetd = 1;
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
    ?>

    <div class="col-sm-12 col-md-12 text-center">
        <ul class="pagination" id="mypagies"> 
           <!-- <li class="disabled"><a class="lp1" href="#"><i class="fa fa-angle-left"></i></a></li>-->

            <?php
            //echo $endPage;
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

                if ($page == $current_page_selecetd || $page == '1') {
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
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>
    </div>
    <?php
}

if ($_REQUEST['action'] == 'showBrands') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];


    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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

    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        // $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {

        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }

    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }


    if ($price) {
        $val = explode("-", $price);
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
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($location) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            )
        );
        $address = $location;
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {

            $args['order'] = 'ASC';
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
        }
    } else if ($search_keyword) {
        $args = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            )
        );
    }


//    $args = array(
//        'post_type' => 'product',
//        'post_status' => 'publish',
//        'posts_per_page' => -1,
//        'tax_query' => array(
//            $tax_query
//        )
//    );



    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), 'pa_brand', 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;

    foreach ($tax_terms['pa_brand'] as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single):
        ?>
        <?php
        $explodeBrands = explode(",", $brand);

        $brandSelected = array();
        $checkedArray = array();
        foreach ($explodeBrands as $explodeBrand):

            $brandSelected[] = $explodeBrand;
        endforeach;
        foreach ($brandSelected as $selectedBrands):
            if ($tax_terms_attribute_val_single == $selectedBrands) {


                $checked = 'checked';
                $checkedArray[] = $checked;
            } else {
                $checked = '';
                $checkedArray[] = $checked;
            }

        endforeach;

        if (in_array("checked", $checkedArray)) {
            $chekedArray = 'checked';
        } else {
            $chekedArray = "";
        }
        ?>
        <div class="col-sm-3 col-md-3 no-gutter">
            <label class="check "><?= $tax_terms_attribute_val_single ?> 
                <input type="checkbox" name="selected_brand[]" value="<?= $tax_terms_attribute_single_key; ?>" data-brand="<?= $tax_terms_attribute_val_single; ?>" <?= $chekedArray; ?>>
                <span class="checkmark"></span>
            </label>
        </div>
        <?php
    endforeach;
}
if ($_REQUEST['action'] == 'showYears') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];
    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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

    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        // $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($checked_value) {
        $val = explode(",", $checked_value);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'term_id', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_condition) {

        $val = explode(",", $pa_condition);
        $tax_condition_query = array('relation' => 'OR');
        foreach ($val as $sizeValue):

            $value[] = $sizeValue;
            $getTaxonomy = $wpdb->get_var("SELECT taxonomy FROM iewZNddPterm_taxonomy WHERE term_id =$sizeValue ");

            //foreach ($getTaxonomy as $getTaxonomies):
            $tax_condition_query[] = array(
                'taxonomy' => "$getTaxonomy",
                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                'terms' => $value,
                'operator' => 'IN'
            );
            // endforeach;
            // $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'term_id', 'terms' => $sizeValue, 'operator' => 'IN');
        endforeach;
    }

    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }


    if ($price) {
        $val = explode("-", $price);
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
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($search_keyword) {
        $arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else if ($location) {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            )
        );
        $address = $location;
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {

            $arguments['order'] = 'ASC';
            $arguments['orderby'] = 'distance';

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



            $arguments['meta_query'] = array(
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
            $arguments['geo_query'] = array(
                'lat_field' => 'lat', // this is the name of the meta field storing latitude
                'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                'distance' => $range, // this is the maximum distance to search
                'units' => 'km'       // this supports options: miles, mi, kilometers, km
            );
        }
    } else {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            )
        );
    }
    $allproducts = new WP_Query($arguments);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), 'pa_years', 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;

    foreach ($tax_terms['pa_years'] as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single):
        ?>
        <div class="col-sm-3 col-md-3 no-gutter">
            <label class="check "><?= $tax_terms_attribute_val_single ?> 
                <?php
                $explodeBrands = explode(",", $pa_year);
                $brandSelected = array();
                $checkedArray = array();
                foreach ($explodeBrands as $explodeBrand):

                    $brandSelected[] = $explodeBrand;
                endforeach;
                foreach ($brandSelected as $selectedBrands):
                    if ($tax_terms_attribute_val_single == $selectedBrands) {


                        $checked = 'checked';
                        $checkedArray[] = $checked;
                    } else {
                        $checked = '';
                        $checkedArray[] = $checked;
                    }

                endforeach;

                if (in_array("checked", $checkedArray)) {
                    $chekedArray = 'checked';
                } else {
                    $chekedArray = "";
                }
                ?>
                <input type="checkbox" name="selected_years[]" value="<?= $tax_terms_attribute_single_key; ?>" data-year="<?= $tax_terms_attribute_val_single; ?>" <?= $chekedArray; ?>>
                <span class="checkmark"></span>
            </label>
        </div>
        <?php
    endforeach;
}
if ($_REQUEST['action'] == 'showSizes') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];







    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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


    if ($brand) {

        //$val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }


    if ($price) {
        $val = explode("-", $price);
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
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            )
        );
    }
    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $name, 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;

    foreach ($tax_terms as $tax_terms_attribute_key => $tax_terms_attribute_val):

        if ($tax_terms_attribute_key == "pa_blade-size-cm²" || $tax_terms_attribute_key == "pa_blade-sizein²" || $tax_terms_attribute_key == "pa_carbon-number" || $tax_terms_attribute_key == "pa_length-cm" || $tax_terms_attribute_key == "pa_length-feet" || $tax_terms_attribute_key == "pa_size-number" || $tax_terms_attribute_key == "pa_size-xssmlxlxxl" || $tax_terms_attribute_key == "pa_surface-m²" || $tax_terms_attribute_key == "pa_thickness-mm" || $tax_terms_attribute_key == "pa_volume-liters" || $tax_terms_attribute_key == "pa_width-cm" || $tax_terms_attribute_key == "pa_width-inches" || $tax_terms_attribute_key == "pa_boom-size-cm" || $tax_terms_attribute_key == "pa_kitebars-size-m" || $tax_terms_attribute_key == "pa_length-cm" || $tax_terms_attribute_key == "pa_mast-size-cm") {
            echo '<h4>' . wc_attribute_label($tax_terms_attribute_key) . '</h4><br>';
            foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) :
                if ($_REQUEST[$tax_terms_attribute_key] == $tax_terms_attribute_single_key) {
                    $pa_boom_check = "checked";
                } else {
                    $pa_boom_check = "";
                }
                ?>
                <div class="col-sm-3 col-md-3 no-gutter">
                    <label class="check "><?= $tax_terms_attribute_val_single; ?>

                        <?php
                        $dataSize = str_replace(".", "-", $tax_terms_attribute_val_single);
                        if ($tax_terms_attribute_key == 'pa_surface-m²') {
                            $tax_terms_name = 'pa_surface';
                        } else if ($tax_terms_attribute_key == 'pa_boom-size-cm') {
                            $tax_terms_name = 'pa_boom_size';
                        } else if ($tax_terms_attribute_key == 'pa_carbon-number') {
                            $tax_terms_name = 'pa_carbon_number';
                        } else if ($tax_terms_attribute_key == 'pa_mast-size-cm') {
                            $tax_terms_name = 'pa_mast_size';
                        } else if ($tax_terms_attribute_key == 'pa_volume-liters') {
                            $tax_terms_name = 'pa_volume';
                        } else if ($tax_terms_attribute_key == 'pa_size-xssmlxlxxl') {
                            $tax_terms_name = 'pa_size';
                        } else if ($tax_terms_attribute_key == 'pa_size-number') {
                            $tax_terms_name = 'pa_size_number';
                        } else if ($tax_terms_attribute_key == 'pa_blade-size-cm²') {
                            $tax_terms_name = "pa_blade_size";
                        } else if ($tax_terms_attribute_key == 'pa_blade-sizein²') {
                            $tax_terms_name = "pa_blade_size_in";
                        } else if ($tax_terms_attribute_key == 'pa_length-cm') {
                            $tax_terms_name = "pa_length_cm";
                        } else if ($tax_terms_attribute_key == 'pa_length-feet') {
                            $tax_terms_name = "pa_length_feet";
                        } else if ($tax_terms_attribute_key == 'pa_thickness-mm') {
                            $tax_terms_name = 'pa_thickness_mm';
                        } else if ($tax_terms_attribute_key == 'pa_width-cm') {
                            $tax_terms_name = "pa_width_cm";
                        } else if ($tax_terms_attribute_key == 'pa_width-inches') {
                            $tax_terms_name = "pa_width_inches";
                        } else if ($tax_terms_attribute_key == "pa_kitebars-size-m") {
                            $tax_terms_name = "pa_kitebars_size_m";
                        } else if ($tax_terms_attribute_key == '"pa_mast-size-cm') {
                            $tax_terms_name = "pa_mast_size_cm";
                        } else {
                            $tax_terms_name = '';
                        }

                        if ($pa_mast_size) {
                            if ($tax_terms_attribute_val_single == $pa_mast_size) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_carbon_number) {
                            if ($tax_terms_attribute_val_single == $pa_carbon_number) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_blade_size) {
                            if ($tax_terms_attribute_val_single == $pa_blade_size) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_surface) {
                            $newSurface = str_replace("-", ".", $pa_surface);

                            if ($tax_terms_attribute_val_single == $newSurface) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_boom_size) {
                            if ($tax_terms_attribute_val_single == $pa_boom_size) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_volume) {
                            if ($tax_terms_attribute_val_single == $pa_volume) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }

                        if ($pa_size) {
                            if ($tax_terms_attribute_val_single == $pa_size) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }

                        if ($pa_blade_size_in) {
                            if ($tax_terms_attribute_val_single == $pa_blade_size_in) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_size_number) {
                            if ($tax_terms_attribute_val_single == $pa_size_number) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_length_cm) {
                            if ($tax_terms_attribute_val_single == $pa_length_cm) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_length_feet) {
                            if ($tax_terms_attribute_val_single == $pa_length_feet) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_thickness_mm) {
                            if ($tax_terms_attribute_val_single == $pa_thickness_mm) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_width_cm) {
                            if ($tax_terms_attribute_val_single == $pa_width_cm) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_width_inches) {
                            if ($tax_terms_attribute_val_single == $pa_width_inches) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_kitebars_size_m) {
                            if ($tax_terms_attribute_val_single == $pa_kitebars_size_m) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_mast_size_cm) {
                            if ($tax_terms_attribute_val_single == $pa_mast_size_cm) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if (in_array("checked", $checkedArray)) {
                            $chekedArray = 'checked';
                        } else {
                            $chekedArray = "";
                        }
                        ?>

                        <input type="radio" name="<?= $tax_terms_attribute_key; ?>" value="<?= $dataSize; ?>" class="size_checkbox" data-size="<?= $dataSize; ?>" data-size-name="<?= $tax_terms_name; ?>" <?= $chekedArray; ?> onclick="applySizeFilter('<?= $category_name; ?>', '<?= $tax_terms_attribute_key; ?>')">
                        <span class="checkmark_new"></span>
                    </label>
                </div>
                <?php
            endforeach;
        }
    endforeach;
}

if ($_REQUEST['action'] == 'showConditions') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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

    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        //   $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);


        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }




    if ($price) {
        $val = explode("-", $price);
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
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($search_keyword) {
        $args = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            )
        );
    } else {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            )
        );
    }

//    $args = array(
//        'post_type' => 'product',
//        'post_status' => 'publish',
//        'posts_per_page' => 12,
//        'tax_query' => array(
//            $tax_query
//        )
//    );






    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $name, 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;
    ?>
    <div class="modal-body">
        <br>
        <div class="row">

            <div id="original_conditions">
                <?php foreach ($tax_terms as $tax_terms_attribute_key => $tax_terms_attribute_val) : ?>
                    <?php
                    if ($tax_terms_attribute_key == "pa_condition" || $tax_terms_attribute_key == "pa_warranty" || $tax_terms_attribute_key == "pa_damage" || $tax_terms_attribute_key == "pa_repair") {
                        echo '<h4>' . wc_attribute_label($tax_terms_attribute_key) . '</h4><br>';
                        foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) :
                            if ($_REQUEST[$tax_terms_attribute_key] == $tax_terms_attribute_single_key) {
                                $pa_boom_check = "checked";
                            } else {
                                $pa_boom_check = "";
                            }
                            $condition_name = str_replace(" ", "-", $tax_terms_attribute_val_single);
                            if ($tax_terms_attribute_key == 'pa_condition') {
                                $tax_term_attribute_name = 'condition';
                            } else {
                                $tax_term_attribute_name = $tax_terms_attribute_key;
                            }
                            ?>

                            <div class="col-sm-3 col-md-3 no-gutter">
                                <label class="container_new"><?= $tax_terms_attribute_val_single; ?>
                                    <?php
                                    $changeValue = str_replace(" ", "-", $tax_terms_attribute_val_single);
                                    $conditionSlug = strtolower($changeValue);


                                    $checkedArray = array();


                                    if ($pa_condition) {

                                        if ($conditionSlug == $pa_condition) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_warranty) {
                                        if ($conditionSlug == $pa_warranty) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_damage) {
                                        if ($conditionSlug == $pa_damage) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_repair) {
                                        if ($conditionSlug == $pa_repair) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }

                                    if (in_array("checked", $checkedArray)) {
                                        $chekedArray = 'checked';
                                    } else {
                                        $chekedArray = "";
                                    }
                                    ?>


                                    <input type="radio" name="<?= $tax_terms_attribute_key; ?>" value="<?= $conditionSlug; ?>" class="condition_checkbox"  data-condition="<?= $conditionSlug; ?>"  data-condition-name="<?= $tax_terms_attribute_key; ?>" <?= $chekedArray; ?> onclick="applyConditionsFilter('<?= $category_name; ?>', '<?= $tax_terms_attribute_key; ?>')">
                                    <span class="checkmark_new"></span>
                                </label>
                            </div>
                            <?php
                        endforeach;
                    }
                endforeach;
                ?>
            </div>
            <div id="ajax_load_more_conditions"></div>
            <div class="load_more_data_button col-md-12">
                <a href="javascript:void(0)" onclick="showConditions('<?= $category_name; ?>')" class="load_more_data"><button>Show All Conditions</button></a>
                <a href="javascript:void(0)" class="loading_more_data" style="display:none;">Loading...</a>
            </div>
        </div>

    </div>
    <div class="modal-footer">
        <div class="myfooter">
            <div class="col-xs-6 col-sm-6 col-md-6 text-left">
                <a href="javascript:void(0)" class="btn" onclick="resetButton('pa_condition', '<?= $category_name; ?>')">Reset</a>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <a href="javascript:void(0)" class="btn blue" onclick="applyConditionButton('<?= $category_name; ?>')">Apply</a>
            </div>
        </div>
    </div>
    <div id="ajax_load_more_conditions"></div>
    <?php
}

if ($_REQUEST['action'] == 'catalogModal') {
    global $wpdb;
    $term_table_name = $wpdb->prefix . 'terms';
    $category_selected = $_REQUEST['category'];

    $selected_term = $wpdb->get_var("SELECT term_id FROM $term_table_name WHERE slug='$category_selected' ");
    $explode_category = explode("-", $_REQUEST['category']);
    $count_category_words = count($explode_category);

    $all_categories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=0');
    ?>

    <div class="row">

        <div class="col-xs-6 col-sm-4 col-md-2 myboxss">
            <?php
            if ($all_categories[1]->slug == $category_selected) {
                $windsurf_class = 'active_catalog';
            } else {
                $windsurf_class = '';
            }
            ?>


            <a href="javascript:void(0)" class="fetch_products" onclick="fetchData(<?= $all_categories[1]->term_id; ?>, 'windsurf_cat', '<?= $all_categories[1]->slug; ?>')">
                <div class="mypores <?= $windsurf_class; ?>" id="<?= $all_categories[1]->slug; ?>">
                    <?php
                    $thumbnail_id = get_woocommerce_term_meta($all_categories[1]->term_id, 'thumbnail_id', true);
                    $image = wp_get_attachment_url($thumbnail_id);
                    ?>

                    <img src="<?php echo $image; ?>" alt="<?php echo $all_categories[1]->name; ?>">
                    <p><?= $all_categories[1]->name; ?></p>
                </div>
            </a>




        </div>
        <div class="col-xs-6 col-sm-4 col-md-2 myboxss">
            <?php
            if ($all_categories[2]->slug == $category_selected) {
                $kitesurf_class = 'active_catalog';
            } else {
                $kitesurf_class = '';
            }
            ?>

            <a href="javascript:void(0)" class="fetch_products" data-category = "<?= $all_categories[2]->term_id; ?>" data-cat="kitesurf_cat" onclick="fetchData(<?= $all_categories[2]->term_id; ?>, 'kitesurf_cat', '<?= $all_categories[2]->slug; ?>')">
                <div class="mypores <?= $kitesurf_class; ?>" id="<?= $all_categories[2]->slug; ?>">
                    <?php
                    $thumbnail_id = get_woocommerce_term_meta($all_categories[2]->term_id, 'thumbnail_id', true);
                    $image = wp_get_attachment_url($thumbnail_id);
                    ?>

                    <img src="<?php echo $image; ?>" alt="<?php echo $all_categories[2]->name; ?>">
                    <p><?= $all_categories[2]->name; ?></p>
                </div>
            </a>

        </div>
        <div class="col-xs-6 col-sm-4 col-md-2 myboxss">
            <?php
            if ($all_categories[3]->slug == $category_selected) {
                $sup_class = 'active_catalog';
            } else {
                $sup_class = '';
            }
            ?>

            <a href="javascript:void(0)" class="fetch_products " data-category = "<?= $all_categories[3]->term_id; ?>" data-cat="sup_cat" onclick="fetchData(<?= $all_categories[3]->term_id; ?>, 'sup_cat', '<?= $all_categories[3]->slug; ?>')">
                <div class="mypores <?= $sup_class; ?>" id="<?= $all_categories[3]->slug; ?>">
                    <?php
                    $thumbnail_id = get_woocommerce_term_meta($all_categories[3]->term_id, 'thumbnail_id', true);
                    $image = wp_get_attachment_url($thumbnail_id);
                    ?>

                    <img src="<?php echo $image; ?>" alt="<?php echo $all_categories[3]->name; ?>">
                    <p><?= $all_categories[3]->name; ?></p>
                </div>
            </a>

        </div>
        <div class="col-xs-6 col-sm-4 col-md-2 myboxss">
            <?php
            if ($all_categories[4]->slug == $category_selected) {
                $surf_class = 'active_catalog';
            } else {
                $surf_class = '';
            }
            ?>

            <a href="javascript:void(0)" class="fetch_products " data-category = "<?= $all_categories[4]->term_id; ?>" data-cat="surf_cat" onclick="fetchData(<?= $all_categories[4]->term_id; ?>, 'surf_cat', '<?= $all_categories[4]->slug; ?>')">
                <div class="mypores <?= $surf_class; ?>" id="<?= $all_categories[4]->slug; ?>">
                    <?php
                    $thumbnail_id = get_woocommerce_term_meta($all_categories[4]->term_id, 'thumbnail_id', true);
                    $image = wp_get_attachment_url($thumbnail_id);
                    ?>

                    <img src="<?php echo $image; ?>" alt="<?php echo $all_categories[4]->name; ?>">
                    <p><?= $all_categories[4]->name; ?></p>
                </div>
            </a>

        </div>
        <div class="col-xs-6 col-sm-4 col-md-2 myboxss">
            <?php
            if ($all_categories[5]->slug == $category_selected) {
                $surfwear_class = 'active_catalog';
            } else {
                $surfwear_class = '';
            }
            ?>

            <a href="javascript:void(0)" class="fetch_products " data-category = "<?= $all_categories[5]->term_id; ?>" data-cat="surfwear_cat" onclick="fetchData(<?= $all_categories[5]->term_id; ?>, 'surfwear_cat', '<?= $all_categories[5]->slug; ?>')">
                <div class="mypores <?= $surfwear_class; ?>" id="<?= $all_categories[5]->slug; ?>">
                    <?php
                    $thumbnail_id = get_woocommerce_term_meta($all_categories[5]->term_id, 'thumbnail_id', true);
                    $image = wp_get_attachment_url($thumbnail_id);
                    ?>

                    <img src="<?php echo $image; ?>" alt="<?php echo $all_categories[5]->name; ?>">
                    <p><?= $all_categories[5]->name; ?></p>
                </div>
            </a>

        </div>
        <div class="col-xs-6 col-sm-4 col-md-2 myboxss">
            <?php
            if ($all_categories[6]->slug == $category_selected) {
                $camera_class = 'active_catalog';
            } else {
                $camera_class = '';
            }
            ?>




            <a href="javascript:void(0)" class="fetch_products" id="fetch_product_<?= $all_categories[6]->slug; ?>" data-category = "<?= $all_categories[6]->term_id; ?>" data-cat="camera_cat" onclick="fetchData(<?= $all_categories[6]->term_id; ?>, 'camera_cat', '<?= $all_categories[6]->slug; ?>')">
                <div class="mypores <?= $camera_class; ?>" id="<?= $all_categories[6]->slug; ?>">
                    <?php
                    $thumbnail_id = get_woocommerce_term_meta($all_categories[6]->term_id, 'thumbnail_id', true);
                    $image = wp_get_attachment_url($thumbnail_id);
                    ?>

                    <img src="<?php echo $image; ?>" alt="<?php echo $all_categories[6]->name; ?>">
                    <p><?= $all_categories[6]->name; ?></p>
                </div>
            </a>

        </div>
    </div>
    <div class="row">
        <?php
        if ($count_category_words == '1' || $count_category_words == '2' || $count_category_words == '3') {
            $sub_cat_display = 'display:block;';
        } else {
            $sub_cat_display = 'display:none;';
        }
        ?>


        <div class="Boards" style="<?= $sub_cat_display; ?>">
            <div class="loader popups" style="display:none;" id="popup_loader">
                <img src="https://www.pedul.com/images/loading.gif" alt="loader"/>
            </div>


            <div id="windsurf_cat" style="display:none;">
                <?php
                $cat_id = $all_categories[1]->term_id;
                $subcategories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $cat_id);
                ?>
                <ul class="myBoards">
                    <?php
                    foreach ($subcategories as $fetchSubCategory):
                        ?>

                        <li class=""><a href="javascript:void(0)" onclick="showChildCategories(<?= $fetchSubCategory->term_id; ?>, '<?= $fetchSubCategory->slug; ?>')"><?= $fetchSubCategory->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>


            <div id="kitesurf_cat" style="display:none;">
                <?php
                $cat_id = $all_categories[2]->term_id;
                $subcategories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $cat_id);
                ?>
                <ul class="myBoards">
                    <?php
                    foreach ($subcategories as $fetchSubCategory):
                        ?>

                        <li class=""><a href="javascript:void(0)" onclick="showChildCategories(<?= $fetchSubCategory->term_id; ?>, '<?= $fetchSubCategory->slug; ?>')"><?= $fetchSubCategory->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>


            <div id="sup_cat" style="display:none;">
                <?php
                $cat_id = $all_categories[3]->term_id;
                $subcategories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $cat_id);
                ?>
                <ul class="myBoards">
                    <?php
                    foreach ($subcategories as $fetchSubCategory):
                        ?>

                        <li class=""><a href="javascript:void(0)" onclick="showChildCategories(<?= $fetchSubCategory->term_id; ?>, '<?= $fetchSubCategory->slug; ?>')"><?= $fetchSubCategory->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>


            <div id="surf_cat" style="display:none;">
                <?php
                $cat_id = $all_categories[4]->term_id;
                $subcategories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $cat_id);
                ?>
                <ul class="myBoards">
                    <?php
                    foreach ($subcategories as $fetchSubCategory):
                        ?>

                        <li class=""><a href="javascript:void(0)" onclick="showChildCategories(<?= $fetchSubCategory->term_id; ?>, '<?= $fetchSubCategory->slug; ?>')"><?= $fetchSubCategory->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>


            <div id="surfwear_cat" style="display:none;">
                <?php
                $cat_id = $all_categories[5]->term_id;


                $subcategories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $cat_id);
                ?>
                <ul class="myBoards">
                    <?php
                    foreach ($subcategories as $fetchSubCategory):
                        ?>
                        <li class=""><a href="javascript:void(0)" onclick="showChildCategories(<?= $fetchSubCategory->term_id; ?>, '<?= $fetchSubCategory->slug; ?>')"><?= $fetchSubCategory->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>


            <div id="camera_cat" style="display:none;">
                <?php
                $cat_id = $all_categories[6]->term_id;
                $subcategories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $cat_id);
                ?>
                <ul class="myBoards">
                    <?php
                    foreach ($subcategories as $fetchSubCategory):
                        ?>

                        <li class=""><a href="javascript:void(0)" onclick="showChildCategories(<?= $fetchSubCategory->term_id; ?>, '<?= $fetchSubCategory->slug; ?>')"><?= $fetchSubCategory->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="sub_cat_boards" style="display:none;">
            <?php
            $second_subcategories = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $selected_term);
            ?>
            <ul class="myBoards">
                <?php
                foreach ($second_subcategories as $fetchSecondSubCategory):
                    ?>

                    <li class=""><a href="javascript:void(0)" onclick="showSubChildCategories(<?= $fetchSecondSubCategory->term_id; ?>, '<?= $fetchSecondSubCategory->slug; ?>')"><?= $fetchSecondSubCategory->name; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>


        <div class="sub_child_cat_boards"></div>

    </div>
    <?php
}

if ($_REQUEST['action'] == 'showBrandModal') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];







    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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


//    if ($brand) {
//
//        $val = explode(",", $brand);
//        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
//    }
    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {

        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($brand) {

        //  $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }


    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }




    if ($price) {
        $val = explode("-", $price);
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
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($search_keyword) {
        $args = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else if ($location) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
        $address = $location;
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {
            $args1['order'] = 'ASC';
            $args['order'] = 'ASC';
            $args['orderby'] = 'distance';
            $args1['orderby'] = 'distance';
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
            $args1['geo_query'] = array(
                'lat_field' => 'lat', // this is the name of the meta field storing latitude
                'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                'distance' => $range, // this is the maximum distance to search
                'units' => 'km'       // this supports options: miles, mi, kilometers, km
            );
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
        }
    } else {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
    }


//    $args = array(
//        'post_type' => 'product',
//        'post_status' => 'publish',
//        'posts_per_page' => 12,
//        'tax_query' => array(
//            $tax_query
//        )
//    );
    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $name, 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;
    ?>





    <div class="modal-body">
        <br>
        <div class="row">

            <br>
            <div id="original_brands">
                <?php foreach ($tax_terms['pa_brand'] as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single): ?>
                    <div class="col-sm-3 col-md-3 no-gutter">
                        <label class="check "><?= $tax_terms_attribute_val_single ?>
                            <?php
                            $explodeBrands = explode(",", $brand);
                            $brandSelected = array();
                            $checkedArray = array();
                            foreach ($explodeBrands as $explodeBrand):

                                $brandSelected[] = $explodeBrand;
                            endforeach;
                            foreach ($brandSelected as $selectedBrands):

                                if ($tax_terms_attribute_val_single == $selectedBrands) {


                                    $checked = 'checked';
                                    $checkedArray[] = $checked;
                                } else {
                                    $checked = '';
                                    $checkedArray[] = $checked;
                                }

                            endforeach;

                            if (in_array("checked", $checkedArray)) {
                                $chekedArray = 'checked';
                            } else {
                                $chekedArray = "";
                            }
                            ?>
                            <input type="checkbox" name="selected_brand[]" value="<?= $tax_terms_attribute_single_key; ?>" class="seller_checkbox" data-brand="<?= $tax_terms_attribute_val_single; ?>" <?= $chekedArray; ?>>
                            <span class="checkmark"></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="ajax_load_more_brands"></div>
            <div class="load_more_data_button col-md-12">
                <a href="javascript:void(0)" onclick="showBrands('<?= $category_name; ?>')" class="load_more_data"><button>Show All Brands</button></a>
                <a href="javascript:void(0)" class="loading_more_data" style="display:none;">Loading...</a>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <div class="myfooter">
            <div class="col-xs-6 col-sm-6 col-md-6 text-left">
                <a href="javascript:void(0)" onclick="resetButton('brand', '<?= $category_name; ?>')" class="btn" id= "buton">Reset</a>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <a href="javascript:void(0)" class="btn blue" onclick="applyBrandFilter('<?= $category_name; ?>')">Apply</a>
            </div>
        </div>
    </div>
    <?php
}

if ($_REQUEST['action'] == 'showYearsModal') {

    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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
    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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

    if ($brand) {

        // $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_condition) {

        $val = explode(",", $pa_condition);
        $tax_condition_query = array('relation' => 'OR');
        foreach ($val as $sizeValue):

            $value[] = $sizeValue;
            $getTaxonomy = $wpdb->get_var("SELECT taxonomy FROM iewZNddPterm_taxonomy WHERE term_id =$sizeValue ");


            $tax_condition_query[] = array(
                'taxonomy' => "$getTaxonomy",
                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                'terms' => $value,
                'operator' => 'IN'
            );

        endforeach;
    }

    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }


    if ($price) {
        $val = explode("-", $price);
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
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($search_keyword) {
        $arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else if ($location) {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
        $address = $location;
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {

            $arguments['order'] = 'ASC';
            $arguments['orderby'] = 'distance';

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



            $arguments['meta_query'] = array(
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
            $arguments['geo_query'] = array(
                'lat_field' => 'lat', // this is the name of the meta field storing latitude
                'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                'distance' => $range, // this is the maximum distance to search
                'units' => 'km'       // this supports options: miles, mi, kilometers, km
            );
        }
    } else {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
    }


//    $arguments = array(
//        'post_type' => 'product',
//        'post_status' => 'publish',
//        'offset' => $offset,
//        'posts_per_page' => $limit,
//        'tax_query' => array(
//            $tax_query
//        )
//    );
    $allproducts = new WP_Query($arguments);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $name, 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;
    ?>
    <div class="modal-body">
        <br>
        <div class="row">

            <br>
            <div id="original_years">
                <?php
                foreach ($tax_terms['pa_years'] as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single):
                    ?>
                    <div class="col-sm-3 col-md-3 no-gutter">
                        <label class="check "><?= $tax_terms_attribute_val_single ?>
                            <?php
                            $explodeBrands = explode(",", $pa_year);
                            $brandSelected = array();
                            $checkedArray = array();
                            foreach ($explodeBrands as $explodeBrand):

                                $brandSelected[] = $explodeBrand;
                            endforeach;
                            foreach ($brandSelected as $selectedBrands):
                                if ($tax_terms_attribute_val_single == $selectedBrands) {


                                    $checked = 'checked';
                                    $checkedArray[] = $checked;
                                } else {
                                    $checked = '';
                                    $checkedArray[] = $checked;
                                }

                            endforeach;

                            if (in_array("checked", $checkedArray)) {
                                $chekedArray = 'checked';
                            } else {
                                $chekedArray = "";
                            }
                            ?>
                            <input type="checkbox" name="selected_years[]" data-year="<?= $tax_terms_attribute_val_single; ?>" value="<?= $tax_terms_attribute_single_key; ?>" <?= $chekedArray; ?> class="years_checkbox" />
                            <span class="checkmark"></span>
                        </label>
                    </div>
                <?php endforeach;
                ?>
            </div>
            <div id="ajax_load_more_years"></div>
            <div class="load_more_data_button col-md-12">
                <a href="javascript:void(0)" onclick="showYear('<?= $category_name; ?>')" class="load_more_data"><button>Show All Years</button></a>
                <a href="javascript:void(0)" class="loading_more_data" style="display:none;">Loading...</a>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <div class="myfooter">
            <div class="col-xs-6 col-sm-6 col-md-6 text-left">
                <a href="javascript:void(0)" onclick="resetButton('pa_year', '<?= $category_name; ?>')" class="btn">Reset</a>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <a href="javascript:void(0)" class="btn blue" onclick="applyYearFilter('<?= $category_name; ?>')">Apply</a>
            </div>
        </div>
    </div>
    <?php
}
if ($_REQUEST['action'] == 'showSizeModal') {

    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];







    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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

    $response = array();
    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        // $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }






    if ($price) {
        $val = explode("-", $price);
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
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($search_keyword) {
        $args = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
    } else {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
    }
    

    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $name, 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;
    ?>

    <div class="modal-body">
        <br>
        <div class="row">

            <br>
            <div id="original_size">

                <?php
                foreach ($tax_terms as $tax_terms_attribute_key => $tax_terms_attribute_val) :
                    ?>
                    <?php
                    if ($tax_terms_attribute_key == "pa_blade-size-cm²" || $tax_terms_attribute_key == "pa_blade-sizein²" || $tax_terms_attribute_key == "pa_carbon-number" || $tax_terms_attribute_key == "pa_length-cm" || $tax_terms_attribute_key == "pa_length-feet" || $tax_terms_attribute_key == "pa_size-number" || $tax_terms_attribute_key == "pa_size-xssmlxlxxl" || $tax_terms_attribute_key == "pa_surface-m²" || $tax_terms_attribute_key == "pa_thickness-mm" || $tax_terms_attribute_key == "pa_volume-liters" || $tax_terms_attribute_key == "pa_width-cm" || $tax_terms_attribute_key == "pa_width-inches" || $tax_terms_attribute_key == "pa_boom-size-cm" || $tax_terms_attribute_key == "pa_kitebars-size-m" || $tax_terms_attribute_key == "pa_length-cm" || $tax_terms_attribute_key == "pa_mast-size-cm") {
                        echo '<h4>' . wc_attribute_label($tax_terms_attribute_key) . '</h4><br>';
                        foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) :
                            ?>
                            <div class="col-sm-3 col-md-3 no-gutter">
                                <label class="container_new"><?= $tax_terms_attribute_val_single; ?>

                                    <?php
                                    $dataSize = str_replace(".", "-", $tax_terms_attribute_val_single);
                                    if ($tax_terms_attribute_key == 'pa_surface-m²') {
                                        $tax_terms_name = 'pa_surface';
                                    } else if ($tax_terms_attribute_key == 'pa_boom-size-cm') {
                                        $tax_terms_name = 'pa_boom_size';
                                    } else if ($tax_terms_attribute_key == 'pa_carbon-number') {
                                        $tax_terms_name = 'pa_carbon_number';
                                    } else if ($tax_terms_attribute_key == 'pa_mast-size-cm') {
                                        $tax_terms_name = 'pa_mast_size';
                                    } else if ($tax_terms_attribute_key == 'pa_volume-liters') {
                                        $tax_terms_name = 'pa_volume';
                                    } else if ($tax_terms_attribute_key == 'pa_size-xssmlxlxxl') {
                                        $tax_terms_name = 'pa_size';
                                    } else if ($tax_terms_attribute_key == 'pa_size-number') {
                                        $tax_terms_name = 'pa_size_number';
                                    } else if ($tax_terms_attribute_key == 'pa_blade-size-cm²') {
                                        $tax_terms_name = "pa_blade_size";
                                    } else if ($tax_terms_attribute_key == 'pa_blade-sizein²') {
                                        $tax_terms_name = "pa_blade_size_in";
                                    } else if ($tax_terms_attribute_key == 'pa_length-cm') {
                                        $tax_terms_name = "pa_length_cm";
                                    } else if ($tax_terms_attribute_key == 'pa_length-feet') {
                                        $tax_terms_name = "pa_length_feet";
                                    } else if ($tax_terms_attribute_key == 'pa_thickness-mm') {
                                        $tax_terms_name = 'pa_thickness_mm';
                                    } else if ($tax_terms_attribute_key == 'pa_width-cm') {
                                        $tax_terms_name = "pa_width_cm";
                                    } else if ($tax_terms_attribute_key == 'pa_width-inches') {
                                        $tax_terms_name = "pa_width_inches";
                                    } else if ($tax_terms_attribute_key == "pa_kitebars-size-m") {
                                        $tax_terms_name = "pa_kitebars_size_m";
                                    } else if ($tax_terms_attribute_key == '"pa_mast-size-cm') {
                                        $tax_terms_name = "pa_mast_size_cm";
                                    } else {
                                        $tax_terms_name = '';
                                    }



                                    if ($pa_mast_size) {
                                        if ($tax_terms_attribute_val_single == $pa_mast_size) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_carbon_number) {
                                        if ($tax_terms_attribute_val_single == $pa_carbon_number) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_blade_size) {
                                        if ($tax_terms_attribute_val_single == $pa_blade_size) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_surface) {
                                        if ($tax_terms_attribute_val_single == $pa_surface) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_boom_size) {
                                        if ($tax_terms_attribute_val_single == $pa_boom_size) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_volume) {
                                        if ($tax_terms_attribute_val_single == $pa_volume) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }

                                    if ($pa_size) {
                                        if ($tax_terms_attribute_val_single == $pa_size) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }

                                    if ($pa_blade_size_in) {
                                        if ($tax_terms_attribute_val_single == $pa_blade_size_in) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_size_number) {
                                        if ($tax_terms_attribute_val_single == $pa_size_number) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_length_cm) {
                                        if ($tax_terms_attribute_val_single == $pa_length_cm) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_length_feet) {
                                        if ($tax_terms_attribute_val_single == $pa_length_feet) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_thickness_mm) {
                                        if ($tax_terms_attribute_val_single == $pa_thickness_mm) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_width_cm) {
                                        if ($tax_terms_attribute_val_single == $pa_width_cm) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_width_inches) {
                                        if ($tax_terms_attribute_val_single == $pa_width_inches) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_kitebars_size_m) {
                                        if ($tax_terms_attribute_val_single == $pa_kitebars_size_m) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_mast_size_cm) {
                                        if ($tax_terms_attribute_val_single == $pa_mast_size_cm) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if (in_array("checked", $checkedArray)) {
                                        $chekedArray = 'checked';
                                    } else {
                                        $chekedArray = "";
                                    }
                                    ?>

                                    <input type="radio" name="<?= $tax_terms_attribute_key; ?>" value="<?= $dataSize; ?>" class="size_checkbox" data-size="<?= $dataSize; ?>" data-size-name="<?= $tax_terms_name; ?>" <?= $chekedArray; ?> onclick="applySizeFilter('<?= $category_name; ?>', '<?= $tax_terms_attribute_key; ?>')">
                                    <span class="checkmark_new"></span>
                                </label>
                            </div>
                            <?php
                        endforeach;
                    }
                endforeach;
                ?>
            </div>
            <div id="ajax_load_more_size"></div>
            <div id="ajax_new_load_more_size"></div>

            <div class="load_more_data_button col-md-12">
                <a href="javascript:void(0)" onclick="showSize('<?= $category_name; ?>')" class="load_more_data"><button>Show All Size</button></a>
                <a href="javascript:void(0)" class="loading_more_data" style="display:none;">Loading...</a>
            </div>

        </div>
    </div>

    <div class="modal-footer">
        <div class="myfooter">
            <div class="col-xs-6 col-sm-6 col-md-6 text-left">
                <a href="javascript:void(0)" class="btn" onclick="resetSizeButton('<?= $category_name; ?>')">Reset</a>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <a href="javascript:void(0)" class="btn blue" onclick="applySizeButton('<?= $category_name; ?>')">Apply</a>
            </div>
        </div>
    </div>
    <?php
}
if ($_REQUEST['action'] == 'showAjaxSizeModal') {

    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];







    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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


    if ($brand) {

        // $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }

    if ($pa_size) {

        $val = explode(",", $pa_size);
        $tax_size_query = array('relation' => 'AND');
        foreach ($val as $sizeValue):

            $value[] = $sizeValue;
            $getTaxonomy = $wpdb->get_var("SELECT taxonomy FROM iewZNddPterm_taxonomy WHERE term_id =$sizeValue ");

            //foreach ($getTaxonomy as $getTaxonomies):
            $tax_size_query[] = array(
                'taxonomy' => "$getTaxonomy",
                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                'terms' => $value,
                'operator' => 'IN'
            );
            // endforeach;
            // $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'term_id', 'terms' => $sizeValue, 'operator' => 'IN');
        endforeach;
    }


    if ($price) {
        $val = explode("-", $price);
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
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
    }


    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $name, 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;
    ?>

    <div class="modal-body">
        <br>
        <div class="row">

            <br>


            <?php
            foreach ($tax_terms as $tax_terms_attribute_key => $tax_terms_attribute_val) :
                ?>
                <?php
                if ($tax_terms_attribute_key == "pa_blade-size-cm²" || $tax_terms_attribute_key == "pa_blade-sizein²" || $tax_terms_attribute_key == "pa_carbon-number" || $tax_terms_attribute_key == "pa_length-cm" || $tax_terms_attribute_key == "pa_length-feet" || $tax_terms_attribute_key == "pa_size-number" || $tax_terms_attribute_key == "pa_size-xssmlxlxxl" || $tax_terms_attribute_key == "pa_surface-m²" || $tax_terms_attribute_key == "pa_thickness-mm" || $tax_terms_attribute_key == "pa_volume-liters" || $tax_terms_attribute_key == "pa_width-cm" || $tax_terms_attribute_key == "pa_width-inches" || $tax_terms_attribute_key == "pa_boom-size-cm" || $tax_terms_attribute_key == "pa_kitebars-size-m" || $tax_terms_attribute_key == "pa_length-cm" || $tax_terms_attribute_key == "pa_mast-size-cm") {
                    echo '<h4>' . wc_attribute_label($tax_terms_attribute_key) . '</h4><br>';
                    foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) :
                        if ($_REQUEST[$tax_terms_attribute_key] == $tax_terms_attribute_single_key) {
                            $pa_boom_check = "checked";
                        } else {
                            $pa_boom_check = "";
                        }
                        ?>
                        <div class="col-sm-3 col-md-3 no-gutter">
                            <label class="container_new"><?= $tax_terms_attribute_val_single; ?>

                                <?php
                                $dataSize = str_replace(".", "-", $tax_terms_attribute_val_single);
                                if ($tax_terms_attribute_key == 'pa_surface-m²') {
                                    $tax_terms_name = 'pa_surface';
                                } else if ($tax_terms_attribute_key == 'pa_boom-size-cm') {
                                    $tax_terms_name = 'pa_boom_size';
                                } else if ($tax_terms_attribute_key == 'pa_carbon-number') {
                                    $tax_terms_name = 'pa_carbon_number';
                                } else if ($tax_terms_attribute_key == 'pa_mast-size-cm') {
                                    $tax_terms_name = 'pa_mast_size';
                                } else if ($tax_terms_attribute_key == 'pa_volume-liters') {
                                    $tax_terms_name = 'pa_volume';
                                } else if ($tax_terms_attribute_key == 'pa_size-xssmlxlxxl') {
                                    $tax_terms_name = 'pa_size';
                                } else if ($tax_terms_attribute_key == 'pa_size-number') {
                                    $tax_terms_name = 'pa_size_number';
                                } else if ($tax_terms_attribute_key == 'pa_blade-size-cm²') {
                                    $tax_terms_name = "pa_blade_size";
                                } else if ($tax_terms_attribute_key == 'pa_blade-sizein²') {
                                    $tax_terms_name = "pa_blade_size_in";
                                } else if ($tax_terms_attribute_key == 'pa_length-cm') {
                                    $tax_terms_name = "pa_length_cm";
                                } else if ($tax_terms_attribute_key == 'pa_length-feet') {
                                    $tax_terms_name = "pa_length_feet";
                                } else if ($tax_terms_attribute_key == 'pa_thickness-mm') {
                                    $tax_terms_name = 'pa_thickness_mm';
                                } else if ($tax_terms_attribute_key == 'pa_width-cm') {
                                    $tax_terms_name = "pa_width_cm";
                                } else if ($tax_terms_attribute_key == 'pa_width-inches') {
                                    $tax_terms_name = "pa_width_inches";
                                } else if ($tax_terms_attribute_key == "pa_kitebars-size-m") {
                                    $tax_terms_name = "pa_kitebars_size_m";
                                } else if ($tax_terms_attribute_key == '"pa_mast-size-cm') {
                                    $tax_terms_name = "pa_mast_size_cm";
                                } else {
                                    $tax_terms_name = '';
                                }
                                if ($pa_mast_size) {
                                    if ($tax_terms_attribute_val_single == $pa_mast_size) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_carbon_number) {
                                    if ($tax_terms_attribute_val_single == $pa_carbon_number) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_blade_size) {
                                    if ($tax_terms_attribute_val_single == $pa_blade_size) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_surface) {
                                    if ($tax_terms_attribute_val_single == $pa_surface) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_boom_size) {
                                    if ($tax_terms_attribute_val_single == $pa_boom_size) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_volume) {
                                    if ($tax_terms_attribute_val_single == $pa_volume) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }

                                if ($pa_size) {
                                    if ($tax_terms_attribute_val_single == $pa_size) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }

                                if ($pa_blade_size_in) {
                                    if ($tax_terms_attribute_val_single == $pa_blade_size_in) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_size_number) {
                                    if ($tax_terms_attribute_val_single == $pa_size_number) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_length_cm) {
                                    if ($tax_terms_attribute_val_single == $pa_length_cm) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_length_feet) {
                                    if ($tax_terms_attribute_val_single == $pa_length_feet) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_thickness_mm) {
                                    if ($tax_terms_attribute_val_single == $pa_thickness_mm) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_width_cm) {
                                    if ($tax_terms_attribute_val_single == $pa_width_cm) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_width_inches) {
                                    if ($tax_terms_attribute_val_single == $pa_width_inches) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_kitebars_size_m) {
                                    if ($tax_terms_attribute_val_single == $pa_kitebars_size_m) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if ($pa_mast_size_cm) {
                                    if ($tax_terms_attribute_val_single == $pa_mast_size_cm) {


                                        $checked = 'checked';
                                        $checkedArray[] = $checked;
                                    } else {
                                        $checked = '';
                                        $checkedArray[] = $checked;
                                    }
                                }
                                if (in_array("checked", $checkedArray)) {
                                    $chekedArray = 'checked';
                                } else {
                                    $chekedArray = "";
                                }


//                                
                                ?>

                                <input type="radio" name="<?= $tax_terms_attribute_key; ?>" value="<?= $tax_terms_attribute_single_key; ?>" class="size_checkbox" data-size="<?= $dataSize; ?>" data-size-name="<?= $tax_terms_name; ?>" <?= $chekedArray; ?> onclick="applySizeFilter('<?= $category_name; ?>', '<?= $tax_terms_attribute_key; ?>')">
                                <span class="checkmark_new"></span>
                            </label>
                        </div>
                        <?php
                    endforeach;
                }
            endforeach;
            ?>





        </div>
    </div>


    <?php
}

if ($_REQUEST['action'] == 'showConditionModal') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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

    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        //  $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);


        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }




    if ($price) {
        $val = explode("-", $price);
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
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($search_keyword) {
        $args = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
    } else {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
    }

//    $args = array(
//        'post_type' => 'product',
//        'post_status' => 'publish',
//        'posts_per_page' => 12,
//        'tax_query' => array(
//            $tax_query
//        )
//    );






    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $name, 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;
    ?>
    <div class="modal-body">
        <br>
        <div class="row">

            <div id="original_conditions">
                <?php foreach ($tax_terms as $tax_terms_attribute_key => $tax_terms_attribute_val) : ?>
                    <?php
                    if ($tax_terms_attribute_key == "pa_condition" || $tax_terms_attribute_key == "pa_warranty" || $tax_terms_attribute_key == "pa_damage" || $tax_terms_attribute_key == "pa_repair") {
                        echo '<h4>' . wc_attribute_label($tax_terms_attribute_key) . '</h4><br>';
                        foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) :
                            if ($_REQUEST[$tax_terms_attribute_key] == $tax_terms_attribute_single_key) {
                                $pa_boom_check = "checked";
                            } else {
                                $pa_boom_check = "";
                            }
                            $condition_name = str_replace(" ", "-", $tax_terms_attribute_val_single);
                            if ($tax_terms_attribute_key == 'pa_condition') {
                                $tax_term_attribute_name = 'condition';
                            } else {
                                $tax_term_attribute_name = $tax_terms_attribute_key;
                            }
                            ?>

                            <div class="col-sm-3 col-md-3 no-gutter">
                                <label class="container_new"><?= $tax_terms_attribute_val_single; ?>
                                    <?php
                                    $changeValue = str_replace(" ", "-", $tax_terms_attribute_val_single);
                                    $conditionSlug = strtolower($changeValue);


                                    $checkedArray = array();


                                    if ($pa_condition) {

                                        if ($conditionSlug == $pa_condition) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_warranty) {
                                        if ($conditionSlug == $pa_warranty) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_damage) {
                                        if ($conditionSlug == $pa_damage) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_repair) {
                                        if ($conditionSlug == $pa_repair) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }

                                    if (in_array("checked", $checkedArray)) {
                                        $chekedArray = 'checked';
                                    } else {
                                        $chekedArray = "";
                                    }
                                    ?>


                                    <input type="radio" name="<?= $tax_terms_attribute_key; ?>" value="<?= $conditionSlug; ?>" class="condition_checkbox"  data-condition="<?= $conditionSlug; ?>"  data-condition-name="<?= $tax_terms_attribute_key; ?>" <?= $chekedArray; ?> onclick="applyConditionsFilter('<?= $category_name; ?>', '<?= $tax_terms_attribute_key; ?>')">
                                    <span class="checkmark_new"></span>
                                </label>
                            </div>
                            <?php
                        endforeach;
                    }
                endforeach;
                ?>
            </div>
            <div id="ajax_load_more_conditions"></div>
            <div class="load_more_data_button col-md-12">
                <a href="javascript:void(0)" onclick="showConditions('<?= $category_name; ?>')" class="load_more_data"><button>Show All Conditions</button></a>
                <a href="javascript:void(0)" class="loading_more_data" style="display:none;">Loading...</a>
            </div>
        </div>

    </div>
    <div class="modal-footer">
        <div class="myfooter">
            <div class="col-xs-6 col-sm-6 col-md-6 text-left">
                <a href="javascript:void(0)" class="btn" onclick="resetButton('pa_condition', '<?= $category_name; ?>')">Reset</a>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <a href="javascript:void(0)" class="btn blue" onclick="applyConditionButton('<?= $category_name; ?>')">Apply</a>
            </div>
        </div>
    </div>
    <?php
}

if ($_REQUEST['action'] == 'showSellerModal') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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

    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        //  $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }


    if ($price) {
        $val = explode("-", $price);
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
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 200,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($search_keyword) {
        $args = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 200,
            'tax_query' => array(
                $tax_query
            )
        );
    } else if ($location) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 200,
            'tax_query' => array(
                $tax_query
            )
        );
        $address = $location;
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {

            $args['order'] = 'ASC';
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
        }
    } else {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 200,
            'tax_query' => array(
                $tax_query
            )
        );
    }

//    $args = array(
//        'post_type' => 'product',
//        'post_status' => 'publish',
//        'posts_per_page' => 50,
//        'tax_query' => array(
//            $tax_query
//        )
//    );

    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $name, 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;
    ?>
    <div class="modal-body">
        <br>
        <div class="row">

            <br>
            <div id="original_seller">
                <?php foreach ($tax_terms['pa_seller'] as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single): ?>
                    <div class="col-sm-3 col-md-3 no-gutter">
                        <label class="check "><?= $tax_terms_attribute_val_single ?>
                            <?php
                            $explodeBrands = explode(",", $pa_seller);
                            $brandSelected = array();
                            $checkedArray = array();
                            foreach ($explodeBrands as $explodeBrand):

                                $brandSelected[] = $explodeBrand;
                            endforeach;
                            foreach ($brandSelected as $selectedBrands):
                                if ($tax_terms_attribute_val_single == $selectedBrands) {


                                    $checked = 'checked';
                                    $checkedArray[] = $checked;
                                } else {
                                    $checked = '';
                                    $checkedArray[] = $checked;
                                }

                            endforeach;

                            if (in_array("checked", $checkedArray)) {
                                $chekedArray = 'checked';
                            } else {
                                $chekedArray = "";
                            }
                            ?>

                            <input type="checkbox" name="seller[]" value="<?= $tax_terms_attribute_single_key; ?>" class="seller_checkbox" data-seller="<?= $tax_terms_attribute_val_single; ?>" <?= $chekedArray; ?>> 
                            <span class="checkmark"></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="ajax_load_more_seller"></div>
            <div class="load_more_data_button col-md-12">
                <!--<a href="javascript:void(0)" onclick="showSeller('<?= $category_name; ?>')" class="load_more_data"><button>Show All Sellers</button></a>-->
                <a href="javascript:void(0)" class="loading_more_data" style="display:none;">Loading...</a>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <div class="myfooter">
            <div class="col-xs-6 col-sm-6 col-md-6 text-left">
                <a href="javascript:void(0)" onclick="resetButton('seller', '<?= $category_name; ?>')" class="btn">Reset</a>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <a href="javascript:void(0)" class="btn blue" onclick="applySellerFilter('<?= $category_name; ?>')">Apply</a>
            </div>
        </div>
    </div>
    <?php
}

if ($_REQUEST['action'] == 'showSeller') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];







    if ($current_language == 'en') {
        $slug_name = $category_name;
    } else if ($current_language == 'fr') {
        $slug_name = $category_name . '-' . $current_language . "-2";
    } else {
        $slug_name = $category_name . '-' . $current_language;
    }

    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                'terms' => $term_id,
            )
        )
    );
    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

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
    endforeach;

    foreach ($tax_terms['pa_seller'] as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single):
        ?>
        <div class="col-sm-3 col-md-3 no-gutter">
            <label class="check "><?= $tax_terms_attribute_val_single ?> 

                <input type="checkbox" name="selected_years" value="<?= $tax_terms_attribute_single_key; ?>">
                <span class="checkmark"></span>
            </label>
        </div>
        <?php
    endforeach;
}

if ($_REQUEST['action'] == 'mapShow') {

    $response = array();
    $address = $_REQUEST['address'];

    $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
    $geometry = json_decode($geo, true);

    $latitude = $geometry['results'][0]['geometry']['location']['lat'];
    $longitude = $geometry['results'][0]['geometry']['location']['lng'];
    $response['result']['default_latitude'] = $latitude;
    $response['result']['default_longitude'] = $longitude;
    echo json_encode($response);
}

if ($_REQUEST['action'] == 'locationProducts') {
    //die('hlo');

    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    if ($current_language == 'en') {
        $slug_name = $category_name;
    } else if ($current_language == 'fr') {
        $slug_name = $category_name . '-' . $current_language . "-2";
    } else {
        $slug_name = $category_name . '-' . $current_language;
    }
    $limit = 12;
    $pageNo = $_REQUEST['page_no'];
    if ($page) {
        $newLimit = $limit - 1;
        $offset = ($pageNo * $limit) - $newLimit;
    } else {
        $offset = 0;
    }

    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);

    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");




    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));
    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    if ($brand) {

        //  $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }

    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }


    if ($price) {

        $val = explode("-", $price);
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
        $arguments = array(
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
    } else if ($search_keyword && empty($location)) {
        //  die('aa');
        $arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    } else if ($location && $search_keyword) {
        //die('aa');
        $arguments = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
        $address = $location;
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {

            $arguments['order'] = 'ASC';
            $arguments['orderby'] = 'distance';

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
            $range = 700;
            $bbox = get_bounding_box_deg($latitude, $longitude, $range);
            $area[] = $bbox[1];
            $area[] = $bbox[0];
            $arealng[] = $bbox[2];
            $arealng[] = $bbox[3];



            $arguments['meta_query'] = array(
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
            $arguments['geo_query'] = array(
                'lat_field' => 'lat', // this is the name of the meta field storing latitude
                'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                'distance' => $range, // this is the maximum distance to search
                'units' => 'km'       // this supports options: miles, mi, kilometers, km
            );
        }
    } else if ($location && empty($search_keyword)) {

        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
        $address = $location;
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {

            $arguments['order'] = 'ASC';
            $arguments['orderby'] = 'distance';

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



            $arguments['meta_query'] = array(
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
            $arguments['geo_query'] = array(
                'lat_field' => 'lat', // this is the name of the meta field storing latitude
                'lng_field' => 'lng', // this is the name of the meta field storing longitude 
                'latitude' => $latitude, // this is the latitude of the point we are getting distance from
                'longitude' => $longitude, // this is the longitude of the point we are getting distance from
                'distance' => $range, // this is the maximum distance to search
                'units' => 'km'       // this supports options: miles, mi, kilometers, km
            );
        }
    } else {
        //die('aa');
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    }



    // $loop = new WP_Query($args);
    $fetchAllProducts = new WP_Query($arguments);
    $product_count = $fetchAllProducts->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);

    foreach ($fetchAllProducts->posts as $fetchAllProduct):
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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny    ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
    <?php
    if ($page) {
        $current_page_selecetd = $page;
    } else {
        $current_page_selecetd = "1";
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
    ?>

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

                if ($page == $current_page_selecetd) {
                    $activeClass = 'active';
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
    <?php
}

if ($_REQUEST['action'] == 'location_pagination_data') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category'];
    $pageNo = $_REQUEST['page_no'];
    if ($current_language == 'en') {
        $slug_name = $category_name;
    } else if ($current_language == 'fr') {
        $slug_name = $category_name . '-' . $current_language . "-2";
    } else {
        $slug_name = $category_name . '-' . $current_language;
    }

    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
    $limit = '12';
    $newLimit = $limit - 1;
    $offset = ($pageNo * $limit) - $newLimit;
    $pageargs = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'offset' => $offset,
        'posts_per_page' => $limit,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                'terms' => $term_id,
            )
        )
    );
    $address = $_REQUEST['location'];
    $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyAXz4tTNJ8PtN8unz_PcQzSynqWLFFm-7M');
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
    // $loop = new WP_Query($args);
    $fetchAllProducts = new WP_Query($pageargs);
    $product_count = $fetchAllProducts->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);

    foreach ($fetchAllProducts->posts as $fetchAllProduct):
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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny    ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
    <?php
    $current_page_selecetd = $_REQUEST['page_no'];
    $startPage = $current_page_selecetd - 4;
    $endPage = $current_page_selecetd + 4;
    if ($startPage <= 0) {
        $endPage -= ($startPage - 1);
        $startPage = 1;
    }
    if ($endPage > $total_no_of_pages) {
        $endPage = $total_no_of_pages;
    }
    ?>
    <div class="col-sm-12 col-md-12 text-center">
        <ul class="pagination" id="mypagies"> 

            <?php
            if ($current_page_selecetd == '1' || $current_page_selecetd == '') {
                $page_class = 'disabled';
            } else {
                $page_class = '';
            }
            ?>
            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData('1', '<?= $category_name; ?>'')"><i class="fa fa-angle-left"></i><i class="fa fa-angle-left"></i></a></li>
            <li class="<?= $page_class; ?>"><a class="lp1" href="javascript:void(0)" onclick="paginationData(<?= $current_page_selecetd - 1; ?>, '<?= $category_name; ?>')"><i class="fa fa-angle-left"></i></a></li>
            <?php
            for ($page = $startPage; $page <= $endPage; $page++) {

                if ($page == $current_page_selecetd) {
                    $activeClass = 'active';
                } else {
                    $activeClass = '';
                }
                ?>
                <li class="<?= $activeClass; ?>"><a href="javascript:void(0)" onclick="paginationData(<?= $page; ?>, '<?= $category_name; ?>')"><?= $page; ?><span class="sr-only">(current)</span></a></li>
            <?php } ?>

            <li><a class="lp2" href="javascript:void(0)" onclick="paginationData(<?= $current_page_selecetd + 1; ?>, '<?= $category_name; ?>')"><i class="fa fa-angle-right"></i></a></li>
            <li><a class="lp2" href="javascript:void(0)" onclick="paginationData(<?= $total_no_of_pages; ?>, '<?= $category_name; ?>')"><i class="fa fa-angle-right"></i><i class="fa fa-angle-right"></i></a></li>
        </ul>
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>
    </div>
    <?php
}

if ($_REQUEST['action'] == 'showPriceModal') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);
    //$price = $_REQUEST['price'];
    $query = $parseURL['query'];
    $parseString = parse_str($query);


    if ($current_language == 'en') {
        $slug_name = $category_name;
    } else if ($current_language == 'fr') {
        $slug_name = $category_name . '-' . $current_language . "-2";
    } else {
        $slug_name = $category_name . '-' . $current_language;
    }

    $term_id = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug = '$slug_name' ");
    $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$term_id  ");


    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));

    $term = array();
    $term_taxonomy = array();

    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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
    $explodePrice = explode("-", $price);
    $selected_min_price = $explodePrice[0];
    $selected_max_price = $explodePrice[1];

    if ($brand) {

        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
        foreach ($val as $allBrands):
            $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$allBrands' ");


            $term[] = $getTermId;

        endforeach;
        foreach ($term as $termTaxonomyId):
            $get_term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$termTaxonomyId  ");
            $term_taxonomy[] = $get_term_taxonomy_id;
        endforeach;

        $term_taxonomy_id = implode(",", $term_taxonomy);
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
        foreach ($val as $allBrands):
            $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$allBrands' ");


            $term[] = $getTermId;

        endforeach;
        foreach ($term as $termTaxonomyId):
            $get_term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$termTaxonomyId  ");
            $term_taxonomy[] = $get_term_taxonomy_id;
        endforeach;

        $term_taxonomy_id = implode(",", $term_taxonomy);
    }
    if ($pa_seller) {
        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
        foreach ($val as $allBrands):
            $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$allBrands' ");


            $term[] = $getTermId;

        endforeach;
        foreach ($term as $termTaxonomyId):
            $get_term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$termTaxonomyId  ");
            $term_taxonomy[] = $get_term_taxonomy_id;
        endforeach;

        $term_taxonomy_id = implode(",", $term_taxonomy);
    }
    if ($pa_condition) {

        $condition_val = explode(",", $pa_condition);
        $tax_condition_query = array('relation' => 'OR');
        foreach ($condition_val as $conditionValue):

            $valueCondition[] = $conditionValue;
            $getTaxonomy = $wpdb->get_var("SELECT taxonomy FROM iewZNddPterm_taxonomy WHERE term_id =$conditionValue ");

            //foreach ($getTaxonomy as $getTaxonomies):
            $tax_condition_query[] = array(
                'taxonomy' => "$getTaxonomy",
                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                'terms' => $valueCondition,
                'operator' => 'IN'
            );

            $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE term_id ='$conditionValue' ");

            $term[] = $conditionValue;

        endforeach;

        foreach ($term as $termTaxonomyId):
            $get_term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$termTaxonomyId  ");
            $term_taxonomy[] = $get_term_taxonomy_id;
        endforeach;
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_blade_size' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_carbon_number' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_blade_size' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_surface' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_boom_size' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_volume' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_size' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_blade_size_in' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_size_number' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');

        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_length_cm' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_length_feet' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_thickness_mm' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_width_cm' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_width_inches' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_kitebars_size_m' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
        $getTermId = $wpdb->get_var("SELECT term_id FROM $termsTable WHERE slug ='$pa_mast_size_cm' ");

        $term_taxonomy_id = $wpdb->get_var("SELECT term_taxonomy_id  FROM $termsTaxonomyTable WHERE term_id =$getTermId  ");
    }


    if ($search_keyword) {
        $query_string = "SELECT MIN(cast(FLOOR(br_prices.meta_value) as decimal)) as min_price, MAX(cast(FLOOR(br_prices.meta_value) as decimal)) as max_price FROM iewZNddPposts  INNER JOIN iewZNddPpostmeta as br_prices ON (iewZNddPposts.ID = br_prices.post_id) INNER JOIN  iewZNddPterm_relationships ON (iewZNddPposts.ID = iewZNddPterm_relationships.object_id) WHERE iewZNddPterm_relationships.term_taxonomy_id IN ($term_taxonomy_id) AND iewZNddPposts.post_type = 'product' AND iewZNddPposts.post_status = 'publish' AND br_prices.meta_key = '_price' AND br_prices.meta_value > 0 AND iewZNddPposts.post_title LIKE '%$search_keyword%' ";
    } else if ($location) {
        $address = $location;
        $geo = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=AIzaSyCAjHUDYo8WFag0TnZDtoYJptf0MYabYEs');
        $geo = json_decode($geo, true);
        if ($geo['status'] = 'OK') {
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
//            echo '<pre>';
//            print_r($area);
//            echo '</pre>';
        }
        //echo $query_string = "SELECT MIN(cast(FLOOR(br_prices.meta_value) as decimal)) as min_price, MAX(cast(FLOOR(br_prices.meta_value) as decimal)) as max_price FROM iewZNddPposts  INNER JOIN iewZNddPpostmeta as br_prices ON (iewZNddPposts.ID = br_prices.post_id) INNER JOIN  iewZNddPterm_relationships ON (iewZNddPposts.ID = iewZNddPterm_relationships.object_id) WHERE iewZNddPterm_relationships.term_taxonomy_id =$term_taxonomy_id AND iewZNddPposts.post_type = 'product' AND iewZNddPposts.post_status = 'publish' AND br_prices.meta_key = '_price' AND br_prices.meta_value > 0 AND (br_prices.meta_key='lat' AND br_prices.meta_value BETWEEN '$area[0]' AND '$area[1]') AND (br_prices.meta_key = 'lng' AND br_prices.meta_value BETWEEN '$arealng[0]' AND '$arealng[1]')";
        $query_string = "	SELECT MIN(cast(FLOOR(br_prices.meta_value) as decimal)) as min_price, MAX(cast(FLOOR(br_prices.meta_value) as decimal)) as max_price
								FROM iewZNddPposts 
								INNER JOIN iewZNddPpostmeta ON (iewZNddPposts.ID = iewZNddPpostmeta.post_id)
								INNER JOIN iewZNddPpostmeta AS br_lng ON (iewZNddPposts.ID = br_lng.post_id)
								INNER JOIN iewZNddPpostmeta AS br_prices ON (iewZNddPposts.ID = br_prices.post_id)
								INNER JOIN iewZNddPterm_relationships ON (iewZNddPposts.ID = iewZNddPterm_relationships.object_id) 
								WHERE iewZNddPterm_relationships.term_taxonomy_id = $term_taxonomy_id 
								AND iewZNddPposts.post_type = 'product' 
								AND iewZNddPposts.post_status = 'publish' 
								AND br_prices.meta_key = '_price' AND br_prices.meta_value > 0
								AND (( (iewZNddPpostmeta.meta_key = 'lat' AND ROUND(iewZNddPpostmeta.meta_value,10) BETWEEN '$area[0]' AND '$area[1]')
								AND (br_lng.meta_key = 'lng' AND ROUND(br_lng.meta_value,10) BETWEEN '$arealng[0]' AND '$arealng[1]') ))";
    } else {
        $query_string = "SELECT MIN(cast(FLOOR(br_prices.meta_value) as decimal)) as min_price, MAX(cast(FLOOR(br_prices.meta_value) as decimal)) as max_price FROM iewZNddPposts  INNER JOIN iewZNddPpostmeta as br_prices ON (iewZNddPposts.ID = br_prices.post_id) INNER JOIN  iewZNddPterm_relationships ON (iewZNddPposts.ID = iewZNddPterm_relationships.object_id) WHERE iewZNddPterm_relationships.term_taxonomy_id IN ($term_taxonomy_id) AND iewZNddPposts.post_type = 'product' AND iewZNddPposts.post_status = 'publish' AND br_prices.meta_key = '_price' AND br_prices.meta_value > 0";
    }


    $prices = $wpdb->get_row($query_string);
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 50,
        'tax_query' => array(
            $tax_query
        )
    );


    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $name, 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }


    endforeach;
    ?>
    <div class="modal-body">
        <br>
        <div class="row">
            <div class="col-md-12 text-center">
                <div class="products">
                    <div id="original_amount">
                        <?php if (empty($_REQUEST['price'])) { ?>
                            <input type="hidden" name="minimum_price"  value="<?= $prices->min_price; ?>"/>
                            <input type="hidden" name="maximum_price"  value="<?= $prices->max_price; ?>"/> 
                            <?php
                        } else {
                            $explode_price = explode("-", $_REQUEST['price']);
                            ?>
                            <input type="hidden" name="minimum_price"  value="<?= $explode_price[0]; ?>"/>
                            <input type="hidden" name="maximum_price"  value="<?= $explode_price[1]; ?>"/> 
                        <?php }
                        ?>
                        <?php if (empty($price)) { ?>
                            <h3><?= $prices->min_price; ?> &euro;  -  <?= $prices->max_price; ?> &euro;</h3>
                        <?php } else { ?>
                            <h3><?= $selected_min_price; ?> &euro;  -  <?= $selected_max_price; ?> &euro;</h3>
                        <?php }
                        ?>

                    </div>
                    <div id="ajax_amount"></div>
                    
                    <br>
                    <!--<input id="ex2" type="text" class="span2" value="" data-slider-min="10" data-slider-max="1000" data-slider-step="1" data-slider-value="[10,1000]"/>--> 
                    <input type="hidden" id="amount" readonly style="border:0; color:#f6931f; font-weight:bold;" name="apply_price_range" value="<?= $price; ?>">

                    <div id="slider-range"></div>
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <div class="myfooter">
            <div class="col-xs-6 col-sm-6 col-md-6 text-left">
                <a href="javascript:void(0)" onclick="resetButton('price', '<?= $category_name; ?>')" class="btn">Reset</a>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <a href="javascript:void(0)" class="btn blue" onclick="applyPriceRange('<?= $category_name; ?>')">Apply</a>
            </div>
        </div>
    </div>
    <?php
}
if ($_REQUEST['action'] == 'reset') {

    global $wpdb;
    global $wp_query;
    global $post;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

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

    $checked_value = $_REQUEST['checked_value'];

    //$val = explode(",", $checked_value);
    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    echo $parseString = parse_str($query);

    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));


    if ($brand) {

        // $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_condition) {

        $val = explode(",", $pa_condition);
        $tax_condition_query = array('relation' => 'OR');
        foreach ($val as $sizeValue):

            $value[] = $sizeValue;
            $getTaxonomy = $wpdb->get_var("SELECT taxonomy FROM iewZNddPterm_taxonomy WHERE term_id =$sizeValue ");

            //foreach ($getTaxonomy as $getTaxonomies):
            $tax_condition_query[] = array(
                'taxonomy' => "$getTaxonomy",
                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                'terms' => $value,
                'operator' => 'IN'
            );
            // endforeach;
            // $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'term_id', 'terms' => $sizeValue, 'operator' => 'IN');
        endforeach;
    }

    if ($pa_size) {

        $val = explode(",", $pa_size);
        $tax_size_query = array('relation' => 'OR');
        foreach ($val as $sizeValue):

            $value[] = $sizeValue;
            $getTaxonomy = $wpdb->get_var("SELECT taxonomy FROM iewZNddPterm_taxonomy WHERE term_id =$sizeValue ");

            //foreach ($getTaxonomy as $getTaxonomies):
            $tax_size_query[] = array(
                'taxonomy' => "$getTaxonomy",
                'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                'terms' => $value,
                'operator' => 'IN'
            );
            // endforeach;
            // $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'term_id', 'terms' => $sizeValue, 'operator' => 'IN');
        endforeach;
    }


    if ($price) {
        $val = explode("-", $price);
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
        $arguments = array(
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
    } else if ($pa_size && $price && empty($pa_condition)) {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query,
                $tax_size_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($pa_size && empty($price) && empty($pa_condition)) {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query,
                $tax_size_query
            )
        );
    } else if ($pa_condition && empty($pa_size) && empty($price)) {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query,
                $tax_condition_query
            )
        );
    } else if ($pa_condition && $pa_size && empty($price)) {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query,
                $tax_size_query,
                $tax_condition_query
            )
        );
    } else if ($pa_condition && empty($pa_size) && $price) {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query,
                $tax_condition_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($pa_condition && $pa_size && $price) {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query,
                $tax_size_query,
                $tax_condition_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else {
        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    }



    $product_seller = new WP_Query($arguments);

    $product_count = $product_seller->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);
    foreach ($product_seller->posts as $fetchAllProduct):

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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny    ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
    <?php
    $current_page_selecetd = $_REQUEST['page_no'];
    $startPage = $current_page_selecetd - 4;
    $endPage = $current_page_selecetd + 4;
    if ($startPage <= 0) {
        $endPage -= ($startPage - 1);
        $startPage = 1;
    }
    if ($endPage > $total_no_of_pages) {
        $endPage = $total_no_of_pages;
    }
    ?>

    <div class="col-sm-12 col-md-12 text-center">
        <ul class="pagination" id="mypagies"> 
        <!--    <li class="disabled"><a class="lp1" href="#"><i class="fa fa-angle-left"></i></a></li>-->


            <?php
            //echo $endPage;
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

                if ($page == $current_page_selecetd || $page == '1') {
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
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>
    </div>
    <?php
}

if ($_REQUEST['action'] == 'showAjaxConditionModal') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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
    if ($search_keyword) {
        $wordExplode = explode(" ", $search_keyword);
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

    if ($brand) {

        //  $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_mast_size) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
    }
    if ($pa_carbon_number) {
        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
    }
    if ($pa_blade_size) {
        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
    }
    if ($pa_surface) {
        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
    }
    if ($pa_boom_size) {
        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
    }
    if ($pa_volume) {
        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
    }

    if ($pa_size) {
        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
    }

    if ($pa_blade_size_in) {
        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
    }
    if ($pa_size_number) {
        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
    }
    if ($pa_length_cm) {
        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
    }
    if ($pa_length_feet) {
        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');
    }
    if ($pa_thickness_mm) {
        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
    }
    if ($pa_width_cm) {
        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
    }
    if ($pa_width_inches) {
        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
    }
    if ($pa_kitebars_size_m) {
        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
    }
    if ($pa_mast_size_cm) {
        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }




    if ($price) {
        $val = explode("-", $price);
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
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else if ($search_keyword) {
        $args = array(
            's' => $search_keyword,
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
    } else {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'tax_query' => array(
                $tax_query
            )
        );
    }

//    $args = array(
//        'post_type' => 'product',
//        'post_status' => 'publish',
//        'posts_per_page' => 12,
//        'tax_query' => array(
//            $tax_query
//        )
//    );






    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $name, 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;
    ?>
    <div class="modal-body">
        <br>
        <div class="row">

            <div id="original_conditions">
                <?php foreach ($tax_terms as $tax_terms_attribute_key => $tax_terms_attribute_val) : ?>
                    <?php
                    if ($tax_terms_attribute_key == "pa_condition" || $tax_terms_attribute_key == "pa_warranty" || $tax_terms_attribute_key == "pa_damage" || $tax_terms_attribute_key == "pa_repair") {
                        echo '<h4>' . wc_attribute_label($tax_terms_attribute_key) . '</h4><br>';
                        foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) :
                            if ($_REQUEST[$tax_terms_attribute_key] == $tax_terms_attribute_single_key) {
                                $pa_boom_check = "checked";
                            } else {
                                $pa_boom_check = "";
                            }
                            $condition_name = str_replace(" ", "-", $tax_terms_attribute_val_single);
                            if ($tax_terms_attribute_key == 'pa_condition') {
                                $tax_term_attribute_name = 'condition';
                            } else {
                                $tax_term_attribute_name = $tax_terms_attribute_key;
                            }
                            ?>

                            <div class="col-sm-3 col-md-3 no-gutter">
                                <label class="container_new"><?= $tax_terms_attribute_val_single; ?>
                                    <?php
                                    $changeValue = str_replace(" ", "-", $tax_terms_attribute_val_single);
                                    $conditionSlug = strtolower($changeValue);
                                    $checkedArray = array();
                                    if ($pa_condition) {

                                        if ($conditionSlug == $pa_condition) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_warranty) {
                                        if ($conditionSlug == $pa_warranty) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_damage) {
                                        if ($conditionSlug == $pa_damage) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }
                                    if ($pa_repair) {
                                        if ($conditionSlug == $pa_repair) {


                                            $checked = 'checked';
                                            $checkedArray[] = $checked;
                                        } else {
                                            $checked = '';
                                            $checkedArray[] = $checked;
                                        }
                                    }

                                    if (in_array("checked", $checkedArray)) {
                                        $chekedArray = 'checked';
                                    } else {
                                        $chekedArray = "";
                                    }
                                    ?>


                                    <input type="radio" name="<?= $tax_terms_attribute_key; ?>" value="<?= $conditionSlug; ?>" class="condition_checkbox"  data-condition="<?= $conditionSlug; ?>"  data-condition-name="<?= $tax_terms_attribute_key; ?>" <?= $chekedArray; ?> onclick="applyConditionsFilter('<?= $category_name; ?>', '<?= $tax_terms_attribute_key; ?>')">
                                    <span class="checkmark_new"></span>
                                </label>
                            </div>
                            <?php
                        endforeach;
                    }
                endforeach;
                ?>
            </div>
            <div id="ajax_load_more_conditions"></div>

        </div>

    </div>

    <?php
}

if ($_REQUEST['action'] == 'resetSizeButton') {
    global $wpdb;
    global $wp_query;
    global $post;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];

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
    $checked_value = $_REQUEST['checked_value'];

    //$val = explode(",", $checked_value);
    $url = $_REQUEST['url'];

    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);

    $tax_query = array('relation' => 'AND', array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
            'terms' => $term_id,
    ));


    if ($brand) {

        //$val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_seller) {

        $val = explode(",", $pa_seller);
        $tax_query[] = array('taxonomy' => 'pa_seller', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }

//    if ($pa_mast_size) {
//        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size, 'operator' => 'IN');
//    }
//    if ($pa_carbon_number) {
//        $tax_query[] = array('taxonomy' => 'pa_carbon-number', 'field' => 'slug', 'terms' => $pa_carbon_number, 'operator' => 'IN');
//    }
//    if ($pa_blade_size) {
//        $tax_query[] = array('taxonomy' => 'pa_blade-size-cm²', 'field' => 'slug', 'terms' => $pa_blade_size, 'operator' => 'IN');
//    }
//    if ($pa_surface) {
//        $tax_query[] = array('taxonomy' => 'pa_surface-m²', 'field' => 'slug', 'terms' => $pa_surface, 'operator' => 'IN');
//    }
//    if ($pa_boom_size) {
//        $tax_query[] = array('taxonomy' => 'pa_boom-size-cm', 'field' => 'slug', 'terms' => $pa_boom_size, 'operator' => 'IN');
//    }
//    if ($pa_volume) {
//        $tax_query[] = array('taxonomy' => 'pa_volume-liters', 'field' => 'slug', 'terms' => $pa_volume, 'operator' => 'IN');
//    }
//
//    if ($pa_size) {
//        $tax_query[] = array('taxonomy' => 'pa_size-xssmlxlxxl', 'field' => 'slug', 'terms' => $pa_size, 'operator' => 'IN');
//    }
//
//    if ($pa_blade_size_in) {
//        $tax_query[] = array('taxonomy' => 'pa_blade-sizein²', 'field' => 'slug', 'terms' => $pa_blade_size_in, 'operator' => 'IN');
//    }
//    if ($pa_size_number) {
//        $tax_query[] = array('taxonomy' => 'pa_size-number', 'field' => 'slug', 'terms' => $pa_size_number, 'operator' => 'IN');
//    }
//    if ($pa_length_cm) {
//        $tax_query[] = array('taxonomy' => 'pa_length-cm', 'field' => 'slug', 'terms' => $pa_length_cm, 'operator' => 'IN');
//    }
//    if ($pa_length_feet) {
//        $tax_query[] = array('taxonomy' => 'pa_length-feet', 'field' => 'slug', 'terms' => $pa_length_feet, 'operator' => 'IN');

//    }
//    if ($pa_thickness_mm) {
//        $tax_query[] = array('taxonomy' => 'pa_thickness-mm', 'field' => 'slug', 'terms' => $pa_thickness_mm, 'operator' => 'IN');
//    }
//    if ($pa_width_cm) {
//        $tax_query[] = array('taxonomy' => 'pa_width-cm', 'field' => 'slug', 'terms' => $pa_width_cm, 'operator' => 'IN');
//    }
//    if ($pa_width_inches) {
//        $tax_query[] = array('taxonomy' => 'pa_width-inches', 'field' => 'slug', 'terms' => $pa_width_inches, 'operator' => 'IN');
//    }
//    if ($pa_kitebars_size_m) {
//        $tax_query[] = array('taxonomy' => 'pa_kitebars-size-m', 'field' => 'slug', 'terms' => $pa_kitebars_size_m, 'operator' => 'IN');
//    }
//    if ($pa_mast_size_cm) {
//        $tax_query[] = array('taxonomy' => 'pa_mast-size-cm', 'field' => 'slug', 'terms' => $pa_mast_size_cm, 'operator' => 'IN');
//    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }




    if ($price) {

        $val = explode("-", $price);
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
        $arguments = array(
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
    } else {

        $arguments = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'tax_query' => array(
                $tax_query
            )
        );
    }

    $product_seller = new WP_Query($arguments);

    $product_count = $product_seller->found_posts;
    $total_no_of_pages = ceil($product_count / $limit);
    foreach ($product_seller->posts as $fetchAllProduct):
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
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>

                                    <?php } else { ?>
                                        <a href="<?= $product->get_permalink(); ?>" target="_blank"><img width="338" height="600" src="/wp-content/themes/dashstore-child/images/No_Photo_Available.jpg" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>
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
                                            <?php $image = wp_get_attachment_image_src($productGallery, 'medium'); // Danny    ?>
                                            <a href="<?= $product->get_permalink(); ?>" target="_blank"><img src="<?= $image[0]; ?>" class="product_featured_image attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image"></a>                                             

                                        </div>
                                    </div>
                                    <!--/row-fluid-->                                    
                                </div>
                                <?php
                            endforeach;
                        }
                        ?>
                        <div class="product-description">
                            <h2><a href="<?= $product->get_permalink(); ?>" target="_blank">
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
                            <p><?= number_format("$product->price", 2); ?>€</p>
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
        <?php
        $current_page_selecetd = $_REQUEST['page_no'];
        $startPage = $current_page_selecetd - 4;
        $endPage = $current_page_selecetd + 4;
        if ($startPage <= 0) {
            $endPage -= ($startPage - 1);
            $startPage = 1;
        }
        if ($endPage > $total_no_of_pages) {
            $endPage = $total_no_of_pages;
        }
        ?>

        <ul class="pagination" id="mypagies"> 
          <!--  <li class="disabled"><a class="lp1" href="#"><i class="fa fa-angle-left"></i></a></li>-->

            <?php
            //echo $endPage;
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

                if ($page == $current_page_selecetd || $page == '1') {
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
        <p>1-<?= $limit; ?> of <?= $product_count; ?> products available</p>
    </div>
    <?php
}

if ($_REQUEST['action'] == 'resetSizeModal') {
    global $wpdb;
    global $wp_query;
    global $post;
    global $product;
    $termsTable = $wpdb->prefix . 'terms';
    $termsTaxonomyTable = $wpdb->prefix . 'term_taxonomy';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $post_table = $wpdb->prefix . 'posts';
    $post_meta_table = $wpdb->prefix . 'postmeta';

    $current_language = ICL_LANGUAGE_CODE;
    $category_name = $_REQUEST['category_name'];







    $url = $_REQUEST['url'];
    $parseURL = parse_url($url);

    $query = $parseURL['query'];
    $parseString = parse_str($query);


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


    if ($brand) {

        //  $val = explode(",", $brand);
        $brandReplace = str_replace(" ", "-", $brand);
        $val = explode(",", $brandReplace);

        $tax_query[] = array('taxonomy' => 'pa_brand', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_year) {
        $val = explode(",", $pa_year);
        $tax_query[] = array('taxonomy' => 'pa_years', 'field' => 'slug', 'terms' => $val, 'operator' => 'IN');
    }
    if ($pa_condition) {
        $tax_query[] = array('taxonomy' => 'pa_condition', 'field' => 'slug', 'terms' => $pa_condition, 'operator' => 'IN');
    }
    if ($pa_warranty) {
        $tax_query[] = array('taxonomy' => 'pa_warranty', 'field' => 'slug', 'terms' => $pa_warranty, 'operator' => 'IN');
    }
    if ($pa_damage) {
        $tax_query[] = array('taxonomy' => 'pa_damage', 'field' => 'slug', 'terms' => $pa_damage, 'operator' => 'IN');
    }
    if ($pa_repair) {
        $tax_query[] = array('taxonomy' => 'pa_repair', 'field' => 'slug', 'terms' => $pa_repair, 'operator' => 'IN');
    }


    if ($price) {
        $val = explode("-", $price);
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
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            ),
            'meta_query' => array(
                $meta_query
            )
        );
    } else {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                $tax_query
            )
        );
    }
    $allproducts = new WP_Query($args);

    foreach ($allproducts->posts as $fetchAllProduct):
        $product = new WC_Product($fetchAllProduct->ID);
        $attributes = $product->get_attributes();
        $product_count = get_terms('product_cat');

        foreach ($attributes as $attribute) {

            $name = $attribute->get_name();
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $name, 'all');

                foreach ($terms as $term) {

                    $single_term = esc_html($term->name);
                    $tax_terms[$name][$term->term_id] = esc_html($term->name);
                }
            }
        }
    endforeach;

    foreach ($tax_terms as $tax_terms_attribute_key => $tax_terms_attribute_val):

        if ($tax_terms_attribute_key == "pa_blade-size-cm²" || $tax_terms_attribute_key == "pa_blade-sizein²" || $tax_terms_attribute_key == "pa_carbon-number" || $tax_terms_attribute_key == "pa_length-cm" || $tax_terms_attribute_key == "pa_length-feet" || $tax_terms_attribute_key == "pa_size-number" || $tax_terms_attribute_key == "pa_size-xssmlxlxxl" || $tax_terms_attribute_key == "pa_surface-m²" || $tax_terms_attribute_key == "pa_thickness-mm" || $tax_terms_attribute_key == "pa_volume-liters" || $tax_terms_attribute_key == "pa_width-cm" || $tax_terms_attribute_key == "pa_width-inches" || $tax_terms_attribute_key == "pa_boom-size-cm" || $tax_terms_attribute_key == "pa_kitebars-size-m" || $tax_terms_attribute_key == "pa_length-cm" || $tax_terms_attribute_key == "pa_mast-size-cm") {
            echo '<h4>' . wc_attribute_label($tax_terms_attribute_key) . '</h4><br>';
            foreach ($tax_terms_attribute_val as $tax_terms_attribute_single_key => $tax_terms_attribute_val_single) :
                if ($_REQUEST[$tax_terms_attribute_key] == $tax_terms_attribute_single_key) {
                    $pa_boom_check = "checked";
                } else {
                    $pa_boom_check = "";
                }
                ?>
                <div class="col-sm-3 col-md-3 no-gutter">
                    <label class="check "><?= $tax_terms_attribute_val_single; ?>

                        <?php
                        $dataSize = str_replace(".", "-", $tax_terms_attribute_val_single);
                        if ($tax_terms_attribute_key == 'pa_surface-m²') {
                            $tax_terms_name = 'pa_surface';
                        } else if ($tax_terms_attribute_key == 'pa_boom-size-cm') {
                            $tax_terms_name = 'pa_boom_size';
                        } else if ($tax_terms_attribute_key == 'pa_carbon-number') {
                            $tax_terms_name = 'pa_carbon_number';
                        } else if ($tax_terms_attribute_key == 'pa_mast-size-cm') {
                            $tax_terms_name = 'pa_mast_size';
                        } else if ($tax_terms_attribute_key == 'pa_volume-liters') {
                            $tax_terms_name = 'pa_volume';
                        } else if ($tax_terms_attribute_key == 'pa_size-xssmlxlxxl') {
                            $tax_terms_name = 'pa_size';
                        } else if ($tax_terms_attribute_key == 'pa_size-number') {
                            $tax_terms_name = 'pa_size_number';
                        } else if ($tax_terms_attribute_key == 'pa_blade-size-cm²') {
                            $tax_terms_name = "pa_blade_size";
                        } else if ($tax_terms_attribute_key == 'pa_blade-sizein²') {
                            $tax_terms_name = "pa_blade_size_in";
                        } else if ($tax_terms_attribute_key == 'pa_length-cm') {
                            $tax_terms_name = "pa_length_cm";
                        } else if ($tax_terms_attribute_key == 'pa_length-feet') {
                            $tax_terms_name = "pa_length_feet";
                        } else if ($tax_terms_attribute_key == 'pa_thickness-mm') {
                            $tax_terms_name = 'pa_thickness_mm';
                        } else if ($tax_terms_attribute_key == 'pa_width-cm') {
                            $tax_terms_name = "pa_width_cm";
                        } else if ($tax_terms_attribute_key == 'pa_width-inches') {
                            $tax_terms_name = "pa_width_inches";
                        } else if ($tax_terms_attribute_key == "pa_kitebars-size-m") {
                            $tax_terms_name = "pa_kitebars_size_m";
                        } else if ($tax_terms_attribute_key == '"pa_mast-size-cm') {
                            $tax_terms_name = "pa_mast_size_cm";
                        } else {
                            $tax_terms_name = '';
                        }
                        if ($pa_mast_size) {
                            if ($tax_terms_attribute_val_single == $pa_mast_size) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_carbon_number) {
                            if ($tax_terms_attribute_val_single == $pa_carbon_number) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_blade_size) {
                            if ($tax_terms_attribute_val_single == $pa_blade_size) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_surface) {
                            if ($tax_terms_attribute_val_single == $pa_surface) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_boom_size) {
                            if ($tax_terms_attribute_val_single == $pa_boom_size) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_volume) {
                            if ($tax_terms_attribute_val_single == $pa_volume) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }

                        if ($pa_size) {
                            if ($tax_terms_attribute_val_single == $pa_size) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }

                        if ($pa_blade_size_in) {
                            if ($tax_terms_attribute_val_single == $pa_blade_size_in) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_size_number) {
                            if ($tax_terms_attribute_val_single == $pa_size_number) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_length_cm) {
                            if ($tax_terms_attribute_val_single == $pa_length_cm) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_length_feet) {
                            if ($tax_terms_attribute_val_single == $pa_length_feet) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_thickness_mm) {
                            if ($tax_terms_attribute_val_single == $pa_thickness_mm) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_width_cm) {
                            if ($tax_terms_attribute_val_single == $pa_width_cm) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_width_inches) {
                            if ($tax_terms_attribute_val_single == $pa_width_inches) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_kitebars_size_m) {
                            if ($tax_terms_attribute_val_single == $pa_kitebars_size_m) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if ($pa_mast_size_cm) {
                            if ($tax_terms_attribute_val_single == $pa_mast_size_cm) {


                                $checked = 'checked';
                                $checkedArray[] = $checked;
                            } else {
                                $checked = '';
                                $checkedArray[] = $checked;
                            }
                        }
                        if (in_array("checked", $checkedArray)) {
                            $chekedArray = 'checked';
                        } else {
                            $chekedArray = "";
                        }
                        ?>

                        <input type="radio" name="<?= $tax_terms_attribute_key; ?>" value="<?= $dataSize; ?>" class="size_checkbox" data-size="<?= $dataSize; ?>" data-size-name="<?= $tax_terms_name; ?>" <?= $chekedArray ?> onclick="applySizeFilter('<?= $category_name; ?>', '<?= $tax_terms_attribute_key; ?>')">
                        <span class="checkmark_new"></span>
                    </label>
                </div>
                <?php
            endforeach;
        }
    endforeach;
}



    