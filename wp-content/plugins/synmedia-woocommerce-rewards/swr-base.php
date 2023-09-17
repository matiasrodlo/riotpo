<?php
/*
  Plugin Name: SYN Media WooCommerce Rewards
  Plugin URI: http://www.synmedia.ca
  Description: Rewards system for WooCommerce
  Version: 2.0.6
  Author: SYN Media Inc.
  Author URI: http://www.synmedia.ca
  Requires at least: 3.1
  Tested up to: 3.4.2

  Copyright: © 2009-2012 SYN Media Inc.
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

define('SWR_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ));
define('SWR_URL', plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) ));
define('SWR_SLUG', plugin_basename(__FILE__));
require('swr-settings.php');
require('swr-subscribe.php');
/* require('swr-product-type.php'); */
/* require('swr-refer-a-friend.php'); */
/* require('syn-updates/syn-updates.php'); */

function swr_rewards_init() {
	global $woocommerce, $swr_settings;
	include('classes/class-wc-reward.php');
	include('classes/class-wc-rewards.php');
	include('classes/class-wc-rewards-settings.php');
	include('swr-functions.php');
	/* include('swr-user-rewards-details.php'); */
	include('swr-coupon.php');
	
	do_action('swr_init', $woocommerce);
	
	$woocommerce->rewards = new WC_Rewards();
	$woocommerce->rewards->init();
	$swr_settings = $woocommerce->rewards->rewards['rewards_settings'];
	
	do_action('swr_init_after_settings', $woocommerce);
	
	if( $woocommerce->rewards->rewards['rewards_settings']->is_enabled() && ( $swr_settings->current_user_can_use_rewards() || !is_user_logged_in() ) ){
		if($woocommerce->rewards->rewards['rewards_settings']->show_top_cart()){
			add_action('woocommerce_before_cart_table', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_cart_table()){
			add_action('woocommerce_cart_collaterals', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_cart_totals()){
			add_action('woocommerce_after_cart_totals', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_shipping_calculator()){
			add_action('woocommerce_after_shipping_calculator', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_before_order()){
			add_action('woocommerce_before_checkout_form', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_order()){
			add_action('woocommerce_after_checkout_form', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_order_thankyou()){
			add_action('woocommerce_thankyou', 'swr_thankyou_rewards');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_product_price()){
			add_action('woocommerce_single_product_summary', 'swr_show_product_reward', 15);
		}
		
		switch($swr_settings->settings['swr_use_rewards_where_to_show']){
			case 'before':
			default:
				add_action('woocommerce_checkout_after_customer_details', 'swr_show_use_rewards');
				break;
			case 'top':
				add_action('woocommerce_checkout_before_customer_details', 'swr_show_use_rewards');
				break;
			case 'after':
				add_action('woocommerce_checkout_order_review', 'swr_show_use_rewards');
				break;
		}
		
		add_action('woocommerce_review_order_before_submit', 'swr_update_reward');
		add_action('woocommerce_view_order', 'swr_thankyou_rewards');
		add_action('woocommerce_product_options_pricing', 'swr_product_options_pricing');
		add_action('woocommerce_process_product_meta', 'swr_process_product_meta', 1, 2);
		add_action('wp_footer', 'swr_check_actions');
		add_action('wp_ajax_nopriv_swr_update_product_qty', 'swr_update_product_qty');
		add_action('wp_ajax_swr_update_product_qty', 'swr_update_product_qty');
		add_filter('woocommerce_get_price_html', 'swr_get_price_html', 10, 2);
		/* add_filter('woocommerce_calculated_total', 'swr_calculated_total'); */
		add_action('woocommerce_calculate_totals', 'swr_calculated_total');
		add_filter('woocommerce_checkout_update_order_meta', 'swr_update_order_meta');
		add_filter('woocommerce_get_order_item_totals', 'swr_get_order_item_totals', 10, 2);
		add_action('woocommerce_email_header', 'swr_email_header');
		add_action('woocommerce_email_footer', 'swr_email_footer');
		add_action('woocommerce_order_status_refunded', 'swr_remove_reward');
		add_action('woocommerce_order_status_cancelled', 'swr_remove_reward');
		add_action('woocommerce_order_status_cancelled', 'swr_give_back_rewards');
		add_action('woocommerce_order_status_pending', 'swr_remove_reward');
		add_action('woocommerce_order_status_failed', 'swr_remove_reward');
		add_action('woocommerce_order_status_on-hold', 'swr_remove_reward');
		add_action('woocommerce_order_status_processing', 'swr_remove_reward');
		add_action('woocommerce_product_after_variable_attributes', 'swr_product_after_variable_attributes', 10, 2);
		add_action('woocommerce_order_status_completed', 'swr_add_reward');
		add_action('woocommerce_email_after_order_table', 'swr_email_after_order_table');
		if($swr_settings->review_enabled()){
			add_action( 'comment_post', 'swr_add_comment_rating', 1 );
		}
		if($swr_settings->specific_product()){
			add_action('woocommerce_after_add_to_cart_button', 'swr_after_add_to_cart_button');
		}
		if(current_user_can('manage_options')){
			add_action('show_user_profile', 'swr_profile_rewards');
			add_action('edit_user_profile', 'swr_profile_rewards');
			add_action('personal_options_update', 'swr_profile_save_rewards');
			add_action('edit_user_profile_update', 'swr_profile_save_rewards');
		}
		add_action('wp_enqueue_scripts', 'swr_frontend_scripts');
		add_action('admin_enqueue_scripts', 'swr_admin_scripts');
		do_action('swr_init_enabled', $woocommerce);
	}
	
	add_shortcode('swr_cart_amount', 'get_swr_cart_amount');
	add_shortcode('swr_rewards_amount', 'get_swr_rewards_amount');
	add_shortcode('swr_view_rewards', 'get_swr_view_rewards');
	add_shortcode('swr_product_rewards', 'get_swr_product_rewards');
	
	if(isset($_GET['install_swr_pages']) && $_GET['install_swr_pages']==1){
		swr_create_pages();
	}
	if(isset($_GET['give_older_orders_rewards']) && $_GET['give_older_orders_rewards']==1){
		swr_give_older_orders_rewards();
	}
	
	/* $swr_settings->check_expired_rewards(); */
	
}
add_action('woocommerce_init', 'swr_rewards_init');

function swr_plugins_loaded() {
	load_plugin_textdomain( 'rewards', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('plugins_loaded', 'swr_plugins_loaded');

function swr_rewards_head() {
	if(isset($_GET['page']) && ($_GET['page']=='woocommerce_settings' || $_GET['page']=='woocommerce') && isset($_GET['tab']) && $_GET['tab']=='rewards'){
		if(!isset($_GET['section']))
			$_GET['section'] = 'rewards_settings';
		do_action('swr_'.$_GET['section'].'_scripts');
	}
}
add_action('admin_head', 'swr_rewards_head');

function swr_frontend_scripts(){
	global $woocommerce, $swr_settings;
	if($swr_settings->is_enabled() && is_cart() || is_checkout()){
		wp_enqueue_script('swr_checkout_script', plugins_url('synmedia-woocommerce-rewards/assets/js/jquery.swr.checkout.js'), array('jquery'), '1.0');
		if(is_checkout()){
			wp_enqueue_style('swr_orders_rewards', plugins_url('synmedia-woocommerce-rewards/assets/css/swr.checkout.css'));
		}
	}elseif($swr_settings->is_enabled() && 1==2){
		wp_enqueue_script('swr_orders_script', plugins_url('synmedia-woocommerce-rewards/assets/js/jquery.swr.orders.js'), array('jquery'), '1.0');
		?>
		<script type="text/javascript">
			var rewards_title = "<?php echo($swr_settings->settings['swr_rewards_title']); ?>";
		</script>
		<?php
	}elseif($swr_settings->is_enabled() && is_product()){
		wp_enqueue_script('swr_product_script', plugins_url('synmedia-woocommerce-rewards/assets/js/jquery.swr.product.js'), array('jquery'), '1.0');
	}
	if($swr_settings->is_enabled()){
		wp_enqueue_script('swr_base', SWR_URL.'/assets/js/jquery.swr.base.js', array('jquery'), '1.0');
	}
}

function swr_admin_scripts(){
	wp_register_script('swr_writepanel', plugins_url('synmedia-woocommerce-rewards/assets/js/swr.writepanel.js'), array('jquery'));
	wp_enqueue_script('swr_writepanel');
}

function swr_create_pages(){
	global $woocommerce;
	if(!function_exists('woocommerce_create_page')){
		$path = str_replace('synmedia-woocommerce-rewards', 'woocommerce', plugin_dir_path(__FILE__));
		require_once($path.'admin/woocommerce-admin-install.php');
	}
	
	do_action('swr_create_pages');
	
	update_option('swr_pages_installed', 1);
}

function swr_give_older_orders_rewards(){
	global $swr_settings;
	$orders = get_posts(array(
		'numberposts'	=> -1,
		'post_type'		=> 'shop_order'
	));
	if($orders && count($orders) > 0){
		foreach($orders as $order){
			$terms = wp_get_object_terms( $order->ID, 'shop_order_status', array('fields' => 'slugs') );
			$status = (isset($terms[0])) ? $terms[0] : 'pending';
			$reward = $swr_settings->get_reward_earned_for_order($order->ID);
			if(!$reward){
				swr_update_order_meta($order->ID, false);
				if($status=='completed'){
					swr_add_reward($order->ID);
				}
			}
		}
	}
}

function swr_before_cart_table(){
	global $woocommerce;
	$msg = swr_get_text_rewards();
?>
<p class="<?php echo((WOOCOMMERCE_VERSION >= 2)?'woocommerce-info':'woocommerce_info') ?> swr_get_rewards" style="clear:both;"><?php echo($msg); ?></p>
<?php
}


function swr_update_product_qty(){
	global $swr_settings;
	
	$new_rewards = $old_rewards = 0;
	
	$ret = $products = array();
	if(isset($_POST['product_id']) && $_POST['product_id'] > 0){
		$variation_id = $_POST['product_id'];
		$prod = new WC_Product_Variation($variation_id);
		$products[] = array('qty'=>$_POST['qty'],'product'=>$prod);
	}elseif(isset($_POST['qtys'])){
		$qtys = $_POST['qtys'];
		$qtys = explode('|', $qtys);
		if(count($qtys) > 0){
			foreach($qtys as $pro){
				$pro = explode(':', $pro);
				preg_match('/quantity\[(.+)\]/', $pro[0], $match);
				$prod = new WC_Product($match[1]);
				$products[] = array('qty'=>$pro[1],'product'=>$prod);
			}
		}
	}elseif($_POST['add-to-cart'] > 0){
		$prod = new WC_Product($_POST['add-to-cart']);
		$products[] = array('qty'=>$_POST['qty'],'product'=>$prod);
	}
	$prod = null;
	unset($prod);
	if(count($products) > 0){
		foreach($products as $pro){
			$new_rewards += $swr_settings->get_product_extra_rewards($pro['product'], $pro['qty']);
			$old_rewards += $swr_settings->get_rewards_amount($pro['product']->get_price()*$pro['qty']);
		}
		$ret['swr_new_reward'] = $swr_settings->format_reward($new_rewards);
		$ret['swr_old_reward'] = $swr_settings->format_reward($old_rewards);
	}
	echo(json_encode($ret));
	die();
}

function swr_show_product_reward(){
	global $post, $product, $woocommerce, $swr_settings;
	$amount = $swr_settings->get_rewards_amount($product->get_price(), true);
	$extra_reward = $swr_settings->get_product_extra_rewards($product, 1, true, true);
?>
<p itemprop="reward" class="reward">

<?php
	
	if( !empty($extra_reward) && $extra_reward != $amount ){
	
		if( isset($swr_settings->rewards_page) && !empty($swr_settings->rewards_page) ){
			
			echo(sprintf(__('You\'ll earn <del class="swr_old_reward">%s</del> <ins class="swr_new_reward">%s</ins> <a href="%s">%s</a>', 'rewards'), $amount, $extra_reward, get_permalink($swr_settings->rewards_page), $swr_settings->get_title()));
			
		}else{
			
			echo(sprintf(__('You\'ll earn <del class="swr_old_reward">%s</del> <ins class="swr_new_reward">%s</ins> %s', 'rewards'), $amount, $extra_reward, $swr_settings->get_title()));
			
		}
	
	}else{
	
		if( isset($swr_settings->rewards_page) && !empty($swr_settings->rewards_page) ){
		
			echo(sprintf(__('You\'ll earn <span class="swr_new_reward">%s</span> <a href="%s">%s</a>', 'rewards'), $amount, get_permalink($swr_settings->rewards_page), $swr_settings->get_title()));
		
		}else{
			
			echo(sprintf(__('You\'ll earn <span class="swr_new_reward">%s</span> %s', 'rewards'), $amount, $swr_settings->get_title()));
			
		}
		
	}
	
?>
</p>
<?php
}

function swr_get_product_extra_reward($product_id){
	return(get_post_meta($product_id, '_reward', true));
}

function swr_show_use_rewards(){

	global $woocommerce, $swr_settings;
	
	$datas = array();
	
	$tmp_datas = explode('&', isset($_POST['post_data'])?$_POST['post_data']:'');
	
	if( !empty($tmp_datas) && !empty($tmp_datas[0]) ){
	
		foreach($tmp_datas as $da){
		
			$tmp = explode('=', $da);
			$datas[$tmp[0]] = $tmp[1];
			
		}
		
	}
	
	$total = $swr_settings->get_cart_applied_total();
	
	$current_rewards = swr_get_user_current_rewards();
	
	if( is_user_logged_in() && !empty($current_rewards) && $current_rewards>0 && !$swr_settings->specific_product() && $swr_settings->get_min_points() <= $current_rewards && is_checkout() && $swr_settings->can_applied_one_table() ){
?>

<h3 id="redeem_rewards"><?php echo( $swr_settings->get_title() ); ?></h3>

<p class="<?php echo((WOOCOMMERCE_VERSION >= 2)?'woocommerce-info':'woocommerce_info') ?> swr_use_rewards">

	<?php if( $swr_settings->get_rewards_used_type() == 'pointsvalue' ){ ?>
	
	<label><input type="checkbox" name="swr_use_rewards" id="swr_use_rewards" value="1"<?= (isset($datas['swr_use_rewards']) && $datas['swr_use_rewards'])?' checked="checked"':'' ?> /> <?php echo(sprintf(__('Apply my %s in %s to this order.', 'rewards'), $current_rewards, $swr_settings->get_title())); ?></label>
	
	<?php }else if( $swr_settings->get_rewards_used_type() == 'table' ){ ?>
	
	<?php if( count( $swr_settings->table_values ) > 0 ){ ?>
	
	<?php foreach( $swr_settings->table_values as $key => $value ){ if( ( $current_rewards < $value['points_required'] ) || ( $total < $value['points_values'] ) ) continue; ?>
	
	<label><input type="checkbox" name="swr_use_rewards" value="<?php echo( $value['points_required'] ); ?>"<?= (isset($datas['swr_use_rewards']) && $datas['swr_use_rewards'] && $datas['swr_use_rewards'] == $value['points_required'])?' checked="checked"':'' ?> /> <?php echo(sprintf(__('Apply %s %s for a discount of %s to this order.', 'rewards'), $value['points_required'], $swr_settings->get_title(), woocommerce_price( $value['points_values'] ))); ?></label><?php if( count( ( $key + 1 ) < $swr_settings->table_values ) ){ ?><br /><?php } ?>
	
	<?php } ?>
	
	<?php } ?>
	
	<?php } ?>

</p>

<?php
	}
	
}

function swr_calculated_total($cart){
	global $woocommerce, $swr_settings;
	
	$datas = array();
	$tmp_datas = explode('&', isset($_POST['post_data'])?$_POST['post_data']:'');
	if(!empty($tmp_datas) && !empty($tmp_datas[0])){
		foreach($tmp_datas as $da){
			$tmp = explode('=', $da);
			$datas[$tmp[0]] = $tmp[1];
		}
	}
	$current_rewards = swr_get_user_current_rewards();
	
	if(((isset($datas['swr_use_rewards']) && $datas['swr_use_rewards']) || (isset($_POST['swr_use_rewards']) && $_POST['swr_use_rewards'])) && is_user_logged_in() && !empty($current_rewards) && ($swr_settings->get_rewards_type() == 'money' || ($swr_settings->get_rewards_type() == 'points' && $swr_settings->get_min_points() <= $current_rewards))){
		
		if( $swr_settings->get_rewards_used_type() == 'table' ){
		
			if( isset($datas['swr_use_rewards']) && $datas['swr_use_rewards'] ){
				
				$points = $datas['swr_use_rewards'];
				
			}else{
				
				$points = $_POST['swr_use_rewards'];
				
			}
		
			foreach( $swr_settings->table_values as $key => $value ){
				
				if( $value['points_required'] == $points ){
					$current_rewards = $value['points_values'];
					break;
				}
				
			}
			
			
			
		}else{
			
			$current_rewards = swr_get_user_current_rewards(array(
				'convert_to_money' => true
			));
			
		}
		
		$s_total = ($cart->cart_contents_total + $cart->tax_total + ($swr_settings->swr_apply_rewards_to_shipping ? $cart->shipping_tax_total + $cart->shipping_total : 0 ));
		
		if($s_total-$cart->discount_total > 0){
		
			if($current_rewards > $s_total-$cart->discount_total){
				$_SESSION['rewards_used'] = $s_total-$cart->discount_total;
				$cart->discount_total += $s_total-$cart->discount_total;
				$cart->total = 0;
			}else{
				$_SESSION['rewards_used'] = $current_rewards;
				$cart->discount_total += $current_rewards;
				$cart->total -= $cart->discount_total;
			}
			
		}
		
	}
	/* return($cart); */
}

function swr_update_order_meta($order_id, $extra = true){
	global $woocommerce, $swr_settings;
	$order = new WC_Order($order_id);
	if($order->user_id > 0){
		$current_rewards = swr_get_user_current_rewards(array(
			'user_id' => $order->user_id,
			'convert_to_money' => true
		));
		$current_rewards_non = swr_get_user_current_rewards(array(
			'user_id' => $order->user_id
		));
		if(isset($_POST['swr_use_rewards']) && $_POST['swr_use_rewards'] && $current_rewards > 0){
			$use_rewards = $_SESSION['rewards_used'];
			if($swr_settings->get_rewards_type() == 'points'){
			
				switch( $swr_settings->get_rewards_used_type() ){
					
					case 'pointsvalue':
						$use_rewards = $swr_settings->convert_rewards('money', 'points', $use_rewards, $swr_settings->get_rewards_calculation());
						break;
						
					case 'table':
						$use_rewards = $swr_settings->get_table_based_points( $use_rewards );
						break;
					
				}
			
			}
			$swr_settings->set_user_rewards($order->user_id, $current_rewards_non-$use_rewards);
			$swr_settings->set_reward_used_for_order($order_id, $use_rewards);
			$reward = str_replace('</span>','',str_replace('<span class="amount">', '', $swr_settings->format_reward($current_rewards, true)));
			$order->add_order_note(sprintf(__('Customer applied %s %s to this order.', 'rewards'), $use_rewards, $swr_settings->get_title()));
		}
		$swr_settings->set_reward_earned_for_order($order_id, $swr_settings->get_reward_from_order($order_id, false, $extra));
		$swr_settings->set_reward_status_for_order($order_id, 0);
	}
}

function swr_thankyou_rewards($order_id){
	global $woocommerce, $sitepress, $swr_settings;
	
	$order = new WC_Order($order_id);
	$my_account_page_id = get_option('woocommerce_myaccount_page_id');
	if(function_exists('icl_object_id')){
		$my_account_page_id = icl_object_id($my_account_page_id, 'page', false, $sitepress->get_current_language());
	}
	
	if( is_user_logged_in() ){
	
		if( isset($swr_settings->rewards_page) && !empty($swr_settings->rewards_page) ){
			
			$msg = sprintf(__('You have earned %s <a href="%s">%s</a> for this order', 'rewards'), $swr_settings->format_reward($order->rewards_earned, true), get_permalink($swr_settings->rewards_page), $swr_settings->get_title());
			
		}else{
			
			$msg = sprintf(__('You have earned %s %s for this order', 'rewards'), $swr_settings->format_reward($order->rewards_earned, true), $swr_settings->get_title());
			
		}
	
	}else{
	
		if( isset($swr_settings->rewards_page) && !empty($swr_settings->rewards_page) ){
		
			$msg = sprintf(__('You\'ve missed a great chance to earn %s <a href="%s">%s</a> for this order.', 'rewards'), $swr_settings->format_reward($order->rewards_earned, true), get_permalink($swr_settings->rewards_page), $swr_settings->get_title());
		
		}else{
			
			$msg = sprintf(__('You\'ve missed a great chance to earn %s %s for this order.', 'rewards'), $swr_settings->format_reward($order->rewards_earned, true), $swr_settings->get_title());
			
		}
		
	}
?>
<p class="<?php echo((WOOCOMMERCE_VERSION >= 2)?'woocommerce-info':'woocommerce_info') ?> swr_get_rewards"><?php echo($msg); ?></p>
<?php
}


function swr_update_reward(){
	global $woocommerce, $swr_settings;
	$msg = swr_get_text_rewards();
	if($swr_settings->is_enabled()){
		echo('<div class="updated_rewards" style="display:none;">'.$msg.'</div>');
	}
}


function swr_get_text_rewards(){
	global $woocommerce, $sitepress, $swr_settings;
	$my_account_page_id = get_option('woocommerce_myaccount_page_id');
	if(function_exists('icl_object_id')){
		$my_account_page_id = icl_object_id($my_account_page_id, 'page', false, $sitepress->get_current_language());
	}
	$datas = array();
	$tmp_datas = explode('&', isset($_POST['post_data'])?$_POST['post_data']:'');
	if(!empty($tmp_datas) && !empty($tmp_datas[0])){
		foreach($tmp_datas as $da){
			$tmp = explode('=', $da);
			$datas[$tmp[0]] = $tmp[1];
		}
	}
	
	if( is_user_logged_in() || ( isset($datas['createaccount']) && $datas['createaccount']) ){
	
		if( isset($swr_settings->rewards_page) && !empty($swr_settings->rewards_page) ){
			
			$msg = sprintf(__('You\'ll earn %s <a href="%s">%s</a> for this order', 'rewards'), $swr_settings->get_cart_reward(), get_permalink($swr_settings->rewards_page), $swr_settings->get_title());
			
		}else{
			
			$msg = sprintf(__('You\'ll earn %s %s for this order', 'rewards'), $swr_settings->get_cart_reward(), $swr_settings->get_title());
			
		}
	
	}else{
	
		if( isset($swr_settings->rewards_page) && !empty($swr_settings->rewards_page) ){
		
			$msg = sprintf(__('<a href="%s">Create an account</a> and earn %s <a href="%s">%s</a> for this order', 'rewards'), get_permalink($my_account_page_id), $swr_settings->get_cart_reward(), get_permalink($swr_settings->rewards_page), $swr_settings->get_title());
		
		}else{
			
			$msg = sprintf(__('<a href="%s">Create an account</a> and earn %s %s for this order', 'rewards'), get_permalink($my_account_page_id), $swr_settings->get_cart_reward(), $swr_settings->get_title());
			
		}
		
	}
	
	return($msg);
}

function swr_order_data($load_data){
	$load_data['rewards_earned'] = '';
	$load_data['rewards_completed'] = '';
	$load_data['rewards_used'] = '';
	return($load_data);
}
add_filter('woocommerce_load_order_data', 'swr_order_data');

function swr_add_reward($order_id){
	global $woocommerce, $swr_settings;
	$order = new WC_Order($order_id);
	if(!$order->rewards_completed && $order->user_id > 0 && $order->rewards_earned > 0){
		$swr_settings->set_reward_status_for_order($order_id, 1);
		$current_rewards = swr_get_user_current_rewards(array(
			'user_id' => $order->user_id
		));
		if(!$current_rewards)
			$current_rewards = 0;
		$current_rewards += $order->rewards_earned;
		update_user_meta($order->user_id, 'swr_rewards', $current_rewards);
		$reward = swr_clean_amount($swr_settings->format_reward($order->rewards_earned, true));
		$order->add_order_note(sprintf(__('Customer earned %s in %s.','rewards'), $reward, $swr_settings->get_title()));
	}
}

function swr_remove_reward($order_id){
	global $woocommerce, $swr_settings;
	$order = new WC_Order($order_id);
	if($order->rewards_completed && $order->user_id > 0){
		$swr_settings->set_reward_status_for_order($order_id, 0);
		$current_rewards = swr_get_user_current_rewards(array(
			'user_id' => $order->user_id
		));
		if(!$current_rewards)
			$current_rewards = 0;
		$current_rewards -= $order->rewards_earned;
		update_user_meta($order->user_id, 'swr_rewards', $current_rewards);
		$reward = swr_clean_amount($swr_settings->format_reward($order->rewards_earned, true));
		$order->add_order_note(sprintf(__('Removed %s %s.','rewards'), $reward, $swr_settings->get_title()));
	}
}

function swr_give_back_rewards($order_id){
	global $swr_settings;
	$swr_settings->give_back_rewards_for_order($order_id);
}

function swr_clean_amount($amount){
	return(str_replace('</span>','',str_replace('<span class="amount">','',$amount)));
}

function swr_update_cart_reward(){
	global $woocommerce;
	check_ajax_referer( 'update-shipping-method', 'security' );
	
	if ( ! defined('WOOCOMMERCE_CART') ) define( 'WOOCOMMERCE_CART', true );
	
	if ( isset( $_POST['shipping_method'] ) ) $_SESSION['_chosen_shipping_method'] = $_POST['shipping_method'];
	$woocommerce->cart->calculate_totals();
	$msg = swr_get_text_rewards();
	echo('<span class="updated_rewards" style="display:none;">'.$msg.'</span>');
}
add_action('wp_ajax_woocommerce_update_shipping_method', 'swr_update_cart_reward');
add_action('wp_ajax_nopriv_woocommerce_update_shipping_method', 'swr_update_cart_reward');
 
function swr_rewards_tab_filter($tabs){
	$tabs['rewards'] = 'Rewards';
	return($tabs);
}
add_filter('woocommerce_settings_tabs_array', 'swr_rewards_tab_filter');

function swr_rewards(){
	global $woocommerce;
	$rewards = $woocommerce->rewards->get_rewards();
						
	$section = empty( $_GET['section'] ) ? key( $rewards ) : urldecode( $_GET['section'] );
	
	foreach ( $rewards as $reward ) {
		$title = ( isset( $reward->method_title ) && $reward->method_title) ? ucwords( $reward->method_title ) : ucwords( $method->id );
		$current = ( $reward->id == $section ) ? 'class="current"' : '';
		
		$links[] = '<a href="' . add_query_arg( 'section', $reward->id, admin_url('admin.php?page=woocommerce&tab=rewards') ) . '"' . $current . '>' . $title . '</a>';
	}
	
	echo '<ul class="subsubsub"><li>' . implode(' | </li><li>', $links) . '</li></ul><br class="clear" />';
	
	if ( isset( $rewards[ $section ] ) )
		$rewards[ $section ]->admin_options();
}
add_action('woocommerce_settings_tabs_rewards', 'swr_rewards');

function get_swr_cart_amount(){
	global $woocommerce, $swr_settings;
	$woocommerce->cart->calculate_totals();
	return('<span class="swr_cart_shortcode_amount">'.$swr_settings->get_cart_reward().'</span>');
}

function get_swr_rewards_amount(){
	return('<span class="swr_cart_shortcode_amount">'.swr_get_user_current_rewards(array('formatted'=>true)).'</span>');
}

function get_swr_product_rewards(){
	
	ob_start();
	
	$content = swr_show_product_reward();
	
	return ob_get_clean();
}

function get_swr_view_rewards( $atts ){
	global $woocommerce;
	return $woocommerce->shortcode_wrapper( 'get_swr_wrapper_view_rewards', $atts );
}

function get_swr_wrapper_view_rewards(){

	if(is_user_logged_in()){
	
		$recent_orders = 10;
		
		include(SWR_PATH.'/templates/my-rewards.php');
		
	}else{
	
		woocommerce_get_template('myaccount/form-login.php');
		
	}
	
}

function swr_check_actions(){
	global $swr_settings;
	switch($swr_settings->settings['swr_use_rewards_where_to_show']){
		case 'before':
		default:
			$action = 'woocommerce_checkout_after_customer_details';
			break;
		case 'top':
			$action = 'woocommerce_checkout_before_customer_details';
			break;
		case 'after':
			$action = 'woocommerce_checkout_order_review';
			break;
	}
	if(did_action($action)===0){
	?>
	<div id="use_my_rewards_container" style="display:none;"><?php swr_show_use_rewards(); ?></div>
	<script type="text/javascript">
		var use_my_rewards_location = "<?= $swr_settings->settings['swr_use_rewards_where_to_show'] ?>";
		jQuery(function($){
			switch(use_my_rewards_location){
				case 'before':
				default:
					if($("#order_review_heading").length > 0){
						$("#order_review_heading").before($(".swr_use_rewards"));
					}else if($("#customer_details").length > 0){
						$("#customer_details").after($(".swr_use_rewards"));
					}else{
						$("form.checkout").prepend($(".swr_use_rewards"));
					}
					break;
				case 'top':
					$("form.checkout").prepend($(".swr_use_rewards"));
					break;
			}
			$("#use_my_rewards_container").remove();
		});
	</script>
	<?php
	}
}

function swr_profile_rewards($user){
	global $woocommerce, $swr_settings;
	$rewards = swr_get_user_current_rewards(array(
		'user_id' => $user->ID
	));
?>
	<h3><?php _e('Rewards', 'rewards'); ?></h3>
	<table class="form-table">
		<tr>
			<th>
				<label for="gateways"><?php echo(sprintf(__('Current %s earned', 'rewards'), $swr_settings->get_title())); ?></label>
			</th>
			<td>
				<input type="text" name="swr_rewards" id="swr_rewards" value="<?php echo($rewards); ?>" class="regular-text">
			</td>
		</tr>
	</table>
	<?php
	global $woocommerce, $recent_orders, $swr_settings;

/* $swr_settings = $woocommerce->rewards->rewards['rewards_settings']; */

$customer_id = $user->ID;

$args = array(
    'numberposts'     => $recent_orders,
    'meta_key'        => '_customer_user',
    'meta_value'	  => $customer_id,
    'post_type'       => 'shop_order',
    'post_status'     => 'publish' 
);


$customer_orders = get_posts($args);

$msg = sprintf(__('Current %s balance: %s', 'rewards'), $swr_settings->get_title(), swr_get_user_current_rewards(array('user_id'=>$customer_id, 'formatted'=>true)));

if ($customer_orders) :
?>
	<h4><?php _e("Orders", "rewards"); ?></h4>
	<table class="shop_table my_account_orders" style="width:50%;">
	
		<thead>
			<tr>
				<th class="order-number" style="width:35%;"><span class="nobr"><?php _e('Order', 'rewards'); ?></span></th>
				<th class="order-total rewards-earned" style="width:20%;"><span class="nobr"><?php _e('Total earned', 'rewards'); ?></span></th>
				<th class="order-total rewards-used" style="width:20%;"><span class="nobr"><?php _e('Total Used', 'rewards'); ?></span></th>
				<th class="order-total rewards-status" style="width:25%;"><span class="nobr"><?php _e('Status', 'rewards'); ?></span></th>
			</tr>
		</thead>
		
		<tbody><?php
			foreach ($customer_orders as $customer_order) :
				$order = new WC_Order();
				
				$order->populate( $customer_order );
				
				$status = get_term_by('slug', $order->status, 'shop_order_status');
				
				?><tr class="order">
					<td class="order-number" width="1%">
						<a href="<?php echo esc_url( add_query_arg('order', $order->id, get_permalink(woocommerce_get_page_id('view_order'))) ); ?>"><?php echo $order->get_order_number(); ?></a> &ndash; <time title="<?php echo esc_attr( strtotime($order->order_date) ); ?>"><?php echo date_i18n(get_option('date_format'), strtotime($order->order_date)); ?></time>
					</td>
					<td class="order-total rewards-earned" width="1%"><?php echo($swr_settings->format_reward($order->rewards_earned)); ?></td>
					<td class="order-total rewards-used" width="1%"><?php echo($swr_settings->format_reward($order->rewards_used)); ?></td>
					<td class="order-total rewards-status" width="1%"><?php _e($status->name, 'woocommerce'); ?></td>
				</tr><?php
			endforeach;
		?></tbody>
	
	</table>
<?php
else :
?>
	<p><?php _e('You have no recent orders.', 'rewards'); ?></p>
<?php
endif;

if($swr_settings->review_enabled()):
$comments = array();
$tmp_comments = get_comments(array(
	'user_id' => $customer_id
));
if($tmp_comments && count($tmp_comments) > 0){
	foreach($tmp_comments as $tmp_comment){
		$met = get_comment_meta($tmp_comment->comment_ID, 'rewards_earned', true);
		if($met){
			$comments[] = array(
				'item_id' => $tmp_comment->comment_post_ID,
				'date' => $tmp_comment->comment_date,
				'reward' => $met,
				'status' => wp_get_comment_status($tmp_comment->comment_ID)
			);
		}
	}
}
if($comments && count($comments) > 0):
?>
<h2><?php _e("Reviews", "rewards"); ?></h2>
	<table class="shop_table my_account_orders">
	
		<thead>
			<tr>
				<th class="order-number" style="width:35%;"><span class="nobr"><?php _e('Date', 'rewards'); ?></span></th>
				<th class="order-total rewards-product" style="width:20%;"><span class="nobr"><?php _e('Product', 'rewards'); ?></span></th>
				<th class="order-total rewards-earned" style="width:20%;"><span class="nobr"><?php _e('Total earned', 'rewards'); ?></span></th>
				<th class="order-total rewards-status" style="width:25%;"><span class="nobr"><?php _e('Status', 'rewards'); ?></span></th>
			</tr>
		</thead>
		
		<tbody><?php
			foreach ($comments as $comment) :
				?><tr class="comment">
					<td class="order-number"><?php echo date_i18n(get_option('date_format'), strtotime($comment['date'])); ?></td>
					<td class="order-total rewards-product"><?php echo(get_the_title($comment['item_id'])); ?></td>
					<td class="order-total rewards-earned"><?php echo($comment['reward']); ?></td>
					<td class="order-total rewards-status"><?php _ex(ucfirst($comment['status']), 'adjective'); ?></td>
				</tr><?php
			endforeach;
		?></tbody>
	
	</table>
<?php else : ?>
	<p><?php _e('You have to review an item first', 'rewards'); ?></p>
<?php endif; ?>
<?php
	endif;
?>
<?php
}

function swr_profile_save_rewards($user_id){
	update_user_meta($user_id, 'swr_rewards', $_POST['swr_rewards']);
}

function swr_add_shortcode_button() {
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) return;
	if ( get_user_option('rich_editing') == 'true') :
		add_filter('mce_external_plugins', 'swr_add_shortcode_tinymce_plugin');
		add_filter('mce_buttons', 'swr_register_shortcode_button');
	endif;
}
add_action('init', 'swr_add_shortcode_button', 12);

function swr_register_shortcode_button($buttons) {
	array_push($buttons, "swr_shortcodes_button");
	return $buttons;
}

function swr_add_shortcode_tinymce_plugin($plugin_array) {
	$plugin_array['SWRShortcodes'] = plugins_url('synmedia-woocommerce-rewards/assets/js/admin.editor_plugin.js');
	return $plugin_array;
}

function swr_email_header(){
	global $SWR_IS_EMAIL;
	$SWR_IS_EMAIL = true;
}

function swr_email_footer(){
	global $SWR_IS_EMAIL;
	$SWR_IS_EMAIL = false;
}

function swr_get_order_item_totals($total_rows, $order){
	global $woocommerce, $swr_settings, $SWR_IS_EMAIL;
	if(isset($SWR_IS_EMAIL) && $SWR_IS_EMAIL){
		$rewards_earned = $swr_settings->get_reward_earned_for_order($order->id);
		$rewards_used = $swr_settings->get_reward_used_for_order($order->id);
		if($rewards_earned > 0){
			$total_rows['rewards_earned'] = array(
				'label' => sprintf(__( '%s earned:', 'rewards' ), $swr_settings->get_title()),
				'value'	=> $rewards_earned
			);
		}
		if($rewards_used > 0){
			$total_rows['rewards_used'] = array(
				'label' => sprintf(__( '%s used:', 'rewards' ), $swr_settings->get_title()),
				'value'	=> $rewards_used
			);
		}
	}
	return($total_rows);
}

function swr_add_comment_rating($comment_id){
	global $woocommerce, $swr_settings;
	if ( isset($_POST['rating']) ) :
		global $post;
		if ( ! $_POST['rating'] || $_POST['rating'] > 5 || $_POST['rating'] < 0 ) return;
		$comment = get_comment($comment_id);
		$already_commented = false;
		$comments = get_comments(array(
			'post_id' => $comment->comment_post_ID,
			'user_id' => $comment->user_id
		));
		if($comments && count($comments) > 0){
			foreach($comments as $tmp_comment){
				$met = get_comment_meta($tmp_comment->comment_ID, 'rewards_earned', true);
				if($met)
					$already_commented = true;
			}
		}
		
		if(swr_user_bought_item($comment->user_id, $comment->comment_post_ID) && !$already_commented){
		
			add_comment_meta($comment_id, 'rewards_earned', $swr_settings->get_review_reward(), true);
			if($comment->comment_approved){
				$current_rewards = $swr_settings->get_user_rewards(array(
					'user_id' => $comment->user_id
				));
				$current_rewards += $swr_settings->get_review_reward();
				update_user_meta($comment->user_id, 'swr_rewards', $current_rewards);
			}
			
		}
	endif;
}

function swr_comment_status_changed($new_status, $old_status, $comment){
	global $swr_settings;
	$reward = get_comment_meta($comment->comment_ID, 'rewards_earned', true);
	switch($new_status){
		case 'approved':
			$current_rewards = $swr_settings->get_user_rewards(array(
				'user_id' => $comment->user_id
			));
			$current_rewards += $reward;
			update_user_meta($comment->user_id, 'swr_rewards', $current_rewards);
			break;
		default:
			if($old_status == 'approved'){
				$current_rewards = $swr_settings->get_user_rewards(array(
					'user_id' => $comment->user_id
				));
				$current_rewards -= $reward;
				update_user_meta($comment->user_id, 'swr_rewards', $current_rewards);
			}
			break;
	}
}
add_action("transition_comment_status", "swr_comment_status_changed", 10, 3);

/* Validate if the user has bought this item or not */
function swr_user_bought_item($user_id = 0, $item_id = 0){
	global $wpdb;
	$has_bought = false;
	if($user_id > 0 && $item_id > 0){
		$orders = get_posts(array(
			'post_type' => 'shop_order',
			'meta_key' => '_customer_user',
			'meta_value' => $user_id
		));
		if($orders && count($orders) > 0){
			foreach($orders as $order){
				$order = new WC_Order($order->ID);
			 	if ( sizeof( $order->get_items() ) > 0 ) {
			 	
					foreach( $order->get_items() as $item ) {
					
						if( (isset($item['id']) && $item['id'] == $item_id) || (isset($item['product_id']) && $item['product_id'] == $item_id ) ){
							
							$has_bought = true;
							break;
							
						}
						
					}
				}
				if($has_bought)
					break;
			}
		}
	}
	
	return($has_bought);
}



/**
 * Queue admin menu icons CSS
 * 
 */
function swr_admin_menu_styles() {
	global $woocommerce;
	wp_enqueue_style('swr_admin_menu_styles', plugins_url('synmedia-woocommerce-rewards/assets/css/swr.admin.css'));
}
add_action('admin_print_styles', 'swr_admin_menu_styles');

function swr_meta_boxes($post_id){
	$data = get_post_custom($post_id);
?>
		<h4><?php _e('Rewards', 'rewards'); ?></h4>
		<ul class="totals">
			
			<li class="left">
				<label><?php _e('Earned:', 'rewards'); ?></label>
				<input type="text" id="_rewards_earned" name="_rewards_earned" placeholder="0.00" value="<?php if (isset($data['_rewards_earned'][0])) echo $data['_rewards_earned'][0];
				?>" class="first rewards" />
			</li>
			
			<li class="right">
				<label><?php _e('Used:', 'rewards'); ?></label>
				<input type="text" name="_rewards_used" id="_rewards_used" value="<?php 
				if (isset($data['_rewards_used'][0])) echo $data['_rewards_used'][0];
				?>" placeholder="0.00" class="rewards" />
			</li>
	
		</ul>
		<div style="display:none;"><span class="calc_rewards_span">&nbsp;<button type="button" class="button calc_rewards"><?php _e('Calc rewards &rarr;', 'rewards'); ?></button></span></div>
		<style>
			.calc_rewards{
				margin-left: 5px;
			}
		</style>
<?php
}
/* add_action( 'woocommerce_admin_order_totals_after_shipping', 'swr_meta_boxes' ); */

function swr_process_shop_order_meta($order_id, $post){
	global $swr_settings;
	$swr_settings->set_reward_earned_for_order($order_id, stripslashes($_POST['_rewards_earned']));
	$swr_settings->set_reward_used_for_order($order_id, stripslashes($_POST['_rewards_used']));
	$completed = $swr_settings->get_reward_status_for_order($order_id);
	if($completed){
		
	}
}
/* add_action('woocommerce_process_shop_order_meta', 'swr_process_shop_order_meta', 1, 2); */

function swr_product_options_pricing(){
	global $swr_settings, $woocommerce;
	if($swr_settings->specific_product()){
		woocommerce_wp_text_input(array( 'id' => '_reward_price', 'class' => 'wc_input_price short', 'label' => sprintf(__('Required %s', 'rewards'), $swr_settings->get_title())));
	}
	woocommerce_wp_text_input(array( 'id' => '_reward', 'class' => 'short', 'label' => sprintf(__('Extra %s', 'rewards'), $swr_settings->get_title())));
}

function swr_process_product_meta($post_id, $post){
	update_post_meta($post_id, '_reward', stripslashes( $_POST['_reward'] ));
	update_post_meta($post_id, '_reward_price', stripslashes( $_POST['_reward_price'] ));
}

function swr_get_price_html($price, $product){
	global $swr_settings, $woocommerce;
	$reward_price = get_post_meta($product->id, '_reward_price', true);
	if(!empty($reward_price)){
		$price .= sprintf(__(' or %s %s', 'rewards'), $reward_price, $swr_settings->get_title());
	}
	return($price);
}

function swr_after_add_to_cart_button(){
	global $swr_settings;
	echo('<div style="display:none;" id="buywithpoints_container"><button type="submit" class="button alt buywithpoints">'.sprintf(__('Buy with %s','rewards'), $swr_settings->get_title()).'</button></div>');
}

function swr_product_after_variable_attributes($loop, $variation_data){
	global $swr_settings;
?>
<tr>
	<td><label><?php echo(sprintf(__('Extra %s', 'rewards'), $swr_settings->get_title())); ?></label><input type="text" size="5" name="extra_rewards[<?php echo $loop; ?>]" value="<?php if (isset($variation_data['_reward'][0])) echo $variation_data['_reward'][0]; ?>" /></td>

	<td>
	<?php if($swr_settings->specific_product()): ?>
	<label><?php echo(sprintf(__('Required %s', 'rewards'), $swr_settings->get_title())); ?></label><input type="text" size="5" name="reward_price[<?php echo $loop; ?>]" value="<?php if (isset($variation_data['_reward_price'][0])) echo $variation_data['_reward_price'][0]; ?>" />
	<?php endif; ?>
	</td>
</tr>
<?php
}

function swr_process_product_meta_variable( $post_id ) {
	global $woocommerce, $wpdb; 
	
	if (isset($_POST['variable_sku'])) :
	
		$variable_post_id 		= $_POST['variable_post_id'];
		$extra_rewards			= $_POST['extra_rewards'];
		$reward_price			= $_POST['reward_price'];
		
		$max_loop = max( array_keys( $_POST['variable_post_id'] ) );
		
		for ( $i=0; $i <= $max_loop; $i++ ) :
			
			if ( ! isset( $variable_post_id[$i] ) ) continue;
			
			$variation_id = (int) $variable_post_id[$i];
			
			update_post_meta( $variation_id, '_reward', $extra_rewards[$i] );
			update_post_meta( $variation_id, '_reward_price', $reward_price[$i] );
		 	
		 endfor; 
		 
	endif;

}
add_action('woocommerce_process_product_meta_variable', 'swr_process_product_meta_variable');

/**
 * Define columns to show on the users page.
 *
 * @access public
 * @param array $columns Columns on the manage users page
 * @return array The modified columns
 */
function swr_user_columns( $columns ) {
	if ( ! current_user_can( 'manage_woocommerce' ) )
		return $columns;

	$columns['swr_rewards_balance'] = __('Rewards', 'rewards');
	return $columns;
}

add_filter( 'manage_users_columns', 'swr_user_columns', 10, 1 );

/**
 * Define values for custom columns.
 *
 * @access public
 * @param mixed $value The value of the column being displayed
 * @param mixed $column_name The name of the column being displayed
 * @param mixed $user_id The ID of the user being displayed
 * @return string Value for the column
 */
function swr_user_column_values( $value, $column_name, $user_id ) {
	global $woocommerce, $wpdb, $swr_settings;
	switch ($column_name) :
		case "swr_rewards_balance" :

			$value = swr_get_user_current_rewards(array('user_id'=>$user_id, 'formatted'=>true)).' '.$swr_settings->get_title();

		break;
	endswitch;
	return $value;
}

add_action( 'manage_users_custom_column', 'swr_user_column_values', 10, 3 );

function swr_email_after_order_table($order){
	global $swr_settings;
	$terms = wp_get_object_terms( $order->id, 'shop_order_status', array('fields' => 'slugs') );
	$status = (isset($terms[0])) ? $terms[0] : 'pending';
	$rewards_earned = $swr_settings->get_reward_earned_for_order($order->id);
	if($rewards_earned > 0 && $status=='completed'){
		echo('<p>'.sprintf(__('Your %s %s are now available.', 'rewards'), $rewards_earned, $swr_settings->get_title()).'</p>');
	}
}

?>