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
		//alert("changePAge")
		( this.model.parent.checkHash() )? this.show( this.model.parent.checkHash() ) : this.show( "etusivu" );	
		
		_gaq.push(['_trackPageview',location.pathname + location.search  + location.hash]);
		snoobi.trackPageView( "tehtavatulevaisuudessa" + location.pathname + location.search  + location.hash )
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

		//snoobi.trackPageView( location.pathname + location.search  + location.hash, "tehtavatulevaisuudessa", hash_target);
				
		this.hideEverything();
		$("." +  hash_target ).show()
		
		this.checkForErrors( hash_status, hash_parameter )
		this.highlightNavi( hash_target )
		this.changeIllustration( hash_target )
		
		if( hash_target == "kilpailutyo" && ( hash_status == "error" || hash_status == "facebook_auth"  ) ){
			this.showVoting()
		}
		if( hash_status == "facebook_auth" ){
			$("#vote_name").val( url_variables.get("name")  )
			$("#vote_email").val( url_variables.get("email") )
			$("#vote_facebook_id").val( url_variables.get("facebook_id") )
			$("#voting_form").submit()
		}
			
		if( hash_status == "vote_kiitos" ){
			
			alert( "Kiitos äänestäsi! Onnea arvontaan." )
			/*$( "#dialog-message" ).dialog({
				modal: true,
				buttons: {
					Ok: function() {
						$( this ).dialog( "close" );
					}
				}
			});*/
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
	
	this.initEditTools = function(){
		var parent = this
		$(".image.item_edit").hide()	
		$(".image .show_tools").click( function(){ parent.onEdit("image")  })
		$(".image .save_edit").click( function(){ parent.onEditEnd("image")  })
		$(".image_cancel").click( function(){
			$( ".image.item_display").show()
			$( ".image.item_edit").hide()
		
		} )
		
		
		
		$(".team_name.item_edit").hide()	
		$(".team_name .show_tools").click( function(){ parent.onEdit("team_name")  })
		$(".team_name .save_edit").click( function(){ parent.onEditEnd("team_name")  })
		
		$(".url.item_edit").hide()	
		$(".url .show_tools").click( function(){ parent.onEdit("url")  })
		$(".url .save_edit").click( function(){ parent.onEditEnd("url")  })
		
		$(".team.item_edit").hide()	
		$(".team .show_tools").click( function(){ parent.onEdit("team")  })
		$(".team .save_edit").click( function(){ parent.onEditEnd("team")  })
		
		$(".description.item_edit").hide()	
		$(".description .show_tools").click( function(){ parent.onEdit("description")  })
		$(".description .save_edit").click( function(){ parent.onEditEnd("description")  })
		
		$(".title.item_edit").hide()	
		$(".title .show_tools").click( function(){ parent.onEdit("title")  })
		$(".title .save_edit").click( function(){ parent.onEditEnd("title")  })
		
		$(".contact_details.item_edit").hide()	
		$(".contact_details .show_tools").click( function(){ parent.onEdit("contact_details")  })
		
	}
	
	this.onEdit = function( target ){
		$( "." + target + ".item_display").hide()
		$( "." + target + ".item_edit").show()
	}
	
	this.onEditEnd = function( target ){
		$( "." + target + ".item_display").show()
		$( "." + target + ".item_edit").hide()
		
		var jquery_path =  "." + target + ".item_edit .edit_field"
		
		$("#update_form .update_value").val( $( jquery_path ).val() )
		$("#update_form .target").val( target )
		$("#update_form").submit()
		
	}
	
	
	this.init()
	
}

