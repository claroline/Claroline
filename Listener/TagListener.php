<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Listener;

use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\GenericDatasEvent;
use Claroline\CoreBundle\Menu\GroupAdditionalActionEvent;
use Claroline\CoreBundle\Menu\UserAdditionalActionEvent;
use Claroline\CoreBundle\Menu\WorkspaceAdditionalActionEvent;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service
 */
class TagListener
{
    private $httpKernel;
    private $request;
    private $router;
    private $tagManager;
    private $translator;

    /**
    * @DI\InjectParams({
    *     "httpKernel"   = @DI\Inject("http_kernel"),
    *     "requestStack" = @DI\Inject("request_stack"),
    *     "router"       = @DI\Inject("router"),
    *     "tagManager"   = @DI\Inject("claroline.manager.tag_manager"),
    *     "translator"   = @DI\Inject("translator")
    * })
    */
    public function __construct(
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        TagManager $tagManager,
        TranslatorInterface $translator
    )
    {
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->tagManager = $tagManager;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("claroline_tag_object")
     *
     * @param GenericDatasEvent $event
     */
    public function onObjectTag(GenericDatasEvent $event)
    {
        $taggedObject = null;
        $datas = $event->getDatas();

        if (is_array($datas) && isset($datas['tag']) && isset($datas['object'])) {
            $user = isset($datas['user']) ? $datas['user'] : null;
            $taggedObject = $this->tagManager->tagObject($datas['tag'], $datas['object'], $user);
        }
        $event->setResponse($taggedObject);
    }

    /**
     * @DI\Observe("claroline_retrieve_tagged_objects")
     *
     * @param GenericDatasEvent $event
     */
    public function onRetrieveObjectsByTag(GenericDatasEvent $event)
    {
        $taggedObjects = array();
        $datas = $event->getDatas();

        if (is_array($datas) && isset($datas['tag']) && !empty($datas['tag'])) {
            $search = $datas['tag'];
            $user = isset($datas['user']) ? $datas['user'] : null;
            $withPlatform = isset($datas['with_platform']) && $datas['with_platform'];
            $strictSearch = isset($datas['strict']) ? $datas['strict'] : false;
            $class = isset($datas['class']) ? $datas['class'] : null;
            $objectResponse = isset($datas['object_response']) && $datas['object_response'];
            $orderedBy = isset($datas['ordered_by']) ? $datas['ordered_by'] : 'id';
            $order = isset($datas['order']) ? $datas['order'] : 'ASC';

            $objects = $this->tagManager->getTaggedObjects(
                $user,
                $withPlatform,
                $class,
                $search,
                $strictSearch
            );

            if (!is_null($class) && $objectResponse) {
                $ids = array();

                foreach ($objects as $object) {
                    $ids[] = $object->getObjectId();
                }
                $taggedObjects = $this->tagManager->getObjectsByClassAndIds(
                    $class,
                    $ids,
                    $orderedBy,
                    $order
                );
            } else {

                foreach ($objects as $object) {
                    $datas = array();
                    $datas['class'] = $object->getObjectClass();
                    $datas['id'] = $object->getObjectId();
                    $datas['name'] = $object->getObjectName();
                    $taggedObjects[] = $datas;
                }
            }
        }
        $event->setResponse($taggedObjects);
    }

    /**
     * @DI\Observe("claroline_retrieve_tags")
     *
     * @param GenericDatasEvent $event
     */
    public function onRetrieveTags(GenericDatasEvent $event)
    {
        $tags = array();
        $tagsName = array();
        $datas = $event->getDatas();

        if (is_array($datas)) {
            $user = isset($datas['user']) ? $datas['user'] : null;
            $search = isset($datas['search']) ? $datas['search'] : '';
            $withPlatform = isset($datas['with_platform']) && $datas['with_platform'];
            $orderedBy = isset($datas['ordered_by']) ? $datas['ordered_by'] : 'name';
            $order = isset($datas['order']) ? $datas['order'] : 'ASC';
            $withPager = isset($datas['with_pager']) && $datas['with_pager'];
            $page = isset($datas['page']) ? $datas['page'] : 1;
            $max = isset($datas['max']) ? $datas['max'] : 50;
            $strictSearch = isset($datas['strict']) ? $datas['strict'] : false;

            $tags = $this->tagManager->getTags(
                $user,
                $search,
                $withPlatform,
                $orderedBy,
                $order,
                $withPager,
                $page,
                $max,
                $strictSearch
            );
        } else {
            $tags = $this->tagManager->getPlatformTags();
        }

        foreach ($tags as $tag) {
            $tagsName[] = $tag->getName();
        }
        $event->setResponse($tagsName);
    }

    /**
     * @DI\Observe("resource_action_tag_action")
     */
    public function onResourceTagAction(CustomActionResourceEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineTagBundle:Tag:resourceTagForm';
        $params['resourceNode'] = $event->getResource()->getResourceNode()->getId();
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("claroline_group_additional_action")
     *
     * @param \Claroline\CoreBundle\Menu\GroupAdditionalActionEvent $event
     */
    public function onGroupActionMenuRender(GroupAdditionalActionEvent $event)
    {
        $group = $event->getGroup();
        $url = $this->router->generate(
            'claro_tag_group_tag_form',
            array('group' => $group->getId())
        );

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('tag_action', array(), 'tag'),
            array('uri' => $url)
        )->setExtra('icon', 'fa fa-tags')
        ->setExtra('display', 'modal_form');

        return $menu;
    }

    /**
     * @DI\Observe("claroline_user_additional_action")
     *
     * @param \Claroline\CoreBundle\Menu\UserAdditionalActionEvent $event
     */
    public function onUserActionMenuRender(UserAdditionalActionEvent $event)
    {
        $user = $event->getUser();
        $url = $this->router->generate(
            'claro_tag_user_tag_form',
            array('user' => $user->getId())
        );

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('tag_action', array(), 'tag'),
            array('uri' => $url)
        )->setExtra('icon', 'fa fa-tags')
        ->setExtra('display', 'modal_form');

        return $menu;
    }

    /**
     * @DI\Observe("claroline_workspace_additional_action")
     *
     * @param \Claroline\CoreBundle\Menu\UserAdditionalActionEvent $event
     */
    public function onWorkspaceActionMenuRender(WorkspaceAdditionalActionEvent $event)
    {
        $workspace = $event->getWorkspace();
        $url = $this->router->generate(
            'claro_tag_workspace_tag_form',
            array('workspace' => $workspace->getId())
        );

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('tag_action', array(), 'tag'),
            array('uri' => $url)
        )->setExtra('icon', 'fa fa-tags')
        ->setExtra('display', 'modal_form');

        return $menu;
    }

    /**
     * @DI\Observe("claroline_retrieve_user_workspaces_by_tag")
     *
     * @param GenericDatasEvent $event
     */
    public function onRetrieveUserWorkspacesByTag(GenericDatasEvent $event)
    {
        $workspaces = array();
        $datas = $event->getDatas();

        if (is_array($datas) && isset($datas['user']) && isset($datas['tag'])) {
            $user = $datas['user'];
            $tag = $datas['tag'];
            $orderedBy = isset($datas['ordered_by']) ? $datas['ordered_by'] : 'id';
            $order = isset($datas['order']) ? $datas['order'] : 'ASC';
            $workspaces = $this->tagManager->getTaggedWorkspacesByRoles(
                $user,
                $tag,
                $orderedBy,
                $order
            );
        }
        $event->setResponse($workspaces);
    }

    /**
     * @DI\Observe("claroline_users_delete")
     *
     * @param GenericDatasEvent $event
     */
    public function onUsersDelete(GenericDatasEvent $event)
    {
        $users = $event->getDatas();
        $ids = array();

        foreach ($users as $user) {
            $ids[] = $user->getId();
        }
        $this->tagManager->removeTaggedObjectsByClassAndIds(
            'Claroline\CoreBundle\Entity\User',
            $ids
        );
    }

    /**
     * @DI\Observe("claroline_groups_delete")
     *
     * @param GenericDatasEvent $event
     */
    public function onGroupsDelete(GenericDatasEvent $event)
    {
        $groups = $event->getDatas();
        $ids = array();

        foreach ($groups as $group) {
            $ids[] = $group->getId();
        }
        $this->tagManager->removeTaggedObjectsByClassAndIds(
            'Claroline\CoreBundle\Entity\Group',
            $ids
        );
    }

    /**
     * @DI\Observe("claroline_workspaces_delete")
     *
     * @param GenericDatasEvent $event
     */
    public function onWorkspacesDelete(GenericDatasEvent $event)
    {
        $workspaces = $event->getDatas();
        $ids = array();

        foreach ($workspaces as $workspace) {
            $ids[] = $workspace->getId();
        }
        $this->tagManager->removeTaggedObjectsByClassAndIds(
            'Claroline\CoreBundle\Entity\Workspace\Workspace',
            $ids
        );
    }

    /**
     * @DI\Observe("claroline_resources_delete")
     *
     * @param GenericDatasEvent $event
     */
    public function onResourcesDelete(GenericDatasEvent $event)
    {
        $resources = $event->getDatas();
        $ids = array();

        foreach ($resources as $resource) {
            $ids[] = $resource->getId();
        }
        $this->tagManager->removeTaggedObjectsByClassAndIds(
            'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            $ids
        );
    }
}
