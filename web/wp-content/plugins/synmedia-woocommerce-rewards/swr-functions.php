<?php
	
function swr_get_cart_rewards(){
	global $swr_settings;
	return($swr_settings->get_cart_reward());
}

function swr_get_user_current_rewards($settings = array()){
	global $swr_settings;
	return($swr_settings->get_user_rewards($settings));
}

function ajax_swr_update_payment_method(){
	global $woocommerce;
	
	$ret = array(
		'rewards' => ''
	);

	check_ajax_referer( 'update-order-review', 'security' );
	
	$ret['rewards'] = swr_get_text_rewards();
	
	echo(json_encode($ret));
	
	die();
}
add_action('wp_ajax_swr_update_payment_method', 'ajax_swr_update_payment_method');
add_action('wp_ajax_nopriv_swr_update_payment_method', 'ajax_swr_update_payment_method');

?>