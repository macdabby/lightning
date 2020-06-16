lightning.fileBrowser = {
    type: null,
    field: null,
    elf: null,
    funcNum: null,
    init: function() {
        var query = document.location.search.substring(1).split('&');
        for (var i in query) {
            var split = query[i].split('=');
            if (split[0] == 'type') {
                lightning.fileBrowser.type = split[1];
            }
            if (split[0] == 'field') {
                lightning.fileBrowser.field = split[1];
            }
            if (split[0] == 'CKEditorFuncNum') {
                lightning.fileBrowser.funcNum = split[1];
            }
            if (split[0] == 'url') {
                lightning.fileBrowser.urlType = split[1];
            }
            if (split[0] == 'web_root') {
                lightning.fileBrowser.webRoot = unescape(split[1]);
            }
        }

        // TODO: This has to be changed to handle other browsers.
        lightning.fileBrowser.elf = $('#elfinder').elfinder({
            // set your elFinder options here
            url: '/api/elfinder',  // connector URL
            closeOnEditorCallback: true,
            getFileCallback: lightning.fileBrowser.select,
            height: '100%',
            resizable: false
        }).elfinder('instance');
    },

    select: function(file, urlType) {
        var url = file.url;
        if (lightning.fileBrowser.urlType == 'full') {
            url = (lightning.fileBrowser.webRoot + '/' + url).replace(/([^:])\/\/+/g, '$1/');
        }
        switch (lightning.fileBrowser.type) {
            case 'ckeditor':
                window.opener.CKEDITOR.tools.callFunction(lightning.fileBrowser.funcNum, url);
                window.close();
                break;
            case 'tinymce':
                // pass selected file path to TinyMCE
                parent.tinymce.activeEditor.windowManager.getParams().setUrl(url);

                // force the TinyMCE dialog to refresh and fill in the image dimensions
                var t = parent.tinymce.activeEditor.windowManager.windows[0];
                t.find('#src').fire('change');

                // close popup window
                parent.tinymce.activeEditor.windowManager.close();
                break;
            case 'lightning-field':
                window.opener.lightning.fileBrowser.fieldSelected(url, lightning.fileBrowser.field);
                window.close();
                break;
            case 'lightning-cms':
                window.opener.lightning.cms.imageSelected(url, lightning.fileBrowser.field);
                window.close();
                break;
        }
    },

    /**
     * Open the image browser window for selection.
     *
     * @param {string} type
     *   The browser type to use: elfinder, ckfinder, etc.
     * @param {string} field
     *   The type the type of the parent: ckeditor, tinymce, lightning-field, lightning-cms
     * @param {string} url
     *   The url type to use: full, absolute
     */
    openSelect: function(type, field, url) {
        if (typeof url == 'undefined') {
            url = 'absolute&web_root=' + escape(lightning.vars.web_root);
        }

        // TODO: add case here for other browser types.
        switch (lightning.fileBrowser.type || lightning.vars.fileBrowser.type) {
            case 'elfinder':
                window.open(
                    '/elfinder?type=' + type + '&field=' + field + '&url=' + url,
                    'Image Browser',
                    ''
                );
                break;
            case 'ckfinder':
                CKFinder.popup({
                    basePath: lightning.vars.cms.basepath,
                    chooseFiles: true,
                    chooseFilesOnDblClick: true,
                    onInit: function( finder ) {
                        finder.on( 'files:choose', function( evt ) {
                            var file = evt.data.files.first();
                            self.updateImage(id, file.getUrl());
                        } );
                        finder.on( 'file:choose:resizedImage', function( evt ) {
                            self.updateImage(id, evt.data.resizedUrl);
                        } );
                    }
                });
                break;
        }
    },
    fieldSelected: function(url, field) {
        var imageField = $('#file_browser_image_' + field);
        if (imageField.length > 0) {
            imageField.prop('src', url).show();
        }
        $('#' + field).val(url);
    },
    clear: function(field) {
        var imageField = $('#file_browser_image_' + field);
        if (imageField.length > 0) {
            imageField.prop('src', '').hide();
        }
        $('#' + field).val('');
    }
};
