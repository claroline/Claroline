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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/template")
 */
class TemplateController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var TemplateManager */
    private $templateManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TemplateManager $templateManager
    ) {
        $this->authorization = $authorization;
        $this->templateManager = $templateManager;
    }

    public function getName()
    {
        return 'template';
    }

    public function getClass()
    {
        return Template::class;
    }

    /**
     * @Route("{id}/default", name="apiv2_template_default_define", methods={"PUT"})
     * @EXT\ParamConverter("template", class="ClarolineCoreBundle:Template\Template", options={"mapping": {"id": "uuid"}})
     */
    public function defineAsDefaultAction(Template $template): JsonResponse
    {
        $this->checkPermission('EDIT', $template, [], true);

        $this->templateManager->defineTemplateAsDefault($template);

        return new JsonResponse(
            $this->serializer->serialize($template)
        );
    }
}
