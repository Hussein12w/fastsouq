<?php
/**
 * Plugin Name: FastSouq API
 * Description: REST API for the FastSouq mobile app (iPhone / Android). Uses the FastSouq theme's own data and rules: categories, filters, posting (with or without an account), favorites, reports, sponsored banners.
 * Version: 1.0.0
 * Author: FastSouq
 * Requires PHP: 7.4
 * Text Domain: fastsouq-api
 *
 * Base URL: https://fastsouq.ae/wp-json/fastsouq/v1/   — full reference in API.md
 */
defined( 'ABSPATH' ) || exit;

define( 'FSAPI_VERSION', '1.0.0' );
define( 'FSAPI_NS', 'fastsouq/v1' );

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/format.php';
require __DIR__ . '/includes/routes-public.php';
require __DIR__ . '/includes/routes-ads.php';
require __DIR__ . '/includes/routes-account.php';

/** The API needs the FastSouq theme (it holds the categories, fields and rules). */
function fsapi_ready() {
	return function_exists( 'qs_save_ad' ) && function_exists( 'qs_listing_query_vars' );
}

/* Language per request: ?lang=ar|en or the Accept-Language header (default Arabic). */
add_filter(
	'rest_pre_dispatch',
	function ( $result, $server, $request ) {
		if ( 0 !== strpos( $request->get_route(), '/' . FSAPI_NS ) ) {
			return $result;
		}
		if ( ! fsapi_ready() ) {
			return new WP_Error( 'theme_missing', 'The FastSouq theme must be active.', array( 'status' => 503 ) );
		}
		$lang = $request->get_param( 'lang' );
		if ( ! $lang ) {
			$h    = strtolower( (string) $request->get_header( 'accept_language' ) );
			$lang = 0 === strpos( $h, 'en' ) ? 'en' : 'ar';
		}
		qs_lang( in_array( $lang, array( 'ar', 'en' ), true ) ? $lang : 'ar' );
		return $result;
	},
	10,
	3
);

/** Error in the app's language: code = the theme's text key (or a fixed code), message = readable text. */
function fsapi_error( $code, $status = 400, $msg = '' ) {
	$text = $msg ?: ( function_exists( 'qs_t' ) ? qs_t( $code ) : $code );
	return new WP_Error( $code, $text === $code ? fsapi_default_msg( $code ) : $text, array( 'status' => $status ) );
}
function fsapi_default_msg( $code ) {
	$en = 'en' === qs_lang();
	$m  = array(
		'not_found'     => array( 'غير موجود', 'Not found' ),
		'not_allowed'   => array( 'غير مسموح', 'Not allowed' ),
		'login_needed'  => array( 'سجّل الدخول أولاً', 'Please log in' ),
		'too_many'      => array( 'محاولات كثيرة، حاول بعد قليل', 'Too many attempts, try again shortly' ),
		'spam'          => array( 'تعذر إرسال الطلب', 'The request could not be sent' ),
		'bad_request'   => array( 'طلب غير صالح', 'Bad request' ),
		'pw_needed'     => array( 'أدخل كلمة المرور الحالية', 'Enter your current password' ),
	);
	return isset( $m[ $code ] ) ? $m[ $code ][ $en ? 1 : 0 ] : $code;
}

/** Permission: a signed-in user (Bearer token). */
function fsapi_need_user() {
	return is_user_logged_in() ? true : fsapi_error( 'login_needed', 401 );
}

/**
 * Per-IP limit for sign-in style endpoints (not for posting ads). Mobile networks share IPs, so for the
 * password checks only failed attempts are counted: fsapi_throttle( key, max, window, false ) checks,
 * fsapi_throttle_hit( key ) counts a failure.
 */
function fsapi_throttle_key( $key ) {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return 'fsapi_' . $key . '_' . md5( $ip );
}
function fsapi_throttle( $key, $max = 10, $window = 600, $count = true ) {
	$t = fsapi_throttle_key( $key );
	$n = (int) get_transient( $t );
	if ( $n >= $max ) {
		return fsapi_error( 'too_many', 429 );
	}
	if ( $count ) {
		set_transient( $t, $n + 1, $window );
	}
	return true;
}
function fsapi_throttle_hit( $key, $window = 600 ) {
	$t = fsapi_throttle_key( $key );
	set_transient( $t, (int) get_transient( $t ) + 1, $window );
}
