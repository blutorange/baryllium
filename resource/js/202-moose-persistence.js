/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function($, window, Moose, undefined){
    Moose.Persistence = (function(){
        function getElementValue($element) {
            var val;
            if (($element).attr('type') === 'checkbox') {
                val = $element.prop('checked');
            } else {
                val = $element.val();
            }
            return val;
        }
        
        function setElementValue($element, value) {
            if (($element).attr('type') === 'checkbox') {
                $element.prop('checked', Boolean(value));
            } else {
                $element.val(value);
            }
        }

        function getClientConfiguration(namespace, key, defaultValue) {
            var json;
            try {
                json = $.parseJSON(localStorage[namespace]);
            } catch (ignored) {
                json = null;
            }
            if (!$.isPlainObject(json)) {
                json = {};
                window.localStorage[namespace] = JSON.stringify(json);
            }
            if (arguments.length === 1)
                return json;
            var stringKey = String(key);
            return json.hasOwnProperty(stringKey) ? json[stringKey] : defaultValue;
        }

        function setClientConfiguration(namespace, key, value) {
            var json = getClientConfiguration(namespace);
            json[String(key)] = value;
            window.localStorage[namespace] = JSON.stringify(json);
        }
        
        function getCookieConfiguration(namespace, key, defaultValue) {
            //TODO            
        }

        function setCookieConfiguration(namespace, key, value) {
            //TODO
        }
        
        function setupFormField(formField, persistenceType) {
            var $field = $(formField);
            var getter, setter;
            switch (persistenceType || $field.data('persist-type')) {
                case 'client':
                    getter = getClientConfiguration;
                    setter = setClientConfiguration;
                    break;
                case 'cookie':
                    getter = getCookieConfiguration;
                    getter = setCookieConfiguration;
                    break;
                case 'server':
                    getter = setter = $.noop;
                    console.error('Persistence type server not yet implemented.');
                    break;
                default:
                    getter = setter = $.noop;
                    console.error('Unknown persistence type: ' + persistenceType);
                    break;
            }
            var key = this.id || this.name;
            var initialValue = getter('fields', key, undefined);
            if (initialValue !== undefined) {
                setElementValue($field, initialValue);
            }
            $field.on("change", function() {
                var value = getElementValue($field);
                setter('fields', key, value);
            });
        }

        function onDocumentReady() {
            $('.persist').eachValue(setupFormField);
        }
        
        return {
            /**
             * @param formField A DOM element of jQuery element representing
             * a form field, ie. an input, textarea or select element.
             * @param persistanceType String, either 'client', 'cookie', or
             * 'server' (not supported yet). When this parameter not given,
             * the persistence type is taken from the data-persist-type
             * attribute.
             */
            setupFormField: setupFormField,
            onDocumentReady: onDocumentReady,
            getClientConfiguration: getClientConfiguration,
            getCookieConfiguration: getCookieConfiguration,

        };
    })();
})(jQuery, window, window.Moose);