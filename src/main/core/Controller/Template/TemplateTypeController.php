<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Template;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/template_type')]
class TemplateTypeController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
        private readonly TemplateManager $templateManager
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/{type}', name: 'apiv2_template_type_list', methods: ['GET'])]
    public function listAction(
        ?string $type = null,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        if ($type) {
            $finderQuery->addFilter('type', $type);
        }

        $templateTypes = $this->crud->search(TemplateType::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $templateTypes->toResponse();
    }

    #[Route(path: '/{id}/open', name: 'apiv2_template_type_open', methods: ['GET'])]
    public function openAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        TemplateType $templateType
    ): StreamedJsonResponse {
        $this->checkPermission('OPEN', $templateType, [], true);

        $finderQuery = new FinderQuery();
        $finderQuery->addFilter('type', $templateType->getUuid());
        $templates = $this->crud->search(Template::class, $finderQuery);

        return new StreamedJsonResponse([
            'type' => $this->serializer->serialize($templateType),
            'templates' => $templates->getItems(),
        ]);
    }
}
