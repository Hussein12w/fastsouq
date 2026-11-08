<?php
defined( 'ABSPATH' ) || exit;
get_header();
echo '<div class="wrap empty big">' . qs_ic( 'search' ) . '<h1>404</h1><p>' . esc_html( qs_t( 'not_found' ) ) . '</p><a class="btn btn-y" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( qs_t( 'back_home' ) ) . '</a></div>'; // phpcs:ignore
get_footer();
