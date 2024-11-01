jQuery(document).ready(function($) {
	// init admin dialog
	$('#my-dialog').dialog({
        title: vbee_ajax_object.confirm,
        dialogClass: 'wp-dialog',
        autoOpen: false,
        draggable: false,
        width: 'auto',
        modal: true,
        resizable: false,
        closeOnEscape: true,
        position: {
            my: "center",
            at: "center",
            of: window
        },
        open: function () {
            // close dialog by clicking the overlay behind it
            $('.ui-widget-overlay').bind('click', function(){
                $('#my-dialog').dialog('close');
            })
        },
        create: function () {
            // style fix for WordPress admin
            $('.ui-dialog-titlebar-close').addClass('ui-button');
        },
    });

	// feature list action convert
	function vBeeRequestConvert(id){
		if(vbee_ajax_object.is_page === 'single' || vbee_ajax_object.is_page === 'is_admin'){
			var post_id = id;
			var element = $('#vbee-'+post_id);
			var eLoad = $('.vbee-load');
		    $.ajax({
		        type: 'POST',
		        url: vbee_ajax_object.ajaxurl,
		        dataType: 'json',
		        data: {
		            'action' : 'Vbee_action', 
		            'post_id': post_id,
		            'security': vbee_ajax_object.ajax_nonce
		        },
		        beforeSend: function () {
		        	element.html('<a class="inprocess-audio">Đang tạo audio</a>');
		        	eLoad.css('display', 'flex');
		        	var start = setInterval(function(){
		        		$.ajax({
					        type: 'POST',
					        url: vbee_ajax_object.ajaxurl,
					        dataType: 'json',
					        data: {
					            'action' : 'VbeeActionCheck', 
					            'post_id': post_id,
					            'security': vbee_ajax_object.ajax_nonce
					        },
					        success: function (data) {
					        	if(data.status){
					        		element.html('<a href="'+data.audio+'" tag class="test-audio" target="_blank">Nghe Thử</a><a class="del-audio action_delete" data-id="'+post_id+'">Xóa audio</a>');
					        		clearInterval(start);
					        	}
					        },
					    });
		        	}, 5000);
		        },
		        success: function (data) {
		        	eLoad.hide();
		        },
		    });
		}
	}

	// action convert
	$('.bg-add').click(function(event) {
		event.preventDefault();
		var id = $(this).data('id');
		vBeeRequestConvert(id);
	});

	// feature delete file audio 
	function vBeeAudioDelete(id){
		var elementVbee = $('#vbee-'+id);
		var eLoad = $('.vbee-load');
		$.ajax({
	        type: 'POST',
	        url: vbee_ajax_object.ajaxurl,
	        dataType: 'json',
	        data: {
	            'action' : 'VbeeActionDelete', 
	            'post_id': id,
	            'security': vbee_ajax_object.ajax_nonce
	        },
	        beforeSend: function () {
	        	eLoad.css('display', 'flex');
	        },
	        success: function (data) {
	        	if(data.status){
	        		elementVbee.html('<a class="none-audio">Chưa có audio</a>');
	        	}
	        	eLoad.hide();
	        },
	    });
	}

	// action delete
	$(document).on( "click", ".action_delete", function(e) {
	  	e.preventDefault();
        var id = $(this).data('id');
        $('#post_action').val(id);
        $('#my-dialog').dialog('open');
	});

	$(document).on( "click", ".action_canel", function(e) {
	  	e.preventDefault();
        $('#my-dialog').dialog('close');
	});

	$(document).on( "click", ".confirm_delete", function(e) {
	  	e.preventDefault();
        var post_id = $('#post_action').val();
        vBeeAudioDelete(post_id);
        $('#my-dialog').dialog('close');
	});

	$('#posts-filter').submit(function(event) {
	  	var action = $('#bulk-action-selector-top').val();
	  	if(action == 'convert' || action == 'del_audio'){
	  		event.preventDefault();
			$('.check-column input[name="post[]"]:checked').each(function() {
			    if(action == 'del_audio'){
			    	vBeeAudioDelete($(this).val());
			    } else if(action == 'convert'){
			    	vBeeRequestConvert($(this).val())
			    }
			});
			
	  	}
	});
});