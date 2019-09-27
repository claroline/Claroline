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
use Claroline\CoreBundle\Manager\ToolManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/template")
 */
class TemplateController extends AbstractCrudController
{
    /** @var AuthorizationCheckerInterface */
    protected $authorization;

    /** @var TemplateManager */
    private $templateManager;

    /** @var ToolManager */
    private $toolManager;

    /**
     * TemplateController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param TemplateManager               $templateManager
     * @param ToolManager                   $toolManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TemplateManager $templateManager,
        ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->templateManager = $templateManager;
        $this->toolManager = $toolManager;
    }

    public function getName()
    {
        return 'template';
    }

    public function getClass()
    {
        return Template::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'doc', 'find'];
    }

    /**
     * @EXT\Route(
     *     "/{lang}/list",
     *     name="apiv2_lang_template_list"
     * )
     *
     * @param string  $lang
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function templatesByLangListAction($lang, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['lang'] = $lang;

        return new JsonResponse(
            $this->finder->search(Template::class, $params)
        );
    }

    /**
     * @EXT\Route(
     *     "/delete",
     *     name="apiv2_template_full_delete_bulk"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function templatesByLangDeleteBulkAction(Request $request)
    {
        $query = $request->query->all();
        $options = $this->options['deleteBulk'];

        if (isset($query['options'])) {
            $options = $query['options'];
        }
        $templateRepo = $this->om->getRepository(Template::class);
        $toDelete = [];
        $templates = $this->decodeIdsString($request, Template::class);

        foreach ($templates as $template) {
            $localizedTemplates = $templateRepo->findBy([
                'name' => $template->getName(),
                'type' => $template->getType(),
            ]);
            foreach ($localizedTemplates as $localizedTemplate) {
                $toDelete[] = $localizedTemplate;
            }
        }
        $this->crud->deleteBulk($toDelete, $options);

        return new JsonResponse(null, 204);
    }

    /**
     * @EXT\Route(
     *     "{id}/default",
     *     name="apiv2_template_default_define"
     * )
     * @EXT\ParamConverter(
     *     "template",
     *     class="ClarolineCoreBundle:Template\Template",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param Template $template
     *
     * @return JsonResponse
     */
    public function templateDefaultDefineAction(Template $template)
    {
        $this->checkToolAccess();
        $this->templateManager->defineTemplateAsDefault($template);

        return new JsonResponse(null, 200);
    }

    /**
     * @param string $rights
     */
    private function checkToolAccess($rights = 'OPEN')
    {
        $templateTool = $this->toolManager->getAdminToolByName('templates_management');

        if (is_null($templateTool) || !$this->authorization->isGranted($rights, $templateTool)) {
            throw new AccessDeniedException();
        }
    }
}
