<?php
/**
 * My Orders
 *
 * Shows recent orders on the account page
 */
 
global $woocommerce, $recent_orders, $swr_settings, $swr_refer;

$customer_id = get_current_user_id();

if( $swr_settings->current_user_can_use_rewards() ){

$args = array(
    'numberposts'     => $recent_orders,
    'meta_key'        => '_customer_user',
    'meta_value'	  => $customer_id,
    'post_type'       => 'shop_order',
    'post_status'     => 'publish' 
);

$customer_orders = get_posts($args);

$msg = sprintf(__('Current %s balance: %s', 'rewards'), $swr_settings->get_title(), swr_get_user_current_rewards(array('user_id'=>$customer_id, 'formatted'=>true)));

?>

<?php if( $swr_settings->show_top_rewards() ){ ?>
<p class="<?php echo((WOOCOMMERCE_VERSION >= 2)?'woocommerce-info':'woocommerce_info') ?> swr_get_rewards"><?php echo($msg); ?></p>
<?php } ?>

<?php

if ($customer_orders) :
?>
	
	<h2><?php _e("Orders", 'rewards'); ?></h2>
	<table class="shop_table my_account_orders">
	
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
<h2><?php _e("Reviews", 'rewards'); ?></h2>
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
if( isset($swr_refer) && $swr_refer->is_enabled() ):

$refs = $swr_refer->get_raf_orders( $customer_id );

if( $refs && is_array($refs) && !empty($refs) ):
?>
<h2><?php _e("Refer a friend", 'rewards'); ?></h2>
	<table class="shop_table my_account_orders">
	
		<thead>
			<tr>
				<th class="order-number" style="width:35%;"><span class="nobr"><?php _e('Date', 'rewards'); ?></span></th>
				<th class="order-total rewards-product" style="width:20%;"><span class="nobr"><?php _e('Username', 'rewards'); ?></span></th>
				<th class="order-total rewards-earned" style="width:20%;"><span class="nobr"><?php _e('Total earned', 'rewards'); ?></span></th>
				<th class="order-total rewards-status" style="width:25%;"><span class="nobr"><?php _e('Status', 'rewards'); ?></span></th>
			</tr>
		</thead>
		
		<tbody><?php
			foreach ($refs as $order) :
				?><tr class="comment">
					<td class="order-number"><?php echo date_i18n(get_option('date_format'), strtotime($order['date'])); ?></td>
					<td class="order-total rewards-product"><?php echo($order['username']); ?></td>
					<td class="order-total rewards-earned"><?php echo($order['rewards']); ?></td>
					<td class="order-total rewards-status"><?php echo(($order['status']==1?__( 'Waiting order completion', 'wc_rewards' ):__( 'Completed', 'wc_rewards' ))); ?></td>
				</tr><?php
			endforeach;
		?></tbody>
	
	</table>
<?php else : ?>
<p><?php _e('No friend subscribed.', 'wc_rewards'); ?></p>
<?php endif; ?>

<?php
endif;
?>

<?php
	
}else{
	$msg = sprintf( __( "Your user role cannot use or get %s, if you think this is a mistake you can contact us.", 'rewards' ), $swr_settings->get_title() );
?>

<p class="<?php echo((WOOCOMMERCE_VERSION >= 2)?'woocommerce-info':'woocommerce_info') ?> swr_get_rewards"><?php echo($msg); ?></p>

<?php } ?>