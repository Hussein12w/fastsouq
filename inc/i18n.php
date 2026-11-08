<?php
/**
 * Two-language interface: Arabic (default) and English. Pick with ?lang=ar|en (remembered in a cookie).
 * To change any wording, edit the texts in qs_dict() below.
 */
defined( 'ABSPATH' ) || exit;

const QS_LANGS = array( 'ar', 'en' );

function qs_lang() {
	static $lang = null;
	if ( null === $lang ) {
		$lang = 'ar';
		if ( isset( $_GET['lang'] ) && in_array( $_GET['lang'], QS_LANGS, true ) ) {
			$lang = $_GET['lang'];
		} elseif ( isset( $_COOKIE['qs_lang'] ) && in_array( $_COOKIE['qs_lang'], QS_LANGS, true ) ) {
			$lang = $_COOKIE['qs_lang'];
		}
	}
	return $lang;
}
add_action(
	'template_redirect',
	function () {
		if ( isset( $_GET['lang'] ) && in_array( $_GET['lang'], QS_LANGS, true ) ) {
			setcookie( 'qs_lang', $_GET['lang'], time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), false );
			wp_safe_redirect( remove_query_arg( 'lang' ) );
			exit;
		}
	},
	1
);
function qs_is_rtl() {
	return 'ar' === qs_lang();
}

function qs_t( $key ) {
	static $d = null;
	if ( null === $d ) {
		$d = qs_dict();
	}
	$i = 'en' === qs_lang() ? 1 : 0;
	return isset( $d[ $key ] ) ? $d[ $key ][ $i ] : $key;
}
function qs_e( $key ) {
	echo esc_html( qs_t( $key ) );
}
function qs_tf( $key, $vars ) {
	$s = qs_t( $key );
	foreach ( $vars as $k => $v ) {
		$s = str_replace( '{' . $k . '}', $v, $s );
	}
	return $s;
}

/** Term name in the current language (Arabic = term name, English = term meta name_en). */
function qs_term_name( $term ) {
	if ( ! $term || is_wp_error( $term ) ) {
		return '';
	}
	if ( 'en' === qs_lang() ) {
		return ( $v = get_term_meta( $term->term_id, 'name_en', true ) ) ? $v : $term->name;
	}
	if ( $v = get_term_meta( $term->term_id, 'name_ar', true ) ) {
		return $v;
	}
	/* Makes & models are stored in English: show their Arabic names in Arabic. */
	if ( 'qs_make' === $term->taxonomy && function_exists( 'qs_make_ar' ) && ( $v = qs_make_ar( $term->name ) ) ) {
		return $v;
	}
	return $term->name;
}
function qs_title( $post = null ) {
	$post = get_post( $post );
	if ( 'en' === qs_lang() && ( $v = get_post_meta( $post->ID, '_qs_title_en', true ) ) ) {
		return $v;
	}
	return get_the_title( $post );
}
function qs_desc( $post = null ) {
	$post = get_post( $post );
	if ( 'en' === qs_lang() && ( $v = get_post_meta( $post->ID, '_qs_desc_en', true ) ) ) {
		return $v;
	}
	return $post->post_content;
}

/** [Arabic, English] */
function qs_dict() {
	return array(
		'site_name' => array( 'فاست سوق', 'FastSouq' ),
		'tagline' => array( 'سوق السيارات وقطع الغيار والإكسسوارات والخدمات في الإمارات', 'The UAE marketplace for cars, parts, accessories & services' ),
		'home'            => array( 'الرئيسية', 'Home' ),
		'login'           => array( 'تسجيل الدخول', 'Log in' ),
		'logout'          => array( 'تسجيل الخروج', 'Log out' ),
		'post_ad'         => array( 'أضف إعلانك', 'Post your ad' ),
		'search'          => array( 'بحث', 'Search' ),
		'search_ph' => array( 'ابحث عن سيارة، قطعة غيار، إكسسوار أو خدمة…', 'Search cars, parts, accessories or services…' ),
		'menu'            => array( 'القائمة', 'Menu' ),
		'all'             => array( 'الكل', 'All' ),
		'view_all'        => array( 'عرض الكل', 'View all' ),
		'back'            => array( 'رجوع', 'Back' ),
		'cars'            => array( 'سيارات للبيع', 'Cars for sale' ),
		'accessories'     => array( 'الإكسسوارات', 'Accessories' ),
		'services'        => array( 'خدمات السيارات', 'Car services' ),
		'cars_h'          => array( 'السيارات', 'Cars' ),
		'type_car'        => array( 'إعلان سيارة', 'Car ad' ),
		'type_acc'        => array( 'إعلان إكسسوار', 'Accessory ad' ),
		'type_service'    => array( 'إعلان خدمة', 'Service ad' ),
		'type_car_d'      => array( 'بيع سيارتك أو اطلب سيارة', 'Sell your car or ask for one' ),
		'type_acc_d'      => array( 'إكسسوارات داخلية وخارجية، تواير ورنقات وتزويد', 'Interior & exterior parts, wheels and upgrades' ),
		'type_service_d'  => array( 'كراج، كرين، مساعدة على الطريق أو عناية بالسيارات', 'Garage, towing, roadside help or car care' ),
		'hero_t' => array( 'بيع واشترِ بسرعة — سيارات، قطع غيار، إكسسوارات وخدمات', 'Sell and buy fast — cars, parts, accessories & services' ),
		'hero_p' => array( 'آلاف الإعلانات في كل الإمارات. النشر مجاني وسريع.', 'Thousands of ads across the UAE. Posting is free and fast.' ),
		'browse_cars'     => array( 'تصفّح السيارات', 'Browse cars' ),
		'latest_cars'     => array( 'أحدث السيارات', 'Latest cars' ),
		'latest_acc'      => array( 'أحدث الإكسسوارات', 'Latest accessories' ),
		'latest_services' => array( 'خدمات السيارات', 'Car services' ),
		'promo_t' => array( 'عندك سيارة أو قطعة أو خدمة؟', 'Got a car, a part or a service?' ),
		'promo_p' => array( 'انشر إعلانك مجاناً خلال دقائق، ووصّله لآلاف المشترين في كل الإمارات.', 'Post free in minutes and reach thousands of buyers across the UAE.' ),
		'ads_n'           => array( '{n} إعلان', '{n} ads' ),
		'results'         => array( 'نتيجة', 'results' ),
		'no_results'      => array( 'لا توجد إعلانات مطابقة', 'No matching ads' ),
		'nothing_here'    => array( 'لا يوجد محتوى', 'Nothing here' ),
		'not_found'       => array( 'الصفحة غير موجودة', 'Page not found' ),
		'back_home'       => array( 'العودة للرئيسية', 'Back to home' ),
		'search_for'      => array( 'نتائج البحث عن', 'Results for' ),
		/* filters */
		'filters'         => array( 'الفلتر', 'Filter' ),
		'show_filters'    => array( 'الفلتر', 'Filter' ),
		'reset'           => array( 'مسح', 'Reset' ),
		'apply'           => array( 'تطبيق', 'Apply' ),
		'ad_type'         => array( 'نوع الإعلان', 'Ad type' ),
		'section'         => array( 'القسم', 'Section' ),
		'condition'       => array( 'الحالة', 'Condition' ),
		'new'             => array( 'جديد', 'New' ),
		'used'            => array( 'مستعمل', 'Used' ),
		'model' => array( 'الموديل', 'Model' ),
		'model_from'      => array( 'من سنة', 'From' ),
		'model_to'        => array( 'إلى سنة', 'To' ),
		'gear'            => array( 'ناقل الحركة', 'Transmission' ),
		'auto'            => array( 'أوتوماتيك', 'Automatic' ),
		'manual'          => array( 'عادي', 'Manual' ),
		'price'           => array( 'السعر', 'Price' ),
		'price_min'       => array( 'من', 'Min' ),
		'price_max'       => array( 'إلى', 'Max' ),
		'any'             => array( 'الكل', 'Any' ),
		'sort'            => array( 'ترتيب', 'Sort' ),
		'newest'          => array( 'الأحدث', 'Newest' ),
		'price_low'       => array( 'الأقل سعراً', 'Lowest price' ),
		'price_high'      => array( 'الأعلى سعراً', 'Highest price' ),
		/* ad fields */
		'cur'             => array( 'درهم', 'AED' ),
		'price_ask'       => array( 'السعر عند التواصل', 'Price on request' ),
		'km'              => array( 'كم', 'km' ),
		'kms' => array( 'المسافة المقطوعة', 'Mileage' ),
		'car_type'        => array( 'نوع السيارة', 'Car type' ),
		'deal'            => array( 'نوع العرض', 'Offer type' ),
		'sale'            => array( 'للبيع', 'For sale' ),
		'wanted'          => array( 'مطلوب', 'Wanted' ),
		'agency_paint'    => array( 'صبغ الوكالة', 'Original paint' ),
		'dealer_warranty' => array( 'ضمان الوكيل', 'Dealer warranty' ),
		'warranty'        => array( 'يوجد ضمان', 'Warranty' ),
		'yes'             => array( 'نعم', 'Yes' ),
		'no'              => array( 'لا', 'No' ),
		'acc_section'     => array( 'قسم الإكسسوار', 'Accessory section' ),
		'service_section' => array( 'قسم الخدمة', 'Service section' ),
		'services_offered'=> array( 'الخدمات المقدّمة', 'Services offered' ),
		'address'         => array( 'العنوان', 'Address' ),
		'map_pick'        => array( 'حدد مكانك على الخريطة', 'Pick your location on the map' ),
		'map_hint'        => array( 'اضغط على الخريطة لوضع الدبوس، أو استخدم موقعك الحالي', 'Tap the map to drop the pin, or use your current location' ),
		'my_location'     => array( 'استخدم موقعي', 'Use my location' ),
		'open_maps'       => array( 'افتح في خرائط Google', 'Open in Google Maps' ),
		'phone'           => array( 'رقم الهاتف', 'Phone number' ),
		'name' => array( 'عنوان الإعلان', 'Ad title' ),
		'name_car'        => array( 'اسم السيارة / عنوان الإعلان', 'Car name / ad title' ),
		'name_acc'        => array( 'اسم المنتج', 'Product name' ),
		'name_service'    => array( 'اسم الكراج / مقدّم الخدمة', 'Garage / provider name' ),
		'about'           => array( 'نبذة', 'Description' ),
		'about_ph'        => array( 'اكتب تفاصيل واضحة تساعد المشتري…', 'Write clear details that help buyers…' ),
		'photos'          => array( 'صور العرض', 'Photos' ),
		'photos_hint'     => array( 'حتى 10 صور — الأولى هي الصورة الرئيسية', 'Up to 10 photos — the first is the main photo' ),
		'photos_keep'     => array( 'الصور الحالية محفوظة — ارفع صوراً جديدة لاستبدالها', 'Current photos are kept — upload new ones to replace them' ),
		'up_t'            => array( 'اضغط لاختيار الصور', 'Tap to choose photos' ),
		'title_en'        => array( 'العنوان بالإنجليزية (اختياري)', 'Title in English (optional)' ),
		'details'         => array( 'التفاصيل', 'Details' ),
		'description'     => array( 'الوصف', 'Description' ),
		'location'        => array( 'الموقع', 'Location' ),
		'ad_no'           => array( 'رقم الإعلان', 'Ad no.' ),
		'views'           => array( 'مشاهدة', 'views' ),
		'today'           => array( 'اليوم', 'Today' ),
		'ago_d'           => array( 'منذ {n} يوم', '{n} days ago' ),
		'ago_1'           => array( 'منذ يوم', '1 day ago' ),
		'featured'        => array( 'مميز', 'Featured' ),
		'call'            => array( 'اتصال', 'Call' ),
		'whatsapp'        => array( 'واتساب', 'WhatsApp' ),
		'show_phone'      => array( 'إظهار الرقم', 'Show number' ),
		'share'           => array( 'مشاركة', 'Share' ),
		'report'          => array( 'إبلاغ', 'Report' ),
		'add_fav'         => array( 'أضف للمفضلة', 'Add to favorites' ),
		'in_fav'          => array( 'في المفضلة', 'In favorites' ),
		'fav_added'       => array( 'أُضيف للمفضلة', 'Added to favorites' ),
		'fav_removed'     => array( 'أُزيل من المفضلة', 'Removed from favorites' ),
		'link_copied'     => array( 'تم نسخ الرابط', 'Link copied' ),
		'favorites'       => array( 'المفضلة', 'Favorites' ),
		'no_favs'         => array( 'لم تضف أي إعلان للمفضلة بعد', 'No favorites yet' ),
		'similar'         => array( 'إعلانات مشابهة', 'Similar ads' ),
		'advertiser'      => array( 'المعلن', 'Advertiser' ),
		'member_since'    => array( 'عضو منذ', 'Member since' ),
		'his_ads'         => array( 'إعلانات المعلن', 'Advertiser’s ads' ),
		'safety'          => array( 'نصائح الأمان', 'Safety tips' ),
		'safety1'         => array( 'عاين السيارة أو المنتج قبل الدفع', 'Inspect the car or item before paying' ),
		'safety2'         => array( 'لا تحوّل أي مبلغ مقدماً لشخص لا تعرفه', 'Never send money in advance to strangers' ),
		'safety3'         => array( 'قابل البائع في مكان عام', 'Meet the seller in a public place' ),
		'reasons'         => array( 'إعلان مكرر|معلومات غير صحيحة|احتيال محتمل|أخرى', 'Duplicate ad|Wrong information|Possible scam|Other' ),
		'report_ok'       => array( 'شكراً — تم استلام البلاغ وسيراجعه فريقنا', 'Thanks — your report was received' ),
		'whatsapp_us'     => array( 'تواصل معنا عبر واتساب', 'Chat with us on WhatsApp' ),
		/* account */
		'welcome' => array( 'أهلاً بك في فاست سوق', 'Welcome to FastSouq' ),
		'login_tab'       => array( 'تسجيل الدخول', 'Log in' ),
		'reg_tab'         => array( 'حساب جديد', 'Create account' ),
		'full_name'       => array( 'الاسم الكامل', 'Full name' ),
		'email'           => array( 'البريد الإلكتروني', 'Email' ),
		'password'        => array( 'كلمة المرور', 'Password' ),
		'phone_or_email'  => array( 'رقم الهاتف أو البريد الإلكتروني', 'Phone number or email' ),
		'remember'        => array( 'تذكّرني', 'Remember me' ),
		'forgot_pw'       => array( 'نسيت كلمة المرور؟', 'Forgot your password?' ),
		'login_btn'       => array( 'دخول', 'Log in' ),
		'register_btn'    => array( 'إنشاء الحساب', 'Create account' ),
		'auth_side_t' => array( 'بِع واشترِ بسرعة في كل الإمارات', 'Buy and sell fast across the UAE' ),
		'auth_side1' => array( 'انشر إعلانات السيارات والقطع والإكسسوارات والخدمات مجاناً', 'Post car, parts, accessory and service ads for free' ),
		'auth_side2' => array( 'احفظ المفضلة وتابع إعلاناتك', 'Save favorites and manage your ads' ),
		'login_err'       => array( 'بيانات الدخول غير صحيحة', 'Incorrect login details' ),
		'reg_err_phone'   => array( 'أدخل رقم هاتف إماراتي صحيح', 'Enter a valid UAE phone number' ),
		'reg_err_exists'  => array( 'هذا الرقم أو البريد مسجّل مسبقاً — سجّل الدخول', 'This phone or email is already registered — please log in' ),
		'reg_err_email'   => array( 'أدخل بريداً إلكترونياً صحيحاً', 'Enter a valid email' ),
		'reg_err_pw'      => array( 'كلمة المرور 8 أحرف على الأقل', 'Password must be at least 8 characters' ),
		'reg_err_name'    => array( 'أدخل الاسم', 'Enter your name' ),
		'my_account'      => array( 'حسابي', 'My account' ),
		'my_profile'      => array( 'ملفي', 'My profile' ),
		'my_ads'          => array( 'إعلاناتي', 'My ads' ),
		'hello'           => array( 'أهلاً', 'Hello' ),
		'save'            => array( 'حفظ', 'Save' ),
		'saved_ok'        => array( 'تم حفظ البيانات', 'Details saved' ),
		'edit'            => array( 'تعديل', 'Edit' ),
		'delete'          => array( 'حذف', 'Delete' ),
		'confirm_delete'  => array( 'هل تريد حذف هذا الإعلان؟', 'Delete this ad?' ),
		'deleted_ok'      => array( 'تم حذف الإعلان', 'Ad deleted' ),
		'no_my_ads'       => array( 'لم تنشر أي إعلان بعد', 'You have not posted any ads yet' ),
		'status_publish'  => array( 'منشور', 'Published' ),
		'status_pending'  => array( 'قيد المراجعة', 'Under review' ),
		'status_draft'    => array( 'مسودة', 'Draft' ),
		'status_expired'  => array( 'منتهي', 'Expired' ),
		'expires_in'      => array( 'ينتهي خلال {n} يوم', 'Expires in {n} days' ),
		'see_profile'     => array( 'صفحتي العامة', 'My public page' ),
		/* posting */
		'post_title'      => array( 'أضف إعلانك', 'Post your ad' ),
		'post_sub' => array( 'النشر مجاني — يراجع فريقنا الإعلان ثم يُنشر', 'Posting is free — our team reviews the ad, then it goes live' ),
		'choose_type'     => array( 'اختر نوع الإعلان', 'Choose the ad type' ),
		'confirm'         => array( 'التأكيد والإرسال', 'Confirm & submit' ),
		'declare'         => array( 'أقرّ بأن الإعلان حقيقي والمعلومات صحيحة', 'I confirm the ad is real and the information is correct' ),
		'review_note' => array( 'يراجع فريق فاست سوق إعلانك قبل النشر للتأكد من أنه حقيقي.', 'The FastSouq team reviews your ad before publishing.' ),
		'publish'         => array( 'إرسال الإعلان', 'Submit ad' ),
		'login_first_t'   => array( 'سجّل دخولك لنشر إعلان', 'Log in to post an ad' ),
		'login_first_p'   => array( 'نشر الإعلانات مجاني ويتطلب حساباً برقم هاتفك.', 'Posting is free and needs an account with your phone number.' ),
		'post_ok_t'       => array( 'تم إرسال إعلانك للمراجعة!', 'Your ad was sent for review!' ),
		'post_ok_p'       => array( 'سيُنشر بعد مراجعة فريقنا. تابع حالته من «إعلاناتي».', 'It goes live after our team reviews it. Track it in “My ads”.' ),
		'edit_ok_p'       => array( 'تم حفظ التعديلات وأُعيد الإعلان للمراجعة.', 'Changes saved — the ad went back for review.' ),
		'post_another'    => array( 'أضف إعلاناً آخر', 'Post another ad' ),
		'err_required'    => array( 'أكمل الحقول المطلوبة', 'Please complete the required fields' ),
		'err_agree'       => array( 'يجب الإقرار بأن الإعلان حقيقي', 'You must confirm the ad is real' ),
		'err_limit'       => array( 'وصلت للحد الأقصى من الإعلانات اليوم — حاول غداً', 'You reached today’s ad limit — please try tomorrow' ),
		'required'        => array( 'مطلوب', 'Required' ),
		'pick'            => array( 'اختر', 'Choose' ),
		'year' => array( 'سنة الصنع', 'Year' ),
		'year_from' => array( 'من سنة', 'From year' ),
		'year_to' => array( 'إلى سنة', 'To year' ),
		'make' => array( 'الشركة المصنعة', 'Make' ),
		'body' => array( 'النوع', 'Type' ),
		'fuel' => array( 'نوع الوقود', 'Fuel' ),
		'cyl' => array( 'عدد الأسطوانات', 'Cylinders' ),
		'specs' => array( 'المواصفات', 'Specs' ),
		'color' => array( 'اللون', 'Colour' ),
		'km_from' => array( 'كم من', 'Km from' ),
		'km_to' => array( 'كم إلى', 'Km to' ),
		'part_brand' => array( 'الشركة المصنعة للقطعة', 'Part brand' ),
		'compat_make' => array( 'ماركة السيارة المتوافقة', 'Compatible make' ),
		'compat_model' => array( 'موديل السيارة المتوافقة', 'Compatible model' ),
		'compat_year' => array( 'سنة السيارة المتوافقة', 'Compatible year' ),
		'part_type' => array( 'نوع القطعة', 'Part type' ),
		'sku' => array( 'رقم القطعة', 'Part number' ),
		'item_type' => array( 'الصنف', 'Item' ),
		'subsection' => array( 'القسم الفرعي', 'Sub-section' ),
		'emirate' => array( 'الإمارة', 'Emirate' ),
		'city' => array( 'المدينة', 'City' ),
		'area' => array( 'المنطقة / العنوان', 'Area / address' ),
		'period' => array( 'مدة الإيجار', 'Rental period' ),
		'choose_section' => array( 'اختر القسم', 'Choose a section' ),
		'choose_sub' => array( 'اختر القسم الفرعي', 'Choose a sub-section' ),
		'popular_makes' => array( 'تصفّح حسب الماركة', 'Browse by make' ),
		'featured_ads' => array( 'إعلانات مميزة', 'Featured ads' ),
		'latest' => array( 'أحدث الإعلانات', 'Latest ads' ),
		'all_sections' => array( 'كل الأقسام', 'All sections' ),
		'browse_sections' => array( 'تصفّح الأقسام', 'Browse sections' ),
		'all_makes' => array( 'كل الماركات', 'All makes' ),
		'title_ph' => array( 'مثال: تويوتا لاندكروزر 2021 GXR', 'e.g. Toyota Land Cruiser 2021 GXR' ),
		'd_vehicles' => array( 'سيارات، دراجات، شاحنات، قوارب، لوحات…', 'Cars, bikes, trucks, boats, plates…' ),
		'd_spare_parts' => array( 'محرك، جير، فرامل، هيكل، كهرباء…', 'Engine, gearbox, brakes, body, electrics…' ),
		'd_accessories' => array( 'داخلية، خارجية، صوتيات، إضاءة، رحلات…', 'Interior, exterior, audio, lighting, 4x4…' ),
		'd_tools' => array( 'أجهزة فحص وبرمجة ومعدات ورش', 'Diagnostics, programming & workshop tools' ),
		'd_services' => array( 'صيانة، سمكرة، غسيل، سحب، تعديل…', 'Repair, bodywork, wash, towing, tuning…' ),
		'promote' => array( 'ميّز إعلانك', 'Promote' ),
		'promo_page_t' => array( 'روّج إعلانك', 'Promote your ad' ),
		'promo_page_p' => array( 'اختر طريقة الترويج وادفع بأمان. يُفعَّل الترويج تلقائياً بعد الدفع.', 'Choose how to promote and pay securely. It activates automatically after payment.' ),
		'promo_featured' => array( 'إعلان مميز', 'Featured ad' ),
		'promo_featured_d' => array( 'يظهر في أول النتائج وفي الرئيسية بشارة «مميز» لمدة {n} يوم', 'Shows first in results and on the home page with a “Featured” badge for {n} days' ),
		'promo_bump' => array( 'رفع الإعلان', 'Bump to top' ),
		'promo_bump_d' => array( 'يعود إعلانك لأعلى القائمة كأنه نُشر الآن', 'Moves your ad back to the top as if just posted' ),
		'promo_extend' => array( 'تمديد مدة الإعلان', 'Extend the ad' ),
		'promo_extend_d' => array( 'يضيف {n} يوم لمدة ظهور الإعلان', 'Adds {n} days to how long the ad stays live' ),
		'buy_now' => array( 'ادفع الآن', 'Pay now' ),
		'featured_until' => array( 'مميز حتى {d}', 'Featured until {d}' ),
		'no_promos' => array( 'خيارات الترويج غير متاحة حالياً', 'Promotion options are not available yet' ),
		'promo_only_published' => array( 'يمكن ترويج الإعلانات المنشورة فقط', 'Only published ads can be promoted' ),
		'promo_ok' => array( 'تم الدفع — سيُفعَّل الترويج لإعلانك تلقائياً', 'Payment received — your promotion is being activated' ),
		'ad_label' => array( 'الإعلان', 'Ad' ),
		'why_promote' => array( 'لماذا تميّز إعلانك؟', 'Why promote?' ),
		'why1' => array( 'يظهر قبل الإعلانات العادية', 'Shows above regular ads' ),
		'why2' => array( 'مشاهدات وتواصل أكثر', 'More views and contacts' ),
		'why3' => array( 'بيع أسرع', 'Sell faster' ),
		'item_name' => array( 'اسم القطعة / المنتج', 'Part / product name' ),
		'service_name' => array( 'اسم مقدّم الخدمة', 'Provider name' ),
		'vehicle_name' => array( 'عنوان الإعلان', 'Ad title' ),
	);
}
