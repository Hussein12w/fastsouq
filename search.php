<?php
defined( 'ABSPATH' ) || exit;
get_header();
$q   = get_search_query();
$sec = qs_f( 'sec' ) ? qs_term( qs_f( 'sec' ) ) : null;
qs_browse( array( 'title' => qs_t( 'search_for' ) . ' “' . $q . '”', 'icon' => 'search', 'crumbs' => array( array( qs_t( 'search' ) ) ), 'section' => $sec, 'keep' => array_filter( array( 's' => $q, 'post_type' => 'listing', 'sec' => $sec ? $sec->slug : '' ) ), 'chips' => $sec ? '' : qs_section_chips() ) );
get_footer();
