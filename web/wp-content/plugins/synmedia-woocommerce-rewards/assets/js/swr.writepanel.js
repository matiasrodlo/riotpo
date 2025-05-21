jQuery(function($){
	$(".calc_totals").after($(".calc_rewards_span"));
	$(".calc_rewards").click(function(){
		// Block write panel
		$('#woocommerce-order-totals').block({ message: null, overlayCSS: { background: '#fff url(' + woocommerce_writepanel_params.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
			
		var answer = confirm('Do you really want to recalculate the rewards?');
		
		if (answer) {
			
			// Get row totals
			var line_subtotals 		= 0;
			var line_subtotal_taxes = 0;
			var line_totals 		= 0;
			var cart_discount 		= 0;
			var cart_tax 			= 0;
			var order_shipping 		= parseFloat( $('#_order_shipping').val() );
			var order_shipping_tax 	= parseFloat( $('#_order_shipping_tax').val() );
			var order_discount		= parseFloat( $('#_order_discount').val() );
			
			if ( ! order_shipping ) order_shipping = 0;
			if ( ! order_shipping_tax ) order_shipping_tax = 0;
			if ( ! order_discount ) order_discount = 0;
			
			$('#order_items_list tr.item').each(function(){
				
				var line_subtotal 		= parseFloat( $(this).find('input.line_subtotal').val() );
				var line_subtotal_tax 	= parseFloat( $(this).find('input.line_subtotal_tax').val() );
				var line_total 			= parseFloat( $(this).find('input.line_total').val() );
				var line_tax 			= parseFloat( $(this).find('input.line_tax').val() );
				
				if ( ! line_subtotal ) line_subtotal = 0;
				if ( ! line_subtotal_tax ) line_subtotal_tax = 0;
				if ( ! line_total ) line_total = 0;
				if ( ! line_tax ) line_tax = 0;
				
				line_subtotals = parseFloat( line_subtotals + line_subtotal );
				line_subtotal_taxes = parseFloat( line_subtotal_taxes + line_subtotal_tax );
				line_totals = parseFloat( line_totals + line_total );
				
				if (woocommerce_writepanel_params.round_at_subtotal=='no') {
					line_tax = parseFloat( line_tax.toFixed( 2 ) );
				}
				
				cart_tax = parseFloat( cart_tax + line_tax );
				
			});
			
			// Tax
			if (woocommerce_writepanel_params.round_at_subtotal=='yes') {
				cart_tax = parseFloat( cart_tax.toFixed( 2 ) );
			}
			
			// Cart discount
			var cart_discount = ( (line_subtotals + line_subtotal_taxes) - (line_totals + cart_tax) );
			if (cart_discount<0) cart_discount = 0;
			cart_discount = cart_discount.toFixed( 2 );
			
			// Total
			var order_total = line_totals + cart_tax + order_shipping + order_shipping_tax - order_discount;
			order_total = order_total.toFixed( 2 );
			
			// Set fields
			$('#_cart_discount').val( cart_discount );
			$('#_order_tax').val( cart_tax );
			$('#_order_total').val( order_total );
			
			// Since we currently cannot calc shipping from the backend, ditch the rows. They must be manually calculated.
			$('#tax_rows').empty();

			$('#woocommerce-order-totals').unblock();

		} else {
			$('#woocommerce-order-totals').unblock();
		}
		return false;
	}).hover(function(){
		$('.rewards').css('background-color', '#d8c8d2');
	}, function(){
		$('.rewards').css('background-color', '');
	});
});