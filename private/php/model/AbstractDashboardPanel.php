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

/**
 * Base class for all dashboard panels with common functionality.
 *
 * @author mad_gaksha
 */
abstract class AbstractDashboardPanel implements DashboardPanelInterface {
    /** @var string */
    private $label;
    
    /** @var string */
    private $template;
    
    /** @var string */
    private $clazz;
    
    /** @var array */
    private $data;
    
    /** @var string[] */
    private $htmlData;

    protected function __construct(string $clazz, string $template, string $label) {
        $this->label = $label;
        $this->template = $template;
        $this->clazz = $clazz;
        $this->htmlData = [];
    }
    
    public function addHtmlData(string $key, string $value) : DashboardPanelInterface {
        $this->htmlData[$key]= $value;
        return $this;
    }
    
    public function getLabel(): string {
        return $this->label;
    }
    
    public function getHtmlData(): array {
        return $this->htmlData;
    }
    
    public function getClass() : string {
        return $this->clazz;
    }

    public function getTemplate(): string {
        return $this->template;
    }
    
    public function addData(string $key, $value) : DashboardPanelInterface {
        $this->data[$key] = $value;
        return $this;
    }

    public final function getData() : array {
        return \array_merge($this->getAdditionalData(), $this->data);
    }
    
    public function wantsDisplay(): bool {
        return true;
    }

    protected abstract function & getAdditionalData() : array ;
}