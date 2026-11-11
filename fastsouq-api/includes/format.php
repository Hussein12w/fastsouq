<?php
/** JSON shapes returned to the app (ads, users, categories, banners). */
defined( 'ABSPATH' ) || exit;

function fsapi_img( $att, $size ) {
	$u = $att ? wp_get_attachment_image_url( $att, $size ) : '';
	return $u ?: '';
}
function fsapi_price( $id ) {
	$p = qs_m( $id, 'price' );
	$n = is_numeric( $p ) && (float) $p > 0 ? (float) $p : null;
	return array(
		'price'      => $n,
		'price_text' => $n ? qs_fmt( $n ) . ' ' . qs_t( 'cur' ) . ( qs_m( $id, 'period' ) ? ' / ' . qs_opt( 'period', qs_m( $id, 'period' ) ) : '' ) : qs_t( 'price_ask' ),
		'currency'   => 'AED',
	);
}
function fsapi_term( $t ) {
	return $t && ! is_wp_error( $t ) ? array( 'slug' => $t->slug, 'name' => qs_term_name( $t ) ) : null;
}
function fsapi_photos( $id ) {
	return array_values( array_unique( array_filter( array_map( 'intval', array_merge( array( get_post_thumbnail_id( $id ) ), (array) qs_m( $id, 'gallery' ) ) ) ) ) );
}
function fsapi_favs( $uid = 0 ) {
	$uid = $uid ?: get_current_user_id();
	return $uid ? array_values( array_filter( array_map( 'intval', (array) get_user_meta( $uid, 'fsapi_favs', true ) ) ) ) : array();
}

/** An ad as shown in lists (same content as the website card). */
function fsapi_ad_summary( $id ) {
	static $favs = null;
	$favs  ??= fsapi_favs();
	$group   = qs_group( $id );
	$lv      = qs_levels( $id );
	$phone   = qs_phone_of( $id );
	$photos  = fsapi_photos( $id );
	return array(
		'id'           => (int) $id,
		'ref'          => 'QS-' . ( 1000 + (int) $id ),
		'title'        => qs_title( $id ),
		'group'        => $group,
		'status'       => get_post_status( $id ),
		'service'      => 'service' === $group ? qs_term_name( $lv[1] ) : null,
		'facts'        => array_map( fn( $f ) => array( 'icon' => $f[0], 'text' => $f[1] ), qs_card_facts( $id ) ),
		'image'        => $photos ? fsapi_img( $photos[0], 'medium_large' ) : '',
		'images_count' => count( $photos ),
		'category'     => fsapi_term( $lv[2] ?: ( $lv[1] ?: $lv[0] ) ),
		'city'         => qs_city_label( $id ),
		'featured'     => (bool) qs_m( $id, 'featured' ),
		'wanted'       => 'wanted' === qs_m( $id, 'deal' ),
		'date'         => get_post_time( 'c', true, $id ),
		'ago'          => qs_ago( $id ),
		'phone'        => $phone ? '+' . $phone : '',
		'whatsapp'     => $phone ? qs_wa_link( $phone, qs_title( $id ) . ' — ' . get_permalink( $id ) ) : '',
		'url'          => get_permalink( $id ),
		'is_favorite'  => in_array( (int) $id, $favs, true ),
	) + fsapi_price( $id );
}

/** Full ad (ad page). $own adds the values to pre-fill the edit form. */
function fsapi_ad_detail( $id, $own = false ) {
	$lv     = qs_levels( $id );
	$author = get_userdata( get_post_field( 'post_author', $id ) );
	list( $make, $model ) = qs_make_model( $id );
	$path   = array();
	if ( $lv[0] && isset( qs_sections()[ $lv[0]->slug ] ) && ( $cars = qs_term( 'cars' ) ) ) {
		$path[] = fsapi_term( $cars );
	}
	foreach ( array_filter( $lv ) as $t ) {
		$path[] = fsapi_term( $t );
	}
	$cities = array();
	foreach ( (array) get_the_terms( $id, 'qs_city' ) as $t ) {
		if ( $t && ! is_wp_error( $t ) ) {
			$cities[ $t->parent ? 'city' : 'emirate' ] = fsapi_term( $t );
		}
	}
	$lat = qs_m( $id, 'lat' );
	$lng = qs_m( $id, 'lng' );
	$out = fsapi_ad_summary( $id ) + array(
		'description' => (string) get_post_field( 'post_content', $id ),
		'images'      => array_map( fn( $a ) => array( 'id' => $a, 'large' => fsapi_img( $a, 'large' ), 'full' => fsapi_img( $a, 'full' ), 'thumb' => fsapi_img( $a, 'medium' ) ), fsapi_photos( $id ) ),
		'specs'       => array_map( fn( $r ) => array( 'label' => $r[0], 'value' => (string) $r[1] ), qs_ad_specs( $id ) ),
		'path'        => $path,
		'make'        => fsapi_term( $make ),
		'model'       => $model ? array( 'slug' => $model->slug, 'name' => qs_model_label( $id, $model ) ) : null,
		'location'    => array(
			'emirate' => $cities['emirate'] ?? null,
			'city'    => $cities['city'] ?? null,
			'area'    => (string) qs_m( $id, 'area' ),
			'lat'     => is_numeric( $lat ) ? (float) $lat : null,
			'lng'     => is_numeric( $lng ) ? (float) $lng : null,
		),
		'seller'      => $author ? array(
			'id'           => (int) $author->ID,
			'name'         => $author->display_name,
			'member_since' => gmdate( 'Y', strtotime( $author->user_registered ) ),
			'ads_count'    => (int) count_user_posts( $author->ID, 'listing', true ),
		) : null,
		'views'       => (int) qs_m( $id, 'views' ),
		'is_owner'    => $own,
	);
	if ( $own ) {
		$out['edit_values'] = qs_post_values( $id );
	}
	return $out;
}

function fsapi_user( $uid ) {
	$u = get_userdata( $uid );
	if ( ! $u ) {
		return null;
	}
	$counts = array();
	foreach ( array( 'publish', 'pending', 'expired', 'draft' ) as $st ) {
		$counts[ $st ] = count( get_posts( array( 'post_type' => 'listing', 'author' => $uid, 'post_status' => $st, 'fields' => 'ids', 'posts_per_page' => -1, 'no_found_rows' => true ) ) );
	}
	$ph = get_user_meta( $uid, 'qs_phone', true );
	return array(
		'id'           => (int) $uid,
		'name'         => $u->display_name,
		'phone'        => $ph ? '+' . $ph : '',
		'email'        => $u->user_email,
		'member_since' => gmdate( 'Y-m-d', strtotime( $u->user_registered ) ),
		'ads'          => $counts,
		'favorites'    => count( fsapi_favs( $uid ) ),
	);
}

/** Category node with its children (depth levels below). */
function fsapi_cat_node( $t, $depth ) {
	$icons = array_map( fn( $x ) => $x[1], qs_mains() ) + array_map( fn( $x ) => $x[1], qs_sections() );
	$n     = array(
		'slug'  => $t->slug,
		'name'  => qs_term_name( $t ),
		'icon'  => $icons[ $t->slug ] ?? ( get_term_meta( $t->term_id, 'icon', true ) ?: null ),
		'count' => (int) $t->count,
	);
	if ( ! $t->parent ) {
		$n['menu'] = (int) get_term_meta( $t->term_id, 'menu', true );
	}
	if ( isset( qs_sections()[ $t->slug ] ) || ( ! $t->parent && 'cars' !== $t->slug ) ) {
		$n['group'] = qs_group_of( $t->slug ); // ads are posted in this node (its fields: config → form.groups)
	}
	if ( $depth > 0 ) {
		$n['children'] = array_map( fn( $c ) => fsapi_cat_node( $c, $depth - 1 ), qs_children( $t ) );
	}
	return $n;
}

/** Ads of the current list, without authors the signed-in user blocked. */
function fsapi_blocked() {
	return is_user_logged_in() ? array_values( array_filter( array_map( 'intval', (array) get_user_meta( get_current_user_id(), 'fsapi_blocks', true ) ) ) ) : array();
}

function fsapi_banner( $place, $term = null ) {
	$ids = qs_sponsored_ids( $place, $term );
	return $ids ? qs_sponsored_data( $ids[ array_rand( $ids ) ] ) : null;
}
