/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.Moose.Factory.Util = function(window, Moose, undefined){
    var $ = Moose.Library.jQuery;
    
    function processServletError(error) {
        console.error(error);
        var message = (error || {}).message || 'Unhandled error';
        var details = (error || {}).details || 'Failed to save post, please try again later.';
        alert(message + ": " + details);
    }

    /**
     * @param {string} url
     * @param {string} method HTTP method, eg. POST or GET.
     * @param {object} data Data to send as query parameter or form data.
     * @param {function} callback Called with the retrieved JSON data as the first argument on success.
     * @param {boolean} showLoader Whether the loading overlay should be displayed.
     * @returns {undefined}
     */
    function ajaxServlet(url, method, data, callback, showLoader, asJson) {
        showLoader = arguments.length <= 4 || showLoader;
        if (showLoader) {
            $.LoadingOverlay('show', Moose.Environment.loadingOverlayOptions);
        }
        window.setTimeout(function(){
            $.ajax(url, {
                async: true,
                cache: false,
                method: method,
                contentType: asJson ? 'application/json; charset=UTF-8' : 'application/x-www-form-urlencoded; charset=UTF-8',
                dataType: 'json',
                data: asJson ? JSON.stringify(data) : data
            }).done(function (data, textStatus, jqXHR) {
                var error = data.error;
                if (error) {
                    console.error(jqXHR.responseJSON);
                    processServletError(error);
                } else {
                    callback && callback(data);
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                console.error(jqXHR);
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
};