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
var Page = require('./Page')

var RegisterPage = Object.create(Page, {
    /**
     * define elements
     */
    registerForm: {get:function(){return Page.element.call(this, '#register_form');}},
    
    tfUsername: {get: function(){return Page.element.call(this, '[name=studentid]');}},
    tfPasswordCampusDual: {get: function () {return Page.element.call(this, '[name=passwordcdual]');}},
    tfPassword: {get:function(){return Page.element.call(this, '[name=password]');}},
    tfPasswordRepeat: {get:function(){return Page.element.call(this, '[name=password-repeat]');}},
    
    cbSavePasswordCampusDual: {get:function(){return Page.element.call(this, '[name=savecd]');}},
    cbTermsOfService: {get:function(){return Page.element.call(this, '[name=agb]');}},
    
    btnSubmit: {get:function(){return Page.element.call(this, '[name=btnSubmit]');}},    

    /**
     * Define or overwrite page methods
     */
    open: {
        value: function(options) {
            options = options || {};
            var url = 'public/controller/register.php';
            if (options.skipCheck) url += '?skp-reg-ck=1';
            Page.open.call(this, url);
            expect(this.registerForm.soon).to.exist;
        }
    },
    
    setTOS: {
        value: function(accept) {
            var me = this;
            if (me.cbTermsOfService.now.isSelected() !== accept) {
                me.cbTermsOfService.now.click();
                browser.waitUntil(function(){return me.cbTermsOfService.now.isSelected() === accept;});
            }
            return this;
        }
    },
    
    acceptTOS: {
        value: function() {
            this.setTOS(true);
            return this;
        }
    },
    
    rejectTOS: {
        value: function() {
            this.setTOS(false);
            return this;
        }
    },
    
    submit: {
        value: function() {
            this.registerForm.now.submitForm();
        }
    }
});

module.exports = RegisterPage;