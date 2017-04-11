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

namespace Moose\Tasks;

use Crunz\Event;
use Crunz\Schedule;
use InvalidArgumentException;
use Symfony\Component\Process\ProcessUtils;

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'bootstrap.php';

class PhpEventRunner {
    private function __construct() {
    }
       
    private function run(array & $cliOptions) {
        if (!array_key_exists('class', $cliOptions)) {
            throw new InvalidArgumentException('No class given.');
        }
        if (!array_key_exists('options', $cliOptions)) {
            throw new InvalidArgumentException('No json given.');
        }
        $class = $cliOptions['class'];
        $json = $cliOptions['options'];
        $options = json_decode($json);
        if ($options === null || json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid json given.');
        }
        $implements = class_implements($class);
        if (!is_array($implements) || !array_key_exists(EventInterface::class, $implements)) {
            throw new InvalidArgumentException("Class $class does not implement EventInterface.");
        }
        (new $class())->run($options);
    }
    
    public static function runPhp(Schedule $schedule, string $class, array $options = null) : Event {
        $implements = class_implements($class);
        if (!is_array($implements) || !array_key_exists(EventInterface::class,
                        $implements)) {
            throw new InvalidArgumentException("Class $class does not implement EventInterface.");
        }
        $php = PHP_BINARY;
        $file = ProcessUtils::escapeArgument(realpath(__FILE__));
        $json = json_encode($options ?? []);
        return $schedule->run("$php", [$file,
            '--action' => 'run',
            '--class' => $class,
            '--options' => $json]);
    }
    
    public static function main() {
        $longOpts = ['action::', 'class::', 'options::'];
        $options = getopt("", $longOpts);
        if ($options === false) {
            return;
        }
        if (array_key_exists('action', $options)) {
            switch ($options['action']) {
                case 'run':
                    $runner = new PhpEventRunner();
                    $runner->run($options);
                    break;
            }
        }
    }
}

PhpEventRunner::main();