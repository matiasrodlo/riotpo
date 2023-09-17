<?php
/**
 * Updates
 * 
 *
 * @class 		WC_Rewards_Updates
 * @package		WooCommerce
 * @category	Reward
 * @author		WooThemes
 */
class WC_Rewards_Updates extends WC_Reward {
		
	public function __construct() { 
        $this->id					= 'rewards_update';
        $this->method_title     	= __('Updates', 'woocommerce');
        $this->method_description	= __('Give you access to updates directly from Wordpress', 'woocommerce');
		
		// Load the form fields.
		$this->init_form_fields();
		
		// Load the settings.
		$this->init_settings();

		// Define user set variables
		$this->swr_licence = $this->settings['swr_licence'];
		
		// Actions
		add_action( 'woocommerce_update_options_rewards_rewards_update', array( &$this, 'process_admin_options') );
    } 
    
	/**
     * Initialise Settings Form Fields
     */
    function init_form_fields() {
    
    	$this->form_fields = array( 
			'swr_licence' => array(
				'title' => __('Licence certificate', 'woocommerce'),
				'type' => 'text',
				'css' => 'width:240px;',
				'tip' => __('Licence certificate ID, can be found on your CodeCanyon account -> Downloads and click on the \'Licence Certificate\' link on my plugin. It should download a .txt file that contain a line with \'Item Purchase Code:\', just copy the code here!', 'woocommerce')
			)
		);
		
    } // End init_form_fields()
    
    public function get_licence(){
	    return($this->swr_licence);
    }
    
}