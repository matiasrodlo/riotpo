<?php

function swr_coupon_discount_types($coupon_discount_types){
	$coupon_discount_types['fixed_reward'] = __('Rewards Bonus', 'rewards');
	$coupon_discount_types['percent_reward'] = __('Rewards % Bonus', 'rewards');
	return($coupon_discount_types);
}
add_filter('woocommerce_coupon_discount_types', 'swr_coupon_discount_types', 1);

?>