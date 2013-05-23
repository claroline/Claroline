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
class PathBuilder
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
        $breadcrumbs = $event->getRequest()->query->get('_breadcrumbs', array());

        if ($breadcrumbs != null) {
            $toAppend = '';
            for ($i = 0, $size = count($breadcrumbs); $i < $size; $i++) {
                if ($i === 0) {
                    $toAppend .= '?';
                } else {
                    $toAppend .= '&';
                }
                $toAppend .= '_breadcrumbs[]='. $breadcrumbs[$i];
            }

            $response = $event->getResponse();

            if (($response = $event->getResponse()) instanceof RedirectResponse) {
                $response->setTargetUrl($response->getTargetUrl() . $toAppend);
            }
        }
    }
}