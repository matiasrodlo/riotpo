<?php
/**
 * WooCommerce Points and Rewards
 *
 * @package     WC-Points-Rewards/Classes
 * @author      WooThemes
 * @copyright   Copyright (c) 2013, WooThemes
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Cart / Checkout class
 *
 * Adds earn/redeem messages to the cart / checkout page and calculates the discounts available
 *
 * @since 1.0
 */
class WC_Points_Rewards_Cart_Checkout {


	/**
	 * Add cart/checkout related hooks / filters
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// add earn points/redeem points message above cart / checkout
		add_action( 'woocommerce_before_cart', array( $this, 'render_earn_points_message' ), 15 );
		add_action( 'woocommerce_before_cart', array( $this, 'render_redeem_points_message' ), 16 );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'render_earn_points_message' ), 5 );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'render_redeem_points_message' ), 6 );

		// handle the apply discount submit on the cart page
		add_action( 'wp', array( $this, 'maybe_apply_discount' ) );

		// handle the apply discount AJAX submit on the checkout page
		add_action( 'wp_ajax_wc_points_rewards_apply_discount', array( $this, 'ajax_maybe_apply_discount' ) );
	}


	/**
	 * Redeem the available points by generating and applying a discount code on the cart page
	 *
	 * @since 1.0
	 */
	public function maybe_apply_discount() {
		global $woocommerce;

		// only apply on cart and from apply discount action
		if ( ! is_cart() || ! isset( $_POST['wc_points_rewards_apply_discount'] ) )
			return;

		// bail if the discount has already been applied
		if ( $woocommerce->cart->has_discount( WC_Points_Rewards_Discount::get_discount_code() ) )
			return;

		// generate and set unique discount code
		$discount_code = WC_Points_Rewards_Discount::generate_discount_code();

		// apply the discount
		$woocommerce->cart->add_discount( $discount_code );
	}


	/**
	 * Redeem the available points by generating and applying a discount code via AJAX on the checkout page
	 *
	 * @since 1.0
	 */
	public function ajax_maybe_apply_discount() {
		global $woocommerce;

		// security check
		check_ajax_referer( 'apply-coupon', 'security' );

		// bail if the discount has already been applied
		if ( $woocommerce->cart->has_discount( WC_Points_Rewards_Discount::get_discount_code() ) )
			die;

		// generate and set unique discount code
		$discount_code = WC_Points_Rewards_Discount::generate_discount_code();

		// apply the discount
		$woocommerce->cart->add_discount( $discount_code );

		$woocommerce->show_messages();

		die;
	}


	/**
	 * Renders a message above the cart displaying how many points the customer will receive for completing their purchase
	 *
	 * @since 1.0
	 */
	public function render_earn_points_message() {

		global $wc_points_rewards;

		// get the total points earned for this purchase
		$points_earned = $this->get_points_earned_for_purchase();

		$message = get_option( 'wc_points_rewards_earn_points_message' );

		// bail if no message set or no points will be earned for purchase
		if ( ! $message || ! $points_earned )
			return;

		// points earned
		$message = str_replace( '{points}', number_format_i18n( $points_earned ), $message );

		// points label
		$message = str_replace( '{points_label}', $wc_points_rewards->get_points_label( $points_earned ), $message );

		// wrap with info div
		$message = '<div class="woocommerce-info wc_points_rewards_earn_points">' . $message . '</div>';

		echo apply_filters ( 'wc_points_rewards_earn_points_message', $message, $points_earned );
	}


	/**
	 * Renders a message and button above the cart displaying the points available to redeem for a discount
	 *
	 * @since 1.0
	 */
	public function render_redeem_points_message() {
		global $woocommerce, $wc_points_rewards;

		// don't display a message if coupons are disabled or points have already been applied for a discount
		if ( ! $woocommerce->cart->coupons_enabled() || $woocommerce->cart->has_discount( WC_Points_Rewards_Discount::get_discount_code() ) )
			return;

		// get the total discount available for redeeming points
		$discount_available = $this->get_discount_for_redeeming_points();

		$message = get_option( 'wc_points_rewards_redeem_points_message' );

		// bail if no message set or no points will be earned for purchase
		if ( ! $message || ! $discount_available )
			return;

		// points required to redeem for the discount available
		$points = WC_Points_Rewards_Manager::calculate_points_for_discount( $discount_available );
		$message = str_replace( '{points}', number_format_i18n( $points ), $message );

		// the maximum discount available given how many points the customer has
		$message = str_replace( '{points_value}', woocommerce_price( $discount_available ), $message );

		// points label
		$message = str_replace( '{points_label}', $wc_points_rewards->get_points_label( $points ), $message );

		// add 'Apply Discount' button
		$message .= '<form class="wc_points_rewards_apply_discount" action="' . esc_url( $woocommerce->cart->get_cart_url() ) . '" method="post">';
		$message .= '<input type="submit" class="button" name="wc_points_rewards_apply_discount" value="' . __( 'Apply Discount', 'wc_points_rewards' ) . '" /></form>';

		// wrap with info div
		$message = '<div class="woocommerce-info wc_points_rewards_redeem_points">' . $message . '</div>';

		echo apply_filters ( 'wc_points_rewards_redeem_points_message', $message, $discount_available );

		// add AJAX submit for applying the discount on the checkout page
		if ( is_checkout() ) {
			$woocommerce->add_inline_js( '
			/* Points & Rewards AJAX Apply Points Discount */
			$( "form.wc_points_rewards_apply_discount" ).submit( function() {
				var $section = $( "div.wc_points_rewards_redeem_points" );

				if ( $section.is( ".processing" ) ) return false;

				$section.addClass( "processing" ).block({message: null, overlayCSS: {background: "#fff url(" + woocommerce_params.ajax_loader_url + ") no-repeat center", backgroundSize: "16px 16px", opacity: 0.6}});

				var data = {
					action:    "wc_points_rewards_apply_discount",
					security:  woocommerce_params.apply_coupon_nonce
				};

				$.ajax({
					type:     "POST",
					url:      woocommerce_params.ajax_url,
					data:     data,
					success:  function( code ) {

						$( ".woocommerce-error, .woocommerce-message" ).remove();
						$section.removeClass( "processing" ).unblock();

						if ( code ) {
							$section.before( code );

							$section.remove();

							$( "body" ).trigger( "update_checkout" );
						}
					},
					dataType: "html"
				});
				return false;
			});
			' );
		}
	}


	/**
	 * Returns the amount of points earned for the purchase, calculated by getting the points earned for each individual
	 * product purchase multiplied by the quantity being ordered
	 *
	 * @since 1.0
	 */
	private function get_points_earned_for_purchase() {
		global $woocommerce;

		$points_earned = 0;

		foreach ( $woocommerce->cart->cart_contents as $item_key => $item ) {

			$points_earned += WC_Points_Rewards_Product::get_points_earned_for_product_purchase( $item['data'] ) * $item['quantity'];
		}

		// reduce by any discounts.  One minor drawback: if the discount includes a discount on tax and/or shipping
		//  it will cost the customer points, but this is a better solution than granting full points for discounted orders
		$discount = $woocommerce->cart->discount_cart + $woocommerce->cart->discount_total;
		$points_earned -= min( WC_Points_Rewards_Manager::calculate_points( $discount ), $points_earned );

		// check if applied coupons have a points modifier and use it to adjust the points earned
		$coupons = $woocommerce->cart->get_applied_coupons();

		if ( ! empty( $coupons ) ) {

			$points_modifier = 0;

			// get the maximum points modifier if there are multiple coupons applied, each with their own modifier
			foreach ( $coupons as $coupon_code ) {

				$coupon = new WC_Coupon( $coupon_code );

				if ( ! empty( $coupon->coupon_custom_fields['_wc_points_modifier'][0] ) && $coupon->coupon_custom_fields['_wc_points_modifier'][0] > $points_modifier )
					$points_modifier = $coupon->coupon_custom_fields['_wc_points_modifier'][0];
			}

			if ( $points_modifier > 0 )
				$points_earned = round( $points_earned * ( $points_modifier / 100 ) );
		}

		return apply_filters( 'wc_points_rewards_points_earned_for_purchase', $points_earned, $woocommerce->cart );
	}


	/**
	 * Returns the maximum possible discount available given the total amount of points the customer has
	 *
	 * @since 1.0
	 */
	public static function get_discount_for_redeeming_points() {
		global $woocommerce;

		// get the value of the user's point balance
		$available_user_discount = WC_Points_Rewards_Manager::get_users_points_value( get_current_user_id() );

		// no discount
		if ( $available_user_discount <= 0 )
			return 0;

		$discount_applied = 0;

		// calculate the discount to be applied by iterating through each item in the cart and calculating the individual
		// maximum discount available
		foreach ( $woocommerce->cart->cart_contents as $item_key => $item ) {

			$max_discount = WC_Points_Rewards_Product::get_maximum_points_discount_for_product( $item['data'] );

			if ( is_numeric( $max_discount ) ) {

				// adjust the max discount by the quantity being ordered
				$max_discount *= $item['quantity'];

				// if the discount available is greater than the max discount, apply the max discount
				$discount = ( $available_user_discount <= $max_discount ) ? $available_user_discount : $max_discount;

			} else {

				// otherwise just use the available discount
				$discount = $available_user_discount;
			}

			// add the discount to the amount to be applied
			$discount_applied += $discount;

			// reduce the remaining discount available to be applied
			$available_user_discount -= $discount;
		}

		// if the available discount is greater than the order total, make the discount equal to the order total less any other discounts
		$discount_applied = max( 0, min( $discount_applied, $woocommerce->cart->subtotal_ex_tax - $woocommerce->cart->discount_total - $woocommerce->cart->discount_cart ) );

		return $discount_applied;
	}


} // end \WC_Points_Rewards_Cart_Checkout class
