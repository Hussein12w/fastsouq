<?php
/** Footer, floating WhatsApp, mobile tab bar, side menu, modal and toasts. */
defined( 'ABSPATH' ) || exit;
$uid   = get_current_user_id();
$wa    = preg_replace( '/\D/', '', (string) get_theme_mod( 'qs_whatsapp', '' ) );
$phone = get_theme_mod( 'qs_phone', '' );
$email = get_theme_mod( 'qs_email', '' );
$nav   = qs_nav();
?>
</main>
<footer class="ftr"><div class="wrap ftr-cols">
	<div class="ftr-brand"><?php echo qs_logo(); // phpcs:ignore ?><p><?php qs_e( 'tagline' ); ?></p>
		<div class="soc"><?php foreach ( array( 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'x' => 'X', 'youtube' => 'YouTube', 'facebook' => 'Facebook' ) as $k => $n ) { if ( $u = get_theme_mod( 'qs_social_' . $k ) ) { echo '<a href="' . esc_url( $u ) . '" class="soc-i" target="_blank" rel="noopener">' . esc_html( $n ) . '</a>'; } } ?></div></div>
	<?php foreach ( array_slice( qs_menu_split()[0], 0, 3 ) as $n ) : ?>
	<div><h4><?php echo esc_html( $n['label'] ); ?></h4><?php foreach ( array_slice( $n['subs'], 0, 8 ) as $s ) { echo '<a href="' . esc_url( $s['url'] ) . '">' . esc_html( $s['label'] ) . '</a>'; } ?></div>
	<?php endforeach; ?>
	<div><h4><?php qs_e( 'site_name' ); ?></h4>
		<?php if ( has_nav_menu( 'footer' ) ) { wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1 ) ); } ?>
		<a href="<?php echo esc_url( qs_page_url( 'post' ) ); ?>"><?php qs_e( 'post_ad' ); ?></a>
		<a href="<?php echo esc_url( qs_page_url( $uid ? 'account' : 'login' ) ); ?>"><?php qs_e( $uid ? 'my_account' : 'login' ); ?></a>
		<?php if ( $phone ) : ?><p class="ltr"><?php echo qs_ic( 'phone', 'sm' ); ?> <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p><?php endif; ?>
		<?php if ( $email ) : ?><p class="ltr"><?php echo qs_ic( 'mail', 'sm' ); ?> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><?php endif; ?></div>
</div>
<div class="ftr-bot"><div class="wrap ftr-bot-in"><span>© <?php echo esc_html( gmdate( 'Y' ) . ' ' . qs_t( 'site_name' ) ); ?></span></div></div></footer>

<?php if ( $wa ) : ?><a class="wa-float" href="<?php echo esc_url( 'https://wa.me/' . $wa ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( qs_t( 'whatsapp_us' ) ); ?>" title="<?php echo esc_attr( qs_t( 'whatsapp_us' ) ); ?>"><?php echo qs_ic( 'wa' ); ?></a><?php endif; ?>

<nav class="tabbar">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="<?php echo is_front_page() ? 'on' : ''; ?>"><?php echo qs_ic( 'home' ); ?><span><?php qs_e( 'home' ); ?></span></a>
	<a href="#" data-act="drawer"><?php echo qs_ic( 'menu' ); ?><span><?php qs_e( 'menu' ); ?></span></a>
	<a href="<?php echo esc_url( qs_page_url( 'post' ) ); ?>" class="tab-post"><b><?php echo qs_ic( 'plus' ); ?></b><span><?php qs_e( 'post_ad' ); ?></span></a>
	<a href="<?php echo esc_url( qs_page_url( 'favorites' ) ); ?>"><?php echo qs_ic( 'heart' ); ?><span><?php qs_e( 'favorites' ); ?></span></a>
	<a href="<?php echo esc_url( qs_page_url( $uid ? 'account' : 'login' ) ); ?>"><?php echo qs_ic( 'user' ); ?><span><?php qs_e( $uid ? 'my_account' : 'login' ); ?></span></a>
</nav>

<div class="drawer" id="drawer" hidden><div class="dr-bg" data-act="drawer"></div><aside class="dr-panel">
	<div class="dr-h"><?php echo qs_logo(); // phpcs:ignore ?><button class="icon-btn" data-act="drawer" aria-label="close"><?php echo qs_ic( 'close' ); ?></button></div>
	<div class="dr-langs"><?php echo qs_lang_btns(); // phpcs:ignore ?></div>
	<div class="dr-acts"><a href="<?php echo esc_url( qs_page_url( 'post' ) ); ?>" class="btn btn-y"><?php echo qs_ic( 'plus' ) . ' '; qs_e( 'post_ad' ); ?></a></div>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo qs_ic( 'home' ); ?><span><?php qs_e( 'home' ); ?></span></a>
	<h5><?php qs_e( 'all_sections' ); ?></h5>
	<?php foreach ( $nav as $n ) : ?>
	<details class="dr-sec"><summary><?php echo qs_ic( $n['icon'] ); ?><span><?php echo esc_html( $n['label'] ); ?></span></summary>
		<a class="dr-all" href="<?php echo esc_url( $n['url'] ); ?>"><span><?php echo esc_html( qs_t( 'view_all' ) . ' — ' . $n['label'] ); ?></span></a>
		<?php foreach ( $n['subs'] as $s ) : ?>
		<?php if ( $s['subs'] ) : ?>
		<details class="dr-sub"><summary><span><?php echo esc_html( $s['label'] ); ?></span></summary>
			<a class="dr-all" href="<?php echo esc_url( $s['url'] ); ?>"><span><?php echo esc_html( qs_t( 'view_all' ) ); ?></span></a>
			<?php foreach ( $s['subs'] as $k ) { echo '<a href="' . esc_url( $k['url'] ) . '"><span>' . esc_html( $k['label'] ) . '</span></a>'; } ?>
		</details>
		<?php else : ?>
		<a href="<?php echo esc_url( $s['url'] ); ?>"><span><?php echo esc_html( $s['label'] ); ?></span></a>
		<?php endif; ?>
		<?php endforeach; ?>
	</details>
	<?php endforeach; ?>
	<h5><?php qs_e( 'my_account' ); ?></h5>
	<a href="<?php echo esc_url( qs_page_url( $uid ? 'account' : 'login' ) ); ?>"><?php echo qs_ic( 'user' ); ?><span><?php qs_e( $uid ? 'my_account' : 'login' ); ?></span></a>
	<a href="<?php echo esc_url( qs_page_url( 'favorites' ) ); ?>"><?php echo qs_ic( 'heart' ); ?><span><?php qs_e( 'favorites' ); ?></span></a>
</aside></div>
<div class="modal" id="modal" hidden></div>
<div class="toasts" id="toasts" aria-live="polite"></div>
</div>
<?php wp_footer(); ?>
</body>
</html>
