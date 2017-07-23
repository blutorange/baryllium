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

/**
 * Description of DataTable
 *
 * @author madgaksha
 */
class DataTable implements DataTableInterface, DataTableBuilderInterface {
    /** @var string */
    private $id;
    
    /** @var string */
    private $url;
    
    /** @var string */
    private $action;
    
    /** @var bool */
    private $isOrderable = true;
    
    /** @var bool */
    private $isSearchable = true;
    
    /** @var bool */
    private $isPaginable = true;
    
    /** @var int|null */
    private $initialOrderColumnIndex;
    
    /** @var bool */
    private $isInitialOrderAscending = true;
    
    /** @var int Milliseconds. */
    private $searchDelay = 1000;
    
    /** @var DataTableInterface[] */
    private $columns = [];
    
    /** @var array */
    private $handlers = [];
    
    /** @var bool */
    private $isCaptionI18n = true;
    
    /** @var string */
    private $caption;

    private function __construct(string $id, string $url = null, string $action = null) {
        $this->id = $id;
        $this->url = $url ?? '.';
        $this->action = $action ?? 'list';
    }
    
    public function getIsOrderable() : bool {
        return $this->isOrderable;
    }
    
    public function getIsSearchable() : bool {
        return $this->isSearchable;
    }
    
    public function getIsPaginable() : bool {
        return $this->isPaginable;
    }
    
    public function getUrl() : string {
        return $this->url;
    }
    
    public function getAction() : string {
        return $this->action;
    }
    
    public function getRowClickHandler() {
        return $this->handlers['rowClick'] ?? '';
    }
    
    public function getId() :string {
        return $this->id;
    }
    
    public function getSearchDelay() : int {
        return $this->searchDelay;
    }
    
    public function getInitialOrderColumnIndex() {
        return $this->initialOrderColumnIndex;
    }

    public function getIsInitialOrderAscending() : bool {
        return $this->isInitialOrderAscending;
    }
    
    public function & getColumns() : array {
        return $this->columns;
    }
    
    public function build() : DataTableInterface {
        $this->columns = \array_map(function($column){
            return ($column instanceof DataTableColumnBuilderInterface) ? $column->build() : $column;
        }, $this->columns);
        return $this;
    }
    
    public function addColumn($column): DataTableBuilderInterface {
        if ($column !== null) {
            $this->columns []= $column;
        }
        return $this;
    }
    
    public function setUrl(string $url) : DataTableBuilderInterface {
        $this->url = $url;
        return $this;
    }
    
    public function setSearchDelay(int $searchDebounce) : DataTableBuilderInterface {
        $this->searchDelay = $searchDebounce;
        return $this;
    }
    
    public function setRelativeUrl(string $url, Context $context = null) : DataTableBuilderInterface {
        if ($context === null) {
            $context = Context::getInstance();
        }
        return $this->setUrl($context->getServerPath($url));
    }

    public function setAction(string $action) : DataTableBuilderInterface {
        $this->action = $action;
        return $this;
    }

    public function setIsOrderable(bool $orderable): DataTableBuilderInterface {
        $this->isOrderable = $orderable;
        return $this;
    }
    
    public function setRowClickHandler(string $rowClickHandler) : DataTableBuilderInterface {
        $this->handlers['rowClick'] = $rowClickHandler;
        return $this;
    }

    public function setIsPaginable(bool $pagingable): DataTableBuilderInterface {
        $this->isPaginable = $pagingable;
        return $this;
    }

    public function setIsSearchable(bool $searchable): DataTableBuilderInterface {
        $this->isSearchable = $searchable;
        return $this;
    }
    
    public function setId($id) : DataTableBuilderInterface {
        $this->id = $id;
        return $this;
    }

    public function setInitialOrderColumnIndex(int $initialOrderColumnIndex = null) : DataTableBuilderInterface {
        $this->initialOrderColumnIndex = $initialOrderColumnIndex;
        return $this;
    }

    public function setIsInitialOrderAscending(bool $isInitialOrderAscending) : DataTableBuilderInterface {
        $this->isInitialOrderAscending = $isInitialOrderAscending;
        return $this;
    }

    public static function builder(string $id, string $url = null, string $action = null) : DataTableBuilderInterface {
        return new DataTable($id, $url, $action);
    }

    public function setCaption(string $caption): DataTableBuilderInterface {
        $this->caption = $caption;
        return $this;
    }

    public function setIsCaptionI18n(bool $captionI18n): DataTableBuilderInterface {
        $this->isCaptionI18n = $captionI18n;
        return $this;
    }

    public function getCaption() : string {
        return $this->caption;
    }

    public function getIsCaptionI18n(): bool {
        return $this->isCaptionI18n;
    }

    public function hasCaption(): bool {
        return $this->caption !== null;
    }

}