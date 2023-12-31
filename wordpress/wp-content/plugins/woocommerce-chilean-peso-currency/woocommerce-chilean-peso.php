<?php

/*
  Plugin Name: WooCommerce Chilean Peso + Chilean States
  Plugin URI: http://plugins.svn.wordpress.org/woocommerce-chilean-peso-currency/
  Description: This plugin add the chilean currency, symbol , paypal and the states to WooCommerce.
  Version: 2.4.1
  Author: Cristian Tala Sánchez <cristian.tala@gmail.com>
  Author URI: http://www.cristiantala.cl
  License: GPLv3
  Requires at least: 3.0 +
  Tested up to: 3.7.1
 */
/*
 *      Copyright 2012 Cristian Tala Sánchez <cristian.tala@gmail.com>
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 3 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

function add_clp_currency($currencies) {
    $currencies["CLP"] = 'Pesos Chilenos';
    return $currencies;
}

function add_clp_currency_symbol($currency_symbol, $currency) {
    switch ($currency) {
        case 'CLP': $currency_symbol = '$';
            break;
    }
    return $currency_symbol;
}

function custom_woocommerce_states($states) {

    $states['CL'] = array(
        'I' => 'I de Tarapacá',
        'II' => 'II de Antofagasta',
        'III' => 'III de Atacama',
        'IV' => 'IV de Coquimbo',
        'V' => 'V de Valparaíso',
        'VI' => 'VI del Libertador General Bernardo O\'Higgins',
        'VII' => 'VII del Maule',
        'VIII' => 'VIII del Bío Bío',
        'IX' => 'IX de la Araucanía',
        'XIV' => 'XIV de los Ríos',
        'X' => 'X de los Lagos',
        'XI' => 'XI Aysén del General Carlos Ibáñez del Campo',
        'XII' => 'XII de Magallanes y Antártica Chilena',
        'RM' => 'Metropolitana de Santiago',
        'XV' => 'XV de Arica y Parinacota',
    );

    return $states;
}


    function add_clp_paypal_valid_currency( $currencies ) {  
     array_push ( $currencies , 'CLP' );
     return $currencies;  
    } 


function convert_clp_to_usd($paypal_args){
	if ( $paypal_args['currency_code'] == 'CLP'){
		$convert_rate = 520; //set the converting rate
		$paypal_args['currency_code'] = 'USD'; //change CLP to USD
		$i = 1;

		while (isset($paypal_args['amount_' . $i])) {
			$paypal_args['amount_' . $i] = round( $paypal_args['amount_' . $i] / $convert_rate, 2);
			++$i;
		}

	}
return $paypal_args;
}


// Se eliminan los datos postales como obligatorios.
function postalcode_override_default_address_fields( $address_fields ) {
     $address_fields['postcode']['required'] = false;

     return $address_fields;
}

//Se eliminan los códigos portales como obligatorios. (Filtro)
add_filter( 'woocommerce_default_address_fields' , 'postalcode_override_default_address_fields' );

add_filter('woocommerce_paypal_args', 'convert_clp_to_usd');
add_filter('woocommerce_states', 'custom_woocommerce_states');
add_filter('woocommerce_currencies', 'add_clp_currency', 10, 1);
add_filter('woocommerce_currency_symbol', 'add_clp_currency_symbol', 10, 2);
add_filter( 'woocommerce_paypal_supported_currencies', 'add_clp_paypal_valid_currency' );     
?>
