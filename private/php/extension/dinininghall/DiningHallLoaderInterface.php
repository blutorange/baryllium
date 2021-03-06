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

/**
 *
 * @author madgaksha
 */
interface DiningHallLoaderInterface {
    public function __construct();
    
    /** @return string The name of this dining hall. */
    public static function getName() : string;
    
    /**
     * @param string $language The language tag, such as "de" or "en".
     * @return string The localized name of this dining hall.
     */
    public static function getLocalizedName(string $language) : string;
    
    /** @return GeoLocationInterface Where this dining hall is located on planet Earth. */
    public static function getLocation() : GeoLocationInterface;
    
    /**
     * @param DateTime $from No meals before this date are retrieved.
     * @param DateTime $to No meals after this date are retrieved.
     * @param bool $withImages Whether images should be loaded when possible. When
     * set to false, implementations supporting images should lazy load them
     * when calling DiningHallMealInterface::getImage().
     * @return DiningHallMealInterface[] Meals found within the given time span.
     * @throws DiningHallException When the meals could not be retrieved.
     */
    public function fetchMenu(DateTime $from, DateTime $to, bool $withImages = false) : array;
}