/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function($, window, Moose, undefined){
    /**
     * Module for interacting with forms, validating input etc.
     */
    Moose.Forms = (function(){
        function setupForm(form) {
            $(form).parsley({
                successClass: 'has-success',
                errorClass: 'has-error',
                classHandler: function (field) {
                    return field.$element.closest('.form-group');
                },
                errorsWrapper: '<ul class=\"help-block\"></ul>',
                errorElem: '<li></li>'
            });
        }
        
        /**
         * Shows a loading animation and delays submission of the form
         * for the given amount of time.
         * @param {DOMElement|jQuery} form Form to be delayed.
         * @param {number} delay Delay in milliseconds.
         */
        function delayFormSubmit(form, delay) {
            delay = delay || 400;
            $('form').one('submit', function(event) {
                var $this = $(this);
                event.preventDefault();
                $.LoadingOverlay('show', Moose.Environment.loadingOverlayOptions);
                setTimeout(function() {
                    $this.submit();
               }, delay < 100 ? 100 : delay);
            });
        }
        
        function onDocumentReady() {
            window.parsley.setLocale(Moose.Environment.locale);
            $('[data-bootstrap-parsley]').eachValue(setupForm);
            $('form').eachValue(delayFormSubmit);
        }

        return {
            onDocumentReady: onDocumentReady,
            setupForm: setupForm
        };
    })();    
})(jQuery, window, window.Moose);