(function(){
 
tinymce.create('tinymce.plugins.mymodulesPlugin',{init:function(ed,url){ed.addCommand('mymodules',function(){ed.windowManager.open({file:url+'/dialog.php',width:320,height:120 ,inline:1},{plugin_url:url,some_custom_arg:'custom arg'});});ed.addButton('mymodules',{title:'Modules',cmd:'mymodules',image:url+'/img/example.gif'});ed.onNodeChange.add(function(ed,cm,n){cm.setActive('mymodules',n.nodeName=='IMG'); });},createControl:function(n,cm){return null;},getInfo:function(){return{longname:'My modules',author:'Some author',authorurl:'http://ramainen.ru/',infourl:'http://cait.ru/',version:"1.0"};}});tinymce.PluginManager.add('mymodules',tinymce.plugins.mymodulesPlugin);})();