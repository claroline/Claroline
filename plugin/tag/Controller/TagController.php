<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Controller;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\TagBundle\Entity\ResourcesTagsWidgetConfig;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Form\ResourcesTagsWidgetConfigurationType;
use Claroline\TagBundle\Form\TagType;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TagController extends Controller
{
    private $formFactory;
    private $request;
    private $router;
    private $tagManager;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "router"          = @DI\Inject("router"),
     *     "tagManager"      = @DI\Inject("claroline.manager.tag_manager"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        RequestStack $requestStack,
        RouterInterface $router,
        TagManager $tagManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->tagManager = $tagManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @EXT\Route(
     *     "/resource/{resourceNode}/tag/form",
     *     name="claro_tag_resource_tag_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTagBundle:Tag:resourceTagModalForm.html.twig")
     */
    public function resourceTagFormAction(ResourceNode $resourceNode)
    {
        $form = $this->formFactory->create(new TagType());
        $tags = $this->tagManager->getPlatformTags();
        $resourceTags = $this->tagManager->getTagsByObject($resourceNode);

        return [
            'form' => $form->createView(),
            'resourceNode' => $resourceNode,
            'tags' => $tags,
            'resourceTags' => $resourceTags,
        ];
    }

    /**
     * @EXT\Route(
     *     "/resource/{resourceNode}/tag",
     *     name="claro_tag_resource_tag",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTagBundle:Tag:resourceTagModalForm.html.twig")
     */
    public function resourceTagAction(ResourceNode $resourceNode)
    {
        if (!$this->container->get('security.authorization_checker')->isGranted('EDIT', new ResourceCollection([$resourceNode]))) {
            throw new AccessDeniedException();
        }

        $form = $this->formFactory->create(new TagType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $tags = explode(',', $form->get('tags')->getData());
            $this->tagManager->tagObject($tags, $resourceNode);

            return new JsonResponse('success', 200);
        } else {
            $tags = $this->tagManager->getPlatformTags();
            $resourceTags = $this->tagManager->getTagsByObject($resourceNode);

            return [
                'form' => $form->createView(),
                'resourceNode' => $resourceNode,
                'tags' => $tags,
                'resourceTags' => $resourceTags,
            ];
        }
    }

    /**
     * @EXT\Route(
     *     "/group/{group}/tag/form",
     *     name="claro_tag_group_tag_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTagBundle:Tag:groupTagModalForm.html.twig")
     */
    public function groupTagFormAction(Group $group)
    {
        $form = $this->formFactory->create(new TagType());
        $tags = $this->tagManager->getPlatformTags();
        $groupTags = $this->tagManager->getTagsByObject($group);

        return [
            'form' => $form->createView(),
            'group' => $group,
            'tags' => $tags,
            'groupTags' => $groupTags,
        ];
    }

    /**
     * @EXT\Route(
     *     "/group/{group}/tag",
     *     name="claro_tag_group_tag",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTagBundle:Tag:groupTagModalForm.html.twig")
     * @SEC\PreAuthorize("canOpenAdminTool('user_management')")
     */
    public function groupTagAction(Group $group)
    {
        $form = $this->formFactory->create(new TagType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $tags = explode(',', $form->get('tags')->getData());
            $this->tagManager->tagObject($tags, $group);

            return new JsonResponse('success', 200);
        } else {
            $tags = $this->tagManager->getPlatformTags();
            $groupTags = $this->tagManager->getTagsByObject($group);

            return [
                'form' => $form->createView(),
                'group' => $group,
                'tags' => $tags,
                'groupTags' => $groupTags,
            ];
        }
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/tag/form",
     *     name="claro_tag_user_tag_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTagBundle:Tag:userTagModalForm.html.twig")
     */
    public function userTagFormAction(User $user)
    {
        $form = $this->formFactory->create(new TagType());
        $tags = $this->tagManager->getPlatformTags();
        $userTags = $this->tagManager->getTagsByObject($user);

        return [
            'form' => $form->createView(),
            'user' => $user,
            'tags' => $tags,
            'userTags' => $userTags,
        ];
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/tag",
     *     name="claro_tag_user_tag",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTagBundle:Tag:userTagModalForm.html.twig")
     * @SEC\PreAuthorize("canOpenAdminTool('user_management')")
     */
    public function userTagAction(User $user)
    {
        $form = $this->formFactory->create(new TagType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $tags = explode(',', $form->get('tags')->getData());
            $this->tagManager->tagObject($tags, $user);

            return new JsonResponse('success', 200);
        } else {
            $tags = $this->tagManager->getPlatformTags();
            $userTags = $this->tagManager->getTagsByObject($user);

            return [
                'form' => $form->createView(),
                'user' => $user,
                'tags' => $tags,
                'userTags' => $userTags,
            ];
        }
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/tag/form",
     *     name="claro_tag_workspace_tag_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTagBundle:Tag:workspaceTagModalForm.html.twig")
     */
    public function workspaceTagFormAction(Workspace $workspace)
    {
        $form = $this->formFactory->create(new TagType());
        $tags = $this->tagManager->getPlatformTags();
        $workspaceTags = $this->tagManager->getTagsByObject($workspace);

        return [
            'form' => $form->createView(),
            'workspace' => $workspace,
            'tags' => $tags,
            'workspaceTags' => $workspaceTags,
        ];
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/tag",
     *     name="claro_tag_workspace_tag",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTagBundle:Tag:workspaceTagModalForm.html.twig")
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function workspaceTagAction(Workspace $workspace)
    {
        $form = $this->formFactory->create(new TagType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $tags = explode(',', $form->get('tags')->getData());
            $this->tagManager->tagObject($tags, $workspace);

            return new JsonResponse('success', 200);
        } else {
            $tags = $this->tagManager->getPlatformTags();
            $workspaceTags = $this->tagManager->getTagsByObject($workspace);

            return [
                'form' => $form->createView(),
                'workspace' => $workspace,
                'tags' => $tags,
                'workspaceTags' => $workspaceTags,
            ];
        }
    }

    /******************
     * Widget methods *
     ******************/

    /**
     * @EXT\Route(
     *     "/resources/widget/{widgetInstance}",
     *     name="claro_tag_resources_widget",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineTagBundle:Widget:resourcesTagsWidget.html.twig")
     */
    public function resourcesTagsWidgetAction(WidgetInstance $widgetInstance)
    {
        $workspace = $widgetInstance->getWorkspace();
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        $roles = $token->getRoles();
        $roleNames = [];
        $datas = [];

        foreach ($roles as $role) {
            $roleNames[] = $role->getRole();
        }
        $config = $this->tagManager->getResourcesTagsWidgetConfig($widgetInstance);
        $details = $config->getDetails();
        $nbTags = !empty($details) && isset($details['nb_tags']) ? $details['nb_tags'] : 10;

        if (is_null($workspace)) {
            $taggedObjects = $this->tagManager->getTaggedResourcesByRoles($user, $roleNames);
        } else {
            $taggedObjects = $this->tagManager->getTaggedResourcesByWorkspace($workspace, $user, $roleNames);
        }
        $tags = [];
        $sorted = [];
        // Sort all tagged objects by tag
        foreach ($taggedObjects as $taggedObject) {
            $tag = $taggedObject->getTag();
            $tagId = $tag->getId();

            if (!isset($tags[$tagId])) {
                $tags[$tagId] = [];
                $tags[$tagId]['tag'] = $tag->getName();
                $tags[$tagId]['tag_id'] = $tag->getId();
                $tags[$tagId]['objects'] = [];
            }
            $tags[$tagId]['objects'][] = [
                'id' => $taggedObject->getObjectId(),
                'name' => $taggedObject->getObjectName(),
            ];
        }
        // Sort all tags by number of tagged objects
        foreach ($tags as $tag) {
            $nbObjects = count($tag['objects']);

            if (!isset($sorted[$nbObjects])) {
                $sorted[$nbObjects] = [];
            }
            $sorted[$nbObjects][] = $tag;
        }
        // Sort most used tags DESC
        krsort($sorted);
        $index = 0;
        // Keep X ($nbTags) most used tags
        foreach ($sorted as $contents) {
            if ($index === $nbTags) {
                break;
            } else {
                foreach ($contents as $content) {
                    if ($index === $nbTags) {
                        break;
                    } else {
                        $datas[] = $content;
                        ++$index;
                    }
                }
            }
        }

        return [
            'widgetInstance' => $widgetInstance,
            'nbTags' => $nbTags,
            'datas' => $datas,
        ];
    }

    /**
     * @EXT\Route(
     *     "/resources/widget/{widgetInstance}/configure/form",
     *     name="claro_tag_resources_widget_configure_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTagBundle:Widget:resourcesTagsWidgetConfigForm.html.twig")
     */
    public function resourcesTagsWidgetConfigureFormAction(WidgetInstance $widgetInstance)
    {
        $config = $this->tagManager->getResourcesTagsWidgetConfig($widgetInstance);

        $form = $this->formFactory->create(
            new ResourcesTagsWidgetConfigurationType($config),
            $config
        );

        return ['form' => $form->createView(), 'config' => $config];
    }

    /**
     * @EXT\Route(
     *     "/resources/widget/configure/config/{config}",
     *     name="claro_tag_resources_widget_configure",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function resourcesTagsWidgetConfigureAction(ResourcesTagsWidgetConfig $config)
    {
        $form = $this->formFactory->create(
            new ResourcesTagsWidgetConfigurationType($config),
            $config
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $details = $config->getDetails();
            $nbTags = $form->get('nbTags')->getData();
            $details['nb_tags'] = $nbTags;
            $config->setDetails($details);
            $this->tagManager->persistResourcesTagsWidgetConfig($config);

            return new JsonResponse('success', 204);
        } else {
            return new JsonResponse('success', 204);
        }
    }

    /**
     * @EXT\Route(
     *     "/resources/widget/resource/{resourceNode}/open",
     *     name="claro_tag_resource_from_widget_open",
     *     options={"expose"=true}
     * )
     */
    public function resourceFromWidgetOpenAction(ResourceNode $resourceNode)
    {
        $resourceType = $resourceNode->getResourceType();

        if ($resourceType->getName() === 'directory') {
            $route = $this->router->generate(
                'claro_workspace_open_tool',
                [
                    'toolName' => 'resource_manager',
                    'workspaceId' => $resourceNode->getWorkspace()->getId(),
                ]
            );
            $route .= '?#resources/'.$resourceNode->getId();

            return new RedirectResponse($route);
        } else {
            $route = $this->router->generate(
                'claro_resource_open_short',
                ['node' => $resourceNode->getId()]
            );

            return new RedirectResponse($route);
        }
    }

    /**
     * @EXT\Route(
     *     "/resource/{resourceNode}/tag/{tag}/delete",
     *     name="claro_tag_resource_tag_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function tagDeleteFromResourceAction(ResourceNode $resourceNode, Tag $tag)
    {
        if (!$this->container->get('security.authorization_checker')->isGranted('EDIT', new ResourceCollection([$resourceNode]))) {
            throw new AccessDeniedException();
        }
        $this->tagManager->removeTaggedObjectsByResourceAndTag($resourceNode, $tag);

        return new JsonResponse('success', 200);
    }
}
