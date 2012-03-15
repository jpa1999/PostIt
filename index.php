<?php

$data_path = "../data/";

$id 		= ( !empty( $_POST['id'] ) )? $_POST['id'] : $_GET['id'];
$id 		= cleanString( $id );

$posting 	= ( !empty( $_POST['posting'] ) )? $_POST['posting'] : $_GET['posting'];
$posting 	= cleanString( $posting );

$state 		= cleanString( $_GET['state'] );
$email 		= cleanString( $_GET['email'] );
$q 			= ( !empty( $_POST['q'] ) )? $_POST['q'] : $_GET['q'];

//---------------------------------
// Bind to Wordpress
//---------------------------------
define('WP_USE_THEMES', false);
require_once('../../../../wp-blog-header.php');
header("HTTP/1.1 200 OK");
header("Status: 200 All rosy") ;


//---------------------------------
// CHECK FOR ADMIN
//---------------------------------
if ( !current_user_can('manage_options') && $q != "register" && $q !="cron" ) {
	die( "Please login" );	
}

//---------------------------------
// GetMailer
//---------------------------------
require_once('event_mailer.class.php');

//---------------------------------
// Get data from wordpress page
//---------------------------------
if( !empty( $id )){ 
	// Get mecessary properties from post to single array
	// So you do not have to remember if they come from page or meta 
	$data_items = getDataItems( $id );

}

//--------------------------
// PATHS
//---------------------------
$paths = getPaths( $id, $data_path );
//--------------------------
// Register
//---------------------------
if( $q == "register" ){
	
	// CAN NON-INVITED REGISTER?
	checkEmail( $email );
	
	echo "Kiitos rekisteröitymisestä!";
	
	addLine(  $paths["register"]['registered'], $email  );
	addLine( $paths["polls"]['to_be_sended'], $email );
	addLine( $paths["reminders_registered"]['to_be_sended'], $email );
	
	removeLine( $paths["reminders_not_registered"]['to_be_sended'], $email );
	
	//Send mail to registered
	if( validEmail( $email ) ){
		
		sendMailWithEventMailer( $email, "register", $data_items );
		
		//Send mail
		/*$event_mail = new EventMailer();
		$event_mail->posting_type =  "register";
		$event_mail->event_data =   $data_items;
		
		if( file_exists( $paths["register"]['body_text'] ) ){
			$body_text = nl2br( file_get_contents( $paths["register"]['body_text'] ) );	
			$event_mail->body_text = $body_text;
		}else{
			$event_mail->body_text = "";
		}
		
		$event_mail->send( $email );*/
		
		// Add to sended mails
		//addLine( $sended_path, $email );
	}
	 
}
//--------------------------
// Get event name
//---------------------------
if( $q == "get_event_name" ){
	echo ( $data_items["title"] );
}
//--------------------------
// Dates
//---------------------------
if( $q == "get_date" ){
	echo ( file_exists( $paths[ $posting ]['date'] ) )? file_get_contents(  $paths[ $posting ]['date']  ) : "";
}
if( $q == "set_date" ){
	echo file_put_contents( $paths[ $posting ]['date'], $_GET['date'] );
}
if( $q == "reset_date" ){
	resetDate ( $paths[ $posting ]['date'] );
}
//--------------------------
// Single add mail
//---------------------------
if( $q == "add_to_invite" ){
	addLine(  $paths["invites"]['to_be_sended'], $email  );
}
if( $q == "remove_from_invite" ){
	removeLine(  $paths["invites"]['to_be_sended'], $email  );
}
//------------------------------------
// Send invites, reminders and polls
//------------------------------------
if( $q == "send_one_invites" ){
	 sendOneInvite( $paths, $data_items  );
}
if( $q == "send_one_reminders_registered" ){		sendAndReport( $paths, "reminders_registered", $data_items );		}
if( $q == "send_one_reminders_not_registered" ){	sendAndReport( $paths, "reminders_not_registered", $data_items );	}
if( $q == "send_one_polls" ){ 						sendAndReport( $paths, "polls", $data_items );						}

//--------------------------
// List update
//--------------------------
if( $q == "update_list" ){
	file_put_contents( $paths[ $posting ]['to_be_sended'], $_POST['list'] );
	echo "List updated: " . $paths[ $posting ]['to_be_sended'];
}

//--------------------------
// Get unregistered lis
//---------------------------
if( $_GET['q'] == "list_unregistered" ){

	$unregistered_array = array_diff (
				array_merge ( 
						file( $paths["invites"]['to_be_sended'], 	FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ), 
						file( $paths["invites"]['sended'], 			FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) 
				), 
				file( $paths["register"]['registered'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) 
			
			);
	echo implode( "\n",$unregistered_array );
	
}


//--------------------------
// List folders
//---------------------------
if( $q == "list_created_events" ){
	
	$events_array = getEventsFromWordpress();
	$json = '{ "data":[';
	
	foreach( $events_array as $event ){
		$json .= '{"folder_id":"' . $event->ID . '","folder_name" :"' . $event->post_title . '" },';
	}

	$json = rtrim ( $json, "," );
	$json .= "]}";
	
	echo $json;

}

function getEventsFromWordpress(){
	
	$event_pages_args = array( 'post_type' => 'tapahtumat', 'post_parent' => '0' );
	$event_pages = get_posts( $event_pages_args );	
	
	global $data_path;
	foreach( $event_pages as $event_page ){
		if( !file_exists( $data_path . $event_page->ID ) ){
			add_new_event(  $event_page->ID  );
		}
	}
	
	return ( $event_pages );
	
}
//----------------------------
// Posting On Off
//----------------------------
if( $q == "on_off" ){
	if( $_GET['on'] == "true" ){
		file_put_contents( $paths["on_off"]['on_off'], "1" );
		echo "Postitus on kytketty päälle.";
	}else{
		if( file_exists( $paths["on_off"]['on_off'] ) ){
			unlink( $paths["on_off"]['on_off'] );
		}
		echo "Postitus on kytketty pois päältä.";
	}
}

//----------------------------
// Update body text
//----------------------------
if( $q == "update_body_text" ){
	file_put_contents( $paths[ $posting ]['body_text'], $_POST['body_text'] );
	echo "Teksti päivitetty";
}
//--------------------------
// Paths
//---------------------------
if( $q == "get_paths" ){
	print_r( json_encode( $paths ) );	
}
//--------------------------
// Cron
//---------------------------
if( $q == "cron_status" ){
 	echo checkCron();
}

if( $q == "cron" ){
 	if ($handle = opendir( $data_path )) {
		while( false !== ($entry = readdir($handle)) ){
			$filetype = filetype( $data_path.$entry );
			if( $filetype== "dir" && $entry!="." && $entry!=".." && $entry!="errors" ){
				tryCronSending( $entry );
			}
		}
	}
}


//---------------------------
// functions
//---------------------------
function tryCronSending( $id ){
	
	global $data_path;
	global $paths;
	
	$paths = getPaths( $id, $data_path );
	$data_items = getDataItems( $id );

	
	if( file_exists($paths["on_off"]['on_off'])  ){
		echo "Switch on! <br>";
		
		if(	count( sendOneInvite( $paths, $data_items  )) == 0  ){
			echo "<br>No Invites?";
			if( 	count( sendAndReport( $paths, "reminders_not_registered", $data_items)) == 0 	){
				echo "<br>No not registereds?";
				if( 	count( sendAndReport( $paths, "reminders_registered", $data_items )) == 0	){
						echo "<br>No Registerededs?";
					 	sendAndReport( $paths, "polls", $data_items  );	
				}
			}
		}else{
			echo "Invites not zero"; 	
		}
		
	}else{
		echo "Switched off! " . $id;	
	}

}

//---------------------------------
// Get data from Wordpress page
//---------------------------------
function getDataItems( $id ){
	
	$page = get_page( $id );
	$data_items = array( 
						"type"					=> 	$page->post_type,
						"parent"				=> 	$page->post_parent,
						"title" 				=> 	$page->post_title,
						"ingress" 				=> 	$page->post_excerpt,
						"content" 				=> 	$page->post_content,
						"image" 				=>	get_post_meta( $id, "event_image", true ),
						"location"				=>	get_post_meta( $id, "event_location", true ),
						"extra_info"			=>	get_post_meta( $id, "event_extra_info", true ),
						"gallup_url"			=>	get_post_meta( $id, "event_gallup_url", true ),
						"list_events_boolean"	=>	get_post_meta( $id, "listaa_tapahtumat", true),
						"datetime"				=>	get_post_meta( $id, "event_datetime", true ),
						"registration_end_date"	=>	get_post_meta( $id, "event_registration_end_date", true ),
						"page_url"				=>  get_permalink( $id )
						
						 );

	return $data_items;
		
}
//---------------------------
// Mails
//---------------------------
function sendOneInvite( $paths, $data_items  ){
	
	$sended_mails = Array();
	
	$sended_mails = sendAndReport( $paths, "invites", $data_items );
	print_r( $sended_mails );
	
	if( !empty($sended_mails) ){
		foreach( $sended_mails as $sended_mail ){
			if( !empty($sended_mail) ){ 	addLine(  $paths["reminders_not_registered"]['to_be_sended'], $sended_mail  ); 	}
		}
	}
	
	return ($sended_mails );
}

function sendAndReport( $paths, $posting_type, $data_items ){
	$sended_mails = Array();
	$sended_mails = trySendOneMail( $paths[ $posting_type ]['date'], $paths[ $posting_type ]['to_be_sended'], $paths[ $posting_type ]['sended'], $posting_type , $data_items );
	reportEmail( $sended_mails );
	return $sended_mails;
}

function trySendOneMail( $date_path, $to_be_sended_path, $sended_path, $posting,  $data_items ){
	
	global $paths;

	$date_not_gone 	= dateNotGone($date_path);
	$date_active 	= dateActive($date_path);
	$main_switch_on = mainSwitchOn();
	
	if( !$date_not_gone && $date_active && $main_switch_on ) {
		$sended_mails = sendOneEmail( $to_be_sended_path, $sended_path,  $paths, $posting,  $data_items   );
		return $sended_mails;
	}else{
		if( $date_not_gone ){
			$message = "<br>" . "Lähetys ei ole vielä alkanut ({$posting})";	
		}
		if( !$date_active ){
			$message = "<br>" . "Lähetyspäivämäärää ei ole vielä asetettu ({$posting})";	
		}
		if( !$main_switch_on ){
			$message = "<br>" . "Lähetykset on kytketty pois pääkytkimestä ({$posting}) " . mainSwitchOn() . " / ";	
		}
		
		echo $message;
		
		return Array();
		
	}
}

function sendOneEmail( $source_path, $sended_path, $paths, $posting,  $data_items  ){
	
	//-------------------------
	// Get next line
	//-------------------------
	$email_list = Array();
	
	for($i=0;$i<10;$i++){
		$email = popLine ( $source_path );
		echo $email;
		
		if( validEmail( $email )){
			
			if( checkLineNotOnFile( $sended_path, $email ) ){
				array_push( $email_list , $email );
			}else{
				echo( "Sähköposti on jo lähetettyjen listalla. Postia ei lähetetty uudestaan." ) ;
			}
			
		}else{
			echo "Not valid email for pushing to list";	
		}
	}
	
	
	//--------------------------
	// Do sending here
	//--------------------------
	//if( validEmail( $email ) ){
	if( count( $email_list ) > 0 ){
		// Check that mail is not in sended mails
		//if( !checkLineNotOnFile( $sended_path, $email ) ){
			//die( "Sähköposti on jo lähetettyjen listalla. Postia ei lähetetty uudestaan." ) ;
		//}
		// Send mail
		if( sendMailWithEventMailer( $email_list, $posting, $data_items, $paths ) ){
			// Add to sended mails
			foreach( $email_list as $email ){
				addLine( $sended_path, $email );
			}
			echo "<br>Send one email from " . $source_path . " -> " . $email_list ;
		}else{
			// Push back to source if no mail sended
			foreach( $email_list as $email ){
				addLine( $source_path, $email );
			}
			echo "<br>Could not send from" . $source_path . " -> " . $email ;
			$email = "";
		}
		
		return ($email_list);
		
	}else{
		//reportError("not valid email " .$email );
		echo "Not a single email to send?";
		return( Array() );
	}
	
}

function sendMailWithEventMailer( $email, $posting, $data_items, $paths ){
	
	$event_mail = new EventMailer();
	$event_mail->posting_type =  	$posting;
	$event_mail->event_data =   $data_items;
		
	if( file_exists( $paths[ $posting ]['body_text'] ) ){
		$body_text = nl2br( file_get_contents( $paths[ $posting ]['body_text'] ) );	
		$event_mail->body_text = $body_text;
	}else{
		$event_mail->body_text = "";
	}
		
	return $event_mail->send( $email );	
}

function reportEmail( $sended_mail ){
	if( !empty($sended_mail) ){ echo " Lähetettiin yksi posti" . $sended_mail; }else{ echo "<br>Ei lähetetty postia: " . $sended_mail; }
}

function moveEmail( $source, $target, $email ){
	removeLine( $source, $email );
	addLine( $target, $email );
}

//-----------
// Date
//-----------
function mainSwitchOn(){
	
	global $paths;
	
	if( file_exists( $paths["on_off"]['on_off'] ) ){
		$on_off_status = file_get_contents( $paths["on_off"]['on_off'] );
		if( $on_off_status == "1" ){
			return true;	
		}else{
			return false;	
		}
	}else{
		return false;	
	}	
}

function dateActive( $date_file ){
	if (  file_exists( $date_file )  ){
		$date_string = file_get_contents( $date_file );
		if( empty( $date_string ) ){
			return false;	
		}else{
			return true;	
		}
	}else{
		return false;
	}
}
function dateNotGone( $date_file ){
	
	if( file_exists( $date_file )  ){
		$date_string = file_get_contents( $date_file );
		$timestamp = changeDatetimeToTime( $date_string );
		return ( $timestamp > time() )? true : false;
	}else{
		//This is a bit cofusing date is gone if there is no file
		// Date not gone is usually combined with dateActive so no harm done
		return false;
	}
	//echo $date_string . " | Date bigger than now: " . ($timestamp > time()) ."<br>";	
}

function changeDatetimeToTime( $date_fi ){
	$space_explode 	= explode( " ", $date_fi );
	$date_explode 	= explode( ".", $space_explode[0] );
	$time_explode 	= explode( ":", $space_explode[1] );
	return mktime( (int)$time_explode[0],(int)$time_explode[1],0, (int)$date_explode[1],(int)$date_explode[0],(int)$date_explode[2] );
}
function resetDate( $date_file ){
	unlink ( $date_file );	
}
//----------
// Lines
//----------
function removeLine( $path, $email ){
	
	checkFile($path);
	
	$line_removed = false;
	$lines = file( $path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES  );
	
	foreach( $lines as $key => $val ){
		if(  trim($val) == trim($email) ){
			unset( $lines[$key] );
			$line_removed=true;
		}
	}
	
	if( $line_removed ) saveArrayToFile( $path, $lines );
	
}

function addLine( $path, $email ){
	
	checkFile(	$path	);
	if( checkEmail(trim($email)) ){
		if( checkLineNotOnFile( $path, $email ) ){
			file_put_contents( $path, trim($email) . "\n" , FILE_APPEND );
		}
	}else{
		echo "<br> Not able to add line because not valid email " . $email;	
	}

}

function popLine ( $path ){
	$lines = file( $path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES  );
	$line = array_pop( $lines );
	saveArrayToFile( $path, $lines );
	return $line;
}

function saveArrayToFile( $path, $array ){
	$array_string = implode( "\n",$array ); 
	file_put_contents( $path, $array_string );
}


//-------------------------
// Crontab
//-------------------------
function checkCron(){
	
	$cron_result = exec("crontab -l"); 
	
	if( empty( $cron_result ) ){
		$cron_result = "Ajastettu lähetys ( cron ) on tyhjä - Ota yhteyttä ylläpitoon"	;
	}else{
		$cron_result = "Ajastettu lähetys toiminnassa, asetuksilla: " . $cron_result; 
	}
	
	return $cron_result;
}

//-------------------------
// Errors
//-------------------------
function reportError( $message ){
	
	global $q;
	global $id;
	global $posting;
	global $state;
	global $data_path;
	global $paths;
	
	$data = $message ." | ". $q ." | ". $id ." | " . $posting ." | ". $state ." | ". date( "d.m. Y H:i:s u") ."\n";
	
	//$path = $data_path . $id ."/errors/errors.txt";
	file_put_contents( $paths['errors']['errors'], $data, FILE_APPEND );
	
}

//--------------------------
// Clean up strings
//--------------------------
function cleanString( $string ){
	$string = strip_tags( $string );
	//$database = new database();
	//return mysql_real_escape_string($string, $database->yhteysnumero );	
	return $string;
}
//---------------------------
// Check if line exists
//---------------------------
function checkLineNotOnFile( $path, $line ){
	$file_array = file( $path, FILE_IGNORE_NEW_LINES );
	if( in_array( trim($line), $file_array ) ){
		reportError("Line allready in file");
		return( false );
	}else{
		return( true );
	}
}
//---------------------------
// Check file
//---------------------------
function checkFile( $path ){
	if( !file_exists($path) ){
		reportError("no such file" . $path);
		exit("No such file! " . $path );
	}
}
//---------------------------
// Check email
//---------------------------
function checkEmail( $email ){
	if( !validEmail($email)){
		reportError("Not valid email");
		return false;
	}else{
		return true;	
	}
}
//---------------------------
// Check email
//---------------------------
/**
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.
*/
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if
(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      /*if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }*/
   }
   return $isValid;
}

//---------------------------
// Set Paths
//---------------------------
function getPaths( $id ){
	
	global $data_path;
	$path = $data_path . $id;
	
	$paths = array( 
					"invites" => array( 
										"date" => 			$path . "/invites/date.txt",
										"sended" => 		$path . "/invites/sended.txt",
										"to_be_sended" =>  	$path . "/invites/to_be_sended.txt",
										"body_text" =>  	$path . "/invites/body_text.txt"
									), 
					"register" => array( 
										"registered" => 	$path . "/registered/registered.txt",
										"body_text" => 		$path . "/registered/body_text.txt"
									), 
					"reminders_not_registered" => array( 
										"date" => 			$path . "/reminders_not_registered/date.txt",
										"sended" => 		$path . "/reminders_not_registered/sended.txt",
										"to_be_sended" =>  	$path . "/reminders_not_registered/to_be_sended.txt",
										"body_text" =>  	$path . "/reminders_not_registered/body_text.txt"
									), 
					"reminders_registered" => array( 
										"date" => 			$path . "/reminders_registered/date.txt",
										"sended" => 		$path . "/reminders_registered/sended.txt",
										"to_be_sended" =>  	$path . "/reminders_registered/to_be_sended.txt",
										"body_text" =>  	$path . "/reminders_registered/body_text.txt"
									), 
					"polls"	=> array( 
										"date" => 			$path . "/polls/date.txt",
										"sended" => 		$path . "/polls/sended.txt",
										"to_be_sended" =>  	$path . "/polls/to_be_sended.txt",
										"body_text" =>  	$path . "/polls/body_text.txt"
									),
					"on_off"	=> array( 
										"on_off" => 		$path . "/on_off.txt"
									)
					,
					"errors"	=> array( 
										"errors" => 		$path . "/errors/errors.txt"
									)
					);
					
		return $paths;

}

//--------------------------
// Create new event files
//---------------------------
function add_new_event( $id ){
	
	global $data_path;
	$path = $data_path.$id;
	$paths = getPaths( $id );
	
	if( !empty( $id ) ){
		
		if( file_exists (  $path ) ){
			exit( "Folder allready exists!" );
		}
		
		mkdir( $path );
		//Invite
		mkdir( $path . "/invites"  );
		//file_put_contents( $paths["invites"]['date'], "" );
		file_put_contents( $paths["invites"]['sended'], "" );
		file_put_contents( $paths["invites"]['to_be_sended'], "" );
		//Registered
		mkdir( $path . "/registered"  );
		file_put_contents( $paths["register"]['registered'], "" );
		file_put_contents( $paths["register"]['body_text'], "" );
		//Reminder not registered
		mkdir( $path . "/reminders_not_registered"  );
		//file_put_contents( $paths["reminders_not_registered"]['date'], "" );
		file_put_contents( $paths["reminders_not_registered"]['sended'], "" );
		file_put_contents( $paths["reminders_not_registered"]['to_be_sended'] , "" );
		//Reminder registered
		mkdir( $path . "/reminders_registered"  );
		//file_put_contents( $paths["reminders_registered"]['date'], "" );
		file_put_contents( $paths["reminders_registered"]['sended'], "" );
		file_put_contents( $paths["reminders_registered"]['to_be_sended'], "" );
		//Poll
		mkdir( $path . "/polls"  );
		file_put_contents( $paths["polls"]['sended'], "" );
		file_put_contents( $paths["polls"]['to_be_sended'], "" );
		mkdir( $path . "/errors"  );
		file_put_contents( $paths["errors"]['errors'], "" );
		
	}else{
		exit( "No id!" );	
	}
}

?>