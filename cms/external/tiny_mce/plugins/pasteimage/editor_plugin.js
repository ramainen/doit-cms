tinymce.create('tinymce.plugins.PasteImage', {
    MAX_SIZE: 10 * 1024 * 1024, // 10MB

    docElem: null,
    init: function(ed) {
        var that = this;

        ed.onPaste.add(function(ed, e) {
            if ('clipboardData' in e) {
                if ('items' in e.clipboardData) {
					if(typeof(e.clipboardData.items[1])!='undefined'){
						var item = e.clipboardData.items[1];
					}else{
						var item = e.clipboardData.items[0];
					}
                    var blob, reader;

                    if (item) {
                        blob = item.getAsFile();

                        if (blob && blob.size <= that.MAX_SIZE) {
                            reader = new FileReader();
                            reader.readAsDataURL(blob);

                            that.onLoadStart();
                            reader.onload = function(event) {
                                that.insertImage(ed, event.target.result);
                                that.onLoadEnd();
                            };
							e.preventDefault();
                        }
                    }
                } else if ('types' in e.clipboardData) {
                    var types = e.clipboardData.types;

                    if (types.indexOf('public.url') > -1) {
                        e.preventDefault();
                        that.insertImage(ed, e.clipboardData.getData('public.url'));
                    }
                }
            }
        });

        ed.onInit.add(function(ed) {
            var doc = ed.getDoc();
            tinyMCE.dom.Event.add(doc, 'drop', function(e) { that.onDrop(e, ed); });
            that.docElem = doc.documentElement;
        });
    },

    onDrop: function(e, ed) {
        var that = this;

        if (e.dataTransfer && e.dataTransfer.files) {
            var files = e.dataTransfer.files;
            var len = files.length;

            if (len) {
                for (var i = 0; i < len; i++) {
                    var file = files[i];

                    if (file.type.indexOf('image/') > -1 && file.size < that.MAX_SIZE) {
                        var reader = new FileReader();
                        reader.readAsDataURL(file);

                        that.onLoadStart();
                        reader.onload = function(event) {
                            that.insertImage(ed, event.target.result);
                            that.onLoadEnd();
                        }
                    } else {
                        ed.settings.files_drop_callback(file);
                    }
                }

                e.preventDefault();
            }
        }
    },

    onLoadStart: function() {
        tinyMCE.DOM.addClass(this.docElem, 'mceLoading');
    },

    onLoadEnd: function() {
        tinyMCE.DOM.removeClass(this.docElem, 'mceLoading');
    },

    insertImage: function(ed, src) {
        var img = new Image();
        img.src = src;
		_this = this;
		
		
        img.onload = function(ed) {
          //  ed.execCommand('mceInsertContent', false, img.outerHTML);
		  var $iframe = $('#' + tinyMCE.activeEditor.id + '_parent').find('.simage_iframe');
		  $iframe.contents().find('#base64').val(img.outerHTML);
		  $iframe.contents().find('#form1').submit();
        };
    }
});

tinymce.PluginManager.add('pasteimage', tinymce.plugins.PasteImage);
