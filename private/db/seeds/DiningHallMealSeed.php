<?php

namespace Moose\Seed;

use DateTime;
use Moose\Dao\AbstractDao;
use Moose\Entity\DiningHall;
use Moose\Entity\DiningHallMeal;
use Moose\Seed\DormantSeed;
use Moose\Util\MathUtil;


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

/**
 * @author madgaksha
 */
class DiningHallMealSeed extends DormantSeed {
    
    /**
     * 
     * @param int $count
     * @param bool $beforeAndAfter
     * @param DateTime $date
     * @param DiningHall $diningHall
     * @return DiningHallMeal[]
     */
    public function & seedRandomToday(int $count = 10, bool $beforeAndAfter = true, DateTime $date = null, DiningHall $diningHall = null) : array {
        $count = MathUtil::max(1, $count);
        $dateToday = $date ?? $this->time();
        $mealList = [];
        $diningHall = $diningHall ?? AbstractDao::diningHall($this->em())->findOne();
        $this->makeRandom($count, $dateToday, $diningHall);
        if ($beforeAndAfter) {
            $dateYesterday = new DateTime();
            $dateYesterday->modify('-1 day');
            $this->makeRandom($count, $dateYesterday, $diningHall);
            $dateTomorrow= new DateTime();
            $dateTomorrow->modify('+1 day');
            $this->makeRandom($count, $dateTomorrow, $diningHall);
        }
        return $mealList;
    }
    
    private function makeRandom(int $count, DateTime $date, DiningHall $diningHall) {
        for ($i = 0; $i < $count; ++$i) {
            $name = $this->name();
            $this->em()->persist($mealList[] = DiningHallMeal::create()
                    ->setIsAvailable(rand(1,10) >= 3)
                    ->setDate($date)
                    ->setFlagsAdditive(rand(0, (1<<10)-1))
                    ->setFlagsAllergen(rand(0, (1<<15)-1))
                    ->setFlagsOther(rand(0, (1<<6)-1))
                    ->setName($name)
                    ->setPrice(rand(1,500))
                    ->setImage($this->imageDataUri($name, 256))
                    ->setDiningHall($diningHall)
            );
        }
    }
}
