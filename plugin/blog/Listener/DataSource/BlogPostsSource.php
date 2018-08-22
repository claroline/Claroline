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
use Claroline\CoreBundle\Event\DataSource\DataSourceEvent;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Entity\Statusable;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class BlogPostsSource
{
    /** @var FinderProvider */
    private $finder;

    /**
     * BlogPostsSource constructor.
     *
     * @DI\InjectParams({
     *     "finder" = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param FinderProvider $finder
     */
    public function __construct(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @DI\Observe("data_source.blog_posts.load")
     *
     * @param DataSourceEvent $event
     */
    public function getData(DataSourceEvent $event)
    {
        $options = $event->getOptions() ? $event->getOptions() : [];
        $options['sortBy'] = '-publicationDate';
        $options['hiddenFilters']['status'] = Statusable::STATUS_PUBLISHED;

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        }
        $event->setData(
            $this->finder->search(Post::class, $options)
        );

        $event->stopPropagation();
    }
}
