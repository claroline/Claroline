<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Resource\Types;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\Resource\MaskManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/resource_directory', name: 'apiv2_resource_directory_')]
class DirectoryController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly MaskManager $maskManager,
        private readonly RightsManager $rightsManager
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/{id}/rights', name: 'apply_rights', methods: ['PUT'])]
    public function applyRightsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resourceNode
    ): JsonResponse {
        $this->checkPermission('ADMINISTRATE', $resourceNode, [], true);

        $this->om->startFlushSuite();

        foreach ($resourceNode->getRights() as $right) {
            $this->rightsManager->update(
                $right->getMask(),
                $right->getRole(),
                $resourceNode,
                true,
                $right->getCreatableResourceTypes()
            );
        }

        $this->om->endFlushSuite();

        return new JsonResponse(null);
    }
}
