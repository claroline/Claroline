<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Controller\Widget;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Manager\WidgetManager;
use Claroline\TagBundle\Form\WorkspaceTagType;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceTagController extends Controller
{
    /**
     * @DI\InjectParams({
     *     "formFactory"   = @DI\Inject("form.factory"),
     *     "widgetManager" = @DI\Inject("claroline.manager.widget_manager"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "tagManager"    = @DI\Inject("claroline.manager.tag_manager"),
     * })
     */
    public function __construct(
      FormFactory $formFactory,
      WidgetManager $widgetManager,
      TokenStorageInterface $tokenStorage,
      TagManager $tagManager
  ) {
        $this->formFactory = $formFactory;
        $this->widgetManager = $widgetManager;
        $this->tokenStorage = $tokenStorage;
        $this->tagManager = $tagManager;
    }

    /**
     * @EXT\Route(
     *     "/workspace_tag/config/{widgetInstance}",
     *     name="claro_widget_workspace_tag_update_config"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineTagBundle:Widget:WorkspaceTag\widgetConfigureForm.html.twig")
     */
    public function updateSimpleTextWidgetConfigAction(WidgetInstance $widgetInstance, Request $request)
    {
        $form = $this->formFactory->create(new WorkspaceTagType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $displayConfigs = $this->widgetManager->getWidgetDisplayConfigsByUserAndWidgets(
            $this->tokenStorage->getToken()->getUser(),
            [$widgetInstance]
          );
            $config = $displayConfigs[0];
            $config->setDetails(['tags' => $form->get('tags')->getData()]);
            $this->widgetManager->persistWidgetConfigs(null, null, $config);

            return new JsonResponse('success', 204);
        }

        return ['form' => $form->createView(), 'widgetInstance' => $widgetInstance];
    }

    /**
     * @EXT\Route(
     *     "/workspace_tag/config/{widgetInstance}/form",
     *     name="claro_widget_workspace_tag_update_form"
     * )
     * @EXT\Method("POST")
     * @EXT\Template()
     */
    public function widgetConfigureFormAction(WidgetInstance $widgetInstance, Request $request)
    {
        $displayConfigs = $this->widgetManager->getWidgetDisplayConfigsByUserAndWidgets(
          $this->tokenStorage->getToken()->getUser(),
          [$widgetInstance]
        );
        $tags = explode(',', $displayConfigs[0]->getDetails()['tags']);
        $form = $this->formFactory->create(new WorkspaceTagType($tags));

        return ['form' => $form->createView(), 'widgetInstance' => $widgetInstance];
    }

    /**
     * @EXT\Route(
     *     "/resources/widget/{widgetInstance}",
     *     name="claro_resources_widget",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template()
     */
    public function displayAction(WidgetInstance $widgetInstance)
    {
        $displayConfigs = $this->widgetManager->getWidgetDisplayConfigsByUserAndWidgets(
          $this->tokenStorage->getToken()->getUser(),
          [$widgetInstance]
        );
        $tags = explode(',', $displayConfigs[0]->getDetails()['tags']);
        $workspaces = [];

        foreach ($tags as $tag) {
            $workspaces[$tag] = $this->tagManager->getTaggedWorkspaces($tag);
        }

        return ['workspaces' => $workspaces];
    }
}
