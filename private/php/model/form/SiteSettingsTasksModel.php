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

use Symfony\Component\Validator\Constraints as Assert;

use Moose\Context\MooseConfig;
use Moose\Extension\DiningHall\DiningHallLoaderInterface;
use Moose\Util\PlaceholderTranslator;
use Moose\Util\ReflectionCache;
use Moose\ViewModel\TasksDiningHallModel;
use Moose\Web\HttpRequestInterface;
use Moose\Web\StringConverterTrait;

/**
 * Description of SiteSettingsTasksModel
 *
 * @author madgaksha
 */
class SiteSettingsTasksModel extends AbstractFormModel {
    private static $MAP;
    
    /**
     * @var string
     * @Assert\NotBlank(message="settings.config.configpath.blank")
     */
    private $configPath;
       
    /**
     * @Assert\NotNull(message="settings.config.halls.null")
     * @var TasksDiningHallModel[]
     */
    private $halls;
    
    private static function getMap() {
        if (self::$MAP === null) {
            self::$MAP = [
            'configPath' => 'configpath',
            'halls' => ['dhall', [], self::getHallConverter()],
            ];
        }
        return self::$MAP;
    }
    
    private static function getHallConverter() : ParamConverterInterface {
        return new class implements ParamConverterInterface {
            use StringConverterTrait;
            public function convert($value) {
                if (!\is_array($value)) {
                    return [];
                }
                $halls = ReflectionCache::getImplementationsOf(DiningHallLoaderInterface::class);
                return \array_map(function(string $class) use ($value) {
                    /* @var $class DiningHallLoaderInterface|string */
                    $data = $value[$class] ?? [];
                    $activated = $this->getBool($data, 'activated', false);
                    $schedule = $this->getInt($data, 'schedule', 60);
                    return new TasksDiningHallModel($class, $activated, $schedule);
                }, $halls);
            }
            public function getDefault($staticDefaultValue) {
                return $staticDefaultValue;
            }
        };
    }
    
    protected function __construct(HttpRequestInterface $request, PlaceholderTranslator $translator, array $fields) {
        parent::__construct($request, $translator, $fields);
    }
    
    public static function fromConfig(HttpRequestInterface $request,
            PlaceholderTranslator $translator, MooseConfig $config) {
        $model = new SiteSettingsTasksModel($request, $translator, self::getMap());
        $model->setConfigPath($config->getOriginalFile());
        $tasks = $config->getTasks();
        $halls = array_map(function($hall) use ($tasks) {
        /* @var $hall DiningHallLoaderInterface */
            $isActivated = $tasks->getIsDiningHallActivated($hall);
            $schedule = $tasks->getDiningHallSchedule($hall);
            return new TasksDiningHallModel($hall, $isActivated, $schedule);
        }, ReflectionCache::getImplementationsOf(DiningHallLoaderInterface::class));
        $model->setHalls($halls);
        return $model;
    }
    
    public static function fromRequest(HttpRequestInterface $request, PlaceholderTranslator $translator) : SiteSettingsTasksModel {
        return new SiteSettingsTasksModel($request, $translator, self::getMap());
    }
    
    protected function getGroups(): array {
        return [];
    }
    
    public function getConfigPath() : string {
        return $this->configPath;
    }
    
    public function setConfigPath(string $configPath = null) : SiteSettingsTasksModel {
        $this->configPath = $configPath ?? '';
        return $this;
    }
    
    /**
     * 
     * @return TasksDiningHallModel[]
     */
    public function getHalls(): array {
        return $this->halls;
    }

    /**
     * 
     * @param TasksDiningHallModel $halls
     * @return SiteSettingsTasksModel
     */
    public function setHalls(array $halls) : SiteSettingsTasksModel {
        $this->halls = $halls;
        return $this;
    }
}
