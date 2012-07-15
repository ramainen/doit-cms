 

var importexcelDialog = {
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
		f.someval.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
	 
	},
	clear : function() {

		
		
		tinyMCEPopup.editor.execCommand('mceCleanup');
 
	},
	insert : function(txt) {
		// Insert the contents from the input into the document
		 
		
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, txt);
		tinyMCEPopup.close();
	}
	
};

tinyMCEPopup.onInit.add(importexcelDialog.init, importexcelDialog);
