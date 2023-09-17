<?php

function swr_type_plugins_loaded(){
	
	// Allows the selection of the 'bundled product' type
	add_filter( 'product_type_selector', 'swr_product_type_selector' );
	
	// Creates the admin panel tab 'Rewards certificate'
	add_action( 'woocommerce_product_write_panel_tabs', 'swr_product_write_panel_tabs' );
	
}
add_action( 'plugins_loaded', 'swr_type_plugins_loaded' );


/**
 * Add Rewards certificate write panel tab
 **/
function swr_product_write_panel_tabs() {
	echo '<li class="rewards_certificate_tab show_if_reward related_product_options linked_product_options"><a href="#certificate_product_data">'.__('Rewards certificate', 'wc_rewards').'</a></li>';
}

/**
 * Add 'rewards' type to the menu
 **/
function swr_product_type_selector( $options ) {
	$options['rewards'] = __('Rewards certificate', 'wc_rewards');
	return $options;
}

?>