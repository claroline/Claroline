<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Template;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/template_type")
 */
class TemplateTypeController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var Crud */
    private $crud;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TemplateManager */
    private $templateManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        Crud $crud,
        SerializerProvider $serializer,
        TemplateManager $templateManager
    ) {
        $this->authorization = $authorization;
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->templateManager = $templateManager;
    }

    /**
     * @Route("/{type}", name="apiv2_template_type_list", methods={"GET"})
     */
    public function listAction(Request $request, string $type = null): JsonResponse
    {
        $query = $request->query->all();
        if ($type) {
            $query['hiddenFilters'] = [
                'type' => $type,
            ];
        }

        return new JsonResponse(
            $this->crud->list(TemplateType::class, $query)
        );
    }

    /**
     * @Route("/{id}/open", name="apiv2_template_type_open", methods={"GET"})
     * @EXT\ParamConverter("templateType", class="ClarolineCoreBundle:Template\TemplateType", options={"mapping": {"id": "uuid"}})
     */
    public function openAction(TemplateType $templateType): JsonResponse
    {
        $this->checkPermission('OPEN', $templateType, [], true);

        $query = [];
        $query['hiddenFilters'] = [
            'type' => $templateType->getUuid(),
        ];

        return new JsonResponse([
            'type' => $this->serializer->serialize($templateType),
            'templates' => $this->crud->list(Template::class, $query),
        ]);
    }

    /**
     * @Route("/{id}/templates", name="apiv2_template_type_templates")
     * @EXT\ParamConverter("templateType", class="ClarolineCoreBundle:Template\TemplateType", options={"mapping": {"id": "uuid"}})
     */
    public function listTemplatesAction(TemplateType $templateType, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $templateType, [], true);

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'type' => $templateType->getUuid(),
        ];

        return new JsonResponse(
            $this->crud->list(Template::class, $query)
        );
    }

    /**
     * @Route("/{id}/preview/{templateId}/{locale}", name="apiv2_template_preview")
     * @EXT\ParamConverter("template", class="ClarolineCoreBundle:Template\Template", options={"mapping": {"templateId": "uuid"}})
     */
    public function previewAction(Template $template, string $locale)
    {
        $placeholders = [];

        return new JsonResponse([
            'title' => $this->templateManager->getTemplateContent($template, $placeholders, $locale, 'title'),
            'content' => $this->templateManager->getTemplateContent($template, $placeholders, $locale, 'content'),
        ]);
    }
}
