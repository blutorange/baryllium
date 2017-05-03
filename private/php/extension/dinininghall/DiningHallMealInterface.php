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
interface DiningHallMealInterface {
    
    const FLAG_OTHER_VEGETARIAN = 1<<0;
    const FLAG_OTHER_VEGAN = 1<<1;
    const FLAG_OTHER_PORK = 1<<2;
    const FLAG_OTHER_GARLIC = 1<<3;
    const FLAG_OTHER_ALCOHOL = 1<<4;
    const FLAG_OTHER_BEEF = 1<<5;
    
    const FLAG_ADDITIVE_FOOD_DYE = 1<<0;
    const FLAG_ADDITIVE_PRESERVATIVE = 1<<1;
    const FLAG_ADDITIVE_ANTIOXIDANT = 1<<2;
    const FLAG_ADDITIVE_FLAVOUR_ENHANCER = 1<<3;
    const FLAG_ADDITIVE_SULPHUR= 1<<4;
    const FLAG_ADDITIVE_BLACK_DYE = 1<<5;
    const FLAG_ADDITIVE_WAX_COATING = 1<<6;
    const FLAG_ADDITIVE_PHOSPHATE = 1<<7;
    const FLAG_ADDITIVE_ARTIFICAL_SWEETENER = 1<<8;
    const FLAG_ADDITIVE_PHENYLALANINE = 1<<9;
    
    const FLAG_ALLERGEN_GLUTEN = 1<<0;
    const FLAG_ALLERGEN_LOBSTER = 1<<1;
    const FLAG_ALLERGEN_EGG = 1<<2;
    const FLAG_ALLERGEN_FISH = 1<<3;
    const FLAG_ALLERGEN_PEANUT = 1<<4;
    const FLAG_ALLERGEN_SOYBEAN = 1<<5;
    const FLAG_ALLERGEN_LACTOSE = 1<<6;
    const FLAG_ALLERGEN_NUTS = 1<<7;
    const FLAG_ALLERGEN_CELERY = 1<<8;
    const FLAG_ALLERGEN_MUSTARD = 1<<9;
    const FLAG_ALLERGEN_SESAME = 1<<10;
    const FLAG_ALLERGEN_SULPHUR_DIOXIDE = 1<<11;
    const FLAG_ALLERGEN_LUPINE = 1<<12;
    const FLAG_ALLERGEN_MOLLUSCS = 1<<13;
    const FLAG_ALLERGEN_WHEAT = 1<<14;
    
    /** @return string The name of this meal. Not null. */
    public function getName() : string;
    
    /** @return int How much this meal costs, in cents. Null when unknown. */
    public function getPrice();
    
    /** @return int Flags for food additives of this meal, see constants. */
    public function getFlagsAdditive() : int;
    
    /** @return int Flags for allergic substances of this meal, see constants. */
    public function getFlagsAllergen() : int;
    
    /** @return int Flags for other substances of this meal, see constants. */
    public function getFlagsOther() : int;    
    
    /** @return DateTime The date when this meal is offered. Not null. */
    public function getDate() : DateTime;
    
    /**
     * @return bool Whether this meal is available, ie. whether it is sold, or 
     * perhaps sold out etc.
     */
    public function getIsAvailable() : bool;
    
    /**
     * Implementations may choose to implement lazy loading. Use 
     * DiningHallLoaderInterface::fetchMenu(...,...,true) do force the loader
     * to load the images immediately.
     * @return string|null An image for this meal, as a data URI string with the mime
     * type, eg. <code>data:image/png;base64,iVBORw0</code>. Null when not
     * available.
     * @throws DiningHallException When the implementation attempts to load the
     * image lazily and fails in doing so.
     */
    public function getImage();
    
    /**
     * @param int $flag Flag to check, see constants.
     * @return bool Whether this meal has the given flag set.
     */
    public function is(int $flag) : bool;   
}