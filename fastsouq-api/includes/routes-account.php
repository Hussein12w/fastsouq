<?php
/** Sign-in, register, forgot password, profile, delete account, my ads, favorites, blocked users, push devices. */
defined( 'ABSPATH' ) || exit;

function fsapi_signed_in( $uid, WP_REST_Request $r, $status = 200 ) {
	return new WP_REST_Response( array( 'token' => fsapi_issue_token( $uid, (string) ( $r->get_param( 'device' ) ?: $r->get_header( 'user_agent' ) ) ), 'user' => fsapi_user( $uid ) ), $status );
}

add_action(
	'rest_api_init',
	function () {
		$pub  = '__return_true';
		$user = 'fsapi_need_user';

		/* POST /auth/login {login (phone or email), password, device?} — same accounts as the website. */
		register_rest_route(
			FSAPI_NS,
			'/auth/login',
			array(
				'methods'             => 'POST',
				'permission_callback' => $pub,
				'callback'            => function ( WP_REST_Request $r ) {
					if ( is_wp_error( $t = fsapi_throttle( 'login', 20, 900, false ) ) ) { // 20 wrong passwords / 15 min
						return $t;
					}
					$login = trim( (string) $r->get_param( 'login' ) );
					$pass  = (string) $r->get_param( 'password' );
					$user  = null;
					if ( is_email( $login ) ) {
						$user = get_user_by( 'email', $login );
					} elseif ( $ph = qs_phone_norm( $login ) ) {
						$user = get_userdata( qs_user_by_phone( $ph ) );
					} else {
						$user = get_user_by( 'login', $login );
					}
					if ( ! $user || ! $pass || ! wp_check_password( $pass, $user->user_pass, $user->ID ) ) {
						fsapi_throttle_hit( 'login', 900 );
						return fsapi_error( 'login_err', 401 );
					}
					return fsapi_signed_in( $user->ID, $r );
				},
			)
		);

		/* POST /auth/register {name, phone, email, password} — same rules as the website's register form. */
		register_rest_route(
			FSAPI_NS,
			'/auth/register',
			array(
				'methods'             => 'POST',
				'permission_callback' => $pub,
				'callback'            => function ( WP_REST_Request $r ) {
					if ( is_wp_error( $t = fsapi_throttle( 'register', 30, 3600 ) ) ) {
						return $t;
					}
					$name  = sanitize_text_field( (string) $r->get_param( 'name' ) );
					$phone = qs_phone_norm( (string) $r->get_param( 'phone' ) );
					$email = sanitize_email( (string) $r->get_param( 'email' ) );
					$pass  = (string) $r->get_param( 'password' );
					$err   = ! $name ? 'reg_err_name' : ( ! $phone ? 'reg_err_phone' : ( ! is_email( $email ) ? 'reg_err_email' : ( strlen( $pass ) < 8 ? 'reg_err_pw' : '' ) ) );
					if ( ! $err && ( qs_user_by_phone( $phone ) || email_exists( $email ) ) ) {
						$err = 'reg_err_exists';
					}
					if ( $err ) {
						return fsapi_error( $err, 'reg_err_exists' === $err ? 409 : 400 );
					}
					$uid = wp_insert_user( array( 'user_login' => $phone, 'user_pass' => $pass, 'user_email' => $email, 'display_name' => $name, 'nickname' => $name, 'first_name' => $name, 'role' => 'subscriber' ) );
					if ( is_wp_error( $uid ) ) {
						return fsapi_error( 'reg_err_exists', 409 );
					}
					update_user_meta( $uid, 'qs_phone', $phone );
					update_user_meta( $uid, 'fsapi_from_app', 1 );
					wp_new_user_notification( $uid, null, 'admin' );
					return fsapi_signed_in( $uid, $r, 201 );
				},
			)
		);

		/* POST /auth/forgot {login} — emails a reset link (WordPress's own). Always answers the same way. */
		register_rest_route(
			FSAPI_NS,
			'/auth/forgot',
			array(
				'methods'             => 'POST',
				'permission_callback' => $pub,
				'callback'            => function ( WP_REST_Request $r ) {
					if ( is_wp_error( $t = fsapi_throttle( 'forgot', 10 ) ) ) {
						return $t;
					}
					$login = trim( (string) $r->get_param( 'login' ) );
					$u     = is_email( $login ) ? get_user_by( 'email', $login ) : get_userdata( qs_user_by_phone( qs_phone_norm( $login ) ) );
					if ( $u && $u->user_email ) {
						retrieve_password( $u->user_login );
					}
					$en = 'en' === qs_lang();
					return array( 'sent' => true, 'message' => $en ? 'If this account has an email, a reset link has been sent to it.' : 'إذا كان للحساب بريد إلكتروني، أرسلنا إليه رابط تعيين كلمة المرور.' );
				},
			)
		);

		/* POST /auth/logout — signs this device out. */
		register_rest_route(
			FSAPI_NS,
			'/auth/logout',
			array(
				'methods'             => 'POST',
				'permission_callback' => $pub,
				'callback'            => function () {
					fsapi_revoke_token( fsapi_token_from_request() );
					return array( 'ok' => true );
				},
			)
		);

		/* /me — profile; POST updates name / email / phone; DELETE {password} deletes the account and its ads. */
		register_rest_route(
			FSAPI_NS,
			'/me',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => $user,
					'callback'            => fn() => fsapi_user( get_current_user_id() ),
				),
				array(
					'methods'             => 'POST',
					'permission_callback' => $user,
					'callback'            => function ( WP_REST_Request $r ) {
						$uid = get_current_user_id();
						if ( null !== $r->get_param( 'name' ) ) {
							$n = sanitize_text_field( (string) $r->get_param( 'name' ) );
							if ( ! $n ) {
								return fsapi_error( 'reg_err_name' );
							}
							wp_update_user( array( 'ID' => $uid, 'display_name' => $n, 'nickname' => $n, 'first_name' => $n ) );
						}
						if ( null !== $r->get_param( 'email' ) ) {
							$e = sanitize_email( (string) $r->get_param( 'email' ) );
							if ( ! is_email( $e ) ) {
								return fsapi_error( 'reg_err_email' );
							}
							if ( ( $o = email_exists( $e ) ) && (int) $o !== $uid ) {
								return fsapi_error( 'acc_email_used', 409 );
							}
							wp_update_user( array( 'ID' => $uid, 'user_email' => $e ) );
						}
						if ( null !== $r->get_param( 'phone' ) ) {
							$p = qs_phone_norm( (string) $r->get_param( 'phone' ) );
							if ( ! $p ) {
								return fsapi_error( 'reg_err_phone' );
							}
							if ( ( $o = qs_user_by_phone( $p ) ) && (int) $o !== $uid ) {
								return fsapi_error( 'reg_err_exists', 409 );
							}
							update_user_meta( $uid, 'qs_phone', $p );
						}
						return fsapi_user( $uid );
					},
				),
				array(
					'methods'             => 'DELETE',
					'permission_callback' => $user,
					'callback'            => function ( WP_REST_Request $r ) {
						$u = wp_get_current_user();
						if ( user_can( $u, 'edit_others_posts' ) ) {
							return fsapi_error( 'not_allowed', 403 ); // staff accounts are removed from the dashboard
						}
						$pass = (string) $r->get_param( 'password' );
						if ( ! $pass || ! wp_check_password( $pass, $u->user_pass, $u->ID ) ) {
							return fsapi_error( 'pw_needed', 403 );
						}
						require_once ABSPATH . 'wp-admin/includes/user.php';
						foreach ( get_posts( array( 'post_type' => 'listing', 'author' => $u->ID, 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1 ) ) as $pid ) {
							foreach ( fsapi_photos( $pid ) as $a ) {
								wp_delete_attachment( $a, true );
							}
							wp_delete_post( $pid, true );
						}
						wp_delete_user( $u->ID );
						return array( 'deleted' => true );
					},
				),
			)
		);

		/* POST /me/password {current, new} — this device stays signed in, the others are signed out. */
		register_rest_route(
			FSAPI_NS,
			'/me/password',
			array(
				'methods'             => 'POST',
				'permission_callback' => $user,
				'callback'            => function ( WP_REST_Request $r ) {
					$u   = wp_get_current_user();
					$cur = (string) $r->get_param( 'current' );
					$new = (string) $r->get_param( 'new' );
					if ( ! wp_check_password( $cur, $u->user_pass, $u->ID ) ) {
						return fsapi_error( 'login_err', 403 );
					}
					if ( strlen( $new ) < 8 ) {
						return fsapi_error( 'reg_err_pw' );
					}
					wp_set_password( $new, $u->ID ); // clears all tokens (see auth.php)
					return fsapi_signed_in( $u->ID, $r );
				},
			)
		);

		/* GET /me/ads?status=all|publish|pending|expired — my ads with their status. */
		register_rest_route(
			FSAPI_NS,
			'/me/ads',
			array(
				'methods'             => 'GET',
				'permission_callback' => $user,
				'callback'            => function ( WP_REST_Request $r ) {
					$st   = sanitize_key( (string) $r->get_param( 'status' ) );
					$st   = in_array( $st, array( 'publish', 'pending', 'expired', 'draft' ), true ) ? $st : array( 'publish', 'pending', 'expired', 'draft' );
					$page = max( 1, absint( $r->get_param( 'page' ) ?: 1 ) );
					$q    = new WP_Query( array( 'post_type' => 'listing', 'author' => get_current_user_id(), 'post_status' => $st, 'fields' => 'ids', 'posts_per_page' => 20, 'paged' => $page ) );
					return array(
						'items' => array_map( fn( $id ) => fsapi_ad_summary( $id ) + array( 'views' => (int) qs_m( $id, 'views' ), 'expires' => qs_m( $id, 'expires' ) ? gmdate( 'c', (int) qs_m( $id, 'expires' ) ) : null ), $q->posts ),
						'total' => (int) $q->found_posts,
						'page'  => $page,
						'pages' => (int) $q->max_num_pages,
					);
				},
			)
		);

		/* Favorites: GET list; POST {ids:[…]} adds several (to merge favorites saved on the phone before sign-in). */
		register_rest_route(
			FSAPI_NS,
			'/me/favorites',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => $user,
					'callback'            => function () {
						$ids = array_values( array_filter( fsapi_favs(), fn( $id ) => 'publish' === get_post_status( $id ) ) );
						return array( 'ids' => $ids, 'items' => array_map( 'fsapi_ad_summary', array_reverse( $ids ) ) );
					},
				),
				array(
					'methods'             => 'POST',
					'permission_callback' => $user,
					'callback'            => function ( WP_REST_Request $r ) {
						$add = array_filter( array_map( 'absint', (array) $r->get_param( 'ids' ) ), fn( $id ) => 'listing' === get_post_type( $id ) );
						$all = array_values( array_unique( array_merge( fsapi_favs(), $add ) ) );
						update_user_meta( get_current_user_id(), 'fsapi_favs', array_slice( $all, -500 ) );
						return array( 'ids' => $all );
					},
				),
			)
		);
		register_rest_route(
			FSAPI_NS,
			'/me/favorites/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'PUT',
					'permission_callback' => $user,
					'callback'            => function ( WP_REST_Request $r ) {
						$id = (int) $r['id'];
						if ( 'listing' !== get_post_type( $id ) ) {
							return fsapi_error( 'not_found', 404 );
						}
						$all = array_values( array_unique( array_merge( fsapi_favs(), array( $id ) ) ) );
						update_user_meta( get_current_user_id(), 'fsapi_favs', array_slice( $all, -500 ) );
						return array( 'ids' => $all, 'message' => qs_t( 'fav_added' ) );
					},
				),
				array(
					'methods'             => 'DELETE',
					'permission_callback' => $user,
					'callback'            => function ( WP_REST_Request $r ) {
						$all = array_values( array_diff( fsapi_favs(), array( (int) $r['id'] ) ) );
						update_user_meta( get_current_user_id(), 'fsapi_favs', $all );
						return array( 'ids' => $all, 'message' => qs_t( 'fav_removed' ) );
					},
				),
			)
		);

		/* Blocked advertisers (required by the App Store for user content): their ads disappear for this user. */
		register_rest_route(
			FSAPI_NS,
			'/me/blocks',
			array(
				'methods'             => 'GET',
				'permission_callback' => $user,
				'callback'            => fn() => array_map( fn( $id ) => array( 'id' => $id, 'name' => ( $u = get_userdata( $id ) ) ? $u->display_name : '' ), fsapi_blocked() ),
			)
		);
		register_rest_route(
			FSAPI_NS,
			'/me/blocks/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'PUT',
					'permission_callback' => $user,
					'callback'            => function ( WP_REST_Request $r ) {
						$id = (int) $r['id'];
						if ( ! get_userdata( $id ) || get_current_user_id() === $id ) {
							return fsapi_error( 'bad_request' );
						}
						$all = array_values( array_unique( array_merge( fsapi_blocked(), array( $id ) ) ) );
						update_user_meta( get_current_user_id(), 'fsapi_blocks', $all );
						return array( 'blocked' => $all );
					},
				),
				array(
					'methods'             => 'DELETE',
					'permission_callback' => $user,
					'callback'            => function ( WP_REST_Request $r ) {
						$all = array_values( array_diff( fsapi_blocked(), array( (int) $r['id'] ) ) );
						update_user_meta( get_current_user_id(), 'fsapi_blocks', $all );
						return array( 'blocked' => $all );
					},
				),
			)
		);

		/* POST /me/devices {token, platform: ios|android} — Firebase push token of this phone (sending comes with the app). */
		register_rest_route(
			FSAPI_NS,
			'/me/devices',
			array(
				'methods'             => 'POST',
				'permission_callback' => $user,
				'callback'            => function ( WP_REST_Request $r ) {
					$tok = sanitize_text_field( (string) $r->get_param( 'token' ) );
					if ( ! $tok ) {
						return fsapi_error( 'bad_request' );
					}
					$list         = (array) get_user_meta( get_current_user_id(), 'fsapi_devices', true );
					$list[ $tok ] = array( 'platform' => sanitize_key( (string) $r->get_param( 'platform' ) ), 'lang' => qs_lang(), 'time' => time() );
					update_user_meta( get_current_user_id(), 'fsapi_devices', array_slice( $list, -10, null, true ) );
					return array( 'ok' => true );
				},
			)
		);
	}
);
