<?php
/** All ads of one make (or one model). */
defined( 'ABSPATH' ) || exit;
get_header();
$t    = get_queried_object();
$make = $t->parent ? get_term( $t->parent, 'qs_make' ) : $t;
$crumbs = $t->parent ? array( array( qs_term_name( $make ), get_term_link( $make ) ), array( qs_term_name( $t ) ) ) : array( array( qs_term_name( $t ) ) );
$chips  = '';
if ( ! $t->parent && ( $kids = qs_children( $t, 'qs_make' ) ) ) {
	$chips = '<div class="chips scroll sub-chips">' . implode( '', array_map( fn( $m ) => '<a class="chip" href="' . esc_url( get_term_link( $m ) ) . '">' . esc_html( qs_term_name( $m ) ) . '</a>', array_filter( $kids, fn( $m ) => $m->count > 0 ) ) ) . '</div>';
}
qs_browse( array( 'title' => $t->parent ? qs_term_name( $make ) . ' ' . qs_term_name( $t ) : qs_term_name( $t ), 'icon' => 'car', 'crumbs' => $crumbs, 'section' => qs_term( 'vehicles' ), 'chips' => $chips ) );
get_footer();
