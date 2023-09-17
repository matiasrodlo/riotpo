jQuery(document).ready(function($){
	$("body").bind("updated_checkout updated_shipping_method", function(){
		var newamount = $(".updated_rewards:last .amount").html();
		var newamountwithtext = $(".updated_rewards").html();
		$(".swr_cart_shortcode_amount .amount").html(newamount);
		$(".swr_get_rewards").html(newamountwithtext);
		$(".updated_rewards").remove();
		$(".swr_use_rewards:not(:first)").remove();
	});
	$(".swr_use_rewards input").live("click", function(){
		$('.swr_use_rewards input').filter(':checked').not(this).removeAttr('checked');
		$('body').trigger('update_checkout');
	});
	$("#createaccount").live("click", function(){
		$('body').trigger('update_checkout');
	});
	
	if($(".updated_rewards").length > 0){
		var newamountwithtext = $(".updated_rewards:last").html();
		$(".updated_rewards").remove();
		$(".swr_get_rewards").html(newamountwithtext);
	}
	
	$('.payment_methods input').live("click", function(){
	
		var payment_method 	= $('#order_review input[name=payment_method]:checked').val();
	
		var data = {
			action: 			'swr_update_payment_method',
			security: 			woocommerce_params.update_order_review_nonce,
			payment_method:		payment_method
		};
		
		$.post(woocommerce_params.ajax_url, data, function(json) {
		
			$(".swr_get_rewards").html(json.rewards);
			
		}, 'json');
		
	});
});