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

var syncRequest = require('sync-request');

function Seed(){}

Seed.prototype.growEmpty = function(baseURL) {
    this.grow(baseURL, {
        Schema : {
            Drop: [],
            Update: [true]
        }
    });
};

Seed.prototype.growBasic = function(baseURL) {
    this.grow(baseURL, {
        Schema : {
            Drop: [],
            Update: [true]
        },
        University: {
            BaDresden: []
        },
        ScheduledEvent : {
            ExpireTokenPurge: [],
            DiningHallMenuFetch : ['Moose\Extension\DiningHall\MensaJohannstadtLoader'],
            MailSend: []
        },
        'FieldOfStudy:1' : {
            Informationstechnologie: [],
            Medieninformatik: []
        },
        TutorialGroup : {
            Seed: [],
            Random: []
        },
        Course : {
            Seed: [],
            Random : [25]
        },
        'FieldOfStudy:2' : {
            Seed: [],
            AddRandomCourses : [1]
        },
        User : {
            Admin: [],
            Seed: [],
            Random : [20, 'password']
        },
        Thread : {
            Seed: [],
            Random : [50]
        },
        Post : {
            Seed: [],
            Random : [100]
        }
    });
};

Seed.prototype.grow = function(baseURL, data) {
    if (typeof(baseURL) !== 'string')
        throw new TypeError('No base url given.');
    var res = syncRequest('POST', baseURL + '/public/servlet/seed.php', {
        timeout: 60000,
        cache: false,
        json: data
    });
    var responseJSON = JSON.parse(res.getBody('utf8'));
    if (res.statusCode !== 200)
        throw new Error('Server returned ' + res.statusCode);
    if (responseJSON.error) 
        throw new Error('Seed request unsuccessful: ' + responseJSON.error.message + '(' + responseJSON.error.details + ')');                
};

module.exports = new Seed();