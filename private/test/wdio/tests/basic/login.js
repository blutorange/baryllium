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

describe(browser.options.desiredCapabilities.browserName + ' - Login page', function() {
    var LoginPage = require('../../lib/pages/LoginPage');
    var Portal = require('../../lib/pages/Portal');
    var Dashboard = require('../../lib/pages/DashboardPage');

    it('should deny access for non-existing user.', function() {
        LoginPage.open();
        
        expect(Portal.msgListWarn.now).to.have.lengthOf(0);
        
        LoginPage.tfUsername.now.setValue('s9999999');
        LoginPage.tfPassword.now.setValue('password');
        LoginPage.submit();
        
        expect(Portal.msgListWarn.some.soon).to.have.lengthOf(1);
    });
    
    it('should deny access for wrong password.', function() {
        LoginPage.open();
        
        expect(Portal.msgListWarn.now).to.have.lengthOf(0);
        
        LoginPage.tfUsername.now.setValue('s6900633');
        LoginPage.tfPassword.now.setValue('wordpass');
        LoginPage.submit();
        
        expect(Portal.msgListWarn.some.soon).to.have.lengthOf(1);
    });
    
    it('should validate input client-side.', function() {
        LoginPage.open();
        expect(Portal.msgListParsley.now).to.have.lengthOf(0);
        
        LoginPage.tfUsername.now.setValue('');
        LoginPage.tfPassword.now.setValue('');
        LoginPage.submit();
        
        expect(Portal.msgListParsley.some.soon).to.have.lengthOf(2);
    });
    
    it('should allow access for the correct credentials.', function() {
        LoginPage.open();       
        LoginPage.tfUsername.now.setValue('s6900633');
        LoginPage.tfPassword.now.setValue('password');
        LoginPage.submit();
        
        expect(Dashboard.divDashboard.soon).to.exist;
    });
});
