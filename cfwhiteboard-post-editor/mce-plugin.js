(function(){

	// Get the URL to this script file (as JavaScript is loaded in order)
	// (http://stackoverflow.com/questions/2255689/how-to-get-the-file-path-of-the-currenctly-executing-javascript-code)
	var scripts = document.getElementsByTagName("script"),
	src = scripts[scripts.length-1].src;
	
	if ( scripts.length ) {
	
		for ( i in scripts ) {

			var scriptSrc = '';
			
			if ( typeof scripts[i].src != 'undefined' ) { scriptSrc = scripts[i].src; } // End IF Statement

			var txt = scriptSrc.search( 'cfwhiteboard-post-editor' );
			
			if ( txt != -1 ) {
			
				src = scripts[i].src;
			
			} // End IF Statement
		
		} // End FOR Loop
	
	} // End IF Statement



	var framework_url = src.substring(0, src.indexOf('cfwhiteboard-post-editor')) + 'cfwhiteboard-post-editor';
	var icon_url = framework_url + '/cfwhiteboard-button.png';


	// Make sure the "Kitchen Sink" is visible, or they won't be able to see the button we're adding
	setUserSetting('hidetb', '1');

	tinymce.create("tinymce.plugins.CfWhiteboard", {
		init: function(editor, url) {
			// taken from /wordpress/wp-includes/js/tinymce/plugins/wordpress/editor_plugin.dev.js
			// editor.controlManager.setActive('wp_adv', 1);
			// DOM.show(id);
			// t._resizeIframe(ed, tbId, -28);
			editor.settings.wordpress_adv_hidden = 0;
			// setUserSetting('hidetb', '1');
		},

		// openKitchenSink: function(editor) {
		// 	if (editor.settings.wordpress_adv_hidden) {
		// 		editor.execCommand('WP_Adv');
		// 	}
		// },
			
		createControl: function(name, controlManager) {
			if(name == "cfwhiteboard_button"){

				var button = controlManager.createSplitButton( "cfwhiteboard_button",{
					title: "Insert Workout Description(s)",
					image: icon_url,
					icons: false
				});
					
				var ticker = 0;

				button.updateMenuItems = function(c, dropMenu) {
					// cache the DropMenu instance on the SplitButton instance
					if (dropMenu) button.dropMenu = dropMenu;
					else dropMenu = button.dropMenu;

					// Gather class names/descriptions
					var classes = [];
					var tempClass;
					jQuery('#cfwhiteboard-wods-meta li').each(function() {
						tempClass = CFW.parseClassDescription( jQuery(this) );

						var hasDescription = false;
						for (var i = 0; i < tempClass.components.length; i++) {
							if (tempClass.components[i].description.replace(/^\s+/,'').replace(/\s+$/,'')) {
								hasDescription = true;
								break;
							}
						}
						// only add classes that have descriptions
						if (!hasDescription) return;

						// Generate the markup for the class description
						tempClass.markup = CFW.generateClassDescriptionMarkup(tempClass);
						tempClass.markup = CFW.newlineToBr(tempClass.markup);

						classes.push( tempClass );
					});

					if (!classes.length) {
						// no classes entered, provide link to CFW meta box
						dropMenu.add({
							title: '0 classes are being tracked. Click to add some workouts to CF Whiteboard.',
							onclick: function() {
								window.location = window.location.origin + window.location.pathname + '#cfwhiteboard-wods-meta';
								var $metaBox = jQuery('#cfwhiteboard-wods-meta');
								if ($metaBox.find('ul:visible').length == 0) {
									$metaBox.find('.handlediv').click();
								}
							}
						});
					} else if (classes.length > 1) {
						// multiple classes entered, provide menu item for inserting all at once

						var markupAll = '';
						for (var i = 0; i < classes.length; i++) {
							markupAll += classes[i].markup;
						}

						dropMenu.add({
							title: 'Insert All Classes',
							onclick: function() {
								tinyMCE.activeEditor.execCommand("mceInsertContent", false, markupAll);
							}
						});
					}

					var generateInsertMarkupFunc = function(markup) {
						return function() {
							tinyMCE.activeEditor.execCommand("mceInsertContent", false, markup);
						};
					};
					for (var i = 0; i < classes.length; i++) {
						dropMenu.add({
							title: 'Insert '+classes[i].name,
							onclick: generateInsertMarkupFunc( classes[i].markup )
						});
					}
				};
				button.onRenderMenu.add(button.updateMenuItems);

				var showMenuFunc = button.showMenu;
				button.showMenu = function() {
					if (button.dropMenu) {
						var count = 0;
						var last;
						for (var i in button.dropMenu.items) {
							++count;
							last = button.dropMenu.items[i];

							tinymce.DOM.remove(last.id);
							last.destroy();
							delete button.dropMenu.items[last.id];
						}

						// Update menu items
						button.updateMenuItems('?', button.dropMenu);
					} else {
						// menu not yet rendered
					}

					showMenuFunc.apply(this, arguments);
				};

				return button;
			
			} // End IF Statement
			
			return null;
		},

		/*
		addImmediate:function(d,e,a){d.add({title:e,onclick:function(){tinyMCE.activeEditor.execCommand( "mceInsertContent",false,a)}})},
		
		addWithDialog:function(d,e,a){d.add({title:e,onclick:function(){tinyMCE.activeEditor.execCommand( "cfwOpenDialog",false,{title:e,identifier:a})}})},
		*/

		getInfo: function() {
			return {
				longname: "CF Whiteboard: Insert Workout Description(s)",
				author: "CF Whiteboard",
				authorurl: "http://cfwhiteboard.com"/*,infourl:"http://cfwhiteboard.com",version:"1.0"*/
			};
		}
	});
	
	tinymce.PluginManager.add("CfWhiteboard", tinymce.plugins.CfWhiteboard);
})();
