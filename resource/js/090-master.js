/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function($, window, undefined){
    $(window.document).ready(function () {
        $("body").animate({'opacity': '1'}, 350);

        // Persist client side options set via form fields.
        $('form .persist-client').each(function(){
            var $field = $(this);
            var key = this.id || this.name;
            var initialValue = window.moose.getClientConfiguration('fields', key, undefined);
            if (initialValue !== undefined) {
                window.moose.setElementValue($field, initialValue);
            }
            $field.on("change", function() {
                var value = window.moose.getElementValue($field);
                window.moose.setClientConfiguration('fields', key, value);
            });
        });

        // Setup parsley for forms.
        window.parsley.setLocale(window.moose.locale);
        $('[data-bootstrap-parsley]').parsley({
            successClass: 'has-success',
            errorClass: 'has-error',
            classHandler: function (field) {
                return field.$element.closest('.form-group');
            },
            errorsWrapper: '<ul class=\"help-block\"></ul>',
            errorElem: '<li></li>'
        });

        // Enable infinite scroll
        if (!window.moose.getClientConfiguration('fields', 'option.paging.list', false)) {
            var img = document.createElement('img');
            img.alt = 'Loading';
            img.src = window.moose.loadingGif;
            $('.jscroll-body').jscroll({
                loadingHtml: img.outerHTML,
                padding: 20,
                nextSelector: '.jscroll-next:last a',
                contentSelector: '.jscroll-content',
                pagingSelector: '.jscroll-paging',
                loadingDelay: 1000,
                callback: function () {
                    var me = $(this);
                    var destroy = me.find(".jscroll-destroy");
                    if (destroy.length > 0) {
                        destroy.closest('.jscroll-paging').hide();
                    }
                }
            });
        }
        
        // Enable inline editing of posts.
        $('body').on('click', '[data-provide="markdown-loc-editable"]', function () {
            if ($.LoadingOverlay("active") || window.moose.markdownEditing)
                return;
            var me = $(this);
            var oldContent = null;
            var updateUrl = me.data('updateurl');
            var updateSelector = me.data('update');
            var asHtml = !!updateSelector;
            var old = me.clone(true, false).empty();
            var blurs = 0;
            var onSave = function (editor) {
                var content = editor.parseContent();
                if (oldContent === null || oldContent == editor.getContent()) {
                    old.append(content);
                    editor.$editor.replaceWith(old);
                    window.moose.markdownEditing = false;
                    return;
                }
                $.LoadingOverlay('show', window.moose.loadingOverlayOptions);
                $.ajax(updateUrl, {
                    async: true,
                    cache: false,
                    method: 'PATCH',
                    dataType: 'json',
                    data: {
                        content: content,
                        returnhtml: asHtml
                    },
                }).done(function (data, textStatus, jqXHR) {
                    var error = data.error;
                    if (error) {
                        var message = (error || {}).message || 'Unhandled error';
                        var details = (error || {}).details || 'Failed to save post, please try again later.';
                        alert(message + ": " + details);
                    } else {
                        var newContent = data.content;
                        if (asHtml) {
                            editor.$editor.closest(updateSelector).replaceWith(newContent);
                        }
                        else {
                            old.append(content);
                            editor.$editor.replaceWith(newContent);
                        }
                        window.moose.markdownEditing = false;
                    }
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    alert("Could not save post (" + textStatus + "): " + errorThrown);
                }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
                    $.LoadingOverlay('hide');
                });
            }
            var options = $.extend(window.moose.markdownEditorCommonOptions, {
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
            window.moose.markdownEditing = true;
            $(this).markdown(options);
        });

        // Setup markdown editor (for posts etc.)
        $('[data-provide="markdown-loc"]').each(function () {
            var $me = $(this);
            var postUrl = $me.data('imageposturl');
            var $input = $(document.getElementById(this.id + "-hidden"));
            var options = $.extend(window.moose.markdownEditorCommonOptions, {
                onBlur: function (e) {
                    $input.val(e.parseContent());
                }
            });
            options.dropZoneOptions.url = postUrl;
            $(this).markdown(options);
        });
    });
})(jQuery, window, undefined);