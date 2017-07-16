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

namespace Moose\Controller;

use Defuse\Crypto\Key;
use Doctrine\ORM\Tools\SchemaTool;
use Moose\Context\Context;
use Moose\Context\MooseConfig;
use Moose\Seed\DormantSeed;
use Moose\Util\CmnCnst;
use Moose\Util\DebugUtil;
use Moose\Util\EncryptionUtil;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequest;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

class SetupController extends BaseController {

    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        // Check whether we can establish a connection to the database.
        // Otherwise, the user must setup the database.
        try {
            Context::getInstance()->getEm()->getConnection()->connect();
            Context::getInstance()->closeEm();
            $this->renderTemplate('t_setup_redirect_user');
        }
        catch (\Throwable $e) {
            $this->renderTemplate('t_setup', [
                'action' => $_SERVER['PHP_SELF'] . '?' . \http_build_query(['dbg-db-md' => $request->getParam('dbg-db-md')]),
                'form' => [
                    'sysmail' => Context::getInstance()->getConfiguration()->getSystemMailAddress(),
                    'server' => self::getDefaultServer(),
                    'taskserver' => self::getDefaultServer()
                ]
            ]);
        }
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        // Read options for database setup and apply defaults.
        $logfile = $request->getParam('logfile');
        $port = $request->getParamInt('port', 3306);
        $host = $request->getParam('host');
        $dbname = $request->getParam('dbname');
        $dbnameTest = $request->getParam('dbnameTest');
        $dbnameDev = $request->getParam('dbnameDev');
        $doctrineProxy = $request->getParam('dbProxyDir', '');
        $user = $request->getParam('user');
        $pass = $request->getParam('pass');
        $collation = $request->getParam('collation');
        $encoding = $request->getParam('encoding');
        $driver = $request->getParam('driver', 'mysql');
        $systemMail = $request->getParam('sysmail', 'admin@example.com');
        $server = $request->getParam('server', self::getDefaultServer());
        $taskServer = $request->getParam('taskserver', self::getDefaultServer());

        if (empty($doctrineProxy)) {
            $doctrineProxy = \dirname(__DIR__, 1);
        }
        
        // Retrieve database mode. By default, we use production mode, unless
        // indicates otherwise via URL params.
        $dbMode = $request->getParam(CmnCnst::URL_PARAM_DEBUG_ENVIRONMENT, 'production');
        switch($dbMode) {
            case MooseConfig::ENVIRONMENT_TESTING:
                $dbSetupName = $dbnameTest;
                break;
            case MooseConfig::ENVIRONMENT_DEVELOPMENT:
                $dbSetupName = $dbnameDev;
                break;
            case MooseConfig::ENVIRONMENT_PRODUCTION:
            default:
                $dbMode = MooseConfig::ENVIRONMENT_PRODUCTION;
                $dbSetupName = $dbname;
                break;
        }
        
        // Read options for mail setup and apply defaults.
        $mailType = $request->getParam('mailtype', 'php');
        $mailType = $mailType === 'smtp' ? 'smtp' : 'php';
        $smtphost = $request->getParam('smtphost', null);
        $smtpuser = $request->getParam('smtpuser', null);
        $smtppass = $request->getParam('smtppass', null);
        $smtpport = $request->getParam('smtpport', '465');
        $smtpsec = $request->getParam('smtpsec', 'ssl');
        $smtppers = $request->getParam('smtppers', 'off');
        $smtptime = $request->getParam('smtptime', '20');
        $smtpbind = $request->getParam('smtpbind', '0');
        
        if ($mailType === 'smtp') {
            $detail = '';
            if (empty($smtphost)) {
                $detail .= 'Host must be given when SMTP is selected. ';
            }
            if (empty($smtpuser)) {
                $detail .= 'Username must be given when SMTP is selected. ';
            }
            if (empty($smtppass)) {
                $detail = 'Password must be given when SMTP is selected. ';
            }
            if (strlen($detail) > 0) {
                $this->renderTemplate('t_setup', [
                    'messages' => [
                        Message::danger("SMTP options incorrect.", $detail)
                    ],
                    'action' => $_SERVER['PHP_SELF'] . '?' . \http_build_query([CmnCnst::URL_PARAM_DEBUG_ENVIRONMENT => $dbMode]),
                    'form' => $request->getAllParams(HttpRequest::PARAM_FORM)
                ]);
                return;                
            }
        }

        // Write the configuration to the configuration file.
        try {
            $context = $this->configureContext($systemMail, $dbMode, $dbname, $user, $pass,
                    $host, $port, $driver, $collation, $encoding, $dbnameDev,
                    $dbnameTest, $mailType, $smtphost, $smtpport, $smtpuser,
                    $smtppass, $smtpsec, $smtppers, $smtptime , $smtpbind,
                    $logfile, $server, $taskServer, $doctrineProxy);
        }
        catch (Throwable $e) {
            Context::getInstance()->getLogger()->log($e, "Failed to configure context");
            $this->renderTemplate('t_setup', [
                'messages' => [
                    Message::danger(
                            'Failed to write config file: ' . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(),
                            $e->getTraceAsString()
                )],
                'form' => $request->getAllParams(HttpRequest::PARAM_FORM),
                'action' => $_SERVER['PHP_SELF'] . '?' . \http_build_query([CmnCnst::URL_PARAM_DEBUG_ENVIRONMENT => $dbMode])
            ]);
            return;
        }
        
        // Run the database initialization tool and create the schema.
        try {
            $this->initDb($context, $doctrineProxy);
        }
        catch (\Throwable $e) {
            Context::getInstance()->getLogger()->log($e, "Failed to initialize database schema");
            $this->renderTemplate('t_setup', [
                'messages' => [
                    Message::danger(
                            'Failed to initialize DB schema: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(),
                            $e->getTraceAsString()
                )],
                'action' => $_SERVER['PHP_SELF'] . '?' . \http_build_query([CmnCnst::URL_PARAM_DEBUG_ENVIRONMENT => $dbMode]),
                'form' => $request->getAllParams(HttpRequest::PARAM_FORM)
            ]);
            return;
        }

        // Add some initial entries to the database, such as the mail scheduled task.
        try {
            $this->prepareDb(Context::getInstance());
        }
        catch (Throwable $e) {
            Context::getInstance()->getLogger()->log($e, "Failed to prepare db");
            $this->renderTemplate('t_setup', [
                'messages' => [
                    Message::danger(
                            'Failed to prepare database: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(),
                            $e->getTraceAsString()
                )],
                'form' => $request->getAllParams(HttpRequest::PARAM_FORM),
                'action' => $_SERVER['PHP_SELF'] . '?' . \http_build_query([CmnCnst::URL_PARAM_DEBUG_ENVIRONMENT => $dbMode])
            ]);
            return;
        }
        
        // Make sure we open any closed entity managers.
        $context->closeEm();

        // Write the configuration to file.
        try {
            $this->writeConfigFile();
        }
        catch (\Throwable $e) {
            Context::getInstance()->getLogger()->log($e, "Could not create directory for config file $path");
            $this->renderTemplate('t_setup', [
                'messages' => [
                    Message::warn(
                            'Failed to write config file: ' . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(),
                            $e->getTraceAsString()
                )],
                'form' => $request->getAllParams(HttpRequest::PARAM_FORM),
                'action' => $_SERVER['PHP_SELF'] . '?' . \http_build_query([CmnCnst::URL_PARAM_DEBUG_ENVIRONMENT => $dbMode])
            ]);
            return;
        }

        // Remove the FIRST_INSTALL file.
        $firstInstall = \dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'FIRST_INSTALL';
        if (!unlink($firstInstall)) {
            $response->addMessage(Message::infoI18n('setup.unlink.message', 'setup.unlink.details', $this->getTranslator(), ['name' => $firstInstall]));
        }
       
        $this->renderTemplate('t_setup_redirect_user', [
            'privateKey' => Context::getInstance()->getConfiguration()->getPrivateKey()->saveToAsciiSafeString()
        ]);
    }

    private function initDb(Context $context, string $doctrineProxy = null) {
        $em = $context->getEm();
        // Generate proxies.
        $metas = $em->getMetadataFactory()->getAllMetadata();
        $em->getProxyFactory()->generateProxyClasses($metas,
                empty($doctrineProxy) ? \dirname(__DIR__, 1) : $doctrineProxy);
        // Update database schema.
        $tool = new SchemaTool($em);
        $tool->dropDatabase();
        $tool->updateSchema($metas);
    }

    private function writeConfigFile() {
        $path = $this->getPhinxPath();
        $isDev = Context::getInstance()->getConfiguration()->isNotEnvironment(
                MooseConfig::ENVIRONMENT_PRODUCTION);
        Context::getInstance()->getConfiguration()->saveAs($path, $isDev, !$isDev);
    }
    
    private function configureContext($systemMail, $dbMode, $dbname, $user, $pass, $host, $port,
            $driver, $collation, $encoding, $dbnameDev, $dbnameTest, $mailType,
            $smtphost, $smtpport, $smtpuser, $smtppass, $smtpsec, $smtppers,
            $smtptime , $smtpbind, $logfile, $server, $taskServer, $doctrineProxy) {
        $yaml = $this->makeConfig($systemMail, $dbMode, $dbname, $user, $pass,
                $host, $port, $driver, $collation, $encoding, $dbnameDev,
                $dbnameTest, $mailType, $smtphost, $smtpport, $smtpuser,
                $smtppass, $smtpsec, $smtppers, $smtptime , $smtpbind, $logfile,
                $server, $taskServer, $doctrineProxy);
        $pk = Key::createNewRandomKey();
        if ($dbMode !== MooseConfig::ENVIRONMENT_DEVELOPMENT
                && $dbMode !== MooseConfig::ENVIRONMENT_TESTING) {
            EncryptionUtil::encryptArray($yaml, $pk);
            $yaml['is_encrypted'] = true;
        }
        else {
            $yaml['private_key'] = $pk->saveToAsciiSafeString();
            $yaml['is_encrypted'] = false;
        }
        $config = MooseConfig::createFromArray($yaml, $pk);
        Context::reconfigureInstance(null, null, $config);
        return Context::getInstance();
    }

    private function getPhinxPath() {
        return \dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR  . 'phinx.yml';
    }

    private function makeConfig($systemMail, $dbMode, $dbname, $user, $pass,
            $host, $port, $driver, $collation, $encoding, $dbNameDev,
            $dbNameTest, $mailType, $smtphost, $smtpport, $smtpuser,
            $smtppass, $smtpsec, $smtppers, $smtptime, $smtpbind, $logfile,
            $server, $taskServer, $doctrineProxy) {
        // Remove trailing slashes
        $server = \preg_replace('/\\/+$/', '', $server);
        $taskServer = \preg_replace('/\\/+$/', '', $taskServer);
        // Treat spaces-only as empty.
        $logfile = strlen(trim($logfile)) === 0 ? '' : $logfile;
        $contextPath = \dirname($_SERVER['PHP_SELF'], 4);
        // Dirname may add backslashes, especially when going to the top-level path.
        $contextPath = preg_replace('/\\\\/u', '/', $contextPath);
        $mailType = $mailType === 'smtp' ? 'smtp' : 'tls';
        $smtpConf = [
            'host' => $smtphost,
            'user' => $smtpuser,
            'pass' => $smtppass,
            'port' => intval($smtpport),
            'persistent' => $smtppers === "on",
            'secure' => $smtpsec !== 'tls',
            'timeout' => intval($smtptime),
            'bindto' => $smtpbind
        ];
        $yaml = [
            'paths'        => [
                'doctrine_proxy' => $doctrineProxy,
                'public_server'  => $server,
                'local_server'   => $taskServer,
                'migrations'     => '%%PHINX_CONFIG_DIR%%/private/db/migrations',
                'seeds'          => '%%PHINX_CONFIG_DIR%%/db/seeds',
                'context'        => $contextPath
            ],
            'security' => [
                'remember_me_timeout' => '604800',
                'http_only' => 'true',
                'same_site' => Cookie::SAMESITE_STRICT,
                'session_secure' => 'false',
                'session_timeout' => '86400'
            ],
            'environments' => [
                'default_migration_table' => 'phinxlog',
                'default_database'        => $dbMode,
                'production'              => [
                    'logfile'   => $logfile,
                    'database'  => [
                        'driver'    => $driver,
                        'host'      => $host,
                        'name'      => $dbname,
                        'user'      => $user,
                        'pass'      => $pass,
                        'port'      => $port,
                        'charset'   => $encoding,
                        'collation' => $collation
                    ],
                    'mail'      => $mailType,
                    'smtp'      => $smtpConf
                ],
            ],
            'system_mail_address' => $systemMail,
            'version_order' => 'creation'
        ];
        if (!empty($dbNameTest)) {
            $yaml['environments']['testing'] = [
                'logfile'   => $logfile,
                'database'  => [
                    'driver'    => $driver,
                    'host'      => $host,
                    'name'      => $dbNameTest,
                    'user'      => $user,
                    'pass'      => $pass,
                    'port'      => $port,
                    'charset'   => $encoding,
                    'collation' => $collation,
                ],
                'mail'      => $mailType,
                'smtp'      => $smtpConf,
            ];
        }
        if (!empty($dbNameDev)) {
            $yaml['environments']['development'] = [
                'logfile'   => $logfile,
                'database'  => [
                    'driver'    => $driver,
                    'host'      => $host,
                    'name'      => $dbNameDev,
                    'user'      => $user,
                    'pass'      => $pass,
                    'port'      => $port,
                    'charset'   => $encoding,
                    'collation' => $collation
                ],
                'mail'      => $mailType,
                'smtp'      => $smtpConf,
            ];
        }
        return $yaml;
    }
    
    protected function prepareDb(Context $context) {
        DormantSeed::grow([
           'University' => [
               'AllWithDiningHall'
           ],
           'ScheduledEvent' => [
               'ExpireTokenPurge',
               'DiningHallMenuFetch',
               'MailSend'
           ]
        ], $context->getEm());
    }
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_NEVER;
    }

    private static function getDefaultServer() : string {
        return 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

}