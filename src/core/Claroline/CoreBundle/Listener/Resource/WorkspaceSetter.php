<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 *
 * If a "_path" parameter is passed in the request, the resource breadcrumbs will be displayed.
 */
class WorkspaceSetter
{
    /**
     * @DI\Observe("kernel.response")
     *
     * Appends the mode to the url if needed for redirections.
     *
     * @param $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $workspace = $event->getRequest()->query->get('_workspace');

        if ($workspace!= null) {
            $toAppend = '';
            if (count($event->getRequest()->query->all() > 0)) {
                $toAppend .= '&';
            } else {
                $toAppend .= '?';
            }

            $toAppend .= '_workspace=' . $workspace;

            $response = $event->getResponse();

            if (($response = $event->getResponse()) instanceof RedirectResponse) {
                $response->setTargetUrl($response->getTargetUrl() . $toAppend);
            }
        }
    }
}