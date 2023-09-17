<?php

function swr_init_updates(){
	require(SWR_PATH.'/classes/class-wc-rewards-update.php');
}
add_action('swr_init', 'swr_init_updates');

function swr_add_updates_integration($rewards) {
	$rewards[] = 'WC_Rewards_Updates';
	return($rewards);
}
add_filter('woocommerce_rewards', 'swr_add_updates_integration', 11);

function swr_update_init(){
	global $woocommerce;
	if ( ! class_exists( 'SYN_Auto_Update' ) )
		require_once( 'syn-updates/syn_auto_update.php' );
	$syn_plugin_item = '2588711';
	$syn_plugin_licence = $woocommerce->rewards->rewards['rewards_update']->get_licence();
	$plugin_data = get_plugin_data(__FILE__);
	$syn_current_version = $plugin_data['Version'];
	$syn_update = new SYN_Auto_Update($syn_current_version, SWR_SLUG, $syn_plugin_item, $syn_plugin_licence);
}
add_action('admin_init', 'swr_update_init', 11);
	
?>