 
//jquery paste image plugin include begin

// Created by STRd6
// MIT License
(function($) {
  var defaults;
  $.event.fix = (function(originalFix) {
    return function(event) {
      event = originalFix.apply(this, arguments);
      if (event.type.indexOf('copy') === 0 || event.type.indexOf('paste') === 0) {
        event.clipboardData = event.originalEvent.clipboardData;
      }
      return event;
    };
  })($.event.fix);
  defaults = {
    callback: $.noop,
    matchType: /image.*/
  };
  return $.fn.pasteImageReader = function(options) {
    if (typeof options === "function") {
      options = {
        callback: options
      };
    }
    options = $.extend({}, defaults, options);
    return this.each(function() {
      var $this, element;
      element = this;
      $this = $(this);
      return $this.live('paste', function(event) {
        var clipboardData, found;
        found = false;
        clipboardData = event.clipboardData;
        return Array.prototype.forEach.call(clipboardData.types, function(type, i) {
          var file, reader;
          if (found) {
            return;
          }

          if (type.match(options.matchType) || clipboardData.items[i].type.match(options.matchType)) {
            file = clipboardData.items[i].getAsFile();
            reader = new FileReader();
            reader.onload = function(evt) {
              return options.callback.call(element, {
                dataURL: evt.target.result,
                event: evt,
                file: file,
                name: file.name
              });
            };
            reader.readAsDataURL(file);
            return found = true;
          }
        });
      });
    });
  };
})(jQuery);
//end jquery image paste plugin
//////////////////////////////////


var admin_dropdown_timer;
$(function () {

	$('.closewindow').bind('click', function () {
		window.open('', '_self', '');
		window.close();
	});
	$('.admin_table tr:odd td').css('background', '#E3F9FF');
	// BUTTONS


	// MENUS

	$('.hierarchy').each(function () {
		$(this).menu({
			content:$(this).next().html(), showSpeed:00, crossSpeed:0, flyOut:true, linkHover:false, linkHoverSecondary:false
		})
	});

	
	
	
	
	/*		 	$('.fg-button').hover(
	 function(){ $(this).removeClass('ui-state-default').addClass('ui-state-focus'); },
	 function(){ $(this).removeClass('ui-state-focus').addClass('ui-state-default'); }
	 );
	 */
 

	$('.enable_multiple').bind('click',function(e){
		$('input[name=_enable_multiple]').val(1)
		$('.control-group').hide();
		$('.multiple_mode').show();
		$('.maybe_multiple').attr({'multiple':true})
		$($('.input_elements legend')[0]).html('Добавление нескольких элементов');
		e.preventDefault();
	})
	
	var isCurrentBrowserIE= /*@cc_on!@*/false;
	var forIEscript_url = '/cms/external/tiny_mce/tiny_mce_gzip.php';
	if(isCurrentBrowserIE){
		var forIEscript_url = '/cms/external/tiny_mce/tiny_mce.js';
	}
	$('.admin_button').button();
	$('.tinymce').tinymce({
		script_url:forIEscript_url,
		language:"ru",
		theme:"advanced",
		skin:"o2k7",
		convert_urls:false,
		verify_html:false,
		plugins:"pagebreak,style,table,save,advhr,advimage,advlink,emotions,inlinepopups,preview,media,contextmenu,paste,directionality,fullscreen,noneditable,nonbreaking,xhtmlxtras,simages,importexcel,mymodules,pasteimage",
		theme_advanced_buttons1:"save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect",
		theme_advanced_buttons2:"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,image,simages,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3:"tablecontrols,|,removeformat,visualaid,|,sub,sup,|,charmap,emotions,media,styleprops,|,nonbreaking,pagebreak,importexcel,mymodules",
		theme_advanced_toolbar_location:"top",

		theme_advanced_toolbar_align:"left",
		theme_advanced_statusbar_location:"bottom",
		theme_advanced_resizing:true
	});	
	
	$('.tinymce_non_p').tinymce({
		script_url:forIEscript_url,
		language:"ru",
		theme:"advanced",
		skin:"o2k7",
		convert_urls:false,
		verify_html:false,
		plugins:"pagebreak,style,table,save,advhr,advimage,advlink,emotions,inlinepopups,preview,media,contextmenu,paste,directionality,fullscreen,noneditable,nonbreaking,xhtmlxtras,simages,importexcel,mymodules,pasteimage",
		theme_advanced_buttons1:"save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect",
		theme_advanced_buttons2:"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,image,simages,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3:"tablecontrols,|,removeformat,visualaid,|,sub,sup,|,charmap,emotions,media,styleprops,|,nonbreaking,pagebreak,importexcel,mymodules",
		theme_advanced_toolbar_location:"top",

		theme_advanced_toolbar_align:"left",
		theme_advanced_statusbar_location:"bottom",
		forced_root_block : false,
		theme_advanced_resizing:true
	});

/*

	$.datepicker.regional['ru'] = {
		closeText: 'Закрыть',
		prevText: '&#x3c;Пред',
		nextText: 'След&#x3e;',
		currentText: 'Сегодня',
		monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
		'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
		'Июл','Авг','Сен','Окт','Ноя','Дек'],
		dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
		dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
		dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
		weekHeader: 'Не',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ru']);
*/
	$('.admin_date').datepicker();




if( window.FormData === undefined ){
	//Старый браузер
	$('.hidden_on_old_browsers').hide();
	$('.hidden_on_new_browsers').show();
}else{
	$('.hidden_on_new_browsers').hide();
}




var timeout = -1;
var showDrag=false;
$('body').bind('dragenter', function () {
	$("body").addClass('body_drag')
	$('.drag-n-drop-target').addClass('dragndrop_hover');
    showDrag = true; 
});
$('body').bind('dragover', function(){
    showDrag = true; 
    event.preventDefault();
    return false;
});
$('body').bind('drop', function(e){
    if(!$(e.target).hasClass('fileupload')){
		event.preventDefault();
    }
 //   
	$("body").removeClass('body_drag')
	$('.drag-n-drop-target').removeClass('dragndrop_hover')
    
});
$('body').bind('dragleave', function (e) {
    showDrag = false; 
    clearTimeout( timeout );
    timeout = setTimeout( function(){
    if( !showDrag ){ 
		$("body").removeClass('body_drag')
		$('.drag-n-drop-target').removeClass('dragndrop_hover')
     }
    }, 200 );
});



$(".drag-n-drop-target").each(function(){
	this.ondragover=function()
	{
		$(this).addClass('hover')
	}
	this.ondragleave=function()
	{
		$(this).removeClass('hover')
	}
	/*
	this.ondrop = function(event) {
		//alert(1)
		$('.dragndrop_hover').removeClass('hover').removeClass('dragndrop_hover')
	    $("body").removeClass('body_drag')
		
	    event.preventDefault();
	    

	     var $targetfile =$(this).find('.str_input')
		    if(event.dataTransfer.files[0]){
			    var file = event.dataTransfer.files[0];
				var data = new FormData();
				var btn = $(this).find('.btn')
				var url = $(this).find('.fileupload').data('url')
				btn.button('loading')
	        	
				data.append('Filedata', file);
			 
				    $.ajax({
				        url: url,
				        data: data,
	   					cache: false,
					    contentType: false,
					    processData: false,
					    type: 'POST',
					    success: function(data){
					    	if(data=='error2'){
					    		alert('неверный тип файла');
					    	}else{
					        	$targetfile.val(data)	
					        }
					        btn.button('reset')
					        
					    }
				    });
			  } 



	};

	*/
})
$(".str_input").pasteImageReader(function(results) {
  var dataURL, filename;

		    var $targetfile =$(this)
		    if(results.file){
			    var file = results.file;
				var data = new FormData();
				var btn = $(this).parent().find('.btn')
				var url = $(this).parent().find('.fileupload').data('url')
				btn.button('loading')
	        	
				data.append('Filedata', file, "blob.png");
			 
				    $.ajax({
				        url: url,
				        data: data,
	   					cache: false,
					    contentType: false,
					    processData: false,
					    type: 'POST',
					    success: function(data){
					    	if(data=='error2'){
					    		alert('неверный тип файла');
					    	}else{
					        	$targetfile.val(data)	
					        }
					        btn.button('reset')
					        
					    }
				    });
			  } 


  return filename = results.filename, dataURL = results.dataURL, results;
});
 
 

		$('.fileupload').live('change',function(){
			var multple_current=-1;
			var multple_all=0;
			
			var multiple_files = [];
		    var $targetfile =$(this).parent().parent().find('.str_input') 
			 
		    var file;
			
			var btn = $(this).parent()
			var $this = $(this);
	    	var url = $(this).data('url')
			

			if($(this)[0].files){
				btn.button('loading')
		    	multple_all = $(this)[0].files.length
		     	


			    var process_one_file=function(){
			    	multple_current++;
			    	if ($this[0].files[multple_current]){
			    		
			    		file = $this[0].files[multple_current];
						var data = new FormData();
						data.append('Filedata', file);
					    $.ajax({
					        url: url,
					        data: data,
		   					cache: false,
						    contentType: false,
						    processData: false,
						    type: 'POST',
						    success: function(data){
						    
						        if(data=='error2'){
						    		alert('неверный тип файла');
						    	}else{
						    		multiple_files.push(data)
						    		
						        //		
						        }
								process_one_file()
						       
						        if(multple_current ==multple_all ){
						        	if(multiple_files.length>0){
						        		$targetfile.val(multiple_files.join(';'))
						        	}
						        	btn.button('reset')
						        	$this.replaceWith($this.clone());
					    		}
						        
						        //

						    }
					    });
			    		
			    	}
			    }

	    		process_one_file()

		    	 
			} 
		})
 

	$('.modal-edit-save').bind('click',function(){
			if($('.edit_field_content').val().substr(0,14)!='[admin.fields]'){
				$('.edit_field_content').val("[admin.fields]\n"+$('.edit_field_content').val());
			}
			$('.field_edit_dialog form').submit();
			return false;
	})
	
	
	$('.input_elements input, .input_elements select, .input_elements textarea').on('change',function(){
		rehide_containers();
		
	})
	
	$('.automatic_checkbox_input').each(function(){
	
		if($(this).val()=='1'){
	
			$(this).parent().find('.automatic_checkbox').attr('checked', true)
		
		}else{
			$(this).parent().find('.automatic_checkbox').attr('checked', false)
		}
	})
 
	$('.automatic_checkbox').bind('click',function(){
		if($(this).is(':checked')){
			$(this).parent().parent().find('.automatic_checkbox_input').val('1');
		}else{
			$(this).parent().parent().find('.automatic_checkbox_input').val('0');
		}
		rehide_containers();
	})
 
	$('.dropdown-menu').bind('mouseout',function(e){
	
		if($.contains(this, e.relatedTarget) == false) {
			$('.dropdown.open .dropdown-toggle').dropdown('toggle');
		}
			//alert(e.eventPhase+' '+e.target.tagName)
			//debugger
		
	})
	
	
	function hide_dropdowns(){
		 
	}
	$('.admin_edit_form').on('submit',function(){
	
		$('.automatic_checkbox').each(function(){
			if($(this).is(':checked')){
				$(this).parent().parent().find('.automatic_checkbox_input').val('1');
			}else{
				$(this).parent().parent().find('.automatic_checkbox_input').val('0');
			}
		})
	 
	
	
	
		$('.hiddenable_container').each(function(){
			element = $(this).data('element');
			value = $(this).data('value');
			if($('[name="data['+element+']"]').val() == value){
				$(this).show();
			} else {
				if($(this).data('deletable')==true){
					$(this).remove();
				}
			}
	
		})
	});
	rehide_containers();
	
	
	
	
});
function rehide_containers(){
	var element='';
	var value='';
	$('.hiddenable_container').each(function(){
		element = $(this).data('element');
		value = $(this).data('value');
		if($('[name="data['+element+']"]').val() == value){
			$(this).show();
		} else {
			$(this).hide();
		}
	
	})
}

function create_field_template()
{
	$('.field_template_question').hide();
	$('.edit_field_content').val('[admin.fields]\n'+
	'small url "Адрес" "(необязательно)"\n'+
	'small title "Название"\n'+
	'rich text "Текст"\n'+
	'\n'+
	';date posted_at Дата\n'+
	';userdate date Дата\n'+
	';image image "Изображение" galleries 180 auto\n'+
	';file file  Файл "files"\n'+
	';select razdel Раздел "Оборудование(1)" "Обучение(2)"\n'+
	';checkbox is_active "Активный?"\n'+
	
	'\n'+
	';<тип поля> <имя поля> <название для администратора>\n'+
	
 
	'\n'+
	';[admin.addbuttons]\n'+
	';/list/texts/	Подстраницы\n'+
	';/list/goods/catalog_id/   "Связанные товары"\n'+
	
	
	'\n'+
	';[admin.use_model]\n'+
	';source=d()->Page\n'+
	';sort=yes\n'+
	';list=yes\n'+
	';edit=yes\n'+
	
 
	'\n'+
	';[admin.columns]\n'+
	';page_url=Адрес\n'+
	';title=TITLE\n'+
	';multi_domain = Домен\n'+
 
	'\n'+
	';[admin]\n'+
	';urlredirect=/\n' 
	
	
	)
}
function show_field_editor()
{
	if($('.edit_field_content').val()==''){
	
		$('.field_template_question').show();
	}
	/*$('.field_edit_dialog').modal({height:400,width:600,buttons: {
				
				'Сохранить': function() {
					if($('.edit_field_content').val().substr(0,14)!='[admin.fields]'){
						$('.edit_field_content').val("[admin.fields]\n"+$('.edit_field_content').val());
					}
					$('.field_edit_dialog form').submit();
					$(this).dialog('close');	 
				},
				'Закрыть': function() {
					$(this).dialog('close');
				}
			}});*/
			
			$('.field_edit_dialog').modal()
			
			$('.dropdown.open .dropdown-toggle').dropdown('toggle');
	return false;
}

function window_cancel()
{
	if(window.opener){
		 window.open('','_self','');window.close();
	} else {
		history.history.go(-1);
	}
}