<?php

namespace Entity;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity for EMails they are sent to Users
 *
 * @Entity
 * @Table(name="mail")
 * 
 * @author David
 */
class Mail extends AbstractEntity {

    const TABLE_NAME = "mail";

    /**
     * @Column(name="mailto", type="string", length=255, unique=false, nullable=false)
     * @Assert\NotEmpty(message="mail.mailto.empty")
     * @Assert\Length(maxLength=255, maxMessage="mail.mailto.maxlength")
     * @Assert\Email(message="mail.mailto.invalid")
     * @var string The address to which the mail is to be sent.
     */
    protected $mailTo;

    /**
     * @Column(name="mailfrom", type="string", length=255, unique=false, nullable=false)
     * @Assert\NotEmpty(message="mail.mailfrom.empty")
     * @Assert\Length(maxLength=255, maxMessage="mail.mailfrom.maxlength")
     * @Assert\Email(message="mail.mailfrom.invalid")
     * @var string The address from which the mail is sent.
     */
    protected $mailFrom;

    /**
     * @Column(name="subject", type="string", length=255, unique=false, nullable=false)
     * @Assert\NotNull(message="mail.subject.empty")
     * @Assert\Length(maxLength=255, maxMessage="mail.subject.maxlength")
     * @var string
     * The subject of the mail.
     */
    protected $subject;

    /**
     * @Column(name="ishtml", type="boolean", nullable=true)
     * @var bool Whether the content of this mail is HTML (or text).
     */    
    protected $isHtml;
    
    /**
     * @Column(name="content", type="text", unique=false, nullable=false)
     * @Assert\NotNull(message="mail.content.empty")
     * @Assert\Length(maxLength=255, maxMessage="mail.content.maxlength")
     * @var string
     * The content of the email.
     */
    protected $content;

    /**
     * @Column(name="sentdate", type="date", unique=false, nullable=true)
     * @var \DateTime Date when the mail was sent.
     */
    protected $sentDate;

    /**
     * @Column(name="issent", type="boolean", unique=false, nullable=true)
     * @var bool Whether the mail was sent successfully.
     */
    protected $isSent;
    
    public function setIsSent(bool $isSent = null) {
        $this->isSent = $isSent ?? false;
    }

    public function getIsSent() : bool {
        return $this->isSent ?? false;
    }

    public function getIsHtml() : bool {
        return $this->isHtml ?? false;
    }

    public function setIsHtml(bool $isHtml) {
        $this->isHtml = $isHtml ?? false;
        return $this;
    }
        
    public function setMailTo(string $mailTo = null) {
        $this->mailTo = $mailTo;
    }

    public function getMailTo() {
        return $this->mailTo;
    }
    
    public function setMailFrom(string $mailFrom = null) : Mail {
        $this->mailFrom = $mailFrom;
        return $this;
    }

    public function getMailFrom() {
        return $this->mailFrom;
    }

    public function setSubject(string $subject = null) {
        $this->subject = $subject;
    }

    public function getSubject() {
        return $this->subject;
    }

     public function setContent(string $content = null) {         
        $this->content = $content;
    }

    public function getContent() {
        return $this->content;
    }
    
    public function setSentDate(DateTime $sentDate = null) {
        $this->sentDate = $sentDate;
    }

    public function getSentDate() {
        return $this->sentDate;
    }
}
