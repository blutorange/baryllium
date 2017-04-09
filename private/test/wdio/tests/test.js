describe('webdriverio with example', function() {
    it('should open a simple webpage', function() {
        browser.url('/private/sandbox.php');
        browser.checkViewport();
        browser.getTitle().should.be.equal('sandbox');
    });
});