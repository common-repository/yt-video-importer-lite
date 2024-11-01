/**
 * 
 */
;(function($){
	$(document).ready(function(){
		
		$(document).on('click', '#cbc-import-video-thumbnail', function(e){
			e.preventDefault();
			
			var data = {
				'action' 	: 'cbc_import_video_thumbnail',
				'id'		: YVIL_POST_DATA.post_id
			};
			
			$.ajax({
				type 	: 'post',
				url 	: ajaxurl,
				data	: data,
				success	: function( response ){
					WPSetThumbnailHTML( response.data );
				}
			});	
			
		});
		
	});
})(jQuery);