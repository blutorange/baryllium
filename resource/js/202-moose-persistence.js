/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.Moose.Factory.Persistence = function(window, Moose, undefined) {
    var $ = Moose.Library.jQuery;
    var c = Moose.Library.Cookies;
    var ls = window.localStorage;

    function getterCookie(key) {
        return c.get(key);
    }

    function setterCookie(key, value){
        c.set(key, value)
    }

    function setterClient(key, value) {
        ls[key] = value;
    }

    function getterClient(key) {
        return ls[key];
    }

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

    function getConfiguration(namespace, getter, setter, key, defaultValue) {
        var json;
        try {
            json = $.parseJSON(atob(getter(namespace)));
        } catch (ignored) {
            json = null;
        }
        if (!$.isPlainObject(json)) {
            json = {};
            setter(namespace, JSON.stringify(json));
        }
        if (key === null || key === undefined)
            return json;
        var stringKey = String(key);
        return json.hasOwnProperty(stringKey) ? json[stringKey] : defaultValue;
    }

    function setConfiguration(namespace, key, value, getter, setter) {
            var json = getConfiguration(namespace, getter, setter);
            json[String(key)] = value;
            setter(namespace, btoa(JSON.stringify(json)));
    }

    function getClientConfiguration(namespace, key, defaultValue) {
        return getConfiguration(namespace, getterClient, setterClient, key, defaultValue);
    }

    function setClientConfiguration(namespace, key, value) {
        setConfiguration(namespace, key, value, getterClient, setterClient);
    }

    function getCookieConfiguration(namespace, key, defaultValue) {                     
        return getConfiguration(namespace, getterCookie, setterCookie, key, defaultValue);
    }

    function setCookieConfiguration(namespace, key, value){
        setConfiguration(namespace, key, value, getterCookie, setterCookie);
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
            ls[namespace] = JSON.stringify(json);
        }
        if (arguments.length === 1)
            return json;
        var stringKey = String(key);
        return json.hasOwnProperty(stringKey) ? json[stringKey] : defaultValue;
    }

    function setClientConfiguration(namespace, key, value) {
        var json = getClientConfiguration(namespace);
        json[String(key)] = value;
        ls[namespace] = JSON.stringify(json);
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
                setter = setCookieConfiguration;                    
                break;
            case 'server':
                getter = $.noop;
                setter = $.noop;
                console.error('Server side persistence not yet implemented.');
                break;
            default:
                getter = $.noop;
                setter = $.noop;
                console.error('Unknown persistence mode: ' + $field.data('persist-type'));
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
};