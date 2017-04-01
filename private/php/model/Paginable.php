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

use ArrayIterator;

/**
 * @author madgaksha
 */
class Paginable implements PaginableInterface {
    private $paginableUrlPattern;
    private $paginablePageCount;
    private $paginablePageCurrent;
    private $paginableEntriesPerPage;
    private $paginablePages;
    /** @var array */
    private $paginableEntryList;
    private $paginableEntryListIterator;

    public function __construct(string $urlPattern, int $entriesPerPage, int $pageCount, int $pageCurrent, array & $entryList) {
        $this->paginableUrlPattern = $urlPattern;
        $this->paginablePageCount = $pageCount;
        $this->paginablePageCurrent = $pageCurrent;
        $this->paginableEntriesPerPage = $entriesPerPage;
        $this->paginableEntryList = &$entryList;
        $this->paginableEntryListIterator = new ArrayIterator($entryList, ArrayIterator::STD_PROP_LIST);
    }
       
    public function hasPaginablePrevious() : bool {
        return $this->paginablePageCount > 1 && $this->paginablePageCurrent > 1;
    }
    
    public function hasPaginableNext() : bool {
        return $this->paginablePageCount > 1 && $this->paginablePageCurrent < $this->paginablePageCount;
    }
   
    public function getPaginableFirstEntryOrdinal() {
        return $this->paginableEntriesPerPage*($this->paginablePageCurrent-1)+1;
    }
    
    public function getPaginableCurrentPage() {
        return $this->paginablePageCurrent;
    }
    public function getPaginableEntriesPerPage() {
        return $this->paginableEntriesPerPage;
    }
    public function getPaginablePageCount() {
        return $this->paginablePageCount;
    }
    public function getPaginableUrlPattern() {
        return $this->paginableUrlPattern;
    }
    
    public function getPaginablePage(int $page) {
        return \strtr($this->getPaginableUrlPattern(), [
            '{%offset%}' => (string)(($page-1) * $this->getPaginableEntriesPerPage()),
            '{%count%}' => (string)($this->getPaginableEntriesPerPage()),
        ]);        
    }
    
    public function getPaginablePages(int $left=-1, int $right=-1, int $first = 0, int $last=0) : array {
        if ($this->paginablePages === null) { 
            $pages = [];
            for ($page = 1; $page <= $this->getPaginablePageCount(); ++$page) {
                if ($left < 0  || $page < $this->pageCurrent() && $this->pageCurrent() - $page <= $left) {
                    $pages[$page] = $this->getPaginablePage($page);
                }
                else if ($right < 0 || $page > $this->pageCurrent() && $page - $this->pageCurrent() <= $right) {
                    $pages[$page] = $this->getPaginablePage($page);                    
                }
                elseif ($page <= $first || $page + $last > $this->getPaginablePageCount() ) {
                    $pages[$page] = $this->getPaginablePage($page);
                }
            }
            $this->paginablePages = $pages;
       }
       return $this->paginablePages;
    }

    public static function fromOffsetAndCount(string $urlPattern, int $entriesCount, int $offset, int $count, array & $entryList) {
        $count = $count < 1 ? 1 : $count;
        $offset = $offset < 0 ? 0 : $offset;
        $entriesCount = $entriesCount < 0 ? 0 : $entriesCount;
        $entriesPerPage = $count;
        $pageCount = \intdiv($entriesCount,$count)+($entriesCount%$count === 0 ? 0 : 1);
        $pageCurrent = \intdiv($offset,$count)+1;       
        if (\sizeof($entryList) > $entriesPerPage) {
            $entryList = array_slice($entryList, 0, $entriesPerPage, false);
        }
        return new Paginable($urlPattern, $entriesPerPage, $pageCount, $pageCurrent, $entryList);
   }
    
    public static function ofEmpty() {
        $entries = [];
        return new Paginable('', 0, 0, 1, $entries);
    }

    public function getPaginableCurrentEntries(): array {
        return $this->paginableEntryList;
    }

    public function current() {
        return $this->paginableEntryListIterator->current();
    }

    public function key() {
        return $this->paginableEntryListIterator->key();
    }

    public function next() {
        $this->paginableEntryListIterator->next();
    }

    public function rewind() {
        $this->paginableEntryListIterator->rewind();
    }

    public function valid(): bool {
        return $this->paginableEntryListIterator->valid();
    }

    public function count(): int {
        return $this->paginableEntryListIterator->count();
    }

    public function offsetExists($offset): bool {
        return $offset >= 0 && $offset < sizeof($this->paginableEntryList);
    }

    public function offsetGet($offset) {
        return $this->paginableEntryList[$offset];
    }

    public function offsetSet($offset, $value): void {
        $this->paginableEntryList[$offset] = $value;
    }

    public function offsetUnset($offset): void {
        unset($this->paginableEntryList[$offset]);
    }
}
