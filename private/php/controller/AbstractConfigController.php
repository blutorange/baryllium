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

namespace Moose\Controller;

use Doctrine\DBAL\Types\ProtectedString;
use Moose\Context\MooseConfig;
use Moose\Context\StaticKeyProvider;
use Moose\Util\CmnCnst;
use Moose\Web\HttpResponse;
use Moose\Web\RequestException;
use Nette\Mail\Message;
use Throwable;

/**
 * Description of AbstractSiteSettingsController
 *
 * @author madgaksha
 */
abstract class AbstractConfigController extends BaseController {
    protected function saveConfiguration(string $path = null, MooseConfig $conf = null) : array {
        $conf = $conf ?? $this->getContext()->getConfiguration();
        $path = $path ?? $conf->getOriginalFile();
        try {
            if ($conf->isNotEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION)) {
                $conf->saveAs($path, true, false);
            }
            else {
                $conf->saveAs($path, false, true);
            }
        }
        catch (Throwable $e) {
            return [Message::dangerI18n('settings.config.save.failed', $e->getMessage(), $$this->getTranslator())];
        }
        $isProd = Context::getInstance()->getConfiguration->isEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION);
        Context::getInstance()->getCache()->save(CmnCnst::CACHE_MOOSE_CONFIGURATION, $conf->convertToArray(true, $isProd, true));
        return [];
    }
}
