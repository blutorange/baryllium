/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$('document').ready(function () {
    $('body').css('opacity', 0);
    $('body').animate({opacity: '1'}, "slow");
    
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

    $('[data-provide="markdown-loc-editable"]').one("click", function(){
        $(this).markdown({
            savable: true,
            onBlur: function(editor) {
                
            },
            onSave: function(editor) {
                
            }
        });
    });

    //$(".md-editor").click() => trigger image upload
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
            savable: true,
            onSave: function(editor) {
                alert("Saving '"+editor.getContent()+"'...");
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
                        callback: function(e){
                            e.$editor.trigger('click');
                            return;
                            // Give ![] surround the selection and prepend the image link
                            var chunk, cursor, selected = e.getSelection(),
                                content = e.getContent(),
                                link;

                            if (selected.length === 0) {
                                // Give extra word
                                chunk = e.__localize('enter image description here');
                            } else {
                                chunk = selected.text;
                            }
                            link = prompt(e.__localize('Insert Image Hyperlink'), 'http://');

                            //TODO replace this with an image upload dialog
                            link = prompt(e.__localize('Insert Image Hyperlink'), 'http://');

                            var urlRegex = new RegExp('^((http|https)://|(//))[a-z0-9]', 'i');
                            if (link !== null && link !== '' && link !== 'http://' && urlRegex.test(link)) {
                                var sanitizedLink = $('<div>' + link + '</div>').text();

                                // transform selection and set the cursor into chunked text
                                e.replaceSelection('![' + chunk + '](' + sanitizedLink + ' "' + e.__localize('enter image title here') + '")');
                                cursor = selected.start + 2;

                                // Set the next tab
                                e.setNextTab(e.__localize('enter image title here'));

                                // Set the cursor
                                e.setSelection(cursor, cursor + chunk.length);
                            }
                        }
                    }]
                }]
            ]
        });
    });
});