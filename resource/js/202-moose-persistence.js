/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.Moose.Factory.Persistence = function(window, Moose, undefined) {
    "use strict";
    var $ = Moose.Library.jQuery;
    var c = Moose.Library.Cookies;
    var ls = window.localStorage;
    var cacheServer = {};
    var cacheServerListeners = [];
    
    function getterCookie(key) {
        return c.get(key);
    }

    function setterCookie(key, value){
        c.set(key, value)
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

    function getCookieConfiguration(namespace, key, defaultValue, onGet) {                     
        var value = getConfiguration(namespace, getterCookie, setterCookie, key, defaultValue);
        onGet && onGet(value);
        return value;
    }

    function setCookieConfiguration(namespace, key, value){
        setConfiguration(namespace, key, value, getterCookie, setterCookie);
    }
    
    function getServerConfiguration(namespace, key, defaultValue, onGet, uid) {
        if (cacheServer[namespace]) {
            if (cacheServer[namespace] === true) {
                cacheServerListeners[namespace].push(function(options) {
                    onGet(Object.prototype.hasOwnProperty.call(options, key) ? options[key] : defaultValue);
                });
            }
            else {
                onGet(Object.prototype.hasOwnProperty.call(cacheServer[namespace], key) ? cacheServer[namespace][key] : defaultValue);
            }
            return;
        }
        cacheServerListeners[namespace] = [];
        cacheServer[namespace] = true;
        var data = {
            action: 'all',
            request: {
                fields: {
                    uid: uid,
                    optionList: null
                }
            }
        };
        var callback = function(data) {
            var options = data.options;
            cacheServer[namespace] = data.options;
            $.eachValue(cacheServerListeners[namespace], function(listener) {
               listener(options); 
            });
            onGet(Object.prototype.hasOwnProperty.call(options, key) ? options[key] : defaultValue);
        };
        Moose.Util.ajaxServlet(Moose.Environment.paths[namespace], 'GET', data, callback, true);
    }
    
    function setServerConfiguration(namespace, key, value, uid) {
        var optionList = {};
        optionList[key] = value;
        var data = {
            action: 'option',
            request: {
                fields: {
                    uid: uid,
                    optionList: optionList
                }
            }
        };
        Moose.Util.ajaxServlet(Moose.Environment.paths[namespace], 'POST', data, $.noop, true);
    }

    function getClientConfiguration(namespace, key, defaultValue, onGet) {
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
        var value = json.hasOwnProperty(stringKey) ? json[stringKey] : defaultValue;
        onGet && onGet(value);
        return value;
    }

    function setClientConfiguration(namespace, key, value) {
        var json = getClientConfiguration(namespace);
        json[String(key)] = value;
        ls[namespace] = JSON.stringify(json);
    }

    function setupFormField(formField, persistenceType, persistanceNamespace) {
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
                getter = getServerConfiguration;
                setter = setServerConfiguration;
                break;
            default:
                getter = $.noop;
                setter = $.noop;
                console.error('Unknown persistence mode: ' + $field.data('persist-type'));
        }
        var namespace = persistanceNamespace || $field.data('persist-namespace') || 'fields';
        var uid = $field.data('persist-uid') || 0;
        var key = this.id || this.name;
        getter(namespace, key, undefined, function(initialValue) {
            if (initialValue !== undefined) {
                setElementValue($field, initialValue);
            }            
        }, uid);
        $field.on("change", function() {
            var value = getElementValue($field);
            setter(namespace, key, value, uid);
        });
    }
    
    function onNewElement(context) {
        $('.persist', context).eachValue(setupFormField);
    }
    
    function getClientField(name) {
        return getClientConfiguration('fields')[name];
    }
    
    function getServerField(name) {
        return getServerConfiguration('fields')[name];
    }
    
    function getCookieField(name) {
        return getCookieConfiguration('fields')[name];
    }

    function onDocumentReady() {
        onNewElement(window.document);
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
        onNewElement: onNewElement,
        onDocumentReady: onDocumentReady,
        getClientField: getClientField,
        getServerField: getServerField,
        getCookieField: getCookieField,
        getClientConfiguration: getClientConfiguration,
        getCookieConfiguration: getCookieConfiguration,
        getServerConfiguration: getServerConfiguration
    };
};