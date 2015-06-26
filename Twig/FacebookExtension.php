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
class FacebookExtension extends \Twig_Extension
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
        return array(
            'is_facebook_available' => new \Twig_Function_Method($this, 'isFacebookAvailable'),
            'get_facebook_app_id' => new \Twig_Function_Method($this, 'getAppIp')
        );
    }

    public function isFacebookAvailable()
    {
        return $this->container->get('icap.oauth.manager.facebook')->isFacebookAvailable();
    }

    public function getAppIp()
    {
        return $this->container->get('claroline.config.platform_config_handler')->getParameter('facebook_client_id');
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'is_facebook_available_extension';
    }
}
