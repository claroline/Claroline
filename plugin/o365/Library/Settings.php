<?php

namespace FormaLibre\OfficeConnectBundle\Library;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Routing\RouterInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("formalibre.office_connect.library.settings")
 */
class Settings
{
    /**
     * @DI\InjectParams({
     *     "ch"     = @DI\Inject("claroline.config.platform_config_handler"),
     *     "router" = @DI\Inject("router")
     * })
     */
    public function __construct(PlatformConfigurationHandler $ch, RouterInterface $router)
    {
        $this->ch = $ch;
        $this->router = $router;
    }

    public function setClientId($id)
    {
        $this->ch->setParameter('o365_client_id', $id);
    }

    public function setPassword($pw)
    {
        $this->ch->setParameter('o365_pw', $pw);
    }

    public function setAppTenantDomainName($dom)
    {
        $this->ch->setParameter('o365_domain', $dom);
    }

    public function getClientId()
    {
        return $this->ch->getParameter('o365_client_id');
    }

    public function getPassword()
    {
        return $this->ch->getParameter('o365_pw');
    }

    public function getAppTenantDomainName()
    {
        return $this->ch->getParameter('o365_domain');
    }

    public function getRedirectUri()
    {
        return $this->router->generate('claro_o365_login', array(), true);
    }

    public function getResourceUri()
    {
        return 'https://graph.windows.net'; // 'https://portal.microsoftonline.com' ;
    }

    public function getApiVersion()
    {
        return 'api-version=2013-11-08';
    }

    public function getConfiguration()
    {
        return new Configuration(
            $this->getClientId(),
            $this->getPassword(),
            $this->getAppTenantDomainName(),
            $this->ch->getParameter('o365_active')
        );
    }
}
