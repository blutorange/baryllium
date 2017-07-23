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
 * For setting the options of a DataTableColumnInterface.
 * @author madgaksha
 */
interface DataTableColumnBuilderInterface extends DataTableColumnInterface {
    public function setName(string $name) : DataTableColumnBuilderInterface;
    public function setTitle(string $title) : DataTableColumnBuilderInterface;
    public function setTitleI18n(string $key, array $vars = null, PlaceholderTranslator $translator = null) : DataTableColumnBuilderInterface;
    public function addCellClass(string $class) : DataTableColumnBuilderInterface;
    public function setIsOrderable(bool $orderable) : DataTableColumnBuilderInterface;
    public function setIsVisible(bool $visible) : DataTableColumnBuilderInterface;

    public function setSearchTemplate(string $searchTemplate, array $searchTemplateData = null) : DataTableColumnBuilderInterface;
    
    /** @return int A column with a greater priority (eg 2) will be removed from the display before a column with a lower priority (eg 1). */
    public function setResponsivePriority(int $responsivePriority) : DataTableColumnBuilderInterface;
    
    /**
     * @param string $type The type of data. Use one of the constants defined
     * in DataTableColumnInterface.
     */
    public function setType(string $type) : DataTableColumnBuilderInterface;

    public function build() : DataTableColumnInterface;
    
    // Shortcuts
    public function image() : DataTableColumnBuilderInterface;
    public function text() : DataTableColumnBuilderInterface;
    public function html() : DataTableColumnBuilderInterface;
    public function badge() : DataTableColumnBuilderInterface;
    public function date() : DataTableColumnBuilderInterface;
    /** Same as setSearchableTemplate(self::SEARCHABLE_TEXT) */
    public function search() : DataTableColumnBuilderInterface;
    /** Same as setIsOrderable(true) */
    public function order() : DataTableColumnBuilderInterface;
    /** Same as setTitleI18n($i18nKey) */
    public function title(string $i18nKey) : DataTableColumnBuilderInterface;
    /** Same as setResponsivePriority(PRIORITY_LOW) */
    public function low(int $adjustment = 0) : DataTableColumnBuilderInterface;
    /** Same as setResponsivePriority(PRIORITY_MEDIUM) */
    public function medium(int $adjustment = 0) : DataTableColumnBuilderInterface;
    /** Same as setResponsivePriority(PRIORITY_HIGH) */
    public function high(int $adjustment = 0) : DataTableColumnBuilderInterface;
    /** Same as setIsVisible(false) */
    public function hide() : DataTableColumnBuilderInterface;
}
