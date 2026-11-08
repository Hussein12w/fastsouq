<?php
/**
 * Template Name: FastSouq — Login / Register
 */
defined( 'ABSPATH' ) || exit;
if ( is_user_logged_in() ) {
	wp_safe_redirect( qs_page_url( 'account' ) );
	exit;
}
get_header();
$reg      = 'register' === qs_f( 'tab' );
$redirect = qs_f( 'redirect' ) ?: qs_page_url( 'account', array( 'tab' => 'ads' ) );
$old      = fn( $k ) => esc_attr( qs_f( 'old_' . $k ) );
?>
<div class="wrap auth"><div class="auth-card">
	<div class="auth-side"><div class="as-art"><img src="<?php echo esc_url( QS_URI . '/assets/img/logo-icon.png' ); ?>" alt=""></div><h2><?php qs_e( 'auth_side_t' ); ?></h2><ul><li><?php echo qs_ic( 'check' ) . ' '; qs_e( 'auth_side1' ); ?></li><li><?php echo qs_ic( 'check' ) . ' '; qs_e( 'auth_side2' ); ?></li></ul></div>
	<div class="auth-form"><h1><?php qs_e( 'welcome' ); ?></h1>
		<div class="tabs"><a href="<?php echo esc_url( remove_query_arg( array( 'tab', 'qs_err' ) ) ); ?>" class="<?php echo $reg ? '' : 'on'; ?>"><?php qs_e( 'login_tab' ); ?></a><a href="<?php echo esc_url( add_query_arg( 'tab', 'register', remove_query_arg( 'qs_err' ) ) ); ?>" class="<?php echo $reg ? 'on' : ''; ?>"><?php qs_e( 'reg_tab' ); ?></a></div>
		<?php echo qs_notice(); // phpcs:ignore ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="stack-form">
			<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>">
			<?php if ( $reg ) : ?>
			<input type="hidden" name="action" value="qs_register"><?php wp_nonce_field( 'qs_register', '_qsn' ); ?>
			<label class="fld"><span><?php qs_e( 'full_name' ); ?></span><input name="name" value="<?php echo $old( 'name' ); // phpcs:ignore ?>" required></label>
			<label class="fld"><span><?php qs_e( 'phone' ); ?></span><div class="phone-in"><span class="cc">🇦🇪 +971</span><input name="phone" inputmode="tel" class="ltr-in" value="<?php echo $old( 'phone' ); // phpcs:ignore ?>" placeholder="5X XXX XXXX" autocomplete="tel" required></div></label>
			<label class="fld"><span><?php qs_e( 'email' ); ?></span><input type="email" name="email" class="ltr-in" value="<?php echo $old( 'email' ); // phpcs:ignore ?>" autocomplete="email" required></label>
			<label class="fld"><span><?php qs_e( 'password' ); ?></span><input type="password" name="pass" minlength="8" autocomplete="new-password" required></label>
			<button class="btn btn-y btn-lg full" type="submit"><?php echo qs_ic( 'check' ) . ' '; qs_e( 'register_btn' ); ?></button>
			<?php else : ?>
			<input type="hidden" name="action" value="qs_login"><?php wp_nonce_field( 'qs_login', '_qsn' ); ?>
			<label class="fld"><span><?php qs_e( 'phone_or_email' ); ?></span><input name="login" class="ltr-in" value="<?php echo $old( 'login' ); // phpcs:ignore ?>" placeholder="05X XXX XXXX" autocomplete="username" required></label>
			<label class="fld"><span><?php qs_e( 'password' ); ?></span><input type="password" name="pass" autocomplete="current-password" required></label>
			<label class="check"><input type="checkbox" name="remember" value="1" checked> <?php qs_e( 'remember' ); ?></label>
			<button class="btn btn-y btn-lg full" type="submit"><?php echo qs_ic( 'user' ) . ' '; qs_e( 'login_btn' ); ?></button>
			<a class="link small center" href="<?php echo esc_url( wp_lostpassword_url( qs_page_url( 'login' ) ) ); ?>"><?php qs_e( 'forgot_pw' ); ?></a>
			<?php endif; ?>
		</form>
	</div>
</div></div>
<?php
get_footer();
