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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/clacoform")
 */
class ClacoFormController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    public function getClass(): string
    {
        return ClacoForm::class;
    }

    public function getIgnore(): array
    {
        return ['create', 'deleteBulk', 'list', 'get'];
    }

    public function getName(): string
    {
        return 'clacoform';
    }

    /**
     * @Route("/{id}/stats", name="apiv2_clacoform_stats", methods={"GET"})
     *
     * @EXT\ParamConverter("id", class="Claroline\ClacoFormBundle\Entity\ClacoForm", options={"mapping": {"id": "uuid"}})
     */
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
}
