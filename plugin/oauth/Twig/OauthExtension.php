<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\OAuthBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class OauthExtension extends \Twig_Extension
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'is_service_available' => new \Twig_Function_Method($this, 'isServiceAvailable'),
            'get_oauth_app_id' => new \Twig_Function_Method($this, 'getAppIp'),
        ];
    }

    public function isServiceAvailable($service)
    {
        return $this->container->get('icap.oauth.manager')->isServiceAvailable($service);
    }

    public function getAppIp($service)
    {
        return $this->container->get('claroline.config.platform_config_handler')->getParameter($service.'_client_id');
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'is_oauth_available_extension';
    }
}
