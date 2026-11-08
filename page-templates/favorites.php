<?php
/**
 * Template Name: FastSouq — Favorites
 */
defined( 'ABSPATH' ) || exit;
get_header();
$ids = qs_favs();
$ids = $ids ? get_posts( array( 'post_type' => 'listing', 'post__in' => $ids, 'fields' => 'ids', 'posts_per_page' => 60, 'orderby' => 'post__in' ) ) : array();
?>
<section class="page-head slim"><div class="wrap"><?php echo qs_crumbs( array( array( qs_t( 'favorites' ) ) ) ); // phpcs:ignore ?><h1><?php qs_e( 'favorites' ); ?></h1></div></section>
<div class="wrap sec"><?php echo $ids ? '<div class="grid g3">' . implode( '', array_map( 'qs_card', $ids ) ) . '</div>' : qs_empty( 'heart', qs_t( 'no_favs' ), '<a class="btn btn-y" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( qs_t( 'back_home' ) ) . '</a>' ); // phpcs:ignore ?></div>
<?php
get_footer();
