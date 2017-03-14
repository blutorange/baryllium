<?php

namespace Entity;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Ui\PlaceholderTranslator;

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
     * @var string The name of the file used for creating this document, or when there was no such file.
     */
    private static $MAX_LENGTH_FILENAME = 255;
    protected $fileName;   
    
    /**
     * @Column(name="doc_title", type="string", length=255, unique=false, nullable=false)
     * @var string The title of this document, which might default to the filename when the user uploads a document.
     */
    private static $MAX_LENGTH_DOCUMENTTITLE = 255;
    protected $documentTitle;
    
    /**
     * @Column(name="description", type="text", unique=false, nullable=true)
     * @var string A description that describes the context of this document.
     */
    protected $description;
    
    /**
     * @Column(name="edit_date", type="date", unique=false, nullable=false)
     * @var DateTime The date when this document was last modified.
     */
    protected $editDate;
    
    /**
     * @Column(name="date", type="blob", unique=false, nullable=false)
     * @var Resource The binary content of this file.
     */    
    protected $content;
    
    /**
     * @OneToOne(targetEntity="User")
     * @JoinColumn(name="uploader_id", referencedColumnName="id", nullable = false)
     * @var User The use who uploaded this file.
     */
    protected $uploader;
    
    /**
     * @OneToOne(targetEntity="Forum")
     * @JoinColumn(name="forum_id", referencedColumnName="id", nullable = false)
     * @var User The forum ("folder") to which this document belongs to.
     */
    protected $forum;
    
    /**
     * @Column(name="mime", type="string", length=32, unique=false, nullable=true)
     * @var string The mime type of this file, or null when unknown.
     */    
    private static $MAX_LENGTH_MIME = 32;
    protected $mime;
    
    public function getFileName() {
        return $this->fileName;
    }

    public function getDocumentTitle() : string {
        return $this->documentTitle;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getEditDate(): DateTime {
        return $this->editDate;
    }

    public function getContent(): Resource {
        return $this->content;
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

    public function setEditDate(DateTime $editDate) : Document {
        $this->editDate = $editDate ?? $this->editDate;
        return $this;
    }

    public function setContent(Resource $content) : Document {
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
        
    public function validate(array & $errMsg, PlaceholderTranslator $translator): bool {
        $valid = true;
        $valid = $valid && $this->validateNonEmptyStringLength($this->documentTitle,
                self::$MAX_LENGTH_DOCUMENTTITLE, $errMsg, $translator,
                'error.validation', 'error.document.title.empty',
                'error.document.title.overlong');
        $valid = $valid && $this->validateStringLength($this->fileName,
                self::$MAX_LENGTH_FILENAME, $errMsg, $translator,
                'error.validation', 'error.document.filename.overlong');
        $valid = $valid && $this->validateNonNull($this->editDate,
                $errMsg, $translator, 'error.validation',
                'error.document.editdate.empty');
        $valid = $valid && $this->validateNonNull($this->content,
                $errMsg, $translator, 'error.validation',
                'error.document.content.empty');
        $valid = $valid && $this->validateNonNull($this->uploader,
                $errMsg, $translator, 'error.validation',
                'error.document.uploader.empty');
        $valid = $valid && $this->validateNonNull($this->forum,
                $errMsg, $translator, 'error.validation',
                'error.document.forum.empty');
        $valid = $valid && $this->validateStringLength($this->mime,
                self::$MAX_LENGTH_MIME, $errMsg, $translator,
                'error.validation', 'error.document.mime.overlong');
        return $valid;
    }
}