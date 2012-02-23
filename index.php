<?php
$data_path = "../data/";
$id 		= cleanString( $_GET['id'] );
$posting 	= cleanString( $_GET['posting'] );
$state 		= cleanString( $_GET['state'] );
$email 		= cleanString( $_GET['email'] );
$q 			= cleanString( $_GET['q'] );


//--------------------------
// Create new event files
//---------------------------
if( $_GET['q'] == "add_new_event" ){
	
	if( !empty( $id ) ){
		
		$path = $data_path . $id;
		
		if( file_exists ( $path ) ){
			exit( "Folder allready exists!" );
		}
		
		mkdir( $path );
		//Invite
		mkdir( $path . "/invite"  );
		file_put_contents( $path . "/invite/sended.txt","" );
		file_put_contents( $path . "/invite/to_be_sended.txt","" );
		//Poll
		mkdir( $path . "/poll"  );
		file_put_contents( $path . "/poll/sended.txt","" );
		file_put_contents( $path . "/poll/to_be_sended.txt","" );
		//Registered
		mkdir( $path . "/registered"  );
		file_put_contents( $path . "/registered/registered.txt","" );
		//Reminder not registered
		mkdir( $path . "/reminders_not_registered"  );
		file_put_contents( $path . "/reminders_not_registered/sended.txt","" );
		file_put_contents( $path . "/reminders_not_registered/to_be_sended.txt","" );
		//Reminder registered
		mkdir( $path . "/reminders_registered"  );
		file_put_contents( $path . "/reminders_registered/sended.txt","" );
		file_put_contents( $path . "/reminders_registered/to_be_sended.txt","" );
	}else{
		exit( "No id!" );	
	}
}

//--------------------------
// Register
//---------------------------
if( $_GET['q'] == "register" ){
	
	$path = $data_path . $id ."/registered/registered.txt";
	if( !file_exists($path) ){
		reportError("no such file");
		exit("No such file!");
	}
	if( validEmail($email)){
		addLine(  $path, $email  );
	}else{
		reportError("not valid email");
		exit("Not valid email!");
	}
}
//--------------------------
// Reminders
//---------------------------
if( $_GET['q'] == "create_reminders" ){
	
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
}
//--------------------------
// Single mail addresses
//---------------------------
if( $_GET['q'] == "remove_mail" ){
	
	$path = $data_path . $id ."/". $posting ."/". $state .".txt";
	
	if( validEmail($email) ){
		echo "Tried to remove email: " . $email;
		removeLine(  $path, $email  );
	}else{
		reportError("not valid email");
	}
}

if( $_GET['q'] == "add_mail_to_invite" ){
	addMail("invite");
}



if( $_GET['q'] == "send_invite" ){
	sendOneEmail( "invite" );
}
if( $_GET['q'] == "send_reminder" ){
	sendOneEmail( "reminder_registered" );
	sendOneEmail( "reminder_not_registered" );
}

//---------------------------
// functions
//---------------------------
function addMail( $posting_type ){
	
	global $data_path;
	global $id;
	global $email;
	
	if( $posting_type =="invite" ){
		$path = $data_path . $id ."/invite/to_be_sended.txt";	
	}else{
		exit("Wrong posting type");
	}
	
	if( validEmail($email) ){
		addLine(  $path, $email  );
	}else{
		reportError("not valid email");
	}
}

function sendOneEmail( $posting_type ){
	
	global $id;
	global $data_path;
	
	if( $posting_type =="invite" ){
		$to_be_sended_path = $data_path . $id ."/invite/to_be_sended.txt";	
		$sended_path = $data_path . $id ."/invite/sended.txt";
	}else if( $posting_type =="reminder_registered" ){
		$to_be_sended_path = $data_path . $id ."/reminders_registered/to_be_sended.txt";	
		$sended_path = $data_path . $id ."/reminders_registered/sended.txt";
	}else if( $posting_type =="reminder_not_registered" ){
		$to_be_sended_path = $data_path . $id ."/reminders_not_registered/to_be_sended.txt";	
		$sended_path = $data_path . $id ."/reminders_not_registered/sended.txt";
	}else{
		exit("Wrong posting type");
	}
	//-------------------------
	// Get next line
	//-------------------------
	
	$email = popLine ( $to_be_sended_path );
	//--------------------------
	// Do sending here
	//--------------------------
	if( validEmail($email) ){
		echo "Popped: " . $email;
	}else{
		reportError("not valid email");
	}
	//--------------------------
	// Add to sended mails
	//--------------------------
	
	addLine( $sended_path, $email );
}

function removeLine( $path, $email ){
	
	
	
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
	file_put_contents( $path, trim($email) . "\n" , FILE_APPEND );
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