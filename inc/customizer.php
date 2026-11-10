<?php
/** Appearance → Customize → FastSouq. */
defined( 'ABSPATH' ) || exit;
add_action(
	'customize_register',
	function ( $c ) {
		$c->add_section( 'qs', array( 'title' => 'FastSouq', 'priority' => 30 ) );
		$c->add_setting( 'qs_logo', array( 'sanitize_callback' => 'esc_url_raw' ) );
		$c->add_control( new WP_Customize_Image_Control( $c, 'qs_logo', array( 'label' => 'الشعار (مربّع)', 'section' => 'qs' ) ) );
		foreach ( array( 1, 2 ) as $i ) {
			$c->add_setting( 'qs_slide' . $i, array( 'sanitize_callback' => 'esc_url_raw' ) );
			$c->add_control( new WP_Customize_Image_Control( $c, 'qs_slide' . $i, array( 'label' => 'صورة السلايدر ' . $i . ' في الواجهة (مقترح 1920×640)', 'section' => 'qs' ) ) );
			$c->add_setting( 'qs_slide' . $i . '_link', array( 'sanitize_callback' => 'esc_url_raw' ) );
			$c->add_control( 'qs_slide' . $i . '_link', array( 'label' => 'رابط عند الضغط على الصورة ' . $i . ' (اختياري)', 'section' => 'qs', 'type' => 'url' ) );
		}
		$c->add_setting( 'qs_ad_placeholder', array( 'default' => 1, 'sanitize_callback' => 'absint' ) );
		$c->add_control( 'qs_ad_placeholder', array( 'label' => 'إظهار مربع «أعلن هنا» في المساحات الجانبية الفارغة', 'section' => 'qs', 'type' => 'checkbox' ) );
		$txt = function ( $id, $label, $default = '', $type = 'text', $san = 'sanitize_text_field' ) use ( $c ) {
			$c->add_setting( $id, array( 'default' => $default, 'sanitize_callback' => $san ) );
			$c->add_control( $id, array( 'label' => $label, 'section' => 'qs', 'type' => $type ) );
		};
		$txt( 'qs_whatsapp', 'رقم واتساب الموقع (زر واتساب العائم) — مثال 971501234567' );
		$txt( 'qs_phone', 'هاتف الموقع' );
		$txt( 'qs_email', 'بريد الموقع', '', 'email', 'sanitize_email' );
		foreach ( array( 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'x' => 'X', 'youtube' => 'YouTube', 'facebook' => 'Facebook' ) as $k => $n ) {
			$txt( 'qs_social_' . $k, $n, '', 'url', 'esc_url_raw' );
		}
		$txt( 'qs_listing_days', 'مدة الإعلان بالأيام (0 = بدون انتهاء)', 60, 'number', 'absint' );
		$txt( 'qs_daily_limit', 'الحد الأقصى للإعلانات الجديدة يومياً لكل عضو', 10, 'number', 'absint' );
	}
);
