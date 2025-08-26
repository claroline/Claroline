<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Manager\CategoryManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/clacoform/category')]
class CategoryController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly CategoryManager $categoryManager
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/{id}/assign/all', name: 'apiv2_clacoform_category_assign_all', methods: ['PUT'])]
    public function reassignCategoriesAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resourceNode
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $resourceNode, [], true);

        $clacoForm = $this->om->getRepository(ClacoForm::class)->findOneBy(['resourceNode' => $resourceNode]);
        $categories = $this->om->getRepository(Category::class)->findAutoCategories($clacoForm);
        foreach ($categories as $category) {
            $this->categoryManager->assignCategory($category);
        }

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/assign/{id}', name: 'apiv2_clacoform_category_assign', methods: ['PUT'])]
    public function assignAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Category $category
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $category->getClacoForm(), [], true);

        $this->categoryManager->assignCategory($category);

        return new JsonResponse(null, 204);
    }
}
