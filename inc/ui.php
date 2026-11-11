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
			/* Main categories */
			'music'     => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
			'sofa'      => '<path d="M4 11V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3"/><path d="M2 13a2 2 0 0 1 4 0v2h12v-2a2 2 0 0 1 4 0v4a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/><path d="M5 18v2M19 18v2"/>',
			'appliance' => '<rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="13" r="5"/><path d="M8 5.5h.01M11 5.5h.01"/>',
			'factory'   => '<path d="M2 20V9l6 4V9l6 4V4h4l2 16z"/><path d="M2 20h20"/>',
			'gamepad'   => '<rect x="2" y="7" width="20" height="11" rx="5"/><path d="M7 10.5v4M5 12.5h4"/><circle cx="15.5" cy="11.5" r=".8"/><circle cx="17.5" cy="13.8" r=".8"/>',
			'tv'        => '<rect x="2" y="5" width="20" height="13" rx="2"/><path d="M8 21h8M12 18v3"/>',
			'ticket'    => '<path d="M3 8a2 2 0 0 0 2-2h14a2 2 0 0 0 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 0-2 2H5a2 2 0 0 0-2-2v-2a2 2 0 0 0 0-4z"/><path d="M13 6v2M13 11v2M13 16v2"/>',
			'dumbbell'  => '<path d="M6.5 6.5v11M17.5 6.5v11M6.5 12h11"/><rect x="3" y="8" width="3.5" height="8" rx="1"/><rect x="17.5" y="8" width="3.5" height="8" rx="1"/>',
			'book'      => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/>',
			'laptop'    => '<rect x="4" y="4" width="16" height="11" rx="1.5"/><path d="M2 19h20l-1.5-4h-17z"/>',
			'diamond'   => '<path d="M6 3h12l4 6-10 12L2 9z"/><path d="M2 9h20M12 21 8 9l4-6 4 6z"/>',
			'trophy'    => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0z"/><path d="M7 6H4a3 3 0 0 0 3 4M17 6h3a3 3 0 0 1-3 4"/>',
			'shirt'     => '<path d="M8 3 3 6l2 5 3-1v11h8V10l3 1 2-5-5-3a4 4 0 0 1-8 0z"/>',
			'mobile'    => '<rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M11 18h2"/>',
			'briefcase' => '<rect x="2" y="7" width="20" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M2 13h20"/>',
			'list'      => '<path d="M9 6h12M9 12h12M9 18h12"/><rect x="3" y="4.5" width="3" height="3" rx=".5"/><rect x="3" y="10.5" width="3" height="3" rx=".5"/><rect x="3" y="16.5" width="3" height="3" rx=".5"/>',
			'dots'      => '<circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/>',
			'megaphone' => '<path d="M3 11v2a1 1 0 0 0 1 1h3l6 4V6L7 10H4a1 1 0 0 0-1 1z"/><path d="M16 9a4 4 0 0 1 0 6M19 6.5a8 8 0 0 1 0 11"/>',
		);
	}
	return '<svg class="ic ' . esc_attr( $cls ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $p[ $name ] ?? '' ) . '</svg>';
}
function qs_art( $group ) {
	static $c = array();
	$f = array( 'vehicle' => 'car', 'item' => 'acc', 'service' => 'service', 'general' => 'general' )[ $group ] ?? 'car';
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
	$key = $tax . ( $p ? $p->term_id : 0 ) . qs_lang();
	if ( ! isset( $c[ $key ] ) ) {
		/* Alphabetical in the current language (Arabic أ–ي, English A–Z); "Other" (أخرى) always last. */
		$t = get_terms( array( 'taxonomy' => $tax, 'parent' => $p ? $p->term_id : 0, 'hide_empty' => false ) );
		$t = is_wp_error( $t ) ? array() : qs_sort_terms( $t );
		if ( 'qs_make' === $tax ) {
			$other = array_filter( $t, 'qs_is_other' );
			$t     = array_merge( array_diff_key( $t, $other ), $other );
		}
		$c[ $key ] = is_wp_error( $t ) ? array() : $t;
	}
	return $c[ $key ];
}
/** The "Other" make / model (أخرى). */
function qs_is_other( $t ) {
	return $t && ( 'other' === $t->slug || '-other' === substr( $t->slug, -6 ) ) && 'Other' === $t->name;
}
/** Model name shown on an ad: the name the advertiser typed when they picked "Other". */
function qs_model_label( $id, $model ) {
	if ( ! $model ) {
		return '';
	}
	return qs_is_other( $model ) && ( $typed = qs_m( $id, 'model_other' ) ) ? $typed : qs_term_name( $model );
}
/** Alphabetical order by the name shown in the current language. */
function qs_sort_terms( $terms ) {
	$names = array();
	foreach ( $terms as $t ) {
		$names[ $t->term_id ] = qs_term_name( $t );
	}
	if ( class_exists( 'Collator' ) ) {
		$col = new Collator( 'en' === qs_lang() ? 'en' : 'ar' );
		usort( $terms, fn( $a, $b ) => $col->compare( $names[ $a->term_id ], $names[ $b->term_id ] ) );
	} else {
		$norm = fn( $s ) => mb_strtolower( strtr( $s, array( 'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا', 'ى' => 'ي', 'ة' => 'ه', 'ؤ' => 'و', 'ئ' => 'ي', 'ـ' => '' ) ) );
		usort( $terms, fn( $a, $b ) => strcmp( $norm( $names[ $a->term_id ] ), $norm( $names[ $b->term_id ] ) ) );
	}
	return $terms;
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
	if ( count( $chain ) > 1 && ( $root = get_term( $chain[0], 'qs_cat' ) ) && 'cars' === $root->slug ) {
		array_shift( $chain ); // car ads: [car section, sub-section, item type] under "Cars"
	}
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
	return qs_term_name( $city ) . ( $em ? ( 'en' === qs_lang() ? ', ' : '، ' ) . qs_term_name( $em ) : '' );
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
/** The key facts shown on an ad card, as [icon, text] (website card and app list). */
function qs_card_facts( $id ) {
	$group = qs_group( $id );
	$out   = array();
	if ( 'vehicle' === $group ) {
		if ( $y = qs_m( $id, 'year' ) ) {
			$out[] = array( 'cal', (string) $y );
		}
		if ( '' !== ( $k = qs_m( $id, 'km' ) ) ) {
			$out[] = array( 'gauge', qs_fmt( $k ) . ' ' . qs_t( 'km' ) );
		}
	} elseif ( in_array( $group, array( 'item', 'general' ), true ) && ( $c = qs_m( $id, 'cond' ) ) ) {
		$out[] = array( 'tag', qs_opt( 'item_cond', $c ) );
	}
	return $out;
}
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
	/* Card = photo, price, name, at most two key facts, and the contact buttons. Everything else is on the ad page. */
	$top   = 'service' === $group ? '<span class="svc-sec">' . qs_ic( 'wrench', 'sm' ) . ' ' . esc_html( qs_term_name( $lv[1] ) ) . '</span>' : qs_price( $id );
	$attrs = array_map( fn( $f ) => qs_ic( $f[0], 'sm' ) . ' ' . esc_html( $f[1] ), qs_card_facts( $id ) );
	$acts  = $phone ? '<div class="c-acts"><a class="btn btn-sm btn-line c-call" href="tel:+' . esc_attr( $phone ) . '" aria-label="' . esc_attr( qs_t( 'call' ) . ' ' . qs_phone_fmt( $phone ) ) . '">' . qs_ic( 'phone' ) . '<span class="c-lbl">' . esc_html( qs_t( 'call' ) ) . '</span></a><a class="btn btn-sm btn-wa" href="' . esc_url( qs_wa_link( $phone, qs_title( $id ) . ' — ' . $url ) ) . '" target="_blank" rel="noopener" aria-label="WhatsApp">' . qs_ic( 'wa' ) . '<span class="c-lbl">' . esc_html( qs_t( 'whatsapp' ) ) . '</span></a></div>' : '';
	return '<article class="card dz qs-card g-' . esc_attr( $group ) . ( qs_m( $id, 'featured' ) ? ' is-feat' : '' ) . '"><a class="stretch" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( qs_title( $id ) ) . '"></a>
    <div class="media">' . qs_media( $id ) . '<div class="badges">' . $badge . '</div>' . qs_fav_btn( $id ) . '</div>
    <div class="cbody"><div class="cprice">' . $top . '</div><h3>' . esc_html( qs_title( $id ) ) . '</h3>
      ' . ( $attrs ? '<div class="cattrs">' . implode( '', array_map( fn( $x ) => '<span>' . $x . '</span>', $attrs ) ) . '</div>' : '' ) . $acts . '</div></article>';
}

/** Details of an ad as [label, value] rows, in the current language (ad page and app). */
function qs_ad_specs( $id ) {
	$group = qs_group( $id );
	$lv    = qs_levels( $id );
	list( $make, $model ) = qs_make_model( $id );
	$addr  = trim( qs_city_label( $id ) . ( qs_m( $id, 'area' ) ? ' — ' . qs_m( $id, 'area' ) : '' ) );
	$rows = array();
	$add  = function ( $label, $val ) use ( &$rows ) {
		if ( '' !== trim( (string) $val ) ) {
			$rows[] = array( $label, $val );
		}
	};
	$add( qs_t( 'subsection' ), qs_term_name( $lv[1] ) );
	if ( 'vehicle' === $group ) {
		$add( qs_t( 'make' ), $make ? qs_term_name( $make ) : '' );
		$add( qs_t( 'model' ), qs_model_label( $id, $model ) );
		$add( qs_t( 'year' ), qs_m( $id, 'year' ) );
		$body = qs_m( $id, 'body' );
		if ( $body ) {
			foreach ( qs_body_options()[ $lv[1] ? $lv[1]->slug : '' ] ?? array() as $o ) {
				$p = explode( '|', $o );
				if ( $p[0] === $body ) {
					$add( qs_t( 'body' ), 'en' === qs_lang() ? $p[2] : $p[1] );
				}
			}
		}
		$add( qs_t( 'condition' ), qs_m( $id, 'cond' ) ? qs_opt( 'cond', qs_m( $id, 'cond' ) ) : '' );
		$add( qs_t( 'kms' ), '' !== qs_m( $id, 'km' ) ? qs_fmt( qs_m( $id, 'km' ) ) . ' ' . qs_t( 'km' ) : '' );
		foreach ( array( 'gear', 'fuel', 'cyl', 'specs', 'color' ) as $k ) {
			$add( qs_t( $k ), '' !== qs_m( $id, $k ) ? qs_opt( $k, qs_m( $id, $k ) ) : '' );
		}
		$add( qs_t( 'deal' ), qs_m( $id, 'deal' ) ? qs_opt( 'deal', qs_m( $id, 'deal' ) ) : '' );
		$add( qs_t( 'period' ), qs_m( $id, 'period' ) ? qs_opt( 'period', qs_m( $id, 'period' ) ) : '' );
	} elseif ( 'general' === $group ) {
		$add( qs_t( 'cat_detail' ), qs_term_name( $lv[2] ) );
		$add( qs_t( 'condition' ), qs_m( $id, 'cond' ) ? qs_opt( 'item_cond', qs_m( $id, 'cond' ) ) : '' );
	} elseif ( 'item' === $group ) {
		$add( qs_t( 'item_type' ), qs_term_name( $lv[2] ) );
		$add( qs_t( 'part_brand' ), qs_m( $id, 'brand' ) );
		$add( qs_t( 'compat_make' ), $make ? qs_term_name( $make ) : '' );
		$add( qs_t( 'compat_model' ), qs_model_label( $id, $model ) );
		$add( qs_t( 'compat_year' ), qs_m( $id, 'year' ) );
		$add( qs_t( 'condition' ), qs_m( $id, 'cond' ) ? qs_opt( 'item_cond', qs_m( $id, 'cond' ) ) : '' );
		$add( qs_t( 'part_type' ), qs_m( $id, 'part_type' ) ? qs_opt( 'part_type', qs_m( $id, 'part_type' ) ) : '' );
		$add( qs_t( 'sku' ), qs_m( $id, 'sku' ) );
	}
	$add( qs_t( 'location' ), $addr );
	$add( qs_t( 'ad_no' ), 'QS-' . ( 1000 + $id ) );
	return $rows;
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

/**
 * Main categories (alphabetical) with two levels below each, for the menu, drawer, home slider and search.
 * Under "Cars" the second level is the five car sections (vehicles, spare parts…) and the third their sub-sections.
 */
function qs_nav() {
	static $n = array();
	$lang = qs_lang();
	if ( isset( $n[ $lang ] ) ) {
		return $n[ $lang ];
	}
	$icons = array_map( fn( $x ) => $x[1], qs_mains() ) + array_map( fn( $x ) => $x[1], qs_sections() );
	$item  = function ( $t, $kids = true ) use ( &$item, $icons ) {
		return array(
			'slug'  => $t->slug,
			'label' => qs_term_name( $t ),
			'url'   => get_term_link( $t ),
			'icon'  => $icons[ $t->slug ] ?? ( get_term_meta( $t->term_id, 'icon', true ) ?: 'grid' ),
			'term'  => $t,
			'subs'  => $kids ? array_map( fn( $c ) => $item( $c, false ), qs_children( $t ) ) : array(),
		);
	};
	$out = array();
	foreach ( qs_children( 0 ) as $t ) {
		$e         = $item( $t );
		$e['menu'] = (int) get_term_meta( $t->term_id, 'menu', true );
		foreach ( $e['subs'] as $i => $s ) {
			$e['subs'][ $i ]['subs'] = array_map( fn( $c ) => $item( $c, false ), qs_children( $s['term'] ) );
		}
		$out[] = $e;
	}
	return $n[ $lang ] = $out;
}
/** The (up to 6) main categories shown in the menu bar, in their menu order; the rest go under "More". */
function qs_menu_split() {
	$in = array_values( array_filter( qs_nav(), fn( $e ) => $e['menu'] > 0 ) );
	usort( $in, fn( $a, $b ) => $a['menu'] <=> $b['menu'] );
	$in   = array_slice( $in, 0, 6 );
	$keep = wp_list_pluck( $in, 'slug' );
	return array( $in, array_values( array_filter( qs_nav(), fn( $e ) => ! in_array( $e['slug'], $keep, true ) ) ) );
}
/**
 * One menu item with its mega panel (like dubizzle): the list of sub-categories on one side,
 * and the children of the hovered one on the other, with "View all".
 */
function qs_mega( $n, $on = false ) {
	$more = 'more' === $n['slug'];
	$subs = '';
	$pans = '';
	foreach ( $n['subs'] as $i => $s ) {
		$subs .= '<a href="' . esc_url( $s['url'] ) . '" class="mm-sub' . ( $i ? '' : ' on' ) . '" data-i="' . $i . '">' . ( $more ? qs_ic( $s['icon'], 'sm' ) . ' ' : '' ) . '<span>' . esc_html( $s['label'] ) . '</span>' . qs_ic( 'chev', 'flip sm' ) . '</a>';
		$kids  = '';
		foreach ( $s['subs'] as $k ) {
			$kids .= '<a href="' . esc_url( $k['url'] ) . '">' . esc_html( $k['label'] ) . '</a>';
		}
		$pans .= '<div class="mm-pane' . ( $i ? '' : ' on' ) . '" data-i="' . $i . '"><div class="mm-ph"><b>' . esc_html( $s['label'] ) . '</b><a href="' . esc_url( $s['url'] ) . '" class="mm-all">' . esc_html( qs_t( 'view_all' ) ) . ' ' . qs_ic( 'arrow', 'flip sm' ) . '</a></div>' . ( $kids ? '<div class="mm-cols">' . $kids . '</div>' : '' ) . '</div>';
	}
	$post = $more ? '' : '<a class="mm-post" href="' . esc_url( qs_page_url( 'post', 'cars' === $n['slug'] ? array( 'main' => 'cars' ) : array( 'section' => $n['slug'] ) ) ) . '"><b>' . esc_html( qs_tf( 'post_in', array( 'c' => $n['label'] ) ) ) . '</b><span class="mm-post-ic">' . qs_ic( $n['icon'] ) . '</span></a>';
	return '<div class="mm' . ( $on ? ' on' : '' ) . ( $more ? ' mm-more' : '' ) . '"><a href="' . esc_url( $n['url'] ) . '" class="mm-top' . ( $on ? ' on' : '' ) . '">' . qs_ic( $n['icon'] ) . '<span>' . esc_html( $n['label'] ) . '</span>' . qs_ic( 'chev', 'dd-caret' ) . '</a>'
		. ( $n['subs'] ? '<div class="mm-panel"><div class="mm-subs">' . $subs . $post . '</div><div class="mm-kids">' . $pans . '</div></div>' : '' ) . '</div>';
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
