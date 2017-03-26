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

namespace Extension\DiningHall;

use DateTime;
use Pekkis\MimeTypes\MimeTypes;
use Requests;
use Requests_Response;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Description of MensaJohannstadtMeal
 *
 * @author madgaksha
 */
class MensaJohannstadtMeal extends DiningHallMealImpl {
    private $detailLink;
    
    public function __construct(string $detailLink, string $name, DateTime $date, int $price = null,
            int $flags = 0, string $image = null) {
        parent::__construct($name, $date, $price, $flags, $image);
        $this->detailLink = $detailLink;
    }
    
    protected function fetchImage() {
        $url = MensaJohannstadtLoader::URL_DETAILS . $this->detailLink;
        $response = Requests::get($url, [
            'Host' => MensaJohannstadtLoader::HOST,
            'User-Agent' => MensaJohannstadtLoader::USER_AGENT
        ]);
        if ($response->status_code !== 200) {
            $name = $this->getName();
            throw new DiningHallException("Could not fetch meal image for $name, expected a 200 (OK) but got a $response->status_code");
        }
        $body = $response->body;
        if (empty($body)) {
            $name = $this->getName();
            throw new DiningHallException("Could not fetch meal image for $name, got an empty body.");
        }
        return $this->parseDetailsForImage($body);
    }

    private function parseDetailsForImage($body) {
        $crawler = new Crawler($body);
        $img = $crawler->filter('a[href]#essenfoto');
        if ($img->count() !== 1) {
            $url = $this->getAltImage($crawler);
        }
        else {
            $href = $img->attr('href');
            $url = "https:$href";
        }
        $response = Requests::get($url, [
            'User-Agent' => MensaJohannstadtLoader::USER_AGENT
        ]);
        if ($response->status_code !== 200) {
            $name = $this->getName();
            throw new DiningHallException("Could not fetch meal image for $name, expected a 200 (OK) but got a $response->status_code");
        }
        return $this->getImageBase64($response);
    }

    private function getImageBase64(Requests_Response $response) : string {
        $mime = $response->headers['Content-Type'];
        if (empty($mime)) {
            $path = parse_url($response->url ?? '', PHP_URL_PATH) ?? '';
            $ext = pathinfo($path, PATHINFO_EXTENSION) ?? '';
            $mime = (new MimeTypes())->extensionToMimeType($ext) ?? 'image/jpeg';
        }
        $body = $response->body;
        if (empty($body)) {
            $name = $this->getName();
            throw new DiningHallException("Could not fetch meal image for $name, got an empty body.");
        }
        $b64 = base64_encode($body ?? '');
        return "data:$mime;charset=utf-8;base64,$b64";
    }

    private function getAltImage($crawler) : string {
        $alt = $crawler->filter('#essenbild>img[src]');
        $count = $alt->count();
        if ($count !== 1) {
            $name = $this->getName();
            throw new DiningHallException("Could not fetch image for $name, expected 1 image, but got $count.");
        }
        $src = $alt->attr('src');
        $details = MensaJohannstadtLoader::URL_DETAILS;
        return "$details$src";
    }
}