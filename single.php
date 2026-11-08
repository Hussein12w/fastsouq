<?php
/** Regular pages (About, Terms, Privacy…) and single blog posts. */
defined( 'ABSPATH' ) || exit;
get_header();
the_post();
?>
<section class="page-head slim"><div class="wrap"><?php echo qs_crumbs( array( array( get_the_title() ) ) ); // phpcs:ignore ?><h1><?php the_title(); ?></h1></div></section>
<div class="wrap sec"><article class="box qs-content"><?php the_content(); ?></article></div>
<?php
get_footer();
