<?php

namespace Claroline\ForumBundle\Listener\DataSource;

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\ForumBundle\Entity\Message;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ForumSource
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

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
        $options = $event->getOptions() ? $event->getOptions() : [];
        $options['hiddenFilters']['published'] = true;
        $options['hiddenFilters']['moderation'] = false;
        $options['hiddenFilters']['flagged'] = false;

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
            $this->finder->search(Message::class, $options)
        );

        $event->stopPropagation();
    }
}
