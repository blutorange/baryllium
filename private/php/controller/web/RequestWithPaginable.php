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

namespace Moose\Web;

use Moose\Util\CmnCnst;
use Moose\Util\MathUtil;

/**
 * For handlers handling a request specifying a \Entity\Course.
 * Courses are identified either by the course id <code>cid</code>
 * or the forum id <code>fid</code>.
 * @author madgaksha
 */
trait RequestWithPaginable {
    /**
     * @param HttpRequestInterface $request
     * @return int
     */
    public function retrieveCount(HttpRequestInterface $request) : int {
        $count = $this->getRequest()->getParamInt(CmnCnst::URL_PARAM_COUNT, 0, HttpRequest::PARAM_QUERY);
        if ($count === 0) {
            $count = $request->getCookieOption(CmnCnst::COOKIE_FIELDS, CmnCnst::COOKIE_OPTION_POST_COUNT, 0);
            if (!is_numeric($count)) {
                $count = 0;
            }
        }
        return MathUtil::max($count, CmnCnst::MIN_PAGINABLE_COUNT);
    }

    /**
     * @param HttpRequestInterface $request
     * @return int
     */
    public function retrieveOffset(HttpRequestInterface $request) : int {
        $offset = intval($request->getParamInt(CmnCnst::URL_PARAM_OFFSET, 0));
        return $offset < 0 ? 0 : $offset;
    }
    
    /**
     * @param HttpRequestInterface $request
     * @param array $allowedFields List of allowed fields. When null, all fields are allowed.
     * @return string Or null when not found.
     */
    public function retrieveSort(HttpRequestInterface $request, array $allowedFields = null) {
        $field = $request->getParam(CmnCnst::URL_PARAM_SORT, null);
        if ($allowedFields === null || \in_array($field, $allowedFields)) {
            return $field;
        }
        return null;
    }
    
    public function retrieveSortDirection(HttpRequestInterface $request) : bool {
        $ascending = $request->getParam(CmnCnst::URL_PARAM_SORTDIR, true);
        if (\is_string($ascending)) {
            $lower = \mb_convert_case($ascending, MB_CASE_LOWER);
            if ($lower === 'asc'  || $lower === 'true') {
                $ascending = true;
            }
            else if ($lower === 'desc' || $lower === 'false') {
                $ascending = false;
            }
        }
        return boolval($ascending);
    }
    
    public function & retrieveSearch(HttpRequestInterface $request, array $allowedFields = null) : array {
        $fields = $request->getParam(CmnCnst::URL_PARAM_SEARCH, []);
        foreach ($fields as $fieldName => $fieldValue) {
            if (\key_exists($fieldName, $allowedFields)) {
                $fields[$fieldName] = [
                    'op' => $allowedFields[$fieldName],
                    'val' => $fieldValue
                ];
            }
            else {
                unset($fields, $fieldName);
            }
        }
        return $fields;
    }
    
    public function retrieveAll(HttpRequestInterface $request, array $allowedFieldsSort = null, array $allowedFieldsSearch = null) : RequestWithPaginableData {
        $data = new RequestWithPaginableData();
        $data->count = $this->retrieveCount($request);
        $data->offset = $this->retrieveOffset($request);
        $data->sort = $this->retrieveSort($request, $allowedFieldsSort);
        $data->sortDirection = $this->retrieveSortDirection($request);
        $data->search = & $this->retrieveSearch($request, $allowedFieldsSearch ?? $allowedFieldsSort);
        return $data;
    }
}

class RequestWithPaginableData {
    /** @var int Default when not provided in the request. */
    public $count;
    /** @var int Defaults to 0 when not provided in the request. */
    public $offset;
    /** @var string|null */
    public $sort;
    /** @var string Either true for ascending, or false for descending. */
    public $sortDirection;
    /** @var array */
    public $search;
}
