<?php
/**
 * WooCommerce Reward class
 * 
 * Extended by individual integrations to offer additional functionality.
 *
 * @class 		WC_Reward
 * @package		WooCommerce
 * @category	Reward
 * @author		WooThemes
 */
class WC_Reward extends WC_Settings_API {
	
	/**
	 * Admin Options
	 *
	 * Setup the gateway settings screen.
	 * Override this in your gateway.
	 *
	 * @since 1.0.0
	 */
	function admin_options() {
	?>
		
		<h3><?php echo isset( $this->method_title ) ? $this->method_title : __( 'Settings', 'woocommerce' ) ; ?></h3>
		
		<?php echo isset( $this->method_description ) ? wpautop( $this->method_description ) : ''; ?>
		
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>
		
		<!-- Section -->
		<div><input type="hidden" name="section" value="<?php echo $this->id; ?>" /></div>
		
		<?php
	}
	
	public function generate_text_html( $key, $data ) {
    	$html = $this->insert_tooltip($key, $data, 'generate_text_html', '.titledesc');
    	return $html;
    }
    
    public function generate_select_html( $key, $data ) {
    	$html = $this->insert_tooltip($key, $data, 'generate_select_html', '.titledesc');
    	return $html;
    }
    
    public function generate_checkbox_html( $key, $data ) {
    	$html = $this->insert_tooltip($key, $data, 'generate_checkbox_html', '.titledesc');
    	return $html;
    }
    
    private function insert_tooltip($key, $data, $func, $append_to){
    	if ( ! class_exists( 'simple_html_dom' ) )
			require_once('simple_html_dom.php');
	    global $woocommerce;
    	$html = parent::$func( $key, $data );
    	
    	$data['tip'] = isset( $data['tip'] ) ? $data['tip'] : '';
    	
    	if(!empty($data['tip'])){
	    	$html = str_get_html($html);
	    	$html->find($append_to,0)->innertext = $html->find($append_to,0)->innertext . '<img class="help_tip" data-tip="'.esc_attr($data['tip']).'" src="'.$woocommerce->plugin_url() . '/assets/images/help.png" width="16" height="16" />';
	    	$html = $html->save();
    	}
    	
    	return $html;
    }

}