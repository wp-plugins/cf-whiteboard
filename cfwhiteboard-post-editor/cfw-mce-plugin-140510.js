(function(){

	// Get the URL to this script file (as JavaScript is loaded in order)
	// (http://stackoverflow.com/questions/2255689/how-to-get-the-file-path-of-the-currenctly-executing-javascript-code)
	var scripts = document.getElementsByTagName("script");
	var src = scripts[scripts.length-1].src;

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
	var icon_url = framework_url + '/cfw-mce-button-130113.png';


	// Make sure the "Kitchen Sink" is visible, or they won't be able to see the button we're adding
	if (typeof(setUserSetting) == 'function') setUserSetting('hidetb', '1');

	tinymce.create("tinymce.plugins.cfwhiteboard", {
		init: function(editor, url) {
			// taken from /wordpress/wp-includes/js/tinymce/plugins/wordpress/editor_plugin.dev.js
			// editor.controlManager.setActive('wp_adv', 1);
			// DOM.show(id);
			// t._resizeIframe(ed, tbId, -28);
			editor.settings.wordpress_adv_hidden = 0;
			// setUserSetting('hidetb', '1');

			// editor.settings.plugins += ',paste';
			// editor.settings.paste_auto_cleanup_on_paste = true;
			// var old_paste_preprocess = editor.settings.paste_preprocess;
			// editor.settings.paste_preprocess = function(pl, o) {
			// 	old_paste_preprocess && old_paste_preprocess(pl, o);

	            // Content string containing the HTML from the clipboard
	            // console.log(o.content);
	            // o.content = "-: CLEANED :-\n" + o.content;
	        // };
			// var old_paste_postprocess = editor.settings.paste_postprocess;
	        // editor.settings.paste_postprocess = function(pl, o) {
	        // 	old_paste_postprocess && old_paste_postprocess(pl, o);

	        //     // Content DOM node containing the DOM structure of the clipboard
	        //     // console.log(o.node.innerHTML);
	        //     // o.node.innerHTML = o.node.innerHTML + "\n-: CLEANED :-";

	        //     var $node = jQuery(o.node);
	        //     if (jQuery($node.text()).hasClass('cfw-wods')) {
	        //     	$node.html($node.text());
	        //     }
	        // };
		},

		// openKitchenSink: function(editor) {
		// 	if (editor.settings.wordpress_adv_hidden) {
		// 		editor.execCommand('WP_Adv');
		// 	}
		// },

		createControl: function(name, controlManager) {
			if (name != "cfwhiteboard_button") return null;

			var button = controlManager.createButton( "cfwhiteboard_button", {
				title: "Insert Workout Description(s)",
				image: icon_url,
				icons: false,
				cmd: ''
			});

			var clickHandler = function () {
				button.$cfwMenu.empty();

				// Gather class names/descriptions
				var classes = [];
				var tempClass;
				jQuery('#cfwhiteboard-wods-meta .cfw-wods li').each(function() {
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

                var footerText = classes.length > 0 ? CFW.newlineToBr('Post your scores to <span class="cfw-link-to-whiteboard" data-post-id="'+CFW.postId+'" style="border-bottom: 1px dotted;">the Whiteboard</span>.\n\n') : '';

				if (!classes.length) {
					// no classes entered, provide link to CFW meta box
					var $li = jQuery('<li><a href="#cfwhiteboard-wods-meta">No workouts. Click to add one.</a></li>');
					$li.on('click', function() {
						var $metaBox = jQuery('#cfwhiteboard-wods-meta');
						if ($metaBox.find('.cfw-main:visible').length == 0) {
							$metaBox.find('.handlediv').click();
						}
					});
					button.$cfwMenu.append($li);
				} else if (classes.length > 1) {
					// multiple classes entered, provide menu item for inserting all at once

					var markupAll = '';
					for (var i = 0; i < classes.length; i++) {
						markupAll += classes[i].markup;
					}
					markupAll += footerText;

					var $li = jQuery('<li><a href="javascript:// Insert into Post">Insert All '+classes.length+' Classes</a></li>');
					$li.on('click', function() {
						tinyMCE.activeEditor.execCommand("mceInsertContent", false, markupAll);
					});
					button.$cfwMenu.append($li);
        			button.$cfwMenu.append('<li class="divider"></li>');
				}

				var generateInsertMarkupFunc = function(markup) {
					return function() {
						tinyMCE.activeEditor.execCommand("mceInsertContent", false, markup);
					};
				};
				for (var i = 0; i < classes.length; i++) {
					var $li = jQuery('<li><a href="javascript:// Insert into Post">Insert '+classes[i].name+'</a></li>');
					$li.on('click', generateInsertMarkupFunc( classes[i].markup + footerText ));
					button.$cfwMenu.append($li);
				}
			};

			button.postRender = function() {
				this.parent();

				this.$cfwButton = jQuery('#'+this.id);

				this.$cfwButton.on('click', clickHandler).width(88).css('position','relative').css('outline','none');
				this.$cfwButton.find('img').remove();
				this.$cfwButton.append('<span style="position:absolute; top:0; right:0; bottom:0; left:0; background: url('+ icon_url +') no-repeat 1px center transparent;"></span>');

                this.$cfwButton.attr('data-toggle','bsdropdown');
                this.$cfwButton.attr('data-target','.cfw-dummy-selector');
				this.$cfwButton.wrap('<div class="dropdown" style="display:inline-block; vertical-align:top; *zoom:1; *display:inline;"></div>');
				this.$cfwButton.closest('.dropdown').wrap('<span class="cfw-twb"></span>');
				this.$cfwButton.after('<ul class="dropdown-menu"></ul>');
				this.$cfwMenu = this.$cfwButton.closest('.dropdown').find('.dropdown-menu');
				var button = this;
				this.$cfwMenu.on('click', function() {
					button.$cfwButton.bootstrap_dropdown('toggle');
				});
			};

			return button;
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

	tinymce.PluginManager.add("cfwhiteboard", tinymce.plugins.cfwhiteboard);
})();
