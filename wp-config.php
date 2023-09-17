<?php
/** Enable W3 Total Cache Edge Mode */
define('W3TC_EDGE_MODE', true); // Added by W3 Total Cache

/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

/** 
 * Configuración básica de WordPress.
 *
 * Este archivo contiene las siguientes configuraciones: ajustes de MySQL, prefijo de tablas,
 * claves secretas, idioma de WordPress y ABSPATH. Para obtener más información,
 * visita la página del Codex{@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} . Los ajustes de MySQL te los proporcionará tu proveedor de alojamiento web.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** Ajustes de MySQL. Solicita estos datos a tu proveedor de alojamiento web. ** //
/** El nombre de tu base de datos de WordPress */
define('DB_NAME', 'matiash_base');

/** Tu nombre de usuario de MySQL */
define('DB_USER', 'matiash_user');

/** Tu contraseña de MySQL */
define('DB_PASSWORD', 'elbosque15');

/** Host de MySQL (es muy probable que no necesites cambiarlo) */
define('DB_HOST', 'localhost');

/** Codificación de caracteres para la base de datos. */
define('DB_CHARSET', 'utf8');

/** Cotejamiento de la base de datos. No lo modifiques si tienes dudas. */
define('DB_COLLATE', '');

/**#@+
 * Claves únicas de autentificación.
 *
 * Define cada clave secreta con una frase aleatoria distinta.
 * Puedes generarlas usando el {@link https://api.wordpress.org/secret-key/1.1/salt/ servicio de claves secretas de WordPress}
 * Puedes cambiar las claves en cualquier momento para invalidar todas las cookies existentes. Esto forzará a todos los usuarios a volver a hacer login.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'w+Of]}93j%IwqvbWKIPw6-3jQ<!J[`/lqKYS)q%bDlM~-3<iT/g9?0#aS)/<q:iS'); // Cambia esto por tu frase aleatoria.
define('SECURE_AUTH_KEY', 'B6o~ed=9Y7s5|x3E`e0T|#( j5yR,|39AmedbeDr^3`$aCXYy_o6,]|XIq]wmE9>'); // Cambia esto por tu frase aleatoria.
define('LOGGED_IN_KEY', 'i9>I|L$;I4T<vK+S+wX:>pc{p^=zQtpf/<y]+Hy6<!xbJ4#>r^G]hI,1i#BQCxbR'); // Cambia esto por tu frase aleatoria.
define('NONCE_KEY', 'V+CxE*/yd7-ZYuut@/:)++A`r8Yg&bS:7FB:FPKy<6& ;C8oI[?[}hlV};,6cac#'); // Cambia esto por tu frase aleatoria.
define('AUTH_SALT', '?|_-B93>6*24;1aKFN+0(wl-iTPw3q-HS-FfkM14b1o1EFm7?b_57%`P-vp6!#<9'); // Cambia esto por tu frase aleatoria.
define('SECURE_AUTH_SALT', '?^_z-%sG00szQUn0xbdX}tN)gjwC3=baV*h95%zZk8#%3%E())QQJi+NQ`{hEOrC'); // Cambia esto por tu frase aleatoria.
define('LOGGED_IN_SALT', '&G#mV-2{,5nAO`e%&L&LGv+^ObYr`Q`tu!X*Q.F43Rs+>mvIn*2&!2s$waTLV!fx'); // Cambia esto por tu frase aleatoria.
define('NONCE_SALT', 'xOt|T]^+@#Ss2Sn.e9PF(6*?4$+:x0iHcci1`H7J$[p}1|5Nk{>Q#Kq[/KQ<DUP6'); // Cambia esto por tu frase aleatoria.

/**#@-*/

/**
 * Prefijo de la base de datos de WordPress.
 *
 * Cambia el prefijo si deseas instalar multiples blogs en una sola base de datos.
 * Emplea solo números, letras y guión bajo.
 */
$table_prefix  = 'wp_';

/**
 * Idioma de WordPress.
 *
 * Cambia lo siguiente para tener WordPress en tu idioma. El correspondiente archivo MO
 * del lenguaje elegido debe encontrarse en wp-content/languages.
 * Por ejemplo, instala ca_ES.mo copiándolo a wp-content/languages y define WPLANG como 'ca_ES'
 * para traducir WordPress al catalán.
 */
define('WPLANG', 'es_ES');

/**
 * Para desarrolladores: modo debug de WordPress.
 *
 * Cambia esto a true para activar la muestra de avisos durante el desarrollo.
 * Se recomienda encarecidamente a los desarrolladores de temas y plugins que usen WP_DEBUG
 * en sus entornos de desarrollo.
 */
define('WP_DEBUG', false);

/* ¡Eso es todo, deja de editar! Feliz blogging */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

