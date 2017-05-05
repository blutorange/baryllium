<?php

/* Note: This license has also been called the "New BSD License" or "Modified
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

namespace Moose\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Intervention\Image\ImageManagerStatic;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

/**
 * A document that might have been uploaded, generated automatically etc.
 * For example, this could be an image, a PDF and more.
 *
 * @Entity
 * @Table(name="documentdata")
 * @author madgaksha
 */
class DocumentData extends AbstractEntity {   
    
    const THUMBNAIL_GENERIC = 'iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAYAAADDPmHLAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4QUFEx8zghhvZAAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAACzklEQVR42u3coWsycRjA8efeGUQEMQnivzAtoqwvGQTDmmia/gELQ4NhYhpYDEsGk+EE8bpR8GxeEAZGs4IDo7+3C++rY4o3n+83n3I8v493+gPPMsYI6e0PI9Bd4DsHu65rBoOBeJ4n6/VabvHq8fz8LNVq1QLAQc1m04xGIz4yGgG8vb0Zx3GYlsbvAK7rsvgHlUolMx6PjQoAtm2z4gdtt1t5fX2VXq9nbh6A53ms+D/qdDrS7XbNTQPYbDas9H/6+Pj41QiOAmCj6LYRsBGkHAEAlCMAgHIEAFCOAADKEQBAOQIAKEcAAOUIAKAcAQCUIwCAcgQAUI4AAMoRAEA5AgAoRwAA5QgAoBwBAJQjAIByBABQjgAAyhEAQDkCAChHAADlCADwixD0+30DAMW1222ZzWZnRRBgrN8vl8td7T+Ti8VCMpkMAK5ZpVK5mUfIcAtQHgAO+vr6AoDmPj8/AaC5+Xwuq9XKAEBp+/1e3t/fuQJobjKZSKvVMgBQ3HA4lHK5bKbT6U1DsI49AyidTqt/SFAoFJJ4PC7BYNBX53V/fy8vLy8/2pNgI+iEdrudLJdL351XOBzmFkAAIAAQAAgABAACAAGAAEAAIAAQAAgABAACAAGAAEAAIAAQAAgABAAAMAIAEAAIAAQAAgABgABAACAAEAAIAAQAAgABgABAACAAEAAIAAQAAgABgABAACAAEAAIAAQAAgABgABAACAAEAAIAAQAAgABgABAACAAEAAIAAQAAgABgHwJwLIspuTT7u7uLg8gGo0yaZ92jrU5CiCZTDJpn5ZKpS4P4OnpiUn7sEgkIoVCwbo4gGw2a+XzeSbus2q12lneJ3DKQY1GwxIR4zgOk79ygUBA6vW6PD4+nuXbuWWMOflg13WNbdvieZ5sNhv5zmvpZ4sei8Xk4eFBisWiJBKJs/00s1hE9gEIAAQAAgDp6y80+xP/Wrb2YAAAAABJRU5ErkJggg==';
    const THUMBNAIL_TEXT = 'iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAYAAADDPmHLAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4QUFFAMFrpqxJQAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAEOklEQVR42u3dPUgrSxiA4W9jxCISTK8iVwWJSSHHQhCrgIWgpLGwUlLEn0YQUbSyknsJCPYGxMYyq4VdGutU/gRBEiSmingxMRZCzNzucjh48ESi2dl53z7IzD67O7tuNpZSSsjcPEwBAAgABAAyMu9nPvT8/Czlclm5bQHZ09NjAeA3vb6+yvHxsbJtW4rFoisnI5PJcAR4r8fHx79XV1c3b25uOGaatgao1WqytrbGxheRXC6n6vW6WQBOT0/V9fU1u4qIpFIpWVlZUU9PT38ZA+Ds7Iwt/8s6IRaL5Uql0r9GALi9vWWr/1KhUJB4PB5wA4IPAby8vLDF36lYLLoCATeCDEcAAMMRAMBwBAAwHAEADEcAgC9AsLi4qA0CAHxB9/f32iAAgOEIAGA4AgAYjgAA34jg4eEhAwCDEcTj8R9OQwAAwxEAwHAEADAcAQAMRwAAwxEAwBmXiC1DAAAHVCgUWoYAAIYjAIDhCABgOAIAGI4AAIYjAIDDESwvL/8ol8sAMLW7uzvZ2tr6spdxeJniP29kZETe3t5a8rfz+bzq7+9v+htMrI9kjY6OGvMiwUwmY9wrYjgF/JSJL80EwE9VKhUAmFw2m1UAMDjbtjkCmFw6nZbz83MFAIPb3t42CgGXgb8pEolINBqVYDBo+f1+sSwLAOSMUqlU015ryynA8AAAAAIAAYAAQAAg03LMAyGTk5MSDofF49HPpFJK8vm8nJyctOyBEa0BzM7OyubmpvZ7U19fn+zt7XEKaLSxsTFXHE51HIcjALS3t7sCgI7jYBHIVQABwAGraDek4zgcASCfz7sCgI7jcMRlYDKZlEAgIKFQSNra2rS9D5BIJADwmarVquzs7HBCZhFIACAAEAAIAAQAAgAZcB/A6/XK/Py89g+EJJNJqVarAGi0hYUFWVpa0npPGh8fl0AgoN0NLUfsbqFQyBWHUx3H4QgAOh7230vH/2OwCOQqgADQ4ur1uismU7dHwh0D4OrqyhUAdByHIy4DDw8P/19F634fAACfqFarycHBASdkFoEEAAIAAYAAQAAgAJAB9wF8Pp+sr69LOBzW9ptBuVxOEomElEolADRaLBaT6elprfek3t5eUUrJxsYGp4BGGxgYcMXhVMdxOAKAW97EreM4WARyFUAAaHE8EGI4gIuLC1cAuLy85D7AZzo6OhKPx8MDIaYC4IEQFoEEAAIAAYAAQAAgABAACAAEAAIAAYAAQAAgABAACAAEAHNr5vcPPgTg9/uZcYfV1dVlfRuAYDDIjDuo7u5u6ezs/L4jQDQaZdYdVLO3x4cAIpGINTExwcw7oKGhIZmbm7O+FYBlWbK7uwuCFjc8PCz7+/v/dHR0NHdB+ac/eKyUknQ6rWzblmw2K5VKha3yxfl8PhkcHJSpqSmZmZmxvN7mf43DcssvdxP3AQgABABqqP8Atdv4xjAQlmcAAAAASUVORK5CYII=';
    const THUMBNAIL_VIDEO = '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';
    
    /**
     * @Column(name="date", type="blob", unique=false, nullable=false)
     * @Assert\NotNull(message="document.content.null")
     * @Assert\Length(max=10485760, maxMessage="documentdata.content.maxlength", charset="binary")
     * @var resource The binary content of this file.
     */    
    protected $content;
    
    /**
     * @Column(name="thumbnail", type="blob", unique=false, nullable=true)
     * @var string|resource A thumbnail for this document.
     */
    protected $thumbnail;
        
    /**
     * @Column(name="mime_thumbnail", type="string", length=32, unique=false, nullable=false)
     * @Assert\Length(max=32, maxMessage="documentdata.mime.maxlength")
     * @Assert\NotNull(message="document.mime.null")
     * @var string The mime type of this file, or null when unknown.
     */    
    protected $mimeThumbnail;
    
    public function __construct() {
    }
    
    public function getContent() {
        return $this->content;
    }

    /**
     * @Column(name="mime", type="string", length=32, unique=false, nullable=false)
     * @Assert\Length(max=32, maxMessage="documentdata.mime.maxlength")
     * @Assert\NotNull(message="document.mime.null")
     * @var string The mime type of this file, or null when unknown.
     */    
    protected $mime;
    
    public function getMimeThumbnail() : string {
        return $this->mimeThumbnail;
    }
    
    public function getContentString() : string {
        $c = $this->content;
        if (is_resource($c)) {
            return stream_get_contents($c);
        }
        return (string)$c;
    }
    
    public function getMime() {
        return $this->mime;
    }

    public function getThumbnail() {
        return $this->thumbnail;
    }
    
    public function getThumbnailString() : string {
        $c = $this->thumbnail;
        if (is_resource($c)) {
            return stream_get_contents($c);
        }
        return (string)$c;
    }
    
    public function setMime(string $mime = null) : DocumentData {
        $this->mime = $mime;
        return $this;
    }
    
    public function setMimeThumbnail(string $mimeThumbnail) : DocumentData {
        $this->mimeThumbnail = $mimeThumbnail;
        return $this;
    }
    
    public function setContent($content) : DocumentData {
        $this->content = $content ?? $this->content;
        return $this;
    }
    
    public function setThumbnail($thumbnail) : DocumentData {
        $this->thumbnail = $thumbnail ?? $this->thumbnail;
        return $this;
    }
    
    /**
     * @return string[] An array with two entries, the general and specific
     * part of the mime type, eg. <code>['image','png']</code>
     */    
    public function getMimeParts() {
        $parts = \explode('/', $this->mime);
        if (\sizeof($parts) === 1) {
            $parts[1] = '';
        }
        return $parts;
    }
    
    public function generateThumbnail(int $width=128, int $height=128, int $quality = 80) {
        $thumbnailData = null;
        $thumbnailMime = null;
        try {
            switch ($this->getMimeParts()[0]) {
                case 'image':
                    $thumbnailData  = $this->generateThumbnailImage($width, $height, $quality);
                    $thumbnailMime = 'image/jpeg';
                    break;
                case 'video':
                    $thumbnailData = \base64_decode(self::THUMBNAIL_VIDEO);
                    $thumbnailMime = 'image/png';
                    break;
                case 'text':
                    $thumbnailData = \base64_decode(self::THUMBNAIL_TEXT);
                    $thumbnailMime = 'image/png';
                    break;
            }
        }
        catch (Throwable $e) {
            \error_log("Failed to generate thumbnail for mime $this->mime: " . $e);
            $thumbnailData = null;
        }
        if ($thumbnailData === null) {
            $thumbnailData = \base64_decode(self::THUMBNAIL_GENERIC);
            $thumbnailMime = 'image/png';
        }
        $this->setThumbnail($thumbnailData);
        $this->setMimeThumbnail($thumbnailMime);
    }

    private function generateThumbnailImage(int $width, int $height, int $quality) : string {
        $image = ImageManagerStatic::make($this->getContentString());
        if ($width/$height > $image->getWidth()/$image->getHeight()) {
            $image->resize($height*$image->getWidth()/$image->getHeight(), $height);
        }
        else {
            $image->resize($width, $width*$image->getHeight()/$image->getWidth());
        }
        $image->resizeCanvas($width, $height);
        return (string) ($image->encode('jpg', $quality));
    }
}