/* The 3-Clause BSD License
 * 
 * SPDX short identifier: BSD-3-Clause
 *
 * Note: This license has also been called the "New BSD License" or "Modified
 * BSD License". See also the 2-clause BSD License.
 * 
 * Copyright 2015 The Moose Team
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/* global browser*/

var PromisedObject = Object.create(new Object(), {
    now: {
        get: function() {
            throw new Error('Get must be implemented for ' + this);
        }
    },
    _timeout: {
        value: 30000,
        writable: true
    },
    _interval: {
        value: 500,
        writable: true
    },
    timeout: {
        value: function(value) {
            if (arguments.length === 0) return this._timeout;
            this._timeout = Math.max(0, Number(value));
            return this;
        }
    },
    interval: {
        value: function(value) {
            if (arguments.length === 0) return this._interval;
            this._interval = Math.max(0, Number(value));
            return this;
        }
    },
    errorMessage: {
        get: function() {
            return 'Object not found after ' + this.timeout() + 'ms with a wait of ' + this.interval() + 'ms.';
        }
    },
    errorMessageInverse: {
        get: function() {
            return 'Object still found after ' + this.timeout() + 'ms with a wait of ' + this.interval() + 'ms.';
        }
    },
    gone: {
      get: function() {
            return browser.waitUntil(
                (function(){return !this.now;}).bind(this),
                this.timeout(),
                this.errorMessageInverse,
                this.interval()
            );
        }  
    },
    soon: {
        get: function() {
            return browser.waitUntil(
                (function(){return this.now;}).bind(this),
                this.timeout(),
                this.errorMessage,
                this.interval()
            );
        }
    }                
});

function promisedElement(locatable, selector) {
    console.log("Creating element retriever for " + selector);
    return Object.create(PromisedObject, {
        now: {
            get: function() {
                var l = locatable.now ? locatable.now : locatable;
                var el = l.element(selector);
                if (el === null) return null;
                return el.isExisting() ? el : null;
            }
        },
        element: {
            value: function(selector) {
                return promisedElement(this, selector);
            }
        }
    });
}

function promisedElements(locatable, selector) {
    console.log("Creating elements retriever for " + selector);
    return Object.create(PromisedObject, {
        now: {
            get: function() {
                var els = locatable.elements(selector);
                if (!els) return null;
                if (!els.value) return null;
                if (els.value.length < this.atLeast() || els.value.length > this.atMost()) return null;
                return els.value;
            }
        },
        _atLeast: {
            value: 0,
            writable: true
        },
        _atMost: {
            value: 9999,
            writable: true
        },
        atLeast: {
            value: function(value) {
                if (arguments.length === 0 ) return this._atLeast;
                this._atLeast = Math.max(0, Number(value));
                return this;
            }
        },
        atMost: {
            value: function(value) {
                if (arguments.length === 0 ) return this._atMost;
                this._atMost = Math.max(0, Number(value));
                return this;
            }
        },
        exactly: {
            value: function(value) {
                return this.atLeast(value).atMost(value);
            }
        },
        some: {
            get: function() {
                return this.atLeast(1);
            }
        }
    });    
}

function Page () {
}

Page.prototype.open = function (path) {
    browser.url('/' + path);
}

Page.prototype.element = function(selector) {
    return promisedElement(browser, selector);
}

Page.prototype.elements = function(selector) {
    return promisedElements(browser, selector);
}

module.exports = new Page();

