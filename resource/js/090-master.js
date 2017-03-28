/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$('document').ready(function () {
    $('body').css('opacity', 0);
    $('body').animate({opacity: '1'}, "slow");
    
    // Setup parsley for forms.
    window.parsley.setLocale(window.moose.locale);
    $('[data-bootstrap-parsley]').parsley({
        successClass: 'has-success',
        errorClass: 'has-error',
        classHandler: function(field) {
            return field.$element.closest('.form-group');
        },
        errorsWrapper: '<ul class=\"help-block\"></ul>',
        errorElem: '<li></li>'
    });
    
    // Enable infinite scroll
    var img = document.createElement('img');
    img.alt = 'Loading';
    img.src = window.moose.loadingGif;
    $('.jscroll-body').jscroll({
        loadingHtml: img.outerHTML,
        padding: 20,
        nextSelector: '.jscroll-next:last a',
        contentSelector: '.jscroll-content',
        pagingSelector: '.jscroll-paging',
        callback: function(){
            var me = $(this);
            var destroy = me.find(".jscroll-destroy");
            if (destroy.length > 0) {
                destroy.closest('.jscroll-paging').hide();
            }
        }
    });

    // Enable inline editing of posts.
    var markdownEditing = false;
    $('[data-provide="markdown-loc-editable"]').on("click", function(){
        if ($.LoadingOverlay("active") || markdownEditing) return;
        var me = $(this);
        var oldContent = null;
        var updateUrl = me.data('updateurl');
        var old = me.clone(true, false).empty();
        var blurs = 0;
        var onSave = function(editor) {
            var content = editor.parseContent();
            if (oldContent === null || oldContent == editor.getContent()) {
                old.append(content);
                editor.$editor.replaceWith(old);
                markdownEditing = false;
                return;
            }
            $.LoadingOverlay('show', window.moose.loadingOverlayOptions);
            $.ajax(updateUrl, {
                async: true,
                cache: false,
                method: 'PATCH',
                dataType: 'json',
                data: {
                    content: content
                },
            }).done(function(data, textStatus, jqXHR) {
                var error = data.error;
                if (error) {
                    var message = (error||{}).message || 'Unhandled error';
                    var details = (error||{}).details || 'Failed to save post, please try again later.';                    
                    alert(message + ": " + details);
                }
                else {
                    old.append(content);
                    editor.$editor.replaceWith(old);
                    markdownEditing = false;
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("Could not save post (" + textStatus + "): " + errorThrown);
            }).always(function(dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
                $.LoadingOverlay('hide');
            });
        }
        markdownEditing = true;
        $(this).markdown({
            savable: true,
            onSave: onSave,
            onShow: function(editor) {
                if (oldContent === null) {
                    oldContent = editor.getContent();
                }
            },
            onBlur: function(editor) {
                blurs++;
                if (blurs > 1) {
                    onSave(editor);
                }
            }
        });
    });

    // Setup markdown editor (for posts etc.)
    $('[data-provide="markdown-loc"]').each(function(){
        //console.log(e.parseContent());    
        var input = $(document.getElementById(this.id + "-hidden"));
        $(this).markdown({
            language: window.moose.locale,
            onBlur: function(e) {
                input.val(e.parseContent());
            },
            dropZoneOptions: {
                url: "./forum.php",
                paramName: "file", // The name that will be used to transfer the file
                maxFilesize: 2, // MB
                thumbnailHeight: 32,
                previewTemplate: '<div class="dropzone"><div class="dz-preview dz-file-preview"><div class="dz-details"><div class="dz-filename"><span data-dz-name></span></div><div class="dz-size" data-dz-size></div><img data-dz-thumbnail/></div>  <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>  <div class="dz-success-mark"><span>✔</span></div>  <div class="dz-error-mark"><span>✘</span></div><div class="dz-error-message"><span data-dz-errormessage></span></div></div></div>',
                accept: function(file, done) {
                  if (file.name == "justinbieber.jpg") {
                    done("Naha, you don't.");
                  }
                  else { done(); }
                }
            },
            //hiddenButtons: ['cmdImage'],
            additionalButtons: [
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
                        callback: function(editor){
                            editor.$editor.trigger('click');
                        }
                    }]
                }]
            ]
        });
    });
});
