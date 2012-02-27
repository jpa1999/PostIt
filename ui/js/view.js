// JavaScript Document


var MainView = function( model ){

	this.model = model
	this.cookies = new Cookies;
	
	this.init = function(){
		this.addHashListener()
		this.changePage()
		this.initButtons()
	}
	
	
	
	
	this.addHashListener = function(){
		var parent = this
		jQuery(window).hashchange( function(){ parent.changePage() }  );	
	}
	
	this.changePage = function(){
		( this.model.parent.checkHash() )? this.show( this.model.parent.checkHash() ) : this.show( "etusivu" );	
		
		
	}
	
	this.hideEverything = function(){
		$(".view").hide()	
		
	}
	
	this.show = function( hash ){
		
		var url_variables = new UrlVariables()
		
		var hash_array 		= hash.split("-")
		var hash_target 	= hash_array[0]
		var hash_status 	= hash_array[1]
		var hash_parameter 	= hash_array[2]

		if( hash_parameter ){
			this.model.updateID( hash_parameter )	
		}
		
		this.hideEverything();
		$("." +  hash_target ).show()

		if( hash != "etusivu" ){
			
			this.getLists( hash_target, this.model.id )
			this.getDates( hash_target, this.model.id )
			this.initRegisterForm( hash_target, this.model.id )
			
			$(".navigation ul").show()
			$(".event_title").show()
		
		}else{
			$(".navigation ul").hide()
			$(".event_title").hide()
		}
		this.checkForErrors( hash_status, hash_parameter )
		this.highlightNavi( hash_target )
		
	}
	
	this.initRegisterForm = function( hash_target, id ){
		parent = this
		if( hash_target =="invites" ){
			$(".register button").click( function(){ parent.sendRegister( hash_target, id ) } )	
			$(".invite_add_email button").click( function(){ parent.addEmail( hash_target, id ) } )	
			$(".send_one_invite button").click( function(){ parent.sendInvite( hash_target, id ) } )	
		}
	}
	this.addEmail = function ( hash_target, id){
		$.get( "..\?q=add_to_invite&id=" +id+ "&email=" + $(".invite_add_email .email").val(),{}, function(){ window.location.reload() }  )
		//window.location.reload()
	}
	this.sendRegister = function ( hash_target, id){
		$.get( "..\?q=register&id=" +id+ "&email=" + $(".register .register_email").val(),{}, function(){ window.location.reload() }  )
	}
	this.sendInvite = function ( hash_target, id){
		$.get( "..\?q=send_invite&id=" + id,{}, function(){ window.location.reload() }  )
	}
	
	this.getLists = function( hash_target, id ){
		
		var basepath = "../../data/" + id
		var unregistered_path = "../?q=list_unregistered&id=" + id
		var random_string = "?rand=" + Math.random()
		
		if( hash_target =="invites" ){
			$("#invites_tabs .invites_to_be_sended pre").load( basepath + "/invite/to_be_sended.txt" + random_string )
			$("#invites_tabs .invites_sended pre").load( basepath + "/invite/sended.txt" + random_string )
			$("#invites_tabs .registered pre").load( basepath + "/registered/registered.txt" + random_string )
			$("#invites_tabs .unregistered pre").load( unregistered_path );
			
			this.loadDate( "invites", id )
		}
		if( hash_target =="reminders_registered" ){
			$("#reminders_registered_tabs .reminders_reg_to_be_sended pre").load( basepath + "/reminders_registered/to_be_sended.txt" + random_string );	
			$("#reminders_registered_tabs .reminders_reg_sended pre").load( basepath + "/reminders_registered/sended.txt" + random_string );
		}
		if( hash_target =="reminders_not_registered" ){
			$("#reminders_not_registered_tabs .reminders_not_reg_to_be_sended pre").load( basepath + "/reminders_not_registered/to_be_sended.txt" + random_string );	
			$("#reminders_not_registered_tabs .reminders_not_reg_sended pre").load( basepath + "/reminders_not_registered/sended.txt" + random_string );
		}
		if( hash_target =="polls" ){
			$("#polls_tabs .polls_to_be_sended pre").load( basepath + "/poll/to_be_sended.txt" + random_string );	
			$("#polls_tabs .polls_sended pre").load( basepath + "/poll/sended.txt" + random_string );
		}
		if( hash_target =="errors" ){
			$("#errors pre").load( "../../data/errors/errors.txt" );	
		}
		
	}
	
	this.getDates = function( hash_target, id ){
		parent = this
		$.datepicker.setDefaults( $.datepicker.regional[ "fi" ] );
		//$('#' + hash_target + '_date').datetimepicker( $.datepicker.regional[ "fi" ]  );
	
		$('#' + hash_target + '_date').datetimepicker( {
   										onClose: function(dateText, inst) { 
												alert("hep!")
												alert( dateText )
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
		$("#event_selection select").change( function( event ){ main_view.changeEvent() }  );
		
		$("a.invites").click( function(){ window.location.hash ="invites-show-" + main_view.model.id } )
		$("a.register").click( function(){ window.location.hash ="register-show-" + main_view.model.id } )
		$("a.polls").click( function(){ window.location.hash ="polls-show-" + main_view.model.id } )
		$("a.reminders_registered").click( function(){ window.location.hash ="reminders_registered-show-" + main_view.model.id } )
		$("a.reminders_not_registered").click( function(){ window.location.hash ="reminders_not_registered-show-" + main_view.model.id } )
	}
	
	this.changeEvent = function(){
		this.model.updateID( $("#event_selection select").val() )
		window.location.hash = "invites-show-" + this.model.id;
	}
	this.populateEventDropDown = function( data ){
		for( index in data.data ){
			folder_id = data.data[ index ].dir_name
			$("#event_selection select").append( "<option value='" + folder_id + "'>" + folder_id + "</option>\n" )
		}
		
	}
	
	this.init()
	
}

