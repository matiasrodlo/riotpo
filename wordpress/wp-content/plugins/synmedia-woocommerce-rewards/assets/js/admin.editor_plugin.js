(
	function(){
	
		tinymce.create(
			"tinymce.plugins.SWRShortcodes",
			{
				init: function(d,e) {},
				createControl:function(d,e)
				{
				
					if(d=="swr_shortcodes_button"){
					
						d=e.createMenuButton( "swr_shortcodes_button",{
							title:"Insert Shortcode",
							icons:false
							});
							
							var a=this;d.onRenderMenu.add(function(c,b){
								
								
								a.addImmediate(b,"Cart rewards amount", '[swr_cart_amount]');
								a.addImmediate(b,"Rewards earned amount", '[swr_rewards_amount]');
								a.addImmediate(b,"Product rewards", '[swr_product_rewards]');
								
								
								b.addSeparator();
								
								c=b.addMenu({title:"Pages"});
										a.addImmediate(c,"Rewards","[swr_view_rewards]" );

							});
						return d
					
					} // End IF Statement
					
					return null
				},
		
				addImmediate:function(d,e,a){d.add({title:e,onclick:function(){tinyMCE.activeEditor.execCommand( "mceInsertContent",false,a)}})}
				
			}
		);
		
		tinymce.PluginManager.add( "SWRShortcodes", tinymce.plugins.SWRShortcodes);
	}
)();