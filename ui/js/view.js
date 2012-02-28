// JavaScript Document


var MainView = function( model ){

	this.model = model
	this.cookies = new Cookies;
	this.random_string =	"?rand=" + Math.random()
	this.basepath = 		"../../data/" + this.model.id
		
	
	this.init = function(){}

	this.update = function(){
		this.changePage()
		this.initButtons()
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

		if( hash != "etusivu" ){
			
			this.getLists( 			hash.target, this.model.id )
			this.getDates( 			hash.target, this.model.id )
			this.initRegisterForm( 	hash.target, this.model.id )
			
			$(".navigation ul").show()
			$(".event_title").show()
		
		}else{
			$(".navigation ul").hide()
			$(".event_title").hide()
		}
		this.checkForErrors( hash.status,hash.parameter )
		this.highlightNavi( hash.target )
		
	}
	
	this.initRegisterForm = function( hash_target, id ){
		parent = this
		if( hash_target =="invites" ){
			$(".register button").click( function(){ parent.sendRegister( hash_target, id ) } )	
			$(".invite_add_email button").click( function(){ parent.addEmail( hash_target, id ) } )	
			$(".send_one_invite button").click( function(){ parent.sendInvite( hash_target, id ) } )	
			
		}
		
		$(".send_one_reminder_not_reg button").click( function(){ parent.sendReminderNotReg( hash_target, id ) } )
		$(".send_one_reminder_reg button").click( function(){ parent.sendReminderReg( hash_target, id ) } )
		$(".send_one_poll button").click( function(){ parent.sendPoll( hash_target, id ) } )
	}
	this.addEmail = function ( hash_target, id){
		$.get( "../?q=add_to_invite&id=" +id+ "&email=" + $(".invite_add_email .email").val(),{}, function(){ window.location.reload() })

	}
	this.sendRegister = function ( hash_target, id){
		$.get( "../?q=register&id=" +id+ "&email=" + $(".register .register_email").val(),{}, function(){ /*window.location.reload()*/  })
	}
	
	//Send buttons
	this.sendInvite = function ( hash_target, id){
		$.get( "../?q=send_invite&id=" + id,{}, function(){ window.location.reload()  })
	}
	this.sendReminderNotReg = function ( hash_target, id){
		alert("sendReminderNotReg")
		$.get( "../?q=send_reminder_not_registered&id=" + id,{}, function(){ window.location.reload()  })
	}
	this.sendReminderReg = function ( hash_target, id ){
		alert("sendReminderReg")
		$.get( "../?q=send_reminder_registered&id=" + id,{}, function(){ window.location.reload()  })
	}
	this.sendPoll= function ( hash_target, id ){
		$.get( "../?q=send_poll&id=" + id,{}, function(){ window.location.reload()  })
	}
	
	this.getLists = function( hash_target, id ){
		
		if( hash_target != "errors"){
			this.loadSendLists( hash_target )
			this.loadDate(		hash_target, id )
		}else{
			$("#errors pre").load( "../../data/errors/errors.txt" );
		}
		
		if( hash_target =="invites" ){
			$("#invites_tabs .registered pre").load( 	this.basepath + "/registered/registered.txt" + this.random_string )
			$("#invites_tabs .unregistered pre").load(  "../?q=list_unregistered&id=" + id );
		}
		
	}
	
	this.loadSendLists = function( target ){
		to_be_sended_container = "#" + target + "_tabs ." + target + "_to_be_sended pre"
		sended_container = "#" + target + "_tabs ." + target + "_sended pre"
		
		$( to_be_sended_container ).load( 	"../" + this.model.paths[target].to_be_sended 	+ this.random_string )
		$( sended_container ).load( 		"../" + this.model.paths[target].sended 		+ this.random_string )
		
		//$( to_be_sended_container ).load( 	this.basepath + "/" + target + "/to_be_sended.txt" + this.random_string )
		//$( sended_container ).load( 		this.basepath + "/" + target + "/sended.txt" + this.random_string 		)
	
	}
	
	this.getDates = function( hash_target, id ){
		parent = this
		$.datepicker.setDefaults( $.datepicker.regional[ "fi" ] );
		$('#' + hash_target + '_date').datetimepicker( {
   										onClose: function(dateText, inst) { 
												$.get("../?q=set_date&id=" + id + "&date=" +dateText+ "&posting=" + hash_target) 
										}
									} );
	}
	this.loadDate = function( hash_target, id ){
			$.get("../?q=get_date&id=" + id + "&posting=" + hash_target,{}, function( data ){ $('#' + hash_target + '_date').val( data ) }) 
			
	}
	
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
	
	this.initButtons = function(){
		
		var main_view = this
		var targets = [ "invites", "register", "polls", "reminders_registered", "reminders_not_registered" ]
		
		for(var target in targets){
			var current_target = targets[ target ]
			$("a." + current_target ).click( function(){  main_view.changeView( current_target ) } )
		}
		$("#event_selection select").change( function( event ){ main_view.changeEvent() }  );

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
		$.template("event_dropdown_item","<option value='{{=folder_id}}'>{{=folder_id}}</option>\n")
		
		$("#event_selection select").html( 		$.render( [{}],"event_dropdown_header" )  	)
		$("#event_selection select").append( 	$.render(data, "event_dropdown_item" )  	)

	}
	
	this.init()
	
}

