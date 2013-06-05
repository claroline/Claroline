<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Resource\ModeAccessor;
use Claroline\CoreBundle\Library\Resource\QueryStringWriter;

/**
 * @DI\Service()
 *
 * Handles resource parameters (such as mode, workspace and breacrumbs) passed in
 * the query string of the current request.
 */
class QueryStringHandler
{
    private $modeAccessor;
    private $queryWriter;

    /**
     * @DI\InjectParams({
     *     "accessor" = @DI\Inject("claroline.resource.mode_accessor"),
     *     "writer"   = @DI\Inject("claroline.resource.query_string_writer")
     * })
     */
    public function __construct(ModeAccessor $accessor, QueryStringWriter $writer)
    {
        $this->modeAccessor = $accessor;
        $this->queryWriter = $writer;
    }

    /**
     * @DI\Observe("kernel.request")
     *
     * Sets the resource mode.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $mode = $event->getRequest()->get('_mode');

        if ($mode === 'path') {
            $this->modeAccessor->setPathMode(true);
        }
    }

    /**
     * @DI\Observe("kernel.response")
     *
     * Reappends parameters passed in the query string to a redirection url if needed.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (($response = $event->getResponse()) instanceof RedirectResponse) {
            if ('' !== $query = $this->queryWriter->getQueryString()) {
                $response->setTargetUrl("{$response->getTargetUrl()}?{$query}");
            }
        }
    }
}