<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security;

use Claroline\AppBundle\Security\ObjectCollection;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Allows the target class to checks the current user permissions on a ResourceNode.
 */
trait PermissionCheckerTrait
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @param mixed $permission
     * @param mixed $object
     * @param array $options
     * @param bool  $throwException
     *
     * @return bool
     */
    protected function checkPermission($permission, $object, $options = [], $throwException = false)
    {
        if (!$this->authorization instanceof AuthorizationCheckerInterface) {
            throw new \RuntimeException('PermissionCheckerTrait requires the AuthorizationChecker (@security.authorization_checker) to be injected in your service.');
        }

        switch ($object) {
            //@todo Remove that line once we can
            case $object instanceof ResourceNode:
              $collection = new ResourceCollection([$object]);
              break;
            case is_array($object):
              $collection = new ObjectCollection($object, $options);
              break;
            default:
              $collection = new ObjectCollection([$object], $options);
        }

        $granted = $this->authorization->isGranted($permission, $collection);
        if (!$granted && $throwException) {
            throw new AccessDeniedException(sprintf('Operation "%s" cannot be done on object %s', $permission, get_class($object)));
        }

        return $granted;
    }
}
