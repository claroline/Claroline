<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\LockManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/objectlock")
 */
class ObjectLockController
{
    private $om;
    private $serializer;
    private $manager;

    public function __construct(
      ObjectManager $om,
      SerializerProvider $serializer,
      LockManager $manager
  ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * @Route("/lock/class/{class}/id/{id}", name="apiv2_object_lock", methods={"PUT"})
     */
    public function lockAction($class, $id): JsonResponse
    {
        $this->manager->lock($class, $id);

        return new JsonResponse($this->serializer->serialize($this->manager->getLock($class, $id)));
    }

    /**
     * @Route("/unlock/class/{class}/id/{id}", name="apiv2_object_unlock", methods={"PUT"})
     */
    public function unlockAction($class, $id): JsonResponse
    {
        $this->manager->unlock($class, $id);

        return new JsonResponse($this->serializer->serialize($this->manager->getLock($class, $id)));
    }

    /**
     * @Route("/class/{class}/id/{id}", name="apiv2_object_lock_get", methods={"GET"})
     */
    public function getAction($class, $id): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($this->manager->getLock($class, $id)));
    }
}
