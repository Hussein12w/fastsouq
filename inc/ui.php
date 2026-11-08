<?php
/** Helpers and shared UI: icons, ad data, cards, breadcrumbs, navigation, map, favorites. */
defined( 'ABSPATH' ) || exit;

function qs_ic( $name, $cls = '' ) {
	static $p = null;
	if ( null === $p ) {
		$p = qs_icon_paths() + array(
			'car'   => '<path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11"/><rect x="3" y="11" width="18" height="6" rx="2"/><circle cx="7.5" cy="17" r="1.8"/><circle cx="16.5" cy="17" r="1.8"/>',
			'wheel' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 3v6M12 15v6M3 12h6M15 12h6"/>',
			'gear'  => '<circle cx="6" cy="6" r="2"/><circle cx="12" cy="6" r="2"/><circle cx="18" cy="6" r="2"/><circle cx="6" cy="18" r="2"/><circle cx="12" cy="18" r="2"/><path d="M6 8v8M12 8v8M18 8v4H6"/>',
		);
	}
	return '<svg class="ic ' . esc_attr( $cls ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $p[ $name ] ?? '' ) . '</svg>';
}
function qs_art( $group ) {
	static $c = array();
	$f = array( 'vehicle' => 'car', 'item' => 'acc', 'service' => 'service' )[ $group ] ?? 'car';
	return $c[ $f ] ??= file_get_contents( QS_DIR . '/assets/art/' . $f . '.svg' );
}
function qs_fmt( $n ) {
	return number_format( (float) $n, ( floor( (float) $n ) == (float) $n ) ? 0 : 2 );
}
function qs_m( $id, $k ) {
	return get_post_meta( $id, '_qs_' . $k, true );
}

/* ---------- Categories ---------- */
function qs_term( $slug, $tax = 'qs_cat' ) {
	static $c = array();
	return $c[ $tax . $slug ] ??= ( get_term_by( 'slug', $slug, $tax ) ?: null );
}
function qs_children( $parent, $tax = 'qs_cat' ) {
	$p = is_object( $parent ) ? $parent : ( $parent ? qs_term( $parent, $tax ) : null );
	if ( $parent && ! $p ) {
		return array();
	}
	static $c = array();
	$key = $tax . ( $p ? $p->term_id : 0 );
	if ( ! isset( $c[ $key ] ) ) {
		$t = get_terms( array( 'taxonomy' => $tax, 'parent' => $p ? $p->term_id : 0, 'hide_empty' => false, 'meta_key' => 'sort', 'orderby' => 'meta_value_num' ) );
		if ( is_wp_error( $t ) || ! $t ) {
			$t = get_terms( array( 'taxonomy' => $tax, 'parent' => $p ? $p->term_id : 0, 'hide_empty' => false, 'orderby' => 'name' ) );
		}
		$c[ $key ] = is_wp_error( $t ) ? array() : $t;
	}
	return $c[ $key ];
}
/** The ad's terms by level: [section, sub-section, item type]. */
function qs_levels( $id ) {
	$out   = array( null, null, null );
	$terms = get_the_terms( $id, 'qs_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return $out;
	}
	$deep = $terms[0];
	foreach ( $terms as $t ) {
		if ( count( get_ancestors( $t->term_id, 'qs_cat' ) ) > count( get_ancestors( $deep->term_id, 'qs_cat' ) ) ) {
			$deep = $t;
		}
	}
	$chain = array_reverse( array_merge( array( $deep->term_id ), get_ancestors( $deep->term_id, 'qs_cat' ) ) );
	foreach ( $chain as $i => $tid ) {
		if ( $i < 3 ) {
			$out[ $i ] = get_term( $tid, 'qs_cat' );
		}
	}
	return $out;
}
function qs_sub( $id ) {
	return qs_levels( $id )[1];
}
function qs_group( $id ) {
	return qs_m( $id, 'group' ) ?: 'vehicle';
}
/** [make, model] terms of an ad. */
function qs_make_model( $id ) {
	$make  = null;
	$model = null;
	$t     = get_the_terms( $id, 'qs_make' );
	foreach ( ( $t && ! is_wp_error( $t ) ) ? $t : array() as $x ) {
		if ( $x->parent ) {
			$model = $x;
			$make  = $make ?: get_term( $x->parent, 'qs_make' );
		} else {
			$make = $x;
		}
	}
	return array( $make, $model );
}
function qs_city_label( $id ) {
	$t = get_the_terms( $id, 'qs_city' );
	if ( ! $t || is_wp_error( $t ) ) {
		return '';
	}
	$city = $t[0];
	foreach ( $t as $x ) {
		if ( $x->parent ) {
			$city = $x;
		}
	}
	$em = $city->parent ? get_term( $city->parent, 'qs_city' ) : null;
	return qs_term_name( $city ) . ( $em ? '، ' . qs_term_name( $em ) : '' );
}
/** Label of an option value ("Arabic|English" lists in inc/data.php). */
function qs_opt( $list, $val ) {
	$o = qs_options( $list );
	if ( ! isset( $o[ $val ] ) ) {
		return (string) $val;
	}
	$p = explode( '|', $o[ $val ] );
	return 'en' === qs_lang() ? ( $p[1] ?? $p[0] ) : $p[0];
}
function qs_pair( $s ) {
	$p = explode( '|', $s );
	return 'en' === qs_lang() ? ( $p[1] ?? $p[0] ) : $p[0];
}

/* ---------- Ad data ---------- */
function qs_photo( $id, $size = 'qs-card' ) {
	$u = get_the_post_thumbnail_url( $id, $size );
	return $u ? '<img class="ph ok" src="' . esc_url( $u ) . '" alt="" loading="lazy">' : '';
}
function qs_media( $id, $size = 'qs-card' ) {
	return qs_art( qs_group( $id ) ) . qs_photo( $id, $size );
}
function qs_price( $id ) {
	$p = qs_m( $id, 'price' );
	if ( ! is_numeric( $p ) || (float) $p <= 0 ) {
		return '<span class="price ask">' . esc_html( qs_t( 'price_ask' ) ) . '</span>';
	}
	return '<span class="price">' . qs_fmt( $p ) . ' <small>' . esc_html( qs_t( 'cur' ) ) . '</small></span>';
}
function qs_ago( $id ) {
	$d = (int) floor( ( time() - get_post_time( 'U', true, $id ) ) / DAY_IN_SECONDS );
	return $d <= 0 ? qs_t( 'today' ) : ( 1 === $d ? qs_t( 'ago_1' ) : qs_tf( 'ago_d', array( 'n' => $d ) ) );
}
function qs_phone_of( $id ) {
	return qs_m( $id, 'phone' ) ?: get_user_meta( get_post_field( 'post_author', $id ), 'qs_phone', true );
}
function qs_phone_fmt( $d ) {
	$d = preg_replace( '/\D/', '', (string) $d );
	return preg_match( '/^971(5\d)(\d{3})(\d{4})$/', $d, $m ) ? '+971 ' . $m[1] . ' ' . $m[2] . ' ' . $m[3] : ( $d ? '+' . $d : '' );
}
function qs_phone_norm( $in ) {
	$d = preg_replace( '/\D/', '', (string) $in );
	$d = ltrim( preg_replace( '/^(00)?971/', '', $d ), '0' );
	return preg_match( '/^[2-9]\d{7,8}$/', $d ) ? '971' . $d : '';
}
function qs_wa_link( $phone, $text = '' ) {
	return 'https://wa.me/' . preg_replace( '/\D/', '', $phone ) . ( $text ? '?text=' . rawurlencode( $text ) : '' );
}

/* ---------- Favorites (cookie) ---------- */
function qs_favs() {
	$raw = isset( $_COOKIE['qs_favs'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['qs_favs'] ) ) : '';
	return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
}
function qs_fav_btn( $id, $wide = false ) {
	static $f = null;
	$f ??= qs_favs();
	$on = in_array( (int) $id, $f, true );
	if ( $wide ) {
		return '<button class="act fav-wide' . ( $on ? ' on' : '' ) . '" data-fav="' . (int) $id . '">' . qs_ic( 'heart' ) . ' <span>' . esc_html( qs_t( $on ? 'in_fav' : 'add_fav' ) ) . '</span></button>';
	}
	return '<button class="fav' . ( $on ? ' on' : '' ) . '" data-fav="' . (int) $id . '" aria-label="' . esc_attr( qs_t( 'add_fav' ) ) . '">' . qs_ic( 'heart' ) . '</button>';
}

/* ---------- Card (fields per group, as in the spreadsheet) ---------- */
function qs_card( $id ) {
	$group = qs_group( $id );
	$url   = get_permalink( $id );
	$phone = qs_phone_of( $id );
	$lv    = qs_levels( $id );
	list( $make, $model ) = qs_make_model( $id );
	$badge = qs_m( $id, 'featured' ) ? '<span class="b b-y">' . qs_ic( 'star', 'sm' ) . ' ' . esc_html( qs_t( 'featured' ) ) . '</span>' : '';
	if ( 'wanted' === qs_m( $id, 'deal' ) ) {
		$badge .= '<span class="b b-k">' . esc_html( qs_t( 'wanted' ) ) . '</span>';
	}
	$mm = trim( qs_term_name( $make ) . ' ' . qs_term_name( $model ) );
	if ( 'vehicle' === $group ) {
		$attrs = array(
			( $y = qs_m( $id, 'year' ) ) ? qs_ic( 'cal', 'sm' ) . ' ' . esc_html( $y ) : '',
			'' !== ( $k = qs_m( $id, 'km' ) ) ? qs_ic( 'gauge', 'sm' ) . ' ' . qs_fmt( $k ) . ' ' . esc_html( qs_t( 'km' ) ) : '',
			( $g = qs_m( $id, 'gear' ) ) ? qs_ic( 'gear', 'sm' ) . ' ' . esc_html( qs_opt( 'gear', $g ) ) : '',
			( $sp = qs_m( $id, 'specs' ) ) ? qs_ic( 'globe', 'sm' ) . ' ' . esc_html( qs_opt( 'specs', $sp ) ) : '',
		);
		$top   = qs_price( $id );
		$line2 = esc_html( $mm ?: qs_term_name( $lv[1] ) );
	} elseif ( 'item' === $group ) {
		$attrs = array(
			( $c = qs_m( $id, 'cond' ) ) ? qs_ic( 'tag', 'sm' ) . ' ' . esc_html( qs_opt( 'item_cond', $c ) ) : '',
			( $pt = qs_m( $id, 'part_type' ) ) ? qs_ic( 'shield', 'sm' ) . ' ' . esc_html( qs_opt( 'part_type', $pt ) ) : '',
			$mm ? qs_ic( 'car', 'sm' ) . ' ' . esc_html( $mm ) : '',
		);
		$top   = qs_price( $id );
		$line2 = esc_html( qs_term_name( $lv[2] ?: $lv[1] ) );
	} else {
		$attrs = array();
		$top   = '<span class="svc-sec">' . qs_ic( 'wrench', 'sm' ) . ' ' . esc_html( qs_term_name( $lv[1] ) ) . '</span>';
		$line2 = '';
	}
	$attrs = array_slice( array_filter( $attrs ), 0, 3 );
	$acts  = $phone ? '<div class="c-acts"><a class="btn btn-sm btn-line" href="tel:+' . esc_attr( $phone ) . '">' . qs_ic( 'phone' ) . ' <span class="ltr">' . esc_html( qs_phone_fmt( $phone ) ) . '</span></a><a class="btn btn-sm btn-wa" href="' . esc_url( qs_wa_link( $phone, qs_title( $id ) . ' — ' . $url ) ) . '" target="_blank" rel="noopener" aria-label="WhatsApp">' . qs_ic( 'wa' ) . '</a></div>' : '';
	return '<article class="card dz qs-card g-' . esc_attr( $group ) . ( qs_m( $id, 'featured' ) ? ' is-feat' : '' ) . '"><a class="stretch" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( qs_title( $id ) ) . '"></a>
    <div class="media">' . qs_media( $id ) . '<div class="badges">' . $badge . '</div>' . qs_fav_btn( $id ) . '</div>
    <div class="cbody"><div class="cprice">' . $top . '</div><h3>' . esc_html( qs_title( $id ) ) . '</h3>
      ' . ( $line2 ? '<div class="csub">' . $line2 . '</div>' : '' ) . ( $attrs ? '<div class="cattrs">' . implode( '', array_map( fn( $x ) => '<span>' . $x . '</span>', $attrs ) ) . '</div>' : '' ) . '
      <div class="cloc"><span>' . qs_ic( 'pin', 'sm' ) . ' ' . esc_html( qs_city_label( $id ) ) . '</span><span class="ago">' . esc_html( qs_ago( $id ) ) . '</span></div>' . $acts . '</div></article>';
}

function qs_sec_head( $h, $link = '', $icon = '' ) {
	return '<div class="sec-h"><h2>' . ( $icon ? '<span class="h-ic">' . qs_ic( $icon ) . '</span>' : '' ) . esc_html( $h ) . '</h2>' . ( $link ? '<a href="' . esc_url( $link ) . '" class="more">' . esc_html( qs_t( 'view_all' ) ) . ' ' . qs_ic( 'chev', 'flip' ) . '</a>' : '' ) . '</div>';
}
function qs_crumbs( $arr ) {
	$h = '<nav class="crumbs"><button class="back-btn" data-act="back" aria-label="' . esc_attr( qs_t( 'back' ) ) . '">' . qs_ic( 'arrow', 'flipb' ) . '<span>' . esc_html( qs_t( 'back' ) ) . '</span></button><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( qs_t( 'home' ) ) . '</a>';
	foreach ( $arr as $a ) {
		$h .= qs_ic( 'chev', 'flip sm' ) . ( ! empty( $a[1] ) ? '<a href="' . esc_url( $a[1] ) . '">' . esc_html( $a[0] ) . '</a>' : '<span>' . esc_html( $a[0] ) . '</span>' );
	}
	return $h . '</nav>';
}
function qs_empty( $icon = 'search', $text = '', $btn = '' ) {
	return '<div class="empty">' . qs_ic( $icon ) . '<p>' . esc_html( $text ?: qs_t( 'no_results' ) ) . '</p>' . $btn . '</div>';
}
function qs_logo( $cls = '' ) {
	$img = get_theme_mod( 'qs_logo' ) ?: QS_URI . '/assets/img/logo.png';
	return '<a href="' . esc_url( home_url( '/' ) ) . '" class="logo qs-logo ' . esc_attr( $cls ) . '" aria-label="FastSouq"><img src="' . esc_url( $img ) . '" alt="FastSouq — فاست سوق"></a>';
}
function qs_pagination() {
	$l = paginate_links( array( 'type' => 'array', 'prev_text' => qs_ic( 'chevl', 'flip' ), 'next_text' => qs_ic( 'chev', 'flip' ), 'mid_size' => 1 ) );
	return $l ? '<nav class="qs-pager">' . implode( '', $l ) . '</nav>' : '';
}
function qs_notice() {
	$h = '';
	if ( $m = qs_f( 'qs_msg' ) ) {
		$h .= '<div class="qs-notice ok">' . qs_ic( 'check' ) . ' ' . esc_html( qs_t( $m ) ) . '</div>';
	}
	if ( $e = qs_f( 'qs_err' ) ) {
		$h .= '<div class="qs-notice err">' . qs_ic( 'info' ) . ' ' . esc_html( qs_t( $e ) ) . '</div>';
	}
	return $h;
}
function qs_lang_btns() {
	$h = '';
	foreach ( array( 'ar' => 'العربية', 'en' => 'English' ) as $l => $n ) {
		$h .= '<a href="' . esc_url( add_query_arg( 'lang', $l ) ) . '" class="' . ( qs_lang() === $l ? 'on' : '' ) . '" lang="' . $l . '" rel="nofollow">' . esc_html( $n ) . '</a>';
	}
	return $h;
}

/** Main navigation: the five sections with their sub-sections. */
function qs_nav() {
	static $n = null;
	if ( null !== $n ) {
		return $n;
	}
	$n = array();
	foreach ( qs_sections() as $slug => $x ) {
		if ( ! ( $t = qs_term( $slug ) ) ) {
			continue;
		}
		$n[] = array( 'slug' => $slug, 'label' => qs_term_name( $t ), 'url' => get_term_link( $t ), 'icon' => $x[1], 'term' => $t, 'subs' => array_map( fn( $c ) => array( 'label' => qs_term_name( $c ), 'url' => get_term_link( $c ), 'slug' => $c->slug ), qs_children( $t ) ) );
	}
	return $n;
}
/** Top-level section of the current page. */
function qs_nav_key() {
	if ( is_front_page() ) {
		return 'home';
	}
	$t = null;
	if ( is_tax( 'qs_cat' ) ) {
		$t = get_queried_object();
	} elseif ( is_singular( 'listing' ) ) {
		$t = qs_levels( get_queried_object_id() )[0];
	}
	while ( $t && $t->parent ) {
		$t = get_term( $t->parent, 'qs_cat' );
	}
	return $t ? $t->slug : '';
}

/* ---------- Map ---------- */
function qs_map( $lat, $lng, $edit = false ) {
	$lat = is_numeric( $lat ) ? (float) $lat : '';
	$lng = is_numeric( $lng ) ? (float) $lng : '';
	return '<div class="qs-map" data-map data-lat="' . esc_attr( $lat ) . '" data-lng="' . esc_attr( $lng ) . '"' . ( $edit ? ' data-edit' : '' ) . '></div>';
}
function qs_modal_tpl( $id, $html, $cls = '' ) {
	return '<template id="tpl-' . esc_attr( $id ) . '" data-cls="' . esc_attr( $cls ) . '">' . $html . '</template>';
}
function qs_ads_url( $args = array() ) {
	$u = get_post_type_archive_link( 'listing' );
	return $args ? add_query_arg( $args, $u ) : $u;
}
