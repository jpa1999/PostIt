// JavaScript Document


var MainView = function( model ){

	this.model = model
	this.cookies = new Cookies;
	this.random_string =	"?rand=" + Math.random()
	this.basepath = 		"../../data/" + this.model.id
		
	
	this.init = function(){}

	this.update = function(){
		
		this.basepath = "../../data/" + this.model.id
		
		this.changePage()
		this.initNavButtons()
		this.getCronStatus()
		
		if( this.model.id ){
			this.updateTitle()
			this.updateOnOff()
		}
	}
	
	
	//----------------------
	// ON OFF
	//----------------------
	this.updateOnOff = function(){
		main_view = this
		
		$(".switch").show()
		
		$(".switch").off("click")
		$(".switch").on("click", function(){   main_view.onClickOnOff()   } )
		
		$.ajax({
			url: this.basepath + "/on_off.txt?rand=" + Math.random(), 
			data: {},
			type: 'get',
			error: function(XMLHttpRequest, textStatus, errorThrown){
				main_view.onOnOffDataLoaded( textStatus )
			},
			success: main_view.onOnOffDataLoaded
		});
	}
	
	this.onOnOffDataLoaded = function( data ){
		( data == 1 )? $(".switch").addClass("switch_on") : $(".switch").removeClass("switch_on");
	}
	
	this.onClickOnOff = function(){
		$(".switch").toggleClass("switch_on")
		$.get( "../?q=on_off&on=" + $(".switch").hasClass("switch_on") + "&id=" + this.model.id + "&random=" +  Math.random() , function( data ){ alert( data ); window.location.reload()  } )
	}
	
	//--------------------------
	
	this.updateTitle = function(){
		$(".event_title").load( "../?q=get_event_name&id=" + this.model.id )	
	}
	
	this.changePage = function(){
		( this.model.hash )? this.show( this.model.hash ) : this.show( "etusivu" );	
	}
	
	this.hideEverything = function(){
		$(".view").hide()	
		
	}
	
	this.show = function( hash ){
		
		this.hideEverything();
		$("." +  hash.target ).show()

		if( hash != "etusivu" && hash.target != null ){
			
			this.getLists( 		hash.target, this.model.id )
			this.getDates( 		hash.target, this.model.id )
			this.initButtons( 	hash.target, this.model.id )
			
			$(".navigation ul").show()
			$(".event_title").show()
		
		}else{
			$(".navigation ul").hide()
			$(".event_title").hide()
			$(".switch").hide()
		}
		this.checkForErrors( hash.status,hash.parameter )
		this.highlightNavi( hash.target )
	}

	//------------------------------------
	// BUTTONS
	//------------------------------------
	this.initButtons = function( hash_target, id ){
		parent = this
		if( hash_target =="invites" ){
			
			$(".register button").off( "click" )
			$(".register button").on( "click", function(){ parent.sendRegister( hash_target, id ) } )
			
			$(".invite_add_email button").off( "click" )
			$(".invite_add_email button").on( "click", function(){ parent.addEmail( hash_target, id ) } )	
		}
		
		var targets = [ "invites","polls", "reminders_registered", "reminders_not_registered", "register" ]
		while( targets.length > 0 ){  
			target = targets.pop()
			if( target != "registered" ) this.loadDate( target, id )
			this.setButton( target, id, parent )  
		}
	}
	this.setButton = function( target, id, parent ){
		
		try{
			$("." + target + " .send_one button").off( "click" )
			$("." + target + " .send_one button").on( "click", function(){ parent.sendOne( target, id ) 	})
		}catch( error ){}
		
		try{
		$("." + target + "_to_be_sended .full_list button").off("click")
		$("." + target + "_to_be_sended .full_list button").on("click", function(){ parent.saveUpdatedList( target, id ) 	})
		}catch( error ){}
		
		try{
		$("." + target + "_to_be_sended .body_text button").off("click")
		$("." + target + "_to_be_sended .body_text button").on("click", function(){ parent.saveUpdatedBodyText( target, id ) 	})
		}catch( error ){}
		
	}
	this.sendOne = function( target, id ){
		$.get( "../?q=send_one_" +target+ "&posting=" +target+ "&id=" + id,{}, function( data ){ alert( data ); /*window.location.reload()*/  })
	}

	
	this.addEmail = function ( hash_target, id){
		$.get( "../?q=add_to_invite&id=" +id+ "&email=" + $(".invite_add_email .email").val(),{}, function(){ window.location.reload() })
	}
	this.sendRegister = function ( hash_target, id){
		$.get( "../?q=register&id=" +id+ "&email=" + $(".register .register_email").val(),{}, function( data ){ alert( data ); /*window.location.reload()*/  })
	}

	//------------------------------------
	// GET LISTS
	//------------------------------------
	this.getLists = function( hash_target, id ){
		
		if( hash_target != "errors"){
			this.loadSendLists( hash_target )
			this.loadBodyText( hash_target )
			//this.loadDate( hash_target, id )
		}else{
			$("#errors pre").load( "../../data/errors/errors.txt" );
		}
		
		if( hash_target =="invites" ){
			$("#invites_tabs .registered pre").load( 	"../" + this.model.paths['register'].registered + this.random_string )
			$("#invites_tabs .unregistered pre").load(  "../?q=list_unregistered&id=" + id );
			
			$( "#registered .body_text textarea" ).load( 	"../" + this.model.paths["register"].body_text 	+ this.random_string,  function(){   main_view.resizeTextArea( "#registered .body_text textarea"  )   } )
		}
		
	}
	
	this.loadSendLists = function( target ){
		to_be_sended_container = "#" + target + "_tabs ." + target + "_to_be_sended textarea.email_list"
		sended_container = "#" + target + "_tabs ." + target + "_sended pre"
		
		var main_view = this
		$( to_be_sended_container ).load( 	"../" + this.model.paths[target].to_be_sended 	+ this.random_string,  function(){  main_view.resizeTextArea( to_be_sended_container )  } )
		$( sended_container ).load( 		"../" + this.model.paths[target].sended 		+ this.random_string )
	
	}
	this.saveUpdatedList = function( target, id ){
		$.post( 
				"../",
				{ 'q':'update_list', 'posting': target, 'id': id, 'list': $("." + target + "_to_be_sended textarea.email_list").val() },
				function(data){ 
						alert( data );
						window.location.reload() 
					}
				)
	}
	//------------------
	// Body text
	//------------------
	this.loadBodyText = function( target ){
		body_text_container =  "#" + target + "_tabs ." + target + "_to_be_sended textarea.body_text"
		var main_view = this
		$( body_text_container ).load( 	"../" + this.model.paths[target].body_text 	+ this.random_string,  function(){   main_view.resizeTextArea( body_text_container  )   } )
	}
	
	this.saveUpdatedBodyText = function( target, id ){
		$.post( 
				"../",
				{ 'q':'update_body_text', 'posting': target, 'id': id, 'body_text': $("." + target + "_to_be_sended textarea.body_text").val() },
					function(data){ 
						alert( data); 
						window.location.reload()
					}
				)
	}
	//------------------
	this.resizeTextArea = function( target ){
		$(".hidden_temp_for_email_lists pre").html(  $(target).html()  )
		 $(target).height( $(".hidden_temp_for_email_lists").height()+10 )
	}
	//-----------------------------
	// Dates
	//-----------------------------
	this.getDates = function( hash_target, id ){
		
		main_view = this
		//Reset
		$('#' + hash_target + ' .date_reset').off( "click" )
		$('#' + hash_target + ' .date_reset').on( "click", function(){ main_view.resetDate( id , hash_target ) })
		//Datepicker
		$.datepicker.setDefaults( $.datepicker.regional[ "fi" ] );
		$('#' + hash_target + '_date').datetimepicker( {
   										onClose: function(date_string, inst) {  main_view.sendDate( id , hash_target, date_string ) }
									} );
	}
	this.sendDate = function( id, target, date_string ){
		url = "../?q=set_date&id=" + id + "&date=" + date_string + "&posting=" + target
		$.get( url, this.onDateSend ) 
	}
	this.onDateSend = function( data ){
		( data>0 )? alert("Aika vaihdettu") : alert("Vaihto epäonnistui"); 
		window.location.reload()
	}
	this.resetDate = function( id, target ){
		$.get("../?q=reset_date&id=" + id + "&posting=" + target, function(){ window.location.reload() } )
	}
	this.loadDate = function( target, id ){
		$.get("../?q=get_date&id=" + id + "&posting=" + target,{}, function( data ){ 
																						$('#' + target + '_date').val( data );
																						if( data ){
																							$(".quick_view ." +target+ ".item .date").html( data.split(".")[0] +"."+data.split(".")[1] +"." )
																						}else{
																							$(".quick_view ." +target+ ".item .date").html( "-" )
																						}
																					}) 
	}
	//--------------------------------
	// Cron
	//---------------------------------
	this.getCronStatus = function(){
		$(".cron").load( "../?q=cron_status" )	
	}
	
	//--------------------------------
	// Navi
	//---------------------------------
	this.highlightNavi = function( target ){
		$(".navigation ul li a").removeClass("selected_li")
		$(".navigation ul li a." + target ).addClass("selected_li")
	}
	this.checkForErrors = function( hash_status, hash_parameter ){
		$(".error").hide()
		if( hash_status == "error" ){ 
			$(".error." + hash_parameter).show()
		}
	}
	
	//--------------------------------
	// Buttons
	//---------------------------------
	this.initNavButtons = function(){
		var main_view = this
		
		var targets = [ "invites", "register", "polls", "reminders_registered", "reminders_not_registered","errors" ]
		while( targets.length > 0 ){ this.setNaviLink( targets.pop() ) }
		$("#event_selection select").change( function( event ){ main_view.changeEvent() }  );
	}
	
	this.setNaviLink = function( target ){
		$("a." + target ).off( "click" )
		$("a." + target ).on( "click", function(){  main_view.changeView( target ) } )
	}
	
	this.changeView = function( view_name ){
		this.changeHash( view_name + "-show-" + this.model.id )
	}
	this.changeEvent = function(){
		this.changeHash("invites-show-" + $("#event_selection select").val() )
	}
	this.changeHash = function( value ){
		window.location.hash = value
	}
	
	this.populateEventDropDown = function( data ){
		
		$.template("event_dropdown_header","<option value='0'>Valitse tapahtuma:</option>")
		$.template("event_dropdown_item","<option value='{{=folder_id}}'>{{=folder_name}}</option>\n")
		
		$("#event_selection select").html( 		$.render( [{}],"event_dropdown_header" )  	)
		$("#event_selection select").append( 	$.render( data, "event_dropdown_item" )  	)
		
		//set selected option
		$("#event_selection option[value=" +this.model.id+ "]").attr('selected', 'selected')


	}
		

	
	this.init()
	
}

