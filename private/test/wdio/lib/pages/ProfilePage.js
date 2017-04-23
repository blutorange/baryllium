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

var ProfilePage = Object.create(Page, {
    /**
     * Define elements
     */
    lnkTabUser: {get:function(){return Page.element.call(this, '#tab-user');}},
    lnkTabSettings: {get:function(){return Page.element.call(this, '#tab_settings');}},
    lnkTabNews: {get:function(){return Page.element.call(this, '#tab_news');}},
    
    txtRealName: {get:function(){return Page.element.call(this, '.profile .profile-name');}},
    txtStudentId: {get:function(){return Page.element.call(this, '.profile .profile-sid');}},
    txtFieldOfStudy: {get:function(){return Page.element.call(this, '.profile .profile-fos');}},
    txtTutorialGroup: {get:function(){return Page.element.call(this, '.profile .profile-tutgroup');}},
    txtMailAddress: {get:function(){return Page.element.call(this, '.profile .profile-mail');}},
    txtPostCount: {get:function(){return Page.element.call(this, '.profile .profile-postcount');}},
    
    imgAvatar: {get:function(){return Page.element.call(this, '.profile-avatar-area .avatar-img');}},
    
    formUser: {get:function(){return Page.element.call(this, '#user_profile_form');}},
    uplAvatar: {get:function(){return this.formUser.element('[name=avatar]');}},
    btnUserSubmit: {get:function(){return this.formUser.element('[name=btnSubmit]');}},
    btnChangeAvatar: {get:function(){return Page.element.call(this, '#btn_change_avatar');}},
    
    cbPagingType: {get:function(){return Page.element.call(this, '[name=option.paging.list]');}},
    tfPagingCount: {get:function(){return Page.element.call(this, '[name=option.post.count]');}},
    selEditMode: {get:function(){return Page.element.call(this, '[name=option.edit.mode]');}},
        

    /**
     * Define or overwrite page methods
     */
    open: {
        value: function(forumId) {
            if (arguments.length > 0)
                Page.open.call(this, 'public/controller/forum.php?fid=' + forumId);
            else
                Page.open.call(this, 'public/controller/forum.php');
            expect(this.lnkTabUser.soon).to.exist;
        }
    },

    threadByName: {
        value: function(name) {
            return lnkListThread.now.find(function(thread) {
                return this.threadName(thread) === name;
            });
        }
    },
    
    threadPostCount: {
        value: function(threadLink) {
            return Number(threadLink.now.element('.badge').getText());
        }        
    },
    
    threadName: {
        value: function(threadLink) {
            return threadLink.now.element('.thread').getText();
        }
    },
    
    submiNewThread: {
        value: function() {
            this.formNewThread.now.submitForm();
        }
    }
});

module.exports = ProfilePage;