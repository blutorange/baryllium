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
use Doctrine\ORM\Mapping\Table;
use Nette\Mail\Message as NetteMessage;
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
     * @Assert\NotBlank(message="mail.mailto.empty")
     * @Assert\Length(max=255, maxMessage="mail.mailto.maxlength")
     * @Assert\Email(message="mail.mailto.invalid")
     * @var string The address to which the mail is to be sent.
     */
    protected $mailTo;

    /**
     * @Column(name="mailfrom", type="string", length=255, unique=false, nullable=false)
     * @Assert\NotBlank(message="mail.mailfrom.empty")
     * @Assert\Length(max=255, maxMessage="mail.mailfrom.maxlength")
     * @Assert\Email(message="mail.mailfrom.invalid")
     * @var string The address from which the mail is sent.
     */
    protected $mailFrom;

    /**
     * @Column(name="subject", type="string", length=255, unique=false, nullable=false)
     * @Assert\NotNull(message="mail.subject.empty")
     * @Assert\Length(max=255, maxMessage="mail.subject.maxlength")
     * @var string
     * The subject of the mail.
     */
    protected $subject;

    /**
     * @Column(name="ishtml", type="boolean", nullable=false)
     * @var bool Whether the content of this mail is HTML (or text).
     */    
    protected $isHtml;
    
    /**
     * @Column(name="content", type="text", unique=false, nullable=false)
     * @Assert\NotNull(message="mail.content.empty")
     * @Assert\Length(max=255, maxMessage="mail.content.maxlength")
     * @var string
     * The content of the email.
     */
    protected $content;

    /**
     * @Column(name="sentdate", type="datetime", unique=false, nullable=true)
     * @var DateTime Date when the mail was sent.
     */
    protected $sentDate;

    /**
     * @Column(name="creationdate", type="datetime", unique=false, nullable=false)
     * @Assert\NotNull(message="mail.creationdate.null")
     * @var DateTime Date when the mail was created.
     */
    protected $creationDate;

    /**
     * @Column(name="issent", type="boolean", unique=false, nullable=false)
     * @var bool Whether the mail was sent successfully.
     */
    protected $isSent;

    public function __construct() {
        $this->creationDate = new DateTime();
        $this->isSent = false;
        $this->isHtml = true;
    }
    
    public function setIsSent(bool $isSent) : Mail {
        $isSent = $isSent ?? false;
        if (($this->isSent) === false && $isSent === true) {
            $this->sentDate = new DateTime();
        }
        $this->isSent = $isSent;
        return $this;
    }

    public function getIsSent() : bool {
        return $this->isSent ?? false;
    }

    public function getIsHtml() : bool {
        return $this->isHtml ?? false;
    }

    public function setIsHtml(bool $isHtml) : Mail {
        $this->isHtml = $isHtml ?? false;
        return $this;
    }
        
    public function setMailTo(string $mailTo = null) : Mail {
        $this->mailTo = $mailTo;
        return $this;        
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

    public function setSubject(string $subject = null) : Mail {
        $this->subject = $subject;
        return $this;
    }

    public function getSubject() {
        return $this->subject;
    }

     public function setContent(string $content = null) : Mail {         
        $this->content = $content;
        return $this;
    }

    public function getContent() {
        return $this->content;
    }
    
    public function setSentDate(DateTime $sentDate = null) : Mail {
        $this->sentDate = $sentDate;
        return $this;
    }

    public function getSentDate() {
        return $this->sentDate;
    }
    
    public function getCreationDate(): DateTime {
        return $this->creationDate;
    }

    /**
     * @return NetteMessage
     */
    public function toMessage() : NetteMessage {
        $message = new NetteMessage();
        $message->setEncoding('UTF-8');
        $message->setFrom($this->getMailFrom());
        $message->addTo($this->getMailTo());
        $message->setSubject($this->getSubject());
        if ($this->isHtml) {
            $message->setHtmlBody($this->getContent());
        }
        else {
            $message->setBody($this->getContent());
        }
        return $message;
    }

}
