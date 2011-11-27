/**
 * @author Fakhrutdinov Damir (aka Ainu) http://doit-cms.ru/
 * @copyright Copyright © 2011, Fakhrutdinov Damir (aka Ainu), All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.sImagesPlugin', {
		init : function(ed, url) {
			ed.addCommand('mcesImages', function() {});	
			ed.addButton('simages', {
				title : 'Загрузка изображений',
				cmd : 'mcesImages',
				image : url + '/upl/icon.gif" style="display:none;"><iframe style="overflow:hidden;height:20px;width:20px;border:none;border:0;"  frameborder="0" border="0" src="'+url+'/upl/index.php?'+ed.id+'"></iframe><img style="display:none;" src="/cms/external/pixel.gif'

		
			});
		},

		getInfo : function() {
			return {
				longname : 'SImages Manager',
				author : 'Fakhrutdinov Damir',
				authorurl : 'http://doit-cms.ru/',
				infourl : 'http://doit-cms.ru/',
				version : '0.1'
			};
		}
	});

tinymce.PluginManager.add('simages', tinymce.plugins.sImagesPlugin);
editorsk++;

})();
var edlink;
var editorsk=0;
