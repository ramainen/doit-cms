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
		plugins:"pagebreak,style,table,save,advhr,advimage,advlink,emotions,inlinepopups,preview,media,contextmenu,paste,directionality,fullscreen,noneditable,nonbreaking,xhtmlxtras,simages",
		theme_advanced_buttons1:"save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect",
		theme_advanced_buttons2:"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,image,simages,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3:"tablecontrols,|,removeformat,visualaid,|,sub,sup,|,charmap,emotions,media,styleprops,|,nonbreaking,pagebreak",
		theme_advanced_toolbar_location:"top",

		theme_advanced_toolbar_align:"left",
		theme_advanced_statusbar_location:"bottom",
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

	$('.modal-edit-save').bind('click',function(){
			if($('.edit_field_content').val().substr(0,14)!='[admin.fields]'){
				$('.edit_field_content').val("[admin.fields]\n"+$('.edit_field_content').val());
			}
			$('.field_edit_dialog form').submit();
			return false;
	})
});
function create_field_template()
{
	$('.field_template_question').hide();
	$('.edit_field_content').val('[admin.fields]\n'+
	'small title "Название"\n'+
	'rich text "Текст"\n'+
	'\n'+
	';small url "Адрес страницы (необязательно)"\n'+
	';userdate date Дата\n'+
	';image image "Изображение" galleries 180 auto\n'+
	';file file  Файл "files"\n'+
	';select razdel Раздел "Оборудование(1)" "Обучение(2)"\n'+
	'\n'+
	';<тип поля> <имя поля> <название для администратора>\n'+
	
	'\n'+
	';[admin.addbuttons]\n'+
	';/list/texts/	Подстраницы\n'+
	';/list/goods/catalog_id/   "Связанные товары"')
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
	return false;
}

function window_cancel()
{
	if(window.opener){
		 window.open('','_self','');window.close();
	} else {
		history.back();
	}
}