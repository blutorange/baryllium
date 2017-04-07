describe('webdriverio with example', function() {
    it('should open a simple webpage', function() {
        browser.url('http://example.com');
        browser.checkViewport();
        browser.getTitle().should.be.equal('Example Domain');
    });
});

