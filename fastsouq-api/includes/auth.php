<?php
/**
 * App sign-in: long-lived tokens ("fs_<user id>_<random>"), sent as  Authorization: Bearer <token>
 * (or  X-FS-Token: <token>  where the host strips the Authorization header).
 * Only a hash of each token is stored (user meta "fsapi_tokens"), so a token can be revoked per device.
 */
defined( 'ABSPATH' ) || exit;

function fsapi_token_from_request() {
	$h = '';
	foreach ( array( 'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION' ) as $k ) {
		if ( ! empty( $_SERVER[ $k ] ) ) {
			$h = sanitize_text_field( wp_unslash( $_SERVER[ $k ] ) );
			break;
		}
	}
	if ( $h && preg_match( '/^Bearer\s+(\S+)$/i', $h, $m ) ) {
		return $m[1];
	}
	return ! empty( $_SERVER['HTTP_X_FS_TOKEN'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FS_TOKEN'] ) ) : '';
}

/** User id for a token, or 0. Updates "last used" at most once an hour. */
function fsapi_user_from_token( $token ) {
	if ( ! preg_match( '/^fs_(\d+)_([A-Za-z0-9]{40})$/', (string) $token, $m ) ) {
		return 0;
	}
	$uid  = (int) $m[1];
	$list = (array) get_user_meta( $uid, 'fsapi_tokens', true );
	$h    = hash( 'sha256', $token );
	if ( ! isset( $list[ $h ] ) ) {
		return 0;
	}
	if ( time() - (int) ( $list[ $h ]['used'] ?? 0 ) > HOUR_IN_SECONDS ) {
		$list[ $h ]['used'] = time();
		update_user_meta( $uid, 'fsapi_tokens', $list );
	}
	return $uid;
}

function fsapi_issue_token( $uid, $device = '' ) {
	$token = 'fs_' . (int) $uid . '_' . wp_generate_password( 40, false, false );
	$list  = array_filter( (array) get_user_meta( $uid, 'fsapi_tokens', true ) );
	if ( count( $list ) >= 20 ) { // keep the 20 most recent devices
		uasort( $list, fn( $a, $b ) => ( $b['used'] ?? 0 ) <=> ( $a['used'] ?? 0 ) );
		$list = array_slice( $list, 0, 19, true );
	}
	$list[ hash( 'sha256', $token ) ] = array( 'created' => time(), 'used' => time(), 'device' => mb_substr( sanitize_text_field( $device ), 0, 80 ) );
	update_user_meta( $uid, 'fsapi_tokens', $list );
	return $token;
}

function fsapi_revoke_token( $token ) {
	if ( $uid = fsapi_user_from_token( $token ) ) {
		$list = (array) get_user_meta( $uid, 'fsapi_tokens', true );
		unset( $list[ hash( 'sha256', $token ) ] );
		update_user_meta( $uid, 'fsapi_tokens', $list );
	}
}

/* Signs the request in from the token (API requests only). */
add_filter(
	'determine_current_user',
	function ( $uid ) {
		if ( $uid || empty( $_SERVER['REQUEST_URI'] ) || false === strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), FSAPI_NS ) ) {
			return $uid;
		}
		$t = fsapi_token_from_request();
		return $t ? ( fsapi_user_from_token( $t ) ?: $uid ) : $uid;
	},
	30
);

/* Password changed from the website / dashboard → sign out all app devices. */
add_action( 'after_password_reset', fn( $user ) => delete_user_meta( $user->ID, 'fsapi_tokens' ) );
add_action(
	'profile_update',
	function ( $uid, $old ) {
		$new = get_userdata( $uid );
		if ( $new && $old && $new->user_pass !== $old->user_pass && empty( $GLOBALS['fsapi_keep_tokens'] ) ) {
			delete_user_meta( $uid, 'fsapi_tokens' );
		}
	},
	10,
	2
);
/* Password set from the app or by WordPress → sign out all app devices (the app then gets a fresh token). */
add_action( 'wp_set_password', fn( $pw, $uid ) => delete_user_meta( $uid, 'fsapi_tokens' ), 10, 2 );
