<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 *  @DI\Service()
 */
class UnavailablePlatformListener
{
    private $templating;
    private $ch;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *      "templating"   = @DI\Inject("templating"),
     *      "ch"           = @DI\Inject("claroline.config.platform_config_handler"),
     *      "tokenStorage" = @DI\Inject("security.token_storage"),
     *      "kernel"       = @DI\Inject("kernel"),
     * })
     */
    public function __construct(
        TwigEngine $templating,
        PlatformConfigurationHandler $ch,
        TokenStorageInterface $tokenStorage,
        HttpKernelInterface $kernel
    ) {
        $this->templating = $templating;
        $this->ch = $ch;
        $this->tokenStorage = $tokenStorage;
        $this->kernel = $kernel;
    }

    /**
     * @DI\Observe("kernel.response")
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->kernel->getEnvironment() === 'prod') {
            $token = $this->tokenStorage->getToken();
            $isAdmin = false;

            if ($token) {
                foreach ($token->getRoles() as $role) {
                    if ($role->getRole() === 'ROLE_ADMIN') {
                        $isAdmin = true;
                    }
                }
            }

            $now = time();
            $minDate = $this->ch->getParameter('platform_init_date');
            $maxDate = $this->ch->getParameter('platform_limit_date');

            if (
                ($minDate > $now || $now > $maxDate) &&
                !$isAdmin && $event->isMasterRequest() &&
                !in_array($event->getRequest()->get('_route'), $this->getPublicRoute())
            ) {
                throw new HttpException(503);
            }
        }
    }

    private function getPublicRoute()
    {
        return array(
            'claro_index',
            '_profiler',
            'claro_security_login',
            'claro_security_login_check',
        );
    }
}
