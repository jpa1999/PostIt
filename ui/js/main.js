$(document).ready( documentReady )

function documentReady(){
	initAplication()

}

function initAplication(){
	var url_variables = new UrlVariables()
	var parent = this
	parent.App = new Application( parent )
}


var Application = function( parent ){
		
	this.id = 0
	
	
	this.init = function(){
		var application = this
		$.getJSON( "../?q=list_created_events",   function( data ){ 
														application.view.populateEventDropDown( data ) 
													})
		this.parent = parent
		this.view = new MainView( this )
	}
	
	this.updateID = function( id ){
		this.id = id
	}
	
	this.init()	
}

//------------------------------------
// HASH
//------------------------------------
this.checkHash = function(){
			
	if( window.location.hash != false ){
		hash_string = String( window.location.hash )
		hash_position = hash_string.indexOf('#')
		
		values = hash_string.substring(  hash_position+1 )
				
		hash_value_pairs = values.split("&")
				
		this.hash_items = new Array()
				
		for(i=0; i<hash_value_pairs.length; i++ ){
			item_name = hash_value_pairs[i].split("=")[0]
			item_value = hash_value_pairs[i].split("=")[1]
					
			this.hash_items[ item_name ] = item_value
		}
		
		return values
	}else{
		return false	
	}	

}


//--------------------------------
//--------------------------------
// url variables
//--------------------------------
//---------------------------------
var UrlVariables = function(){
	
	 var variable_pairs = []
	 
	 this.init = function(){ 
	 	 this.setVariablePairs()
	 }
	
	 this.setVariablePairs = function(){
		
		var search_string = top.location.search.substr(1)
		variable_pairs = search_string.split("&")
		
		if( variable_pairs.length == 0 ){
			variable_pairs[0] = search_string
		}
		
		for( i=0;i<variable_pairs.length;i++ ){
			variable_pairs[ i ] = variable_pairs[ i ].split("=")
		}
			 
	}
	
	 this.get = function( variable_name ){
		
		var variable_value = ""
		
		for( i=0;i<variable_pairs.length;i++ ){
			if( variable_pairs[ i ][ 0 ] == variable_name ){ 
				variable_value = variable_pairs[ i ][ 1 ]
				break 
			}
		}
					
		return variable_value
	}
	
	 this.init()
	
}

//--------------------------------------
// Cookies
//--------------------------------------
var Cookies = function(){
	
	this.set = function ( c_name,value,exdays ){
		
		var exdate=new Date();
		exdate.setDate( exdate.getDate() + exdays )
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString())
		document.cookie = c_name + "=" + c_value
	}

	this.get = function(c_name){
		var i,x,y,ARRcookies=document.cookie.split(";")
		for (i=0;i<ARRcookies.length;i++){
			x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="))
			y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1)
			x=x.replace(/^\s+|\s+$/g,"")
			if (x==c_name){   return unescape(y)  }
		 }
	}
	
	this.remove = function( c_name ){
		this.set( c_name,"",-1);
	}

}