<?php

namespace Claroline\EvaluationBundle\Subscriber\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ResourceEvaluationSource implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var FinderProvider */
    private $finder;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->finder = $finder;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'data_source.resource_evaluations.load' => 'getData',
        ];
    }

    public function getData(GetDataEvent $event)
    {
        /** @var User $currentUser */
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $options = $event->getOptions();
        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $workspace = $event->getWorkspace();

            $options['hiddenFilters']['workspace'] = $workspace->getUuid();
            if (!$this->authorization->isGranted('ADMINISTRATE', $workspace)) {
                $options['hiddenFilters']['user'] = $currentUser->getUuid();
            }
        } elseif (!$this->authorization->isGranted(PlatformRoles::ADMIN)) {
            // desktop
            $options['hiddenFilters']['user'] = $currentUser->getUuid();
        }

        $event->setData(
            $this->finder->search(ResourceUserEvaluation::class, $options)
        );

        $event->stopPropagation();
    }
}
