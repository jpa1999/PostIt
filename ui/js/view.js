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

		this.hideEverything();
		$("." +  hash_target ).show()

		this.getLists( hash_target, hash_parameter )
		
		this.checkForErrors( hash_status, hash_parameter )
		this.highlightNavi( hash_target )
		
	}
	
	this.getLists = function( hash_target, id ){
		
		var basepath = "../../data/" + id
		
		if( hash_target =="invites" ){
			$("#invites_tabs .invites_to_be_sended pre").load( basepath + "/invite/to_be_sended.txt" );	
			$("#invites_tabs .invites_sended pre").load( basepath + "/invite/sended.txt" );
			$("#invites_tabs .registered pre").load( basepath + "/registered/registered.txt" );
		}
		if( hash_target =="reminders_registered" ){
			$("#reminders_registered_tabs .reminders_reg_to_be_sended pre").load( basepath + "/reminders_registered/to_be_sended.txt" );	
			$("#reminders_registered_tabs .reminders_reg_sended pre").load( basepath + "/reminders_registered/sended.txt" );
		}
		if( hash_target =="reminders_not_registered" ){
			$("#reminders_not_registered_tabs .reminders_not_reg_to_be_sended pre").load( basepath + "/reminders_not_registered/to_be_sended.txt" );	
			$("#reminders_not_registered_tabs .reminders_not_reg_sended pre").load( basepath + "/reminders_not_registered/sended.txt" );
		}
		if( hash_target =="polls" ){
			$("#polls_tabs .polls_to_be_sended pre").load( basepath + "/poll/to_be_sended.txt" );	
			$("#polls_tabs .polls_sended pre").load( basepath + "/poll/sended.txt" );
		}
		
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
	
	}
	
	this.populateEventDropDown = function( data ){
		for( index in data.data ){
			folder_id = data.data[ index ].dir_name
			$("#event_selection select").append( "<option value='" + folder_id + "'>" + folder_id + "</option>\n" )
		}
		
	}
	
	this.init()
	
}

