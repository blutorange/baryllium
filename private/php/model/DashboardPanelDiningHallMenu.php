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

use Moose\Context\Context;
use Moose\Dao\Dao;
use Moose\Entity\DiningHallMeal;
use Moose\Entity\User;
use Moose\Extension\DiningHall\DiningHallLoaderInterface;
use Moose\Util\PlaceholderTranslator;

/**
 * Description of DashboardPanelDiningHallMenu
 *
 * @author mad_gaksha
 */
class DashboardPanelDiningHallMenu extends AbstractDashboardPanel {
    /** @var DiningHallMeal[] */
    private $data;
    protected function __construct(array $meals, string $hallName,
            PlaceholderTranslator $translator) {
        parent::__construct('menu-panel', 'partials/component/tc_dashboard_menu',
                $translator->gettext('dashboard.label.dininghallmenu'));
        $this->data = [
            'meals' => $meals,
            'hallName' => $hallName
        ];
    }

    public function & getAdditionalData(): array {
        return $this->data;
    }

    private static function doHide(User $user) : bool {
        return $user->getTutorialGroup() === null;
    }

    public static function forCurrentUser(): DashboardPanelInterface {
        $user = Context::getInstance()->getUser();
        if (self::doHide($user)) {
            return DashboardPanelHidden::make();
        }
        $preferredHall = $user->getUserOption()->getPreferredDiningHall();
        if ($preferredHall !== null && \class_exists($preferredHall) && \in_array(DiningHallLoaderInterface::class, \class_implements($preferredHall))) {
            $meals = Dao::diningHallMeal(Context::getInstance()->getEm())->findAllByHallNameAndToday($preferredHall::getName());
            $hallName = $preferredHall::getLocalizedName(Context::getInstance()->getSessionHandler()->getLang());
        }
        else {
            $meals = Dao::diningHallMeal(Context::getInstance()->getEm())->findAllByToday($preferredHall);
            $hallName = Context::getInstance()->getSessionHandler()->getTranslator()->gettext('dininghall.all');
        }
        return new DashboardPanelDiningHallMenu($meals, $hallName,
                Context::getInstance()->getSessionHandler()->getTranslator());
    }
}