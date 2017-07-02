/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Module for interacting with forms, validating input etc.
 */
window.Moose.Factory.Forms = function(window, Moose, undefined) {
    "use strict";
    var $ = Moose.Library.jQuery;

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

    function restResponseHandler(response, newValue) {
        if (typeof(response.promise) === 'function') {
            if (response.responseJson) response = responseJson;
            else {
                try {
                    response = JSON.parse(response.responseText);
                }
                catch (e) {
                    console.error('Bad server response, could not parse JSON.', e, response);
                    return 'Bad server response, could not parse JSON.';
                }
            }
        }
        if (!$.isPlainObject(response)) {
            console.error('Bad server response, did not return JSON object.', response);
            return 'Bad server response, did not return JSON object.';
        }
        if (response.success === true)
            return;
        if (response.error)
            return response.error.message + ": " + response.error.details;
        console.error('Server indicated neither success nor failure.', response, newValue);
        return 'Server indicated neither success nor failure.';
    }

    function setupEditable(element) {
        var $element = $(element);
        $element.editable({
            send: 'always',
            mode: $element.data('data-mode') || Moose.Persistence.getClientConfiguration('fields', 'option.edit.mode') || 'popup',
            value: function() {
                if ($element.hasClass('editable-empty')) return '';
                var $content = $element.find('.editable-content');
                return $content.length > 0 ? $content.text() : $element.text();
            },
            display: function(value) {
                var $content = $element.find('.editable-content');
                ($content.length > 0 ? $content : $element).text(value);
            },
            url: function(params) {
                var fields = {
                    id: $element.data('id')
                };
                fields[$element.data('field')] = params.value;
                return $.ajax($element.data('save-url'), {
                    method: $element.data('method') || 'POST',
                    async: true,
                    cache: false,
                    contentType: 'application/json; charset=UTF-8',
                    dataType: 'json',
                    data: JSON.stringify({
                        action: $element.data('action'),
                        entity: {
                            fields: fields
                        }
                    })
                });
            },
            success: restResponseHandler,
            error: restResponseHandler
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
    
    function setupClearableInput(element) {
        var $input = $('.clearable-field', element);
        var $trigger = $('.clearable-trigger', element);
        $input.keyup(function() {
            $trigger.toggle(Boolean($input.val()));
        });
        $trigger.toggle(Boolean($input.val()));
        $trigger.click(function() {
            $input.val('').focus();
            $trigger.hide();
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
    
    function setupDatepicker(element) {
        $(element).datepicker({
            language: Moose.Environment.locale
        });
    }
    
    function preventSubmitOnEnter(element) {
        $(element).find('input,textarea').each(function(){
            $(this).on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) { 
                    e.preventDefault();
                    return false;
                }
            });
        });
    }
    
    function onNewElement(context) {
        $('[data-bootstrap-parsley]', context).eachValue(setupForm);
        $('.pw-trigger', context).eachValue(setupPasswordHideShow);
        $('.editable', context).eachValue(setupEditable);
        $('.clearable-input', context).eachValue(setupClearableInput);
        $('.ms-datepicker', context).eachValue(setupDatepicker);
        $('form', context).eachValue(delayFormSubmit);
        $('form.no-enter', context).eachValue(preventSubmitOnEnter);
    }

    function onDocumentReady() {
        window.parsley.setLocale(Moose.Environment.locale);
        onNewElement(window.document);
    }

    return {
        onNewElement: onNewElement,
        onDocumentReady: onDocumentReady,
        setupForm: setupForm
    };
};