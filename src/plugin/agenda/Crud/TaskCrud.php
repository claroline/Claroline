<?php

namespace Claroline\AgendaBundle\Crud;

use Claroline\AgendaBundle\Entity\Task;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\PlanningManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TaskCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var PlanningManager */
    private $planningManager;

    public function __construct(TokenStorageInterface $tokenStorage, PlanningManager $planningManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->planningManager = $planningManager;
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var Task $object */
        $object = $event->getObject();

        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User && empty($object->getCreator())) {
            $object->setCreator($user);
        }

        $object->setCreatedAt(new \DateTime());
        $object->setUpdatedAt(new \DateTime());
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var Task $object */
        $object = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();

        if (!empty($object->getWorkspace())) {
            // add event to workspace planning
            $this->planningManager->addToPlanning($object, $object->getWorkspace());
        } elseif ($user instanceof User) {
            // add event to user planning
            $this->planningManager->addToPlanning($object, $user);
        }
    }

    public function preUpdate(UpdateEvent $event)
    {
        /** @var Task $object */
        $object = $event->getObject();

        $object->setUpdatedAt(new \DateTime());
    }
}
