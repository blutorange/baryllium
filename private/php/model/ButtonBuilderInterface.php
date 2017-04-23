<?php

/* Note: This license has also been called the "New BSD License" or "Modified
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
 * A button, used for dialog etc.
 *
 * @author madgaksha
 */
interface ButtonBuilderInterface {
    public function setId(string $id) : ButtonBuilderInterface;
    
    public function setLabel(string $label) : ButtonBuilderInterface;
    
    public function setLabelI18n(string $link, array $vars = null, PlaceholderTranslator $translator = null) : ButtonBuilderInterface;
        
    public function setType(int $buttonType) : ButtonBuilderInterface;
    
    public function setHasCallbackOnClick(bool $hasCallback) : ButtonBuilderInterface;
    
    public function addHtmlAttribute(string $attributeName, string $attributeValue) : ButtonBuilderInterface;
    
    public function addHtmlClass(string $class) : ButtonBuilderInterface;
        
    public function addCallbackOnClickData(string $key, string $value) : ButtonBuilderInterface;
    
    public function setLink(string $link = null) : ButtonBuilderInterface;
       
    public function setTitle(string $title = null) : ButtonBuilderInterface;
    
    public function setTitleI18n(string $i18nKey, array $vars = null, PlaceholderTranslator $translator = null) : ButtonBuilderInterface;
    
    public function setGlyphicon(string $glyphicon = null) : ButtonBuilderInterface;
    
    public function setPartialId(string $partialId) : ButtonBuilderInterface;
    
    public function build() : ButtonInterface;
}