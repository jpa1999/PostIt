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

		getLists();
		
		this.checkForErrors( hash_status, hash_parameter )
		this.highlightNavi( hash_target )
		
	}
	
	this.getLists = function(){
	
		
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
	
	this.init()
	
}

