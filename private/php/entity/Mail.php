<?php

namespace Entity;

use Dao\MailDao;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Entity\AbstractEntity;
use Nette\Mail\Message;
use Ui\PlaceholderTranslator;

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
     * @var string The address to which the mail is to be sent.
     */
    protected $mailTo;
    private static $MAX_LENGTH_MAILTO = 255;

    /**
     * @Column(name="mailfrom", type="string", length=255, unique=false, nullable=false)
     * @var string The address from which the mail is sent.
     */
    protected $mailFrom;
    private static $MAX_LENGTH_MAILFROM = 255;

    /**
     * @Column(name="subject", type="string", length=255, unique=false, nullable=false)
     * @var string
     * The subject of the mail.
     */
    protected $subject;
    private static $MAX_LENGTH_SUBJECT = 255;

    /**
     * @Column(name="ishtml", type="boolen", nullable=true)
     * @var bool Whether the content of this mail is HTML (or text).
     */    
    protected $isHtml;
    
    /**
     * @Column(type="text", unique=false, nullable=false)
     * @var string
     * The content of the email.
     */
    protected $content;

    /**
     * @Column(name="sentdate", type="date", unique=false, nullable=true)
     * @var string
     * Date when the mail was sent.
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

    public function validate(array & $errMsg, PlaceholderTranslator $translator): bool {
        $valid = true;
        $valid = $valid && $this->validateNonEmptyStringLength($this->subject,
                        self::$MAX_LENGTH_SUBJECT, $errMsg, $translator,
                        'error.validation', 'error.mail.subject.empty',
                        'error.mail.subject.overlong');
        $valid = $valid && $this->validateNonEmptyStringLength($this->mailTo,
                        self::$MAX_LENGTH_MAILTO, $errMsg, $translator,
                        'error.validation', 'error.mail.mailto.empty',
                        'error.mail.mail.overlong');
        $valid = $valid && $this->validateNonEmptyStringLength($this->mailFrom,
                        self::$MAX_LENGTH_MAILFROM, $errMsg, $translator,
                        'error.validation', 'error.mail.mailto.empty',
                        'error.mail.mail.overlong');
        return $valid;
    }

    public function validateMore(array & $errMsg, EntityManager $em, PlaceholderTranslator $translator): bool {
        $valid = true;
        //TODO
        return $valid;
    }

    public function getDao(EntityManager $em): MailDao {
        return new MailDao($em);
    }
    
    public function toNetteMail() : Message {
        $mail = new Message();
        $mail->setFrom($this->getMailFrom());
        $mail->setSubject($this->getSubject());
        $mail->addTo($this->getMailTo());
        if ($this->getIsHtml()) {
            $mail->setHtmlBody($this->getContent());        
        }
        else {
            $mail->setBody($this->getContent());
        }
        return $mail;
    }
}
