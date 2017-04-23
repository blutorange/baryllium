// Main configuration file for Chrome.

var merge = require('deepmerge');
var wdioConf = require('./wdio.root.conf.js');

exports.config = merge(wdioConf.config, {});

exports.config.capabilities.push(merge(exports.config.defaultCapabilities, {
    browserName: 'phantomjs'
}));