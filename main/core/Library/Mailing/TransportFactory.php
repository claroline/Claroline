<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Mailing;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.mailing.transport_factory")
 */
class TransportFactory
{
    private $configHandler;

    /**
     * @DI\InjectParams({
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(PlatformConfigurationHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    public function getTransport()
    {
        $type = $this->configHandler->getParameter('mailer_transport');

        if ($type === 'sendmail') {
            return new \Swift_Transport_SendmailTransport(
                new \Swift_Transport_StreamBuffer(new \Swift_StreamFilters_StringReplacementFilterFactory()),
                new \Swift_Events_SimpleEventDispatcher()
            );
        } elseif ($type === 'smtp') {
            $transport = $this->getBaseSmtpTransport();
            $transport->setHost($this->configHandler->getParameter('mailer_host'));
            $transport->setPort($this->configHandler->getParameter('mailer_port'));
            $transport->setEncryption($this->configHandler->getParameter('mailer_encryption'));
            $transport->setUsername($this->configHandler->getParameter('mailer_username'));
            $transport->setPassword($this->configHandler->getParameter('mailer_password'));
            $transport->setAuthMode($this->configHandler->getParameter('mailer_auth_mode'));
            // should probably be configurable too
            $transport->setTimeout(30);
            $transport->setSourceIp(null);

            return $transport;
        } elseif ($type === 'gmail') {
            $transport = $this->getBaseSmtpTransport();
            $transport->setHost('smtp.gmail.com');
            $transport->setPort(465);
            $transport->setEncryption('ssl');
            $transport->setAuthMode('login');
            $transport->setUsername($this->configHandler->getParameter('mailer_username'));
            $transport->setPassword($this->configHandler->getParameter('mailer_password'));

            return $transport;
        }

        throw new \Exception("Unsupported transport type '{$type}'");
    }

    private function getBaseSmtpTransport()
    {
        return new \Swift_Transport_EsmtpTransport(
            new \Swift_Transport_StreamBuffer(new \Swift_StreamFilters_StringReplacementFilterFactory()),
            array(
                new \Swift_Transport_Esmtp_AuthHandler(
                    array(
                        new \Swift_Transport_Esmtp_Auth_CramMd5Authenticator(),
                        new \Swift_Transport_Esmtp_Auth_LoginAuthenticator(),
                        new \Swift_Transport_Esmtp_Auth_PlainAuthenticator(),
                    )
                ),
            ),
            new \Swift_Events_SimpleEventDispatcher()
        );
    }
}
