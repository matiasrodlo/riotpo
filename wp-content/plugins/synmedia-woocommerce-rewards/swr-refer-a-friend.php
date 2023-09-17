<?php

function swr_init_refer(){
	require(SWR_PATH.'/classes/class-wc-rewards-referafriend.php');
	require(SWR_PATH.'/classes/class-google.php');
}
add_action('swr_init', 'swr_init_refer');

function swr_refer_scripts(){
	wp_enqueue_script('swr_admin_refer_script', SWR_URL.'/assets/js/admin.refer.js', array('jquery'), '1.0');
}
add_action('swr_rewards_refer_a_friend_scripts', 'swr_refer_scripts');

function swr_add_refer_integration($rewards) {
	$rewards[] = 'WC_Refer_Friend';
	return($rewards);
}
add_filter('woocommerce_rewards', 'swr_add_refer_integration', 11);

function swr_init_enabled_refer(){

	global $swr_refer;
	
	$swr_refer = new WC_Refer_Friend();
	
	if( $swr_refer->is_enabled() ){
	
		$swr_refer->check_ref();
		
		add_shortcode('swr_refer_a_friend', 'get_swr_refer_a_friend');
		add_filter('woocommerce_checkout_update_order_meta', 'swr_raf_checkout_update_order_meta');
		add_action('user_register', 'swr_raf_user_register');
		add_action('woocommerce_order_status_completed', 'swr_raf_order_status_completed');
		
	}
	
}
add_action('swr_init_enabled', 'swr_init_enabled_refer');

function swr_raf_user_register($user_id){
	
	global $swr_refer;
	
	if( $swr_refer->is_enabled() ){
	
		$ref_user_id = $swr_refer->ref_user_id_exist();
		
		if( intval($ref_user_id) > 0 ){
			
			$swr_refer->add_reference( $user_id, $ref_user_id );
				
			$swr_refer->add_referenced_user( $ref_user_id, $user_id );
			
		}
		
	}
	
}


function swr_raf_checkout_update_order_meta( $order_id ){
	
	global $swr_refer;
	
	/* $swr_refer->check_order( $order_id ); */
	
}

function swr_raf_order_status_completed( $order_id ){
	
	global $swr_refer;
	
	$swr_refer->check_order( $order_id );
	
}

/**
 * Get the order tracking shortcode content.
 *
 * @access public
 * @param array $atts
 * @return string
 */
function get_swr_refer_a_friend( $atts ) {
	global $woocommerce;
	return $woocommerce->shortcode_wrapper('swr_refer_a_friend', $atts);
}

function swr_refer_a_friend($atts){
	global $woocommerce, $refer_message, $refer_option;
	
	$woocommerce->nocache();
	
	if(is_user_logged_in()){
		extract( shortcode_atts( array(
			'refer_message' => 'Place your first order with '.get_bloginfo("name").' and earn rewards!',
			'refer_option'	=> 1
		), $atts ) );
		
		global $post;

		if ( ! empty( $_POST ) && $refer_option == 1) {
	
			$woocommerce->verify_nonce( 'refer_a_friend' );
	
			$refer_emails 	= empty( $_POST['swr_refer_emails'] ) ? '' : esc_attr( $_POST['swr_refer_emails'] );
			$refer_message	= empty( $_POST['swr_refer_message'] ) ? '' : esc_attr( $_POST['swr_refer_message']);
			
			$refer_emails = explode(',', $refer_emails);
			
			if(count($refer_emails) > 0){
				foreach($refer_emails as $key=>&$email){
					$email = trim($email);
					if(!is_email($email)){
						unset($refer_emails[$key]);
					}
				}
			}
			
			$refer_emails = array_unique($refer_emails);
	
			if (count($refer_emails) <= 0) {
	
				echo '<p class="woocommerce_error">' . __('Please enter at least one valid email address', 'rewards') . '</p>';
	
			} else {
	
				print_r($refer_emails);
	
			}
	
		}
		
		include(SWR_PATH.'/templates/refer-a-friend.php');
	}else{
		woocommerce_get_template('myaccount/form-login.php');
	}
}

function swr_refer_create_pages(){
	woocommerce_create_page( esc_sql( _x('refer-a-friend', 'page_slug', 'rewards') ), 'swr_refer_a_friend', __('Refer a friend', 'rewards'), '<p>Here you can enter some of your friends email address to earn rewards when they place their first order!</p><h2>Option 1 - Enter your friends email addresses</h2>[swr_refer_a_friend refer_option="1" refer_message="Place your first order with '.get_bloginfo("name").' and earn rewards!"]<h2>Option 2 - Share via social networks</h2><p>When you share our website with your social friends, a custom link will be created to track your friend.</p> [swr_refer_a_friend refer_option="2"]<h2>Option 3 - Personal link</h2><p>Copy, paste, give, send, print, forward this link to your friends!</p>[swr_refer_a_friend refer_option="3"]<h2>Option 4 - QR code</h2>[swr_refer_a_friend refer_option="4"]', woocommerce_get_page_id('myaccount') );
}
add_action('swr_create_pages', 'swr_refer_create_pages');
	
?>