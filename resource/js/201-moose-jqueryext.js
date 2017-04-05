/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function($, window, Moose, undefined){
    Moose.jQueryExtension = (function(fn){
        /**
         * Same as <code>$.fn.each</code>, but the callback takes only one
         * argument, the DOMElement.
         * @param {function} callback Callback for each element. It is passed
         * the current DOMElement as the first argument.
         * @returns jQuery
         */
        function eachValue(callback){
            return this.each(function(){callback.call(this, this);});
        }
        
        function registerAll() {
            fn.eachValue = eachValue;
        }
        
        return {
            registerAll: registerAll
        }
    })($.fn);
})(jQuery, window, window.Moose);