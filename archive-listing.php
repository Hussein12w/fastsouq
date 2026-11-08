<?php
/** All ads. */
defined( 'ABSPATH' ) || exit;
get_header();
$title = qs_f( 'featured' ) ? qs_t( 'featured_ads' ) : qs_t( 'latest' );
qs_browse( array( 'title' => $title, 'icon' => 'grid', 'crumbs' => array( array( $title ) ), 'chips' => qs_section_chips() ) );
get_footer();
