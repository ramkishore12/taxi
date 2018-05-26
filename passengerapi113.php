<?php defined('SYSPATH') or die('No direct script access.');

/****************************************************************

* Contains API details - Version 8.0.0

* @Package: Taximobility

* @Author:  NDOT Team

* @URL : http://www.ndot.in

****************************************************************/
Class Model_Passengerapi113 extends Model_ModPassengerapi113
{
	public function add_passenger_carddata($array, $referred_passenger_id = null,$preTransactId = '', $preTransactAmount = '', $cardTypeDesc = '')
    {
        try {
            $p_email        = $array['email'];
            $passenger_id   = $array['passenger_id'];
            $creditcard_no  = $array['creditcard_no'];
            $creditcard_no  = encrypt_decrypt('encrypt', $creditcard_no);
            $creditcard_cvv = $array['creditcard_cvv'];
            $expdatemonth   = $array['expdatemonth'];
            $expdateyear    = $array['expdateyear'];
            $card_type      = $array['card_type'];
            $default        = $array['default'];
            $auth_code 		= isset($array['auth_code'])?$array['auth_code']:'';
			
			$args = array(array('$unwind' => '$creditcard_details'),
						  array('$sort' => array('creditcard_details.passenger_cardid' => -1)),
						  array('$project' => array('card_id' => '$creditcard_details.passenger_cardid')),
						  array('$limit' => 1)
						  );
			$get_id = $this->mongo_db->aggregate(MDB_PASSENGERS,$args);
			$inc_id = (isset($get_id['result'][0]['card_id']) && !empty($get_id['result'][0]['card_id'])) ? $get_id['result'][0]['card_id'] : 0;
			$inc_id +=1;			
			$update_array = array("creditcard_details"=>array(
								'passenger_cardid' => (int)$inc_id,
								'passenger_id' => (int)$passenger_id,
								'passenger_email' => $p_email,
								'card_type' => $card_type,
								'creditcard_no' => $creditcard_no,
								'expdatemonth' => $expdatemonth,
								'expdateyear' => $expdateyear,
								'default_card' => (int)$default,
								'creditcard_cvv' => $creditcard_cvv,
								'auth_code' => $auth_code,
								'status' => 1,
								"createdate" => Commonfunction::MongoDate(strtotime($this->currentdate_bytimezone)),
								'pre_transaction_id' => $preTransactId,
								'pre_transaction_amount' => $preTransactAmount,
								'card_type_description' => $cardTypeDesc,
								'status' =>1,																
								'void_status' => 1 ));
            if ($default == 1) {
				$match = array('_id'=>(int)$passenger_id);
				$args = array(array('$unwind' => '$creditcard_details'),
						  array('$match' => array('_id' => (int)$passenger_id)),
						  array('$project' => array('card_id' => '$creditcard_details.passenger_cardid'))
						);
				$keys = $this->mongo_db->aggregate(MDB_PASSENGERS,$args);
				$val = array();
				if(!empty($keys['result'])){
					$i=0;
					foreach($keys['result'] as $k => $v ){
						$val["creditcard_details.".$i.".default_card"] = 0;
						$i++;
					}
					$def_update          = $val;
					$match['creditcard_details.passenger_id'] = (int)$passenger_id;
					$update = $this->mongo_db->updateOne(MDB_PASSENGERS,$match,array('$set'=>$def_update),array('upsert' => false));
				}				
            }
			$result = $this->mongo_db->updateOne(MDB_PASSENGERS,array('_id'=>(int)$passenger_id),
											  array('$push'=>$update_array),
											  array('upsert' => false));
            return $inc_id;
        }
        catch (Kohana_Exception $e) {
            return 0;
        }
    }
    public function send_pushnotification($d_device_token="",$device_type="",$pushmessage=null,$android_api="")
    { 
	   if($device_type == 1)
	   {			
			# FCM
			if($d_device_token != ''){
				$url = 'https://fcm.googleapis.com/fcm/send';
				#prep the bundle
				$pushmessage = json_encode($pushmessage);
				$fields = array
						(
							'to' => $d_device_token,
							'data' => array( "message" => $pushmessage),
						);
				$headers = array
						(
							'Authorization: key='.$android_api ,
							'Content-Type: application/json'
						);
				#Send Reponse To FireBase Server	
				$ch = curl_init();
				curl_setopt( $ch,CURLOPT_URL, $url );
				curl_setopt( $ch,CURLOPT_POST, true );
				curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
				curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
				$result = curl_exec($ch );
				curl_close( $ch );
			}
						
		}
		elseif($device_type == 2)
		{                          
			//---------------------------------- IPHONE ----------------------------------// 
			$deviceToken = trim($d_device_token);                                                                                      
			if(!empty($deviceToken))
			{
				// Put your private key's passphrase here:
				$passphrase = '1234';
				// Put your alert message here:                                 
				$badge = 0;
				////////////////////////////////////////////////////////////////////////////////
				if(file_exists($_SERVER['DOCUMENT_ROOT'].'/'.PUBLIC_UPLOADS_FOLDER.'/iOS/push_notification/ck.pem')){
					$root = $_SERVER['DOCUMENT_ROOT'].'/'.PUBLIC_UPLOADS_FOLDER.'/iOS/push_notification/ck.pem';
				}else{
					$root = $_SERVER['DOCUMENT_ROOT'].'/application/classes/controller/ck.pem' ;
				}				
				$ctx = stream_context_create();
				stream_context_set_option($ctx, 'ssl', 'local_cert',$root );
				stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

				// Open a connection to the APNS server
				$fp = stream_socket_client(
					'ssl://gateway.push.apple.com:2195', $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
				if (!$fp)
					exit("Failed to connect: $err $errstr" . PHP_EOL);
				//echo 'Connected to APNS' . PHP_EOL; 
				// Create the payload body
				//$message=$pushmessage['message'];
				$message = "Success";
				$badge = isset($pushmessage['badge']) ? $pushmessage['badge']:'0';
				$body['aps'] = array(
					'alert' => $message,
					'trip_details' => $pushmessage,
					'sound' => 'default',
					'badge' => $badge
					);	
				// Encode the payload as JSON
				$payload = json_encode($body);
				//print_r($payload);exit;
				// Build the binary notification
				$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
				// Send it to the server
				$result = fwrite($fp, $msg, strlen($msg));
				fclose($fp);  
			} 
		}		
	}
}
