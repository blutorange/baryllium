<?php
declare(strict_types=1);

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
namespace Moose\FormModel;

use Moose\Context\MooseConfig;
use Moose\Util\PlaceholderTranslator;
use Moose\Web\HttpRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of SiteSettingsMailFormModel
 *
 * @author madgaksha
 */
class SiteSettingsMailTestFormModel extends SiteSettingsMailFormModel {

    const MAP = [
            'testMailAddress' => 'testmail'
    ];
    
    /**
     * @var string
     * @Assert\NotBlank(message="settings.mail.testmail.blank")
     * @Assert\Email(message="settings.mail.testmail.invalid")
     */
    private $testMailAddress;

    protected  function __construct(HttpRequestInterface $request,
            PlaceholderTranslator $translator, array $fields) {
        parent::__construct($request, $translator, $fields);
    }
    
    public static function fromRequest(HttpRequestInterface $request,
            PlaceholderTranslator $translator) {
        return new SiteSettingsMailTestFormModel($request, $translator, \array_merge(parent::MAP, self::MAP));
    }
    
    protected static function setFromConfig($model,
            PlaceholderTranslator $translator, MooseConfig $config) {
        parent::setFromConfig($model, $translator, $config);
        $model->setTestMailAddress($config->getSystemMailAddress());
        return $model;
    }
    
    /**
     * @param HttpRequestInterface $request
     * @param PlaceholderTranslator $translator
     * @param MooseConfig $config
     * @return SiteSettingsMailTestFormModel
     */
    public static function fromConfig(HttpRequestInterface $request,
            PlaceholderTranslator $translator, MooseConfig $config) {
        $model = new SiteSettingsMailTestFormModel($request, $translator, \array_merge(parent::MAP, self::MAP));
        self::setFromConfig($model, $translator, $config);
        return $model;
    }
            
    public function getTestMailAddress() : string {
        return $this->testMailAddress;
    }

    public function setTestMailAddress(string $testMailAddress = null) {
        $this->testMailAddress = $testMailAddress ?? '';
        return $this;
    }
}
