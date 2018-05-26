<?php defined('SYSPATH') OR die('No direct access allowed.'); 
$controller = Request::initial()->controller();
$action = Request::initial()->action(); 
$session = Session::instance();
require('style_css.php');
?>

<script src="<?php echo URL_BASE;?>public/webbooking/js/owl.carousel.js"></script> 
<script src="https://maps.google.com/maps/api/js?key=<?php echo GOOGLE_MAP_API_KEY; ?>&libraries=places,geometry" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo SCRIPTPATH ?>gmaps.js"></script>
<script src="<?php echo URL_BASE; ?>public/country_code/build/js/jquery.ccpicker.js">
</script>
<link rel="stylesheet" href="<?php echo URL_BASE; ?>public/country_code/build/css/jquery.ccpicker.css" />
<link rel="stylesheet" href="<?php echo URL_BASE; ?>public/dispatch/vendor/bootstrap/css/bootstrap-datetimepicker.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>public/common/js/datetimehrspicker/jquery-ui-1.12.11.custom.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>public/common/js/datetimehrspicker/jquery-ui-timepicker-addon.min.css"/>
<script src="<?php echo URL_BASE; ?>public/common/js/datetimehrspicker/jquery-ui-1.12.11.custom.min.js"></script>
<script src="<?php echo URL_BASE; ?>public/common/js/jquery-ui-timepicker-addon.min.js"></script>
<script>
	var taxInclusive = '(<?php echo __("tax_inclusive"); ?>)';
	var taxExclusive = '(<?php echo __("tax_exclusive"); ?>)';
	
	$(document).ready(function(){
		
		
		var fbuser_id = "<?php echo isset($_GET['fb']) ? base64_decode($_GET['fb']) : "" ?>";
		if(fbuser_id != ''){
			$('.new_acc_create').hide();
			$('.signin_btn_cont').hide();
		}else{
			$('.new_acc_create').show();
			$('.signin_btn_cont').show();
		}

		var message_succcess = $("#message").text();
		var country = '<?php echo DEFAULT_COUNTRY_NAME; ?>';
		var fbuser_id = "<?php echo isset($_GET['fb']) ? base64_decode($_GET['fb']) : "" ?>";
		if(fbuser_id != ''){
			$('.new_acc_create, .signin_btn_cont').hide();
		}else{
			$('.new_acc_create, .signin_btn_cont').show();
		}
	   var request = new XMLHttpRequest();
	   request.open("GET", "public/country_code/data.json", false);
	   request.send(null)
	   var my_JSON_object = JSON.parse(request.responseText);
	   for (var key in my_JSON_object) {
		    if (my_JSON_object.hasOwnProperty(key)) {
			      if(my_JSON_object[key]["countryName"] == country)
			      {
			      	var code_country = my_JSON_object[key]["code"];
			      }
			}
		}
	 
		if(message_succcess != ''){
			$('.msg-success-error').text(message_succcess);
            $('.alert_msg').addClass('alert_success');
            setTimeout(function(){
            	$('.alert_msg').removeClass('alert_success');
            	$('.msg-success-error').text('');
            }, 4000);			
		}
		$("#country_code").CcPicker();
		$("#country_code").CcPicker("setCountryByCode",code_country);

		$("#signin_country_code").CcPicker();
		//$("#signin_country_code").CcPicker("setCountryByCode","in");
		
		$("#fp_country_code").CcPicker();
		//$("#fp_country_code").CcPicker("setCountryByCode","in");
	});
</script>
<!-- <?php $sucessful_message = Message::display();?>
<span id="messagedisplay"><?php echo $sucessful_message; ?></span>
 -->
<div class="webbooking_container">
	<form id="front_form" style="display: none;">
		<div class="model_carousel">
			<div class="owl-carousel owl-theme">
				<?php 
				$default_select_model = '';
				$default_select_model_name = '';
				if(count($get_model_details) > 0) {
					$tabId = 1;
					$modelIdsArr = array();
					foreach($get_model_details as $models){ 
						$modelIdsArr[] = $models['model_id'];
						if($tabId == 1)
						{
							$default_select_model = $models['model_id'];
							$default_select_model_name = $models['model_name'];
						}
						?>
						<div class="item sel-model <?php echo ($tabId==1)?'active':''; ?>" data-model="<?php echo $models['model_id']; ?>" data-model_name="<?php echo $models['model_id']; ?>">

						<?php 
							if ( file_exists( DOCROOT .MODEL_IMGPATH. "thumb_act_".$models['model_id'].'.png' ) ) { 
							    $active_model_path = MODEL_IMGPATH."thumb_act_".$models['model_id'].'.png';
							} else { 
							    $active_model_path = "public/webbooking/images/".$models['model_id'].'_active.png';
							} 
							if ( file_exists( DOCROOT .MODEL_IMGPATH. "thumb_".$models['model_id'].'.png' ) ) {
							    $normal_model_path = MODEL_IMGPATH."thumb_".$models['model_id'].'.png';
							} else {
							    $normal_model_path = "public/webbooking/images/".$models['model_id'].'.png';
							}
						?>
							<img class="active_img" src="<?php echo URL_BASE.$active_model_path;?>" alt="<?php echo $models['model_name']; ?>" title="<?php echo $models['model_name']; ?>">
						   	<img class="normal_img" src="<?php echo URL_BASE.$normal_model_path;?>" alt="<?php echo $models['model_name']; ?>" title="<?php echo $models['model_name']; ?>">
						   	<h4><?php echo $models['model_name']; ?></h4>
						</div>
				<?php
						$tabId++;
					}
					$modelIds = implode(",",$modelIdsArr); ?>
					
				<?php } if(RENTAL_AVAILABLE == 1 || OUTSTATION_AVAILABLE == 1) { 
					$packimgActive = URL_BASE.'public/webbooking/images/pack_active.png';
					$packimg = URL_BASE.'public/webbooking/images/pack.png';
					
					if(file_exists(DOCROOT.MODEL_IMGPATH.'web_pack_active.png')){
						$packimgActive = URL_BASE.MODEL_IMGPATH.'web_pack_active.png';
					}
					
					if(file_exists(DOCROOT.MODEL_IMGPATH.'web_pack.png')){
						$packimg = URL_BASE.MODEL_IMGPATH.'web_pack.png';
					}
				?>
					<div class="item sel-model" data-model="pack" data-model_name="package">
						<img class="active_img" src="<?php echo $packimgActive ?>" alt="<?php echo __('package'); ?>" title="<?php echo __('package'); ?>">
					   	<img class="normal_img" src="<?php echo $packimg ?>" alt="<?php echo __('package'); ?>" title="<?php echo __('package'); ?>">
					   	<h4><?php echo __('package'); ?></h4>
					</div>
				<?php } ?>				
			</div>
			<span id="select_model_error" class="error"></span>
		</div>
		<?php /* <div class="alert_msg alert_success alert_failure">
			<p class="msg-success-error"></p>
			<a href="javascript:;" class="close_alert"></a>
		</div> */ ?>
		<div class="form-group pickup_loc">
			<input type="text" name="pickup_location" id="pickup_location" class="form-control" placeholder="<?php echo __('pickup_location'); ?>">
			<span class="ctooltip"><i class="ctooltiptext" id="pickup_tooltip"></i></span>
		</div>
		<div class="form-group drop_loc">
			<input type="text" name="drop_location" id="drop_location" class="form-control" placeholder="<?php echo __('drop_location'); ?>">
			<span class="ctooltip"><i class="ctooltiptext" id="drop_tooltip"></i></span>
		</div>

		<div class="form-group package-plan-sec" style="display:none;">
			<select name="package_type" id="package_type" class="form-control" title="<?php echo __('select_trip_type'); ?>" onchange="loadModelFleets(this.value)">
				<option value=""><?php echo __('select_package_type'); ?></option>
				<?php if(RENTAL_AVAILABLE == 1) { 
				?>
					<option value="1"><?php echo __('rental'); ?></option>
				<?php } if(OUTSTATION_AVAILABLE == 1) { 
				?>
					<option value="2"><?php echo __('outstation'); ?></option>
				<?php } ?>
			</select>
			<span class="ctooltip"><i class="ctooltiptext" id="package_type_error"></i></span>
		</div>

		<div class="form-group package-plan-os-sec" style="display:none;">
			<select name="package_os_type" id="package_os_type" class="form-control">
				<option value=""><?php echo __('select_os_type'); ?></option>
				<option value="1"><?php echo __('one_way'); ?></option>
				<option value="2"><?php echo __('round_trip'); ?></option>
			</select>
			<span class="ctooltip"><i class="ctooltiptext" id="round_one_error"></i></span>
		</div>

		<div class="form-group days_count" id="os_trip_type_count" style="display:none;">
			<span><?php echo __('os_days_count'); ?></span><select name="os_days_count" id="os_days_count" class="form-control">
				<?php $i=1;
				for($i = 0; $i <= 15; $i++) { ?>
					<option value="<?php echo $i; ?>" ><?php echo $i; ?></option>
				<?php
				} ?>
			</select><span><?php echo __('os_days'); ?></span>
			<span class="ctooltip"><i class="ctooltiptext" id="days_count_error"></i></span>
		</div>
		<div class="trip-description" style="display:none;">
			<span id="tripDescription"></span>
		</div>
		<div class="form-group package-plan-sec" style="display:none;">
			<select name="package_fleet" id="package_fleet" class="form-control" onchange="loadPackagePlans(this.value)">
				<option value=""><?php echo __('select_package_fleet'); ?></option>
				<?php if(count($get_model_details) > 0) { 
					foreach($get_model_details as $model) {?>
						<option value="<?php echo $model['model_id']; ?>" ><?php echo $model['model_name']; ?></option>
				<?php }
				} ?>
			</select>
			<span class="ctooltip"><i class="ctooltiptext" id="package_fleet_error"></i></span>
		</div>

		

		<div class="form-group package-plan-sec" style="display:none;">
			<select name="package_plan" id="package_plan" class="form-control" onchange="loadPackageDetail(this.value)">
				<option value=""><?php echo __('select_package_plan'); ?></option>
			</select>
			<span class="ctooltip"><i class="ctooltiptext" id="package_plan_error"></i></span>
			<input type="hidden" name="package_base_fare" id="package_base_fare" value="" />
			<input type="hidden" name="package_distance" id="package_distance" value="" />
			<input type="hidden" name="package_plan_duration" id="package_plan_duration" value="" />
			<input type="hidden" name="package_addl_hour_fare" id="package_addl_hour_fare" value="" />
			<input type="hidden" name="package_addl_distance_fare" id="package_addl_distance_fare" value="" />
		</div>

		<div class="form-group">
			<input type="text" id="pickup_date" name="pickup_date" class="form-control date_pick" placeholder="">
			<span class="ctooltip"><i class="ctooltiptext" id="pickup_date_error"></i></span>
		</div>
		<div class="form-group btn_block">
			<button type="button" name="" id="ride_now" title="<?php echo __("ride_now"); ?>" class="btn active" value="0"><?php echo __('ride_now'); ?></button>
			<button type="button" name="" id="ride_later" title="<?php echo __('ride_later'); ?>" class="btn" value="1"><?php echo __('ride_later'); ?></button>
			<button onClick="fare_estimate()" id="fare_est" type="button" name="" title="<?php echo __('fare_estimate'); ?>" class="btn"><?php echo __('fare_estimate'); ?></button>
		</div>

		<div class="hidden_sec">
			<input type="hidden" name="pass_latitude" id="pass_latitude" value="" />
			<input type="hidden" name="pass_longitude" id="pass_longitude" value="" />
			<input type="hidden" name="modelids" id="modelids" value="<?php echo $modelIds; ?>">
			<input type="hidden" name="pass_logged_in" id="pass_logged_in" value="<?php echo $passenger_id; ?>" />
			<input type="hidden" name="pickup_latitude" id="pickup_latitude" value="">
			<input type="hidden" name="pickup_longitude" id="pickup_longitude" value="">
			<input type="hidden" name="ride_type" id="ride_type" value="">
			<input type="hidden" name="drop_latitude" id="drop_latitude" value="">
			<input type="hidden" name="drop_longitude" id="drop_longitude" value="">
			<input type="hidden" name="select_model" id="select_model" value="<?php echo $default_select_model; ?>">
			<input type="hidden" name="select_model_name" id="select_model_name" value="<?php echo $default_select_model_name; ?>">
			<input type="hidden" name="page_redirect" id="page_redirect" value="1">
			<input type="hidden" name="rental_outstation" id="rental_outstation" value="0">
			<input type="hidden" name="rent_out_plan_id" id="rent_out_plan_id" value="0">
			<input type="hidden" name="trip_type" id="trip_type" value="0">
			<input type="hidden" name="direct_sigin" id="direct_sigin" value="0">
			<input type="hidden" name="control_at" id="control_at" value="0">
			<input type="hidden" name="hidden_plan_duration" id="hidden_plan_duration" >
			
			<input type="hidden" name="est_time_hrmin" id="est_time_hrmin" value="">
			<input type="hidden" name="est_time_sec" id="est_time_sec" value="">
		</div>
	</form>
	<?php if(!isset($_SESSION['id'])) { ?>
		<div class="signin_btn_cont"><a class="forgot_btn sign_in" href="javascript:void;"><?php echo __('signin_create'); ?></a></div>
	<?php } ?>
	<div id="driver_listing_hidden_sec" style="display:none;"></div>
	<div id="driver_est_hidden_sec" style="display:none;"></div>

	<div id="fare_est_block" class="fare_est_block" style="display: none;">
		<h2><?php echo __('fare_estimate'); ?></h2>
		<p id="address_not_find"></p>
		<ul id="fare_details">
			<li><p id="est_time"></p><span><?php echo __('journey'); ?></span></li>
			<li><p id="app_distance"></p><?php echo UNIT_NAME ?><span><?php echo __('distance'); ?></span></li>
			<li><p id="company_tax"></p><span><?php echo __('tax'); ?></span></li>
			<li><p id="approx_fare"></p><span><?php echo __('approx_fare'); ?></span>
										<span id='tax_inclusive'></span></li>
		</ul>
		<div class="est_time_blk">
			<p>
				<span><?php echo __('estimated_driver'); ?></span>
				<span class="est_arr_time">--</span>
			</p>
		</div>
	</div>
</div>
<div id="signup_container" class="signup_container" style="display: none;">
	<div class="signup_steps signup_step1">
		<button type="button" name="page_backward" id="page_backward" class="btn btn_red btn_back" title="<?php echo __('back'); ?>"><?php echo __('back'); ?></button>
		<h2 class="signin_heading"><?php echo __('continue_with_mbl'); ?><small><?php echo __('4_digit_otp_sms'); ?></small></h2>
		<form name="get_mobileno_form" method="post" autocomplete="off">
			<div class="form-group">
				<div class="mob_no">
					<!-- <span><img src="<?php echo URL_BASE;?>public/webbooking/images/flag_india.png"></span>
					<select name="c_code" placeholder="+91" class="form-control" id="country_code">
						<option>+91</option>
						<option>+97</option>
					</select> -->
					<input type="hidden" id="country_code" name="c_code" class="phone-field">
					<input type="text" name="mobile_number" id="mobile" minlength="7" maxlength="15" placeholder="123-456-7890" class="form-control">
				</div>
				<span class="ctooltip"><i class="ctooltiptext" id="mobileno_tooltip"><</i></span>
			</div>
			<div class="form-group sub_butt">
				<button type="button" name="signin_submit" id="signin_submit" onclick="//check_passenger_exists()" class="btn btn_red btn_lg nxt_btn" title="Next"><?php echo __('next'); ?></button>
			</div>
			
			<div class="new_acc_create">
				<label><?php echo __('or'); ?></label>
				<h2><?php echo __('continue_with_fb'); ?></h2>
				<a href="<?php echo URL_BASE;?>users/fconnect_login" title="<?php echo __('login_with_facebook');?>" class="btn btn_blue btn_lg"><img src="<?php echo URL_BASE;?>public/webbooking/images/facebook_login.png"  alt="<?php echo __('login_with_facebook');?>" /></a>
			</div>
		</form>
	</div>
	<div class="signup_steps signup_step2 otp_container" style="display: none;">
		<h2><?php echo __('verify_mbl'); ?><small><?php echo __('enter_otp_pin'); ?><br><span id="pass_mobile"></span></small></h2>
		<form name="otp_form" method="post" autocomplete="off">
			<div class="otp_block">
				<div class="form-group">
					<input type="text" name="name" id="otp_val1" class="form-control otp_input" maxlength="1" tabindex="0" />
				</div>
				<div class="form-group">
					<input type="text" name="name" id="otp_val2" class="form-control otp_input" maxlength="1" tabindex="1"/>
				</div>
				<div class="form-group">
					<input type="text" name="name" id="otp_val3" class="form-control otp_input" maxlength="1" tabindex="2"/>
				</div>
				<div class="form-group">
					<input type="text" name="name" id="otp_val4" class="form-control otp_input" maxlength="1" tabindex="3"/>
				</div>
				<input type="hidden" name="passenger_id" id="passenger_id" value="">
				<span class="error" id="otp_error"></span>
			</div>
			<?php if(HIDEOTP != '1'){ ?>
				<span>OTP:</span><span id="otpNumber"></span> 
			<?php } ?>
			<div class="form-group sub_butt">
				<button type="button" value="Sign up" id="signup_button_id" onclick="//signup_now()" class="btn btn_red btn_lg" title="<?php echo __('verify_otp'); ?>"><?php echo __('verify_otp'); ?></button>
			</div>
			<div class="form-group sub_butt">
				<a href="javascript:;" id="resend_otp" onclick="" class="resend_otp_btn" title="Resend OTP"><?php echo __('resend_otp') ?></a>
				<a href="javascript:;" onclick="change_mob_no()" class="change_mob_btn" title="Change mobile number"><?php echo __('change_mobile_no'); ?></a>
			</div>
		</form>
	</div>
	<div class="signup_steps signup_step3" style="display: none;">
		<h2><?php echo __('create_account_signin') ?><small><?php echo __('enter_details')?>(<span id="pass_mobile_signup"></span>)</small></h2>
		<form name="signup_form" method="post" onsubmit="booking.html">
			<div class="form-group name_icon">
				<input type="text" name="name" id="pass_name" class="form-control" placeholder="<?php echo __('name'); ?>" title="<?php echo __('name'); ?>" value=""/>
				<span class="ctooltip"><i class="ctooltiptext" id="passname_tooltip"></i></span>
			</div>
			<div class="form-group password_icon">
				<i class="pwd_ico"></i>
				<input type="password" name="password" id="pass_password" class="form-control show_password" minlength="6" placeholder="<?php echo __('password'); ?>" title="<?php echo __('password'); ?>" value=""/>
				<span class="ctooltip"><i class="ctooltiptext" id="password_tooltip"></i></span>
			</div>
			<div class="form-group email_icon">
				<input type="text" name="email" id="pass_email" class="form-control" placeholder="<?php echo __('email'); ?>" title="<?php echo __('email'); ?>" value=""/>
				<span class="ctooltip"><i class="ctooltiptext" id="mail_tooltip"></i></span>
			</div>
			<div class="form-group sub_butt">
				<input type="button" id="passenger_signin_submit" name="signin_submit" value="<?php echo __('save_continue'); ?>" class="btn btn_red btn_lg" title="<?php echo __('save_continue'); ?>"/>
			</div>
			<div class="form-group">
				<p><span><?php echo __('signin_agree') ?></span><a href="<?php echo URL_BASE; ?>termsconditions.html" target="_blank" title="<?php echo __('terms_conditions'); ?>"><?php echo __('terms_conditions'); ?></a></p>
			</div>	
		</form>
	</div>
	<div class="signup_steps signup_step5" style="display: none;">
		<button type="button" name="back_page" id="back_page" class="btn btn_red btn_back" title="<?php echo __('back'); ?>"><?php echo __('back'); ?></button>
		<h2 class="signin_heading"><?php echo __('button_signin') ?></h2>
		<form name="signin_mobile_form" id="signin_mobile_form" method="post" autocomplete="off">
			<div class="form-group">
				<div class="mob_no">
					<!-- <span><img src="<?php echo URL_BASE;?>public/webbooking/images/flag_india.png"></span>
					<select name="c_code" placeholder="+91" class="form-control" id="signin_country_code">
						<option>+91</option>
						<option>+97</option>
					</select> -->
					<input type="hidden" name="country_flag" id="country_flag" value="">
					<input type="hidden" id="signin_country_code" name="c_code" class="phone-field">
					<input type="text" name="mobile_number" id="signin_mobile" placeholder="123-456-7890" minlength="7" maxlength="13" class="form-control" readonly>
				</div>
				<span class="phone_error error" id="mobile_error"></span>
			</div>
			<div class="form-group password_icon">
				<i class="pwd_ico"></i>
				<input type="password" name="password" id="signin_password" class="form-control show_password" placeholder="<?php echo __('password'); ?>" title="<?php echo __('password'); ?>" value=""/>
				<span class="ctooltip"><i class="ctooltiptext" id="passpwd_tooltip"><</i></span>
			</div>
			<div class="form-group sub_butt">
				<button type="button" name="submit_signin" id="submit_signin" onclick="//check_passenger_exists()" class="btn btn_red btn_lg nxt_btn" title="<?php echo __('next'); ?>"><?php echo __('next'); ?></button>
			</div>
			<div style="display: none;" class="new_acc_create">
				<label>or</label>
				<h2>Continue with facebook</h2>
				<a onclick="facebookconnect_login();" href="javascript:;" class="btn btn_blue btn_lg"><img alt="facebook" src="<?php echo URL_BASE;?>public/webbooking/images/facebook_login.png"/></a>				
			</div>			
		</form>
		<div>
			<a href="javascript;"  class="forgot_btn" data-toggle="modal" data-target="#frgetpswdmodal" ><?php echo __('forgot_password'); ?></a>
			<!-- Modal -->
			<div id="frgetpswdmodal" class="modal fade" role="dialog">
			  	<div class="modal-dialog">
			    	<!-- Modal content-->
				    <div class="modal-content">
						<div class="modal-header">
		        			<button type="button" class="close" data-dismiss="modal">&times;</button>
			        		<h4 class="modal-title"><?php echo __('forgot_password'); ?></h4>
			      		</div>
			      		<div class="modal-body">
							<form name="forgot_form" method="post" autocomplete="off">								
								<div class="form-group" >
									<div class="full_name_mobile">
										<div class="mob_no">
											<input type="hidden" id="fp_country_code" name="fp_code" class="phone-field">
											<input type="text" name="fp_mobile_number" id="fp_mobile_number" placeholder="0123456789" maxlength="13" class="form-control">
										</div>
										<span class="phone_error error" id="mobile_error"></span>
									</div>
									<em id="forgot_code_phone_error"></em>
								</div>
								<div class="form-group sub_butt">
									<input class="btn btn_red" type="button" name="forgot_submit" tabindex="3" onclick="forgotSubmit();" value="<?php echo __('btn_submit'); ?>" title="<?php echo __('btn_submit'); ?>"/>
								</div>
							</form>
			      		</div>
			      		<div class="modal-footer"></div>
			    	</div>
				</div>
			</div>
		</div>
	</div>
	
</div>

<script type="text/javascript">
	CURRENCY = "<?php echo CURRENCY; ?>";
	$(".pwd_ico").click(function(){
		var password = document.getElementById("signin_password");
		if(password != '')
		{	
			$(".pwd_ico").toggleClass('active');
			if (password.type === "password") {
	       		password.type = "text";
	    	} else {
		        password.type = "password";
		    }
		}

		var p_password = document.getElementById("pass_password");
		if(p_password != '')
		{
			if (p_password.type === "password") {
	       		p_password.type = "text";	
	       		$(this).addClass('active');
	    	} else {
		        p_password.type = "password";
		        $(this).removeClass('active');
		    }
		}
	});

	URL_BASE = "<?php echo URL_BASE;?>";
	error_free_form = 0;
	PUBLIC_IMGPATH = '<?php echo PUBLIC_IMGPATH.'/' ; ?>';
	signup_status = '<?php echo isset($_SESSION['signup_status']) ? $_SESSION['signup_status'] : "" ; ?>';
	
	// form submit
	some_problem_decrypt = '<?php echo __("some_problem_decrypt");?>';
	some_problem_tryagain = '<?php echo __("some_problem_tryagain");?>';
	currency = '<?php echo CURRENCY;?>';
	location_error = '<?php echo __("location_error"); ?>';
	no_proper_pickup = '<?php echo __("no_proper_pickup"); ?>';
	no_proper_drop = '<?php echo __("no_proper_drop"); ?>';
	taxi_model_error = '<?php echo __("taxi_model_error"); ?>';
	pickup_date_error = '<?php echo __("pickup_date_error"); ?>';
	pickup_time_error = '<?php echo __("pickup_time_error"); ?>';
	later_booking_need_miniumonehour = '<?php echo __("later_booking_need_miniumonehour"); ?>';
	rental_plan_error = '<?php echo __("rental_plan_error"); ?>';
	os_location_error = '<?php echo __("os_location_error"); ?>';
	min_round_trip = '<?php echo DEFAULT_ONEWAY_DURATION * 60; ?>';
	var usertimezone = '<?php echo TIMEZONE; ?>';
	var current_time = new Date().toLocaleString('en-US', {
        timeZone: usertimezone
    });
	today = new Date(current_time);
    todayDate = today.getDate();
    todayMonth = today.getMonth() + 1;
    todayMinute = today.getMinutes();
    todayHour = today.getHours();
    todayYear = today.getFullYear();
    validateMin = parseFloat(todayHour * 60) + parseFloat(todayMinute) + parseFloat(60);
    timeFormat = "hh:ii";
	dateFormat = "yyyy-mm-dd";
	dateFormat = dateFormat+' '+timeFormat;
	
	WEB_API_KEY = "<?php echo WEB_API_KEY; ?>";
	current_language = "<?php echo DEFAULT_LANGUAGE;?>";
	iconBase = '<?php echo PUBLIC_IMGPATH.'/' ; ?>';
	currency = '<?php echo CURRENCY;?>';
	cityname = '';
	// form submit
	$('.owl-carousel').owlCarousel({
		<?php if(SELECTED_LANGUAGE == 'ar'){ ?>
			rtl:true,
		<?php }else{ ?>
			rtl:false,
		<?php } ?>
	    loop:false,
	    margin:0,
	    nav:true,
	    responsive:{
	        0:{
	            items:2
	        },
	        600:{
	            items:3
	        },
	        1000:{
	            items:3
	        }
	    }
	});
	
	$('.item').click(function(){
		$('.item').removeClass('active');
		$(this).addClass('active');
	});

	var today = new Date();
	var timeFormat = "hh:ii:ss";
	//var dateFormat = DEFAULT_DATE_FORMAT_SCRIPT;
	var dateFormat = "yyyy-mm-dd";
	var dateFormat = dateFormat+' '+timeFormat;
	var php_date_0 = '<?php echo date("O");?>';
	$( function() {
    	$('#pickup_date').datetimepicker({
    		timezone: php_date_0,
    		timeInput: false,
    		autoclose:true,
    		showTimepicker:true,
			showSecond: true,
			timeFormat: 'HH:mm:ss',
			stepHour: 1,
			stepMinute: 1,
			stepSecond: 1
    	});
    	$("#pickup_date").datetimepicker().datetimepicker("setDate", new Date());
    	 /*timezone: php_date_0,
        timeFormat: 'HH:mm:ss',
        dateFormat: 'yy-mm-dd',
        minDateTime: customRangeStart(),
        timeInput: false,
        autoclose: true,
        todayBtn: true,
        pickerPosition: "top-right"*/
  	});

	//to get driver markers
	var bounds = new google.maps.LatLngBounds();
	var markers = [];
	var map; 
	var start;
	var end;
	var autocomplete, toAutocomplete;
	var directionsService = new google.maps.DirectionsService;
	var directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true});

	function initMap()
	{ 
		var iconBase = PUBLIC_IMGPATH;
		var modelID = $(".active input").val();
		  
		if (navigator.geolocation) { 
			navigator.geolocation.getCurrentPosition(function(position) {
				do_something(position.coords);
			},
			function(failure) { 
				<?php $global_session->set('set_map_marker','1'); ?>
				var local_path = URL_BASE+'map.txt';
				$.getJSON(local_path, function(response) {
					var loc = response.loc.split(',');
					var coords = {
						latitude: loc[0],
						longitude: loc[1]
					};
					do_something(coords);
				});
			});
		}
	    
		function  do_something(coords)
		{ 
			infoWindow = new google.maps.InfoWindow();
			var defaultLatLng = new google.maps.LatLng(coords.latitude, coords.longitude);
			getFareDets(modelID,coords.latitude,coords.longitude);
			$("#pass_latitude").val(coords.latitude);
			$("#pass_longitude").val(coords.longitude);
			map = new google.maps.Map(document.getElementById('map_block'), {
				center: defaultLatLng,
				zoom: 12,
				streetViewControl: false
			});

			//The below code is to set map location to user's current location
			jQuery.post( "https://www.googleapis.com/geolocation/v1/geolocate?key=<?php echo GOOGLE_MAP_API_KEY; ?>", function(success) 
			{
				map.setCenter(new google.maps.LatLng(success.location.lat, success.location.lng));	
				var lat = success.location.lat;		
				var lng = 	success.location.lng;
				var latlng = new google.maps.LatLng(lat, lng);
	       		var geocoder = geocoder = new google.maps.Geocoder();
	        	geocoder.geocode({ 'latLng': latlng }, function (results, status) {
	            if (status == google.maps.GeocoderStatus.OK) { 
	            	$("#booked_location").val(results[1].formatted_address)
	               }
	        	});
	  			$("#booked_latitude").val(success.location.lat);
				$("#booked_longitude").val(success.location.lng);
				
	        });
				
	       
			directionsDisplay.setMap(map);
				
			marker = new google.maps.Marker({
				position: defaultLatLng,
				map: map,
				title:'some',
				animation: google.maps.Animation.DROP,
				icon: iconBase +  'location_icon.png',
			});			
			markers.push(marker);
	    }
		
		//Auto Suggest for pickup and drop locations
		/** option variable to search the particular country *
		var options = {
			componentRestrictions: {country: MAP_COUNTRY}
		};
		/** google autocomplete functionality in add booking **/
		var options = {types: [] };
		/* Restrictions to load only particular country */
		
		autocomplete = new google.maps.places.Autocomplete(document.getElementById('pickup_location'), options);
		toAutocomplete = new google.maps.places.Autocomplete(document.getElementById('drop_location'), options);
		
		google.maps.event.addDomListener(document.getElementById('pickup_location'), 'focus', geolocate);
		google.maps.event.addDomListener(document.getElementById('drop_location'), 'focus', geolocate);
		
		/* get the latitude and longitude of pickup and drop location and saved them in hidden elements */
		google.maps.event.addListener(autocomplete, 'place_changed', function () {
			var pickup = autocomplete.getPlace();//Get a place lat&long
			/***************Get a locationA Latitude and Longitude  ***********/
			document.getElementById('pickup_latitude').value = pickup.geometry.location.lat();//initialized latitude
			document.getElementById('pickup_longitude').value = pickup.geometry.location.lng();//initialized longitude
			/***************End of Get a locationA Latitude and Longitude ***********/
			if($('#pickup_location').val() != '' && $('#drop_location').val() != '')
			{
				fare_estimate();
			}
		});
		//drop location
		google.maps.event.addListener(toAutocomplete, 'place_changed', function () {
			var droploc = toAutocomplete.getPlace();//Get a place lat&long
			/***************Get a locationA Latitude and Longitude  ***********/
			document.getElementById('drop_latitude').value = droploc.geometry.location.lat();//initialized latitude
			document.getElementById('drop_longitude').value = droploc.geometry.location.lng();//initialized longitude
			/***************End of Get a locationA Latitude and Longitude ***********/
			if($('#pickup_location').val() != '' && $('#drop_location').val() != '')
			{
				fare_estimate();
			}
		});
	}

	function forgotSubmit()
	{
		has_error = '0';
		if($('#fp_mobile_number').val() == '')
		{
			has_error = '1';
			$('#fp_mobile_number').addClass('inputerror');
		} else {
			$('#fp_mobile_number').removeClass('inputerror');
		}

		if(has_error == '0')
		{
			$(".loader").css('display', 'block');
			var parameter = JSON.stringify({
	            "country_code": $('#fp_country_code_phoneCode').val(),
	            "phone_no": $('#fp_mobile_number').val(),
	            "user_type": 'P'
	        });
            
        	var formData = "input=" + parameter;

			$.ajax({
	            url: URL_BASE + 'decrypt/encrypt',
	            type: 'post',
	            // dataType:'',
	            data: formData,
	            async: false,
	            cache: false,
	            success: function(encrypt_input) {
	                url = URL_BASE + 'passengerapi113/index?type=forgot_password&lang=' + current_language;
	                $.ajax({
	                    type: "POST",
	                    url: url,
	                    headers: {
	                        "Authorization": WEB_API_KEY
	                    },
	                    data: encrypt_input,
	                    cache: false,
	                    //dataType: 'html',
	                    success: function(encrypted_response) {
	                        var formData = {
	                            value: encrypted_response
	                        };
	                        //console.log('-----------------'+encrypted_response);
	                        $.ajax({
	                            url: URL_BASE + 'decrypt',
	                            type: 'post',
	                            // dataType:'',
	                            data: formData,
	                            async: false,
	                            cache: false,
	                            success: function(decrypted_output) {
	                            	$('.close').trigger('click');
	                            	$(".loader").css('display', 'none'); //remove loader image
	                                decrypted_json = $.parseJSON(decrypted_output);

	                                if (decrypted_json.status != 1) {
	                                    $('.msg-success-error').text(decrypted_json.message);
	                                    $('.alert_msg').addClass('alert_failure');
	                                    setTimeout(function(){
	                                    	$('.alert_msg').removeClass('alert_failure');
		                                	$('.msg-success-error').text('');
		                                }, 4000);
	                                } else {
	                                    $('.msg-success-error').text(decrypted_json.message);
	                                    $('.alert_msg').addClass('alert_success');
	                                    window.localStorage.clear();
	                                    setTimeout(function(){
	                                    	$('.alert_msg').removeClass('alert_success');
		                                	$('.msg-success-error').text('');
		                                }, 4000);
	                                }
	                                
	                                $(".dialog-buttons").addClass("singlebtn");
	                            },
	                            error: function() {
	                                $(".loader").css('display', 'none'); //remove loader image
	                                $('.msg-success-error').text(some_problem_tryagain);
	                                $('.alert_msg').addClass('alert_failure');
	                                setTimeout(function(){
	                                	$('.alert_msg').removeClass('alert_failure');
	                                	$('.msg-success-error').text('');
	                                }, 4000);
	                                $(".book_ride").removeAttr('disabled');
	                            }
	                        });
	                    },
	                    error: function() {
	                        $(".loader").css('display', 'none'); //remove loader image
	                        $('.msg-success-error').text(some_problem_tryagain);
	                        $('.alert_msg').addClass('alert_failure');
	                        setTimeout(function(){
	                        	$('.alert_msg').removeClass('alert_failure');
	                        	$('.msg-success-error').text('');
	                        }, 4000);
	                        $(".book_ride").removeAttr('disabled');
	                    }
	                });
	            },
	            error: function() {
	                $(".loader").css('display', 'none'); //remove loader image
	                $('.msg-success-error').text(some_problem_tryagain);
	                $('.alert_msg').addClass('alert_failure');
	                setTimeout(function(){
	                	$('.alert_msg').removeClass('alert_failure');
	                	$('.msg-success-error').text('');
	                }, 4000);
	                $(".book_ride").removeAttr('disabled');
	            },
	        });
		}
		
	}

	function getFareDets(modelID,passLat,passLong)
	{ 
		if(passLat == '' && passLong == '') {
			var pass_latitude = $("#pass_latitude").val();
			var pass_longitude = $("#pass_longitude").val();
		} else {
			var pass_latitude = passLat;
			var pass_longitude = passLong;
		}

		//assign the selected model in the hidden element
		$("#model").val(modelID);
		$.ajax({
			url:URL_BASE+"find/getModelFareDets",
			type:"post",
			data:"modelID="+modelID+"&passlat="+pass_latitude+"&passlong="+pass_longitude,
			success:function(data){
				var res = $.parseJSON(data);
				$.each(res, function(i, val) {
					if(i==0) {
						var min_fare = val.min_fare;
						var base_fare = val.base_fare;
						var below_km = val.below_km;
						var above_km = val.above_km;
						var cancellation_fare = val.cancellation_fare;
						min_fare = min_fare.split('<?php echo CURRENCY;?>');
						base_fare = base_fare.split('<?php echo CURRENCY;?>');
						below_km = below_km.split('<?php echo CURRENCY;?>');
						above_km = above_km.split('<?php echo CURRENCY;?>');
						cancellation_fare = cancellation_fare.split('<?php echo CURRENCY;?>');
							
						$("#min_km").html(val.min_km);
						$("#below_km").html(val.below_above_km);
						$("#above_km").html(val.below_above_km);
						var nighChApp = (val.night_charge == 1) ? '(Yes)' : '(No)';
						var eveChApp = (val.evening_charge == 1) ? '(Yes)' : '(No)';
						$("#night_chargeapp").html(nighChApp);
						$("#eve_chargeapp").html(eveChApp);
						$("#base_fare").html('<?php echo CURRENCY;?> '+ parseInt(base_fare[1]).toFixed(2));
						$("#min_fare").html('<?php echo CURRENCY;?> '+ parseInt(min_fare[1]).toFixed(2));
						$("#below_fare").html('<?php echo CURRENCY;?> '+ parseInt(below_km[1]).toFixed(2));
						$("#above_fare").html('<?php echo CURRENCY;?> '+ parseInt(above_km[1]).toFixed(2));
						$("#night_fare_percent").html(val.night_fare+"%");
						$("#eve_fare_percent").html(val.evening_fare+"%");
						$("#cancel_fare").html('<?php echo CURRENCY;?> '+ parseInt(cancellation_fare[1]).toFixed(2));
					}
				});
				if(res.drivers_count > 0) {
					$("#availDrivers").html(res.drivers_count+" <?php echo __('car_avail'); ?>");
				} else {
					$("#availDrivers").html("<?php echo __('no_cars_avail'); ?>");
				}
				//set free status driver markers in map
				driverMarkers = res.driver_list[0];
				if(driverMarkers.length > 0)
				{
					for( i = 0; i < driverMarkers.length; i++ ) { 
						// Display multiple markers on a map
						var infoWindow = new google.maps.InfoWindow(), marker, i;
						// Loop through our array of markers & place each one on the map
						var position = new google.maps.LatLng(driverMarkers[i][0], driverMarkers[i][1]);
						bounds.extend(position);
						marker = new google.maps.Marker({
							position: position,
							map: map,
							animation: google.maps.Animation.DROP,
							icon: driverMarkers[i][3],
						});
						markers.push(marker);
						// Allow each marker  to have an info window
						google.maps.event.addListener(marker, 'click', (function(marker, i) {
							return function() {
								infoWindow.setContent(driverMarkers[i][2]);
								infoWindow.open(map, marker);
							}
						})(marker, i));
						// Automatically center the map fitting all markers on the screen
						//map.fitBounds(bounds);
					}
					
				} else {
					//~ removeMarkers();
				} 
			},
			error:function(data)
			{
				
			}
		});
	}

	google.maps.event.addDomListener(window, "load", initMap);

	function geolocate() {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function (position) {
				var geolocation = new google.maps.LatLng(
				position.coords.latitude, position.coords.longitude);
				var circle = new google.maps.Circle({
					center: geolocation,
					radius: position.coords.accuracy
				});
				autocomplete.setBounds(circle.getBounds());
				// Log autocomplete bounds here
				//console.log(autocomplete.getBounds());
			});
		}
	}

	function fare_estimate()
	{  
		$(this).addClass('active');
		$("#drop_location_error").text('');
		var modelID = $("#select_model").val();
		var modelName = $("#select_model_name").text();
		var pickup_location = $("#pickup_location").val();
		var drop_location = $("#drop_location").val();
		var selected_package_type = $('#package_os_type').val();
		
		pickup_latitude = parseFloat($("#pickup_latitude").val());
		pickup_longitude = parseFloat($("#pickup_longitude").val());
		drop_latitude = parseFloat($("#drop_latitude").val());
		drop_longitude = parseFloat($("#drop_longitude").val());
		// console.log('lat'+pickup_latitude+' '+'lang'+pickup_longitude);

		if(pickup_location != "" && drop_location != "" && $(".item").hasClass('active') != '') {
			$("#couldnot_find_address").hide();
			$("#fade, .fare_estimate_popup").show();		
			show_fare(pickup_latitude,pickup_longitude,pickup_location,drop_location,modelID,modelName,selected_package_type,drop_latitude,drop_longitude);
			outstation_type_options();
			/*Directions display */
			var directionsDisplay = new google.maps.DirectionsRenderer;
	        var directionsService = new google.maps.DirectionsService;
	        var map = new google.maps.Map(document.getElementById('map_block'), {
	          zoom: 5,
	          center: {lat: pickup_latitude, lng: pickup_longitude}
	        });
	        directionsDisplay.setMap(map);
	        directionsDisplay.setOptions( { suppressMarkers: true } );
	        calculateAndDisplayRoute(directionsService, directionsDisplay);
	        
	        function makeMarker( position, icon, title ) {
				new google.maps.Marker({
					position: position,
				 	map: map,
					icon: icon,
				  	title: title
			 	});
			}
	        function calculateAndDisplayRoute(directionsService, directionsDisplay) {
		        var selectedMode = "DRIVING";
		        directionsService.route({
		        	origin: {lat: pickup_latitude, lng: pickup_longitude},  // Haight.
		          	destination: {lat: drop_latitude, lng: drop_longitude},  // Ocean Beach.
		          	// Note that Javascript allows us to access the constant
		          	// using square brackets and a string value as its
		          	// "property."
		          	travelMode: google.maps.TravelMode[selectedMode]
		        }, function(response, status) {
		          	if (status == 'OK') {
		          		directionsDisplay.setDirections(response);
		          	   	var leg = response.routes[ 0 ].legs[ 0 ];
						makeMarker( leg.start_location, URL_BASE+'public/common/images/startMarker.png', "Pickup Location" );
					  	makeMarker( leg.end_location, URL_BASE+'public/common/images/endMarker.png', 'Drop Location' );
		          	} else {
			            window.alert('Directions request failed due to ' + status);
		          	}
		        });
	      	}
			/*Directions display*/			
		} else {
			if($("#pickup_location").val() == ''){
				$("#pickup_location").addClass('inputerror');
				$("#pickup_tooltip").html('<?php echo __("location_error"); ?>');
			} else {
				$("#pickup_location").removeClass('inputerror');
				$("#pickup_tooltip").html('');
			}

			if($(".item").hasClass('active') == ''){
				$("#select_model_error").text('<?php echo __("taxi_model_error"); ?>');
			} else {
				$("#select_model_error").text('');
			}
			/*if($("#pickup_date").val() == ''){ 
				$("#pickup_date_error").text('<?php echo __("select_the_date"); ?>');
			} else {
				$("#pickup_date_error").text('');
			}*/
			if($("#drop_location").val() == ''){ 
				$("#drop_location").addClass('inputerror');
				$("#drop_tooltip").html('<?php echo __("drop_location_error"); ?>');
			} else {
				$("#drop_location").removeClass('inputerror');
				$("#drop_tooltip").html('');
			}

			/* package based checking */
			var trip_type = $('#trip_type').val();

			if(trip_type != 0)
			{
				if($("#package_type").val() == ''){
					error_free_form = 1;
					$("#package_type").addClass('inputerror');
					$("#package_type_error").html('<?php echo __("package_type_error"); ?>');
				} else {
					$("#package_type").removeClass('inputerror');
					$("#package_type_error").html('');
				}

				if($("#package_type").val() == '2')
				{
					if($("#package_os_type").val() == ''){ 
						error_free_form = 1;
						$("#package_os_type").addClass('inputerror');
						$("#round_one_error").html('<?php echo __("outstation_type_error"); ?>');
					} else {
						$("#package_os_type").removeClass('inputerror');
						$("#round_one_error").html('');
					}
				}

				if($("#package_fleet").val() == ''){
					error_free_form = 1;
					$("#package_fleet").addClass('inputerror');
					$("#package_fleet_error").html('<?php echo __("package_fleet_error"); ?>');
				} else {
					$("#package_fleet").removeClass('inputerror');
					$("#package_fleet_error").html('');
				}
				if($("#package_plan").val() == ''){
					error_free_form = 1;
					$("#package_plan").addClass('inputerror');
					$("#package_plan_error").html('<?php echo __("package_plan_error"); ?>');
				} else {
					$("#package_plan").removeClass('inputerror');
					$("#package_plan_error").html('');
				}
			} else {
				$("#package_type_error").html('');
				$("#package_fleet_error").html('');
				$("#package_plan_error").html('');
				$("#round_one_error").html('');
			}
			/* package based checking */

			return false;
		}

		document.getElementById("fare_est_block").style.display = "block";
		document.getElementById("map_block").style.display = "block";
		document.getElementById("home_banner_block").style.display = "none";
		document.getElementById("fare_est").classList.add("active");
		document.getElementById("ride_now").classList.remove("active");
	}

	function show_fare(pickup_latitude,pickup_longitude,pickup_location,drop_location,modelID,modelName,selected_package_type,drop_latitude,drop_longitude)
	{
		var pass = 1;
		var packageType = $('#package_type').val();
		var packagePlan = $('#package_plan').val();
		
		if(pass == 1)
		{
			$.ajax({
				url: URL_BASE+"users/fare_estimate",
				type: "post",
				data: "pickup_latitude="+ pickup_latitude +"&pickup_longitude="+ pickup_longitude+"&pickup_location="+ pickup_location +"&drop_location="+ drop_location +"&modelID="+ modelID+"&package="+packagePlan+"&package_os_type="+ selected_package_type+"&package_type="+ packageType+"&drop_latitude="+ drop_latitude+"&drop_longitude="+ drop_longitude,
				beforeSend:function() {
					$("#trip_estimate_distance, #trip_estimate_fare,#modelImg,#model_name_lbl").html('<img src="'+URL_BASE+'" alt="Loading..."/>');
				},
				success:function(data) {
					var json = $.parseJSON(data);
					
					$("#modelImg").html('<a href="javascript:;"><img src='+URL_BASE+'"public/<?php echo UPLOADS; ?>/model_image/thumb_act_'+modelID+'.png"><span>'+modelName+'</span></a>');
					
					if(json.address_not_find) {
						$("#address_not_find").html(json.description);
					}else{
						$("#address_not_find").hide();
						if(packageType == 2 && selected_package_type == 2){
							var total = 2 * json.distance;
							$("#app_distance").html(total);
						}else{
							$("#app_distance").html(json.distance);
						}
						$("#approx_fare").html('<?php echo CURRENCY; ?> '+json.total_fare);
						$("#company_tax").html(json.company_tax+' %');
						$("#model_name_lbl").html(modelName);
						$("#appx_amount").html(json.total_fare);
						
						$("#est_time").html(json.total_min);
						$("#est_time_hrmin").val(json.total_min);
						$("#est_time_sec").val(json.total_sec);
						
						search_nearest_driver_location_new_design();

						if(packageType > 0)
						{
							outstation_type_options();
							$("#tax_inclusive").html(taxExclusive);
						}else{
							$("#tax_inclusive").html(taxInclusive);
						}
					}
				},
				error:function(data) {
					$(".loader").css('display', 'none'); //remove loader image
				}
			});
		}
	}

	//start of outstation fare calculation
	function outstation_type_options()
	{
		os_pickup_latitude = $('#pickup_latitude').val();
		os_pickup_longitude = $('#pickup_longitude').val();
		os_drop_latitude = $('#drop_latitude').val();
		os_drop_longitude = $('#drop_longitude').val();
		var packageType = $("#package_type").val();
		
		var travel_duration = 0;

		if (os_pickup_latitude != '' && os_pickup_longitude != '' && os_drop_latitude != '' && os_drop_longitude != '') {
			var base_fare = $('#package_base_fare').val();
			var approx_distance = $('#package_distance').val();
			var additional_distance_fare = $('#package_addl_distance_fare').val();
			var approx_duration = $('#package_plan_duration').val();
			var additional_hr_fare = $('#package_addl_hour_fare').val();

	        var pickup = os_pickup_latitude + "," + os_pickup_longitude;
	        var drop_latlng = os_drop_latitude + "," + os_drop_longitude;
	        var extra_distance_fare = extra_duration_fare = 0;
	        var start = pickup;
	        var end = drop_latlng;
	        var request = {
	            origin: start,
	            destination: end,
	            optimizeWaypoints: true,
	            travelMode: google.maps.TravelMode.DRIVING
	        };

	        var directionsService = new google.maps.DirectionsService();

	        directionsService.route(request, function(response, status) {
	        	if (status === 'OK') {
	            	
	                var route = response.routes[0];
	                var total_distance = total_duration = extra_duration = extra_duration_to_hr = extra_duration_fare = 0;
	                var total_distance_km = show_time = td = 0;
	                
	                for (var i = 0; i < route.legs.length; i++) {
	                    var drop_lat = route.legs[i].end_location.lat();
	                    var drop_lng = route.legs[i].end_location.lng();
	                    var drop_latlng = drop_lat + "," + drop_lng;

	                    //total_distance += (oneway_round == 2) ? (2 * parseFloat(route.legs[i].distance.text)) : parseFloat(route.legs[i].distance.text);
	                    total_distance += parseFloat(route.legs[i].distance.value);
	                    total_duration += parseInt(route.legs[i].duration.value);
	                }
	                
	                travel_duration = parseFloat(total_duration)/60;
	                total_duration = (parseFloat(total_duration)*2) / 60; // convert to mins
	                day = 0;
	                if(total_duration > min_round_trip && $('#rental_outstation').val() == 2)
	                {
	                	$("#package_os_type option[value='1']").remove();
	                    $("#package_os_type").val('2');
	                    $(".days_count").show();
	                    day = $('#os_days_count').val();
	                    $(".trip-description").show();
	                    $("#tripDescription").html(day+' day(s) round trip');
	                } else {
	                	var optionExists = ($('#package_os_type option[value="1"]').length > 0);
						if(!optionExists)
						{
	                		$("#package_os_type option").eq(1).before($("<option></option>").val("1").text("One-way"));
	                	}
                		if($("#package_os_type").val() == 2 && $('#rental_outstation').val() == 2)
	                    {
	                        day = $('#os_days_count').val();
	                        $(".days_count").show();
	                        $(".trip-description").show();
	                        $("#tripDescription").html(day+' day(s) round trip');
	                    } else if($("#package_os_type").val() == 1) {
	                        $("#package_os_type").val('1')
	                        $(".days_count").hide();
	                        $(".trip-description").show();
	                        if (!$("#tripDescription").text().trim().length) {
	                        	$("#tripDescription").html('12 hour(s) one-way trip');
							}	
	                    } else {
	                    	$(".days_count").hide();
	                    	$(".trip-description").hide();
	                    	$("#tripDescription").text();
	                    }
	                }
	                
	                var oneway_round = $("#package_os_type").val();
                
					total_distance = parseFloat(total_distance) / 1000;
					total_distance = (packageType == 2 && oneway_round == 2) ? (2 * parseFloat(total_distance)) : parseFloat(total_distance);
					travel_duration = (packageType == 2 && oneway_round == 2) ? (2 * parseFloat(travel_duration)) : parseFloat(travel_duration);										
					if (total_distance > approx_distance) {
	                    var extra_distance = total_distance - approx_distance;
	                    extra_distance_fare = extra_distance * additional_distance_fare;
	                }

	                if (parseFloat(travel_duration) > parseFloat(approx_duration)) {
	                	var extra_duration = parseFloat(travel_duration) - parseFloat(approx_duration);
	                    var extra_duration_to_hr = parseFloat(extra_duration) / 60;
	                    extra_duration_fare = parseFloat(extra_duration_to_hr) * parseFloat(additional_hr_fare);
	                }
	                
	                var approx_trip_fare = parseFloat(base_fare) + parseFloat(extra_duration_fare) + parseFloat(extra_distance_fare);
	                approx_trip_fare = parseFloat(approx_trip_fare).toFixed(2);                
	                extra_rt_fare = 0;
	                
	                if(day > 0)
	                {
	                    cal_hr = (day * 24 * 60)-approx_duration;
	                    if(cal_hr > 0)
	                    {
	                        final_cal_hr = cal_hr / 60;
	                        extra_rt_fare = final_cal_hr * additional_hr_fare;
	                    }
	                    approx_trip_fare = parseFloat(approx_trip_fare) - parseFloat(extra_duration_fare);
	                }
	                
	                final_approx_trip_fare = parseFloat(approx_trip_fare) + parseFloat(extra_rt_fare);
	                
	                if(isNaN(final_approx_trip_fare)) {
	                	final_approx_trip_fare = 0;
	                }
	                
	                
	                var total_secs = (parseFloat(travel_duration)) * 60;		                
					var show_time = secondsToHms(total_secs);
					total_distance = parseFloat(total_distance).toFixed(2);
					
					$("#app_distance").html(total_distance);
					$("#est_time").html(show_time);	                
	                $("#approx_fare").html(CURRENCY+' '+final_approx_trip_fare.toFixed(2));
	            }
	        });

	    }
	}
	//End of outstation fare calculation

	//shown the rental and outstation div based on the click
    $('#os_days_count').change(function() {
        var daystohours = parseInt(this.value) * (24*60*60);// convert to seconds
        $(".trip-description").show();
        $("#tripDescription").html(this.value+' day(s) round trip');
        outstation_type_options();
    });

    $('#package_os_type').on('change', function() {
    	val = $(this).val();
        if(val == 2){
        	$("#round_one_error").html('');
        	$("#package_os_type").removeClass('inputerror');
        	$('.days_count').show();
        	$("#tripDescription").html('0 day round trip');
        	$(".trip-description").show();
        } else {
        	var plan_duration = $('#hidden_plan_duration').val();
        	$("#round_one_error").html('');
        	$("#package_os_type").removeClass('inputerror');
        	$('.days_count').hide();
        	if(plan_duration == 24){	
        		$("#tripDescription").html('24 hours one-way trip');
        	}else{
        		$("#tripDescription").html('12 hours one-way trip');
        	}
        	$(".trip-description").show();
        }
        fare_estimate();
    });

	$('.sign_in').on('click', function() {
		$("#direct_sigin").val('1');
		$("#pickup_location").removeClass('inputerror');
		$("#pickup_date").removeClass('inputerror');
		$("#drop_date").removeClass('inputerror');
		$('.webbooking_container').hide();
		$("#front_form").css("display","none");
		$(".signup_container .signup_steps").hide();
		$(".signup_container,.signup_container .signup_steps.signup_step1").show();		
	});

	$("#ride_now").click(function(){
		var error_free_form = 0; 
		$("#direct_sigin").val('0');
		$("#ride_type").val($(this).val());
		$("#pickup_location_error").text('');
		$("#select_model_error").text('');
		$("#pickup_date_error").text('');
		$("#drop_location_error").text('');
		if($("#pickup_location").val() == ''){
			error_free_form = 1;
			$('#pickup_location').addClass('inputerror');
		    $("#pickup_tooltip").html('<?php echo __("location_error"); ?>');
		} else {
			$('#pickup_location').removeClass('inputerror');
			$("#pickup_tooltip").html('');
		}

		if($(".item").hasClass('active') == ''){
			error_free_form = 1;
			$("#select_model_error").text('<?php echo __("taxi_model_error"); ?>');
		} else {
			$("#select_model_error").text('');
		}
		if($("#pickup_date").val() == ''){ 
			error_free_form = 1;
			$('#pickup_date').addClass('inputerror');
			// $("#pickup_date_error").text('<?php echo __("select_the_date"); ?>');
		} else {
			$('#pickup_date').removeClass('inputerror');
			// $("#pickup_date_error").text('');
		}

		/* package based checking */
		var trip_type = $('#trip_type').val();

		if(trip_type != 0)
		{	
			if($("#package_type").val() == ''){ 
				error_free_form = 1;
				$("#package_type").addClass('inputerror');
				$("#package_type_error").html('<?php echo __("package_type_error"); ?>');
			} else {
				$("#package_type").removeClass('inputerror');
				$("#package_type_error").html('');
			}

			if($("#package_fleet").val() == ''){
				error_free_form = 1;
				$("#package_fleet").addClass('inputerror');
				$("#package_fleet_error").html('<?php echo __("select_model_error"); ?>');
			} else {
				$("#package_fleet").removeClass('inputerror');
				$("#package_fleet_error").html('');
			}
			if($("#package_plan").val() == ''){
				error_free_form = 1;
				$("#package_plan").addClass('inputerror');
				$("#package_plan_error").html('<?php echo __("select_package_error"); ?>');
			} else {
				$("#package_plan").removeClass('inputerror');
				$("#package_plan_error").html('');
			}
		} else {
			$("#package_type").removeClass('inputerror');
			$("#package_fleet").removeClass('inputerror');
			$("#package_plan").removeClass('inputerror');
		}
		/* package based checking */

		if(error_free_form == 0)
		{
			pass_log = $('#pass_logged_in').val();
			if(pass_log == 0)
			{
				save_details_req();
				$("#pickup_location_error").text('');
				$("#pickup_date_error").text('');
				$('.webbooking_container').hide();
				$("#front_form").css("display","none");
				$(".signup_container .signup_steps").hide();
				$(".signup_container,.signup_container .signup_steps.signup_step1").show();
				// window.location = 'signin.html';
			} else {
				submit_form($(this).val());
			}
		}
	});

	$("#ride_later").click(function(){
		var error_free_form = 0;
		$("#direct_sigin").val('0');
		$("#ride_type").val($(this).val());
		$("#pickup_location_error").text('');
		$("#select_model_error").text('');
		$("#pickup_date_error").text('');
		$("#drop_location_error").text('');

		if($("#pickup_location").val() == ''){
			error_free_form = 1;
			$("#pickup_location").addClass('inputerror');
			$("#pickup_tooltip").html('<?php echo __("location_error"); ?>');
		} else {
			$("#pickup_location").removeClass('inputerror');
			$("#pickup_tooltip").html('');
		}
		if($(".item").hasClass('active') == ''){
			error_free_form = 1;
			$("#select_model_error").text('<?php echo __("taxi_model_error"); ?>');
		} else {
			$("#select_model_error").text('');
		}
		if($("#pickup_date").val() == ''){ 
			error_free_form = 1;
			$("#pickup_date").addClass('inputerror');
			$("#pickup_date_error").text('<?php echo __("select_the_date"); ?>');
		} else {
			var d = new Date($('#pickup_date').val());
			var sel_date = d.getDate();
			var sel_month = d.getMonth()+1;
			var sel_year = d.getFullYear();
            if ((todayMonth <= sel_month) && (todayDate <= sel_date) && (todayYear <= sel_year)) {
            	var totalmin = d.getMinutes();
                var totalHour = d.getHours();
                var totalmins = parseFloat(totalHour * 60) + parseFloat(totalmin);      
                
                if (todayDate == sel_date && totalmins < validateMin) {
                	error_free_form = 1;
                    $("#pickup_date").addClass('inputerror');
					$("#pickup_date_error").html(later_booking_need_miniumonehour);
                } else {
                	$("#pickup_date").removeClass('inputerror');
					$("#pickup_date_error").html('');
	            }
            } else {
            	error_free_form = 1;
                $("#" + formid + " #os_pickup_time_error").text(later_booking_need_miniumonehour);
            }
		}

		/* package based checking */
		var trip_type = $('#trip_type').val();

		if(trip_type != 0)
		{
			if($("#package_type").val() == ''){
				error_free_form = 1;
				$("#package_type").addClass('inputerror');
				$("#package_type_error").html('<?php echo __("package_type_error"); ?>');
			} else {
				$("#package_type").removeClass('inputerror');
				$("#package_type_error").html('');
			}

			if($("#package_type").val() == '2')
			{
				if($("#package_os_type").val() == ''){ 
					error_free_form = 1;
					$("#package_os_type").addClass('inputerror');
					$("#round_one_error").html('<?php echo __("outstation_type_error"); ?>');
				} else {
					$("#package_os_type").removeClass('inputerror');
					$("#round_one_error").html('');
				}
			}

			if($("#package_fleet").val() == ''){
				error_free_form = 1;
				$("#package_fleet").addClass('inputerror');
				$("#package_fleet_error").html('<?php echo __("package_fleet_error"); ?>');
			} else {
				$("#package_fleet").removeClass('inputerror');
				$("#package_fleet_error").html('');
			}
			if($("#package_plan").val() == ''){
				error_free_form = 1;
				$("#package_plan").addClass('inputerror');
				$("#package_plan_error").html('<?php echo __("package_plan_error"); ?>');
			} else {
				$("#package_plan").removeClass('inputerror');
				$("#package_plan_error").html('');
			}
		} else {
			$("#package_type_error").html('');
			$("#package_fleet_error").html('');
			$("#package_plan_error").html('');
		}
		/* package based checking */

		if(error_free_form == 0)
		{
			pass_log = $('#pass_logged_in').val();
			if(pass_log == 0)
			{
				save_details_req();
				$("#pickup_location_error").text('');
				$("#pickup_date_error").text('');
				$('.webbooking_container').hide();
				$("#front_form").css("display","none");
				$(".signup_container .signup_steps").hide();
				$(".signup_container,.signup_container .signup_steps.signup_step1").show();
				// window.location = 'signin.html';
			} else {
				window.localStorage.setItem('page_redirect', '0');
				submit_form($(this).val());
			}
		}
	});

	function save_details_req()
	{
		window.localStorage.setItem('modelIds', $('#modelIds').val());
		window.localStorage.setItem('pass_logged_in', $('#pass_logged_in').val());
		window.localStorage.setItem('pickup_location', $('#pickup_location').val());
		window.localStorage.setItem('pass_latitude', $('#pass_latitude').val());
		window.localStorage.setItem('pass_longitude', $('#pass_longitude').val());
		window.localStorage.setItem('pickup_latitude', $("#pickup_latitude").val());
		window.localStorage.setItem('pickup_longitude', $("#pickup_longitude").val());
		window.localStorage.setItem('pickup_date', $("#pickup_date").val());
		window.localStorage.setItem('ride_type', $('#ride_type').val());
		window.localStorage.setItem('drop_location', $('#drop_location').val());
		window.localStorage.setItem('drop_latitude', $('#drop_latitude').val());
		window.localStorage.setItem('drop_longitude', $('#drop_longitude').val());
		window.localStorage.setItem('select_model', $("#select_model").val());
		window.localStorage.setItem('select_model_name', $("#select_model_name").val());
		window.localStorage.setItem('page_redirect', $("#page_redirect").val());

		/* for package */
		window.localStorage.setItem('package_type', $("#package_type").val());
		window.localStorage.setItem('package_fleet', $("#package_fleet").val());
		window.localStorage.setItem('package_plan', $("#package_plan").val());
		window.localStorage.setItem('rental_outstation', $("#rental_outstation").val());
		window.localStorage.setItem('rent_out_plan_id', $("#rent_out_plan_id").val());
		window.localStorage.setItem('trip_type', $("#trip_type").val());
		window.localStorage.setItem('package_os_type', $("#package_os_type").val());
		window.localStorage.setItem('os_days_count', $("#os_days_count").val());
		
		window.localStorage.setItem('est_time_hrmin', $("#est_time_hrmin").val());
		window.localStorage.setItem('est_time_sec', $("#est_time_sec").val());
		/* for package */
	}

	$('.sel-model').on('click', function() {
		$("#select_model_error").text('');
		model = $(this).data('model');
		name = $(this).data('model_name');
		$('.item').removeClass('active');
		$(this).addClass('active');
		$('#select_model').val(model);
		$('#select_model_name').val(name);
		clear_error_class();
		if(model == 'pack')
		{
			$('.package-plan-sec').show();
			$('#trip_type').val('1');
		} else {
			$('#trip_type').val('0');
			$("#ride_now").show();
			$('.package-plan-sec').hide();
			$('.package-plan-os-sec').hide();
			$('.days_count').hide();
			$('.trip-description').hide();
			$('#package_type').val('');
			$('#package_fleet').val('');
			$('#package_plan').children('option:not(:first)').remove();			
			$('#rental_outstation').val('0');
			$('#rent_out_plan_id').val('0');
		}
		fare_estimate();
	});

	function clear_error_class()
	{
		$("#package_type").removeClass('inputerror');
		$("#package_os_type").removeClass('inputerror');
		$("#package_fleet").removeClass('inputerror');
		$("#package_plan").removeClass('inputerror');
		$('#pickup_location').removeClass('inputerror');
		$('#pickup_date').removeClass('inputerror');
	}

	function submit_form(now_after)
	{
		//savebooking api
	    $(".loader").css('display', 'block'); //show loader image
        var error_free_form = 0;
        var os_trip_type = '';
        var os_days_count = '';
        if(window.localStorage.getItem('page_redirect') == '1')
        {
        	var pickupdate_time = window.localStorage.getItem('pickup_date');
	        var passenger_id = $("#pass_logged_in").val();
	        var latitude = window.localStorage.getItem('pickup_latitude');
	        var longitude = window.localStorage.getItem('pickup_longitude');
	        var motor_model = window.localStorage.getItem('modelIds');
	        var pickupplace = window.localStorage.getItem('pickup_location');
	        var dropplace = window.localStorage.getItem('select_model');
	        var drop_latitude = window.localStorage.getItem('drop_latitude');
	        var drop_longitude = window.localStorage.getItem('drop_longitude');
	        var promo_code = '';
	        var sub_logid = passenger_id;
	        var rental_outstation = window.localStorage.getItem('rental_outstation');
	        var rent_out_plan_id = window.localStorage.getItem('rent_out_plan_id');

	        var est_time_hrmin = window.localStorage.getItem('est_time_hrmin');
	        var est_time_sec = window.localStorage.getItem('est_time_sec');
        } else {
        	var pickupdate_time = $("#pickup_date").val();
	        var passenger_id = $("#pass_logged_in").val();
	        var latitude = $("#pickup_latitude").val();
	        var longitude = $("#pickup_longitude").val();
	        var motor_model = $("#select_model").val();
	        var pickupplace = $("#pickup_location").val();
	        var dropplace = $("#drop_location").val();
	        var drop_latitude = $("#drop_latitude").val();
	        var drop_longitude = $("#drop_longitude").val();
	        var promo_code = '';
	        var sub_logid = passenger_id;
	        var rental_outstation = $("#rental_outstation").val();
	        var rent_out_plan_id = $("#rent_out_plan_id").val();
	        os_trip_type = $('#package_os_type').val();
            os_days_count = $("#os_days_count").val();
            
            var est_time_hrmin = $("#est_time_hrmin").val();
	        var est_time_sec = $("#est_time_sec").val();
        }
        
        var parameter = JSON.stringify({
            "passenger_id": passenger_id,
            "latitude": latitude,
            "longitude": longitude,
            "motor_model": motor_model,
            "pickup_time": pickupdate_time,
            "pickupplace": pickupplace,
            "dropplace": dropplace,
            "cityname": cityname,
            "sub_logid": sub_logid,
            "drop_latitude": drop_latitude,
            "drop_longitude": drop_longitude,
            "now_after": now_after,
            "rental_outstation": rental_outstation,
            "rent_out_tour_id": rent_out_plan_id,
            "promo_code": promo_code,
            "friend_id2": 0,
            "friend_percentage2": 0,
            "friend_id3": 0,
            "friend_percentage3": 0,
            "friend_id4": 0,
            "friend_percentage4": 0,
            "friend_id1": passenger_id,
            "friend_percentage1": 100,
            "os_trip_type" : os_trip_type,
            "os_days_count" : os_days_count,            
            "approx_duration" : est_time_hrmin,
            "approx_duration_sec" : est_time_sec,
            "bookingfront" : 1,
        });
            
        var formData = "input=" + parameter;
        $.ajax({
            url: URL_BASE + 'decrypt/encrypt',
            type: 'post',
            // dataType:'',
            data: formData,
            async: false,
            cache: false,
            success: function(encrypt_input) {
                url = URL_BASE + '/passengerapi113?type=savebooking&lang=' + current_language;
                $.ajax({
                    type: "POST",
                    url: url,
                    headers: {
                        "Authorization": WEB_API_KEY
                    },
                    data: encrypt_input,
                    cache: false,
                    //dataType: 'html',
                    success: function(encrypted_response) {
                        var formData = {
                            value: encrypted_response
                        };
                        
                        $.ajax({
                            url: URL_BASE + 'decrypt',
                            type: 'post',
                            // dataType:'',
                            data: formData,
                            async: false,
                            cache: false,
                            success: function(decrypted_output) {
                                decrypted_json = $.parseJSON(decrypted_output);
                                var appName = URL_BASE;
                                var actionType = 'package_plan_success';                                
                                var jsonString = (JSON.stringify(decrypted_json));
                                var escapedJsonParameters = escape(jsonString);
                                var appurl = appName + '' + actionType + "/" + encrypted_response;

                                $(".book_ride").removeAttr('disabled');

                                if (decrypted_json.status != 1) {
                                	$(".loader").css('display', 'none'); //remove loader image
                                    
                                    $('.msg-success-error').text(decrypted_json.message);
                                    $('.alert_msg').addClass('alert_failure');
                                    setTimeout(function(){
                                    	$('.alert_msg').removeClass('alert_failure');
	                                	$('.msg-success-error').text('');
	                                }, 4000);
                                } else {
                                	jQuery.post("users/set_alert_msgs", {success_msg: decrypted_json.message}, function(data){});
                                	setTimeout(function(){
                                    	$(location).attr('href', URL_BASE+'dashboard.html');
	                                }, 1000);
                                	$(location).attr('href', URL_BASE+'dashboard.html');
                                }
                                
                                $(".dialog-buttons").addClass("singlebtn");
                            },
                            error: function() {
                                $(".loader").css('display', 'none'); //remove loader image
                                $('.msg-success-error').text(some_problem_tryagain);
                                $('.alert_msg').addClass('alert_failure');
                                setTimeout(function(){
                                	$('.alert_msg').removeClass('alert_failure');
                                	$('.msg-success-error').text('');
                                }, 4000);
                                $(".book_ride").removeAttr('disabled');
                            }
                        });
                    },
                    error: function() {
                        $(".loader").css('display', 'none'); //remove loader image
                        $('.msg-success-error').text(some_problem_tryagain);
                        $('.alert_msg').addClass('alert_failure');
                        setTimeout(function(){
                        	$('.alert_msg').removeClass('alert_failure');
                        	$('.msg-success-error').text('');
                        }, 4000);
                        $(".book_ride").removeAttr('disabled');
                    }
                });
            },
            error: function() {
                $(".loader").css('display', 'none'); //remove loader image
                $('.msg-success-error').text(some_problem_tryagain);
                $('.alert_msg').addClass('alert_failure');
                setTimeout(function(){
                	$('.alert_msg').removeClass('alert_failure');
                	$('.msg-success-error').text('');
                }, 4000);
                $(".book_ride").removeAttr('disabled');
            },
        });
    }

	$(document).ready(function(){
		$("#pickup_location").on('keyup', function(){
			$("#pickup_location").removeClass('inputerror');
			$('#pickup_location_error').text('');
		});
		$("#drop_location").on('keyup', function(){
			$("#drop_location").removeClass('inputerror');
			$('#drop_location_error').text('');
		});
		$("#pickup_date").on('change', function(){
			$("#pickup_date").removeClass('inputerror');
			$('#pickup_date_error').text('');
		});
		$("#package_type").on('change', function(){
			$("#package_type").removeClass('inputerror');
			$('#package_type_error').text('');
		});
		$("#package_fleet").on('change', function(){
			$("#package_fleet").removeClass('inputerror');
			$('#package_fleet_error').text('');
		});
		$("#package_plan").on('change', function(){
			$("#package_plan").removeClass('inputerror');
			$('#package_plan_error').text('');
		});
		
		$("#mobile , #signin_mobile").keyup(function(event) {
	        //to allow left and right arrow key move
	        if (event.which >= 37 && event.which <= 40) {
	            return false;
	        }
	        this.value = this.value.replace(/[`~!@#$%^&*()\s_|+\-=?;:'",.<>\{\}\[\]\\\/A-Z]/gi, '');
    	});
    

		$("#back_page").click(function(){
			$("#front_form").css("display","none");
			$(".signup_container .signup_steps").hide();
			$(".signup_container, .signup_container .signup_steps.signup_step1").show();
		});

		$("#page_backward").click(function(){
			$(".signup_container .signup_steps").hide();
			$("#front_form").css("display","block");
			$('.webbooking_container').show();
		});

		$('#pickup_location').on('keyup', function(){
			$("#pickup_location").removeClass('inputerror');
			$("#pickup_tooltip").html("");
		});
		$('#drop_location').on('keyup', function(){
			$("#drop_location").removeClass('inputerror');
			$("#drop_tooltip").html("");
		});
		$('#pickup_date').on('keyup', function(){
			$("#pickup_location_error").text('');
		});

		$('#package_type').on('change', function() {
			sel = $(this).val();
			$('#package_fleet').val('');
			$('.package-plan-os-sec').hide();
			if(sel != '')
			{
				$('#rental_outstation').val(sel);
			} else {
				$('#rental_outstation').val('0');
			}
			if(sel == 2)
			{
				$('.package-plan-os-sec').show();
				// $('.days_count').show();
			} else {
				$('.package-plan-os-sec').hide();
				$('.days_count').hide();
			}
			fare_estimate();
		});

		$('#package_plan').on('change', function() {
			sel = $(this).val();
			if(sel != '')
			{
				$('#rent_out_plan_id').val(sel);
			} else {
				$('#rent_out_plan_id').val('0');
			}
			fare_estimate();
		});

		$(".otp_input").keyup(function () {
			if (this.value.length == this.maxLength){
			 var x=parseInt($(this).attr('tabindex'));
			 y=x+1;
			 $('[tabindex='+y+']').focus();
			}
		});
		window.onbeforeunload = function(){
			if($('#control_at').val() != '0')
			{
				return "<?php echo __('leave_confirm'); ?>";
			}
		};

		if(window.localStorage.getItem('page_redirect') == '1')
        {
        	$('#modelIds').val(window.localStorage.getItem('modelIds'));
			//$('#pass_logged_in').val(window.localStorage.getItem('pass_logged_in'));
			$('#pickup_location').val(window.localStorage.getItem('pickup_location'));
			$('#pass_latitude').val('');
			$('#pass_longitude').val('');
			$('#pickup_latitude').val(window.localStorage.getItem('pickup_latitude'));
			$('#pickup_longitude').val(window.localStorage.getItem('pickup_longitude'));
			$('#pickup_date').val(window.localStorage.getItem('pickup_date'));
			$('#ride_type').val(window.localStorage.getItem('ride_type'));
			$('#drop_location').val(window.localStorage.getItem('drop_location'));
			$('#drop_latitude').val(window.localStorage.getItem('drop_latitude'));
			$('#drop_longitude').val(window.localStorage.getItem('drop_longitude'));
			$('#select_model').val(window.localStorage.getItem('select_model'));
			$('#select_model_name').val(window.localStorage.getItem('select_model_name'));
			$('#page_redirect').val('0');

			$('#package_type').val(window.localStorage.getItem('package_type'));
			$('#package_fleet').val(window.localStorage.getItem('package_fleet'));			
			$('#rental_outstation').val(window.localStorage.getItem('rental_outstation'));
			$('#rent_out_plan_id').val(window.localStorage.getItem('rent_out_plan_id'));
			$('#trip_type').val(window.localStorage.getItem('trip_type'));

			if(window.localStorage.getItem('rental_outstation') == '2')
			{
				$('.package-plan-os-sec').show();
				$('#package_os_type').val(window.localStorage.getItem('package_os_type'));
				if(window.localStorage.getItem('package_os_type') == '2')
				{
					$('.days_count').show();
					$("#ride_now").hide();
					$('#os_days_count').val(window.localStorage.getItem('os_days_count'));
				} else {
					$("#ride_now").show();
					$('.days_count').hide();
				}
			} else {
				$('.package-plan-os-sec').hide();
			}

			$('.item').removeClass('active');
			
			$("div").find("[data-model='" + window.localStorage.getItem('select_model') + "']").addClass('active');

			if(window.localStorage.getItem('select_model') == 'pack')
			{
				$('.package-plan-sec').show();
				loadPackagePlans($('#package_fleet').val(), $('#package_type').val());
			} else {
				$('.package-plan-sec').hide();
			}
			$('#package_plan').val(window.localStorage.getItem('package_plan'));
			fare_estimate();
        }

        /* clear previously stored values */
        window.localStorage.clear();
		
		if(signup_status == "")
		{
			$("#front_form").css("display","block");
		} else if(signup_status == 1) {
			jQuery.post("users/destroy_signin_session");
			$("#front_form").css("display","none");
			$(".signup_container .signup_steps").hide();
			$(".signup_container, .signup_container .signup_steps.signup_step2").show();
		} else if(signup_status == 2) {
			jQuery.post("users/destroy_signin_session");
			$("#front_form").css("display","none");
			$(".signup_container .signup_steps").hide();
			$(".signup_container,.signup_container .signup_steps.signup_step3").show();
		} else if(signup_status == 3) {
			jQuery.post("users/destroy_signin_session");
			$("#front_form").css("display","block");
			$(".signup_container .signup_steps").hide();
			$(".signup_container, .signup_container .signup_steps.signup_step2").hide();
			na = $('#ride_type').val();
			submit_form(na);
		} else if(signup_status == 4) {
			jQuery.post("users/destroy_signin_session");
			$("#front_form").css("display","block");
			$(".signup_container .signup_steps").hide();
			$(".signup_container, .signup_container .signup_steps.signup_step2").hide();
		} else {
			jQuery.post("users/destroy_signin_session");
			$("#front_form").css("display","none");
			$(".signup_container .signup_steps").hide();
			$(".signup_container,.signup_container .signup_steps.signup_step1").show();
		}



		$("#signin_submit").click(function(){
			 var cc_flag = $('#cc_flag').attr('class').split(' ')[1];
			//var cc_flag = 'in';
			var error_free_form = 0;

			if($("#mobile").val() == '')
			{
				error_free_form = 1;	
			}
			if(error_free_form == 0)
			{ 
				$(".loader").css('display', 'block'); //show loader image
				var mobile_number = $("#mobile").val();				
				var country_code = $("#country_code_phoneCode").val();
			    var WEB_API_KEY = "<?php echo WEB_API_KEY; ?>";
			    var device_id = 1;
			    var device_type = 1;
			    var device_token = 1;
			    var fbuser_id = "<?php echo isset($_GET['fb']) ? base64_decode($_GET['fb']) : "" ?>";
			    
			    var parameter = JSON.stringify({
	                "phone": mobile_number,
	                "country_code": country_code,
	                "device_id": device_id,
	                "device_type": device_type,
	                "device_token": device_token,
	                "fbuser_id": fbuser_id
	            });
	            
	            var formData = "input=" + parameter;
			    $.ajax({
			        url: URL_BASE + 'decrypt/encrypt',
			        type: 'post',
			        data: formData,
			        async: false,
			        cache: false,
			        success: function(encrypt_input) {			        	
			        	url = URL_BASE + '/passengerapi113?type=signupwith_phone&lang=en';
	                    $.ajax({
	                        type: "POST",
	                        url: url,
	                        headers: {
	                            "Authorization": WEB_API_KEY
	                        },
	                        data: encrypt_input,
	                        cache: false,
	                        //dataType: 'html',
	                        success: function(encrypted_response) {
	                            // console.log('encrypted_response',encrypted_response);return false;
	                            var formData = {
	                                value: encrypted_response
	                            };

	                            $.ajax({
									url: URL_BASE + 'decrypt',
									type: 'post',
									data: formData,
									async: false,
									cache: false,
									success: function(decrypted_output){
										$(".loader").css('display', 'none'); //remove loader image
										$("#control_at").val('1');
										decrypted_json = JSON.parse(decrypted_output);
										var phone_exist = decrypted_json.phone_exist;
										var otp = decrypted_json.otp;
										var mobile_no = decrypted_json.detail.phone;
										var country_code = decrypted_json.detail.country_code;
										if(country_code.substring(0,1) == '+'){
											country_code = country_code.substring(1,country_code.length);
										}
										var otp_mobile = country_code+" "+mobile_no;
                                		var passenger_id = decrypted_json.detail.passenger_id;
                                		$("#pass_mobile").text(otp_mobile);

                                    	if(phone_exist == 0 || phone_exist == 2)
                                    	{
											$("#otpNumber").html(otp);
                                    		$(".signup_step1").hide();
                                    		$(".signup_step2").show();
                                    		var sign_up_time = new Date();

 											var sign_min = sign_up_time.getDate()  + "-" + (sign_up_time.getMonth()+1) + "-" + sign_up_time.getFullYear() + "," + sign_up_time.getHours() + ":" + sign_up_time.getMinutes() + ":" + sign_up_time.getSeconds();
 											var signup_status = 0;

                                    		jQuery.post("users/session_start_time", {signup_time: sign_min,signup_status: signup_status}, function(data)
											{

											})	                             
                                    	}else if(phone_exist == 3)
                                    	{
                                    		$("#passenger_id").val(passenger_id);
                                    		var country_code_sigin = $("#country_code_phoneCode").val();
                                    		var mobile_no = $("#mobile").val();
                                    		var number = "+"+country_code_sigin+" "+mobile_no;
                                    		$("#pass_mobile_signup").text(number);
                                    		$("#front_form").css("display","none");
											$(".signup_container .signup_steps").hide();
											$(".signup_container,.signup_container .signup_steps.signup_step3").show();
                                    	}
                                    	else{
                                    		var mobile_no = $("#mobile").val();
                                    	$(".cc-picker-code").text(country_code);
                                    		$("#signin_country_code_phoneCode").val(country_code);
                                    		$("#fp_country_code_phoneCode").val(country_code);
                                    		$("#country_flag_code .cc-picker-flag").removeClass('lt');
                                    	$("#country_flag_code .cc-picker-flag").addClass(cc_flag);
                                    		$("#signin_mobile").val(mobile_no);
                                    		$(".signup_container .signup_steps").hide();
                                    		$(".signup_container,.signup_container .signup_steps.signup_step5").addClass('no_mob_dropdown');
                                    		$(".signup_container,.signup_container .signup_steps.signup_step5").show();
                                    		/*var passenger_ride_type = $("#ride_type").val();
											submit_form(passenger_ride_type);*/
                                    	}
									},

									error: function() {
	                                    $(".loader").css('display', 'none'); //remove loader image
		                                $('.msg-success-error').text(some_problem_tryagain);
		                                $('.alert_msg').addClass('alert_failure');
		                                setTimeout(function(){
		                                	$('.alert_msg').removeClass('alert_failure');
		                                	$('.msg-success-error').text('');
		                                }, 4000);
		                                $(".book_ride").removeAttr('disabled');
	                                }	                            	
	                            });
	                        },

	                        failure: function(encrypted_response) {
	                        	$(".loader").css('display', 'none'); //remove loader image
                                $('.msg-success-error').text(some_problem_tryagain);
                                $('.alert_msg').addClass('alert_failure');
                                setTimeout(function(){
                                	$('.alert_msg').removeClass('alert_failure');
                                	$('.msg-success-error').text('');
                                }, 4000);
                                $(".book_ride").removeAttr('disabled');
	                        }
	                    });
			        },
			        failure: function(encrypt_input) {
			        	$(".loader").css('display', 'none'); //remove loader image
                        $('.msg-success-error').text(some_problem_tryagain);
                        $('.alert_msg').addClass('alert_failure');
                        setTimeout(function(){
                        	$('.alert_msg').removeClass('alert_failure');
                        	$('.msg-success-error').text('');
                        }, 4000);
                        $(".book_ride").removeAttr('disabled');
			        }
			    });
			}else{
				$(".mob_no,#mobile").addClass("inputerror");
				$("#mobileno_tooltip").html("<?php echo __('mobilenumber_cannot_beempty'); ?>");
				return false;
			}
		});

		$("#signup_button_id").click(function(){
			var error_free_form = 0;
			var fb_userid = "<?php echo isset($_GET['fb']) ? base64_decode($_GET['fb']) : "" ?>";
			var mobile_number = $("#mobile").val();
			var country_code = $("#country_code_phoneCode").val();
		    // var country_code = '+'+cc.trim();
		    var WEB_API_KEY = "<?php echo WEB_API_KEY; ?>";
		    var otp_1 = $("#otp_val1").val();
		    var otp_2 = $("#otp_val2").val();
		    var otp_3 = $("#otp_val3").val();
		    var otp_4 = $("#otp_val4").val();
		    var otp_val = otp_1+otp_2+otp_3+otp_4;
		    if(otp_val == '')
		    {
		    	error_free_form = 1;
		    }
		    if(error_free_form == 0){
		    	$(".loader").css('display', 'block'); //show loader image
			    var parameter = JSON.stringify({
				                "phone": mobile_number,
				                "country_code": country_code,
				                "otp": otp_val,
				                "fbuser_id": fb_userid
				            });
				var formData = "input=" + parameter;
				$.ajax({
			        url: URL_BASE + 'decrypt/encrypt',
			        type: 'post',
			        data: formData,
			        async: false,
			        cache: false,
			        success: function(encrypt_input) {
			        	url = URL_BASE + '/passengerapi113?type=phoneotp_verify&lang=en';
		                    $.ajax({
		                        type: "POST",
		                        url: url,
		                        headers: {
		                            "Authorization": WEB_API_KEY
		                        },
		                        data: encrypt_input,
		                        cache: false,
		                        //dataType: 'html',
		                        success: function(encrypted_response) {
		                             //console.log('encrypted_response',encrypted_response);return false;
		                            var formData = {
		                                value: encrypted_response
		                            };

		                            $.ajax({
										url: URL_BASE + 'decrypt',
										type: 'post',
										data: formData,
										async: false,
										cache: false,
										success: function(decrypted_output){
											$(".loader").css('display', 'none'); //remove loader image
											decrypted_json = JSON.parse(decrypted_output);
											
											if(decrypted_json.status == 1)
											{
												$('.msg-success-error').text("<?php echo __('otp_verified'); ?>");
			                                    $('.alert_msg').addClass('alert_success');
			                                    setTimeout(function(){
			                                    	$('.alert_msg').removeClass('alert_success');
				                                	$('.msg-success-error').text('');
				                                }, 4000);
				                                var country_code_sigin = $("#country_code_phoneCode").val();
	                                    		var mobile_no = $("#mobile").val();
	                                    		var number = "+"+country_code_sigin+" "+mobile_no;
	                                    		$("#pass_mobile_signup").text(number);
												if(fb_userid != ''){
													$('#control_at').val('0');
													var fb_user_id = decrypted_json.detail.fbuser_id;
													var username = decrypted_json.detail.name;
													var email = decrypted_json.detail.email;
													var id = decrypted_json.detail.id;
													var phone = decrypted_json.detail.phone;
													var phone_code = decrypted_json.detail.country_code;
		                                    		var sign_up_time = new Date();
		                                    		var sign_min = sign_up_time.getDate()  + "-" + (sign_up_time.getMonth()+1) + "-" + sign_up_time.getFullYear() + "," + sign_up_time.getHours() + ":" + sign_up_time.getMinutes() + ":" + sign_up_time.getSeconds();
		 											if($('#direct_sigin').val() == 1)
													{
														var signup_status = 4;
													} else {
														var signup_status = 3;
													}
													
													jQuery.post("users/set_passenger_value", {username: username,email: email,id: id,signup_status: signup_status, phone: phone,phone_code: phone_code}, function(data){

														
													var url = "<?php echo URL_BASE ?>users/page_redirect";
													$(location).attr('href',url);
														
													});
		 											/*jQuery.post("users/fbuser_start_time", {
		                                    			username: username,
		                                    			email: email,
		                                    			id: id,
		                                    			phone: phone,
		                                    			phone_code: phone_code,
		                                    			signup_time: sign_min}, function(data)
													{});*/
													
		                                    	} else{
													$('.signup_steps').css('display','none');
													$('.signup_step3').css('display','block');
													$("#passenger_id").val(decrypted_json.detail.passenger_id);
		                                    	}
											}else if(decrypted_json.status == -2){
												$('.msg-success-error').text(decrypted_json.message);
				                                $('.alert_msg').addClass('alert_failure');
				                                setTimeout(function(){
				                                	$('.alert_msg').removeClass('alert_failure');
				                                	$('.msg-success-error').text('');
				                                }, 4000);
											}
										},

										error: function() {
		                                    $(".loader").css('display', 'none'); //remove loader image
			                                $('.msg-success-error').text(some_problem_tryagain);
			                                $('.alert_msg').addClass('alert_failure');
			                                setTimeout(function(){
			                                	$('.alert_msg').removeClass('alert_failure');
			                                	$('.msg-success-error').text('');
			                                }, 4000);
			                                $(".book_ride").removeAttr('disabled');
		                                }	                            	
		                            });
		                        },

		                        failure: function(encrypted_response) {
		                        	$(".loader").css('display', 'none'); //remove loader image
	                                $('.msg-success-error').text(some_problem_tryagain);
	                                $('.alert_msg').addClass('alert_failure');
	                                setTimeout(function(){
	                                	$('.alert_msg').removeClass('alert_failure');
	                                	$('.msg-success-error').text('');
	                                }, 4000);
	                                $(".book_ride").removeAttr('disabled');
		                        }
		                    });
			        },
			        failure: function(encrypt_input) {
			        	$(".loader").css('display', 'none'); //remove loader image
                        $('.msg-success-error').text(some_problem_tryagain);
                        $('.alert_msg').addClass('alert_failure');
                        setTimeout(function(){
                        	$('.alert_msg').removeClass('alert_failure');
                        	$('.msg-success-error').text('');
                        }, 4000);
                        $(".book_ride").removeAttr('disabled');
			        }
			    });
			}else{
				$(".otp_block #otp_error").text("Enter your OTP");
			}
		});

		$("#resend_otp").click(function(){
			$(".loader").css('display', 'block'); //show loader image
			var mobile_number = $("#mobile").val();
			var country_code = $("#country_code_phoneCode").val()
		    // var country_code = '+'+cc.trim();
		    var WEB_API_KEY = "<?php echo WEB_API_KEY; ?>";
		    var device_type = 1;
		    var parameter = JSON.stringify({
			                "phone": mobile_number,
			                "country_code": country_code,
			                "device_type": device_type
			            });
			var formData = "input=" + parameter;
			$.ajax({
		        url: URL_BASE + 'decrypt/encrypt',
		        type: 'post',
		        data: formData,
		        async: false,
		        cache: false,
		        success: function(encrypt_input) {
		        	url = URL_BASE + '/passengerapi113?type=resend_phoneotp&lang=en';
	                    $.ajax({
	                        type: "POST",
	                        url: url,
	                        headers: {
	                            "Authorization": WEB_API_KEY
	                        },
	                        data: encrypt_input,
	                        cache: false,
	                        //dataType: 'html',
	                        success: function(encrypted_response) {
	                            // console.log('encrypted_response',encrypted_response)
	                            var formData = {
	                                value: encrypted_response
	                            };

	                            $.ajax({
									url: URL_BASE + 'decrypt',
									type: 'post',
									data: formData,
									async: false,
									cache: false,
									success: function(decrypted_output){
										$(".loader").css('display', 'none'); //remove loader image
										decrypted_json = JSON.parse(decrypted_output);
										var otp = decrypted_json.otp;
										$("#otpNumber").html(otp);
										$(".loader").css('display', 'none'); //remove loader image
				                        $('.msg-success-error').text(decrypted_json.message);
				                        $('.alert_msg').addClass('alert_success');
				                        setTimeout(function(){
				                        	$('.alert_msg').removeClass('alert_success');
				                        	$('.msg-success-error').text('');
				                        }, 4000);
				                        $(".book_ride").removeAttr('disabled');
									},

									error: function() {
	                                    $(".loader").css('display', 'none'); //remove loader image
				                        $('.msg-success-error').text(some_problem_tryagain);
				                        $('.alert_msg').addClass('alert_failure');
				                        setTimeout(function(){
				                        	$('.alert_msg').removeClass('alert_failure');
				                        	$('.msg-success-error').text('');
				                        }, 4000);
				                        $(".book_ride").removeAttr('disabled');
	                                }	                            	
	                            });
	                        },

	                        failure: function(encrypted_response) {
	                        	$(".loader").css('display', 'none'); //remove loader image
		                        $('.msg-success-error').text(some_problem_tryagain);
		                        $('.alert_msg').addClass('alert_failure');
		                        setTimeout(function(){
		                        	$('.alert_msg').removeClass('alert_failure');
		                        	$('.msg-success-error').text('');
		                        }, 4000);
		                        $(".book_ride").removeAttr('disabled');
	                        }
	                    });
		        },
		        failure: function(encrypt_input) {
		        	$(".loader").css('display', 'none'); //remove loader image
                    $('.msg-success-error').text(some_problem_tryagain);
                    $('.alert_msg').addClass('alert_failure');
                    setTimeout(function(){
                    	$('.alert_msg').removeClass('alert_failure');
                    	$('.msg-success-error').text('');
                    }, 4000);
                    $(".book_ride").removeAttr('disabled');
		        }
		    });
		});

		$("#passenger_signin_submit").click(function(){
			var error_free_form = 0;
			var passenger_name = $("#pass_name").val();
			var passenger_email = $("#pass_email").val();
			var passenger_password = $("#pass_password").val();
			var passenger_id = $("#passenger_id").val();
		    //var referral_code = "";
		    var device_id = 1;
		    var device_type = 1;
		    var device_token = 1;
		    var WEB_API_KEY = "<?php echo WEB_API_KEY; ?>";
		    if(passenger_name == ""){
		    	error_free_form = 1;
		    	$("#pass_name").addClass("inputerror");
		    	$("#passname_tooltip").html("<?php echo __('name_not_empty'); ?>");
		    }else{
		    	$("#pass_name").removeClass("inputerror");
		    	$("#pass_name").html("");
		    }
		    if(passenger_email == ""){
		    	error_free_form = 1;
		    	$("#pass_email").addClass("inputerror");
		    	$("#mail_tooltip").html("<?php echo __('mail_not_empty'); ?>");
		    	
		    }else{
		    	$("#pass_email").removeClass("inputerror");
		    	$("#pass_email").html("");
		    }
		    if(passenger_password == ""){
		    	error_free_form = 1;
		    	$("#pass_password").addClass("inputerror");	
		    	$("#password_tooltip").html("<?php echo __('pwd_not_empty'); ?>");
		    }else if(passenger_password.length < 6){
		    	error_free_form = 1;
		    	$("#pass_password").addClass("inputerror");	
		    	$("#password_tooltip").html("<?php echo __('atleast_six'); ?>");
		    }else{
		    	$("#pass_password").removeClass("inputerror");		
		    	$("#password_tooltip").html("");
		    }

		    if(error_free_form == 1)
		    {
		    	return false;
		    }

		    var parameter = JSON.stringify({
			                "name": passenger_name,
			                "email": passenger_email,
			                "password": passenger_password,
			                "passenger_id": passenger_id,
			                "device_id": device_id,
			                "device_type": device_type,
			                "device_token": device_token
			            });
			var formData = "input=" + parameter;
			$.ajax({
		        url: URL_BASE + 'decrypt/encrypt',
		        type: 'post',
		        data: formData,
		        async: false,
		        cache: false,
		        success: function(encrypt_input) {
		        	url = URL_BASE + '/passengerapi113?type=passenger_signup_completion&lang=en';
	                    $.ajax({
	                        type: "POST",
	                        url: url,
	                        headers: {
	                            "Authorization": WEB_API_KEY
	                        },
	                        data: encrypt_input,
	                        cache: false,
	                        //dataType: 'html',
	                        success: function(encrypted_response) {
	                            $('#control_at').val('0');
	                            var formData = {
	                                value: encrypted_response
	                            };

	                            $.ajax({
									url: URL_BASE + 'decrypt',
									type: 'post',
									data: formData,
									async: false,
									cache: false,
									success: function(decrypted_output){
										$(".loader").css('display', 'none'); //remove loader image
										decrypted_json = JSON.parse(decrypted_output);
										//console.log("passenger_signup_completion",decrypted_output);
										if(decrypted_json.status == 1)
										{
											
											var username = decrypted_json.detail.name;
											var email = decrypted_json.detail.email;
											var id = decrypted_json.detail.id;
											var phone = decrypted_json.detail.phone;
											var phone_code = decrypted_json.detail.country_code;
											if($('#direct_sigin').val() == 1)
											{
												var signup_status = 4;
											} else {
												var signup_status = 3;
											}
											
											jQuery.post("users/set_passenger_value", {username: username,email: email,id: id,signup_status: signup_status, phone: phone,phone_code: phone_code}, function(data)
												{
													var url = "<?php echo URL_BASE ?>users/page_redirect";
													$(location).attr('href',url);
												});
											/*$('.msg-success-error').text("<?php echo __('passenger_complete'); ?>");
		                                    $('.alert_msg').addClass('alert_success');
		                                    setTimeout(function(){
		                                    	$('.alert_msg').removeClass('alert_success');
			                                	$('.msg-success-error').text('');
			                                }, 4000);*/
											
										} else {
											$('.msg-success-error').text(decrypted_json.message);
						                    $('.alert_msg').addClass('alert_failure');
						                    setTimeout(function(){
						                    	$('.alert_msg').removeClass('alert_failure');
						                    	$('.msg-success-error').text('');
						                    }, 4000);
						                    $(".book_ride").removeAttr('disabled');
										}
									},

									error: function() {
	                                    $(".loader").css('display', 'none'); //remove loader image
					                    $('.msg-success-error').text(some_problem_tryagain);
					                    $('.alert_msg').addClass('alert_failure');
					                    setTimeout(function(){
					                    	$('.alert_msg').removeClass('alert_failure');
					                    	$('.msg-success-error').text('');
					                    }, 4000);
					                    $(".book_ride").removeAttr('disabled');
	                                }	                            	
	                            });
	                        },

	                        failure: function(encrypted_response) {
	                        	$(".loader").css('display', 'none'); //remove loader image
			                    $('.msg-success-error').text(some_problem_tryagain);
			                    $('.alert_msg').addClass('alert_failure');
			                    setTimeout(function(){
			                    	$('.alert_msg').removeClass('alert_failure');
			                    	$('.msg-success-error').text('');
			                    }, 4000);
			                    $(".book_ride").removeAttr('disabled');
	                        }
	                    });
		        },
		        failure: function(encrypt_input) {
		        	$(".loader").css('display', 'none'); //remove loader image
                    $('.msg-success-error').text(some_problem_tryagain);
                    $('.alert_msg').addClass('alert_failure');
                    setTimeout(function(){
                    	$('.alert_msg').removeClass('alert_failure');
                    	$('.msg-success-error').text('');
                    }, 4000);
                    $(".book_ride").removeAttr('disabled');
		        }
		    });
		});

		$("#submit_signin").click(function(){
			var error_free_form = 1;
			//$(".loader").css('display', 'block'); //show loader image
			var mobile_number = $("#mobile").val();
			var country_code = $("#signin_country_code_phoneCode").val();
		    // var country_code = '+'+cc.trim();
		    var password = $("#signin_password").val();
		    var WEB_API_KEY = "<?php echo WEB_API_KEY; ?>";
		    var device_id = 1;
		    var device_type = 1;
		    var device_token = 1;

		    if(password == '')
		    { 
		    	$("#signin_password").addClass("inputerror");
		    	error_free_form = 0;
		    	$("#passpwd_tooltip").html("<?php echo __('pwd_not_empty'); ?>");
		    	return false;
		    }
		    else
		    {
		    	$("#passpwd_tooltip").html('');
		    	error_free_form = 1;
		    	$(".loader").css('display', 'block'); //show loader image
		    }

		if(error_free_form == 1)
		{
		    var parameter = JSON.stringify({
                "phone": mobile_number,
                "password": password,
                "country_code": country_code,
                "deviceid": device_id,
                "devicetype": device_type,
                "devicetoken": device_token
            });
		    var formData = "input=" + parameter;
		    $.ajax({
		        url: URL_BASE + 'decrypt/encrypt',
		        type: 'post',
		        data: formData,
		        async: false,
		        cache: false,
		        success: function(encrypt_input) {
		        	url = URL_BASE + '/passengerapi113?type=passenger_login&lang=en';
	                    $.ajax({
	                        type: "POST",
	                        url: url,
	                        headers: {
	                            "Authorization": WEB_API_KEY
	                        },
	                        data: encrypt_input,
	                        cache: false,
	                        //dataType: 'html',
	                        success: function(encrypted_response) {
	                            //console.log('encrypted_response',encrypted_response)
	                            var formData = {
	                                value: encrypted_response
	                            };

	                            $.ajax({
									url: URL_BASE + 'decrypt',
									type: 'post',
									data: formData,
									async: false,
									cache: false,
									success: function(decrypted_output){
										$(".loader").css('display', 'none'); //remove loader image
										decrypted_json = JSON.parse(decrypted_output);
										if(decrypted_json.status == 1)
										{
											$('#control_at').val('0');
											var username = decrypted_json.detail.name;
											var email = decrypted_json.detail.email;
											var id = decrypted_json.detail.id;
											var phone = decrypted_json.detail.phone;
											var phone_code = decrypted_json.detail.country_code;
											if($('#direct_sigin').val() == 1)
											{
												var signup_status = 4;
											} else {
												var signup_status = 3;
											}
											
											jQuery.post("users/set_passenger_value", {username: username,email: email,id: id,signup_status: signup_status, phone: phone,phone_code: phone_code}, function(data)
												{
													var url = "<?php echo URL_BASE ?>users/page_redirect";
													$(location).attr('href',url);
												})
											
										} else {
											$('.msg-success-error').text(decrypted_json.message);
		                                    $('.alert_msg').addClass('alert_failure');
		                                    setTimeout(function(){
		                                    	$('.alert_msg').removeClass('alert_failure');
			                                	$('.msg-success-error').text('');
			                                }, 5000);
										}
									},
									error: function() {
	                                    $(".loader").css('display', 'none'); //remove loader image
					                    $('.msg-success-error').text(some_problem_tryagain);
					                    $('.alert_msg').addClass('alert_failure');
					                    setTimeout(function(){
					                    	$('.alert_msg').removeClass('alert_failure');
					                    	$('.msg-success-error').text('');
					                    }, 4000);
					                    $(".book_ride").removeAttr('disabled');
	                                }	                            	
	                            });
	                        },
	                        failure: function(encrypted_response) {
	                        	$(".loader").css('display', 'none'); //remove loader image
			                    $('.msg-success-error').text(some_problem_tryagain);
			                    $('.alert_msg').addClass('alert_failure');
			                    setTimeout(function(){
			                    	$('.alert_msg').removeClass('alert_failure');
			                    	$('.msg-success-error').text('');
			                    }, 4000);
			                    $(".book_ride").removeAttr('disabled');
	                        }
	                    });
		        },
		        failure: function(encrypt_input) {
		        	$(".loader").css('display', 'none'); //remove loader image
                    $('.msg-success-error').text(some_problem_tryagain);
                    $('.alert_msg').addClass('alert_failure');
                    setTimeout(function(){
                    	$('.alert_msg').removeClass('alert_failure');
                    	$('.msg-success-error').text('');
                    }, 4000);
                    $(".book_ride").removeAttr('disabled');
		        }
		    });
		}
		});
	});

	/* load package/model based plans */
	function loadModelFleets(selected)
	{
		model = $('#package_fleet').val();
		$('#package_fleet').find('option:first').attr('selected', 'selected');
		if(selected == 2){
			$("#ride_now").hide();
			$("#ride_later").addClass("active");
			$('#tripDescription').show();
		}else{
			$("#ride_later").removeClass("active");
			$("#ride_now").show();
			$('#tripDescription').hide();
		}
		if(selected != '' && model != '')
		{
			loadPackagePlans(selected, model);
		} else {
			$('#package_plan').children('option:not(:first)').remove();
			//$('#package_plan').append('<option value="" >No plans</option>');
		}
	}
	/* load package/model based plans */

	/* package based plan */
	function loadPackagePlans(model, type)
	{
		var os_type = $('#package_os_type').val()
		if(model != '')
		{
			if (type === undefined)
			{
				type = $('#package_type').val();
			}
			$("#select_model").val(model);
			var selected = '';

			if(window.localStorage.getItem('package_plan') != 'null')
			{
				var selected = window.localStorage.getItem('package_plan');
			}
			
			$.ajax({
				url : "<?php echo URL_BASE; ?>users/get_selected_packages",
				data : { 'type' : type, 'model' : model, 'selected' : selected },
				type : 'POST',
				dataTtype : 'JSON',
				success : function(data) {	
				var response = JSON.parse(data);
					if(data != '')
					{
						$('#package_plan').children('option:not(:first)').remove();
						$('#package_plan').append(response.plans);
						$('#hidden_plan_duration').val(response.hour);
						if(os_type == 1 && response.hour == 24){
							$("#tripDescription").html('24 hours one-way trip');
						}else if(os_type == 1 && response.hour == 12){
							$("#tripDescription").html('12 hours one-way trip');
						}
						if(response.hour == 24){
							$('div#os_trip_type_count select').val(1);
							$("div#os_trip_type_count select option[value=0]").attr("disabled","disabled");
							//$("#tripDescription").html('24 hour(s) one-way trip');
						}else{
							$("div#os_trip_type_count select option[value=0]").removeAttr('disabled');
							$('div#os_trip_type_count select').val(0);
							//$("#tripDescription").html('12 hour(s) one-way trip');
						}
					} else {
						$('#package_plan').children('option:not(:first)').remove();
						$('#package_plan').append('<option value="" >No plans</option>');
					}
					fare_estimate();
				},
				error : function() {

				}
			});
		} else {
			$('#package_plan').children('option:not(:first)').remove();
			$('#package_plan').append('<option value="" >No plans</option>');
		}
	}
	/* package based plan */

	function loadPackageDetail(selected)
	{
		if(selected > 0)
		{
			$.ajax({
				url : "<?php echo URL_BASE; ?>users/get_package_detail",
				data : { 'plan' : selected },
				type : 'POST',
				dataTtype : 'JSON',
				success : function(data) {
					op = JSON.parse(data);
					if(op != '')
					{
						$('#package_base_fare').val(op.base_fare);
						$('#package_distance').val(op.distance);
						$('#package_plan_duration').val(op.plan_duration);
						$('#package_addl_distance_fare').val(op.additional_fare_per_distance);
						$('#package_addl_hour_fare').val(op.additional_fare_per_hour);
						outstation_type_options();
					}
				},
				error : function() {

				}
			});
		}
	}
	
	function search_nearest_driver_location_new_design(){//1-add,2-edit
		formflag = 1;
		var post_param = [];

        post_param.push({"name":"pickup_lat","value":$("#pickup_latitude").val()});
        post_param.push({"name":"pickup_lng","value":$("#pickup_longitude").val()});
        post_param.push({"name":"no_passengers","value":4});
        post_param.push({"name":"model_minfare","value":4});
        post_param.push({"name":"taxi_model","value":$("#select_model").val()});
        post_param.push({"name":"luggage","value":0});
        post_param.push({"name":"pickup_date","value":$("#pickup_date").val()});
		post_param.push({"name":"formflag","value":formflag});

		var Path = "<?php echo URL_BASE; ?>";
		var url_path = Path+"taxidispatch/search_nearest_driver_location_new_design";
		var response;
		
		$.ajax({
			url: url_path, 
			data: post_param, 
			cache: false, 
			success: function(response){
				/*$('.est_arr_time').html('--');
				$("#driver_listing_hidden_sec").html(response);
				//console.log(response);
				var driver_count = $("#driver_count").val();
				var driver_count_text = '';
				if(driver_count>0){
					var driver_count_text = ' ('+driver_count+') ';					
				}
				$(".driver_est_hidden_sec").html(driver_count_text);				
				if(driver_count>0){
					$(".est_arr_time").html($("#driver_current_arrival").val());
				} else {
					$('.est_arr_time').html('--');					
				}*/
			}		 
		});	
	}

$(document).click(function(event) {       
    if (!$(event.target).is("#country_flag_code, .cc-picker-code") && !$(event.target).closest(".cc-picker-code-list").length) {
    	$('.cc-picker-code-list').hide();
    }
});
$(".cc-picker-code-list").click(function(e) {          
 	e.stopPropagation();         
});

function secondsToHms(d) {
    d = Number(d);
    var h = Math.floor(d / 3600);
    var m = Math.floor(d % 3600 / 60);
    // var s = Math.floor(d % 3600 % 60);

    var hDisplay = h > 0 ? h + " hrs " : "";
    var mDisplay = m > 0 ? m + " mts" : "";
    // var sDisplay = s > 0 ? s + (s == 1 ? " sec" : " secs") : "";
    return hDisplay + mDisplay; 
}
</script>
