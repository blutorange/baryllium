var fs = require('fs');
var path = require('path');
describe('Dummy', function() {
    it('Should be a dummy.', function() {
        browser.url('/public/controller/dashboard.php');
        browser.waitForExist('#setup_system_form');
    });
});
