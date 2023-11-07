<?php
//Set Output Header
header('Content-Type: application/json; charset=UTF-8');

//Prevent unwanted echoes
ob_start();

//
// Requirements to interact with WordPress
//
$parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
require_once($parse_uri[0] . 'wp-load.php');

//Get plugin options
$options = $wpdb->get_var(
	"
	SELECT option_value
	FROM $wpdb->options
	WHERE option_name = 'woocommerce_efs_options'
	"
);

$optionsArray = unserialize($options);
$efs_merchant_id = $optionsArray['efs_merchant_id'];
$efs_merchant_token = $optionsArray['efs_merchant_token'];
$efs_order_status = $optionsArray['efs_order_status'];

//
// Check for incoming efs_merchant_token & efs_merchant_id to match options
//
if($efs_merchant_id == $_GET['efs_merchant_id'] && $efs_merchant_token == $_GET['efs_merchant_token'])
{
	//
	// Get Orders
	//
	$statusFilter = $efs_order_status;
	$orders = $wpdb->get_results( 
		"
		SELECT 
			p.ID AS OrderNumber,
			p.post_excerpt AS OrderComments,
			t.name AS OrderStatus
			
		FROM $wpdb->posts p
		JOIN $wpdb->term_relationships tr ON tr.object_id = p.ID
		JOIN $wpdb->term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
		JOIN $wpdb->terms t ON t.term_id = tt.term_id
		WHERE p.post_type = 'shop_order'
		AND t.name = '{$statusFilter}'
		"
	);

	//
	// Continue if orders are available
	//
	$orderCount = count($orders);
	if($orderCount > 0)
	{
		//
		// Get Order Details
		//
		$parsedOrders = array();
		foreach($orders as $order)
		{
			$parsedOrder = array();
			$parsedOrder['OrderComments'] = $order->OrderComments;
				
			$orderMeta = $wpdb->get_results(
				"
				SELECT *
				FROM $wpdb->postmeta
				WHERE post_id = {$order->OrderNumber}
				"
			);
			
			foreach($orderMeta as $meta)
			{
				$parsedOrder[$meta->meta_key] = $meta->meta_value;
			}
			$parsedOrder['ID'] = $order->OrderNumber;
			$parsedOrders[$order->OrderNumber] = $parsedOrder;
		}
		
		//
		// Loop through orders, build array for json
		//
		$ordersArray = array();
		foreach($parsedOrders as $orderNumber => $orderData)
		{
			//
			// Retrieve SKU and build item XML
			//
			$wooOrder = new WC_Order($orderNumber);
			$newShippingMethod = $wooOrder->get_shipping_method();
			if(strlen($newShippingMethod) > 0)
			{
				$shipping_method = $newShippingMethod;
			} else {
				$shipping_method = (strlen($orderData['_shipping_method_title']) > 0 ? $orderData['_shipping_method_title'] : $orderData['_shipping_method']);
			}

			$orderItems = $wooOrder->get_items();
			$itemsArray = array();
			foreach($orderItems as $orderItem)
			{
				if($orderItem['variation_id'] > 0)
				{
					$skuId = $orderItem['variation_id'];
				} else {
					$skuId = $orderItem['product_id'];
				}
				$sku = $wpdb->get_var(
					"
					SELECT meta_value
					FROM $wpdb->postmeta
					WHERE post_id = {$skuId}
					AND meta_key = '_sku'
					"
				);
				if(!$sku) $sku = "NO_SKU_ASSIGNED";
				
				$itemsArray[] = array(
					"id" => (string)$skuId,
					"sku" => $sku,
					"quantity" => (int)$orderItem['qty']
				);	
			}
			
			//
			// Build order Json
			//
			$ordersArray[] = array
			(
				"cart_id" => $orderData['ID'],
				"order_number" => (strlen($orderData['_order_number']) > 0 ? (string)$orderData['_order_number'] : (string)$orderNumber),
				"shipping_method" => $shipping_method,
				"billing_address" => array(
					"first_name" => $orderData['_billing_first_name'],
					"last_name" => $orderData['_billing_last_name'],
					"company" => $orderData['_billing_company'],
					"address1" => $orderData['_billing_address_1'],
					"address2" => $orderData['_billing_address_2'],
					"city" => $orderData['_billing_city'],
					"province" => $orderData['_billing_state'],
					"postal_code" => $orderData['_billing_postcode'],
					"country" => $orderData['_billing_country'],
					"phone" => $orderData['_billing_phone'],
					"email" => $orderData['_billing_email']			
				),
				"shipping_address" => array(
					"first_name" => $orderData['_shipping_first_name'],
					"last_name" => $orderData['_shipping_last_name'],
					"company" => $orderData['_shipping_company'],
					"address1" => $orderData['_shipping_address_1'],
					"address2" => $orderData['_shipping_address_2'],
					"city" => $orderData['_shipping_city'],
					"province" => $orderData['_shipping_state'],
					"postal_code" => $orderData['_shipping_postcode'],
					"country" => $orderData['_shipping_country'],
					"phone" => $orderData['_shipping_phone'],
					"email" => $orderData['_shipping_email']
				),
				"items" => $itemsArray
			);
		}
		
		$json = array(
			"status" => "success",
			"orders" => $ordersArray
		);
	
	} else {
		$json = array(
			"status" => "success",
			"orders" => "[]"
		);
	}
	status_header( 200 );
} else {
	status_header( 400 );
	$json = array(
		"status" => "failure",
		"error" => "invalid efs_merchant_id or efs_merchant_token"
	);
}
ob_clean();
echo json_encode($json);
ob_end_flush();
?>