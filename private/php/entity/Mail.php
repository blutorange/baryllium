<?php

namespace Entity;

use Gettext\Translator;
use Doctrine\Common\Collections\ArrayCollection;
use Entity\AbstractEntity;
use Entity\Mail;
use Doctrine\ORM\EntityManager;
use Ui\Message;
use Dao\MailDao;

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
     * @Column(name="receiveremail", type="string", length=255, unique=false, nullable=false)
     * @var string
     * The adress that we used to sent the email.
     */
    protected $receiverMail;

    /**
     * @Column(name="subject", type="string", length=255, unique=false, nullable=false)
     * @var string
     * The subject of the mail.
     */
    protected $subject;

    /**
     * @Column(type="string", length=25500, unique=false, nullable=false)
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
     * @Column(name="issent", type="boolean", unique=false, nullable=false)
     * @var bool
     * When the Email was sent, we change the bool from FALSE 0 to TRUE 1.
     */
    protected $isSent;
    
    public function setIsSent(bool $isSent = null) {
        $this->isSent = $isSent ?? false;
    }

    public function getIsSent() {
        return $this->isSent;
    }

    public function setReceiverMail(string $receiverMail = null) {
        $this->receiverMail = $receiverMail;
    }

    public function getReceiverMail() {
        return $this->receiverMail;
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
    
    public function setSentDate(\DateTime $sentDate = null) {
        $this->sentDate = $sentDate;
    }

    public function getSentDate() {
        return $this->sentDate;
    }

    public function validate(array & $errMsg, Translator $translator): bool {
        $valid = true;
        //TODO
        return $valid;
    }

    public function validateMore(array & $errMsg, EntityManager $em, Translator $translator): bool {
        $valid = true;
        //TODO
        return $valid;
    }

    public function getDao(EntityManager $em): MailDao {
        return new MailDao($em);
    }
}
