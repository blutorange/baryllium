// Main configuration file for Chrome.

var merge = require('deepmerge');
var wdioConf = require('./wdio.root.conf.js');

// http://kb.mozillazine.org/Firefox_:_FAQs_:_About:config_Entries
// about:config
exports.config = merge(wdioConf.config, {
    firefoxProfile: {
        'security.ask_for_password': false,
        'security.insecure_password.ui.enabled': false,
        'security.insecure_field_warning.contextual.enabled': false,
        'browser.showQuitWarning': false,
        'devtools.webide.autosaveFiles': true,
        'browser.download.downloadDir': wdioConf.config.downloadDirectory,
        'browser.download.dir': wdioConf.config.downloadDirectory,
        'browser.download.useDownloadDir': true,
        'browser.download.folderList': 1,
        'update.showSlidingNotification': false,
        'update_notifications.enabled': false,
        'extensions.update.notifyUser': false,
        'browser.tabs.warnOnClose': false,
        'browser.urlbar.autoFill': false,
        'browser.urlbar.autocomplete.enabled': false,
        'signon.prefillForms': false,
        'browser.formfill.enable': false,
        'signon.rememberSignons': false,
        'browser.download.manager.alertOnEXEOpen': false,
        'browser.download.manager.flashCount': false,
        'browser.download.manager.focusWhenStarting	': false,
        'browser.download.manager.showAlertOnComplete': false,
        'browser.download.manager.scanWhenDone': false,
        'browser.download.manager.showWhenStarting': false,
        'browser.download.manager.skipWinSecurityPolicyChecks': true,
        'browser.popups.showPopupBlocker': false,
        'pdfjs.disabled': true
    }
});

exports.config.capabilities.push(merge(exports.config.defaultCapabilities, {
    browserName: 'firefox'
}));
exports.config.services.push('firefox-profile');