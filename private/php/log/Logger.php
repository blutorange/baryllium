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

namespace Moose\Log;

/**
 * Description of Logger
 *
 * @author madgaksha
 */
class Logger {
    private static $HANDLER_NONE;
    private static $HANDLER_ECHO;
    private static $HANDLER_VAR_DUMP;
    
    const LEVEL_ALL = 0;
    const LEVEL_DEBUG = 1;
    const LEVEL_INFO = 2;
    const LEVEL_WARNING = 3;
    const LEVEL_ERROR = 4;
    const LEVEL_NONE = 5;
    
    const LEVEL_NAMES = [
        'ALL',
        'DEBUG',
        'INFO',
        'WARNING',
        'ERROR',
        'NONE'
    ];
    
    /** @var LogHandlerInterface */
    private $logHandler;
    private $level;
    
    private function __construct($logHandler) {
        $this->logHandler = $logHandler;
        $this->level = self::LEVEL_WARNING;
    }
    
    private static function stringify($object = null, $level = 0) {
        if (\is_callable($object)) {
            return \call_user_func($object);
        }
        else if (\is_object($object)) {
            if ($object instanceof Throwable) {
                $class = \get_class($object);
                $msg = $object->getMessage();
                $file = $object->getFile();
                $line = $object->getLine();
                $trace = $object->getTraceAsString();
                return "$class: $msg in $file:$line\n$trace";
            }
            else if (\method_exists($object, '__toString')) {
                return $object->__toString();
            }
            return \get_class($object) . '$$' . \spl_object_hash($object);
        }
        else if (\is_array ($object)) {
            $array = \array_map(function($key, $value) use ($level) {
                return self::stringify($key, $level + 1) . ' => ' . self::stringify($value, $level + 1);
            }, \array_keys($object), $object);
            $rep = \str_repeat(' ', $level);
            return "[\n" . $rep . ' ' . \implode(",\n" . $rep . ' ', $array) . "\n" . $rep . ']';
        }
        else if($object === null) {
            return 'null';
        }
        else {
            return \print_r($object, true);
        }        
    }
    
    public function setLevel(int $level) : Logger {
        if ($level > self::LEVEL_NONE) {
            $level = self::LEVEL_NONE;
        }
        else if ($level < self::LEVEL_ALL) {
            $level = self::LEVEL_ALL;
        }
        $this->level = $level;
        return $this;
    }
    
    public function log($object, string $label = null, int $level = self::LEVEL_INFO) : Logger {
        if ($level < $this->level || $level <= self::LEVEL_ALL || $level >= self::LEVEL_NONE) {
            return $this;
        }
        $time = (new \DateTime())->format('Y-m-d H:i:s e');
        $message = self::stringify($object);
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $levelName = self::LEVEL_NAMES[$level];
        if (\sizeof($trace) > 0) {
            $last0 = $trace[0];
            $line = $last0['line'] ?? 0;
            $file = $last0['file'] ?? '';
            $class = null;
            if (\sizeof($trace) > 1) {
                $last1 = $trace[1];
                $function = $last1['function'] ?? null;
                $class = $last1['class'] ?? null;
                $type = $last1['type'] ?? null;
            }
            $caller = $class !== null ? "$class$type$function($line)" : "$file:$line";        
        }
        else {
            $caller = "Unknown";
        }
        if (empty($label)) {
            $this->logHandler->output("[$time] $levelName - $caller: $message");
        }
        else {
            $this->logHandler->output("[$time] $levelName - $caller: $label\n$message");
        }
        return $this;
    }
    
    public static function none() : Logger {
        return self::$HANDLER_NONE ?? self::$HANDLER_NONE = new Logger(new LogHandlerNone());
    }
    
    public static function _echo() : Logger {
        return self::$HANDLER_ECHO ?? self::$HANDLER_ECHO = new Logger(new LogHandlerEcho());
    }
    
    public static function varDump() : Logger {
        return self::$HANDLER_VAR_DUMP ?? self::$HANDLER_VAR_DUMP = new Logger(new LogHandlerVarDump());
    }
    
    public static function create(LogHandlerInterface $handler) : Logger {
        return new Logger($handler);
    }
}