<?php
/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

global $product, $woocommerce_loop;

$related = $product->get_related(12);

if ( sizeof($related) == 0 ) return;

$args = apply_filters('woocommerce_related_products_args', array(
	'post_type'				=> 'product',
	'ignore_sticky_posts'	=> 1,
	'no_found_rows' 		=> 1,
	'posts_per_page' 		=> $posts_per_page,
	'orderby' 				=> $orderby,
	'post__in' 				=> $related
) );

$products = new WP_Query( $args );

$woocommerce_loop['columns'] 	= $columns;

if ( $products->have_posts() ) : ?>

	<?php $sliderrandomid = rand() ?>
    
    <script>
	jQuery(document).ready(function($) {
		/* items_slider */
		$('.gbtr_items_slider_id_<?php echo $sliderrandomid ?> .gbtr_items_slider').iosSlider({
			snapToChildren: true,
			desktopClickDrag: true,
			scrollbar: true,
			scrollbarHide: true,
			scrollbarLocation: 'bottom',
			scrollbarHeight: '2px',
			scrollbarBackground: '#ccc',
			scrollbarBorder: '0',
			scrollbarMargin: '0',
			scrollbarOpacity: '1',
			navNextSelector: $('.gbtr_items_slider_id_<?php echo $sliderrandomid ?> .gbtr_items_sliders_nav .big_arrow_right'),
			navPrevSelector: $('.gbtr_items_slider_id_<?php echo $sliderrandomid ?> .gbtr_items_sliders_nav .big_arrow_left'),
			onSliderLoaded: gbtr_items_slider_UpdateSliderHeight,
			onSlideChange: gbtr_items_slider_UpdateSliderHeight,
			onSliderResize: gbtr_items_slider_UpdateSliderHeight
		});
		
		function gbtr_items_slider_UpdateSliderHeight(args) {
                                
			currentSlide = args.currentSlideNumber;
			
			/* update height of the first slider */

			setTimeout(function() {
				var setHeight = $('.gbtr_items_slider_id_<?php echo $sliderrandomid ?> .gbtr_items_slider .product_item:eq(' + (args.currentSlideNumber-1) + ')').outerHeight(true);
				$('.gbtr_items_slider_id_<?php echo $sliderrandomid ?> .gbtr_items_slider').animate({ height: setHeight+20 }, 300);
			},300);
			
		}
	});
	</script>
    
    <div class="grid_12">
    
        <div class="gbtr_items_slider_id_<?php echo $sliderrandomid ?>">
            
            <div class="gbtr_items_sliders_header">
                <div class="gbtr_items_sliders_title">
                    <div class="gbtr_featured_section_title"><strong><?php _e('Related Products', 'theretailer'); ?></strong></div>
                </div>
                <div class="gbtr_items_sliders_nav">                        
                    <a class='big_arrow_right'></a>
                    <a class='big_arrow_left'></a>
                    <div class='clr'></div>
                </div>
            </div>
            
            <div class="gbtr_bold_sep"></div>   
        
            <div class="gbtr_items_slider_wrapper">
                <div class="gbtr_items_slider">
                    <ul class="slider">
                        <?php while ( $products->have_posts() ) : $products->the_post(); ?>
        
                            <?php woocommerce_get_template_part( 'content', 'product' ); ?>
            
                        <?php endwhile; // end of the loop. ?>
                    </ul>     
                </div>
            </div>
        
        </div>
    
    </div>

<?php endif;

wp_reset_postdata();
