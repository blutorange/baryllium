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

use Moose\Context\Context;
use Moose\Util\DebugUtil;
use Moose\Util\PlaceholderTranslator;

/**
 * A button, used for dialog etc.
 *
 * @author madgaksha
 */
class BaseButton implements ButtonInterface, ButtonBuilderInterface {
    /** @var string */
    private $label;
    
    /** @var string */
    private $id;    
    
    /** @var string */
    private $partialId;

    /** @var integer */
    private $type;
    
    /** @var array */
    private $dataOnClick;
    
    /** @var string[] */
    private $htmlAttributes;
    
    /** @var string */
    private $htmlClasses;
    
    /** @var string */
    private $link;
    
    /** @var string */
    private $title;
    
    /** @var string */
    private $glyphicon;
    
    /** @var bool */
    private $hasCallbackOnClick;

    /** @var string */
    private $htmlType;

    private static $UNIQUE_COUNTER = 0;

    private function __construct(string $id) {
        $this->id = $id;
        $this->partialId = (string)++self::$UNIQUE_COUNTER;
        $this->dataOnClick = [];
        $this->htmlAttributes = [];
        $this->htmlClasses = '';
        $this->hasCallbackOnClick = false;
        $this->htmlType = 'button';
        $this->type = ButtonInterface::TYPE_DEFAULT;
    }

    public function __toString() {
        $d = \implode(';', $this->dataOnClick);
        $c = $this->getBootstrapClass();
        return "Button($c,$this->id,$this->label,$d)";
    }
    
    public final function getData() {
        return $this->dataOnClick;
    }
    
    public final function getLabel() {
        return $this->label;
    }
    
    public final function getId(): string {
        return $this->id;
    }
    
    public final function getPartialId(): string {
        return $this->partialId;
    }
    
    public function getHtmlAttributes(): array {
        return $this->htmlAttributes;
    }

    public final function getBootstrapClass() : string {
        switch ($this->type) {
            case self::TYPE_DEFAULT;
                return 'btn-default';
            case self::TYPE_PRIMARY:
                return 'btn-primary';
            case self::TYPE_SUCCESS:
                return 'btn-success';
            case self::TYPE_INFO:
                return 'btn-info';
            case self::TYPE_WARNING:
                return 'btn-warning';
            case self::TYPE_DANGER:
                return 'btn-danger';
            case self::TYPE_LINK:
                return 'btn-link';
            default:
                DebugUtil::log("Unknown button type $this->type.");
                return 'btn-default';
        }
    }
    
    public final function getType() : int {
        return $this->type;
    }
    
    public final function getCallbackOnClickData(): array {
        return $this->dataOnClick;
    }

    public final function getLink() {
        return $this->link;
    }

    public final function hasCallbackOnClick(): bool {
        return $this->hasCallbackOnClick;
    }
    
    public final function getGlyphicon() {
        return $this->glyphicon;
    }

    public final function getTitle() {
        return $this->title;
    }
    
    public static function createBuilder(string $id) : ButtonBuilderInterface {
        return new BaseButton($id);
    }

    public function addCallbackOnClickData(string $key, string $value): ButtonBuilderInterface {
        $this->dataOnClick[$key] =  $value;
        return $this;
    }

    public function addHtmlAttribute(string $attributeName,
            string $attributeValue = null): ButtonBuilderInterface {
        $this->htmlAttributes[$attributeName] = $attributeValue;
        return $this;
    }

    public function build(): ButtonInterface {
        return $this;
    }

    public function setHasCallbackOnClick(bool $hasCallback): ButtonBuilderInterface {
        $this->hasCallbackOnClick = $hasCallback;
        return $this;
    }

    public function setId(string $id): ButtonBuilderInterface {
        $this->id = $id;
        return $this;
    }

    public function setLabel(string $label): ButtonBuilderInterface {
        $this->label = $label;
        return $this;
    }

    public function setLink(string $link = null) : ButtonBuilderInterface {
        $this->link = $link;
        return $this;
    }

    public function setType(int $buttonType): ButtonBuilderInterface {
        $this->type = $buttonType;
        return $this;
    }

    public function setTitle(string $title = null): ButtonBuilderInterface {
        $this->title = $title ?? '';
        return $this;        
    }

    public function setGlyphicon(string $glyphicon = null): ButtonBuilderInterface {
        $this->glyphicon = $glyphicon;
        return $this;
    }

    public function setLabelI18n(string $i18nKey, array $vars = null,
            PlaceholderTranslator $translator = null): ButtonBuilderInterface {
        $translator = $translator ?? Context::getInstance()->getSessionHandler()->getTranslator();
        $label = $translator->gettextVar($i18nKey ?? 'button.dialog.close', $vars);
        $this->setLabel($label);
        return $this;
    }

    public function setTitleI18n(string $i18nKey, array $vars = null,
            PlaceholderTranslator $translator = null): ButtonBuilderInterface {
        $translator = $translator ?? Context::getInstance()->getSessionHandler()->getTranslator();
        $title = $translator->gettextVar($i18nKey ?? 'button.dialog.close', $vars);
        $this->setTitle($title);
        return $this;
    }

    public function setPartialId(string $partialId): ButtonBuilderInterface {
        $this->partialId = $partialId;
    }

    public function addHtmlClass(string $class): ButtonBuilderInterface {
        $this->htmlClasses .= "$class ";
        return $this;
    }

    public function getHtmlClasses(): string {
        return $this->htmlClasses;
    }

    public function getHtmlType(): string {
        return $this->htmlType;
    }

    public function setHtmlType(string $htmlType): ButtonBuilderInterface {
        $this->htmlType = $htmlType;
        return $this;
    }

    public function hide(): ButtonBuilderInterface {
        $this->addHtmlAttribute('style', 'display:none;');
        return $this;
    }

}