<?php
declare(strict_types = 1);
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

namespace Moose\Extension\Opal;

use Moose\Log\Logger;
use Moose\Util\PlaceholderTranslator;
use Moose\Web\HttpBotInterface;
use Requests_Exception;

/**
 * The main interface for an authorization provider, eg. the BA Dresden.
 * @author madgaksha
 */
interface OpalAuthorizationProviderInterface {
    /**
     * <p>
     * Performs the authorization process. The HTTPBot passed to this method is the
     * in the state directly after the institution was selected and the login button
     * on the OPAL login page was pressed.
     * </p>
     * @param HttpBotInterface $bot The HTTP bot.
     * @return string The SAMLResponse.
     * @throws OpalAuthorizationException When authorization fails, eg. due
     * to wrong credentials or changes in how the web service works.
     * @throws Requests_Exception When the networks fails.
     */
    public function perform(HttpBotInterface $bot, Logger $logger);
    
    public function getNativeName() : string;
    public function getName(PlaceholderTranslator $translator) : string;
    
    /**
     * For selecting the correct institution on the login page.
     * The selection is an HTML select element with several options
     * with a value and a name. Each value-text pair is passed to this
     * method, which must decide to which it applies.
     * @param string $value The value of the option.
     * @param string $text The text of the option.
     * @return bool True iff the option applies to this authorization provider.
     */
    public function matches(string $value, string $text) : bool;
}