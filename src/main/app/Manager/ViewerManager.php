<?php

namespace Claroline\AppBundle\Manager;

use Claroline\AppBundle\API\Finder\FinderFactoryInterface;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Finder\FinderResultInterface;
use Claroline\AppBundle\Entity\AbstractUserView;
use Claroline\AppBundle\Entity\UserViewCounterInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AppBundle\Repository\ViewerRepositoryInterface;
use Claroline\AppBundle\Serializer\ViewerSerializer;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;

/**
 * Manages user views for contents.
 */
class ViewerManager
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly FinderFactoryInterface $finderFactory,
        private readonly ViewerSerializer $viewerSerializer,
    ) {
    }

    public function listViews(string $finderClass, FinderQuery $finderQuery): FinderResultInterface
    {
        return $this->finderFactory->create($finderClass)
            ->submit($finderQuery)
            ->getResult(function (AbstractUserView $viewer): array {
                return $this->viewerSerializer->serialize($viewer);
            });
    }

    public function countVisitors(string $viewClass, UserViewCounterInterface $subject, Organization $organization): int
    {
        $repo = $this->om->getRepository($viewClass);
        if (!$repo instanceof ViewerRepositoryInterface) {
            throw new \RuntimeException(sprintf('Repository for %s must extend AbstractViewerRepository. ', $viewClass));
        }

        return $repo->countVisitors($subject, $organization);
    }

    public function addView(string $viewClass, UserViewCounterInterface $subject, ?User $user = null): AbstractUserView
    {
        $now = new \DateTime();

        $repo = $this->om->getRepository($viewClass);
        if (!$repo instanceof ViewerRepositoryInterface) {
            throw new \RuntimeException(sprintf('Repository for %s must extend AbstractViewerRepository. ', $viewClass));
        }

        /** @var AbstractUserView $view */
        $view = $repo->findOneByDateAndUser($subject, $now, $user);

        $update = false;
        if (empty($view)) {
            $view = new $viewClass();
            $view->setUser($user);
            $view->setSubject($subject);

            $update = true;
        } else {
            $elapsedMinutes = abs($view->getSeenAt()->getTimestamp() - $now->getTimestamp()) / 60;
            if ($elapsedMinutes > 15) {
                // debounce view update to avoid incrementing value on multiple F5
                $update = true;
            }
        }

        if ($update) {
            $view->incrementCount();
            $view->setSeenAt($now);
            $subject->addView();

            $this->om->persist($view);
            $this->om->persist($subject);
            $this->om->flush();
        }

        return $view;
    }
}
