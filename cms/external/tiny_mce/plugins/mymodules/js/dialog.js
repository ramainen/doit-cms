 

var mymodulesDialog = {
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
		f.someval.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
	 
	},

	insert : function() {
		// Insert the contents from the input into the document
		a=new Date();
		
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, '<img src="/cms/external/tiny_mce/plugins/mymodules/module.php?'+document.forms[0].someval.value+'=plugin'+a.getTime()+'" />');
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(mymodulesDialog.init, mymodulesDialog);
