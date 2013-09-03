<?php

namespace ICAP\BlogBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Entity\Comment;
use ICAP\BlogBundle\Entity\Post;
use ICAP\BlogBundle\Form\BlogType;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LogListener extends ContainerAware
{
    public function onCreateLogListItem(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'ICAPBlogBundle:Log:log_list_item.html.twig',
            array('log' => $event->getLog())
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onPostCreateLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'ICAPBlogBundle:Log:log_details.html.twig',
            array(
                'log' => $event->getLog(),
                'listItemView' => $this->container->get('templating')->render(
                    'ICAPBlogBundle:Log:log_list_item.html.twig',
                    array('log' => $event->getLog())
                )
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
