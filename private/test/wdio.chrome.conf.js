// Main configuration file for Chrome.

var merge = require('deepmerge');
var wdioConf = require('./wdio.root.conf.js');

exports.config = merge(wdioConf.config, {});

exports.config.capabilities.push(merge(exports.config.defaultCapabilities, {
    browserName: 'chrome',
    chromeOptions: {
        args: [
            '--disable-plugins',
            '--disable-save-password-bubble',
            '--disable-translate'
        ],
        extensions: [
        ],
        prefs: {
            'credentials_enable_service': false,
            profile: {
                'profile.password_manager_enabled': false,
                "managed_default_content_settings": {
                    notifications: 2
                }
            }
        }
    }
}));