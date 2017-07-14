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

use DateTime;
use DateTimeZone;
use Intervention\Image\ImageManagerStatic;
use League\Plates\Engine;
use Moose\Context\Context;
use Moose\Context\MooseConfig;
use Moose\Util\PlaceholderTranslator;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function mb_strtoupper;
use function mb_substr;

/**
 * @author Philipp
 */
class UiUtil {

    private function __construct() {
        
    }
    
    public static function firstToUpcase(string $string) : string {
        $fc = mb_strtoupper(mb_substr($string, 0, 1));
        return $fc . mb_substr($string, 1);
    }

    /**
     * 
     * @return string The rendered template as HTML.
     */
    public static function renderTemplateToHtml(string $templateName,
            Engine $engine, PlaceholderTranslator $translator,
            array & $messageList = null, string $lang = 'de',
            array & $data = null): string {
        $locale = 'de';
        $selfUrl = '';
        $messageList = $messageList ?? [];
        if ($data !== null && \array_key_exists('messages', $data)) {
            $messageList = \array_merge($messageList, $data['messages']);
        }
        if ($data !== null && \array_key_exists('locale', $data)) {
            $locale = $data['locale'];
        }
        else {
            $locale = $lang;
        }
        if (empty($data) || !\array_key_exists('selfUrl', $data)) {
            $selfUrl = \array_key_exists('PHP_SELF', $_SERVER) ? $_SERVER['PHP_SELF']
                        : '';
            if (\array_key_exists('QUERY_STRING', $_SERVER)) {
                $selfUrl = $selfUrl . '?' . \filter_input(\INPUT_SERVER,
                                'QUERY_STRING', \FILTER_UNSAFE_RAW);
            }
        }
        else {
            $selfUrl = $data['selfUrl'];
        }
        $engine->addData([
            'i18n'      => $translator,
            'locale'    => $locale,
            'messages'  => $messageList,
            'selfUrl'   => $selfUrl,
            'isDevMode' => Context::getInstance()->getConfiguration()->isNotEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION)
        ]);
        if ($data === null) {
            return $engine->render($templateName);
        }
        else {
            return $engine->render($templateName, $data);
        }
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public static function fileToBase64(UploadedFile $file) {
        if (!$file->isValid()) {
            return null;
        }
        $mime = $file->getMimeType();
        if (($data = \file_get_contents($file->getRealPath())) === false) {
            return null;
        }
        $base64 = \base64_encode($data);
        return "data:$mime;base64,$base64";
    }
    
    public static function toBase64(string $mime, string $data) {
        $base64 = \base64_encode($data);
        return "data:$mime;base64,$base64";
    }
    
    /**
     * @param Crawler $node
     * @return string[]
     */
    public static function getClassList(Crawler $node) : array {
        $classString = $node->attr('class');
        if (empty($classString)) {
            return [];
        }
        return \preg_split('/ +/u', \trim($classString));
    }
    
    /**
     * @param int $timestampSeconds
     * @return DateTime
     */
    public static function timestampToDate($timestampSeconds) {
        if (\is_string($timestampSeconds)) {
            if (!\is_numeric($timestampSeconds)) {
                return null;
            }
            $ts = \intval($timestampSeconds);
        }
        else {
            $ts = $timestampSeconds;
        }
        if ($ts < 0) {
            return null;
        }
        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestampSeconds);
        return $dateTime;
    }
    
    /**
     * @param string $format Format of the date.
     * @param string $date Date to parse according to the format.
     * @return DateTime|null The date, or null when the date does not match the format.
     */
    public static function formatToDate(string $format, string $date, DateTimeZone $timezone = null) {
        if (empty($date)) {
            return null;
        }
        $result = DateTime::createFromFormat($format, \trim($date), $timezone ?? new DateTimeZone('UTC'));
        return $result === false ? null : $result;
    }
    
    /**
     * @param type $imageData The image, either a filepath, a GD image resource, an Imagick object or a binary image data.
     * @param int $width Desired width.
     * @param int $height Desired height.
     * @param int $quality Compression quality, default is <code>90</code>.
     * @param string $encoding Encoding algorithm, default is <code>jpg</code>.
     * @return string
     */
    public static function generateThumbnailImage($imageData, int $width, int $height, int $quality = 90, $encoding = 'jpg') : string {
        $image = ImageManagerStatic::make($imageData);
        if ($width/$height > $image->getWidth()/$image->getHeight()) {
            $image->resize($height*$image->getWidth()/$image->getHeight(), $height);
        }
        else {
            $image->resize($width, $width*$image->getHeight()/$image->getWidth());
        }
        $image->resizeCanvas($width, $height);
        return (string)($image->encode($encoding, $quality));
    }
}
