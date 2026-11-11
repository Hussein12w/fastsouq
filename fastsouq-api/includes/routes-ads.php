<?php
/** Ads: list / search / filter, ad details, post, edit, delete, report. */
defined( 'ABSPATH' ) || exit;

/** Copies the request fields into $_POST / $_FILES so the theme's own save code (qs_save_ad) runs unchanged. */
function fsapi_fill_post( WP_REST_Request $r ) {
	$in = array();
	foreach ( array_merge( (array) $r->get_body_params(), (array) $r->get_json_params() ) as $k => $v ) {
		if ( is_scalar( $v ) ) {
			$in[ $k ] = (string) $v;
		} elseif ( 'keep_photos' === $k && is_array( $v ) ) {
			$in[ $k ] = implode( ',', array_map( 'absint', $v ) );
		}
	}
	$_POST = wp_slash( $in );
	$files = $r->get_file_params();
	if ( ! empty( $files['photos'] ) ) {
		$_FILES['photos'] = $files['photos'];
	}
}

/** Value of a filter from the request (same names as the website URL filters). */
function fsapi_get( WP_REST_Request $r ) {
	return function ( $k ) use ( $r ) {
		if ( 'sec' === $k ) {
			$k = 'category';
		}
		$v = $r->get_param( $k );
		return is_scalar( $v ) ? sanitize_text_field( (string) $v ) : '';
	};
}

function fsapi_can_see( $id ) {
	if ( 'listing' !== get_post_type( $id ) ) {
		return false;
	}
	return 'publish' === get_post_status( $id ) || ( is_user_logged_in() && (int) get_post_field( 'post_author', $id ) === get_current_user_id() );
}

add_action(
	'rest_api_init',
	function () {
		$pub = '__return_true';

		/*
		 * GET /ads — list, search and filter. Same filters as the website:
		 * q, category (any level), group, make, model, emirate, city, ymin, ymax, pmin, pmax, kmin, kmax,
		 * body, cond, gear, fuel, cyl, specs, color, part_type, deal, brand, sort (''|asc|desc),
		 * seller (user id), ids (comma list, e.g. favorites kept on the phone), page, per_page (≤ 50).
		 */
		register_rest_route(
			FSAPI_NS,
			'/ads',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => $pub,
					'callback'            => function ( WP_REST_Request $r ) {
						$pp   = min( 50, max( 1, absint( $r->get_param( 'per_page' ) ?: 20 ) ) );
						$page = max( 1, absint( $r->get_param( 'page' ) ?: 1 ) );
						$args = array_merge(
							qs_listing_query_vars( fsapi_get( $r ) ),
							array( 'post_type' => 'listing', 'post_status' => 'publish', 'posts_per_page' => $pp, 'paged' => $page, 'fields' => 'ids', 'author__not_in' => fsapi_blocked() )
						);
						if ( $q = trim( (string) $r->get_param( 'q' ) ) ) {
							$args['s']             = sanitize_text_field( $q );
							$args['qs_search_ext'] = 1;
						}
						if ( $s = absint( $r->get_param( 'seller' ) ) ) {
							$args['author'] = $s;
						}
						if ( $ids = $r->get_param( 'ids' ) ) {
							$args['post__in'] = array_filter( array_map( 'absint', explode( ',', (string) $ids ) ) ) ?: array( 0 );
						}
						$q = new WP_Query( $args );
						return array(
							'items'  => array_map( 'fsapi_ad_summary', $q->posts ),
							'total'  => (int) $q->found_posts,
							'page'   => $page,
							'pages'  => (int) $q->max_num_pages,
							'banner' => 1 === $page ? fsapi_banner( 'cat_list', $r->get_param( 'category' ) ? qs_term( sanitize_title( $r->get_param( 'category' ) ) ) : null ) : null,
						);
					},
				),
				/*
				 * POST /ads — post an ad (multipart/form-data, same field names as the website form; photos[] for images).
				 * Without a token: also acc_name, acc_pass, acc_email (optional). The account is made from the ad's phone
				 * (or, for a phone that already has an account, its password is checked) and a token is returned.
				 */
				array(
					'methods'             => 'POST',
					'permission_callback' => $pub,
					'callback'            => function ( WP_REST_Request $r ) {
						fsapi_fill_post( $r );
						$was = get_current_user_id();
						$id  = qs_save_ad( $was, 0, false );
						if ( is_wp_error( $id ) ) {
							return fsapi_error( $id->get_error_code(), 'err_limit' === $id->get_error_code() ? 429 : 400 );
						}
						$out = array( 'id' => $id, 'status' => get_post_status( $id ), 'message' => qs_t( 'post_ok_p' ), 'ad' => fsapi_ad_summary( $id ) );
						if ( ! $was ) {
							$uid                = get_current_user_id();
							$out['new_account'] = ! empty( $GLOBALS['qs_new_account'] );
							$out['token']       = fsapi_issue_token( $uid, (string) $r->get_header( 'user_agent' ) );
							$out['user']        = fsapi_user( $uid );
						}
						return new WP_REST_Response( $out, 201 );
					},
				),
			)
		);

		/* Before posting without an account: is this phone already registered? (the app then asks only for its password). */
		register_rest_route(
			FSAPI_NS,
			'/auth/check-phone',
			array(
				'methods'             => 'POST',
				'permission_callback' => $pub,
				'callback'            => function ( WP_REST_Request $r ) {
					if ( is_wp_error( $t = fsapi_throttle( 'phone', 120 ) ) ) {
						return $t;
					}
					$ph = qs_phone_norm( (string) $r->get_param( 'phone' ) );
					return $ph ? array( 'valid' => true, 'exists' => (bool) qs_user_by_phone( $ph ) ) : array( 'valid' => false, 'exists' => false, 'message' => qs_t( 'reg_err_phone' ) );
				},
			)
		);

		register_rest_route(
			FSAPI_NS,
			'/ads/(?P<id>\d+)',
			array(
				/* GET /ads/{id} — full ad, 5 similar ads and the side banner. The owner also gets edit_values. */
				array(
					'methods'             => 'GET',
					'permission_callback' => $pub,
					'callback'            => function ( WP_REST_Request $r ) {
						$id = (int) $r['id'];
						if ( ! fsapi_can_see( $id ) ) {
							return fsapi_error( 'not_found', 404 );
						}
						$own = is_user_logged_in() && (int) get_post_field( 'post_author', $id ) === get_current_user_id();
						if ( ! $own && 'publish' === get_post_status( $id ) ) { // one view per device per day
							$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
							$k  = 'fsapi_v' . md5( $id . '|' . $ip . '|' . get_current_user_id() );
							if ( ! get_transient( $k ) ) {
								set_transient( $k, 1, DAY_IN_SECONDS );
								update_post_meta( $id, '_qs_views', (int) get_post_meta( $id, '_qs_views', true ) + 1 );
							}
						}
						$lv      = qs_levels( $id );
						$similar = get_posts( array( 'post_type' => 'listing', 'fields' => 'ids', 'posts_per_page' => 5, 'post__not_in' => array( $id ), 'author__not_in' => fsapi_blocked(), 'tax_query' => array( array( 'taxonomy' => 'qs_cat', 'terms' => ( $lv[2] ?: $lv[1] ?: $lv[0] )->term_id ?? 0 ) ) ) );
						return fsapi_ad_detail( $id, $own ) + array(
							'similar' => array_map( 'fsapi_ad_summary', $similar ),
							'banner'  => fsapi_banner( 'ad_side', $lv[0] ),
						);
					},
				),
				/* POST /ads/{id} — edit (owner). Same fields as posting; keep_photos = ids of current photos to keep (+ new photos[]). Goes back to review. */
				array(
					'methods'             => 'POST',
					'permission_callback' => 'fsapi_need_user',
					'callback'            => function ( WP_REST_Request $r ) {
						$id = (int) $r['id'];
						fsapi_fill_post( $r );
						$res = qs_save_ad( get_current_user_id(), $id, false );
						if ( is_wp_error( $res ) ) {
							return fsapi_error( $res->get_error_code(), 'not_allowed' === $res->get_error_code() ? 403 : 400 );
						}
						return array( 'id' => $res, 'status' => get_post_status( $res ), 'message' => qs_t( 'edit_ok_p' ), 'ad' => fsapi_ad_summary( $res ) );
					},
				),
				/* DELETE /ads/{id} — owner moves the ad to the bin (same as the website). */
				array(
					'methods'             => 'DELETE',
					'permission_callback' => 'fsapi_need_user',
					'callback'            => function ( WP_REST_Request $r ) {
						$id = (int) $r['id'];
						if ( 'listing' !== get_post_type( $id ) || (int) get_post_field( 'post_author', $id ) !== get_current_user_id() ) {
							return fsapi_error( 'not_allowed', 403 );
						}
						wp_trash_post( $id );
						return array( 'deleted' => true, 'message' => qs_t( 'deleted_ok' ) );
					},
				),
			)
		);

		/* POST /ads/{id}/report {reason} — once per account / device; 3 reports hide the ad until reviewed. */
		register_rest_route(
			FSAPI_NS,
			'/ads/(?P<id>\d+)/report',
			array(
				'methods'             => 'POST',
				'permission_callback' => $pub,
				'callback'            => function ( WP_REST_Request $r ) {
					$id = (int) $r['id'];
					if ( 'listing' !== get_post_type( $id ) ) {
						return fsapi_error( 'not_found', 404 );
					}
					$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
					$who = md5( get_current_user_id() ? 'u' . get_current_user_id() : 'ip' . $ip );
					$by  = (array) get_post_meta( $id, '_fsapi_reporters', true );
					if ( ! in_array( $who, $by, true ) ) {
						$by[] = $who;
						update_post_meta( $id, '_fsapi_reporters', array_slice( $by, -200 ) );
						qs_report_ad( $id, mb_substr( sanitize_text_field( (string) $r->get_param( 'reason' ) ), 0, 300 ) );
					}
					return array( 'reported' => true, 'message' => qs_t( 'report_ok' ) );
				},
			)
		);
	}
);
