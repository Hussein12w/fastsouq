<?php
/** Home: hero with sections, vehicle types, makes, featured ads, latest per section. */
defined( 'ABSPATH' ) || exit;
get_header();
$ids = function ( $args ) {
	return get_posts( array_merge( array( 'post_type' => 'listing', 'fields' => 'ids', 'no_found_rows' => true, 'posts_per_page' => 6, 'meta_query' => array( 'feat' => array( 'key' => '_qs_featured', 'type' => 'NUMERIC' ), 'bump' => array( 'key' => '_qs_bump', 'type' => 'NUMERIC' ) ), 'orderby' => array( 'feat' => 'DESC', 'bump' => 'DESC' ) ), $args ) );
};
$count = function ( $t ) {
	$q = new WP_Query( array( 'post_type' => 'listing', 'fields' => 'ids', 'posts_per_page' => 1, 'tax_query' => array( array( 'taxonomy' => 'qs_cat', 'terms' => $t->term_id ) ) ) );
	return (int) $q->found_posts;
};
$featured = get_posts( array( 'post_type' => 'listing', 'fields' => 'ids', 'posts_per_page' => 6, 'no_found_rows' => true, 'meta_query' => array( array( 'key' => '_qs_featured', 'value' => 1, 'type' => 'NUMERIC' ), 'bump' => array( 'key' => '_qs_bump', 'type' => 'NUMERIC' ) ), 'orderby' => array( 'bump' => 'DESC' ) ) );
$makes    = array_filter( qs_children( 0, 'qs_make' ), fn( $m ) => $m->count > 0 );
usort( $makes, fn( $a, $b ) => $b->count <=> $a->count );
$hero_img = get_theme_mod( 'qs_hero' );
?>
<section class="qs-hero">
	<div class="wrap qs-hero-in">
		<span class="free-chip"><?php echo qs_ic( 'bolt' ) . ' '; qs_e( 'post_sub' ); ?></span>
		<h1><?php qs_e( 'hero_t' ); ?></h1>
		<p class="lead"><?php qs_e( 'hero_p' ); ?></p>
		<div class="hero-secs">
			<?php foreach ( qs_nav() as $n ) : ?>
			<a class="hero-sec" href="<?php echo esc_url( $n['url'] ); ?>"><span class="hs-ic"><?php echo qs_ic( $n['icon'] ); ?></span><b><?php echo esc_html( $n['label'] ); ?></b><small><?php echo esc_html( qs_tf( 'ads_n', array( 'n' => $count( $n['term'] ) ) ) ); ?></small></a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php if ( $hero_img ) : ?><div class="qs-hero-bg" style="background-image:url('<?php echo esc_url( $hero_img ); ?>')"></div><?php endif; ?>
</section>

<?php if ( qs_term( 'vehicles' ) ) : ?>
<?php $veh = qs_nav()[0]; ?>
<section class="wrap sec"><?php echo qs_sec_head( $veh['label'], $veh['url'], $veh['icon'] ); // phpcs:ignore ?>
	<div class="sub-grid">
		<?php foreach ( qs_children( $veh['term'] ) as $c ) : ?>
		<a class="sub-tile" href="<?php echo esc_url( get_term_link( $c ) ); ?>"><span class="st-ic"><?php echo qs_ic( $veh['icon'] ); ?></span><b><?php echo esc_html( qs_term_name( $c ) ); ?></b></a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<?php if ( $makes ) : ?>
<section class="wrap sec"><?php echo qs_sec_head( qs_t( 'popular_makes' ) ); // phpcs:ignore ?>
	<div class="make-grid"><?php foreach ( array_slice( $makes, 0, 18 ) as $m ) { echo '<a class="make-tile" href="' . esc_url( get_term_link( $m ) ) . '"><b>' . esc_html( qs_term_name( $m ) ) . '</b><small>' . esc_html( qs_tf( 'ads_n', array( 'n' => $m->count ) ) ) . '</small></a>'; } ?></div>
</section>
<?php endif; ?>

<?php if ( $featured ) : ?>
<section class="wrap sec feat-sec"><?php echo qs_sec_head( qs_t( 'featured_ads' ), qs_ads_url( array( 'featured' => 1 ) ), 'star' ); // phpcs:ignore ?><div class="grid g3"><?php echo implode( '', array_map( 'qs_card', $featured ) ); // phpcs:ignore ?></div></section>
<?php endif; ?>

<?php
foreach ( qs_nav() as $i => $n ) :
	$list = $ids( array( 'tax_query' => array( array( 'taxonomy' => 'qs_cat', 'terms' => $n['term']->term_id ) ) ) );
	if ( ! $list ) {
		continue;
	}
	?>
<section class="wrap sec"><?php echo qs_sec_head( $n['label'], $n['url'], $n['icon'] ); // phpcs:ignore ?><div class="grid g3"><?php echo implode( '', array_map( 'qs_card', $list ) ); // phpcs:ignore ?></div></section>
	<?php if ( 0 === $i ) : ?>
<section class="wrap"><div class="promo qs-promo"><div class="promo-txt"><h2><?php qs_e( 'promo_t' ); ?></h2><p><?php qs_e( 'promo_p' ); ?></p><a class="btn btn-y2 btn-lg" href="<?php echo esc_url( qs_page_url( 'post' ) ); ?>"><?php echo qs_ic( 'plus' ) . ' '; qs_e( 'post_ad' ); ?></a></div><img src="<?php echo esc_url( QS_URI . '/assets/img/logo-icon.png' ); ?>" alt="" class="promo-logo"></div></section>
	<?php endif; ?>
<?php endforeach; ?>
<?php
get_footer();
