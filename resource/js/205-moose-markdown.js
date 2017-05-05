/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// The markdown editor library we use is bootstrap-markdown.
// See http://www.codingdrama.com/bootstrap-markdown/ for details.
// This module takes care of configuring the markdown editor for our
// needs, eg. enabling image upload and inline editing and saving.

window.Moose.Factory.Markdown = function(window, Moose, undefined) {
    var $ = Moose.Library.jQuery;

    // For inline editing via the markdown editor:
    // A lock so that only one element is converted to a markdown editor
    // for editing at any time.
    var markdownEditLock = false;

    // Additional buttons to add to the markdown editor toolbar.
    // Name may be the name of an existing group or the name
    // of a custom new group. Buttons in the same group are
    // grouped together visually.
    var additionalButtons =  [
        [{
            name: "groupLink",
            data: [{
                name: "cmdCustomImage",
                toggle: false,
                title: "Insert image",
                icon: {
                    glyph: 'glyphicon glyphicon-upload',
                    fa: 'fa fa-picture-o',
                    'fa-3': 'icon-picture',
                    octicons: 'octicon octicon-file-media'
                },
                callback: function (editor) {
                    editor.$editor.trigger('click');
                }
            }]
        }]
    ];

    var dropZoneOptions = {
        uploadMultiple: true,
        paramName: 'documents',
        method: 'POST',
        maxFiles: 10,
        addRemoveLinks: true,
        maxFilesize: 10, // MB
        thumbnailHeight: 32,
        previewTemplate: "<div class=\"dz-preview dz-file-preview\">\n  <div class=\"dz-image\"><img data-dz-thumbnail /></div>\n  <div class=\"dz-details\">\n    <div class=\"dz-size\"><span data-dz-size></span></div>\n    <div class=\"dz-filename\"><span data-dz-name></span></div>\n  </div>\n  <div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>\n  <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>\n  <div class=\"dz-success-mark\">\n    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">\n      <title>Check</title>\n      <defs></defs>\n      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">\n        <path d=\"M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" stroke-opacity=\"0.198794158\" stroke=\"#747474\" fill-opacity=\"0.816519475\" fill=\"#FFFFFF\" sketch:type=\"MSShapeGroup\"></path>\n      </g>\n    </svg>\n  </div>\n  <div class=\"dz-error-mark\">\n    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">\n      <title>Error</title>\n      <defs></defs>\n      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">\n        <g id=\"Check-+-Oval-2\" sketch:type=\"MSLayerGroup\" stroke=\"#747474\" stroke-opacity=\"0.198794158\" fill=\"#FFFFFF\" fill-opacity=\"0.816519475\">\n          <path d=\"M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" sketch:type=\"MSShapeGroup\"></path>\n        </g>\n      </g>\n    </svg>\n  </div>\n</div>",
        acceptedFiles: 'image/*',
        init: function() {
            var markdown = $('.md-input', this.element).data('markdown');
            var caretPos = 0;
            this.on('drop', function(e) {
                caretPos = markdown.$textarea.prop('selectionStart');
            });
            this.on('successmultiple', function(file, response) {
                var data = typeof(response)==="string" ? $.parseJSON(response) : response;
                $.each(data, function(index, link){
                    file[index].deleteUrl = link;
                    var text = markdown.$textarea.val();
                    markdown.$textarea.val(text.substring(0, caretPos) + '\n![description](' + link + '&tmb=true)\n' + text.substring(caretPos));    
                });
                var $input = $(window.document.getElementById(markdown.$textarea[0].id + "-hidden"));
                $input.val(markdown.parseContent());
            });
            this.on('removedfile', function(file) {
                $.ajax(file.deleteUrl, {
                    async: true,
                    cache: false,
                    method: 'DELETE',
                    dataType: 'json'
                }).done(function (data, textStatus, jqXHR) {

                });
            });
            this.on('error', function(file, error, xhr) {
                if (!xhr) {
                    alert(error);
                    return;
                }
                try {
                    var data = $.parseJSON(xhr.responseText);
                    alert(data.error.message + ": " + data.error.details);
                }
                catch (e) {
                    console.error(xhr.responseText, e);
                    alert("Could not upload image. Please try again later.");
                }
            });
        }
    };

    var markdownEditorCommonOptions = {
        language: Moose.Environment.locale,
        dropZoneOptions: $.extend(dropZoneOptions, $.fn.dropzone.messages),
        additionalButtons: additionalButtons
    };

    /**
     * Converts a textarea to a markdown editor, with the additional options
     * defined above. For image uploads it uses the value of the
     * <code>data-imageposturl</code> as the url to POST the image to. 
     * Also, it stores markdown rendered as HTML in an input field with
     * the ID <code>textareaID-hidden</code>, if such an input element
     * exists. For example, when the textara has got the idea 'myEditor',
     * this function looks for an input field with the ID 'myEditor-hidden'.
     * @param {DOMElement|jQuery} textarea Textarea to convert to a markdown
     * editor.
     */
    function initTextareaToMarkdown(textarea) {
        var $me = $(textarea);
        var imagePostUrl = $me.data('imageposturl');
        var $input = $(window.document.getElementById(this.id + "-hidden"));
        // Make a copy of the options so it doesn't interfere with other
        // editors.
        var options = $.extend(true, {
            onBlur: function (editor) {
                $input.val(editor.parseContent());
            }
        }, markdownEditorCommonOptions);
        $.extend(options.dropZoneOptions, {
            url: imagePostUrl
        });
        $me.parent().addClass('dropzone');
        $me.markdown(options);            
    }

    // Enable inline editing of posts.
    /**
     * Makes a DOM element editable by replacing it with a markdown editor.
     * The following attributes may be specified:
     * <ul>
     *   <li>data-imageposturl: URL used for image uploads.</li>
     *   <li>data-updateurl: URL to which the new content is sent as a PATCH request when saving the changes.</li>
     *   <li>data-update: When specified, searches for the closest parent element matching this selector and replaces it with the data returned by PATCH request.</li>
     *   <li>data-editable: When specified, searches for some child element matching this selector and takes it as the intial content of the markdown editor.</li>
     * </ul>
     * @param {DOMElement|jQuery} editableBlock Block to convert.
     */
    function initInlineMarkdownEditor(editableBlock) {
        if ($.LoadingOverlay("active") || markdownEditLock)
            return;
        var me = $(editableBlock);
        var updateUrl = me.data('updateurl');
        var updateSelector = me.data('update');
        var editable = me.data('editable') ? me.find(me.data('editable')) : me;
        var postUrl = me.data('imageposturl');
        var oldContent = null;
        var asHtml = !!updateSelector;
        var old = editable.clone(true, false).empty();
        var blurs = 0;
        var onSave = function (editor) {
            var content = editor.parseContent();
            // Check whether anything changed at all.
            // If there were no changes, simply remove the markdown editor
            // and we are done.
            if (oldContent === null || oldContent === editor.getContent()) {
                old.append(content);
                editor.$editor.replaceWith(old);
                markdownEditLock = false;
                return;
            }
            // Show loading overlay and send a PATCH request to the post
            // update servlet, requesting a change to the post's content.
            // This may fail either due to missing permissions, a database
            // error, or an internal script of server error.
            // When succesful, we expect the servlet to return the updated
            // HTML of the post. We then proceed and replace the HTML with
            // the new one.
            var data = {
                    content: content,
                    returnhtml: asHtml
            };
            Moose.Util.ajaxServlet(updateUrl, 'PATCH', data, function (data) {
                if (asHtml) {
                    editor.$editor.closest(updateSelector).replaceWith(data.html);
                }
                else {
                    old.append(content);
                    editor.$editor.replaceWith(data.content);
                }
                markdownEditLock = false;
            });
        };
        var options = $.extend(true, markdownEditorCommonOptions, {
            savable: true,
            onSave: onSave,
            onShow: function (editor) {
                if (oldContent === null) {
                    oldContent = editor.getContent();
                }
            },
            onBlur: function (editor) {
                blurs++;
                if (blurs > 1) {
                    onSave(editor);
                }
            }                
        });
        $.extend(options.dropZoneOptions, {
            url: postUrl
        });
        markdownEditLock = true;
        $(this).addClass('dropzone');
        editable.markdown(options);
        // Scroll editor into view.
        var offset = (updateSelector ? me.closest(updateSelector) : me).offset();
        $('html, body').animate({
            scrollTop: offset.top - 20,
            scrollLeft: offset.left
        });            
    }
    
    function onNewElement(context) {
        $('[data-provide="markdown-loc"]', context).eachValue(initTextareaToMarkdown);
    }

    function onDocumentReady() {
        onNewElement(window.document);
        $('body').on('click', '[data-provide="markdown-loc-editable"]', function(){
            initInlineMarkdownEditor(this);
        });
    }

    return {
        onNewElement: onNewElement,
        onDocumentReady: onDocumentReady,
        initInlineMarkdownEditor: initInlineMarkdownEditor
    };
};