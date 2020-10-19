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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BlogPostsSource
{
    /** @var FinderProvider */
    private $finder;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
    }

    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions() ?? [];
        $options['hiddenFilters']['published'] = Statusable::STATUS_PUBLISHED;

        if (DataSource::CONTEXT_HOME === $event->getContext()) {
            // only what is accessible by anonymous
            $options['hiddenFilters']['roles'] = ['ROLE_ANONYMOUS'];
        } else {
            // filter by current user roles
            $options['hiddenFilters']['roles'] = $this->tokenStorage->getToken()->getRoleNames();
        }

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        } else {
            $options['hiddenFilters']['archived'] = false;
        }

        $event->setData(
            $this->finder->search(Post::class, $options)
        );

        $event->stopPropagation();
    }
}
