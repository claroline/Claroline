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

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 *  @DI\Service()
 */
class UnavailablePlatformListener
{
    private $templating;
    private $ch;
    private $sc;

    /**
     * @DI\InjectParams({
     *      "templating" = @DI\Inject("templating"),
     *      "ch"         = @DI\Inject("claroline.config.platform_config_handler"),
     *      "sc"         = @DI\Inject("security.context")
     * })
     */
    public function __construct(TwigEngine $templating, $ch, SecurityContextInterface $sc)
    {
        $this->templating = $templating;
        $this->ch = $ch;
        $this->sc = $sc;
    }

    /**
     * @DI\Observe("kernel.response")
     */
    public function onKernelRequest($event)
    {
        $token = $this->sc->getToken();
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
            $response = new Response($this->templating->render(
                'ClarolineCoreBundle:Exception:unavailable_platform_exception.html.twig'
            ). $event->getRequest()->get('_route'));

            $response->setStatusCode(500);
            $event->setResponse($response);
        }
    }

    private function getPublicRoute()
    {
        return array(
            'claro_index',
            '_profiler',
            'claro_security_login',
            'claro_security_login_check'
        );
    }
} 