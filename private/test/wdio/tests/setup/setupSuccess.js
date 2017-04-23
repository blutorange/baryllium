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

var fs = require('fs');
var path = require('path');

describe(browser.options.desiredCapabilities.browserName + ' - System setup', function() {
    it('Should open the initial configuration screen.', function() {
        browser.url('/private/php/setup/setup.php?dbg-db-md=testing');
        browser.waitForExist('#setup_system_form');
    });

    it('Should setup the system and initialize the database.', function() {
        browser.getTitle().should.be.equal('Setup');
        browser.selectByValue('[name=driver]', 'mysql');
        browser.setValue('[name=pass]', 'baryllium');
        browser.setValue('[name=dbnameDev]', 'baryllium');
        browser.setValue('[name=dbnameTest]', 'baryllium');
        browser.checkViewport();
        browser.submitForm('#setup_system_form');
        browser.waitForExist('#t_setup_redirect_user a', 60000);
        
        expect(fs.existsSync('./FIRST_INSTALL')).to.be.false;

        browser.checkViewport();
        browser.click('#t_setup_redirect_user a');
        browser.waitForExist('#setup_admin_form');    
    });
    
    it('Should successfully create the account for the administrator.', function() {
        browser.setValue('[name=firstname]', 'Andre');
        browser.setValue('[name=lastname]', 'Wachsmuth');
        browser.setValue('[name=mail]', 's1234567@ba-dresden.de');
        browser.setValue('[name=password]', 'sadmin');
        browser.setValue('[name=password-repeat]', 'sadmin');
        browser.checkViewport();
        browser.submitForm('#setup_admin_form');
        browser.waitForExist('#login_form');
    });

    it('Should be able to sign in as the adminstrator.', function() {
        browser.setValue('[name=studentid]', 'sadmin');
        browser.setValue('[name=password]', 'sadmin');
        console.log('URL is ',browser.getUrl());
        browser.checkViewport();
        browser.submitForm('#login_form');
        browser.waitForExist('#setup_import_form');        
    });

    it('Should import the list of field of studies and courses.', function() {        
        var toUpload = path.join(__dirname, 'fos.csv');
        browser.chooseFile('input[name=importcss]', toUpload);
        browser.submitForm('#setup_import_form');
        browser.waitForExist('li.field-of-study');
        browser.checkViewport();
        browser.elements('li.field-of-study').value.length.should.be.equal(2);
        browser.elements('li.field-of-study>ul>li').value.length.should.be.equal(57);
    });

    it('Should show the correct admin profile.', function() {
        browser.click('.nav_sec-profile a');
        browser.waitForExist('#home');
        browser.checkViewport();
        browser.getText('#home .profile-name').should.have.string('Andre').and.have.string('Wachsmuth');
        browser.getText('#home .profile-mail').should.have.string('s1234567@ba-dresden.de');
        browser.getText('#home .profile-postcount').should.have.string('0');
    });
});
