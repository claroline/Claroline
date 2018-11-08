<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Listener\Administration;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service
 */
class TagsListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * TagsListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param TwigEngine $templating
     */
    public function __construct(
        TwigEngine $templating
    ) {
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("administration_tool_claroline_tag_admin_tool")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onAdministrationToolOpen(OpenAdministrationToolEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineTagBundle:administration:tags.html.twig', [
                'context' => [
                    'type' => Tool::ADMINISTRATION,
                ],
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}
