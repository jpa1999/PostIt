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
// GetMailer
//---------------------------------
require_once('event_mailer.class.php');

//---------------------------------
// Get data from wordpress page
//---------------------------------
if( !empty( $id )){ 
	// Get mecessary properties from post to single array
	// So you do not have to remember if they come from page or meta 
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
}
//--------------------------
// PATHS
//---------------------------
$paths = getPaths( $id, $data_path );

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
					);
					
		return $paths;

}

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
//-----------------------------
// Send invite
//-----------------------------
if( $q == "send_one_invites" ){
	
	$date_path = $paths["invites"]['date'];
	
	if(  !dateNotGone($date_path) && dateActive($date_path) ) {
		
		$sended_mail = sendOneEmail(  $paths["invites"]['to_be_sended'], $paths["invites"]['sended'],  $paths, $posting,  $data_items  );
		
		if( !empty($sended_mail) ){
			addLine(  $paths["reminders_not_registered"]['to_be_sended'], $sended_mail  );
			echo "Sended one mail";
		}
	}else{
		echo "Send date in future or Date not active - no mail send";	
	}
}
//--------------------------
// Send reminder
//---------------------------
if( $_GET['q'] == "send_one_reminders_registered" ){
	if(  !dateNotGone( $paths["reminders_registered"]['date'] ) ) {
		sendOneEmail( $paths["reminders_registered"]['to_be_sended'], 		$paths["reminders_registered"]['sended'] );
	}
}
if( $_GET['q'] == "send_one_reminders_not_registered" ){
	if(  !dateNotGone( $paths["reminders_not_registered"]['date'] ) ) {
		sendOneEmail( $paths["reminders_not_registered"]['to_be_sended'], 	$paths["reminders_not_registered"]['sended'] );
	}
}

//--------------------------
// Send Poll
//---------------------------
if( $_GET['q'] == "send_one_polls" ){
	if(  !dateNotGone( $paths["polls"]['date'] ) ) {
		sendOneEmail( $paths["polls"]['to_be_sended'], $paths["polls"]['sended'] );
	}
}
//--------------------------
// List update
//--------------------------
if( $_POST['q'] == "update_list" ){
	file_put_contents( $paths[ $posting ]['to_be_sended'], $_POST['list'] );
	echo "List updated: " . $paths[ $posting ]['to_be_sended'];
}
//--------------------------
// Register
//---------------------------
if( $_GET['q'] == "register" ){
	
	// CAN NON-INVITED REGISTER?
	checkEmail( $email );
	addLine(  $paths["register"]['registered'], $email  );
	
	if(  dateNotGone( $paths["polls"]['date'] )  					) addLine(  $paths["polls"]['to_be_sended'], $email  );
	if(  dateNotGone( $paths["reminders_registered"]['date'] )  	) addLine(  $paths["reminders_registered"]['to_be_sended'], $email  );
	
	removeLine( $paths["reminders_not_registered"]['to_be_sended'], $email );

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
		
	}else{
		exit( "No id!" );	
	}
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
	/*if ($handle = opendir( $data_path )) {
		while(  false !== ($entry = readdir($handle))  ){
			
			$page = get_page( $entry );
			$folder_name = $page->post_title;
			
       		$filetype = filetype( $data_path.$entry );
			if( $filetype== "dir" && $entry!="." && $entry!=".." && $entry!="errors" ){
				$json .= '{"folder_id":"' . $entry . '","folder_name" :"' . $folder_name . '" },';
			}
			
    	}
	}*/
	
	
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
if( $_GET['q'] == "get_paths" ){
	print_r( json_encode( $paths ) );	
}
//---------------------------
// functions
//---------------------------
function moveEmail( $source, $target, $email ){
	removeLine( $source, $email );
	addLine( $target, $email );
}


function sendOneEmail( $source_path, $sended_path, $paths, $posting,  $data_items  ){
	
	print_r( $data_items );
	
	//-------------------------
	// Get next line
	//-------------------------
	$email = popLine ( $source_path );
	
	echo "Send one email from " . $source_path . " -> " . $email ;
	
	//--------------------------
	// Do sending here
	//--------------------------
	if( validEmail( $email ) ){
		echo "Popped: " . $email;
		
		//Send mail
		$event_mail = new EventMailer();
		$event_mail->posting_type =  	$posting;
		$event_mail->event_data =   $data_items;
		
		if( file_exists( $paths[ $posting ]['body_text'] ) ){
			$body_text = nl2br( file_get_contents( $paths[ $posting ]['body_text'] ) );	
			$event_mail->body_text = $body_text;
		}else{
			$event_mail->body_text = "";
		}
		
		
		$event_mail->send( $email );
		
		// Add to sended mails
		addLine( $sended_path, $email );
		
		
		
		return ($email);
		
	}else{
		echo "NO Pop";
		reportError("not valid email");
		return("");
	}
	
}
//-----------
// Date
//-----------
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
	$date_string = file_get_contents( $date_file );
	$timestamp = changeDatetimeToTime( $date_string );
	return ( $timestamp > time() )? true : false;
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
	
	checkFile($path);
	checkEmail( trim($email) );
	if( checkLineNotOnFile( $path, $email ) ){
		file_put_contents( $path, trim($email) . "\n" , FILE_APPEND );
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
// Errors
//-------------------------
function reportError( $message ){
	
	global $q;
	global $id;
	global $posting;
	global $state;
	global $data_path;
	
	$data = $message ." | ". $q ." | ". $id ." | " . $posting ." | ". $state ." | ". date( "d.m. Y H:i:s u") ."\n";
	
	$path = $data_path . "/errors/errors.txt";
	//$path = $data_path . $id ."/errors/errors.txt";
	file_put_contents( $path, $data, FILE_APPEND );
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
		reportError("no such file");
		exit("No such file!");
	}
}
//---------------------------
// Check email
//---------------------------
function checkEmail( $email ){
	if( !validEmail($email)){
		reportError("Not valid email");
		exit("Not valid email!");
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

?>