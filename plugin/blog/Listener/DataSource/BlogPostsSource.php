<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\BlogBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Entity\Statusable;

class BlogPostsSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * BlogPostsSource constructor.
     *
     * @param FinderProvider $finder
     */
    public function __construct(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions() ? $event->getOptions() : [];
        $options['hiddenFilters']['status'] = Statusable::STATUS_PUBLISHED;

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        } elseif (DataSource::CONTEXT_HOME === $event->getContext()) {
            $options['hiddenFilters']['anonymous'] = true;
        }
        $event->setData(
            $this->finder->search(Post::class, $options)
        );

        $event->stopPropagation();
    }
}
