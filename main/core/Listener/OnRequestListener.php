<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Claroline\CoreBundle\Entity\User;

/**
 * @DI\Service()
 */
class OnRequestListener
{
    private $container;
    private $storage;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
        $this->storage = $container->get('security.token_storage');
    }

    /**
     * @DI\Observe("kernel.request")
     *
     * Sets the platform language.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        /*
            if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
                // ne rien faire si ce n'est pas la requête principale
                return;
            }
        */

        if (
            $this->container->get('claroline.manager.ip_white_list_manager')->isWhiteListed()
        ) {
            if (!$this->storage->getToken()) {
                $this->replaceToken();
            } elseif ($this->storage->getToken()->getUser() === 'anon.') {
                $this->replaceToken();
            }
        }
    }

    private function replaceToken()
    {
        $defaultId = $this->container->get('claroline.config.platform_config_handler')->getParameter('default_root_anon_id');

        if ($defaultId) {
            $user = $this->container->get('doctrine.orm.entity_manager')
                ->getRepository('ClarolineCoreBundle:User')->find($defaultId);

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->storage->setToken($token);
        }
    }
}
