<?php
/**
 * WooCommerce Rewards class
 * 
 * Loads Rewards into WooCommerce.
 *
 * @class 		WC_Rewards
 * @package		WooCommerce
 * @category	Rewards
 * @author		WooThemes
 */
class WC_Rewards {
	
	var $rewards = array();
	
    /**
     * init function.
	 *
     * @access public
     */
    function init() {
		do_action('woocommerce_rewards_init');
		
		$load_rewards = apply_filters('woocommerce_rewards', array());
		
		// Load reward classes
		foreach ( $load_rewards as $reward ) {
			
			$load_reward = new $reward();
			
			$this->rewards[$load_reward->id] = $load_reward;
			
		}
		
	}
	
	function get_rewards() {
		return $this->rewards;
	}
    
}