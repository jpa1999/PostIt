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
									"date" => 			$path . "/invites/date.txt",
									"sended" => 		$path . "/invites/sended.txt",
									"to_be_sended" =>  	$path . "/invites/to_be_sended.txt"
								), 
				"register" => array( 
									"registered" => 	$path . "/registered/registered.txt",
								), 
				"reminders_not_registered" => array( 
									"date" => 			$path . "/reminders_not_registered/date.txt",
									"sended" => 		$path . "/reminders_not_registered/sended.txt",
									"to_be_sended" =>  	$path . "/reminders_not_registered/to_be_sended.txt"
								), 
				"reminders_registered" => array( 
									"date" => 			$path . "/reminders_registered/date.txt",
									"sended" => 		$path . "/reminders_registered/sended.txt",
									"to_be_sended" =>  	$path . "/reminders_registered/to_be_sended.txt"
								), 
				"polls"	=> array( 
									"date" => 			$path . "/polls/date.txt",
									"sended" => 		$path . "/polls/sended.txt",
									"to_be_sended" =>  	$path . "/polls/to_be_sended.txt"
								)
				);

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
	if(  !dateNotGone( $paths["invites"]['date'] ) ) {
		$sended_mail = sendOneEmail(  $paths["invites"]['to_be_sended'], $paths["invites"]['sended']  );
		
		if( !empty($sended_mail) ){
			echo "AD to registered";
			addLine(  $paths["reminders_not_registered"]['to_be_sended'], $sended_mail  );
		}
	}else{
		echo "Send date in future";	
	}
}
//--------------------------
// Send reminder
//---------------------------
if( $_GET['q'] == "send_reminder_registered" ){
	if(  !dateNotGone( $paths["reminders_registered"]['date'] ) ) {
		sendOneEmail( $paths["reminders_registered"]['to_be_sended'], 		$paths["reminders_registered"]['sended'] );
	}
}
if( $_GET['q'] == "send_reminder_not_registered" ){
	if(  !dateNotGone( $paths["reminders_not_registered"]['date'] ) ) {
		sendOneEmail( $paths["reminders_not_registered"]['to_be_sended'], 	$paths["reminders_not_registered"]['sended'] );
	}
}

//--------------------------
// Send Poll
//---------------------------
if( $_GET['q'] == "send_poll" ){
	if(  !dateNotGone( $paths["polls"]['date'] ) ) {
		sendOneEmail( $paths["polls"]['to_be_sended'], $paths["polls"]['sended'] );
	}
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
// List folders
//---------------------------
if( $_GET['q'] == "list_created_events" ){
	
	$json = '{ "data":[';
	
	if ($handle = opendir( $data_path )) {
		while(  false !== ($entry = readdir($handle))  ){
       		$filetype = filetype( $data_path.$entry );
			if( $filetype== "dir" && $entry!="." && $entry!=".." && $entry!="errors" ){
				$json .= '{"folder_id":"' . $entry . '"},';
			}
    	}
	}
	
	$json = rtrim ( $json, "," );
	$json .= "]}";
	
	echo $json;

	
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


function sendOneEmail( $source_path, $sended_path ){
	
	echo "Send one email";
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
		echo "NO Pop";
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
	
	echo $date_string . " | Date bigger than now: " . ($timestamp > time()) ."<br>";

	
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