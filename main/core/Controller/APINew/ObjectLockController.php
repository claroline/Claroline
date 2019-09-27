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
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @EXT\Route("/objectlock")
 */
class ObjectLockController
{
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
     * @EXT\Route("/lock/class/{class}/id/{id}", name="apiv2_object_lock")
     * @EXT\Method("PUT")
     */
    public function lockAction($class, $id)
    {
        $this->manager->lock($class, $id);

        return new JsonResponse($this->serializer->serialize($this->manager->getLock($class, $id)));
    }

    /**
     * @EXT\Route("/unlock/class/{class}/id/{id}", name="apiv2_object_unlock")
     * @EXT\Method("PUT")
     */
    public function unlockAction($class, $id)
    {
        $this->manager->unlock($class, $id);

        return new JsonResponse($this->serializer->serialize($this->manager->getLock($class, $id)));
    }

    /**
     * @EXT\Route("/class/{class}/id/{id}", name="apiv2_object_lock_get")
     * @EXT\Method("GET")
     */
    public function getAction($class, $id)
    {
        return new JsonResponse($this->serializer->serialize($this->manager->getLock($class, $id)));
    }
}
