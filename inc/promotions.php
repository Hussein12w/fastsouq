<?php
/**
 * Paid promotions through WooCommerce (any gateway, e.g. Ziina).
 *
 * Posting stays free. Each promotion is a WooCommerce product with two extra fields
 * (Product data → General): "FastSouq promotion" (featured / bump / extend) and "Days".
 * You set the price and publish it like any product — you can add as many packages as you like
 * (e.g. Featured 7 days, Featured 30 days). After the order is paid, the promotion is applied
 * to the member's ad automatically.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Paid promotion is OFF in this version (planned for the next one).
 * To turn it on later: add_filter( 'qs_promos_enabled', '__return_true' ); (e.g. in Code Snippets).
 */
function qs_promos_enabled() {
	return apply_filters( 'qs_promos_enabled', false ) && class_exists( 'WooCommerce' ) && function_exists( 'wc_get_products' );
}
/** Published promotion products, cheapest first. */
function qs_promo_products() {
	if ( ! qs_promos_enabled() ) {
		return array();
	}
	$ids = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 30, 'meta_query' => array( array( 'key' => '_qs_promo', 'value' => array( 'featured', 'bump', 'extend' ), 'compare' => 'IN' ) ) ) );
	$out = array();
	foreach ( $ids as $pid ) {
		$p = wc_get_product( $pid );
		if ( $p && $p->is_purchasable() && '' !== $p->get_price() ) {
			$out[] = $p;
		}
	}
	usort( $out, fn( $a, $b ) => (float) $a->get_price() <=> (float) $b->get_price() );
	return $out;
}

/* ---------- Product fields ---------- */
if ( qs_promos_enabled() ) :
add_action(
	'woocommerce_product_options_general_product_data',
	function () {
		echo '<div class="options_group">';
		woocommerce_wp_select(
			array(
				'id'          => '_qs_promo',
				'label'       => 'FastSouq promotion',
				'options'     => array( '' => '— Not a promotion —', 'featured' => 'Featured ad (مميز)', 'bump' => 'Bump to top (رفع)', 'extend' => 'Extend ad duration (تمديد)' ),
				'desc_tip'    => true,
				'description' => 'Members buy this to promote one of their ads. Set the price above.',
			)
		);
		woocommerce_wp_text_input( array( 'id' => '_qs_promo_days', 'label' => 'Days (featured / extend)', 'type' => 'number', 'custom_attributes' => array( 'min' => 1 ) ) );
		woocommerce_wp_text_input( array( 'id' => '_qs_promo_name_en', 'label' => 'Name in English (optional)' ) );
		echo '</div>';
	}
);
add_action(
	'woocommerce_process_product_meta',
	function ( $pid ) {
		$type = sanitize_key( $_POST['_qs_promo'] ?? '' ); // phpcs:ignore
		update_post_meta( $pid, '_qs_promo', in_array( $type, array( 'featured', 'bump', 'extend' ), true ) ? $type : '' );
		update_post_meta( $pid, '_qs_promo_days', absint( $_POST['_qs_promo_days'] ?? 0 ) ); // phpcs:ignore
		update_post_meta( $pid, '_qs_promo_name_en', sanitize_text_field( wp_unslash( $_POST['_qs_promo_name_en'] ?? '' ) ) ); // phpcs:ignore
		if ( $type ) {
			update_post_meta( $pid, '_sold_individually', 'yes' );
			update_post_meta( $pid, '_virtual', 'yes' );
		}
	}
);

endif;

/** Create the three starter packages as drafts (no price yet) — the admin sets the price and publishes. */
function qs_create_promo_products() {
	if ( ! qs_promos_enabled() || get_option( 'qs_promos_created' ) ) {
		return;
	}
	$list = array(
		array( 'إعلان مميز — 7 أيام', 'Featured ad — 7 days', 'featured', 7 ),
		array( 'رفع الإعلان للأعلى', 'Bump to top', 'bump', 0 ),
		array( 'تمديد الإعلان — 30 يوم', 'Extend ad — 30 days', 'extend', 30 ),
	);
	foreach ( $list as $x ) {
		$p = new WC_Product_Simple();
		$p->set_name( $x[0] );
		$p->set_status( 'draft' );
		$p->set_virtual( true );
		$p->set_sold_individually( true );
		$p->set_catalog_visibility( 'hidden' );
		$p->set_tax_status( 'taxable' );
		$id = $p->save();
		update_post_meta( $id, '_qs_promo', $x[2] );
		update_post_meta( $id, '_qs_promo_days', $x[3] );
		update_post_meta( $id, '_qs_promo_name_en', $x[1] );
	}
	update_option( 'qs_promos_created', 1 );
}
add_action( 'admin_init', 'qs_create_promo_products' );

/* Promotion products never show in the shop. */
add_action(
	'woocommerce_product_query',
	function ( $q ) {
		$mq   = (array) $q->get( 'meta_query' );
		$mq[] = array( 'relation' => 'OR', array( 'key' => '_qs_promo', 'compare' => 'NOT EXISTS' ), array( 'key' => '_qs_promo', 'value' => '' ) );
		$q->set( 'meta_query', $mq );
	}
);

function qs_promo_name( $p ) {
	$en = get_post_meta( $p->get_id(), '_qs_promo_name_en', true );
	return 'en' === qs_lang() && $en ? $en : $p->get_name();
}
function qs_promo_desc( $p ) {
	$type = get_post_meta( $p->get_id(), '_qs_promo', true );
	$days = (int) get_post_meta( $p->get_id(), '_qs_promo_days', true );
	return qs_tf( 'promo_' . $type . '_d', array( 'n' => $days ) );
}

/* ---------- Buying: ?qs_buy=<product>&qs_ad=<ad> adds to cart for that ad and goes to checkout ---------- */
add_action(
	'template_redirect',
	function () {
		if ( empty( $_GET['qs_buy'] ) || ! qs_promos_enabled() ) {
			return;
		}
		$pid = absint( $_GET['qs_buy'] );
		$ad  = absint( $_GET['qs_ad'] ?? 0 );
		if ( ! is_user_logged_in() || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_qsn'] ?? '' ) ), 'qs_buy' . $ad ) ) {
			wp_safe_redirect( qs_page_url( 'login' ) );
			exit;
		}
		if ( 'listing' !== get_post_type( $ad ) || (int) get_post_field( 'post_author', $ad ) !== get_current_user_id() || ! in_array( get_post_status( $ad ), array( 'publish', 'expired' ), true ) || ! get_post_meta( $pid, '_qs_promo', true ) ) {
			wp_safe_redirect( qs_page_url( 'account', array( 'tab' => 'ads', 'qs_err' => 'promo_only_published' ) ) );
			exit;
		}
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $pid, 1, 0, array(), array( 'qs_ad' => $ad ) );
		wp_safe_redirect( wc_get_checkout_url() );
		exit;
	}
);
add_filter(
	'woocommerce_get_item_data',
	function ( $data, $item ) {
		if ( ! empty( $item['qs_ad'] ) ) {
			$data[] = array( 'name' => qs_t( 'ad_label' ), 'value' => esc_html( get_the_title( $item['qs_ad'] ) . ' (QS-' . ( 1000 + (int) $item['qs_ad'] ) . ')' ) );
		}
		return $data;
	},
	10,
	2
);
add_action(
	'woocommerce_checkout_create_order_line_item',
	function ( $line, $key, $values ) {
		if ( ! empty( $values['qs_ad'] ) ) {
			$line->add_meta_data( '_qs_ad', (int) $values['qs_ad'], true );
			$line->add_meta_data( qs_t( 'ad_label' ), get_the_title( $values['qs_ad'] ) . ' (QS-' . ( 1000 + (int) $values['qs_ad'] ) . ')', true );
		}
	},
	10,
	3
);
/* A promotion cart must have a selected ad. */
add_filter(
	'woocommerce_add_to_cart_validation',
	function ( $ok, $pid ) {
		if ( get_post_meta( $pid, '_qs_promo', true ) && empty( $_GET['qs_ad'] ) && ! doing_action( 'template_redirect' ) ) { // phpcs:ignore
			wc_add_notice( qs_t( 'promo_only_published' ), 'error' );
			return false;
		}
		return $ok;
	},
	10,
	2
);

/* ---------- Apply after payment ---------- */
function qs_apply_promotions( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	foreach ( $order->get_items() as $item ) {
		$ad = (int) $item->get_meta( '_qs_ad' );
		if ( ! $ad || $item->get_meta( '_qs_applied' ) || 'listing' !== get_post_type( $ad ) ) {
			continue;
		}
		$pid  = $item->get_product_id();
		$type = get_post_meta( $pid, '_qs_promo', true );
		$days = max( 1, (int) get_post_meta( $pid, '_qs_promo_days', true ) );
		$now  = time();
		if ( 'featured' === $type ) {
			$until = max( $now, (int) get_post_meta( $ad, '_qs_featured_until', true ) ) + $days * DAY_IN_SECONDS;
			update_post_meta( $ad, '_qs_featured', 1 );
			update_post_meta( $ad, '_qs_featured_until', $until );
			update_post_meta( $ad, '_qs_bump', $now );
		} elseif ( 'bump' === $type ) {
			update_post_meta( $ad, '_qs_bump', $now );
		} elseif ( 'extend' === $type ) {
			update_post_meta( $ad, '_qs_expires', max( $now, (int) get_post_meta( $ad, '_qs_expires', true ) ) + $days * DAY_IN_SECONDS );
			if ( 'expired' === get_post_status( $ad ) ) {
				wp_update_post( array( 'ID' => $ad, 'post_status' => 'publish' ) );
			}
		}
		$item->update_meta_data( '_qs_applied', $now );
		$item->save();
		$order->add_order_note( sprintf( 'FastSouq: %s applied to ad #%d', $type, $ad ) );
	}
}
add_action( 'woocommerce_order_status_processing', 'qs_apply_promotions' );
add_action( 'woocommerce_order_status_completed', 'qs_apply_promotions' );
/* Promotion-only orders are completed automatically once paid. */
add_filter(
	'woocommerce_payment_complete_order_status',
	function ( $status, $order_id, $order ) {
		if ( $order ) {
			foreach ( $order->get_items() as $item ) {
				if ( ! $item->get_meta( '_qs_ad' ) ) {
					return $status;
				}
			}
			return 'completed';
		}
		return $status;
	},
	10,
	3
);
/* After paying, send the member back to their ads. */
add_action(
	'woocommerce_thankyou',
	function ( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		foreach ( $order->get_items() as $item ) {
			if ( $item->get_meta( '_qs_ad' ) ) {
				echo '<p class="qs-thanks"><a class="button" href="' . esc_url( qs_page_url( 'account', array( 'tab' => 'ads' ) ) ) . '">' . esc_html( qs_t( 'my_ads' ) ) . '</a></p>';
				return;
			}
		}
	},
	20
);

/** "Promote your ad" panel in My account. */
function qs_promote_view( $ad ) {
	if ( ! $ad || 'listing' !== get_post_type( $ad ) || (int) get_post_field( 'post_author', $ad ) !== get_current_user_id() ) {
		return qs_empty( 'star', qs_t( 'promo_only_published' ) );
	}
	$items = qs_promo_products();
	$h     = '<div class="box promo-box"><h2>' . qs_ic( 'star' ) . ' ' . esc_html( qs_t( 'promo_page_t' ) ) . '</h2><p class="muted">' . esc_html( qs_t( 'promo_page_p' ) ) . '</p>
		<a class="m-item" href="' . esc_url( get_permalink( $ad ) ) . '"><div class="m-art">' . qs_media( $ad, 'thumbnail' ) . '</div><div><b>' . esc_html( qs_title( $ad ) ) . '</b>' . qs_price( $ad ) . '</div></a>';
	if ( ! in_array( get_post_status( $ad ), array( 'publish', 'expired' ), true ) ) {
		return $h . '<div class="warn-bar">' . qs_ic( 'info' ) . ' ' . esc_html( qs_t( 'promo_only_published' ) ) . '</div></div>';
	}
	if ( ! $items ) {
		return $h . '<div class="warn-bar">' . qs_ic( 'info' ) . ' ' . esc_html( qs_t( 'no_promos' ) ) . '</div></div>';
	}
	$icons = array( 'featured' => 'star', 'bump' => 'arrow', 'extend' => 'clock' );
	$h    .= '<div class="pk-grid">';
	foreach ( $items as $p ) {
		$type = get_post_meta( $p->get_id(), '_qs_promo', true );
		$url  = add_query_arg( array( 'qs_buy' => $p->get_id(), 'qs_ad' => $ad, '_qsn' => wp_create_nonce( 'qs_buy' . $ad ) ), home_url( '/' ) );
		$h   .= '<div class="pk pk-' . esc_attr( $type ) . '">' . qs_ic( $icons[ $type ] ?? 'star' ) . '<b>' . esc_html( qs_promo_name( $p ) ) . '</b><small>' . esc_html( qs_promo_desc( $p ) ) . '</small><span class="pk-price">' . wp_kses_post( $p->get_price_html() ) . '</span><a class="btn btn-y full" href="' . esc_url( $url ) . '">' . esc_html( qs_t( 'buy_now' ) ) . '</a></div>';
	}
	return $h . '</div><ul class="why"><li>' . qs_ic( 'check' ) . ' ' . esc_html( qs_t( 'why1' ) ) . '</li><li>' . qs_ic( 'check' ) . ' ' . esc_html( qs_t( 'why2' ) ) . '</li><li>' . qs_ic( 'check' ) . ' ' . esc_html( qs_t( 'why3' ) ) . '</li></ul></div>';
}
