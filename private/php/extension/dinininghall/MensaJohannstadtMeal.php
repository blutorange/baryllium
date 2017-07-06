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

namespace Moose\Extension\DiningHall;

use DateTime;
use Moose\Util\DebugUtil;
use Pekkis\MimeTypes\MimeTypes;
use Requests;
use Requests_Response;
use Symfony\Component\DomCrawler\Crawler;
use const MB_CASE_LOWER;
use function mb_convert_case;

/**
 * Description of MensaJohannstadtMeal
 *
 * @author madgaksha
 */
class MensaJohannstadtMeal extends DiningHallMealImpl {
    
    const SELECTOR_FLAGS = '#speiseplandetailsrechts .speiseplaninfos li';
    const REGEX_ADDITIVE = '/^[^(]+\\((1|2|3|4|5|6|7|8|9|10)\\)$/ui';
    const REGEX_ALLERGEN = '/^[^(]+\\((a|a1|b|c|d|e|f|g|h|i|j|k|l|m|n)\\)$/ui';
    const MAP_ALLERGEN = [
        'a' => DiningHallMealInterface::FLAG_ALLERGEN_GLUTEN,
        'a1' => DiningHallMealInterface::FLAG_ALLERGEN_WHEAT,
        'b' => DiningHallMealInterface::FLAG_ALLERGEN_LOBSTER,
        'c' => DiningHallMealInterface::FLAG_ALLERGEN_EGG,
        'd' => DiningHallMealInterface::FLAG_ALLERGEN_FISH,
        'e' => DiningHallMealInterface::FLAG_ALLERGEN_PEANUT,
        'f' => DiningHallMealInterface::FLAG_ALLERGEN_SOYBEAN,
        'g' => DiningHallMealInterface::FLAG_ALLERGEN_LACTOSE,
        'h' => DiningHallMealInterface::FLAG_ALLERGEN_NUTS,
        'i' => DiningHallMealInterface::FLAG_ALLERGEN_CELERY,
        'j' => DiningHallMealInterface::FLAG_ALLERGEN_MUSTARD,
        'k' => DiningHallMealInterface::FLAG_ALLERGEN_SESAME,
        'l' => DiningHallMealInterface::FLAG_ALLERGEN_SULPHUR_DIOXIDE,
        'm' => DiningHallMealInterface::FLAG_ALLERGEN_LUPINE,
        'n' => DiningHallMealInterface::FLAG_ALLERGEN_MOLLUSCS
    ];
    const MAP_ADDITIVE = [
        1 => DiningHallMealInterface::FLAG_ADDITIVE_FOOD_DYE,
        2 => DiningHallMealInterface::FLAG_ADDITIVE_PRESERVATIVE,
        3 => DiningHallMealInterface::FLAG_ADDITIVE_ANTIOXIDANT,
        4 => DiningHallMealInterface::FLAG_ADDITIVE_FLAVOUR_ENHANCER,
        5 => DiningHallMealInterface::FLAG_ADDITIVE_SULPHUR,
        6 => DiningHallMealInterface::FLAG_ADDITIVE_BLACK_DYE,
        7 => DiningHallMealInterface::FLAG_ADDITIVE_WAX_COATING,
        8 => DiningHallMealInterface::FLAG_ADDITIVE_PHOSPHATE,
        9 => DiningHallMealInterface::FLAG_ADDITIVE_ARTIFICAL_SWEETENER,
        10 => DiningHallMealInterface::FLAG_ADDITIVE_PHENYLALANINE
    ];
    
    private $detailLink;
    private $loaded;
    
    public function __construct(string $detailLink, string $name, DateTime $date, int $price = null,
            int $flagsOther = null, bool $isAvailable = false, string $image = null) {
        parent::__construct($name, $date, $price, null, null, $flagsOther, $isAvailable, $image);
        $this->detailLink = $detailLink;
        $this->loaded = false;
    }
    
    protected function fetchFlagsAdditive() {
        $this->loadDetails();
        return $this->flagsAdditive;
    }
    
    protected function fetchFlagsAllergen() {
        $this->loadDetails();
        return $this->flagsAllergen;
    }
    
    protected function fetchImage() {
        $this->loadDetails();
        return $this->image;
    }
    
    private function loadDetails() {
        if ($this->loaded) {
            return;
        }
        $url = MensaJohannstadtLoader::URL_DETAILS . $this->detailLink;
        $response = Requests::get($url, [
            'Host' => MensaJohannstadtLoader::HOST,
            'User-Agent' => MensaJohannstadtLoader::USER_AGENT
        ]);
        if ($response->status_code !== 200) {
            $name = $this->getName();
            throw new DiningHallException("Could not fetch meal details for $name, expected a 200 (OK) but got a $response->status_code");
        }
        $body = $response->body;
        if (empty($body)) {
            $name = $this->getName();
            throw new DiningHallException("Could not fetch meal details for $name, got an empty body.");
        }
        $this->image = $this->parseDetailsForImage($body);
        $this->parseDetailsForFlags($body);
        $this->loaded = true;
    }
    
    private function parseDetailsForFlags($body) {
        $flagsAdditive = 0;
        $flagsAllergen = 0;
        $crawler = new Crawler($body);
        $crawler->filter(self::SELECTOR_FLAGS)->each(function(Crawler $node, int $i) use (& $flagsAdditive, & $flagsAllergen) {
            $text = \trim($node->text());
            $flagsAdditive = $flagsAdditive | $this->parseForAdditives($text);
            $flagsAllergen = $flagsAllergen | $this->parseForAllergen($text);
        });
        $this->flagsAdditive = $flagsAdditive;
        $this->flagsAllergen = $flagsAllergen;
    }

    private function parseForAdditives(string $text) : int {
        $matches = [];
        if (\preg_match(self::REGEX_ADDITIVE, $text, $matches) === 1) {
            return self::MAP_ADDITIVE[\intval($matches[1])];
        }
        return 0;
    }
    
    private function parseForAllergen(string $text) : int {
        $matches = [];
        if (\preg_match(self::REGEX_ALLERGEN, $text, $matches) === 1) {
            return self::MAP_ALLERGEN[mb_convert_case($matches[1], MB_CASE_LOWER)];
        }
        return 0;
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
        if ($url === null) {
            return null;
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

    /**
     * @param Crawler $crawler
     * @return string
     */
    private function getAltImage($crawler) {
        $alt = $crawler->filter('#essenbild>img[src]');
        $count = $alt->count();
        if ($count !== 1) {
            $name = $this->getName();
            Context::getInstance()->getLogger()->log("Could not fetch image for $name, expected 1 image, but got $count.");
            return null;
        }
        $src = $alt->attr('src');
        $details = MensaJohannstadtLoader::URL_DETAILS;
        return "$details$src";
    }
}