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
            'test-integration-before': {
                files: {
                    'private/config/phinx.yml.bkp': 'private/config/phinx.yml'
                }
            },
            'test-integration-after': {
                files: {
                    'private/config/phinx.yml': 'private/config/phinx.yml.bkp'
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
            test: {
                configFile: './private/test/wdio.conf.js'
            }
        },
        clean: {
            'clean-resource': ['resource/build/*'],
            'test-integration': ['private/config/phinx.yml', 'FIRST_INSTALL']
        },
        touch: {
            'test-integration': ['FIRST_INSTALL']
        }
    });
    
    // Load plugin for uglify task.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-babel');
    grunt.loadNpmTasks('grunt-webdriver');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-touch');
    
    // Default tasks.
    grunt.registerTask('build', [
        'copy',
        'less',
        'cssmin',
        'autoprefixer',
//        'babel',
        'uglify',
    ]);
    
    grunt.registerTask('clean-resource', [
        'clean'
    ]);
    
    grunt.registerTask('copy-test-integration-before', ['copy:test-integration-before']);
    grunt.registerTask('copy-test-integration-after', ['copy:test-integration-after']);
    grunt.registerTask('test-integration', [
        'copy-test-integration-before',
        'clean',
        'touch',
        'webdriver',
        'copy-test-integration-after',
    ]);
    
    grunt.registerTask('test', [
        'test-integration'
    ]);
};