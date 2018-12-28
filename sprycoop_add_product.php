<?php
/*
  Template Name: sprycoop add product
 */
get_header();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css" />
<link rel="stylesheet" href="/wp-content/themes/dashstore-child/css/style1.css">
<link href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
<script>
    $(document).ready(function () {
        $('.flipButton').bind("click", function () {
            $(this).next().toggleClass('hover');
        })
    });

</script>
<br><br>
<?php
global $wpdb;
//$catalogs = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=0&exclude=4388');
$catalogs = get_terms(array('taxonomy' => 'product_cat',
    'hide_empty' => false,
    'parent' => 0,
    'orderby' => 'ID',
    'order' => 'ASC',
    'exclude' => 4388
        ));
$catalog_main_array = array();
foreach ($catalogs as $catalog) {
    $order = get_field('order', $catalog);
    $catalog_main_array[$order] = $catalog;
}
ksort($catalog_main_array, SORT_NUMERIC);
?>
<?php
echo (rand() . "<br>");
echo (rand() . "<br>");
echo (rand(10,100));
exit();
?>
<div class="our-products">
    <div class="container">
        <div class="row">
            <div class="col-md-9">
                <div class="myproducts3">
                    <div id="catalog_html">
                        <h2>
                            Hi<br>
                            What do you want to sell ?
                        </h2>
                        <div class="row">
                            <?php
                            $loop_count = '1';
                            foreach ($catalog_main_array as $allCatalog):

                                $thumbnail_id = get_woocommerce_term_meta($allCatalog->term_id, 'thumbnail_id', true);
                                $image = wp_get_attachment_url($thumbnail_id);
                                if ($loop_count == '1') {
                                    $active_class = 'active_catalog';
                                } else {
                                    $active_class = '';
                                }
                                ?>
                                <div class="col-xs-6 col-sm-4 col-md-2 myboxss">
                                    <a class="fetch_products" href="javascript:void(0)" onclick="showFirstSubCategories('<?= $allCatalog->slug; ?>');" >
                                        <div class="mypores <?= $active_class; ?>" id="<?= $allCatalog->slug; ?>">
                                            <img src="<?= $image; ?>" alt="<?= $allCatalog->slug; ?>">
                                            <p><?= $allCatalog->name; ?></p>
                                        </div>
                                    </a>
                                </div>


                                <?php
                                $loop_count++;

                            endforeach;
                            ?>

                            <?php
                            hierarchical_category_tree(0, 1);

                            function hierarchical_category_tree($allCatalog, $level) {

                                if ($allCatalog == 0) {
                                    $next = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $allCatalog);
                                } else {

                                    foreach ($allCatalog as $key => $val) :

                                        echo '<div class="sub_cat_boards level_' . $level . '" style="display:none;" id="term_category_' . $key . '" data-level="' . $level . '" >';
                                        $subnext = get_categories('hide_empty=0&taxonomy=product_cat&hierarchical=1&parent=' . $val);
                                        if ($subnext) :
                                            $hierarchical = array();
                                            echo ' <ul class="myBoards">';
                                            foreach ($subnext as $allCatalog) :
                                                ?>

                                                <li><a href="javascript:void(0)" onclick="showAllSubCategories('<?= $allCatalog->slug; ?>',<?=$level;?>);" data-id="<?= $allCatalog->term_id; ?>" data-slug="<?= $allCatalog->slug; ?>"><?= $allCatalog->name; ?></a></li>
                                                <?php
                                                $hierarchical[$allCatalog->slug] = $allCatalog->term_id;
                                            endforeach;
                                            echo '</ul>';
                                            $level++;
                                            hierarchical_category_tree($hierarchical, $level);

                                        endif;
                                        echo '</div>';
                                    endforeach;
                                }
                                if ($next) :
                                    foreach ($next as $allCatalog) :
                                        $hierarchical[$allCatalog->slug] = $allCatalog->term_id;
                                    endforeach;

                                    hierarchical_category_tree($hierarchical, $level);
                                endif;
                            }
                            ?>


                        </div>

                    </div>
                </div>

            </div>

            <div class="col-md-3">
                <div class="row">
                    <div class="col-sm-6 col-md-12">
                        <div class="rgtproducts">
                            <div class="col-md-12">
                                <h2>Product sheet </h2>
                            </div>
                            <div class="col-sm-12 col-md-12 Gordon">
                                <?php
                                $product = new WC_Product($object_id);
                                $brand = $product->get_attribute('pa_brand');

                                $all_terms = get_terms("pa_brand", 'orderby=name&hide_empty=0');
                                ?>
                                <select class="selectpicker" data-show-subtext="true" data-live-search="true" name="brand_product_sheet" onchange="selectSecondWindow()">

                                    <option value="0"><?php _e("Brand", "surfsnb"); ?></option>
                                    <?php
                                    if ($all_terms) {
                                        foreach ($all_terms as $term) {
                                            $selected = "";
                                            if ($brand == $term->name) {
                                                $selected = "selected";
                                            }
                                            ?>

                                            <option value=<?= esc_attr($term->slug); ?>><?= $term->name; ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-12 col-md-12 brands brands2 Gordon">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="exampleInputEmail" placeholder="Model">
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-12 Gordon">
                                <select class="selectpicker" data-show-subtext="true" name="year_product_sheet" onchange="selectSecondWindow()">
                                    <?php
                                    $product = new WC_Product($object_id);
                                    $years = $product->get_attribute('pa_years');

                                    $all_terms = get_terms("pa_years", 'orderby=name&hide_empty=0');
                                    ?>
                                    <option value="0"><?php _e("Year", "surfsnb"); ?></option>
                                    <?php
                                    if ($all_terms) {
                                        foreach ($all_terms as $term) {
                                            $selected = "";
                                            if ($years == $term->name) {
                                                $selected = "";
                                                $selected = "selected";
                                            }
                                            ?>
                                            <option value=<?= esc_attr($term->slug); ?>><?= $term->name; ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-12 text-right">
                                <a data-toggle="modal" href="#myModal33" data-target="#myModal33">I can't find my brand</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-12" id="third_window" style="display:none;">
                        <div class="rgtproducts alert alert-dismissible">
                            <div class="col-md-12">

                                <h2>About the size <span class="Skip"> <a href="#" class="btn close" data-dismiss="alert" aria-label="close">Skip</a></span> </h2>

                            </div>
                            <?php
                            global $wc_product_attributes;

                            // Array of defined attribute taxonomies
                            $attribute_taxonomies = wc_get_attribute_taxonomies();
                            foreach ($attribute_taxonomies as $tax) :
                                $hide_attributes_list = "6,26,28,10,34";
                                if (in_array($tax->attribute_id, explode(',', $hide_attributes_list)))
                                    continue;
                                $attribute_taxonomy_name = wc_attribute_taxonomy_name($tax->attribute_name);
                                $label = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
                                //echo 'Lala: ' . esc_attr( $attribute_taxonomy_name ) . '-' . esc_html( $label ) . '<br>';

                                $attribute_label = $label;
                                $attribute['name'] = $attribute_taxonomy_name;
                                $attribute['is_taxonomy'] = true;
                                $attribute['is_visible'] = true;
                                $attribute['is_variation'] = false;
                                $taxonomy = $attribute['name'];
                                $metabox_class = "taxonomy " . $attribute_taxonomy_name;
                                $position = 0;
                                $i++;
                                $product = new WC_Product($object_id);
                                $brand = $product->get_attribute($taxonomy);

                                $all_terms = get_terms($taxonomy, 'orderby=name&hide_empty=0');
                                ?>
                                <div class="col-sm-12 col-md-12 Gordon">
                                    <select class="selectpicker" data-show-subtext="true" data-live-search="true">
                                        <option><?php _e($attribute_label, "surfsnb"); ?></option>
                                        <?php
                                        if ($all_terms) {
                                            foreach ($all_terms as $term) {
                                                $selected = "";
                                                if ($brand == $term->name) {
                                                    $selected = "selected";
                                                }
                                                ?>
                                                <option value="<?= esc_attr($term->slug) ?>"><?= $term->name ?></option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                            <!--               <div class="col-sm-12 col-md-12 Gordon">
                                              <select class="selectpicker" data-show-subtext="true" data-live-search="true">
                                                 <option>Width</option>
                                                 <option>Bill Gordon</option>
                                                 <option>Elizabeth Warren</option>
                                                 <option>Mario Flores</option>
                                                 <option>Don Young</option>
                                                 <option disabled="disabled">Marvin Martinez</option>
                                              </select>
                                           </div>
                                           <div class="col-sm-12 col-md-12 Gordon">
                                              <select class="selectpicker" data-show-subtext="true" data-live-search="true">
                                                 <option>Lenght</option>
                                                 <option>Bill Gordon</option>
                                                 <option>Elizabeth Warren</option>
                                                 <option>Mario Flores</option>
                                                 <option>Don Young</option>
                                                 <option disabled="disabled">Marvin Martinez</option>
                                              </select>
                                           </div>-->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-sm-6 col-md-5">
                        <div class="buyback_storages">
                            <div class="flip-container">
                                <div id="buyback_flipper" class="flipper">
                                    <div class="flipper_front">
                                        <div class="rgtproducts">
                                            <div class="col-md-12">
                                                <h2>About the product condition <span class="Skip2 buyback_infoBulle pointer" onclick="hover_flip();">
                                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/dd.png">
                                                    </span> </h2>
                                            </div>
                                            <div class="col-md-8 Gordon">
                                                <?php
                                                $product = new WC_Product($object_id);
                                                $condition = $product->get_attribute('pa_condition');
                                                $all_terms = get_terms("pa_condition", 'orderby=id&hide_empty=0');
                                                ?>
                                                <select class="selectpicker" data-show-subtext="true">
                                                    <option><?php _e("Condition", "surfsnb"); ?></option>
                                                    <?php
                                                    if ($all_terms) {
                                                        foreach ($all_terms as $term) {

                                                            $selected = "";
                                                            if ($condition == $term->name) {
                                                                $selected = "selected";
                                                            }
                                                            ?>
                                                            <option value="<?= esc_attr($term->slug); ?>"><?= $term->name; ?></option>									   
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flipper_back">
                                        <div onclick="hover_unflip();" >
                                            <div class="rgtproducts">
                                                <div class="col-md-12 mywhat">
                                                    <h2>What is what? </h2>
                                                </div>
                                                <div class="col-md-12 Gordon">
                                                    <p><strong>New: </strong> The product is new. </p>
                                                    <p><strong>Very good condition: </strong> The product was never, or have some very small use traces.  </p>
                                                    <p><strong>Good condition:  </strong> The item has some few superficial use traces, but it isnâ€™t damaged. </p>
                                                    <p><strong>Decent condition: </strong>The item has been used regularly, therefore you can see some use traces. It has probably been repaired, but in a proper and professional way. It can be used as it is, with no imminent reparations needed </p>
                                                    <p><strong>Bad condition: </strong> he item has been used a lot, it is damaged but still usable. The equipment has been badly repaired.  </p>
                                                    <p><strong>Very bad condition: </strong> The item is damaged and  has to be repaired, or it will need to  get repaired <br>  very soon.  </p>
                                                    <div class="myequipment">
                                                        <a class="btn" onclick="hover_unflip();">I GOT IT</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4" id="fifth_window">
                        <div class="rgtproducts">
                            <div class="col-md-12">
                                <h2>what is your price ?</h2>
                            </div>
                            <div class="col-md-12 moinfos2 brands form-group">
                                <div class="col-md-7">
                                    <input type="text" class="form-control" id="exampleInputEmail" placeholder="Price â‚¬">
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
            <div class="col-md-12">
                <div class="buyback_storages2">
                    <div class="flip-container">
                        <div id="buyback_flipper_3" class="flipper">
                            <div class="flipper_front">
                                <div class="rgtproducts" id="equipment">
                                    <div class="col-md-12">
                                        <h2>Add photos from your equipment ( front, back, side, extra, damage, repairs...) <span class="Skip2 buyback_infoBulle pointer" onclick="hover_flip_2();"><img src="<?php echo get_stylesheet_directory_uri(); ?>/img/dd.png"></span></h2>
                                    </div>
                                    <div class="col-md-12 Gordon">
                                        <form class="myphotoes2">
                                            <input type="file" name="file-1[]" id="file-1" class="inputfile inputfile-1" data-multiple-caption="{count} files selected" multiple="">
                                            <label for="file-1">PICTURES<br><img src="<?php echo get_stylesheet_directory_uri(); ?>/img/photo2.png" alt="photo2q"></label>
          <!--                                  <label for="file-1">PICTURES<br><img src="surf.sellandbuy.online/wp-content/img/photo2.png" alt="photo2"></label>-->
          <!--                                 <label for="file-1">PICTURES<br><img src="/wp-content/img/photo2.png" alt="photo2"></label>-->
          <!--                                   <label for="file-1">PICTURES<br><img src="<?php echo get_template_directory_uri(); ?>/img/photo2.png" alt="photo2"></label>-->
                                        </form>
                                        <br>
                                    </div>
                                </div>
                            </div>
                            <div class="flipper_back">
                                <div onclick="hover_unflip_2();" >
                                    <div class="rgtproducts" id="portrait">
                                        <div class="Gordon">
                                            <div class="col-xs-3 col-sm-2 col-md-1 text-center">
                                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/producrs.png" alt="producrs">
                                            </div>
                                            <div class="col-xs-9 col-sm-6 col-md-7 attract33">
                                                <h2>Few tips to take good pics and attract your future buyers</h2>
                                                <div class="mydamage hidden-xs">
                                                    <p><strong>1 <span> - </span>Take pictures with good light and on a white background.</strong></p>
                                                    <p><strong>2 - Take portrait pictures</strong></p>
                                                    <p><strong>3 - Take pictures from the front, back, side, damage and repair.</strong></p>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 attract33 hidden-sm hidden-md hidden-lg">

                                                <div class="mydamage">
                                                    <p><strong>1 <span> - </span>Take pictures with good light and on a white background.</strong></p>
                                                    <p><strong>2 - Take portrait pictures</strong></p>
                                                    <p><strong>3 - Take pictures from the front, back, side, damage and repair.</strong></p>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-4 col-md-4">
                                                <div class="frant22">
                                                    <div class="frant2">
                                                        <h3>FRONT</h3>
                                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/camera.png" alt="camera">
                                                    </div>
                                                    <div class="frant3">
                                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/sub-prod2.png" alt="sub-prod2">
                                                    </div>
                                                </div>
                                                <div class="frant22">
                                                    <div class="frant2">
                                                        <h3>BACK</h3>
                                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/camera.png" alt="camera">
                                                    </div>
                                                    <div class="frant3">
                                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/sub-prod.png" alt="sub-prod">
                                                    </div>
                                                </div>
                                                <div class="frant22">
                                                    <div class="frant2">
                                                        <h3>SIDE</h3>
                                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/camera.png" alt="camera">
                                                    </div>
                                                    <div class="frant3">
                                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/sub-prod3.png" alt="sub-prod3">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 myequipment2 text-center">
                                                <a class="btn" onclick="hover_unflip_2();">Ready to <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/camera2.png" alt="camera2"></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-sm-6 col-md-6 moinfos3">
                        <button type="button" class="btn btn-lg btn-block btn-warning">Publish </button>
                    </div>
                    <div class="col-sm-6 col-md-6">
                        <div class="buyback_storages3">
                            <div class="flip-container">
                                <div id="buyback_flipper_16" class="flipper">
                                    <div class="flipper_front">
                                        <div class="moinfos3">
                                            <button type="button" class="btn btn-lg btn-block btn-warning"> more infos  </button>
                                            <span class="Skip2 buyback_infoBulle pointer" onclick="hover_flip_15();"><img src="<?php echo get_stylesheet_directory_uri(); ?>/img/dd2.png"></span>
                                        </div>
                                    </div>
                                    <div class="flipper_back">
                                        <div onclick="hover_unflip_15();" >
                                            <div class="rgtproducts" id="mypotential">

                                                <div class="col-md-12 Gordon">
                                                    <p>Increase your chances of selling your product by giving <br>more information to your potential buyer.</p>
                                                    <div id="myequipment4" class="myequipment4 text-right">
                                                        <a class="btn" onclick="hover_unflip_15();">OK</a>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-5 hidden-sm hidden-xs">
                <div class="rgtproducts alert alert-dismissible">
                    <div class="col-md-12">
                        <h2>Describe your product brieï¬‚y</h2>
                    </div>
                    <div class="col-md-12 Gordon">
                        <textarea  class="textAreaMultiline form-control" id="description" placeholder="How much time did you use your product ?&#10;&#10;Are you using you product  more on the sea or  on the lake ? &#10;&#10;What is included with your product?&#10;&#10;Are you radical or smooth ?"></textarea>
                        <div class="Skip disscuss text-right">

                            <a href="#" class="btn close" data-dismiss="alert" aria-label="close"> Skip</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="rgtproducts alert alert-dismissible">
                    <div class="col-md-12">
                        <h2>Tell us more details about the equipmentâ€™s condition</h2>
                    </div>
                    <div class="col-sm-4 col-md-4 Gordon">
                        <select class="selectpicker" data-show-subtext="true">
                            <option>Damage</option>
                            <option>Bill Gordon</option>
                            <option>Elizabeth Warren</option>
                            <option>Mario Flores</option>
                            <option>Don Young</option>
                            <option disabled="disabled">Marvin Martinez</option>
                        </select>
                    </div>
                    <div class="col-sm-4 col-md-4 Gordon">
                        <select class="selectpicker" data-show-subtext="true">
                            <option>Repair</option>
                            <option>Bill Gordon</option>
                            <option>Elizabeth Warren</option>
                            <option>Mario Flores</option>
                            <option>Don Young</option>
                            <option disabled="disabled">Marvin Martinez</option>
                        </select>
                    </div>
                    <div class="col-sm-4 col-md-4 Gordon">
                        <select class="selectpicker" data-show-subtext="true">
                            <option>Warranty</option>
                            <option>Bill Gordon</option>
                            <option>Elizabeth Warren</option>
                            <option>Mario Flores</option>
                            <option>Don Young</option>
                            <option disabled="disabled">Marvin Martinez</option>
                        </select>
                    </div>
                    <div class="col-md-12 Skip text-right">
                        <a href="#" class="btn close" data-dismiss="alert" aria-label="close">  Skip</a>
                    </div>
                </div>
                <div class="hidden-lg hidden-md">
                    <div class="rgtproducts alert alert-dismissible">
                        <div class="col-md-12">
                            <h2>Describe your product brieï¬‚y</h2>
                        </div>
                        <div class="col-md-12 Gordon">
                            <textarea  class="form-control" id="description" placeholder="How much time did you use your product ?&#10;&#10;Are you using you product  more on the sea or  on the &#10;lake ? &#10;&#10;What is included with your product?&#10;&#10;Are you radical or smooth ?"></textarea>
                            <div class="Skip disscuss text-right">

                                <a href="#" class="btn close" data-dismiss="alert" aria-label="close"> Skip</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="buyback_storages alert alert-dismissible">
                    <div class="flip-container">
                        <div id="buyback_flipper_17" class="flipper">
                            <div class="flipper_front">
                                <div class="rgtproducts">
                                    <div class="col-md-12">
                                        <h2>Is the price negotiable ? &nbsp;<span class="buyback_infoBulle pointer" onclick="hover_flip_16();"><img src="<?php echo get_stylesheet_directory_uri(); ?>/img/dd.png"></span> </h2>
                                    </div>
                                    <div class="col-md-12 brands Gordon">
                                        <div class="col-sm-6 col-md-5 moinfos2 form-group">
                                            <input type="text" class="form-control" id="exampleInputEmail" placeholder="Price â‚¬">
                                        </div>
                                        <div class="col-md-7 moinfos2 no-gutter form-group">
                                            <div class="Skip text-right">
                                                <a href="#" class="btn close" data-dismiss="alert" aria-label="close">  Skip</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flipper_back">
                                <div onclick="hover_unflip_16();" >
                                    <div class="rgtproducts">

                                        <div class="col-md-12 Gordon">
                                            <p>Choose the minimum amount people are allowed to offer you for your 
                                                product. Please fill this in to prevent nonsense biddings.    </p>
                                            <div class="myequipment3 text-center">
                                                <a class="btn" onclick="hover_unflip_16();">I GOT IT</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="buyback_storages">
                    <div class="flip-container">
                        <div id="buyback_flipper_18" class="flipper">
                            <div class="flipper_front">
                                <div class="rgtproducts" id="equipment">
                                    <div class="col-md-12">
                                        <h2>Shipping details  &nbsp;<span class="buyback_infoBulle pointer" onclick="hover_flip_17();"><img src="<?php echo get_stylesheet_directory_uri(); ?>/img/dd.png"></span> </h2>
                                    </div>
                                    <div class="col-md-12 Gordon">
                                        <div class="col-xs-6 col-md-3 moinfos2 form-group">
                                            <input type="text" class="form-control" id="exampleInputEmail" placeholder="Weight(kg)">
                                        </div>
                                        <div class="col-xs-6 col-md-3 moinfos2 form-group">
                                            <input type="text" class="form-control" id="exampleInputEmail" placeholder="Lenght(cm)">
                                        </div>
                                        <div class="col-xs-6 col-md-3 moinfos2 form-group">
                                            <input type="text" class="form-control" id="exampleInputEmail" placeholder="Width(cm)">
                                        </div>
                                        <div class="col-xs-6 col-md-3 moinfos2 form-group">
                                            <input type="text" class="form-control" id="exampleInputEmail" placeholder="Height(cm)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flipper_back">
                                <div onclick="hover_unflip_17();" >
                                    <div class="rgtproducts">

                                        <div class="col-md-12 Gordon">
                                            <p>By entering box dimmensions , we will provide to your future buyer  a quotation to ship your product with our different partners.  You will always have the possibilities to ï¬?nd your own shipment or to give the product directly in the buyerâ€™s hand.  </p>
                                            <p><span class="redtext">IMPORTANT! make sure you ï¬?ll in the good dimension and good weight. You could be charge 2 month later for a wrong size provided.</span></p>
                                            <div id="myequipment3" class="myequipment3 text-right">
                                                <a class="btn" onclick="hover_unflip_17();">I GOT IT</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


            </div>
            <div class="col-md-12 moinfos">
                <div class="col-md-12">
                    <button type="button" class="btn btn-lg btn-block btn-warning">Publish  </button>
                </div>
            </div>
        </div>
    </div>




    <div class="sheets modal fade" id="myModal33" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="Gordon modal-body text-center">
                    <img class="seaaaa" src="<?php echo get_stylesheet_directory_uri(); ?>/img/seaa.png" alt="seaa">
                    <h2>OUPS!</h2>
                    <h3>We do not yet have this brand in our database....</h3>
                    <p>Leave us your email: we will notify you as soon as the brand is added!</p>
                    <div class="row">
                        <div class="col-xs-12 col-md-8 col-md-offset-1 form-group myhere">
                            <input type="text" class="form-control"  placeholder="Write your brand here">
                        </div>  
                        <div class="col-xs-12 form-search col-md-10 col-md-offset-1 text-left">
                            <div class="input-append">
                                <input type="text" class="span2" placeholder="ex: Nina@surfsnb.com">
                                <button type="submit" class="btn btn-primary"><img src="<?php echo get_stylesheet_directory_uri(); ?>/img/kite.png" alt="kite"></button>

                            </div>
                        </div>
                        <br>
                    </div>

                </div>

            </div>

        </div>
    </div>

</div>
</div>
<br><br><br>
<script>
    function hover_flip() {
        document.getElementById('buyback_flipper').className += " flipper_flip";
    }

    function hover_unflip() {
        document.getElementById('buyback_flipper').className = document.getElementById('buyback_flipper').className.replace("flipper_flip");
    }

    function hover_flip_2() {
        document.getElementById('buyback_flipper_3').className += " flipper_flip";
    }

    function hover_unflip_2() {
        document.getElementById('buyback_flipper_3').className = document.getElementById('buyback_flipper_3').className.replace("flipper_flip");
    }

    function hover_flip_15() {
        document.getElementById('buyback_flipper_16').className = " flipper_flip";
    }

    function hover_unflip_15() {
        document.getElementById('buyback_flipper_16').className = document.getElementById('buyback_flipper_16').className.replace("flipper_flip");
    }

    function hover_flip_16() {
        document.getElementById('buyback_flipper_17').className = " flipper_flip";
    }

    function hover_unflip_16() {
        document.getElementById('buyback_flipper_17').className = document.getElementById('buyback_flipper_17').className.replace("flipper_flip");
    }
    function hover_flip_17() {
        document.getElementById('buyback_flipper_18').className = " flipper_flip";
    }

    function hover_unflip_17() {
        document.getElementById('buyback_flipper_18').className = document.getElementById('buyback_flipper_18').className.replace("flipper_flip");
    }



    /*
     * Sprycoop Solution Client Side Scriptiong Starts from here
     */

    /*
     * Function Name : showFirstSubCategories
     * Function Desc : This function is to toggle  the first subcategories
     * Author : Sprycoop Solutions Private Limited
     * Added On : November 17, 2018
     * Added By  : Team Lead
     */

    function showFirstSubCategories(term_id) {

        var level = jQuery("#term_category_" + term_id).attr('data-level');
        jQuery("#term_category_" + term_id).toggle();
        jQuery(".level_" + level).addClass("active");





    }
    function showAllSubCategories(term_id) {

        var level = jQuery("#term_category_" + term_id).attr('data-level');
        jQuery("#term_category_" + term_id).toggle();
        jQuery(".level_" + level).addClass("active");

    }

    /*
     * 
     * @param {type} Sprycoop Show Hide page windows
     * @returns {These scripts are for which window will show when previous window will selected}
     */
    function selectSecondWindow() {
        var brand_value = jQuery("select[name=brand_product_sheet]").val();
        var year_value = jQuery("select[name=year_product_sheet]").val();
        if (brand_value == '0' || year_value == '0') {
            jQuery("#third_window").fadeOut();
        } else {
            jQuery("#third_window").fadeIn("slow");
        }
    }


    /*
     * 
     * @param {type} Sprycoop Show Hide page windows Ends here
     * @returns {These scripts are for which window will show when previous window will selected}
     */

    /*
     * Sprycoop Solution Client Side Scriptiong Ends Here
     */



    /*
     * Products Windows Script starts from here
     */

    function showSizeWindow(category_name) {
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/dashstore-child/product_ajax.php",
            data: {"action": "showSizeWindow", "category_name": category_name},
            success: function (response) {

            }
        });
    }
    /*
     * Products Windows Script Ends here
     */
</script>
<?php get_footer(); ?>
      
