<?php defined( 'SYSPATH' ) or die( "No direct script access." );
# popular places
$popular_icons = array('TMP'=>'Temple','RES'=>'Restaurant','AIR'=>'Airport','SHP'=>'Shopping mall','RAI'=>'Railway');
DEFINE( "POPULAR_ICONS", $popular_icons);

$path    = explode( '.', $_SERVER['SCRIPT_NAME'] );
$url     = explode( '/', $path[0] );
$cnt     = count( $url ) - 1;
$SEGMENT = "";
for ( $i = 0; $i < $cnt; $i++ ) {
    $SEGMENT .= $url[$i] . '/';
}
$urlSegments     = parse_url( $_SERVER["SERVER_NAME"] );
$urlHostSegments = explode( '.', $urlSegments['path'] );
$uploads         = "uploads";
$expiry          = 1;
/*
 * This code will work in live
 
if ( count( $urlHostSegments ) > 2 ) {
    $uploads = str_replace( "-", "_", $urlHostSegments[0] );
    if ( ( $uploads == "www" ) || ( $uploads == "live" ) || ( $uploads == "192" ) ) { //$uploads == "192"-->Local
        $uploads = "uploads";
        $expiry  = 0;
    }   
    $livechat = '1';
} else {
    $expiry  = 0;
    $uploads = "uploads";
}*/
# for local purpose
# for local purpose
$livechat = '0';
    
DEFINE("SUB_DOMAIN_TEMP",$uploads);
DEFINE( 'UPLOADS', $uploads );
DEFINE( 'PUBLIC_UPLOADS_FOLDER', "public/" . UPLOADS );
if ( $_SERVER['SERVER_PORT'] == "443" ) {
    DEFINE( 'PROTOCOL', 'https' );
} else {
    DEFINE( 'PROTOCOL', 'http' );
}
DEFINE( 'URL_BASE', url::base( PROTOCOL, TRUE ) );

$session                = Session::instance();
$company_curency_symbol = $company_curency_code = "";
# Site details
$companyId              = ( $session->get( 'company_id' ) > 0 ) ? $session->get( 'company_id' ) : 0;
define( "COMPANY_CID", $companyId );
$site_info_projection_array=[];
//$result         = $commonmodel->common_site_info($site_info_projection_array);
$result = Commonfunction::get_siteinfo();//Get siteinfo from XML file instead of query

DEFINE( "THEMEID", $result[0]["theme_id"] );
$this->siteinfo = $result;
View::bind_global( 'siteinfo', $this->siteinfo );
##Dynamic Language Process Starts
$db_language = explode(',', $result[0]["site_default_language"]);
$default_language =  $result[0]["default_language"];
DEFINE('DEFAULT_LANGUAGE',$default_language);

$staticLanguArr = array("en"=>"english","tr"=>"turkish","ar"=>"arabic","de"=>"german","ru"=>"russian","es"=>"spanish");
if(count($db_language) > 0){
    foreach($db_language as $langval){
        if(isset($staticLanguArr[$langval])){
            $DynamicLanguageArr[$langval] = $staticLanguArr[$langval];
        }
    }
}else{
    $DynamicLanguageArr = array("en"=>"english");
}
/* Code start added by ashok on 22-08-2017 Node Configuration*/
DEFINE( 'URL_NODE', $_SERVER['REQUEST_SCHEME'].'://'. $_SERVER['SERVER_NAME'].':3010/');//commented for development

DEFINE( 'URL_NODE_MOBILE', $_SERVER['REQUEST_SCHEME'].'://'. $_SERVER['SERVER_NAME'].':3029/');//commented for development

$subdomain=explode('.',$_SERVER['SERVER_NAME']);
$id_node=$subdomain[0];

//Logo newly added
DEFINE( 'LOGO_SMALL_IMAGE', isset($result[0]['logo_small']) ? $result[0]['logo_small'] : '');
//DEFINE( 'URL_NODE', 'http://192.168.1.125:3010/');//for development
DEFINE( 'SUB_NODE', $id_node);
DEFINE( 'NODE_ENVIROMENT',1);//changed from 1 to 0 for development  
/*  Code ended added by ashok on 22-08-2017 */

# driver threshold setting for driver recharge options
DEFINE( "DRIVER_GRACE_TIME_MTS", isset($result[0]["driver_grace_time_mts"])?$result[0]["driver_grace_time_mts"]:'');
DEFINE( "DRIVER_DEFAULT_AMOUNT", $result[0]["driver_default_amount"] );
DEFINE( "DRIVER_THRESHOLD_AMOUNT", $result[0]["driver_threshold_amount"] );
DEFINE( "DRIVER_THRESHOLD_SETTING", $result[0]["driver_threshold_setting"] );
$result[0]["cron_time"] = isset( $result[0]["cron_time"] ) ? $result[0]["cron_time"] : '';
DEFINE( "CRON_TIME", $result[0]["cron_time"] );
DEFINE("MULTI_MODEL_DISPATCH",isset($result[0]['multi_model_dispatch'])?$result[0]['multi_model_dispatch']:0);
DEFINE( "STATIC_LANGUAGE_ARRAY", $staticLanguArr );
DEFINE( "DYNAMIC_LANGUAGE_ARRAY", $DynamicLanguageArr );
//~ DEFINE( "TRIAL_ENTER_PACK", array(0,3));
DEFINE( "SELECTED_LANGUAGE", $result[0]["selected_language"] );
##Dynamic Language Process End
DEFINE( "SITENAME", $result[0]["app_name"] );
DEFINE( "SITE_EMAILID", $result[0]["email_id"] );
DEFINE( "SITE_APP_DESCRIPTION", $result[0]["app_description"] );
DEFINE( "SITE_NOTIFICATION_SETTING", $result[0]["notification_settings"] );

//Change idle settings time from minutes to milliseconds
$idle_settings = $result[0]["idle_settings"] * 60000;
DEFINE( "SITE_IDLE_SETTING", (int)$idle_settings );

DEFINE( "SITE_METAKEYWORD", $result[0]["meta_keyword"] );
DEFINE( "SITE_METADESCRIPTION", $result[0]["meta_description"] );
DEFINE( "IMG_MAX_SIZE", '8M' );
$site_favicon = $result[0]["site_favicon"];
##Theme Colors Settings Starts
DEFINE( "ADMIN_HEADER_BG",'#'.$result[0]['admin_header_background']);
DEFINE( "DISPATCH_FOOTER_BG",'#'.$result[0]['dispatch_header_background']);
DEFINE( "ADMIN_FOOTER_BG",'#'.$result[0]['admin_footer_background']);
DEFINE( "ADMIN_SIDEBAR_BG",'#'.$result[0]['admin_sidebar_background']);
DEFINE( "ADMIN_SIDEBAR_SUBTAB",'#'.$result[0]['admin_sidebar_sub_tab']);
DEFINE( "ADMIN_SIDEBAR_ICON",'#'.$result[0]['admin_sidebar_icon']);
DEFINE( "ADMIN_SIDEBAR_ACTIVE",'#'.$result[0]['admin_sidebar_active']);
DEFINE( "ADMIN_SIDEBAR_ICON_ACTIVE",'#'.$result[0]['admin_sidebar_icon_active']);
DEFINE( "ADMIN_SIDEBAR_ICON_CIRCLE",'#'.$result[0]['admin_sidebar_icon_circle']);
DEFINE( "ADMIN_BUTTON_BG",'#'.$result[0]['admin_button_background']);
DEFINE( "ADMIN_BUTTON_HOVER_BG",'#'.$result[0]['admin_button_hover_background']);
DEFINE( "ADMIN_HIGHLIGHT_BG",'#'.$result[0]['highlights']);
DEFINE( "ADMIN_CANCEL_BG",'#'.$result[0]['cancel']);

DEFINE( "DISPATCH_BUTTON_BG",'#'.$result[0]['dispatch_button_background']);
DEFINE( "DISPATCH_BUTTON_HOVER_BG",'#'.$result[0]['dispatch_button_hover_background']);

DEFINE( "WEBSITE_HEADER_BG",'#'.$result[0]['website_header_background']);
DEFINE( "WEBSITE_FOOTER_BG",'#'.$result[0]['website_footer_background']);
DEFINE( "WEBSITE_SIDEBAR_BG",'#'.$result[0]['website_sidebar_background']);
DEFINE( "WEBSITE_SIDEBAR_ICON",'#'.$result[0]['website_sidebar_icon']);
DEFINE( "WEBSITE_SIDEBAR_ICON_ACTIVE",'#'.$result[0]['website_sidebar_icon_active']);
DEFINE( "WEBSITE_SIDEBAR_ACTIVE",'#'.$result[0]['website_sidebar_active']);
DEFINE( "WEBSITE_BUTTON_BG",'#'.$result[0]['website_button_background']);
DEFINE( "WEBSITE_BUTTON_HOVER_BG",'#'.$result[0]['website_button_hover_background']);
##Theme Colors Settings End
# twilio settings
/*DEFINE( "SMS_ACCOUNTID", $result[0]["sms_account_id"] );
DEFINE( "SMS_AUTHTOKEN", $result[0]["sms_auth_token"] );
DEFINE( "SMS_FROMNO", $result[0]["sms_from_number"] );*/
/*if ( $_SERVER['SERVER_PORT'] == "443" ) {
    DEFINE( 'PROTOCOL', 'https' );
} else {
    DEFINE( 'PROTOCOL', 'http' );
}*/
//DEFINE( 'URL_BASE', url::base( PROTOCOL, TRUE ) );
//DEFINE( 'UPLOADS', $uploads );
DEFINE( 'TEST_MODE', "T" );
DEFINE( 'LIVE_MODE', "L" );
DEFINE( 'IN_REVIEW', "R" );
# pre auhorize amount
DEFINE( 'PRE_AUTHORIZATION_AMOUNT', $result[0]['pre_authorized_amount'] );
DEFINE( 'PRE_AUTHORIZATION_REG_AMOUNT', 0 );
//~ DEFINE( 'PRE_AUTHORIZATION_RETRY_REG_AMOUNT', 1 );
# stripe payment does not accept low amount
DEFINE( 'PRE_AUTHORIZATION_RETRY_REG_AMOUNT', $result[0]['pre_authorized_amount'] );

DEFINE( 'GOOGLE_MAP_API_KEY', $result[0]['web_google_map_key'] );
DEFINE( 'GOOGLE_GEO_API_KEY', $result[0]['web_google_geo_key'] );
DEFINE( 'GOOGLE_TIMEZONE_API_KEY', $result[0]['google_timezone_api_key'] );
DEFINE( 'IPINFOAPI_KEY', "3aec9d045fb56ca9da8994707354ed3a85f6ea8ed850aafb284524eb6a5b3bbe" );
DEFINE( 'ENCRYPT_KEY', "ndotencript_" );
//set the headers here
header( 'Cache-Control: no-cache, no-store, must-revalidate' ); // HTTP 1.1.
header( 'Pragma: no-cache' ); // HTTP 1.0.
header( 'Expires: 0' ); // Proxies.
//View path
define( "ADMINVIEW", "admin/" );
define( "COMPANYVIEW", "company/" );
define( "MANAGERVIEW", "manager/" );
define( "VIEW_PATH", "pages/" );
define( "SCRIPTPATH", URL_BASE . "public/common/js/" );
//Status
define( "ACTIVE", "A" );
define( "INACTIVE", "I" );
define( "DELETED", "D" );
define( "SUCCESS", "S" );
define( "PENDING", "P" );
define( "SUCCESSWITHWARNING", "SW" );
define( "FAILURE", "F" );
define( "FAILUREWITHWARNING", "FW" );
define( "DELIVERED", "D" );
define( "UNDELIVERED", "U" );
define( "ADMIN", "A" );
define( "STOREADMIN", "S" );
define( "NORMALUSER", "N" );
define( "PAGE_NO", 1 );
define( "SUCESS", 1 );
define( "FAIL", 0 );
define( "PAID_PACKAGE", 6 );
define( "COMMON_ANDROID_PASSENGER_APP", 'https://play.google.com/store/apps/details?id=com.Taximobility&hl=en' );
define( "COMMON_IOS_PASSENGER_APP", 'https://itunes.apple.com/us/app/taximobility-passenger/id981530483?mt=8' );
define( "COMMON_ANDROID_DRIVER_APP", 'https://play.google.com/store/apps/details?id=com.taximobility.driver' );
define( "LOCATION_LATI", $result[0]['default_latitude']);
define( "LOCATION_LONG", $result[0]['default_longitude']);
define( "LOCATION_ADDR", "San Francisco" );
define( "IPADDRESS", "182.72.62.190" );
define( "RANDOM_KEY_LENGTH", "15" );
define( "REPLACE_LOGO", "##LOGO##" );
define( "REPLACE_NAME", "##NAME##" );
define( "REPLACE_USERNAME", "##USERNAME##" );
define( "REPLACE_SHAREDUSER", "##SHAREDUSER##" );
define( "REPLACE_CONTROLLERNAME", "##CONTROLLERNAME##" );
define( "REPLACE_PASSWORD", "##PASSWORD##" );
define( "REPLACE_SUBJECT", "##SUBJECT##" );
define( "RESET_LINK", "##RESET_LINK##" );
define( "REPLACE_PHONE", "##PHONE##" );
define( "REPLACE_MESSAGE", "##MESSAGE##" );
define( "REPLACE_PROMOCODE", "##PROMOCODE##" );
define( "REPLACE_PROMOLOGO", "##PROMOLOGO##");
define( "REPLACE_OTP", "##OTP##" );
define( "REPLACE_EMAIL", "##EMAIL##" );
define( "REPLACE_SALES_PERSON_EMAIL", "##SALES_PERSONEMAIL##" );
define( "REPLACE_MOBILE", "##MOBILE##" );
define( "REPLACE_SITENAME", "##SITENAME##" );
define( "REPLACE_COPYRIGHTYEAR", "##COPYYEAR##" );
define( "REPLACE_DOMAINNAME", "##DOMAINNAME##" );
define( "REPLACE_EMAIL_LOGO", "##EMAILLOGO##" );
define( "REPLACE_SITELINK", "##SITELINK##" );
define( "REPLACE_SITEURL", "##SITEURL##" ); //
define( "REPLACE_ACTLINK", "##ACTLINK##" ); //
define( "REPLACE_REQMESSAGE", "##REQMESSAGE##" );
define( "REPLACE_ORDERLIST", "##ORDERS_LIST##" );
define( "REPLACE_DELIVERYADDRESS", "##DELIVERY_ADDRESS##" );
define( "REPLACE_ORDERID", "##ORDERID##" );
define( "REPLACE_SITEEMAIL", "##SITEEMAIL##" );
define( "REPLACE_NOOFTAXI", "##NOOFTAXI##" );
define( "REPLACE_COMPANY", "##COMPANY##" );
define( "REPLACE_CLIENT_NAME", "##CLIENT_NAME##" );
define( "REPLACE_COUNTRY", "##COUNTRY##" );
define( "REPLACE_CITY", "##CITY##" );
define( "CONTACT_NO", "##CONTACT_NO##" );
define( "REPLACE_PICKUP", "##PICKUP##" );
define( "REPLACE_MAPURl", "##MAPURL##" );
define( "REPLACE_DROP", "##DROP##" );
define( "REPLACE_CURRENCY", "##CURRENCY##" );
define( "REPLACE_PACKAGE", "##PACKAGE_NAME##" );
define( "REPLACE_TAXIS", "##NO_OF_TAXIS##" );
define( "REPLACE_CHARGE", "##CHARGE_PER_TAXI##" );
define( "REPLACE_AMOUNT", "##TOTAL_AMOUNT##" );
define( "REPLACE_COPYRIGHTS", "##COPYRIGHTS##" );
define( "REPLACE_INSTALLATIONTYPE", "##REPLACE_INSTALLATIONTYPE##" );
define( "REPLACE_ANDROID_PASSENGER_APP", "##ANDROID_PASSENGER_APP##" );
define( "REPLACE_IOS_PASSENGER_APP", "##IOS_PASSENGER_APP##" );
define( "REPLACE_ANDROID_DRIVER_APP", "##ANDROID_DRIVER_APP##" );
define( 'MESSAGE', '##MESSAGE##' );
define( 'TEMPLATE_CONTENT', '##TEMPLATE_CONTENT##' );
define( 'SITE_DESCRIPTION', '##SITE_DESCRIPTION##' );
define( "REPLACE_VERIFYLINK", "##VERIFYLINK##" );
define( "REPLACE_STARTDATE", "##STARTDATE##" );
define( "REPLACE_EXPIREDATE", "##EXPIREDATE##" );
define( "REPLACE_USAGELIMIT", "##USAGELIMIT##" );
define( "IMGPATH", URL_BASE . "public/admin/images/" );
define( 'PUBLIC_FOLDER', "public/admin" );
//define( 'PUBLIC_UPLOADS_FOLDER', "public/" . UPLOADS );
define( 'PUBLIC_UPLOADS_LANDING_FOLDER', "public/" . UPLOADS . "/landing_page/" );

// Banner Image
$banner_image_1=isset($result[0]['banner_image_1'])?$result[0]['banner_image_1']:"";
define( 'PUBLIC_UPLOAD_BANNER_IMAGE', PUBLIC_UPLOADS_LANDING_FOLDER.$banner_image_1);

$banner_image_2=isset($result[0]['banner_image_2'])?$result[0]['banner_image_2']:"";
define( 'FRONT_BANNER_IMAGE',$banner_image_2);


define( 'PUBLIC_IMAGES_FOLDER', "public/common/images/" );
define( 'PUBLIC_UPLOAD_BANNER_FOLDER', PUBLIC_UPLOADS_FOLDER . "/banners/" );
define( 'UPLOADED_FILES', "uploadfiles" );
define( 'USERS_IMGFOLDER', "users/" );
define( 'USER_IMGPATH', PUBLIC_FOLDER . '/' . UPLOADED_FILES . '/' . USERS_IMGFOLDER );
define( 'USER_IMGPATH_THUMB', USER_IMGPATH . 'thumb/' );
define( 'USER_IMGPATH_PROFILE', USER_IMGPATH . 'profile/' );
define( 'PUBLIC_FOLDER_IMGPATH', PUBLIC_FOLDER . '/' . 'images' );
define( 'PUBLIC_IMGPATH', URL_BASE . 'public/common/images' );

if ( file_exists( DOCROOT . "public/".UPLOADS."/emailtemplate" ) ) {
    $template_path = "public/".UPLOADS."/emailtemplate/";
} else {
    $template_path = "public/common/emailtemplate/";
}
define( "TEMPLATEPATH", $template_path );

define( "REPLACE_TAXINO", "##TAXINO##" );
define( 'SITE_LOGOIMG', "site_logo" );
define( 'BANNER_IMG', "banners/" );
define( 'LOGOPATH', PUBLIC_FOLDER . '/images/' );
define( 'SITE_LOGO_IMGPATH', PUBLIC_UPLOADS_FOLDER . '/' . SITE_LOGOIMG . '/' );
define( 'BANNER_IMGPATH', PUBLIC_UPLOADS_FOLDER . '/' . BANNER_IMG );
define( 'TAXI_IMG', "taxi_image/" );
define( 'COMPANY_IMG', "company/" );
define( 'TAXI_IMG_IMGPATH', PUBLIC_UPLOADS_FOLDER . '/' . TAXI_IMG );
define( 'COMPANY_IMG_IMGPATH', PUBLIC_UPLOADS_FOLDER . '/' . COMPANY_IMG );
define( 'PASS_IMG', "passenger/" );
define( 'PASS_IMG_IMGPATH', PUBLIC_UPLOADS_FOLDER . '/' . PASS_IMG );
define( 'TAXI_IMG_WIDTH', 340 );
define( 'TAXI_IMG_HEIGHT', 260 ); //
define( 'NEW_TAXI_IMG_WIDTH', 185 );
define( 'NEW_TAXI_IMG_HEIGHT', 140 );
define( 'TAXI_APP_THMB32_IMG_WIDTH', 32 );
define( 'TAXI_APP_THMB32_IMG_HEIGHT', 32 );
define( 'TAXI_APP_THMB100_IMG_WIDTH', 100 );
define( 'TAXI_APP_THMB100_IMG_HEIGHT', 100 );
define( 'COMPANY_IMG_WIDTH', 32 );
define( 'COMPANY_IMG_HEIGHT', 32 );
define( 'NEW_COMPANY_IMG_WIDTH', 200 );
define( 'NEW_COMPANY_IMG_HEIGHT', 200 );
define( 'PASS_IMG_WIDTH', 140 );
define( 'PASS_IMG_HEIGHT', 140 );
define( 'PASS_THUMBIMG_WIDTH', 50 );
define( 'PASS_THUMBIMG_HEIGHT', 50 );
define( 'PASS_THUMBIMG_WIDTH1', 100 );
define( 'PASS_THUMBIMG_HEIGHT1', 100 );
define( 'SITE_LOGO_WIDTH', 155 );
define( 'SITE_LOGO_HEIGHT', 35 );
define( 'BANNER_SLIDER_WIDTH', 1600 );
define( 'BANNER_SLIDER_HEIGHT', 557 );
define( 'FAVICON_IMG', "favicon/" );
define( 'SITE_FAVICON_IMGPATH', PUBLIC_UPLOADS_FOLDER . '/' . FAVICON_IMG );
define( 'SITE_BANNER_IMGPATH', PUBLIC_UPLOADS_FOLDER . '/' . BANNER_IMG );
define( 'FAVICON_WIDTH', 16 );
define( 'FAVICON_HEIGHT', 16 );
define( 'SITE_BANNER_WIDTH', 1400 );
define( 'SITE_BANNER_HEIGHT', 625 );
define( 'DRIVER_IMG', "driver_image/" );
define( 'SITE_DRIVER_IMGPATH', PUBLIC_UPLOADS_FOLDER . '/' . DRIVER_IMG );
define( 'PASSENGER_TRIP_MAP_IMAGE_PATH', 'public/frontend/logged_in/images/passenger_trip_image/' );
define( 'WITHDRAW_IMG_PATH', PUBLIC_UPLOADS_FOLDER . '/withdraw_request_attachements/' );
//Taxi Dispatch
define( 'BOOTSTRAP_IMGPATH', URL_BASE . 'public/dispatch/vendor/bootstrap/images' );
define( 'TAXI_DISPATCH', "admin/taxi_dispatch/" );
define( 'DATATABLE_CSSPATH', URL_BASE . "public/dispatch/vendor/Datatable/css/" );
define( 'DATATABLE_JSPATH', URL_BASE . "public/dispatch/vendor/Datatable/js/" );
define( 'TDISPATCH_VIEW', 1 ); //1-Show , 0-Hide
define( 'MAP_COUNTRY', 'IND' );
define( 'REC_PER_PAGE', $result[0]['pagination_settings'] );
define( 'FARE_SETTINGS', $result[0]['price_settings'] );
define( 'ADMIN_NOTIFICATION_TIME', $result[0]['notification_settings'] );
define( 'CONTINOUS_REQUEST_TIME', $result[0]['continuous_request_time'] );
define( 'FB_KEY', $result[0]['facebook_key'] );
define( 'FB_SECRET_KEY', $result[0]['facebook_secretkey'] );
define( 'DEFAULT_COUNTRY', $result[0]['site_country'] );
define( 'DEFAULT_STATE', $result[0]['site_state'] );
define( 'DEFAULT_CITY', $result[0]['site_city'] );
define( 'APP_DESCRIPTION', html_entity_decode($result[0]['app_description']) );
define( 'TOTAL_RATING', 5 );
define( 'REMINDER_TIME', 1 );
define( 'ADMIN_COMMISSON', $result[0]['admin_commission'] );
define( 'ADMIN_COMMISSON_SINGLE', $result[0]['admin_commission_single'] );
define( 'TAX', $result[0]['tax'] );
define( 'MIN_FUND', "" );
define( 'MAX_FUND', "" );
define( 'SITE_NAME', $result[0]['app_name'] );
define( 'SITE_COPYRIGHT', $result[0]['site_copyrights'] );
define( 'FB_SHARE', $result[0]['facebook_share'] );
define( 'TW_SHARE', $result[0]['twitter_share'] );
define( 'GOOGLE_SHARE', $result[0]['google_share'] );
define( 'LINKEDIN_SHARE', $result[0]['linkedin_share'] );
define( 'SMS', $result[0]['sms_enable'] );
define( 'DRIVER_TELL_TO_FRIEND_MESSAGE', $result[0]['driver_tell_to_friend_message'] );
define( 'REFERRAL_DISCOUNT', $result[0]['referral_discount'] );
define( 'SITE_EMAIL_CONTACT', $result[0]['email_id'] );
define( 'SHOW_MAP', $result[0]['show_map'] );
define( 'TAXI_CHARGE', $result[0]['taxi_charge'] );
define( 'SITE_FARE_CALCULATION_TYPE', $result[0]['fare_calculation_type'] );
define( 'DRIVER_REF_SETTINGS', $result[0]['driver_referral_setting'] );
define( 'DRIVER_REF_AMOUNT', $result[0]['driver_referral_amount'] );
define( 'REFERRAL_SETTINGS', $result[0]['referral_settings'] );
define( 'REFERRAL_AMOUNT', $result[0]['referral_amount'] );
define( 'DRIVER_REFERRAL_SETTINGS', $result[0]['driver_referral_setting'] );
define( 'BOOK_BY_PASSENGER', 1 );
define( 'BOOK_BY_CONTROLLER', 2 );
define( 'REPLACE_COMPANYNAME', '##COMPANYNAME##' );
define( 'REPLACE_COMPANYDOMAIN', '##COMPANYDOMAIN##' );
DEFINE( 'LOCATIONUPDATESECONDS', $result[0]['location_update_seconds'] );
define( 'DEFAULTMILE', $result[0]['default_miles'] );
define( 'DEFAULT_DRIVER_MILE', 3 );
define( 'TRAILEXPIRY', 10 );
define( 'DEFAULT_CONNECTION', 'default' );
define( 'WALLET_AMOUNT_1', $result[0]['wallet_amount1'] );
define( 'WALLET_AMOUNT_2', $result[0]['wallet_amount2'] );
define( 'WALLET_AMOUNT_3', $result[0]['wallet_amount3'] );
define( 'WALLET_AMOUNT_RANGE', $result[0]['wallet_amount_range'] );
define( 'ADMIN_COMMISION_SETTING', $result[0]['admin_commision_setting'] );
define( 'COMPANY_COMMISION_SETTING', $result[0]['company_commision_setting'] );
define( 'DRIVER_COMMISION_SETTING', $result[0]['driver_commision_setting'] );
DEFINE( 'CUSTOMER_ANDROID_KEY', $result[0]['customer_android_key'] );
define( "NIGHT_FROM", "20:00:00" );
define( "NIGHT_TO", "05:59:59" );
define( "EVENING_FROM", "16:00:00" );
define( "EVENING_TO", "19:59:59" );
define( "SAR_EQUAL_USD", "0.27" );
define( 'REPLACE_TRIPDETAILS', '##TRIP_DETAILS##' );
$subdomainname = '.' . $_SERVER["HTTP_HOST"];
define( "SUB_DOMAIN_NAME", $uploads );
define( "DOMAIN_NAME", 'taximobility.com' );
define( "DOMAIN_URL_NAME", 'www.taximobility.com' );
$url       = URL_BASE;
$subdomain = getUrlSubdomain( $url );
define( "SUBDOMAIN", $subdomain );
define( 'LIVECHATSTATUS', $livechat );
$company_app_name = isset( $company_details[0]['company_app_name'] ) ? $company_details[0]['company_app_name'] : '';
# after loggin in    
//if ( $session->get( 'userid' ) != "" || $session->get( 'id' ) != "" ) {
    //$default_currency = $commonmodel->common_currency_details();
    $default_currency = Commonfunction::get_currency_details();//Get currency details from XML file instead of query
    define( 'CURRENCY_SYMB', $default_currency[0]['currency_symbol'] );
    define( 'DEFAULT_PAYMENT_GATEWAY_ID', '' );    
    $company_currency_code  = $default_currency[0]['currency_code'];
    $company_curency_symbol = $default_currency[0]['currency_symbol'];
    $company_telephone_code = $default_currency[0]['telephone_code'];
    define( 'CURRENCY_FORMAT', $default_currency[0]['currency_code'] );
    define( 'CURRENCY', $company_curency_symbol );
    define( 'TELEPHONECODE', $company_telephone_code );
if ( $companyId == 0 ) {
    define( 'DEFAULT_DATE_TIME_FORMAT', $result[0]['date_time_format'] );
    if ( $result[0]['date_time_format_script'] != "" ) {
        $date_time_script = explode( " ", $result[0]['date_time_format_script'] );
        if ( isset( $date_time_script[0] ) ) {
            define( 'DEFAULT_DATE_FORMAT_SCRIPT', $date_time_script[0] );
        } else {
            define( 'DEFAULT_TIME_FORMAT_SCRIPT', "" );
        }
        if ( isset( $date_time_script[1] ) ) {
            define( 'DEFAULT_TIME_FORMAT_SCRIPT', $date_time_script[1] );
            define( 'DEFAULT_TIME_SHOW', true );
        } else {
            define( 'DEFAULT_TIME_FORMAT_SCRIPT', "" );
            define( 'DEFAULT_TIME_SHOW', false );
        }
    } else {
        define( 'DEFAULT_DATE_FORMAT_SCRIPT', "yy-mm-dd" );
        define( 'DEFAULT_TIME_FORMAT_SCRIPT', "hh:mm:ss" );
        define( 'DEFAULT_TIME_SHOW', true );
    }
} else {
    $date_time_format        = "";
    $date_time_format_script = "";
    $date_time_format        = ( isset( $company_details[0]['date_time_format'] ) && $company_details[0]['date_time_format'] != "" ) ? $company_details[0]['date_time_format'] : "Y-m-d H:i:s";
    $date_time_format_script = ( isset( $company_details[0]['date_time_format_script'] ) && $company_details[0]['date_time_format_script'] != "" ) ? $company_details[0]['date_time_format_script'] : "";
    define( 'DEFAULT_DATE_TIME_FORMAT', $date_time_format );
    if ( $date_time_format_script != "" ) {
        $date_time_script = explode( " ", $date_time_format_script );
        if ( isset( $date_time_script[0] ) ) {
            define( 'DEFAULT_DATE_FORMAT_SCRIPT', $date_time_script[0] );
        } else {
            define( 'DEFAULT_TIME_FORMAT_SCRIPT', "" );
        }
        if ( isset( $date_time_script[1] ) ) {
            define( 'DEFAULT_TIME_FORMAT_SCRIPT', $date_time_script[1] );
            define( 'DEFAULT_TIME_SHOW', true );
        } else {
            define( 'DEFAULT_TIME_FORMAT_SCRIPT', "" );
            define( 'DEFAULT_TIME_SHOW', false );
        }
    } else {
        define( 'DEFAULT_DATE_FORMAT_SCRIPT', "yy-mm-dd" );
        define( 'DEFAULT_TIME_FORMAT_SCRIPT', "hh:mm:ss" );
        define( 'DEFAULT_TIME_SHOW', true );
    }
}

/*if ( $companyId > 0 ) { 
    $email_logo = URL_BASE . SITE_LOGO_IMGPATH . '/' . SUB_DOMAIN_NAME . '_email_logo.png';
} else {
       $email_logo = URL_BASE . SITE_LOGO_IMGPATH . 'site_email_logo.png';
}*/
$email_logo = URL_BASE . SITE_LOGO_IMGPATH . 'site_email_logo.png';
DEFINE( "EMAIL_TEMPLATE_LOGO", $email_logo );
DEFINE( "EMAILTEMPLATELOGO", $email_logo );
function getUrlSubdomain( $url )
{
    $urlSegments     = parse_url( $url );
    $urlHostSegments = explode( '.', $urlSegments['host'] );
    if ( count( $urlHostSegments ) > 2 ) {
        if ( $urlHostSegments[0] != "www" ) {
            return $urlHostSegments[0];
        } else {
            return null;
        }
    } else {
        return null;
    }
}
function findcompanyid( $commonmodel, $subdomain )
{
    $result = array();
    if ( $subdomain != "" ) {
        $result = $commonmodel->common_findcompanyid( $subdomain );
    }
    return $result;
}
$company_available_amount = 0;
$default_unit             = $default_skip_credit_card = $company_site_name = $company_customer_app_url = $company_driver_app_url = '';
if ( $companyId > 0 ) {
    $rs = $commonmodel->common_company_details( $companyId );
    define( "COMPANY_LOGO_URL_PATH", URL_BASE . PUBLIC_UPLOADS_FOLDER . '/' . SITE_LOGOIMG . '/' . $rs[0]['company_logo'] );
    define( "COMPANY_LOGO_FILE_PATH", DOCROOT . PUBLIC_UPLOADS_FOLDER . '/' . SITE_LOGOIMG . '/' . $rs[0]['company_logo'] );
    define( "COMPANY_LOGO_NAME", $rs[0]['company_logo'] );
    define( "COMPANY_FAV_URL_PATH", URL_BASE . PUBLIC_UPLOADS_FOLDER . '/' . FAVICON_IMG . '/' . $rs[0]['company_favicon'] );
    define( "COMPANY_FAV_FILE_PATH", DOCROOT . PUBLIC_UPLOADS_FOLDER . '/' . FAVICON_IMG . '/' . $rs[0]['company_favicon'] );
    define( "COMPANY_FAV_NAME", $rs[0]['company_favicon'] );
    $company_app_name = $rs[0]['company_app_name'];
    define( "COMPANY_FACEBOOK_LINK", isset( $rs[0]['company_facebook_share'] ) ? $rs[0]['company_facebook_share'] : "" );
    define( "COMPANY_TWITTER_LINK", isset( $rs[0]['company_twitter_share'] ) ? $rs[0]['company_twitter_share'] : "" );
    define( "COMPANY_GOOGLE_LINK", isset( $rs[0]['company_google_share'] ) ? $rs[0]['company_google_share'] : "" );
    define( "COMPANY_LINKED_LINK", isset( $rs[0]['company_linkedin_share'] ) ? $rs[0]['company_linkedin_share'] : "" );
    $company_customer_app_url = isset( $rs[0]['customer_app_url'] ) ? $rs[0]['customer_app_url'] : "";
    $company_driver_app_url   = isset( $rs[0]['driver_app_url'] ) ? $rs[0]['driver_app_url'] : "";
    define( "COMPANY_API_KEY", isset( $rs[0]['company_api_key'] ) ? $rs[0]['company_api_key'] : "" );
    define( "COMPANY_FB_KEY", isset( $rs[0]['company_facebook_key'] ) ? $rs[0]['company_facebook_key'] : "" );
    define( "COMPANY_FB_SECRET", isset( $rs[0]['company_facebook_secretkey'] ) ? $rs[0]['company_facebook_secretkey'] : "" );
    define( "COMPANY_NOTIFICATION_TIME", isset( $rs[0]['company_notification_settings'] ) ? $rs[0]['company_notification_settings'] : "" );
    define( "COMPANY_NAME", isset( $rs[0]['company_name'] ) ? $rs[0]['company_name'] : "" );
    define( "COMPANY_HEADER_BGCOLOR", isset( $rs[0]['header_bgcolor'] ) ? $rs[0]['header_bgcolor'] : "" );
    define( "COMPANY_HEADER_MENUCOLOR", isset( $rs[0]['menu_color'] ) ? $rs[0]['menu_color'] : "" );
    define( "COMPANY_HEADER_MOUSEOVERCOLOR", isset( $rs[0]['mouseover_color'] ) ? $rs[0]['mouseover_color'] : "" );
    define( "COMPANY_CONTACT_PHONE_NUMBER", isset( $rs[0]['company_phone_number'] ) ? $rs[0]['company_phone_number'] : "" );
    define( "COMPANY_META_TITLE", isset( $rs[0]['company_meta_title'] ) ? $rs[0]['company_meta_title'] : "" );
    define( "COMPANY_META_KEYWORD", isset( $rs[0]['company_meta_keyword'] ) ? $rs[0]['company_meta_keyword'] : "" );
    define( "COMPANY_META_DESCRIPTION", isset( $rs[0]['company_meta_description'] ) ? $rs[0]['company_meta_description'] : "" );
    define( "COMPANY_COPYRIGHT", isset( $rs[0]['company_copyrights'] ) ? $rs[0]['company_copyrights'] : "" );
    $company_site_name = isset( $rs[0]['company_app_name'] ) ? $rs[0]['company_app_name'] : "";
    if ( $session->get( 'user_type' ) != 'A' ) {
        # this condition checked for backend if logged user is not an admin means default unit should be company defined
        $default_unit = $rs[0]['default_unit'];
    } else {
        $default_unit = $result[0]['default_unit'];
    }
    $default_skip_credit_card = isset( $rs[0]['skip_credit_card'] ) ? $rs[0]['skip_credit_card'] : "";
    define( "COMPANY_FARE_CALCULATION_TYPE", isset( $rs[0]['fare_calculation_type'] ) ? $rs[0]['fare_calculation_type'] : "" );
    define( "TELL_TO_FRIEND_MESSAGE", isset( $rs[0]['company_app_description'] ) ? $rs[0]['company_app_description'] : "" );
    define( "CANCELLATION_FARE", isset( $rs[0]['cancellation_fare'] ) ? $rs[0]['cancellation_fare'] : "" );
    define( "COMPANY_FIRST_NAME", isset( $rs[0]['name'] ) ? $rs[0]['name'] : "" );
    define( "COMPANY_LAST_NAME", isset( $rs[0]['lastname'] ) ? $rs[0]['lastname'] : "" );
    define( "COMPANY_CONTACT_EMAIL", isset( $rs[0]['email'] ) ? $rs[0]['email'] : "" );
    define( "COMPANY_STREET_ADDR", isset( $rs[0]['address'] ) ? $rs[0]['address'] : "" );
    define( "COMPANY_LOGIN_CITY", isset( $rs[0]['login_city'] ) ? $rs[0]['login_city'] : DEFAULT_CITY );
    define( "COMPANY_LOGIN_STATE", isset( $rs[0]['login_state'] ) ? $rs[0]['login_state'] : DEFAULT_STATE );
    define( "COMPANY_LOGIN_COUNTRY", isset( $rs[0]['login_country'] ) ? $rs[0]['login_country'] : DEFAULT_COUNTRY );
    define( "CONTACT_EMAIL", SITE_EMAIL_CONTACT );
    $site_favicon = $rs[0]['company_favicon'];
    define( "DRIVER_COMMISSION", isset( $rs[0]['driver_commission'] ) ? $rs[0]['driver_commission'] : "" );
    $company_available_amount = ( isset( $rs[0]["account_balance"] ) && $rs[0]["account_balance"] > 0 ) ? round( $rs[0]["account_balance"], 3 ) : 0;
} else {
    define( "COMPANY_NOTIFICATION_TIME", 60 );
    $company_customer_app_url = 'https://play.google.com/store/apps/details?id=com.taximobility';
    $company_driver_app_url   = 'https://play.google.com/store/apps/details?id=com.taximobility.driver';
    define( 'TELL_TO_FRIEND_MESSAGE', $result[0]['tell_to_friend_message'] );
    $default_unit             = $result[0]['default_unit'];
    $default_skip_credit_card = $result[0]['skip_credit_card'];
    $company_site_name        = SITE_NAME;
    define( "CANCELLATION_FARE", $result[0]['cancellation_fare_setting'] );
    define( "COMPANY_COPYRIGHT", SITE_COPYRIGHT );
    define( "CONTACT_EMAIL", SITE_EMAIL_CONTACT );
    define( "COMPANY_CONTACT_EMAIL", SITE_EMAIL_CONTACT );
}
define( "COMPANY_APP_NAME", $company_app_name );
define( "COMPANY_CUSTOMER_APP_URL", $company_customer_app_url );
define( "COMPANY_DRIVER_APP_URL", $company_driver_app_url );
define( "COMPANY_SITENAME", $company_site_name );
define( "DEFAULT_UNIT", $default_unit );
define( "DEFAULT_SKIP_CREDIT_CARD", $default_skip_credit_card );
define( "COMPANY_CURRENCY", $company_curency_symbol ); //eg: $
define( "COMPANY_CURRENCY_FORMAT", $company_curency_code ); //eg: USD
define( "SITE_FAVICON", $site_favicon );
define( "COMPANY_AVAILABLE_AMOUNT", $company_available_amount );
define( "COPYRIGHT_YEAR", "2015" );
$config = array(
     "Africa/Abidjan" => "Africa/Abidjan",
    "Africa/Accra" => "Africa/Accra",
    "Africa/Addis_Ababa" => "Africa/Addis_Ababa",
    "Africa/Algiers" => "Africa/Algiers",
    "Africa/Asmara" => "Africa/Asmara",
    "Africa/Asmera" => "Africa/Asmera",
    "Africa/Bamako" => "Africa/Bamako",
    "Africa/Bangui" => "Africa/Bangui",
    "Africa/Banjul" => "Africa/Banjul",
    "Africa/Bissau" => "Africa/Bissau",
    "Africa/Blantyre" => "Africa/Blantyre",
    "Africa/Brazzaville" => "Africa/Brazzaville",
    "Africa/Bujumbura" => "Africa/Bujumbura",
    "Africa/Cairo" => "Africa/Cairo",
    "Africa/Casablanca" => "Africa/Casablanca",
    "Africa/Ceuta" => "Africa/Ceuta",
    "Africa/Conakry" => "Africa/Conakry",
    "Africa/Dakar" => "Africa/Dakar",
    "Africa/Dar_es_Salaam" => "Africa/Dar_es_Salaam",
    "Africa/Djibouti" => "Africa/Djibouti",
    "Africa/Douala" => "Africa/Douala",
    "Africa/El_Aaiun" => "Africa/El_Aaiun",
    "Africa/Freetown" => "Africa/Freetown",
    "Africa/Gaborone" => "Africa/Gaborone",
    "Africa/Harare" => "Africa/Harare",
    "Africa/Johannesburg" => "Africa/Johannesburg",
    "Africa/Juba" => "Africa/Juba",
    "Africa/Kampala" => "Africa/Kampala",
    "Africa/Khartoum" => "Africa/Khartoum",
    "Africa/Kigali" => "Africa/Kigali",
    "Africa/Kinshasa" => "Africa/Kinshasa",
    "Africa/Lagos" => "Africa/Lagos",
    "Africa/Libreville" => "Africa/Libreville",
    "Africa/Lome" => "Africa/Lome",
    "Africa/Luanda" => "Africa/Luanda",
    "Africa/Lubumbashi" => "Africa/Lubumbashi",
    "Africa/Lusaka" => "Africa/Lusaka",
    "Africa/Malabo" => "Africa/Malabo",
    "Africa/Maputo" => "Africa/Maputo",
    "Africa/Maseru" => "Africa/Maseru",
    "Africa/Mbabane" => "Africa/Mbabane",
    "Africa/Mogadishu" => "Africa/Mogadishu",
    "Africa/Monrovia" => "Africa/Monrovia",
    "Africa/Nairobi" => "Africa/Nairobi",
    "Africa/Ndjamena" => "Africa/Ndjamena",
    "Africa/Niamey" => "Africa/Niamey",
    "Africa/Nouakchott" => "Africa/Nouakchott",
    "Africa/Ouagadougou" => "Africa/Ouagadougou",
    "Africa/Porto-Novo" => "Africa/Porto-Novo",
    "Africa/Sao_Tome" => "Africa/Sao_Tome",
    "Africa/Timbuktu" => "Africa/Timbuktu",
    "Africa/Tripoli" => "Africa/Tripoli",
    "Africa/Tunis" => "Africa/Tunis",
    "Africa/Windhoek" => "Africa/Windhoek",
    "America/Adak" => "America/Adak",
    "America/Anchorage" => "America/Anchorage",
    "America/Anguilla" => "America/Anguilla",
    "America/Antigua" => "America/Antigua",
    "America/Araguaina" => "America/Araguaina",
    "America/Argentina/Buenos_Aires" => "America/Argentina/Buenos_Aires",
    "America/Argentina/Catamarca" => "America/Argentina/Catamarca",
    "America/Argentina/ComodRivadavia" => "America/Argentina/ComodRivadavia",
    "America/Argentina/Cordoba" => "America/Argentina/Cordoba",
    "America/Argentina/Jujuy" => "America/Argentina/Jujuy",
    "America/Argentina/La_Rioja" => "America/Argentina/La_Rioja",
    "America/Argentina/Mendoza" => "America/Argentina/Mendoza",
    "America/Argentina/Rio_Gallegos" => "America/Argentina/Rio_Gallegos",
    "America/Argentina/Salta" => "America/Argentina/Salta",
    "America/Argentina/San_Juan" => "America/Argentina/San_Juan",
    "America/Argentina/San_Luis" => "America/Argentina/San_Luis",
    "America/Argentina/Tucuman" => "America/Argentina/Tucuman",
    "America/Argentina/Ushuaia" => "America/Argentina/Ushuaia",
    "America/Aruba" => "America/Aruba",
    "America/Asuncion" => "America/Asuncion",
    "America/Atikokan" => "America/Atikokan",
    "America/Atka" => "America/Atka",
    "America/Bahia" => "America/Bahia",
    "America/Bahia_Banderas" => "America/Bahia_Banderas",
    "America/Barbados" => "America/Barbados",
    "America/Belem" => "America/Belem",
    "America/Belize" => "America/Belize",
    "America/Blanc-Sablon" => "America/Blanc-Sablon",
    "America/Boa_Vista" => "America/Boa_Vista",
    "America/Bogota" => "America/Bogota",
    "America/Boise" => "America/Boise",
    "America/Buenos_Aires" => "America/Buenos_Aires",
    "America/Cambridge_Bay" => "America/Cambridge_Bay",
    "America/Campo_Grande" => "America/Campo_Grande",
    "America/Cancun" => "America/Cancun",
    "America/Caracas" => "America/Caracas",
    "America/Catamarca" => "America/Catamarca",
    "America/Cayenne" => "America/Cayenne",
    "America/Cayman" => "America/Cayman",
    "America/Chicago" => "America/Chicago",
    "America/Chihuahua" => "America/Chihuahua",
    "America/Coral_Harbour" => "America/Coral_Harbour",
    "America/Cordoba" => "America/Cordoba",
    "America/Costa_Rica" => "America/Costa_Rica",
    "America/Creston" => "America/Creston",
    "America/Cuiaba" => "America/Cuiaba",
    "America/Curacao" => "America/Curacao",
    "America/Danmarkshavn" => "America/Danmarkshavn",
    "America/Dawson" => "America/Dawson",
    "America/Dawson_Creek" => "America/Dawson_Creek",
    "America/Denver" => "America/Denver",
    "America/Detroit" => "America/Detroit",
    "America/Dominica" => "America/Dominica",
    "America/Edmonton" => "America/Edmonton",
    "America/Eirunepe" => "America/Eirunepe",
    "America/El_Salvador" => "America/El_Salvador",
    "America/Ensenada" => "America/Ensenada",
    "America/Fort_Nelson" => "America/Fort_Nelson",
    "America/Fort_Wayne" => "America/Fort_Wayne",
    "America/Fortaleza" => "America/Fortaleza",
    "America/Glace_Bay" => "America/Glace_Bay",
    "America/Godthab" => "America/Godthab",
    "America/Goose_Bay" => "America/Goose_Bay",
    "America/Grand_Turk" => "America/Grand_Turk",
    "America/Grenada" => "America/Grenada",
    "America/Guadeloupe" => "America/Guadeloupe",
    "America/Guatemala" => "America/Guatemala",
    "America/Guayaquil" => "America/Guayaquil",
    "America/Guyana" => "America/Guyana",
    "America/Halifax" => "America/Halifax",
    "America/Havana" => "America/Havana",
    "America/Hermosillo" => "America/Hermosillo",
    "America/Indiana/Indianapolis" => "America/Indiana/Indianapolis",
    "America/Indiana/Knox" => "America/Indiana/Knox",
    "America/Indiana/Marengo" => "America/Indiana/Marengo",
    "America/Indiana/Petersburg" => "America/Indiana/Petersburg",
    "America/Indiana/Tell_City" => "America/Indiana/Tell_City",
    "America/Indiana/Vevay" => "America/Indiana/Vevay",
    "America/Indiana/Vincennes" => "America/Indiana/Vincennes",
    "America/Indiana/Winamac" => "America/Indiana/Winamac",
    "America/Indianapolis" => "America/Indianapolis",
    "America/Inuvik" => "America/Inuvik",
    "America/Iqaluit" => "America/Iqaluit",
    "America/Jamaica" => "America/Jamaica",
    "America/Jujuy" => "America/Jujuy",
    "America/Juneau" => "America/Juneau",
    "America/Kentucky/Louisville" => "America/Kentucky/Louisville",
    "America/Kentucky/Monticello" => "America/Kentucky/Monticello",
    "America/Knox_IN" => "America/Knox_IN",
    "America/Kralendijk" => "America/Kralendijk",
    "America/La_Paz" => "America/La_Paz",
    "America/Lima" => "America/Lima",
    "America/Los_Angeles" => "America/Los_Angeles",
    "America/Louisville" => "America/Louisville",
    "America/Lower_Princes" => "America/Lower_Princes",
    "America/Maceio" => "America/Maceio",
    "America/Managua" => "America/Managua",
    "America/Manaus" => "America/Manaus",
    "America/Marigot" => "America/Marigot",
    "America/Martinique" => "America/Martinique",
    "America/Matamoros" => "America/Matamoros",
    "America/Mazatlan" => "America/Mazatlan",
    "America/Mendoza" => "America/Mendoza",
    "America/Menominee" => "America/Menominee",
    "America/Merida" => "America/Merida",
    "America/Metlakatla" => "America/Metlakatla",
    "America/Mexico_City" => "America/Mexico_City",
    "America/Miquelon" => "America/Miquelon",
    "America/Moncton" => "America/Moncton",
    "America/Monterrey" => "America/Monterrey",
    "America/Montevideo" => "America/Montevideo",
    "America/Montreal" => "America/Montreal",
    "America/Montserrat" => "America/Montserrat",
    "America/Nassau" => "America/Nassau",
    "America/New_York" => "America/New_York",
    "America/Nipigon" => "America/Nipigon",
    "America/Nome" => "America/Nome",
    "America/Noronha" => "America/Noronha",
    "America/North_Dakota/Beulah" => "America/North_Dakota/Beulah",
    "America/North_Dakota/Center" => "America/North_Dakota/Center",
    "America/North_Dakota/New_Salem" => "America/North_Dakota/New_Salem",
    "America/Ojinaga" => "America/Ojinaga",
    "America/Panama" => "America/Panama",
    "America/Pangnirtung" => "America/Pangnirtung",
    "America/Paramaribo" => "America/Paramaribo",
    "America/Phoenix" => "America/Phoenix",
    "America/Port-au-Prince" => "America/Port-au-Prince",
    "America/Port_of_Spain" => "America/Port_of_Spain",
    "America/Porto_Acre" => "America/Porto_Acre",
    "America/Porto_Velho" => "America/Porto_Velho",
    "America/Puerto_Rico" => "America/Puerto_Rico",
    "America/Rainy_River" => "America/Rainy_River",
    "America/Rankin_Inlet" => "America/Rankin_Inlet",
    "America/Recife" => "America/Recife",
    "America/Regina" => "America/Regina",
    "America/Resolute" => "America/Resolute",
    "America/Rio_Branco" => "America/Rio_Branco",
    "America/Rosario" => "America/Rosario",
    "America/Santa_Isabel" => "America/Santa_Isabel",
    "America/Santarem" => "America/Santarem",
    "America/Santiago" => "America/Santiago",
    "America/Santo_Domingo" => "America/Santo_Domingo",
    "America/Sao_Paulo" => "America/Sao_Paulo",
    "America/Scoresbysund" => "America/Scoresbysund",
    "America/Shiprock" => "America/Shiprock",
    "America/Sitka" => "America/Sitka",
    "America/St_Barthelemy" => "America/St_Barthelemy",
    "America/St_Johns" => "America/St_Johns",
    "America/St_Kitts" => "America/St_Kitts",
    "America/St_Lucia" => "America/St_Lucia",
    "America/St_Thomas" => "America/St_Thomas",
    "America/St_Vincent" => "America/St_Vincent",
    "America/Swift_Current" => "America/Swift_Current",
    "America/Tegucigalpa" => "America/Tegucigalpa",
    "America/Thule" => "America/Thule",
    "America/Thunder_Bay" => "America/Thunder_Bay",
    "America/Tijuana" => "America/Tijuana",
    "America/Toronto" => "America/Toronto",
    "America/Tortola" => "America/Tortola",
    "America/Vancouver" => "America/Vancouver",
    "America/Virgin" => "America/Virgin",
    "America/Whitehorse" => "America/Whitehorse",
    "America/Winnipeg" => "America/Winnipeg",
    "America/Yakutat" => "America/Yakutat",
    "America/Yellowknife" => "America/Yellowknife",
    "Antarctica/Casey" => "Antarctica/Casey",
    "Antarctica/Davis" => "Antarctica/Davis",
    "Antarctica/DumontDUrville" => "Antarctica/DumontDUrville",
    "Antarctica/Macquarie" => "Antarctica/Macquarie",
    "Antarctica/Mawson" => "Antarctica/Mawson",
    "Antarctica/McMurdo" => "Antarctica/McMurdo",
    "Antarctica/Palmer" => "Antarctica/Palmer",
    "Antarctica/Rothera" => "Antarctica/Rothera",
    "Antarctica/South_Pole" => "Antarctica/South_Pole",
    "Antarctica/Syowa" => "Antarctica/Syowa",
    "Antarctica/Troll" => "Antarctica/Troll",
    "Antarctica/Vostok" => "Antarctica/Vostok",
    "Arctic/Longyearbyen" => "Arctic/Longyearbyen",
    "Asia/Aden" => "Asia/Aden",
    "Asia/Almaty" => "Asia/Almaty",
    "Asia/Amman" => "Asia/Amman",
    "Asia/Anadyr" => "Asia/Anadyr",
    "Asia/Aqtau" => "Asia/Aqtau",
    "Asia/Aqtobe" => "Asia/Aqtobe",
    "Asia/Ashgabat" => "Asia/Ashgabat",
    "Asia/Ashkhabad" => "Asia/Ashkhabad",
    "Asia/Atyrau" => "Asia/Atyrau",
    "Asia/Baghdad" => "Asia/Baghdad",
    "Asia/Bahrain" => "Asia/Bahrain",
    "Asia/Baku" => "Asia/Baku",
    "Asia/Bangkok" => "Asia/Bangkok",
    "Asia/Barnaul" => "Asia/Barnaul",
    "Asia/Beirut" => "Asia/Beirut",
    "Asia/Bishkek" => "Asia/Bishkek",
    "Asia/Brunei" => "Asia/Brunei",
    "Asia/Calcutta" => "Asia/Calcutta",
    "Asia/Chita" => "Asia/Chita",
    "Asia/Choibalsan" => "Asia/Choibalsan",
    "Asia/Chongqing" => "Asia/Chongqing",
    "Asia/Chungking" => "Asia/Chungking",
    "Asia/Colombo" => "Asia/Colombo",
    "Asia/Dacca" => "Asia/Dacca",
    "Asia/Damascus" => "Asia/Damascus",
    "Asia/Dhaka" => "Asia/Dhaka",
    "Asia/Dili" => "Asia/Dili",
    "Asia/Dubai" => "Asia/Dubai",
    "Asia/Dushanbe" => "Asia/Dushanbe",
    "Asia/Famagusta" => "Asia/Famagusta",
    "Asia/Gaza" => "Asia/Gaza",
    "Asia/Harbin" => "Asia/Harbin",
    "Asia/Hebron" => "Asia/Hebron",
    "Asia/Ho_Chi_Minh" => "Asia/Ho_Chi_Minh",
    "Asia/Hong_Kong" => "Asia/Hong_Kong",
    "Asia/Hovd" => "Asia/Hovd",
    "Asia/Irkutsk" => "Asia/Irkutsk",
    "Asia/Istanbul" => "Asia/Istanbul",
    "Asia/Jakarta" => "Asia/Jakarta",
    "Asia/Jayapura" => "Asia/Jayapura",
    "Asia/Jerusalem" => "Asia/Jerusalem",
    "Asia/Kabul" => "Asia/Kabul",
    "Asia/Kamchatka" => "Asia/Kamchatka",
    "Asia/Karachi" => "Asia/Karachi",
    "Asia/Kashgar" => "Asia/Kashgar",
    "Asia/Kathmandu" => "Asia/Kathmandu",
    "Asia/Katmandu" => "Asia/Katmandu",
    "Asia/Khandyga" => "Asia/Khandyga",
    "Asia/Kolkata" => "Asia/Kolkata",
    "Asia/Krasnoyarsk" => "Asia/Krasnoyarsk",
    "Asia/Kuala_Lumpur" => "Asia/Kuala_Lumpur",
    "Asia/Kuching" => "Asia/Kuching",
    "Asia/Kuwait" => "Asia/Kuwait",
    "Asia/Macao" => "Asia/Macao",
    "Asia/Macau" => "Asia/Macau",
    "Asia/Magadan" => "Asia/Magadan",
    "Asia/Makassar" => "Asia/Makassar",
    "Asia/Manila" => "Asia/Manila",
    "Asia/Muscat" => "Asia/Muscat",
    "Asia/Nicosia" => "Asia/Nicosia",
    "Asia/Novokuznetsk" => "Asia/Novokuznetsk",
    "Asia/Novosibirsk" => "Asia/Novosibirsk",
    "Asia/Omsk" => "Asia/Omsk",
    "Asia/Oral" => "Asia/Oral",
    "Asia/Phnom_Penh" => "Asia/Phnom_Penh",
    "Asia/Pontianak" => "Asia/Pontianak",
    "Asia/Pyongyang" => "Asia/Pyongyang",
    "Asia/Qatar" => "Asia/Qatar",
    "Asia/Qyzylorda" => "Asia/Qyzylorda",
    "Asia/Rangoon" => "Asia/Rangoon",
    "Asia/Riyadh" => "Asia/Riyadh",
    "Asia/Saigon" => "Asia/Saigon",
    "Asia/Sakhalin" => "Asia/Sakhalin",
    "Asia/Samarkand" => "Asia/Samarkand",
    "Asia/Seoul" => "Asia/Seoul",
    "Asia/Shanghai" => "Asia/Shanghai",
    "Asia/Singapore" => "Asia/Singapore",
    "Asia/Srednekolymsk" => "Asia/Srednekolymsk",
    "Asia/Taipei" => "Asia/Taipei",
    "Asia/Tashkent" => "Asia/Tashkent",
    "Asia/Tbilisi" => "Asia/Tbilisi",
    "Asia/Tehran" => "Asia/Tehran",
    "Asia/Tel_Aviv" => "Asia/Tel_Aviv",
    "Asia/Thimbu" => "Asia/Thimbu",
    "Asia/Thimphu" => "Asia/Thimphu",
    "Asia/Tokyo" => "Asia/Tokyo",
    "Asia/Tomsk" => "Asia/Tomsk",
    "Asia/Ujung_Pandang" => "Asia/Ujung_Pandang",
    "Asia/Ulaanbaatar" => "Asia/Ulaanbaatar",
    "Asia/Ulan_Bator" => "Asia/Ulan_Bator",
    "Asia/Urumqi" => "Asia/Urumqi",
    "Asia/Ust-Nera" => "Asia/Ust-Nera",
    "Asia/Vientiane" => "Asia/Vientiane",
    "Asia/Vladivostok" => "Asia/Vladivostok",
    "Asia/Yakutsk" => "Asia/Yakutsk",
    "Asia/Yangon" => "Asia/Yangon",
    "Asia/Yekaterinburg" => "Asia/Yekaterinburg",
    "Asia/Yerevan" => "Asia/Yerevan",
    "Atlantic/Azores" => "Atlantic/Azores",
    "Atlantic/Bermuda" => "Atlantic/Bermuda",
    "Atlantic/Canary" => "Atlantic/Canary",
    "Atlantic/Cape_Verde" => "Atlantic/Cape_Verde",
    "Atlantic/Faeroe" => "Atlantic/Faeroe",
    "Atlantic/Faroe" => "Atlantic/Faroe",
    "Atlantic/Jan_Mayen" => "Atlantic/Jan_Mayen",
    "Atlantic/Madeira" => "Atlantic/Madeira",
    "Atlantic/Reykjavik" => "Atlantic/Reykjavik",
    "Atlantic/South_Georgia" => "Atlantic/South_Georgia",
    "Atlantic/St_Helena" => "Atlantic/St_Helena",
    "Atlantic/Stanley" => "Atlantic/Stanley",
    "Australia/ACT" => "Australia/ACT",
    "Australia/Adelaide" => "Australia/Adelaide",
    "Australia/Brisbane" => "Australia/Brisbane",
    "Australia/Broken_Hill" => "Australia/Broken_Hill",
    "Australia/Canberra" => "Australia/Canberra",
    "Australia/Currie" => "Australia/Currie",
    "Australia/Darwin" => "Australia/Darwin",
    "Australia/Eucla" => "Australia/Eucla",
    "Australia/Hobart" => "Australia/Hobart",
    "Australia/LHI" => "Australia/LHI",
    "Australia/Lindeman" => "Australia/Lindeman",
    "Australia/Lord_Howe" => "Australia/Lord_Howe",
    "Australia/Melbourne" => "Australia/Melbourne",
    "Australia/North" => "Australia/North",
    "Australia/NSW" => "Australia/NSW",
    "Australia/Perth" => "Australia/Perth",
    "Australia/Queensland" => "Australia/Queensland",
    "Australia/South" => "Australia/South",
    "Australia/Sydney" => "Australia/Sydney",
    "Australia/Tasmania" => "Australia/Tasmania",
    "Australia/Victoria" => "Australia/Victoria",
    "Australia/West" => "Australia/West",
    "Australia/Yancowinna" => "Australia/Yancowinna",
    "Brazil/Acre" => "Brazil/Acre",
    "Brazil/DeNoronha" => "Brazil/DeNoronha",
    "Brazil/East" => "Brazil/East",
    "Brazil/West" => "Brazil/West",
    "Canada/Atlantic" => "Canada/Atlantic",
    "Canada/Central" => "Canada/Central",
    "Canada/East-Saskatchewan" => "Canada/East-Saskatchewan",
    "Canada/Eastern" => "Canada/Eastern",
    "Canada/Mountain" => "Canada/Mountain",
    "Canada/Newfoundland" => "Canada/Newfoundland",
    "Canada/Pacific" => "Canada/Pacific",
    "Canada/Saskatchewan" => "Canada/Saskatchewan",
    "Canada/Yukon" => "Canada/Yukon",
    "CET" => "CET",
    "Chile/Continental" => "Chile/Continental",
    "Chile/EasterIsland" => "Chile/EasterIsland",
    "CST6CDT" => "CST6CDT",
    "Cuba" => "Cuba",
    "EET" => "EET",
    "Egypt" => "Egypt",
    "Eire" => "Eire",
    "EST" => "EST",
    "EST5EDT" => "EST5EDT",
    "Etc/Greenwich" => "Etc/Greenwich",
    "Etc/UCT" => "Etc/UCT",
    "Etc/Universal" => "Etc/Universal",
    "Etc/Zulu" => "Etc/Zulu",
    "Europe/Amsterdam" => "Europe/Amsterdam",
    "Europe/Andorra" => "Europe/Andorra",
    "Europe/Astrakhan" => "Europe/Astrakhan",
    "Europe/Athens" => "Europe/Athens",
    "Europe/Belfast" => "Europe/Belfast",
    "Europe/Belgrade" => "Europe/Belgrade",
    "Europe/Berlin" => "Europe/Berlin",
    "Europe/Bratislava" => "Europe/Bratislava",
    "Europe/Brussels" => "Europe/Brussels",
    "Europe/Bucharest" => "Europe/Bucharest",
    "Europe/Budapest" => "Europe/Budapest",
    "Europe/Busingen" => "Europe/Busingen",
    "Europe/Chisinau" => "Europe/Chisinau",
    "Europe/Copenhagen" => "Europe/Copenhagen",
    "Europe/Dublin" => "Europe/Dublin",
    "Europe/Gibraltar" => "Europe/Gibraltar",
    "Europe/Guernsey" => "Europe/Guernsey",
    "Europe/Helsinki" => "Europe/Helsinki",
    "Europe/Isle_of_Man" => "Europe/Isle_of_Man",
    "Europe/Istanbul" => "Europe/Istanbul",
    "Europe/Jersey" => "Europe/Jersey",
    "Europe/Kaliningrad" => "Europe/Kaliningrad",
    "Europe/Kiev" => "Europe/Kiev",
    "Europe/Kirov" => "Europe/Kirov",
    "Europe/Lisbon" => "Europe/Lisbon",
    "Europe/Ljubljana" => "Europe/Ljubljana",
    "Europe/London" => "Europe/London",
    "Europe/Luxembourg" => "Europe/Luxembourg",
    "Europe/Madrid" => "Europe/Madrid",
    "Europe/Malta" => "Europe/Malta",
    "Europe/Mariehamn" => "Europe/Mariehamn",
    "Europe/Minsk" => "Europe/Minsk",
    "Europe/Monaco" => "Europe/Monaco",
    "Europe/Moscow" => "Europe/Moscow",
    "Europe/Nicosia" => "Europe/Nicosia",
    "Europe/Oslo" => "Europe/Oslo",
    "Europe/Paris" => "Europe/Paris",
    "Europe/Podgorica" => "Europe/Podgorica",
    "Europe/Prague" => "Europe/Prague",
    "Europe/Riga" => "Europe/Riga",
    "Europe/Rome" => "Europe/Rome",
    "Europe/Samara" => "Europe/Samara",
    "Europe/San_Marino" => "Europe/San_Marino",
    "Europe/Sarajevo" => "Europe/Sarajevo",
    "Europe/Saratov" => "Europe/Saratov",
    "Europe/Simferopol" => "Europe/Simferopol",
    "Europe/Skopje" => "Europe/Skopje",
    "Europe/Sofia" => "Europe/Sofia",
    "Europe/Stockholm" => "Europe/Stockholm",
    "Europe/Tallinn" => "Europe/Tallinn",
    "Europe/Tirane" => "Europe/Tirane",
    "Europe/Tiraspol" => "Europe/Tiraspol",
    "Europe/Ulyanovsk" => "Europe/Ulyanovsk",
    "Europe/Uzhgorod" => "Europe/Uzhgorod",
    "Europe/Vaduz" => "Europe/Vaduz",
    "Europe/Vatican" => "Europe/Vatican",
    "Europe/Vienna" => "Europe/Vienna",
    "Europe/Vilnius" => "Europe/Vilnius",
    "Europe/Volgograd" => "Europe/Volgograd",
    "Europe/Warsaw" => "Europe/Warsaw",
    "Europe/Zagreb" => "Europe/Zagreb",
    "Europe/Zaporozhye" => "Europe/Zaporozhye",
    "Europe/Zurich" => "Europe/Zurich",
    "Factory" => "Factory",
    "Greenwich" => "Greenwich",
    "Hongkong" => "Hongkong",
    "HST" => "HST",
    "Iceland" => "Iceland",
    "Indian/Antananarivo" => "Indian/Antananarivo",
    "Indian/Chagos" => "Indian/Chagos",
    "Indian/Christmas" => "Indian/Christmas",
    "Indian/Cocos" => "Indian/Cocos",
    "Indian/Comoro" => "Indian/Comoro",
    "Indian/Kerguelen" => "Indian/Kerguelen",
    "Indian/Mahe" => "Indian/Mahe",
    "Indian/Maldives" => "Indian/Maldives",
    "Indian/Mauritius" => "Indian/Mauritius",
    "Indian/Mayotte" => "Indian/Mayotte",
    "Indian/Reunion" => "Indian/Reunion",
    "Iran" => "Iran",
    "Israel" => "Israel",
    "Jamaica" => "Jamaica",
    "Japan" => "Japan",
    "Kwajalein" => "Kwajalein",
    "Libya" => "Libya",
    "MET" => "MET",
    "Mexico/BajaNorte" => "Mexico/BajaNorte",
    "Mexico/BajaSur" => "Mexico/BajaSur",
    "Mexico/General" => "Mexico/General",
    "MST" => "MST",
    "MST7MDT" => "MST7MDT",
    "Navajo" => "Navajo",
    "NZ" => "NZ",
    "NZ-CHAT" => "NZ-CHAT",
    "Pacific/Apia" => "Pacific/Apia",
    "Pacific/Auckland" => "Pacific/Auckland",
    "Pacific/Bougainville" => "Pacific/Bougainville",
    "Pacific/Chatham" => "Pacific/Chatham",
    "Pacific/Chuuk" => "Pacific/Chuuk",
    "Pacific/Easter" => "Pacific/Easter",
    "Pacific/Efate" => "Pacific/Efate",
    "Pacific/Enderbury" => "Pacific/Enderbury",
    "Pacific/Fakaofo" => "Pacific/Fakaofo",
    "Pacific/Fiji" => "Pacific/Fiji",
    "Pacific/Funafuti" => "Pacific/Funafuti",
    "Pacific/Galapagos" => "Pacific/Galapagos",
    "Pacific/Gambier" => "Pacific/Gambier",
    "Pacific/Guadalcanal" => "Pacific/Guadalcanal",
    "Pacific/Guam" => "Pacific/Guam",
    "Pacific/Honolulu" => "Pacific/Honolulu",
    "Pacific/Johnston" => "Pacific/Johnston",
    "Pacific/Kiritimati" => "Pacific/Kiritimati",
    "Pacific/Kosrae" => "Pacific/Kosrae",
    "Pacific/Kwajalein" => "Pacific/Kwajalein",
    "Pacific/Majuro" => "Pacific/Majuro",
    "Pacific/Marquesas" => "Pacific/Marquesas",
    "Pacific/Midway" => "Pacific/Midway",
    "Pacific/Nauru" => "Pacific/Nauru",
    "Pacific/Niue" => "Pacific/Niue",
    "Pacific/Norfolk" => "Pacific/Norfolk",
    "Pacific/Noumea" => "Pacific/Noumea",
    "Pacific/Pago_Pago" => "Pacific/Pago_Pago",
    "Pacific/Palau" => "Pacific/Palau",
    "Pacific/Pitcairn" => "Pacific/Pitcairn",
    "Pacific/Pohnpei" => "Pacific/Pohnpei",
    "Pacific/Ponape" => "Pacific/Ponape",
    "Pacific/Port_Moresby" => "Pacific/Port_Moresby",
    "Pacific/Rarotonga" => "Pacific/Rarotonga",
    "Pacific/Saipan" => "Pacific/Saipan",
    "Pacific/Samoa" => "Pacific/Samoa",
    "Pacific/Tahiti" => "Pacific/Tahiti",
    "Pacific/Tarawa" => "Pacific/Tarawa",
    "Pacific/Tongatapu" => "Pacific/Tongatapu",
    "Pacific/Truk" => "Pacific/Truk",
    "Pacific/Wake" => "Pacific/Wake",
    "Pacific/Wallis" => "Pacific/Wallis",
    "Pacific/Yap" => "Pacific/Yap",
    "Poland" => "Poland",
    "Portugal" => "Portugal",
    "PRC" => "PRC",
    "PST8PDT" => "PST8PDT",
    "ROC" => "ROC",
    "ROK" => "ROK",
    "Singapore" => "Singapore",
    "Turkey" => "Turkey",
    "UCT" => "UCT",
    "Universal" => "Universal",
    "US/Alaska" => "US/Alaska",
    "US/Aleutian" => "US/Aleutian",
    "US/Arizona" => "US/Arizona",
    "US/Central" => "US/Central",
    "US/East-Indiana" => "US/East-Indiana",
    "US/Eastern" => "US/Eastern",
    "US/Hawaii" => "US/Hawaii",
    "US/Indiana-Starke" => "US/Indiana-Starke",
    "US/Michigan" => "US/Michigan",
    "US/Mountain" => "US/Mountain",
    "US/Pacific" => "US/Pacific",
    "US/Pacific-New" => "US/Pacific-New",
    "US/Samoa" => "US/Samoa",
    "UTC" => "UTC",
    "W-SU" => "W-SU",
    "WET" => "WET",
    "Zulu" => "Zulu" 
);
define( 'SELECT_TIMEZONE', serialize( $config ) );
function convert_timezone( $time, $timezone = '' )
{
    $date      = new DateTime( $time, new DateTimeZone( $timezone ) );
    $localtime = $date->format( 'Y-m-d H:i:s' );
    return $localtime;
}
function findcompany_currency( $company_cid )
{
    $commonmodel = Model::factory( 'commonmodel' );
    $result      = $commonmodel->findcompany_currency( $company_cid );
    return $result;
}
function findcompany_currencyformat( $company_cid )
{
    return CURRENCY_FORMAT;
}
// FUNCTION FOR CURRENCY CONVERSION
function currency_conversion( $company_currency, $amt )
{
    try {
        return $amt;
    }
    catch ( Kohana_Exception $e ) {
        Message::error( __( 'currency_converstion_not_applicable' ) );
        $location = URL_BASE . '/admin/dashboard';
        header( "Location: $location" );
    }
}
// FUNCTION FOR CURRENCY CONVERSION to USD
function currency_conversion_usd( $company_currency, $amt )
{
    try {
        return $amt;
    }
    catch ( Kohana_Exception $e ) {
        $converted_amt = SAR_EQUAL_USD * $amt;
        return $converted_amt;
    }
}
//ENCRIPTION AND DECRIPTION FUNCTION
function encrypt_decrypt( $action, $string )
{
    $output = false;
    $key    = 'Taxi Application';
    // initialization vector 
    $iv     = md5( md5( $key ) );
    if ( $action == 'encrypt' ) {
        $output = base64_encode( ENCRYPT_KEY . $string );
    } else if ( $action == 'decrypt' ) {
        $decrypt_val = base64_decode( $string );
        $split       = explode( '_', $decrypt_val );
        if ( count( $split ) > 1 ) {
            $output = trim( $split[1] );
        } else {
            $output = "";
        }
    }
    return $output;
}
// Repeat X function
function repeatx( $data, $repeatstring, $repeatcount )
{
    if ( $data != "" ) {
        if ( $repeatcount == 'All' ) {
            return str_repeat( $repeatstring, ( strlen( $data ) ) );
        } else {
            return str_repeat( $repeatstring, ( strlen( $data ) - $repeatcount ) ) . substr( $data, -$repeatcount, $repeatcount );
        }
    } else {
        return 0;
    }
}
define( "SENDGRID_HOST", 'smtp.sendgrid.net' );
define( "SENDGRID_PORT", '25' );
define( "SENDGRID_USERNAME", 'Taxindot9' );
define( "SENDGRID_PASSWORD", 'taxisendto&6!' );
/******** Language Selection ************/
DEFINE( 'IOS_DRIVER_LANG', is_array($result[0]['ios_driver_language_settings'])?$result[0]['ios_driver_language_settings']:array());
DEFINE( 'IOS_PASSENGER_LANG',is_array($result[0]['ios_passenger_language_settings'])?$result[0]['ios_passenger_language_settings']:array());
DEFINE( 'IOS_DISPATCH_LANG',is_array($result[0]['ios_dispatch_language_settings'])?$result[0]['ios_dispatch_language_settings']:array());
DEFINE( 'IOS_DRIVER_COLORCODE',is_array($result[0]['ios_driver_colorcode_settings'])?$result[0]['ios_driver_colorcode_settings']:array());
DEFINE( 'IOS_PASSENGER_COLORCODE',is_array($result[0]['ios_passenger_colorcode_settings'])?$result[0]['ios_passenger_colorcode_settings']:array());
DEFINE( 'IOS_DISPATCH_COLORCODE',is_array($result[0]['ios_dispatch_colorcode_settings'])?$result[0]['ios_dispatch_colorcode_settings']:array());
DEFINE( 'ANDROID_DRIVER_LANG', is_array($result[0]['android_driver_language_settings'])?$result[0]['android_driver_language_settings']:array());
DEFINE( 'ANDROID_PASSENGER_LANG', is_array($result[0]['android_passenger_language_settings'])?$result[0]['android_passenger_language_settings']:array());
DEFINE( 'ANDROID_DISPATCH_LANG', is_array($result[0]['android_dispatch_language_settings'])?$result[0]['android_dispatch_language_settings']:array());
DEFINE( 'ANDROID_DRIVER_COLORCODE',is_array($result[0]['android_driver_colorcode_settings'])?$result[0]['android_driver_colorcode_settings']:array());
DEFINE( 'ANDROID_PASSENGER_COLORCODE',is_array($result[0]['android_passenger_colorcode_settings'])?$result[0]['android_passenger_colorcode_settings']:array());
DEFINE( 'ANDROID_DISPATCH_COLORCODE',is_array($result[0]['android_dispatch_colorcode_settings'])?$result[0]['android_dispatch_colorcode_settings']:array());
$web_language = is_array($result[0]['website_language_settings'])?$result[0]['website_language_settings']:array();
DEFINE( 'WEB_DB_LANGUAGE',$web_language);
/***********************************************/
define( "SUPERADMIN_EMAIL", "superadmin@taximobility.com" );
define( 'UNIT', DEFAULT_UNIT );
if ( UNIT == 1 ) {
    define( 'UNIT_NAME', 'miles' );
} else {
    define( 'UNIT_NAME', 'km' );
}
if ( $companyId > 0 && $session->get( 'user_type' ) != 'A' && FARE_SETTINGS == 2 ) {
    define( 'FARE_CALCULATION_TYPE', COMPANY_FARE_CALCULATION_TYPE ); //1 => Distance, 2 => Time, 3=> Distance / Time
} else {
    define( 'FARE_CALCULATION_TYPE', SITE_FARE_CALCULATION_TYPE ); //1 => Distance, 2 => Time, 3=> Distance / Time
}
define( 'SKIP_CREDIT_CARD', DEFAULT_SKIP_CREDIT_CARD ); // 1 as Skip , 0 as No-Skip
/****Tell to friend-App URL Display by PAID VERSION Start****/
if ( $companyId > 0 ) {
} else {
    define( "ANDROID_PASSENGER_APP", COMMON_ANDROID_PASSENGER_APP );
    define( "IOS_PASSENGER_APP", COMMON_IOS_PASSENGER_APP );
    define( "ANDROID_DRIVER_APP", COMMON_ANDROID_DRIVER_APP );
}
/**** Driver tracking DB initializing****/
define( 'DRIVER_TRACK_DB', 'driver_tracking' ); //Access Database 2
define( "REPLACE_ENDDATE", "##ENDDATE##" );
define( "REPLACE_TAXIMODEL", "##TAXIMODEL##" );
define( "REPLACE_TAXISPEED", "##TAXISPEED##" );
define( "REPLACE_MAXIMUMLUGGAGE", "##MAXIMUMLUGGAGE##" );
define( 'MODEL_IMGPATH', PUBLIC_UPLOADS_FOLDER . '/model_image/' );
define( 'FRONTEND_MAP_LAT_LONG_PATH', 'application/config/' );
define( 'MOBILE_LOGO_PATH', PUBLIC_UPLOADS_FOLDER . '/iOS/static_image/' );
define( "SMTP", 1 );
/**
 * Check Company Expiry Date based restriction
 */
$dynamicTimeZone = isset( $result[0]["user_time_zone"] ) ? $result[0]["user_time_zone"] : 'Asia/Kolkata';
define( "TIMEZONE", $dynamicTimeZone );
define( "USER_SELECTED_TIMEZONE", $dynamicTimeZone );
define( 'CHECK_EXPIRY', $expiry );
$currentTime      = convert_timezone( 'now', $dynamicTimeZone );
$cTsatmp          = strtotime( $currentTime );
//~ $expiryTime       = isset( $result[0]["expiry_date"] ) ? strtotime( Commonfunction::convertphpdate( 'Y-m-d H:i:s', $result[0]["expiry_date"] ) ) : '';
//~ $expiry_date_time = isset( $result[0]["expiry_date"] ) ? Commonfunction::convertphpdate( 'Y-m-d H:i:s', $result[0]["expiry_date"] ) : '';

    $expireDate = isset( $result[0]["expiry_date"] ) ? $result[0]["expiry_date"] : '';

    if(is_array($expireDate)){
        $expiryTime       = isset( $result[0]["expiry_date"] ) ? strtotime( Commonfunction::convertphpdate( 'Y-m-d H:i:s', $result[0]["expiry_date"] ) ) : '';
        $expiry_date_time = isset( $result[0]["expiry_date"] ) ? Commonfunction::convertphpdate( 'Y-m-d H:i:s', $result[0]["expiry_date"] ) : '';
    }else{
        $expiryTime       = $expireDate;
        $expiry_date_time = (isset( $result[0]["expiry_date"] ) && ($result[0]["expiry_date"]!=''))? date( 'Y-m-d H:i:s', $result[0]["expiry_date"] ) : '';
    }
        



if ( isset( $result[0]['package_type'] ) ) {
    DEFINE( 'PACKAGE_TYPE', $result[0]['package_type'] );
} else {
    DEFINE( 'PACKAGE_TYPE', 0 );
} 

## trial plan expiry details
$diff = $days_message = '';
if ( $expiry_date_time != '' ) {
    $date1 = new DateTime( $expiry_date_time );
    $date2 = new DateTime( $currentTime );
    $diff  = $date2->diff( $date1 )->format( "%R%a" ) + 1;
}

DEFINE( 'TRIAL_EXPIRY_DAYS', $diff );
if($diff > 1)
    $days_message = 'trial_expiry_days';
else
    $days_message = 'trial_expiry_days_1';
    
DEFINE( 'DAYS_MESSAGE', $days_message );

DEFINE( 'EXPIRY_TIME', $expiryTime );
DEFINE( 'EXPIRT_DATETIME_FORMAT', $expiry_date_time );
DEFINE( 'CURRENT_TIMEZONE_DATE', $currentTime );
DEFINE( 'TERMS_URL', 'https://www.taximobility.com/termsconditions.html' );
DEFINE( 'PRIVACY_URL', 'https://www.taximobility.com/privacypolicy.html' );
DEFINE( 'CONTACT_URL', 'https://www.taximobility.com/contact-us.html' );
DEFINE( 'FAQ_URL', 'https://www.taximobility.com/faq.html' );
DEFINE( 'CLOUD_SITENAME', 'TaxiMobility' );
DEFINE( 'CLOUD_CURRENCY_SYMB', '    $' );
DEFINE( 'CLOUD_CURRENCY_NUM', '356' );
DEFINE( 'CLOUD_CURRENCY_FORMAT', 'USD' );
DEFINE( 'CLOUD_CURRENCY_CONVERSION_RATE', '67.00' );
DEFINE( 'SAMPLE_ANDROID_LANG_FILES', PUBLIC_UPLOADS_FOLDER . '/android/language/' );
DEFINE( 'SAMPLE_IOS_LANG_FILES', PUBLIC_UPLOADS_FOLDER . '/iOS/language/' );
DEFINE( 'SAMPLE_ANDROID_COLORCODE_FILES', PUBLIC_UPLOADS_FOLDER . '/android/colorcode/' );
DEFINE( 'SAMPLE_IOS_COLORCODE_FILES', PUBLIC_UPLOADS_FOLDER . '/iOS/colorcode/' );
DEFINE( 'IOS_DEFAULT_CUSTOMIZE_FILES', PUBLIC_UPLOADS_FOLDER . '/iOS/language_colorcode_default_customize/' );
DEFINE( 'ANDROID_DEFAULT_CUSTOMIZE_FILES', PUBLIC_UPLOADS_FOLDER . '/android/language_colorcode_default_customize/' );
/****** Alter DB Configuration **********/
DEFINE( 'ALTER_DB_NAME', 'livempty' );
DEFINE( 'ALTER_DB_USER', 'root' );
DEFINE( 'ALTER_DB_PWD', 'KNlZmiaNUp0B' );
DEFINE( 'ALTER_DB_HOST', '192.168.1.118:27018' );
/****** payment Gateway Curl Process url *******/
DEFINE( 'PAYMENT_REDIRECT_HOST', 'www.ndottech.com' );
DEFINE( 'CRM_HOST_URL', 'http://192.168.1.35:1212/CRM_Live/api/PaymentResponse' );
DEFINE( 'CRM_PORT', '1212' );
DEFINE( 'CRM_PAYMENT_AUTHENTICATION_ACCESSKEY','NdotPayment2017DateUpdate');
DEFINE( 'CRM_UPDATE_ENABLE',0);
define( 'CLOUD_EMAIL_VERIFICATION',$result[0]['cloud_email_verification']);

/****** Billing Information **********/
DEFINE( 'CLOUD_SERVICE_TAX', 15 );
DEFINE( 'CLOUD_INVOICE_DUE_DAY', 800 );
DEFINE( 'REPLACE_CONTACT_URL', "##CONTACT_URL##" );
DEFINE( 'REPLACE_PRIVACY_URL', "##PRIVACY_URL##" );
DEFINE( 'REPLACE_FAQ_URL', "##FAQ_URL##" );
//Saas expiry date verification
if (file_exists( DOCROOT . "application/classes/saas/expiry_web.php" ) ) {
    require Kohana::find_file('classes/saas/', 'expiry_web');
}
DEFINE( "REPLACE_BOOKINGID", "##BOOKINGID##" );
DEFINE( "REPLACE_PICKUPDATE", "##PICKUPDATE##" );
DEFINE( "REPLACE_PICKUPLOC", "##PICKUPLOC##" );
DEFINE( "REPLACE_DROPLOC", "##DROPLOC##" );
DEFINE( "REPLACE_DISCOUNT", "##DISCOUNT##" );
DEFINE( "MILES", "miles" );


# IP condition
$ip = Request::$client_ip;
DEFINE( "LOCALIP", $ip );
DEFINE( "STATICIP", '0.0.1.0' );

/* Option to save logs */
DEFINE( "SAVE_LOGS", $result[0]['save_logs']);

/* withdraw request change status */
DEFINE( "REPLACE_WALLET_REQ_ID", "##WALLET_REQ_ID##" );
DEFINE( "REPLACE_WALLET_REQ_AMT", "##WALLET_REQ_AMT##" );
DEFINE( "REPLACE_WALLET_REQ_STATUS", "##WALLET_REQ_STATUS##" );
DEFINE( "REPLACE_WALLET_REQ_APPROVED_BY", "##WALLET_REQ_APPROVED_BY##" );

define( "REPLACE_TYPE", "##TYPE##" );

$country_name = $commonmodel->get_country_name(DEFAULT_COUNTRY );
DEFINE( 'DEFAULT_COUNTRY_NAME', $country_name );
DEFINE( 'CHECK_PROJECT_VERSION', 'V.8.0.2' );
$project_version = isset( $result[0]['project_version'] ) ? $result[0]['project_version'] : '8.0';
DEFINE( 'PROJECT_VERSION', $project_version );
$is_live = isset( $result[0]['is_live'] ) ? $result[0]['is_live'] : 0;
DEFINE( 'IS_LIVE', $is_live );
DEFINE( 'USE_ZONE_BASE_FARE', 1 );

#rental & outstation option
$rental_outstation_status = $result[0]['rental_outstation_status'];
DEFINE( 'RENTAL_OUTSTATION_STATUS', $rental_outstation_status );
$min_outstation = $result[0]['min_outstation'];
DEFINE( 'MIN_OUTSTATION', $min_outstation );
$existing_project_versions = array('V.8.0','V.8.0.2','V.8.1');
DEFINE('PRO_VERSIONS',$existing_project_versions); 

/*if(!defined('SUBDOMAIN_NAME'))
{
DEFINE('SUBDOMAIN_NAME','loadtest');
}*/
DEFINE('WEB_API_KEY', 'FNpfuspyEAzhjfoh2ONpWK0rsnClVL6OCaasqDQtWdI=');
DEFINE('MOBILE_NODE_ENVIRONMENT',$result[0]['socket_service']);

DEFINE( 'RENTAL_AVAILABLE', $result[0]['rental_available_status'] );
DEFINE( 'OUTSTATION_AVAILABLE', $result[0]['outstation_available_status'] );
DEFINE('DEFAULT_BOOKING_LIMIT', 99999);
DEFINE('DEFAULT_ONEWAY_DURATION', $result[0]['default_oneway_trip_duration']);
## show / hide - OTP in front end
DEFINE('HIDEOTP', $result[0]['hide_otp']);
?>
