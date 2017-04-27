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

use Moose\Context\Context;
use Moose\Util\PlaceholderTranslator;

/**
 * @author madgaksha
 */
class DataTableColumn implements DataTableColumnInterface, DataTableColumnBuilderInterface {

    /** @var string */
    private $id;
    
    /** @var string */
    private $name;
    
    /** @var string */
    private $title = '';
    
    /** @var string */
    private $type = self::TYPE_TEXT;
    
    /** @var string|null */
    private $searchTemplate = null;
    
    /** @var array|null */
    private $searchTemplateData = null;
    
    /** @var bool */
    private $isOrderable = false;

    /** @var bool */
    private $isVisible = true;

    /** @var int */    
    private $responsivePriority = self::PRIORITY_MEDIUM;

    /** @var string[] */
    private $cellClasses = [];

    private function __construct(string $name, string $id = null) {
        $this->name = $name;
        $this->id = $id ?? $name;
    }
    
    public function getName() : string {
        return $this->name;
    }
    
    public function getTitle() : string {
        return $this->title;
    }
    
    public function getType() : string {
        return $this->type;
    }
    
    public function getIsOrderable() : bool {
        return $this->isOrderable;
    }

    public function getSearchTemplate() {
        return $this->searchTemplate;
    }
    
    public function getSearchTemplateData() : array {
        return $this->searchTemplateData ?? [];
    }
    
    public function getResponsivePriority() : int {
        return $this->responsivePriority;
    }
    
    public function getIsVisible() : bool {
        return $this->isVisible;
    }
    
    public function & getCellClasses() : array {
        return $this->cellClasses;
    }
    
    public function addCellClass(string $class): DataTableColumnBuilderInterface {
        $this->cellClasses []= $class;
        return $this;
    }

    public function setName(string $name): DataTableColumnBuilderInterface {
        $this->name = $name;
        return $this;
    }

    public function setTitle(string $title): DataTableColumnBuilderInterface {
        $this->title = $title;
        return $this;
    }
    
    public function setType(string $type) : DataTableColumnBuilderInterface {
        $this->type = $type;
        return $this;
    }

    public function setIsOrderable(bool $isOrderable) : DataTableColumnBuilderInterface {
        $this->isOrderable = $isOrderable;
        return $this;
    }

    public function setSearchTemplate(string $searchTemplate, array $searchTemplateData = null) : DataTableColumnBuilderInterface {
        $this->searchTemplate = $searchTemplate;
        $this->searchTemplateData = $searchTemplateData;
        return $this;
    }
    
    public function setIsVisible(bool $isVisible) : DataTableColumnBuilderInterface {
        $this->isVisible = $isVisible ?? false;
        return $this;
    }

    public function setResponsivePriority(int $responsivePriority) : DataTableColumnBuilderInterface {
        $this->responsivePriority = $responsivePriority ?? 1;
        return $this;
    }
       
    public function setTitleI18n(string $key, array $vars = null, PlaceholderTranslator $translator = null): DataTableColumnBuilderInterface {
        if ($translator === null) {
            $translator = Context::getInstance()->getSessionHandler()->getTranslator();
        }
        return $this->setTitle($translator->gettextVar($key, $vars));
    }
    
    public function search() : DataTableColumnBuilderInterface {
        return $this->setSearchTemplate(self::SEARCH_TEXT);
    }
    
    public function order() : DataTableColumnBuilderInterface {
        return $this->setIsOrderable(true);
    }
    
    public function title(string $i18nKey) : DataTableColumnBuilderInterface {
        return $this->setTitleI18n($i18nKey);
    }
    
    public function image() : DataTableColumnBuilderInterface {
        return $this->setType(self::TYPE_IMAGE);
    }
    
    public function text() : DataTableColumnBuilderInterface {
        return $this->setType(self::TYPE_TEXT);
    }
    
    public function html() : DataTableColumnBuilderInterface {
        return $this->setType(self::TYPE_HTML);
    }
    
    public function badge() : DataTableColumnBuilderInterface {
        return $this->setType(self::TYPE_BADGE);
    }
    
    public function date() : DataTableColumnBuilderInterface {
        return $this->setType(self::TYPE_DATE);
    }

    public function low(int $adjustment) : DataTableColumnBuilderInterface {
        return $this->setResponsivePriority(self::PRIORITY_LOW+$adjustment);
    }
    
    public function medium(int $adjustment) : DataTableColumnBuilderInterface {
        return $this->setResponsivePriority(self::PRIORITY_MEDIUM+$adjustment);
    }
    
    public function high(int $adjustment) : DataTableColumnBuilderInterface {
        return $this->setResponsivePriority(self::PRIORITY_HIGH+$adjustment);
    }
    
    public function hide() : DataTableColumnBuilderInterface {
        return $this->setIsVisible(false);
    }

    public function build(): DataTableColumnInterface {
        return $this;
    }
    
    public static function builder(string $name, string $id = null) : DataTableColumnBuilderInterface {
        return new DataTableColumn($name, $id);
    }
    
    public static function basic(string $name, string $titleI18n, string $id = null) : DataTableColumnInterface {
        return static::builder($name, $id)->setTitleI18n($titleI18n)->build();
    }
}