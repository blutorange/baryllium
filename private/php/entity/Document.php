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

namespace Entity;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Entity\User;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A document that might have been uploaded, generated automatically etc.
 * For example, this could be an image, a PDF and more.
 *
 * @Entity
 * @Table(name="document")
 * @author madgaksha
 */
class Document extends AbstractEntity {
    /**
     * @Column(name="file_name", type="string", length=255, unique=false, nullable=true)
     * @Assert\Length(max=255, maxMessage="document.filename.maxlength")
     * @var string The name of the file used for creating this document, or when there was no such file.
     */
    protected $fileName;   
    
    /**
     * @Column(name="doc_title", type="string", length=255, unique=false, nullable=false)
     * @Assert\NotBlank(message="document.documenttitle.blank")
     * @Assert\Length(max=255, maxMessage="document.documenttitle.maxlength")
     * @var string The title of this document, which might default to the filename when the user uploads a document.
     */
    protected $documentTitle;
    
    /**
     * @Column(name="description", type="text", unique=false, nullable=true)
     * @var string A description that describes the context of this document.
     */
    protected $description;
    
    /**
     * @Column(name="createtime", type="datetime", unique=false, nullable=false)
     * @Assert\NotNull(message="document.createtime.null")
     * @var DateTime The date when this document was last modified.
     */
    protected $createTime;
    
    /**
     * @Column(name="date", type="blob", unique=false, nullable=false)
     * @Assert\NotNull(message="document.content.null")
     * @Assert\Length(max=20971520, maxMessage="document.content.maxlength", charset="binary")
     * @var resource The binary content of this file.
     */    
    protected $content;
    
    /**
     * @ManyToOne(targetEntity="User")
     * @Assert\NotNull(message="document.uploader.null")
     * @JoinColumn(name="uploader_id", referencedColumnName="id", nullable = false)
     * @var User The use who uploaded this file.
     */
    protected $uploader;
    
    /**
     * @ManyToOne(targetEntity="Course")
     * @JoinColumn(name="course_id", referencedColumnName="id", nullable = false)
     * @Assert\NotNull(message="document.course.null")
     * @var Course The course ("folder") to which this document belongs to.
     */
    protected $course;
    
    /**
     * @Column(name="mime", type="string", length=32, unique=false, nullable=false)
     * @Assert\Length(max=32, maxMessage="document.mime.maxlength")
     * @Assert\NotNull(message="document.mime.null")
     * @var string The mime type of this file, or null when unknown.
     */    
    protected $mime;
    
    public function __construct() {
        $this->createTime = new DateTime();
    }
    
    public function getFileName() {
        return $this->fileName;
    }

    public function getDocumentTitle() : string {
        return $this->documentTitle;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getCreateDate(): DateTime {
        return $this->createTime;
    }

    public function getContent() {
        return $this->content;
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

    public function setFileName(string $fileName = null) : Document {
        $this->fileName = $fileName;
        return $this;
    }

    public function setDocumentTitle(string $documentTitle) : Document {
        $this->documentTitle = $documentTitle ?? $this->documentTitle;
        return $this;
    }

    public function setDescription(string $description = null) : Document {
        $this->description = $description;
        return $this;
    }

    public function setCreateTime(DateTime $createTime) : Document {
        $this->createDate = $createTime ?? $this->createDate;
        return $this;
    }

    public function setContent($content) : Document {
        $this->content = $content ?? $this->content;
        return $this;
    }

    public function setMime(string $mime = null) : Document {
        $this->mime = $mime;
        return $this;
    }
    
    public function getUploader(): User {
        return $this->uploader;
    }

    public function setUploader(User $uploader) {
        $this->uploader = $uploader;
        return $this;
    }
    
    public function getCourse(): Course {
        return $this->course;
    }

    public function setCourse(Course $course) {
        $this->course = $course;
    }
    
    /**
     * @param UploadedFile $file
     * @return Document
     * @throws IOException
     */
    public static function fromUploadFile(UploadedFile $file) : Document {
        $content = file_get_contents($file->getRealPath());        
        if ($content === false) {
            throw new IOException('Failed to read file content.');
        }
        $document = new Document();
        $document->setFileName($file->getClientOriginalName());
        $document->setMime($file->getMimeType());
        $document->setContent($content);
        $document->setDocumentTitle(basename($file->getClientOriginalName(), '.' . $file->getClientOriginalExtension()));
        return $document;
    }
}