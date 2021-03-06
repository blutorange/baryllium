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

var BoardPage = Object.create(Page, {
    /**
     * Define elements
     */
    lnkListForum: {get:function(){return Page.elements.call(this, '#forumlist_wrapper .cardlist-link');}},

    divWrapperForum: {get:function(){return Page.elements.call(this, '#forumlist_wrapper');}},

    /**
     * Define or overwrite page methods
     */
    open: {
        value: function() {
            Page.open.call(this, 'public/controller/board.php');
            expect(this.divWrapperForum.soon).to.exist;
        }
    },
    
    forumBy: {
        value: function(something) {
            if (typeof(something) === 'number') return this.forumByIndex(something);
            if (typeof(something) === 'string') return this.forumByName(something);
            return something;
        }
    },
    
    forumByName: {
        value: function(name) {
            var self = this;
            return this.lnkListForum.now.find(function(forum) {
                return self.forumName(forum) === name;
            });
        }
    },
    
    forumByIndex: {
        value: function(index) {
            var list = this.lnkListForum.now;
            return index < list.length ? list[index] : undefined;
        }
    },
    
    forumPostCount: {
        value: function(forum) {
            var f = this.forumBy(forum);
            if (!f) return undefined;
            return Number(f.element('.badge').getText());
        }        
    },
    
    forumName: {
        value: function(forum) {
            var f = this.forumBy(forum);
            if (!f) return undefined;
            return this.forumBy(forum).element('.cardlist-text').getText();
        }
    },
});

module.exports = BoardPage;