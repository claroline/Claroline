<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Controller\API;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Manager\CategoryManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/clacoform')]
class ClacoFormController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly CategoryManager $categoryManager
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/{id}/stats', name: 'apiv2_clacoform_stats', methods: ['GET'])]
    public function getStatsAction(ClacoForm $clacoForm): JsonResponse
    {
        $this->checkPermission('EDIT', $clacoForm, [], true);

        $stats = $this->om->getRepository(ClacoForm::class)->getEntryStats($clacoForm);

        return new JsonResponse([
            'total' => $stats['total'],
            'users' => $stats['users'],
            'fields' => array_map(function (array $fieldStats) {
                return [
                    'field' => $this->serializer->serialize($fieldStats['field']),
                    'values' => $fieldStats['values'],
                ];
            }, $stats['fields']),
        ]);
    }

    #[Route(path: '/{id}/assign_categories', name: 'apiv2_clacoform_categories_assign', methods: ['PUT'])]
    public function reassignCategoriesAction(ClacoForm $clacoForm): JsonResponse
    {
        $this->checkPermission('EDIT', $clacoForm, [], true);

        $categories = $clacoForm->getCategories();
        foreach ($categories as $category) {
            $this->categoryManager->assignCategory($category);
        }

        return new JsonResponse(null, 204);
    }
}
