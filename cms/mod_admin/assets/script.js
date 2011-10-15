        $(function() {
	 
 $('.closewindow').bind('click',function(){
window.open('','_self','');
	window.close();
 });
  $('.admin_table tr:odd td').css('background','#E3F9FF');
	// BUTTONS
     
    	
    	// MENUS    	
	 
		$('.hierarchy').each(function(){ $(this).menu({
			content: $(this).next().html() ,showSpeed: 00 ,crossSpeed:0,flyOut: true, linkHover:false, linkHoverSecondary:false
		})
		});
		
/*		 	$('.fg-button').hover(
    		function(){ $(this).removeClass('ui-state-default').addClass('ui-state-focus'); },
    		function(){ $(this).removeClass('ui-state-focus').addClass('ui-state-default'); }
    	);
	*/	
		 
	 	 	$('.admin-link').hover(
    		function(){ $(this).removeClass('ui-state-default').addClass('ui-state-focus'); },
    		function(){ $(this).removeClass('ui-state-focus').addClass('ui-state-default'); }
    	);
		
		 
		// or from an external source
	 
	
		$('.admin_button').button();
                $('.tinymce').tinymce({
                        script_url : '/cms/external/tiny_mce/tiny_mce_gzip.php',
						language : "ru",
                        theme : "advanced",
						skin : "o2k7",
                        plugins : "pagebreak,style,table,save,advhr,advimage,advlink,emotions,inlinepopups,preview,media,contextmenu,paste,directionality,fullscreen,noneditable,nonbreaking,xhtmlxtras",
                        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect",
                        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                        theme_advanced_buttons3 : "tablecontrols,|,removeformat,visualaid,|,sub,sup,|,charmap,emotions,media,styleprops,|,nonbreaking,pagebreak",
                        theme_advanced_toolbar_location : "top",
 
                       theme_advanced_toolbar_align : "left",
                        theme_advanced_statusbar_location : "bottom",
                        theme_advanced_resizing : true
                });
        });