/**
 * 
 */
;(function($){
	
	$(document).ready(function(){
		
		$('#yt-server-key-deprecate').click(function(e){
			e.preventDefault();
			$('#yt-server-key-deprecated').toggleClass('hide-if-js');
		});
		
		// tabs
		if( typeof(Storage) !== "undefined" ){
			var data = {
				active : sessionStorage['cbc_tab_active'],
				activate : function(event, ui){
					$(ui.newTab).find('i')
						.removeClass('dashicons-arrow-right')	
						.addClass('dashicons-arrow-down');
					
					$(ui.oldTab).find('i')
						.addClass('dashicons-arrow-right')	
						.removeClass('dashicons-arrow-down');
					
					sessionStorage['cbc_tab_active'] = ui.newTab.index();
				},
				create: function(event, ui){
					$(ui.tab).find('i')
						.removeClass('dashicons-arrow-right')	
						.addClass('dashicons-arrow-down');
				}
			};
		}else{
			var data = {};
		};
		
		var h = $(location).attr('hash');
		if( '' != h ){
			var item = $( '.cbc-tab-labels a[href="' + h + '"]' );
			if( item.length > 0 ){
				var items = $( '.cbc-tab-labels a' ),
					index = $.inArray( item[0], $.makeArray( items ) );
				if( -1 != index ){
					data.active = index;
				}				
			} 
		}
		
		$( "#cbc_tabs" ).tabs( data );
		// end tabs
		
		var checkbox = $('.toggler').find('input[type=checkbox]');
		$.each(checkbox, function(i, ch){
			var tbl = $(this).parents('table'),
				tr = $(tbl).find('tr.toggle');
			
			if( !$(this).is(':checked') ){
				$(tr).hide();
			}	
			
			$(this).click(function(){
				if( $(ch).is(':checked') ){
					$(tr).show(400);
				}else{
					$(tr).hide(200);
				}
			});
			
		});
		
	});
	
})(jQuery);