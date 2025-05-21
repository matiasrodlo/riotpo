<?php

function swr_user_register($user_id){
	global $swr_settings;
	
	if( $swr_settings->subscribe_enabled() ){
		
		$swr_settings->set_user_rewards( $user_id, $swr_settings->get_subscribe_reward() );
		
	}
	
}
add_action('user_register', 'swr_user_register');
	
?>