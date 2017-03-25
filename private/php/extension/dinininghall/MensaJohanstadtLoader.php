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
use DateTimeZone;
use Requests;
use Symfony\Component\DomCrawler\Crawler;
use Util\MathUtil;

/**
 * Description of MensaJohanstadtLoader
 *
 * @author madgaksha
 */
class MensaJohannstadtLoader implements DiningHallLoaderInterface {
    
    const NAME = "Mensa Johannstadt";
    const LATITUDE = 51.053164;
    const LONGITUDE = 13.760893;
    const URL = 'https://www.studentenwerk-dresden.de/mensen/speiseplan/suche.html';
    const URL_DETAILS = 'https://www.studentenwerk-dresden.de/mensen/speiseplan/';
    const ID = '32';
    const PARAM_SEND = 'suchen';
    const REGEX_PRICE = '/(\d+)\s*,\s*(\d\d)\s*€\s*\\/\s*(\d+)\s*,\s*(\d\d)\s*€/u';
    const REGEX_DATE = '/(\d\d)\s*\.\s*(\d\d)\./u';
    const SELECTOR_ITEM = 'table.speiseplan tbody tr';
    const SECONDS_IN_WEEK = 7*24*60*60;
    const PARAM_CURRENT_WEEK = 0;
    const PARAM_NEXT_WEEK = 1;
    const PARAM_NEXTNEXT_WEEK = 2;
    const USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.98 Safari/537.36';
    const HOST = 'www.studentenwerk-dresden.de';
    const SELECTOR_FLAGS = 'a>img[src]';

    private $flagInfo;
    
    public function __construct() {
        $this->flagInfo = [
            'alkohol' => DiningHallMeal::FLAG_ALCOHOL,
            'knoblauch' => DiningHallMeal::FLAG_GARLIC,
            'rindfleisch' => DiningHallMeal::FLAG_BEEF,
            'schweinefleisch' => DiningHallMeal::FLAG_PORK,
            'vegan' => DiningHallMeal::FLAG_VEGAN,
            'fleischlos' => DiningHallMeal::FLAG_VEGETARIAN
        ];
    }
    
    public function getLocation(): GeoLocationInterface {
        return new GeoLocation(self::LATITUDE, self::LONGITUDE);
    }

    public function getName(): string {
        return self::NAME;
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @param bool $loadImages
     * @return DiningHallMeal[]
     */
    public function fetchMenu(DateTime $from, DateTime $to, bool $loadImages = false): array {
        $monday = new DateTime('monday this week');
        $monday->setTime(0, 0, 0);
        $timeMonday = $monday->getTimestamp();
        $timeFrom = $from->getTimestamp();
        $timeTo = $to->getTimestamp();
        $meals = [];
        if (MathUtil::intervalOverlap($timeFrom, $timeTo, $timeMonday, $timeMonday + self::SECONDS_IN_WEEK)) {
            $this->fetchForWeek(self::PARAM_CURRENT_WEEK, $meals);
        }
        if (MathUtil::intervalOverlap($timeFrom, $timeTo, $timeMonday + self::SECONDS_IN_WEEK, $timeMonday + 2*self::SECONDS_IN_WEEK)) {
            $this->fetchForWeek(self::PARAM_NEXT_WEEK, $meals);
        }
        if (MathUtil::intervalOverlap($timeFrom, $timeTo, $timeMonday + 2*self::SECONDS_IN_WEEK, $timeMonday + 3*self::SECONDS_IN_WEEK)) {
            $this->fetchForWeek(self::PARAM_NEXTNEXT_WEEK, $meals);
        }
        $filteredMeals = $this->filterMenu($meals, $from, $to);
        if ($loadImages) {
            $this->preloadImages($filteredMeals);
        }
        return $filteredMeals;
    }
    
    private function fetchForWeek(int $week, array & $meals) {
        $response = Requests::post(self::URL, [
            'Host' => self::HOST,
            'User-Agent' => self::USER_AGENT
        ], [
            'query' => '',
            'mensen[]' => self::ID,
            'zeitraum' => (string)$week,
            'senden' => self::PARAM_SEND
        ],[
            'follow_redirects' => true
        ]);
        if ($response->status_code !== 200) {
            throw new DiningHallException("Could not fetch meals, expected a 200 (OK) but got a $response->status_code");
        }
        $body = $response->body;
        if (empty($body)) {
            throw new DiningHallException('Could not fetch meals, got an empty body.');
        }
        $this->parseMenu($response->body ?? '', $meals);
        return $meals;
    }

    private function parseMenu(string $html, array & $meals) {
        $crawler = new Crawler($html);
        $crawler->filter(self::SELECTOR_ITEM)->each($this->getMenuParser($meals));
    }
    
    private function getMenuParser(array & $meals) {
        return function(Crawler $mealNode) use (& $meals) {
            $nodeDate = $mealNode->filter('td:first-child');
            $nodeName = $mealNode->filter('td.text');
            $nodeFlags = $mealNode->filter('td.stoffe');
            $nodePrice = $mealNode->filter('td.preise');
            $name = $this->assertText($nodeName);
            $price = $this->parsePrice($this->assertText($nodePrice));
            $date = $this->parseDate($this->assertText($nodeDate));
            $flags = $this->parseFlags($this->assertOne($nodeFlags));
            $nodeDetailLink = $nodeName->filter('a[href]');
            $this->assertOne($nodeDetailLink);
            $detailLink = $nodeDetailLink->attr('href');
            $meal = new MensaJohannstadtMeal($detailLink, $name, $date, $price, $flags, null);
            array_push($meals, $meal);
        };
    }

    private function assertOne(Crawler $crawler = null) : Crawler {
        $count = $crawler !== null ? $crawler->count() : 0;
        if ($count !== 1) {
            throw new DiningHallException("Failed to retrieve menu, expected exactly one node, but found $count.");
        }
        return $crawler;
    }
    
    private function assertText(Crawler $crawler = null) : string {
        $this->assertOne($crawler);
        $text = $crawler->text();
        if (empty($text)) {
            throw new DiningHallException('Failed to retrieve menu, text node empty.');
        }
        return $text;
    }

    private function parseFlags(Crawler $flags) : int {
        $flagInt = 0;
        foreach ($flags->filter(self::SELECTOR_FLAGS) as $node) {
            $base = pathinfo($node->getAttribute('src'), PATHINFO_FILENAME);
            if (!array_key_exists($base, $this->flagInfo)) {
                throw new DiningHallException("Failed to retrieve meal, unknown flag $base.");
            }
            $flagInt = $flagInt | $this->flagInfo[$base];
        }
        return $flagInt;
    }

    private function parsePrice(string $price) {
        $matches = [];
        if (preg_match(self::REGEX_PRICE, $price, $matches) !== 1) {
            throw new DiningHallException("Failed to retrieve meal, price node with text $price does not contain a valid price.");
        }
        return intval($matches[1],10) * 100 + intval($matches[2], 10);
    }

    private function parseDate(string $date) : DateTime {
        $now = new DateTime();
        $curYear = intval($now->format('Y'), 10);
        $curMonth = intval($now->format('m'), 10);
        $matches = [];
        if (preg_match(self::REGEX_DATE, $date, $matches) !== 1) {
            throw new DiningHallException("Failed to retrieve meal, date node with text $date does not contain a valid date.");
        }
        $day = intval($matches[1], 10);
        $month = intval($matches[2], 10);
        if ($curMonth === 12 && $month === 1) {
            $year = $curYear + 1;
        }
        else if ($curMonth === 1 && $month === 12) {
            $year = $curYear - 1;
        }
        else {
            $year = $curYear;
        }
        $now->setDate($year, $month, $day);
        $now->setTime(12, 0, 0);
        $now->setTimezone(new DateTimeZone('Europe/Berlin'));
        return $now;
    }

    /**
     * Filters meals by date.
     * @param DiningHallMealInterface[] $meals
     * @param DateTime $from
     * @param DateTime $to
     * @return DiningHallMealInterface[] The filtered meals.
     */
    private function filterMenu(array & $meals, DateTime $from, DateTime $to) : array {
        $timeFrom = $from->getTimestamp();
        $timeTo = $to->getTimestamp();
        return array_values(array_filter($meals, function(DiningHallMealInterface $meal) use ($timeFrom, $timeTo) {
            $time = $meal->getDate()->getTimestamp();
            return $time >= $timeFrom && $time <= $timeTo;
        }));
    }

    /**
     * @param DiningHallMeal[] $filteredMeals
     */
    public function preloadImages(array $filteredMeals) {
        foreach ($filteredMeals as $meal) {
            $meal->getImage();
        }
    }

}