<?PHP
require_once "phpmailer/class.phpmailer.php";
require_once "phpmailer/class.smtp.php";

class EventMailer{
	
	var $posting_type;
	var $event_data;
	
	var $sub_title;
	var $body_text;
	var $email;
	
	var $template_file = "../templates/email_template_1/teosto_newsletter_big_picture.html";
	var $template;
	
					
	public function EventMailer(){}
	
	
	public function send( $email ){
		
		
		$this->email = $email;
		$this->setVariableData();
		$this->getTemplate();
		$this->prepareTemplate();
		$this->setTextOnlyBody();
		
		$this->smtpmailer();
		
		//print_r( $this->template );
		
	}
	
	function setVariableData(){
		
		switch( $this->posting_type ){
			case "register" : 
				$this->subject = "Kiitos rekisteröitymisestä";
				$this->sub_title = "Kiitos rekisteröitymisestä";
				$this->link_image_filename = "lisatietoja_nappi.jpg";
			break;
			case "invites" : 
				$this->subject = "Henkilökohtainen kutsu tilaisuuteen";
				$this->sub_title = "Henkilökohtainen kutsu tilaisuuteen";
				$this->link_image_filename = "ilmoittaudu_nappi.jpg";
			break;
			case "reminders_not_registered" : 
				$this->subject = "Henkilökohtainen kutsu tilaisuuteen";
				$this->sub_title = "Henkilökohtainen kutsu tilaisuuteen";
				$this->link_image_filename = "ilmoittaudu_nappi.jpg";
			break;
			case "reminders_registered" : 
				$this->subject = "Muistutus: Teoston tilaisuus lähestyy";
				$this->sub_title = "Muistutus: Teoston tilaisuus lähestyy";
				$this->link_image_filename = "lisatietoja_nappi.jpg";
			break;
			case "polls" : 
				$this->subject = "Henkilökohtainen kutsu mielipidekyselyyn";
				$this->sub_title = "Henkilökohtainen kutsu mielipidekyselyyn";
				$this->link_image_filename = "mielipide_nappi.jpg";
			break;
		}
	}
	
	function getTemplate(){
		if( file_exists( $this->template_file )  ){  $this->template = file_get_contents(  $this->template_file );  }else{  echo "No template file for mailing!";	}
	}
	
	function prepareTemplate(){
		
		$this->template = str_replace("<!-- sub_title -->", $this->sub_title, 				$this->template );
		$this->template = str_replace("<!-- title -->", 	$this->event_data['title'], 	$this->template );
		$this->template = str_replace("<!-- location -->", 	$this->event_data['location'], 	$this->template );
		$this->template = str_replace("<!-- time -->", 		$this->event_data['datetime'], 	$this->template );
		$this->template = str_replace("<!-- body_text -->", $this->body_text, 				$this->template );
	
		$right_column_string = "<a href='" . $this->event_data['page_url'] . "' ><img src='cid:linkimagejpg' /></a>";
		$this->template = str_replace("<!-- right column -->", $right_column_string, $this->template );
	}
	
	function setTextOnlyBody(){
		$this->text_only_body = $this->event_data['title'] . "\n\n" . 	$this->event_data['location'] . "\n" . $this->event_data['datetime'] . "\n\n" . $this->body_text;
	}
	
	function smtpmailer(){ 
		
		global $error;
		$mail = new PHPMailer();  // create a new object
		$mail->IsSMTP(); // enable SMTP
		$mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
		$mail->SMTPAuth = true;  // authentication enabled
		$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
		$mail->Host = 'smtp.gmail.com';
		$mail->Port = 465; 
		require_once "mail_credentials.php";
		$mail->CharSet="UTF-8";
		$mail->IsHTML( true );
		
		$mail->AddAddress( $this->email );
		//---------------------------
		// Add images to mail
		//---------------------------
		$mail->AddEmbeddedImage( '../templates/email_template_1/header.gif', 'headergif', 'header.gif'); 
		$mail->AddEmbeddedImage( '../templates/email_template_1/footer_lines.gif', 'footerlinesgif', 'footer_lines.gif'); 
		//$mail->AddEmbeddedImage( '../templates/email_template_1/main_image.jpg', 'mainimagejpg', 'main_image.jpg'); 
		$mail->AddEmbeddedImage( '../templates/email_template_1/' . $this->link_image_filename, 'linkimagejpg', $this->link_image_filename); 
		$mail->AddEmbeddedImage( '../templates/email_template_1/title_background.jpg', 'titlebackgroundjpg', 'title_background.jpg'); 
		
		//---------------------------
		// Subject and content
		//---------------------------
		$mail->Subject = $this->subject;
		$mail->Body = $this->template;
		$mail->AltBody = $this->text_only_body;
		
		if(!$mail->Send()) {
			$error = 'Mail error: '.$mail->ErrorInfo; 
			return false;
		} else {
			$error = 'Message sent!';
			return true;
		}
		
		echo $error;
	}
	
	
}
?>