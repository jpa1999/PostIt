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
	this.parent = parent
	this.paths = {}
	
	this.init = function(){
		this.url_variables = new UrlVariables()
		
		//Set id from url hash
		this.updateID()
		this.view = new MainView( this )
		this.addHashListener()
	}
		
	this.addHashListener = function(){
		var application = this
		jQuery(window).hashchange(  function(){ application.updateID() }   );	
	}
	
	this.updateID = function(){
		this.hash = new Hash()
		this.id = this.hash.parameter
		this.loadPathsAndFolders()
	}
	
	this.loadPathsAndFolders = function(){
		var application = this
		$.getJSON('../?q=get_paths&id=' + this.id, function( data ){ application.pathsLoaded( data ) })
		$.getJSON( "../?q=list_created_events",   function( data ){  application.eventsLoaded( data )  })
	}
	
	this.eventsLoaded = function( data ){
		( data )? this.view.populateEventDropDown( data.data )	: alert("Virhe tapahtumien listauksessa! Ota yhteyttä ylläpitoon.");
	}
	this.pathsLoaded = function( paths_data ){
		if( paths_data ){ 
			this.paths = paths_data
			this.view.update()
		}else{
			alert("Virhe polkujen listauksessa! Ota yhteyttä ylläpitoon.");
		}
	}
	
	this.setHashParameters = function(){
		return this.parent.checkHash()	
	}
	
	this.init()	
}

//------------------------------------
// HASH
//------------------------------------
var Hash = function(){
			
	this.init = function(){
		
		if( window.location.hash != false ){
			
			hash_string = String( window.location.hash )
			hash_position = hash_string.indexOf('#')
			values = hash_string.substring(  hash_position+1 )
			
			var hash_array 		= values.split("-")
			this.target 	= hash_array[0]
			this.status 	= hash_array[1]
			this.parameter = hash_array[2]
			
		}	
	}

	this.init()
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