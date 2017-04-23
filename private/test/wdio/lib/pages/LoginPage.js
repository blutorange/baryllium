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

var Page = require('./Page')

var LoginPage = Object.create(Page, {
    /**
     * define elements
     */
    loginForm: {
        get: function () {
            return Page.element.call(this, '#login_form');
        }
    },
    tfUsername: {
        get: function () {
            return Page.element.call(this, '[name=studentid]');
        }
    },
    tfPassword: {
        get: function () {
            return Page.element.call(this, '[name=password]');
        }
    },
    btnSubmit: {
        get: function () {
            return Page.element.call(this, '[name=btnSubmit]');
        }
    },
    lnkRegister: {
        get: function () {
            return Page.element.call(this, '#login_register');
        }
    },
    lnkPasswordRecovery: {
        get: function () {
            return Page.element.call(this, '#login_pwrecover');
        }
    },

    /**
     * Define or overwrite page methods
     */
    open: {
        value: function() {
            Page.open.call(this, 'public/controller/login.php');
            expect(this.loginForm.soon).to.exist;
        }
    },
    
    login: {
        value: function(username, password) {
            this.open();
            this.tfUsername.now.setValue(username);
            this.tfPassword.now.setValue(arguments.length >= 2 ? password : 'password');
            this.submit();
            expect(this.loginForm.gone).to.be.true;
        }
    },
    
    submit: {
        value: function() {
            this.loginForm.now.submitForm();
        }
    }
});

module.exports = LoginPage;