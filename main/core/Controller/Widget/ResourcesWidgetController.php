<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Widget;

use Claroline\CoreBundle\Entity\Widget\ResourcesWidgetConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Form\ResourcesWidgetConfigurationType;
use Claroline\CoreBundle\Form\Widget\ResourcesTextConfigurationType;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\ResourcesWidgetManager;
use Claroline\CoreBundle\Manager\TextManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

class ResourcesWidgetController extends Controller
{
    private $formFactory;
    private $request;
    private $resourceManager;
    private $resourcesWidgetManager;
    private $translator;
    private $textManager;
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "requestStack"           = @DI\Inject("request_stack"),
     *     "resourceManager"        = @DI\Inject("claroline.manager.resource_manager"),
     *     "resourcesWidgetManager" = @DI\Inject("claroline.manager.resources_widget_manager"),
     *     "translator"             = @DI\Inject("translator"),
     *     "textManager"            = @DI\Inject("claroline.manager.text_manager"),
     *     "widgetManager"          = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        RequestStack $requestStack,
        ResourceManager $resourceManager,
        ResourcesWidgetManager $resourcesWidgetManager,
        TranslatorInterface $translator,
        WidgetManager $widgetManager,
        TextManager $textManager
    ) {
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->resourceManager = $resourceManager;
        $this->resourcesWidgetManager = $resourcesWidgetManager;
        $this->translator = $translator;
        $this->widgetManager = $widgetManager;
        $this->textManager = $textManager;
    }

    /**
     * @EXT\Route(
     *     "/resources/widget/{widgetInstance}",
     *     name="claro_resources_widget",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Widget:resourcesWidget.html.twig")
     */
    public function resourcesWidgetAction(WidgetInstance $widgetInstance)
    {
        $workspace = $widgetInstance->getWorkspace();
        $workspaceId = !is_null($workspace) ? $workspace->getId() : null;
        $config = $this->resourcesWidgetManager->getResourcesWidgetConfig($widgetInstance);
        $mode = $config->getMode();
        $resourceNodes = [];

        switch ($mode) {
            case ResourcesWidgetConfig::DIRECTORY_MODE:
                $directoriesIds = $config->getDirectories();

                foreach ($directoriesIds as $directoryId) {
                    $parent = $this->resourceManager->getById($directoryId);
                    $children = is_null($workspaceId) || ($workspaceId === $parent->getWorkspace()->getId()) ?
                        $this->resourcesWidgetManager->getAllResourcesByDirectory($parent) :
                        [];

                    foreach ($children as $child) {
                        array_push($resourceNodes, $child);
                    }
                }
                break;
            case ResourcesWidgetConfig::TAG_MODE:
                $tags = $config->getTags();

                foreach ($tags as $tagName) {
                    $resources = $this->resourcesWidgetManager->getAllResourcesByTag($tagName, $workspaceId);

                    foreach ($resources as $resource) {
                        $resourceNodes[$resource['id']] = $resource;
                    }
                }
                break;
        }

        return [
            'widgetInstance' => $widgetInstance,
            'resourceNodes' => $resourceNodes,
        ];
    }

    /**
     * @EXT\Route(
     *     "/resources/widget/{widgetInstance}/configure/form",
     *     name="claro_resources_widget_configure_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Template("ClarolineCoreBundle:Widget:resourcesWidgetConfigureForm.html.twig")
     */
    public function resourcesWidgetConfigureFormAction(WidgetInstance $widgetInstance)
    {
        $config = $this->resourcesWidgetManager->getResourcesWidgetConfig($widgetInstance);
        $mode = $config->getMode();
        $directories = $config->getDirectories();
        $tags = $config->getTags();
        $workspace = $widgetInstance->getWorkspace();
        $rootDir = null;

        if (!is_null($workspace)) {
            $rootDir = $this->resourceManager->getWorkspaceRoot($workspace);
        }
        $form = $this->formFactory->create(
            new ResourcesWidgetConfigurationType($this->translator, $mode, $directories, $tags, $rootDir, $workspace)
        );

        return ['form' => $form->createView(), 'config' => $config];
    }

    /**
     * @EXT\Route(
     *     "/resources/widget/configure/config/{config}",
     *     name="claro_resources_widget_configure",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Template("ClarolineCoreBundle:Widget:resourcesWidgetConfigureForm.html.twig")
     */
    public function resourcesWidgetConfigureAction(ResourcesWidgetConfig $config)
    {
        $form = $this->formFactory->create(new ResourcesWidgetConfigurationType($this->translator));
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $mode = $form->get('mode')->getData();
            $config->setMode($mode);
            $resource = $form->get('resource')->getData();
            $tagsText = $form->get('tags')->getData();

            if ($resource) {
                $config->clearDirectories();
                $config->addDirectory($resource->getId());
            }
            if ($tagsText) {
                $tags = explode(',', $tagsText);
                $config->clearTags();

                foreach ($tags as $tag) {
                    $t = trim($tag);

                    if (!empty($t)) {
                        $config->addTag(trim($tag));
                    }
                }
            }
            $this->resourcesWidgetManager->persistResourcesWidgetConfig($config);

            return new JsonResponse('success', 204);
        } else {
            return ['form' => $form->createView(), 'config' => $config];
        }
    }

    /**
     * @EXT\Route(
     *     "/resources_text/widget/{widgetInstance}/configure/form",
     *     name="claro_resources_text_widget_configure_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Template("ClarolineCoreBundle:Widget:resourceTextConfigureForm.html.twig")
     */
    public function resourceTextConfigureFormAction(WidgetInstance $widgetInstance)
    {
        $config = $this->widgetManager->getConfiguration($widgetInstance);
        $details = $config->getDetails();
        $resourceNode = (isset($details) && isset($details['nodeId'])) ? $this->resourceManager->getById($details['nodeId']) : null;
        $form = $this->formFactory->create(new ResourcesTextConfigurationType($resourceNode));

        return ['form' => $form->createView(), 'config' => $config];
    }

    /**
     * @EXT\Route(
     *     "/resources_text/widget/{config}/configure/edit",
     *     name="claro_resources_text_widget_configure",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Template("ClarolineCoreBundle:Widget:resourceTextConfigureForm.html.twig")
     */
    public function resourceTextSubmitFormAction(WidgetDisplayConfig $config)
    {
        $form = $this->formFactory->create(new ResourcesTextConfigurationType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $resource = $form->get('resource')->getData();
            if ($resource) {
                $config->addDetail('nodeId', $resource->getId());
            } else {
                $config->removeDetail('nodeId');
            }

            $this->widgetManager->persistConfiguration($config);

            return new JsonResponse('success', 204);
        }

        return ['form' => $form->createView(), 'config' => $config];
    }

    /**
     * @EXT\Route(
     *     "/resources/widget/{widgetInstance}",
     *     name="claro_resources_widget",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Widget:resourceTextDisplay.html.twig")
     */
    public function displayResourceTextWidgetAction(WidgetInstance $widgetInstance)
    {
        $config = $this->widgetManager->getConfiguration($widgetInstance);
        $details = $config->getDetails();
        if (isset($details) && isset($details['nodeId'])) {
            $resourceNode = $this->resourceManager->getById($details['nodeId']);
            $text = $this->resourceManager->getResourceFromNode($resourceNode);

            return ['text' => $this->textManager->getLastContentRevision($text)];
        }

        return ['text' => ''];
    }
}
