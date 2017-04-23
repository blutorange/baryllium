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

describe(browser.options.desiredCapabilities.browserName + ' - Register page', function() {
    var LoginPage = require('../../lib/pages/LoginPage');
    var RegisterPage = require('../../lib/pages/RegisterPage');
    var Portal = require('../../lib/pages/Portal');
    var Dashboard = require('../../lib/pages/DashboardPage');

    it('should create an activated account for a new user.', function() {
        RegisterPage.open({
            skipCheck: true
        });
              
        expect(Portal.msgListSuccess.now).to.have.lengthOf(0);
       
        RegisterPage.tfUsername.now.setValue('s1234567');
        RegisterPage.tfPasswordCampusDual.now.setValue('foobar');
        RegisterPage.tfPassword.now.setValue('foobar');
        RegisterPage.tfPasswordRepeat.now.setValue('foobar');
        RegisterPage.acceptTOS();
        RegisterPage.submit();
        
        expect(Portal.msgListSuccess.atLeast(1).soon).to.have.lengthOf(1);
        
        LoginPage.open();
        LoginPage.tfUsername.now.setValue('s1234567');
        LoginPage.tfPassword.now.setValue('foobar');
        LoginPage.submit();
        
        expect(Dashboard.divDashboard.soon).to.exist;
    });
              
    it('should refuse to create an account for an existing user.', function() {
        RegisterPage.open({
            skipCheck: true
        });
       
        expect(Portal.msgListParsley.now).to.have.lengthOf(0);
       
        RegisterPage.tfUsername.now.setValue('s1234567');
        RegisterPage.tfPasswordCampusDual.now.setValue('foobar');
        RegisterPage.tfPassword.now.setValue('foobar');
        RegisterPage.tfPasswordRepeat.now.setValue('foobar');
        RegisterPage.acceptTOS();
        RegisterPage.submit();
        
        expect(Portal.msgListParsley.atLeast(1).soon).to.have.lengthOf(1);
    });
    
    it('should check password repeat client side.', function() {
        RegisterPage.open({
            skipCheck: true
        });
       
        expect(Portal.msgListParsley.now).to.have.lengthOf(0);
       
        RegisterPage.tfUsername.now.setValue('s7654321');
        RegisterPage.tfPasswordCampusDual.now.setValue('foobar');
        RegisterPage.tfPassword.now.setValue('foobar');
        RegisterPage.tfPasswordRepeat.now.setValue('barfoo');
        RegisterPage.acceptTOS();
        RegisterPage.submit();
        
        expect(Portal.msgListParsley.atLeast(1).soon).to.have.lengthOf(1);
    });
       
    it('should not allow unsafe passwords client-side.', function() {
        RegisterPage.open({
            skipCheck: true
        });
       
        expect(Portal.msgListParsley.now).to.have.lengthOf(0);
       
        RegisterPage.tfUsername.now.setValue('s7654321');
        RegisterPage.tfPasswordCampusDual.now.setValue('foobar');
        RegisterPage.tfPassword.now.setValue('foo');
        RegisterPage.tfPasswordRepeat.now.setValue('foo');
        RegisterPage.acceptTOS();
        RegisterPage.submit();
        
        expect(Portal.msgListParsley.atLeast(1).soon).to.have.lengthOf(1);
    });
    
    it('should not allow unsafe passwords server-side.', function() {
        RegisterPage.open({
            skipCheck: true
        });
       
        expect(Portal.msgListDanger.now).to.have.lengthOf(0);
        Portal.disableParsley();
       
        RegisterPage.tfUsername.now.setValue('s7654321');
        RegisterPage.tfPasswordCampusDual.now.setValue('foobar');
        RegisterPage.tfPassword.now.setValue('foo');
        RegisterPage.tfPasswordRepeat.now.setValue('foo');
        RegisterPage.acceptTOS();
        RegisterPage.submit();
        
        expect(Portal.msgListDanger.atLeast(1).soon).to.have.lengthOf(1);
    });
    
    it('should require terms of service to be accepted client-side.', function() {
        RegisterPage.open({
            skipCheck: true
        });
              
        expect(Portal.msgListParsley.now).to.have.lengthOf(0);
              
        RegisterPage.tfUsername.now.setValue('s7654321');
        RegisterPage.tfPasswordCampusDual.now.setValue('foobar');
        RegisterPage.tfPassword.now.setValue('foobar');
        RegisterPage.tfPasswordRepeat.now.setValue('foobar');
        RegisterPage.rejectTOS();
        RegisterPage.submit();
        
        expect(Portal.msgListParsley.atLeast(1).soon).to.have.lengthOf(1);
    });
    
    it('should require terms of service to be accepted server-side.', function() {
        RegisterPage.open({
            skipCheck: true
        });

        expect(Portal.msgListInfo.now).to.have.lengthOf(0);
        Portal.disableParsley();
             
        RegisterPage.tfUsername.now.setValue('s7654321');
        RegisterPage.tfPasswordCampusDual.now.setValue('foobar');
        RegisterPage.tfPassword.now.setValue('foo');
        RegisterPage.tfPasswordRepeat.now.setValue('foo');
        expect(RegisterPage.cbTermsOfService.now.isSelected()).to.be.false;
        RegisterPage.submit();
        
        expect(Portal.msgListInfo.atLeast(1).soon).to.have.lengthOf(1);
    });
    
    it('should require all required fields to be filled out.', function() {
        RegisterPage.open({
            skipCheck: true
        });
        expect(Portal.msgListParsley.now).to.have.lengthOf(0);
        RegisterPage.submit();
        expect(Portal.msgListParsley.atLeast(1).soon).to.have.lengthOf(5);
    });
    
    it('should require student ID server-side.', function() {
        RegisterPage.open({
            skipCheck: true
        });
        
        expect(Portal.msgListWarn.now).to.have.lengthOf(0);
        Portal.disableParsley();
        
        RegisterPage.tfPasswordCampusDual.now.setValue('foobar');
        RegisterPage.tfPassword.now.setValue('foobar');
        RegisterPage.tfPasswordRepeat.now.setValue('foobar');
        RegisterPage.acceptTOS();
        
        RegisterPage.submit();
        
        expect(Portal.msgListWarn.atLeast(1).soon).to.have.lengthOf(1);
    });
    
    it('should require password server-side.', function() {
        RegisterPage.open({
            skipCheck: true
        });
        
        expect(Portal.msgListInfo.now).to.have.lengthOf(0);
        Portal.disableParsley();
        
        RegisterPage.tfUsername.now.setValue('s7654321');
        RegisterPage.tfPasswordCampusDual.now.setValue('foobar');
        RegisterPage.tfPasswordRepeat.now.setValue('foobar');
        RegisterPage.acceptTOS();
        RegisterPage.submit();
        
        expect(Portal.msgListInfo.atLeast(1).soon).to.have.lengthOf(1);
    });
    
    it('should require password Campus Dual server-side.', function() {
        RegisterPage.open({
            skipCheck: true
        });
        
        expect(Portal.msgListInfo.now).to.have.lengthOf(0);
        Portal.disableParsley();
        
        RegisterPage.tfUsername.now.setValue('s7654321');
        RegisterPage.tfPassword.now.setValue('foobar');
        RegisterPage.tfPasswordRepeat.now.setValue('foobar');
        RegisterPage.acceptTOS();
        RegisterPage.submit();
        
        expect(Portal.msgListInfo.atLeast(1).soon).to.have.lengthOf(1);
    });
});