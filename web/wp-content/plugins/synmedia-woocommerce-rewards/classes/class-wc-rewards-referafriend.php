<?php
/**
 * Refer a friend
 * 
 * Allows tracking code to be inserted into store pages.
 *
 * @class 		WC_Refer_Friend
 * @package		WooCommerce
 * @category	Reward
 * @author		WooThemes
 */
class WC_Refer_Friend extends WC_Reward {
		
	public function __construct() { 
        $this->id					= 'rewards_refer_a_friend';
        $this->method_title     	= __('Refer a friend', 'woocommerce');
        $this->method_description	= __('Refer a friend is a system of referral for the client. It give the client a way to email is friend about your website with a unique link to track the friend\'s order', 'woocommerce');
		
		// Load the form fields.
		$this->init_form_fields();
		
		// Load the settings.
		$this->init_settings();

		// Define user set variables
		$this->swr_raf = $this->settings['swr_raf'];
		$this->swr_raf_refer_min_order_amount = isset( $this->settings['swr_raf_refer_min_order_amount'] ) ? $this->settings['swr_raf_refer_min_order_amount'] : 0;
		
		$this->swr_raf_cookie_expiration = isset( $this->settings['swr_raf_cookie_expiration'] ) ? $this->settings['swr_raf_cookie_expiration'] : 360;
		$this->swr_raf_referer_order = isset( $this->settings['swr_raf_referer_order'] ) ? $this->settings['swr_raf_referer_order'] : 'firstorder';
		$this->swr_raf_friend_order = isset( $this->settings['swr_raf_friend_order'] ) ? $this->settings['swr_raf_friend_order'] : 'firstorder';
		$this->swr_raf_referer_rewards = isset( $this->settings['swr_raf_referer_rewards'] ) ? $this->settings['swr_raf_referer_rewards'] : 0;
		$this->swr_raf_friend_rewards = isset( $this->settings['swr_raf_friend_rewards'] ) ? $this->settings['swr_raf_friend_rewards'] : 0;
		
		// Actions
		add_action( 'woocommerce_update_options_rewards_rewards_refer_a_friend', array( &$this, 'process_admin_options') );
    } 
    
	/**
     * Initialise Settings Form Fields
     */
    function init_form_fields() {
    
    	$this->form_fields = array( 
			'swr_raf' => array(  
				'title' => __('Refer a friend', 'woocommerce'),
				'label' => __('Enable Refer a friend', 'woocommerce'),
				'type' => 'checkbox',
				'default' => 'no'
			),
			'swr_raf_refer_min_order_amount' => array(
				'title' => __('Minimum Order Amount', 'wc_rewards'),
				'id' 		=> 'swr_raf_refer_min_order_amount',
				'css' 		=> 'width:50px;',
				'type' 		=> 'text',
				'tip'	=>  __('Minimum order amount to activate the rewards. Ex.: 100', 'wc_rewards')
			),
			'swr_raf_referer_order' => array(
				'title' => __('Client apply to', 'wc_rewards'),
				'id' 		=> 'swr_raf_referer_order',
				'tip'	=>  __('Select if a rewards should be given to the first order, any order but just one time or any order that the friend make.', 'wc_rewards'),
				'css' => 'width:250px;',
				'type' => 'select',
				'class' => 'chosen_select',
				'options' => array(
					'firstorder'	=> __('First order only', 'wc_rewards'),
					'oneorder'		=> __('One order but not exclusively the first', 'wc_rewards'),
					'anyorder' 		=> __('Any order that meet the min. order amount.', 'wc_rewards'),
				),
				'default' => 'firstorder'
			),
			'swr_raf_referer_rewards' => array(
				'title' => __('Referer rewards', 'wc_rewards'),
				'id' 		=> 'swr_raf_referer_rewards',
				'css' 		=> 'width:50px;',
				'type' 		=> 'text',
				'tip'	=>  __('Enter the referer rewards.<br />3 possibilities: X%, X/Y, X.<br /><br />Ex.: 1% of the order, 1/20 1 rewards per each 20$ purchase, 2 rewards each time.', 'wc_rewards')
			),
			'swr_raf_friend_order' => array(
				'title' => __('Friend apply to', 'wc_rewards'),
				'id' 		=> 'swr_raf_friend_order',
				'tip'	=>  __('Select if a rewards should be given to the first order, any order but just one time or any order that the friend make.', 'wc_rewards'),
				'css' => 'width:250px;',
				'type' => 'select',
				'class' => 'chosen_select',
				'options' => array(
					'firstorder'	=> __('First order only', 'wc_rewards'),
					'oneorder'		=> __('One order but not exclusively the first', 'wc_rewards'),
					'anyorder' 		=> __('Any order that meet the min. order amount.', 'wc_rewards'),
				),
				'default' => 'firstorder'
			),
			'swr_raf_friend_rewards' => array(
				'title' => __('Friend rewards', 'wc_rewards'),
				'id' 		=> 'swr_raf_friend_rewards',
				'css' 		=> 'width:50px;',
				'type' 		=> 'text',
				'tip'	=>  __('Enter the friend rewards.<br />3 possibilities: X%, X/Y, X.<br /><br />Ex.: 1% of the order, 1/20 1 rewards per each 20$ purchase, 2 rewards each time.', 'wc_rewards')
			)
		);
		
    } // End init_form_fields()
    
    public function is_enabled(){
	    return($this->swr_raf=='yes'?true:false);
    }
    
    public function get_cookie_expiration(){
	    return($this->swr_raf_cookie_expiration);
    }
    
    public function get_referer_rewards(){
	    
	    return $this->swr_raf_referer_rewards;
	    
    }
    
    public function get_friend_rewards(){
	    
	    return $this->swr_raf_friend_rewards;
	    
    }
    
    public function get_raf_order(){
	    return $this->swr_raf_referer_order;
    }
    
    public function get_raf_friend_order(){
	    return $this->swr_raf_friend_order;
    }
    
    public function check_ref(){
	    global $woocommerce;
	    
	    if( isset($_GET['ref']) && !empty($_GET['ref']) ){
		
			$ref_user_id = $_GET['ref'];
			
			$refs = get_option( 'rewards_references', array() );
			
			$session_id = session_id();
			if( empty($session_id) )
				session_start();			
			$session_id = session_id();
			
			if( !isset($refs['session']) )
				$refs['session'] = array();
			
			$refs['session'][$session_id] = $ref_user_id;
			
			if( !isset($refs['ip']) )
				$refs['ip'] = array();
				
			$refs['ip'][$_SERVER['REMOTE_ADDR']] = $ref_user_id;
			
			update_option( 'rewards_references', $refs );
			
			setcookie("RREF", $ref_user_id, time()+(3600 * 24 * $this->get_cookie_expiration()));
			
		}
    }
    
    public function ref_user_id_exist(){
	    global $woocommerce;
	    
	    $refs = get_option( 'rewards_references', array() );
	    
	    $session_id = session_id();
		if( empty($session_id) )
			session_start();			
		$session_id = session_id();
	    
	    if( isset($_COOKIE['RREF']) && !empty($_COOKIE['RREF']) ){
	    
		    return intval($_COOKIE['RREF']);
		    
	    }else if( isset($refs['session'][$session_id]) && !empty($refs['session'][$session_id]) ){
		    
		    return intval($refs['session'][$session_id]);
		    
	    }else if( isset($refs['ip'][$_SERVER['REMOTE_ADDR']]) && !empty($refs['ip'][$_SERVER['REMOTE_ADDR']]) ){
		    
		    return intval($refs['ip'][$_SERVER['REMOTE_ADDR']]);
		    
	    }else{
		    
		    return false;
		    
	    }
	    
    }
    
    public function get_raf_orders( $ref_user_id ){
    
    	$orders = array();
	    
	    $refs = $this->get_raf_users( $ref_user_id );
	    
	    if( $refs && !empty($refs) ){
		    
		    foreach($refs as $ref){
			    
			    if( !empty($ref['orders']) ){
			    
			    	foreach( $ref['orders']as $order_id=>$torder ){
				    	
					    $order = new WC_Order( $order_id );
					    $user_info = get_userdata( $order->user_id );
					    
					    $sorder = array(
					    	'date' => $order->order_date,
					    	'status' => $torder['status'],
					    	'rewards' => $torder['rewards'],
					    	'username' => $user_info->display_name
					    );
					    
					    $orders[] = $sorder;
				    	
			    	}
				    
			    }
			    
		    }
		    
	    }
	    
	    return $orders;
	    
    }
    
    public function get_raf_users( $ref_user_id ){
	    
	    return get_user_meta( $ref_user_id, 'swr_raf_users', true );
	    
    }
    
    public function add_reference( $user_id, $ref_user_id ){
	    
	    update_user_meta( $user_id, 'swr_raf_user_id', $ref_user_id );
	    
    }
    
    public function get_referenced_user( $user_id ){
	    
	    return get_user_meta( $user_id, 'swr_raf_user_id', true );
	    
    }
    
    public function add_referenced_user( $ref_user_id, $user_id ){
	    
	    $refs = $this->get_raf_users( $ref_user_id );
			
		if( !$refs )
			$refs = array();
		
		$reference = array(
			'user_id'			=> $user_id,
			'orders'			=> array()
		);
		
		$refs[$user_id] = $reference;
		
		update_user_meta( $ref_user_id, 'swr_raf_users', $refs );
	    
    }
    
    public function get_nb_order( $user_id ){

	    $customer_orders = get_posts(array(
		    'numberposts'     => '-1',
		    'meta_key'        => '_customer_user',
		    'meta_value'	  => $user_id,
		    'post_type'       => 'shop_order',
		    'post_status'     => 'publish' 
		));
		
		return $customer_orders ? count($customer_orders) : 0;
	    
    }
    
    public function check_order( $order_id ){
    
    	global $swr_settings;
	    
	    $order = new WC_Order($order_id);
	    
	    $ref_user_id = $this->get_referenced_user( $order->user_id );
	    
	    if( $ref_user_id ){
	    
	    	$debug .= 'Pass ref_user_id: '.$ref_user_id.'\r\n';
		    
		    $refs = $this->get_raf_users( $ref_user_id );
		    
		    $raf_order = $this->get_raf_order();
		    
		    $raf_friend_order = $this->get_raf_friend_order();
		    
		    $nb_order = $this->get_nb_order( $order->user_id );
		    
		    $order_amount = $swr_settings->get_order_amount( $order );
		    
		    $terms = wp_get_object_terms( $order->id, 'shop_order_status', array('fields' => 'slugs') );
		    $status = (isset($terms[0])) ? $terms[0] : 'pending';
		    
		    $debug .= "$raf_order;;$raf_friend_order;;$nb_order;;$order_amount;;$status".'\r\n';
		    
		    if(( ($raf_order == 'firstorder' && $nb_order <= '1') || ($raf_order == 'oneorder' && empty($refs[$order->user_id]['orders'])) || $raf_order == 'anyorder') && $order_amount >= $this->swr_raf_refer_min_order_amount ){
			    
			    $debug .= 'Enter\r\n';
			    
			    $ref_order = array(
			    	'order_id' => $order_id,
			    	'status' => ($status=='completed'?2:1),
			    	'rewards' => 0
			    );
			    
			    $rewards_factor = $this->get_referer_rewards();
			    $ref_order['rewards'] = $this->calculate_rewards( $rewards_factor, $order_amount );
			    
			    $refs[$order->user_id]['orders'][$order_id] = $ref_order;
			    
			    update_user_meta( $ref_user_id, 'swr_raf_users', $refs );
			    
			    $swr_settings->add_user_rewards( $ref_user_id, $ref_order['rewards'] );
			    
		    }
		    
		    if(( ($raf_friend_order == 'firstorder' && $raf_friend_order <= '1') || ($raf_friend_order == 'oneorder' && empty($refs[$order->user_id]['orders'])) || $raf_friend_order == 'anyorder') && $order_amount >= $this->swr_raf_refer_min_order_amount ){
			    
			    $rewards_factor = $this->get_friend_rewards();
			    $friend_rewards = $this->calculate_rewards( $rewards_factor, $order_amount );
			    
			    $swr_settings->add_user_rewards( $order->user_id, $friend_rewards );
			    
		    }
		    
	    }
	    
	    mail( 'gpl@synmedia.ca', 'Rewards', $debug );
	    
    }
    
    public function calculate_rewards( $factor, $amount ){
    	global $swr_settings;
	    
	    $rewards = 0;
			
		if( strpos($factor, '%') !== false ){
		
			$rewards = ( ( intval( $factor ) / 100 ) * $amount );
			
			if($swr_settings->get_rewards_fraction_type() == 'loosefloor' || $swr_settings->get_rewards_fraction_type() == 'strict')
				$rewards = floor($rewards);
			
		}elseif( strpos($factor, '/') !== false ){
		
			$fraction = $swr_settings->separate_fraction($factor);
			
			$rewards = $amount / $fraction['dollars'];
			
			if( $swr_settings->get_rewards_type() == 'points' ){
			
				if($swr_settings->get_rewards_fraction_type() == 'strict')
					$rewards = floor($rewards);
					
				$rewards = $rewards * $fraction['points'];
				
				if($swr_settings->get_rewards_fraction_type() == 'loosefloor')
					$rewards = floor($rewards);
					
			}
			
		}else{
			
			$rewards = $factor;
			
		}
		
		return $rewards;
	    
    }
    
}