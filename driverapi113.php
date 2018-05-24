<?php defined('SYSPATH') or die('No direct script access.');

/****************************************************************

* Contains API details - Version 8.0.0

* @Package: Taximobility

* @Author:  NDOT Team

* @URL : http://www.ndot.in

****************************************************************/
Class Controller_Driverapi113 extends Controller_Moddriverapi113
{
public function __construct()
	{	
		$this->session = Session::instance();
		try {
			require Kohana::find_file('classes','mobile_common_config');
			require Kohana::find_file('classes/controller', 'ndotcrypt');
                     
			$this->commonmodel=Model::factory('commonmodel');
			//DEFINE("MOBILEAPI_107","mobileapi118");
			DEFINE("MOBILEAPI_107","driverapi113");
			DEFINE("MOBILEAPI_107_EXTENDED","mobileapi111extended");
			DEFINE("FIND","find114");
			if((COMPANY_CID !='0'))
			{
				$this->app_name = COMPANY_SITENAME;
				$this->siteemail= COMPANY_CONTACT_EMAIL;
				$this->domain_name = SUBDOMAIN;
			}
			else
			{
				$this->siteemail= SITE_EMAIL_CONTACT;				
				$this->app_name = SITE_NAME;
				$this->app_name = preg_replace("/#?[a-z0-9]+;/i","",$this->app_name); // Remove &amp; tag from site name
				$this->domain_name='site';
			}
			
			$this->lang = I18n::lang(LANG);
			$this->app_description=APP_DESCRIPTION;	
			$this->emailtemplate=Model::factory('emailtemplate');
			$this->notification_time = ADMIN_NOTIFICATION_TIME;
			$this->customer_google_api = CUSTOMER_ANDROID_KEY; // For GCM
			$this->continuous_request_time = CONTINOUS_REQUEST_TIME;
			$this->site_currency = CURRENCY;
			//$this->currentdate=Commonfunction::getCurrentTimeStamp();
			# created date
			$this->currentdate = Commonfunction::createdateby_user_timezone();
			
			$this->promo_msg = ['0' => __('referral_code_not_exists'), 
								'-1' => __('invalid_promocode'), 
								'-2' => __('promo_already_used')];
								
			$this->currentdate_bytimezone = Commonfunction::createdateby_user_timezone();
		}
		catch (Database_Exception $e)
		{
			// Insert failed. Rolling back changes...
			$message = array("message" => __('Database Connection Failed'),"status" => 2);
            echo json_encode($message);
			exit;
		}
		
	}		
			
	function action_encrypt_decrypt($action, $string) 
	{							
		$output = false;			
		$key = 'Taxi Application Project';
                
		// initialization vector 		
		$iv = md5(md5($key));		
		if( $action == 'encrypt' ) {		
		  $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, $iv);		
		  $output = base64_encode($string);		
		}		
		else if( $action == 'decrypt' ){		
		  $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);		
		  $output = base64_decode($string);		
		}
		return $output;		
    }
    
	public function action_index()
	{			
		$api = Model::factory(MOBILEAPI_107);	
		$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
		/// We are getting the date from mobile as urlencoded format in POST method
		$mobile_encodeddata =$beforeApi= file_get_contents('php://input');                                             
		$mobile_decryptdata='';
		$additional_param=[];
		$api_key_encrypt='';
		$company_api_key='';
		$method='';                
		
		$method=isset($_REQUEST["type"])?$_REQUEST["type"]:'';
		require Kohana::find_file('classes/controller', 'ndot_trial_mobilekey_validate');
		
		if($method==''){
			$message = array("message" => "Invalid Request ","status" => 2);
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			exit;	
		} 
		// Here we are decode the url encoded values and conver the values in to array
		$mobiledata =  (array)json_decode($mobile_decryptdata,true);
		//$mobiledata =  (array)json_decode($mobile_encodeddata,true);	//To check in simulator witout encryption
		$this->email_lang = isset($_REQUEST["lang"])?$_REQUEST["lang"]:SELECTED_LANGUAGE;
		$errors = array();	
		
		# api logger
		$currentTime=date('Y_m_d_H');
			  if (!file_exists(DOCROOT."application/loc/".$currentTime.".txt"))
			  {
	        		@$newFile= fopen(DOCROOT."application/loc/".$currentTime.".txt", 'w+');	
	        		@fclose($newFile);
	        		@chmod(DOCROOT."application/loc/".$currentTime.".txt", 0777);
	        }
	        if((string)$method=='driver_location_history')
	        {
	        	  @file_put_contents(DOCROOT."application/loc/".$currentTime.".txt","Method ".$method." | ".SUBDOMAIN_NAME." CLayer<br/>". json_encode($mobiledata)."<br/>"."Time is 13123".date('Y-m-d H:i:s')."<br/>"."<br/>" . PHP_EOL, FILE_APPEND);
			  }
	        if (!file_exists(DOCROOT."application/api/".$currentTime.".txt"))
	        {
	        		@$newFile= fopen(DOCROOT."application/api/".$currentTime.".txt", 'w+');	
	        		@fclose($newFile);
	        		@chmod(DOCROOT."application/api/".$currentTime.".txt", 0777);
	        }
			// API Log File
			 if((string)$method!='driver_location_history' && (string)$method!='getpassenger_update')
	        {
	        	if (!file_exists(DOCROOT."application/api/".$currentTime.".txt")){
	        		@$newFile= fopen(DOCROOT."application/api/".$currentTime.".txt", 'w+');	
	        		@fclose($newFile);
	        		@chmod(DOCROOT."application/api/".$currentTime.".txt", 0777);
	        	}
	        	 
	        	@file_put_contents(DOCROOT.'application/api/'.$currentTime.".txt",'<div class="api">Method <b style="color:red;" onclick="return selectElementContents(this);"><div class="method">'.$method." | ".SUBDOMAIN_NAME." CLayer </div></b><br /><button onClick='return verify(event,this,1);'>Verify</button><br />".'Raw Data'."<br />".'<br /><p ><div class="decode">'.json_encode($mobiledata).'</div></p><br /><br />Get Method Params<br /><br />'.json_encode($_GET)."<br /><br />".'PostMan Request'."<br />".'<br /><div class="encode"><p onclick="return selectElementContents(this);">'.$beforeApi."</p></div><br /><br />"."Api : <b>V1</b> Time is ".date('Y-m-d H:i:s')."<br />"."<br /></div>" . PHP_EOL, FILE_APPEND);
	        }
		# api logger end
		
		// Check mobile input param validation
		if(empty($mobiledata) && $mobile_encodeddata!=''){
			 $message = array("message" => __('invalid_request'),"status" => -1);                       
			 $mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			 exit;
		}					
		
		$host  = $_SERVER['SERVER_NAME'];
		$dateStamp = $_SERVER['REQUEST_TIME'];					
		$default_companyid='';
		$company_all_currenttimestamp = $this->commonmodel->getcompany_all_currenttimestamp($default_companyid);

		#language work for api response
		$language_array = WEB_DB_LANGUAGE;
		$posted_language = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : SELECTED_LANGUAGE; 
		$default_customize = isset($language_array[$posted_language])?$language_array[$posted_language]:1;
		$apilanguage = ($default_customize == 1) ? $posted_language.'def' : $posted_language;
		$this->lang = I18n::lang($apilanguage);
		
		switch($method)
		{
			case 'getcoreconfig':
			$_SESSION['session_set'] = 'SITE_NAME';
			//$config_array = $api->select_site_settings($default_companyid);
			$config_array = $api_ext->select_site_settings($default_companyid);
			//print_r($config_array);exit;
			$config_array['app_name'] = SITE_NAME;		
			$config_array['site_country'] = DEFAULT_COUNTRY;
			$config_array['default_city_id'] = DEFAULT_CITY;	
			$config_array['default_city_name'] = DEFAULT_CITY_NAME;		
			$config_array['facebook_key'] = FB_KEY;		
			$config_array['facebook_secretkey'] = FB_SECRET_KEY;		
			$config_array['facebook_share'] = FB_SHARE;		
			$config_array['twitter_share'] = TW_SHARE;		
			$config_array['site_logo'] = SITE_LOGO;
			$utc_time = gmdate('Y-m-d H:i:s');
			$utc_time = strtotime($utc_time);
			$config_array[0]['utc_time'] = $utc_time;
			$config_array[0]['current_time'] = strtotime($this->currentdate_bytimezone);
			$config_array[0]['mobile_socket'] = MOBILE_NODE_ENVIRONMENT;
			$config_array[0]['mobile_socket_url'] = MOBILE_NODE_URL;
			$language_color_status = array();
			if(count($config_array) > 0)
			{
				if($default_companyid == '')
				{
					$config_array[0]['noimage_base'] = URL_BASE.PUBLIC_IMAGES_FOLDER.'no_image109.png';
					$config_array[0]['api_base'] = URL_BASE;
					$config_array[0]['logo_base'] = URL_BASE.'/public/admin/images/';					
					$config_array[0]['aboutpage_description'] = $this->app_description;
					$config_array[0]['admin_email'] = $this->siteemail;
					$config_array[0]['tell_to_friend_subject'] = __('telltofrien_subject');
					$config_array[0]['skip_credit'] = SKIP_CREDIT_CARD;
					$config_array[0]['metric'] = UNIT_NAME;
					$config_array[0]['default_city_id'] = DEFAULT_CITY;	
					$config_array[0]['default_city_name'] = DEFAULT_CITY_NAME;
				}
				else
				{					
					$config_array[0]['noimage_base'] = URL_BASE.PUBLIC_IMAGES_FOLDER.'no_image109.png';
					$config_array[0]['api_base'] = URL_BASE;
					$config_array[0]['site_country'] = "";
					$config_array[0]['logo_base'] = URL_BASE.PUBLIC_UPLOADS_FOLDER.'/site_logo/';	
					$config_array[0]['aboutpage_description'] = $this->app_description;		
					$config_array[0]['admin_email'] = $this->siteemail;	
					$config_array[0]['tell_to_friend_subject'] = __('telltofrien_subject');
					$config_array[0]['skip_credit'] = SKIP_CREDIT_CARD;
					$config_array[0]['metric'] = UNIT_NAME;
					$config_array[0]['default_city_id'] = DEFAULT_CITY;	
					$config_array[0]['default_city_name'] = DEFAULT_CITY_NAME;
				}
				$config_array[0]['share_content'] = __('telltofriend_content');
				$config_array[0]['referral_code_info'] = __('referral_code_info_details');
				$config_array[0]['cancellation_setting'] = CANCELLATION_FARE;
				$config_array[0]['ios_google_map_key'] = IOS_GOOGLE_MAP_API_KEY;
				$config_array[0]['ios_google_geo_key'] = IOS_GOOGLE_GEO_API_KEY;
				$config_array[0]['android_google_api_key'] = ANDROID_GOOGLE_GEO_API_KEY;
				$config_array[0]['google_business_key'] = GOOGLE_BUSINESS_KEY_USED_STATUS;
				$expiry_date = "";
				if(EXPIRY_DATE != "")
				{
					$date = Commonfunction::convertphpdate('Y-m-d H:i:s',EXPIRY_DATE);
					$expiry_date = Commonfunction::getDateTimeFormat($date,1);
				}
				$config_array[0]['domain_expiry_date'] = $expiry_date;
				$referral_settings = 0;		
				$referral_settings_message = __("referral_settings_message");
				if(REFERRAL_SETTINGS == 1) {
					$referral_settings = 1;
					$referral_settings_message = "";
				} else {
					$referral_settings = 0;
					$referral_settings_message = __("referral_settings_message");
				}
				$config_array[0]['referral_settings'] = $referral_settings;
				$config_array[0]['referral_settings_message'] = $referral_settings_message;
				
				$driverReferralSettings = 0;		
				$driverRefSettingsMsg = __("referral_settings_message");		
				if(DRIVER_REFERRAL_SETTINGS == 1) {		
					$driverReferralSettings = 1;		
					$driverRefSettingsMsg = "";		
				}		
				$config_array[0]['driver_referral_settings'] = $driverReferralSettings;		
				$config_array[0]['driver_referral_settings_message'] = $driverRefSettingsMsg;	
				$config_array[0]['android_passenger_version'] = ANDROID_PASSENGER_VERSION;
				$config_array[0]['android_driver_version'] = ANDROID_DRIVER_VERSION;	
				$config_array[0]['country_code'] = TELEPHONECODE;
				$config_array[0]['country_iso_code'] = ISO_COUNTRY_CODE;
				$config_array[0]['tax'] = TAX;

				/***Get Company car model details start***/
				//$company_model_details = $api->company_model_details($default_companyid);
				$company_model_details = $api_ext->company_model_details($default_companyid);
			//	echo "<pre>"; print_r($company_model_details);exit;
				if(count($company_model_details)>0) {
					foreach($company_model_details as $key => $val) {
						if(!empty($va)){
							if(file_exists($_SERVER["DOCUMENT_ROOT"].'/'.PUBLIC_UPLOADS_FOLDER.'/model_image/android/'.$val["model_id"].'_focus.png')) {
								$focus_image = URL_BASE.PUBLIC_UPLOADS_FOLDER.'/model_image/android/'.$val['model_id'].'_focus.png';
								$company_model_details[$key]["focus_image"] = $focus_image;
							}
							if(file_exists($_SERVER["DOCUMENT_ROOT"].'/'.PUBLIC_UPLOADS_FOLDER.'/model_image/android/'.$val["model_id"].'_unfocus.png')) {
								$unfocus_image = URL_BASE.PUBLIC_UPLOADS_FOLDER.'/model_image/android/'.$val['model_id'].'_unfocus.png';
								$company_model_details[$key]['unfocus_image'] = $unfocus_image;
							}
							if(file_exists($_SERVER["DOCUMENT_ROOT"].'/'.PUBLIC_UPLOADS_FOLDER.'/model_image/ios/'.$val["model_id"].'_focus.png')) {
								$focus_image_ios = URL_BASE.PUBLIC_UPLOADS_FOLDER.'/model_image/ios/'.$val['model_id'].'_focus.png';
								$company_model_details[$key]["focus_image_ios"] = $focus_image_ios;
							}
							if(file_exists($_SERVER["DOCUMENT_ROOT"].'/'.PUBLIC_UPLOADS_FOLDER.'/model_image/ios/'.$val["model_id"].'_unfocus.png')) {
								$unfocus_image_ios = URL_BASE.PUBLIC_UPLOADS_FOLDER.'/model_image/ios/'.$val['model_id'].'_unfocus.png';
								$company_model_details[$key]["unfocus_image_ios"] = $unfocus_image_ios;
							}
							if($val["model_id"] == 10 && file_exists($_SERVER["DOCUMENT_ROOT"].'/'.PUBLIC_UPLOADS_FOLDER.'/model_image/android/10_focus.svg')) {
								$svg_focus_image = URL_BASE.PUBLIC_UPLOADS_FOLDER.'/model_image/android/10_focus.svg';
								$company_model_details[$key]["focus_image_svg"] = $svg_focus_image;
							}
						}
					}
					$config_array[0]['model_details'] = $company_model_details;
				}else{ 
					$config_array[0]['model_details']=__('model_details_not_found');
				}
				$gateway_details = $this->commonmodel->gateway_details($default_companyid);

				$gateway_array = array(); $passenger_payment_option = array();
				foreach($gateway_details as $valArr) {
					$gateway_array[] = $valArr;
					if($valArr["pay_mod_id"] != 3) {
						$passenger_payment_option[] = $valArr;
						if(SKIP_CREDIT_CARD == 0)
							break;
					}
				}
				$config_array[0]['app_name'] = preg_replace("/#?[a-z0-9]+;/i","",SITE_NAME);
				$config_array[0]['gateway_array'] = $gateway_array;
				$config_array[0]['passenger_payment_option'] = $passenger_payment_option;
				$config_array[0]['Itune_Passenger'] = (isset($config_array[0]['itune_passenger']) && ($config_array[0]['itune_passenger'] !='')) ? $config_array[0]['itune_passenger'] :'';
				
				$config_array[0]['Itune_Driver'] = (isset($config_array[0]['itune_driver']) && ($config_array[0]['itune_driver'] !='')) ? $config_array[0]['itune_driver'] :'';
				
				$config_array[0]['Fb_Profile'] = (isset($config_array[0]['fb_profile']) && ($config_array[0]['fb_profile'] !='')) ? $config_array[0]['ios_foursquare_api_key'] :'';				
                                
				$config_array[0]['site_currency'] = CURRENCY;
				/***Get Company car model details end***/
				$details_arr = $config_array[0];
				# iOS
				$language_color_status['ios_driver_language'] = isset($config_array[0]['ios_driver_language']) ? $config_array[0]['ios_driver_language'] :'';
				$language_color_status['ios_passenger_language'] = isset($config_array[0]['ios_passenger_language']) ? $config_array[0]['ios_passenger_language'] :'';
				$language_color_status['ios_driver_colorcode'] = isset($config_array[0]['ios_driver_colorcode']) ? $config_array[0]['ios_driver_colorcode'] :'';
				$language_color_status['ios_passenger_colorcode'] = isset($config_array[0]['ios_passenger_colorcode']) ? $config_array[0]['ios_passenger_colorcode'] :'';
				# android
				$language_color_status['android_driver_language'] = isset($config_array[0]['android_driver_language']) ? $config_array[0]['android_driver_language'] :'';
				$language_color_status['android_passenger_language'] = isset($config_array[0]['android_passenger_language']) ? $config_array[0]['android_passenger_language'] :'';
				$language_color_status['android_passenger_colorcode'] = isset($config_array[0]['android_passenger_colorcode']) ? $config_array[0]['android_passenger_colorcode'] :'';
				$language_color_status['android_driver_colorcode'] = isset($config_array[0]['android_driver_colorcode']) ? $config_array[0]['android_driver_colorcode'] :'';
				# four square settings
				$config_array[0]['android_foursquare_api_key'] = (isset($config_array[0]['android_foursquare_api_key']) && ($config_array[0]['android_foursquare_api_key'] !='')) ? $config_array[0]['android_foursquare_api_key'] :'0';
				
				$config_array[0]['android_foursquare_status'] = (isset($config_array[0]['android_foursquare_status']) && ($config_array[0]['android_foursquare_status'] !='')) ? $config_array[0]['android_foursquare_status'] :'0';
				
				$config_array[0]['ios_foursquare_api_key'] = (isset($config_array[0]['ios_foursquare_api_key']) && ($config_array[0]['ios_foursquare_api_key'] !='')) ? $config_array[0]['ios_foursquare_api_key'] :'0';				
				
				$config_array[0]['ios_foursquare_status'] = (isset($config_array[0]['ios_foursquare_status']) && ($config_array[0]['ios_foursquare_status'] !='')) ? $config_array[0]['ios_foursquare_status'] :'0';
			
				# unset detail array
				unset($details_arr['ios_driver_language']);
				unset($details_arr['ios_passenger_language']);
				unset($details_arr['ios_driver_colorcode']);
				unset($details_arr['ios_passenger_colorcode']);
				unset($details_arr['android_driver_language']);
				unset($details_arr['android_passenger_language']);
				unset($details_arr['android_passenger_colorcode']);
				unset($details_arr['android_driver_colorcode']);
				$message = array("message" =>__('success'),"detail" => array($details_arr),"status" => 1);
				$message['language_color_status'] = $language_color_status;
				# language color codes loading
				
				$device_arr = array(1,2); # 1 -Android, 2 - iOS
				$language_color = array();
				$type=$path_type='';
				foreach($device_arr as $d){	
					$folderPath = MOBILE_iOS_IMAGES_FILES;
					$type = 'iOS';
					$path_type = 'iOSPaths';
					if($d == 1){
						$folderPath = MOBILE_ANDROID_IMAGES_FILES;
						$type = 'android';
						$path_type = 'androidPaths';
					}
					
					$dateStamp = $_SERVER['REQUEST_TIME'];
					$iOSPathArr = array();
					# dynamic language array
					$dynamic_language_array = array('en' => 'english');
					if(defined('DYNAMIC_LANGUAGE_ARRAY'))
						$dynamic_language_array = DYNAMIC_LANGUAGE_ARRAY;
						
					$iOSPassengerLanguageDOC = DOCROOT.$folderPath."language/passenger/";
					$iOSDriverLanguageDOC = DOCROOT.$folderPath."language/driver/";
					$iOSPassengerLanguageVIEW = URL_BASE.$folderPath."language/passenger/";				
					$iOSDriverLanguageVIEW = URL_BASE.$folderPath."language/driver/";	
					$iOSColorCode = URL_BASE.$folderPath."colorcode/PassengerAppColor.xml?timeCache=".$dateStamp;
					$iOSDriverColorCode = URL_BASE.$folderPath."colorcode/DriverAppColor.xml?timeCache=".$dateStamp;	
					# android color codes
					$androidPassColorcode = URL_BASE.$folderPath."colorcode/passengerAppColors.xml?timeCache=".$dateStamp;
					$androidDriverColorcode = URL_BASE.$folderPath."colorcode/driverAppColors.xml?timeCache=".$dateStamp;
					if(defined('STATIC_LANGUAGE_ARRAY')){
						$staticLanguArr = array_flip(STATIC_LANGUAGE_ARRAY);
					}else{
						$staticLanguArr = array("english"=>"en","turkish"=>"tr","arabic"=>"ar","german"=>"de","russian"=>"ru","spanish"=>"es");
					}
					//~ echo '<pre>';print_r($staticLanguArr);exit;
					# Passenger Language Files
					$passLangFiles  = opendir($iOSPassengerLanguageDOC);
					$passLangs = array();
					while (false !== ($filename = readdir($passLangFiles))) {
						if($filename != '.' && $filename != '..'){
							$langArr = explode('_',$filename);
							if(isset($langArr[1]))
							$langName =  ($d == 2) ? str_replace('.strings','',$langArr[1]) : str_replace('.xml','',$langArr[1]);								
							$designType = 'LTR';								
							$checkRTL = strtolower($langName);	
							if(in_array($checkRTL,$dynamic_language_array)){								
								if($checkRTL == "arabic" || $checkRTL == "urdu") {								
									$designType = 'RTL';		
								}
								$langType = isset($staticLanguArr[$checkRTL]) ? $staticLanguArr[$checkRTL]:'';		
								$fileNam = $filename."?timeCache=".$dateStamp;		
								$langFilesArr = array("language"=>$langName,"design_type"=>$designType,"language_code"=>$langType,"url"=>$iOSPassengerLanguageVIEW.$fileNam);
								$passLangs[] = $langFilesArr;
							}
						}
					}

					# Driver Language Files
					$driverLangFiles  = opendir($iOSDriverLanguageDOC);
					$driverLangs = array();
					while (false !== ($driverFilename = readdir($driverLangFiles))) {
						if($driverFilename != '.' && $driverFilename != '..'){
							$driverLangArr = explode('_',$driverFilename);
							if(isset($driverLangArr[1]))
							$driverLangName =  ($d == 2) ? str_replace('.strings','',$driverLangArr[1]) : str_replace('.xml','',$driverLangArr[1]);
							$designType = 'LTR';
							$checkRTL = strtolower($driverLangName);
							if(in_array($checkRTL,$dynamic_language_array)){		
								if($checkRTL == "arabic" || $checkRTL == "urdu") {		
									$designType = 'RTL';		
								}		
								$langType = isset($staticLanguArr[$checkRTL]) ? $staticLanguArr[$checkRTL]:'';		
								$driverFileName = $driverFilename."?timeCache=".$dateStamp;		
								$driverLangFilesArr = array("language"=>$driverLangName,"design_type"=>$designType,"language_code"=>$langType,"url"=>$iOSDriverLanguageVIEW.$driverFileName);
								$driverLangs[] = $driverLangFilesArr;
							}
						}
					}
				
					$iOSPathArr = array("driver_language"=>$driverLangs,"passenger_language"=>$passLangs,"colorcode"=>$iOSColorCode,"driverColorCode"=>$iOSDriverColorCode);
					# android color code	
					if($d == 1){				
						$iOSPathArr["colorcode"] = $androidPassColorcode;
						$iOSPathArr["driverColorCode"] = $androidDriverColorcode;
					}
					
					$language_color[$type] =  $iOSPathArr;
				}				
				$message['language_color'] = $language_color;
				// Socket Reconnect Attempt
				$message['reconnect_socket'] = 1;
				
				
			}
			else
			{
				$message = array("message" => __('failed'),"status" => 2);
			}
			$additional_param['json_unescaped_unicode_string']=JSON_UNESCAPED_UNICODE;
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$details_arr,$config_array,$company_model_details,$gateway_details);
			break;

			case 'get_authentication':
				$host  = $_SERVER['SERVER_NAME'];
				$dateStamp = $_SERVER['REQUEST_TIME'];
				$mobilehost = isset($mobiledata['mobilehost'])?strtolower($mobiledata['mobilehost']):''; 
				if(isset($mobilehost)){
					if($mobilehost == $host){
						$value = $host."-".$dateStamp;
						//New version Header Authorization checking
						if(isset($headers['Authorization'])){ 
							$encode =  $mobile_data_ndot_crypt->encrypt_encode($value);
						}
						
						$message = array("message" =>__('success'),"encode" => $encode,"status" => 1);
					} else {
						$message = array("message" => "Host does not match","status" => 2);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						break;
					}
				} else {
					$message = array("message" => " Invalid Host Request ","status" => 2);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					break; 
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message);
			break;

			case 'tripfare_update':
				$array = $mobiledata;
				$api_model = Model::factory(MOBILEAPI_107);
				$extended_api = Model::factory(MOBILEAPI_107_EXTENDED);
			#	$booking_fee = $array['booking_fee'];
			    	$booking_fee = isset($array['booking_fee'])?$array['booking_fee']:'';	
				$pay_mod_id = $array['pay_mod_id'];
				if($pay_mod_id == '1' ||  $pay_mod_id == '2' ||  $pay_mod_id == '4' ||  $pay_mod_id == '5')
				{
					$validator = $this->payment_validation($array);
				}
				else
				{
					$validator = $this->payment_validationwith_card($array);
				}
				$driver_statistics=array();
				if($validator->check())
				{
					$passenger_log_id = $array['trip_id'];
					$trip_type = isset($array['trip_type'])?$array['trip_type']:'1';
					$system_fare_details = array();
					if($trip_type == '3')
					{
						/* Outstation fare update */
						$distance = isset($array['os_distance'])?$array['os_distance']:$array['distance'];
						$actual_distance = isset($array['os_distance'])?$array['os_distance']:$array['actual_distance'];
						$actual_amount = isset($array['os_actual_amount'])?$array['os_actual_amount']:0;
						$trip_fare = isset($array['os_actual_amount'])?$array['os_actual_amount']:0;
						$minutes_traveled=isset($array['os_minutes_traveled'])?$array['os_minutes_traveled']:0;
						$minutes_fare=isset($array['os_minutes_fare'])?$array['os_minutes_fare']:0;
						$promodiscount_amount = isset($array['os_promodiscount_amount'])?$array['os_promodiscount_amount']:0;
						$fare = isset($array['os_actual_amount'])?$array['os_actual_amount']:0;
						$base_fare=isset($array['os_actual_amount'])?$array['os_actual_amount']:0;
						$system_fare_details = array(
							'system_distance' => ($array['actual_distance'] == "")?$array['actual_distance']:$array['distance'],
							'system_actual_amount' => $array['actual_amount'],
							'system_trip_fare' => $array['trip_fare'],
							'system_promodiscount_amount' => $array['promodiscount_amount'],
							'system_minutes_traveled' => $array['minutes_traveled'],
							'system_minutes_fare' => $array['minutes_fare']
						);
						/* Outstation fare update */
					} else {
						if($array['actual_distance'] == "")
						{
							$distance = $array['distance'];
						} else {
							$distance = $array['actual_distance'];
						}
						$actual_distance = $array['actual_distance'];
						$trip_fare = $array['trip_fare']; // Trip Fare without Tax,Tips and Discounts
						$actual_amount = $array['actual_amount'];
						$minutes_traveled=$array['minutes_traveled'];
						$minutes_fare=$array['minutes_fare'];
						$promodiscount_amount = $array['promodiscount_amount'];
						$fare = $array['fare']; // Total Fare with Tax,Tips and Discounts can editable by driver
						$base_fare=$array['base_fare'];
					}
					$remarks = $array['remarks'];
					$tips = $array['tips']; // Tips Optional
					$nightfare_applicable = $array['nightfare_applicable'];
					$nightfare = $array['nightfare'];
					$eveningfare_applicable = $array['eveningfare_applicable'];
					$eveningfare = $array['eveningfare'];
					$tax_amount = $array['tax_amount'];
					$tax_percentage = $array['company_tax'];
					$erp_charge = isset($array['erp_charge'])? $array['erp_charge']: 0;
					
					$fare_calculation_type = isset($array['fare_calculation_type']) ? $array['fare_calculation_type'] : FARE_CALCULATION_TYPE;
					$model_fare_type = isset($array['model_fare_type']) ? $array['model_fare_type'] :2;//2 for not calculated based on km wise fare
					
					// Actual amount means if any deviations in trip fare driver will update it manualy but now this is not required.
					$trip_fare = $trip_fare;
					//~ $total_fare = $fare;
					$total_fare = $fare;
				
					$wallet_cash = ($total_fare == '0') ? 0 : 1;
					$amount = $total_fare; // Total amount which is used for pass to payment gateways
					$formated_amount = commonfunction::amount_indecimal($amount,'api');
					$get_passenger_log_details = $api_ext->get_passenger_log_detail($passenger_log_id);
					$pre_transaction_id = "";
					$pre_authorize_amount="";
					if(count($get_passenger_log_details) > 0)
					{
						$promocode = $get_passenger_log_details[0]->promocode;
						$driver_register_type = isset($get_passenger_log_details[0]->driver_register_type) ? $get_passenger_log_details[0]->driver_register_type:'1';//default 1 as commission
						$passenger_id = isset($get_passenger_log_details[0]->passengers_id) ? $get_passenger_log_details[0]->passengers_id:'';
						if($promocode != ''){
							$this->commonmodel->promocode_used_update($promocode,$passenger_id);
						}					
							
						$pre_transaction_id = isset($get_passenger_log_details[0]->pre_transaction_id) ? $get_passenger_log_details[0]->pre_transaction_id : "";
						$pre_authorize_amount=isset($get_passenger_log_details[0]->pre_transaction_amount) ? $get_passenger_log_details[0]->pre_transaction_amount : "";
						$default_unit = ($get_passenger_log_details[0]->default_unit == 0) ? "KM":"MILES";
						$default_unit = (FARE_SETTINGS == 2) ? $default_unit : UNIT_NAME;
						$flag = 1;
						$trans_result = $api_ext->check_tranc($passenger_log_id,$flag);
						$drivers_id = $get_passenger_log_details[0]->driver_id;
						if($trans_result == 1)
						{
							/********** Update Driver Status after complete Payments *****************/
							$drivers_id = $get_passenger_log_details[0]->driver_id;
							$update_driver_array  = array(
								'status' => 'F',
								'driver_id'=>$drivers_id
								);
							$result = $extended_api->update_driver_location($update_driver_array);
							/************Update Driver Status ***************************************/
							$message_status = 'R';$driver_reply='A';$journey_status=1; // Waiting for Payment
							$journey = $api->update_journey_status($passenger_log_id,$message_status,$driver_reply,$journey_status);
							/*************** Update in driver request table ******************/
							$update_trip_array  = array(
								'status' => 8,
								'trip_id'=>$passenger_log_id
								);
							$result = $extended_api->update_driver_request_details($update_trip_array);
							/*************************************************************************/	
							if(count($get_passenger_log_details) > 0)
							{
								$default_companyid = isset($get_passenger_log_details[0]->company_id) ? $get_passenger_log_details[0]->company_id : $default_companyid;
								// Driver Statistics ********************/
								$driver_logs_rejected = $api->get_rejected_drivers($drivers_id,$default_companyid);	
								$rejected_trips = count($driver_logs_rejected);	
								$driver_cancelled_trips = $api->get_driver_cancelled_trips($drivers_id,$default_companyid);
								$driver_earnings = $api->get_driver_earnings_with_rating($drivers_id,$default_companyid);
								$driver_tot_earnings = $api->get_driver_total_earnings($drivers_id);
								$driver_statistics = array();
								$total_trip = $trip_total_with_rate = $total_ratings = $today_earnings = $total_amount=0;

								foreach($driver_earnings as $stat){
									$total_trip++;
									$total_ratings += $stat['rating'];
									$total_amount += $stat['total_amount'];
								}
								$overall_trip = $total_trip + $rejected_trips + $driver_cancelled_trips;
								$time_driven = $api->get_time_driven($drivers_id,'R','A','1');
								$driver_statistics = array( 
									"total_trip" => $overall_trip,
									"completed_trip" => $total_trip,
									"total_earnings" => commonfunction::amount_indecimal($driver_tot_earnings,'api'),
									"overall_rejected_trips" => $rejected_trips,
									"cancelled_trips" => $driver_cancelled_trips,
									"today_earnings"=>round($total_amount,2),
									"shift_status"=>'IN',
									"time_driven"=>$time_driven,
									"status"=> 1
								);
							}
							else
							{
								$driver_statistics=array();
							}
							//Driver Statistics Functionality End
							$message = array("message" => __('trip_fare_already_updated'), "status"=>-1);
							$message['driver_statistics']=$driver_statistics;
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							break;
						}
						if($array['pay_mod_id'] == 1 || $array['pay_mod_id'] == 5)//5 for wallet payment
						{
							/** Return payment using payment gateway **/
							if($pre_transaction_id != "") 
							{     
								// Payment gateway void transaction
								$paymentresponse =[];
								$code=0;

								if (class_exists('Paymentgateway')) 
								{
									$void_amount=['preTransactAmount'=>$pre_authorize_amount];
									$paymentresponse = Paymentgateway::payment_gateway_connect('void',$pre_transaction_id,$void_amount);
									$payment_status=$paymentresponse['payment_status'];

								} else {
									trigger_error("Unable to load class: Paymentgateway", E_USER_WARNING);
								}
									
							}
							//Inserting to Transaction Table 
							
							
							$commission_fare=$total_fare-$erp_charge;
							
							try {
								$paymentType = $array['pay_mod_id'];
								//if the package is enterprise and driver is in subscription plan,then admin,company and driver commission is zero because the entire trip amount is for driver
								if((PACKAGE_TYPE == 3 || PACKAGE_TYPE == 0) && $driver_register_type == '2'){
									$update_commission = array();
									$update_commission['admin_commission']   = 0;
									$update_commission['company_commission'] = 0;
									$update_commission['driver_commission'] = 0;
									$update_commission['payments_to_company'] = 0;
									$update_commission['payments_to_driver'] = 0;
									$update_commission['trans_packtype'] = 'T';
								} else{
									$update_commission = $this->commonmodel->update_commission($passenger_log_id,$commission_fare,ADMIN_COMMISSON,$paymentType,$promodiscount_amount);	
								}
								/*if($array['pay_mod_id'] == 5) {
									$update_commission = $this->commonmodel->update_commission($passenger_log_id,$total_fare,ADMIN_COMMISSON,$paymentType);
								} else {
									$update_commission = $this->commonmodel->update_commission($passenger_log_id,$total_fare,ADMIN_COMMISSON,$paymentType);
								}*/
								$insert_array = array(
									"passengers_log_id" => (int)$passenger_log_id,
									"distance" 			=> (double)urldecode($distance),
									"actual_distance" 	=> (double)urldecode($actual_distance),
									"distance_unit" 	=> $default_unit,
									"tripfare"			=> (double)$trip_fare,
									"fare" 				=> (double)$fare,
									"tips" 				=> $tips,
									"waiting_cost"		=> (double)$array['waiting_cost'],
									"passenger_discount"=> (double)$array['passenger_discount'],
									"promo_discount_fare"=> (double)$promodiscount_amount,
									"tax_percentage"	=> (double)$tax_percentage,
									"company_tax"		=> (double)$tax_amount,
									"waiting_time"		=> urldecode($array['waiting_time']),
									"trip_minutes"		=> $minutes_traveled,
									"minutes_fare"		=> (double)$minutes_fare,
									"base_fare"			=> (double)$base_fare,
									"remarks"			=> $remarks,
									"payment_type"		=> $array['pay_mod_id'],
									"amt"				=> (double)$amount,
									"nightfare_applicable" => $nightfare_applicable,
									"nightfare" 		=> (double)$nightfare,
									"eveningfare_applicable" => $eveningfare_applicable,
									"eveningfare" 		=> (double)$eveningfare,
									"admin_amount"		=> (double)$update_commission['admin_commission'],
									"company_amount"	=> (double)$update_commission['company_commission'],
									"driver_amount"		=> (double)$update_commission['driver_commission'],
									"payments_to_company"		=> (double)$update_commission['payments_to_company'],
									"payments_to_driver"		=> (double)$update_commission['payments_to_driver'],
									"trans_packtype"	=> $update_commission['trans_packtype'],
									"fare_calculation_type"	=> $fare_calculation_type,
									"model_fare_type"	=> $model_fare_type,
									"erp_charge"	=> $erp_charge,
									"booking_fee"	=> $booking_fee,
									"trip_type"	=> $trip_type,
								);
								
								if($trip_type == '3' || $trip_type == 3)
								{
									$insert_array['system_fare_details'] = $system_fare_details;
								}
								// echo '<pre>'; print_r($insert_array); exit;
								$check_trans_already_exist = $api->checktrans_details($passenger_log_id);
								if(count($check_trans_already_exist)>0)
								{
									$tranaction_id = $check_trans_already_exist[0]['id'];
									$update_transaction = $extended_api->update_transaction_table($insert_array,$tranaction_id);
									$jobreferral = $tranaction_id;
								}
								else
								{
									$transaction = $extended_api->insert_transaction_table($insert_array);
									$jobreferral = $transaction;
								}
							
								# update entry in wallet log for wallet payment
								/*if($array['pay_mod_id'] == 5){
									$wallet_fieldArr = array("passenger_id",
														"amount",
														"payment_status",
														"payment_type",
														"credit_debit");
									$wallet_valueArr = array($passenger_id, (float)$trip_fare,1,0,2);
									$api->add_wallet_log($wallet_fieldArr, $wallet_valueArr);
								}*/
							
								/********** Update Driver Status after complete Payments *****************/
								$drivers_id = $get_passenger_log_details[0]->driver_id;
								$update_driver_array  = array(
									'status' => 'F',
									'driver_id'=>$drivers_id
								);
								$result = $extended_api->update_driver_location($update_driver_array);
								/************Update Driver Status ***************************************/
								/*************** Update in driver request table ******************/
								$update_driver_request_array  = array(
									'status' => 8,
									'trip_id'=>$passenger_log_id
								);
								$result = $extended_api->update_driver_request_details($update_driver_request_array);
								/*************************************************************************/
								$pickup = $get_passenger_log_details[0]->current_location;
								if(SMS == 1)
								{
									$passenger_phone_no = $get_passenger_log_details[0]->passenger_country_code.$get_passenger_log_details[0]->passenger_phone;
									$message_details = $this->commonmodel->sms_message_by_title('payment_confirmed_sms');
									if(count($message_details) > 0) 
									{
										$to = $passenger_phone_no;
										$message = $message_details[0]['sms_description'];
										$message = str_replace("##booking_key##",$passenger_log_id,$message);
										$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
										$this->commonmodel->send_sms($to,$message);
									}
								}
								/* create user logs */
						        $user_unique = $get_passenger_log_details[0]->driver_id.__('log_driver_type');
						        $log_array = array(
					                'user_id' => (int)$get_passenger_log_details[0]->driver_id,
					                'user_type' => __('log_driver_type'),
					                'login_type' => __('log_device'),
					                'activity' => __('log_tripfare_update'),
					                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
					            );
						        commonfunction::save_user_logs($log_array, $user_unique);
						        /* create user logs */
								$detail = array("fare" => commonfunction::amount_indecimal($amount,'api'),"pickup" => $pickup,"jobreferral"=>$jobreferral,"trip_id"=>$passenger_log_id);
								$message = array("message" => __('trip_fare_updated'),"detail"=>$detail,"status"=>1);
								$pushmessage = array("message" => __('trip_fare_updated'),"fare" => $amount,"trip_id"=>$passenger_log_id,"pickup" => $pickup, "status"=>5);
								$send_mail_status = $this->send_mail_passenger($passenger_log_id,1);
							}
							catch (Kohana_Exception $e) {
								$message = array("message" => __('trip_fare_already_updated'), "status"=>-1);
							}
						}
						else if($array['pay_mod_id'] == 2)
						{
							$passengers_id = $get_passenger_log_details[0]->passengers_id;
							$card_type = '';
							$default = 'yes';
							$carddetails = $api_ext->get_creadit_card_details($passengers_id,$card_type,$default);
						
							 if(count($carddetails)>0)
							 {
								$creditcard_no = encrypt_decrypt('decrypt',$carddetails[0]['creditcard_no']);
								//~ $creditcard_cvv = 456;
								$expmonth = $carddetails[0]['expdatemonth'];
								$expyear = $carddetails[0]['expdateyear'];
								
								if($creditcard_no != "")
								{
									$array['default_unit'] = $default_unit;
									list($payment_status,$payment_response) = $this->trippayment($array,$default_companyid);//$account_id
									if($payment_status == 0)
									{
										$gateway_response = isset($payment_response)?$payment_response:'Payment Failed';
										$message = array("message" => $gateway_response, "gateway_response" =>$gateway_response,"status"=>0);
									}
									else if($payment_status == 3)
									{
										$message = array("message" => __('gve_credit_card_details'), "status"=>-2);
									}
									else if($payment_status == 1)
									{
										$formated_amount = commonfunction::amount_indecimal($amount,'api');
										//if the pack is enterprise and driver is in subscription then the card payment amount should be added to driver wallet
										if((PACKAGE_TYPE == 3 || PACKAGE_TYPE == 0) && $driver_register_type == '2'){
											$api->update_driver_wallet($drivers_id,$formated_amount);

										}

										$tranaction_id = "";
										$check_trans_already_exist = $api->checktrans_details($passenger_log_id);
										if(count($check_trans_already_exist)>0)
										{
											$tranaction_id = $check_trans_already_exist[0]['id'];
										}
										$jobreferral = $tranaction_id;
										$pickup = $get_passenger_log_details[0]->current_location;
										$detail = array("fare" =>$formated_amount,
														"pickup" => $pickup,"jobreferral"=>$jobreferral,"trip_id"=>$passenger_log_id);
										$message = array("message" => __('trip_fare_updated'), "detail" => $detail,"status"=>1);	
										$pushmessage = array("message" => __('trip_fare_updated'),"fare" => $amount,"trip_id"=>$passenger_log_id,"pickup" => $pickup, "status"=>5);
										/*************** Update in driver request table ******************/
										$update_driver_request_array  = array(
											'status' => 8,
											'trip_id'=>$passenger_log_id
										);
										/* create user logs */
								        $user_unique = $get_passenger_log_details[0]->driver_id.__('log_driver_type');
								        $log_array = array(
							                'user_id' => (int)$get_passenger_log_details[0]->driver_id,
							                'user_type' => __('log_driver_type'),
							                'login_type' => __('log_device'),
							                'activity' => __('log_tripfare_update'),
							                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
							            );
								        commonfunction::save_user_logs($log_array, $user_unique);
								        /* create user logs */
										$result = $extended_api->update_driver_request_details($update_driver_request_array);
									}
									else if($payment_status == -1)
									{
										$message = array("message" => __('invalid_trip'),"status"=>-1);	
									}
									else if($payment_status == 7)
									{
										$message = array("message" => __('no_payment_gateway'),"status"=>-1);	
									}
								}
								else
								{
									$message = array("message" => __('no_creditcard'),"status"=>-9);
								} 
							}		
							else
							{		 								
								$message = array("message" => __('no_card'),"status"=>-9);
							}
						}
						else if($array['pay_mod_id'] == 3)
						{
							$creditcard_no = $array['creditcard_no'];
							$creditcard_cvv = $array['creditcard_cvv'];
							$expmonth = $array['expmonth'];
							$expyear = $array['expyear'];
							$authorize_status = $extended_api->isVAlidCreditCard($creditcard_no,"",true);
							if($authorize_status == 1)
							{
								$array['default_unit'] = $default_unit;
								list($payment_status,$payment_response) = $this->trippayment($array,$default_companyid);//$account_id
								if($payment_status == 0)
								{
									$gateway_response = isset($payment_response)?$payment_response:'Payment Failed';
									$message = array("message" => $gateway_response, "gateway_response" =>$gateway_response,"status"=>0);		
								}				
								else if($payment_status == 3)
								{
									$message = array("message" => __('gve_credit_card_details'), "status"=>-2);
								}
								else if($payment_status == 1)
								{
									$tranaction_id = "";
									//if the pack is enterprise and driver is in subscription then the card payment amount should be added to driver wallet
									if((PACKAGE_TYPE == 3 || PACKAGE_TYPE == 0) && $driver_register_type == '2'){
										$api->update_driver_wallet($drivers_id,$formated_amount);

									}

									$check_trans_already_exist = $api->checktrans_details($passenger_log_id);
									if(count($check_trans_already_exist)>0)
									{
										$tranaction_id = $check_trans_already_exist[0]['id'];
									}
									
									$jobreferral = $tranaction_id;
									$pickup = $get_passenger_log_details[0]->current_location;
									$detail = array("fare" => commonfunction::amount_indecimal($amount,'api'),"pickup" => $pickup,"jobreferral"=>$jobreferral,"trip_id"=>$passenger_log_id);
									$message = array("message" =>  __('trip_fare_updated'), "detail" => $detail,"status"=>1);	
									$pushmessage = array("message" => __('trip_fare_updated'),"fare" => $amount,"trip_id"=>$passenger_log_id,"pickup" => $pickup, "status"=>5);
									/*************** Update in driver request table ******************/
									$update_driver_request_array  = array(
										'status' => 8,
										'trip_id'=>$passenger_log_id
									);
									/* create user logs */
							        $user_unique = $get_passenger_log_details[0]->driver_id.__('log_driver_type');
							        $log_array = array(
						                'user_id' => (int)$get_passenger_log_details[0]->driver_id,
						                'user_type' => __('log_driver_type'),
						                'login_type' => __('log_device'),
						                'activity' => __('log_tripfare_update'),
						                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
						            );
							        commonfunction::save_user_logs($log_array, $user_unique);
							        /* create user logs */
									$result = $extended_api->update_driver_request_details($update_driver_request_array);
									/*************************************************************************/								
									/** Send Trip fare details to Driver ***/
									$d_device_token = $get_passenger_log_details[0]->driver_device_token;
									$d_device_type = $get_passenger_log_details[0]->driver_device_type;
									$p_device_token = $get_passenger_log_details[0]->passenger_device_token;
									$p_device_type = $get_passenger_log_details[0]->passenger_device_type;
								}
								else if($payment_status == -1)
								{
									$message = array("message" => __('invalid_trip'),"status"=>-1);	
								}
							}
							else
							{
								$message = array("message" => __('invalid_card'),"status"=>-9);
							}
						}
						
						//Driver Statistics Functionality Start
						$driver_id = $get_passenger_log_details[0]->driver_id;
						$default_companyid = isset($get_passenger_log_details[0]->company_id) ? $get_passenger_log_details[0]->company_id : $default_companyid;
						// Driver Statistics ********************/
						$driver_logs_rejected = $api->get_rejected_drivers($driver_id,$default_companyid);	
						$rejected_trips = count($driver_logs_rejected);	
						$driver_cancelled_trips = $api->get_driver_cancelled_trips($driver_id,$default_companyid);
						$driver_earnings = $api->get_driver_earnings_with_rating($driver_id,$default_companyid);
						$statistics = array();
						$total_trip = $trip_total_with_rate = $total_ratings = $today_earnings = $total_amount=0;
						foreach($driver_earnings as $stat){
								$total_trip++;
								$total_ratings += $stat['rating'];
								$total_amount += $stat['total_amount'];											
						}
						
						$overall_trip = $total_trip + $rejected_trips + $driver_cancelled_trips;													
						$time_driven = $api->get_time_driven($driver_id,'R','A','1');	
						$driver_statistics = array( 
										"total_trip" => $overall_trip,
										"completed_trip" => $total_trip,
										"total_earnings" => commonfunction::amount_indecimal($total_amount,'api'),
										"overall_rejected_trips" => $rejected_trips,
										"cancelled_trips" => $driver_cancelled_trips,
										"today_earnings"=>commonfunction::amount_indecimal($total_amount,'api'),
										"shift_status"=>'IN',
										"time_driven"=>$time_driven,
										"status"=> 1
									);
						/**************************************************/
					}
					else
					{
						$message = array("message" => __('invalid_trip'),"status"=>-1);
					}
				}
				else
				{
					$validation_error = $validator->errors('errors');	
					$message = array("message" => $validation_error,"status"=>-3);						
				}	
				//Driver Statistics Functionality End
				$message['driver_statistics']=$driver_statistics;
				//Company count updated with Crm
				if (CRM_UPDATE_ENABLE==1 && class_exists('Thirdpartyapi')) 
				{
                    if (method_exists('Thirdpartyapi','crm_complete_trip_count')) 
                    {                                    
                        $thirdpartyapi= Thirdpartyapi::instance();
                        $thirdpartyapi->crm_complete_trip_count();
                    }
                }
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$detail,$api_model,$extended_api,$get_passenger_log_details,$driver_statistics,$insert_array,$carddetails);
			break;	

			case 'driver_invite_with_referral':
				$driverId = isset($mobiledata['driver_id']) ? $mobiledata['driver_id'] : '';
				if(!empty($driverId)) 
				{
					$check_driver_login_status = $this->is_login_status($driverId,$default_companyid);
					if($check_driver_login_status == 1)
					{ 
						$driverReferral = $api->getDriverReferralDetails($driverId);
						if(count($driverReferral) > 0) 
						{
							$driverImage = $api->getDriverProfileImage($driverId);
							$drProfileImg = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
							if(!empty($driverImage) && file_exists(DOCROOT.SITE_DRIVER_IMGPATH.$driverImage)) {
								$drProfileImg = URL_BASE.SITE_DRIVER_IMGPATH.$driverImage;
							}
							$detail = array("referral_code" => $driverReferral[0]['registered_driver_code'],"referral_amount" => $driverReferral[0]['registered_driver_code_amount'],"profile_image"=>$drProfileImg);
							$message = array("message" => __('referral_amount'),"detail" => $detail,"status"=>1);
						} else {
							$message = array("message" => __('invalid_user'),"status"=>-2);
						}
					} else {
						$message = array("message" => __('driver_not_login'),"status"=>-1);
					}
					
				} else {
					$message = array("message" => __('invalid_request'),"status"=>-1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$driverImage,$drProfileImg,$driverReferral);
			break;

			case 'driver_location_history':
				$location_array = $mobiledata;
				$api = Model::factory(MOBILEAPI_107);
				$extended_api = Model::factory(MOBILEAPI_107_EXTENDED);
				$message = array("message" => __('driver_history_updated'),"status" => 1);
				if(!empty($location_array))
				{
					$company_id = $default_companyid;
					$device_type = isset($location_array['device_type']) ? $location_array['device_type'] :1;
					$deviceToken = isset($location_array['device_token']) ? $location_array['device_token'] :'';
					$waiting_hours = isset($location_array['waiting_time']) ? $location_array['waiting_time'] :'0';
					$driverId = $location_array['driver_id'];
					
					# check whether driver logged in another device | assigned duration exceeded
					$check_device = $api->check_driver_device($driverId, $device_type, $deviceToken);
					if($check_device != 1){
						$message['message'] = ($check_device == 2) ? __('already_login1') : __('assigned_taxi_expired') ;
						//$message['status'] = ($check_device == 2 || $device_type == 2) ? 15 : -15;
						$message['status'] = ($check_device == 2) ? 15 : -15;
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					}
					$company_det = $api->get_company_id($location_array['driver_id']);
					if(count($company_det)>0)
					{
						$company_id = $company_det[0]['company_id'];
						$company_all_currenttimestamp = $this->commonmodel->getcompany_all_currenttimestamp($company_det[0]['company_id']);
					}
	
					$history_validator = $this->history_validation($location_array);
					if($history_validator->check())
					{
						$driver_status = $location_array['status'];
						$device_token = "";
						$driver_id = $location_array['driver_id'];
						$trip_id = $location_array['trip_id'];
						$coordinates = explode('|',$location_array['locations']);
						if(count($coordinates)>1){
							$last_1=array_slice($coordinates, -2, 2, true);
							$coordinates = explode(',',$last_1[count($coordinates)-2]);
						}else{
							$coordinates = explode(',',$coordinates[0]);
						}
						
						$latitude = empty($coordinates['0'])?'0.0':$coordinates['0'];		
						$longitude = empty($coordinates['1'])?'0.0':$coordinates['1'];
						# driver empty lat-long storing process
						$shift_id = isset($location_array['shift_id']) ? $location_array['shift_id'] :'';
						if($latitude !='0.0' && $longitude !='0.0' && $shift_id!=''){
							$empty_latlong = [(double)$longitude,(double)$latitude];
						#	$api->save_freedriver_location($driver_id,$shift_id,$empty_latlong);
						}
							
						if(!empty($trip_id))
						{
							//Passenger or Dispatcher cancel alert to driver
							$tripCancelAlert = $api->getTripCancelAlert($driver_id, $trip_id, $company_all_currenttimestamp);
							if(count($tripCancelAlert) > 0)
							{
								$canMsg = ($tripCancelAlert[0]['trip_status'] == 4) ? __('passenger_trip_cancelled') : __('dispatcher_trip_cancelled'); 
								$message = array("message" => $canMsg,"status"=>10);
								$update_driver_request_array  = array("notification_status" => 5,'id' => $tripCancelAlert[0]['trip_id']);
								$result = $extended_api->update_driver_request($update_driver_request_array);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								//unset($message,$update_driver_request_array);
								break;
							}
								
							/** driver update from dispatcher **/
							$trip_update_status = $api->get_trip_update_status($trip_id);
							if(count($trip_update_status) > 0)
							{
								$drop_location=isset($trip_update_status[0]['drop_location']) ? urldecode($trip_update_status[0]['drop_location']) : "";
								$drop_latitude=isset($trip_update_status[0]['drop_latitude'])?$trip_update_status[0]['drop_latitude']:"";
								$drop_longitude=isset($trip_update_status[0]['drop_longitude'])?$trip_update_status[0]['drop_longitude']:"";
								$pickup_location=isset($trip_update_status[0]['current_location']) ? urldecode($trip_update_status[0]['current_location']) : "";
								$pickup_latitude=isset($trip_update_status[0]['pickup_latitude'])?$trip_update_status[0]['pickup_latitude']:"";
								$pickup_longitude=isset($trip_update_status[0]['pickup_longitude'])?$trip_update_status[0]['pickup_longitude']:"";
								$driver_notes=isset($trip_update_status[0]['notes_driver'])?$trip_update_status[0]['notes_driver']:"";
								$notification_status=isset($trip_update_status[0]['notification_status'])?$trip_update_status[0]['notification_status']:"";
								$tripUpdateMSg = ($notification_status == 6) ? __('disptcher_updated') : __('passenger_update_drop_location');
								if($device_type == 2){
									
									$message = array("message" => __('driver_history_updated'),"status" => 1);
									$update_driver_request_array  = array("notification_status" => 7,'id' => $trip_id);
									$result = $extended_api->update_driver_request($update_driver_request_array);
									$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
									break;
								}								
							}
						}
						/** driver update from dispatcher **/
						if($driver_status == 'F')
						{
							/***** Update Driver Current Location *********************/								
							if(count($coordinates)>0)
							{	
								if(($latitude != 0) && ($longitude != 0))
								{
									$update_driver_array  = array(
															"latitude" => $latitude,
															"longitude" => $longitude,
															"status" => 'F',
															"update_date"=> $company_all_currenttimestamp,
															'driver_id'=>$driver_id
															);
								
									if(!empty($trip_id))
										$update_driver_array["status"] = strtoupper($driver_status);
										
									$update_current_result = $extended_api->update_driver_location($update_driver_array);
									$check_new_request = $api->check_new_request($driver_id,$company_all_currenttimestamp);
									if($check_new_request > 0)
									{
										$passenger_name = "";
										$get_passenger_log_details = $api_ext->get_passenger_log_detail($check_new_request);
											
										if(count($get_passenger_log_details)>0)
										{
											$rental_outstation = '0';
											$trip_type = '0';
											foreach($get_passenger_log_details as $values)
											{		
												$p_device_type = $values->passenger_device_type;
												$p_device_token  = $values->passenger_device_token;
												/** get minimum speed **/
												$taxi_id=$values->taxi_id;
												$taxi_modelid = $values->taxi_modelid;
												$model_name = $values->model_name;
												$dr_company_id=$values->company_id;
												$get_min_speed=$api->get_minimum_speed($taxi_id,$default_companyid);
												$belowspeed_mins= isset($get_min_speed[0]['taxi_min_speed']) ? $get_min_speed[0]['taxi_min_speed'] : 0;
												/** get minimum speed **/
												$pickupplace  = urldecode($values->current_location);
												$dropplace = urldecode($values->drop_location);	
												$passenger_id = $values->passengers_id;
												$passenger_phone = $values->passenger_phone;
												$time_to_reach_passen = $values->time_to_reach_passen;
												$sub_logid = $values->sub_logid;
												$pickup_latitude = $values->pickup_latitude;
												$pickup_longitude = $values->pickup_longitude;
												$drop_latitude = $values->drop_latitude;
												$drop_longitude = $values->drop_longitude;
												$passenger_salutation = $values->passenger_salutation;
												$p_name = $values->passenger_name;
												$pickup_time = $values->pickup_time;
												$bookby = $values->bookby;
												$notes_driver = $values->notes_driver;		
												$approx_fare =isset($values->approx_fare)?$values->approx_fare:0;		
												$rental_outstation = $values->rental_outstation;		
											}
											//$passenger_name = (!empty($passenger_salutation)) ? $passenger_salutation.' '.ucfirst($p_name) : $p_name;
											$passenger_name = ucfirst($p_name);
											$notification_time = $this->notification_time;
											if($notification_time != 0 )
											{ $timeoutseconds = $notification_time;}else{$timeoutseconds = 15;}
											//if timeout seconds greater than 60 seconds we have to convert to mins and secs
											if($timeoutseconds > 60) 
											{
												$notification_minutes = floor($timeoutseconds / 60);
												$notification_seconds = $timeoutseconds % 60;
												$notification_minutes = ($notification_minutes < 10) ? '0'.$notification_minutes : $notification_minutes;
											} 
											else 
											{
												$notification_minutes = "00";
												$notification_seconds = $timeoutseconds;
											}
											$notification_seconds = ($notification_seconds < 10) ? '0'.$notification_seconds : $notification_seconds;
											$total_timeout = $notification_minutes." : ".$notification_seconds;
											
											if($rental_outstation==1){
												$trip_type = '2';
											}
											else if($rental_outstation == 2){
												$trip_type = '3';
											}
												
											$trip_details = array("message" => __('api_request_confirmed_passenger'),"status" => "1","passengers_log_id" => $check_new_request,
											
											"booking_details" => array ( "pickupplace" => $pickupplace, "dropplace" => $dropplace, "pickup_time" => $pickup_time,"driver_id" => $driver_id,"passenger_id" => $passenger_id,"roundtrip" => "","passenger_phone" => $passenger_phone,"cityname" => "", "distance_away" => "","sub_logid" => $sub_logid,"drop_latitude" => $drop_latitude,"drop_longitude" => $drop_longitude, "taxi_id" => $taxi_id, "taxi_modelid" => $taxi_modelid, "model_name" => $model_name, "company_id" => $dr_company_id,"pickup_latitude" => $pickup_latitude, "pickup_longitude" => $pickup_longitude,"bookedby" => $bookby, "passenger_name" => $passenger_name,"profile_image" => "","drop" => $dropplace,"approx_fare"=>$approx_fare),
											
											"estimated_time" => $time_to_reach_passen ,"notification_time" => $timeoutseconds,"notification_minutes" => $notification_minutes,"notification_seconds" => $notification_seconds,"notes" =>$notes_driver,"belowspeed_mins"=>$belowspeed_mins,"trip_type" => $trip_type);	
												
											$message = array("message" => __('driver_history_updated'),"trip_details"=>$trip_details,"status" => 5);	
												
											$check_another_request = $api->check_new_request_bydriver($driver_id,$company_all_currenttimestamp,$check_new_request);
											if(count($check_another_request) > 0)
											{
												foreach($check_another_request as $cns)
												{
													$api->change_driver_reqflow($cns['trip_id'],$cns['available_drivers'],$cns['rejected_timeout_drivers']);
												}
											}
												
											$datas  = array(
													'status' => 1,
													'trip_id'=>$check_new_request
												);
											$datas_update1 = $extended_api->update_driver_request_details($datas);
											
											$update_driver_array  = array(
												"status" => 'B',
												'driver_id'=>$driver_id
											);
											$datas_update2 = $extended_api->update_driver_location($update_driver_array);
										}	
										else
										{
											$message = array("message" => __('driver_history_updated'),"status" => 1);
										}
									}
									else
									{
										$message = array("message" => __('driver_history_updated'),"status" => 1);
									}
								}									
							}
						}
						else if($driver_status == 'A')
						{
							#Trip completed by admin
							$travelStatus = $api_ext->check_tranc($trip_id,$flag=1);
							if($travelStatus == 1)
							{
								//~ $message = array("message" => __('tripcompleted_admin'),"status"=>7);	
								//~ $mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								//~ break;
							}
							#Trip completed by admin end
							
							/***** Update Driver Current Location ******************************************/
							$update_driver_array  = array(
								'latitude' => $latitude,
								'longitude' => $longitude,
								'status' => strtoupper($driver_status),
								'update_date'=> $company_all_currenttimestamp,
								'driver_id'=>$driver_id
							);
							$update_current_result = $extended_api->update_driver_location($update_driver_array);
							/*******************************************************************************/
							$result = $api->save_driver_location_history($location_array,$default_companyid,$trip_id);//in location array, mobiledata have distance
							$distance = isset($result[1]) ? $result[1] :'0';
							$trip_fare = 0;
							/** to get the trip fare based on the distance and minutes travelled for ongoing trip **/
							if(!empty($location_array['trip_id']))
							{
								$tripDets = $api->getTripCompanyModelDets($location_array['trip_id']);		
								if($tripDets['booking_from'] == 2)
								{	
									$farecalculation_type = (FARE_SETTINGS == 2 && $tripDets['brand_type'] == 'M') ? $tripDets['fare_calculation_type'] : FARE_CALCULATION_TYPE;
									$taxi_fare_details = $api->get_model_fare_details($tripDets['company_id'],$tripDets['taxi_modelid'],$tripDets['search_city'],$tripDets['brand_type']);
									$actualPickupDateTime = $tripDets['actual_pickup_time'];
									$base_fare = '0';
									$min_km_range = '0';
									$min_fare = '0';
									$below_above_km_range = '0';
									$below_km = '0';
									$above_km = '0';
									$minutes_cost= '0';
									//km wise fare
									$km_wise_fare = '0';
									$additional_fare_per_km= $waiting_per_minute = '0';
									if(count($taxi_fare_details) > 0)
									{
										$base_fare = $taxi_fare_details[0]['base_fare'];
										$min_km_range = $taxi_fare_details[0]['min_km'];
										$min_fare = $taxi_fare_details[0]['min_fare'];
										$below_above_km_range = $taxi_fare_details[0]['below_above_km'];
										$below_km = $taxi_fare_details[0]['below_km'];
										$above_km = $taxi_fare_details[0]['above_km'];
										$minutes_fare = $taxi_fare_details[0]['minutes_fare'];
										$km_wise_fare = $taxi_fare_details[0]['km_wise_fare'];
										$additional_fare_per_km=$taxi_fare_details[0]['additional_fare_per_km'];
										$city_model_fare=$taxi_fare_details[0]['city_model_fare'];
										
										$night_charge = $taxi_fare_details[0]['night_charge'];
										$night_timing_from = $taxi_fare_details[0]['night_timing_from'];
										$night_timing_to = $taxi_fare_details[0]['night_timing_to'];
										$night_fare = $taxi_fare_details[0]['night_fare'];
										$evening_charge = $taxi_fare_details[0]['evening_charge'];
										$evening_timing_from = $taxi_fare_details[0]['evening_timing_from'];
										$evening_timing_to = $taxi_fare_details[0]['evening_timing_to'];
										$evening_fare = $taxi_fare_details[0]['evening_fare'];
										$waiting_per_minute = $taxi_fare_details[0]['waiting_time'];
									}
									/********Minutes fare calculation *******/ 
									$interval  = abs(strtotime($company_all_currenttimestamp) - strtotime($tripDets['actual_pickup_time']));
									$minutes   = round($interval / 60);
									/********Minutes fare calculation *******/
									$baseFare = $base_fare;
									$total_fare = $base_fare;
										
									if($farecalculation_type==1 || $farecalculation_type==3)
									{
										if($distance < $min_km_range)
										{
											//min fare has set as base fare if trip distance 
											$baseFare = $min_fare;
											$total_fare = $min_fare;
										}
										//km wise fare
										else if($km_wise_fare == 1 && $distance >$min_km_range){
											$distance_after_minkm = $distance - $min_km_range;
											$additional_distance_fare = $distance_after_minkm*$additional_fare_per_km;
											$total_additional_fare = $min_fare +$additional_distance_fare+$baseFare;
											$city_fare_percent = ($city_model_fare/100);
											$total_fare = $total_additional_fare +($total_additional_fare*$city_fare_percent);
										}
										//km wise fare
										else if($distance <= $below_above_km_range)
										{
											$fare = $distance * $below_km;
											$total_fare  = 	$fare + $base_fare ;
										}
										else if($distance > $below_above_km_range)
										{
											$fare = $distance * $above_km;
											$total_fare  = 	$fare + $base_fare ;
										}
									}
										
									if($farecalculation_type==2 || $farecalculation_type==3)
									{
										/********** Minutes fare calculation ************/
										if($minutes_fare > 0)
										{
											$minutes_cost = $minutes * $minutes_fare;
											$total_fare  = $total_fare + $minutes_cost;
										}
										/************************************************/
									}
									
									//waiting time calculation per minute
									$waiting_minutes = $waiting_hours * 60;//hr to mt
									$waiting_cost = $waiting_per_minute * $waiting_minutes;
									$waiting_cost = round($waiting_cost,2);
									$total_fare = $waiting_cost + $total_fare;
									//waiting time calculation per minute
									
									// night fare calculation
									if ($night_charge == 1) 
									{				
										$night_start_date= date('Y-m-d')." ".$night_timing_from;
										$night_timing_to_value=$night_timing_to;
										$night_timing_from_value=$night_timing_from;
										$night_end_date= date('Y-m-d')." ".$night_timing_to;
										# check night start time is in previous day
										if(strtotime($night_end_date) < strtotime($night_start_date))
										{
											$night_start_date=date('Y-m-d', strtotime('-1 day'))." ".$night_timing_from_value;
										}
										else
										{
											$night_start_date= date('Y-m-d')." ".$night_timing_from_value;
										}

										if( strtotime($actualPickupDateTime) >= strtotime($night_start_date) && strtotime($actualPickupDateTime) <= strtotime($night_end_date))
										{
											$nightfare_applicable = 1;
											$nightfare = ($night_fare/100)*$total_fare;//night_charge%100;                                        
											$total_fare  = $nightfare + $total_fare;
										}	
									}

									//Evening Fare Calculation
									$parsed_eve = date_parse($evening_timing_from);
									$evening_from_seconds = $parsed_eve['hour'] * 3600 + $parsed_eve['minute'] * 60 + $parsed_eve['second'];

									$parsed_eve = date_parse($evening_timing_to);
									$evening_to_seconds = $parsed_eve['hour'] * 3600 + $parsed_eve['minute'] * 60 + $parsed_eve['second'];

									$eveningfare = $evefare_applicable=$date_difference=0;
									if ($evening_charge != 0) 
									{
										$parsed = date_parse($actualPickupDateTime);
										$pickup_seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
										if( $pickup_seconds >= $evening_from_seconds && $pickup_seconds <= $evening_to_seconds)
										{
											$evefare_applicable = 1;
											$eveningfare = ($evening_fare/100)*$total_fare;
											$total_fare  = $eveningfare + $total_fare;
										}
									}
									
									
									$trip_fare = $total_fare;
									
									# TAX inclusion
									$company_tax = $tripDets['company_tax'];
									$tax = (FARE_SETTINGS == 2 && $tripDets['brand_type'] == 'M') ? $company_tax : TAX;
									$tax_amount = ($tax/100)*$trip_fare;
									$tax_amount = commonfunction::amount_indecimal($tax_amount,'api');
									$trip_fare = $trip_fare + $tax_amount;
								}
							}
							/*****/
							$distance = isset($distance) ? $distance : '0';
							$trip_fare = isset($trip_fare) ? $trip_fare : '0';
							
							$distance = commonfunction::amount_indecimal($distance,'api');
							$trip_fare = commonfunction::amount_indecimal($trip_fare,'api');							
							
							if(isset($result[0]) && $result[0] == 1)
							{
								$message = array("message" => __('driver_history_updated'),"status" => 1,"distance"=>$distance,"trip_fare"=>$trip_fare);	
							}
							else if($result == -1)
							{
								$message = array("message" => __('driver_history_already'),"status" => -1);	
							}
							else if($result == 2)
							{
								$message = array("message" => __('invalid_user'),"status" => 2);	
							}
							else if($result == 3)
							{
								$message = array("message" => __('no_access'),"status" => 3);	
							}
							else if($result == 5)
							{
								$message = array("message" => __('driver_history_updated'),"status" => 1,"distance"=>$distance,"trip_fare"=>$trip_fare);	
							}
							else
							{
								$message = array("message" => __('invalid_user'),"status"=>-1);	
							}
						}
						elseif($driver_status == 'B')
						{
							/***** Update Driver Current Location *********************************************************/
							if(($latitude != 0) &&($longitude != 0))
							{
								$update_driver_array  = array(
									"latitude" => $latitude,
									"longitude" => $longitude,
									"status" => strtoupper($driver_status),
									"update_date"=> $company_all_currenttimestamp,
									'driver_id'=>$driver_id
								);
								$update_current_result = $extended_api->update_driver_location($update_driver_array);
							}
							/**********************************************************************************************/
							$get_passenger_log_details = $api_ext->get_passenger_log_detail($trip_id);
							if(count($get_passenger_log_details)>0)
							{
								$driver_reply = $get_passenger_log_details[0]->driver_reply;
								$travel_status = $get_passenger_log_details[0]->travel_status;
								$location_array = array("drop_location" => $get_passenger_log_details[0]->drop_location,"drop_latitude" => $get_passenger_log_details[0]->drop_latitude,"drop_longitude" => $get_passenger_log_details[0]->drop_longitude);
								$message = array("message" => __('driver_history_updated'),"drop_location_details" => $location_array,"status" => 1);
								if(($driver_reply == 'A') && ($travel_status == 4))
								{
									$message = array("message" => __("trip_cancelled_passenger"),"detail"=>"","status"=>7);
								}
							}
							else
							{
								$message = array("message" => __('driver_history_updated'),"status" => 1);	
							}
								
							$check_new_request_trip = $api->check_new_request_bydriver($driver_id,$company_all_currenttimestamp,$trip_id);
							$check_driver_status_free=$api->check_driver_status_free($driver_id);
							if($check_driver_status_free=="B" && count($check_new_request_trip) > 0)
							{
								foreach($check_new_request_trip as $cns){
									$api->change_driver_reqflow($cns['trip_id'],$cns['available_drivers'],$cns['rejected_timeout_drivers']);
								}
							} 
						}
						else
						{
							$message = array("message" => __('validation_error'),"detail"=>"","status"=>-3);
						}
					}
					else
					{
						$errors = $history_validator->errors('errors');	
						$message = array("message" => __('validation_error'),"detail"=>$errors,"status"=>-3);
					}
				}
				else
				{
					$message = array("message" => __('invalid_request'),"status"=>-4);
				}
				
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$get_passenger_log_details,$taxi_fare_details,$get_passenger_log_details,$trip_update_status,$check_new_request_trip,$check_driver_status_free,$tripDets,$result,$history_validator);
			break;		

			case 'user_logout':		
				$driver_logout_array = $mobiledata;
				$driver_id = $mobiledata['driver_id'];		
				$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);	
				if($driver_id != null)
				{
					$shiftupdate_id = $driver_logout_array['shiftupdate_id'];
					$driver_model = Model::factory('driver');
					$update_id = $driver_id;							
					$check_result = $api->check_driver_companydetails($driver_id,$default_companyid);
					if($check_result == 0)	
					{
						$message = array("message" => __('invalid_user'),"status"=>-1);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						//unset($message);
						exit;
					}
					
					$driver_current_status = $api_ext->get_driver_current_status($update_id);
					if(count($driver_current_status) > 0)
					{
						$get_driver_log_details = $api->get_driver_log_details($update_id,$default_companyid);
						$driver_trip_count = count($get_driver_log_details);//exit;
						if($driver_trip_count == 0)
						{
							$datas  = array("login_from"=>"","login_status"=>"N",
								"device_id" => "","device_token" => "",
								"device_type" => "","notification_setting"=>"0",
										"notification_status"=>"0");
							$login_status_update = $api_ext->update_driver_people($datas,$update_id);
							/*** Update in Driver table **/
							$driver_reply = $driver_model->update_driver_shift_status($update_id,'0');
							/** Update in driver shift history table **/
							$shiftupdate_arrary  = array("shift_end" => $this->currentdate);
							$shiftupdateid = $shiftupdate_id;
							if($shiftupdateid)
							{
								$companyid='';
								$transaction = $api_ext->update_drivershiftend($shiftupdateid,$companyid);
							}
							/* create user logs */
					        $user_unique = $driver_id.__('log_driver_type');

// Notification Purpose
// Notification Logger -- Start
$not_project=array();
$not_project['profile_picture']=1;
$not_project['name']=1;
$not_match=array();
$not_match['_id']=(int)$driver_id;
$not_result=$this->commonmodel->dynamic_findone_new(MDB_PEOPLE,$not_match,$not_project);
$not_name=isset($not_result['name'])?$not_result['name']:"";
$notification_content=array();
$notification_content['msg']=__('notification_logout_driver',array(':drivername' => $not_name));
$notification_content['domain']=SUBDOMAIN_NAME;
$notification_content['image']=isset($not_result['profile_picture'])?$not_result['profile_picture']:"";
$notification_content['type']='DRIVER_LOGOUT';
// Notification Logger -- End		


					        $log_array = array(
				                'user_id' => (int)$driver_id,
				                'user_type' => __('log_driver_type'),
				                'login_type' => __('log_device'),
				                'activity' => __('log_driver_arrived'),
'notification_content' =>$notification_content,
'notification_type' =>(int)1,				                
				                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
				            );
					        commonfunction::save_user_logs($log_array, $user_unique);
					        /* create user logs */
							$message = array("message" => __('logout_success'),"status"=>1);
						}
						else
						{
							$tripId = isset($get_driver_log_details[0]->passengers_log_id) ? $get_driver_log_details[0]->passengers_log_id : 0;
							$message = array("message" => __('trip_in_future'),"status"=>-4,"trip_id" => $tripId);
						}
					}
					else
					{
						$message = array("message" => __('invalid_user'),"status"=>-1);
					}
				}
				else
				{
					$message = array("message" => __('invalid_user'),"status"=>-1);	
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);	
				//unset($message,$api_ext,$driver_model);
			break;		

			case 'edit_driver_profile':					
				$d_personal_array = $mobiledata;
				if(!empty($d_personal_array))
				{
					$driver_id = $d_personal_array['driver_id'];
					if($d_personal_array["driver_id"] != null)
					{
						$validator = $this->edit_passenger_profile_validation($d_personal_array);						
						if($validator->check())
						{
							$d_email = urldecode($d_personal_array['email']);
							$d_phone = urldecode($d_personal_array['phone']);
							$password = urldecode($d_personal_array['password']);					
							$bankname = urldecode($d_personal_array['bankname']);
							$bankaccount_no = urldecode($d_personal_array['bankaccount_no']);
							$email_exist = $api->edit_check_email_people($d_email,$driver_id);
						    $phone_exist = $api->edit_check_phone_people($d_phone,$driver_id);
							if($email_exist > 0)
							{
								$message = array("message" => __('email_exists'),"status"=> 0);
							}
							else if($phone_exist > 0)
							{
								$message = array("message" => __('phone_exists'),"status"=> 2);
							}
							else
							{			
								if($d_personal_array['profile_picture'] != NULL)
								{
									/* Profile Update */
									$imgdata = base64_decode($d_personal_array['profile_picture']);
									
									$f = finfo_open();
									$mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
									$mime_type = explode('/',$mime_type);												
									$mime_type = $mime_type[1];					
									$img = imagecreatefromstring($imgdata); 
												
									if($img != false)
									{                   
										$result = $api_ext->driver_profile($d_personal_array['driver_id'],$default_companyid);
										if(count($result) >0)
										{
											$profile_picture = $result[0]['profile_picture'];
											$thumb_image = 'thumb_'.$profile_picture;
											$main_image_path = $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DRIVER_IMGPATH.$profile_picture;
											$thumb_image_path = $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DRIVER_IMGPATH.'thumb_'.$profile_picture;
											if(file_exists($main_image_path) &&($profile_picture != ""))
											{
												unlink($main_image_path);
											}
											if(file_exists($thumb_image_path) && ($thumb_image != ""))
											{
												unlink($thumb_image_path);
											}
										}		
										$mime_type = str_replace(' ', '_', $mime_type);	
										$image_name = uniqid().'.'.$mime_type;
										$thumb_image_name = 'thumb_'.$image_name;
										$image_url = DOCROOT.SITE_DRIVER_IMGPATH.'/'.$image_name;								
										$image_path = DOCROOT.SITE_DRIVER_IMGPATH.$image_name;  
										imagejpeg($img,$image_url);
										imagedestroy($img);
										chmod($image_path,0777);
										$d_image = Image::factory($image_path);
										$path11=DOCROOT.SITE_DRIVER_IMGPATH;
										Commonfunction::imageoriginalsize($d_image,$path11,$image_name,90);
										
										$path12=$thumb_image_name;
										Commonfunction::imageresize($d_image,PASS_THUMBIMG_WIDTH, PASS_THUMBIMG_HEIGHT,$path11,$thumb_image_name,90);
										if($password != "")
										{
											$update_array = array(	
											"id"=>$d_personal_array['driver_id'],						
											"salutation"=> urldecode($d_personal_array['salutation']),
											"name" => urldecode($d_personal_array['firstname']),
											"lastname" => urldecode($d_personal_array['lastname']),
											"email" => $d_email,
											"password" => md5($password),
											"org_password" => $password,
											"profile_picture" => $image_name);
										}
										else
										{
											$update_array = array(	
												"id"=> urldecode($d_personal_array['driver_id']),	
												"salutation"=> urldecode($d_personal_array['salutation']),
												"name" => urldecode($d_personal_array['firstname']),
												"lastname" => urldecode($d_personal_array['lastname']),
												"email" => $d_email,
												"profile_picture" => $image_name);
											}
											$bank_update_array = array(
											"id"=>$d_personal_array['driver_id'],
											"bankname" => $bankname,
											"bankaccount_no" => $bankaccount_no);
											/* create user logs */
						                    $user_unique = $d_personal_array['driver_id'].__('log_driver_type');
						                    $log_array = array(
						                        'user_id' => (int)$d_personal_array['driver_id'],
						                        'user_type' => __('log_driver_type'),
						                        'login_type' => __('log_device'),
						                        'activity' => __('log_profile_update'),
						                        'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
								                    );
						                    commonfunction::save_user_logs($log_array, $user_unique);
						                    /* create user logs */
											$message = $api->edit_driver_profile($update_array,$default_companyid);
											$update_bank = $api->edit_company_profile($bank_update_array);
										}
										else
										{
											$message = array("message" => __('image_not_upload'),"status"=>4);		
										}
									}
									else
									{
										if($password != "")
										{
											$update_array = array(	
											"id"=> $d_personal_array['driver_id'],						
											"salutation"=> urldecode($d_personal_array['salutation']),
											"name" => urldecode($d_personal_array['firstname']),
											"lastname" => urldecode($d_personal_array['lastname']),
											"email" => $d_email,
											"password" => md5($password),
											"org_password" => $password);
										}
										else
										{
											$update_array = array(	
											"id"=>$d_personal_array['driver_id'],						
											"salutation"=>urldecode($d_personal_array['salutation']),
											"name" => urldecode($d_personal_array['firstname']),
											"lastname" => urldecode($d_personal_array['lastname']),
											"email" => $d_email);
										}
										$bank_update_array = array(
										"id"=>$d_personal_array['driver_id'],
										"bankname" => $bankname,
										"bankaccount_no" => $bankaccount_no);
										$message = $api->edit_driver_profile($update_array,$default_companyid);
										$update_bank = $api->edit_company_profile($bank_update_array);
									}
									/*****************************************/
									if($message == 0)
									{				
										$result = $api_ext->driver_profile($d_personal_array['driver_id']);						
										if(count($result) >0)
										{
											$driver_pending_amount = 0; $driver_referral_wallet_pending_amount = 0;
											if(isset($result[0]['company_id'])) 
											{
												$referral_pending_result = $api->driver_referral_pending_amount($d_personal_array['driver_id']);
												if(count($referral_pending_result) > 0) 
												{
													$driver_referral_wallet_pending_amount = ($referral_pending_result[0]["driver_referral_wallet_pending_amount"]) ? $referral_pending_result[0]["driver_referral_wallet_pending_amount"] : 0;
												}
												$pending_result = $api->driver_withdraw_pending_amount($result[0]['company_id'],$d_personal_array['driver_id']);
												if(count($pending_result) > 0) 
												{
													$driver_pending_amount = ($pending_result[0]["pending_amount"]) ? $pending_result[0]["pending_amount"] : 0;
												}
											}	
											$driverWalletAmount = $result[0]['driver_wallet_amount'];
											$driverAvailableAmount = $result[0]['account_balance'];
										}												
										$result = array(
													"driver_wallet_amount"=> $driverWalletAmount,
													"driver_wallet_pending_amount"=> $driver_referral_wallet_pending_amount,
													"trip_amount"=> $driverAvailableAmount,
													"trip_pending_amount"=> $driver_pending_amount
												);
										$message = array("message" => __('profile_updated'),"status"=>1, "detail" => $result);	
									}	
									else
									{
										$message = array("message" => __('try_again'),"status"=>1);	
									}					
								}				
							}
							else
							{							
								$errors = $validator->errors('errors');	
								$message = array("message" => __('validation_error'),"status"=>-5,"detail"=>$errors);		
							}
						}
						else
						{
							$message = array("message" => __('invalid_user_driver'),"status"=>-1);	
						}
					}
					else
					{
						$message = array("message" => __('invalid_request'),"status"=>-1);	
					}
					
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					//unset($message,$main_image_path,$thumb_image_path);
			break;

			case 'driver_profile':
				$driver_array = $mobiledata;
				$plan_expiration_message = '';
				if($driver_array['userid'] != null)
				{
					$check_driver_login_status = $this->is_login_status($driver_array['userid'],$default_companyid);
					if($check_driver_login_status == 1)
					{	
						$result = $api_ext->driver_profile($driver_array['userid']);
						if(count($result) >0)
						{
							$driver_pending_amount = 0; 
							$driver_referral_wallet_pending_amount = 0;
							if(isset($result[0]['company_id'])) {
								$referral_pending_result = $api->driver_referral_pending_amount($driver_array['userid']);
								if(count($referral_pending_result) > 0) {
									$driver_referral_wallet_pending_amount = ($referral_pending_result[0]["driver_referral_wallet_pending_amount"]) ? $referral_pending_result[0]["driver_referral_wallet_pending_amount"] : 0;
								}
								$pending_result = $api->driver_withdraw_pending_amount($result[0]['company_id'],$driver_array['userid']);
								if(count($pending_result) > 0) {
									$driver_pending_amount = ($pending_result[0]["pending_amount"]) ? $pending_result[0]["pending_amount"] : 0;
								}
							}
							$country_code = (!empty($result[0]['country_code'])) ? trim($result[0]['country_code']).'-':'';
							$name = $result[0]['name'];
							$salutation = $result[0]['salutation'];
							$email = $result[0]['email'];
							$commission_subscription = $result[0]['commission_subscription'];
							$phone = $country_code.$result[0]['phone'];
							$profile_picture = $result[0]['profile_picture'];
							$address = $result[0]['address'];
							$driver_license_id = $result[0]['driver_license_id'];
							$lastname = $result[0]['lastname'];
							$bankname = $result[0]['bankname'];
							$bankaccount_no = $result[0]['bankaccount_no'];
							$taxi_no = $result[0]['taxi_no'];
							$mapping_startdate = Commonfunction::getDateTimeFormat($result[0]['mapping_startdate'],1);
							$mapping_enddate = Commonfunction::getDateTimeFormat($result[0]['mapping_enddate'],1);
							$model_name = $result[0]['model_name'];
							$driverWalletAmount = $result[0]['driver_wallet_amount'];
							$driverAvailableAmount = $result[0]['account_balance'];
							/************************************Driver Image *******************************/
							$main_image_path = $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DRIVER_IMGPATH.$profile_picture;
							$thumb_image_path = $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DRIVER_IMGPATH.'thumb_'.$profile_picture;
							if(file_exists($main_image_path) && ($profile_picture !='')) {
								$driver_main_image = URL_BASE.SITE_DRIVER_IMGPATH.$profile_picture;
							} else {
								//~ $driver_main_image = URL_BASE."/public/images/noimages109.png";
								$driver_main_image = URL_BASE.PUBLIC_IMAGES_FOLDER."noimages.jpg";
							}

							if(file_exists($thumb_image_path) && ($profile_picture !='')) {
								$driver_thumb_image = URL_BASE.SITE_DRIVER_IMGPATH.'thumb_'.$profile_picture;
							} else {
								//~ $driver_thumb_image = URL_BASE."/public/images/noimages109.png";
								$driver_thumb_image = URL_BASE.PUBLIC_IMAGES_FOLDER."noimages.jpg";
							}

							$dresult = $api->driver_ratings($driver_array['userid']);
							$overall_rating = $i= $trip_total_with_rate= $totalrating=0;
							if(count($dresult) > 0)
							{
								foreach($dresult as $comments)
								{
									if($comments['rating'] != 0)
										$trip_total_with_rate +=1;
										
									$overall_rating += $comments['rating'];
									$i++;	
								}
																
								if($trip_total_with_rate!=0 && $overall_rating!=0){
									$totalrating = $overall_rating/$trip_total_with_rate;
								}else{
									$totalrating = 5;
								}	
								$totalrating = round($totalrating);
							}
							else
							{
								$totalrating = 5;
							}
						
						$plan_expiration_message = '';
						//start of driver subscription type
						if((PACKAGE_TYPE == 3 || PACKAGE_TYPE == 0) && $commission_subscription == '2'){
							$driverdetails = $api_ext->get_driver_plan_info($driver_array['userid']);

							if(isset($driverdetails['planinfo'][0]) && !empty($driverdetails['planinfo'][0])){
								$driver_plan_details = $driverdetails['planinfo'][0];
								   if(isset($driver_plan_details['expiry_date']) && $driver_plan_details['expiry_date']!='' && isset($driver_plan_details['subscribed_date']) && $driver_plan_details['subscribed_date']!='' ){
                       
				                        $expirydate = Commonfunction::convertphpdate('',$driver_plan_details['expiry_date']);
				                        $subscribeddate = Commonfunction::convertphpdate('',$driver_plan_details['subscribed_date']);
				                       
				                        $today = date('Y-m-d H:i:s',time()); 
				                        $exp = date('Y-m-d H:i:s',strtotime($expirydate));
				                        $sub = date('Y-m-d H:i:s',strtotime($subscribeddate));
					                    if($exp>=$today){
				                    
					                        $subDate =  date_create($sub);
					                        $expDate =  date_create($exp);
					                        $todayDate = date_create($today);
					                        $expdiff =  date_diff($todayDate, $expDate);
					                        $remaining_day  = $expdiff->format("%R%a");
					                        $remaining_hour = $expdiff->format('%h');
				                        	
					                        $betdiff =  date_diff($subDate, $expDate);
					                        $plan_day  = $betdiff->format("%R%a");
					                        $plan_hour = $betdiff->format('%h');
					                        if($remaining_day>=0){
					                        	$remaining_day = ltrim($remaining_day, '+');
					                        	$plan_day = ltrim($plan_day, '+');
				                        		if($plan_day>=30 && $remaining_day<=7 || ($plan_day <30 && $plan_day>7 && $remaining_day<=7)){
				                        			if(($remaining_day==0) &&($plan_hour>0))
													{
														$check_exp_date = date('Y-m-d', strtotime($expDate));
														$cur_date = date('Y-m-d');
														if(strtotime($check_exp_date) == $cur_date)
														{
															$plan_expiration_message = __('plan_expire_within_1_days');	
														} else {
						                        			$plan_expiration_message = __('plan_expire_within_7_days');	
															$plan_expiration_message = str_replace('##VALIDITY_DAYS##', '1',$plan_expiration_message);
														}
													} else {
					                        			$plan_expiration_message = __('plan_expire_within_7_days');	
														$plan_expiration_message = str_replace('##VALIDITY_DAYS##',$remaining_day,$plan_expiration_message);
													}
						                        }elseif ($plan_day<7 && $remaining_day<=1 ) {
						                        	## If plan expire with in one day
													if(($remaining_day==0) &&($plan_hour>0))
													{
														$plan_expiration_message = __('plan_expire_within_1_days');	
													}
													else
													{
														$plan_expiration_message = __('plan_expire_within_7_days');
													}
													$plan_expiration_message = str_replace('##VALIDITY_DAYS##',$remaining_day,$plan_expiration_message);
						                            # code...
						                        }elseif ($plan_day<1 && $remaining_hour<=2) {
		   	      							 		$plan_expiration_message = __('plan_expire_within_2_hours');	
		   	      							 		$plan_expiration_message = str_replace('##VALIDITY_HOURS##',$remaining_hour,$plan_expiration_message);

						                            # code...
						                        }
					                        }
				                    	}else{
				                    		$plan_expiration_message = __('subscription_plan_expired');	
		   	      							
				                    	}
				                       

				                    }

								
							}
						}
						
						$driverWalletAmount =  round($driverWalletAmount - $driver_referral_wallet_pending_amount);
						
						$total_amount = $driverAvailableAmount - $driver_pending_amount;
						
						$driver_trip_wallet_amount = $total_amount+$driverWalletAmount;
						
						
						//end of driver subscription type
							$result = array(
								"salutation" => $salutation,
								"name" => $name,
								"lastname"=>$lastname,
								"bankname"=>$bankname,
								"bankaccount_no"=>$bankaccount_no,
								"email"=> $email,
								"phone"=>$phone,
								"main_image_path" => $driver_main_image,
								"thumb_image_path" => $driver_thumb_image,
								"address" => $address,
								"taxi_no" => $taxi_no,
								"taxi_map_from" => $mapping_startdate,
								"taxi_map_to" => $mapping_enddate,
								"taxi_model" => $model_name,
								"driver_license_id" => $driver_license_id,
								"driver_rating" => $totalrating,								
								"driver_wallet_amount"=> (string)commonfunction::amount_indecimal($driverWalletAmount,'api'),// This is total referral amount for the driver which is displayed in widthdrawal page								
								"driver_trip_wallet_amount"=> (string)commonfunction::amount_indecimal($driver_trip_wallet_amount,'api'),// Total wallet amount displayed in driver profile page								
								"driver_wallet_pending_amount"=> (string)commonfunction::amount_indecimal($driver_referral_wallet_pending_amount,'api'),//This is total pending referral amount for the driver which is displayed in widthdrawal page
								"trip_amount"=> commonfunction::amount_indecimal($driverAvailableAmount,'api'), // Total avaialble amount which is came from trip commission								
								"trip_pending_amount"=> commonfunction::amount_indecimal($driver_pending_amount,'api'),// Total pending amount which is came from trip commission
								"total_amount"=> commonfunction::amount_indecimal($total_amount,'api'),// Trip amount in withdrawal page
								"commission_subscription"=>$commission_subscription,
								"plan_expiration_message"=>$plan_expiration_message
							);
							$message = array("message" =>__('success'),"detail"=>$result,"status"=>1);	
						}
						else
						{
							//~ $driver_trip = check_driver_has_trip_request($driver_array['userid'],$company_all_currenttimestamp);
							//~ $status = ($driver_trip == 0) ? -1 : 0;
							$status = 0;
							$message = array("message" => __('assigned_taxi_expired'),"status"=> $status);	
						}
					}
					else
					{
						$message = array("message" => __('driver_not_login'),"status"=>-1);
					}
				}
				else
				{
					$message = array("message" => __('invalid_user_driver'),"status"=>-1);	
				}
				//~ print_r($message);exit;
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$result,$driver_thumb_image,$driver_main_image);
			break;

			/** Driver Recent Trip List **/
			case 'driver_recent_trip_list':
				$trip_array = $mobiledata;

				if(!empty($trip_array)) 
				{
					$login_status = $api->driver_logged_status($trip_array);
					$driver_login = $api->driver_login_status($trip_array);
					//echo "<pre>";print_r($login_status);exit();
					if($driver_login == 1){
						$active_status = "A";	
					}else{
						$active_status = isset($trip_array['driver_type']) ? $trip_array['driver_type'] : "";
					}

					if($login_status == 1)
					{
						$trip_list = $api->get_recent_driver_trip_list($trip_array);
						if((PACKAGE_TYPE == 3 || PACKAGE_TYPE == 0)){

							$driverdetails = $api_ext->get_driver_plan_info($trip_array['driver_id']);
							$driver_notify_details = $api_ext->get_driver_comp_notification($trip_array['driver_id']);
							$driver_register_type = isset($driver_notify_details['commission_subscription'])?$driver_notify_details['commission_subscription']:'1';//default commission
							$comp_commission_notify_flag = isset($driver_notify_details['comp_commission_notify_flag'])?$driver_notify_details['comp_commission_notify_flag']:'';//default commission

							//check driver company current register type
							$driver_company_id = $api->get_driver_companyid($trip_array);
							
							$company_register_type = '1';//commission default
							if($driver_company_id!='' && $driver_company_id!=0){
								$driver_company_details = $api->driver_company_current_register_type($driver_company_id);
								if(isset($driver_company_details['companydetails'] )&& !empty($driver_company_details['companydetails'])){
									$company_register_type = isset($driver_company_details['companydetails']['commission_subscription'])?$driver_company_details['companydetails']['commission_subscription']:'1';//default commission
								}
								
							}
							//check driver company current register type
							//echo $driver_register_type." ".$company_register_type;exit;
							//start of driver subscription type
							//echo $driver_register_type;exit;
							if($driver_register_type == '2'){
								if(isset($driverdetails['planinfo'][0]) && !empty($driverdetails['planinfo'][0])){
									$driver_plan_details = $driverdetails['planinfo'][0];
									if(isset($driver_plan_details['expiry_date']) && $driver_plan_details['expiry_date']!='' ){
										$expirydate = Commonfunction::convertphpdate('',$driver_plan_details['expiry_date']);
										$today = date('Y-m-d H:i:s',time()); 

									   $exp = date('Y-m-d H:i:s',strtotime($expirydate));
									   $expDate =  date_create($exp);
									   $todayDate = date_create($today);
									   $diff =  date_diff($todayDate, $expDate);
									   $remaining_day = $diff->format("%R%a");
									  

										//current date is greater than the expiry date
										if($remaining_day<0){
											
											$message = array("message" => __('subscription_plan_expired'),"status" => -2,"trip_list"=>$trip_list);//-2 is for redirecting the driver to subscription plan change
											$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);	
											
										}
									
									}
								}
								else{
										$message = array("message" => __('need_to_choose_subscription_plan'),"status" => -2,"trip_list"=>$trip_list);//-2 is for redirecting the driver to subscription plan change
										$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								}
							}
							//end of driver subscription type

							//start of driver commission type
							else{
									if(DRIVER_THRESHOLD_SETTING == 1){
										$check_driver_wallet_amount = $api->get_driver_wallet($trip_array['driver_id']);
										if($active_status == "D"){
											$message = array("message" => __('driver_not_active'),"status" => 10);
										}else{
											//echo $check_driver_wallet_amount;exit();
											if($check_driver_wallet_amount<DRIVER_THRESHOLD_AMOUNT){
												$message = array("message" => __('company_changes_to_commission_wallet_amount_less'),"status" => -3,"trip_list"=>$trip_list);//-3 is for redirecting the driver to wallet recharge	
												$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);

											}
										}
									}
							}
						}
					//end of enterprise package
					
						
						$driver_wallet = $api->get_driver_wallet($trip_array['driver_id']);

						if($active_status != "D"){

							if(count($trip_list) > 0)
							{
								foreach($trip_list as $key => $val)
								{
									$trip_list[$key]['drop_time'] = Commonfunction::getDateTimeFormat($val['drop_time'],1);
								}
								$message = array("message" => __('drive_recent_trip_list'),"status" => 1,"trip_list"=>$trip_list);
							}else{
								$message = array("message" => __('activation_confirmation_subject'),"status" => -1);
							}

							$message['driver_threshold_setting'] = DRIVER_THRESHOLD_SETTING;
							$message['driver_threshold_amount'] = DRIVER_THRESHOLD_AMOUNT;
							$message['driver_wallet'] = $driver_wallet;	
						}else{
							$message = array("message" => __('driver_not_active'),"status" => 10);
						}
					}
					else
					{
						$message = array("message" => __('driver_not_login'),"status"=>-1);	
					}
				} 
				else 
				{
					$message = array("message" => __('invalid_request'),"status" => -1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$login_status,$trip_list);
			break;

			case 'driver_shift_status':
				$array = $mobiledata;
				$validator = $this->shift_status_validation($array);
				if($validator->check())
				{
					$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
					$driver_id = $array['driver_id'];
					$company_status = $api->api_companystatus($array['driver_id']);
					if(($company_status == 'D') || ($company_status == 'T')){
						$message = array("message" => __('company_blocked_temp'),"status"=>-7);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						exit;
					}
					if($array['driver_id'] != null)
					{
						$check_result = $api->check_driver_companydetails($array['driver_id'],$default_companyid);
						if($check_result == 0)
						{
							$message = array("message" => __('company_deactivaed_driver'),"status"=>'-1');
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							exit;
						}
						$getTaxiassignedforDriver = $api->get_assignedtaxi_list($driver_id,$default_companyid);
						$current_driver_status = $api_ext->get_driver_current_status($array['driver_id'],$default_companyid);
						$shiftstatus = $array['shiftstatus'];
						if($array['shiftstatus'] == 'IN')
						{
							if(count($getTaxiassignedforDriver)>0)
							{	
								$taxi_id = "";
								$getTaxiforDriver = $api->getTaxiforDriver($driver_id,$default_companyid);	
								if(count($getTaxiforDriver) > 0 )
								{
									$taxi_id = $getTaxiforDriver[0]['mapping_taxiid'];
									$driver_reply = $api->update_driver_shift_status($driver_id,$array['shiftstatus']);
									$datas = array(
													"driver_id" => $driver_id,
													"taxi_id" 			=> $taxi_id,		
													"shift_end"		=> "",
													"reason"		=> $array['reason'],
													"createdate"		=> $this->currentdate,
												);
									$transaction = $api_ext->insert_drivershift($datas, $default_companyid);	
									$insert_id = $transaction[0];
									if($transaction)
									{
										/* create user logs */
								        $user_unique = $array['driver_id'].__('log_driver_type');
								        $log_array = array(
							                'user_id' => (int)$array['driver_id'],
							                'user_type' => __('log_driver_type'),
							                'login_type' => __('log_device'),
							                'activity' => __('log_shift_in'),
							                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
							            );
								        commonfunction::save_user_logs($log_array, $user_unique);
								        /* create user logs */
										$detail = array("update_id"=>$insert_id);
										$message = array("message" => __('driver_shift'),"status"=>1,"detail"=>$detail);
									}
									else
									{
										$message = array("message" => __('try_again'),"status"=>-2);
									}
							   	}	
							   	else
							   	{
									$message = array("message" => __('taxi_not_assigned'),"status"=>-3);
							   	} 	
							}
							else
							{
								$message = array("message" => __('taxi_not_assigned'),"status"=>-3);
							}						
						}
						else
						{
							if($current_driver_status[0]->status != 'A')
							{
								$get_driver_log_details = $api->get_driver_log_details($driver_id,$default_companyid);
								$driver_trip_count = count($get_driver_log_details);//exit;
								if($driver_trip_count == 0)
								{
									$update_id = $array['update_id'];
									$update_arrary  = array("shift_end" => $company_all_currenttimestamp);
									if($update_id != "")
									{
										$transaction = $api_ext->update_drivershiftend($update_id, $default_companyid);
										$driver_reply = $api->update_driver_shift_status($driver_id,'OUT');
										if($transaction)
										{
											/* create user logs */
									        $user_unique = $driver_id.__('log_driver_type');
									        $log_array = array(
								                'user_id' => (int)$driver_id,
								                'user_type' => __('log_driver_type'),
								                'login_type' => __('log_device'),
								                'activity' => __('log_shift_out'),
								                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
								            );
									        commonfunction::save_user_logs($log_array, $user_unique);
									        /* create user logs */
											$message = array("message" => __('driver_shift_out'),"status"=>1);
										}
										else
										{
											$message = array("message" => __('try_again'),"status"=>-2);
										}
									}
									else
									{
										$message = array("message" => __('update_id_missing'),"status"=>-5);
									}
								}
								else
								{
									$message = array("message" => __('trip_in_future'),"status"=>-4);
								}
							}
							else
							{
								$update_id = $array['update_id'];
								$get_driver_log_details = $api->get_driver_log_details($update_id,$default_companyid);
								$tripId = isset($get_driver_log_details[0]->passengers_log_id) ? $get_driver_log_details[0]->passengers_log_id : 0;
								
								$message = array("message" => __('driver_in_trip'),"status"=>-1,"trip_id" => $tripId);
							}
						}
					}
					else
					{
						$message = array("message" => __('invalid_user_driver'),"status"=>-1);
					}
				}
				else
				{
					$validation_error = $validator->errors('errors');	
					$message = array("message" => __('validation_error'),"status"=>-3,"detail"=>$validation_error);				
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$api_ext);
			break;

			case 'driver_reply':
				$driver_reply_array = $mobiledata;
				if($driver_reply_array['pass_logid'] != null)
				{
					$api_model = Model::factory(MOBILEAPI_107);
					$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
					$pass_logid = $driver_reply_array['pass_logid'];
					$driver_reply = $driver_reply_array['driver_reply'];
					$driver_id = $driver_reply_array['driver_id'];
					$taxi_id = $driver_reply_array['taxi_id'];
					$company_id = $driver_reply_array['company_id'];
					$field = $driver_reply_array['field'];
					$flag = $driver_reply_array['flag'];
					if($driver_reply == 'A'){$travel_status = 9;}elseif($driver_reply == 'R'){$travel_status=10;}else{$travel_status=9;}
					$driver_statistics=array();
					$result = $api_model->update_driverreply_status($pass_logid,$driver_id,$taxi_id,$company_id,$driver_reply,$travel_status,$field,$flag,$default_companyid);
					if($result == 1)
					{
						if($driver_reply == 'A')
						{
							/********* Update the status in driver request table **************/									
							$datas  = array("status"=>'3');
							$update_result = $api_ext->update_driverrequest($datas, $pass_logid);	
							
							/********** Update the Driver table he goes Busy status ****************/
							$datas  = array("status"=>'B');
							$update_driver_result = $api_ext->update_driver_driverinfo($datas ,$driver_id);
							
							//** Split fare push notification section **//
							$spltPassDetails = $api->getSplitPassengersDetails($pass_logid);
							if(count($spltPassDetails) > 0) {
								$primary_passenger_name = '';
								$primaryPassImage = '';
								foreach($spltPassDetails as $passengerDets)
								{
									if($primary_passenger_name == '')
										$primary_passenger_name = $passengerDets['name'];
											
									if($primaryPassImage == '') 
									{
										if(isset($passengerDets['profile_image']) && !empty($passengerDets['profile_image']) && file_exists(URL_BASE.PASS_IMG_IMGPATH.$passengerDets['profile_image'])) 
										{
											$primaryPassImage = URL_BASE.PASS_IMG_IMGPATH.$passengerDets['profile_image'];
										} else {
											$primaryPassImage = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
										}
									}
										
									$pushMessage  = array("message"=>"You have a split fare request","trip_id"=>$pass_logid, "pickup_location"=>$passengerDets['current_location'], "drop_location"=>$passengerDets['drop_location'], "passenger_name"=>$primary_passenger_name, "primary_passenger_profile"=>$primaryPassImage, "total_fare"=>$passengerDets['total_fare'], "split_fare"=>$passengerDets['split_fare']);
									if($passengerDets['primary_pass_id'] != $passengerDets['passenger_id']) 
									{
										$api_ext->send_pushnotification($passengerDets['device_token'],$passengerDets['device_type'],$pushMessage,$this->customer_google_api);
									}
								}
							}
							/***************** Function to send the sms ***************************/	
							$laterBookings = $api_model->get_booking_details($pass_logid);
							if(count($laterBookings) > 0) 
							{
								//** Email Section Starts **//
									
								//~ $subject = __('later_booking_confirm_subjest');
								//~ $message = __('later_booking_confirm_message').__('driver_onthe_way');
								$current_language = $laterBookings[0]['current_language'];
								$name = $laterBookings[0]['name'];
								//~ $message = str_replace("##booking_key##",$pass_logid,$message);
								$replace_variables=array(REPLACE_LOGO=>EMAILTEMPLATELOGO,
											REPLACE_SITENAME=>$this->app_name,
											REPLACE_USERNAME=>$name,
											//REPLACE_SUBJECT=>$subject,
											//REPLACE_MESSAGE=>$message,
											REPLACE_BOOKINGID=>$pass_logid,
											REPLACE_SITEEMAIL=>$this->siteemail,
											REPLACE_COMPANYDOMAIN=>$this->domain_name,
											REPLACE_SITEURL=>URL_BASE,
											REPLACE_COPYRIGHTS=>SITE_COPYRIGHT,
											REPLACE_COPYRIGHTYEAR=>COPYRIGHT_YEAR);
								
								/*if($this->lang!='en'){
									if(file_exists(DOCROOT.TEMPLATEPATH.$this->lang.'/laterbooking_confirm_message-'.$this->lang.'.html')){
										$message=$this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.$this->lang.'/laterbooking_confirm_message-'.$this->lang.'.html',$replace_variables);
									}else{
										$message=$this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.'laterbooking_confirm_message.html',$replace_variables);
									}
								}else{
									$message=$this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.'laterbooking_confirm_message.html',$replace_variables);
								} */
									
								$emailTemp = $this->commonmodel->get_email_template('booking_confirmation', $current_language);
								if(isset($emailTemp['status']) && ($emailTemp['status'] == '1')){
									
									$email_description = isset($emailTemp['description']) ? $emailTemp['description']: '';
									$subject = isset($emailTemp['subject']) ? $emailTemp['subject']: '';
									$email_description = $email_description;
										$message           = $this->emailtemplate->emailtemplate($email_description, $replace_variables);
									$from              = CONTACT_EMAIL;										
									$to = $laterBookings[0]['email'];
									$redirect = "no";	
									if($to != '') {
										if(SMTP == 1)
										{
											include($_SERVER['DOCUMENT_ROOT']."/modules/SMTP/smtp.php");
										}
										else
										{
											// To send HTML mail, the Content-type header must be set
											$headers  = 'MIME-Version: 1.0' . "\r\n";
											$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
											// Additional headers
											$headers .= 'From: '.$from.'' . "\r\n";
											$headers .= 'Bcc: '.$to.'' . "\r\n";
											mail($to,$subject,$message,$headers);	
										}
									} 
								}
								
								//** Email Section Ends **//
								//** SMS Section Starts **//
								if(SMS == 1)
								{
									$message_details = $this->commonmodel->sms_message_by_title('booking_confirmed_sms');
									if(count($message_details) > 0) 
									{
										$to = $laterBookings[0]['passenger_phone'];
										$message = (count($message_details)) ? $message_details[0]['sms_description'] : '';
										$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
										$message = str_replace("##booking_key##",$pass_logid,$message);
										$message = $message.__('driver_onthe_way');
										$sms_response = $this->commonmodel->send_sms($to,$message);
									}
								}
								//** SMS Section Ends **//
								/* create user logs */
								$user_unique = $driver_reply_array['driver_id'].__('log_driver_type');
								$log_array = array(
					                'user_id' => (int)$driver_reply_array['driver_id'],
					                'user_type' => __('log_driver_type'),
					                'login_type' => __('log_device'),
					                'activity' => __('log_driver_approve_trip'),
					                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
					            );
						        commonfunction::save_user_logs($log_array, $user_unique);
						        /* create user logs */
							}								
						}
						$message = __('request_confirmed');	
						$push_msg = __('driver_confirm_push');
						$push_status = 1;
						$response_status = 1;							
					}
					else if($result == 2)		
					{	
						/********** Update the Driver table he goes Busy status ****************/
						$datas  = array("status"=>'F');
						$update_driver_result = $api_ext->update_driver_people($datas, $driver_id);		
						/**************************************************************************/							
						$message = __('request_rejected');
						$push_msg = __('request_rejected_passenger');
						$push_status = 6;
						$response_status = 2;	
                        // version 6.2.3 update
                        $void_transaction_trip=$api->voidTransaction_for_trip($pass_logid);
                        /* create user logs */
                        $user_unique = $driver_reply_array['driver_id'].__('log_driver_type');
						$log_array = array(
			                'user_id' => (int)$driver_reply_array['driver_id'],
			                'user_type' => __('log_driver_type'),
			                'login_type' => __('log_device'),
			                'activity' => __('log_driver_reject_trip'),
			                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
			            );
				        commonfunction::save_user_logs($log_array, $user_unique);
				        /* create user logs */
					}else if($result == 3)		
					{								
							/***************** Function to send the sms ***************************/	
							$laterBookings = $api_model->get_booking_details($pass_logid);
							if(count($laterBookings) > 0) 
							{
								//** Email Section Starts **//
								
								//~ $subject = __('booking_cancel_subject');
								$current_language = $laterBookings[0]['current_language'];
								$name = $laterBookings[0]['name'];
								//~ $message = __('booking_cancel_message');
								//~ $message = str_replace("##booking_key##",$pass_logid,$message);
								$replace_variables=array(
								REPLACE_LOGO=>EMAILTEMPLATELOGO,
								REPLACE_SITENAME=>$this->app_name,
								REPLACE_USERNAME=>$name,
								//~ REPLACE_SUBJECT=>$subject,
								//~ REPLACE_MESSAGE=>$message,
								REPLACE_BOOKINGID=>$pass_logid,
								REPLACE_SITEEMAIL=>$this->siteemail,
								REPLACE_COMPANYDOMAIN=>$this->domain_name,
								REPLACE_SITEURL=>URL_BASE,
								REPLACE_COPYRIGHTS=>SITE_COPYRIGHT,
								REPLACE_COPYRIGHTYEAR=>COPYRIGHT_YEAR);
								
								/*if($this->lang!='en'){
								if(file_exists(DOCROOT.TEMPLATEPATH.$this->lang.'/laterbooking_confirm_message-'.$this->lang.'.html')){
									$message=$this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.$this->lang.'/laterbooking_confirm_message-'.$this->lang.'.html',$replace_variables);
								}else{
									$message=$this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.'laterbooking_confirm_message.html',$replace_variables);
								}
								}else{
									$message=$this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.'laterbooking_confirm_message.html',$replace_variables);
								} */
								
								$emailTemp = $this->commonmodel->get_email_template('booking_cancellation', $current_language);
								if(isset($emailTemp['status']) && ($emailTemp['status'] == '1'))
								{									
									$email_description = isset($emailTemp['description']) ? $emailTemp['description']: '';
									$subject = isset($emailTemp['subject']) ? $emailTemp['subject']: '';
									$message           = $this->emailtemplate->emailtemplate($email_description, $replace_variables);
									$from = CONTACT_EMAIL;
									$to = $laterBookings[0]['email'];
									$redirect = "no";
									if($to != '') 
									{
										if(SMTP == 1)
										{
											include($_SERVER['DOCUMENT_ROOT']."/modules/SMTP/smtp.php");
										}
										else
										{
											// To send HTML mail, the Content-type header must be set
											$headers  = 'MIME-Version: 1.0' . "\r\n";
											$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
											// Additional headers
											$headers .= 'From: '.$from.'' . "\r\n";
											$headers .= 'Bcc: '.$to.'' . "\r\n";
											mail($to,$subject,$message,$headers);
										}
									}
								}
									 
								//** Email Section Ends **//
								//** SMS Section Starts **//
								if(SMS == 1)
								{
									$message_details = $this->commonmodel->sms_message_by_title('booking_cancelled_sms');
									if(count($message_details) > 0) {
										$to = $laterBookings[0]['passenger_phone'];
										$message = (count($message_details)) ? $message_details[0]['sms_description'] : '';
										$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
										$message = str_replace("##booking_key##",$pass_logid,$message);
										$this->commonmodel->send_sms($to,$message);
									}
								}
								//** SMS Section Ends **//
								/* create user logs */
	                            $user_unique = $driver_reply_array['driver_id'].__('log_driver_type');
								$log_array = array(
					                'user_id' => (int)$driver_reply_array['driver_id'],
					                'user_type' => __('log_driver_type'),
					                'login_type' => __('log_device'),
					                'activity' => __('log_driver_cancel_trip'),
					                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
					            );
						        commonfunction::save_user_logs($log_array, $user_unique);
						        /* create user logs */
							}
								
							// Driver Statistics ********************/
							$driver_cancelled_trips = $api->get_driver_cancelled_trips($driver_id,$company_id);
							$driver_logs_rejected = $api->get_rejected_drivers($driver_id,$company_id);
							$rejected_trips = count($driver_logs_rejected);
							$driver_earnings = $api->get_driver_earnings_with_rating($driver_id,$company_id);
							$driver_tot_earnings = $api->get_driver_total_earnings($driver_id);
							$statistics = array();
							$total_trip = $today_earnings = $total_amount=0;
																
							foreach($driver_earnings as $stat){
							$total_trip++;
							$total_amount += $stat['total_amount'];
							}
							$overall_trip = $total_trip + $rejected_trips + $driver_cancelled_trips;
							$time_driven = $api->get_time_driven($driver_id,'R','A','1');
							$driver_statistics = array(
								"total_trip" => $overall_trip,
								"completed_trip" => $total_trip,
								"total_earnings" => round($driver_tot_earnings,2),
								"overall_rejected_trips" => $rejected_trips,
								"cancelled_trips" => $driver_cancelled_trips,
								"today_earnings"=>round($total_amount,2),
								"shift_status"=>'IN',
								"time_driven"=>$time_driven,
								"status"=> 1
							  );
						//Driver Statistics Functionality End
						/********** Update the Driver table he goes Busy status ****************/
						$datas  = array("status"=>'F');
						$update_driver_result = $api_ext->update_driver_driverinfo($datas, $driver_id);			
						/*************** Update in driver request table ******************/
						
						$update_trip_array  = array("status"=>'9');
						$result = $api_ext->update_driverrequest($update_trip_array, $pass_logid);			
						// version 6.2.3 update
						$void_transaction_trip=$api->voidTransaction_for_trip($pass_logid);
						/*************************************************************************/
						$message = __('trip_cancelled_driver');
						$push_msg = __('driver_cancel_after_confirm');
						$push_status = 7;
						$response_status = 3;
					}
					else if($result == 4)
					{
						$push_msg = $message = __('trip_already_cancel_rejected');
						$push_status = 8;
						$response_status = 4;
					}
					else if($result == 5){
						$push_msg = $message = __('trip_already_confirm');
						$push_status = 9;
						$response_status = 5;
					}
					else if($result == 6){
						$push_msg = $message = __('trip_already_rejected');
						$push_status = 10;
						$response_status =6;
					}
					else if($result == 7){
						$push_msg = $message = __('trip_cancel');
						$push_status = 11;
						$response_status = 7;
						// version 6.2.3 update
						$void_transaction_trip=$api->voidTransaction_for_trip($pass_logid);
					} else if($result == 10){
						$push_msg = $message = __('trip already confirm to other driver');
						$push_status = 12;
						$response_status = 8;
						$pass_logid = "";
					}
					else {
						$message = __('trip_cancel_timeout');
						$push_msg = __('trip_cancel_timeout');
						$push_status = 12;
						$response_status = 8;
					}

					$phone_no = '';
					$device_token = '';
					$driver_name = $p_device_token = $phone_no = $driver_phone = $p_device_type="";

				    $latitude = $longitude="";
				    $taxi_details = "";

					//free sms url with the arguments
					if((SMS == 1) && ($driver_phone !=''))
					{
						$message_details = $this->commonmodel->sms_message('3');
						$to = $driver_phone;
						$message_temp = $message_details[0]['sms_description'];
						$sms_message = str_replace("##booking_key##",$pass_logid,$message_temp);
					}										
					$totalrating = "";																
					$driverdetails = array();
					$trip_detail = array();
					$driverdetails=$api->get_passenger_log_detail_reply($pass_logid);
					foreach($driverdetails as $values)
					{
						if(isset($values->profile_image) && $values->profile_image)
						{
							$img = URL_BASE.PUBLIC_UPLOADS_FOLDER.'/passenger/thumb_'.$values->profile_image;
						}else{
							//$img = URL_BASE."/public/images/noimages109.png";
							$img = URL_BASE.PUBLIC_IMAGES_FOLDER."noimages.jpg";
						} 
						$values->profile_image=$img;
					}
					if($result == '10'){
						$pass_logid = "";
					}
					$detail = array("trip_id"=>$pass_logid,"driverdetails"=>$driverdetails,"driver_statistics"=>$driver_statistics);
					if($response_status == 1)
					{
						$message = array("message" => $message,"status" => $response_status,"detail"=>$detail);	
					}
					else
					{
						$message = array("message" => $message,"status" => $response_status,"driver_statistics"=>$driver_statistics);	
					}
					if($push_status == 1 || $push_status == 6 || $push_status == 7)
					{														
						if($push_status == 1)
						{															
							$push_message = array("message"=>$push_msg,"trip_id"=>$pass_logid,"driverdetails"=>$driverdetails,"status"=>$push_status);
						}
						else
						{
							$push_message = array("message"=>$push_msg,"trip_id"=>$pass_logid,"trip_detail"=>$trip_detail,"status"=>$push_status);
						}
					}
				}
				else
				{
					$message = array("message" => __('invalid_trip'),"status"=>-1);	
				}
				
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$replace_variables);
			break;

			case 'reject_trip':
				$array = $mobiledata;
				$trip_id = $array['trip_id'];
				$reject_type = $array['reject_type'];
				$driver_id = $array['driver_id'];
				$taxi_id=$array['taxi_id'];
				$company_id= $array['company_id'];
				$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
				
				if($trip_id != "")
				{			
					$passenger_log_details = $api_ext->get_trip_detail_only($trip_id);					
					if(count($passenger_log_details) >0)
					{							
						$post=array();
						$post['driver_id']=$driver_id;
						$post['passengers_id']=$passenger_log_details[0]->passengers_id;
						$post['passengers_log_id']=$trip_id;
						$post['reason']=$array['reason'];	
						$company_all_currenttimestamp = $this->commonmodel->getcompany_all_currenttimestamp($company_id);
						$post['createdate']= $company_all_currenttimestamp;
						$operator_id = $passenger_log_details[0]->operator_id;	
                                                 
						if($reject_type == 1)
						{	
							if($passenger_log_details[0]->driver_reply == 'R')
							{
								$message=__('trip_cancel_timeout');
								$message = array("message" => $message,"status" => '8');	
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							} 
							else if ($passenger_log_details[0]->travel_status == 6) 
							{
								$message = array("message" => __('trip_already_canceled'), "status"=>4);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								break;
							}
							else
							{
								//push message for rejected driver
								$rejected_driver=$passenger_log_details[0]->driver_id;
								$passengers_log_id=$trip_id;
								$push_msg = __('request_rejected');
								$message = array("message"=>$push_msg,"trip_id"=>$passengers_log_id,"trip_detail"=>"","status"=>6);
								
								/********** Update Trip Status *****************/
								$driver_reply = "";
								$get_driver_request = $api->get_driver_request($trip_id);
								if(count($get_driver_request) >0)
								{
									/******* Update the driver id in */
									$rejection_type = 1;
									$prev_rejected_timeout_drivers = $get_driver_request[0]['rejected_timeout_drivers'];
									$status = $get_driver_request[0]['status'];
									
									# increase actual_limit
									$actual_limit = $get_driver_request[0]['actual_limit'];
									$driver_limit = $get_driver_request[0]['driver_limit'];
									$actual_limit++;
									if($actual_limit >= $driver_limit){
										$api->update_tripdriverlimit($trip_id);
									}
									$api->update_driverlimit($trip_id,$actual_limit);
									$get_request_dets=$api->check_new_request_tripid($taxi_id,$company_id,$trip_id,$driver_id,$company_all_currenttimestamp,"",$operator_id);
									
									if($prev_rejected_timeout_drivers != "")
									{
										$rejected_timeout_drivers = $prev_rejected_timeout_drivers.','.$driver_id;
									}
									else
									{
										$rejected_timeout_drivers = $driver_id;
									}
									
									if($status != '4')
									{		
										$update_trip_array  = array("status"=>'0',"rejected_timeout_drivers" => $rejected_timeout_drivers);
									}
									$add_rejected_list = $api->add_rejected_list($post,$rejection_type);
									// Driver Statistics ********************/
									$driver_logs_rejected = $api->get_rejected_drivers($driver_id,$company_id);	
									$rejected_trips = count($driver_logs_rejected);	
									//to get cancelled trip counts from drivers
									$driver_cancelled_trips = $api->get_driver_cancelled_trips($driver_id,$company_id);
									$driver_earnings = $api->get_driver_earnings_with_rating($driver_id,$company_id);
									$driver_tot_earnings = $api->get_driver_total_earnings($driver_id);
									$statistics = array();
									$total_trip = $trip_total_with_rate = $total_ratings = $today_earnings = $total_amount=0;
																	
									foreach($driver_earnings as $stat){
										$total_trip++;
										$total_ratings += $stat['rating'];
										$total_amount += $stat['total_amount'];			
									}
									$overall_trip = $total_trip + $rejected_trips + $driver_cancelled_trips;							
									$time_driven = $api->get_time_driven($driver_id,'R','A','1');
									$statistics = array(
										"total_trip" => $overall_trip,
										"completed_trip" => $total_trip,
										"total_earnings" => round($driver_tot_earnings,2),
										"overall_rejected_trips" => $rejected_trips,
										"cancelled_trips" => $driver_cancelled_trips,
										"today_earnings"=>round($total_amount,2),											
										"shift_status"=>'IN',
										"time_driven"=>$time_driven,
										"status"=> 1
									);
									/* create user logs */
							        $user_unique = $driver_id.__('log_driver_type');
							        $log_array = array(
						                'user_id' => (int)$driver_id,
						                'user_type' => __('log_driver_type'),
						                'login_type' => __('log_device'),
						                'activity' => __('log_driver_reject_trip'),
						                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
						            );
							        commonfunction::save_user_logs($log_array, $user_unique);
							        /* create user logs */
									$message = array("message" => __('request_rejected'),"driver_statistics"=>$statistics,"status" => 6);
								}								
								/***********************************************************************************/							
							}
						}
						else
						{							
							$get_driver_request = $api->get_driver_request($trip_id);
							$rejection_type = 0;
							if(count($get_driver_request) >0)
							{
								/******* Update the driver id in */
								$prev_rejected_timeout_drivers = $get_driver_request[0]['rejected_timeout_drivers'];
								$status = $get_driver_request[0]['status'];
								$reject_driversArr = explode(",",$prev_rejected_timeout_drivers);
								if(!in_array($driver_id, $reject_driversArr)) 
								{
									if($prev_rejected_timeout_drivers != "")
									{
										$rejected_timeout_drivers = $prev_rejected_timeout_drivers.','.$driver_id;
									}
									else
									{
										$rejected_timeout_drivers = $driver_id;
									}
									
									# increase actual_limit
									$actual_limit = $get_driver_request[0]['actual_limit'];
									$driver_limit = $get_driver_request[0]['driver_limit'];
									$actual_limit++;
									if($actual_limit >= $driver_limit){
										$api->update_tripdriverlimit($trip_id);
									}
									$api->update_driverlimit($trip_id,$actual_limit);
									$get_request_dets=$api->check_new_request_tripid($taxi_id,$company_id,$trip_id,$driver_id,$company_all_currenttimestamp,"",$operator_id);		
									if($status != '4')
									{
										$datas  = array("status"=>'0', "rejected_timeout_drivers" => $rejected_timeout_drivers);
										$result = $api_ext->update_driverrequest($datas, $trip_id);
									}
								}
								$add_rejected_list = $api->add_rejected_list($post,$rejection_type);
								// Driver Statistics ********************/
								$driver_logs_rejected = $api->get_rejected_drivers($driver_id,$company_id);	
								$rejected_trips = count($driver_logs_rejected);	
								//to get cancelled trip counts from drivers
								$driver_cancelled_trips = $api->get_driver_cancelled_trips($driver_id,$company_id);
								$driver_earnings = $api->get_driver_earnings_with_rating($driver_id,$company_id);
								$driver_tot_earnings = $api->get_driver_total_earnings($driver_id);
								$statistics = array();
								$total_trip = $trip_total_with_rate = $total_ratings = $today_earnings = $total_amount=0;
																
								foreach($driver_earnings as $stat){
									$total_trip++;
									$total_ratings += $stat['rating'];
									$total_amount += $stat['total_amount'];											
								}
								$overall_trip = $total_trip + $rejected_trips + $driver_cancelled_trips;							
								$time_driven = $api->get_time_driven($driver_id,'R','A','1');	
								$statistics = array( 
									"total_trip" => $overall_trip,
									"completed_trip" => $total_trip,
									"total_earnings" => round($driver_tot_earnings,2),
									"overall_rejected_trips" => $rejected_trips,
									"cancelled_trips" => $driver_cancelled_trips,
									"today_earnings"=>round($total_amount,2),											
									"shift_status"=>'IN',
									"time_driven"=>$time_driven,
									"status"=> 1
								  ); 
								
								/* create user logs */
						        $user_unique = $driver_id.__('log_driver_type');
						        $log_array = array(
					                'user_id' => (int)$driver_id,
					                'user_type' => __('log_driver_type'),
					                'login_type' => __('log_device'),
					                'activity' => __('log_driver_timeout_trip'),
					                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
					            );
						        commonfunction::save_user_logs($log_array, $user_unique);
						        /* create user logs */
								$message = array("message" => __('driver_reply_timeout'),"driver_statistics"=>$statistics,"status" => 7);
								
								# increase actual_limit
								$actual_limit = $get_driver_request[0]['actual_limit'];
								$actual_limit++;
								$api->update_driverlimit($trip_id,$actual_limit);									
							}		
						}	
					}
					else
					{
						$message = array("message" => __('invalid_trip'),"status"=>2);
					}						
				}
				else
				{
					$message =__('trip_id_req');
					$message = array("message" => $message,"status" => '-1');
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$api_ext,$passenger_log_details,$get_request_dets,$statistics,$get_driver_request);
			exit;

			case 'driver_arrived':
				$array = $mobiledata;
				$trip_id = $array['trip_id'];
				if($array['trip_id'] != null)
				{
					$check_travelstatus = $api_ext->check_travelstatus($trip_id);
					$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
					if($check_travelstatus == -1)
					{
						$message = array("message" => __('invalid_trip'),"status"=>2);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						//unset($message);
						break;
					}				
					if($check_travelstatus == 4)
					{
						$message = array("message" => __('trip_cancelled_passenger'), "status"=>-1);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						//unset($message);
						break;
					}
					if($check_travelstatus != 9)
					{
						$message = array("message" => __('passenger_in_journey'), "status"=>-1);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						//unset($message);
						break;
					}						
					$get_passenger_log_details = $api_ext->get_passenger_log_detail($trip_id);		
					$driver_id = $get_passenger_log_details[0]->driver_id;
					$passenger_email = $get_passenger_log_details[0]->passenger_email;
					$driver_current_location = $api_ext->get_driver_current_status($driver_id);
					$driver_latitute = $driver_longtitute="";
					if(count($driver_current_location)>0)
					{
						$driver_latitute = $driver_current_location[0]->latitude;
						$driver_longtitute  = $driver_current_location[0]->longitude;
						$driver_status  = $driver_current_location[0]->status;					
					}

					if($driver_status == 'A')
					{
						$message = array("message" => __('already_trip'), "status"=>-1);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						break;					
					}
					/********** Update Driver Status after complete Payments *****************/
					$datas  = array("travel_status" => '3'); // Start to Pickup
					$result = $api_ext->update_passengerlogs($datas,$trip_id);
					
					/*************** Update arrival in driver request table ******************/				
					$datas  = array("status"=>'5','trip_id'=>$trip_id);
					$driver_request_result = $api_ext->update_driver_request_details($datas);		
					
					/**************************** Update status in driver table *********/
					$datas  = array("status"=>'B');
					$datas['driver_id'] = $driver_id;
					$driver_result = $api_ext->update_driver_location($datas);		
				
					/*************************************************************************/				
					/** Send Trip fare details to Passenger ***/
					$p_device_token = $get_passenger_log_details[0]->passenger_device_token;
					$device_type = $get_passenger_log_details[0]->passenger_device_type;
					$passenger_id = $get_passenger_log_details[0]->passengers_id;
					$pushmessage = array("message"=>__('passenger_on_board'),"trip_id"=>$trip_id,"driver_latitute"=>$driver_latitute,"driver_longtitute"=>$driver_longtitute,"status"=>2);
					if(SMS == 1)
					{
						$message_details = $this->commonmodel->sms_message_by_title('driver_arrived');
						if(count($message_details) > 0) {
							$to = $this->commonmodel->getuserphone('P',$passenger_email);
							$message = $message_details[0]['sms_description'];
							$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
							$this->commonmodel->send_sms($to,$message);
						}
					}
					/* create user logs */
			        $user_unique = $driver_id.__('log_driver_type');
			        $log_array = array(
		                'user_id' => (int)$driver_id,
		                'user_type' => __('log_driver_type'),
		                'login_type' => __('log_device'),
		                'activity' => __('log_driver_arrived'),
		                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
		            );
			        commonfunction::save_user_logs($log_array, $user_unique);
			        /* create user logs */
					$message = array("message" => __('driver_arrival_send'),"status"=>1);					
				}
				else
				{
					$message = array("message" => __('invalid_trip'),"status"=>-1);	
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);	
				//unset($message);
			break;

			case 'driver_status_update':
				$driver_status_array = $mobiledata;
				$act_pickup_location = isset($driver_status_array['actual_pickup_location']) ? 	urldecode($driver_status_array['actual_pickup_location']) : '';
				$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
				if($driver_status_array['driver_id'] != null)
				{
					$driverId = $driver_status_array['driver_id'];
					$check_driver_login_status = $this->is_login_status($driverId,$default_companyid);
					if($check_driver_login_status == 1)
					{ 
						$driver_model = Model::factory('driver');
						$current_driver_status = $driver_model->get_driver_current_status($driverId);
						if(count($current_driver_status) > 0)
						{
							$trip_details = array();
							$passengers_log_id = $driver_status_array['trip_id'];
							$update_driver_arrary  = array(
											"latitude" => $driver_status_array['latitude'],
											"longitude" => $driver_status_array['longitude'],
											"status" => strtoupper($driver_status_array['status']));						
							if($current_driver_status[0]->status != 'A')
							{								
								if(($driver_status_array['status'] == 'A') && ($passengers_log_id != null))
								{
									$get_passenger_log_details = $api_ext->get_passenger_log_detail($passengers_log_id);
									foreach($get_passenger_log_details as $values)
									{
										$current_location = $values->current_location;	
										$pickup_latitude = $values->pickup_latitude;
										$pickup_longitude = $values->pickup_longitude;			
										$drop_location = $values->drop_location;	
										$drop_latitude= $values->drop_latitude;
										$drop_longitude = $values->drop_longitude;
										$driver_name = $values->driver_name;									
										$p_device_type = $values->passenger_device_type;
										$p_device_token  = $values->passenger_device_token;	
										$actual_pickup_time  = $values->actual_pickup_time;
										$travel_status = $values->travel_status;
										$driver_reply = $values->driver_reply;
										$notes = $values->notes_driver;
										$default_unit = ($values->default_unit == 0) ? "KM":"MILES";
										$default_unit = (FARE_SETTINGS == 2) ? $default_unit : UNIT_NAME;
										$driver_plan_info = $values->driver_plan_info; // For check the driver have plan if they are in subscribed drivers										
									}
									/********** Check whther the Trip is alreadt cancelled by the passenger **********/
									if(($driver_reply == 'A') && ($travel_status == 4))
									{
										$message = array("message" => __("trip_cancelled_passenger"),"detail"=>"","status"=>7);
										$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
										exit;
									}
									/*********************************************************************************/
											
									/** update journey inprogress in Passenger log table when driver start the journey**/
									$company_det =$api->get_company_id($driver_status_array['driver_id']);
									$compId = (count($company_det) > 0) ? $company_det[0]['company_id'] : $default_companyid;
									$actual_pickup_time = $this->commonmodel->getcompany_all_currenttimestamp($compId);
									$travel_status = 2;
									$act_pickup_location=$api->getaddress($driver_status_array['latitude'],$driver_status_array['longitude']);
								    if($act_pickup_location == false)
								    {
										$act_pickup_location = $current_location;
								    }
									$act_pic_lat = ($driver_status_array['latitude'] != 0) ? $driver_status_array['latitude'] : $pickup_latitude;
									$act_pic_long = ($driver_status_array['longitude'] != 0) ? $driver_status_array['longitude'] : $pickup_longitude;

									$datas = array('travel_status' => $travel_status,
										'actual_pickup_time'=>$actual_pickup_time,
										'current_location'=>$act_pickup_location,
										'pickup_latitude'=>$act_pic_lat,
										'pickup_longitude'=>$act_pic_long);

									$result = $api_ext->update_passengerlogs($datas, $passengers_log_id);
									/** Passenger log table update end **/
									/*************** Update arrival in driver request table ******************/
									$datas  = array("status"=>'6','trip_id' => $passengers_log_id);
									$result = $api_ext->update_driver_request_details($datas);
									/*************************************************************************/	
									if(($driver_status_array['latitude'] != 0) &&($driver_status_array['longitude'] != 0))
									{
										$update_driver_arrary['driver_id'] = $driver_status_array['driver_id'];
										$result = $api_ext->update_driver_location($update_driver_arrary);
									}
									/* create user logs */
							        $user_unique = $driver_status_array['driver_id'].__('log_driver_type');

// Notification Logger -- Start
$not_project=array();
$not_project['profile_picture']=1;
$not_project['name']=1;
$not_match=array();
$not_match['_id']=(int)$driverId;
$not_result=$this->commonmodel->dynamic_findone_new(MDB_PEOPLE,$not_match,$not_project);
$not_name=isset($not_result['name'])?$not_result['name']:"";
$notification_content=array();
$notification_content['msg']=__('notification_start_trip_driver',array(':drivername' => $not_name));
$notification_content['domain']=SUBDOMAIN_NAME;
$notification_content['image']=isset($not_result['profile_picture'])?$not_result['profile_picture']:"";
$notification_content['type']='DRIVER_START_TRIP';
// Notification Logger -- End	

							        $log_array = array(
							                'user_id' => (int)$driver_status_array['driver_id'],
							                'user_type' => __('log_driver_type'),
							                'login_type' => __('log_device'),
							                'activity' => __('log_trip_start'),
'notification_content' =>$notification_content,
'notification_type' =>(int)1,								                
							                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
							            );
									commonfunction::save_user_logs($log_array, $user_unique);
									/* create user logs */
									$trip_details = array("pickup_latitude"=>$driver_status_array['latitude'],"pickup_longitude"=>$driver_status_array['longitude'],"pickup_location"=>$act_pickup_location,"drop_latitude"=>$drop_latitude,"drop_longitude"=>$drop_longitude,"drop_location"=>$drop_location,"notes"=>$notes,"metric"=>$default_unit);
									$message = array("message" => __('driver_location_update'),"status"=>1,"detail"=>$trip_details);
									$push_message = array("message" =>__('journey_started'),"pickup_time"=>$actual_pickup_time,"trip_id"=>$passengers_log_id,"status"=>3);
								}	
								elseif(($driver_status_array['status'] == 'A') && ($passengers_log_id == null))
								{
									$message = array("message" => __('invalid_trip_id'),"status"=>-1,"detail"=>$trip_details);
								}
								else
								{									
									if(($driver_status_array['latitude'] != 0) &&($driver_status_array['longitude'] != 0))
									{
										$update_driver_arrary['driver_id'] = $driver_status_array['driver_id'];
										$result = $api_ext->update_driver_location($update_driver_arrary);	
									}
									$message = array("message" => __('driver_location_update'),"status"=>1);
								}
							}
							else
							{
								$update_driver_arrary  = array(
											"latitude" => $driver_status_array['latitude'],
											"longitude" => $driver_status_array['longitude'],
											"status" => strtoupper($driver_status_array['status']));	
								if(($driver_status_array['latitude'] != 0 ) &&($driver_status_array['longitude'] != 0))
								{
									$update_driver_arrary['driver_id'] = $driver_status_array['driver_id'];
									$result = $api_ext->update_driver_location($update_driver_arrary);	
								}
								$message = array("message" => __('already_trip'),"status"=>-1);
							}
						}
						else
						{									
							$insert_array = array(
									"driver_id" => $driver_status_array['driver_id'],
									"latitude"		=> $driver_status_array['latitude'],
									"longitude"		=> $driver_status_array['longitude'],
									"status"			=> 'F',
									"shift_status" => 'OUT');									
							if(($driver_status_array['latitude'] != 0) &&($driver_status_array['longitude'] != 0))
							{
								$transaction = $api_ext->insert_driverinfo($insert_array);
							}
							$message = array("message" => __('driver_location_update'),"status"=>1);
						}
					}
					else
					{
						$message = array("message" => __('driver_not_login'),"status"=>-1);	
					}	
				}
				else
				{
					$message = array("message" => __('invalid_user'),"status"=>-1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$transaction,$get_passenger_log_details);
			break;

			case 'complete_trip':
				/*$get_passenger_log_details = $api_ext->get_passenger_log_detail('474');

            $drop_time = '2017-12-07 19:22:55';
            $actual_pickup_time ='2017-12-07 19:20:19';
 			$interval  = abs(strtotime($drop_time) - strtotime($actual_pickup_time));
									echo $minutes   = round($interval / 60);   exit();*/
				$array = $mobiledata;
				//print_r($array);exit;
				$extended_api = Model::factory(MOBILEAPI_107_EXTENDED);
				if(!empty($array))
				{
					$drop_latitude = $array['drop_latitude'];
					$drop_longitude = $array['drop_longitude'];
					$drop_location = urldecode($array['drop_location']);
					$trip_id = $array['trip_id'];
					$distance = isset($array['distance'])?round($array['distance'],2):0;
					$actual_distance = $array['actual_distance'];
					$waiting_hours = $array['waiting_hour'];
					$erp_charge = isset($array['erp_charge'])?$array['erp_charge']:'';
					$driver_app_version = (isset($array['driver_app_version'])) ? $array['driver_app_version'] : '';
					if(!empty($trip_id))
					{
						$gateway_details = $this->commonmodel->gateway_details($default_companyid);
						$get_passenger_log_details = $api_ext->get_passenger_log_detail($trip_id);
						
						$p_referral_discount = 0;
						$pickupdrop = $taxi_id = $company_id = 0;
						$fare_per_hour = $waiting_per_minute = $total_fare = $nightfare = 0;//hr to mt
						
						if(count($get_passenger_log_details) > 0)
						{					
							/******* Check whether the trip is completed if so we change the driver status and trip travel status and give response **********/
							if($get_passenger_log_details[0]->transaction_id != 0)
							{
								$travel_status = $get_passenger_log_details[0]->travel_status;
								$driver_id = $get_passenger_log_details[0]->driver_id;
								if(($travel_status == 1 || $travel_status == 5 || $travel_status == 2))
								{
									/********** Update Driver Status after complete Payments *****************/
									$update_driver_array  = array(
										'status' => 'F',
										'driver_id'=>$driver_id
									);
									$result = $extended_api->update_driver_location($update_driver_array);
									/************Update Driver Status ***************************************/
									$message_status = 'R';$driver_reply='A';$journey_status=1; // Waiting for Payment
									$journey = $api->update_journey_status($trip_id,$message_status,$driver_reply,$journey_status);
									/*************** Update arrival in driver request table ******************/
									$update_driver_request_details  = array(
										'status' => 7,
										'trip_id'=>$trip_id
									);
									$result = $extended_api->update_driver_request_details($update_driver_request_details);
									/*************************************************************************/	
									$resMessage = ($travel_status == 1) ?  __('trip_fare_already_updated') : __('trip_fare_and_status_updated');
									$message = array("message" => $resMessage, "status"=>-1);
									$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
									//unset($message);
									break;
								}
							}
							else
							{
								//driver_register_type is to check the driver is in commission or subscription (1-commission and 2-subscription)
								$driver_register_type = (isset($get_passenger_log_details[0]->driver_register_type))?$get_passenger_log_details[0]->driver_register_type:'1'; //default 1 - commission

								$passenger_discount = (isset($get_passenger_log_details[0]->passenger_discount))?$get_passenger_log_details[0]->passenger_discount:0; //
								$passengers_id = $get_passenger_log_details[0]->passengers_id;
								$referred_by = $get_passenger_log_details[0]->referred_by;					
								$referrer_earned = $get_passenger_log_details[0]->referrer_earned;
								$company_tax = $get_passenger_log_details[0]->company_tax;
								$company_fare_calculation_type = $get_passenger_log_details[0]->fare_calculation_type;
								$tax = (FARE_SETTINGS != 2) ? TAX : $company_tax;
								
								$travel_status = $get_passenger_log_details[0]->travel_status;
								$splitTrip = $get_passenger_log_details[0]->is_split_trip;//0 - Normal trip, 1 - Split trip
								//$total_distance = $get_passenger_log_details[0]->distance;
								$total_distance = $distance;//mobile is giving the distance
								$used_wallet_amount = $get_passenger_log_details[0]->used_wallet_amount;
								$promocode = $get_passenger_log_details[0]->promocode;
								$default_unit = ($get_passenger_log_details[0]->default_unit == 0) ? "KM":"MILES";
								$default_unit = (FARE_SETTINGS == 2) ? $default_unit : UNIT_NAME;
								$p_referral_discount = 0;
								$pickupdrop = $taxi_id = $company_id = 0;
								$fare_per_hour = $waiting_per_minute = $total_fare = $nightfare = 0;//hr to mt
								if(($travel_status == 2) || ($travel_status == 5))
								{
									$pickup = $get_passenger_log_details[0]->current_location;
									$drop = $get_passenger_log_details[0]->drop_location;
									$pickupdrop = $get_passenger_log_details[0]->pickupdrop;
									$taxi_id = $get_passenger_log_details[0]->taxi_id;
									$pickuptime = date('H:i:s', strtotime($get_passenger_log_details[0]->pickup_time));
									$actualPickupTime = date('H:i:s', strtotime($get_passenger_log_details[0]->actual_pickup_time));
									$actualPickupDateTime = date('Y-m-d H:i:s', strtotime($get_passenger_log_details[0]->actual_pickup_time));
									$company_id = $get_passenger_log_details[0]->company_id;
									$driver_id = $get_passenger_log_details[0]->driver_id;
									$approx_distance = $get_passenger_log_details[0]->approx_distance;
									$approx_fare = $get_passenger_log_details[0]->approx_fare;
									$fixedprice = $get_passenger_log_details[0]->fixedprice;
									$passengers_id = $get_passenger_log_details[0]->passengers_id;
									$now_after = $get_passenger_log_details[0]->now_after;
									$referred_by = $get_passenger_log_details[0]->referred_by;			
									$actual_pickup_time = $get_passenger_log_details[0]->actual_pickup_time;
									$brand_type = $get_passenger_log_details[0]->brand_type;
									$passengerMobile = $get_passenger_log_details[0]->passenger_country_code.$get_passenger_log_details[0]->passenger_phone;
									$taxi_model_id = $get_passenger_log_details[0]->taxi_modelid;									
									$cityName = isset($get_passenger_log_details[0]->city_name) ? $get_passenger_log_details[0]->city_name : '';
									
									$farecalculation_type = (FARE_SETTINGS == 2 && $brand_type == 'M') ? $company_fare_calculation_type : FARE_CALCULATION_TYPE;
							
									
									if($travel_status != 5) {
										$drop_time = $this->commonmodel->getcompany_all_currenttimestamp($company_id);
									} else {
										$drop_time = $get_passenger_log_details[0]->drop_time;
									}
									
									/*************** Update arrival in driver request table ******************/
									$update_trip_array  = array(
										'status' => 7,
										'trip_id'=>$trip_id
									);
									$result = $extended_api->update_driver_request_details($update_trip_array);
									/*************************************************************************/	
									/** Update Driver Status **/
									if(($array['drop_latitude'] > 0 ) && ($array['drop_longitude'] > 0))
									{
										$update_driver_array  = array('latitude' => $array['drop_latitude'],'longitude' => $array['drop_longitude'],'status' => 'A','driver_id'=>$driver_id);
									}else{
										$update_driver_array  = array('status' => 'A','driver_id'=>$driver_id);
									}
									
									$result = $extended_api->update_driver_location($update_driver_array);
									/*********************/
									$base_fare = '0';
									$min_km_range = '0';
									$min_fare = '0';
									$cancellation_fare = '0';
									$below_above_km_range = '0';
									$below_km = '0';
									$above_km = '0';
									$night_charge = '0';
									$night_timing_from = '0';
									$night_timing_to ='0';
									$night_fare = '0';
									$evening_charge = '0';
									$evening_timing_from = '0';
									$evening_timing_to ='0';
									$evening_fare = '0';
									//$waiting_per_hour = '0';
									$waiting_per_minute = '0';//hr to mt
									$minutes_cost= '0';
									$farePerMin = '0';
									//km wise fare
									$km_wise_fare='0';
									$additional_fare_per_km = '0';
									$trip_type = '1';
									
									/* rental / outstation trip fare calculation factors */
									$os_plan_fare = '0';
									$os_plan_distance = '0' ;
									$os_plan_duration = '0'; 
									$os_additional_fare_per_distance = '0' ;
									$os_additional_fare_per_hour = '0';
									$os_duration = '0';
									$os_distance_unit = '0';
									/* rental / outstation trip fare calculation factors */

									$baseFare = '0';
									$tax_amount = "0";	
									$roundtrip="No";
									$waiting_cost = "0";
									if($pickupdrop == 1)
									{
										$roundtrip = "Yes";
									}

									/********Minutes fare calculation *******/
									$interval  = abs(strtotime($drop_time) - strtotime($actual_pickup_time));
									$minutes   = round($interval / 60);       
									/********Minutes fare calculation *******/
									
									$siteinfo_details = $api->siteinfo_details();
									// Passenger individual Discount Calculation
									$discount_fare="0";								
									// Referral Discount Claculation
									
									$promo_type = $promo_discount = $promodiscount_amount = 0;
									$eveningfare = 0; $evefare_applicable=$date_difference=0;
									$nightfare_applicable =0;
									/********Rental/Outstation fare calculation start*******/		
									$rental_outstation =isset($get_passenger_log_details[0]->rental_outstation)?$get_passenger_log_details[0]->rental_outstation:'';
									$outstation_normal_fare_calc = 0;
									$rental_details = [];
									if($rental_outstation > 0){
										
										if($rental_outstation==1){
											$trip_type = '2';
										}//rental fare calculation end
										//outstation on fixed fare calculation starts
										else if($rental_outstation == 2){
											$trip_type = '3';
										}
										$rent_out_tour_id = isset($get_passenger_log_details[0]->rent_out_tour_id)?$get_passenger_log_details[0]->rent_out_tour_id:0;
										$rental_details['base_fare'] = $baseFare = isset($get_passenger_log_details[0]->base_fare)?$get_passenger_log_details[0]->base_fare:0;
										$rental_details['plan_distance'] = $plan_distance = isset($get_passenger_log_details[0]->plan_distance)?$get_passenger_log_details[0]->plan_distance:0;
										$rental_details['plan_duration'] = $plan_duration = isset($get_passenger_log_details[0]->plan_duration)?$get_passenger_log_details[0]->plan_duration:0;
										$rental_details['plan_distance_unit'] = $plan_distance_unit = isset($get_passenger_log_details[0]->plan_distance_unit)?$get_passenger_log_details[0]->plan_distance_unit:UNIT_NAME;
										$rental_details['additional_fare_per_distance'] = $additional_fare_per_distance = isset($get_passenger_log_details[0]->additional_fare_per_distance)?$get_passenger_log_details[0]->additional_fare_per_distance:0;
										$rental_details['additional_fare_per_hour'] = $additional_fare_per_hour = isset($get_passenger_log_details[0]->additional_fare_per_hour)?$get_passenger_log_details[0]->additional_fare_per_hour:0;
										$rental_details['trip_distance'] = $total_distance;
										$rental_details['trip_minutes'] = $minutes;
										
										$returnResult = commonfunction::rental_outstation_calc($rental_details);
										$os_plan_fare = $baseFare;
										$os_plan_distance = $plan_distance ;
										$os_plan_duration = $plan_duration; 
										$os_additional_fare_per_distance = $additional_fare_per_distance ;
										$os_additional_fare_per_hour = $additional_fare_per_hour;
										$os_duration = $minutes;
										$os_distance_unit = $plan_distance_unit;
										
										$total_fare = $returnResult['fare'];
										$trip_fare = round($total_fare,2);
										
										//outstation on fixed fare calculation starts
									}/********Rental/Outstation fare calculation end *******/
									
								//else if($rental_outstation == '' || ($rental_outstation!='' && $outstation_normal_fare_calc == 0)){
									else{
										$taxi_fare_details = $api->get_model_fare_details($company_id,$taxi_model_id,$cityName,$brand_type);
										
										if(count($taxi_fare_details) > 0)
										{
											$base_fare = $taxi_fare_details[0]['base_fare'];
											$min_km_range = $taxi_fare_details[0]['min_km'];
											$min_fare = $taxi_fare_details[0]['min_fare'];
											$cancellation_fare = $taxi_fare_details[0]['cancellation_fare'];
											$below_above_km_range = isset($taxi_fare_details[0]['below_above_km'])?$taxi_fare_details[0]['below_above_km']:0;
											$below_km = $taxi_fare_details[0]['below_km'];
											$above_km = $taxi_fare_details[0]['above_km'];
											$night_charge = $taxi_fare_details[0]['night_charge'];
											$night_timing_from = $taxi_fare_details[0]['night_timing_from'];
											$night_timing_to = $taxi_fare_details[0]['night_timing_to'];
											$night_fare = $taxi_fare_details[0]['night_fare'];
											$evening_charge = $taxi_fare_details[0]['evening_charge'];
											$evening_timing_from = $taxi_fare_details[0]['evening_timing_from'];
											$evening_timing_to = $taxi_fare_details[0]['evening_timing_to'];
											$evening_fare = $taxi_fare_details[0]['evening_fare'];
											//$waiting_per_hour = $taxi_fare_details[0]['waiting_time'];
											$waiting_per_minute = $taxi_fare_details[0]['waiting_time'];//hr to mt
											$minutes_fare = $taxi_fare_details[0]['minutes_fare'];
											$farePerMin = $minutes_fare;
											//km wise fare
											$km_wise_fare = $taxi_fare_details[0]['km_wise_fare'];
											$additional_fare_per_km=$taxi_fare_details[0]['additional_fare_per_km'];
											$city_model_fare=$taxi_fare_details[0]['city_model_fare'];
										}
												
										$baseFare = $base_fare;
										$total_fare = $base_fare;
										if($farecalculation_type ==1 || $farecalculation_type ==3)
										{
											$baseFare = $base_fare;
											if($distance < $min_km_range)//total_distance to distance
											{
												//min fare has set as base fare if trip distance 
												$baseFare = $min_fare;
												$total_fare = $min_fare;
											}
											//km wise fare
											else if($km_wise_fare == 1 && $distance >$min_km_range){
												$distance_after_minkm = $distance - $min_km_range;
												$additional_distance_fare = $distance_after_minkm*$additional_fare_per_km;
												$total_additional_fare = $min_fare +$additional_distance_fare+$baseFare;
												$city_fare_percent = ($city_model_fare/100);
												$total_fare = $total_additional_fare +($total_additional_fare*$city_fare_percent);
												
											}
											//km wise fare

											else if($distance <= $below_above_km_range)//total_distance  to distance
											{
												$fare = $distance * $below_km;
												$total_fare  = 	$fare + $base_fare ;
											}
											else if($distance > $below_above_km_range)//total_distance  to distance
											{
												$fare = $distance * $above_km;
												$total_fare  = 	$fare + $base_fare ;
											}
										}
										if($farecalculation_type ==2 || $farecalculation_type ==3)
										{
											/********** Minutes fare calculation ************/
											if($minutes_fare > 0)
											{
												$minutes_cost = $minutes * $minutes_fare;
												$total_fare  = $total_fare + $minutes_cost;
											}
											/************************************************/
										}

$check_fare=$total_fare;




				//Night Fare Calculation
										$parsed = date_parse($night_timing_from);
										$night_from_seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

										$parsed = date_parse($night_timing_to);
										$night_to_seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

										if ($night_charge == 1) 
										{				
											
											$night_start_date ='';
											$night_end_date ='';

											$night_start_date= date('Y-m-d')." ".$night_timing_from;
											$night_timing_to_value=$night_timing_to;
											$night_timing_from_value=$night_timing_from;
											$night_end_date= date('Y-m-d')." ".$night_timing_to;
											# check night start time is in previous day
											if(strtotime($night_end_date) < strtotime($night_start_date))
											{
												$night_start_date=date('Y-m-d', strtotime('-1 day'))." ".$night_timing_from_value;
											}
											else
											{
												$night_start_date= date('Y-m-d')." ".$night_timing_from_value;
											}
											$trip_time = date('Y-m-d')." ".$actualPickupTime;

											if( strtotime($actualPickupDateTime) >= strtotime($night_start_date) && strtotime($actualPickupDateTime) <= strtotime($night_end_date))
											{
												$nightfare_applicable = 1;
												$nightfare = ($night_fare/100)*$approx_fare;//night_charge%100;                                                                               

$trip_fare=$approx_fare-$nightfare;											//$total_fare  = $nightfare + $total_fare;
											}	
										}
										//Evening Fare Calculation
										$parsed_eve = date_parse($evening_timing_from);
										$evening_from_seconds = $parsed_eve['hour'] * 3600 + $parsed_eve['minute'] * 60 + $parsed_eve['second'];

										$parsed_eve = date_parse($evening_timing_to);
										$evening_to_seconds = $parsed_eve['hour'] * 3600 + $parsed_eve['minute'] * 60 + $parsed_eve['second'];

										if ($evening_charge == 1) 
										{
											if( $pickup_seconds >= $evening_from_seconds && $pickup_seconds <= $evening_to_seconds)
											{
												$evefare_applicable = 1;
												$eveningfare = ($evening_fare/100)*$approx_fare;//night_charge%100;
$trip_fare=$approx_fare-$eveningfare;
												//$total_fare  = $eveningfare + $total_fare;
											}
										}								
										//normal fare calculation end
									}
	
										
if($approx_fare>$check_fare)
{
$total_fare=$approx_fare;
}
										$booking_fee=0;
										$trip_fare = round($trip_fare,2);
										//~ echo $total_fare;exit;
										if($now_after == 1  || $now_after=='1')
										{
											
											$booking_fee= BOOKING_FEE;
											$booking_fee = (double)$booking_fee;
											$total_fare= $total_fare + BOOKING_FEE;
										}
										//~ echo $total_fare;exit;
										

										// Waiting Time calculation
										//$waiting_cost = $waiting_per_hour * $waiting_hours;

										//waiting time calculation per minute
										$waiting_minutes = $waiting_hours * 60;//hr to mt
										$waiting_cost = $waiting_per_minute * $waiting_minutes;
										//waiting time calculation per minute

										$waiting_cost = round($waiting_cost,2);
										$total_fare = $waiting_cost + $total_fare;
                                                                            $total_fare=$total_fare + $erp_charge;
										$parsed = date_parse($actualPickupTime);
										$pickup_seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
										
						//end of else
									
$trip_fare=round($trip_fare,2);							if($promocode != "")
									{
										$promodetails = $api_ext->getpromodetails($promocode,$passengers_id);
										# new promotion process
										if(!empty($promodetails))
										{
											$promo_discount = $promodetails['promo_discount'];
											# promo type [1 -amount, 2- discount]
											$promo_type = isset($promodetails['promo_type']) ? $promodetails['promo_type'] : '';
											$promodiscount_amount = Commonfunction::promotion_process($promo_type, $promo_discount, $total_fare, $trip_wallet = '1');	# $trip_wallet [1 -trip, 2 -wallet]
											$discount_fare = $promo_discount;
											$total_fare = $total_fare-$promodiscount_amount;												
										}
										else 
										{
											$promodiscount = 0;
											$promodiscount_amount = 0;
										}
									}
									
									$referral_discount = $siteinfo_details[0]['referral_discount'];
									$referdiscount = 0;
									// Company Tax amount Calculation
									
									$total_fare=round($total_fare,2);
									//driver subscription is only for enterprise pack
									if($driver_register_type == '2'){
										if(PACKAGE_TYPE!=3){
											$driver_register_type = '1';
										}
									}	
									
									# tax calculation
									# Admin tax will be applicable for single brand drivers 
									$tax = ($brand_type == 'S') ? TAX : $tax;
									$tax_amount = ($tax/100)*$total_fare;
									$tax_amount = commonfunction::amount_indecimal($tax_amount,'api');
									# tax amount will not be included with trip fare for outstation - Its handled by mobile developers
									if($rental_outstation != 2){
										$total_fare =  $total_fare+$tax_amount;
									}				
									$tax_amount = (string)$tax_amount;						
									
									$total_fare = ($fixedprice != 0) ? $fixedprice : $total_fare;
									//~ $trip_fare = round($total_fare,2);
									$trip_fare = commonfunction::amount_indecimal($trip_fare,'api');
									$total_fare = commonfunction::amount_indecimal($total_fare,'api');
									$subtotal_fare = $total_fare;//to display the actual total trip fare in complete trip page
									$usedWalAmount = 0;
									$totalFareAmt = 0;
									$passenger_payment_option = 0;
									
									if($travel_status != 5) 
									{
										//condition checked to avoid amount detection while trip is in waiting for payment status
										/** Referral amount detection if the passenger have amount in their wallet **/
										$show_credit_payment = 1;
										$totalPendingPercentage = $api->getpendingFarePercentage($trip_id);
										$splitPassDets = $api->getSplitPassengersDetails($trip_id);
										if(count($splitPassDets) > 0) {
											foreach($splitPassDets as $splitPass) 
											{						
												$farePercentage = ($splitPass['primary_pass_id'] == $splitPass['passenger_id']) ? ($splitPass['fare_percentage'] + $totalPendingPercentage) : $splitPass['fare_percentage'];
										
												$splittedFare = ($total_fare * $farePercentage) / 100;
												$splTotfare = round($splittedFare, 2);
												
												if($splitPass["approve_status"] == "A") 
												{
													list($actusedWalAmount,$actTotalFare) = $this->deductWalletAmount($splitPass['passenger_id'],$splitPass['wallet_amount'],$siteinfo_details[0]['referral_settings'],$splTotfare,$usedWalAmount);
												} 
												else 
												{
													/** Secondary passenger percent & amount to Zero **/
													if($splitPass['primary_pass_id'] != $splitPass['passenger_id']) 
													{
														$api->updateSecondaryPercentAmtPrimary($trip_id,$splitPass['passenger_id'],$splitPass['primary_pass_id']);
													}
													$actusedWalAmount = $actTotalFare = 0;
												}
										
												#update used wallet amount in passenger split details
												$api->updateUsedWalletAmount($actusedWalAmount,$splitPass['passenger_id'],$trip_id);
												$usedWalAmount = $usedWalAmount + $actusedWalAmount;
												$totalFareAmt = $totalFareAmt + $actTotalFare;
												$passenger_payment_option = ($splitPass['primary_pass_id'] == $splitPass['passenger_id']) ? $splitPass['passenger_payment_option'] : 0;
											}
										}
										$usedAmount = $usedWalAmount;
										$total_fare = $totalFareAmt;
										//to update the used wallet amount and  for a trip in passenger log table
										$message_status = 'R';$driver_reply='A';$journey_status=5; 
										// Waiting for Payment
										//total_distance to distance
// Way Points
$waypoints=isset($mobiledata['waypoints'])?$mobiledata['waypoints']:array();											
										$journey = $api->update_journey_statuswith_drop($trip_id,$message_status,$driver_reply,$journey_status,$drop_latitude,$drop_longitude,$drop_location,$drop_time,$distance,$waiting_hours,$tax,$driver_app_version,$usedAmount,$waiting_per_minute,$farePerMin,$waypoints);//hr to mt
										/** Referral amount detection if the passenger have amount in their wallet **/
										//update the wallet amount in referred driver's row
										$referredDriver = $api->getReferredDriver($driver_id);
										if($referredDriver > 0) 
										{
											$driverReferral = $api->getDriverReferralDetails($referredDriver);
											if(count($driverReferral) > 0)
											{
												$wallAmount = $driverReferral[0]['registered_driver_wallet'] + $driverReferral[0]['registered_driver_code_amount'];
												$update_referral_array  = array(
													'registered_driver_wallet' => $wallAmount,
													'registered_driver_id'=>$driverReferral[0]['registered_driver_id']
												);
												$result = $extended_api->update_driver_referral_list($update_referral_array);
												//update referrer earned status in registered driver's row while he completing his first trip
												$update_referral_array  = array(
													'referral_status' => 1,
													'registered_driver_id'=>$driver_id
												);
												$result = $extended_api->update_driver_referral_list($update_referral_array);
											}
										}
									} else {
										$usedAmount = $used_wallet_amount;
										$total_fare = $total_fare - $used_wallet_amount;
									}
									$referdiscount = '0.00';
									$discount_fare = round($discount_fare,2);
									$nightfare = round($nightfare,2);							
									if(SMS ==1)
									{
										$message_details = $this->commonmodel->sms_message_by_title('complete_trip');
										if(count($message_details) > 0) 
										{
											$to = $passengerMobile;
											$message = $message_details[0]['sms_description'];
											$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
											$this->commonmodel->send_sms($to,$message);
										}
									}
									/* create user logs */
							        $user_unique = $get_passenger_log_details[0]->driver_id.__('log_driver_type');

// Notification Logger -- Start
$not_project=array();
$not_project['profile_picture']=1;
$not_project['name']=1;
$not_match=array();
$not_match['_id']=(int)$get_passenger_log_details[0]->driver_id;
$not_result=$this->commonmodel->dynamic_findone_new(MDB_PEOPLE,$not_match,$not_project);
$not_name=isset($not_result['name'])?$not_result['name']:"";
$notification_content=array();
$notification_content['msg']=__('notification_complete_booking_driver',array(':drivername' => $not_name));
$notification_content['domain']=SUBDOMAIN_NAME;
$notification_content['image']=isset($not_result['profile_picture'])?$not_result['profile_picture']:"";
$notification_content['type']='DRIVER_COMPLETE_BOOKING';
// Notification Logger -- End		

							        $log_array = array(
						                'user_id' => (int)$get_passenger_log_details[0]->driver_id,
						                'user_type' => __('log_driver_type'),
						                'login_type' => __('log_device'),
						                'activity' => __('log_complete_trip'),
'notification_content' =>$notification_content,
'notification_type' =>(int)1,							                
						                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
						            );
							        commonfunction::save_user_logs($log_array, $user_unique);
							        /* create user logs */
									/** Update Driver Status End**/
									//variable to know whether the passenger have credit card
									$check_card_data = $api_ext->check_passenger_card_data($passengers_id);
									//~ $credit_card_sts = ($check_card_data == 0) ? 0:SKIP_CREDIT_CARD;
									$credit_card_sts = ($check_card_data == 0) ? 0:1;
									//condition checked to remove creditcard key value from array
									if($credit_card_sts == 0) 
									{
										//condition checked to remove credit card if the passenger dont have credit card details
										$smpleArr = array();
										foreach($gateway_details as $key=>$valArr) {
											if($valArr['pay_mod_id'] != 2) {
												$smpleArr[] = $valArr;
												if(SKIP_CREDIT_CARD == 0)
													break;
											}
										}
										$gateway_details = $smpleArr;
									}
							
									$passenger_payment_option_array = array();
									//if the completed trip is split fare type means dont show the new card option
									if($splitTrip == 1){
										//condition checked to remove credit card if the passenger dont have credit card details
										$smpleArr = array();
										foreach($gateway_details as $key=>$valArr){
											if($valArr['pay_mod_id'] != 1 && $valArr['pay_mod_id'] != 3) {
												$smpleArr[] = $valArr;
											}
										}
										$gateway_details = $smpleArr;
									} 
									else 
									{
										if(count($gateway_details) > 0 && $passenger_payment_option > 0) 
										{
											foreach($gateway_details as $valArr) 
											{
												/*if($passenger_payment_option == $valArr["pay_mod_id"]) {
													
												}*/
												$passenger_payment_option_array[] = $valArr;
											}
										}
									}
									//~ print_r($passenger_payment_option_array);exit;
									$gateway_details = (count($passenger_payment_option_array) > 0) ? $passenger_payment_option_array : $gateway_details;
								
									//to change the payment mode detail if trip fare is zero
									if($total_fare == 0) {
										$gateway_details = array("0"=>array("pay_mod_id"=>"5","pay_mod_name"=>"Wallet","pay_mod_default"=>"1"));
									}
							
									//the hours value has been changed to seconds
									$convertSeconds = $waiting_hours * 3600;
									$converthours = floor($convertSeconds / 3600);
									$convertmins = floor(($convertSeconds - ($converthours*3600)) / 60);
									$convertsecs = floor($convertSeconds % 60);
									$waitH = ($converthours < 10) ? '0'.$converthours : $converthours;
									$waitM = ($convertmins < 10) ? '0'.$convertmins : $convertmins;
									$waitS = ($convertsecs < 10) ? '0'.$convertsecs : $convertsecs;
									$waitingTime = ($waitH != "00") ? $waitH.':'.$waitM.':'.$waitS.' Hours' :  $waitM.':'.$waitS.' Mins';
									$total_distance = ($total_distance == '') ? '0' : $total_distance;
							
									$baseFare = commonfunction::amount_indecimal($baseFare,'api');
									$usedAmount = commonfunction::amount_indecimal($usedAmount,'api');
									$minutes_cost = commonfunction::amount_indecimal($minutes_cost,'api');
									$total_fare = commonfunction::amount_indecimal($total_fare,'api');
									$subtotal_fare = commonfunction::amount_indecimal($subtotal_fare,'api');
									$tax_amount = commonfunction::amount_indecimal($tax_amount,'api');
									$waiting_cost = commonfunction::amount_indecimal($waiting_cost,'api');
									$eveningfare = commonfunction::amount_indecimal($eveningfare,'api');
									$nightfare = commonfunction::amount_indecimal($nightfare,'api');
									$promodiscount_amount = commonfunction::amount_indecimal($promodiscount_amount,'api');
									$discount_fare = commonfunction::amount_indecimal($discount_fare,'api');
									$trip_fare = commonfunction::amount_indecimal($trip_fare,'api');
									$referdiscount = commonfunction::amount_indecimal($referdiscount,'api');
									$erp_charge = commonfunction::amount_indecimal($erp_charge,'api');
									$booking_fee = commonfunction::amount_indecimal($booking_fee,'api');
									
									# minutes travelled conversion
									/*if($trip_type != '3')
										$minutes = commonfunction::convertToHoursMins($minutes, '%02d Hrs %02d Mts');*/
									
									
									$detail = array(
										"trip_id" => $trip_id,
										"pass_id"=>$passengers_id,
										"distance"=> $total_distance,
										"trip_fare"=>$trip_fare,
										"referdiscount"=>$referdiscount,
										"promo_discount_per"=>$promo_discount,
										"promodiscount_amount"=>$promodiscount_amount,
										"passenger_discount"=>$discount_fare,
										"nightfare_applicable"=>$nightfare_applicable,
										"nightfare"=>$nightfare,
										"eveningfare_applicable"=>$evefare_applicable,
										"eveningfare"=>$eveningfare,
										"waiting_time"=>$waitingTime,
										"waiting_cost"=>$waiting_cost,
										"tax_amount"=>$tax_amount,
										"subtotal_fare"=>$subtotal_fare,
										"total_fare"=>$total_fare,
										"gateway_details"=>$gateway_details,
										"pickup"=>$pickup,"drop"=>$drop_location,
										"company_tax"=>$tax,
										/*"waiting_per_hour" => $waiting_per_hour,*/
										"waiting_per_minute" => $waiting_per_minute,
										"roundtrip"=> $roundtrip,
										"minutes_traveled"=>$minutes,
										"minutes_fare"=>$minutes_cost,
										"metric"=>$default_unit,
										"credit_card_status"=>$credit_card_sts,
										"wallet_amount_used"=>$usedAmount,
										"base_fare"=>$baseFare,
										"street_pickup"=>0,
										"fare_calculation_type"=>$farecalculation_type,
										"model_fare_type"=>$km_wise_fare,
										"promo_type" => $promo_type,
										"trip_type" => $trip_type,
										"os_plan_fare" => $os_plan_fare,
										"os_plan_distance" => $os_plan_distance,
										"os_plan_duration" => $os_plan_duration,
										"os_additional_fare_per_distance" => $os_additional_fare_per_distance,
										"os_additional_fare_per_hour" => $os_additional_fare_per_hour,
										"os_duration" => $os_duration,
										"os_distance_unit" => $os_distance_unit,
										"trip_start_time" => $actualPickupDateTime,
										"booking_fee" => $booking_fee,
										"erp_charge" => $erp_charge,
										"trip_end_time" => $drop_time,
									);
									//~ print_r($detail);exit;
									$message = array("message"=>__('trip_completed_driver'),"detail"=>$detail,"status"=>4);
									/** Send Trip fare details to Driver ***/
									$d_device_token = $get_passenger_log_details[0]->driver_device_token;
									$d_device_type = $get_passenger_log_details[0]->driver_device_type;
									/** Send Trip fare details to Passenger ***/
									$pushmessage = array("message"=>__('trip_completed'),"status"=>4);
									$p_device_token = $get_passenger_log_details[0]->passenger_device_token;
									$p_device_type = $get_passenger_log_details[0]->passenger_device_type;
								}
								else if($travel_status == 1)
								{
									$message = array("message" => __('trip_already_completed'),"status"=>-1);	
								}		
								else
								{
									$message = array("message" => __('trip_not_started'),"status"=>-1);
								}
							}
						}
						else
						{
							$message = array("message" => __('invalid_trip'),"status"=>-1);	
						}
					}
					else
					{
						$message = array("message" => __('invalid_trip'),"status"=>-1);	
					}
				}
				else
				{
					$message = array("message" => __('invalid_request'),"status"=>-1);	
				}
				//~ print_r($message);exit;
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$detail,$extended_api,$gateway_details,$get_passenger_log_details,$taxi_fare_details,$totalPendingPercentage,$splitPassDets);
			break;

			case 'get_trip_detail':
				$array = $mobiledata;
				$trip_id = $array['trip_id'];
				//passenger_id params come from ios passenger app only
				$passenger_id = isset($array['passenger_id']) ? $array['passenger_id'] : '';
				if($trip_id != null)
				{
					$trip_id = $trip_id;						
					$api_model = Model::factory(MOBILEAPI_107);			
					$get_passenger_log_details = $api_model->get_trip_detail($trip_id,$passenger_id);
					if(count($get_passenger_log_details)>0)
					{
						foreach($get_passenger_log_details as $journey)
						{
							$driver_id = $journey->driver_id;
							$taxi_id = $journey->taxi_id;
							$company_id = $journey->company_id;
							$driver_image_name = $journey->driver_image;
							$passenger_image = $journey->passenger_image;
							$trip_details['taxi_min_speed']=$journey->taxi_min_speed;
							$trip_details['trip_id'] = $journey->passengers_log_id;
							$trip_details['current_location'] = $journey->pickup_location;
							$trip_details['pickup_latitude'] = $journey->pickup_latitude;
							$trip_details['pickup_longitude'] = $journey->pickup_longitude;
							$trip_details['waiting_fare_mt'] = $journey->waiting_fare_mt;
							$trip_details['drop_location'] = $journey->drop_location;
							$trip_details['drop_latitude'] = $journey->drop_latitude;
							$trip_details['drop_longitude'] = $journey->drop_longitude;
							$trip_details['drop_time'] = ($journey->drop_time != "0000-00-00 00:00:00") ? Commonfunction::getDateTimeFormat($journey->drop_time,3) : "";
							$trip_details['pickup_date_time'] = ($journey->actual_pickup_time != "0000-00-00 00:00:00") ? Commonfunction::getDateTimeFormat($journey->actual_pickup_time,3) : Commonfunction::getDateTimeFormat($journey->pickup_time,3);
							$trip_details['pickup_time'] = ($journey->actual_pickup_time != "0000-00-00 00:00:00") ? date("H:i:s",strtotime($journey->actual_pickup_time)) : "";
							$trip_details['booking_time'] = Commonfunction::getDateTimeFormat($journey->pickup_time,3);
							$trip_details['time_to_reach_passen'] = str_replace('Min','',$journey->time_to_reach_passen);		
							$trip_details['no_passengers']= $journey->no_passengers;	
							$trip_details['rating'] = $journey->rating;
							$trip_details['notes']= $journey->notes_driver;																
							$trip_details['driver_name'] = $journey->driver_name;								
							$trip_details['driver_id'] = $journey->driver_id;							
							$trip_details['taxi_id'] = $journey->taxi_id;
							$trip_details['taxi_number'] = $journey->taxi_no;
							$trip_details['driver_phone'] = !empty($journey->driver_twilio_number) ? trim($journey->driver_twilio_number) : trim($journey->driver_phone);
							$trip_details['passenger_phone'] = !empty($journey->passenger_phone) ? $journey->passenger_phone : "";
							$trip_details['passenger_name'] = $journey->passenger_name;									
							$passengerWallAmt = $journey->wallet_amount;									
							$trip_details['travel_status'] = $journey->travel_status;	
							$driver_reply = $journey->driver_reply;	
							$trip_details['bookedby'] =  $journey->bookby;//1-passenger, 2-Dispatcher, 3-Driver
							$trip_details['street_pickup_trip'] = ($journey->bookby == 3) ? 1 : 0;
							$trip_details['waiting_time'] = $journey->waiting_time;
							$trip_details['waiting_fare'] =  ($journey->waiting_cost != "" && $journey->waiting_cost != null) ? $journey->waiting_cost : 0;
							$trip_details['waiting_fare'] = commonfunction::amount_indecimal($trip_details['waiting_fare'],'api');
							$trip_details['distance'] =  $journey->distance;
							$trip_details['actual_distance'] =  $journey->actual_distance;
							$trip_details['metric'] = $journey->metric;
							$trip_details['amt'] = commonfunction::amount_indecimal($journey->amt,'api');//round($journey->amt,2);		
							$trip_details['actual_paid_amount'] = ($journey->is_split_trip == 1 && isset($journey->split_paid_amount)) ? commonfunction::amount_indecimal($journey->split_paid_amount,'api') : commonfunction::amount_indecimal($journey->actual_paid_amount,'api');		
							$trip_details['used_wallet_amount'] = ($journey->is_split_trip == 1 && isset($journey->split_wallet)) ? ($journey->split_wallet) : $journey->used_wallet_amount;		
							$trip_details['used_wallet_amount'] = commonfunction::amount_indecimal($trip_details['used_wallet_amount'],'api');
							$trip_details['job_ref'] = $journey->job_ref;		
							$trip_details['payment_type'] = $journey->payment_type;
							$trip_details['fare_calculation_type'] = $journey->fare_calculation_type;			
							$trip_details['payment_type_label'] = ($journey->payment_type == 1) ? __('cash'):(($journey->payment_type == 5) ? __('wallet') : __('card'));		
							$trip_details['taxi_speed'] = $journey->taxi_speed;
							$trip_details['waiting_fare_hour'] = commonfunction::amount_indecimal($journey->waiting_fare_hour,'api');
							// Convert to Hour
						$trip_details['waiting_fare_hour'] = commonfunction::amount_indecimal((float)$journey->waiting_fare_mt*60,'api');
							$trip_details['fare_per_minute'] = commonfunction::amount_indecimal($journey->fare_per_minute,'api');
							$subtotal = $journey->tripfare + $trip_details['waiting_fare'] + $journey->eveningfare + $journey->nightfare;
							$trip_details['subtotal'] = commonfunction::amount_indecimal($journey->tripfare,'api');
							$subtotal = $journey->tripfare + $trip_details['waiting_fare'] + $journey->eveningfare + $journey->nightfare;
							$trip_details['subtotal'] = commonfunction::amount_indecimal($subtotal,'api');
							$trip_details['minutes_fare'] = commonfunction::amount_indecimal($journey->minutes_fare,'api');
							$trip_details['distance_fare'] = commonfunction::amount_indecimal((($journey->tripfare) - ($journey->minutes_fare)),'api');
							$distance_fare_metric =  ($journey->distance > 1) ? ($trip_details['distance_fare'] / $journey->distance) : $trip_details['distance_fare']; 
							$trip_details['distance_fare_metric'] = commonfunction::amount_indecimal($distance_fare_metric,'api');
							$trip_details['trip_minutes'] = $journey->trip_minutes;
							$trip_details['promocode_fare'] = commonfunction::amount_indecimal($journey->promocode_fare,'api');
							$trip_details['eveningfare'] = commonfunction::amount_indecimal($journey->eveningfare,'api');
							$trip_details['nightfare'] = commonfunction::amount_indecimal($journey->nightfare,'api');
							$trip_details['tax_percentage'] = $journey->tax_percentage;
							$trip_details['tax_fare'] = commonfunction::amount_indecimal($journey->tax_fare,'api');
							$trip_details['isSplit_fare'] = (int)$journey->is_split_trip;//0-Normal trip, 1-Split Trip
							//variable to know whether the passenger have credit card
							$check_card_data = $api_ext->check_passenger_card_data($journey->passengers_id);
							$totalCancelFare = $api_ext->get_passenger_cancel_faredetail($trip_id);
							$passengerReferrDet = $api_ext->check_passenger_referral_amount($passenger_id);

							$fare_setting = $api_ext->get_cancel_setting($company_id); 
							//check unused referral amount with existing wallet amount
							$referralAmt = (isset($passengerReferrDet[0]['referral_amount'])) ? commonfunction::amount_indecimal($passengerReferrDet[0]['referral_amount'],'api') : 0;
							$reducAmt = ($referralAmt != 0) ? ($passengerWallAmt - $referralAmt) : $passengerWallAmt;

							$credit_card_sts = (($check_card_data == 0) || ($passengerWallAmt > 0 && $reducAmt >= $totalCancelFare) || ($fare_setting == 0)) ? 0 : ((FARE_SETTINGS == 1) ? SKIP_CREDIT_CARD : $fare_setting);
							
							$trip_details['credit_card_status'] = $credit_card_sts;
							//condition to check the passenger is primary or not
							$trip_details['is_primary'] = (!empty($passenger_id) && $passenger_id != $journey->passengers_id) ? false : true;
							$trip_details['trip_duration'] = "0";
							if($trip_details['drop_time'] != "") {
								//total trip duration
								$trip_seconds = strtotime($trip_details['drop_time']) - strtotime($trip_details['pickup_time']);
								$trip_days    = floor($trip_seconds / 86400);
								$trip_hours   = floor(($trip_seconds - ($trip_days * 86400)) / 3600);
								$trip_minutes = floor(($trip_seconds - ($trip_days * 86400) - ($trip_hours * 3600))/60);
								$trip_seconds = floor(($trip_seconds - ($trip_days * 86400) - ($trip_hours * 3600) - ($trip_minutes*60)));
								$trip_hours = ($trip_hours < 10) ? '0'.$trip_hours : $trip_hours;
								$trip_minutes = ($trip_minutes < 10) ? '0'.$trip_minutes : $trip_minutes;
								$trip_seconds = ($trip_seconds < 10) ? '0'.$trip_seconds : $trip_seconds;
								$trip_details['trip_duration'] = $trip_hours.":".$trip_minutes.":".$trip_seconds;
							}
							$mapurl = '';
							//map image for completed trips in trip detail page
							if($journey->travel_status == 1) 
							{
								//print_r($journey->active_record);exit;
								if(file_exists(DOCROOT.MOBILE_TRIP_DETAIL_MAP_IMG_PATH.$trip_id.".png")) {
									$mapurl = URL_BASE.MOBILE_TRIP_DETAIL_MAP_IMG_PATH.$trip_id.".png";
								} 
								else 
								{
									$path = $journey->active_record;
									$path = str_replace('],[', '|', $path);
									$path = str_replace(']', '', $path);
									$path = str_replace('[', '', $path);
									$path = explode('|',$path);$path = array_unique($path);
									include_once MODPATH."/email/vendor/polyline_encoder/encoder.php";
									$polylineEncoder = new PolylineEncoder();
									if(!empty($path))
									{
										foreach($path as $values)
										{
											$values = explode(',',$values);
											if(isset($values[0]) && isset($values[1])){ 
												$polylineEncoder->addPoint($values[0],$values[1]);
												$polylineEncoder->encodedString();
											}
											//~ $polylineEncoder->addPoint($values[0],$values[1]);
											//~ $polylineEncoder->encodedString();
										}
									}
									$encodedString = $polylineEncoder->encodedString();
											
									$marker_end = $journey->drop_latitude.','.$journey->drop_longitude;
									$marker_start = $journey->pickup_latitude.','.$journey->pickup_longitude;
									$startMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'startMarker.png';
									$endMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'endMarker.png';
									if($marker_end != 0) 
									{
										$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x640&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&markers=icon:$endMarker%7C$marker_end&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
									} else {
										$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x640&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
									}
									if(isset($mapurl) && $mapurl != "") 
									{
										$file_path = DOCROOT.MOBILE_TRIP_DETAIL_MAP_IMG_PATH.$trip_id."ss.png";
										@file_put_contents($file_path,@file_get_contents($mapurl));
										$mapurl = URL_BASE.MOBILE_TRIP_DETAIL_MAP_IMG_PATH.$trip_id."ss.png";
									}
								} 
							}
							$trip_details['map_image'] = $mapurl;
							
							# rental & outstation start
							$trip_type = '0';
							$trip_distance =isset($journey->trip_distance)?$journey->trip_distance:'';
							$trip_minutes =isset($journey->trip_minutes)?$journey->trip_minutes:'';
							$rental_outstation =isset($journey->rental_outstation)?$journey->rental_outstation:'';
							$total_fare = $additional_distance_fare = $additional_time_fare = 0;
							$trip_details['trip_type'] = $trip_type;
							//~ echo $rental_outstation;exit;
							if((int)$rental_outstation >0)
							{
								
								//~ $trip_details = [];
								
								$rent_out_tour_id = isset($journey->rent_out_tour_id)?$journey->rent_out_tour_id:0;
								# rental outstation calculation
								$rental_details['base_fare'] = $baseFare = isset($journey->base_fare)?$journey->base_fare:0;
								$rental_details['plan_distance'] = $plan_distance = isset($journey->plan_distance)?$journey->plan_distance:0;
								$rental_details['plan_duration'] = $plan_duration = isset($journey->plan_duration)?$journey->plan_duration:0;
								$rental_details['plan_distance_unit'] = $plan_distance_unit = isset($journey->plan_distance_unit)?$journey->plan_distance_unit:UNIT_NAME;
								$rental_details['additional_fare_per_distance'] = $additional_fare_per_distance = isset($journey->additional_fare_per_distance)?$journey->additional_fare_per_distance:0;
								$rental_details['additional_fare_per_hour'] = $additional_fare_per_hour = isset($journey->additional_fare_per_hour)?$journey->additional_fare_per_hour:0;
								$rental_details['trip_distance'] = $trip_distance;
								$rental_details['trip_minutes'] = $trip_minutes;
										
								$rental_os_data = commonfunction::rental_outstation_calc($rental_details);
								$trip_details['additional_distance_fare'] = $rental_os_data['additional_distance_fare'];
								$trip_details['additional_time_fare'] = $rental_os_data['additional_time_fare'];
								$trip_details['fare'] = $rental_os_data['fare'];
								$trip_details['base_fare'] = $rental_os_data['base_fare'];
								$trip_details['paid_amount'] = $rental_os_data['paid_amount'];
								
								if($rental_outstation==1)
									$trip_type = '2';
								else
									$trip_type = '3';							
								
								
								$trip_details['trip_id'] = $journey->passengers_log_id;
								$trip_details['trip_type'] = $trip_type;
								$trip_details['fare'] = commonfunction::amount_indecimal($journey->amt,'api');//round($journey->amt,2);		
								$trip_details['pickup'] = $journey->pickup_location;
								$trip_details['payment_type'] = $journey->payment_type;
								$trip_details['paid_amount'] = ($journey->is_split_trip == 1 && isset($journey->split_paid_amount)) ? commonfunction::amount_indecimal($journey->split_paid_amount,'api') : commonfunction::amount_indecimal($journey->actual_paid_amount,'api');
								$trip_details['payment_type'] = $journey->payment_type;		
								$trip_details['base_fare'] = $journey->base_fare;	
								$trip_details['waiting_fare'] =  ($journey->waiting_cost != "" && $journey->waiting_cost != null) ? $journey->waiting_cost : 0;
								$trip_details['waiting_fare'] = commonfunction::amount_indecimal($trip_details['waiting_fare'],'api');	
								$trip_details['nightfare'] = commonfunction::amount_indecimal($journey->nightfare,'api');
								$trip_details['eveningfare'] = commonfunction::amount_indecimal($journey->eveningfare,'api');
								$trip_details['promotion'] = commonfunction::amount_indecimal($journey->promocode_fare,'api');
								$trip_details['tax_fare'] = commonfunction::amount_indecimal($journey->tax_fare,'api');
								$trip_details['used_wallet_amount'] = ($journey->is_split_trip == 1 && isset($journey->split_wallet)) ? ($journey->split_wallet) : $journey->used_wallet_amount;
								$trip_details['trip_minutes'] = $journey->trip_minutes;		
								
								//~ print_r($trip_details);exit;
							}
						}
						
						/************************************Driver Image *******************************/					
						$driver_image = $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DRIVER_IMGPATH.'thumb_'.$driver_image_name;
						if(file_exists($driver_image) && ($driver_image_name !=''))
						{
							$driver_image = URL_BASE.SITE_DRIVER_IMGPATH.'thumb_'.$driver_image_name;
						}
						else
						{
							//~ $driver_image = URL_BASE."/public/images/noimages109.png";
							$driver_image = $img = URL_BASE.PUBLIC_IMAGES_FOLDER."noimages.jpg";
						}		
						$trip_details['driver_image'] = $driver_image;
					
						/*************************** Passenger Image ************************************/
						if((!empty($passenger_image)) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$passenger_image))
						{ 
							$profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$passenger_image; 
						}
						else
						{ 
							$profile_image = (isset($trip_details['bookedby']) && $trip_details['bookedby'] == 3) ? URL_BASE.PUBLIC_IMAGES_FOLDER."streetpickup_image.png" : URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
						}	
						$trip_details['passenger_image'] = $profile_image;
						$trip_details['driver_latitute'] = $trip_details['driver_longtitute'] = '0.0';
						$current_driver_status = $api_ext->get_driver_current_status($driver_id);
						
						if(count($current_driver_status)>0)
						{
							foreach($current_driver_status as $driver_details)
							{
								$trip_status = $driver_details->status;
								$trip_details['driver_latitute'] = $driver_details->latitude;
								$trip_details['driver_longtitute'] = $driver_details->longitude;
							}
						}
					
						$trip_details['driver_status'] =  (isset($trip_status) && $trip_status != 'B') ?  $trip_status : 'F';

						/*if($trip_details['travel_status'] == 2){
							$trip_details['driver_status'] = "B";
						}elseif($trip_details['travel_status'] == 5){
							$trip_details['driver_status'] = "A";
						}else{
							$trip_details['driver_status'] = "F";
						}*/

						$dresult = $api->driver_ratings($driver_id);
						//~ print_r($dresult);exit;
						$totalrating=0;
						if(count($dresult) > 0)
						{
							$overall_rating = $i= $trip_total_with_rate=0;								
							foreach($dresult as $comments)
							{
								if($comments['rating'] != 0)
									$trip_total_with_rate +=1;
								
								$overall_rating += $comments['rating'];
								$i++;	
							}
																	
							if($trip_total_with_rate!=0 && $overall_rating!=0){
								$totalrating = $overall_rating/$trip_total_with_rate;
							}else{
								$totalrating = 5;
							}			
							$totalrating = round($totalrating);		
						}
						else
						{
							$totalrating = 5;
						}				
						//~ echo $totalrating;exit;
						if($trip_details['travel_status'] == 1 || $trip_details['travel_status'] == 4 || ($trip_details['travel_status'] ==9 && $driver_reply == 'C')){
							$trip_details['driver_rating'] = $trip_details['rating'];
						}else{
							$trip_details['driver_rating'] = $totalrating;
						}							
					
						/** Split Fare Details **/
						$splitApproveArr = array();
						if($trip_details['isSplit_fare'] == 1)
						{
							$splitApproveArr = $api_ext->getSplitFareStatus($trip_id);
							if(count($splitApproveArr) > 1)
							{
								foreach($splitApproveArr as $splkey=>$splits)
								{
									if((!empty($splits['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.$splits['profile_image']))
									{ 
										$profile_image = URL_BASE.PASS_IMG_IMGPATH.$splits['profile_image']; 
									}
									else{ 
										$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
									}
									$splitApproveArr[$splkey]['profile_image'] = $profile_image;
									$splittedFare = ($trip_details['amt'] * $splits['fare_percentage']) / 100;
									$splitApproveArr[$splkey]['splitted_fare'] = commonfunction::amount_indecimal($splittedFare, 'api');
								}
							}
						}
						$trip_details['splitFareDetails'] = $splitApproveArr;
						//print_r($trip_details);exit;
						if(count($get_passenger_log_details) == 0)
						{
							$message = array("message" => __('try_again'),"status"=>0,"site_currency"=>$this->site_currency);	
						}
						else
						{
							$mes = __('success');
							if($trip_details['travel_status'] == 5) {
								$mes = __('trip_waiting_payment');
							} 
							else if($trip_details['travel_status'] == 4) 
							{
								$mes = __('cancel_by_passenger');
							}						
							$message = array("message" => $mes,"detail"=>$trip_details,"status" => 1,"site_currency"=>$this->site_currency);
						}	
					}
					else
					{
						$message = array("message" => __('invalid_trip'),"status"=>-1,"site_currency"=>$this->site_currency);	
					}									
				}
				else
				{
					$message = array("message" => __('invalid_trip'),"status"=>-1,"site_currency"=>$this->site_currency);	
				}	
				//~ print_r($message);		exit;
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);	
				//unset($message,$api_model,$profile_image,$trip_details,$get_passenger_log_details);
			break;

			case 'forgot_password':
				$array_values = $mobiledata;						
				$message="";
				if($array_values['user_type'] == 'P')
				{
					$phone_exist = $api_ext->check_phone_passengers($array_values['phone_no'],$default_companyid,$array_values['country_code']);
					$phone_no = $array_values['country_code'].'-'.$array_values['phone_no'];
				}
				else
				{
					$phone_exist = $api_ext->check_phone_people($array_values['phone_no'],'D',$default_companyid);
						$phone_no = $array_values['phone_no'];
				}
				if($phone_exist > 0)
				{
					$forgot_result = $api_ext->get_passenger_details_phone($array_values,$default_companyid);
					if(count($forgot_result) > 0) 
					{ 	
						/* Added for language email template */
						/**To generate random key if user enter email at forgot password**/
						$random_key = text::random($type = 'alnum', $length = 7);
						//function to update new password
						$newPassUpdate = $api->new_password_update($array_values,$random_key,$default_companyid);				
						$email = $forgot_result[0]['email'];
						$replace_variables=array(REPLACE_LOGO=>URL_BASE.SITE_LOGO_IMGPATH.$this->domain_name.'_email_logo.png',REPLACE_SITENAME=>$this->app_name,REPLACE_USERNAME=>ucfirst($forgot_result[0]['name']),REPLACE_MOBILE=>$phone_no,REPLACE_PASSWORD=>$random_key,REPLACE_SITELINK=>URL_BASE.'users/contactinfo/',REPLACE_SITEEMAIL=>CONTACT_EMAIL,REPLACE_SITEURL=>URL_BASE,SITE_DESCRIPTION=>$this->app_description,REPLACE_COPYRIGHTS=>SITE_COPYRIGHT,REPLACE_COPYRIGHTYEAR=>COPYRIGHT_YEAR);
						$message=$this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.'user-forgotpassword.html',$replace_variables);						
													
						$emailTemp = $this->commonmodel->get_email_template('forgot_password', $this->email_lang);
						if(isset($emailTemp['status']) && ($emailTemp['status'] == '1'))
						{	
							$email_description = isset($emailTemp['description']) ? $emailTemp['description']: '';
							$subject = isset($emailTemp['subject']) ? $emailTemp['subject']: '';
								$message           = $this->emailtemplate->emailtemplate($email_description, $replace_variables);
							$to = $email;
							$from              = CONTACT_EMAIL;
							//~ $subject = __('forgot_password_subject')." - ".$this->app_name;	
							$subject = $subject." - ".$this->app_name;	
							$redirect = "no";
							if(SMTP == 1)
							{
								include($_SERVER['DOCUMENT_ROOT']."/modules/SMTP/smtp.php");
							}
							else
							{
								// To send HTML mail, the Content-type header must be set
								$headers  = 'MIME-Version: 1.0' . "\r\n";
								$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
								// Additional headers
								$headers .= 'From: '.$from.'' . "\r\n";
								$headers .= 'Bcc: '.$to.'' . "\r\n";
								mail($to,$subject,$message,$headers);
							}
						}
							
						//free sms url with the arguments
						if(SMS == 1)
						{
							$userType = ($array_values['user_type']=='P') ? 'P' : 'D';
							$phoneno = $this->commonmodel->getuserphone($userType,$email);
							$message_details = $this->commonmodel->sms_message_by_title('forgot_password_sms');
							if(count($message_details) > 0) {
								$to = $phoneno;
								$message = $message_details[0]['sms_description'];
								$message = str_replace("##PASSWORD##",$random_key,$message);
								$this->commonmodel->send_sms($to,$message);
							}
						}							
						$message = array("message" => __('forgot_pass_success'),'status' => 1);
						/* create user logs */
				        $user_unique = $forgot_result[0]['_id'].$array_values['user_type'];
				        $log_array = array(
			                'user_id' => (int)$forgot_result[0]['_id'],
			                'user_type' => $array_values['user_type'],
			                'login_type' => __('log_device'),
			                'activity' => __('log_forgot_pwd'),
			                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
			            );
				        commonfunction::save_user_logs($log_array, $user_unique);
				        /* create user logs */
					}
					else
					{
						$message = array("message" => __('invalid_user'),'status' => 2);
					}
				}
				else
				{
					$message = array("message" => __('invalid_user'),"status"=> 2);							
				}					
							
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$replace_variables);
			break;

			case 'driver_login':
				$array = $mobiledata;
				if(!empty($array))
				{
					$array['company_id'] = $default_companyid;
					$array['user_type'] = 'D';
					$validator = $this->driver_login_validation($array);
					
					if($validator->check())
					{
						$phone_exist = $api_ext->check_phone_people(urldecode($array['phone']),'D',$default_companyid);
						if($phone_exist == 0)
						{
							$message = array("message" =>  __('phone_not_exists'),"status"=> 2);
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							exit;
						}		
						else
						{
							$result = $api->driver_login(urldecode($array['phone']),urldecode($array['password']),$default_companyid);
							if(count($result) > 0)
							{
								$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
								//Checking the User Status
								$user_status = $result[0]['status'];
								$login_status = $result[0]['login_status'];
								$login_from = $result[0]['login_from'];
								$device_token = $result[0]['device_token'];
								$device_id = $result[0]['device_id'];
								$company_id = $result[0]['company_id'];
								$driver_id = $result[0]['id'];
								$driver_first_login = $result[0]['driver_first_login'];
								$driver_details = $api_ext->driver_profile($driver_id);
								$freeStatus = isset($driver_details[0]['status'])?$driver_details[0]['status']:'';
								
								
								if($user_status == 'D')
								{
									$driver_details[0]["driver_type"]='D';
								}
								$driver_type = isset($driver_details[0]["driver_type"]) ? $driver_details[0]["driver_type"] : "";
								if($user_status == 'T')
								{
									$message = array("message" => __('account_deactivte'),"status"=> 0);
								}
								else if(($login_status == 'S') && ($login_from == 'D') && ($device_id != $array['device_id']))
								{				
									$deviceType = $array['device_type'];
									$deviceToken = $array['device_token'];
									$deviceId = $array['device_id'];
									# force login from another device						
									if(isset($array['force_login']) && ((string)$array['force_login'] ==='1'))
									{	
										
										# If driver in trip
										if($freeStatus != 'F'){
											$message = array("message" => __('driver_in_trip'),"status"=> -1);
											$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
											exit;
										}
										
										$force_login = $array['force_login'];
										$datas  = array(
														'device_token'=> $deviceToken,
														'device_id'=> $deviceId,
														'device_type'=> $deviceType
														);
										$login_status_update = $this->commonmodel->update_force_login($datas, $driver_id);
											
										$driver_shift = $api->get_driver_currentshift($driver_id);
										$shiftupdate_id = $taxi_id = $driver_status ="";
										if(!empty($driver_shift))
										{
											$shiftupdate_id = $driver_shift['shift_id'];
											$driver_status = $driver_shift['driver_status'];
											$shiftStatus = $driver_shift['shift_status'];
										}
											
										$getTaxiforDriver = $api->getTaxiforDriver($driver_id,$company_id);
										if(count($getTaxiforDriver) > 0 )
										{
											$taxi_id = $getTaxiforDriver[0]['mapping_taxiid'];
												
											# set driver status start
											if($shiftStatus == 'OUT')
											{
												$datas = array(
												"driver_id" => (int)$driver_id,
												"taxi_id" => (int)$taxi_id,		
												"shift_end"		=> "",
												"reason"		=> "",
												"createdate"		=>$this->currentdate
													);
												$api_ext->insert_drivershift($datas);	
												$api->update_driver_shift_status($driver_id,'IN');
											}		
											# set driver status end
												
											/***** Check whether new trips or payment waiting trips is availavble for the driver ********/
											$trip_id = $travel_status = "";
											$get_driver_trip_details = $api->get_driver_log_details($driver_id,$company_id);
											$driver_trip_count = count($get_driver_trip_details);//exit;
											if($driver_trip_count > 0)
											{	
												foreach($get_driver_trip_details as $details)
												{
													$trip_id = $details->passengers_log_id;
													$travel_status = $details->travel_status;
													$driver_status =  ($travel_status != '9') ?  'A' : $driver_status;
												}			
											}	
											/*************************************************************************************/
											$driver_details[0]["shiftupdate_id"]=$shiftupdate_id;
											$driver_details[0]["taxi_id"]=$taxi_id;
											$driver_details[0]["trip_id"]=$trip_id;
											$driver_details[0]["travel_status"]=$travel_status;
											$driver_details[0]["driver_status"]=$driver_status;
											$driver_details[0]["shift_status"]='IN';
											// Driver Statistics ********************/
											$driver_cancelled_trips = $api->get_driver_cancelled_trips($driver_id,$company_id);
											$driver_logs_rejected = $api->get_rejected_drivers($driver_id,$company_id);
											$rejected_trips = count($driver_logs_rejected);
											$driver_earnings = $api->get_driver_earnings_with_rating($driver_id,$company_id);
											$driver_tot_earnings = $api->get_driver_total_earnings($driver_id);
											$statistics = array();
											$total_trip = $trip_total_with_rate = $total_ratings = $today_earnings = $total_amount=0;
											foreach($driver_earnings as $stat)
											{
												$total_trip++;
												$total_ratings += $stat['rating'];
												$total_amount += $stat['total_amount'];
											}
											$overall_trip = $total_trip + $rejected_trips+	$driver_cancelled_trips;
											$time_driven = $api->get_time_driven($driver_id,'R','A','1');
											$statistics = array( 
												"total_trip" => $overall_trip,
												"completed_trip" => $total_trip,
												"total_earnings" => commonfunction::amount_indecimal($driver_tot_earnings,'api'),
												"overall_rejected_trips" => $rejected_trips,
												"cancelled_trips" => $driver_cancelled_trips,
												"today_earnings"=>commonfunction::amount_indecimal($total_amount,'api'),		
												"shift_status"=>'IN',
												"time_driven"=>$time_driven,
												"status"=> 1
											);
											$driver_details[0]["driver_first_login"]=$driver_first_login;
											if($driver_first_login == 1) 
											{
												$datas  = array("driver_first_login"=> 2);
												$login_status_update = $api_ext->update_driver_people($datas,$driver_id);
											}
											$driver_details[0]["driver_statistics"]=$statistics;	
											$details = array("driver_details"=>$driver_details);
											/**************************************************/

											/** Last Recent 3 trip start **/
											$trip_list = array(); $trip_array["driver_id"] = $driver_id;
											$trip_list = $api->get_recent_driver_trip_list($trip_array);
											if(count($trip_list) > 0)
											{
												foreach($trip_list as $key => $val)
												{
													$trip_list[$key]['drop_time'] = Commonfunction::getDateTimeFormat($val['drop_time'],1);
												}
											}
											/** Last Recent 3 trip end **/
											/* create user logs */
											//~ print_r($details);exit;
// Notification Logger -- Start
$not_project=array();
$not_project['profile_picture']=1;
$not_project['name']=1;
$not_match=array();
$not_match['_id']=(int)$driver_id;
$not_result=$this->commonmodel->dynamic_findone_new(MDB_PEOPLE,$not_match,$not_project);
$not_name=isset($not_result['name'])?$not_result['name']:"";
$notification_content=array();
$notification_content['msg']=__('notification_login_driver',array(':drivername' => $not_name));
$notification_content['domain']=SUBDOMAIN_NAME;
$notification_content['image']=isset($not_result['profile_picture'])?$not_result['profile_picture']:"";
$notification_content['type']='DRIVER_LOGIN';
// Notification Logger -- End					
									        $user_unique = $driver_id.$driver_details[0]['user_type'];
									        $log_array = array(
								                'user_id' => (int)$driver_id,
								                'user_type' => $driver_details[0]['user_type'],
								                'login_type' => __('log_device'),
								                'activity' => __('login_log'),
'notification_content' =>$notification_content,
'notification_type' =>(int)1,							                
								                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
								            );
									        commonfunction::save_user_logs($log_array, $user_unique);
									        /* create user logs */
									        $driver_type = isset($driver_details[0]['driver_type']) ? $driver_details[0]['driver_type'] : "";
									        if($driver_type == "D"){
									        	$message = array("message" => __('driver_not_active'),"status"=> 10,"detail"=>$details,"recent_trip_list"=>$trip_list);	
									        }else{

									        	$message = array("message" => __('login_success'),"status"=> 1,"detail"=>$details,"recent_trip_list"=>$trip_list);	
									        }
											$message['driver_threshold_setting'] = DRIVER_THRESHOLD_SETTING;
											$message['driver_threshold_amount'] = DRIVER_THRESHOLD_AMOUNT;
											$message['driver_wallet'] = isset($driver_details[0]["shiftupdate_id"]) ? $driver_details[0]["shiftupdate_id"] : 0;
											//~ $message['driver_wallet'] = $driver_wallet;	
											$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
										}
										else
										{
											$message = array("message" => __('taxi_not_assigned'),"status"=>-3);		
										}											
									}else{			
										$message = array("message" => __('already_login'),"status"=> 0);
									}
								}									
								else
								{
									$driver_status = 'F';
									$taxi_id = "";
									$getTaxiforDriver = $api->getTaxiforDriver($driver_id,$company_id);
									if(count($getTaxiforDriver) > 0 )
									{
										if(($login_status != 'S') && ($login_from != 'D') && ($device_id != $array['device_id']))
										{
											$update_array  = array("notification_setting"=>"1","login_from"=>"D","login_status"=>"S","device_id" => $array['device_id'],"device_token" => $array['device_token'],"device_type" => $array['device_type'],"notification_status"=>"1");	
											// Need for update labong settings automatically
											$login_status_update = $api->update_driver_phone($update_array,$driver_id,$default_companyid);
										}
										//Enable Driver Shift status
										$driver_reply = $api->update_driver_shift_status($driver_id,'IN');
										$taxi_id = $getTaxiforDriver[0]['mapping_taxiid'];
										$datas = array("driver_id" => $driver_id,
												"company_id" => $company_id,
												"taxi_id" => $taxi_id,
												"shift_end" => null,
												"reason" => null );
											
										$transaction = $api_ext->insert_driver_shiftservice($datas);	
										$shiftupdate_id = $transaction[0];
										/***** Check whether new trips or payment waiting trips is availavble for the driver ********/
										$trip_id = $travel_status = "";
										$get_driver_trip_details = $api->get_driver_log_details($driver_id,$company_id);
										$driver_trip_count = count($get_driver_trip_details);//exit;
										if($driver_trip_count > 0)
										{				
											foreach($get_driver_trip_details as $details)
											{
												$trip_id = $details->passengers_log_id;
												$travel_status = $details->travel_status;
												$driver_status =  ($travel_status != '9') ?  'A' : $driver_status;
											}	
										}	
										/*************************************************************************************/
										$driver_details[0]["shiftupdate_id"]=$shiftupdate_id;
										$driver_details[0]["taxi_id"]=$taxi_id;
										$driver_details[0]["trip_id"]=$trip_id;
										$driver_details[0]["travel_status"]=$travel_status;
										$driver_details[0]["driver_status"]=$driver_status;
										$driver_details[0]["shift_status"]='IN';
										// Driver Statistics ********************/
										$driver_cancelled_trips = $api->get_driver_cancelled_trips($driver_id,$company_id);
										$driver_logs_rejected = $api->get_rejected_drivers($driver_id,$company_id);
										$rejected_trips = count($driver_logs_rejected);
										$driver_earnings = $api->get_driver_earnings_with_rating($driver_id,$company_id);
										$driver_tot_earnings = $api->get_driver_total_earnings($driver_id);
										$statistics = array();
										$total_trip = $trip_total_with_rate = $total_ratings = $today_earnings = $total_amount=0;
										foreach($driver_earnings as $stat)
										{
											$total_trip++;
											$total_ratings += $stat['rating'];
											$total_amount += $stat['total_amount'];
										}
										$overall_trip = $total_trip + $rejected_trips+	$driver_cancelled_trips;
										$time_driven = $api->get_time_driven($driver_id,'R','A','1');
										$statistics = array( 
											"total_trip" => $overall_trip,
											"completed_trip" => $total_trip,
											"total_earnings" => commonfunction::amount_indecimal($driver_tot_earnings,'api'),
											"overall_rejected_trips" => $rejected_trips,
											"cancelled_trips" => $driver_cancelled_trips,
											"today_earnings"=>commonfunction::amount_indecimal($total_amount,'api'),
											"shift_status"=>'IN',
											"time_driven"=>$time_driven,
											"status"=> 1
										  );
										$driver_details[0]["driver_first_login"]=$driver_first_login;
										if($driver_first_login == 1) {
											$datas  = array("driver_first_login"=> 2);
											$login_status_update = $api_ext->update_driver_people($datas,$driver_id);
										}
										$driver_details[0]["driver_statistics"]=$statistics;	
										$details = array("driver_details"=>$driver_details);
										/**************************************************/

										/** Last Recent 3 trip start **/
										$trip_list = array(); $trip_array["driver_id"] = $driver_id;
										$trip_list = $api->get_recent_driver_trip_list($trip_array);
										if(count($trip_list) > 0)
										{
											foreach($trip_list as $key => $val)
											{
												$trip_list[$key]['drop_time'] = Commonfunction::getDateTimeFormat($val['drop_time'],1);
											}
										}
										/** Last Recent 3 trip end **/
										/* create user logs */
								        $user_unique = $driver_id.__('log_driver_type');
								        $log_array = array(
								                'user_id' => (int)$driver_id,
								                'user_type' => __('log_driver_type'),
								                'login_type' => __('log_device'),
								                'activity' => __('login_log'),
								                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
								            );
								        commonfunction::save_user_logs($log_array, $user_unique);
								        /* create user logs */
								         $driver_type = isset($driver_details[0]['driver_type']) ? $driver_details[0]['driver_type'] : "";
								        if($driver_type == "D"){
								        	$message = array("message" => __('driver_not_active'),"status"=> 10,"detail"=>$details,"recent_trip_list"=>$trip_list);
								        }else{

										$message = array("message" => __('login_success'),"status"=> 1,"detail"=>$details,"recent_trip_list"=>$trip_list);		
								        }
										$message['driver_threshold_setting'] = DRIVER_THRESHOLD_SETTING;
										$message['driver_threshold_amount'] = DRIVER_THRESHOLD_AMOUNT;
										$message['driver_wallet'] = isset($driver_details[0]["shiftupdate_id"]) ? $driver_details[0]["shiftupdate_id"] : 0;	
									}
									else
									{
										$message = array("message" => __('taxi_not_assigned'),"status"=>-3);
									}												
								}
							}
							else
							{
								$message = array("message" => __('password_failed'),"status"=> -1);							
							}						
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						}
					}
					else
					{
						$errors = $validator->errors('errors');
						$message = array("message" => __('validation_error'),"status"=>-5,"detail"=>$errors);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						exit;
					}
				}
				else
				{
					$message = array("message" => __('invalid_request'),"status"=>-6);	
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				}						
			//unset($message,$statistics,$driver_details,$api_ext);
			break;

			case 'check_driver_referral_code':
				$driverId = isset($mobiledata['driver_id']) ? $mobiledata['driver_id'] : '';
				$driverReferralCode = isset($mobiledata['referral_code']) ? $mobiledata['referral_code'] : '';
				$extended_api = Model::factory(MOBILEAPI_107_EXTENDED);
				if(!empty($driverReferralCode) && !empty($driverId)) 
				{
					$driverReferral = $api->checkDriverReferralExists($driverReferralCode);
					if(count($driverReferral) > 0) 
					{
						$driverUsedReferral = $api->checkDriverUsedReferral($driverId);
						if($driverUsedReferral > 0) 
						{
							//updates the referred driver's id and referral status in registered users row who is using the referral code
							$referralArr = array("referred_driver_id" => $driverReferral[0]['registered_driver_id'],'registered_driver_id'=>$driverId);
							$referUpdate = $extended_api->update_driver_referral_list($referralArr);
							if($referUpdate) {
								$message = array("message" => __('referral_code_save_successful'),"status"=>1);
							} else {
								$message = array("message" => __('try_again'),"status"=>-1);
							}
						} else {
							$message = array("message" => __('referral_code_already_used'),"status"=>-1);
						}
					} else {
						$message = array("message" => __('driver_referral_code_not_exists'),"status"=>-2);
					}
				} else {
					$message = array("message" => __('invalid_request'),"status"=>-1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$referUpdate,$driverUsedReferral,$extended_api);
			break;

			/** Search Driver Withdraw List **/
			case 'search_driver_withdraw_list':
				$search_array = $mobiledata;
				if(!empty($search_array['driver_id'])) 
				{
					$company_det = $api->get_company_id($search_array['driver_id']);
					$search_array['company_id'] = ($company_det > 0) ? $company_det[0]['company_id'] : $default_companyid;
					$result = $api->search_withdraw_request($search_array);
					if(count($result) > 0) 
					{
						$data = array();
						foreach($result as $f) 
						{
							$status_label = __("pending");
							$status_id = 0;
							if($f["request_status"] == 1) {
								$status_label = __("approved");
								$status_id = 1;
							} else if($f["request_status"] == 2) {
								$status_label = __("rejected");
								$status_id = 2;
							}
							$data[] = array (
								"withdraw_request_id" => $f["withdraw_request_id"],
								"request_id" => "#".$f["request_id"],
								"withdraw_amount" => $this->site_currency.$f["withdraw_amount"],
								"request_date" => Commonfunction::getDateTimeFormat($f["request_date"],1),
								"request_status" => $status_label,
								"request_status_id" => $status_id
							);
						}
						$message = array("message" => __('withdraw_req_list'),"details" => $data, "status" => 1);
					}
					else
					{
						$message = array("message" => __('no_data'),"status" => -1);
					}
				} else {
					$message = array("message" => __('invalid_request'),"status" => -1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$result,$data);
			break;

			case 'driver_wallet':
				$driverId = isset($mobiledata['driver_id']) ? $mobiledata['driver_id'] : '';
				if(!empty($driverId)) 
				{
					$check_driver_login_status = $this->is_login_status($driverId,$default_companyid);
					if($check_driver_login_status == 1)
					{
						$driverWallets = $api->getDriverReferralDetails($driverId);
						$walletAmt = isset($driverWallets[0]['registered_driver_wallet']) ? $driverWallets[0]['registered_driver_wallet'] : 0;
						$requestLists = $api->getWalletAmtRequests($driverId);
						$message = array("message" => __('wallet_details'),"wallet_amount"=>$walletAmt,"request_lists"=>$requestLists,"status"=>1);
					} else {
						$message = array("message" => __('driver_not_login'),"status"=>-1);
					}
				} else {
					$message = array("message" => __('invalid_request'),"status"=>-1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message);
			break;

			/** Driver Send Withdraw Request **/
			case 'driver_send_withdraw_request':
				$driver_id = isset($mobiledata['driver_id']) ? $mobiledata['driver_id'] : '';
				$request_amount = isset($mobiledata['request_amount']) ? $mobiledata['request_amount'] : '';
				$available_amount = isset($mobiledata['available_amount']) ? $mobiledata['available_amount'] : '';
				if(!empty($driver_id) && !empty($request_amount) && !empty($available_amount)) 
				{
					if($request_amount == "0.00" || $request_amount == "0") {
						$message = array("message" => __('invalid_amount'),"status" => -1);
					} else if($request_amount > $available_amount) {
						$message = array("message" => __('withdraw_amount_error'),"status" => -1);
					} 
					else 
					{
						$company_det = $api->get_company_id($driver_id);
						$company_id = ($company_det > 0) ? $company_det[0]['company_id'] : $default_companyid;
						$driver_mobile_no = ($company_det > 0) ? $company_det[0]['phone'] : '';
						$company_brand = isset($company_details['company_brand_type']) ? $company_details['company_brand_type'] : 'M';
						$driver_pending_amount=0;
						$pending_result = $api->driver_withdraw_pending_amount($company_id,$driver_id);
						if(count($pending_result) > 0) {
							$driver_pending_amount = ($pending_result[0]["pending_amount"]) ? $pending_result[0]["pending_amount"] : 0;
						}
						$total_amount = round($available_amount - $driver_pending_amount,2);
						if((float)$total_amount >= (float)$request_amount)
						{
							$result = $api->insert_withdraw_request($company_id,$driver_id,$request_amount, $company_brand);
							if(count($result) > 0) 
							{
								$driver_pending_amount=0;
								$pending_result = $api->driver_withdraw_pending_amount($company_id,$driver_id);
								if(count($pending_result) > 0) {
									$driver_pending_amount = ($pending_result[0]["pending_amount"]) ? $pending_result[0]["pending_amount"] : 0;
								}
								$withdraw_request_array = array(
									'trip_amount' => round($available_amount,2),
									"trip_pending_amount"=> round($driver_pending_amount,2),
									'total_amount' => round($available_amount - $driver_pending_amount,2)
								);

								//Send mail to admin.
								$admin_det = $this->commonmodel->company_location( 0 );
								$req_id = $result;

								$company_name = "";
								$result = $api->get_withdraw_deatil($driver_id, $req_id);
								if(count($result) > 0) 
								{
									$driver_company_name = $result[0]["company_name"];
								}
								//print_r($admin_det);exit;
								if ( count( $admin_det ) > 0 )
								{
									$wallet_request_url = URL_BASE.'expense/payout';
						            $admin_email = $admin_det[0]['email'];
						            $this->send_mail_to_withdraw_request( $admin_email, 'Admin', $driver_mobile_no, $driver_company_name, $req_id, $request_amount, $wallet_request_url );
								}
						        
						        //Send mail to company
						        if( $company_brand == "M" && $company_id > 0 )
						        {
						        	$wallet_request_url = URL_BASE.'manage/driverwithdraw';
							        //$company_det = $this->commonmodel->company_location( $company_id );
							        $company_det = $this->commonmodel->common_company_details( $company_id );
									if ( count( $company_det ) > 0 )
									{
							            $company_email = $company_det[0]['email'];
							            $company_name = $company_det[0]['company_name'];
							            $this->send_mail_to_withdraw_request( $company_email, $company_name, $driver_mobile_no, $driver_company_name, $req_id, $request_amount, $wallet_request_url );
									}
								}
						        
								$message = array("message" => __('withdraw_req_sent_success'), "status" => 1,"details"=> $withdraw_request_array);
							}
							else
							{
								$message = array("message" => __('no_data'),"status" => -1);
							}
						}
						else{
							$message = array("message" => __('dont_have_sufficient_wallet_amount'),"status"=>-1);
						}
					}
				} else {
					$message = array("message" => __('invalid_request'),"status" => -1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message);
			break;

			/** Driver Withdraw List Detail Page **/
			case 'driver_withdraw_list_detail':
				$driver_id = isset($mobiledata['driver_id']) ? $mobiledata['driver_id'] : '';
				$withdraw_request_id = isset($mobiledata['withdraw_request_id']) ? $mobiledata['withdraw_request_id'] : '';
				if(!empty($driver_id) && !empty($withdraw_request_id))
				{
					$result = $api->get_withdraw_deatil($driver_id,$withdraw_request_id);
					$log_result = $api->get_withdraw_log($withdraw_request_id);
					
					$activity_log = $data = array();
					if(count($result) > 0) 
					{
						foreach($result as $f) 
						{
							$status_label = __("not_yet_approved");
							if($f["request_status"] == 1) {
								$status_label = __("approved");
							} else if($f["request_status"] == 2) {
								$status_label = __("rejected");
							}
							$data[] = array (
								"withdraw_request_id" => $f["withdraw_request_id"],
								"request_id" => "#".$f["request_id"],
								"company_name" => $f["company_name"],
								"withdraw_amount" => $this->site_currency.commonfunction::amount_indecimal($f["withdraw_amount"],'api'),
								"request_date" => date("D,dM-Y h:i:s A",strtotime($f["request_date"])),
								"brand_type" => ($f["brand_type"] == 1) ? __("Multy") : __("single"),
								"request_status" => $status_label
							);
						}
						if(count($log_result) > 0) 
						{
							foreach($log_result as $l) 
							{
								$attachment = $payment_mode_name = "";
								$status_label = __("not_yet_approved");
								if($l["status"] == 1) {
									$status_label = __("approved");
								} else if($l["status"] == 2) {
									$status_label = __("rejected");
								}
								$status_txt = "Status changed to ".$status_label.".";
								if($l["status"] == 1) {
									$payment_mode_name = $l["payment_mode_name"];
									$transaction_id = $l["transaction_id"];
									$comments = $l["comments"];
								} 
								else if($l["status"] == 2) {
									$payment_mode_name = $l["payment_mode_name"];
									$transaction_id = $l["transaction_id"];
									$comments = $l["comments"];
								}
								if($l['file_name'] != "" && file_exists(DOCROOT.WITHDRAW_IMG_PATH.$l['file_name'])) 
								{
									$attachment = URL_BASE.WITHDRAW_IMG_PATH.$l['file_name'];
								}
								$activity_log[] = array (
									"created_date" => date("D,dM-Y h:i:s A",strtotime($l["created_date"])),
									"status" => $l["status"],
									"status_txt" => $status_txt,
									"payment_mode_name" => ($payment_mode_name != null) ? $payment_mode_name : "",
									"transaction_id" => $transaction_id,
									"comments" => $comments,
									"attachment" => $attachment,
								);
							}
						}
						$message = array("message" => __('withdraw_request_details'),"details" => $data,"activity_log" => $activity_log, "status" => 1);
					}
					else
					{
						$message = array("message" => __('no_data'),"status" => -1);
					}
				} else {
					$message = array("message" => __('invalid_request'),"status" => -1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$result,$log_result,$data,$attachment,$activity_log);
			break;

			case 'driver_booking_list':
				//Current Journey after driver confirmation //TN1013619352
				$driver_list_array = $mobiledata;
				$driver_id = isset($driver_list_array['driver_id']) ? $driver_list_array['driver_id'] : '';
				if($driver_id != null)
				{
					$validator = $this->driver_coming_cancel($driver_list_array);
					if($validator->check())
					{
						$driver_id= $driver_list_array['driver_id'];
						$start = $driver_list_array['start'];
						$limit = $driver_list_array['limit'];	
						$device_type = $driver_list_array['device_type'];
						$request_type = $driver_list_array['request_type'];
						$pagination = 1;
						$driver_pending_bookings = array();
						$past_booking = array();
						if($request_type == 1) 
						{
							/***********************Driver Upcoming******************************/
							$driver_pending_bookings = $api->driver_pending_bookings($driver_id,'R','A','2',$default_companyid);
							if(count($driver_pending_bookings) > 0)
							{
								foreach($driver_pending_bookings as $key => $journey)
								{
									$passenger_photo = isset($journey['passenger_profile_image'])?$journey['passenger_profile_image']:'';
									if((!empty($passenger_photo)) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$passenger_photo))
									{ 
										 $profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$passenger_photo; 
									}
									else
									{ 
										$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
									} 
									$driver_pending_bookings[$key]['profile_image']=$profile_image;
									$payment_type=isset($journey['payment_type'])?$journey['payment_type']:'';	
									switch($payment_type)
									{
										case 1:
											$driver_pending_bookings[$key]['payment_type']="Cash";
											break;
										case 2:
											$driver_pending_bookings[$key]['payment_type']="Credit Card";
											break;
										case 3:
											$driver_pending_bookings[$key]['payment_type']="New Card";
										break;
										case 5:
											$driver_pending_bookings[$key]['payment_type']="Wallet";
										break;
										default:
											$driver_pending_bookings[$key]['payment_type']="Uncard";
										break;
									}
									//to get the pickup time with required date format
									$driver_pending_bookings[$key]['pickup_time'] = Commonfunction::getDateTimeFormat($journey['pickup_time'],1);
									//map image for upcoming trips in driver app
									if(file_exists(DOCROOT.MOBILE_PENDING_TRIP_MAP_IMG_PATH.$journey['passengers_log_id'].".png")) {
										$mapurl = URL_BASE.MOBILE_PENDING_TRIP_MAP_IMG_PATH.$journey['passengers_log_id'].".png";
									} 
									else 
									{	
										include_once MODPATH."/email/vendor/polyline_encoder/encoder.php";
										$polylineEncoder = new PolylineEncoder();
										$polylineEncoder->addPoint($journey['pickup_latitude'],$journey['pickup_longitude']);
										$marker_end = 0;
										if($journey['drop_latitude'] != 0 && $journey['drop_longitude'] != 0)
										{
											$polylineEncoder->addPoint($journey['drop_latitude'],$journey['drop_longitude']);
											$marker_end = $journey['drop_latitude'].','.$journey['drop_longitude'];
										}
										$marker_start = $journey['pickup_latitude'].','.$journey['pickup_longitude'];
										$encodedString = $polylineEncoder->encodedString();
										$startMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'startMarker.png';
										$endMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'endMarker.png';
										if($marker_end != 0) {
											$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x270&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&markers=icon:$endMarker%7C$marker_end&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
										} 
										else 
										{
											$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x270&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
										}
										
										if(isset($mapurl) && $mapurl != "") 
										{
											$file_path = DOCROOT.MOBILE_PENDING_TRIP_MAP_IMG_PATH.$journey['passengers_log_id'].".png";
											file_put_contents($file_path,@file_get_contents($mapurl));
											$mapurl = URL_BASE.MOBILE_PENDING_TRIP_MAP_IMG_PATH.$journey['passengers_log_id'].".png";
										}
									}
									$driver_pending_bookings[$key]['map_image'] = $mapurl;
								}
							}
						} 
						else 
						{
							$booktype = 1;
							$past_booking = $api->driver_past_bookings($pagination,$booktype,$driver_id,'R','A','1',$start,$limit,$default_companyid);
							foreach($past_booking as $key => $journey)
							{
								$passenger_photo = isset($journey['profile_image'])?$journey['profile_image']:'';
								if((!empty($passenger_photo)) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$passenger_photo))
								{ 
									$profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$passenger_photo;
								}
								else
								{ 
									$profile_image = ($journey['bookby'] == 3) ? URL_BASE.PUBLIC_IMAGES_FOLDER."streetpickup_image.png" : URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
								} 
								$past_booking[$key]['profile_image']=$profile_image;
								$payment_type=isset($journey['payment_type'])?$journey['payment_type']:'';
								switch($payment_type)
								{
									case 1:
										$past_booking[$key]['payment_type']="Cash";
										break;
									case 2:
										$past_booking[$key]['payment_type']="Credit Card";
										break;
									case 3:
										$past_booking[$key]['payment_type']="Uncard";
										break;
									case 5:
										$past_booking[$key]['payment_type']="Wallet";
										break;
									default:
										$past_booking[$key]['payment_type']="Uncard";
										break;
								}
								//to get passenger Name
								$past_booking[$key]['passenger_name'] = (!empty($journey['passenger_name'])) ? ucfirst($journey['passenger_name']) : '';
								//to get the pickup time with required date format
								$past_booking[$key]['pickup_time'] = Commonfunction::getDateTimeFormat($journey['pickup_time'],1);
								$past_booking[$key]['drop_time'] = Commonfunction::getDateTimeFormat($journey['drop_time'],1);
								//Map image
								if(file_exists(DOCROOT.MOBILE_COMPLETE_TRIP_MAP_IMG_PATH.$journey['passengers_log_id'].".png")) {
									$mapurl = URL_BASE.MOBILE_COMPLETE_TRIP_MAP_IMG_PATH.$journey['passengers_log_id'].".png";
								} 
								else 
								{
									$path = $journey['active_record'];
									$path = str_replace('],[', '|', $path);
									$path = str_replace(']', '', $path);
									$path = str_replace('[', '', $path);
									$path = explode('|',$path);$path = array_unique($path);
									include_once MODPATH."/email/vendor/polyline_encoder/encoder.php";
									$polylineEncoder = new PolylineEncoder();
									if(count(array_filter($path)) > 0)
									{
										foreach ($path as $values)
										{
											$values = explode(',',$values);
											if(isset($values[0]) && isset($values[1]))
											{ 
												$polylineEncoder->addPoint($values[0],$values[1]);
												$polylineEncoder->encodedString();
											}
										}
									}
									$encodedString = $polylineEncoder->encodedString();
										
									$marker_end = $journey['drop_latitude'].','.$journey['drop_longitude'];
									$marker_start = $journey['pickup_latitude'].','.$journey['pickup_longitude'];
									$startMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'startMarker.png';
									$endMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'endMarker.png';
									if($marker_end != 0) 
									{
										$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x270&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&markers=icon:$endMarker%7C$marker_end&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
									} else {
										$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x270&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
									}
									if(isset($mapurl) && $mapurl != "") {
										$file_path = DOCROOT.MOBILE_COMPLETE_TRIP_MAP_IMG_PATH.$journey['passengers_log_id'].".png";
										file_put_contents($file_path,@file_get_contents($mapurl));
										$mapurl = URL_BASE.MOBILE_COMPLETE_TRIP_MAP_IMG_PATH.$journey['passengers_log_id'].".png";
									}
								}
								$past_booking[$key]['map_image'] = $mapurl;
								$past_booking[$key]['amt'] = (string)commonfunction::amount_indecimal($journey["amt"],'api');
								
								$past_booking[$key]['distance_fare_km'] = ($journey["distance"] > 1) ? ($journey["amt"]/$journey["distance"]) : $journey["amt"];									
								$past_booking[$key]['vehicle_distance_fare'] = ($journey["distance"] > 1) ? ($past_booking[$key]['distance_fare_km']*$journey["distance"]) : $journey["amt"];
																		
								$past_booking[$key]['distance_fare_km'] = (string)commonfunction::amount_indecimal($past_booking[$key]['distance_fare_km'],'api');									
								
								$past_booking[$key]['vehicle_distance_fare'] = (string)commonfunction::amount_indecimal($past_booking[$key]['vehicle_distance_fare'],'api');	
								$time = explode(':', str_replace("Mins","",$journey["twaiting_hour"]));
								$secs = isset($time[0]) ? $time[0] : 0;
								$mins = isset($time[1]) ? $time[1] : 0;
								$waiting_time_per_hours = ($secs*3600) + ($mins*60)/60;
								$waiting_fare_per_hour = 0;
								if($journey["waiting_fare"] > 0 && $waiting_time_per_hours > 0) {
									$waiting_fare_per_hour = ($journey["waiting_fare"]*60)/$waiting_time_per_hours;
								}
								$past_booking[$key]['waiting_fare_per_hour'] = $waiting_fare_per_hour;
								$past_booking[$key]['final_amt'] = 0;
									unset($past_booking[$key]['active_record']);
							}
							
						}
						
						$detail = array("pending_booking"=>$driver_pending_bookings,"past_booking"=>$past_booking);
						$message = array("message" => __('success'),"detail"=>$detail,"status"=>1);						
					}
					else
					{
						$errors = $validator->errors('errors');	
						$message = array("message" => __('validation_error'),"detail"=>$errors,"status"=>2);							
					}												
				}
				else
				{
					$message = array("message" => __('invalid_user'),"status"=>-1);	
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$past_booking,$file_path,$mapurl,$startMarker,$endMarker,$encodedString);
			break;

			/** Street Pickup - Driver Start Trip Process **/
			case 'driver_start_trip':
				$extended_api = Model::factory(MOBILEAPI_107_EXTENDED);
				$trip_array = $mobiledata;
				$driver_allow = 0;
				if(!empty($trip_array)) 
				{
					$trip_array['company_id'] = $default_companyid;
					$login_status = $api->driver_logged_status($trip_array);
					if($login_status == 1)
					{
						$trip_array['company_id'] = $api->get_driver_companyid($trip_array);
						$trip_status = $api->driver_current_trip_status($trip_array);
						//echo "<pre>";print_r($trip_status);exit();
						if(count($trip_status) > 0)
						{	
							$driver_allow =1;
							$account_balance = isset($trip_status[0]['account_balance']) ? $trip_status[0]['account_balance'] : 0;
							if($trip_status[0]['commission_subscription']=='1'){
								
								## Adding additional condition based on threshold condition
								if(DRIVER_THRESHOLD_SETTING == 1 && ($account_balance <= DRIVER_THRESHOLD_AMOUNT))
								{
									$driver_allow =0;
									$message = array("message" => __('not_eligible_trip'),"status" => -1);
									$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								}	
							}else{
								if((PACKAGE_TYPE == 3 || PACKAGE_TYPE == 0)){
									if(!empty($trip_status[0]['plan_detail'])){
										if(isset($trip_status[0]['plan_detail'][0]['expiry_date']) && $trip_status[0]['plan_detail'][0]['expiry_date']!='' ){
                       
					                        $expirydate = Commonfunction::convertphpdate('',$trip_status[0]['plan_detail'][0]['expiry_date']);
					                        				                       
					                        $today = date('Y-m-d H:i:s',time()); 
					                        $exp = date('Y-m-d H:i:s',strtotime($expirydate));
					                        
					                        $expDate =  date_create($exp);
					                        $todayDate = date_create($today);
					                       
					                        $expdiff =  date_diff($todayDate, $expDate);
					                        $remaining_day  = $expdiff->format("%R%a");
					                        $remaining_hour = $expdiff->format('%h');
					                        $remaining_minutes = $expdiff->format('%i');
					                        $final_remaining_mts = $remaining_hour*60 + $remaining_minutes;

					                        if($remaining_day<=0 && $final_remaining_mts <DRIVER_GRACE_TIME_MTS){
					                        	$driver_allow =0;
					                        	$message = array("message" => __('subscribe_plan_going_to_expire'),"status" => -1);
					                        }
				                    	}
									}else{
										$driver_allow =0;
										$message = array("message" => __('subscribe_plan'),"status" => -1);
									}
								}
							}
							if($driver_allow == 1){
							
							$shift_status = isset($trip_status[0]['shift_status']) ? $trip_status[0]['shift_status'] : 'OUT';
							if($shift_status == 'OUT'){
								$message = array("message" => __('driver_shift_out'),"status"=>-1);
							}
							else
							{								
								$trip_array['approx_trip_fare'] = isset($trip_array['approx_trip_fare'])?round($trip_array['approx_trip_fare'],2):0;
								$trip_array['taxi_id'] = isset($trip_status[0]['mapping_taxiid'])?$trip_status[0]['mapping_taxiid']:"";
								$brand_type = isset($trip_status[0]['brand_type'])?$trip_status[0]['brand_type']:"";
								$fare_details = $api->get_model_fare_details($trip_array['company_id'],$trip_status[0]['taxi_model'],'',$brand_type);
								
								$default_unit = ($trip_status[0]['default_unit'] == 0 || $trip_status[0]['default_unit']=='') ? "KM":"MILES";	
								$default_unit = (FARE_SETTINGS == 2) ? $default_unit : UNIT_NAME;
								
								if(count($fare_details) > 0)
								{
									$trip_array['motor_model'] = $trip_status[0]['taxi_model'];
									$details['driver_tripid'] = $api->save_street_trip($trip_array);	
									$update_driver_array  = array(
										'latitude' => $trip_array['pickup_latitude'],
										'longitude' => $trip_array['pickup_longitude'],
										'status' => 'A',
										'driver_id'=>$trip_array['driver_id']
									);
									$update_current_result = $extended_api->update_driver_location($update_driver_array);
									foreach($fare_details as $val)
									{
										$details['base_fare'] = commonfunction::amount_indecimal($val['base_fare'],'api');
										$details['min_km'] = $val['min_km'];
										$details['min_fare'] = commonfunction::amount_indecimal($val['min_fare'],'api');
										$details['below_km'] = commonfunction::amount_indecimal($val['below_km'],'api');
										$details['above_km'] = commonfunction::amount_indecimal($val['above_km'],'api');
										$details['below_above_km'] = isset($val['below_above_km'])?$val['below_above_km']:'';
										$details['km_wise_fare'] = isset($val['km_wise_fare'])?$val['km_wise_fare']:'0';
										$details['additional_fare_per_km'] = commonfunction::amount_indecimal($val['additional_fare_per_km'],'api');
										$details['metric'] = $default_unit;
										$details['minutes_fare'] = commonfunction::amount_indecimal($val['minutes_fare'],'api');
									}	
									$message = array("message" => __('trip_confirmed'),"status" => 1,"detail"=>$details);
								}
								else
								{
									$message = array("message" => __('invalid_motor_model'),"status" => -1);
								}
							}
						}//if of driver allow
						}else{
							$message = array("message" => __('already_trip'),"status"=>-1);
						}
					}
					else
					{
						$message = array("message" => __('driver_not_login'),"status"=>-1);
					}
				} else {
					$message = array("message" => __('invalid_request'),"status" => -1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$extended_api,$fare_details,$trip_status,$details);
			break;

			/** Street Pickup End Trip **/
			case 'street_pickup_end_trip':
				$extended_api = Model::factory(MOBILEAPI_107_EXTENDED);
				$array = $mobiledata;
				if(!empty($array))
				{    
					$drop_latitude = $array['drop_latitude'];
					$drop_longitude = $array['drop_longitude'];
					$drop_location = urldecode($array['drop_location']);
					$trip_id = $array['trip_id'];
					$distance = $array['distance'];
					$actual_distance = $array['actual_distance'];
					$distance_time = $array['distance_time'];
					$waiting_hours = $array['waiting_hour'];
					$driver_app_version = (isset($array['driver_app_version'])) ? $array['driver_app_version'] : '';
					if(!empty($trip_id))
					{
						$gateway_details = $this->commonmodel->gateway_details($default_companyid);
						$get_passenger_log_details = $api->getPassengerLogDetail($trip_id);
						$pickupdrop = $taxi_id = $company_id = $fare_per_hour = $waiting_per_minute = $total_fare = $nightfare = 0;//hr to mt
						if(count($get_passenger_log_details) > 0)
						{	
							//to check the driver is in commission or subscription					
							$driver_register_type = $get_passenger_log_details[0]->driver_register_type;	
							$brand_type = $get_passenger_log_details[0]->brand_type;					
							$company_tax = $get_passenger_log_details[0]->company_tax;						
							$default_metric = ($get_passenger_log_details[0]->default_unit == 0) ? "KM":"MILES";
							$default_metric = (FARE_SETTINGS == 2) ? $default_metric : UNIT_NAME;						
							$company_fare_calculation_type = $get_passenger_log_details[0]->fare_calculation_type;
							
							$farecalculation_type = (FARE_SETTINGS == 2 && $brand_type == 'M') ? $company_fare_calculation_type : FARE_CALCULATION_TYPE;
							
							$tax = (FARE_SETTINGS == 2 && $brand_type == 'M') ? $company_tax : TAX;
							$travel_status = $get_passenger_log_details[0]->travel_status;
							$splitTrip = $get_passenger_log_details[0]->is_split_trip; //0 - Normal trip, 1 - Split trip
							//~ $total_distance = $get_passenger_log_details[0]->distance;
							$total_distance = $actual_distance;
							$pickupdrop = $taxi_id = $company_id = $fare_per_hour = $waiting_per_minute = $total_fare = $nightfare = 0;//hr to mt
							
							if(($travel_status == 2) || ($travel_status == 5))
							{
								$pickup = $get_passenger_log_details[0]->current_location;
								$drop = $get_passenger_log_details[0]->drop_location;
								$pickupdrop = $get_passenger_log_details[0]->pickupdrop;
								$taxi_id = $get_passenger_log_details[0]->taxi_id;
								$pickuptime = date('H:i:s', strtotime($get_passenger_log_details[0]->pickup_time));
								$actualPickupTime = date('H:i:s', strtotime($get_passenger_log_details[0]->actual_pickup_time));
								$actualPickupDateTime = date('Y-m-d H:i:s', strtotime($get_passenger_log_details[0]->actual_pickup_time));
								$company_id = $get_passenger_log_details[0]->company_id;
								$driver_id = $get_passenger_log_details[0]->driver_id;
								$approx_distance = $get_passenger_log_details[0]->approx_distance;
								$approx_fare = $get_passenger_log_details[0]->approx_fare;
								$fixedprice = $get_passenger_log_details[0]->fixedprice;
								$actual_pickup_time = $get_passenger_log_details[0]->actual_pickup_time;

								$taxi_model_id = $get_passenger_log_details[0]->taxi_modelid;
								$brand_type = $get_passenger_log_details[0]->brand_type;
								$cityName = isset($get_passenger_log_details[0]->city_name) ? $get_passenger_log_details[0]->city_name:'';

								$taxi_fare_details = $api->get_model_fare_details($company_id,$taxi_model_id,$cityName,$brand_type);
								if($travel_status != 5) {
									$drop_time = $this->commonmodel->getcompany_all_currenttimestamp($company_id);
								} else {
									$drop_time = $get_passenger_log_details[0]->drop_time;
								}
								/*************** Update arrival in driver request table ******************/
								$update_driver_array  = array(
									'status' => 7,
									'trip_id'=>$trip_id
								);
								/*************************************************************************/
								
								/** Update Driver Status **/
								if(($array['drop_latitude'] > 0 ) && ($array['drop_longitude'] > 0))
								{
									$update_driver_array  = array(
										'latitude' => $array['drop_latitude'],
										'longitude' => $array['drop_longitude'],
										'status' => 'A',
										'driver_id'=>$driver_id
									);
								}
								else
								{
									$update_driver_array  = array(
										'status' => 'A',
										'driver_id'=>$driver_id
									);
								}
								/*********************/
								$base_fare = $min_km_range = $min_fare = $cancellation_fare = $below_above_km_range = $below_km = $above_km = $night_charge = $night_timing_from = $night_timing_to = $night_fare = $evening_charge = $evening_timing_from = $evening_timing_to = $evening_fare = $waiting_per_minute = $minutes_cost= $baseFare= 0;//hr to mt
								$km_wise_fare = $additional_fare_per_km =0;//km wise fare
								if(count($taxi_fare_details) > 0)
								{
									$base_fare = $taxi_fare_details[0]['base_fare'];
									$min_km_range = $taxi_fare_details[0]['min_km'];
									$min_fare = $taxi_fare_details[0]['min_fare'];
									$cancellation_fare = $taxi_fare_details[0]['cancellation_fare'];
									$below_above_km_range = isset($taxi_fare_details[0]['below_above_km'])?$taxi_fare_details[0]['below_above_km']:'';
									$below_km = $taxi_fare_details[0]['below_km'];
									$above_km = $taxi_fare_details[0]['above_km'];
									$night_charge = $taxi_fare_details[0]['night_charge'];
									$night_timing_from = $taxi_fare_details[0]['night_timing_from'];
									$night_timing_to = $taxi_fare_details[0]['night_timing_to'];
									$night_fare = $taxi_fare_details[0]['night_fare'];
									$evening_charge = $taxi_fare_details[0]['evening_charge'];
									$evening_timing_from = $taxi_fare_details[0]['evening_timing_from'];
									$evening_timing_to = $taxi_fare_details[0]['evening_timing_to'];
									$evening_fare = $taxi_fare_details[0]['evening_fare'];
									//$waiting_per_hour = $taxi_fare_details[0]['waiting_time'];
									$waiting_per_minute = $taxi_fare_details[0]['waiting_time'];//hr to mt
									$minutes_fare = $taxi_fare_details[0]['minutes_fare'];
									$farePerMin = $minutes_fare;
									//km wise fare
									$km_wise_fare = $taxi_fare_details[0]['km_wise_fare'];
									$additional_fare_per_km = $taxi_fare_details[0]['additional_fare_per_km'];
									$city_model_fare = $taxi_fare_details[0]['city_model_fare'];
								}

								// Which is used when the driver send waiting time as minutes
								$roundtrip = "No";
								if($pickupdrop == 1)
								{
									$roundtrip = "Yes";
								}
								// Minutes travelled functionlity starts here

								/********Minutes fare calculation *******/
								$interval  = abs(strtotime($drop_time) - strtotime($actual_pickup_time));
								
								$minutes   = round($interval / 60);
								/********Minutes fare calculation *******/
								
								// Minutes travelled functionlity ends here
								if($farecalculation_type == 1 || $farecalculation_type == 3)
								{
									$baseFare = $base_fare;
									if($total_distance < $min_km_range)
									{
										//min fare has set as base fare if trip distance 
										$baseFare = $min_fare;
										$total_fare = $min_fare;
									}
									//km wise fare
									else if($km_wise_fare == 1 && $distance >$min_km_range){
										$distance_after_minkm = $distance - $min_km_range;
										$additional_distance_fare = $distance_after_minkm*$additional_fare_per_km;
										
										$total_additional_fare = $min_fare +$additional_distance_fare+$baseFare;
										$city_fare_percent = ($city_model_fare/100);
										$total_fare = $total_additional_fare +($total_additional_fare*$city_fare_percent);

										
									}
									//km wise fare
									else if($total_distance <= $below_above_km_range)
									{
										$fare = $total_distance * $below_km;
										$total_fare  = 	$fare + $base_fare ;
									}
									else if($total_distance > $below_above_km_range)
									{
										$fare = $total_distance * $above_km;
										$total_fare  = 	$fare + $base_fare ;
									}
								}
								
								if($farecalculation_type == 2 || $farecalculation_type == 3)
								{
									/********** Minutes fare calculation ************/
									if($minutes_fare > 0)
									{
										$minutes_cost = $minutes * $minutes_fare;
										$total_fare  = $total_fare + $minutes_cost;
									}
									/************************************************/
								}
								$trip_fare = $total_fare;

								// Waiting Time calculation
								/*
								$waiting_cost = $waiting_per_hour * $waiting_hours;
								$total_fare = $waiting_cost + $total_fare;*/

								//Waiting Time calculation per minute
								$waiting_minutes = $waiting_hours * 60;
								$waiting_cost = $waiting_per_minute * $waiting_minutes;
								$total_fare = $waiting_cost + $total_fare;

								$parsed = date_parse($actualPickupTime);
								$pickup_seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

								//Night Fare Calculation
								$parsed = date_parse($night_timing_from);
								$night_from_seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

								$parsed = date_parse($night_timing_to);
								$night_to_seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

								$nightfare_applicable = $date_difference=0;
								/*if ($night_charge != 0) 
								{
									if( ($pickup_seconds >= $night_from_seconds && $pickup_seconds <= 86399) || ($pickup_seconds >= 0 && $pickup_seconds <= $night_to_seconds) )
									{
										$nightfare_applicable = 1;
										$nightfare = ($night_fare/100)*$total_fare;
										$total_fare  = $nightfare + $total_fare;
									}
								}*/
								
								if ($night_charge == 1) 
								{				
									
									$night_start_date ='';
									$night_end_date ='';

									$night_start_date= date('Y-m-d')." ".$night_timing_from;
									$night_timing_to_value=$night_timing_to;
									$night_timing_from_value=$night_timing_from;
									$night_end_date= date('Y-m-d')." ".$night_timing_to;
									# check night start time is in previous day
									if(strtotime($night_end_date) < strtotime($night_start_date))
									{
										$night_start_date=date('Y-m-d', strtotime('-1 day'))." ".$night_timing_from_value;
									}
									else
									{
										$night_start_date= date('Y-m-d')." ".$night_timing_from_value;
									}

									if( strtotime($actualPickupDateTime) >= strtotime($night_start_date) && strtotime($actualPickupDateTime) <= strtotime($night_end_date))
									{
										$nightfare_applicable = 1;
										$nightfare = ($night_fare/100)*$total_fare;//night_charge%100;                                        
										$total_fare  = $nightfare + $total_fare;
									}	
								}

								//Evening Fare Calculation
								$parsed_eve = date_parse($evening_timing_from);
								$evening_from_seconds = $parsed_eve['hour'] * 3600 + $parsed_eve['minute'] * 60 + $parsed_eve['second'];

								$parsed_eve = date_parse($evening_timing_to);
								$evening_to_seconds = $parsed_eve['hour'] * 3600 + $parsed_eve['minute'] * 60 + $parsed_eve['second'];

								$eveningfare = $evefare_applicable=$date_difference=0;
								if ($evening_charge != 0) 
								{
									if( $pickup_seconds >= $evening_from_seconds && $pickup_seconds <= $evening_to_seconds)
									{
										$evefare_applicable = 1;
										$eveningfare = ($evening_fare/100)*$total_fare;
										$total_fare  = $eveningfare + $total_fare;
									}
								}

								// Company Tax amount Calculation
								$tax_amount = "";
								//driver subscription is only for enterprise pack
									if($driver_register_type == '2'){
										if(PACKAGE_TYPE!=3){
											$driver_register_type = '1';
										}
									}
									
								# tax calculation
								if($tax > 0)
								{
									$tax_amount = ($tax/100)*$total_fare;
									$total_fare =  $total_fare+$tax_amount;
								}								
								
								$total_fare = ($fixedprice != 0) ? $fixedprice : $total_fare;
								$trip_fare = $trip_fare;
								$total_fare = $total_fare;

// Way Points
$waypoints=isset($mobiledata['waypoints'])?$mobiledata['waypoints']:array();								

								if($travel_status != 5) {
									//to update the used wallet amount and  for a trip in passenger log table
									$message_status = 'R';$driver_reply='A';$journey_status=5; // Waiting for Payment
									$journey = $api->update_journey_statuswith_drop($trip_id,$message_status,$driver_reply,$journey_status,$drop_latitude,$drop_longitude,$drop_location,$drop_time,$total_distance,$waiting_hours,$tax,$driver_app_version,0,$waiting_per_minute,$farePerMin,$waypoints);
									//update the wallet amount in referred driver's row
									$referredDriver = $api->getReferredDriver($driver_id);
									if($referredDriver > 0) {
										$driverReferral = $api->getDriverReferralDetails($referredDriver);
										if(count($driverReferral) > 0){
											$wallAmount = $driverReferral[0]['registered_driver_wallet'] + $driverReferral[0]['registered_driver_code_amount'];
											$update_driver_array  = array(
												'registered_driver_wallet' => $wallAmount,
												'registered_driver_id'=>$driverReferral[0]['registered_driver_id']
											);
											$update_current_result = $extended_api->update_driver_referral_list($update_driver_array);
											//update referrer earned status in registered driver's row while he completing his first trip	
											$update_driver_array  = array(
												'referral_status' => 1,
												'registered_driver_id'=>$driver_id
											);
											$update_current_result = $extended_api->update_driver_referral_list($update_driver_array);
										}
									}
								}
								$tax_amount = $tax_amount;
								$nightfare = $nightfare;
								$smpleArr = array();
								foreach($gateway_details as $key=>$valArr) {
									if($valArr['pay_mod_id'] == 1) {
										$smpleArr[] = $valArr;
									}
								}
								$gateway_details = $smpleArr;

								//the hours value has been changed to seconds
								$convertSeconds = $waiting_hours * 3600;
								$converthours = floor($convertSeconds / 3600);
								$convertmins = floor(($convertSeconds - ($converthours*3600)) / 60);
								$convertsecs = floor($convertSeconds % 60);
								$waitH = ($converthours < 10) ? '0'.$converthours : $converthours;
								$waitM = ($convertmins < 10) ? '0'.$convertmins : $convertmins;
								$waitS = ($convertsecs < 10) ? '0'.$convertsecs : $convertsecs;
								$waitingTime = ($waitH != "00") ? $waitH.':'.$waitM.':'.$waitS.' Hours' :  $waitM.':'.$waitS.' Mins';
								$tax = ($tax == '') ? '0' : $tax;
								$detail = array("trip_id" => $trip_id,"distance" => $total_distance,
								"trip_fare"=> commonfunction::amount_indecimal($trip_fare,'api'),"nightfare_applicable"=>$nightfare_applicable,
								"nightfare"=> commonfunction::amount_indecimal($nightfare,'api'),"eveningfare_applicable"=>$evefare_applicable,
								"eveningfare"=> commonfunction::amount_indecimal($eveningfare,'api'),"waiting_time"=>$waitingTime,
								"waiting_cost"=> commonfunction::amount_indecimal($waiting_cost,'api'),
								"tax_amount"=> commonfunction::amount_indecimal($tax_amount,'api'),
								"subtotal_fare"=> commonfunction::amount_indecimal($total_fare,'api'),
								"total_fare"=> commonfunction::amount_indecimal($total_fare,'api'),
								"gateway_details"=>$gateway_details,"pickup"=>$pickup,"drop"=>$drop_location,
								"company_tax"=>$tax,/*"waiting_per_hour" => commonfunction::amount_indecimal($waiting_per_hour,'api'), */
								"waiting_per_minute" => commonfunction::amount_indecimal($waiting_per_minute,'api'), 
								"roundtrip"=> $roundtrip,"minutes_traveled"=>$minutes,
								"minutes_fare"=>commonfunction::amount_indecimal($minutes_cost,'api'),
								"metric"=>$default_metric,"base_fare"=> commonfunction::amount_indecimal($baseFare,'api'),
								"wallet_amount_used"=>0,"promo_discount_per"=>0,
								"promo_type"=>"",
								//~ "promo_discount_per"=>"",
								"pass_id"=>"","referdiscount"=>"",
								"promodiscount_amount"=>'0.00',"passenger_discount"=>"",
								"credit_card_status"=>0,"street_pickup"=>1,"fare_calculation_type"=>$farecalculation_type,'model_fare_type'=>$km_wise_fare);
								
								# additional keys for support rental & outstation
								$detail["trip_type"] = $detail["os_plan_fare"] = $detail["os_plan_distance"] = $detail["os_plan_duration"] = $detail["os_additional_fare_per_distance"] = $detail["os_additional_fare_per_hour"] = $detail["os_duration"] = $detail["os_distance_unit"] = $detail["trip_start_time"] = $detail["trip_end_time"] = '0';
								
								$message = array("message" => __('trip_completed_driver'),"detail" => $detail,"status" => 4);
							}
							else if($travel_status == 1)
							{
								$message = array("message" => __('trip_already_completed'),"status"=>-1);
							}
							else
							{
								$message = array("message" => __('trip_not_started'),"status"=>-1);
							}
						}
						else
						{
							$message = array("message" => __('invalid_trip'),"status"=>-1);
						}
					}
					else
					{
						$message = array("message" => __('invalid_trip'),"status"=>-1);
					}
				}
				else
				{
					$message = array("message" => __('invalid_request'),"status"=>-1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$extended_api,$get_passenger_log_details,$journey,$detail);
			break;

			case 'street_pickup_tripfare_update':
				$array = $mobiledata;
				$api_model = Model::factory(MOBILEAPI_107);
				$extended_api = Model::factory(MOBILEAPI_107_EXTENDED);
				$pay_mod_id = $array['pay_mod_id'];
				$validator = $this->payment_validation($array);
				$driver_statistics = array();
				if($validator->check())
				{
					$passenger_log_id = $array['trip_id'];
					if($array['actual_distance'] == "")
						$distance = $array['distance'];
					else
						$distance = $array['actual_distance'];
					
					$actual_amount = $array['actual_amount'];
					$remarks = $array['remarks'];
					$minutes_traveled=$array['minutes_traveled'];
					$minutes_fare=$array['minutes_fare'];
					$base_fare=$array['base_fare'];
					$trip_fare = $array['trip_fare']; //Trip Fare without Tax,Tips and Discounts
					$fare = round($array['fare'],2); //Total Fare with Tax,Tips and Discounts can editable by driver
					$tips = round($array['tips'],2); //Tips Optional
					$nightfare_applicable = $array['nightfare_applicable'];
					$nightfare = $array['nightfare'];
					$eveningfare_applicable = $array['eveningfare_applicable'];
					$eveningfare = $array['eveningfare'];
					$tax_amount = $array['tax_amount'];
					$tax_percentage = $array['company_tax'];
					$fare_calculation_type = isset($array['fare_calculation_type']) ? $array['fare_calculation_type'] : FARE_CALCULATION_TYPE;
					$trip_fare = round($trip_fare,2);
					$total_fare = $fare;
					$amount = round($total_fare,2); // Total amount which is used for pass to payment gateways
					$get_passenger_log_details = $api->getPassengerLogDetail($passenger_log_id);
					if(count($get_passenger_log_details) > 0)
					{
						if($array['pay_mod_id'] == 1)
						{
							try {
								$default_unit = ($get_passenger_log_details[0]->default_unit == 0||$get_passenger_log_details[0]->default_unit == '') ? "KM" : "MILES";
								$driver_register_type = isset($get_passenger_log_details[0]->driver_register_type) ?$get_passenger_log_details[0]->driver_register_type : "1";
                                $default_unit = (FARE_SETTINGS == 2) ? $default_unit : UNIT_NAME;
                                //if the package is enterprise and driver is in subscription plan,then admin,company and driver commission is zero because the entire trip amount is for driver
								if((PACKAGE_TYPE == 3 || PACKAGE_TYPE == 0) && $driver_register_type == '2'){
									$update_commission = array();
									$update_commission['admin_commission']   = 0;
									$update_commission['company_commission'] = 0;
									$update_commission['driver_commission'] = 0;
									$update_commission['payments_to_company'] = 0;
									$update_commission['payments_to_driver'] = 0;
									$update_commission['trans_packtype'] = 'T';
								} else{

								  $update_commission = $this->commonmodel->update_commission($passenger_log_id,$total_fare,ADMIN_COMMISSON);
								}

								$insert_array = array(
									"passengers_log_id" => $passenger_log_id,
									"distance" 			=> urldecode($array['distance']),
									"actual_distance" 	=> urldecode($array['actual_distance']),
									"distance_unit" 	=> $default_unit,
									"tripfare"			=> $trip_fare,
									"fare" 				=> $fare,
									"tips" 				=> $tips,
									"waiting_cost"		=> (double)$array['waiting_cost'],
									"passenger_discount"=> (double)0,
									"promo_discount_fare"=> (double)0,
									"tax_percentage"	=> $tax_percentage,
									"company_tax"		=> (double)$tax_amount,
									"waiting_time"		=> urldecode($array['waiting_time']),
									"trip_minutes"		=> $minutes_traveled,
									"minutes_fare"		=> (double)$minutes_fare,
									"base_fare"			=> (double)$base_fare,
									"remarks"			=> $remarks,
									"payment_type"		=> $array['pay_mod_id'],
									"amt"				=> (double)$amount,
									"nightfare_applicable" => $nightfare_applicable,
									"nightfare" 		=> (double)$nightfare,
									"eveningfare_applicable" => $eveningfare_applicable,
									"eveningfare" 		=> (double)$eveningfare,
									"admin_amount"		=> $update_commission['admin_commission'],
									"company_amount"	=> $update_commission['company_commission'],
									"driver_amount"		=> $update_commission['driver_commission'],
									"payments_to_company"		=> $update_commission['payments_to_company'],
									"payments_to_driver"		=> $update_commission['payments_to_driver'],
									"trans_packtype"	=> $update_commission['trans_packtype'],
									"fare_calculation_type"	=> $fare_calculation_type,
									"trip_type"	=> '0'
								);
								$check_trans_already_exist = $api->checktrans_details($passenger_log_id);								
								if(count($check_trans_already_exist)>0)
								{
									$tranaction_id = $check_trans_already_exist[0]['id'];
									$update_transaction = $extended_api->update_transaction_table($insert_array,$tranaction_id);
								}
								else
								{
									$transaction = $extended_api->insert_transaction_table($insert_array);
								}
								//update travel status in passengers log table
								$message_status = 'R';$driver_reply='A';$journey_status=1; // Waiting for Payment
								$journey = $api->update_journey_status($passenger_log_id,$message_status,$driver_reply,$journey_status);
								$pickup = $get_passenger_log_details[0]->current_location;
								$detail = array("fare" => commonfunction::amount_indecimal($amount,'api'),
												"pickup" => $pickup,"trip_id"=>$passenger_log_id);
								$message = array("message" => __('trip_fare_updated'),"detail"=>$detail,"status"=>1);
							}
							catch (Kohana_Exception $e) {
								$message = array("message" => __('trip_fare_already_updated'), "status"=>-1);
							}
						}
						else
						{
							$message = array("message" => __('invalid_payment_mode'),"status"=>-1);
						}
					}
					else
					{
						$message = array("message" => __('invalid_trip'),"status"=>-1);
					}
				}
				else
				{
					$validation_error = $validator->errors('errors');
					$message = array("message" => $validation_error,"status" => -3);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$api_model,$extended_api,$insert_array);
			break;

			case 'driver_earnings':
				if(!empty($mobiledata['driver_id']))
				{
					$company_det = $api->get_company_id($mobiledata['driver_id']);
					if(count($company_det) > 0) 
					{
						$default_companyid = $company_det[0]['company_id'];
						$check_driver_login_status = $this->is_login_status($mobiledata['driver_id'],$default_companyid);
						if($check_driver_login_status == 1)
						{
							$get_company_time_details = $api_ext->get_company_time_details($default_companyid);
							$start_time = $get_company_time_details['start_time']; //Start time
							$end_time = $get_company_time_details['end_time']; //end time
							$current_time = $get_company_time_details['current_time'];
							$getTodayEarnings = $api->getTodayDriverEarnings($mobiledata['driver_id'],$start_time,$end_time);
							if(isset($getTodayEarnings[0]["total_amount"]) && $getTodayEarnings[0]["total_amount"] == null) 
							{
								//unset($getTodayEarnings[0]["total_amount"]);
								$getTodayEarnings[0]["total_amount"] = 0;
							}
							$getTodayEarnings[0]["total_amount"] = !empty($getTodayEarnings[0]["total_amount"]) ? $getTodayEarnings[0]["total_amount"] : 0;
							$getTodayEarnings[0]["total_amount"] = commonfunction::amount_indecimal($getTodayEarnings[0]["total_amount"],'api');
							$getWeeklyReport = $api->getWeeklyWiseEarnings($mobiledata['driver_id'],$current_time);
							$message = array("message" => __('success'),"today_earnings"=>$getTodayEarnings,"weekly_earnings"=>$getWeeklyReport,"status"=>1);
						} 
						else 
						{
							$message = array("message" => __('driver_not_login'),"status"=>-1);
						}
					} else {
						$message = array("message" => __('invalid_user'),"status"=>-1);
					}
				} else {
					$message = array("message" => __('invalid_request'),"status"=>-1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$getWeeklyReport);
			break;

			case 'check_companydomain':
				$company_domain = strtolower(trim($mobiledata['company_domain']));
                $result = $api_ext->check_company_domain($mobiledata);	
                $api_key='';
                if(count($result)==0 && PACKAGE_TYPE!=3)
                {
                    $message = array("message" => __('invalid_company'),"status"=>-8);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
                    exit;
                }
				//New version Header Authorization checking
				else if(isset($headers['Authorization']) && (PACKAGE_TYPE==3))
				{ 					
					$api_key = isset($result[0]['mobile_api_key'])?$result[0]['mobile_api_key']:'';
					$company_api_key=$headers['Authorization'];                    
					if($company_api_key!=$api_key){
                        $message = array("message" => __('invalid_company'),"status"=>-8);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
                        exit;
                    }
				}
				
				#Live
				/*if((!empty($mobiledata['company_domain']) && count($result) == 0) || $mobiledata['company_domain'] =='') {
					$message = array("message" => __('sub_domain_notexists'),"status" => 2);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					break;
				}*/
									
				if(count($result) > 0)
				{
                    if($api_key=='')
                    {
                    	$api_key=$api_key_encrypt;
                	}
					#Live
					/*$mobiledata['domain_id'] = $result[0]['domain_id'];
					$status = $api->update_used_status($mobiledata);
                                        $live_domain=isset($result[0]['live_domain'])?$result[0]['live_domain']:'';
					$baseurl = PROTOCOL."://".$company_domain.".".$mobiledata['company_main_domain']."/mobileapi117/index/";
                                        if($live_domain!=''){
						$baseurl = PROTOCOL."://".$live_domain."/mobileapi117/index/";
					}
					$folderPath = ($mobiledata['device_type'] == 2) ? "public/".$company_domain."/iOS/" : "public/".$company_domain."/android/";
					$iOSImage = URL_BASE."public/".$company_domain."/iOS/static_image/";*/
					$baseurl = URL_BASE."driverapi113/index/";
					$folderPath = ($mobiledata['device_type'] == 2) ? MOBILE_iOS_IMAGES_FILES : MOBILE_ANDROID_IMAGES_FILES;
					$iOSImage = URL_BASE.MOBILE_iOS_IMAGES_FILES."static_image/";
				} 
				else 
				{
                    if($api_key == '')
                    {
                        $api_key=$api_key_encrypt;
                    }
					#Local
					$baseurl = URL_BASE."driverapi113/index/";
					$folderPath = ($mobiledata['device_type'] == 2) ? MOBILE_iOS_IMAGES_FILES : MOBILE_ANDROID_IMAGES_FILES;
					$iOSImage = URL_BASE.MOBILE_iOS_IMAGES_FILES."static_image/";
				}
				
				$folderPath = ($mobiledata['device_type'] == 2) ? MOBILE_iOS_IMAGES_FILES : MOBILE_ANDROID_IMAGES_FILES;
                                
				//functionality to get imgaes, language and color code files for iOS App
				$dateStamp = $_SERVER['REQUEST_TIME'];
				$iOSPathArr = array();		
				# dynamic language array
				$dynamic_language_array = array('en' => 'english');
				if(defined('DYNAMIC_LANGUAGE_ARRAY'))
					$dynamic_language_array = DYNAMIC_LANGUAGE_ARRAY;			
				$iOSPassengerLanguageDOC = DOCROOT.$folderPath."language/passenger/";
				$iOSDriverLanguageDOC = DOCROOT.$folderPath."language/driver/";
				$iOSPassengerLanguageVIEW = URL_BASE.$folderPath."language/passenger/";				
				$iOSDriverLanguageVIEW = URL_BASE.$folderPath."language/driver/";
				if($mobiledata['device_type'] == 2){
					$iOSColorCode = URL_BASE.$folderPath."colorcode/PassengerAppColor.xml?timeCache=".$dateStamp;
					$iOSDriverColorCode = URL_BASE.$folderPath."colorcode/DriverAppColor.xml?timeCache=".$dateStamp;
				} else {
					$iOSColorCode = URL_BASE.$folderPath."colorcode/";
					$iOSDriverColorCode = "";
				}
				//~ $staticLanguArr = array("english"=>"en","turkish"=>"tr","arabic"=>"ar","german"=>"de","russian"=>"ru","spanish"=>"es","indonesian"=>"id","french"=>"fr");
				if(defined('STATIC_LANGUAGE_ARRAY')){
					$staticLanguArr = array_flip(STATIC_LANGUAGE_ARRAY);
				}else{
					$staticLanguArr = array("english"=>"en","turkish"=>"tr","arabic"=>"ar","german"=>"de","russian"=>"ru","spanish"=>"es","indonesian"=>"id","french"=>"fr");
				}
				//iOS Passenger Language Files
				$passLangFiles  = opendir($iOSPassengerLanguageDOC);
				$passLangs = array();
				while (false !== ($filename = readdir($passLangFiles))) 
				{
					if($filename != '.' && $filename != '..')
					{
						$langArr = explode('_',$filename);
						if(isset($langArr[1]))
							$langName =  ($mobiledata['device_type'] == 2) ? str_replace('.strings','',$langArr[1]) : str_replace('.xml','',$langArr[1]);								
						$designType = 'LTR';								
						$checkRTL = strtolower($langName);	
						if(in_array($checkRTL,$dynamic_language_array))
						{
							if($checkRTL == "arabic" || $checkRTL == "urdu") {								
								$designType = 'RTL';		
							}
							$langType = isset($staticLanguArr[$checkRTL]) ? $staticLanguArr[$checkRTL]:'';		
							$fileNam = $filename."?timeCache=".$dateStamp;		
							$langFilesArr = array("language"=>$langName,"design_type"=>$designType,"language_code"=>$langType,"url"=>$iOSPassengerLanguageVIEW.$fileNam);
							$passLangs[] = $langFilesArr;
						}						
					}
				}
				
				//iOS Driver Language Files
				$driverLangFiles  = opendir($iOSDriverLanguageDOC);
				$driverLangs = array();
				while (false !== ($driverFilename = readdir($driverLangFiles))) 
				{
					if($driverFilename != '.' && $driverFilename != '..')
					{
						$driverLangArr = explode('_',$driverFilename);
						if(isset($driverLangArr[1]))
							$driverLangName =  ($mobiledata['device_type'] == 2) ? str_replace('.strings','',$driverLangArr[1]) : str_replace('.xml','',$driverLangArr[1]);
						$designType = 'LTR';
						$checkRTL = strtolower($driverLangName);		
						if(in_array($checkRTL,$dynamic_language_array)){
							if($checkRTL == "arabic" || $checkRTL == "urdu") {		
								$designType = 'RTL';		
							}		
							$langType = isset($staticLanguArr[$checkRTL]) ? $staticLanguArr[$checkRTL]:'';		
							$driverFileName = $driverFilename."?timeCache=".$dateStamp;		
							$driverLangFilesArr = array("language"=>$driverLangName,"design_type"=>$designType,"language_code"=>$langType,"url"=>$iOSDriverLanguageVIEW.$driverFileName);
							$driverLangs[] = $driverLangFilesArr;
						}
					}
				}
				
				$host  = (!empty($mobiledata['company_domain'])) ? $mobiledata['company_domain'].".".$mobiledata['company_main_domain'] : $_SERVER['SERVER_NAME'];
				$dateStamp = $_SERVER['REQUEST_TIME'];
				$key = $host."-".$dateStamp;
				//New version Header Authorization checking
				if(isset($headers['Authorization'])){ 
					$encode =  $mobile_data_ndot_crypt->encrypt_encode($key);
				}
				$message = array("message" =>__('success'),"baseurl" => $baseurl,"apikey" => $api_key,"status" => 1);	
				$iOSPathArr = array("static_image"=>$iOSImage,"driver_language"=>$driverLangs,"passenger_language"=>$passLangs,"colorcode"=>$iOSColorCode,"driverColorCode"=>$iOSDriverColorCode);
				if($mobiledata['device_type'] == 2) {
					$message['iOSPaths'] = $iOSPathArr;
				} else {
					$message['androidPaths'] = $iOSPathArr;
				}
				$message['encode'] = $encode;
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
                                
				//unset($message, $iOSPassengerLanguageDOC, $iOSDriverLanguageDOC,$iOSPassengerLanguageVIEW,$iOSDriverLanguageVIEW,$iOSColorCode,$iOSDriverColorCode);
			break;

			/** Driver Withdraw List **/
			case 'driver_withdraw_list':
				$driver_id = isset($mobiledata['driver_id']) ? $mobiledata['driver_id'] : '';
				if(!empty($driver_id)) 
				{
					$company_det = $api->get_company_id($driver_id);
					$company_id = ($company_det > 0) ? $company_det[0]['company_id'] : $default_companyid;
					$result = $api->get_withdraw_request($company_id,$driver_id);
					if(count($result) > 0) 
					{
						$data = array();
						foreach($result as $f) 
						{
							$status_label = __("pending");
							$status_id = 0;
							if($f["request_status"] == 1) {
								$status_label = __("approved");
								$status_id = 1;
							} else if($f["request_status"] == 2) {
								$status_label = __("rejected");
								$status_id = 2;
							}
							$data[] = array (
								"withdraw_request_id" => $f["withdraw_request_id"],
								"request_id" => "#".$f["request_id"],
								"withdraw_amount" => $this->site_currency.commonfunction::amount_indecimal($f["withdraw_amount"],'api'),
								"request_date" => Commonfunction::getDateTimeFormat($f["request_date"],1),
								"brand_type" => ($f["brand_type"] == 1) ? __("Multy") : __("single"),
								"request_status" => $status_label,
								"request_status_id" => $status_id
							);
						}
						$message = array("message" => __('withdraw_req_list'),"details" => $data, "status" => 1);
					}
					else
					{
						$message = array("message" => __('no_data'),"status" => -1);
					}
				} else {
					$message = array("message" => __('invalid_request'),"status" => -1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$result);
			break;
			
			case 'google_geocoding':
				$postData= $mobiledata;
				$key = GOOGLE_GEO_API_KEY;
				$origin = isset($postData['origin']) ? $postData['origin'] : '';	# 11.0317873,77.0186404
				$destination = isset($postData['destination']) ? $postData['destination'] : '';		#11.020983,76.9663344
				$geocoding_type = ['1','2','3'];	# 1-direction api, 2 -geocoding api, 3 -distance matrix api
				$type = isset($postData['type']) ? $postData['type'] : '';
				
				if($origin != '' && (in_array($type,$geocoding_type))){
					
					if($type == 1 && $destination == ''){
						$message = array("message" => __('invalid_request'),"status" => -1);          
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						exit;
					}
					
					$direction_api = "https://maps.googleapis.com/maps/api/directions/json?key=".$key."&origin=".$origin."&destination=".$destination."&sensor=false&mode=driving&alternatives=true&language=null";
					
					$geocoding_api = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$origin."&key=".$key;
					
					$distancematrix_api = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$origin."&destinations=".$destination."&mode=car&language=en&key=".$key;
					
					if($type == 1)
						$url = $direction_api;
					else if($type == 2)
						$url = $geocoding_api;
					else 
						$url = $distancematrix_api;
						
					$file = file_get_contents($url);
					$arr_conversion = json_decode($file);
					//~ print_r($arr_conversion);exit;
					$mobile_data_ndot_crypt->encrypt_encode_json($arr_conversion, $additional_param);
				}else{
					$message = array("message" => __('invalid_request'),"status" => -1);             
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);          
				}			
			break;
			
			case 'driver_wallet_request':
				$driverId = isset($mobiledata['driver_id']) ? $mobiledata['driver_id'] : '';
				$requestedAmount = isset($mobiledata['driver_wallet_amount']) ? $mobiledata['driver_wallet_amount'] : '';
				if(!empty($requestedAmount) && !empty($driverId)) {
					$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
					
					$company_det =$api->get_company_id($driverId);
					$companyId = (count($company_det) > 0) ? $company_det[0]['company_id'] : $default_companyid;
					$currentTime = $this->commonmodel->getcompany_all_currenttimestamp($companyId);
					$driverWalletDets = $api->getDriverReferralDetails($driverId);
					$existWalletAmt = (count($driverWalletDets) > 0) ? $driverWalletDets[0]['registered_driver_wallet'] : 0;					
					$driver_referral_wallet_pending_amount = 0;
					$referral_pending_result = $api->driver_referral_pending_amount($driverId);
					if(count($referral_pending_result) > 0) {
						$driver_referral_wallet_pending_amount = ($referral_pending_result[0]["driver_referral_wallet_pending_amount"]) ? $referral_pending_result[0]["driver_referral_wallet_pending_amount"] : 0;
					}
					$existWalletAmt = $existWalletAmt - $driver_referral_wallet_pending_amount;
					if($existWalletAmt >= $requestedAmount) {
						$result = $api->saveDriverWalletRequest($driverId,$requestedAmount,$currentTime);
						if($result) {
							$driver_referral_wallet_pending_amount = 0;
							$referral_pending_result = $api->driver_referral_pending_amount($driverId);
							if(count($referral_pending_result) > 0) {
								$driver_referral_wallet_pending_amount = ($referral_pending_result[0]["driver_referral_wallet_pending_amount"]) ? $referral_pending_result[0]["driver_referral_wallet_pending_amount"] : 0;
							}
							$wallArr = array("registered_driver_wallet" => $existWalletAmt,'registered_driver_id' => $driverWalletDets[0]['registered_driver_id']);
							$existWalletAmt =  $existWalletAmt - $requestedAmount;
							$walletUpdate = $api_ext->update_driver_referral_list($wallArr);
							$message = array("message" => __('driver_wallet_request_send'),"driver_wallet_amount"=>$existWalletAmt,"driver_wallet_pending_amount" => $driver_referral_wallet_pending_amount,"status"=>1);
						} else {
							$message = array("message" => __('try_again'),"status"=>-1);
						}
					} else {
						$message = array("message" => __('dont_have_sufficient_wallet_amount'),"status"=>-1);
					}
				} else {
					$message = array("message" => __('invalid_request'),"status"=>-1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$api_ext);
			break;
		}
	}

	public function send_mail_to_withdraw_request( $to, $name, $driver_mobile_no, $driver_company_name, $wallet_request_id, $wallet_request_amount, $wallet_request_url )
	{
		$replace_variables=array(REPLACE_LOGO => EMAILTEMPLATELOGO,
								REPLACE_SITENAME => $this->app_name,
								REPLACE_USERNAME => $name,
								REPLACE_MOBILE => $driver_mobile_no,
								REPLACE_COMPANY => $driver_company_name,
								REPLACE_WALLET_REQ_ID => $wallet_request_id,
	            				REPLACE_WALLET_REQ_AMT => $this->site_currency.$wallet_request_amount,
	            				REPLACE_WALLET_REQ_URL => $wallet_request_url,
								REPLACE_SITEEMAIL => $this->siteemail,
								REPLACE_COMPANYDOMAIN => $this->domain_name,
								REPLACE_SITEURL => URL_BASE,
								REPLACE_COPYRIGHTS => SITE_COPYRIGHT,
								REPLACE_COPYRIGHTYEAR => COPYRIGHT_YEAR);
								
		$emailTemp = $this->commonmodel->get_email_template('send_driver_withdraw_request', $this->email_lang);
		if(isset($emailTemp['status']) && ($emailTemp['status'] == '1'))
		{			
			$email_description = isset($emailTemp['description']) ? $emailTemp['description']: '';
			$subject = isset($emailTemp['subject']) ? $emailTemp['subject']: '';
			$email_description = $email_description;
				$message           = $this->emailtemplate->emailtemplate($email_description, $replace_variables);
			$from              = CONTACT_EMAIL;										
			//$to = $laterBookings[0]['email'];
			$redirect = "no";	
			if($to != '') {
				if(SMTP == 1)
				{
					include($_SERVER['DOCUMENT_ROOT']."/modules/SMTP/smtp.php");
				}
				else
				{
					// To send HTML mail, the Content-type header must be set
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					// Additional headers
					$headers .= 'From: '.$from.'' . "\r\n";
					$headers .= 'Bcc: '.$to.'' . "\r\n";
					mail($to,$subject,$message,$headers);	
				}
			} 
		}						
		//** Email Section Ends **//
	}
}
