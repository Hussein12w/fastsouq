<?php
/** Public data: app settings and form definitions, categories, makes, cities, home screen, sponsored banners. */
defined( 'ABSPATH' ) || exit;

add_action(
	'rest_api_init',
	function () {
		$pub = '__return_true';

		/* Settings the app needs at start: contact, slides, option lists, form fields per group, report reasons. */
		register_rest_route(
			FSAPI_NS,
			'/config',
			array(
				'methods'             => 'GET',
				'permission_callback' => $pub,
				'callback'            => function () {
					$groups = array();
					foreach ( array( 'vehicle', 'item', 'service', 'general' ) as $g ) {
						$fields = array();
						foreach ( qs_fields() as $k => $f ) {
							if ( ! in_array( $g, explode( ' ', $f[2] ), true ) ) {
								continue;
							}
							$kind  = $f[1];
							$field = array( 'key' => $k, 'label' => qs_t( qs_field_label( $k, $g ) ), 'required' => in_array( $g, explode( ' ', $f[3] ), true ) );
							if ( 0 === strpos( $kind, 'chips:' ) || 0 === strpos( $kind, 'select:' ) ) {
								list( $field['type'], $src ) = explode( ':', $kind );
								$field['options'] = array_map( fn( $o ) => array( 'value' => (string) $o[0], 'label' => (string) $o[1] ), qs_field_options( $src, $g ) );
							} elseif ( 'body' === $kind ) {
								$field['type']    = 'select';
								$field['options_by_sub'] = array_map( fn( $l ) => array_map( function ( $o ) { $p = explode( '|', $o ); return array( 'value' => $p[0], 'label' => 'en' === qs_lang() ? $p[2] : $p[1] ); }, $l ), qs_body_options() );
							} elseif ( 'make' === $kind || 'model' === $kind ) {
								$field['type']   = $kind; // lists from /makes (model depends on make; "other" model → show model_other)
							} elseif ( 'itype' === $kind ) {
								$field['type'] = 'category'; // children of the chosen sub-category (/categories)
							} else {
								$field['type'] = $kind; // text | number | textarea
							}
							if ( 'model_other' === $k ) {
								$field['show_if'] = 'model is "other"';
							}
							if ( 'period' === $k ) {
								$field['show_if'] = 'sub = cars-for-rent';
							}
							$fields[] = $field;
						}
						$groups[ $g ] = $fields;
					}
					$slides = array();
					foreach ( array( 1, 2 ) as $i ) {
						if ( $img = get_theme_mod( 'qs_slide' . $i ) ) {
							$slides[] = array( 'image' => $img, 'link' => get_theme_mod( 'qs_slide' . $i . '_link' ) ?: '' );
						}
					}
					$wa = preg_replace( '/\D/', '', (string) get_theme_mod( 'qs_whatsapp', '' ) );
					return array(
						'api_version'    => FSAPI_VERSION,
						'theme_version'  => defined( 'QS_VERSION' ) ? QS_VERSION : '',
						'lang'           => qs_lang(),
						'site'           => array(
							'name'     => qs_t( 'site_name' ),
							'url'      => home_url( '/' ),
							'logo'     => get_theme_mod( 'qs_logo' ) ?: ( defined( 'QS_URI' ) ? QS_URI . '/assets/img/logo.png' : '' ),
							'icon'     => defined( 'QS_URI' ) ? QS_URI . '/assets/img/logo-icon.png' : '',
							'phone'    => (string) get_theme_mod( 'qs_phone', '' ),
							'whatsapp' => $wa ? 'https://wa.me/' . $wa : '',
							'email'    => (string) get_theme_mod( 'qs_email', '' ),
							'privacy'  => get_privacy_policy_url(),
						),
						'slides'         => $slides,
						'currency'       => array( 'code' => 'AED', 'label' => qs_t( 'cur' ) ),
						'form'           => array(
							'groups'        => $groups,
							'common'        => array( 'emirate (required)', 'city', 'area', 'lat', 'lng', 'phone (required)', 'photos[] (up to 10, jpg/png/webp, 10MB each)', 'agree=1 (required)' ),
							'guest_account' => array( 'acc_name', 'acc_pass', 'acc_email' ),
						),
						'sort'           => array(
							array( 'value' => '', 'label' => qs_t( 'newest' ) ),
							array( 'value' => 'asc', 'label' => qs_t( 'price_low' ) ),
							array( 'value' => 'desc', 'label' => qs_t( 'price_high' ) ),
						),
						'report_reasons' => explode( '|', qs_t( 'reasons' ) ),
						'listing_days'   => absint( get_theme_mod( 'qs_listing_days', 60 ) ),
					);
				},
			)
		);

		/* Category tree: main → sub → detail (alphabetical, in the request language). ?parent=slug for one branch. */
		register_rest_route(
			FSAPI_NS,
			'/categories',
			array(
				'methods'             => 'GET',
				'permission_callback' => $pub,
				'callback'            => function ( WP_REST_Request $r ) {
					if ( $p = $r->get_param( 'parent' ) ) {
						$t = qs_term( sanitize_title( $p ) );
						return $t ? fsapi_cat_node( $t, 3 ) : fsapi_error( 'not_found', 404 );
					}
					return array_map( fn( $t ) => fsapi_cat_node( $t, 3 ), qs_children( 0 ) );
				},
			)
		);

		register_rest_route(
			FSAPI_NS,
			'/makes',
			array(
				'methods'             => 'GET',
				'permission_callback' => $pub,
				'callback'            => function () {
					return array_map(
						fn( $m ) => array(
							'slug'   => $m->slug,
							'name'   => qs_term_name( $m ),
							'other'  => qs_is_other( $m ),
							'models' => array_map( fn( $x ) => array( 'slug' => $x->slug, 'name' => qs_term_name( $x ), 'other' => qs_is_other( $x ) ), qs_children( $m, 'qs_make' ) ),
						),
						qs_children( 0, 'qs_make' )
					);
				},
			)
		);

		register_rest_route(
			FSAPI_NS,
			'/cities',
			array(
				'methods'             => 'GET',
				'permission_callback' => $pub,
				'callback'            => function () {
					return array_map( fn( $e ) => fsapi_term( $e ) + array( 'cities' => array_map( 'fsapi_term', qs_children( $e, 'qs_city' ) ) ), qs_children( 0, 'qs_city' ) );
				},
			)
		);

		/* Home screen: slides, main categories with counts, banners, latest 10 ads of each main category. */
		register_rest_route(
			FSAPI_NS,
			'/home',
			array(
				'methods'             => 'GET',
				'permission_callback' => $pub,
				'callback'            => function () {
					$blocked = fsapi_blocked();
					$counts  = get_transient( 'fsapi_main_counts' );
					if ( ! is_array( $counts ) ) {
						$counts = array();
						foreach ( qs_children( 0 ) as $t ) {
							$q                  = new WP_Query( array( 'post_type' => 'listing', 'fields' => 'ids', 'posts_per_page' => 1, 'tax_query' => array( array( 'taxonomy' => 'qs_cat', 'terms' => $t->term_id ) ) ) );
							$counts[ $t->slug ] = (int) $q->found_posts;
						}
						set_transient( 'fsapi_main_counts', $counts, 10 * MINUTE_IN_SECONDS );
					}
					$cats = array();
					foreach ( qs_nav() as $n ) {
						$cats[] = array( 'slug' => $n['slug'], 'name' => $n['label'], 'icon' => $n['icon'], 'count' => $counts[ $n['slug'] ] ?? 0, 'menu' => $n['menu'] );
					}
					$sections = array();
					list( $in, $more ) = qs_menu_split();
					foreach ( array_merge( $in, $more ) as $n ) {
						$ids = get_posts( array( 'post_type' => 'listing', 'fields' => 'ids', 'no_found_rows' => true, 'posts_per_page' => 10, 'author__not_in' => $blocked, 'tax_query' => array( array( 'taxonomy' => 'qs_cat', 'terms' => $n['term']->term_id ) ), 'meta_query' => array( 'feat' => array( 'key' => '_qs_featured', 'type' => 'NUMERIC' ), 'bump' => array( 'key' => '_qs_bump', 'type' => 'NUMERIC' ) ), 'orderby' => array( 'feat' => 'DESC', 'bump' => 'DESC' ) ) );
						if ( $ids ) {
							$sections[] = array( 'slug' => $n['slug'], 'name' => $n['label'], 'icon' => $n['icon'], 'ads' => array_map( 'fsapi_ad_summary', $ids ) );
						}
					}
					$slides = array();
					foreach ( array( 1, 2 ) as $i ) {
						if ( $img = get_theme_mod( 'qs_slide' . $i ) ) {
							$slides[] = array( 'image' => $img, 'link' => get_theme_mod( 'qs_slide' . $i . '_link' ) ?: '' );
						}
					}
					return array(
						'slides'     => $slides,
						'title'      => qs_t( 'hero_t' ),
						'categories' => $cats,
						'banners'    => array( 'top' => fsapi_banner( 'home_top' ), 'middle' => fsapi_banner( 'home_mid' ) ),
						'sections'   => $sections,
					);
				},
			)
		);

		/* One sponsored banner for a place (home_top, home_mid, cat_side, cat_list, ad_side), optionally for a category. */
		register_rest_route(
			FSAPI_NS,
			'/sponsored',
			array(
				'methods'             => 'GET',
				'permission_callback' => $pub,
				'args'                => array( 'place' => array( 'required' => true, 'enum' => array_keys( qs_spons_places() ) ) ),
				'callback'            => function ( WP_REST_Request $r ) {
					$cat = $r->get_param( 'category' ) ? qs_term( sanitize_title( $r->get_param( 'category' ) ) ) : null;
					return array( 'banner' => fsapi_banner( $r->get_param( 'place' ), $cat ) );
				},
			)
		);
	}
);

/* Keep the home counts fresh when ads change. */
add_action( 'transition_post_status', fn( $n, $o, $p ) => 'listing' === $p->post_type && delete_transient( 'fsapi_main_counts' ), 10, 3 );
