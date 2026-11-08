<?php
/** Fallback template (blog posts, other archives). */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="page-head slim"><div class="wrap"><?php echo qs_crumbs( array( array( wp_strip_all_tags( get_the_archive_title() ?: get_bloginfo( 'name' ) ) ) ) ); // phpcs:ignore ?><h1><?php echo esc_html( is_home() ? get_the_title( get_option( 'page_for_posts' ) ) : wp_strip_all_tags( get_the_archive_title() ) ); ?></h1></div></section>
<div class="wrap sec"><div class="qs-posts">
<?php
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		echo '<article class="box"><h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2><p class="muted">' . esc_html( get_the_date() ) . '</p><div class="desc">' . wp_kses_post( get_the_excerpt() ) . '</div></article>';
	}
	echo qs_pagination(); // phpcs:ignore
} else {
	echo qs_empty( 'search', qs_t( 'nothing_here' ) ); // phpcs:ignore
}
?>
</div></div>
<?php
get_footer();
