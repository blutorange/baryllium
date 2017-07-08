<?php
declare(strict_types = 1);

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

use Moose\Log\Logger;
use Symfony\Component\DomCrawler\Crawler;
use const MB_CASE_UPPER;

/**
 * Implements some convenience methods required by the HttpBotInterface.
 * @author madgaksha
 */
trait HttpBotDomTrait
{
    public function selectOne(string $selector, $callback): HttpBotInterface {
        return $this->selectMulti($selector, $callback, 1);
    }

    public function selectOneDom(string $selector, $callback): HttpBotInterface {
        return $this->selectMultiDom($selector, $callback, 1);
    }

    public function selectMultiDom(string $selector, $callback, int $expectedCount = -1): HttpBotInterface {
        return $this->selectMulti($selector, function (Crawler $crawler) use ($callback) {
            /* @var $crawler Crawler */
            $nodes = [];
            for ($i = 0; $i < $crawler->count(); ++$i) {
                $nodes [] = $crawler->getNode($i);
            }
            return \call_user_func($callback, $nodes);
        }, $expectedCount);
    }

    public function submitForm(string $selector,
                               string $submitButtonSelector = null, array $values = [],
                               array $headers = [], array $options = []): HttpBotInterface {
        $this
            ->selectOne($selector, function (Crawler $form) use ($values, $submitButtonSelector) : array {
                /* @var $form Crawler */
                $method = \mb_convert_case($form->attr('method'), MB_CASE_UPPER);
                $htmlForm = $form->form($values);
                $values = $htmlForm->getPhpValues();
                if ($submitButtonSelector !== null) {
                    $submitButton = $form->filter($submitButtonSelector);
                    if ($submitButton->count() !== 1) {
                        $this->getLogger()->log($submitButtonSelector, 'Cannot submit form, submit button not found', Logger::LEVEL_ERROR);
                        throw new HttpBotException("Cannot submit form, submit button $submitButtonSelector not found.");
                    }
                    $submitButtonName = $submitButton->attr('name');
                    if (!empty($submitButtonName)) {
                        $values[$submitButton->attr('name')] = $submitButton->attr('value') ?? '';
                    } else {
                        $this->getLogger()->log($submitButtonSelector, "Cannot submit submit button, it has got no name", Logger::LEVEL_WARNING);
                    }
                }
                return [
                    'action' => $htmlForm->getUri(),
                    'method' => $method,
                    // Merge with values that may have been set already on the form.
                    'values' => $values
                ];
            });

        $config = $this->popReturn();

        return $this->request($config['action'], $config['method'], $config['values'], $headers, $options);
    }

    public function followLink(string $selector,
            string $method = HttpBotInterface::HTTP_GET, array $data = [],
            array $headers = [], array $options = []) : HttpBotInterface {
        $this->selectOne($selector, function(Crawler $crawler) use ($method) {
            /* @var $crawler Crawler */
            $link = $crawler->link($method);
            return [
                'action' => $link->getMethod(),
                'url' => $link->getUri()
            ];
        });
        $config = $this->popReturn();
        if (empty($config['url'])) {
            $this->getLogger()->log($selector, 'Cannot follow given link, it does not define an URL', Logger::LEVEL_ERROR);
            throw new HttpBotException("Cannot follow link $selector, it does not define an URL");
        }
        return $this->request($config['url'], $config['action'], $data, $headers, $options);
    }


    protected abstract function popReturn();

    protected abstract function getLogger(): Logger;

    public abstract function request(string $url,
            string $method = HttpBotInterface::HTTP_GET, array $data = [],
            array $headers = [], array $options = []) : HttpBotInterface;
    
    public abstract function selectMulti(string $selector, $callback, int $expectedCount = -1): HttpBotInterface;
}