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

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Tree;
use Gedmo\Mapping\Annotation\TreeClosure;
use Gedmo\Mapping\Annotation\TreeLevel;
use Gedmo\Mapping\Annotation\TreeParent;
use Moose\Util\MimeTypeGuess;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A document that might have been uploaded, generated automatically etc.
 * For example, this could be an image, a PDF and more.
 *
 * @Entity(repositoryClass="\Gedmo\Tree\Entity\Repository\ClosureTreeRepository")
 * @Table(name="document")
 * @Tree(type="closure")
 * @TreeClosure(class="Moose\Entity\DocumentClosure")
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
     * @Column(name="is_directory", type="boolean", unique=false, nullable=false)
     * @var bool Whether this is a regular file (false) or a directory (true).
     */
    protected $isDirectory;
    
    /**
     * @Column(name="createtime", type="datetime", unique=false, nullable=false)
     * @Assert\NotNull(message="document.createtime.null")
     * @var DateTime The date when this document was last modified.
     */
    protected $createTime;
    
    /**
     * @OneToOne(targetEntity="DocumentData", orphanRemoval = true)
     * @JoinColumn(name="data_id", referencedColumnName="id")
     * @Assert\NotNull(message="document.content.null")
     * @var DocumentData The binary content of this file.
     */
    protected $data;
    
    /**
     * @Column(name="size", type="integer", unique=false, nullable=false)
     * @Assert\GreaterThanOrEqual(value=0, message="document.size.negative")
     * @Assert\NotNull(message="document.size.null")
     * @var string The mime type of this file, or null when unknown.
     */    
    protected $size;

    /**
     * @Column(name="mime_thumbnail", type="string", length=32, unique=false, nullable=false)
     * @Assert\Length(max=32, maxMessage="document.mime.maxlength")
     * @Assert\NotNull(message="document.mime.null")
     * @var string The mime type of this file, or null when unknown.
     */    
    protected $mimeThumbnail;
    
    /**
     * @Column(name="mime", type="string", length=32, unique=false, nullable=false)
     * @Assert\Length(max=64, maxMessage="document.mime.maxlength")
     * @Assert\NotNull(message="document.mime.null")
     * @var string The mime type of this file, or null when unknown.
     */    
    protected $mime;
    
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
     * @TreeLevel
     * @Column(name="tree_level", type="integer", nullable=true)
     * @var int Store node level for the closure tree strategy.
     */
    private $level;

    /**
     * @TreeParent
     * @JoinColumn(name="tree_parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @ManyToOne(targetEntity="Document", inversedBy="children")
     */
    private $parent;
        
    public function __construct() {
        $this->createTime = new DateTime();
        $this->isDirectory = false;
    }
    
    public function getFileName() {
        return $this->fileName;
    }

    public function getDocumentTitle() : string {
        return $this->documentTitle ?? '';
    }

    public function getDescription() {
        return $this->description;
    }

    /**
     * @return string 
     */
    public function getMimeThumbnail() : string {
        return $this->mimeThumbnail;
    }
    
    /**
     * @return string 
     */
    public function getMime() {
        return $this->mime;
    }

    public function getSize() : int {
        return $this->size ?? 0;
    }

    public function setSize(int $size) : Document {
        $this->size = $size < 0 ? 0 : $size;
        return $this;
    }
    
    public function getCreateTime(): DateTime {
        return $this->createTime;
    }

    public function getData() : DocumentData {
        return $this->data;
    }
    
    public function getIsDirectory() : bool {
        return $this->isDirectory ?? false;
    }

    public function setIsDirectory(bool $isDirectory = false) : Document {
        $this->isDirectory = $isDirectory ?? false;
        return $this;
    }
    
    /**
     * @param string $mime Mime type of the main content.
     * @return Document This document for chaining.
     */

    public function setMime(string $mime = null) : Document {
        $this->mime = $mime;
        return $this;
    }
    
    /**
     * @param string $mimeThumbnail Mime type of the thumbnail.
     * @return Document This document for chaining.
     */
    public function setMimeThumbnail(string $mimeThumbnail) : Document {
        $this->mimeThumbnail = $mimeThumbnail;
        return $this;
    }
    
    /**
     * @return string[] An array with two entries, the general and specific
     * part of the mime type, eg. <code>['image','png']</code>
     */    
    public function getMimeParts() {
        $matches = [];
        if (1 === \preg_match('/^(\w+)\/([\w+\-\.]+)/', $this->mime, $matches)) {
            return [$matches[1], $matches[2]];
        }
        else {
            $parts = \explode('/', $this->mime);
            if (\sizeof($parts) === 1) {
                $parts[1] = '';
            }
            return $parts;
        }
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
        $this->createTime = $createTime ?? $this->createTime;
        return $this;
    }

    public function setData(DocumentData $data) : Document {
        $this->data = $data ?? $this->data;
        return $this;
    }
    
    public function getUploader(): User {
        return $this->uploader;
    }

    public function setUploader(User $uploader) : Document {
        $this->uploader = $uploader;
        return $this;
    }
    
    public function getCourse(): Course {
        return $this->course;
    }

    public function setCourse(Course $course) : Document {
        $this->course = $course;
        return $this;
    }

    /** @return int */
    public function getLevel() {
        return $this->level;
    }

    /** @return Document|null */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param int $level
     * @return Document
     */
    public function setLevel($level) : Document {
        $this->level = $level;
        return $this;
    }
    
    /**
     * @param Document|null $parent
     * @return Document
     */
    public function setParent($parent) : Document {
        $this->parent = $parent;
        return $this;
    }
    
    /**
     * Used internally by DoctrineExtensions.
     * @param DocumentClosure $closure
     */
    public function addClosure(DocumentClosure $closure) {
        $this->closures[] = $closure;
    }
    
    /**
     * @param UploadedFile $file
     * @return Document With the document data.
     * @throws IOException
     */
    public static function fromUploadFile(UploadedFile $file) : Document {
        $content = \file_get_contents($file->getRealPath());        
        if ($content === false) {
            throw new IOException('Failed to read file content.');
        }
        $data = new DocumentData();
        $data->setContent($content);
        $document = Document::create()
            ->setFileName($file->getClientOriginalName())
            ->setDocumentTitle(\basename($file->getClientOriginalName(), '.' . $file->getClientOriginalExtension()))
            ->setData($data)
            ->setMime(MimeTypeGuess::getInstance()->guess($file->getPathname()))
            ->generateThumbnail();
        $document->setSize($file->getSize());
        return $document;
    }

    public static function create() : Document {
        return new Document();
    }

    /**
     * Create a new directory document. You must set the uploader and course
     * manually.
     * @param string $name Name of this directory.
     * @return Document The freshly created directory.
     */    
    public static function createDirectory(string $name) : Document {
        $document = self::create()
                ->setFileName($name)
                ->setSize(0)
                ->setDocumentTitle($name)
                ->setIsDirectory(true)
                ->setMime('inode/directory')
                ->setData(DocumentData::createForDirectory());
        $document->generateThumbnail($document->getMimeParts());
        return $document;
    }

    /**
     * Must be called after document data and content was set.
     * @return Document This document for chaining.
     */
    public function generateThumbnail() : Document {
        $this->setMimeThumbnail($this->getData()->generateThumbnail($this->getMimeParts()));
        return $this;
    }
    
    public function sanitize() {
        if ($this->documentTitle === null)
            $this->documentTitle = $this->fileName ?? '';
    }
}