<?php
/** Front-end forms: login, register, profile, post / edit / delete ad, report. */
defined( 'ABSPATH' ) || exit;

/**
 * Ad fields (from the spreadsheet): key => [label, control, groups, required groups]
 * Groups: vehicle (cars & vehicles), item (spare parts, accessories, tools), service, general (every other main category).
 */
function qs_fields() {
	return array(
		'title'     => array( 'name', 'text', 'vehicle item service general', 'vehicle item service general' ),
		'itype'     => array( 'item_type', 'itype', 'item general', '' ),
		'deal'      => array( 'deal', 'chips:deal', 'vehicle', '' ),
		'make'      => array( 'make', 'make', 'vehicle item', '' ),
		'model'     => array( 'model', 'model', 'vehicle item', '' ),
		'model_other' => array( 'model_name', 'text', 'vehicle item', '' ),
		'year'      => array( 'year', 'select:years', 'vehicle item', '' ),
		'body'      => array( 'body', 'body', 'vehicle', '' ),
		'cond'      => array( 'condition', 'chips:cond', 'vehicle item general', 'vehicle item' ),
		'km'        => array( 'kms', 'number', 'vehicle', '' ),
		'gear'      => array( 'gear', 'chips:gear', 'vehicle', '' ),
		'fuel'      => array( 'fuel', 'chips:fuel', 'vehicle', '' ),
		'cyl'       => array( 'cyl', 'select:cyl', 'vehicle', '' ),
		'specs'     => array( 'specs', 'select:specs', 'vehicle', '' ),
		'color'     => array( 'color', 'select:color', 'vehicle', '' ),
		'brand'     => array( 'part_brand', 'text', 'item', '' ),
		'part_type' => array( 'part_type', 'chips:part_type', 'item', '' ),
		'sku'       => array( 'sku', 'text', 'item', '' ),
		'price'     => array( 'price', 'number', 'vehicle item general', '' ),
		'period'    => array( 'period', 'chips:period', 'vehicle', '' ),
		'desc'      => array( 'about', 'textarea', 'vehicle item service general', '' ),
	);
}
/** Labels that change with the group (e.g. make → compatible make for parts). */
function qs_field_label( $key, $group ) {
	$map = array(
		'title' => array( 'vehicle' => 'vehicle_name', 'item' => 'item_name', 'service' => 'service_name' ),
		'itype' => array( 'general' => 'cat_detail' ),
		'make'  => array( 'item' => 'compat_make' ),
		'model' => array( 'item' => 'compat_model' ),
		'model_other' => array( 'item' => 'compat_model_name' ),
		'year'  => array( 'item' => 'compat_year' ),
	);
	return $map[ $key ][ $group ] ?? qs_fields()[ $key ][0];
}
/** Options as [value, label, parent] for selects / chips. */
function qs_field_options( $src, $group = '' ) {
	if ( 'years' === $src ) {
		$y = (int) gmdate( 'Y' ) + 1;
		return array_map( fn( $i ) => array( $y - $i, $y - $i, '' ), range( 0, $y - 1960 ) );
	}
	if ( 'cond' === $src && in_array( $group, array( 'item', 'general' ), true ) ) {
		$src = 'item_cond';
	}
	$out = array();
	foreach ( qs_options( $src ) as $k => $v ) {
		$out[] = array( $k, qs_pair( $v ), '' );
	}
	return $out;
}
/** A car section (vehicles, spare parts…) or a main category other than "Cars": what an ad is posted in. */
function qs_is_section( $slug ) {
	if ( isset( qs_sections()[ $slug ] ) ) {
		return (bool) qs_term( $slug );
	}
	$t = $slug && 'cars' !== $slug ? qs_term( $slug ) : null;
	return $t && ! $t->parent;
}
function qs_post_values( $id ) {
	if ( ! $id ) {
		return array();
	}
	$p  = get_post( $id );
	$lv = qs_levels( $id );
	list( $make, $model ) = qs_make_model( $id );
	$sec = $lv[0] ? $lv[0]->slug : '';
	$v   = array( 'main' => isset( qs_sections()[ $sec ] ) ? 'cars' : $sec, 'section' => $sec, 'sub' => $lv[1] ? $lv[1]->slug : '', 'itype' => $lv[2] ? $lv[2]->slug : '', 'title' => $p->post_title, 'desc' => $p->post_content, 'make' => $make ? $make->slug : '', 'model' => $model ? $model->slug : '' );
	foreach ( array( 'deal', 'year', 'body', 'cond', 'km', 'gear', 'fuel', 'cyl', 'specs', 'color', 'brand', 'part_type', 'sku', 'price', 'period', 'area', 'lat', 'lng', 'phone', 'title_en', 'model_other' ) as $k ) {
		$v[ $k ] = qs_m( $id, $k );
	}
	$c = get_the_terms( $id, 'qs_city' );
	foreach ( ( $c && ! is_wp_error( $c ) ) ? $c : array() as $t ) {
		if ( $t->parent ) {
			$v['city']    = $t->slug;
			$v['emirate'] = get_term( $t->parent, 'qs_city' )->slug;
		} else {
			$v['emirate'] = $t->slug;
		}
	}
	return $v;
}

/* ---------- Helpers ---------- */
function qs_back( $url, $args = array() ) {
	wp_safe_redirect( add_query_arg( $args, $url ) );
	exit;
}
function qs_req( $k ) {
	if ( ! isset( $_POST[ $k ] ) ) {
		return '';
	}
	return is_array( $_POST[ $k ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $k ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $k ] ) );
}
function qs_nonce( $action ) {
	if ( ! isset( $_POST['_qsn'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_qsn'] ) ), $action ) ) {
		wp_die( 'Session expired — please go back and try again.', 403 );
	}
}
function qs_redirect_param() {
	return wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['redirect'] ?? '' ) ), qs_page_url( 'account', array( 'tab' => 'ads' ) ) );
}

/* ---------- Login / register ---------- */
add_action( 'admin_post_nopriv_qs_login', 'qs_handle_login' );
add_action( 'admin_post_nopriv_qs_register', 'qs_handle_register' );
add_action( 'admin_post_qs_login', fn() => qs_back( qs_page_url( 'account' ) ) );
add_action( 'admin_post_qs_register', fn() => qs_back( qs_page_url( 'account' ) ) );
function qs_handle_login() {
	qs_nonce( 'qs_login' );
	$login = qs_req( 'login' );
	if ( ! is_email( $login ) && ( $ph = qs_phone_norm( $login ) ) ) {
		$login = $ph;
	}
	$user = wp_signon( array( 'user_login' => $login, 'user_password' => (string) wp_unslash( $_POST['pass'] ?? '' ), 'remember' => ! empty( $_POST['remember'] ) ), is_ssl() );
	if ( is_wp_error( $user ) ) {
		qs_back( qs_page_url( 'login' ), array( 'qs_err' => 'login_err', 'old_login' => rawurlencode( qs_req( 'login' ) ), 'redirect' => rawurlencode( qs_redirect_param() ) ) );
	}
	qs_back( qs_redirect_param() );
}
function qs_handle_register() {
	qs_nonce( 'qs_register' );
	$name  = qs_req( 'name' );
	$phone = qs_phone_norm( qs_req( 'phone' ) );
	$email = sanitize_email( qs_req( 'email' ) );
	$pass  = (string) wp_unslash( $_POST['pass'] ?? '' );
	$old   = array( 'tab' => 'register', 'old_name' => rawurlencode( $name ), 'old_phone' => rawurlencode( qs_req( 'phone' ) ), 'old_email' => rawurlencode( qs_req( 'email' ) ), 'redirect' => rawurlencode( qs_redirect_param() ) );
	$err   = ! $name ? 'reg_err_name' : ( ! $phone ? 'reg_err_phone' : ( ! is_email( $email ) ? 'reg_err_email' : ( strlen( $pass ) < 8 ? 'reg_err_pw' : '' ) ) );
	if ( ! $err && ( username_exists( $phone ) || email_exists( $email ) ) ) {
		$err = 'reg_err_exists';
	}
	if ( $err ) {
		qs_back( qs_page_url( 'login' ), $old + array( 'qs_err' => $err ) );
	}
	$uid = wp_insert_user( array( 'user_login' => $phone, 'user_pass' => $pass, 'user_email' => $email, 'display_name' => $name, 'nickname' => $name, 'first_name' => $name, 'role' => 'subscriber' ) );
	if ( is_wp_error( $uid ) ) {
		qs_back( qs_page_url( 'login' ), $old + array( 'qs_err' => 'reg_err_exists' ) );
	}
	update_user_meta( $uid, 'qs_phone', $phone );
	wp_set_current_user( $uid );
	wp_set_auth_cookie( $uid, true, is_ssl() );
	wp_new_user_notification( $uid, null, 'admin' );
	qs_back( qs_redirect_param() );
}

/* ---------- Profile ---------- */
add_action( 'admin_post_qs_profile', 'qs_handle_profile' );
function qs_handle_profile() {
	qs_nonce( 'qs_profile' );
	$uid = get_current_user_id();
	if ( $n = qs_req( 'name' ) ) {
		wp_update_user( array( 'ID' => $uid, 'display_name' => $n, 'nickname' => $n ) );
	}
	$e = sanitize_email( qs_req( 'email' ) );
	if ( is_email( $e ) && ( ! email_exists( $e ) || email_exists( $e ) === $uid ) ) {
		wp_update_user( array( 'ID' => $uid, 'user_email' => $e ) );
	}
	if ( $p = qs_phone_norm( qs_req( 'phone' ) ) ) {
		update_user_meta( $uid, 'qs_phone', $p );
	}
	qs_back( qs_page_url( 'account' ), array( 'qs_msg' => 'saved_ok' ) );
}

/* ---------- Posting without an account: the account is made from the ad's phone ---------- */
/** The account that already uses this phone (as its login or its saved number), if any. */
function qs_user_by_phone( $phone ) {
	if ( ! $phone ) {
		return 0;
	}
	if ( $id = username_exists( $phone ) ) {
		return (int) $id;
	}
	$u = get_users( array( 'meta_key' => 'qs_phone', 'meta_value' => $phone, 'number' => 1, 'fields' => 'ID' ) );
	return $u ? (int) $u[0] : 0;
}
/**
 * Checks the "Your account" part of the post form.
 * Phone already registered → the password must match that account. New phone → name, password (8+) and an unused email if given.
 * Returns array( 'ok' => bool, 'exists' => bool, 'err' => text key ).
 */
function qs_guest_check( $phone, $name, $pass, $email ) {
	if ( ! $phone ) {
		return array( 'ok' => false, 'exists' => false, 'err' => 'reg_err_phone' );
	}
	if ( $uid = qs_user_by_phone( $phone ) ) {
		$u  = get_userdata( $uid );
		$ok = $pass && $u && wp_check_password( $pass, $u->user_pass, $uid );
		return array( 'ok' => $ok, 'exists' => true, 'err' => $ok ? '' : ( $pass ? 'acc_wrong_pw' : 'acc_exists' ) );
	}
	$err = ! $name ? 'reg_err_name' : ( strlen( $pass ) < 8 ? 'reg_err_pw' : '' );
	if ( ! $err && $email ) {
		$err = ! is_email( $email ) ? 'reg_err_email' : ( email_exists( $email ) ? 'acc_email_used' : '' );
	}
	return array( 'ok' => ! $err, 'exists' => false, 'err' => $err );
}
function qs_guest_fields() {
	return array( qs_phone_norm( qs_req( 'phone' ) ), qs_req( 'acc_name' ), (string) wp_unslash( $_POST['acc_pass'] ?? '' ), sanitize_email( qs_req( 'acc_email' ) ) );
}
/* Checked by the page before sending, so nothing typed or attached is lost. */
add_action(
	'wp_ajax_nopriv_qs_guest_check',
	function () {
		$r        = qs_guest_check( ...qs_guest_fields() );
		$r['msg'] = $r['err'] ? qs_t( $r['err'] ) : '';
		wp_send_json( $r );
	}
);
add_action( 'wp_ajax_qs_guest_check', fn() => wp_send_json( array( 'ok' => true, 'exists' => false, 'err' => '' ) ) );
/** Signs the advertiser in (existing account) or creates the account. Returns the user id or a WP_Error (code = text key). */
function qs_guest_account( $cookie = true ) {
	if ( '' !== qs_req( 'company_site' ) ) { // filled only by bots
		return new WP_Error( 'spam' );
	}
	list( $phone, $name, $pass, $email ) = qs_guest_fields();
	$r = qs_guest_check( $phone, $name, $pass, $email );
	if ( ! $r['ok'] ) {
		return new WP_Error( $r['err'] );
	}
	if ( $r['exists'] ) {
		$uid = qs_user_by_phone( $phone );
	} else {
		$uid = wp_insert_user( array( 'user_login' => $phone, 'user_pass' => $pass, 'user_email' => $email, 'display_name' => $name, 'nickname' => $name, 'first_name' => $name, 'role' => 'subscriber' ) );
		if ( is_wp_error( $uid ) ) {
			return new WP_Error( 'reg_err_exists' );
		}
		update_user_meta( $uid, 'qs_phone', $phone );
		update_user_meta( $uid, 'qs_from_ad', 1 );
		wp_new_user_notification( $uid, null, 'admin' );
		$GLOBALS['qs_new_account'] = 1;
	}
	wp_set_current_user( $uid );
	if ( $cookie ) {
		wp_set_auth_cookie( $uid, true, is_ssl() );
	}
	return (int) $uid;
}

/* ---------- Post / edit an ad (free; waits for approval) ---------- */
add_action( 'admin_post_nopriv_qs_post_ad', 'qs_handle_post_ad' );
add_action( 'admin_post_qs_post_ad', 'qs_handle_post_ad' );
function qs_handle_post_ad() {
	qs_nonce( 'qs_post_ad' );
	$uid  = is_user_logged_in() ? get_current_user_id() : 0;
	$edit = $uid ? absint( $_POST['edit'] ?? 0 ) : 0;
	$main = qs_req( 'main' );
	$back = qs_page_url( 'post', $edit ? array( 'edit' => $edit ) : array( 'section' => 'cars' === $main ? qs_req( 'section' ) : $main ) );
	$r    = qs_save_ad( $uid, $edit, true );
	if ( is_wp_error( $r ) ) {
		if ( 'not_allowed' === $r->get_error_code() ) {
			wp_die( 'Not allowed', 403 );
		}
		qs_back( 'spam' === $r->get_error_code() ? home_url( '/' ) : ( $uid ? $back : qs_page_url( 'post' ) ), array( 'qs_err' => $r->get_error_code() ) );
	}
	qs_back( qs_page_url( 'post' ), array( 'done' => $r, 'edited' => $edit ? 1 : '', 'newacc' => empty( $GLOBALS['qs_new_account'] ) ? '' : 1 ) );
}
/**
 * Saves a new or edited ad from the posted fields ($_POST / $_FILES): the website form and the app use it.
 * $uid 0 = no account yet (the account is made from the ad's phone). Returns the ad id or a WP_Error (code = text key).
 * Photos: new uploads in "photos[]"; "keep_photos" (ids, comma separated) keeps some of the current ones when editing.
 */
function qs_save_ad( $uid, $edit = 0, $cookie = true ) {
	if ( $edit && ( 'listing' !== get_post_type( $edit ) || (int) get_post_field( 'post_author', $edit ) !== (int) $uid ) ) {
		return new WP_Error( 'not_allowed' );
	}
	$main    = qs_req( 'main' );
	$section = 'cars' === $main ? qs_req( 'section' ) : $main;
	if ( ! qs_is_section( $section ) ) {
		return new WP_Error( 'err_required' );
	}
	$group = qs_group_of( $section );
	$sec_t = qs_term( $section );
	$sub   = qs_term( (string) qs_req( 'sub' ) );
	if ( ! $sec_t || ! $sub || (int) $sub->parent !== (int) $sec_t->term_id ) {
		return new WP_Error( 'err_required' );
	}
	$v = array();
	foreach ( qs_fields() as $k => $f ) {
		if ( ! in_array( $group, explode( ' ', $f[2] ), true ) ) {
			continue;
		}
		$v[ $k ] = 'desc' === $k ? sanitize_textarea_field( wp_unslash( $_POST['desc'] ?? '' ) ) : qs_req( $k );
		if ( in_array( $group, explode( ' ', $f[3] ), true ) && '' === $v[ $k ] ) {
			return new WP_Error( 'err_required' );
		}
	}
	$emirate = qs_term( (string) qs_req( 'emirate' ), 'qs_city' );
	$city    = qs_term( (string) qs_req( 'city' ), 'qs_city' );
	$phone   = qs_phone_norm( qs_req( 'phone' ) ) ?: get_user_meta( $uid, 'qs_phone', true );
	if ( ! $emirate || ! $phone ) {
		return new WP_Error( 'err_required' );
	}
	if ( empty( $_POST['agree'] ) ) {
		return new WP_Error( 'err_agree' );
	}
	if ( ! $uid ) {
		$uid = qs_guest_account( $cookie ); // the ad is valid: sign in or create the account now
		if ( is_wp_error( $uid ) ) {
			return $uid;
		}
	}
	if ( ! $edit ) {
		$today = get_posts( array( 'post_type' => 'listing', 'author' => $uid, 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 50, 'date_query' => array( array( 'after' => '24 hours ago' ) ) ) );
		if ( count( $today ) >= absint( get_theme_mod( 'qs_daily_limit', 10 ) ) ) {
			return new WP_Error( 'err_limit' );
		}
	}
	$arr = array( 'post_type' => 'listing', 'post_title' => $v['title'], 'post_content' => $v['desc'] ?? '', 'post_status' => 'pending', 'post_author' => $uid );
	if ( $edit ) {
		$arr['ID'] = $edit;
	}
	$id = wp_insert_post( $arr, true );
	if ( is_wp_error( $id ) ) {
		return new WP_Error( 'err_required' );
	}
	/* Section → sub-section → item type. */
	$cats  = array( $sec_t->term_id, $sub->term_id );
	$itype = ! empty( $v['itype'] ) ? qs_term( $v['itype'] ) : null;
	if ( $itype && (int) $itype->parent === (int) $sub->term_id ) {
		$cats[] = $itype->term_id;
	}
	wp_set_object_terms( $id, $cats, 'qs_cat' );
	/* Make → model (picked from the list; "Other" + the typed name when the model is not listed). */
	$mm    = array();
	$other = '';
	$make  = ! empty( $v['make'] ) ? qs_term( $v['make'], 'qs_make' ) : null;
	if ( $make && ! $make->parent ) {
		$mm[]  = $make->term_id;
		$model = ! empty( $v['model'] ) ? qs_term( $v['model'], 'qs_make' ) : null;
		if ( $model && (int) $model->parent === (int) $make->term_id ) {
			$mm[] = $model->term_id;
			if ( qs_is_other( $model ) ) {
				$other = mb_substr( trim( (string) ( $v['model_other'] ?? '' ) ), 0, 60 );
			}
		}
	}
	wp_set_object_terms( $id, $mm, 'qs_make' );
	wp_set_object_terms( $id, array_filter( array( $emirate->term_id, ( $city && (int) $city->parent === (int) $emirate->term_id ) ? $city->term_id : 0 ) ), 'qs_city' );
	$lat  = qs_req( 'lat' );
	$lng  = qs_req( 'lng' );
	$meta = array( 'group' => $group, 'area' => qs_req( 'area' ), 'lat' => is_numeric( $lat ) ? round( (float) $lat, 6 ) : '', 'lng' => is_numeric( $lng ) ? round( (float) $lng, 6 ) : '', 'phone' => $phone, 'title_en' => qs_req( 'title_en' ) );
	$meta['model_other'] = $other;
	foreach ( array( 'deal', 'year', 'body', 'cond', 'km', 'gear', 'fuel', 'cyl', 'specs', 'color', 'brand', 'part_type', 'sku', 'price', 'period' ) as $k ) {
		$val = $v[ $k ] ?? '';
		if ( in_array( $k, array( 'price', 'km', 'year' ), true ) ) {
			$val = is_numeric( $val ) ? (float) $val : '';
		}
		$meta[ $k ] = $val;
	}
	if ( 'cars-for-rent' !== $sub->slug ) {
		$meta['period'] = '';
	}
	foreach ( $meta as $k => $val ) {
		update_post_meta( $id, '_qs_' . $k, $val );
	}
	if ( ! $edit ) {
		update_post_meta( $id, '_qs_featured', 0 );
		update_post_meta( $id, '_qs_views', 0 );
	}
	$keep = array_filter( array_map( 'absint', explode( ',', (string) qs_req( 'keep_photos' ) ) ) );
	if ( $edit && $keep ) { // app: keep some of the current photos, add the new ones
		$cur  = array_merge( array( get_post_thumbnail_id( $id ) ), (array) get_post_meta( $id, '_qs_gallery', true ) );
		$keep = array_values( array_intersect( $keep, array_map( 'intval', $cur ) ) );
	}
	if ( ! empty( $_FILES['photos']['name'][0] ) || ( $edit && '' !== qs_req( 'keep_photos' ) ) ) {
		$ids = $keep;
		foreach ( array_keys( (array) ( $_FILES['photos']['name'] ?? array() ) ) as $i ) {
			if ( count( $ids ) >= 10 ) {
				break;
			}
			if ( $att = qs_upload( 'photos', $id, $i ) ) {
				$ids[] = $att;
			}
		}
		if ( $ids ) {
			set_post_thumbnail( $id, $ids[0] );
		} else {
			delete_post_thumbnail( $id );
		}
		update_post_meta( $id, '_qs_gallery', $ids );
	}
	wp_mail( get_option( 'admin_email' ), '[' . qs_t( 'site_name' ) . '] ' . ( $edit ? 'Ad edited' : 'New ad' ) . ' — ' . $v['title'], admin_url( 'post.php?post=' . $id . '&action=edit' ) );
	return (int) $id;
}
function qs_upload( $field, $post_id, $i ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$f   = $_FILES[ $field ];
	$key = $field . '_' . $i;
	$_FILES[ $key ] = array( 'name' => $f['name'][ $i ], 'type' => $f['type'][ $i ], 'tmp_name' => $f['tmp_name'][ $i ], 'error' => $f['error'][ $i ], 'size' => $f['size'][ $i ] );
	if ( ! empty( $f['error'][ $i ] ) || $f['size'][ $i ] > 10 * MB_IN_BYTES || ! preg_match( '/^image\//', (string) wp_check_filetype( $f['name'][ $i ] )['type'] ) ) {
		return 0;
	}
	$id = media_handle_upload( $key, $post_id, array(), array( 'test_form' => false, 'mimes' => array( 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' ) ) );
	return is_wp_error( $id ) ? 0 : (int) $id;
}

add_action( 'admin_post_qs_delete', 'qs_handle_delete' );
function qs_handle_delete() {
	qs_nonce( 'qs_delete' );
	$id = absint( $_POST['post_id'] ?? 0 );
	if ( 'listing' === get_post_type( $id ) && (int) get_post_field( 'post_author', $id ) === get_current_user_id() ) {
		wp_trash_post( $id );
	}
	qs_back( qs_page_url( 'account', array( 'tab' => 'ads' ) ), array( 'qs_msg' => 'deleted_ok' ) );
}

/* ---------- Report (3 reports hide the ad until reviewed) ---------- */
add_action( 'admin_post_qs_report', 'qs_handle_report' );
add_action( 'admin_post_nopriv_qs_report', 'qs_handle_report' );
function qs_handle_report() {
	qs_nonce( 'qs_report' );
	$id = absint( $_POST['post_id'] ?? 0 );
	if ( 'listing' !== get_post_type( $id ) ) {
		qs_back( home_url( '/' ) );
	}
	if ( empty( $_COOKIE[ 'qs_r' . $id ] ) ) {
		qs_report_ad( $id, qs_req( 'reason' ) );
		setcookie( 'qs_r' . $id, '1', time() + MONTH_IN_SECONDS, COOKIEPATH ?: '/' );
	}
	qs_back( get_permalink( $id ) ?: home_url( '/' ), array( 'qs_msg' => 'report_ok' ) );
}
/** Records a report; 3 reports hide the ad until reviewed (website and app). */
function qs_report_ad( $id, $reason ) {
	$list   = (array) get_post_meta( $id, '_qs_report_list', true );
	$list[] = array( 'reason' => $reason, 'time' => time() );
	update_post_meta( $id, '_qs_report_list', array_slice( array_filter( $list ), -50 ) );
	$n = (int) get_post_meta( $id, '_qs_reports', true ) + 1;
	update_post_meta( $id, '_qs_reports', $n );
	if ( $n >= 3 && 'publish' === get_post_status( $id ) ) {
		wp_update_post( array( 'ID' => $id, 'post_status' => 'pending' ) );
	}
	wp_mail( get_option( 'admin_email' ), '[' . qs_t( 'site_name' ) . '] Report #' . $id, $reason . "\n" . admin_url( 'post.php?post=' . $id . '&action=edit' ) );
}
