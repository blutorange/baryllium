<?php

namespace Sandbox;

use Context;
use Entity\Mail;
use Util\DebugUtil;
use Util\MailUtil;

/* Use this for quickly testing some php code... */

require_once './bootstrap.php';

$m= Context::getInstance()->getMailer();

#$mail = (new Mail())->setSubject('Another test mail')->setContent('<p><i>italics</i></p>')->setIsHtml(true)->setMailFrom('sensenmann5@gmail.com')->setMailTo('sensenmann5@gmail.com');
#DebugUtil::dump(MailUtil::queueMail($mail));

DebugUtil::dump(MailUtil::processQueue());

echo DebugUtil::getDumpHtml();