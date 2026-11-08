<?php
/** All ads in one emirate / city. */
defined( 'ABSPATH' ) || exit;
get_header();
$t = get_queried_object();
qs_browse( array( 'title' => qs_term_name( $t ), 'icon' => 'pin', 'crumbs' => array( array( qs_term_name( $t ) ) ), 'chips' => qs_section_chips() ) );
get_footer();
