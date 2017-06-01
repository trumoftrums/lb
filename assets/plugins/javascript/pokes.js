/**
  * pokes
  * @Plugins for Wowonder
  * @author Pp Galvan
  */

	/* add an user suggest */
	var send_poke_active = 1;
	
    /*Search Suggest friend poke*/
      $('body').on('keyup', '.text_search_toque', function(){
      var _this = $(this);
        var raw_query = _this.val();
        if(raw_query !== null && raw_query.length > 0) {
            var query = raw_query;
            /* check if results dropdown-menu already exist */
            if(_this.next('.dropdown-menu').length == 0) {
                /* construct a new one */
                var offset = _this.offset();
                var posX = offset.left - $(window).scrollLeft();
                if($(window).width() - posX < 180) {
                    _this.after('<div class="dropdown-menu auto-complete tl"><div class="loader loader_small ptb10"></div></div>');
                } else {
                    _this.after('<div class="dropdown-menu auto-complete"><div class="loader loader_small ptb10"></div></div>');
                }
            }
            $.post( site_ajax+"/?f=autocomplete&s=users&type=poke", {'query': query}, function(response) {
                if(response.callback) {
                    eval(response.callback);
                } else if(response.suggest) {
                    _this.next('.dropdown-menu').show().html(response.suggest);
                }
            }, 'json');
        } else {
            /* check if results dropdown-menu already exist */
            if(_this.next('.dropdown-menu').length > 0) {
                _this.next('.dropdown-menu').hide();
            }
        }	
    });
	  
	 /* show previous results dropdown-menu when the input is clicked */
    $('body').on('click focus', '.text_search_toque', function() {
        var query = $(this).val();
        if(query !== null && query.length > 0) {
            $(this).next('.dropdown-menu').show();
        }
    });
	
    /* hide the results dropdown-menu when clicked outside the input */
    $('body').on('click', function(e) {
        if(!$(e.target).is(".text_search_toque")) {
            $('.text_search_toque').next('.dropdown-menu').hide();
        }
    }); 

		 /*Send poke for search suggest friend*/
     function poke_list_send(self){
          var id = self.attr('data-uid');
		  if(send_poke_active != 1){ return false; }
		  send_poke_active = 0;
		  self.closest('.content_pokes').find('.dropdown-menu').hide();
		  $('.poke_'+id).find('.btn_poke').addClass('x-hidden');
		  $('.poke_'+id).find('.delete_poke').addClass('x-hidden');
		  $('.poke_'+id).find('.poke_sending').removeClass('x-hidden');
          $.ajax({ type:'post', url: site_ajax, data: {'f':'pokes', 's':'send', 'owner_id':id }, cache:false, success: function(result) { 
              send_poke_active = 1;
			  if(result.result == 1){ 
                  modal('#modal-message', {title: 'You send a poke', message: 'You send a poke to '+result.sendto.user_fullname });
              } else { 
                  $('.poke_'+id).find('.btn_poke').removeClass('x-hidden');
                  $('.poke_'+id).find('.poke_sending').addClass('x-hidden');
                  $('.poke_'+id).find('.delete_poke').removeClass('x-hidden');
                  modal('#modal-message', {title: 'Error', message: result.message});	
				  self.closest('.content_pokes').find('.dropdown-menu').show();
              }			  
			 $(".text_search_toque").val('');
             }
          });
      }
 
    /*delete poke suggest & recived from friends*/
    function delete_poke(self){ 
         var id = self.closest('.poke').attr('data-id');
         self.closest('.poke').addClass('x-hidden');
		 self.prop('disabled', true);
         $.ajax({ type: 'post', url: site_ajax, data: {'f':'pokes', 's':'delete', 'owner_id':id }, cache : false, success: function(result) { 
				 self.prop('disabled', false);
				 if(result.result == 1){ 
					 self.closest('.poke').remove();
				 } else { 
					 self.closest('.poke').removeClass('x-hidden'); modal('#modal-message', {title: 'Error', message: result.message});
				 }			 
			 }
         });
     }

	 /*profile poke*/
     function profile_poke(id){
		if(send_poke_active != 1){ return false; } 
	    $.ajax({ type: 'post',url: site_ajax, data: {'f':'pokes', 's':'send', 'owner_id':id }, cache : false,  success: function(result) { 																															
           send_poke_active = 0;
		   if(result.result == 1){ 
              modal('#modal-message', {title: 'You send a poke', message: 'You send a poke to '+result.sendto.user_fullname });
           } else {
              modal('#modal-message', {title: 'Error', message: result.message });	 
	       }
        }});
	 }
	 
     /*send a poke*/
     function poke(self){ 
	      var id = self.closest('.poke').attr('data-id');
          $('.poke_'+id).find('.btn_poke').addClass('x-hidden');
          $('.poke_'+id).find('.poke_sending').removeClass('x-hidden');
          $('.poke_'+id).find('.delete_poke').addClass('x-hidden');
		  self.prop('disabled', true);
          $.ajax({ type: 'post', url: site_ajax, data:{'f':'pokes', 's':'send', 'owner_id':id }, cache:false, success:function(result){ 
					if(result.result != 1){  
						$('.poke_'+id).find('.btn_poke').removeClass('x-hidden');
						$('.poke_'+id).find('.poke_sending').addClass('x-hidden');
						$('.poke_'+id).find('.delete_poke').removeClass('x-hidden');
						modal('#modal-message', {title: 'Error', message: result.message});
					}
					self.prop('disabled', false);	
                }
          });
     }

	//suggest click
	$('body').on('click', '.js_poke_add', function(event){ event.preventDefault(); var self = $(this); poke_list_send(self); });
	
	//button delete poke
	$('body').on('click', '.delete_poke', function(event){ event.preventDefault(); var self = $(this); delete_poke(self); });
	
	//button send poke
	$('body').on('click', '.btn_poke', function(event){ event.preventDefault(); var self = $(this); poke(self); });
	
	//button send poke in profile
	$('body').on('click', '.js_poke-user', function(event){ event.preventDefault(); var id = $(this).attr('data-uid'); profile_poke(id); });
