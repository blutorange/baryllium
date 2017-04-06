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
                errorsContainer: function (el) {
                    return el.$element.closest(".form-group");
                },
                classHandler: function (field) {
                    return field.$element.closest('.form-group');
                },
                errorsWrapper: '<ul class=\"help-block\"></ul>',
                errorElem: '<li></li>'
            });
        }

        /**
         * Masks unmasks the password for the given input field when clicking
         * on the given trigger element-
         * @param {DOMElement|jQuery} trigger
         * @param {DOMElement|jQuery|null} input When null, searches for an
         * element with the ID given by the <code>data-pw-trigger-id</code> on
         * the trigger element.
         */
        function setupPasswordHideShow(trigger, input) {
            var $trigger = $(trigger);
            var $input = input ? $(input) : $(document.getElementById($trigger.data('pw-trigger-id')));
            $trigger.on('click', function(){
                $input.togglePassword();
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
            $(form).on('submit', function(event) {
                var _form = this;
                event.preventDefault();
                $.LoadingOverlay('show', Moose.Environment.loadingOverlayOptions);
                setTimeout(function() {
                    _form.submit();
               }, delay < 100 ? 100 : delay);
            });
        }
        
        function onDocumentReady() {
            window.parsley.setLocale(Moose.Environment.locale);
            $('[data-bootstrap-parsley]').eachValue(setupForm);
            $('.pw-trigger').eachValue(setupPasswordHideShow);
            $('form').eachValue(delayFormSubmit);
        }

        return {
            onDocumentReady: onDocumentReady,
            setupForm: setupForm
        };
    })();    
})(jQuery, window, window.Moose);