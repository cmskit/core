/* jQuery Folder Tree Plugin
* Version 1.00 - released (28 September 2011) Giannis Koutsaftakis
* <htmlab> (http://www.htmlab.gr) Visit http://www.htmlab.gr/blog for more information
* 28 September 2011
* TERMS OF USE
* This plugin is dual-licensed under the GNU General Public License and the MIT License and
* is copyright 2008 A Beautiful Site, LLC. 
*/

(function($) {
	$.fn.folderTree = function(o)
	{
		if( !o ) var o = {};
		if( o.root == undefined ) o.root = 0; //root-ID
		if( o.script == undefined ) o.script = '';
		if( o.loadMessage == undefined ) o.loadMessage = 'Loading...';
		
		// define Dummy-Function
		if( o.statCheck == undefined ) o.statCheck = function(id,stat){alert(2)};
		
		// builds the Tree in a list of html-elemenets
		return this.each(function()
		{
				
				function create_node (script, dir, target, fol)
				{
					if($(fol).hasClass("sel")){
						$(fol).removeClass('folder').addClass('waitb');
					}else{
						$(fol).removeClass('folder').addClass('wait');
					}
					
					$.post(script, { id: dir }, function(data)
					{
						$(fol).removeClass('wait waitb').addClass('folder');
						
						if(dir == o.root){ //if it's the root dir
							target.html(data);
							target.find("ul.jqueryFolderTree").show();
						}else{
							target.append(data);
							target.find("ul.jqueryFolderTree").css({'padding-left':'20px'}).show();
						}
						
						o.statCheck(target);// check childs for their status
					});
				};
				
				// look for Tree-Handler
				$(this).on("click", ".ui-icon-circle-plus", function(e)
				{
					$(this).removeClass("ui-icon-circle-plus").addClass("ui-icon-circle-minus");
					var cur_li = $(this).closest("li");
					var ul_to = cur_li.find("ul.jqueryFolderTree").first();
					if(ul_to.length > 0){
						ul_to.show();
					}else{
						create_node(o.script+'0', $(this).data('id'), cur_li, $(this).next('li span.folder') );
					}
				});
				
				// look for Offset-Buttons
				$(this).on("click", ".foldoffset", function(e)
				{
					var p = $(this).parent('ul');
					$.post(o.script+$(this).data('offset'), { id: $(this).data('pid') }, function(data)
					{
						var p2 = $(data.replace('display:none', 'padding-left:20px'));
						p.replaceWith(p2);
						o.statCheck(p2);
					});
				});

				$(this).on("click", ".ui-icon-circle-minus", function(e)
				{
					$(this).removeClass("ui-icon-circle-minus").addClass("ui-icon-circle-plus");
					var cur_li = $(this).closest("li");
					var ul_to = cur_li.find("ul.jqueryFolderTree").first();
					ul_to.hide();
				});
				
				$(this).on("click", ".folder", function(e){
					$(".folder", $(this).attr('id')).removeClass('sel');
					$(this).addClass('sel');
				});
				
				//load the Root-Tree
				$(this).html('<ul class="jqueryFolderTree"><li class="wait">' + o.loadMessage + '</li></ul>');
				create_node(o.script+'0', o.root, $(this));
		
		});//this each end
	}
})(jQuery);
