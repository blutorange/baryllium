/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function($, window, Moose, undefined){   
    Moose.Util  = (function() {
        function processServletError(error) {
            var message = (error || {}).message || 'Unhandled error';
            var details = (error || {}).details || 'Failed to save post, please try again later.';
            alert(message + ": " + details);
        }
        
        function ajaxServlet(url, method, data, callback, showLoader) {
            showLoader = arguments.length <= 4 || showLoader;
            if (showLoader) {
                $.LoadingOverlay('show', Moose.Environment.loadingOverlayOptions);
            }
            window.setTimeout(function(){
                $.ajax(url, {
                    async: true,
                    cache: false,
                    method: method,
                    dataType: 'json',
                    data: data
                }).done(function (data, textStatus, jqXHR) {
                    var error = data.error;
                    if (error) {
                        processServletError(error);
                    } else {
                        callback(data);
                    }
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR.responseJSON);
                    if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                        processServletError(jqXHR.responseJSON.error);
                    }
                    else {
                        alert("Could not process request due to an internal error (" + textStatus + "): " + errorThrown);
                    }
                }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
                    if (showLoader) {
                        $.LoadingOverlay('hide');
                    }
                }); 
            }, $.isNumeric(showLoader) ? Math.max(0, showLoader) : 0);    
        }
        
        return {
            ajaxServlet: ajaxServlet
        };
        
    })();
})(jQuery, window, window.Moose);