/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function($, window, Moose, undefined){       
    Moose.Navigation = (function(){
        
        var dataDialog = {};
        
        var callbackActionButton = {
            btnDeletePost: function(data, $button) {
                Moose.Util.ajaxServlet(Moose.Environment.paths.postServlet, 'DELETE', dataDialog.dialog_delete_post, function(data){
                    $(document.getElementById('dialog_delete_post')).modal('hide');
                    window.location.reload();
                }, 400);
            },
            btnDeleteThread: function(data, $button) {
                callback = function(data){
                    window.location = dataDialog.dialog_delete_thread.redirect;
                };
                data = {
                    entity: {
                        fields: {
                            id: (dataDialog.dialog_delete_thread||{}).tid
                        }
                    }
                };
                Moose.Util.ajaxServlet(Moose.Environment.paths.threadServlet, 'DELETE', JSON.stringify(data), callback, 400);
            },
            btnOpenDialog: function(data, $button) {
                var idSelector = String($button.data('target'));
                if (idSelector.charAt(0) === '#') {
                    var id = idSelector.substr(1);
                    dataDialog[id] = data;
                }
            },
            btnMarkdownEdit: function(data, $button) {
                var $editable = $button.closest(data.selectorTop).find(data.selectorDown).first();
                window.Moose.Markdown.initInlineMarkdownEditor($editable);
            },
            btnUploadAvatar: function(data, $button) {
                $('#user_profile_form #avatar_upload').one('change', function(){
                    if (this.files.length > 0) {
                        $('#user_profile_form').submit();
                    }
                }).click();
            }
        };
        
        function onClickActionButton() {
            var $button = $(this);
            var id = $button.data('btn-callback-id');
            var callback = callbackActionButton[id];
            if (callback) {
                var data = $button.data('btn-callback-json')
                if (typeof data === 'string')
                    console.error("Invalid callback data given", json);
                else
                    callback(data, $button);
            }
            else {
                console.error("Callback missing for button", id);
            }
        }
        
        /**
         * Initializes infinite scrolling for the given element. The following
         * classes must be set:
         * <ul>
         *   <li>jscroll-body: The parent element which contains paginable content.</li>
         *   <li>jscroll-next: Element containing the link (a element) to the
         *   next page. When more elements are found, the last one is taken.
         *   </li>
         *   <li>jscroll-paging: Selector for the list of pages.</li>
         *   <li>jscroll-content: For filtering what parts of the dynamically
         *   loaded content are to be displayed.</li>
         * </ul>
         * @param {DOMElement|jQuery} element
         */
        function initJScroll(element) {
            var img = document.createElement('img');
            img.alt = 'Loading';
            img.src = Moose.Environment.loadingGif;
            var jscrollOptions = {
                loadingHtml: img.outerHTML,
                padding: 20,
                nextSelector: '.jscroll-next:last a',
                contentSelector: '.jscroll-content',
                pagingSelector: '.jscroll-paging',
                loadingDelay: 1000,
                callback: function () {
                    var $me = $(this);
                    var $destroy = $me.find(".jscroll-destroy");
                    if ($destroy.length > 0) {
                        $destroy.closest('.jscroll-paging').hide();
                    }
//                    $(element).find('.jscroll-content').append($me.find('.jscroll-content').children());
                }
            };
            $(element).jscroll(jscrollOptions);
        }
        
        function onDocumentReady() {
            //$('.btn-callback').eachValue(bindToActionButton);
            $('body').on('click', '.btn-callback', onClickActionButton);
//            $('[data-toggle="popover"]').popover();
//            $('[data-toggle="tooltip"]').tooltip();
            if (!Moose.Persistence.getClientConfiguration('fields', 'option.paging.list', false)) {
                $('.jscroll-body').eachValue(initJScroll);
            }
        }
        
        return {
            onDocumentReady: onDocumentReady
        };
    })();    
})(jQuery, window, window.Moose);