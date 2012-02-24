<?php
$data_path = "../data/";
$id 		= cleanString( $_GET['id'] );
$posting 	= cleanString( $_GET['posting'] );
$state 		= cleanString( $_GET['state'] );
$email 		= cleanString( $_GET['email'] );
$q 			= cleanString( $_GET['q'] );

$path = $data_path . $id;

//Invite
$invite_sended_path 		= $path . "/invite/sended.txt";
$invite_to_be_sended_path 	= $path . "/invite/to_be_sended.txt";
//Register
$register_registered_path 	= $path . "/registered/registered.txt";
//Reminders for not registered
$reminders_not_registered_to_be_sended_path = $path . "/reminders_not_registered/to_be_sended.txt";
$reminders_not_registered_sended_path 		= $path . "/reminders_not_registered/sended.txt";
//Reminders for registered
$reminders_registered_to_be_sended_path 	= $path . "/reminders_registered/to_be_sended.txt";
$reminders_registered_sended_path 			= $path . "/reminders_registered/sended.txt";
//Poll invite
$poll_sended_path 			= $path . "/poll/sended.txt";
$poll_to_be_sended_path 	= $path . "/poll/to_be_sended.txt";


//--------------------------
// Single add mail
//---------------------------
if( $_GET['q'] == "add_to_invite" ){
	addLine(  $invite_to_be_sended_path, $email  );
}
if( $_GET['q'] == "remove_from_invite" ){
	removeLine(  $invite_to_be_sended_path, $email  );
}
//-----------------------------
// Send invite
//-----------------------------
if( $_GET['q'] == "send_invite" ){
	
	$sended_mail = sendOneEmail( $invite_to_be_sended_path, $invite_sended_path );
	
	if( !empty($sended_mail) ){
		addLine(  $reminders_not_registered_to_be_sended_path, $email  );
	}
}
//--------------------------
// Register
//---------------------------
if( $_GET['q'] == "register" ){
	
	// CAN NON-INVITED REGISTER?
	checkEmail( $email );
	
	addLine(  $register_registered_path, $email  );
	if( dateNotGone("poll") ) addLine(  $poll_to_be_sended, $email  );
	if( dateNotGone("registered") ) addLine(  $reminders_registered_to_be_sended_path, $email  );
	removeLine( $reminders_not_registered_to_be_sended_path, $email );

}
//--------------------------
// Send reminder
//---------------------------
if( $_GET['q'] == "send_reminder" ){
	sendOneEmail( $reminders_registered_to_be_sended_path, $reminders_registered_sended_path );
	sendOneEmail( $reminders_not_registered_to_be_sended_path, $reminders_not_registered_sended_path );
}
//--------------------------
// Send Poll
//---------------------------
if( $_GET['q'] == "send_poll" ){
	sendOneEmail( $poll_to_be_sended_path, $poll_sended_path );
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
		file_put_contents( $invite_sended_path, "" );
		file_put_contents( $invite_to_be_sended_path, "" );
		//Registered
		mkdir( $path . "/registered"  );
		file_put_contents( $register_registered_path, "" );
		//Reminder not registered
		mkdir( $path . "/reminders_not_registered"  );
		file_put_contents( $reminders_not_registered_sended_path, "" );
		file_put_contents( $reminders_not_registered_to_be_sended_path , "" );
		//Reminder registered
		mkdir( $path . "/reminders_registered"  );
		file_put_contents( $reminders_not_registered_sended_path, "" );
		file_put_contents( $reminders_not_registered_to_be_sended_path, "" );
		//Poll
		mkdir( $path . "/poll"  );
		file_put_contents( $poll_sended_path, "" );
		file_put_contents( $poll_to_be_sended_path, "" );
		
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
//Date
function dateNotGone( $date_file ){
	$date_string = file_get_contents( $date_file );
	$timestamp = changeDatetimeToTime( $date_string );
	
	echo "Timestamp: " . $timestamp . " / " .  time();
	
	if( $timestamp < time() ){
		return true;	
	}else{
		return false;	
	}
}
function changeDatetimeToTime( $date_fi ){
	$space_explode 	= explode( " ", $date_fi );
	$date_explode 	= explode( ".", $space_explode[0] );
	$time_explode 	= explode( ":", $space_explode[1] );
		
	print_r( "Date:" . $date_fi );
	return mktime( $time_explode[0],$time_explode[1],0, $date_explode[1],$date_explode[0],$date_explode[2] );
}

//Lines
function removeLine( $path, $email ){
	
	checkFile($path);
	
	$line_removed = false;
	$lines = file( $path, FILE_SKIP_EMPTY_LINES  );
	
	foreach( $lines as $key => $val ){
		if(  trim($val) == trim($email) ){
			unset( $lines[$key] );
			$line_removed=true;
		}
	}
	
	if( $line_removed ){
		echo "Save changed file";
		saveArrayToFile( $path, $lines );
	}
}

function addLine( $path, $email ){
	
	checkFile($path);
	checkEmail($email);
	$file_array = file( $path );
	if( !in_array( trim($email), $file_array ) ){
		file_put_contents( $path, trim($email) . "\n" , FILE_APPEND );
	}
}

function popLine ( $path ){
	$lines = file( $path, FILE_SKIP_EMPTY_LINES  );
	$line = array_pop( $lines );
	saveArrayToFile( $path, $lines );
	return $line;
}

function saveArrayToFile( $path, $array ){
	$array_string = implode( $array ); 
	echo"?????????????????????";
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
	if( validEmail($email)){
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