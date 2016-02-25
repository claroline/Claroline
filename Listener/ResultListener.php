<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Listener;

use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class ResultListener
{
    /**
     * @DI\Observe("create_form_claroline_result")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {

    }

    /**
     * @DI\Observe("create_claroline_result")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {

    }

    /**
     * @DI\Observe("create_claroline_open")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {

    }

    /**
     * @DI\Observe("create_claroline_delete")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {

    }

    /**
     * @DI\Observe("widget_claroline_result")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplayWidget(DisplayWidgetEvent $event)
    {

    }
}
