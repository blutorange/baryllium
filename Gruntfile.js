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

var path = require('path');

module.exports = function(grunt) {
    
    // Project configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        uglify: {
            options: {
                banner: '/*! LICENSE: BSD-3-Clause <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n',
                compress: true,
                mangle: true,
                sourceMap: true,
                preserveComments: false
            },
            build: {
                files: {
//                    'resource/build/js/all.min.js': ['resource/build/js/babel/*.js']
                    'resource/build/js/all.min.js': ['resource/js/*.js']
                }
            }
        },
        cssmin: {
            options: {
                sourceMap: false
            },
            build: {
                files: {
                    'resource/build/css/all.min.css': [
                        'resource/less-css/*.css',
                        'resource/css/*.css'
                    ]
                }
            }
        },
        less: {
            options: {
                compress: false,
                sourceMap: true,
                paths: 'resource/less-import'
            },
            build: {
                files: [
                    {expand: true, flatten: true, dest: 'resource/less-css/', src: 'resource/less/*.less', ext: '.css'}
                ]
            }
        },
        copy: {
            options: {
                encoding: "UTF-8"
            },
            build: {
                files: [
                    // Paths in bootstrap.css are relative, so copy the fonts.
                    {expand: true, cwd: 'resource/fonts/', src: '**/*', dest: 'resource/build/fonts/'},
                    {expand: true, cwd: 'resource/other/', src: '**/*', dest: 'resource/build/other/'}
                ]
            },
            backupConfig: {
                files: {
                    'private/config/phinx.yml.bkp': 'private/config/phinx.yml'
                }
            },
            restoreConfig: {
                files: {
                    'private/config/phinx.yml': 'private/config/phinx.yml.bkp'
                }
            },
            wdioChromeConf: {
                files: {
                    'private/test/wdio.conf.js': 'private/test/wdio.chrome.conf.js'
                }
            },
            wdioPhantomjsConf: {
                files: {
                    'private/test/wdio.conf.js': 'private/test/wdio.phantomjs.conf.js'
                }
            },
            wdioFirefoxConf: {
                files: {
                    'private/test/wdio.conf.js': 'private/test/wdio.firefox.conf.js'
                }
            }
        },
        autoprefixer: {
            build: {
                files: {
                    'resource/build/css/all.prefix.min.css': 'resource/build/css/all.min.css'
                }
            }
        },
        babel: {
            options: {
                sourceMap: true,
                minified: true,
                compact: true,
                presets: ['env']
            },
            build: {
                files: [
                    {expand: true, cwd: 'resource/js/', dest: 'resource/build/js/babel', src: '**/*'}
                ]
            }
        },
        webdriver: {
            testSetup: {
                configFile: './private/test/wdio/wdio.suite.setup.js'
            },
            testBasic: {
                configFile: './private/test/wdio/wdio.suite.basic.js'
            },
            testSandbox: {
                configFile: './private/test/wdio/wdio.suite.sandbox.js'
            }
        },
        clean: {
            cleanResource: ['resource/build/*'],
            cleanIntegration: ['FIRST_INSTALL', 'private/config/phinx.yml', 'private/test/wdio/download/*'],
            allure: ['private/test/wdio/allure-report/*'],
            report: ['private/test/wdio/report/*', 'private/test/report/*']
        },
        touch: {
            integration: [
                'FIRST_INSTALL'
            ]
        },
        vagrant_commands: {
            integrationPre: {
              commands: [
                    ['halt'],
                    ['up']
              ]
            },
            integrationPost: {
                commands: [
                    ['halt'],
                ]
            },
        },
        exec: {
            allureGenerate: {
                command: 'npm run allure-generate -- "' + path.join('.', 'private', 'test', 'wdio', 'report') + '" -o "' + path.join('.', 'private', 'test', 'wdio', 'allure-report') + '"',
            }
        },
        prompt: {
            allure: {
                options: {
                    questions: [
                        {
                            config: 'prompt-allure',
                            type: 'input',
                            validate: function(){return true;},
                            message: 'Check your browser for test results. Check above for URL if browser did not open. Press any key to continue.'
                        }
                    ]
                }
            }  
        },
        connect: {
             allure: {
                options: {
                    base: 'private/test/wdio/allure-report',
                    port: 8001,
                    host: "0.0.0.0",
                    protocol: 'http',
                    open: true,
                    keepalive: false,
                    useAvailablePort: true
                }
            }
        }
    });
    
    // Load plugin for uglify task.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-autoprefixer');
//    grunt.loadNpmTasks('grunt-babel');
    grunt.loadNpmTasks('grunt-webdriver');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-touch');
    grunt.loadNpmTasks('grunt-vagrant-commands');
    grunt.loadNpmTasks('grunt-continue');
    grunt.loadNpmTasks('grunt-exec');
    grunt.loadNpmTasks('grunt-contrib-connect');
    grunt.loadNpmTasks('grunt-prompt');
    
    // Default tasks.
    grunt.registerTask('build', [
        'copy:build',
        'less',
        'cssmin',
        'autoprefixer',
//        'babel',
        'uglify',
    ]);
    
    grunt.registerTask('cleanResource', [
        'clean:cleanResource'
    ]);
    
    grunt.registerTask('allure', [
        'clean:allure',
        'exec:allureGenerate',
        'connect:allure',
        'prompt:allure'
    ]);
    
    grunt.registerTask('testIntegration', [
        'build',
        'copy:backupConfig',
        'clean:cleanIntegration',
        'continue:on',
        'vagrant_commands:integrationPre',
        'webdriver:testSetup',
        'webdriver:testBasic',
        'vagrant_commands:integrationPost',
        'continue:off',
        'copy:restoreConfig',
        'continue:fail-on-warning'

    ]);
    
    grunt.registerTask('testIntegrationChrome', [
        'copy:wdioChromeConf',
        'testIntegration',
    ]);
    
    grunt.registerTask('testIntegrationPhantomJs', [
        'copy:wdioPhantomjsConf',
        'testIntegration',
    ]);
    
    grunt.registerTask('testIntegrationFirefox', [
        'copy:wdioFirefoxConf',
        'testIntegration',
    ]);
};
