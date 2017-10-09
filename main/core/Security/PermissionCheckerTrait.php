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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
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
     * Injects Authorization service.
     *
     * @DI\InjectParams({
     *      "authorization" = @DI\Inject("security.authorization_checker")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     */
    public function setAuthorization(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    private function checkPermission($permission, $object, $options = [], $throwException = false)
    {
        switch ($object) {
            case $object instanceof ResourceNode:
              $collection = new ResourceCollection([$object]);
              break;
            default:
              $collection = new ObjectCollection([$object], $options);
        }

        $granted = $this->authorization->isGranted($permission, $collection);
        if (!$granted && $throwException) {
            throw new AccessDeniedException(
                sprintf('Operation "%s" cannot be done on object %s', $permission, get_class($object))
            );
        }

        return $granted;
    }
}
