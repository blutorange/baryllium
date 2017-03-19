<?php

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
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
     * @Assert\NotEmpty(message="document.documenttitle.empty")
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
     * @Column(name="edit_date", type="date", unique=false, nullable=false)
     * @var DateTime The date when this document was last modified.
     */
    protected $editDate;
    
    /**
     * @Column(name="date", type="blob", unique=false, nullable=false)
     * @Assert\NotNull(message="document.content.empty")
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
     * @OneToOne(targetEntity="Course")
     * @JoinColumn(name="course_id", referencedColumnName="id", nullable = false)
     * @Assert\NotNull(message="document.forum.empty")
     * @var Course The course ("folder") to which this document belongs to.
     */
    protected $course;
    
    /**
     * @ManyToMany(targetEntity="TutorialGroup")
     * @JoinTable(name="document_tutorialgroup",
     *      joinColumns={@JoinColumn(name="document_id", referencedColumnName="id", nullable=false)},
     *      inverseJoinColumns={@JoinColumn(name="tutorialgroup_id", referencedColumnName="id", nullable=false)}
     *      )
     * @Assert\NotNull
     * @var ArrayCollection List of tutorial groups which may access this document.
     */
    protected $tutorialGroupList;
    
    /**
     * @Column(name="mime", type="string", length=32, unique=false, nullable=true)
     * @Assert\Length(max=32, maxMessage="document.mime.maxlength")
     * @var string The mime type of this file, or null when unknown.
     */    
    protected $mime;
    
    public function __construct() {
        $this->$tutorialGroupList = new ArrayCollection();
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
    
    public function getCourse(): Course {
        return $this->course;
    }

    public function setCourse(Course $course) {
        $this->course = $course;
    }
    
    public function addTutorialGroup(TutorialGroup $tutorialGroup) {
        $this->tutorialGroupList->add($tutorialGroup);
    }
    public function removeTutorialGroup(TutorialGroup $tutorialGroup) {
        $this->tutorialGroupList->removeElement($tutorialGroup);
    }
    
    public function clearTutorialGroup() {
        $this->tutorialGroupList->clear();
    }
    
}