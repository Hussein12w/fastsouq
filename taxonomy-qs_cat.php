<?php
/** Section / sub-section / item-type page. */
defined( 'ABSPATH' ) || exit;
get_header();
$t     = get_queried_object();
$chain = array_reverse( array_merge( array( $t->term_id ), get_ancestors( $t->term_id, 'qs_cat' ) ) );
$terms = array_map( fn( $id ) => get_term( $id, 'qs_cat' ), $chain );
$sec   = $terms[0];
$sub   = $terms[1] ?? null;
$crumbs = array();
foreach ( $terms as $i => $x ) {
	$crumbs[] = array( qs_term_name( $x ), $i < count( $terms ) - 1 ? get_term_link( $x ) : '' );
}
$icon  = qs_sections()[ $sec->slug ][1] ?? 'grid';
$chips = $sub ? ( qs_children( $sub ) ? qs_term_chips( $sub, $terms[2] ?? null ) : qs_term_chips( $sec, $sub ) ) : qs_term_chips( $sec );
qs_browse( array( 'title' => qs_term_name( $t ), 'icon' => $icon, 'crumbs' => $crumbs, 'section' => $sec, 'sub' => $sub, 'chips' => $chips ) );
get_footer();
