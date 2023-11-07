<?php
	
function swr_settings_scripts(){
	wp_enqueue_script('swr_admin_settings_script', SWR_URL.'/assets/js/admin.settings.js', array('jquery'), '1.0');
}
add_action('swr_rewards_settings_scripts', 'swr_settings_scripts');

function swr_add_settings_integration($rewards) {
	$rewards[] = 'WC_Rewards_Settings';
	return($rewards);
}
add_filter('woocommerce_rewards', 'swr_add_settings_integration');

function swr_settings_create_pages(){
	woocommerce_create_page( esc_sql( _x('view-rewards', 'page_slug', 'rewards') ), 'swr_view_rewards', __('View Rewards', 'rewards'), '[swr_view_rewards]', woocommerce_get_page_id('myaccount') );
}
add_action('swr_create_pages', 'swr_settings_create_pages');
	
?>