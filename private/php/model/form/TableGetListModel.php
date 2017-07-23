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

namespace Moose\FormModel;

use Moose\Util\CmnCnst;
use Moose\Util\PlaceholderTranslator;
use Moose\Web\HttpRequest;
use Moose\Web\HttpRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of UniversityGetListModel
 *
 * @author madgaksha
 */
class TableGetListModel extends AbstractFormModel {
    
    const MAP = [
        'count'         => [CmnCnst::URL_PARAM_COUNT, 10, 'int'],
        'offset'        => [CmnCnst::URL_PARAM_OFFSET, 0, 'int'],
        'sortDirection' => [CmnCnst::URL_PARAM_SORTDIR, 'asc'],
        'sort'          => CmnCnst::URL_PARAM_SORT,
        'search'        => [CmnCnst::URL_PARAM_SEARCH, [], 'array']
    ];
    
    /**
     * @Assert\Range(min = 0, minMessage="servlet.university.count.null")
     * @Assert\NotNull(message="servlet.university.count.null")
     * @var int
     */
    private $count;
    
    /**
     * @Assert\NotNull(message="servlet.university.count.null")
     * @var int
     */
    private $sort;
    
    /**
     * @Assert\NotNull(message="servlet.university.count.null")
     * @var int
     */
    private $search;

    /**
     * @Assert\Range(min = 0, minMessage="servlet.university.offset.null")
     * @Assert\NotNull(message="servlet.university.offset.null")
     * @var int
     */
    private $offset;
    
    /**
     * @Assert\Choice(cohices={"asc", "dsc"}, message="servlet.university.direction.invalid", strict=true)
     * @Assert\NotBlank(message="servlet.university.direction.null")
     * @var string
     */
    private $sortDirection;
    
    protected function __construct(HttpRequestInterface $request,
            PlaceholderTranslator $translator, array $fields, int $fromWhere) {
        parent::__construct($request, $translator, $fields, $fromWhere);
    }

    public static function fromRequest(HttpRequestInterface $request,
            PlaceholderTranslator $translator, array $allowedFieldsSort = null,
            array $allowedFieldsSearch = null,
            int $fromWhere = HttpRequest::PARAM_FORM): TableGetListModel {
        $allowedFieldsSort = $allowedFieldsSort ?? [];
        $allowedFieldsSearch = $allowedFieldsSearch ?? $allowedFieldsSort;
        $model = new TableGetListModel($request, $translator, self::MAP, $fromWhere);
        $model->filterAllowedFields($allowedFieldsSort, $allowedFieldsSearch);
        return $model;
    }
    
    protected function getGroups(): array {
        return [];
    }
    
    public function getCount() : int {
        return $this->count;
    }

    public function getOffset() : int {
        return $this->offset;
    }
    
    /**
     * @return string|null
     */
    public function getSort() {
        return $this->sort;
    }

    public function getSearch() : array {
        return $this->search;
    }

    public function getIsAscending() : bool {
        return $this->sortDirection === 'asc';
    }
    
    public function getIsDescending() : bool {
        return $this->sortDirection === 'dsc';
    }

    protected function setCount(int $count) {
        $this->count = $count;
        return $this;
    }

    protected function setSort(string $sort = null) {
        $this->sort = $sort;
        return $this;
    }

    protected function setSearch(array $search) {
        $this->search = $search;
        return $this;
    }

    protected function setOffset(int $offset) {
        $this->offset = $offset;
        return $this;
    }

    protected function setSortDirection(string $sortDirection) {
        $this->sortDirection = $sortDirection;
        return $this;
    }
    
    private function filterAllowedFields(array $allowedFieldsSort, array $allowedFieldsSearch) {
        if (!\in_array($this->sort, $allowedFieldsSort)) {
            $this->sort = null;
        }
        foreach ($this->search as $fieldName => $fieldValue) {
            if (\key_exists($fieldName, $allowedFieldsSearch)) {
                $this->search[$fieldName] = [
                    'op' => $allowedFieldsSearch[$fieldName],
                    'val' => $fieldValue
                ];
            }
            else {
                unset($this->search, $fieldName);
            }
        }
    }
}