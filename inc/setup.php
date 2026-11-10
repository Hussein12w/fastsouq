<?php
/** Content model, assets, browse filters, expiry, views, search. */
defined( 'ABSPATH' ) || exit;

/**
 * Field group of a section: the five car sections have their own groups (inc/data.php),
 * "Cars" itself has none, and every other main category uses the general fields.
 */
function qs_group_of( $section_slug ) {
	if ( isset( qs_sections()[ $section_slug ] ) ) {
		return qs_sections()[ $section_slug ][2];
	}
	return 'cars' === $section_slug ? '' : 'general';
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_image_size( 'qs-card', 640, 480, true );
		add_image_size( 'qs-large', 1280, 860, false );
		register_nav_menus( array( 'footer' => 'Footer links' ) );
	}
);

add_action( 'init', 'qs_register_content' );
function qs_register_content() {
	register_post_type(
		'listing',
		array(
			'labels'        => array( 'name' => 'الإعلانات', 'singular_name' => 'إعلان', 'add_new_item' => 'إضافة إعلان', 'edit_item' => 'تعديل الإعلان', 'all_items' => 'كل الإعلانات', 'menu_name' => 'الإعلانات' ),
			'public'        => true,
			'has_archive'   => 'ads',
			'rewrite'       => array( 'slug' => 'ad', 'with_front' => false ),
			'menu_icon'     => 'dashicons-megaphone',
			'menu_position' => 5,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'author' ),
		)
	);
	register_taxonomy(
		'qs_cat',
		'listing',
		array(
			'labels'            => array( 'name' => 'الأقسام', 'singular_name' => 'قسم', 'add_new_item' => 'إضافة قسم', 'edit_item' => 'تعديل القسم', 'parent_item' => 'القسم الرئيسي' ),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'section', 'with_front' => false, 'hierarchical' => false ),
			'meta_box_cb'       => false,
		)
	);
	foreach ( array( 'qs_make' => array( 'make', 'الماركات والموديلات', 'ماركة / موديل' ), 'qs_city' => array( 'location', 'الإمارات والمدن', 'إمارة / مدينة' ) ) as $tax => $x ) {
		register_taxonomy(
			$tax,
			'listing',
			array(
				'labels'            => array( 'name' => $x[1], 'singular_name' => $x[2], 'add_new_item' => 'إضافة', 'edit_item' => 'تعديل', 'parent_item' => 'الأب' ),
				'hierarchical'      => true,
				'public'            => true,
				'show_admin_column' => 'qs_city' === $tax,
				'rewrite'           => array( 'slug' => $x[0], 'with_front' => false, 'hierarchical' => false ),
				'meta_box_cb'       => false,
			)
		);
	}
	register_post_status(
		'expired',
		array(
			'label'                     => 'منتهي',
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'منتهي <span class="count">(%s)</span>', 'منتهية <span class="count">(%s)</span>' ),
		)
	);
	global $wp_rewrite;
	$wp_rewrite->author_base = 'advertiser';
}

/* ---------- Assets ---------- */
add_action(
	'wp_enqueue_scripts',
	function () {
		$v = QS_VERSION;
		wp_enqueue_style( 'qs-fonts', 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap', array(), null );
		wp_enqueue_style( 'qs-main', QS_URI . '/assets/css/main.css', array(), $v );
		wp_enqueue_style( 'qs-site', QS_URI . '/assets/css/site.css', array( 'qs-main' ), $v );
		$map = is_singular( 'listing' ) || is_page( 'post-ad' );
		if ( $map ) {
			wp_enqueue_style( 'leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css', array(), null );
			wp_enqueue_script( 'leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js', array(), null, true );
		}
		wp_enqueue_script( 'qs-site', QS_URI . '/assets/js/site.js', $map ? array( 'leaflet' ) : array(), $v, true );
		wp_localize_script(
			'qs-site',
			'QS',
			array(
				'txt' => array(
					'add_fav' => qs_t( 'add_fav' ), 'in_fav' => qs_t( 'in_fav' ), 'copied' => qs_t( 'link_copied' ),
					'del' => qs_t( 'confirm_delete' ), 'fav_added' => qs_t( 'fav_added' ), 'fav_rm' => qs_t( 'fav_removed' ),
				),
			)
		);
	}
);
add_action(
	'wp_head',
	function () {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><meta name="theme-color" content="#1746B4">' . "\n";
		if ( ! has_site_icon() ) {
			echo '<link rel="icon" href="' . esc_url( QS_URI . '/assets/img/logo-sm.png' ) . '">' . "\n";
		}
	},
	1
);
add_filter(
	'language_attributes',
	function ( $out ) {
		return is_admin() ? $out : 'lang="' . esc_attr( qs_lang() ) . '" dir="' . ( qs_is_rtl() ? 'rtl' : 'ltr' ) . '"';
	}
);

/* Members stay on the site: no admin bar, no dashboard (staff excepted). */
add_filter( 'show_admin_bar', fn( $show ) => current_user_can( 'edit_others_posts' ) ? $show : false );
add_action(
	'admin_init',
	function () {
		if ( ! wp_doing_ajax() && is_user_logged_in() && ! current_user_can( 'edit_others_posts' ) && 'admin-post.php' !== basename( $_SERVER['PHP_SELF'] ?? '' ) ) {
			wp_safe_redirect( qs_page_url( 'account' ) );
			exit;
		}
	}
);

/* ---------- Pages the theme needs ---------- */
function qs_pages() {
	return array(
		'post'      => array( 'post-ad', 'أضف إعلانك', 'page-templates/post-ad.php' ),
		'login'     => array( 'login', 'تسجيل الدخول', 'page-templates/login.php' ),
		'account'   => array( 'my-ads', 'حسابي', 'page-templates/account.php' ),
		'favorites' => array( 'favorites', 'المفضلة', 'page-templates/favorites.php' ),
	);
}
function qs_page_url( $key, $args = array() ) {
	$url = home_url( '/' . qs_pages()[ $key ][0] . '/' );
	return $args ? add_query_arg( $args, $url ) : $url;
}
function qs_create_pages() {
	foreach ( qs_pages() as $p ) {
		$page = get_page_by_path( $p[0] );
		$id   = $page ? $page->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $p[0], 'post_title' => $p[1] ) );
		update_post_meta( $id, '_wp_page_template', $p[2] );
	}
}

/* ---------- Browse filters (field-driven, see qs_filter_fields()) ---------- */
function qs_f( $k ) {
	return isset( $_GET[ $k ] ) ? sanitize_text_field( wp_unslash( $_GET[ $k ] ) ) : '';
}
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( ! ( $q->is_post_type_archive( 'listing' ) || $q->is_tax( array( 'qs_cat', 'qs_make', 'qs_city' ) ) || $q->is_search() || $q->is_author() ) ) {
			return;
		}
		$q->set( 'post_type', 'listing' );
		$q->set( 'posts_per_page', 24 );
		$meta = array( 'relation' => 'AND', 'feat' => array( 'key' => '_qs_featured', 'type' => 'NUMERIC' ), 'bump' => array( 'key' => '_qs_bump', 'type' => 'NUMERIC' ) );
		$tax  = array();
		if ( $g = qs_f( 'group' ) ) {
			$meta[] = array( 'key' => '_qs_group', 'value' => $g );
		}
		foreach ( array( 'body', 'cond', 'gear', 'fuel', 'cyl', 'specs', 'color', 'part_type', 'deal' ) as $k ) {
			if ( '' !== ( $v = qs_f( $k ) ) ) {
				$meta[] = array( 'key' => '_qs_' . $k, 'value' => $v );
			}
		}
		foreach ( array( 'year' => array( 'ymin', 'ymax' ), 'price' => array( 'pmin', 'pmax' ), 'km' => array( 'kmin', 'kmax' ) ) as $k => $r ) {
			if ( '' !== qs_f( $r[0] ) && is_numeric( qs_f( $r[0] ) ) ) {
				$meta[] = array( 'key' => '_qs_' . $k, 'value' => (float) qs_f( $r[0] ), 'compare' => '>=', 'type' => 'NUMERIC' );
			}
			if ( '' !== qs_f( $r[1] ) && is_numeric( qs_f( $r[1] ) ) ) {
				$meta[] = array( 'key' => '_qs_' . $k, 'value' => (float) qs_f( $r[1] ), 'compare' => '<=', 'type' => 'NUMERIC' );
			}
		}
		if ( $b = qs_f( 'brand' ) ) {
			$meta[] = array( 'key' => '_qs_brand', 'value' => $b, 'compare' => 'LIKE' );
		}
		$q->set( 'meta_query', $meta );
		foreach ( array( 'sec' => 'qs_cat', 'cat' => 'qs_cat', 'itype' => 'qs_cat', 'model' => 'qs_make', 'make' => 'qs_make', 'city' => 'qs_city', 'emirate' => 'qs_city' ) as $k => $t ) {
			if ( $v = qs_f( $k ) ) {
				if ( 'make' === $k && qs_f( 'model' ) ) {
					continue;
				}
				if ( 'emirate' === $k && qs_f( 'city' ) ) {
					continue;
				}
				if ( 'sec' === $k && ( qs_f( 'cat' ) || qs_f( 'itype' ) ) ) {
					continue;
				}
				if ( 'cat' === $k && qs_f( 'itype' ) ) {
					continue;
				}
				$tax[] = array( 'taxonomy' => $t, 'field' => 'slug', 'terms' => $v );
			}
		}
		if ( $tax ) {
			$q->set( 'tax_query', $tax );
		}
		$sort = qs_f( 'sort' );
		if ( 'asc' === $sort || 'desc' === $sort ) {
			$q->set( 'meta_key', '_qs_price' );
			$q->set( 'orderby', array( 'meta_value_num' => strtoupper( $sort ), 'bump' => 'DESC' ) );
		} else {
			$q->set( 'orderby', array( 'feat' => 'DESC', 'bump' => 'DESC' ) );
		}
	}
);

/* Search also matches brand-like fields and the English title. */
add_filter(
	'posts_search',
	function ( $search, $q ) {
		global $wpdb;
		if ( ! $search || is_admin() || ! $q->is_main_query() || ! $q->is_search() ) {
			return $search;
		}
		$like = '%' . $wpdb->esc_like( $q->get( 's' ) ) . '%';
		/* Arabic make / model names ("تويوتا", "لاند كروزر") also find the English-named terms. */
		$alt  = qs_make_en( $q->get( 's' ) );
		$alt  = '%' . $wpdb->esc_like( $alt ? $alt : $q->get( 's' ) ) . '%';
		$sub  = $wpdb->prepare( "({$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_qs_title_en','_qs_area','_qs_brand','_qs_sku') AND meta_value LIKE %s) OR {$wpdb->posts}.ID IN (SELECT tr.object_id FROM {$wpdb->term_relationships} tr JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id JOIN {$wpdb->terms} t ON t.term_id = tt.term_id LEFT JOIN {$wpdb->termmeta} tm ON tm.term_id = t.term_id AND tm.meta_key IN ('name_en','name_ar') WHERE tt.taxonomy IN ('qs_make','qs_cat') AND ( t.name LIKE %s OR t.name LIKE %s OR tm.meta_value LIKE %s )))", $like, $like, $alt, $like );
		return preg_replace( '/^\s*AND\s*\(/', ' AND ( ' . $sub . ' OR (', $search, 1 ) . ')';
	},
	10,
	2
);

/* ---------- Expiry (Customizer → FastSouq; 0 = never) ---------- */
add_action(
	'transition_post_status',
	function ( $new, $old, $post ) {
		if ( 'listing' !== $post->post_type || 'publish' !== $new || 'publish' === $old ) {
			return;
		}
		update_post_meta( $post->ID, '_qs_bump', time() );
		if ( $days = absint( get_theme_mod( 'qs_listing_days', 60 ) ) ) {
			update_post_meta( $post->ID, '_qs_expires', time() + $days * DAY_IN_SECONDS );
		}
		if ( 'pending' === $old && ( $u = get_userdata( $post->post_author ) ) && $u->user_email ) {
			wp_mail( $u->user_email, '[' . qs_t( 'site_name' ) . '] ✓ ' . $post->post_title, "تم نشر إعلانك / Your ad is live:\n" . get_permalink( $post ) );
		}
	},
	10,
	3
);
add_action(
	'qs_hourly',
	function () {
		$ids = get_posts( array( 'post_type' => 'listing', 'fields' => 'ids', 'posts_per_page' => 200, 'meta_query' => array( array( 'key' => '_qs_expires', 'value' => time(), 'compare' => '<', 'type' => 'NUMERIC' ) ) ) );
		foreach ( $ids as $id ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'expired' ) );
		}
		$feat = get_posts( array( 'post_type' => 'listing', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 500, 'meta_query' => array( array( 'key' => '_qs_featured_until', 'value' => array( 1, time() ), 'compare' => 'BETWEEN', 'type' => 'NUMERIC' ) ) ) );
		foreach ( $feat as $id ) {
			update_post_meta( $id, '_qs_featured', 0 );
			delete_post_meta( $id, '_qs_featured_until' );
		}
	}
);
add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( 'qs_hourly' ) ) {
			wp_schedule_event( time() + 300, 'hourly', 'qs_hourly' );
		}
	}
);

/* Views (one per visitor per ad per day) and owner preview of ads under review. */
add_action(
	'template_redirect',
	function () {
		if ( is_singular( 'listing' ) ) {
			$id = get_queried_object_id();
			if ( empty( $_COOKIE[ 'qs_v' . $id ] ) ) {
				update_post_meta( $id, '_qs_views', (int) get_post_meta( $id, '_qs_views', true ) + 1 );
				setcookie( 'qs_v' . $id, '1', time() + DAY_IN_SECONDS, COOKIEPATH ?: '/' );
			}
		}
	}
);
add_filter(
	'posts_results',
	function ( $posts, $q ) {
		if ( $q->is_main_query() && $q->is_singular && empty( $posts ) && ! is_admin() && is_user_logged_in() && ( $q->get( 'listing' ) || $q->get( 'p' ) ) ) {
			$p = $q->get( 'p' ) ? get_post( (int) $q->get( 'p' ) ) : get_page_by_path( $q->get( 'listing' ), OBJECT, 'listing' );
			if ( $p && 'listing' === $p->post_type && (int) $p->post_author === get_current_user_id() ) {
				return array( $p );
			}
		}
		return $posts;
	},
	10,
	2
);

/* Every ad always has the ordering keys (featured flag + bump time). */
add_action(
	'save_post_listing',
	function ( $id ) {
		if ( wp_is_post_revision( $id ) ) {
			return;
		}
		if ( '' === get_post_meta( $id, '_qs_featured', true ) ) {
			update_post_meta( $id, '_qs_featured', 0 );
		}
		if ( '' === get_post_meta( $id, '_qs_bump', true ) ) {
			update_post_meta( $id, '_qs_bump', get_post_time( 'U', true, $id ) ?: time() );
		}
	},
	99
);

/* Browser titles in the current language. */
add_filter(
	'document_title_parts',
	function ( $p ) {
		if ( is_singular( 'listing' ) ) {
			$p['title'] = qs_title( get_queried_object_id() );
		} elseif ( is_tax() ) {
			$p['title'] = qs_term_name( get_queried_object() );
		} elseif ( is_front_page() ) {
			$p['title']   = qs_t( 'site_name' );
			$p['tagline'] = qs_t( 'tagline' );
		}
		if ( isset( $p['site'] ) ) {
			$p['site'] = qs_t( 'site_name' );
		}
		return $p;
	}
);
