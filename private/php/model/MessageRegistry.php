<?php

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

namespace Moose\ViewModel;

use Moose\Util\PlaceholderTranslator;

/**
 * <p>
 * A list of predefined messages. When a controller redirects to another page,
 * it cannot add any error or success messages. To avoid this issue, the
 * controller may add an URL parameter with a list of messages to be displayed.
 * A controller may also add the severity of the message. When it does not
 * </p><p>
 * Regarding the methods: There is one method in this class for each
 * method, called <code>makeXXX</code>, with <code>XXX</code> being some
 * arbitrary identifier. Multiple messages are separated as with a
 * <code>+</code> which is not a valid character for a PHP method name. The
 * severity is indicated by a semi-colon, eg.
 * <code>registerComplete:success,newsInvite:info</code>
 * Also, each method must return an object implementing MessageInterface.
 * </p>
 * @author madgaksha
 */
class MessageRegistry {
    public static function makeRegisterComplete(int $messageType, PlaceholderTranslator $translator) : MessageInterface {
        return Message::anyI18n($messageType, 'register.success', 'register.success.detail', $translator);
    }
    
    public static function makePwresetComplete(int $messageType, PlaceholderTranslator $translator) : MessageInterface {
        return Message::anyI18n($messageType, 'pwreset.success', 'pwreset.success.details', $translator);
    }
    
    public static function makeLoginRequired(int $messageType, PlaceholderTranslator $translator) : MessageInterface {
        return Message::anyI18n($messageType, 'page.login.required', 'page.login.required.details', $translator);
    }
    
    public static function makeLoginRequiredSadmin(int $messageType, PlaceholderTranslator $translator) : MessageInterface {
        return Message::anyI18n($messageType, 'page.login.required.sadmin', 'page.login.required.sadmin.details', $translator);
    }
    
    public static function makeRememberFailure(int $messageType, PlaceholderTranslator $translator) : MessageInterface {
        return Message::anyI18n($messageType, 'login.remember.failure', 'login.remember.failure.details', $translator);
    }
    
    public static function makeRememberSadmin(int $messageType, PlaceholderTranslator $translator) : MessageInterface {
        return Message::anyI18n($messageType, 'login.remember.sadmin', 'login.remember.sadmin.details', $translator);
    }
}