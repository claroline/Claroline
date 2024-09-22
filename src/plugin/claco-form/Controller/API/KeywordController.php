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

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/clacoformkeyword', name: 'apiv2_clacoformkeyword_')]
class KeywordController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return Keyword::class;
    }

    public function getIgnore(): array
    {
        return ['list'];
    }

    public static function getName(): string
    {
        return 'clacoformkeyword';
    }

    #[Route(path: '/clacoform/{clacoForm}/keywords/list', name: 'list')]
    public function listByResourceAction(#[MapEntity(class: 'Claroline\ClacoFormBundle\Entity\ClacoForm', mapping: ['clacoForm' => 'uuid'])]
    ClacoForm $clacoForm, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $clacoForm->getResourceNode(), [], true);

        $params = $request->query->all();
        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['clacoForm'] = $clacoForm->getId();

        return new JsonResponse(
            $this->crud->list(Keyword::class, $params)
        );
    }

    /**
     * Returns the keyword.
     *
     */
    #[Route(path: '/{clacoForm}/keyword/{value}/excluding/uuid/{uuid}', name: 'check_unique', defaults: ['uuid' => null])]
    public function getKeywordByNameExcludingUuidAction(#[MapEntity(class: 'Claroline\ClacoFormBundle\Entity\ClacoForm', mapping: ['clacoForm' => 'uuid'])]
    ClacoForm $clacoForm, $value, string $uuid = null): JsonResponse
    {
        $this->checkPermission('EDIT', $clacoForm->getResourceNode(), [], true);

        $keyword = $this->om->getRepository(Keyword::class)->findKeywordByNameExcludingUuid($clacoForm, $value, $uuid);

        if (!empty($keyword)) {
            return new JsonResponse(true);
        }

        return new JsonResponse(false, 204);
    }
}
