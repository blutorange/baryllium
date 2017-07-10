window.Moose.Factory.Navigation = function(window, Moose, undefined) {
    "use strict";
    var $ = Moose.Library.jQuery;
    var ajax = Moose.Util.ajaxServlet;
    var paths = Moose.Environment.paths;
    var document = window.document;
    
    var dataDialog = {};

    var callbackCarousel = {
        schedule: {
            slid: function($element) {
                var $schedule = $element.find('.schedule');
                if (!$schedule.data('fullCalendar')) {
                    Moose.Schedule.setupSchedule($schedule);
                }
            }
        }
    };

    var callbackActionButton = {
        
        // ========= Create ===========
        
        
        btnAddDirectory: function(data, $button) {
            alert("not yet implemented");
        },        
        
        // ========= Update ===========
        
        btnUpdatePwcd: function(data, $button) {
            var $element = $(data.selector);
            if (!$element.closest('form').parsley().validate()) return;
            var ajaxData = {
                action: 'changePwcd',
                entity: {
                    fields: {
                        id: data.userId,
                        passwordCampusDual: $element.val()
                    }
                }
            };
            ajax({
                url: paths.userServlet,
                method: 'PATCH',
                data: ajaxData,
                onSuccess: function(responseData) {
                    $element.val('');
                    window.alert(data.msgSuccess);
                }
            });
        },
        
        btnUpdateExam: function(data, $button) {
            if (window.confirm(data.msgConfirm)) {
                var ajaxData = {
                    action: 'update'
                };
                ajax({
                    url: paths.examServlet,
                    method: 'PATCH',
                    data: ajaxData,
                    onSuccess: function(responseData) {
                        window.location.reload();
                    }
                });
            }
        },
        
        btnUpdateDocument: function(data, $button) {
            alert("not yet implemented");
        },
        
        btnUpdateSchedule: function(data, $button) {
            if (window.confirm(data.msgConfirm)) {
                var ajaxData = {
                    action: 'update'
                };
                ajax({
                    url: paths.lessonServlet,
                    method: 'PATCH',
                    data: ajaxData,
                    onSuccess: function(responseData) {
                        $(data.selector).fullCalendar('refetchEvents');
                    }
                });
            }
        }, 
        
        // ========= Deletion =========
        
        btnRemovePwcd: function(data, $button) {
            if (!window.confirm(data.msgConfirm)) return;
            var ajaxData = {
                action: 'removePwcd',
                entity: {
                    fields: {
                        id: data.userId
                    }
                }
            };
            ajax({
                url: paths.userServlet,
                method: 'PATCH',
                data: ajaxData,
                onSuccess: function() {
                    window.alert(data.msgSuccess);
                },
            });
        },
        
        btnDeletePost: function(data, $button) {
            $button.closest('.modal').modal('hide');
            ajax({
                url: paths.postServlet,
                method: 'DELETE',
                data: getDialogData('dialog_delete_post'),
                onSuccess: function(data) {
                    window.location.reload();
                },
                showLoader: 400,
                asJson: false
            });
        },
        
        btnDeleteDocument: function(_, $button) {
            $button.closest('.modal').modal('hide');
            var data = getDialogData('dialog_delete_document');
            var url = paths.documentServlet + '?action=single&did=' + data.id;
            ajax({
                url: url,
                method: 'DELETE',
                onSuccess: function() {
                    var tree = $(document.getElementById(data.fancytree)).fancytree('instance').tree;
                    var node = tree.getNodeByKey(String(data.id));
                    node.parent.setActive();
                    node.remove();
                },
                showLoader: 400
            });
        },
        
        btnDeleteThread: function(_, $button) {
            $button.closest('.modal').modal('hide');
            var onSuccess = function(data){
                window.location.href = (getDialogData('dialog_delete_thread')||{}).redirect;
            };
            var data = {
                action: 'single',
                entity: {
                    fields: {
                        id: (getDialogData('dialog_delete_thread')||{}).tid
                    }
                }
            };
            ajax({
                url: paths.threadServlet,
                method: 'DELETE',
                data: data,
                onSuccess: onSuccess,
                showLoader: 400
            });
        },
        
        // ========== Other ===========
        
        
        btnLoginCloseDialog: function(data, $button) {
            $button.closest('.modal').modal('hide');
            var ajaxOptions = getDialogData('login_dialog');
            if (ajaxOptions && ajaxOptions.onLoginCancel) {
                ajaxOptions.onLoginCancel(ajaxOptions);
            }
        },
        
        btnLoginDialog: function(data, $button) {
            var $parsley = $button.closest('.modal').find('.bootstrap-parsley');
            var parsley = $parsley.data('Parsley');
            if (!parsley || !parsley.validate()) return;
            var studentId = $parsley.find('#studentid').val();
            var password = $parsley.find('#password').val();
            var rememberMe = $parsley.find('#rememberLogin').prop('checked');
            var onLoginFailure = function() {
                $button.closest('.modal').find('.dialog-login-failure').removeClass('hidden');
            };
            var postData = {
                action: 'login',
                entity: {
                    fields: {
                        studentId: studentId,
                        password: password,
                        rememberMe: rememberMe
                    }
                }
            };
            ajax({
                url: paths.userServlet,
                method: 'POST',
                data: postData,
                onSuccess: function() {
                    $button.closest('.modal').modal('hide');
                    var ajaxOptions = getDialogData('login_dialog');
                    ajaxOptions && ajaxOptions.onAuthorized && ajaxOptions.onAuthorized(ajaxOptions);
                },
                onFailure: onLoginFailure,
                onAccessDenied: onLoginFailure,
                showLoader: 400,
                asJson: true
            });
        },
        
        btnOpenDialog: function(data, $button) {
            var idSelector = String($button.data('target'));
            if (idSelector.charAt(0) === '#') {
                var id = idSelector.substr(1);
                setDialogData(id, data);
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
                console.error("Invalid callback data given", data);
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
    
    function setupCarousel(element) {
        $(element).on('slid.bs.carousel', function (event) {
            var callbackId = $(event.relatedTarget).data('callbackId');
            if (callbackCarousel[callbackId] && callbackCarousel[callbackId].slid) {
                callbackCarousel[callbackId].slid($(element));
            }
        });
    }
    
    function onNewElement(context) {
        if (!Moose.Persistence.getClientConfiguration('fields', 'option.paging.list', false)) {
            $('.jscroll-body', context).eachValue(initJScroll);
        }
        // $('[data-toggle="popover"]').popover();
        // $('[data-toggle="tooltip"]').tooltip();        
    }

    function onDocumentReady() {
        //$('.btn-callback').eachValue(bindToActionButton);
        $('body').on('click', '.btn-callback', onClickActionButton);
        $('.carousel').eachValue(setupCarousel)
        onNewElement(window.document);
    }
    
    function setDialogData(buttonId, data) {
        if (typeof(buttonId) !== "string") {
            buttonId = $(buttonId).attr('id');
        }
        if (buttonId) {
            dataDialog[buttonId] = data;
        }
    }
    
    function getDialogData(buttonId) {
        return dataDialog[buttonId];
    }
    
    function setCallbackData(selector, data) {
        $(selector).data('btnCallbackJson', data);
    }

    return {
        onNewElement: onNewElement,
        onDocumentReady: onDocumentReady,
        setCallbackData: setCallbackData,
        setDialogData: setDialogData
    };
};
