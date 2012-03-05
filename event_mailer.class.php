<?PHP

class EventMailer{
	
	var $sub_title;
	var $event_data;
	var $body_text;
	var $email;
	
	var $template_file = "../templates/email_template_1/teosto_newsletter_big_picture.html";
	var $template;
	
					
	public function EventMailer(){}
	
	
	public function send( $email ){
		
		$this->email = $email;
		$this->getTemplate();
		$this->placeData();
		
		print_r( $this->template );
		
	}
	
	function placeData(){
		
		switch( $this->sub_title ){
			case "invites" : $sub_title = "Kutsu:";
			break;
		}
		
		$this->template = str_replace("<!-- sub_title -->", sub_title, 						$this->template );
		$this->template = str_replace("<!-- title -->", 	$this->event_data['title'], 	$this->template );
		$this->template = str_replace("<!-- location -->", 	$this->event_data['location'], 	$this->template );
		$this->template = str_replace("<!-- time -->", 		$this->event_data['datetime'], 	$this->template );
		$this->template = str_replace("<!-- body_text -->", $this->body_text, 				$this->template );
	}
	
	function getTemplate(){
		
		if( file_exists( $this->template_file )  ){
			$this->template = file_get_contents(  $this->template_file );
		}else{
			echo "No template file for mailing!";	
		}
	}
	
	
}
?>