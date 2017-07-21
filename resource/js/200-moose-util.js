/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.Moose.Factory.Util = function(window, Moose, undefined){
    var $ = Moose.Library.jQuery;
    
    function processServletError(data, options, jqXHR) {
        if (!data.error) {
            data.error = {
                message: 'Unknown error',
                details: 'No details are available'
            };
        }
        var clazz = data.error.class || 'general';
        // If the user needs to login, allow them to do so.
        if (clazz === 'access-denied') {
            options.onAccessDenied && options.onAccessDenied(data.error, data, options, jqXHR);
        }
        else {
            options.onFailure && options.onFailure(data.error, data, options, jqXHR);
        }
    }
    
    function ajaxAppendMessages(url, data, method) {
        $.ajax(url, {
            data: data,
            method: method,
            ajax: true,
            dataType: 'html'
        })
        .done(function(html) {
            $('.moose-messages').append($(html).find('.moose-messages .alert'));
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            $msg = $('<div class="alert alert-danger"><ul><li><span class="glyphicon glyphicon-danger-sign" aria-hidden="true"></span><strong class="msg-short"></strong><span class="msg-details"></span></li></ul></div>');
            $msg.find('.msg-details').text(jqXHR.status + ' ' + jqXHR.statusText);
            $msg.find(".msg-short").text(textStatus);
            $('.moose-messages').append($msg);
        })
        .always(function(){
            $.LoadingOverlay('hide', Moose.Environment.loadingOverlayOptions);
            window.scrollTo(0,0);
        });
    }

    
    function ajaxOnAccessDenied(error, data, ajaxOptions) {
        var $loginDialog = $('#login_dialog');
        if ($loginDialog.length === 1) {
            // Clear the login form
            $studentId = $loginDialog.find('#studentid');
            $remember = $loginDialog.find('#login\\.remember');
            $studentId.val($studentId.attr('value'));
            $remember.prop('checked', $remember[0].hasAttribute('checked'));
            $loginDialog.find('#password').val('');
            // Hide login failure message
             $loginDialog.find('.dialog-login-failure').addClass('hidden');
            // Remove parsley messages
            $loginDialog.find('.bootstrap-parsley').data('Parsley').reset();
            // Remember the current request.
            Moose.Navigation.setDialogData($loginDialog, ajaxOptions);
            // Open the login form.
            $loginDialog.modal('show');
        }
    }
    
    function ajaxOnFailure(error, data, ajaxOptions) {
        console.error("AJAX failed", data);
        var message = error.message || 'Unhandled error';
        var details = error.details || 'Failed to save post, please try again later.';
        alert(message + ": " + details);
    }
    
    function ajaxOnAuthorized(options) {
        // Send the request again
        ajaxServlet(options);
    }

    /**
     * @param {string} url Where to send the request to. Defaults to
     * <code>window.location.href</code>.
     * @param {string} method HTTP method, eg. POST or GET. Defaults to GET.
     * @param {object} data Data to send as query parameter or form data.
     * @param {function} onSuccess When the request succeeds. Called with the
     * retrieved JSON data and the original ajax options. Defaults to noop.
     * @param {function} onFailure When the request fails. Called with the
     * error, the retrieved data, and the original ajax options. Defaults to
     * logging the error and showing an alert.
     * @param {function} onDone Always called.  Called with the original ajax
     * options. Defaults to noop.
     * @param {function} onAccessDenied When access was denied.  Called with the
     * error, the retrieved data, and the original ajax options. Default is that
     * it opens a login dialog and asks the user to login.
     * @param {boolean} showLoader Default true. Whether the loading overlay
     * should be displayed.
     * @param {boolean} asJson Default true. Iff true, sets content type to JSON
     * and stringifies the data. Iff false, sends an application/x-www-form-urlencoded
     * @returns {jQuery.Deferred}
     */
    function ajaxServlet(options) {
        options = $.extend({
            url: window.location.href,
            data: {},
            showLoader: true,
            asJson: true,
            method: 'GET',
            onSuccess: null,
            onLoginCancel: null,
            onFailure: ajaxOnFailure,
            onAccessDenied: ajaxOnAccessDenied,
            onAuthorized: ajaxOnAuthorized,
            onDone: null
        }, options);
        options.method = String(options.method).toUpperCase();
        var noBody = options.method === 'GET' || options.method === 'HEAD';
        if (options.showLoader) {
            $.LoadingOverlay('show', Moose.Environment.loadingOverlayOptions);
        }
        window.setTimeout(function(){
            $.ajax(options.url, {
                async: true,
                cache: false,
                method: options.method,
                contentType: options.asJson && !noBody ? 'application/json; charset=UTF-8' : 'application/x-www-form-urlencoded; charset=UTF-8',
                dataType: 'json',
                data: options.asJson && !noBody ? JSON.stringify(options.data) : options.data
            }).done(function (data, textStatus, jqXHR) {
                data = data || {};
                if (data.error) {
                    processServletError(data, options, jqXHR);
                } else {
                    options.onSuccess && options.onSuccess(data, options, jqXHR);
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                    processServletError(jqXHR.responseJSON, options, jqXHR);
                }
                else {
                    var error = {
                        message: "Could not process request due to an internal error (" + textStatus + "): ",
                        details: String(errorThrown)
                    };
                    options.onFailure && options.onFailure(error, {error: error}, options, jqXHR);
                }
            }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
                if (options.showLoader) {
                    $.LoadingOverlay('hide');
                }
                options.onDone && options.onDone(options, dataOrJqXHR);
            }); 
        }, $.isNumeric(options.showLoader) ? Math.max(0, options.showLoader) : 0);
        return options;
    }

    return {
        ajaxServlet: ajaxServlet,
        ajaxAppendMessages: ajaxAppendMessages
    };
};