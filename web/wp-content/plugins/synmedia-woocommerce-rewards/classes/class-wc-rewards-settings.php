<?php
/**
 * Rewards settings
 * 
 * Allows tracking code to be inserted into store pages.
 *
 * @class 		WC_Rewards_Settings
 * @package		WooCommerce
 * @category	Integrations
 * @author		WooThemes
 */

class WC_Rewards_Settings extends WC_Reward {
	
	public $errors_msg;
		
	public function __construct() {
	
		global $wp_roles;
		
		$this->roles = array();
		
		foreach( $wp_roles->roles as $role ){
			$this->roles[$role['name']] = $role['name'];
		}
	
		$this->errors_msg			= array();
		$this->version				= '2.0.6.0';
        $this->id					= 'rewards_settings';
        $this->pages_created		= get_option('swr_pages_installed');
        $this->method_title     	= __('Rewards Options', 'woocommerce');
        $this->method_description	= __('Rewards offer your client the possibility of earning credit to your store. ', 'woocommerce').'<br />'.($this->pages_created?__('Pages have already been created, but if you want to create them anyway <a href="admin.php?page='.(isset($_GET['page'])?$_GET['page']:'').'&tab='.(isset($_GET['tab'])?$_GET['tab']:'').'&install_swr_pages=1">click here</a>.', 'woocommerce'):__('To create the page "My rewards" into the site, <a href="admin.php?page='.(isset($_GET['page'])?$_GET['page']:'').'&tab='.(isset($_GET['tab'])?$_GET['tab']:'').'&install_swr_pages=1">click here</a>.', 'woocommerce')).'<br />'.__('To give rewards to older orders based on your current settings <a href="admin.php?page='.(isset($_GET['page'])?$_GET['page']:'').'&tab='.(isset($_GET['tab'])?$_GET['tab']:'').'&give_older_orders_rewards=1">click here</a>.', 'woocommerce').((isset($_GET['install_swr_pages']) && $_GET['install_swr_pages']==1)?'<div id="message" class="updated fade"><p><strong>'.__('Pages have been created!', 'woocommerce').'</strong></p></div>':'').((isset($_GET['give_older_orders_rewards']) && $_GET['give_older_orders_rewards']==1)?'<div id="message" class="updated fade"><p><strong>'.__('Rewards have been given to older review!', 'woocommerce').'</strong></p></div>':'');
		
		// Load the form fields.
		$this->init_form_fields();
		
		// Load the settings.
		$this->init_settings();
		
		$this->swr_roles			= isset( $this->settings['swr_roles'] ) ? $this->settings['swr_roles'] : $this->roles;
		
		$this->rewards_page = isset($this->settings['rewards_page']) ? $this->settings['rewards_page'] : '';
		$this->swr_apply_rewards_to_shipping = isset( $this->settings['swr_apply_rewards_to_shipping'] ) ? ($this->settings['swr_apply_rewards_to_shipping'] == 'yes' ? true : false) : true;
		$this->payments		= isset( $this->settings['payments'] ) ? $this->settings['payments'] : array();
		
		$c_version = get_option('swr_rewards_version');
		
		if(str_replace('.','',$c_version) < str_replace('.', '', $this->version)){
			
			update_option('swr_rewards_version', $this->version);
		}
		
		$this->table_values = isset( $this->settings['table_values'] ) ? $this->settings['table_values'] : array();
		
		// Actions
		add_action( 'woocommerce_update_options_rewards_rewards_settings', array( &$this, 'process_admin_options') );
    }
    
    public function get_current_user_role() {
		global $wp_roles;
		$current_user = wp_get_current_user();
		$roles = $current_user->roles;
		$role = array_shift($roles);
		return isset($wp_roles->role_names[$role]) ? translate_user_role($wp_roles->role_names[$role] ) : false;
	}
	
	public function current_user_can_use_rewards(){
		
		$role = $this->get_current_user_role();
		
		return array_search( $role, $this->swr_roles ) === false ? false : true;
		
	}
    
    public function is_enabled(){
	    return($this->settings['swr_enable_rewards_system']=='yes'?true:false);
    }
    
    public function show_top_cart(){
    	return(array_search('topofcart', $this->settings['swr_cart_where_to_show'])===false?false:true);
    }
    
    public function show_below_cart_table(){
    	return(array_search('below_cart_table', $this->settings['swr_cart_where_to_show'])===false?false:true);
    }
    
    public function show_below_cart_totals(){
    	return(array_search('below_cart_totals', $this->settings['swr_cart_where_to_show'])===false?false:true);
    }
    
    public function show_below_shipping_calculator(){
    	return(array_search('below_shipping_calculator', $this->settings['swr_cart_where_to_show'])===false?false:true);
    }
    
    public function show_before_order(){
    	return(array_search('before_order', $this->settings['swr_cart_where_to_show'])===false?false:true);
    }
    
    public function show_below_order(){
    	return(array_search('below_order', $this->settings['swr_cart_where_to_show'])===false?false:true);
    }
    
    public function show_below_order_thankyou(){
	    return(array_search('below_order_thankyou', $this->settings['swr_cart_where_to_show'])===false?false:true);
    }
    
    public function show_top_rewards(){
	    return(array_search('top_rewards', $this->settings['swr_cart_where_to_show'])===false?false:true);
    }
    
    public function show_below_product_price(){
	    return(array_search('below_product_price', $this->settings['swr_cart_where_to_show'])===false?false:true);
    }
    
	/**
     * Initialise Settings Form Fields
     */
    function init_form_fields() {
    
    	$this->form_fields = array( 
			'swr_enable_rewards_system' => array(
				'title' 			=> __('Rewards system', 'woocommerce'),
				'label' 			=> __('Enable the Rewards system', 'woocommerce'),
				'type' 				=> 'checkbox',
				'default' 			=> 'no'
			),
			'swr_rewards_title' => array(
				'title' 	=> __('Rewards title', 'woocommerce'),
				'id' 		=> 'swr_rewards_title',
				'type' 		=> 'text',
				'tip'		=> __("The title to appear throughout your website. Ex.: Website's rewards", 'woocommerce')
			),
			'swr_rewards_type' => array(
				'title' 			=> __('Rewards type', 'woocommerce'),
				'id' 		=> 'swr_rewards_type',
				'tip'		=> __("The type of rewards you want to use. Money or points", 'woocommerce'),
				'css' => 'width:150px;',
				'type' => 'select',
				'class' => 'chosen_select',
				'options' => array(
					'money'	=> __('Money', 'woocommerce'),
					'points' => __('Points', 'woocommerce')
				),
				'default' => 'money'
			),
			'swr_rewards_calculation' => array(
				'title' 			=> __('Calculation type', 'woocommerce'),
				'id' 		=> 'swr_rewards_calculation',
				'tip'	=>  sprintf(__("How to calculate rewards.<br /><br />Percentage and point per currency: Will give a customer X%% of the order and calculate points based on how much a dollar is worth in points. Ex.: 3%% will give %s for an order of %s.<br /><br />Fraction and point value: Will give a customer X points per X amount spend and calculate value of points based on how much a point is worth. Ex.: 2/10 will give 2 points per %s.", 'woocommerce'), swr_clean_amount(woocommerce_price(0.45)), swr_clean_amount(woocommerce_price(15)), swr_clean_amount(woocommerce_price(10))),
				'css' => 'width:250px;',
				'type' => 'select',
				'class' => 'chosen_select',
				'options' => array(
					'percentage'	=> __('Percentage', 'woocommerce'),
					'fraction' => __('Fraction', 'woocommerce')
				),
				'default' => 'percentage'
			),
			'swr_rewards_percentage' => array(
				'title' => __('Percentage', 'woocommerce'),
				'description' => sprintf(__('Will give <span id="swr_percentage_value_enter">X</span> for an order of %s','woocommerce'), swr_clean_amount(woocommerce_price(10))),
				'id' 		=> 'swr_rewards_percentage',
				'css' 		=> 'width:40px;',
				'type' 		=> 'text',
				'tip'	=>  __('Percentage of rewards for each order. Ex.: 3%. You can add decimal too. Ex.: 2.75%', 'woocommerce')
			),
			'swr_rewards_fraction' => array(
				'title' => __('Fraction', 'woocommerce'),
				'description' => sprintf(__(' Ex.: 1/10 will give 1 point for each %s spend, 3/20 will give 3 points for each %s spend.', 'woocommerce'), woocommerce_price(10), woocommerce_price(20)),
				'id' 		=> 'swr_rewards_fraction',
				'css' 		=> 'width:40px;',
				'type' 		=> 'text',
				'desc_tip'	=>  true
			),
			'swr_rewards_fraction_type' => array(
				'title' 			=> __('Fraction type', 'woocommerce'),
				'id' 		=> 'swr_rewards_fraction_type',
				'tip'	=>  sprintf(__("The fraction type determine if a user should get partial points. Ex.: An order of %s with a fraction of 2/10 will give the customer 3.6 points in Loose, 3 in Loose with floor rounding, 2 in Strict.", 'woocommerce'), swr_clean_amount(woocommerce_price(18))),
				'css' => 'width:250px;',
				'type' => 'select',
				'class' => 'chosen_select',
				'options' => array(
					'loose'	=> __('Loose', 'woocommerce'),
					'loosefloor'	=> __('Loose with floor rounding', 'woocommerce'),
					'strict' => __('Strict', 'woocommerce')
				),
				'default' => 'loosefloor'
			),
			'swr_rewards_used_type' => array(
				'title' 			=> __('How to use the rewards', 'woocommerce'),
				'id' 		=> 'swr_rewards_used_type',
				'tip'	=>  sprintf(__("Determine how the customer will use his rewards.<br /><br />Points value:<br />Will give the customer a choice to redeem his rewards has money in the cart.<br /><br />Specific product:<br />Only specified product can be bought with their points, you must enter a value in points for each product.", 'woocommerce'), swr_clean_amount(woocommerce_price(18))),
				'css' => 'width:250px;',
				'type' => 'select',
				'class' => 'chosen_select',
				'options' => array(
					'pointsvalue'	=> __('Points value', 'woocommerce'),
					'table'			=> __('Table of predefined points value', 'woocommerce')
				),
				'default' => 'pointsvalue'
			),
			'swr_rewards_points' => array(
				'title' => __('Points per currency', 'woocommerce'),
				'description' => sprintf(__('points = %s (Only used in Points)', 'woocommerce'), woocommerce_price(1)),
				'id' 		=> 'swr_rewards_points',
				'css' 		=> 'width:40px;',
				'type' 		=> 'text'
			),
			'swr_rewards_points_value' => array(
				'title' => __('Points value', 'woocommerce'),
				'description' => sprintf(__('= 1 point', 'woocommerce'), woocommerce_price(1)),
				'id' 		=> 'swr_rewards_points_value',
				'css' 		=> 'width:40px;',
				'type' 		=> 'text'
			),
			'swr_rewards_min_points' => array(
				'title' => __('Minimum points', 'woocommerce'),
				'id' 		=> 'swr_rewards_min_points',
				'css' 		=> 'width:40px;',
				'type' 		=> 'text',
				'tip'	=>  __('Minimum points to be able to cash in. Ex.: 15 points minimum to let the customer use is points. Leave blank to let any points beeing used.', 'woocommerce')
			),
		    'table_values' => array(
				'type' => 'table_values'
		    ),
			'swr_apply_rewards_to_rewards' => array(  
				'title' 			=> __('Rewards to rewards', 'woocommerce'),
				'type' 				=> 'checkbox',
				'default' 			=> 'yes',
				'tip'				=> sprintf(__('Give rewards to order that use rewards.<br /><br />Rewards to rewards checked:<br />An order of %s with a percentage of 2%% and customer used is bank of %s will give %s.<br /><br />Rewards to rewards not checked:<br />An order of %s with a percentage of 2%% and customer used is bank of %s will give %s.', 'woocommerce'), swr_clean_amount(woocommerce_price(10)), swr_clean_amount(woocommerce_price(5)), swr_clean_amount(woocommerce_price(0.2)), swr_clean_amount(woocommerce_price(10)), swr_clean_amount(woocommerce_price(5)), swr_clean_amount(woocommerce_price(0.1)))
			),
			'swr_apply_rewards_to_shipping' => array(  
				'title' 			=> __('Deduct shipping with rewards', 'woocommerce'),
				'type' 				=> 'checkbox',
				'default' 			=> 'yes',
				'tip'				=> __('Use rewards value to deduct the shipping cost. If not enabled, the customer will always have to pay for shipping.', 'wc_rewards')
			),
			'swr_apply_type' => array(
				'title' => __('Where to apply the reward', 'woocommerce'),
				'css' => 'max-width:250px;',
				'type' => 'select',
				'class' => 'chosen_select',
				'options' => array(
					'total'	=> __('Total', 'woocommerce'),
					'subtotal' => __('Subtotal', 'woocommerce'),
					'totalwithoutshipping' => __('Total without shipping', 'woocommerce')
				),
				'default' => 'total',
				'tip' => __('Select where to apply the rewards, from the total, subtotal or the total without shipping.', 'woocommerce')
			),
			'swr_roles' => array(
				'title' => __('Select roles that can use rewards', 'woocommerce'),
				'css' => 'width:350px;',
				'type' => 'multiselect',
				'class' => 'chosen_select',
				'options' => $this->roles,
				'default' => $this->roles
			),
		    'paymentstitle' => array(
				'title'           => __( 'Payment gateways extra\'s', 'wc_rewards' ),
				'type'            => 'title',
				'description'     => __( '', 'wc_rewards' )
		    ),
			'payments'  => array(
				'type'            => 'payment_gateways'
			),
		    'display'           => array(
				'title'           => __( 'Display settings', 'wc_rewards' ),
				'type'            => 'title',
				'description'     => __( '', 'wc_rewards' )
		    ),
			'rewards_page' => array(
				'title'		=> __( 'Rewards page description', 'wc_rewards' ),
				'default'	=> '',
				'class'		=> 'chosen_select_nostd',
				'css' 		=> 'min-width:300px;',
				'type' 		=> 'single_select_page'
			),
			'swr_cart_where_to_show' => array(
				'title' => __('Where to show the rewards amount of the cart', 'woocommerce'),
				'css' => 'width:350px;',
				'type' => 'multiselect',
				'class' => 'chosen_select',
				'options' => array(
					'topofcart'	=> __('Show on top of cart', 'woocommerce'),
					'below_cart_table' 	=> __('Show below the cart products table', 'woocommerce'),
					'below_cart_totals' => __('Show below the cart totals', 'woocommerce'),
					'below_shipping_calculator' => __('Show below the shipping calculator', 'woocommerce'),
					'before_order' => __('Show before the order checkout form', 'woocommerce'),
					'below_order' => __('Show below the order checkout form', 'woocommerce'),
					'below_order_thankyou' => __('Show below the order thanks', 'woocommerce'),
					'top_rewards' => __('Show on top of the rewards page', 'woocommerce'),
					'below_product_price' => __('Show below single product price', 'woocommerce')
				),
				'default' => array(
					'topofcart',
					'below_order',
					'below_order_thankyou',
					'top_rewards',
					'below_product_price'
				)
			),
			'swr_use_rewards_where_to_show' => array(
				'title' => __('"Use my rewards" location', 'woocommerce'),
				'css' => 'width:350px;',
				'type' => 'select',
				'class' => 'chosen_select',
				'options' => array(
					'top'	=> __('Show on top of cart', 'woocommerce'),
					'before' 	=> __('Show on top of the cart products table', 'woocommerce'),
					'after' => __('Show below the cart totals', 'woocommerce')
				),
				'default' => 'before',
				'tip' => __('Where to show the checkbox Use my rewards for this order on the checkout page.', 'woocommerce')
			),
		    'review'           => array(
				'title'           => __( 'Reviews settings', 'wc_rewards' ),
				'type'            => 'title',
				'description'     => __( '', 'wc_rewards' )
		    ),
			'swr_enable_reviews_reward' => array(  
				'title' 			=> __('Rewards review', 'woocommerce'),
				'label' 			=> __('Give rewards to customer when they review an item they bought', 'woocommerce'),
				'type' 				=> 'checkbox',
				'default' 			=> 'no'
			),
			'swr_rewards_review_value' => array(
				'title' => __('Rewards amount', 'woocommerce'),
				'id' 		=> 'swr_rewards_review_value',
				'css' 		=> 'width:40px;',
				'type' 		=> 'text',
				'tip'		=>  __('How much to give for a customer who review an item', 'woocommerce')
			),
		    'subscribe'           => array(
				'title'           => __( 'Subscribe settings', 'wc_rewards' ),
				'type'            => 'title',
				'description'     => __( '', 'wc_rewards' )
		    ),
			'swr_enable_subscribe_reward' => array(  
				'title' 			=> __('Rewards subscription', 'woocommerce'),
				'label' 			=> __('Give rewards to customer when they review an item they bought', 'woocommerce'),
				'type' 				=> 'checkbox',
				'default' 			=> 'no'
			),
			'swr_rewards_subscribe_value' => array(
				'title' => __('Rewards amount', 'woocommerce'),
				'css' 		=> 'width:40px;',
				'type' 		=> 'text',
				'tip'		=>  __('How much to give for a customer who subscribe to your website.', 'woocommerce')
			)
		);
		
    } // End init_form_fields()
    
    public function get_cart_applied_total(){
	    global $woocommerce;
	    $amount = 0;
    	if($this->settings['swr_apply_type'] == 'totalwithoutshipping'){
    		if($this->rewards_to_rewards()){
	    		$amount = $woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total;
    		}else{
	    		$amount = $woocommerce->cart->total-$woocommerce->cart->shipping_total;
    		}
    	}elseif($this->settings['swr_apply_type'] == 'subtotal'){
    		if($this->rewards_to_rewards()){
	    		$amount = $woocommerce->cart->cart_contents_total;
    		}else{
	    		$amount = $woocommerce->cart->cart_contents_total;
    		}
    	}else{
    		if($this->rewards_to_rewards()){
	    		$amount = $woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total + $woocommerce->cart->shipping_tax_total + $woocommerce->cart->shipping_total;
    		}else{
	    		$amount = $woocommerce->cart->{$this->settings['swr_apply_type']};
    		}
    	}
    	
    	return $amount;
    }
    
    public function get_cart_reward(){
    	global $woocommerce;
    	$extra_reward = 0;
    	$gateway_extra = 0;
    	
    	$amount = $this->get_cart_applied_total();
    	
    	if($woocommerce->cart->cart_contents && !empty($woocommerce->cart->cart_contents)){
	    	foreach($woocommerce->cart->cart_contents as $content){
	    		$extra_reward += $this->get_product_extra_rewards($content['data'], $content['quantity'], false);
	    	}
    	}
    	$reward = $this->get_rewards_amount($amount)+$extra_reward;
    	if($woocommerce->cart->applied_coupons){
			foreach($woocommerce->cart->applied_coupons as $code){
				$coupon = new WC_Coupon( $code );
				if($coupon->is_valid() && $coupon->type=='fixed_reward'){
					$reward += $coupon->amount;
				}else if($coupon->is_valid() && $coupon->type=='percent_reward'){
					$reward += $coupon->amount*$reward;
				}
			}
		}
		
		if( isset($_POST['payment_method']) ){
		
			$gateway_extra = $this->calculate_payments_extras( $_POST['payment_method'], $amount );
			
			$reward += $gateway_extra;
			
		}
		
    	return($this->format_reward(apply_filters('swr_calculated_rewards', $reward)));
    }
    
    public function get_product_reward(){
    }
    
    public function get_rewards_amount($amount, $wooprice = false){
    	switch($this->get_rewards_type()){
	    	case 'points':
	    		switch($this->get_rewards_calculation()){
		    		case 'percentage':
		    		default:
		    			$amount = round($amount*($this->get_percentage()/100));
		    			break;
		    		case 'fraction':
		    			$fraction = $this->get_fraction_separated();
		    			$amount = $amount/$fraction['dollars'];
		    			if($this->get_rewards_fraction_type() == 'strict')
		    				$amount = floor($amount);
		    			$amount = $amount*$fraction['points'];
		    			if($this->get_rewards_fraction_type() == 'loosefloor')
		    				$amount = floor($amount);
		    			break;
	    		}
	    		break;
	    	case 'money':
	    	default:
	    		$amount = $amount*($this->get_percentage()/100);
	    		break;
    	}
    	if($wooprice){
	    	$amount = $this->format_reward($amount);
    	}
    	return($amount);
    }
    
    public function get_product_extra_rewards($product, $nb = 1, $include_amount = true, $wooprice = false){
    
    	$amount = $this->get_rewards_amount($product->get_price()*$nb);
    	
    	if(get_class($product) == 'WC_Product_Simple'){
	    	$extra_reward = get_post_meta($product->id, '_reward', true);
    	}else{
	    	$extra_reward = get_post_meta($product->variation_id, '_reward', true);
    	}
    	
	    if(strpos($extra_reward, '%') !== false){
			$extra_reward = ((intval($extra_reward)/100)*$amount)+($include_amount?$amount:0);
			if($this->get_rewards_fraction_type() == 'loosefloor' || $this->get_rewards_fraction_type() == 'strict')
				$extra_reward = floor($extra_reward);
		}elseif(strpos($extra_reward, '/') !== false){
			$fraction = $this->separate_fraction($extra_reward);
			$extra_reward = ($product->get_price()*$nb)/$fraction['dollars'];
			if($this->get_rewards_type()=='points'){
				if($this->get_rewards_fraction_type() == 'strict')
					$extra_reward = floor($extra_reward);
				$extra_reward = $extra_reward*$fraction['points'];
				if($this->get_rewards_fraction_type() == 'loosefloor')
					$extra_reward = floor($extra_reward);
			}
		}else{
			$extra_reward = ($extra_reward*$nb)+($include_amount?$amount:0);
		}
		
		if($wooprice){
	    	$extra_reward = $this->format_reward($extra_reward);
    	}
    	
		return($extra_reward);
    }
    
    public function get_title(){
    	return($this->settings['swr_rewards_title']);
    }
    
    public function get_percentage(){
    	return($this->settings['swr_rewards_percentage']);
    }
    
    public function get_rewards_type(){
	    return($this->settings['swr_rewards_type']);
    }
    
    public function get_rewards_points(){
	    return($this->settings['swr_rewards_points']);
    }
    
    public function get_rewards_calculation(){
	    return($this->settings['swr_rewards_calculation']);
    }
    
    public function get_rewards_points_value(){
	    return($this->settings['swr_rewards_points_value']);
    }
    
    public function get_fraction(){
	    return($this->settings['swr_rewards_fraction']);
    }
    
    public function get_rewards_fraction_type(){
	    return($this->settings['swr_rewards_fraction_type']);
    }
    
    public function get_rewards_used_type(){
	    return($this->settings['swr_rewards_used_type']);
    }
    
    public function get_fraction_separated(){
    	return($this->separate_fraction($this->get_fraction()));
    }
    
    public function get_min_points(){
	    return($this->settings['swr_rewards_min_points']);
    }
    
    public function review_enabled(){
	    return($this->settings['swr_enable_reviews_reward']=='yes'?true:false);
    }
    
    public function rewards_to_rewards(){
	    return($this->settings['swr_apply_rewards_to_rewards']=='yes'?true:false);
    }
    
    public function get_review_reward(){
	    return($this->settings['swr_rewards_review_value']);
    }
    
    
    public function subscribe_enabled(){
	    return($this->settings['swr_enable_subscribe_reward']=='yes'?true:false);
    }
    
    public function get_subscribe_reward(){
	    return($this->settings['swr_rewards_subscribe_value']);
    }
    
    
    public function specific_product(){
	    return($this->get_rewards_type()=='money'?false:($this->get_rewards_used_type()=='specificproduct'?true:false));
    }
    
    public function separate_fraction($fraction){
    	$ret = array('points'=>0, 'dollars'=>0);
    	$exp = explode('/', $fraction);
    	$ret['points'] = $exp[0];
    	$ret['dollars'] = $exp[1];
	    return($ret);
    }
    
    public function can_applied_one_table(){
	    
	    $can_be = false;
	    
	    if( $this->get_rewards_used_type() != 'table' ){
	    	
	    	return true;
	    	
	    }
	    
	    $total = $this->get_cart_applied_total();
	
		$current_rewards = swr_get_user_current_rewards();
	    
	    if( count( $this->table_values ) > 0 ){
	    
	    	foreach( $this->table_values as $key => $value ){
	    	
	    		if( ( $current_rewards >= $value['points_required'] ) && ( $total >= $value['points_values'] ) )
	    			$can_be = true;
	    		
	    	}
	    
	    }
	    
	    return $can_be;
	    
    }
    
    public function get_user_rewards($settings = array()){
    	$default_settings = array(
			'user_id' => 0,
			'formatted' => false,
			'convert_to_money' => false
		);
		$default_settings = array_merge($default_settings, $settings);
    	if(empty($default_settings['user_id']))
			$default_settings['user_id'] = get_current_user_id();
	    $current_rewards = get_user_meta($default_settings['user_id'], 'swr_rewards', true);
		$current_rewards = $this->format_reward($current_rewards, $default_settings['formatted'], $default_settings['convert_to_money']);
		return($current_rewards);
    }
    
    public function get_table_based_points( $use_rewards ){
	    
	    if( count( $this->table_values ) <= 0 )
	    	return 0;
	    	
	    foreach( $this->table_values as $value ){
		    
		    if( $value[ 'points_values' ] == $use_rewards ){
			    $points = $value[ 'points_required' ];
			    break;
		    }
		    
	    }
	    
	    return $points;
	    
    }
    
    public function convert_rewards($current_type, $to_type, $rewards, $sub_type = ''){
    	switch($sub_type){
	    	case 'percentage':
	    	default:
	    		$rewards_points = (isset($_POST['woocommerce_rewards_settings_swr_rewards_points'])?$_POST['woocommerce_rewards_settings_swr_rewards_points']:$this->get_rewards_points());
	    		break;
	    	case 'fraction':
	    		$rewards_points = (isset($_POST['woocommerce_rewards_settings_swr_rewards_points_value'])?$_POST['woocommerce_rewards_settings_swr_rewards_points_value']:$this->get_rewards_points_value());
	    		break;
    	}
    	if(empty($rewards_points))
    		$rewards_points = 1;
    	if(($current_type == 'points' && $sub_type != 'fraction') || ($current_type == 'money' && $sub_type == 'fraction')){
	    	$rewards = $rewards/$rewards_points;
    	}else{
	    	$rewards = $rewards*$rewards_points;
    	}
    	if($to_type == 'points' && ($this->get_rewards_fraction_type() == 'loosefloor' || $this->get_rewards_fraction_type() == 'strict')){
	    	$rewards = round($rewards);
    	}
	    return($rewards);
    }
    
    public function convert_user_rewards($user_id = 0, $current_type, $to_type){
    	if(empty($user_id))
			return false;
		$current_rewards = get_user_meta($user_id, 'swr_rewards', true);
		$current_rewards = $this->convert_rewards($current_type, $to_type, $current_rewards);
		update_user_meta($user_id, 'swr_rewards', $current_rewards);
    }
    
    public function get_order_amount( $order ){
    
    	$amount = 0;
	    
	    switch($this->settings['swr_apply_type']){
		    case 'total':
		    default:
		    	if($this->rewards_to_rewards()){
		    		$amount = $order->get_total()+$order->get_total_discount();
		    	}else{
		    		$amount = $order->get_total();
		    	}
		    	break;
		    case 'subtotal':
		    	if($this->rewards_to_rewards()){
		    		$amount = $order->get_total()+$order->get_total_discount()-$order->get_shipping()-$order->get_total_tax();
		    	}else{
		    		$amount = $order->get_total()-$order->get_total_tax()-$order->get_shipping();
		    	}
		    	break;
		    case 'totalwithoutshipping':
		    	if($this->rewards_to_rewards()){
		    		$amount = $order->get_total()+$order->get_total_discount()-$order->get_shipping()-$order->get_shipping_tax();
		    	}else{
		    		$amount = $order->get_total()-$order->get_shipping()-$order->get_shipping_tax();
		    	}
		    	break;
	    }
	    
	    return $amount;
	    
    }
    
    public function get_reward_from_order($order_id, $wooprice = false, $extra = true){
	    global $woocommerce;
	    $order = new WC_Order($order_id);
	    
	    $amount = $this->get_order_amount( $order );
	    
	    $extra_reward = 0;
	    if(sizeof($order->get_items())>0){
		    foreach($order->get_items() as $item){
		    	if($item['id']>0){
			    	$_product = $order->get_product_from_item($item);
			    	if($extra)
			    		$extra_reward += $this->get_product_extra_rewards($_product, $item['qty'], false);
			    }
		    }
	    }
	    $reward = $this->get_rewards_amount($amount)+$extra_reward;
	    
    	if(isset($woocommerce->cart->applied_coupons) && $woocommerce->cart->applied_coupons){
			foreach($woocommerce->cart->applied_coupons as $code){
				$coupon = new WC_Coupon( $code );
				if($coupon->is_valid() && $coupon->type=='fixed_reward'){
					$reward += $coupon->amount;
				}else if($coupon->is_valid() && $coupon->type=='percent_reward'){
					$reward += $coupon->amount*$reward;
				}
			}
		}
		
		if( isset($order->payment_method) ){
			
			$gateway_extra = $this->calculate_payments_extras( $order->payment_method, $amount );
			
			$reward += $gateway_extra;
			
		}
		
	    return($this->format_reward($reward, $wooprice));
    }
    
    
    /**
	 * Calculate if extra rewards should be given for the payment gateway type
	 *
	 * @access public
	 * @param mixed $key
	 * @return int
	 */
    public function calculate_payments_extras( $payment_method, $amount ){
	    
	    if( !isset($this->payments[$_POST['payment_method']]) )
	    	return false;
	    	
	    $extra = 0;
	    	
	    $factor = $this->payments[$_POST['payment_method']];
			
		if(strpos($factor, '%') !== false){
		
			$extra = ( ( intval( $factor ) / 100 ) * $amount );
			
			if($this->get_rewards_fraction_type() == 'loosefloor' || $this->get_rewards_fraction_type() == 'strict')
				$extra = floor($extra);
			
		}elseif( strpos($factor, '/') !== false ){
		
			$fraction = $this->separate_fraction($factor);
			
			$extra = $amount / $fraction['dollars'];
			
			if( $this->get_rewards_type() == 'points' ){
			
				if($this->get_rewards_fraction_type() == 'strict')
					$extra = floor($extra);
					
				$extra = $extra * $fraction['points'];
				
				if($this->get_rewards_fraction_type() == 'loosefloor')
					$extra = floor($extra);
					
			}
			
		}else{
			
			$extra = $factor;
			
		}
	    
	    return $extra;
	    
    }
    
    public function give_back_rewards_for_order($order_id){
	    $order = new WC_Order($order_id);
	    $current_rewards_non = swr_get_user_current_rewards(array(
			'user_id' => $order->user_id
		));
	    if($order->rewards_used > 0){
		    $this->set_user_rewards($order->user_id, $current_rewards_non+$order->rewards_used);
	    }
    }
    
    public function format_reward($reward, $wooprice = true, $convert_to_money = false){
    	if(!$reward)
    		$reward = 0;
    	if($convert_to_money && $this->get_rewards_type() == 'points')
    		$reward = $this->convert_rewards('points', 'money', $reward, $this->get_rewards_calculation());
	    return(($this->get_rewards_type()=='money' || $convert_to_money)?($wooprice?woocommerce_price($reward):number_format($reward,2,'.','')):$reward);
    }
    
    public function set_reward_used_for_order($order_id, $reward){
		update_post_meta($order_id, '_rewards_used', $reward);
    }
    
    public function set_reward_earned_for_order($order_id, $reward){
		update_post_meta($order_id, '_rewards_earned', $reward);
    }
    
    public function set_reward_status_for_order($order_id, $status){
	    update_post_meta($order_id, '_rewards_completed', $status);
    }
    
    public function get_reward_used_for_order($order_id){
		return(get_post_meta($order_id, '_rewards_used', true));
    }
    
    public function get_reward_earned_for_order($order_id){
		return(get_post_meta($order_id, '_rewards_earned', true));
    }
    
    public function get_reward_status_for_order($order_id){
	    return(get_post_meta($order_id, '_rewards_completed', true));
    }
    
    public function set_user_rewards($user_id = 0, $rewards){
    	if(empty($user_id))
    		$user_id = get_current_user_id();
    	if($this->get_rewards_fraction_type() == 'loosefloor' || $this->get_rewards_fraction_type() == 'strict')
    		$rewards = round($rewards);
	    update_user_meta($user_id, 'swr_rewards', $rewards);
    }
    
    public function add_user_rewards( $user_id, $rewards ){
	    
	    $current_rewards = $this->get_user_rewards(array(
	    	'user_id' => $user_id
	    ));
	    
	    $this->set_user_rewards( $user_id, ($current_rewards+$rewards) );
	    
    }
    
    public function check_expired_rewards($user_id = 0){
    
    	$days_to_expired = isset($this->settings['swr_rewards_expiration']) ? $this->settings['swr_rewards_expiration'] : '';
    	
    	if( empty($days_to_expired) )
    		return false;
    
	    if(empty($user_id))
    		$user_id = get_current_user_id();
    		
    	$swr_rewards_used = get_user_meta($default_settings['user_id'], 'swr_rewards_used', true);
    		
    	$customer_orders = get_posts( array(
		    'numberposts' => -1,
		    'meta_key'    => '_customer_user',
		    'meta_value'  => $user_id,
		    'post_type'   => 'shop_order',
		    'post_status' => 'publish',
		    'order'       => 'ASC'
		) );
		
		if( $customer_orders ){
			
			foreach ( $customer_orders as $customer_order ) {
			
				$order = new WC_Order();
				$order->populate( $customer_order );
				$status = get_term_by( 'slug', $order->status, 'shop_order_status' );
				
				if( $order->status == 'completed' ){
					
					
					
				}
				
			}
			
		}
		
    }
    
    public function update_user_rewards_used(){
    
    	$rewards_used = array();

	    $orders = get_posts( array(
		    'numberposts' => -1,
		    'post_type'   => 'shop_order',
		    'post_status' => 'publish'
		) );
		
		if( $orders ){
			
			foreach ( $orders as $corder ) {
			
				$order = new WC_Order();
				$order->populate( $corder );
				$status = get_term_by( 'slug', $order->status, 'shop_order_status' );
				
				if( $order->status == 'completed' ){
					
					$used = $this->get_reward_used_for_order($order->id);
					if(!isset($rewards_used[$order->customer_user]))
						$rewards_used[$order->customer_user] = 0;
					
					$rewards_used[$order->customer_user] += $used;
					
				}
				
			}
			
		}
		
		if(!empty($rewards_used)){
			
			foreach( $rewards_used as $user_id=>$used ){
				
				update_user_meta($user_id, 'swr_rewards_used', $used);
				
			}
			
		}
	    
    }
    
    /**
	 * generate_payment_gateways_html function.
	 *
	 * @access public
	 * @return void
	 */
	public function generate_single_select_page_html() {
	
		$args = array( 'name'				=> 'woocommerce_rewards_settings_rewards_page',
    				   'id'					=> 'woocommerce_rewards_settings_rewards_page',
    				   'sort_column' 		=> 'menu_order',
    				   'sort_order'			=> 'ASC',
    				   'show_option_none' 	=> ' ',
    				   'echo' 				=> false,
    				   'selected'			=> $this->rewards_page
    				   );

    	if( isset( $value['args'] ) )
    		$args = wp_parse_args( $value['args'], $args );
		
		ob_start();
		?>
		<tr valign="top" id="gateways_options">
			<th scope="row" class="titledesc"><?php _E( 'Rewards page description', 'wc_rewards' ); ?></th>
			<td class="forminp">
				<?php echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'wc_rewards' ) .  "' id=", wp_dropdown_pages( $args ) ); ?>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}
    
    /**
	 * generate_payment_gateways_html function.
	 *
	 * @access public
	 * @return void
	 */
	public function generate_payment_gateways_html() {
	
		global $woocommerce;
	
		$gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
		ob_start();
		?>
		<tr valign="top" id="gateways_options">
			<th scope="row" class="titledesc"><?php _e( 'Payment Gateways', 'wc_rewards' ); ?></th>
			<td class="forminp">
				<style type="text/css">
					.rewards_post_boxes td {
						vertical-align: middle;
						padding: 4px 7px;
					}
					.rewards_post_boxes td input {
						margin-right: 4px;
					}
					.rewards_post_boxes .check-column {
						vertical-align: middle;
						text-align: left;
						padding: 0 7px;
					}
				</style>
				<table class="rewards_post_boxes widefat" style="width:500px;">
					<thead>
						<tr>
							<th><?php _e( 'Payment Gateway', 'wc_rewards' ); ?></th>
							<th><?php _e( 'Extra rewards', 'wc_rewards' ); ?><img class="help_tip" data-tip="<?php echo(esc_attr( __( 'Here you can set how much rewards should be added.<br /><br />Possible values are :<br />3% for 3% more of the order total<br />1/20 for 1 points more for each 20$<br />3 for 3 points more.<br /><br />Leave blank for no extra', 'wc_rewards' ) )); ?>" src="<?php echo($woocommerce->plugin_url()); ?>/assets/images/help.png" height="16" width="16" style="margin:0px;float:none;" /></th>
						</tr>
					</thead>
					<tbody id="rates">
						<?php
							if ( $gateways ) {
								foreach ( $gateways as $key => $gateway ) {
									?>
									<tr>
										<td><?php echo($gateway->get_title()); ?></td>
										<td><input type="text" size="5" name="<?php echo($gateway->id); ?>" value="<?php echo(isset($this->payments[$gateway->id])?$this->payments[$gateway->id]:''); ?>" /></td>
									</tr>
									<?php
								}
							}
						?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}
	
	/**
	 * validate_box_packing_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_payment_gateways_field( $key ) {
	
		global $woocommerce;
		
		$payments = array();
	
		$gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
		
		if( $gateways ){
			
			foreach( $gateways as $gateway ){
				
				$payments[$gateway->id] = $_POST[$gateway->id];
				
			}
			
		}

		return $payments;
	}
	
	function generate_table_values_html ( $key, $data ) {
    	global $woocommerce;
    	
    	ob_start();
		?>
		<tr valign="top" id="table_points_value">
			<th scope="row" class="titledesc"><?php _e( 'Payment Gateways', 'wc_rewards' ); ?></th>
			<td class="forminp">
				<table class="wc_rewards_table widefat" style="width:500px;">
					<thead>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column" style="padding: 11px 0px 0px 0px;"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></th>
							<th><?php _e( 'Number of points', 'syn_rewards' ); ?>&nbsp;<span class="tips" data-tip="<?php _e('This is the number of points required to use the value in currency.', 'syn_rewards'); ?>">[?]</span></th>
			
							<th><?php _e( 'Points value', 'syn_rewards' ); ?>&nbsp;<span class="tips" data-tip="<?php _e('Ex.: 3.34', 'syn_ups'); ?>">[?]</span></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-cb check-column" style="padding: 15px 0px 0px 0px;"><label class="screen-reader-text" for="cb-select-all-2">Select All</label><input id="cb-select-all-2" type="checkbox"></th>
							<th colspan="4">
								<a href="#" class="button plus insert">Insert row</a>
								<a href="#" class="button minus remove">Remove selected row(s)</a>
							</th>
						</tr>
					</tfoot>
					<tbody>
					
					<?php if( !empty( $this->table_values ) ){ ?>
					
					<?php foreach( $this->table_values as $key => $values ){ ?>
					
						<tr>
						
							<th scope="row" class="check-column" style="padding:13px 0px 0px 0px;" align="center">
								<input type="checkbox" class="values_rows" value="1" />
							</th>
						
							<td class="values_required">
								<input type="text" name="points_required[<?php echo($key+1); ?>]" data-name="points_required[{0}]" value="<?php echo esc_attr( $values['points_required'] ) ?>" />
							</td>
	
							<td class="points_values">
								<input type="text" name="points_values[<?php echo($key+1); ?>]" data-name="points_values[{0}]" value="<?php echo esc_attr( $values['points_values'] ) ?>" />
							</td>
	
						</tr>
					
					<?php } ?>
					
					<?php } ?>

					</tbody>
				</table>
				
				<table id="values_row" style="display:none;">
				
					<tbody>
						<tr>
						
							<th scope="row" class="check-column" style="padding:13px 0px 0px 0px;" align="center">
								<input type="checkbox" class="values_rows" value="1" />
							</th>
						
							<td class="values_required">
								<input type="text" name="points_required[{0}]" data-name="points_required[{0}]" value="" />
							</td>
	
							<td class="points_values">
								<input type="text" name="points_values[{0}]" data-name="points_values[{0}]" value="" />
							</td>
	
						</tr>
					</tbody>
					
				</table>
				
				<script type="text/javascript">
					jQuery( function() {
			
						var table_values = <?php echo( count( $this->table_values ) ); ?>;
			
						jQuery('.wc_rewards_table .remove').click(function() {
							var $tbody = jQuery('.wc_rewards_table').find('tbody');
							if ( $tbody.find('.values_rows:checked').size() > 0 ) {
								
								jQuery(".wc_rewards_table .values_rows:checked").each(function(){
									
									jQuery(this).parents("tr:first").remove();
									
								});
								
							} else {
								alert('No row(s) selected');
							}
							return false;
						});
			
						jQuery('.wc_rewards_table .insert').click(function() {
							var $tbody = jQuery('.wc_rewards_table').find('tbody');
							table_values++;
							var code = jQuery("#values_row tbody").html();
							code = code.format(table_values);
			
							jQuery('.wc_rewards_table tbody').append(code);
							
							jQuery('.wc_rewards_table tbody .chosen_select2').chosen();
							
							return false;
						});
						
						jQuery('.wc_rewards_table tbody .chosen_select2').chosen();
					});
				
					String.prototype.format = function() {
						var args = arguments;
						return this.replace(/{(\d+)}/g, function(match, number) { 
							return typeof args[number] != 'undefined' ? args[number] : match ;
						});
					};
				
				</script>
			</td>
		</tr>
		<?php
		
		return ob_get_clean();
		
    }
    
    public function validate_table_values_field( $key ) {
    
		$values = array();
		
		if( !empty( $_POST['points_required'] ) ){
			
			foreach( $_POST['points_required'] as $key => $points_required ){
			
				if( $key == '{0}' )
					continue;
				
				$values[] = array(
					'points_required'	=> woocommerce_clean( $points_required ),
					'points_values'		=> woocommerce_clean( $_POST[ 'points_values' ][ $key ] )
				);
				
			}
			
		}

		return $values;
		
	}
    
}