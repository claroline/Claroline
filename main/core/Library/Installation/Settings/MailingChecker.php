<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Settings;

use Swift_SmtpTransport;
use Swift_SendmailTransport;
use Swift_TransportException;

class MailingChecker
{
    const UNABLE_TO_START_TRANSPORT = 'unable_to_start_transport';
    const UNABLE_TO_START_SMTP = 'unable_to_start_smtp';
    const UNABLE_TO_START_SENDMAIL = 'unable_to_start_sendmail';
    const UNABLE_TO_START_GMAIL = 'unable_to_start_gmail';

    private $settings;

    public function __construct(MailingSettings $settings)
    {
        $this->settings = $settings;
    }

    public function testTransport()
    {
        if (!$this->settings->isValid()) {
            throw new \Exception('Mailing settings must be validated first');
        }

        switch ($this->settings->getTransport()) {
            case 'smtp':
                return $this->testSmtp();
            case 'gmail':
                return $this->testGmail();
            case 'sendmail':
                return $this->testSendmail();
        }
    }

    private function testSmtp()
    {
        $port = $this->settings->getTransportOption('port');

        if (empty($port)) {
            $port = $this->settings->getTransportOption('encryption') === 'ssl' ? 465 : 25;
        }

        try {
            Swift_SmtpTransport::newInstance()
                ->setHost($this->settings->getTransportOption('host'))
                ->setUsername($this->settings->getTransportOption('username'))
                ->setPassword($this->settings->getTransportOption('password'))
                ->setAuthMode($this->settings->getTransportOption('auth_mode'))
                ->setEncryption($this->settings->getTransportOption('encryption'))
                ->setPort($port)
                ->start();
        } catch (Swift_TransportException $ex) {
            return static::UNABLE_TO_START_SMTP;
        }

        return true;
    }

    private function testGmail()
    {
        try {
            Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
                ->setUsername($this->settings->getTransportOption('username'))
                ->setPassword($this->settings->getTransportOption('password'))
                ->setAuthMode('login')
                ->start();
        } catch (Swift_TransportException $ex) {
            return static::UNABLE_TO_START_GMAIL;
        }

        return true;
    }

    private function testSendmail()
    {
        try {
            Swift_SendmailTransport::newInstance()->start();
        } catch (Swift_TransportException $ex) {
            return static::UNABLE_TO_START_SENDMAIL;
        }

        return true;
    }
}
