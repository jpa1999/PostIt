<?php
$data_path = "../data/";
$id 		= cleanString( $_GET['id'] );
$posting 	= cleanString( $_GET['posting'] );
$state 		= cleanString( $_GET['state'] );
$email 		= cleanString( $_GET['email'] );
$q 			= cleanString( $_GET['q'] );

$path = $data_path . $id;

$paths = array( 
				"invites" => array( 
									"date" => 			$path . "/invite/date.txt",
									"sended" => 		$path . "/invite/sended.txt",
									"to_be_sended" =>  	$path . "/invite/to_be_sended.txt"
								), 
				"register" => array( 
									"registered" => 	$path . "/registered/registered.txt",
								), 
				"reminders_not_registered" => array( 
									"date" => 			$path . "/reminders_not_registered/date.txt",
									"sended" => 		$path . "/reminders_not_registered/to_be_sended.txt",
									"to_be_sended" =>  	$path . "/reminders_not_registered/sended.txt"
								), 
				"reminders_registered" => array( 
									"date" => 			$path . "/reminders_registered/date.txt",
									"sended" => 		$path . "/reminders_registered/to_be_sended.txt",
									"to_be_sended" =>  	$path . "/reminders_registered/sended.txt"
								), 
				"polls"	=> array( 
									"date" => 			$path . "/poll/date.txt",
									"sended" => 		$path . "/poll/sended.txt",
									"to_be_sended" =>  	$path . "/poll/to_be_sended.txt"
								)
				);
//Invite
$invite_date_path 			= $path . "/invite/date.txt";
$invite_sended_path 		= $path . "/invite/sended.txt";
$invite_to_be_sended_path 	= $path . "/invite/to_be_sended.txt";
//Register
$register_registered_path 	= $path . "/registered/registered.txt";
//Reminders for not registered
$reminders_not_registered_date_path			= $path . "/reminders_not_registered/date.txt";
$reminders_not_registered_to_be_sended_path = $path . "/reminders_not_registered/to_be_sended.txt";
$reminders_not_registered_sended_path 		= $path . "/reminders_not_registered/sended.txt";
//Reminders for registered
$reminders_registered_date_path 			= $path ."/reminders_registered/date.txt";
$reminders_registered_to_be_sended_path 	= $path . "/reminders_registered/to_be_sended.txt";
$reminders_registered_sended_path 			= $path . "/reminders_registered/sended.txt";
//Poll invite
$poll_date_path 				= $path . "/poll/date.txt";
$poll_sended_path 				= $path . "/poll/sended.txt";
$poll_to_be_sended_path 		= $path . "/poll/to_be_sended.txt";
// Dates
//--------------------------
// Dates
//---------------------------
if( $_GET['q'] == "get_date" ){
	echo file_get_contents(  $paths[ $posting ]['date']  ) ;
}
if( $_GET['q'] == "set_date" ){
	file_put_contents( $paths[ $posting ]['date'], $_GET['date'] );
}

//--------------------------
// List folders
//---------------------------
if( $_GET['q'] == "list_created_events" ){
	
	$json = '{ "data":[';
	
	if ($handle = opendir( $data_path )) {
		/* This is the correct way to loop over the directory. */
    	while(  false !== ($entry = readdir($handle))  ){
       		$filetype = filetype( $data_path.$entry );
			if( $filetype== "dir" && $entry!="." && $entry!=".." && $entry!="errors" ){
				$json .= '{"dir_name":"' . $entry . '"},';
			}
    	}
	}
	
	$json = rtrim ( $json, "," );
	$json .= "]}";
	
	echo $json;
}
//--------------------------
// Single add mail
//---------------------------
if( $_GET['q'] == "add_to_invite" ){
	addLine(  $paths["invites"]['to_be_sended'], $email  );
}
if( $_GET['q'] == "remove_from_invite" ){
	removeLine(  $paths["invites"]['to_be_sended'], $email  );
}
//-----------------------------
// Send invite
//-----------------------------
if( $_GET['q'] == "send_invite" ){
	
	$sended_mail = sendOneEmail( $paths["invites"]['to_be_sended'], $paths["invites"]['sended'] );
	if( !empty($sended_mail) ){
		addLine(  $paths["reminders_not_registered"]['to_be_sended'], $sended_mail  );
	}
}
//--------------------------
// Register
//---------------------------
if( $_GET['q'] == "register" ){
	
	// CAN NON-INVITED REGISTER?
	checkEmail( $email );
	
	addLine(  $register_registered_path, $email  );
	if(  dateNotGone( $paths["polls"]['date'] )  					) addLine(  $paths["polls"]['to_be_sended'], $email  );
	if(  dateNotGone( $paths["reminders_registered"]['date'] )  	) addLine(  $paths["reminders_registered"]['to_be_sended'], $email  );
	
	removeLine( $paths["reminders_not_registered"]['to_be_sended'], $email );

}
//--------------------------
// Send reminder
//---------------------------
if( $_GET['q'] == "send_reminder" ){
	sendOneEmail( $paths["reminders_registered"]['to_be_sended'], 		$paths["reminders_registered"]['sended'] );
	sendOneEmail( $paths["reminders_not_registered"]['to_be_sended'], 	$paths["reminders_not_registered"]['sended'] );
}
//--------------------------
// Send Poll
//---------------------------
if( $_GET['q'] == "send_poll" ){
	sendOneEmail( $paths["polls"]['to_be_sended'], $paths["polls"]['sended'] );
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
if( $_GET['q'] == "add_new_event" ){
	
	if( !empty( $id ) ){
		
		if( file_exists ( $path ) ){
			exit( "Folder allready exists!" );
		}
		
		mkdir( $path );
		//Invite
		mkdir( $path . "/invite"  );
		file_put_contents( $paths["invites"]['date'], "" );
		file_put_contents( $paths["invites"]['sended'], "" );
		file_put_contents( $paths["invites"]['to_be_sended'], "" );
		//Registered
		mkdir( $path . "/registered"  );
		file_put_contents( $paths["register"]['registered'], "" );
		//Reminder not registered
		mkdir( $path . "/reminders_not_registered"  );
		file_put_contents( $paths["reminders_not_registered"]['date'], "" );
		file_put_contents( $paths["reminders_not_registered"]['sended'], "" );
		file_put_contents( $paths["reminders_not_registered"]['to_be_sended'] , "" );
		//Reminder registered
		mkdir( $path . "/reminders_registered"  );
		file_put_contents( $paths["reminders_registered"]['date'], "" );
		file_put_contents( $paths["reminders_registered"]['sended'], "" );
		file_put_contents( $paths["reminders_registered"]['to_be_sended'], "" );
		//Poll
		mkdir( $path . "/poll"  );
		file_put_contents( $paths["polls"]['sended'], "" );
		file_put_contents( $paths["polls"]['to_be_sended'], "" );
		
	}else{
		exit( "No id!" );	
	}
}


//--------------------------
// Reminders not needed?
//---------------------------
/*if( $_GET['q'] == "create_reminders" ){
	
	if( empty($id) ){
		reportError("Empty id");
		exit("Empty ID!");
	}
	
	$reminders_registered_path 		= $data_path . $id ."/reminders_registered/to_be_sended.txt";
	$reminders_not_registered_path 	= $data_path . $id ."/reminders_not_registered/to_be_sended.txt";
	$sended_path 					= $data_path . $id ."/invite/sended.txt";
	$registered_path 				= $data_path . $id ."/registered/registered.txt";
	
	$sended = file( $sended_path, FILE_IGNORE_NEW_LINES );
	$registered = file( $registered_path, FILE_IGNORE_NEW_LINES );

	$reminders_registered = array();
	$reminders_not_registered = array();
	
	foreach( $sended as $sended_item ){
		$item = trim( $sended_item );
		if( empty($item) ) continue;
		
		if( in_array( $item, $registered )  ){
			array_push( $reminders_registered, $item."\n" );
		}else{
			array_push( $reminders_not_registered, $item."\n");
		}
	}
	
	saveArrayToFile( $reminders_registered_path, $reminders_registered );
	saveArrayToFile( $reminders_not_registered_path, $reminders_not_registered );
}*/

//---------------------------
// functions
//---------------------------
function moveEmail( $source, $target, $email ){
	removeLine( $source, $email );
	addLine( $target, $email );
}


function sendOneEmail( $source_path, $sended_path ){
	
	//-------------------------
	// Get next line
	//-------------------------
	$email = popLine ( $source_path );
	//--------------------------
	// Do sending here
	//--------------------------
	if( validEmail($email) ){
		echo "Popped: " . $email;
		// Add to sended mails
		addLine( $sended_path, $email );
		return ($email);
	}else{
		reportError("not valid email");
		return("");
	}
	
}
//-----------
// Date
//-----------
function dateNotGone( $date_file ){
	$date_string = file_get_contents( $date_file );
	$timestamp = changeDatetimeToTime( $date_string );
	
	echo "Timestamp: " . $timestamp . " / Current: " .  time();
	
	if( $timestamp > time() ){
		return true;	
	}else{
		return false;	
	}
}
function changeDatetimeToTime( $date_fi ){
	$space_explode 	= explode( " ", $date_fi );
	$date_explode 	= explode( ".", $space_explode[0] );
	$time_explode 	= explode( ":", $space_explode[1] );
	return mktime( (int)$time_explode[0],(int)$time_explode[1],0, (int)$date_explode[1],(int)$date_explode[0],(int)$date_explode[2] );
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
	checkLineNotOnFile( $path, $email );
	file_put_contents( $path, trim($email) . "\n" , FILE_APPEND );

}

function popLine ( $path ){
	$lines = file( $path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES  );
	$line = array_pop( $lines );
	saveArrayToFile( $path, $lines );
	return $line;
}

function saveArrayToFile( $path, $array ){
	$array_string = implode( $array ); 
	print_r( $array_string );
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
		exit("Line allready in file");
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