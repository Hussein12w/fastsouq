<?php
/**
 * Arabic names for vehicle makes and models (stored in English). Codes such as X5, Q7 or CX-5 stay as they are.
 * A name set in the dashboard ("Name (Arabic)") always wins over this list.
 */
defined( 'ABSPATH' ) || exit;

function qs_makes_ar() {
	static $d = null;
	if ( null !== $d ) {
		return $d;
	}
	$d = array(
		/* Makes */
		'Toyota' => 'تويوتا', 'Nissan' => 'نيسان', 'Lexus' => 'لكزس', 'Honda' => 'هوندا', 'Mazda' => 'مازدا', 'Mitsubishi' => 'ميتسوبيشي', 'Suzuki' => 'سوزوكي', 'Infiniti' => 'إنفينيتي', 'Subaru' => 'سوبارو', 'Isuzu' => 'إيسوزو', 'Acura' => 'أكيورا', 'Daihatsu' => 'دايهاتسو',
		'Mercedes-Benz' => 'مرسيدس بنز', 'BMW' => 'بي إم دبليو', 'Audi' => 'أودي', 'Volkswagen' => 'فولكس فاجن', 'Porsche' => 'بورش', 'Opel' => 'أوبل', 'Smart' => 'سمارت', 'Maybach' => 'مايباخ',
		'Ford' => 'فورد', 'Chevrolet' => 'شيفروليه', 'GMC' => 'جي إم سي', 'Cadillac' => 'كاديلاك', 'Jeep' => 'جيب', 'Dodge' => 'دودج', 'Chrysler' => 'كرايسلر', 'Lincoln' => 'لينكولن', 'Tesla' => 'تسلا', 'Rivian' => 'ريفيان', 'Hummer' => 'همر', 'RAM' => 'رام',
		'Hyundai' => 'هيونداي', 'Kia' => 'كيا', 'Genesis' => 'جينيسيس', 'KG Mobility (SsangYong)' => 'كي جي موبيليتي (سانج يونج)',
		'Land Rover' => 'لاند روفر', 'Range Rover' => 'رينج روفر', 'Jaguar' => 'جاكوار', 'Bentley' => 'بنتلي', 'Rolls-Royce' => 'رولز رويس', 'Aston Martin' => 'أستون مارتن', 'McLaren' => 'ماكلارين', 'Mini' => 'ميني', 'Lotus' => 'لوتس',
		'Ferrari' => 'فيراري', 'Lamborghini' => 'لامبورغيني', 'Maserati' => 'مازيراتي', 'Alfa Romeo' => 'ألفا روميو', 'Fiat' => 'فيات', 'Lancia' => 'لانشيا',
		'Renault' => 'رينو', 'Peugeot' => 'بيجو', 'Citroën' => 'ستروين', 'DS Automobiles' => 'دي إس', 'Bugatti' => 'بوغاتي',
		'BYD' => 'بي واي دي', 'MG' => 'إم جي', 'Geely' => 'جيلي', 'Changan' => 'شانجان', 'Haval' => 'هافال', 'Great Wall' => 'جريت وول', 'GAC' => 'جي إيه سي', 'Jetour' => 'جيتور', 'Chery' => 'شيري', 'Exeed' => 'إكسيد', 'Hongqi' => 'هونشي', 'JAC' => 'جاك', 'Dongfeng' => 'دونغ فنغ', 'BAIC' => 'بايك', 'Tank' => 'تانك', 'Zeekr' => 'زيكر', 'Lynk & Co' => 'لينك آند كو', 'NIO' => 'نيو', 'XPeng' => 'إكس بنغ', 'Li Auto' => 'لي أوتو',
		'Volvo' => 'فولفو', 'Polestar' => 'بولستار', 'Saab' => 'ساب', 'Škoda' => 'سكودا', 'SEAT' => 'سيات', 'Cupra' => 'كوبرا', 'Dacia' => 'داسيا', 'Lada' => 'لادا',
		/* Models */
		'Land Cruiser' => 'لاند كروزر', 'Prado' => 'برادو', 'Camry' => 'كامري', 'Corolla' => 'كورولا', 'Hilux' => 'هايلكس', 'Yaris' => 'يارس', 'RAV4' => 'راف 4', 'Fortuner' => 'فورتشنر', 'FJ Cruiser' => 'إف جي كروزر', 'Sequoia' => 'سيكويا', 'Tundra' => 'تندرا', 'Supra' => 'سوبرا', 'Avalon' => 'أفالون', 'Highlander' => 'هايلاندر',
		'Patrol' => 'باترول', 'Sunny' => 'صني', 'Altima' => 'ألتيما', 'X-Trail' => 'إكس تريل', 'Pathfinder' => 'باثفايندر', 'Navara' => 'نافارا', 'Kicks' => 'كيكس', 'Maxima' => 'ماكسيما', 'GT-R' => 'جي تي آر', 'Juke' => 'جوك',
		'Accord' => 'أكورد', 'Civic' => 'سيفيك', 'CR-V' => 'سي آر في', 'Pilot' => 'بايلوت', 'City' => 'سيتي', 'HR-V' => 'إتش آر في', 'Odyssey' => 'أوديسي',
		'Mazda 3' => 'مازدا 3', 'Mazda 6' => 'مازدا 6', 'Pajero' => 'باجيرو', 'Outlander' => 'أوتلاندر', 'Lancer' => 'لانسر', 'Attrage' => 'أتراج',
		'Jimny' => 'جيمني', 'Swift' => 'سويفت', 'Vitara' => 'فيتارا', 'Dzire' => 'ديزاير', 'Ertiga' => 'إرتيجا', 'Ciaz' => 'سياز', 'Baleno' => 'بالينو',
		'Forester' => 'فورستر', 'Outback' => 'آوتباك', 'Impreza' => 'إمبريزا', 'D-Max' => 'دي ماكس', 'Terios' => 'تيريوس', 'Sirion' => 'سيريون',
		'C-Class' => 'سي كلاس', 'E-Class' => 'إي كلاس', 'S-Class' => 'إس كلاس', 'G-Class' => 'جي كلاس', 'A-Class' => 'إيه كلاس', 'V-Class' => 'في كلاس',
		'3 Series' => 'الفئة الثالثة', '5 Series' => 'الفئة الخامسة', '7 Series' => 'الفئة السابعة',
		'Golf' => 'جولف', 'Passat' => 'باسات', 'Tiguan' => 'تيجوان', 'Touareg' => 'طوارق', 'Jetta' => 'جيتا', 'Teramont' => 'تيرامونت',
		'Cayenne' => 'كايين', 'Macan' => 'ماكان', 'Panamera' => 'باناميرا', 'Taycan' => 'تايكان', 'Astra' => 'أسترا', 'Corsa' => 'كورسا', 'Insignia' => 'إنسيجنيا', 'ForTwo' => 'فورتو',
		'Explorer' => 'إكسبلورر', 'Expedition' => 'إكسبيديشن', 'Mustang' => 'موستنج', 'Ranger' => 'رينجر', 'Edge' => 'إيدج', 'Bronco' => 'برونكو', 'Territory' => 'تيريتوري',
		'Tahoe' => 'تاهو', 'Silverado' => 'سلفرادو', 'Camaro' => 'كامارو', 'Malibu' => 'ماليبو', 'Suburban' => 'سوبربان', 'Traverse' => 'ترافيرس', 'Captiva' => 'كابتيفا', 'Corvette' => 'كورفيت',
		'Yukon' => 'يوكن', 'Sierra' => 'سييرا', 'Acadia' => 'أكاديا', 'Terrain' => 'تيرين', 'Escalade' => 'إسكاليد',
		'Wrangler' => 'رانجلر', 'Grand Cherokee' => 'جراند شيروكي', 'Cherokee' => 'شيروكي', 'Compass' => 'كومباس', 'Gladiator' => 'جلادياتور',
		'Charger' => 'تشارجر', 'Challenger' => 'تشالنجر', 'Durango' => 'دورانجو', 'Pacifica' => 'باسيفيكا', 'Navigator' => 'نافيجيتور', 'Aviator' => 'أفياتور', 'Nautilus' => 'نوتيلوس',
		'Model 3' => 'موديل 3', 'Model Y' => 'موديل Y', 'Model S' => 'موديل S', 'Model X' => 'موديل X', 'Cybertruck' => 'سايبرتراك',
		'Elantra' => 'إلنترا', 'Accent' => 'أكسنت', 'Sonata' => 'سوناتا', 'Tucson' => 'توسان', 'Santa Fe' => 'سنتافي', 'Creta' => 'كريتا', 'Palisade' => 'باليسيد', 'Kona' => 'كونا',
		'Sportage' => 'سبورتاج', 'Sorento' => 'سورينتو', 'Cerato' => 'سيراتو', 'Pegas' => 'بيجاس', 'Seltos' => 'سيلتوس', 'Telluride' => 'تيلورايد', 'Carnival' => 'كرنفال',
		'Rexton' => 'ريكستون', 'Tivoli' => 'تيفولي', 'Korando' => 'كوراندو',
		'Defender' => 'ديفندر', 'Discovery' => 'ديسكفري', 'Discovery Sport' => 'ديسكفري سبورت', 'Sport' => 'سبورت', 'Velar' => 'فيلار', 'Evoque' => 'إيفوك',
		'Bentayga' => 'بنتايجا', 'Continental GT' => 'كونتيننتال جي تي', 'Flying Spur' => 'فلاينج سبير', 'Cullinan' => 'كولينان', 'Ghost' => 'جوست', 'Phantom' => 'فانتوم', 'Wraith' => 'رايث',
		'Vantage' => 'فانتاج', 'Artura' => 'أرتورا', 'Cooper' => 'كوبر', 'Countryman' => 'كانتريمان', 'Emira' => 'إميرا', 'Eletre' => 'إليتر',
		'Roma' => 'روما', 'Purosangue' => 'بوروسانجوي', 'Urus' => 'أوروس', 'Huracan' => 'هوراكان', 'Revuelto' => 'ريفويلتو', 'Ghibli' => 'جيبلي', 'Levante' => 'ليفانتي', 'Grecale' => 'جريكالي',
		'Giulia' => 'جوليا', 'Stelvio' => 'ستلفيو', 'Tipo' => 'تيبو', 'Ypsilon' => 'إبسيلون',
		'Duster' => 'داستر', 'Koleos' => 'كوليوس', 'Megane' => 'ميجان', 'Symbol' => 'سيمبول', 'Partner' => 'بارتنر', 'Berlingo' => 'بيرلينجو', 'Chiron' => 'شيرون',
		'Atto 3' => 'أتو 3', 'Seal' => 'سيل', 'Han' => 'هان', 'Song Plus' => 'سونج بلس', 'Coolray' => 'كولراي', 'Monjaro' => 'مونجارو', 'Emgrand' => 'إمجراند', 'Tugella' => 'توجيلا',
		'Alsvin' => 'ألسفين', 'Jolion' => 'جوليون', 'Wingle' => 'وينجل', 'Poer' => 'باور', 'Emkoo' => 'إمكو', 'Dashing' => 'داشينج',
		'Tiggo 4' => 'تيجو 4', 'Tiggo 7' => 'تيجو 7', 'Tiggo 8' => 'تيجو 8', 'Arrizo' => 'أريزو', 'Aeolus' => 'أيولوس', 'Shine' => 'شاين',
		'Octavia' => 'أوكتافيا', 'Kodiaq' => 'كودياك', 'Superb' => 'سوبيرب', 'Leon' => 'ليون', 'Ibiza' => 'إيبيزا', 'Formentor' => 'فورمينتور', 'Born' => 'بورن', 'Sandero' => 'سانديرو', 'Niva' => 'نيفا', 'Vesta' => 'فيستا',
	);
	return $d;
}

/** Arabic name for an English make / model name, or '' when it is a code that stays the same. */
function qs_make_ar( $en ) {
	$d = qs_makes_ar();
	return $d[ $en ] ?? '';
}

/** English stored name from what the visitor typed (Arabic or English); '' if unknown. */
function qs_make_en( $typed ) {
	$typed = trim( (string) $typed );
	$f     = array_search( $typed, qs_makes_ar(), true );
	return false === $f ? '' : $f;
}
