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

var Page = require('./Page')

var ForumPage = Object.create(Page, {
    /**
     * Define elements
     */
    lnkListThread: {get:function(){return Page.elements.call(this, '.forum-thread');}},
    formNewThread: {get:function(){return Page.element.call(this, '#new_thread_form');}},
    tfThreadTitle: {get:function(){return Page.element.call(this, '[name=title]');}},
    edPostContent: {get:function(){return Page.element.call(this, '[name=content]');}},

    /**
     * Define or overwrite page methods
     */
    open: {
        value: function(forumId) {
            if (arguments.length > 0)
                Page.open.call(this, 'public/controller/forum.php?fid=' + forumId);
            else
                Page.open.call(this, 'public/controller/forum.php');
        }
    },

    threadByName: {
        value: function(name) {
            return this.lnkListThread.now.find(function(thread) {
                return this.threadName(thread) === name;
            });
        }
    },
    
    threadPostCount: {
        value: function(threadLink) {
            return Number(threadLink.element('.badge').getText());
        }        
    },
    
    threadName: {
        value: function(threadLink) {
            return threadLink.element('.thread').getText();
        }
    },
    
    submitNewThread: {
        value: function() {
            this.formNewThread.submitForm();
        }
    }
});

module.exports = ForumPage;