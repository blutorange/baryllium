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

namespace Moose\Util;

use Doctrine\ORM\EntityManagerInterface;
use Moose\Context\Context;
use Moose\Context\EntityManagerProviderInterface;
use Moose\Context\MailerProviderInterface;
use Moose\Context\TranslatorProviderInterface;
use Moose\Dao\Dao;
use Moose\Dao\MailDao;
use Moose\Entity\Mail;
use Moose\ViewModel\Message;
use Moose\ViewModel\MessageInterface;
use Nette\Mail\IMailer;
use Symfony\Component\Config\Definition\Exception\Exception;
use Throwable;

/**
 * Utility functions for working with mails.
 * @author madgaksha
 */
class MailUtil {
    
    /**
     * Adds a mail to the queue. It will be sent soon.
     * @param Mail $mail
     * @param EntityManagerProviderInterface $emp Entity manager for interacting with the database. When null, tries to acquire it from the Context singleton.
     * @param TranslatorProviderInterface $translator Translator for localizing error messages. When null, tries to acquire it from the Context singleton.
     * @return MessageInterface[] List of errors. Mail was added to queue successfully iff this array is empty.
     */
    public static function & queueMail(Mail $mail, EntityManagerProviderInterface $emp = null, TranslatorProviderInterface $tp = null) : array {
        /* @var $em EntityManagerInterface */
        $em = $emp !== null ? $emp->getEm(CmnCnst::ENTITY_MANAGER_MAIL) : Context::getInstance()->getEm(CmnCnst::ENTITY_MANAGER_MAIL);
        $translator = $tp !== null ? $tp->getTranslator() : Context::getInstance()->getSessionHandler()->getTranslator();
        $dao = Dao::mail($em);
        $errors = $dao->persist($mail, $translator, true);
        if (\sizeof($errors) > 0) {
            return $errors;
        }
        return $errors;
    }
    
    /**
     * 
     * @param Mail $mail
     * @param EntityManagerProviderInterface $emp
     * @param MailerProviderInterface $mp
     * @param TranslatorProviderInterface $tp
     * @return MessageInterface[] List of errors. Mail was added to queue successfully iff this array is empty.
     */
    public static function & sendMail(Mail $mail,
            EntityManagerProviderInterface $emp = null,
            MailerProviderInterface $mp = null,
            TranslatorProviderInterface $tp = null) : array {
        $errors = [];
        $translator = $tp !== null ?
            $tp->getTranslator() :
            Context::getInstance()->getSessionHandler()->getTranslator();
        try {
            $mailer = $mp !== null ? $mp->getMailer() : Context::getInstance()->getMailer();
        } catch (Exception $e) {
            Context::getInstance()->getLogger()->log("Failed to make mailer: " . $e);
            $errors[] = [Message::dangerI18n('mail.error', 'mail.mailer.creation', $translator)];
            return $errors;
        }
        $em = $emp !== null ?
        $emp->getEm(CmnCnst::ENTITY_MANAGER_MAIL) :
        Context::getInstance()->getEm(CmnCnst::ENTITY_MANAGER_MAIL);
        \array_merge($errors, self::queueMail($mail, $emp, $tp));
        \array_merge($errors, self::sendMailList([$mail], $mailer, $translator, Dao::mail($em)));
        return $errors;
    }
    
    /**
     * Processes the mail queue and tries to send all unsent mails.
     * @param MailerProviderInterface $mp
     * @param EntityManagerProviderInterface $emp
     * @param TranslatorProviderInterface $tp
     * @return MessageInterface[] List of errors. Mail was added to queue successfully iff this array is empty.
     */
    public static function & processQueue(int $numberOfMails = -1, MailerProviderInterface $mp = null, EntityManagerProviderInterface $emp = null, TranslatorProviderInterface $tp = null) : array {
        /* @var $mail Mail */
        /* @var $mailer IMailer */
        $translator = $tp !== null ?
            $tp->getTranslator() :
            Context::getInstance()->getSessionHandler()->getTranslator();
        try {
            $mailer = $mp !== null ? $mp->getMailer() : Context::getInstance()->getMailer();
        } catch (Exception $e) {
            Context::getInstance()->getLogger()->log("Failed to make mailer: " . $e);
            $errors = [Message::dangerI18n('mail.error', 'mail.mailer.creation', $translator)];
            return $errors;
        }
        $em = $emp !== null ?
            $emp->getEm(CmnCnst::ENTITY_MANAGER_MAIL) :
            Context::getInstance()->getEm(CmnCnst::ENTITY_MANAGER_MAIL);
        $dao = Dao::mail($em);
        try {
            $mailList = $numberOfMails < 1 ? $dao->findAllUnsent() : $dao->findNUnsent($numberOfMails);
        }
        catch (Throwable $e) {
            Context::getInstance()->getLogger()->log("Failed to retrieve mails: " . $e);
            $errors = [Message::dangerI18n('mail.error', 'mail.queue.retrieval', $translator)];
            return $errors;
        }
        $errors = & self::sendMailList($mailList, $mailer, $translator, $dao);
        return $errors;
    }
    
    /**
     * 
     * @param Mail[] $mailList List of mails to send.
     * @param IMailer $mailer
     * @param PlaceholderTranslator $translator For translating errors.
     * @param MailDao $dao Data access object for mails.
     * @return array Errors that may have occurred.
     */
    private static function & sendMailList(array $mailList, IMailer $mailer, PlaceholderTranslator $translator, MailDao $dao) : array {
        $errors = [];
        foreach ($mailList as $mail) {
            $mail->setIsSent(true);
            $dao->queue($mail);
        }
        foreach ($mailList as $mail) {
            self::doSend($mail, $mailer, $errors);
        }
        \array_merge($errors, $dao->persistQueue($translator, true));
        return $errors;
    }
    
    private static function doSend(Mail $mail, IMailer $mailer, array & $errors) {
        $message = $mail->toMessage();
        try {
            $mailer->send($message);
        } catch (Exception $e) {
            Context::getInstance()->getLogger()->log("Failed to send message: " . $e);
            \array_push($errors, $e);
        }
    }
}