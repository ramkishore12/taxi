<?php defined('SYSPATH') or die('No direct script access.');

/****************************************************************

* Contains API details - Version 8.0.0

* @Package: Taximobility

* @Author:  NDOT Team

* @URL : http://www.ndot.in

****************************************************************/
Class Controller_Passengerapi113 extends Controller_Modpassengerapi113
{
	
	public function __construct()
	{	
		$this->session = Session::instance();
		try {
			require Kohana::find_file('classes','mobile_common_config');
			require Kohana::find_file('classes/controller', 'ndotcrypt');
                     
			$this->commonmodel=Model::factory('commonmodel');
			//DEFINE("MOBILEAPI_107","mobileapi118");
			DEFINE("MOBILEAPI_107","passengerapi113");
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



		# APi logger 

		$currentTime=date('Y_m_d_H');
			  if (!file_exists(DOCROOT."application/loc/".$currentTime.".txt"))
			  {
	        		@$newFile= fopen(DOCROOT."application/loc/".$currentTime.".txt", 'w+');	
	        		@fclose($newFile);
	        		@chmod(DOCROOT."application/loc/".$currentTime.".txt", 0777);
	        }
	        if((string)$method=='driver_location_history')
	        {
	        	  @file_put_contents(DOCROOT."application/loc/".$currentTime.".txt","Method ".$method."<br/>". json_encode($mobiledata)."<br/>"."Time is 13123".date('Y-m-d H:i:s')."<br/>"."<br/>" . PHP_EOL, FILE_APPEND);
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
	        	 
	        	@file_put_contents(DOCROOT.'application/api/'.$currentTime.".txt",'<div class="api">Method <b style="color:red;" onclick="return selectElementContents(this);"><div class="method">'.$method."</div></b><br /><button onClick='return verify(event,this,1);'>Verify</button><br />".'Raw Data'."<br />".'<br /><p ><div class="decode">'.json_encode($mobiledata).'</div></p><br /><br />Get Method Params<br /><br />'.json_encode($_GET)."<br /><br />".'PostMan Request'."<br />".'<br /><div class="encode"><p onclick="return selectElementContents(this);">'.$beforeApi."</p></div><br /><br />"."Api : <b>V1</b> Time is ".date('Y-m-d H:i:s')."<br />"."<br /></div>" . PHP_EOL, FILE_APPEND);
	        }
		# APi logger end



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
			$config_array = $api_ext->select_site_settings($default_companyid);
			//print_r($config_array);exit;
			$config_array['app_name'] = SITE_NAME;		
			$config_array['site_country'] = DEFAULT_COUNTRY;
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
				//~ print_r($company_model_details);exit;
				if(count($company_model_details)>0) {
					foreach($company_model_details as $key => $val) {
						if(!empty($val)){
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

			case 'dynamic_page':			
				$page_array = $_GET;
				$check_validation = $this->check_dynamic_array($page_array);
				if($check_validation->check()) 
				{
					$pagename = $page_array['pagename'];
					$device_type = $page_array['device_type'];
					$pagecontent=$content="";
					if($pagename != null)
					{	
						$content_cms = $api->getcmscontent($pagename,$default_companyid);
						if(count($content_cms)>0)
						{
							foreach($content_cms as $value)
							{								
								$pagecontent = isset($value['content'])?$value['content']:'';
								$content = htmlentities($pagecontent);								
								$menu = $value['menu'];
							}
						}
						else
						{
							if($device_type == 1)
							{
								echo __('page_not_found');
								break;	
							}
							else if($device_type == 2)
							{
								$message = array("message" => __('page_not_found'),"status"=>2);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);	
								//unset($message,$json_decode);
								break;	
							}			
							else
							{
								$message = array("message" => __('page_not_found'),"status"=>2);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								break;
							}	
	
						}							
					}
					else
					{
						$message = array("message" => __('invalid_page'),"status"=>-1);	
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						break;	
					}
				if($device_type == 1)
				{
					echo $pagecontent;
					break;	
				}
				else if($device_type == 2)
				{
					$result = array("content"=>$content,"title"=>$menu);
					$message = array("message"=>__('success'),"detail" => $result,"status"=>1);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);	
					break;	
				}			
				else
				{
					$message = array("message" => __('invalid_page'),"status"=>-1);	
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					break;	
				}	 
			}
			else
			{
				$detail = $check_validation->errors('errors');
				$message = array("detail"=>$detail,"status"=>-3,"message"=>__('validation_error'));		
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);			
			}			
			break;

			case 'wallet_addmoney':
				$passenger_id = isset($mobiledata['passenger_id']) ? $mobiledata['passenger_id'] : '';
				$money = isset($mobiledata['money']) ? $mobiledata['money'] : '';
				$promo_code = isset($mobiledata['promo_code']) ? urldecode($mobiledata['promo_code']) : '';
				$p_validator = $this->wallet_addmoney_validation($mobiledata);
				$promocodeAmount = 0;
			    if($p_validator->check())
			    {
				    if($promo_code != "")
					{
						# new promotion process
						$promoResponse = $api_ext->getpromodetails($promo_code,$passenger_id);
						$promodiscount = $promoResponse['promo_discount'];
						$promo_type = isset($promoResponse['promo_type']) ? $promoResponse['promo_type'] : '';
						$promocodeAmount = Commonfunction::promotion_process($promo_type,$promodiscount,$money,$trip_wallet=2);
						
						$mobiledata['money'] = $money + $promocodeAmount;
					}
					
					$passenger_wallet = $this->wallet_addmoney($mobiledata,$default_companyid,$promo_code,$promocodeAmount);
					$cancelFare = $api->get_passenger_cancel_farebyid($passenger_id);
					$wallAmount = 0;
					$passwallArr = explode("#",$passenger_wallet);
					$wallAmount = isset($passwallArr[1]) ? $passwallArr[1] : 0;
					$passenger_wallet = $passwallArr[0];
					
					if($passenger_wallet == 1) 
					{
                        $credit_card_sts = ($wallAmount >= $cancelFare) ? 0 : SKIP_CREDIT_CARD;
						$message = array("message" => __('amount_added_wallet'), "credit_card_status" => $credit_card_sts,"status"=>1);
					} 
					else if($passenger_wallet == 0)
					{
						$gateway_response = isset($wallAmount)?$wallAmount:'Payment Failed';
						$message = array("message" => $gateway_response, "gateway_response" =>$gateway_response,"status"=>0);		
					} else {
						$message = array("message" => __('no_payment_gateway'),"status"=>-1);
					}
				} else {
					$errors = $p_validator->errors('errors');	
					$message = array("message" => __('validation_error'),"detail"=>$errors,"status"=>-1);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			break;

			case 'nearestdriver_list':
				$search_array = $mobiledata;
				$validator = $this->nearestdriver_validation($search_array);			
				if($validator->check())
				{
					$passenger_id = isset($search_array['passenger_id']) ? $search_array['passenger_id']:'';
					if($search_array['latitude'] !='0' && $search_array['longitude'] !='0')
					{	
						$skip_fav = isset($search_array['skip_fav']) ? $search_array['skip_fav']: 0;
						$favourite_places = $popular_places = array();
						if($skip_fav != 1)
						{									
							$get_favouritepopularplaces = $api->get_favouritepopularplaces($passenger_id,1);
							if(!empty($get_favouritepopularplaces))
							{
								$favourite_places = $get_favouritepopularplaces;
							}
						}							
						#popular
						$get_favouritepopularplaces = $api->get_favouritepopularplaces($passenger_id,2,$search_array['latitude'],$search_array['longitude']);									
						if(!empty($get_favouritepopularplaces)){
							$popular_places = $get_favouritepopularplaces;
						}							
						$passengerStatus = 3;$passengerStatusMessage = "";
						$passenger_id = $search_array['passenger_id'];
						$passenger_status_result = $api->get_passenger_status($passenger_id);
						if(count($passenger_status_result) > 0) {
							if(isset($passenger_status_result[0]["user_status"]) && $passenger_status_result[0]["user_status"] != 'A') {
								$passengerStatus = 5;
								$passengerStatusMessage = ($passenger_status_result[0]["user_status"] == "D") ? __("passenger_status_blocked_msg") : __("passenger_status_deleted_msg");
							}
						}
						$find_model = Model::factory(FIND);	
						$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);							
						$latitude = $search_array['latitude'];
						$longitude = $search_array['longitude'];
						
						# getting city name 
						//$cityName = Commonfunction::getCityName($latitude,$longitude);
						/** City name get from request , if not default city name taken **/
						$cityName = isset($search_array['city_name']) ? $search_array['city_name'] : DEFAULT_CITY_NAME;
						//~ echo $cityName;exit;
						
						$miles = DEFAULTMILE;//$search_array['no_of_miles'];
						$unit = UNIT; // 0 - KM, 1 - Miles
						$taxi_model = $search_array['motor_model'];
						$service_type="";
						//$passengerCompany = (!empty($passenger_id)) ? $api->get_passenger_company_id($passenger_id) : 0;
						$passengerCompany = "";
						$company_id = ($passengerCompany != 0) ? $passengerCompany : $default_companyid;
							
						$pickupTimezone = $api->getpickupTimezone($latitude,$longitude);
						$currentTime = convert_timezone('now',$pickupTimezone);
						$driver_details = $find_model->getNearestDrivers($taxi_model,$latitude,$longitude,$currentTime,$company_id,$miles,$unit);
						//echo "<pre>";print_r($driver_details);exit();
						$get_modelfare_details=$api->get_modelfare_details($default_companyid, $taxi_model, $cityName);
						# Night & Evening fare settings
						if(!empty($get_modelfare_details))
						{							
							$trip_time = $this->currentdate;
							
							$night_charge         = isset($get_modelfare_details[0]['night_charge']) ? $get_modelfare_details[0]['night_charge']:0;
							$night_timing_from    = isset($get_modelfare_details[0]['night_timing_from']) ? $get_modelfare_details[0]['night_timing_from']: '';
							$night_timing_to   	  = isset($get_modelfare_details[0]['night_timing_to']) ? $get_modelfare_details[0]['night_timing_to']: '';
							$night_fare   
							 = isset($get_modelfare_details[0]['night_fare']) ? $get_modelfare_details[0]['night_fare']: '';
								
							$evening_charge         = isset($get_modelfare_details[0]['evening_charge'])?$get_modelfare_details[0]['evening_charge']:0;
							$evening_timing_from    = isset($get_modelfare_details[0]['evening_timing_from'])?$get_modelfare_details[0]['evening_timing_from']:'';
							$evening_timing_to      = isset($get_modelfare_details[0]['evening_timing_to'])?$get_modelfare_details[0]['evening_timing_to']:'';
							$evening_fare           = isset($get_modelfare_details[0]['evening_fare'])?$get_modelfare_details[0]['evening_fare']:'';
								
							# Night Fare Calculation
							$nightfare_applicable = $evefare_applicable = '0';
							if ($night_charge != 0) 
							{			
								$parsed = date_parse($night_timing_from);
								$night_from_seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

								$parsed = date_parse($night_timing_to);
								$night_to_seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

								$nightfare_applicable = $date_difference=0;
							
								$night_start_date ='';
								$night_end_date ='';

								$night_start_date= date('Y-m-d')." ".$night_timing_from;
								$night_timing_to_value=$night_timing_to;
								$night_timing_from_value=$night_timing_from;
								$night_end_date= date('Y-m-d')." ".$night_timing_to;
								if(strtotime($night_end_date) < strtotime($night_start_date))
								{
									$night_start_date=date('Y-m-d', strtotime('-1 day'))." ".$night_timing_from_value;
								}
								else
								{
									$night_start_date= date('Y-m-d')." ".$night_timing_from_value;
								}
								
								if( strtotime($trip_time) >= strtotime($night_start_date) && strtotime($trip_time) <= strtotime($night_end_date))
								{
									$nightfare_applicable = 1;
								}
							}							
								
							# Evening Fare Calculation
							$parsed = explode(':',date('H:i:s', strtotime($trip_time)));
							$pickup_seconds = $parsed[0] * 3600 + $parsed[1] * 60 + $parsed[2];
											
							$parsed_eve = date_parse($evening_timing_from);
							$evening_from_seconds = $parsed_eve['hour'] * 3600 + $parsed_eve['minute'] * 60 + $parsed_eve['second'];

							$parsed_eve = date_parse($evening_timing_to);
							$evening_to_seconds = $parsed_eve['hour'] * 3600 + $parsed_eve['minute'] * 60 + $parsed_eve['second'];

							$eveningfare = 0; $evefare_applicable=$date_difference=0;
							if ($evening_charge != 0) 
							{
								if( $pickup_seconds >= $evening_from_seconds && $pickup_seconds <= $evening_to_seconds)
								{
									$evefare_applicable = 1;
								}
							}
							$get_modelfare_details[0]['nightfare_applicable'] = $nightfare_applicable;
							$get_modelfare_details[0]['eveningfare_applicable'] = $evefare_applicable;
						}
						//~ print_r($get_modelfare_details);exit;
						$getFavDrivers = '';
						if(!empty($passenger_id)) { //to update passengers lat long
							$datas  = array('latitude' => $latitude, 'longitude' => $longitude); // Start to Pickup
							$uplatlong = $api_ext->update_nearpassengers($datas,$passenger_id);
							//to get favourite drivers for a passenger
							$favDrivers = $api->getFavDrivers($passenger_id);
							$getFavDrivers = (!empty($favDrivers)) ? explode(",",$favDrivers) : '';
						}
						$nearest_driver='';
						$a=1;
						$temp='10000';
						$prev_min_distance='10000~0~0~0';
						$taxi_id='';
						$temp_driver=0;
						$nearest_key=0;
						$prev_key=0;
						$total_count = count($driver_details);						
						$company_contact_no='';
						if(COMPANY_CID != 0)
						{
							$company_contact_no=COMPANY_CONTACT_PHONE_NUMBER;
						}
						$no_vehicle_msg=__('no_vehicle_msg').$company_contact_no;
							
						//Get Fare details of the Taxi model_id Start
						$fare_details=__('no_fare_details_found');
						if(count($get_modelfare_details)>0){
							$fare_details=$get_modelfare_details[0];
						}
						//Get Fare details of the Taxi model_id End
						$fare_details['metric'] = UNIT_NAME;
						$fare_details['fare_calculation_type']=FARE_CALCULATION_TYPE; 
						
						$fav_drivers_exist = 0;
						if($total_count > 0)
						{
							$driver_id = isset($driver_details[0]['driver_id'])?$driver_details[0]['driver_id']:"";
							$next_higher_model = isset($driver_details[0]['next_higher_model'])?$driver_details[0]['next_higher_model']:0;
							$pass_confirm_mess = isset($driver_details[0]['pass_confirm_mess'])?$driver_details[0]['pass_confirm_mess']:"";
							$totalrating = 0;
							
							foreach($driver_details as $key => $value)
							{
								
								$driver_allow = 1;
								$driver_register_type = isset($value['driver_register_type'])?$value['driver_register_type']:"1";//default commission
								
								if((PACKAGE_TYPE == 3 || PACKAGE_TYPE == 0) && $driver_register_type == '2'){
									$driver_plandetails = $api_ext->get_driver_plan_info($driver_id);
									if(isset($driver_plandetails['planinfo'][0]) && !empty($driver_plandetails['planinfo'][0])){
										$driver_plan_details = $driver_plandetails['planinfo'][0];
									   if(isset($driver_plan_details['expiry_date']) && $driver_plan_details['expiry_date']!='' && isset($driver_plan_details['subscribed_date']) && $driver_plan_details['subscribed_date']!='' ){
	                       
					                        $expirydate = Commonfunction::convertphpdate('',$driver_plan_details['expiry_date']);
					                        $subscribeddate = Commonfunction::convertphpdate('',$driver_plan_details['subscribed_date']);
					                       
					                        $today = date('Y-m-d H:i:s',time()); 
					                        $exp = date('Y-m-d H:i:s',strtotime($expirydate));
					                        $sub = date('Y-m-d H:i:s',strtotime($subscribeddate));
					                       
					                        $subDate =  date_create($exp);
					                        $expDate =  date_create($exp);
					                        $todayDate = date_create($today);
					                       
					                        $expdiff =  date_diff($todayDate, $expDate);
					                        $remaining_day  = $expdiff->format("%R%a");
					                        $remaining_hour = $expdiff->format('%h');
 											$remaining_minutes = $expdiff->format('%i');
					                        $final_remaining_mts = $remaining_hour*60 + $remaining_minutes;
					                        
					                        $betdiff =  date_diff($subDate, $expDate);
					                        $plan_day  = $betdiff->format("%R%a");
					                        $plan_hour = $betdiff->format('%h');
					                        
											if($exp<$today || ($remaining_day<=0 && $final_remaining_mts <DRIVER_GRACE_TIME_MTS)){
												$driver_allow = 0;
												unset($driver_details[$key]);
											}
					                    	
					                    }else{
											$driver_allow = 0;
											unset($driver_details[$key]);//need to subscribe plan
										}									
									}else{
										$driver_allow = 0;
										unset($driver_details[$key]);//need to subscribe plan

									}
								}//end of package type 3 with driver subscription
								if(DRIVER_THRESHOLD_SETTING == 1 && $driver_register_type == '1'){	
									if(isset($value['account_balance']) && $value['account_balance']!=''){
									  if($value['account_balance'] < (double)DRIVER_THRESHOLD_AMOUNT){
									  	$driver_allow = 0;
									     unset($driver_details[$key]);
									  }
									}
								}//end of package type 3 with driver commission

								if($driver_allow == 1){
									//Set nearest driver equal to 1
									if($driver_id == $value['driver_id'])
									{
										$driver_details[$nearest_key]['nearest_driver'] ='1';
									}
									else
									{
										$driver_details[$key]['nearest_driver'] ='0';
									}
									
									if(!empty($getFavDrivers) && in_array($value['driver_id'],$getFavDrivers)) {
										$fav_drivers_exist++;
									}
									// Get last 20 coordinates of the driver Start
									$get_driver_coordinates= '0';
									$driver_details[$key]['driver_coordinates'] = $get_driver_coordinates;
									// Get last 20 coordinates of the driver End

									//Get Nearest driver Taxi speed Start										
									//FARE_CALCULATION_TYPE : 1 => Distance, 2 => Time, 3=> Distance / Time
											
									//Get Nearest driver Taxi speed Start
									$driver_details[$key]['distance_km'] = round($value['distance_km'],5);
								}
							}
							//echo '<pre>'; print_r($driver_details); exit;
							if(count($driver_details) > 0){
								$driver_details = array_values($driver_details);
								$message = array("detail" => $driver_details, "next_higher_model" => $next_higher_model, "pass_confirm_mess" => $pass_confirm_mess, "fav_drivers"=>$fav_drivers_exist,"fav_driver_message"=>__('fav_driver_book_message'),"fare_details"=>$fare_details,"driver_around_miles"=>DEFAULTMILE,"status" => 1,"message" => 'success','metric'=>UNIT_NAME);
							}
								
							else{

								$message = array("message" => $no_vehicle_msg,"fav_drivers"=>$fav_drivers_exist,"fav_driver_message"=>__('fav_driver_book_message'),"fare_details"=>$fare_details,"driver_around_miles"=>DEFAULTMILE,"status" => 0);	
							}
								
							$message['favourite_places'] = $favourite_places;
							$message['popular_places'] = $popular_places;	
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							break;
					    }
					    else
					    {
							$message = array("message" => $no_vehicle_msg,"fav_drivers"=>$fav_drivers_exist,"fav_driver_message"=>__('fav_driver_book_message'),"fare_details"=>$fare_details,"status" => $passengerStatus,"passenger_status_message" => $passengerStatusMessage);
							 
							$message['favourite_places'] = $favourite_places;
							$message['popular_places'] = $popular_places;					 
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							exit;							  
						}
					}
					else
					{
						$message = array("message" => __('lat_not_zero'),"status"=>-4);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						exit;	
					}
				}
				else
				{
					$errors = $validator->errors('errors');	
					$message = array("message" => __('validation_error'),"detail"=>$errors,"status"=>-5);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					exit;					
				}							
			break;

			case 'savebooking':
				$search_array = $mobiledata;
				$validator = $this->search_validation($search_array);
				$passenger_id = $search_array['passenger_id'];
				$promo_code = isset($search_array['promo_code'])?$search_array['promo_code']:'';
				$referral_code = isset($search_array['referral_code'])?$search_array['referral_code']:'';
				$passenger_payment_option = isset($search_array['passenger_payment_option'])?$search_array['passenger_payment_option']:0;
				$check_passenger = $api->check_passengerlogin($passenger_id);
				if($check_passenger == 0)
				{
					$message = array("message" => __('passenger_blocked'),"status" => -10);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						break;
				}
				if($validator->check())
				{
					if($promo_code != "")
					{
						$check_promo = $api->checkpromocode($promo_code,$passenger_id,$default_companyid);
						if($check_promo == 0)
						{
							$message = array("message" => __('invalid_promocode'),"status" => 3);
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							break;
						}
						else if($check_promo == 3)
						{
							$message = array("message" => __('promo_code_startdate'),"status" => 3);
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							break;
						}
						else if($check_promo == 4)
						{
							$message = array("message" => __('promo_code_expired'),"status" => 3);
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							break;
						}
						else if($check_promo == 2)
						{
							$message = array("message" => __('promo_code_limit_exceed'),"status" => 3);
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							break;
						}
						else
						{
							$formvalues['promo_code'] = $promo_code;
						}
					}

					if(isset($search_array['rental_outstation']) && ($search_array['rental_outstation'] != '' && $search_array['rental_outstation'] != 0))
					{
						$rent_out_plan_id = isset($search_array['rent_out_tour_id'])?$search_array['rent_out_tour_id']:0;
						$check_package_valid = $api->check_package_valid($rent_out_plan_id);
					//	print_r($check_package_valid);exit();
						if($check_package_valid == 0)
						{
							$message = array("message" => __('package_not_valid'),"status" => 3);
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							break;
						}

					}
//					exit;
					
					if($search_array['latitude'] !='0' && $search_array['longitude'] !='0')
					{
						# promo code for new passengers
						$get_promo = $api->getnew_promo($passenger_id);
						
						if($get_promo != null){
							$promo_code = $get_promo;
							$formvalues['promo_code'] = $promo_code;
							$mobiledata['promo_code'] = $promo_code;
						}							
						$add_model = Model::factory('add');
						$find_model = Model::factory(FIND);
						$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
						$latitude = $search_array['latitude'];
						$longitude = $search_array['longitude'];
						
						$miles = DEFAULTMILE;
						$no_passengers = "";
						$pickup_time = $search_array['pickup_time'];
							
						$pickupplace = urldecode($search_array['pickupplace']);
						$dropplace = urldecode($search_array['dropplace']);
						$drop_latitude = $search_array['drop_latitude'];
						$drop_longitude = $search_array['drop_longitude'];
						
						$taxi_fare_km = '';
						$motor_company = '1';
						$motor_model = $search_array['motor_model'];
						$maximum_luggage = "";
						$cityname = $search_array['cityname'];
						$sub_logid = $search_array['sub_logid'];
						$now_after = $search_array['now_after'];
							
						# Ride later pickup time validation
						if($now_after == 1)
						{
							$pickupTime = urldecode($pickup_time);
							$latertime = date('Y-m-d H:i:s', strtotime($this->currentdate)+60*60);
							if(strtotime($pickupTime) < strtotime($latertime))
							{
								$message = array("message" => __('pickuptime_invalid'),"status"=>-4);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								exit;	
							}
						} 
							
						$passenger_id = $search_array['passenger_id'];
						$notes = isset($search_array['notes']) ? $search_array['notes'] : '';
						$passenger_app_version = isset($search_array['passenger_app_version']) ? $search_array['passenger_app_version'] : '';
						$fav_driver_booking_type = isset($search_array['fav_driver_booking_type']) ? $search_array['fav_driver_booking_type'] : 0;
						$approx_trip_fare = isset($search_array['approx_trip_fare']) ? $search_array['approx_trip_fare'] : 0;
						$unit = UNIT; // 0 - KM, 1 - Miles
						$service_type="";
						$city_id  = $api->get_city_id($cityname);	
						$passengerCompany = (!empty($passenger_id)) ? $api->get_passenger_company_id($passenger_id) : 0;
						$company_id = ($passengerCompany != 0) ? $passengerCompany : $default_companyid;
						$passengerInTrip = $api->check_passenger_in_trip($passenger_id,$company_id);
						if($passengerInTrip > 0 && $now_after != 1) 
						{
							//$errorMessage = ($passengerInTrip == 1) ? __('passenger_in_journey') : __('your_last_payment_pending');
							$errorMessage = __('passenger_in_journey');
							$message = array("message" => $errorMessage,"status" => 3,"trip_id" => $passengerInTrip);
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							break;
						}
						//** Function to check passenger's credit card has expired before booking **//
						$gateway_details = $this->commonmodel->gateway_details();//function to get list of payments used ( cash, creditcard, New card )
							
						# check for credit card payment option [split fare]
						if(!empty($mobiledata['friend_id2']) || !empty($mobiledata['friend_id3']) || !empty($mobiledata['friend_id4']))
						{
							$credit = 0;
							foreach($gateway_details as  $arr){
								if($arr['_id'] == 2)
									$credit++;			
							}
							if($credit == 0){
								$message = array("message" => __('creditcard_disabled_admin'),"status" => -6);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								break;
							}
						}
						$payoptArr = array();
						foreach($gateway_details as $key=>$valArr){
							$payoptArr[] = $valArr['pay_mod_id'];
						}
						//function to check passenger has credit card
						$card_type = '';
						$paymentResult = '';
						$default = 'yes';
						$returncode = 1;
						$pre_authorize_amount = 0;
						$passCardDetails = $api_ext->get_creadit_card_details($passenger_id, $card_type, $default);
						$approx_trip_fare = round($approx_trip_fare,2);
						if($approx_trip_fare != 0) {
							$check_wallet_amount = $api->checkWalletAmount($passenger_id,$approx_trip_fare);
							if($check_wallet_amount > 0) {
								$pre_authorize_amount = 0;
							} else {
								if(count($passCardDetails) > 0 && $approx_trip_fare != 0) {
									$creditcard_no = encrypt_decrypt('decrypt',$passCardDetails[0]['creditcard_no']);
									$creditcard_cvv = $passCardDetails[0]['creditcard_cvv'];
									$expdatemonth = $passCardDetails[0]['expdatemonth'];
									$expdateyear = $passCardDetails[0]['expdateyear'];
									$pre_authorize_amount = $approx_trip_fare;
										// Verify wether the card is valid or not
									$paymentresponse=$api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$pre_authorize_amount);
									$returncode=$paymentresponse['code'];
									$paymentResult=(isset($paymentresponse['TRANSACTIONID']) && ($paymentresponse['TRANSACTIONID']!=''))?$paymentresponse['TRANSACTIONID']:$paymentresponse['payment_response'];
									$fcardtype=isset($paymentresponse['cardType'])?$paymentresponse['cardType']:'';
								}
							}
						}

						if($returncode != 0) {
							$favdriversArr = array();
							if($fav_driver_booking_type == 1) {
								$favDrivers = $api->getFavDrivers($passenger_id);
								$favdriversArr = (!empty($favDrivers)) ? explode(",",$favDrivers) : array();
							}
							
							$pickupTimezone = $api->getpickupTimezone($latitude,$longitude);
							$currentTime = convert_timezone('now',$pickupTimezone);
							$pack_check = (isset($search_array['rental_outstation']))?$search_array['rental_outstation']:0;
							$driver_details = $find_model->getNearestDrivers($motor_model,$latitude,$longitude,$currentTime,$company_id,$miles,$unit, '', $pack_check);
							
							$nearest_driver='';
							$a=1;
							$temp='10000';
							$prev_min_distance='10000~0';
							$taxi_id='';
							$temp_driver=0;
							$nearest_key=0;
							$prev_key=0;
							$driver_list="";
							$available_drivers ="";
							$avail_nearest_driver = array();
							$fav_driver_list = array();
							$total_count = count($driver_details);
							$company_contact_no='';
							if(COMPANY_CID != 0)
							{
								$company_contact_no=COMPANY_CONTACT_PHONE_NUMBER;
							}
							$no_vehicle_msg=__('no_vehicle_msg').$company_contact_no;
							$notification_time = $this->notification_time;	
							if($notification_time != 0 )
							{ $timeoutseconds = $notification_time;}else{$timeoutseconds = 15;}
							//Form Values//
							$formvalues = Arr::extract($mobiledata, array('pickupplace','dropplace','pickup_time','driver_id','passenger_id','roundtrip','passenger_phone','cityname','distance_away','sub_logid','drop_latitude','drop_longitude','promo_code','now_after','motor_model','friend_id1','friend_percentage1','friend_id2','friend_percentage2','friend_id3','friend_percentage3','friend_id4','friend_percentage4','friend_percentage_amt1','friend_percentage_amt2','friend_percentage_amt3','friend_percentage_amt4','approx_trip_fare','passenger_payment_option','travel_modelid','os_trip_type','os_days_count'));
							$credit_card_sts = SKIP_CREDIT_CARD; 
							# saving booking place details
							$formvalues['booked_latitude']=isset($search_array['booked_latitude'])?$search_array['booked_latitude']:'';
							$formvalues['booked_longitude']=isset($search_array['booked_longitude'])?$search_array['booked_longitude']:'';
							$formvalues['booked_location']=isset($search_array['booked_location'])?$search_array['booked_location']:'';
							if($total_count > 0)
							{									
								$driver_id = isset($driver_details[0]['driver_id'])?$driver_details[0]['driver_id']:"";
								$taxi_id = isset($driver_details[0]['taxi_id'])?$driver_details[0]['taxi_id']:"";
								$totalrating = 0;
								foreach($driver_details as $key => $value)
								{
									$updatetime_difference = $value['updatetime_difference'];
									//Exclude the drivers who has not logged in and not update the status last specified seconds
									if($updatetime_difference <= LOCATIONUPDATESECONDS)
									{
										if(count($favdriversArr) > 0 && in_array($value['driver_id'], $favdriversArr)) 
										{
											$fav_driver_list[] = $value['driver_id'];
										} 
										else 
										{
											$avail_nearest_driver[] = $value['driver_id'];
										}
									}
								}
										
								if($fav_driver_booking_type == 1 && count($fav_driver_list) == 0) 
								{
									$message = array("message" => __('fav_driver_not_available'),"status" => 4);
									$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);exit;
								}

								if(count($fav_driver_list) > 0) {
									$avail_nearest_driver = $fav_driver_list;
								}
	
								/*********************************************Save booking ***************************************/
								
								$formvalues['taxi_id']=$taxi_id;
								$formvalues['pickup_latitude']=$search_array['latitude'];
								$formvalues['pickup_longitude']=$search_array['longitude'];
								$formvalues['approx_distance']=isset($search_array['approx_distance']) ? $search_array['approx_distance'] : '';
								$formvalues['approx_duration']=isset($search_array['approx_duration']) ? $search_array['approx_duration'] : '';
								$formvalues['pickup_longitude']=$search_array['longitude'];
								$formvalues['driver_id'] =$driver_id;
								$formvalues['notes'] = $notes;
								$formvalues['approx_fare'] =$approx_trip_fare;
								$formvalues['passenger_app_version'] = $passenger_app_version;
								$formvalues['pre_transaction_id']=$paymentResult;
								$formvalues['pre_transaction_amount']=$pre_authorize_amount;
								$formvalues['passenger_payment_option'] = $passenger_payment_option;
								$formvalues['currentTime'] = $currentTime;
								$formvalues['pickupTimezone'] = $pickupTimezone;
								$formvalues['CORRELATIONID']=isset($paymentresponse['CORRELATIONID'])?$paymentresponse['CORRELATIONID']:'';
								$formvalues['rental_outstation'] = isset($search_array['rental_outstation'])?$search_array['rental_outstation']:'';
								$formvalues['rent_out_tour_id'] = isset($search_array['rent_out_tour_id'])?$search_array['rent_out_tour_id']:0;

								/* outstation oneway or round trip */
								$formvalues['os_trip_type'] = isset($search_array['os_trip_type'])?$search_array['os_trip_type']:0;
								$formvalues['os_days_count'] = isset($search_array['rent_out_tour_id'])?$search_array['rent_out_tour_id']:0;

								$result = $api->savebooking($formvalues,$company_id);
								/* create user logs */
			                    $user_unique = $search_array['passenger_id'].__('log_passenger_type');

								//NOTIFICATION LOGGER -- START
								$not_project=array();
								$not_project['profile_image']=1;
								$not_project['name']=1;
								$not_match=array();
								$not_match['_id']=(int)$search_array['passenger_id'];
								$not_result=$this->commonmodel->dynamic_findone_new(MDB_PASSENGERS,$not_match,$not_project);
								$not_name=isset($not_result['name'])?$not_result['name']:"";
								$notification_content=array();
								$notification_content['msg']=__('notification_create_booking_passenger',array(':username' => $not_name));
								$notification_content['domain']=SUBDOMAIN_NAME;
								$notification_content['image']=isset($not_result['profile_image'])?$not_result['profile_image']:"";
								$notification_content['type']='PASSENGER_CREATE_BOOKING';
								//NOTIFICATION LOGGER -- END

			                    $log_array = array(
			                        'user_id' => (int)$search_array['passenger_id'],
			                        'user_type' => __('log_passenger_type'),
			                        'login_type' => __('log_device'),
			                        'activity' => __('log_add_booking'),
									'notification_content' =>$notification_content,
									'notification_type' =>(int)1,
			                        'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
			                    );
			                    commonfunction::save_user_logs($log_array, $user_unique);
			                    /* create user logs */
								$passenger_tripid = $result;
								if(count($avail_nearest_driver)>0){
									$nearest_driver=$avail_nearest_driver[0];
								}
										
								$totalNoofDrivers = (count($avail_nearest_driver) < 5) ? count($avail_nearest_driver) : 5;
								$total_request_time = ($totalNoofDrivers * $notification_time) + 20;
								$total_request_time = (count($avail_nearest_driver) < 5) ? $total_request_time : $this->continuous_request_time+20;
								//function to check whether the passenger have wallet amount by this we can give credit card status
								$total_cancelfare = $api_ext->get_passenger_cancel_faredetail($result);
								$passenger_wallet = $api->get_passenger_wallet_amount($passenger_id);
								
								if(count($passenger_wallet) > 0 && $passenger_wallet[0]['wallet_amount'] >= $total_cancelfare) {
									$credit_card_sts = 0;
								}
								if(($result > 0) && ($formvalues['now_after'] == 0))
								{
									/***** Insert the druiver details to driver request table ************/
									if(!empty($nearest_driver)) 
									{		
										if(count($avail_nearest_driver)>0) 
										{
											$available_drivers_Arr = array();
											# check driver limit for trip request
											$limit_driver = $this->continuous_request_time / $this->notification_time;
											$limit_driver = (int)$limit_driver;
														
											foreach($avail_nearest_driver as $key=>$driveridVal){
												
												$driver_has_request = $api->check_driver_has_trip_request($driveridVal,$company_all_currenttimestamp);
															
												# actual driver limit
												$actual_limit = count($available_drivers_Arr);
												if($driver_has_request == 0){
													$available_drivers_Arr[] = $driveridVal;
												}
											}
														
											$available_drivers =  implode(",",$available_drivers_Arr);
											$nearest_driver = (count($available_drivers_Arr) > 0) ? $available_drivers_Arr[0]: '';
										}
									}
												
									$company_det =$api->get_company_id($nearest_driver);
									$company_id = !empty($company_det) ? $company_det[0]['company_id']: 0;
									
									$datas = array();
									# check driver limit for trip request
									$driver_limit = $this->continuous_request_time / $this->notification_time;
									
									$datas['trip_id'] = $tripid = $result;
									$datas['available_drivers'] = $available_drivers;
									$datas['total_drivers'] = $available_drivers;
									$datas['selected_driver'] = $nearest_driver;
									$datas['status'] = 0;
									$datas['trip_type'] = $fav_driver_booking_type;
												$datas['rejected_timeout_drivers'] = '';			
									$datas['company_id'] = $company_id;								
									$datas['driver_limit'] = (int)$driver_limit;
									$datas['actual_limit'] = 0;
									//Inserting to Transaction Table
									$transaction = $api_ext->insert_request_details($datas);
									
									$detail = array("passenger_tripid"=>$result,"notification_time"=>$notification_time,"total_request_time"=>$total_request_time,"credit_card_status"=>$credit_card_sts);
									
									$message = array("message" => __('api_request_confirmed_passenger'),"status" => 1,"detail"=>$detail);
									$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
									exit;	
								}
								else if(($result > 0) && ($formvalues['now_after'] == 1))
								{									
									/** Later Booking E-mail & SMS Send Start **/
									$passenger_tripid = $result;
									$datas = $this->commonmodel->getPassengerDetails($result);
									$passenger_logid = $datas[0]["passengers_log_id"];
									$pickup_location = $datas[0]["current_location"];
									$drop_location = $datas[0]["drop_location"];
									$drop_location = ($drop_location != '') ? $drop_location:'--';
									$pickup_time = $datas[0]["pickup_time"];
									$name = $datas[0]["name"];
									$email = $datas[0]["email"];
									$phone = $datas[0]["country_code"].$datas[0]["phone"];
									$message = "";
									//~ $message .= "Thanks for booking with us, your booking was confirmed. your booking id ".$passenger_logid.". We will contact shortly.<br>";
									//~ $message .= "Pickup Date : ".$pickup_time."<br>";
									//~ $message .= "Pickup Location : ".$pickup_location."<br>";
									//~ $message .= "Drop Location : ".$drop_location;
									$replace_variables=array(
										REPLACE_LOGO => EMAILTEMPLATELOGO,
										REPLACE_SITENAME => $this->app_name,
										REPLACE_USERNAME => $name,
										REPLACE_BOOKINGID => $passenger_logid,
										REPLACE_PICKUPDATE => $pickup_time,
										REPLACE_PICKUPLOC => $pickup_location,
										REPLACE_DROPLOC => $drop_location,
										//REPLACE_MESSAGE => $message,
										REPLACE_SITEURL => URL_BASE,
										REPLACE_COPYRIGHTS => SITE_COPYRIGHT,
										REPLACE_COPYRIGHTYEAR => COPYRIGHT_YEAR
									);
									
									//~ $message = $this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.'laterbooking_cofirm_message.html',$replace_variables);		
									$emailTemp = $this->commonmodel->get_email_template('later_booking',$this->email_lang);
									if(isset($emailTemp['status']) && ($emailTemp['status'] == '1')){

										$email_description = isset($emailTemp['description']) ? $emailTemp['description']: '';
										$subject = isset($emailTemp['subject']) ? $emailTemp['subject']: '';
										$message           = $this->emailtemplate->emailtemplate($email_description, $replace_variables);
										$from              = CONTACT_EMAIL;									
										$to = $email;
										//~ $subject = __('later_booking_confirm_mail')." - ".$this->app_name;
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

									if(SMS == 1)
									{
										$message_details = $this->commonmodel->sms_message_by_title('booking_confirmed_sms');
										if(count($message_details) > 0) 
										{
											$to = $phone;
											$message = (count($message_details)) ? $message_details[0]['sms_description'] : '';
											$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
											$message = str_replace("##booking_key##",$passenger_logid,$message);
											$this->commonmodel->send_sms($to,$message);
										}
									}

									/** Later Booking E-mail & SMS Send End **/
									/***** Insert the druiver details to driver request table ************/
									$detail = array("passenger_tripid"=>$passenger_tripid,"notification_time"=>$notification_time,"total_request_time"=>$total_request_time,"credit_card_status"=>$credit_card_sts);
									$message = array("message" => __('api_request_disapatcher'),"status" => 1,"detail"=>$detail);
									$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
									//unset($message,$detail);
									exit;
								}
								else
								{
									$message = array("message" => __('try_again'),"status"=>2);	
								}
							}
							else
							{
								if($formvalues['now_after'] == 1) 
							  	{	
									$formvalues['taxi_id'] = 0;
									$formvalues['pickup_latitude']=$search_array['latitude'];
									$formvalues['pickup_longitude']=$search_array['longitude'];
									$formvalues['approx_distance']=isset($search_array['approx_distance']) ? $search_array['approx_distance'] : '';
									$formvalues['approx_duration']=isset($search_array['approx_duration']) ? $search_array['approx_duration'] : '';
									$formvalues['driver_id'] = 0;
									$formvalues['notes'] =$notes;
									$formvalues['approx_fare'] =$approx_trip_fare;
									$formvalues['pre_transaction_id']=$paymentResult;
									$formvalues['passenger_app_version'] = $passenger_app_version;
									$formvalues['pre_transaction_amount']=$pre_authorize_amount;
									$formvalues['currentTime'] = $currentTime;
									$formvalues['pickupTimezone'] = $pickupTimezone;
									$formvalues['rental_outstation'] = isset($search_array['rental_outstation'])?$search_array['rental_outstation']:'';
									$formvalues['rent_out_tour_id'] = isset($search_array['rent_out_tour_id'])?$search_array['rent_out_tour_id']:0;

									$result= $api->savebooking($formvalues,$company_id);
									$passenger_tripid = $result;
									/** Later Booking E-mail & SMS Send Start **/
									$passenger_tripid = $result;
									$datas = $this->commonmodel->getPassengerDetails($result);
									$passenger_logid = $datas[0]["passengers_log_id"];
									$pickup_location = $datas[0]["current_location"];
									$drop_location = $datas[0]["drop_location"];
									$drop_location = ($drop_location != '') ? $drop_location:'--';
									$pickup_time = $datas[0]["pickup_time"];
									$name = $datas[0]["name"];
									$email = $datas[0]["email"];
									$phone = $datas[0]["country_code"].$datas[0]["phone"];
									$message = "";
									$replace_variables=array(
										REPLACE_LOGO => EMAILTEMPLATELOGO,
										REPLACE_SITENAME => $this->app_name,
										REPLACE_USERNAME => $name,
										REPLACE_BOOKINGID => $passenger_logid,
										REPLACE_PICKUPDATE => $pickup_time,
										REPLACE_PICKUPLOC => $pickup_location,
										REPLACE_DROPLOC => $drop_location,
										//REPLACE_MESSAGE => $message,
										REPLACE_SITEURL => URL_BASE,
										REPLACE_COPYRIGHTS => SITE_COPYRIGHT,
										REPLACE_COPYRIGHTYEAR => COPYRIGHT_YEAR
									);
									
									//~ $message = $this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.'laterbooking_cofirm_message.html',$replace_variables);
									
									$emailTemp = $this->commonmodel->get_email_template('later_booking',$this->email_lang);
									if(isset($emailTemp['status']) && ($emailTemp['status'] == '1')){
										$email_description = isset($emailTemp['description']) ? $emailTemp['description']: '';
										$subject = isset($emailTemp['subject']) ? $emailTemp['subject']: '';
										$message           = $this->emailtemplate->emailtemplate($email_description, $replace_variables);
										$from              = CONTACT_EMAIL;									
										$to = $email;
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

									if(SMS == 1)
									{
										$message_details = $this->commonmodel->sms_message_by_title('booking_confirmed_sms');
										if(count($message_details) > 0) 
										{
											$to = $phone;
											$message = (count($message_details)) ? $message_details[0]['sms_description'] : '';
											$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
											$message = str_replace("##booking_key##",$passenger_logid,$message);
											$this->commonmodel->send_sms($to,$message);
										}
									}
										
									$detail = array("passenger_tripid"=>$passenger_tripid,"notification_time"=>$notification_time,"total_request_time"=>$notification_time,"credit_card_status"=>$credit_card_sts);
									$message = array("message" => __('api_request_disapatcher'),"status" => 1,"detail"=>$detail);
									$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
									//unset($message,$detail,$formvalues);
									exit;
								} 
								else 
								{
								   if($fav_driver_booking_type == 1) {
									   $message = array("message" => __('fav_driver_not_available'),"status" => 4);
								   } 
								   else 
								   {
										if($paymentResult!='')
										{
										    if (class_exists('Paymentgateway')) 
										    {
												$void_amount=['preTransactAmount'=>$pre_authorize_amount];
												$paymentresponse = Paymentgateway::payment_gateway_connect('void',$paymentResult,$void_amount);
													$payment_status=$paymentresponse['payment_status'];
											} 
											else 
											{
												trigger_error("Unable to load class: Paymentgateway", E_USER_WARNING);
											}
										}
										$message = array("message" => $no_vehicle_msg,"status" => 3);
									}
									$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
									//unset($message);
									exit;
								}							  
							}
						} 
						else 
						{
							$message=array("message"=>$paymentResult,"status"=>3);
                            $mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							exit;
						}
					}
					else
					{
						$message = array("message" => __('lat_not_zero'),"status"=>-4);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						exit;	
					}
				}
				else
				{
					$errors = $validator->errors('errors');	
					$message = array("message" => __('validation_error'),"detail"=>$errors,"status"=>-5);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					exit;					
				}
		break;

		case 'passenger_referral_code':
			$referral_code = (isset($mobiledata['referral_code'])) ? urldecode($mobiledata['referral_code']) : '';
			$email = (isset($mobiledata['email'])) ? urldecode($mobiledata['email']) : '';
			if(!empty($referral_code)) 
			{
				$referralcode_exist = $api->check_referral_code_exist($mobiledata);					
				# referral code validation
				if(array_key_exists($referralcode_exist, $this->promo_msg))
				{
					$promotion_msg = $this->promo_msg[$referralcode_exist];
					$message = array("message" => $promotion_msg ,"status"=> -1);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						exit;
				}	
				
				$code_prefix = substr($referral_code,0,2);
				
				if($code_prefix == 'RP')
				{
					$passenger_details = $api->passenger_detailsbyemail($email,$default_companyid);
					if(count($passenger_details) > 0) 
					{
						$referral_used = $api->check_referral_code_used($passenger_details[0]['id']);
						if($referral_used == 0) 
						{
							$save_referral = $api->save_referral_code($passenger_details[0]['id'],$referral_code,$default_companyid,$passenger_details[0]['device_id'],$passenger_details[0]['device_token']);
							if($save_referral == 1) {
								$message = array("message" => __('referral_code_save_successful'),"status"=> 1);
							} 
							else 
							{
								$message = array("message" => __('try_again'),"status"=>-1);	
							}
						} 
						else 
						{
							$message = array("message" => __('referral_code_already_used'),"status"=> 4);
						}
					} else {
						$message = array("message" => __('invalid_user'),"status"=>-1);
					}
				}else{
					$save_referral = $api->save_newpromo($email,$referral_code);
					$message = array("message" => __('promocode_saved_success'),"status"=> 1);
				}
				
			} else {
				$message = array("message" => __('referral_code_not_empty'),"status"=> -1);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$save_referral,$referralcode_exist,$passenger_details,$referral_used);
		break;		

		case 'passenger_card_details':
			$p_card_array= $mobiledata;			
			$savecard = $p_card_array['savecard'];
			$email = $p_card_array['email'];
			$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
			$config_array = $api_ext->select_site_settings($default_companyid);
			if($savecard == 1)
			{
				$card_validation = $this->passenger_card_validation($p_card_array);			
				if($card_validation->check())
				{
					$creditcard_no = $p_card_array['creditcard_no'];
					$card_holder_name = (isset($p_card_array['card_holder_name'])) ? urldecode($p_card_array['card_holder_name']) : '';
					$creditcard_cvv = (isset($p_card_array['creditcard_cvv'])) ? urldecode($p_card_array['creditcard_cvv']) : '';
					$expdatemonth = (isset($p_card_array['expdatemonth'])) ? urldecode($p_card_array['expdatemonth']) : '';
					$expdateyear = (isset($p_card_array['expdateyear'])) ? urldecode($p_card_array['expdateyear']) : '';
					$authorize_status =$api_ext->isVAlidCreditCard($creditcard_no,"",true);
					if($authorize_status == 0)
					{
						$message = array("message" => __('invalid_card'),"status"=> 2);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						exit;
					}
					
					$passenger_details = $api->passenger_detailsbyemail($email,$default_companyid);
					$passenger_id = (count($passenger_details) > 0) ? $passenger_details[0]['id'] : 0;
					//Credit Card Pre authorization section goes here
					//preauthorization with amount "0"(Zero)
					$preAuthorizeAmount = PRE_AUTHORIZATION_REG_AMOUNT;
					//list($returncode,$paymentResult,$fcardtype,$preAuthorizeAmount) = $api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
					$paymentresponse=$api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
					$returncode=$paymentresponse['code'];
					$paymentResult=(isset($paymentresponse['TRANSACTIONID']) && ($paymentresponse['TRANSACTIONID']!=''))?$paymentresponse['TRANSACTIONID']:$paymentresponse['payment_response'];
					$fcardtype=isset($paymentresponse['cardType'])?$paymentresponse['cardType']:'';
					if($returncode==0)
					{
						//preauthorization with amount "1"
						$preAuthorizeAmount = PRE_AUTHORIZATION_RETRY_REG_AMOUNT;					
						//list($returncode,$paymentResult,$fcardtype,$preAuthorizeAmount)= $api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
						$paymentresponse= $api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
						$returncode=$paymentresponse['code'];
						$paymentResult=(isset($paymentresponse['TRANSACTIONID']) && ($paymentresponse['TRANSACTIONID']!=''))?$paymentresponse['TRANSACTIONID']:$paymentresponse['payment_response'];
						$fcardtype=isset($paymentresponse['cardType'])?$paymentresponse['cardType']:'';
					}
					if($returncode != 0)
					{
						$result = $api->save_passenger_carddata($p_card_array,$default_companyid,$paymentResult,$preAuthorizeAmount,$fcardtype);
						if($result) 
						{
                            $paymentresponse['preTransactAmount']=$preAuthorizeAmount;
							$void_transaction=$api->voidTransactionAfterPreAuthorize($result,$paymentresponse);
						}
					}
					else
					{
						$error_msg = isset($paymentresponse['payment_response']) ? $paymentresponse['payment_response']: __('insufficient_fund');
						$message=array("message"=>$error_msg,"status"=>3);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						//unset($message);
						exit;
					}
							
					if($result > 0)
					{
						$total_array = array();
						if(count($passenger_details) > 0)
						{
							if((!empty($passenger_details[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$passenger_details[0]['profile_image'])){ 
								$profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$passenger_details[0]['profile_image']; 
							}
							else{ 
								$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
							} 										
							$passenger_id= $passenger_details[0]['id'];
							$total_array['id'] = $passenger_details[0]['id'];
							$total_array['salutation'] = $passenger_details[0]['salutation'];
							$total_array['name'] = $passenger_details[0]['name'];
							$total_array['lastname'] = $passenger_details[0]['lastname'];
							$total_array['email'] = $passenger_details[0]['email'];
							$total_array['profile_image'] = $profile_image;
							$total_array['country_code'] = $passenger_details[0]['country_code'];
							$total_array['phone'] = $passenger_details[0]['phone'];
							$total_array['address'] = $passenger_details[0]['address'];
							$total_array['split_fare'] = '1';
							$referral_code = $passenger_details[0]['referral_code'];
							$total_array['referral_code'] = $referral_code;
							$total_array['referral_code_amount'] = $passenger_details[0]['referral_code_amount'];
							$ref_message = TELL_TO_FRIEND_MESSAGE.''.$referral_code;
							$ref_discount = REFERRAL_DISCOUNT;
							$telltofriend_message = TELL_TO_FRIEND_MESSAGE;
							//Newly Added-13.11.2014
							$total_array['site_currency'] = CURRENCY;
							$total_array['aboutpage_description'] = $this->app_description;
							$total_array['tell_to_friend_subject'] = __('telltofrien_subject');
							$total_array['skip_credit'] = SKIP_CREDIT_CARD;
							$total_array['metric'] = UNIT_NAME;
							//variable to know whether the passenger have credit card details
							$total_array['credit_card_status'] = 1;
							/***Get Company car model details start***/
							//$company_model_details = $api->company_model_details($default_companyid);
							$company_model_details = $api_ext->company_model_details($default_companyid);
							if(count($company_model_details)>0){
								$total_array['model_details']=$company_model_details;
							}else{
								$total_array['model_details']="model details not found";
							}
							/***Get Company car model details end***/										
							$total_array['telltofriend_message'] = $telltofriend_message;	
						}			
						if(isset($passenger_details[0]['new_password']) && $passenger_details[0]['new_password'] != '')	
							$p_password = $passenger_details[0]['new_password'];
						else
							$p_password = isset($passenger_details[0]['org_password'])?$passenger_details[0]['org_password']:'';
							
						//free sms url with the arguments
						if(SMS == 1)
						{
							$message_details = $this->commonmodel->sms_message_by_title('account_create_sms');
							if(count($message_details) > 0) {
								$to = isset($total_array['phone'])? $total_array['country_code'].$total_array['phone']:'';
								$message = $message_details[0]['sms_description'];
								$message = str_replace("##USERNAME##",$to,$message);
								$message = str_replace("##PASSWORD##",$p_password,$message);
								$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
								$this->commonmodel->send_sms($to,$message);
							}							
						}
							
						$mobile_no = isset( $passenger_details[0]['phone'])? $passenger_details[0]['country_code'].$passenger_details[0]['phone']:'';
						$username = isset( $passenger_details[0]['name'])? $passenger_details[0]['name']:'';
						$replace_variables=array(REPLACE_LOGO=>EMAILTEMPLATELOGO,REPLACE_SITENAME=>$this->app_name,REPLACE_USERNAME=>$username,REPLACE_SITELINK=>URL_BASE.'users/contactinfo/',REPLACE_MOBILE=>$mobile_no,REPLACE_PASSWORD=>$p_password,REPLACE_SITEEMAIL=>$this->siteemail,REPLACE_SITEURL=>URL_BASE,REPLACE_COMPANYDOMAIN=>$this->domain_name,REPLACE_COPYRIGHTS=>SITE_COPYRIGHT,REPLACE_COPYRIGHTYEAR=>COPYRIGHT_YEAR);
										
						/* Added for language email template */
						$emailTemp = $this->commonmodel->get_email_template('register_passenger', $this->email_lang);
						if(isset($emailTemp['status']) && ($emailTemp['status'] == '1'))
						{							
							$email_description = isset($emailTemp['description']) ? $emailTemp['description']: '';
							$subject = isset($emailTemp['subject']) ? $emailTemp['subject']: '';
							$message           = $this->emailtemplate->emailtemplate($email_description, $replace_variables);
							$from              = CONTACT_EMAIL;							
							$to = $email;
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
						/*** Update Pssenger password as empty ************/
						$update_passenger_array  = array("login_status" => "S","split_fare" => "1","org_password" => "","new_password" => ""); // 
						$result = $api_ext->update_passengers($update_passenger_array,$passenger_id);
						/***************************************************/
						$message = array("message" => __('signup_success'),"detail"=>$total_array,"status"=>1);		
					}
					elseif($result == -1)
					{
						$message = array("message" => __('you_have_detail'),"status"=>3);
					}
					else
					{
						$message = array("message" => __('try_again'),"status"=>1);	
					}				
				}
				else
				{							
					$validation_error = $card_validation->errors('errors');	
					$message = array("message" => __('validation_error'),"detail"=>$validation_error,"status"=>-3);	
				}
			}
			else
			{
				$update_cred_sts  = array("skip_credit_card" => '1');
				$update_current_result = $api_ext->update_passengers_email($update_cred_sts,$email);
				$passenger_details = $api->passenger_detailsbyemail($email,$default_companyid);
				$total_array = array();
				if(count($passenger_details) > 0)
				{
					if((!empty($passenger_details[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$passenger_details[0]['profile_image']))
					{ 
						$profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$passenger_details[0]['profile_image']; 
					}
					else
					{ 
						$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
					} 								
					$passenger_id = $passenger_details[0]['id'];
					$total_array['id'] = $passenger_details[0]['id'];
					$total_array['salutation'] = $passenger_details[0]['salutation'];
					$total_array['name'] = $passenger_details[0]['name'];
					$total_array['lastname'] = $passenger_details[0]['lastname'];
					$total_array['email'] = $passenger_details[0]['email'];
					$total_array['profile_image'] = $profile_image;
					$total_array['phone'] = $passenger_details[0]['phone'];
					$total_array['country_code'] = $passenger_details[0]['country_code'];
					$total_array['address'] = $passenger_details[0]['address'];
					$total_array['split_fare'] = '0';
					$referral_code = $passenger_details[0]['referral_code'];
					$total_array['referral_code'] = $referral_code;
					$total_array['referral_code_amount'] = $passenger_details[0]['referral_code_amount'];
					$ref_message = TELL_TO_FRIEND_MESSAGE.''.$referral_code;
					$ref_discount = REFERRAL_DISCOUNT;
					$telltofriend_message = TELL_TO_FRIEND_MESSAGE;//str_replace("#REFDIS#",$ref_discount,$ref_message); 
					//Newly Added-13.11.2014
					$total_array['site_currency'] = CURRENCY;
					$total_array['facebook_share'] = $config_array[0]['facebook_share'];
					$total_array['twitter_share'] = $config_array[0]['twitter_share'];
					$total_array['aboutpage_description'] = $this->app_description;
					$total_array['tell_to_friend_subject'] = __('telltofrien_subject');
					$total_array['skip_credit'] = SKIP_CREDIT_CARD;
					$total_array['metric'] = UNIT_NAME;
					$total_array['credit_card_status'] = 0;
					/***Get Company car model details start***/
					//$company_model_details = $api->company_model_details($default_companyid);
					$company_model_details = $api_ext->company_model_details($default_companyid);
					if(count($company_model_details)>0){
						$total_array['model_details']=$company_model_details;
					}else{
						$total_array['model_details']="model details not found";
					}
					/***Get Company car model details end***/										
					$total_array['telltofriend_message'] = $telltofriend_message;	
				}						
				//~ $p_password = isset($passenger_details[0]['org_password'])?$passenger_details[0]['org_password']:'';
				
				if(isset($passenger_details[0]['new_password']) && $passenger_details[0]['new_password'] != '')	
					$p_password = $passenger_details[0]['new_password'];
				else
					$p_password = isset($passenger_details[0]['org_password'])?$passenger_details[0]['org_password']:'';
							
				if(SMS == 1)
				{
					$message_details = $this->commonmodel->sms_message_by_title('account_create_sms');
					if(count($message_details) > 0) 
					{
						$to = isset($total_array['phone'])? $total_array['country_code'].$total_array['phone']:'';
						$message = $message_details[0]['sms_description'];
						$message = str_replace("##USERNAME##",$to,$message);
						$message = str_replace("##PASSWORD##",$p_password,$message);
						$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
						$this->commonmodel->send_sms($to,$message);
					}								
				}
				
				$mobile_no = isset( $passenger_details[0]['phone'])? $passenger_details[0]['country_code'].$passenger_details[0]['phone']:'';
				$username = isset( $passenger_details[0]['name'])? $passenger_details[0]['name']:'';
				$replace_variables=array(REPLACE_LOGO=>EMAILTEMPLATELOGO,REPLACE_SITENAME=>$this->app_name,REPLACE_USERNAME=>$username,REPLACE_SITELINK=>URL_BASE.'users/contactinfo/',REPLACE_MOBILE=>$mobile_no,REPLACE_PASSWORD=>$p_password,REPLACE_SITEEMAIL=>$this->siteemail,REPLACE_SITEURL=>URL_BASE,REPLACE_COMPANYDOMAIN=>$this->domain_name,REPLACE_COPYRIGHTS=>SITE_COPYRIGHT,REPLACE_COPYRIGHTYEAR=>COPYRIGHT_YEAR);
					/* Added for language email template */
					$emailTemp = $this->commonmodel->get_email_template('register_passenger', $this->email_lang);
					if(isset($emailTemp['status']) && ($emailTemp['status'] == '1'))
					{							
						$email_description = isset($emailTemp['description']) ? $emailTemp['description']: '';
						$subject = isset($emailTemp['subject']) ? $emailTemp['subject']: '';
						$message           = $this->emailtemplate->emailtemplate($email_description, $replace_variables);
						$from              = CONTACT_EMAIL;							
						$to = $email;
						$to = $email;
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
							
					/*** Update Pssenger password as empty ************/
					$update_passenger_array  = array("login_status" => "S","org_password" => ""); // 
					$result = $api_ext->update_passengers($update_passenger_array,$passenger_id);
					/***************************************************/							
				$message = array("message" => __('signup_success'),"detail"=>$total_array,"status"=>1);	
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
		break;	

		case 'getdriver_reply':
			$array = $mobiledata;
			$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
			if($array['passenger_tripid'] != null)
			{
				$passenger_tripid = $array["passenger_tripid"];
				$get_passenger_log_det = $api_ext->get_trip_detail_only($passenger_tripid);
				if(count($get_passenger_log_det) > 0)	    		
				{
					$driver_reply = $get_passenger_log_det[0]->driver_reply;
					if($driver_reply == 'A')
					{
						$detail = array("trip_id"=>$passenger_tripid,"driverdetails"=>"");
						$message = array("message" => __("request_confirmed_passenger"),"detail"=>$detail,"status"=>1);
					}
					else
					{
						$change_driver_status = $api->change_driver_status($passenger_tripid,'C');
						$send_drivernotification = $this->commonmodel->send_drivernotification($passenger_tripid);
						$update_trip_array  = array("status"=>'4',"trip_id" => $passenger_tripid);
						$result = $api_ext->update_driver_request_details($update_trip_array);
						// version 6.2.3 update
						$void_transaction_trip=$api->voidTransaction_for_trip($passenger_tripid);
                                                        
						$message = array("message" => __("request_canceled_passenger"),"status"=>3);
					}
				}
				else
				{
					$message = array("message" => __('invalid_trip'),"status"=>-1);	
				}
			}
			else
			{
				$message = array("message" => __('try_again'),"status"=>0);	
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
		break;

		case 'getdriver_update':
			ignore_user_abort(true);
			$array = $mobiledata;
			$extended_api = Model::factory(MOBILEAPI_107_EXTENDED);
			$message = array();
			$trip_id = $array["passenger_tripid"];						
			$notification_time = $this->notification_time;							
			if($notification_time != 0 ){ $timeoutseconds = $notification_time;}else{$timeoutseconds = 15;}
			$timeout = $this->continuous_request_time;//$timeoutseconds; // timeout in seconds
			$microseconds = $timeout*1000000; //Seconds to microseconds 1 second = 1000000 
			$flag = 0;
			$now = time();
			$search_flag=0;						
			if((int)$trip_id != "") 
			{											
				$i = $actual_limit = 0;		
				
				while((time() - $now) < $timeout)
				{	
					$driver_status = $api->get_request_status($trip_id);	
					$driver_status_count=count($driver_status);									
					if($driver_status_count >0)
					{										
						$req_count=$driver_status_count*$timeoutseconds;
						$driver_reply = $driver_status[0]['status'];
						$trip_type = $driver_status[0]['trip_type'];//get booking type 1-Favourite booking, 0-Normal Booking
						$selected_driver_id = $driver_status[0]['selected_driver'];
						$available_drivers = explode(',',$driver_status[0]['total_drivers']);
						$rejected_timeout_drivers = explode(',',$driver_status[0]['rejected_timeout_drivers']);	
						$comp_result = array_diff($available_drivers, $rejected_timeout_drivers);
										
						$timeperdriver = 25;
						
						$timeout=count($available_drivers)*$timeperdriver+20;
						if($timeout < $this->continuous_request_time)
						{
							$timeout=$this->continuous_request_time;
						}
						$microseconds=$timeout*1000000;
						//to get drivers company timestamp
						$company_det =$api->get_company_id($selected_driver_id);
						if(count($company_det)>0){
							$company_all_currenttimestamp = $this->commonmodel->getcompany_all_currenttimestamp($company_det[0]['company_id']);											
						}
						//condition to check driver not updated for above 30seconds if it is means we should change the request to next driver
						$driver_not_updated = $api->check_driver_not_updated($selected_driver_id,$company_all_currenttimestamp);
						
						$time_difference = strtotime($company_all_currenttimestamp) - strtotime($driver_not_updated);
						if($time_difference > $timeperdriver && count($comp_result) != 0 && $driver_reply != '4') 
						{
							$get_request_dets=$api->check_new_request_tripid("","",$trip_id,$selected_driver_id,$company_all_currenttimestamp,"");
											$actual_limit++;
						}
						if(count($comp_result) == 0)
						{
							$driver_reply  = 5;
						}

						if(!empty($driver_reply))
						{							
							if($driver_reply == '3') 
							{
								$message = array("message" => __("request_confirmed_passenger"),"trip_id"=>$trip_id,"status"=>1);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								exit;
							}
							elseif($driver_reply == '4')
							{
								if($trip_type == 1) {
									$message = array("message" => __("fav_driver_not_available"),"status"=>4);
								} else {
									$message = array("message" => __("driver_busy"),"status"=>2);
								}
								// version 6.2.3 update                                            
								$void_transaction_trip=$api->voidTransaction_for_trip($trip_id);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								exit;
							}
							elseif($driver_reply == '5')
							{
								if($trip_type == 1) {
									$message = array("message" => __("fav_driver_not_available"),"status"=>4);
								} else {
									$message = array("message" => __("driver_busy"),"status"=>2);
								}
								// version 6.2.3 update                                                 
								$void_transaction_trip=$api->voidTransaction_for_trip($trip_id);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								exit;
							}
							else 
							{				
								$message = array("message" => __('try_again'),"status"=>0);
							}
						}
						usleep(5000000);												
						$i = $i+5000000;										
						if($i == $microseconds)
						{
							$update_trip_array  = array(
								'status' => 4,
								'trip_id'=>$trip_id
							);
							$result = $extended_api->update_driver_request_details($update_trip_array);
							if($trip_type == 1) {
								$message = array("message" => __("fav_driver_not_available"),"status"=>4);
							} else {
								$message = array("message" => __("driver_busy"),"status"=>2);
							}
							// version 6.2.3 update
							$void_transaction_trip=$api->voidTransaction_for_trip($trip_id);
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							exit;
						}							
					}
					else
					{
						$message = array("message" => __('try_again'),"status"=>0);	
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						exit;
					}											
				}																								
			}
			else
			{
				$message = array("message" => __('validation_error'),"status"=>0);	
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);				
		break;

		case 'add_favourite':
			$add_fav_array = $mobiledata;
			$validator = $this->favourite_validation($add_fav_array);				
			if($validator->check())
			{
				$passenger_id= $add_fav_array['passenger_id'];					
				$fav_comments = urldecode($add_fav_array['fav_comments']);
				$p_favourite_place = urldecode($add_fav_array['p_favourite_place']);
				$p_fav_latitude = $add_fav_array['p_fav_latitude'];
				$p_fav_longtitute = $add_fav_array['p_fav_longtitute'];
				$d_favourite_place = (isset($add_fav_array['d_favourite_place'])) ? urldecode($add_fav_array['d_favourite_place']) : '';
				$d_fav_latitude = (isset($add_fav_array['d_fav_latitude'])) ? $add_fav_array['d_fav_latitude'] : '';
				$d_fav_longtitute = (isset($add_fav_array['d_fav_longtitute'])) ? $add_fav_array['d_fav_longtitute'] : '';
				$p_fav_locationtype = urldecode($add_fav_array['p_fav_locationtype']);
				$notes = isset($add_fav_array['notes']) ? urldecode($add_fav_array['notes']) :"";
				
				$check_fav_place = $api->check_fav_place($passenger_id,$p_favourite_place,$d_favourite_place,$p_fav_locationtype);
				
				if($check_fav_place==0)
				{
					//Set the Favourite Trips
					$image_name = uniqid().'.png';
					$status = $api->save_favourite($passenger_id,$p_favourite_place,$p_fav_latitude,$p_fav_longtitute,$d_favourite_place,$d_fav_latitude,$d_fav_longtitute,$fav_comments,$notes,$p_fav_locationtype,$image_name);
					if($status)					
					{
						// Create directory if it does not exist							
						if(!is_dir(DOCROOT.MOBILE_FAV_LOC_MAP_IMG_PATH."passenger_". $passenger_id ."/")) { 
							mkdir(DOCROOT.MOBILE_FAV_LOC_MAP_IMG_PATH."passenger_". $passenger_id ."/",0777);
						}
						//Map image creation
						include_once MODPATH."/email/vendor/polyline_encoder/encoder.php";
							$polylineEncoder = new PolylineEncoder();
							$polylineEncoder->addPoint($p_fav_latitude,$p_fav_longtitute);
							
						$marker_end = 0;
						if($d_fav_latitude != 0 && $d_fav_longtitute != 0){
							$polylineEncoder->addPoint($d_fav_latitude,$d_fav_longtitute);
							$marker_end = $d_fav_latitude.','.$d_fav_longtitute;
						}
						$encodedString = $polylineEncoder->encodedString();
						$marker_start = $p_fav_latitude.','.$p_fav_longtitute;
						$startMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'startMarker.png';
						$endMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'endMarker.png';
							
						if($marker_end != 0) {
							$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x270&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&markers=icon:$endMarker%7C$marker_end&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
						} else {
							$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x270&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
						}
														
						if(isset($mapurl) && $mapurl != "") {
							$file_path = DOCROOT.MOBILE_FAV_LOC_MAP_IMG_PATH."passenger_". $passenger_id ."/".$image_name;
							@file_put_contents($file_path,@file_get_contents($mapurl));
						}							
						$message = array("message" => __('mark_fav'),"detail"=>"","status"=>1);
					}
					else
					{
						$p_favourite_id = $check_fav_place['0']['p_favourite_id'];
						$message = array("message" => __('try_again'),"status"=>0);	
					}	
				}
				else if($check_fav_place==-1)
				{
					$message = array("message" => __('fav_already_exist_type'),"status"=>3);
				}
				else
				{
					$message = array("message" => __('fav_already_exist'),"status"=>2);
				}					
			}
			else
			{
				$validation_error = $validator->errors('errors');	
				$message = array("message" => __('validation_error'),"status"=>-3);								
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$validator,$check_fav_place,$status,$polylineEncoder,$encodedString,$marker_start,$startMarker,$endMarker,$mapurl,$file_path);
		break;

		case 'delete_favourite':
			$p_fav_array = $mobiledata;
			if($p_fav_array['p_favourite_id'] != null && $p_fav_array['passenger_id'] != null)
			{
				$favourite_details = $api->delete_favourite($p_fav_array['p_favourite_id'],$p_fav_array['passenger_id']);
				
				if($favourite_details) 
				{
				if(file_exists(DOCROOT.MOBILE_FAV_LOC_MAP_IMG_PATH."passenger_". $p_fav_array['passenger_id'] ."/fav_".$p_fav_array['p_favourite_id'].".png")) {
					unlink(DOCROOT.MOBILE_FAV_LOC_MAP_IMG_PATH."passenger_". $p_fav_array['passenger_id'] ."/fav_".$p_fav_array['p_favourite_id'].".png");
					}
					$message = array("message" => __('favourite_deleted'),"status"=>1);	
				} else {
					$message = array("message" => __('no_favourite'),"status"=>-1);
				}
			}
			else
			{
				$message = array("message" => __('no_favourite'),"status"=>-1);								
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$favourite_details,$p_fav_array);
		break;

		case 'getmodel_fare_details':
			//$company_model_details = $api->company_model_details($default_companyid);
			$company_model_details = $api_ext->company_model_details($default_companyid);
			if(count($company_model_details) > 0) 
			{
				foreach($company_model_details as $k => $t) 
				{
					if(file_exists($_SERVER["DOCUMENT_ROOT"].'/'.PUBLIC_UPLOADS_FOLDER.'/model_image/android/'.$t["model_id"].'_focus.png')) 
					{
						$focus_image = URL_BASE.PUBLIC_UPLOADS_FOLDER.'/model_image/android/'.$t['model_id'].'_focus.png';
						$company_model_details[$k]['focus_image'] = $focus_image;
					}
					if(file_exists($_SERVER["DOCUMENT_ROOT"].'/'.PUBLIC_UPLOADS_FOLDER.'/model_image/android/'.$t["model_id"].'_unfocus.png')) 
					{
						$unfocus_image = URL_BASE.PUBLIC_UPLOADS_FOLDER.'/model_image/android/'.$t['model_id'].'_unfocus.png';
							$company_model_details[$k]['unfocus_image'] = $unfocus_image;
					}
					if(file_exists($_SERVER["DOCUMENT_ROOT"].'/'.PUBLIC_UPLOADS_FOLDER.'/model_image/ios/'.$t["model_id"].'_focus.png')) 
					{
						$focus_image_ios = URL_BASE.PUBLIC_UPLOADS_FOLDER.'/model_image/ios/'.$t['model_id'].'_focus.png';
						$company_model_details[$k]['focus_image_ios'] = $focus_image_ios;
					}
					if(file_exists($_SERVER["DOCUMENT_ROOT"].'/'.PUBLIC_UPLOADS_FOLDER.'/model_image/ios/'.$t["model_id"].'_unfocus.png')) 
					{
						$unfocus_image_ios = URL_BASE.PUBLIC_UPLOADS_FOLDER.'/model_image/ios/'.$t['model_id'].'_unfocus.png';
						$company_model_details[$k]['unfocus_image_ios'] = $unfocus_image_ios;
					}
				}
				$details = array("model_details"=>$company_model_details);
				$message = array("message" =>__('success'),"detail" => $details,"status" => 1);
			} else {
				$message = array("message" => __('model_detail_not_found'),"status" => 2);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$company_model_details);
		break;

		case 'unfavourite_driver':
			$passenger_id = isset($mobiledata['passenger_id']) ? $mobiledata['passenger_id'] : '';
			$driver_id = isset($mobiledata['driver_id']) ? $mobiledata['driver_id'] : '';
			if(!empty($passenger_id) && !empty($driver_id)) 
			{
				$result = $api->unfavourite_driver($passenger_id, $driver_id);
				
				if($result == 1) {
					$message = array("message" => __('unfavourite_success'),"status" => 1);
				} else if($result == -1) {
					$message = array("message" => __('no_data'),"status" => -1);
				} else {
					$message = array("message" => __('invalid_user_driver'),"status" => -2);
				}
			} else {
				$message = array("message" => __('invalid_request'),"status" => -1);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$image,$result);
		break;

		case 'favourite_driver_list':
			$passenger_id = isset($mobiledata['passenger_id']) ? $mobiledata['passenger_id'] : '';
			if(!empty($passenger_id)) 
			{
				$result = $api->favourite_driver_list($passenger_id);
				
				if(count($result) > 0) 
				{
					$favourite_driver = array();
					foreach($result as $f) 
					{
						$image = URL_BASE.PUBLIC_IMAGES_FOLDER."noimages.jpg";
						if(file_exists($_SERVER["DOCUMENT_ROOT"].'/'.PUBLIC_UPLOADS_FOLDER.'/driver_image/'.$f['profile_picture']) && ($f['profile_picture'] != "")) 
						{
							$image = URL_BASE.SITE_DRIVER_IMGPATH.$f['profile_picture'];
						}
						$favourite_driver[] = array (
							"driver_id" => $f["id"],
							"name" => $f["name"],
							"email" => $f["email"],
							"phone" => $f["phone"],
							"profile_image" => $image,
							"taxi_no" => $f["taxi_no"]
						);
					}
					$message = array("message" => __('favourite_driver_list'),"details" => $favourite_driver, "status" => 1);
				}
				else
				{
					$message = array("message" => __('no_data'),"status" => -1);
				}
			} else {
				$message = array("message" => __('invalid_request'),"status" => -1);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$image,$result,$favourite_driver);
		break;

		case 'get_favourite_list':		
			if(count($mobiledata) > 0)
			{
				$passenger_id = $mobiledata['passenger_id'];
				$favourite_list = $api->get_favourite_list($passenger_id);
				if(count($favourite_list)>0)
				{
					foreach($favourite_list as $key=>$val)
					{
						$mapurl = '';
						$fav_image = isset($val['fav_image']) ? $val['fav_image'] : 'fav_'.$val['p_favourite_id'].'.png';
						if(file_exists(DOCROOT.MOBILE_FAV_LOC_MAP_IMG_PATH."passenger_". $passenger_id ."/".$fav_image)) 
						{
							$mapurl = URL_BASE.MOBILE_FAV_LOC_MAP_IMG_PATH."passenger_". $passenger_id ."/".$fav_image;
						}
						$favourite_list[$key]['map_image'] = $mapurl;
					}
					
					$message = array("message" => __('success'),"detail"=>$favourite_list,"status"=>1);	
				}
				else
				{
					$message = array("message" => __('no_favourite_trips'),"status"=>0);	
				}				
			}
			else
			{
				$message = array("message" => __('no_favourite_trips'),"status"=>-1);								
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$favourite_list,$mapurl,$passenger_id);
		break;

		case 'getpassenger_update':
			$array = $mobiledata;
			$validator = $this->getpassenger_update_validation($array);						
			if($validator->check())
			{
				$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
				$trip_id = isset($array["trip_id"])? $array["trip_id"]:'';
				if($trip_id == ''){
					$message = array("message" => __('invalid_trip'),"status"=>-1);	
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					exit;
				}
				$passenger_id = $array["passenger_id"];
				$request_type = $array['request_type'];
				$new_drop_lat = isset($array['drop_lat']) ? $array['drop_lat'] : 0;
				$new_drop_long = isset($array['drop_long']) ? $array['drop_long'] : 0;
				$drop_location_name = isset($array['drop_location_name']) ? urldecode($array['drop_location_name']) : "";
				if($request_type == 0)
				{						
					$secondaryPassNotify = 0;
					$arrived_display = $tripstart_display = $trip_complete_display = $tripfare_update_display = $driver_cancel_display = 0;
					
					//** update the drop latitude and longitude when the passenger chenged ahile in trip **//
					if(!empty($trip_id) && !empty($new_drop_lat) && !empty($new_drop_long)) 
					{
						$datas  = array("notification_status"=>'22',"drop_latitude" => $new_drop_lat,
										"drop_longitude" => $new_drop_long,"drop_location" => $drop_location_name);											
						$updateDrop = $api_ext->update_passengerlogs($datas, $trip_id);
					}
					
					$amt="";$pickup="";
					$get_passenger_log_det = $api->get_request_detail($passenger_id,$trip_id);
					//~ print_r($get_passenger_log_det);exit;
					if(count($get_passenger_log_det) > 0)
					{
						$driver_reply = $get_passenger_log_det[0]->driver_reply;
						$travel_status = $get_passenger_log_det[0]->travel_status;
						$driver_id = $get_passenger_log_det[0]->driver_id;
						$transId = $get_passenger_log_det[0]->job_ref;
						$farePercent = $get_passenger_log_det[0]->fare_percentage;
						$amt = round($get_passenger_log_det[0]->amt,2);
						$splittedAmt = ($amt * $farePercent)/100;
						$pickup_location = $get_passenger_log_det[0]->pickup_location;
						$pickup_latitude = $get_passenger_log_det[0]->pickup_latitude;
						$pickup_longitude = $get_passenger_log_det[0]->pickup_longitude;
						$actual_pickup_time = $get_passenger_log_det[0]->actual_pickup_time;
						$drop_time = $get_passenger_log_det[0]->drop_time;
						$notification_status = $get_passenger_log_det[0]->notification_status;
						$primary_passenger = $get_passenger_log_det[0]->primary_passenger;
						$tripfare = $get_passenger_log_det[0]->tripfare; # no need
						$base_fare = $get_passenger_log_det[0]->base_fare;
						$minutes_fare = $get_passenger_log_det[0]->minutes_fare;
						$waiting_fare = $get_passenger_log_det[0]->waiting_fare;
						$nightfare = $get_passenger_log_det[0]->nightfare;
						$eveningfare = $get_passenger_log_det[0]->eveningfare;
						$company_tax = $get_passenger_log_det[0]->company_tax;
						$passenger_discount = $get_passenger_log_det[0]->passenger_discount;
						$promo_discount_fare = $get_passenger_log_det[0]->promo_discount_fare;
						$used_wallet_amount = $get_passenger_log_det[0]->used_wallet_amount;
						$split_wallet_amount = $get_passenger_log_det[0]->split_wallet_amount;
						$isSplit_fare = (int)$get_passenger_log_det[0]->splitTrip;//0->Normal Trip, 1->Split Trip
						$bearing = (int)$get_passenger_log_det[0]->bearing;

						if(!empty($new_drop_lat) && !empty($new_drop_long))
						{
							$driverDeviceDets = $api->getDriverDeviceToken($driver_id);
							if(count($driverDeviceDets) > 0 && !empty($driverDeviceDets[0]['device_token'])){									
									$tripUpdateMSg = __('passenger_update_drop_location');
									$pushMessage = array("message" => $tripUpdateMSg,"drop_location"=>$drop_location_name,"drop_latitude"=>$new_drop_lat,"drop_longitude"=>$new_drop_long,"pickup_location"=>$pickup_location,"pickup_latitude"=>$pickup_latitude,"pickup_longitude"=>$pickup_longitude,"driver_notes"=>'',"badge"=>5,"status"=>'99', 'bearing'=>$bearing);
									# driver api key
									//$driver_android_key    = $this->commonmodel->select_site_settings( 'driver_android_key', MDB_SITEINFO );
									$driver_android_key = DRIVER_ANDROID_KEY;
									$this->commonmodel->send_pushnotification($driverDeviceDets[0]['device_token'],$driverDeviceDets[0]['device_type'],$pushMessage,$driver_android_key);
									if($isSplit_fare == 1)
									{
										$api->splitted_pushnotification($pushMessage,$trip_id,$passenger_id);
									}
								}
						}
							$payment_type = ($get_passenger_log_det[0]->payment_type == 1) ? __('cash'):(($get_passenger_log_det[0]->payment_type == 5) ? __('wallet') : __('card'));
							/** secondary passengers approval status **/
							$splitApproveArr = array();
							if($primary_passenger == $passenger_id && $isSplit_fare == 1)
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
										else
										{ 
											$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
										}
										$splitApproveArr[$splkey]['profile_image'] = $profile_image;
									}
								}
							}
							/************** Driver Location ***************************/
							$driver_latitute = $driver_longtitute = '0.0';
							$current_driver_status = $api_ext->get_driver_current_status($driver_id);
							if(count($current_driver_status)>0)
							{
								$trip_status = $current_driver_status[0]->status;
								$driver_latitute = $current_driver_status[0]->latitude;
								$driver_longtitute = $current_driver_status[0]->longitude;
							}				
							/**********************************************************/
							
							if(($driver_reply == 'A') && ($travel_status == 9))
							{
								$detail = array("trip_id"=>$trip_id,"driverdetails"=>"");
								$message = array("message" => __("request_confirmed_passenger"),"detail"=>$detail,"isSplit_fare"=>$isSplit_fare,"splitfaredetail"=>$splitApproveArr,"driver_latitute"=>$driver_latitute,"driver_longtitute"=>$driver_longtitute,"status"=>1, 'bearing'=>$bearing);
							}
							elseif(($driver_reply == 'A') && ($travel_status == 8))
							{
								$dispatcher_cancel_display = ($notification_status != 8) ?  1 : 0;
								$message = array("message" => __("dispatcher_trip_cancelled"),"detail"=>"","driver_latitute"=>$driver_latitute,"driver_longtitute"=>$driver_longtitute,"status"=>10,"display"=>$dispatcher_cancel_display, 'bearing'=>$bearing);
								$datas  = array("notification_status"=>'8');
								$result = $api->update_split_log_table($datas, $passenger_id, $trip_id);
							}
							elseif(($driver_reply == 'C') && ($travel_status == 6))
							{
								$message = array("message" => __("trip_cancel"),"detail"=>"","driver_latitute"=>$driver_latitute,"driver_longtitute"=>$driver_longtitute,"status"=>7, 'bearing'=>$bearing);
							}
							elseif(($driver_reply == 'C') && ($travel_status == 9))
							{
								$driver_cancel_display = ($notification_status != 5) ?  1 : 0;
								$message = array("message" => __("driver_cancel_after_confirm"),"detail"=>"","driver_latitute"=>$driver_latitute,"driver_longtitute"=>$driver_longtitute,"status"=>8,"display"=>$driver_cancel_display, 'bearing'=>$bearing);
								$datas  = array("notification_status"=>'5');
								$result = $api->update_split_log_table($datas, $passenger_id, $trip_id);								
							}
							elseif(($driver_reply == 'A') && ($travel_status == 3))
							{
								$arrived_display = ($notification_status != 1) ?  1 : 0;
								$message = array("message"=>__('passenger_on_board'),"isSplit_fare"=>$isSplit_fare,"splitfaredetail"=>$splitApproveArr,"trip_id"=>$trip_id,"driver_latitute"=>$driver_latitute,"driver_longtitute"=>$driver_longtitute,"status"=>2,"display"=>$arrived_display, 'bearing'=>$bearing);
								$datas  = array("notification_status"=>'1');
								$result = $api->update_split_log_table($datas, $passenger_id, $trip_id);
							}
							elseif(($driver_reply == 'A') && ($travel_status == 2))
							{
								$tripstart_display = ($notification_status != 2) ?  1 : 0;
								$actual_pickup_time = $this->commonmodel->getcompany_all_currenttimestamp($default_companyid);
								$message = array("message" =>__('journey_started'),"isSplit_fare"=>$isSplit_fare,"splitfaredetail"=>$splitApproveArr,"pickup_time"=>$actual_pickup_time,"trip_id"=>$trip_id,"driver_status"=>$trip_status,"driver_latitute"=>$driver_latitute,"driver_longtitute"=>$driver_longtitute,"status"=>3,"display"=>$tripstart_display, 'bearing'=>$bearing);
								$datas  = array("notification_status"=>'2');
								$result = $api->update_split_log_table($datas, $passenger_id, $trip_id);
							}
							elseif(($driver_reply == 'A') && ($travel_status == 5))
							{
								$trip_complete_display = ($notification_status != 3) ?  1 : 0;
								$message = array("message"=>__('trip_completed'),"driver_status"=>$trip_status,"driver_latitute"=>$driver_latitute,"driver_longtitute"=>$driver_longtitute,"status"=>4,
								"display"=>$trip_complete_display, 'bearing'=>$bearing);
								$update_trip_array  = array("notification_status"=>'3');
								$datas  = array("notification_status"=>'3');
								$result = $api->update_split_log_table($datas, $passenger_id, $trip_id);
							}
							elseif(($driver_reply == 'A') && ($travel_status == 1) && $transId != 0)
							{
								$promotion = ($splittedAmt * $passenger_discount) / 100;
								$promotion = round($promotion,2);
								
								$interval  = abs(strtotime($drop_time) - strtotime($actual_pickup_time));
								$minutes   = round($interval / 60);
								
								$card_amt = $splittedAmt - $split_wallet_amount;
								# New E-receipt details
								if($isSplit_fare == 1){									
									$payment_type = __('wallet');
									$used_wallet_amount = $split_wallet_amount;
									if($splittedAmt > $split_wallet_amount){
										$card_amt = $splittedAmt - $split_wallet_amount;
										$payment_type = __('card');
									}
								}
									
								$tripfare_update_display = ($notification_status != 4) ?  1 : 0;
								$message = array("message" => __('trip_fare_updated'),
								"fare" => commonfunction::amount_indecimal($splittedAmt,'api'),
								"trip_id"=>$trip_id,"pickup" => $pickup_location, "status"=>5,
								"display"=>$tripfare_update_display,"driver_status"=>$trip_status,"driver_latitute"=>$driver_latitute,"driver_longtitute"=>$driver_longtitute,"payment_type"=>$payment_type,
								"base_fare"=>commonfunction::amount_indecimal($base_fare,'api'),
								"waiting_fare"=>commonfunction::amount_indecimal($waiting_fare,'api'),
								"nightfare"=>commonfunction::amount_indecimal($nightfare,'api'),
								"eveningfare"=>commonfunction::amount_indecimal($eveningfare,'api'),
								"paid_amount"=>commonfunction::amount_indecimal($card_amt,'api'),
								"promotion"=>commonfunction::amount_indecimal($promo_discount_fare,'api'),
								"tax"=>$company_tax,"used_wallet_amount"=>commonfunction::amount_indecimal($used_wallet_amount,'api'),"minutes_traveled"=>$minutes,
								"minutes_fare"=>commonfunction::amount_indecimal($minutes_fare,'api'),
								"metric" => UNIT_NAME
								);
								
								# rental & outstation start
								$trip_type = '0';
								$trip_distance =isset($get_passenger_log_det[0]->trip_distance)?$get_passenger_log_det[0]->trip_distance:'';
								$trip_minutes =isset($get_passenger_log_det[0]->trip_minutes)?$get_passenger_log_det[0]->trip_minutes:'';
								$rental_outstation =isset($get_passenger_log_det[0]->rental_outstation)?$get_passenger_log_det[0]->rental_outstation:'';
								$total_fare = $additional_distance_fare = $additional_time_fare = 0;

								if($rental_outstation !=''){
									$rent_out_tour_id = isset($get_passenger_log_det[0]->rent_out_tour_id)?$get_passenger_log_det[0]->rent_out_tour_id:0;
									
									# rental outstation calculation
									$rental_details['base_fare'] = $base_fare = isset($get_passenger_log_det[0]->base_fare)?$get_passenger_log_det[0]->base_fare:0;
									$rental_details['plan_distance'] = $plan_distance = isset($get_passenger_log_det[0]->plan_distance)?$get_passenger_log_det[0]->plan_distance:0;
									$rental_details['plan_duration'] = $plan_duration = isset($get_passenger_log_det[0]->plan_duration)?$get_passenger_log_det[0]->plan_duration:0;
									$rental_details['plan_distance_unit'] = $plan_distance_unit = isset($get_passenger_log_det[0]->plan_distance_unit)?$get_passenger_log_det[0]->plan_distance_unit:UNIT_NAME;
									$rental_details['additional_fare_per_distance'] = $additional_fare_per_distance = isset($get_passenger_log_det[0]->additional_fare_per_distance)?$get_passenger_log_det[0]->additional_fare_per_distance:0;
									$rental_details['additional_fare_per_hour'] = $additional_fare_per_hour = isset($get_passenger_log_det[0]->additional_fare_per_hour)?$get_passenger_log_det[0]->additional_fare_per_hour:0;
									
									$rental_details['trip_distance'] = $trip_distance;
									$rental_details['trip_minutes'] = $trip_minutes;
											
									$rental_os_data = commonfunction::rental_outstation_calc($rental_details);
									$additional_distance_fare = $rental_os_data['additional_distance_fare'];
									$additional_time_fare = $rental_os_data['additional_time_fare'];
								
									
									if($rental_outstation==1)
										$trip_type = '2';
									else
										$trip_type = '3';
								}
								
								$message['trip_type'] = $trip_type;
								$message['additional_distance_fare'] = commonfunction::amount_indecimal($additional_distance_fare,'api');
								$message['additional_time_fare'] = commonfunction::amount_indecimal($additional_time_fare,'api');
								
								# following params value will differs for rental & outstation
								if($trip_type > 1){
									
									//$message['paid_amount'] = $message['fare'] = commonfunction::amount_indecimal($total_fare,'api');
									$message['base_fare'] = commonfunction::amount_indecimal($base_fare,'api');
								}
								# rental & outstation end
								
								$datas  = array("notification_status"=>'4');
								//~ print_r($message);exit;
								$result = $api->update_split_log_table($datas, $passenger_id, $trip_id);
							}
							elseif(($driver_reply == 'A') && ($travel_status == 4) && $primary_passenger != $passenger_id)
							{
								$tripCancel_display = ($notification_status != 9) ?  1 : 0;
								$message = array("message" => __('trip_cancel_by_primary'),"driver_status"=>$trip_status,"driver_latitute"=>$driver_latitute,"driver_longtitute"=>$driver_longtitute, "status"=>9,"display"=>$tripCancel_display, 'bearing'=>$bearing);
								
								$datas  = array("notification_status"=>'9');									
								$result = $api->update_split_log_table($datas, $passenger_id, $trip_id);
							}									
							else
							{
								$message = array("message"=>__('trip_not_started'),"driver_status"=>$trip_status,"driver_latitute"=>$driver_latitute,"driver_longtitute"=>$driver_longtitute,"status"=>6, 'bearing'=>$bearing);
							}							
						}
						else
						{
							$message = array("message" => __('invalid_trip'),"status"=>-1);
						}
					}
					elseif($request_type == 1)
					{
						$get_driver_request = $api->get_driver_request($trip_id);
						if(count($get_driver_request) >0)
						{
							$driver_reply = $get_driver_request[0]['status'];
							$trip_type = $get_driver_request[0]['trip_type'];//get booking type 1-Favourite booking, 0-Normal Booking
							$available_drivers = explode(',',$get_driver_request[0]['total_drivers']);
							$rejected_timeout_drivers = explode(',',$get_driver_request[0]['rejected_timeout_drivers']);
							$comp_result = array_diff($available_drivers, $rejected_timeout_drivers);

							if(count($comp_result) == 0)
							{
								$driver_reply  = 5;
							}
							
							if($driver_reply == '3')
							{
								$detail = array("trip_id"=>$trip_id,"driverdetails"=>"");
								$message = array("message" => __("request_confirmed_passenger"),"detail"=>$detail,"status"=>1);
							}
							elseif($driver_reply == '4')
							{
								$message = array("message" => __("trip_cancel"),"detail"=>"","status"=>7);
								// version 6.2.3 update                                                 
								$void_transaction_trip=$api->voidTransaction_for_trip($trip_id);
							}	
							elseif($driver_reply == '5')
							{
								if($trip_type == 1) {
									$message = array("message" => __("fav_driver_not_available"),"status"=>4);
								} else {
									$message = array("message" => __("driver_busy"),"status"=>2);
								}
								//~ print_r($message);exit;
								// version 6.2.3 update                                                 
								$void_transaction_trip=$api->voidTransaction_for_trip($trip_id);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								exit;
							}
							else
							{
								$message = array("message"=>__('trip_not_started'),"status"=>6);
							}
						}
						else
						{
							$message = array("message" => __('invalid_trip'),"status"=>-1);	
						}
					}
					else
					{
						$message = array("message" => __('No Trips '),"status"=>-1);
					}
				}
				else
				{
						$errors = $validator->errors('errors');	
						$message = array("message" => __('validation_error'),"status"=>-5,"detail"=>$errors);
				}
				//~ print_r($message);exit;
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$get_driver_request,$api_ext);
		break;

		case 'invite_with_referral':
			$passenger_id = isset($mobiledata['passenger_id']) ? $mobiledata['passenger_id'] : '';
			if(!empty($passenger_id)) 
			{
				$passengerReferral = $api->get_passenger_wallet_amount($passenger_id);
				if(count($passengerReferral) > 0) 
				{
					if(REFERRAL_SETTINGS == 1) {
						$referral_settings = 1;
						$referral_settings_message = "";
					} else {
						$referral_settings = 0;
						$referral_settings_message = __("referral_settings_message");
					}
					$detail = array("referral_code" => $passengerReferral[0]['referral_code'],
									"referral_amount" => commonfunction::amount_indecimal($passengerReferral[0]['referral_code_amount'],'api'),
									"referral_settings" => $referral_settings,
									"referral_settings_message" => $referral_settings_message);
					$message = array("message" => __('referral_amount'),"detail" => $detail,"status"=>1);
				} else {
					$message = array("message" => __('invalid_user'),"status"=>-2);
				}
			} else {
				$message = array("message" => __('invalid_request'),"status"=>-1);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$passengerReferral,$detail);
		break;

		/** Passenger language update **/
		case 'passenger_language_update':				
			$passenger_id = isset($mobiledata['passenger_id'])?$mobiledata['passenger_id']:'';
			$language = isset($mobiledata['language'])?$mobiledata['language']:'';
			if($passenger_id !='' && $language != '') {
				
				$update_lang = $api->update_passenger_language($passenger_id,$language);
				$message = array("message" => __('language_updated'),"status" => 1);
			} else {
				$message = array("message" => __('invalid_request'),"status" => -1);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$login_status,$trip_list);
		break;

		case 'forgot_password':
			$array_values = $mobiledata;			
			$message="";
			if($array_values['user_type'] == 'P')
			{
				//26-05-2018 Country code change 
				$cc = (isset($array_values['country_code'])) ? $array_values['country_code'] : '';
				$country_code = (substr($cc, 0, 1) === '+')?$cc:'+'.$cc; 
				$phone_no = $country_code.'-'.$array_values['phone_no'];

				$phone_exist = $api_ext->check_phone_passengers($array_values['phone_no'],$default_companyid,$country_code); 
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

		case 'passenger_fb_connect':
			$array = $mobiledata;
			$accessToken = $array['accesstoken'];
			$uid = $array['userid'];
			$fname = $array['fname'];
			$lname = $array['lname'];
			$email = $array['fbemail'];
			$devicetoken = $array['devicetoken'];
			$device_id = $array['deviceid'];
			$devicetype = $array['devicetype'];
			/** Thumb Image ****/
			$thumb_image = @file_get_contents("https://graph.facebook.com/".$uid."/picture?width=".PASS_THUMBIMG_WIDTH1."&height=".PASS_THUMBIMG_HEIGHT1."");
			$thumb_image_name =  'thumb_'.$uid.'.jpg';
			$thumb_image_path = DOCROOT.PASS_IMG_IMGPATH.$thumb_image_name; 
			@chmod(DOCROOT.PASS_IMG_IMGPATH,0777);
			@chmod($thumb_image_path,0777);
			$thumb_image_file = fopen($thumb_image_path, "w") or die("Unable to open file!");
			fwrite($thumb_image_file, $thumb_image);
			fclose($thumb_image_file);

			$edit_image = @file_get_contents("https://graph.facebook.com/".$uid."/picture?width=".PASS_THUMBIMG_WIDTH1."&height=".PASS_THUMBIMG_HEIGHT1."");
			$edit_image_name =  'edit_'.$uid.'.jpg';
			$edit_image_path = DOCROOT.PASS_IMG_IMGPATH.$edit_image_name;
			@chmod(DOCROOT.PASS_IMG_IMGPATH,0777);
			@chmod($edit_image_path,0777);
			$image_file = fopen($edit_image_path, "w") or die("Unable to open file!");
            fwrite($image_file, $edit_image);
            fclose($image_file);

			/** Big Image **/
			$big_image = @file_get_contents("https://graph.facebook.com/".$uid."/picture?width=".PASS_IMG_WIDTH."&height=".PASS_IMG_HEIGHT."");
			$image_name =  $uid.'.jpg';
			$big_image_path = DOCROOT.PASS_IMG_IMGPATH.$image_name;
			@chmod(DOCROOT.PASS_IMG_IMGPATH,0777);
			@chmod($big_image_path,0777);
			$big_image_file = fopen($big_image_path, "w") or die("Unable to open file!");
            fwrite($big_image_file, $big_image);
            fclose($big_image_file);
			$base_image = imagecreatefromjpeg($edit_image_path);
			$width = 100;
			$height = 19;
			$top_image = imagecreatefrompng(URL_BASE.PUBLIC_IMAGES_FOLDER."edit.png");
			$merged_image = DOCROOT.PASS_IMG_IMGPATH.'edit_'.$uid.'.jpg';
			imagesavealpha($top_image, true);
			imagealphablending($top_image, true);
			imagecopy($base_image, $top_image, 0, 83, 0, 0, $width, $height);
			imagejpeg($base_image, $merged_image);

			/*************************/	
			$otp = text::random($type = 'alnum', $length = 5);
			$referral_code = strtoupper(text::random($type = 'alnum', $length = 6));
			$status = $api->register_facebook_user($accessToken,$uid,$otp,$referral_code,$fname,$lname,$email,$image_name,$devicetoken,$device_id,$devicetype,$default_companyid);
			//~ echo $status;exit;
			if($status == -10){
				$message = array("message" => __('passenger_blocked'),"status" => $status);
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				break;
			}
			$passenger_details = $api->passenger_detailsbyemail($email,$default_companyid,$uid);	
			
			if((!empty($passenger_details[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$passenger_details[0]['profile_image']))
			{ 
				$profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$passenger_details[0]['profile_image']; 
			}
			else{ 
				$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
			} 

			if(count($passenger_details) > 0){
				$passenger_details[0]['profile_image'] = $profile_image;
			}
			$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
			$config_array = $api_ext->select_site_settings($default_companyid);							
			$total_array = array();
			$result = $passenger_details;
			$fbemail = '';
			$skip_credit_card = 2;
			if(count($result) > 0)
			{
				
				$total_array['id'] = $result[0]['id'];
				$total_array['name'] = $result[0]['name'];
				$total_array['email'] = $result[0]['email'];
				$fbemail = $total_array['email'];
				$total_array['profile_image'] = $profile_image;
				$total_array['country_code'] = $result[0]['country_code'];
				$total_array['phone'] = $result[0]['phone'];
				$total_array['address'] = $result[0]['address'];
				$total_array['user_status'] = $result[0]['user_status'];
				$total_array['login_from'] = $result[0]['login_from'];
				$total_array['referral_code'] = $result[0]['referral_code'];
				$total_array['referral_code_amount'] = $result[0]['referral_code_amount'];
				$total_array['split_fare'] = $result[0]['split_fare'];
				//to check whether the passenger gave
				$skip_credit_card = $result[0]['skip_credit_card'];
				$telltofriend_message = TELL_TO_FRIEND_MESSAGE;//str_replace("#REFDIS#",$ref_discount,$ref_message); 
				$total_array['telltofriend_message'] = $telltofriend_message;
				
				//Newly Added-13.11.2014
				$total_array['site_currency'] = CURRENCY;
				$total_array['aboutpage_description'] = $this->app_description;
				$total_array['tell_to_friend_subject'] = __('telltofrien_subject');
				$total_array['skip_credit'] = SKIP_CREDIT_CARD;
				$total_array['metric'] = UNIT_NAME;
				$total_array['favourite_driver'] = $result[0]['favourite_driver'];
				$total_array['skip_favourite'] = $result[0]['skip_favourite'];
				//variable to know whether the passenger have credit card
				$check_card_data = $api_ext->check_passenger_card_data($result[0]['id']);
				$credit_card_sts = ($check_card_data == 0) ? 0 : SKIP_CREDIT_CARD;
				$total_array['credit_card_status'] = $credit_card_sts;
			}
								
			/***Get Company car model details start***/
			//$company_model_details = $api->company_model_details($default_companyid);
			$company_model_details = $api_ext->company_model_details($default_companyid);
			if(count($company_model_details)>0){
				$total_array['model_details']=$company_model_details;
			}else{
				$total_array['model_details']="model details not found";
			}
			/***Get Company car model details end***/
			
		    if($status==1)
			{									
				$message = array("message" => __('succesful_login_flash'),"detail"=>$total_array,"status"=> 1); 
			}
			else if($status==2)
			{	
				$detail = array("email"=>$fbemail);														
				$message = array("message"=>__('account_saved_withoutmobile'),"detail"=>$detail,"status"=>2);					 
			}
			else if($status == -9)
			{	
				$message = array("message"=>__('succesful_login_flash'),"detail"=>$total_array,"status"=>1);													 
			}
			else if($status==4 || $status==3)
			{
				/*if(SKIP_CREDIT_CARD !=1 || $skip_credit_card != 1)
				{
					$message = array("message"=>__('p_card_data_not_filled'),"detail"=>$total_array,"status"=>4);	
				}
				else
				{
					$message = array("message" => __('succesful_login_flash'),"detail"=>$total_array,"status"=> 1);
				}*/
				$message = array("message" => __('succesful_login_flash'),"detail"=>$total_array,"status"=> 1);
			}
			else if($status==-2)
			{	
				$detail = array("email"=>$email);							 
				$message = array("message"=>__('account_not_activated'),"detail"=>$detail,"status"=>-2);													 
			}
			else if($status==10)
			{
				$message = array("message" => __('facebook_email_empty'),"status"=>10);
			}
			else
			{
				$message = array("message" => __('facebook_error'),"status"=>-1);
			}

			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$detail,$company_model_details,$total_array,$config_array,$passenger_details,$edit_image,$edit_image_path,$thumb_image,$thumb_image_path,$thumb_image_file,$image_file,$big_image,$big_image_path,$big_image_file,$base_image,$top_image,$merged_image,$status);
		break;	

		case 'passenger_login':
			$p_login_array = $mobiledata;
			
			$validator = $this->passenger_login_validation($p_login_array);
			if($validator->check())
			{ 
				$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
				$cc = (isset($p_login_array['country_code'])) ? $p_login_array['country_code'] : '';
				$country_code = (substr($cc, 0, 1) === '+')?$cc:'+'.$cc;
			   	$phone_exist = $api_ext->check_phone_passengers($p_login_array['phone'],$default_companyid,$country_code);
			   	if($phone_exist == 0)
				{
					$message = array("message" => __('phone_not_exists'),"status"=> 2);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					//unset($message);
					break;
				} 
				else
				{
					$result = $api->passenger_login($p_login_array['phone'],md5(urldecode($p_login_array['password'])),$p_login_array['devicetoken'],$p_login_array['deviceid'],$p_login_array['devicetype'],$default_companyid,$country_code); 

					if(count($result) > 0)
					{
						//Checking the User Status
						$user_status = $result[0]['user_status'];
						$passenger_email = $result[0]['email'];
						$passenger_id = $result[0]['id'];
						$device_id = $result[0]['device_token'];
						$login_status = $result[0]['login_status'];
						if($user_status == 'D' || $user_status == 'T' )
						{
							$message = array("message" => __('passenger_blocked'),"status"=> 3);
						}
						else if($user_status == 'I')
						{
							$detail = array("email"=>$passenger_email,"phone"=>$p_login_array['phone'],"passenger_id"=>$passenger_id);
							$message = array("message" => __('account_not_activated'),"detail"=>$detail,"status"=> -2);
						}
						else
						{
							$device_token=isset($p_login_array['devicetoken'])?$p_login_array['devicetoken']:'';
							$device_id;
							$update_id = $result[0]['id'];
							
							//$check_personal_date = $api->check_passenger_personal_data($update_id);
							$check_card_data = $api_ext->check_passenger_card_data($update_id);
							//variable to know whether the passenger have credit card
							$credit_card_sts = ($check_card_data == 0) ? 0:SKIP_CREDIT_CARD;
							if(isset($result[0]['name']) && $result[0]['name'] == '')
							{ 
								$detail = array("email"=>$passenger_email,"phone"=>$p_login_array['phone'],"passenger_id"=>$passenger_id);
								$message = array("message" => __('p_personal_data_not_filled'),"status"=> -2,"detail"=>$detail);
							}
							# card details functionality has been commanded to simplify the signup
							/*else if($result[0]['skip_credit_card'] !=1 && $check_card_data == 0)
							{
								$detail = array("email"=>$passenger_email,"phone"=>$p_login_array['phone'],"passenger_id"=>$passenger_id);
								$message = array("message" => __('p_card_data_not_filled'),"status"=> -3,"detail"=>$detail);
							}*/		
							else
							{ 
								if((!empty($result[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'edit_'.$result[0]['profile_image'])){ 
									$edit_image = URL_BASE.PASS_IMG_IMGPATH.'edit_'.$result[0]['profile_image']; 
								}
								else{ 
									$edit_image = URL_BASE."public/images/edit_image.png";
								} 

								$result[0]['edit_image'] = $edit_image;

								if((!empty($result[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$result[0]['profile_image']))
								{ 
									$profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$result[0]['profile_image']; 
								}
								else{ 
									$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
								} 
								$total_array = array();
								if(count($result) > 0)
								{
									$total_array['id'] = $result[0]['id'];
									$total_array['name'] = $result[0]['name'];
									$total_array['email'] = $result[0]['email'];
									$total_array['profile_image'] = $profile_image;
									$total_array['country_code'] = $result[0]['country_code'];
									$total_array['phone'] = $result[0]['phone'];
									$total_array['login_from'] = $result[0]['login_from'];
									$total_array['referral_code'] = $result[0]['referral_code'];
									$total_array['referral_code_amount'] = $result[0]['referral_code_amount'];
									//this field is used to check whether the user logged in after forgot the password 0 - not forgot, 1- forgot
									$total_array['forgot_password'] = $result[0]['forgot_password'];
									$total_array['split_fare'] = $result[0]['split_fare'];
									$telltofriend_message = TELL_TO_FRIEND_MESSAGE;//str_replace("#REFDIS#",$ref_discount,$ref_message); 
									$total_array['telltofriend_message'] = $telltofriend_message;
									//Newly Added-13.11.2014
									$total_array['site_currency'] = $this->site_currency;
									$total_array['aboutpage_description'] = $this->app_description;
									$total_array['tell_to_friend_subject'] = __('telltofrien_subject');
											
									$total_array['credit_card_status'] = $credit_card_sts;
									$total_array['skip_favourite'] = $result[0]['skip_favourite'];
									$total_array['favourite_driver'] = $result[0]['favourite_driver'];
									/** function to update forgot_password status as 0 **/
									if($total_array['forgot_password'] == 1) {
										$update_pass_array  = array("forgot_password" => '0'); // Start to Pickup
										$result = $api_ext->update_passengers($update_pass_array,$passenger_id);	
									}
									/***Get Company car model details end***/
									/* create user logs */
									// Notification Logger -- Start
									$not_project=array();
									$not_project['profile_image']=1;
									$not_project['name']=1;
									$not_match=array();
									$not_match['_id']=(int)$passenger_id;
									$not_result=$this->commonmodel->dynamic_findone_new(MDB_PASSENGERS,$not_match,$not_project);
									$not_name = isset($not_result['name']) ? $not_result['name'] : "";
									$notification_content=array();
									$notification_content['msg']=__('notification_login_passenger',array(':username' => $not_name));
									$notification_content['domain']=SUBDOMAIN_NAME;
									$notification_content['image']=isset($not_result['profile_image'])?$not_result['profile_image']:"";
									$notification_content['type']='PASSENGER_LOGIN';

									// Notification Logger -- End
				                    $user_unique = $result[0]['id'].__('log_passenger_type');
				                    $log_array = array(
				                        'user_id' => (int)$result[0]['id'],
				                        'user_type' => __('log_passenger_type'),
				                        'login_type' => __('log_device'),
				                        'activity' => __('login_log'),
'notification_content' => $notification_content,
'notification_type' =>(int)1,
				                        'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
				                    );
				                    commonfunction::save_user_logs($log_array, $user_unique);
				                    /* create user logs */
									$message = array("message"  => __('succesful_login_flash'),"detail"=>$total_array,"status"=> 1);
								}
							}											
						}
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						//unset($message,$total_array);
						break;												
					}							
					else
					{
						$message = array("message" => __('password_failed'),"status"=> 4);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						//unset($message);
						break;										
					}						
				}					
			}
			else
			{
				$validation_error = $validator->errors('errors');	
				$message = array("message" => __('validation_error'),"detail"=>$validation_error,"status"=>-5);
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$validation_error);
				break;				
			}	
			//unset($validator,$message,$phone_exist,$result,$detail,$profile_image,$edit_image,$check_card_data);
		break;		

		case 'passenger_mobile_otp':
			$array = $mobiledata;
			$email = $array['fbemail'];
			$mobile = $array['mobile'];
			$country_code = isset($array['country_code']) ? $array['country_code'] : '';

			$phone_exist = $api->check_phone_bypassengers($mobile,$email,$default_companyid,$country_code);
			
			if($phone_exist != 0)
			{
				$message = array("message" => __('phone_exists'),"status"=>4);
			}
			else 
			{
				if($email != null && $mobile != null)
				{
					$status = $api->update_passenger_mobile($email,$mobile,$country_code);

					if($status == 1)
					{
						$result = $api->passenger_detailsbyemail($email,$default_companyid);
						$otp = $result[0]['otp'];
						$id = $result[0]['id'];
						$mail="";						
						$total_array = array();
						if(count($result) > 0)
						{
							/*$total_array['id'] = $passenger_details[0]['id'];
							$total_array['name'] = $passenger_details[0]['name'];
							$total_array['email'] = $passenger_details[0]['email'];
							$total_array['phone'] = $passenger_details[0]['phone'];
							$total_array['address'] = $passenger_details[0]['address'];*/
							if((!empty($result[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'edit_'.$result[0]['profile_image'])){ 
								$edit_image = URL_BASE.PASS_IMG_IMGPATH.'edit_'.$result[0]['profile_image']; 
							}
							else{ 
								$edit_image = URL_BASE."public/images/edit_image.png";
							} 

							$result[0]['edit_image'] = $edit_image;

							if((!empty($result[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$result[0]['profile_image']))
							{ 
								$profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$result[0]['profile_image']; 
							}
							else{ 
								$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
							} 
							$total_array['id'] = $result[0]['id'];
							$total_array['name'] = $result[0]['name'];
							$total_array['email'] = $result[0]['email'];
							$total_array['profile_image'] = $profile_image;
							$total_array['country_code'] = $result[0]['country_code'];
							$total_array['phone'] = $result[0]['phone'];
							$total_array['login_from'] = $result[0]['login_from'];
							$total_array['referral_code'] = $result[0]['referral_code'];
							$total_array['referral_code_amount'] = $result[0]['referral_code_amount'];
							//this field is used to check whether the user logged in after forgot the password 0 - not forgot, 1- forgot
							$total_array['forgot_password'] = $result[0]['forgot_password'];
							$total_array['split_fare'] = $result[0]['split_fare'];
							$telltofriend_message = TELL_TO_FRIEND_MESSAGE;//str_replace("#REFDIS#",$ref_discount,$ref_message); 
							$total_array['telltofriend_message'] = $telltofriend_message;
							//Newly Added-13.11.2014
							$total_array['site_currency'] = $this->site_currency;
							$total_array['aboutpage_description'] = $this->app_description;
							$total_array['tell_to_friend_subject'] = __('telltofrien_subject');
							$check_card_data = isset($result[0]['creditcard_details']) ? count($result[0]['creditcard_details']) : 0;
							$credit_card_sts = ($check_card_data == 0) ? 0:SKIP_CREDIT_CARD;
							$total_array['credit_card_status'] = $credit_card_sts;
							$total_array['skip_favourite'] = $result[0]['skip_favourite'];
							$total_array['favourite_driver'] = $result[0]['favourite_driver'];
							/** function to update forgot_password status as 0 **/
						}
						$detail = array("passenger_id"=>$id);
						$message = array("message" => __('signup_success'),"detail"=>$total_array,"status"=>1);
					}
					else
					{
						$message = array("message" => __('try_again'),"status"=>2);
					}
				}
				else
				{
					$message = array("message" => __('invalid_user'),"status"=>3);
				}	

			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$detail,$phone_exist,$status,$message,$replace_variables,$message_details,$total_array);
		break;

		# type - 1 - split_fare / 2 - favourite_driver / 3 - skip_favourite
		# value - 1 - enable, 2 - disable
		case 'set_split_fare':
			$passenger_id = isset($mobiledata['passenger_id']) ? $mobiledata['passenger_id'] : '';
			$type = isset($mobiledata['type']) ? $mobiledata['type'] : '';
			$value = isset($mobiledata['value']) ? $mobiledata['value'] : '';
			if(empty($passenger_id) || empty($type) || empty($value)) {
				
				$message = array("message" => __('invalid_request'),"status" => -1);
			} 
			else 
			{
				# passenger app settings
				$settings_type = ['split_fare' => 1, 'favourite_driver' => 2, 'skip_favourite' => 3];
				if(!in_array($type,$settings_type))
				{
					$message = array("message" => __('invalid_request'),"status" => -1);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					exit;
				}
				$result = $api->set_split_fare($passenger_id,$settings_type,$type,$value);
				if($result > 0) 
				{
					$field_name = array_search($type, $settings_type);
					if($value == 1) 
					{
						$status_message = $field_name.'_on_success_label';
							$message = array("message" => __($status_message), "status" => 1);
					} 
					else 
					{
						$status_message = $field_name.'_off_success_label';
						$message = array("message" => __($status_message), "status" => 0);
					}
				} 
				else 
				{
					$message = array("message" => __('not_eligible_for_splifare_on_label'), "status" => -1);
				}
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$result);
		break;

		case 'splitfare_approval':
			$splitfare_approval = $mobiledata;
			$validator = $this->checkApprovalValidation($mobiledata);	
			if($validator->check())
			{					
				if(!empty($mobiledata['friend_id']))
				{
					//function to check a trip is completed while accept the split trip
					$checkTripStatus = $api->checkTripStatus($mobiledata['trip_id']);
					if($checkTripStatus == 1 || $checkTripStatus == 5 )
					{					
						$message = __('trip_already_completed');
						$message = array("message" => $message,"status"=>2);					
					}
					elseif($checkTripStatus == 4)
					{							
						$message = __('cancelled');
						$message = array("message" => $message,"status"=>2);
						
					} 
					else 
					{						
						$result = $api->setSplitfareApproval($mobiledata['trip_id'],$mobiledata['friend_id'],$mobiledata['approve_status']);
						if($result == 1)
						{	
							$message = array("message" =>__('approve_status_updated'),"trip_id" => $mobiledata['trip_id'],"status"=> 1);
						}
						else if($result == 2)
						{
							$message = array("message" =>__('approve_status_declined'),"trip_id" => $mobiledata['trip_id'],"status"=> 3);
						}
						else
						{
							$message = array("message" => __('invalid_approve'),"status"=>2);
						}
					}
				}
				else
				{
					$message = array("message" => __('invalid_user'),"status"=>-1);	
				}
			}
			else
			{
				$message = array("message" => __('validation_error'),"status"=>0);							
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$validator,$result,$checkTripStatus);
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
						if($trip_details['drop_time'] != "") 
						{
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
								$path = explode('|',$path);
								$path = array_unique($path);
								include_once MODPATH."/email/vendor/polyline_encoder/encoder.php";
								$polylineEncoder = new PolylineEncoder();
								if(!empty($path))
								{
									foreach($path as $values)
									{
										$values = explode(',',$values);
										if(isset($values[0]) && isset($values[1]))
										{ 
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
								} 
								else 
								{
									$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x640&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
									}
									if(isset($mapurl) && $mapurl != "") {
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
							if($rental_outstation !=''){
								
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
								$trip_details['minutes_fare'] = commonfunction::amount_indecimal($journey->minutes_fare,'api');
								
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

						$dresult = $api_ext->driver_ratings($driver_id);
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
													
							if($trip_total_with_rate!=0 && $overall_rating!=0)
							{
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
									else
									{ 
										$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
									}
									$splitApproveArr[$splkey]['profile_image'] = $profile_image;
									$splittedFare = ($trip_details['amt'] * $splits['fare_percentage']) / 100;
									$splitApproveArr[$splkey]['splitted_fare'] = commonfunction::amount_indecimal($splittedFare, 'api');
								}
							}
						}
						$trip_details['splitFareDetails'] = $splitApproveArr;
						//~ print_r($trip_details);exit;
						if(count($get_passenger_log_details) == 0)
						{
							$message = array("message" => __('try_again'),"status"=>0,"site_currency"=>$this->site_currency);	
						}
						else
						{
							$mes = __('success');
							if($trip_details['travel_status'] == 5) {
								$mes = __('trip_waiting_payment');
							} else if($trip_details['travel_status'] == 4) {
								$mes = __('cancel_by_passenger');
							}
							
							
							//~ print_r($trip_details);exit;
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

		case 'edit_card_details':
			$p_card_array= $mobiledata;
			$passenger_cardid = $p_card_array['passenger_cardid'];
			$passenger_id = $p_card_array['passenger_id'];
			$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
			if($passenger_cardid != null)
			{
				$creditcard_no = $p_card_array['creditcard_no'];
				$creditcard_cvv = $p_card_array['creditcard_cvv'];
				$expdatemonth = $p_card_array['expdatemonth'];
				$expdateyear = $p_card_array['expdateyear'];
				$default = $p_card_array['default'];
				$card_validation = $this->edit_passenger_card_validation($p_card_array);
				
				if($card_validation->check())
				{
					$authorize_status =$api_ext->isVAlidCreditCard($creditcard_no,"",true);
					if($authorize_status == 0)
					{
						$message = array("message" => __('invalid_card'),"status"=> 2);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						exit;
					}
					$card_exist = $api->edit_check_card_exist($passenger_cardid,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$passenger_id,$default);
					
					if($card_exist == 1)
					{
						$message = array("message" => __('card_exist'),"status"=> 3);
					}
					else if($card_exist == 2)
					{
						$message = array("message" => __('one_card_exist'),"status"=> 2);
					}
					else
					{
						//Credit Card Pre authorization section goes here
						//preauthorization with amount "0"(Zero)
						$preAuthorizeAmount = PRE_AUTHORIZATION_REG_AMOUNT;
						//list($returncode,$paymentResult,$fcardtype,$preAuthorizeAmount) = $api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
						$paymentresponse=$api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
						$returncode=$paymentresponse['code'];
						$paymentResult=(isset($paymentresponse['TRANSACTIONID']) && ($paymentresponse['TRANSACTIONID']!=''))?$paymentresponse['TRANSACTIONID']:$paymentresponse['payment_response'];
						$fcardtype=isset($paymentresponse['cardType'])?$paymentresponse['cardType']:'';
						if($returncode==0)
						{
							//preauthorization with amount "1"
							$preAuthorizeAmount = PRE_AUTHORIZATION_RETRY_REG_AMOUNT;
							//list($returncode,$paymentResult,$fcardtype,$preAuthorizeAmount)= $api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
							$paymentresponse= $api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
							$returncode=$paymentresponse['code'];
							$paymentResult=(isset($paymentresponse['TRANSACTIONID']) && ($paymentresponse['TRANSACTIONID']!=''))?$paymentresponse['TRANSACTIONID']:$paymentresponse['payment_response'];
							$fcardtype=isset($paymentresponse['cardType'])?$paymentresponse['cardType']:'';
						}
						
						if($returncode != 0)
						{
							$result = $api->edit_passenger_carddata($p_card_array,$paymentResult,$preAuthorizeAmount,$fcardtype);
							if($result == 0) 
							{
                                $paymentresponse['preTransactAmount']=$preAuthorizeAmount;
								$void_transaction=$api->voidTransactionAfterPreAuthorize($passenger_cardid,$paymentresponse);
							}
						}
						else
						{
							$message=array("message"=> $paymentResult,"status"=>3);
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							exit;
						}
						if($result == 0)
						{
							$message = array("message" => __('edit_card_success'),"status"=>1);		
						}
						else
						{
							$message = array("message" => __('try_again'),"status"=>-1);	
						}
					}
				
				}
				else
				{							
					$validation_error = $card_validation->errors('errors');	
					$message = array("message" => __('validation_error'),"detail"=>$validation_error,"status"=>-3);		
				}
			}
			else
			{
				$message = array("message" => __('try_again'),"status"=>1);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$validation_error,$result,$card_validation,$authorize_status,$card_exist,$returncode,$paymentResult,$fcardtype,$preAuthorizeAmount,$void_transaction);
		break;

		case 'get_credit_card_details':
			$array = $mobiledata;
			$passenger_id = $array['passenger_id'];
			$default = $array['default'];
			$card_type = strtoupper($array['card_type']);
			if($array['passenger_id'] != null)
			{												
				$result = $api_ext->get_creadit_card_details($passenger_id,$card_type,$default);
				
				if(count($result)>0)
				{
					$carddetails = array();
					if($default == 'yes')
					{									
						$plain_cardno = encrypt_decrypt('decrypt',$result[0]['creditcard_no']);
						$carddetails['creditcard_no'] = $plain_cardno;
						$carddetails['masked_creditcard_no'] = repeatx($plain_cardno,'X',4);
						$carddetails['expdatemonth'] = $result[0]['expdatemonth'];
						$carddetails['expdateyear'] = $result[0]['expdateyear'];
						$carddetails['creditcard_cvv'] = "";//$result[0]['creditcard_cvv'];
						$carddetails['masked_creditcard_cvv'] = "";//repeatx($result[0]['creditcard_cvv'],'X','All');		
						$carddetails['passenger_cardid'] = $result[0]['passenger_cardid'];
						$carddetails['card_type'] = $result[0]['card_type'];									
						$carddetails['card_holder_name'] = $result[0]['card_holder_name'];									
						$message = array("message" =>__('success'),"detail"=>$carddetails,"status"=>1);					
					}
					else
					{
						$i = 0;
						$alldetails = array();
						foreach($result as $value)
						{
							$plain_cardno = encrypt_decrypt('decrypt',$value['creditcard_no']);
							$carddetails['creditcard_no'] = $plain_cardno;
							$carddetails['masked_creditcard_no'] = repeatx($plain_cardno,'X',4);
							$carddetails['expdatemonth'] = $value['expdatemonth'];
							$carddetails['expdateyear'] = $value['expdateyear'];
							$carddetails['creditcard_cvv'] = "";//$value['creditcard_cvv'];
							$carddetails['masked_creditcard_cvv'] = "";//repeatx($value['creditcard_cvv'],'X','All');		
							$carddetails['default_card'] = $value['default_card'];
							$carddetails['passenger_cardid'] = $value['passenger_cardid'];										
							$carddetails['card_type'] = $value['card_type'];
							$carddetails['card_holder_name'] = isset($value['card_holder_name'])?$value['card_holder_name']:'';
							$alldetails[] = $carddetails;
							$i = $i+1;										
						}
						$message = array("message" =>__('success'),"detail"=>$alldetails,"status"=>1);	
					}								
				}
				else
				{
					$message = array("message" =>__('no_card'),"status"=>2);
				}
			}	
			else
			{
				$message = array("message" => __('invalid_user'),"status"=>-1);	
			}	
			//~ print_r($message);exit;		
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$carddetails,$alldetails);
		break;

		case 'credit_card_delete':
			if(!empty($mobiledata['passenger_cardid']) && !empty($mobiledata['passenger_id'])) 
			{
				$favourite_details = $api->delete_credit_card($mobiledata['passenger_cardid'], $mobiledata['passenger_id']);
				if($favourite_details) {
					$message = array("message" => __('credit_card_deleted'),"status"=>1);
				} else {
					$message = array("message" =>__('invalid_card_id'),"status"=>2);
				}
			} else {
				$message = array("message" =>__('invalid_card_id'),"status"=>2);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$favourite_details);
		break;

		case 'passenger_profile':			
			if($mobiledata['userid'] != null)
			{
				$result = $api->passenger_profile($mobiledata['userid'],'A');
				if(count($result) > 0)
				{
					$passenger_image = $result[0]['profile_image'];							
					/*************************** Passenger Image ************************************/
					if((!empty($passenger_image)) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$passenger_image))
					{ 
						$profile_image = URL_BASE.PASS_IMG_IMGPATH.$passenger_image; 
					}
					else
					{ 
						$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
					}
					$result[0]['profile_image'] = 	$profile_image;
					$message = array("message" => __('success'),"detail"=>$result,"status"=>1);	
				}
				else
				{
					$message = array("message" => __('invalid_user'),"status"=>0);	
				}
						
			}
			else
			{
				$message = array("message" => __('invalid_user'),"status"=>-1);	
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$validator,$result,$profile_image);
		break;		

		case 'edit_passenger_profile':
			$p_personal_array = $mobiledata;
			if( count($p_personal_array) > 0 )
			{
				if($p_personal_array['email'] != null)
				{
					$p_email = urldecode($p_personal_array['email']);
					$country_code = $p_personal_array['country_code'];
					$p_phone = urldecode($p_personal_array['phone']);
					$passenger_id = $p_personal_array['passenger_id'];
					$password = urldecode($p_personal_array['password']);
					$validator = $this->edit_passenger_profile_validation($p_personal_array);
					
					if($validator->check())
					{			
					   $email_exist = $api->edit_check_email_passengers($p_email,$passenger_id,$default_companyid);
					   $phone_exist = $api->edit_check_phone_passengers($p_phone,$passenger_id,$default_companyid,$country_code);
					   
						if($email_exist > 0)
						{
							$message = array("message" => __('email_exists'),"status"=> 3);
						}
						else if($phone_exist > 0)
						{
							$message = array("message" => __('phone_exists'),"status"=> 2);
						}
						else
						{	
							if($p_personal_array['profile_image'] != "")
							{							
								/* Profile Update */
								$imgdata = base64_decode($p_personal_array['profile_image']);
								$f = finfo_open();
								$mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
								$mime_type = explode('/',$mime_type);
								$mime_type = $mime_type[1];
								$img = imagecreatefromstring($imgdata); 
								if($img != false)
								{                   
									// get prev image
									$result = $api->passenger_profile($p_personal_array['passenger_id'],'A');
									if(count($result) >0)
									{
										$profile_picture = $result[0]['profile_image'];
										if($profile_picture != "")
										{
											$main_image_path = $_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.$profile_picture;
											$thumb_image_path = $_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$profile_picture;
											if(file_exists($main_image_path) &&($profile_picture != ""))
											{
												unlink($main_image_path);
											}
											if(file_exists($thumb_image_path) &&($profile_picture != ""))
											{
												unlink($thumb_image_path);
											}
										}
									}										
									$image_name = uniqid().'.'.$mime_type;
									$thumb_image_name = 'thumb_'.$image_name;
									$image_url = DOCROOT.PASS_IMG_IMGPATH.'/'.$image_name;				
									$image_path = DOCROOT.PASS_IMG_IMGPATH.$image_name;  
									imagejpeg($img,$image_url);
									imagedestroy($img);
									chmod($image_path,0777);
									$d_image = Image::factory($image_path);
									$path11=DOCROOT.PASS_IMG_IMGPATH;
									Commonfunction::imageoriginalsize($d_image,$path11,$image_name,90);
									$path12=$thumb_image_name;
									Commonfunction::imageoriginalsize($d_image,$path11,$thumb_image_name,90);
									if($password != "")
									{
										$update_array = array(							
											"salutation"=>urldecode($p_personal_array['salutation']),
											"name" => urldecode($p_personal_array['firstname']),
											"lastname" => urldecode($p_personal_array['lastname']),
											"email" => $p_email,
											"country_code" => $country_code,
											"phone" => $p_phone,
											"password" => md5($password),
											"profile_image" => $image_name
										);
									}
									else
									{
										$update_array = array(								
											"salutation"=>urldecode($p_personal_array['salutation']),
											"name" => urldecode($p_personal_array['firstname']),
											"lastname" => urldecode($p_personal_array['lastname']),
											"email" => $p_email,
											"country_code" => $country_code,
											"phone" => $p_phone,
											"profile_image" => $image_name
										);
									}	
									$message = $api->edit_passenger_personaldata($update_array,$passenger_id,$default_companyid);
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
									"salutation"=>urldecode($p_personal_array['salutation']),
									"name" => urldecode($p_personal_array['firstname']),
									"lastname" => urldecode($p_personal_array['lastname']),
									"email" => $p_email,
									"country_code" => $country_code,
									"phone" => $p_phone,
									"password" => md5($password));
								}
								else
								{
									$update_array = array(
										"salutation"=>urldecode($p_personal_array['salutation']),
										"name" => urldecode($p_personal_array['firstname']),
										"lastname" => urldecode($p_personal_array['lastname']),
										"email" => $p_email,
										"country_code" => $country_code,
										"phone" => $p_phone
									);
								}
								$message = $api->edit_passenger_personaldata($update_array,$passenger_id,$default_companyid);
							}
							/*****************************************/												
							if($message == 0)
							{
								$passenger_details = $api->passenger_profile($p_personal_array['passenger_id'],'A');
								if((!empty($passenger_details[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$passenger_details[0]['profile_image']))
								{ 
									$profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$passenger_details[0]['profile_image']; 
								} else { 
									$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
								}
								/* create user logs */
			                    $user_unique = $p_personal_array['passenger_id'].__('log_passenger_type');
			                    $log_array = array(
			                        'user_id' => (int)$p_personal_array['passenger_id'],
			                        'user_type' => __('log_passenger_type'),
			                        'login_type' => __('log_device'),
			                        'activity' => __('log_profile_update'),
			                        'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
			                    );
			                    commonfunction::save_user_logs($log_array, $user_unique);
			                    /* create user logs */
								$message = array("message" => __('personal_updated'),"profile_image"=>$profile_image,"status"=>1);	
							}	
							if($message == -1)
							{
								$message = array("message" => __('try_again'),"status"=>-1);	
							}											
						}
					}
					else
					{							
						$validation_error = $validator->errors('errors');	
						$message = array("message" => __('validation_error'),"detail"=>$validation_error,"status"=>-3);	
					}
				}
				else
				{
					$message = array("message" => __('invalid_email'),"status"=>-4);	
				}
			}
			else
			{
				$message = array("message" => __('try_again'),"status"=>-5);	
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$validation_error,$result,$profile_image,$update_array,$profile_picture,$main_image_path,$thumb_image_path,$validator,$phone_exist,$email_exist,$mime_type);
		break;

		case 'chg_password_passenger':
			$p_chg_pass_array = $mobiledata;			
			if(!empty($p_chg_pass_array))
			{
				if($p_chg_pass_array['id'] != null)
				{
					$validator = $this->chg_password_passenger_validation($p_chg_pass_array);						
					if($validator->check())
					{
						$message = $api->chg_password_passenger($p_chg_pass_array,$default_companyid,'P');								
						switch($message)
						{
							case -1 :
								$message = array("message" => __('confirm_new_same'),"status"=>-1);	
								break;
							case -2 :
								$message = array("message" => __('old_pass_incorrect'),"status"=>-2);
								break;
							case -3 :
								$message = array("message" => __('invalid_user'),"status"=>-3);
								break;
							case 1 :
								/* create user logs */
			                    $user_unique = $p_chg_pass_array['id'].__('log_passenger_type');
			                    $log_array = array(
			                        'user_id' => (int)$p_chg_pass_array['id'],
			                        'user_type' => __('log_passenger_type'),
			                        'login_type' => __('log_device'),
			                        'activity' => __('log_change_pwd'),
			                        'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
			                    );
			                    commonfunction::save_user_logs($log_array, $user_unique);
			                    /* create user logs */
								$message = array("message" => __('password_changed'),"status"=>1);	
							break;
							case -4 :
								$message = array("message" => __('old_new_pass_same'),"status"=>-4);	
								break;
							}
						}
						else
						{							
							$validation_error = $validator->errors('errors');	
							$message = array("message" => $validation_error,"status"=>-3);							
						}
					}
					else
					{
						$message = array("message" => __('invalid_user'),"status"=>0);	
					}
			}
			else
			{
				$message = array("message" => __('invalid_request'),"status"=>-6);	
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$validator);
		break;

		case 'cancel_trip':	
			$driver_model = Model::factory('driver');
			$api_model = Model::factory(MOBILEAPI_107);	
			$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);	
			$cancel_trip_array = ($mobiledata) ? $mobiledata : $_POST;		
			$passenger_log_id = $cancel_trip_array['passenger_log_id'];
			$remarks = $cancel_trip_array['remarks'];

			$check_travelstatus = $api_ext->check_travelstatus($passenger_log_id);
			if($check_travelstatus == -1)
			{
				$message = array("message" => __('invalid_trip'),"status"=>3);
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				break;
			}
			if($check_travelstatus == 4)
			{
				$message = array("message" => __('trip_already_canceled'), "status"=>-1);
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				break;
			}			
			if($check_travelstatus == 2)
			{
				$message = array("message" => __('passenger_in_journey'), "status"=>-1);
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				break;
			}
			
			$flag = 1;
			$trans_result = $api_ext->check_tranc($passenger_log_id,$flag);
			if($trans_result == 1)
			{
				$message = array("message" => __('trip_fare_already_updated'), "status"=>-1);
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				break;
			}

			if($cancel_trip_array['passenger_log_id'] != null)
			{
				$get_passenger_log_det = $api_ext->get_passenger_log_detail($passenger_log_id);
				$driver_id = $get_passenger_log_det[0]->driver_id;
				$passenger_id = $get_passenger_log_det[0]->passengers_id;
				$passenger_name = $get_passenger_log_det[0]->passenger_name;
				$passenger_email = $get_passenger_log_det[0]->passenger_email;
				$pickup_location = $get_passenger_log_det[0]->current_location;
				$is_split_trip = $get_passenger_log_det[0]->is_split_trip;
				$wallet_amount = $get_passenger_log_det[0]->wallet_amount;
				$company_id = $get_passenger_log_det[0]->company_id;
				$taxi_model = isset($get_passenger_log_det[0]->taxi_model)?$get_passenger_log_det[0]->taxi_model:'';
				$cancel_trip_array['company_id'] = $get_passenger_log_det[0]->company_id;
				
				$cancellation_nfree = (FARE_SETTINGS == 2) ? $get_passenger_log_det[0]->cancellation_nfree : CANCELLATION_FARE;
				$status = "F";
				if(!empty($driver_id))
					$result = $api_model->update_driver_status($status,$driver_id);
				
				if($cancellation_nfree == 0 || empty($driver_id))
				{
					if(SMS == 1 && !empty($passenger_id))
					{
						$message_details = $this->commonmodel->sms_message_by_title('trip_cancel');
						if(count($message_details) > 0) {
							$to = $this->commonmodel->getuserphone('P',$passenger_email);
							$message = $message_details[0]['sms_description'];
							$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
							$this->commonmodel->send_sms($to,$message);
						}
					}
					/* create user logs */
			        $user_unique = $passenger_id.__('log_passenger_type');
			        $log_array = array(
		                'user_id' => (int)$passenger_id,
		                'user_type' => __('log_passenger_type'),
		                'login_type' => __('log_device'),
		                'activity' => __('log_passenger_cancel_trip'),
		                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
		            );
			        commonfunction::save_user_logs($log_array, $user_unique);
			        /* create user logs */
					$payment_types=0;
					$transaction_detail=$api_model->cancel_triptransact_details($cancel_trip_array,$cancellation_nfree,$payment_types,$driver_id);
					$pushmessage = array("message"=>__('trip_cancelled_passenger'), "status"=>2);
					$d_device_token = $get_passenger_log_det[0]->driver_device_token;
					$d_device_type = $get_passenger_log_det[0]->driver_device_type;
					$message = array("message" => __('trip_cancel_passenger'),"cancellation_from"=> __('Free'),"cancellation_amount"=> 0, "status"=>2);	//with out cancellation fee
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					
				}
				else
				{
					$total = $api_ext->get_passenger_cancel_faredetail($passenger_log_id,$taxi_model);
					$passengerReferrDet = $api_ext->check_passenger_referral_amount($passenger_id);
					$referralAmt = (isset($passengerReferrDet[0]['referral_amount'])) ? $passengerReferrDet[0]['referral_amount'] : 0;
					$reducAmt = ($referralAmt != 0) ? ($wallet_amount - $referralAmt) : $wallet_amount;
					if($cancel_trip_array['pay_mod_id'] == 3 || ($wallet_amount > 0 && $reducAmt >= $total)) // By cash
					{
						try 
						{
							$siteinfo_details = $api_ext->siteinfo_details();
								$update_commission = $this->commonmodel->update_commission($passenger_log_id,$total,$siteinfo_details[0]['admin_commission']);
							$total = (empty($total)) ? 0 : $total;
							$datas = array(
								"passengers_log_id" => $passenger_log_id,
								"remarks"		=> $remarks,
								"payment_type"		=> $cancel_trip_array['pay_mod_id'],
								"amt"			=> $total,
								"fare"			=> $total,
								"admin_amount"		=> $update_commission['admin_commission'],
									"company_amount"	=> $update_commission['company_commission'],
									"trans_packtype"	=> $update_commission['trans_packtype']
								);
							$transaction = $api_ext->insert_transactioncoll($insert_array);
							
							$datas  = array("travel_status" => '4'); // Passenger Cancelled
							$result_sts_update = $api_ext->update_passengerlogs($datas, $passenger_log_id);
							$cancel_from = __('Cash');
							//to reduce the wallet amount while cancelling the trip
							if($wallet_amount >= $total)
							{
								$balance_wallet_amount = $wallet_amount - $total;
								$datas = array("wallet_amount" => $balance_wallet_amount);
								$wallet_update = $api_ext->update_passengers($datas, $passenger_id);
								$cancel_from = __('Wallet');
							}
							
							if(SMS == 1 && !empty($passenger_id))
							{
								$message_details = $this->commonmodel->sms_message_by_title('trip_cancel');
								if(count($message_details) > 0) 
								{
									$to = $this->commonmodel->getuserphone('P',$passenger_email);
									$message = $message_details[0]['sms_description'];
									$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
									$this->commonmodel->send_sms($to,$message);
								}
							}
							/* create user logs */
					        $user_unique = $passenger_id.__('log_passenger_type');
					        $log_array = array(
				                'user_id' => (int)$passenger_id,
				                'user_type' => __('log_passenger_type'),
				                'login_type' => __('log_device'),
				                'activity' => __('log_passenger_cancel_trip'),
				                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
				            );
					        commonfunction::save_user_logs($log_array, $user_unique);
					        /* create user logs */
							$pushmessage = array("message"=>__('trip_cancelled_passenger'), "status"=>2);
							$d_device_token = $get_passenger_log_det[0]->driver_device_token;
							$d_device_type = $get_passenger_log_det[0]->driver_device_type;
							$message = array("message" => __('trip_cancel_passenger'),"cancellation_from"=> $cancel_from,"cancellation_amount"=> $total, "status"=>1);
						}
						catch (Kohana_Exception $e) {
							$message = array("message" => __('try_again'), "status"=>3);
						}
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					}
					else
					{							
						$card_type = '';
						$default = 'yes';
						$carddetails = $api_ext->get_creadit_card_details($passenger_id,$card_type,$default);
						$no_default_card = $api_ext->get_creadit_card_details($passenger_id,$card_type,"");
						if(count($carddetails)>0)
						{	
							$payment_status = $this->cancel_trippayment($cancel_trip_array,$cancellation_nfree,$default_companyid);
							
							//$cancelArr = ($payment_status != 0) ? explode("#",$payment_status):'';
                            $cancelArr = explode("#",$payment_status);
							$payment_status = isset($cancelArr[0]) ? $cancelArr[0] : 0;
							$cancelAmount = isset($cancelArr[1]) ? $cancelArr[1] : 0;
							if($payment_status == 0)
							{									
								$gateway_response = isset($cancelAmount)?$cancelAmount:__('cancel_payment_failed');
								$message = array("message" => $gateway_response, "gateway_response" =>$gateway_response,"status"=>0);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								break;
							}
							else if($payment_status == 1)
							{
								// version 6.2.3 update
								$void_transaction_trip=$api->voidTransaction_for_trip($passenger_log_id);
								if(SMS == 1 && !empty($passenger_id))
								{		
									$message_details = $this->commonmodel->sms_message_by_title('trip_cancel');
									if(count($message_details) > 0) 
									{
										$to = $this->commonmodel->getuserphone('P',$passenger_email);
										$message = $message_details[0]['sms_description'];
										$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
										$this->commonmodel->send_sms($to,$message);
									}
								}
								/* create user logs */
						        $user_unique = $passenger_id.__('log_passenger_type');
						        $log_array = array(
					                'user_id' => (int)$passenger_id,
					                'user_type' => __('log_passenger_type'),
					                'login_type' => __('log_device'),
					                'activity' => __('log_passenger_cancel_trip'),
					                'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
					            );
						        commonfunction::save_user_logs($log_array, $user_unique);
						        /* create user logs */
								$message = array("message" => __('trip_cancel_passenger'),"cancellation_from"=> __('credit_card'),"cancellation_amount"=> $cancelAmount, "status"=>1);
								$pushmessage = array("message"=>__('trip_cancelled_passenger'), "status"=>2);
								$d_device_token = $get_passenger_log_det[0]->driver_device_token;
								$d_device_type = $get_passenger_log_det[0]->driver_device_type;
								$send_mail_status = $this->send_cancel_fare_mail_passenger($cancelAmount, $passenger_name, $pickup_location, $passenger_email,$this->email_lang);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);				
							}
							else if($payment_status == -1)
							{
								$message = array("message" => __('invalid_trip'),"status"=>3);	
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								break;
							}
						} 
						else if (count($carddetails) == 0 && count($no_default_card) > 0) 
						{
							$message = array("message" => __('passenger_has_no_default_creditcard'),"status"=>5);	
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);		
							break;	
						} 
						else 
						{
							$message = array("message" => __('cancel_no_creditcard'),"status"=>4);	
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);		
							break;				
						}
					}
				}
			}
			else
			{
				$message = array("message" => __('invalid_trip'),"status"=>3);	
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				break;
			}				
			//unset($message,$detail,$api_model,$extended_api,$transaction_detail,$get_passenger_log_det);
		break;

		case 'update_ratings_comments':
			$rating_array = $mobiledata;
			if(!empty($rating_array['pass_id']))
			{
				$validator = $this->update_ratings_comments_validation($rating_array);
				
				if($validator->check()) 
				{
					$pass_id= $rating_array['pass_id'];
					$ratings = $rating_array['ratings'];
					$comments = urldecode($rating_array['comments']);						
					$fav_driver_id = $api->savecomments($pass_id,$ratings,$comments);
					$setFavDriver = ($ratings < 4) ? 2 : 1;
					$message = ($ratings < 4) ? __('rate_comment_updated') : __('rate_msg_with_set_favorite');
					$message = array("message" => $message,"set_fav_driver" => $setFavDriver,"fav_driver_id" => $fav_driver_id,"status"=>1);
				}	
				else
				{							
					
					$errors = $validator->errors('errors');		
					$message = array("message" => __('validation_error'),"detail"=>$errors,"status"=>-2);	
				}									
			}
			else
			{
				$message = array("message" => __('invalid_user'),"status"=>-1);	
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$validator,$fav_driver_id);
		break;

		case 'set_favourite_driver':
			if(!empty($mobiledata['passenger_id']) && !empty($mobiledata['driver_id'])) {
				$chkdriveradded = $api->chkDriverAdded($mobiledata['passenger_id'],$mobiledata['driver_id']);
				if($chkdriveradded == 0) {
					$api->saveFavouriteDriver($mobiledata['passenger_id'],$mobiledata['driver_id']);
					$message = array("message" => __('fav_driver_saved'),"status"=>1);
				} else {
					$message = array("message" => __('fav_driver_added_already'),"status"=>-1);	
				}
			}
			else
			{
				$message = array("message" => __('invalid_request'),"status"=>-1);	
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$chkdriveradded);
		break;

		//Passenger Signup with Referral code concept
		case 'passenger_signup_single':
			$p_first_name = (isset($mobiledata['first_name'])) ? urldecode($mobiledata['first_name']) : '';
		   $p_last_name = (isset($mobiledata['last_name'])) ? urldecode($mobiledata['last_name']) : '';
		   $p_email = (isset($mobiledata['email'])) ? urldecode($mobiledata['email']) : '';
		   $p_phone = (isset($mobiledata['phone'])) ? urldecode($mobiledata['phone']) : '';
		   $country_code = (isset($mobiledata['country_code'])) ? $mobiledata['country_code'] : '';
		   $p_password = (isset($mobiledata['password'])) ? urldecode($mobiledata['password']) : '';
		   $p_confirm_password = (isset($mobiledata['confirm_password'])) ? urldecode($mobiledata['confirm_password']) : '';
		   $devicetoken = (isset($mobiledata['devicetoken'])) ? urldecode($mobiledata['devicetoken']) : '';
		   $device_id = (isset($mobiledata['deviceid'])) ? urldecode($mobiledata['deviceid']) : '';
		   $devicetype = (isset($mobiledata['devicetype'])) ? urldecode($mobiledata['devicetype']) : '';	
		   $accessToken = (isset($mobiledata['accesstoken'])) ? urldecode($mobiledata['accesstoken']) : '';
		   $uid = (isset($mobiledata['userid'])) ? urldecode($mobiledata['userid']) : '';				   
		   $referral_code = (isset($mobiledata['referral_code'])) ? urldecode($mobiledata['referral_code']) : '';
		   $p_acc_validator = $this->pasenger_signup_validation($mobiledata);
		   if($p_acc_validator->check())
		   {
				$email_exist = $api->check_email_passengers($p_email,$default_companyid);
				$phone_exist = $api_ext->check_phone_passengers($p_phone,$default_companyid,$country_code);
					
				# referral code / promo code validation
				if($referral_code != '')
					$referralcode_exist = $api->check_referral_code_exist($mobiledata);
				
				//~ echo $referralcode_exist;exit;
				if($email_exist > 0)
				{
					//~ $message = array("message" => __('email_exists'),"status"=> 2);
					$message = array("message" => __('signin_email_proceed'),"status"=> 2);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				}
				else if($phone_exist > 0)
				{
					//~ $message = array("message" => __('phone_exists'),"status"=> 3);
					$message = array("message" => __('signin_phone_proceed'),"status"=> 3);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				}
				
				if(!empty($referral_code))
				{
					$promo_msg = ['0' => __('referral_code_not_exists'), 
								'-1' => __('invalid_promocode'), 
								'-2' => __('promo_already_used')];
						
					if(array_key_exists($referralcode_exist, $promo_msg)){
						$promotion_msg = $promo_msg[$referralcode_exist];
						$message = array("message" => $promotion_msg ,"status"=> 5);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					}
				}
				
				$image_name = '';
				if($uid != '') {
					//to get profile image from facebook and store it passenger
					$thumb_image = file_get_contents("http://graph.facebook.com/".$uid."/picture?width=".PASS_THUMBIMG_WIDTH1."&height=".PASS_THUMBIMG_HEIGHT1."");
					$thumb_image_name =  'thumb_'.$uid.'.jpg';
					$thumb_image_path = DOCROOT.PASS_IMG_IMGPATH.$thumb_image_name; 
					@chmod(DOCROOT.PASS_IMG_IMGPATH,0777);
					@chmod($thumb_image_path,0777);
					file_put_contents($thumb_image_path, $thumb_image);

					$edit_image = file_get_contents("http://graph.facebook.com/".$uid."/picture?width=".PASS_THUMBIMG_WIDTH1."&height=".PASS_THUMBIMG_HEIGHT1."");
					$edit_image_name =  'edit_'.$uid.'.jpg';
					$edit_image_path = DOCROOT.PASS_IMG_IMGPATH.$edit_image_name; 
					@chmod(DOCROOT.PASS_IMG_IMGPATH,0777);
					@chmod($edit_image_path,0777);
					file_put_contents($edit_image_path, $edit_image);

					/** Big Image **/
					$big_image = file_get_contents("http://graph.facebook.com/".$uid."/picture?width=".PASS_IMG_WIDTH."&height=".PASS_IMG_HEIGHT."");
					$image_name =  $uid.'.jpg';
					$big_image_path = DOCROOT.PASS_IMG_IMGPATH.$image_name; 
					@chmod(DOCROOT.PASS_IMG_IMGPATH,0777);
					@chmod($big_image_path,0777);
					file_put_contents($big_image_path, $big_image);

					$base_image = imagecreatefromjpeg($edit_image_path);
					$width = 100;
					$height = 19;
					$top_image = imagecreatefrompng(URL_BASE.PUBLIC_IMAGES_FOLDER."edit.png");
					$merged_image = DOCROOT.PASS_IMG_IMGPATH.'edit_'.$uid.'.jpg';
					imagesavealpha($top_image, true);
					imagealphablending($top_image, true);
					imagecopy($base_image, $top_image, 0, 83, 0, 0, $width, $height);
					imagejpeg($base_image, $merged_image);
				}
				/******/						
				$otp = text::random($type = 'numeric', $length = 4);
				$acc_details_result=$api->passenger_signup_with_referral($p_first_name, $p_last_name, $p_email, $p_phone, $country_code, $p_password, $p_confirm_password,$otp,$referral_code,$devicetoken,$device_id,$devicetype,$default_companyid,$accessToken,$uid,$image_name);
				
				if($acc_details_result == 1) 
				{ 							
					$mail="";
					$replace_variables=array(REPLACE_LOGO=>EMAILTEMPLATELOGO,
											REPLACE_SITENAME=>$this->app_name,
											REPLACE_USERNAME=>'',
											REPLACE_OTP=>$otp,
											REPLACE_SITELINK=>URL_BASE.'users/contactinfo/',
											REPLACE_SITEEMAIL=>$this->siteemail,
											REPLACE_SITEURL=>URL_BASE,
											REPLACE_COMPANYDOMAIN=>$this->domain_name,
											REPLACE_COPYRIGHTS=>SITE_COPYRIGHT,
											REPLACE_COPYRIGHTYEAR=>COPYRIGHT_YEAR);
						
					$emailTemp = $this->commonmodel->get_email_template('otp', $this->email_lang);
					if(isset($emailTemp['status']) && ($emailTemp['status'] == '1'))
					{						
						$email_description = isset($emailTemp['description']) ? $emailTemp['description']: '';
						$subject = isset($emailTemp['subject']) ? $emailTemp['subject']: '';
						$message           = $this->emailtemplate->emailtemplate($email_description, $replace_variables);
						$from              = CONTACT_EMAIL;
						$to = $p_email;
						//~ $subject = __('otp_subject')." - ".$this->app_name;
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
						$message_details = $this->commonmodel->sms_message_by_title('otp');
						if(count($message_details) > 0) 
						{
							$to = $country_code.$p_phone;
							$message = $message_details[0]['sms_description'];			
							# add link in otp message for ios
							$otp_device = isset($mobiledata['otp_devicetype']) ? $mobiledata['otp_devicetype'] : '';
							$otp_replace = ($otp_device == 2) ? $otp.' or Tap the link to auto update the otp TaxiOtp://'.$otp.'/ ' : $otp;
							$message = str_replace("##OTP##",$otp_replace,$message);												
							$message = str_replace("##SITE_NAME##",SITE_NAME,$message);								
							$this->commonmodel->send_sms($to,$message);
						}
					}
					
					$detail = array("email"=>$p_email,"phone"=>$p_phone,"skip_credit"=>SKIP_CREDIT_CARD);
					$message = array("message" =>__('account_save_otp'),"detail"=>$detail,"status"=> 1);
				}
				else
				{
					$message = array("message" => __('try_again'),"status"=> 4);
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				
		   }
		   else
		   {
				$errors = $p_acc_validator->errors('errors');
				$message = array("message"=>$errors,"status"=>-1);
				$mobile_data_ndot_crypt->encrypt_encode_json($message,$additional_param);
				exit;
			}		
			//unset($message,$result,$errors,$message_details,$acc_details_result,$thumb_image,$email_exist,$p_acc_validator,$phone_exist,$referralcode_exist);
		break;

		case 'passenger_mobile_check':
			if(!empty($mobiledata['phone'])) 
			{
				$getArr = 2;//param to get array of passenger details
				$passDetails = $api->checkpassengerPhonewithConcat($mobiledata['phone'],$default_companyid,$getArr);
				if(count($passDetails) > 0) 
				{
					$login_status = $passDetails[0]['login_status'];
					$creditcard_details = $passDetails[0]['creditcard_details'];							
					if($login_status == 'S') 
					{
						if($creditcard_details == 0) 
						{									
							$message = array("message" =>__('friend_donot_have_creditcard'),"detail"=>$passDetails,"status"=> 3);
						}
						else
						{							
							$checkPassTrip = $api->checkSecondPassengerinTrip($passDetails[0]['id']);
							if($checkPassTrip > 0)
							{
								$message = array("message" =>__('friend_in_trip'),"detail"=>$passDetails,"status"=> 3);
							} 
							else 
							{
								$message = array("message" =>__('friend_contact_online'),"detail"=>$passDetails,"status"=> 1);
							}
						}								
					} 
					else 
					{
						$message = array("message" =>__('friend_contact_notin_online'),"detail"=>$passDetails,"status"=> 0);
					}
				} else {
					$message = array("message" =>__('friend_contact_not_found'),"detail"=>$passDetails,"status"=> 2);
					//** SMS Section Starts **//
				}
			} else {
				$message = array("message" => __('invalid_request'),"status"=>-1);	
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$message_details,$result,$checkPassTrip,$passDetails);
		break;

		case 'otp_verify':
			$otp = isset($mobiledata['otp']) ? $mobiledata['otp'] : '';
			$email = isset($mobiledata['email']) ? $mobiledata['email'] : '';
			if(!empty($otp)) 
			{
				$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
				$otp_verification = $api->otp_verification($otp,$email);
				if($otp_verification > 0) 
				{
					$update_passenger_array  = array("user_status" => "A"); // activate user if the otp is valid
					$result = $api_ext->update_passengers_email($update_passenger_array,$email);
					$detail = array("email" => $email,"skip_credit" => SKIP_CREDIT_CARD);
					$message = array("message" =>__('signup_success'),"detail" => $detail,"status" => 1);
				} 
				else 
				{
					$message = array("message" => __('invalid_otp'),"status"=>-2);
				}
			} 
			else 
			{
				$message = array("message" => __('invalid_request'),"status"=>-1);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$result,$detail,$otp_verification);
		exit;

		case 'passenger_wallet':
			$passenger_id = isset($mobiledata['passenger_id']) ? $mobiledata['passenger_id'] : '';
			if(!empty($passenger_id)) 
			{
				$passenger_wallet = $api->get_passenger_wallet_amount($passenger_id);
				$siteInfo = $api_ext->siteinfo_details();
				$amount_details = array("wallet_amount1"=> (double)$siteInfo[0]['wallet_amount1'],
										"wallet_amount2"=> (double)$siteInfo[0]['wallet_amount2'],
										"wallet_amount3"=> (double)$siteInfo[0]['wallet_amount3'],
										"wallet_amount_range"=> $siteInfo[0]['wallet_amount_range']);
				if(count($passenger_wallet) > 0) {
					$message = array("wallet_amount" => $passenger_wallet[0]['wallet_amount'],"amount_details"=>$amount_details,"status"=>1);
				} else {
					$message = array("message" => __('invalid_user'),"status"=>-2);
				}
			} 
			else 
			{
				$message = array("message" => __('invalid_request'),"status"=>-1);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$passenger_wallet,$amount_details);
			exit;
		break;

		//api to check valid promocode
		case 'check_valid_promocode':
			$passenger_id = isset($mobiledata['passenger_id']) ? $mobiledata['passenger_id'] : '';
			$promo_code = isset($mobiledata['promo_code']) ? urldecode($mobiledata['promo_code']) : '';
			if(!empty($passenger_id) && !empty($promo_code)) 
			{
				$check_promo = $api->checkwalletpromocode($promo_code,$passenger_id,$default_companyid);
				if($check_promo == 0)
				{
					$message = array("message" => __('invalid_promocode_wallet'),"status" => 3);
				}
				else if($check_promo == 3)
				{
					$message = array("message" => __('promo_code_startdate'),"status" => 3);
				}
				else if($check_promo == 4)
				{
					$message = array("message" => __('promo_code_expired'),"status" => 3);
				}
				else if($check_promo == 2)
				{
					$message = array("message" => __('promo_code_limit_exceed'),"status" => 3);
				}
				else
				{
					$message = array("message" => __('promocode_valid'),"status" => 1);
				}
			} else {
				$message = array("message" => __('invalid_request'),"status"=>-1);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$check_promo,$promo_code);
			exit;
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
				$baseurl = URL_BASE."passengerapi113/index/";
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
				$baseurl = URL_BASE."passengerapi113/index/";
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
					if(in_array($checkRTL,$dynamic_language_array))
					{
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

		/** Help Comment update for a trip from passenger App **/
		case 'help_comment_update':
			$trip_id = isset($mobiledata['trip_id']) ? $mobiledata['trip_id'] : '';
			$help_id = isset($mobiledata['help_id']) ? $mobiledata['help_id'] : '';
			$help_comment = isset($mobiledata['help_comment']) ? $mobiledata['help_comment'] : '';
			if(!empty($help_id) && !empty($help_comment) && !empty($trip_id)) 
			{
				$updateTripComment = $api->updateTripComment($trip_id,$help_id,$help_comment);
				if($updateTripComment)
				{
					$message = array("message" => __('comment_post_success'), "status" => 1);
				} 
				else 
				{
					$message = array("message" => __('problem_post_comment'),"status" => -1);
				}
			} 
			else 
			{
				$message = array("message" => __('invalid_request'),"status" => -1);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message);
		break;

		/** Help content list in passenger app **/
		case 'help_content':
			$helpList = $api->getHelpContents();
			if(count($helpList) > 0)
			{					
				$result = array_map(
							function($helpList) {
								return array(
									'help_id' => $helpList['help_id'],
									'help_content' => __('help'.$helpList['help_id'])
								);
							}, $helpList);
				$message = array("message" => __('success'),"details" => $result, "status" => 1);
			} 
			else 
			{
				$message = array("message" => __('no_data'),"status" => -1);
			}
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message);
		break;

		case 'completed_journey_monthwise':
			$array = $mobiledata;				
			if($array['passenger_id'] != null)
			{
				$validator = $this->trip_history_month_wise($array);
				if($validator->check()) 
				{
					$userid= $array['passenger_id'];
					$start = $array['start'];
					$limit = $array['limit'];	
					$month = $array['month'];	
					$year = $array['year'];	
					$device_type = $array['device_type']; // 1 Android , 2 - IOS
					//Getting from Passenger Model Directly
					$passengers = Model::factory('passengers');
					// Booktype 0 -> Flagger Ride, 1-> Strret Ride, 2-> All
					$booktype="2";
					// all records from 1 month	//
					$fromdate = $year.'-'.$month.'-'.'01';
					$todate = date('Y-m-t', strtotime($fromdate));
					$cmonth = $year.'-'.$month;
					$arraydetails = array();
					$alldetails = array();
					$pagination = 1;
						
					$passengers_all_compl = $api->get_passengertrips_byfrmdate($pagination,$booktype,$userid,'1',$start,$limit,$cmonth);
					
					if(count($passengers_all_compl) > 0)
					{
						foreach($passengers_all_compl as $result)
						{
							$arraydetails['trip_id'] = $result['trip_id'];
							$arraydetails['place'] = $result['place'];
							$arraydetails['booking_time'] = Commonfunction::getDateTimeFormat($result['pickup_time'],1);
							$arraydetails['pickup_time'] = ($result['actual_pickup_time'] != "0000-00-00 00:00:00" && $result['actual_pickup_time'] != "") ? Commonfunction::getDateTimeFormat($result['actual_pickup_time'],1) : Commonfunction::getDateTimeFormat($result['pickup_time'],1);
							$arraydetails['fare'] = Commonfunction::amount_indecimal($result['fare'],'api');
							$arraydetails['pickup_latitude'] = $result['pickup_latitude'];
							$arraydetails['pickup_longitude'] = $result['pickup_longitude'];
							$arraydetails['drop_latitude'] = $result['drop_latitude'];
							$arraydetails['drop_longitude'] = $result['drop_longitude'];
							$arraydetails['notes_driver'] = $result['notes_driver'];
							$arraydetails['drivername'] = $result['drivername'];
							$arraydetails['drop_location'] = $result['drop_location'];
							$arraydetails['createdate'] = Commonfunction::getDateTimeFormat($result['createdate'],1);
							$arraydetails['profile_image'] = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
							if(!empty($result['profile_image']) && file_exists(DOCROOT.SITE_DRIVER_IMGPATH.$result['profile_image'])) 
							{
								$arraydetails['profile_image'] = URL_BASE.SITE_DRIVER_IMGPATH.$result['profile_image'];
							}
							$arraydetails['taxi_no'] = $result['taxi_no'];
							$arraydetails['travel_status'] = $result['travel_status'];
							$arraydetails['payment_type'] = $result['payment_type'];
							$arraydetails['model_name'] = $result['model_name'];
							$arraydetails['driver_confirm'] = 1;
							$date = $result['pickup_time'];
									
							if(file_exists(DOCROOT.MOBILE_COMPLETE_TRIP_MAP_IMG_PATH.$result['trip_id'].".png")) {
								$mapurl = URL_BASE.MOBILE_COMPLETE_TRIP_MAP_IMG_PATH.$result['trip_id'].".png";
							} else {
								$path = $result['active_record'];
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
										//~ $polylineEncoder->addPoint($values[0],$values[1]);
										//~ $polylineEncoder->encodedString();
										if(isset($values[0]) && isset($values[1])){ 
											$polylineEncoder->addPoint($values[0],$values[1]);
											$polylineEncoder->encodedString();
										}
									}
								}
								$encodedString = $polylineEncoder->encodedString();
								
								$marker_end = $result['drop_latitude'].','.$result['drop_longitude'];
								$marker_start = $result['pickup_latitude'].','.$result['pickup_longitude'];
								$startMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'startMarker.png';
								$endMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'endMarker.png';
								
								if($marker_end != 0) {
									$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x270&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&markers=icon:$endMarker%7C$marker_end&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
								} else {
									$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x270&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
								}
								if(isset($mapurl) && $mapurl != "") {
									$file_path = DOCROOT.MOBILE_COMPLETE_TRIP_MAP_IMG_PATH.$result['trip_id'].".png";
									file_put_contents($file_path,@file_get_contents($mapurl));
									$mapurl = URL_BASE.MOBILE_COMPLETE_TRIP_MAP_IMG_PATH.$result['trip_id'].".png";
								}
							}
							$arraydetails['map_image'] = $mapurl;
							$alldetails[] = $arraydetails;
						}
					}
				
				if(count($alldetails) > 0)
				{
					$message = array("message" =>__('success'),"trip_details" => $alldetails,"status"=>1,"site_currency"=> $this->site_currency);
				}
				else
				{
					$message = array("message" => __('no_completed_data_month'),"status"=>0,"site_currency"=>$this->site_currency);	
				}						
			}
			else
			{
				$errors = $validator->errors('errors');	
				$message = array("message" => __('validation_error'),"detail"=>$errors,"status"=>2,"site_currency"=>$this->site_currency);
			}							
		}
		else
		{
			$message = array("message" => __('invalid_user'),"status"=>-1,"site_currency"=>$this->site_currency);	
		}
		$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
		//unset($message,$alldetails,$arraydetails,$file_path,$mapurl,$startMarker,$endMarker,$passengers_all_compl);
		break;

		case 'booking_list':
			//Current Journey after driver confirmation //TN1013619352
			$array = $mobiledata;
			
			if($array['id'] != null)
			{
				$validator = $this->coming_cancel($array);
				if($validator->check()) 
				{
					$userid= $array['id'];
					$start = $array['start'];
					$limit = $array['limit'];
					$device_type = $array['device_type'];
					$check_result = $api->check_passenger_companydetails($array['id'],$default_companyid);
					if($check_result == 0)
					{
						$message = array("message" => __('invalid_user'),"status"=>-1);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						exit;
					}
					if($device_type == 1)
						$pagination = 1;
					else
						$pagination = 0;
					$passengers_trips = array();
					$pending_bookings = $api->get_pending_bookings($default_companyid,$pagination,$userid,'','A','0',$start,$limit);
					foreach($pending_bookings as $key => $val)
					{
						$pending_bookings[$key]['driver_confirm']=true;
						switch($val['travel_status'])
						{
							case 1:
								$pending_bookings[$key]['travel_msg']="Fare Updated";
							break;
							case 2:
								$pending_bookings[$key]['travel_msg']="Inprogress";
							break;
							case 3:
								$pending_bookings[$key]['travel_msg']="Arrived";
							break;
							case 5:
								$pending_bookings[$key]['travel_msg']="Completed";
							break;
							case 9:
								$pending_bookings[$key]['travel_msg']="Confirmed";
							break;
							case 0:
								$pending_bookings[$key]['travel_msg']="Not Confirmed";
								$pending_bookings[$key]['driver_confirm']=false;
							break;
							default:
								$pending_bookings[$key]['travel_msg']="Cancelled";
							break;
						}
						//to get the pickup time with required date format
						$pending_bookings[$key]['pickup_time'] = Commonfunction::getDateTimeFormat($val['pickuptime'],3);
						$pending_bookings[$key]['profile_image'] = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
						if(!empty($val['profile_image']) && file_exists(DOCROOT.SITE_DRIVER_IMGPATH.$val['profile_image'])) {
							$pending_bookings[$key]['profile_image'] = URL_BASE.SITE_DRIVER_IMGPATH.$val['profile_image'];
						}
						
						$pending_bookings[$key]['waiting_fare'] = "0";
								
						if(file_exists(DOCROOT.MOBILE_PENDING_TRIP_MAP_IMG_PATH.$val['passengers_log_id'].".png")) 
						{
							$mapurl = URL_BASE.MOBILE_PENDING_TRIP_MAP_IMG_PATH.$val['passengers_log_id'].".png";
						} 
						else 
						{									
							include_once MODPATH."/email/vendor/polyline_encoder/encoder.php";
							$polylineEncoder = new PolylineEncoder();
							$polylineEncoder->addPoint($val['pickup_latitude'],$val['pickup_longitude']);
							
							$marker_end = 0;
							if($val['drop_latitude'] != 0 && $val['drop_longitude'] != 0){
								$polylineEncoder->addPoint($val['drop_latitude'],$val['drop_longitude']);
								$marker_end = $val['drop_latitude'].','.$val['drop_longitude'];
							}
							$encodedString = $polylineEncoder->encodedString();
							$marker_start = $val['pickup_latitude'].','.$val['pickup_longitude'];
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
								$file_path = DOCROOT.MOBILE_PENDING_TRIP_MAP_IMG_PATH.$val['passengers_log_id'].".png";
								file_put_contents($file_path,@file_get_contents($mapurl));
								$mapurl = URL_BASE.MOBILE_PENDING_TRIP_MAP_IMG_PATH.$val['passengers_log_id'].".png";
							}
						}
						$pending_bookings[$key]['map_image'] = $mapurl;
					}
					$passengers_trips['pending_bookings']=$pending_bookings;
					if(count($passengers_trips) > 0)
					{
						$message = array("message" => __('success'),"detail"=>$passengers_trips,"status"=>1);
					}					
					else
					{
						$message = array("message" => __('no_data'),"status"=>0);
					}	
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
			//~ print_r($message);exit;
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$passengers_trips,$file_path,$mapurl,$startMarker,$endMarker,$pending_bookings,$check_result);
		break;

		case 'add_card_details':
			$p_card_array= $mobiledata;
			$creditcard_no = $p_card_array['creditcard_no'];
			$creditcard_cvv = $p_card_array['creditcard_cvv'];
			$expdatemonth = $p_card_array['expdatemonth'];
			$expdateyear = $p_card_array['expdateyear'];			
			$passenger_id = $p_card_array['passenger_id'];
			$default = $p_card_array['default'];
			$card_validation = $this->passenger_card_validation($p_card_array);
			$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
			if($card_validation->check())
			{			
				$authorize_status =$api_ext->isVAlidCreditCard($creditcard_no,"",true);
				if($authorize_status == 0)
				{
					$message = array("message" => __('invalid_card'),"status"=> 2);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					exit;
				}
				$card_exist = $api->check_card_exist($creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$passenger_id);
				
				if($card_exist > 0)
				{
					$message = array("message" => __('card_exist'),"status"=> 3);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					exit;
				}
				//Credit Card Pre authorization section goes here
				//preauthorization with amount "0"(Zero)
				$preAuthorizeAmount = PRE_AUTHORIZATION_REG_AMOUNT;
				
				//list($returncode,$paymentResult,$fcardtype,$preAuthorizeAmount) = $api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
				$paymentresponse=$api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
				$returncode=$paymentresponse['code'];
				$paymentResult=(isset($paymentresponse['TRANSACTIONID']) && ($paymentresponse['TRANSACTIONID']!=''))?$paymentresponse['TRANSACTIONID']:$paymentresponse['payment_response'];
				$fcardtype=isset($paymentresponse['cardType'])?$paymentresponse['cardType']:'';

				if($returncode==0)
				{
					//preauthorization with amount "1"
					$preAuthorizeAmount = PRE_AUTHORIZATION_RETRY_REG_AMOUNT;
					//list($returncode,$paymentResult,$fcardtype,$preAuthorizeAmount)= $api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
					$paymentresponse=$api->creditcardPreAuthorization($passenger_id,$creditcard_no,$creditcard_cvv,$expdatemonth,$expdateyear,$preAuthorizeAmount);
					$returncode=$paymentresponse['code'];
					$paymentResult=(isset($paymentresponse['TRANSACTIONID']) && $paymentresponse['TRANSACTIONID'] != '' )?$paymentresponse['TRANSACTIONID']:$paymentresponse['payment_response'];
					$fcardtype=isset($paymentresponse['cardType'])?$paymentresponse['cardType']:'';
				}
				//~ print_r($paymentresponse);exit;
				if($returncode != 0)
				{
					$result = $api->add_passenger_carddata($p_card_array,'',$paymentResult,$preAuthorizeAmount,$fcardtype);
					if($result) 
					{
                        $paymentresponse['preTransactAmount']=$preAuthorizeAmount;
						$void_transaction=$api->voidTransactionAfterPreAuthorize($result,$paymentresponse);
					}
				}
				else
				{
					$message=array("message"=> $paymentResult,"status"=>3);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					exit;
				}
				if($result > 0)
				{
					$message = array("message" => __('card_success'),"status"=>1);		
				}
				else
				{
					$message = array("message" => __('try_again'),"status"=>-1);	
				}
			
			}
			else
			{							
				$validation_error = $card_validation->errors('errors');	
				$message = array("message" => __('validation_error'),"detail"=>$validation_error,"status"=>-3);		
			}
			//~ print_r($message);exit;
			$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
			//unset($message,$validation_error,$result,$void_transaction,$card_validation,$returncode,$paymentResult,$fcardtype,$preAuthorizeAmount,$card_exist,$authorize_status,$card_exist);
			break;
			
			case 'passenger_logout':	
			$passenger_log_array = $mobiledata;		
					if($passenger_log_array['id'] != null)
					{
						$api_model = Model::factory(MOBILEAPI_107);	
						$update_id = $passenger_log_array['id'];
						$check_result = $api->check_passenger_companydetails($passenger_log_array['id'],$default_companyid);
						if($check_result == 0)	
						{
							$message = array("message" => __('invalid_user'),"status"=>-1);
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							exit;
						}

$user_unique = $passenger_log_array['id'].__('log_passenger_type');
//NOTIFICATION LOGGER -- START
$not_project=array();
$not_project['profile_image']=1;
$not_project['name']=1;
$not_match=array();
$not_match['_id']=(int)$passenger_log_array['id'];
$not_result=$this->commonmodel->dynamic_findone_new(MDB_PASSENGERS,$not_match,$not_project);
$not_name=isset($not_result['name'])?$not_result['name']:"";

$notification_content=array();
$notification_content['msg']=__('notification_logout_passenger',array(':username' => $not_name));
$notification_content['domain']=SUBDOMAIN_NAME;
$notification_content['image']=isset($not_result['profile_image'])?$not_result['profile_image']:"";
$notification_content['type']='PASSENGER_LOGOUT';
//NOTIFICATION LOGGER -- END

$log_array = array(
	'user_id' => (int)$passenger_log_array['id'],
	'user_type' => __('log_passenger_type'),
	'login_type' => __('log_device'),
	'activity' => __('log_logout'),
	'notification_content' => $notification_content,
	'notification_type' =>(int)1,
	'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
);
commonfunction::save_user_logs($log_array, $user_unique);


						$update_array  = array("login_from"=>"","login_status"=>"N","device_id" => "","device_token" => "","device_type" => "");
						$logout_status_update = $api_model->update_passengers($update_array,$update_id,$default_companyid);
						$delete_rejected_trips = $api_model->delete_rejected_trips($update_id,$company_all_currenttimestamp);
						$message = array("message" => __('logout_success'),"status"=>1);					
					}
					else
					{
						$message = array("message" => __('invalid_user'),"status"=>0);	
					}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);	
				//unset($message,$delete_rejected_trips,$logout_status_update,$check_result,$api_model);
				break;	
			
			case 'google_geocoding':
				$postData= $mobiledata;
				$key = GOOGLE_GEO_API_KEY;
				$origin = isset($postData['origin']) ? $postData['origin'] : '';	# 11.0317873,77.0186404
				$destination = isset($postData['destination']) ? $postData['destination'] : '';		#11.020983,76.9663344
				$geocoding_type = ['1','2'];	# 1-direction api, 2 -geocoding api
				$type = isset($postData['type']) ? $postData['type'] : '';
				
				# API hit function
				$googleApi = function($url)
				{
					$file = file_get_contents($url);
					$arr_conversion = json_decode($file);
					return $arr_conversion;
				};
				
				if($origin != '' && (in_array($type,$geocoding_type))){
					
					if($type == 1 && $destination == ''){
						$message = array("message" => __('invalid_request'),"status" => -1);          
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
						exit;
					}
					$direction_api = "https://maps.googleapis.com/maps/api/directions/json?origin=".$origin."&destination=".$destination."&sensor=false&mode=driving&alternatives=true&language=null";
					
					$geocoding_api = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$origin;
					
					$url = ($type == 1) ? $direction_api : $geocoding_api;
					$apiResponse = $googleApi($url,$key);
					$apiStatus = isset($apiResponse->status) ? $apiResponse->status:'';
					if($apiStatus == 'OK'){
						$mobile_data_ndot_crypt->encrypt_encode_json($apiResponse, $additional_param);
					}else{
						$key = GOOGLE_GEO_API_KEY;
						$apiResponse = $googleApi($url,$key);
						$mobile_data_ndot_crypt->encrypt_encode_json($apiResponse, $additional_param);
					}					
				}else{
					$message = array("message" => __('invalid_request'),"status" => -1);             
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);          
				}			
			break;
			
			case 'edit_favourite':
				$edit_fav_array = $mobiledata;
				$validator = $this->edit_favourite_validation($edit_fav_array);
				if($validator->check())
				{
					$favourite_id= $edit_fav_array['p_favourite_id'];
					$fav_comments = $edit_fav_array['fav_comments'];
					$passenger_id  = $edit_fav_array['passenger_id'];
					$p_favourite_place = urldecode($edit_fav_array['p_favourite_place']);
					$p_fav_latitude = $edit_fav_array['p_fav_latitude'];
					$p_fav_longtitute = $edit_fav_array['p_fav_longtitute'];
					$d_favourite_place = (isset($edit_fav_array['d_favourite_place'])) ? urldecode($edit_fav_array['d_favourite_place']) : '';
					$d_fav_latitude = (isset($edit_fav_array['d_fav_latitude'])) ? $edit_fav_array['d_fav_latitude'] : '';
					$d_fav_longtitute = (isset($edit_fav_array['d_fav_longtitute'])) ? $edit_fav_array['d_fav_longtitute'] : '';
					
					$p_fav_locationtype = urldecode($edit_fav_array['p_fav_locationtype']);
					$notes = isset($edit_fav_array['notes']) ? urldecode($edit_fav_array['notes']):"";
					//Set the Favourite Trips
					$check_fav_place = $api->check_fav_editplace($passenger_id,$p_favourite_place,$d_favourite_place,$favourite_id,$p_fav_locationtype);

					if($check_fav_place==0)
					{ 
						$check_fav_place_exist = $api->check_fav_editplacecheck($passenger_id,$p_favourite_place,$d_favourite_place,$favourite_id,$p_fav_locationtype);
						if($check_fav_place_exist==0)
						{
							$status = $api->edit_favourite($favourite_id,$p_favourite_place,$p_fav_latitude,$p_fav_longtitute,$d_favourite_place,$d_fav_latitude,$d_fav_longtitute,$fav_comments,$notes,$p_fav_locationtype);
							if($status)					
							{
								$image_name = uniqid().'.png';
								// Create directory if it does not exist							
								if(!is_dir(DOCROOT.MOBILE_FAV_LOC_MAP_IMG_PATH."passenger_". $passenger_id ."/")) { 
									mkdir(DOCROOT.MOBILE_FAV_LOC_MAP_IMG_PATH."passenger_". $passenger_id ."/",0777);
								}
															
								//Map image creation
								include_once MODPATH."/email/vendor/polyline_encoder/encoder.php";
								$polylineEncoder = new PolylineEncoder();
								$polylineEncoder->addPoint($p_fav_latitude,$p_fav_longtitute);
								
								$marker_end = 0;
								if($d_fav_latitude != 0 && $d_fav_longtitute != 0){
									$polylineEncoder->addPoint($d_fav_latitude,$d_fav_longtitute);
									$marker_end = $d_fav_latitude.','.$d_fav_longtitute;
								}
								$encodedString = $polylineEncoder->encodedString();
								$marker_start = $p_fav_latitude.','.$p_fav_longtitute;
								$startMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'startMarker.png';
								$endMarker = URL_BASE.PUBLIC_IMAGES_FOLDER.'endMarker.png';
								
								if($marker_end != 0) {
									$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x270&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&markers=icon:$endMarker%7C$marker_end&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
								} else {
									$mapurl = "https://maps.googleapis.com/maps/api/staticmap?size=640x270&zoom=13&maptype=roadmap&markers=icon:$startMarker%7C$marker_start&path=weight:3%7Ccolor:red%7Cenc:$encodedString";
								}
															
								if(isset($mapurl) && $mapurl != "") {
									$file_path = DOCROOT.MOBILE_FAV_LOC_MAP_IMG_PATH."passenger_". $passenger_id .'/'.$image_name;
									file_put_contents($file_path,@file_get_contents($mapurl));
									
									$update_image = $api->update_favourite_image($favourite_id,$image_name);
								}				
								$message = array("message" => __('edit_mark_fav'),"detail"=>"","status"=>1);
							}
							else
							{
								$message = array("message" => __('no_chage_made'),"status"=>0);	
							}

						}else{
							$message = array("message" => __('fav_already_exist'),"status"=>2);

						}	
					}else if($check_fav_place==-1){
						$message = array("message" => __('no_data'),"status"=>-3);

					}
					else
					{
						$message = array("message" => __('fav_already_exist_type'),"status"=>3);
					}										
				}
				else
				{
						$validation_error = $validator->errors('errors');
						$message = array("message" => __('validation_error'),"status"=>-3);	
				}
				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$validator,$validation_error,$check_fav_place_exist,$status,$check_fav_place);
				break;
				
				case 'resend_otp':
					$otp_array = $mobiledata;
					$email = $mobiledata['email'];
					$user_type = $otp_array['user_type'];
					if(isset($email))
					{
						$otp = text::random($type = 'numeric', $length = 4);
						$otp_result = $api->update_otp($otp_array,$otp,$default_companyid);
						if($otp_result == 1) 
						{
							$mail="";
							$replace_variables=array(REPLACE_LOGO=>EMAILTEMPLATELOGO,REPLACE_SITENAME=>$this->app_name,REPLACE_USERNAME=>'',REPLACE_OTP=>$otp,REPLACE_SITELINK=>URL_BASE.'users/contactinfo/',REPLACE_SITEEMAIL=>$this->siteemail,REPLACE_SITEURL=>URL_BASE,REPLACE_COMPANYDOMAIN=>$this->domain_name,REPLACE_COPYRIGHTS=>SITE_COPYRIGHT,REPLACE_COPYRIGHTYEAR=>COPYRIGHT_YEAR);

							/* Added for language email template */
							/*if($this->lang!='en')
							{
								if(file_exists(DOCROOT.TEMPLATEPATH.$this->lang.'/otp-'.$this->lang.'.html'))
								{
									$message=$this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.$this->lang.'/otp-'.$this->lang.'.html',$replace_variables);
								}
								else
								{
									$message=$this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.'otp.html',$replace_variables);
								}
							}
							else
							{
								$message=$this->emailtemplate->emailtemplate(DOCROOT.TEMPLATEPATH.'otp.html',$replace_variables);
							}*/

							/* Added for language email template */
							
							$emailTemp = $this->commonmodel->get_email_template('otp', $this->email_lang);
							if(isset($emailTemp['status']) && ($emailTemp['status'] == '1')){
								
								$email_description = isset($emailTemp['description']) ? $emailTemp['description']: '';
								$subject = isset($emailTemp['subject']) ? $emailTemp['subject']: '';
								$message           = $this->emailtemplate->emailtemplate($email_description, $replace_variables);
								$from              = CONTACT_EMAIL;
								$to = $email;
								/*if($user_type == 'D')
								{
									$subject = __('otp_driver_subject')." - ".$this->app_name;
								}
								else
								{
									$subject = $subject." - ".$this->app_name;	
								}*/
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
								if($otp_array['user_type']=='P')
								{
									$phoneno = $this->commonmodel->getuserphone('P',$email);
								}
								else
								{
									$phoneno = $this->commonmodel->getuserphone('D',$email);
								}
								$message_details = $this->commonmodel->sms_message_by_title('otp');
								if(count($message_details) > 0) {
									$to = $phoneno;
									$message = $message_details[0]['sms_description'];
									//~ $message = str_replace("##OTP##",$otp,$message);
									# add link in otp message for ios
									$otp_device = isset($otp_array['otp_devicetype']) ? $otp_array['otp_devicetype'] : '';
									$otp_replace = ($otp_device == 2) ? $otp.' or Tap the link to auto update the otp TaxiOtp://'.$otp.'/ ' : $otp;
									$message = str_replace("##OTP##",$otp_replace,$message);												
									$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
									$this->commonmodel->send_sms($to,$message);
								}
							}
							$detail = array("email"=>$email);
							$message = array("message" => __('resend_otp'),"detail"=>$detail,"status"=> 1);
						}
						else
						{
							$message = array("message" => __('try_again'),"status"=> 4);
						}
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);   
						exit;
					}
					else
					{
						$message = array("message"=>__('invalid_email'),"status"=>-1);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);   
						//unset($result);
						exit;
					}
					//unset($message,$result,$detail,$message_details,$replace_variables,$otp_result,$phone_exist);
					break;
				
		# New Passenger Login Screen API works
					
				# Passenger Signup with Phone number only
				case 'signupwith_phone':
					
					$postData = $mobiledata;			
					$validator = $this->passengerphone_validation($postData);
					if($validator->check())
					{ 		   
						$phone = (isset($postData['phone'])) ? $postData['phone'] : '';
						$device_type = (isset($postData['device_type'])) ? $postData['device_type'] : '';
						$cc = (isset($postData['country_code'])) ? $postData['country_code'] : '';
						$country_code = (substr($cc, 0, 1) === '+')?$cc:'+'.$cc;
						$fbuser_id = (isset($postData['fbuser_id'])) ? $postData['fbuser_id'] : '';
						$phoneVerify = $api->phonesignup_check($country_code,$phone);
						
						$phoneExist = $phoneVerify['phoneExist'];
						$passengerId = $phoneVerify['passengerId'];
						
						# $phoneExist [ 1 - Existing user | 0 - New User | 2 - Otp not verified | 3 - Singup incompleted]
						$userStatus_msg = [
							'0' => __('otpsent_phone'),
							'1' => __('phone_exists'),
							'2' => __('account_not_activated'),
							'3' => __('d_personal_data_not_filled')									
						];
		
						$responseMessage = $userStatus_msg[$phoneExist];
						$response =[];
						$response["status"] = 1;
						$response["phone_exist"] = $phoneExist;
						if($phoneExist == 0 || $phoneExist == 2){
							$responseMessage = __('otpsent_phone');
							$otp = text::random($type = 'numeric', $length = 4);
							# testing purpose
							$response['otp'] = $postData['otp'] = $otp;
							$dbResponse = $api->passenger_phonesignup($postData);
							if($dbResponse == 0){
								$response = array("message" => __('try_again'),"status"=> 4);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								exit;
							}else{
								if(SMS == 1)
								{							
									$message_details = $this->commonmodel->sms_message_by_title('otp');
									if(count($message_details) > 0) 
									{
										$to = $country_code.$phone;
										$message = $message_details[0]['sms_description'];			
										# add link in otp message for ios
										$otp_device = isset($mobiledata['otp_devicetype']) ? $mobiledata['otp_devicetype'] : '';
										$android_msg = ' '.$otp.' ';
										$ios_msg = $otp.' or Tap the link to auto update the otp TaxiOtp://'.$otp.'/ ';
										$otp_replace = ($device_type == 2) ? $ios_msg : $android_msg;
										$message = str_replace("##OTP##",$otp_replace,$message);												
										$message = str_replace("##SITE_NAME##",SITE_NAME,$message);							
										$this->commonmodel->send_sms($to,$message);
									}
								}
							}		
						}
						$response["detail"] = ['phone' => $phone,
											'country_code' => $country_code,
											'passenger_id' => $passengerId,
											'fbuser_id' => $fbuser_id];
						$response["message"] = $responseMessage;
						$mobile_data_ndot_crypt->encrypt_encode_json($response, $additional_param);
					}
					else
					{
						$validation_error = $validator->errors('errors');	
						$response = array("message" => __('validation_error'),"detail"=>$validation_error,"status"=>-1);
						$mobile_data_ndot_crypt->encrypt_encode_json($response, $additional_param);
					}
				break;
				
				case 'resend_phoneotp':
					$postData = $mobiledata;					
					$validator = $this->resendotp_validation($postData);
					if($validator->check())
					{ 	
						$phone = isset($postData['phone']) ? $postData['phone']:'';
						// $country_code = isset($postData['country_code']) ? $postData['country_code']:'';
						$cc = (isset($postData['country_code'])) ? $postData['country_code'] : '';
						$country_code = (substr($cc, 0, 1) === '+')?$cc:'+'.$cc;	
						$device_type = isset($postData['device_type']) ? $postData['device_type']:'';
						$otp = text::random($type = 'numeric', $length = 4);
						$otp_result = $api->update_newotp($otp,$phone);
						if($otp_result == 1) 
						{					
							if(SMS == 1)
							{
								$message_details = $this->commonmodel->sms_message_by_title('otp');
								if(count($message_details) > 0) {
									$to = $country_code.$phone;
									$message = $message_details[0]['sms_description'];
									# add link in otp message for ios
									$otp_device = isset($otp_array['otp_devicetype']) ? $otp_array['otp_devicetype'] : '';
									$android_msg = ' '.$otp.' ';
									$ios_msg = $otp.' or Tap the link to auto update the otp TaxiOtp://'.$otp.'/ ';
									$otp_replace = ($device_type == 2) ? $ios_msg : $android_msg;
									$message = str_replace("##OTP##",$otp_replace,$message);												
									$message = str_replace("##SITE_NAME##",SITE_NAME,$message);
									$this->commonmodel->send_sms($to,$message);
								}
							}
							$responseMessage = __('otpsent_phone');
							$response = array("message" => $responseMessage,"status"=> 1);
							# testing purpose
							$response['otp'] = $otp;
						}
						else
						{
							$response = array("message" => __('try_again'),"status"=> 4);
						}
						$mobile_data_ndot_crypt->encrypt_encode_json($response, $additional_param);   
					}
					else
					{
						$validation_error = $validator->errors('errors');	
						$response = array("message" => __('validation_error'),"detail"=>$validation_error,"status"=>-1);
						$mobile_data_ndot_crypt->encrypt_encode_json($response, $additional_param);   
					}
				break;
				
				
				case 'phoneotp_verify':
					$postData = $mobiledata;	
					$validator = $this->phoneotp_validation($postData);
					if($validator->check())
					{ 	
						$otp = isset($postData['otp']) ? $postData['otp'] : '';
						$phone = isset($postData['phone']) ? $postData['phone'] : '';
						// $country_code = isset($postData['country_code']) ? $postData['country_code'] : '';
						$cc = (isset($postData['country_code'])) ? $postData['country_code'] : '';
						$country_code = (substr($cc, 0, 1) === '+')?$cc:'+'.$cc;
						$fbuserId = isset($postData['fbuser_id']) ? $postData['fbuser_id'] : '';
						$otp_verification = $api->phoneotp_verification($otp,$country_code,$phone);
						if($otp_verification == 1) 
						{
							$update_passenger_array  = array("user_status" => "A"); // activate user if the otp is valid
							$passengerId = $api->update_passengers_phone($update_passenger_array,$postData);
							if($fbuserId != ''){
								$result = $api->passengerInfo($passengerId);
								if(count($result) > 0)
								{
									if((!empty($result[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'edit_'.$result[0]['profile_image'])){ 
										$edit_image = URL_BASE.PASS_IMG_IMGPATH.'edit_'.$result[0]['profile_image']; 
									}
									else{ 
										$edit_image = URL_BASE."public/images/edit_image.png";
									} 

									$result[0]['edit_image'] = $edit_image;

									if((!empty($result[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$result[0]['profile_image']))
									{ 
										$profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$result[0]['profile_image']; 
									}
									else{ 
										$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
									} 
									$total_array['id'] = $result[0]['id'];
									$total_array['name'] = $result[0]['name'];
									$total_array['email'] = $result[0]['email'];
									$total_array['profile_image'] = $profile_image;
									$total_array['country_code'] = $result[0]['country_code'];
									$total_array['phone'] = $result[0]['phone'];
									$total_array['login_from'] = $result[0]['login_from'];
									$total_array['referral_code'] = $result[0]['referral_code'];
									$total_array['referral_code_amount'] = $result[0]['referral_code_amount'];
									//this field is used to check whether the user logged in after forgot the password 0 - not forgot, 1- forgot
									$total_array['forgot_password'] = $result[0]['forgot_password'];
									$total_array['split_fare'] = $result[0]['split_fare'];
									$telltofriend_message = TELL_TO_FRIEND_MESSAGE;//str_replace("#REFDIS#",$ref_discount,$ref_message); 
									$total_array['telltofriend_message'] = $telltofriend_message;
									//Newly Added-13.11.2014
									$total_array['site_currency'] = $this->site_currency;
									$total_array['aboutpage_description'] = $this->app_description;
									$total_array['tell_to_friend_subject'] = __('telltofrien_subject');
									$check_card_data = isset($result[0]['creditcard_details']) ? count($result[0]['creditcard_details']) : 0;
									$credit_card_sts = ($check_card_data == 0) ? 0:SKIP_CREDIT_CARD;
									$total_array['credit_card_status'] = $credit_card_sts;
									$total_array['skip_favourite'] = $result[0]['skip_favourite'];
									$total_array['favourite_driver'] = $result[0]['favourite_driver'];
									/** function to update forgot_password status as 0 **/
									
									/* create user logs */
									# Notification Logger -- Start
									$not_project=array();
									$not_project['profile_image']=1;
									$not_project['name']=1;
									$not_match=array();
									$not_match['_id']=(int)$passengerId;
									$not_result=$this->commonmodel->dynamic_findone_new(MDB_PASSENGERS,$not_match,$not_project);
									$not_name = isset($not_result['name']) ? $not_result['name'] : "";
									$notification_content=array();
									$notification_content['msg']=__('notification_login_passenger',array(':username' => $not_name));
									$notification_content['domain']=SUBDOMAIN_NAME;
									$notification_content['image']=isset($not_result['profile_image'])?$not_result['profile_image']:"";
									$notification_content['type']='PASSENGER_LOGIN';
									# Notification Logger -- End
									$user_unique = $result[0]['id'].__('log_passenger_type');
									$log_array = array(
										'user_id' => (int)$result[0]['id'],
										'user_type' => __('log_passenger_type'),
										'login_type' => __('log_device'),
										'activity' => __('login_log'),
										'notification_content' => $notification_content,
										'notification_type' =>(int)1,
										'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
									);
									commonfunction::save_user_logs($log_array, $user_unique);
									/* create user logs */
									$message = array("message" =>__('signup_success'),"detail"=>$total_array,"status" => 1);		
									$mobile_data_ndot_crypt->encrypt_encode_json($message,$additional_param);
								}
							}else{
								$message = array("message" =>__('d_personal_data_not_filled'),"status" => 1);
								$message["detail"] = ['passenger_id' => $passengerId];
							}
						} 
						else 
						{
							$message = array("message" => __('invalid_otp'),"status"=>-2);
						}
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param); 
					} 
					else 
					{
						$validation_error = $validator->errors('errors');	
						$message = array("message" => __('validation_error'),"detail"=>$validation_error,"status"=>-1);
						$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param); 
					}
				break;
				
				#Passenger Signup final step
				case 'passenger_signup_completion':
					$postData = $mobiledata;	
					$validator = $this->signupcompletion_validation($postData);
					if($validator->check())
					{
						$email = (isset($postData['email'])) ? urldecode($postData['email']) : '';
						$passenger_id = (isset($postData['passenger_id'])) ? urldecode($postData['passenger_id']) : '';
						$referral_code = (isset($postData['referral_code'])) ? urldecode($postData['referral_code']) : '';
						$email_exist = $api->check_email_passengers($email,$default_companyid='');
						
						if($email_exist > 0)
						{
							$message = array("message" => __('email_exists'),"status"=> 2);
							$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
							exit;
						}
						
						# referral code / promo code validation
						if($referral_code != '')
						{
							$referralcode_exist = $api->check_referral_code_exist($postData);
							$promo_msg = ['0' => __('referral_code_not_exists'), 
										'-1' => __('invalid_promocode'), 
										'-2' => __('promo_already_used')];
								
							if(array_key_exists($referralcode_exist, $promo_msg)){
								$promotion_msg = $promo_msg[$referralcode_exist];
								$message = array("message" => $promotion_msg ,"status"=> 5);
								$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
								exit;
							}
						}
						# updating & fetching passenger details
						$result = $api->update_passengers_details($postData);
						$total_array = array();
						if(count($result) > 0)
						{
							if((!empty($result[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'edit_'.$result[0]['profile_image'])){ 
								$edit_image = URL_BASE.PASS_IMG_IMGPATH.'edit_'.$result[0]['profile_image']; 
							}
							else{ 
								$edit_image = URL_BASE."public/images/edit_image.png";
							} 

							$result[0]['edit_image'] = $edit_image;

							if((!empty($result[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$result[0]['profile_image']))
							{ 
								$profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$result[0]['profile_image']; 
							}
							else{ 
								$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
							} 
							$total_array['id'] = $result[0]['id'];
							$total_array['name'] = $result[0]['name'];
							$total_array['email'] = $result[0]['email'];
							$total_array['profile_image'] = $profile_image;
							$total_array['country_code'] = $result[0]['country_code'];
							$total_array['phone'] = $result[0]['phone'];
							$total_array['login_from'] = $result[0]['login_from'];
							$total_array['referral_code'] = $result[0]['referral_code'];
							$total_array['referral_code_amount'] = $result[0]['referral_code_amount'];
							//this field is used to check whether the user logged in after forgot the password 0 - not forgot, 1- forgot
							$total_array['forgot_password'] = $result[0]['forgot_password'];
							$total_array['split_fare'] = $result[0]['split_fare'];
							$telltofriend_message = TELL_TO_FRIEND_MESSAGE;//str_replace("#REFDIS#",$ref_discount,$ref_message); 
							$total_array['telltofriend_message'] = $telltofriend_message;
							//Newly Added-13.11.2014
							$total_array['site_currency'] = $this->site_currency;
							$total_array['aboutpage_description'] = $this->app_description;
							$total_array['tell_to_friend_subject'] = __('telltofrien_subject');
							$check_card_data = isset($result[0]['creditcard_details']) ? count($result[0]['creditcard_details']) : 0;
							$credit_card_sts = ($check_card_data == 0) ? 0:SKIP_CREDIT_CARD;
							$total_array['credit_card_status'] = $credit_card_sts;
							$total_array['skip_favourite'] = $result[0]['skip_favourite'];
							$total_array['favourite_driver'] = $result[0]['favourite_driver'];
							/** function to update forgot_password status as 0 **/
							
							/* create user logs */
							# Notification Logger -- Start
							$not_project=array();
							$not_project['profile_image']=1;
							$not_project['name']=1;
							$not_match=array();
							$not_match['_id']=(int)$passenger_id;
							$not_result=$this->commonmodel->dynamic_findone_new(MDB_PASSENGERS,$not_match,$not_project);
							$not_name = isset($not_result['name']) ? $not_result['name'] : "";
							$notification_content=array();
							$notification_content['msg']=__('notification_login_passenger',array(':username' => $not_name));
							$notification_content['domain']=SUBDOMAIN_NAME;
							$notification_content['image']=isset($not_result['profile_image'])?$not_result['profile_image']:"";
							$notification_content['type']='PASSENGER_LOGIN';
							# Notification Logger -- End
							$user_unique = $result[0]['id'].__('log_passenger_type');
							$log_array = array(
								'user_id' => (int)$result[0]['id'],
								'user_type' => __('log_passenger_type'),
								'login_type' => __('log_device'),
								'activity' => __('login_log'),
								'notification_content' => $notification_content,
								'notification_type' =>(int)1,
								'log_date' => Commonfunction::MongoDate(strtotime($this->currentdate))
							);
							commonfunction::save_user_logs($log_array, $user_unique);
							/* create user logs */
							$message = array("message" =>__('signup_success'),"detail"=>$total_array,"status" => 1);		
							$mobile_data_ndot_crypt->encrypt_encode_json($message,$additional_param);
						}
					}
					else
					{
						$validation_error = $validator->errors('errors');	
						$message = array("message" => __('validation_error'),"detail"=>$validation_error,"status"=>-1);
						$mobile_data_ndot_crypt->encrypt_encode_json($message,$additional_param);
					}		
				break;

				case 'gplus_connect':
				$array = $mobiledata;
				$uid = $array['guser_id'];
				$fname = $array['fname'];
				$lname = $array['lname'];
				$email = $array['fbemail'];
				$devicetoken = $array['devicetoken'];
				$device_id = $array['deviceid'];
				$devicetype = $array['devicetype'];
				$image_name = isset($array['image_name'])?$array['image_name']:'';

				/*************************/	
				$otp = text::random($type = 'alnum', $length = 5);
				$referral_code = strtoupper(text::random($type = 'alnum', $length = 6));
				$status = $api->register_gplus_user($uid,$otp,$referral_code,$fname,$lname,$email,$image_name,$devicetoken,$device_id,$devicetype,$default_companyid);
				//~ echo $status;exit;
				if($status == -10){
					$message = array("message" => __('passenger_blocked'),"status" => $status);
					$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
					break;
				}
				$passenger_details = $api->passenger_detailsbyemail($email,$default_companyid,$uid);	
				
				if((!empty($passenger_details[0]['profile_image'])) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PASS_IMG_IMGPATH.'thumb_'.$passenger_details[0]['profile_image']))
				{ 
					$profile_image = URL_BASE.PASS_IMG_IMGPATH.'thumb_'.$passenger_details[0]['profile_image']; 
				}
				else{ 
					$profile_image = URL_BASE.PUBLIC_IMAGES_FOLDER."no_image109.png";
				} 

				if(count($passenger_details) > 0){
					$passenger_details[0]['profile_image'] = $profile_image;
				}
				$api_ext = Model::factory(MOBILEAPI_107_EXTENDED);
				$config_array = $api_ext->select_site_settings($default_companyid);							
				$total_array = array();
				$result = $passenger_details;
				$fbemail = '';
				$skip_credit_card = 2;
				if(count($result) > 0)
				{
					
					$total_array['id'] = $result[0]['id'];
					$total_array['name'] = $result[0]['name'];
					$total_array['email'] = $result[0]['email'];
					$fbemail = $total_array['email'];
					$total_array['profile_image'] = $profile_image;
					$total_array['country_code'] = $result[0]['country_code'];
					$total_array['phone'] = $result[0]['phone'];
					$total_array['address'] = $result[0]['address'];
					$total_array['user_status'] = $result[0]['user_status'];
					$total_array['login_from'] = $result[0]['login_from'];
					$total_array['referral_code'] = $result[0]['referral_code'];
					$total_array['referral_code_amount'] = $result[0]['referral_code_amount'];
					$total_array['split_fare'] = $result[0]['split_fare'];
					//to check whether the passenger gave
					$skip_credit_card = $result[0]['skip_credit_card'];
					$telltofriend_message = TELL_TO_FRIEND_MESSAGE;//str_replace("#REFDIS#",$ref_discount,$ref_message); 
					$total_array['telltofriend_message'] = $telltofriend_message;
					
					//Newly Added-13.11.2014
					$total_array['site_currency'] = CURRENCY;
					$total_array['aboutpage_description'] = $this->app_description;
					$total_array['tell_to_friend_subject'] = __('telltofrien_subject');
					$total_array['skip_credit'] = SKIP_CREDIT_CARD;
					$total_array['metric'] = UNIT_NAME;
					$total_array['favourite_driver'] = $result[0]['favourite_driver'];
					$total_array['skip_favourite'] = $result[0]['skip_favourite'];
					//variable to know whether the passenger have credit card
					$check_card_data = $api_ext->check_passenger_card_data($result[0]['id']);
					$credit_card_sts = ($check_card_data == 0) ? 0 : SKIP_CREDIT_CARD;
					$total_array['credit_card_status'] = $credit_card_sts;
				}
									
				/***Get Company car model details start***/
				//$company_model_details = $api->company_model_details($default_companyid);
				$company_model_details = $api_ext->company_model_details($default_companyid);
				if(count($company_model_details)>0){
					$total_array['model_details']=$company_model_details;
				}else{
					$total_array['model_details']="model details not found";
				}
				/***Get Company car model details end***/
				
				if($status==1)
				{									
					$message = array("message" => __('succesful_login_flash'),"detail"=>$total_array,"status"=> 1); 
				}
				else if($status==2)
				{	
					$detail = array("email"=>$fbemail);														
					$message = array("message"=>__('account_saved_withoutmobile'),"detail"=>$detail,"status"=>2);					 
				}
				else if($status == -9)
				{	
					$message = array("message"=>__('succesful_login_flash'),"detail"=>$total_array,"status"=>1);													 
				}
				else if($status==4 || $status==3)
				{
					/*if(SKIP_CREDIT_CARD !=1 || $skip_credit_card != 1)
					{
						$message = array("message"=>__('p_card_data_not_filled'),"detail"=>$total_array,"status"=>4);	
					}
					else
					{
						$message = array("message" => __('succesful_login_flash'),"detail"=>$total_array,"status"=> 1);
					}*/
					$message = array("message" => __('succesful_login_flash'),"detail"=>$total_array,"status"=> 1);
				}
				else if($status==-2)
				{	
					$detail = array("email"=>$email);							 
					$message = array("message"=>__('account_not_activated'),"detail"=>$detail,"status"=>-2);													 
				}
				else if($status==10)
				{
					$message = array("message" => __('gplus_email_empty'),"status"=>10);
				}
				else
				{
					$message = array("message" => __('gplus_error'),"status"=>-1);
				}

				$mobile_data_ndot_crypt->encrypt_encode_json($message, $additional_param);
				//unset($message,$detail,$company_model_details,$total_array,$config_array,$passenger_details,$edit_image,$edit_image_path,$thumb_image,$thumb_image_path,$thumb_image_file,$image_file,$big_image,$big_image_path,$big_image_file,$base_image,$top_image,$merged_image,$status);
			break;
		}
	}


}
