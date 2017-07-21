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
        var $form = $(form);
        $form.parsley({
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
        $form.find('.submit-button').on('click', function() {
            if (form.submitButton) {
                form.submitButton.value = this.getAttribute('data-action') || this.id;
            }
            if (form.submitButtonData) {
                form.submitButtonData.value = this.getAttribute('data-action-data') || '';
            }
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
                var deferred = $.Deferred();
                Moose.Util.ajaxServlet({
                    url: $element.data('save-url'),
                    method: $element.data('method') || 'POST',
                    showLoader: 400,
                    data: {
                        action: $element.data('action'),
                        entity: {
                            fields: fields
                        }
                    },
                    onSuccess: function(data, ajaxOptions, jqXHR) {
                        deferred.resolveWith(null, [data]);
                    },
                    onFailure: function(error, data, ajaxOptions, jqXHR) {
                        deferred.rejectWith(null, [data]);
                    },
                    onLoginCancel: function(ajaxOptions, jqXHR) {
                        deferred.rejectWith(null, ['Login cancelled']);
                    },
                });
                return deferred;
//                return $.ajax($element.data('save-url'), {
//                    method: $element.data('method') || 'POST',
//                    async: true,
//                    cache: false,
//                    contentType: 'application/json; charset=UTF-8',
//                    dataType: 'json',
//                    data: JSON.stringify({
//                        action: $element.data('action'),
//                        entity: {
//                            fields: fields
//                        }
//                    })
//                });
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
    function handleFormSubmit(form, delay) {
        delay = delay || 400;
        $(form).on('submit', function(event) {
            event.preventDefault();
            var _form = this;
            var callback = function() {               
                $.LoadingOverlay('show', Moose.Environment.loadingOverlayOptions);
                window.setTimeout(function() {
                    if (_form.submitButton && $('button[data-action="' + _form.submitButton.value + '"]', _form).hasClass('btn-message-only')) {
                        Moose.Util.ajaxAppendMessages(_form.action, $(_form).serialize(), "POST");
                    }
                    else {
                        _form.submit();
                    }
               }, delay < 100 ? 100 : delay);
           };
           if ($(form).hasClass('requires-login')) {
                Moose.Util.ajaxServlet({
                    url: Moose.Environment.paths.userServlet,
                    method: 'GET',
                    data: {
                        action: 'login'
                    },
                    onSuccess: callback,
                    onAuthorized: callback                              
                });
            }
            else {
                callback();
            }
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
        $('[data-bootstrap-parsley],.bootstrap-parsley', context).eachValue(setupForm);
        $('.pw-trigger', context).eachValue(setupPasswordHideShow);
        $('.editable', context).eachValue(setupEditable);
        $('.clearable-input', context).eachValue(setupClearableInput);
        $('.ms-datepicker', context).eachValue(setupDatepicker);
        $('form', context).eachValue(handleFormSubmit);
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