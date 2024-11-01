(function() {
	tinymce.PluginManager.requireLangPack('yvil_shortcode');
	tinymce.create('tinymce.plugins.CCBVideoPlugin', {
		init : function(ed, url) {
			
			// Register the command
			ed.addCommand('mceCCBVideo', function() {
				// dialog window, set in assets/back-end/js/shortcode-modal.js
				if( CCBVideo_DIALOG_WIN ){
					CCBVideo_DIALOG_WIN.dialog('open');
				}	
			});

			// Register button
			ed.addButton('yvil_shortcode', {
				title : 'Embed video',
				cmd : 'mceCCBVideo',
				class: 'CCB_dialog',
				url:'',
				image : url + '/images/ico.png'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('example', n.nodeName == 'IMG');
			});
		},

		createControl : function(n, cm) {
			return null;
		},

		getInfo : function() {
			return {
				longname 	: 'YouTube Videos for WordPress',
				author 		: 'Constantin Boiangiu',
				authorurl 	: 'http://www.constantinb.com',
				infourl 	: 'http://www.constantinb.com',
				version 	: "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('yvil_shortcode', tinymce.plugins.CCBVideoPlugin);
})();