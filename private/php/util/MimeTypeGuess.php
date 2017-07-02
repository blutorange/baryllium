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

namespace Moose\Util;

use LogicException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\MimeType\FileBinaryMimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * Description of MimeTypeUtil
 */
class MimeTypeGuess implements MimeTypeGuesserInterface {

    /**
     * The singleton instance.
     *
     * @var MimeTypeGuesser
     */
    private static $instance = null;

    /**
     * All registered MimeTypeGuesserInterface instances.
     *
     * @var array
     */
    protected $guessers = array();

    /**
     * Returns the singleton instance.
     *
     * @return self
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Resets the singleton instance.
     */
    public static function reset() {
        self::$instance = null;
    }
    
    public function clear() {
        $this->guessers = [];
    }

    /**
     * Registers all natively provided mime type guessers.
     */
    private function __construct() {
        if (FileBinaryMimeTypeGuesser::isSupported()) {
            $this->register(new FileBinaryMimeTypeGuesser());
        }

        if (FileinfoMimeTypeGuesser::isSupported()) {
            $this->register(new FileinfoMimeTypeGuesser());
        }
    }

    /**
     * Registers a new mime type guesser.
     *
     * When guessing, this guesser is preferred over previously registered ones.
     *
     * @param MimeTypeGuesserInterface $guesser
     */
    public function register(MimeTypeGuesserInterface $guesser) {
        array_unshift($this->guessers, $guesser);
    }

    /**
     * Tries to guess the mime type of the given file.
     *
     * The file is passed to each registered mime type guesser in reverse order
     * of their registration (last registered is queried first). Once a guesser
     * returns a value that is not NULL, this method terminates and returns the
     * value.
     *
     * @param string $path The path to the file
     *
     * @return string The mime type or NULL, if none could be guessed
     *
     * @throws LogicException
     * @throws FileNotFoundException
     * @throws AccessDeniedException
     */
    public function guess($path) {
        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }

        if (!is_readable($path)) {
            throw new AccessDeniedException($path);
        }

        if (!$this->guessers) {
            $msg = 'Unable to guess the mime type as no guessers are available';
            if (!FileinfoMimeTypeGuesser::isSupported()) {
                $msg .= ' (Did you enable the php_fileinfo extension?)';
            }
            throw new LogicException($msg);
        }

        foreach ($this->guessers as $guesser) {
            if (null !== $mimeType = $guesser->guess($path)) {
                if ($mimeType !== 'application/octet-stream') {
                    return $mimeType;
                }
            }
        }
        return 'application/octet-stream';
    }
}