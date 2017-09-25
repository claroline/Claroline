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
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Menu\GroupAdditionalActionEvent;
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
    ) {
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->tagManager = $tagManager;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("claroline_tag_object")
     *
     * @param GenericDataEvent $event
     */
    public function onObjectTag(GenericDataEvent $event)
    {
        $taggedObject = null;
        $data = $event->getData();

        if (is_array($data) && isset($data['tag']) && isset($data['object'])) {
            $user = isset($data['user']) ? $data['user'] : null;
            $taggedObject = $this->tagManager->tagObject($data['tag'], $data['object'], $user);
        }
        $event->setResponse($taggedObject);
    }

    /**
     * @DI\Observe("claroline_tag_multiple_data")
     *
     * @param GenericDataEvent $event
     */
    public function onDataTag(GenericDataEvent $event)
    {
        $taggedObject = null;
        $data = $event->getData();

        if (is_array($data) && isset($data['tags']) && isset($data['data'])) {
            $user = isset($data['user']) ? $data['user'] : null;
            $replace = isset($data['replace']) && $data['replace'];
            $taggedObject = $this->tagManager->tagData($data['tags'], $data['data'], $user, $replace);
        }
        $event->setResponse($taggedObject);
    }

    /**
     * @DI\Observe("claroline_retrieve_tagged_objects")
     *
     * @param GenericDataEvent $event
     */
    public function onRetrieveObjectsByTag(GenericDataEvent $event)
    {
        $taggedObjects = [];
        $data = $event->getData();

        if (is_array($data) && isset($data['tag']) && !empty($data['tag'])) {
            $search = $data['tag'];
            $user = isset($data['user']) ? $data['user'] : null;
            $withPlatform = isset($data['with_platform']) && $data['with_platform'];
            $strictSearch = isset($data['strict']) ? $data['strict'] : false;
            $class = isset($data['class']) ? $data['class'] : null;
            $objectResponse = isset($data['object_response']) && $data['object_response'];
            $orderedBy = isset($data['ordered_by']) ? $data['ordered_by'] : 'id';
            $order = isset($data['order']) ? $data['order'] : 'ASC';
            $ids = isset($data['ids']) ? $data['ids'] : [];

            $objects = $this->tagManager->getTaggedObjects(
                $user,
                $withPlatform,
                $class,
                $search,
                $strictSearch,
                'name',
                'ASC',
                false,
                1,
                50,
                $ids
            );

            if (!is_null($class) && $objectResponse) {
                $objectsIds = [];

                foreach ($objects as $object) {
                    $objectsIds[] = $object->getObjectId();
                }
                $taggedObjects = $this->tagManager->getObjectsByClassAndIds(
                    $class,
                    $objectsIds,
                    $orderedBy,
                    $order
                );
            } else {
                foreach ($objects as $object) {
                    $data = [];
                    $data['class'] = $object->getObjectClass();
                    $data['id'] = $object->getObjectId();
                    $data['name'] = $object->getObjectName();
                    $taggedObjects[] = $data;
                }
            }
        }
        $event->setResponse($taggedObjects);
    }

    /**
     * @DI\Observe("claroline_retrieve_tags")
     *
     * @param GenericDataEvent $event
     */
    public function onRetrieveTags(GenericDataEvent $event)
    {
        $tagsName = [];
        $data = $event->getData();

        if (is_array($data)) {
            $user = isset($data['user']) ? $data['user'] : null;
            $search = isset($data['search']) ? $data['search'] : '';
            $withPlatform = isset($data['with_platform']) && $data['with_platform'];
            $orderedBy = isset($data['ordered_by']) ? $data['ordered_by'] : 'name';
            $order = isset($data['order']) ? $data['order'] : 'ASC';
            $withPager = isset($data['with_pager']) && $data['with_pager'];
            $page = isset($data['page']) ? $data['page'] : 1;
            $max = isset($data['max']) ? $data['max'] : 50;
            $strictSearch = isset($data['strict']) ? $data['strict'] : false;

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
        $params = [];
        $params['_controller'] = 'ClarolineTagBundle:Tag:resourceTagForm';
        $params['resourceNode'] = $event->getResource()->getResourceNode()->getId();
        $subRequest = $this->request->duplicate([], null, $params);
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
            ['group' => $group->getId()]
        );

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('tag_action', [], 'tag'),
            ['uri' => $url]
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
            ['user' => $user->getId()]
        );

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('tag_action', [], 'tag'),
            ['uri' => $url]
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
            ['workspace' => $workspace->getId()]
        );

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('tag_action', [], 'tag'),
            ['uri' => $url]
        )->setExtra('icon', 'fa fa-tags')
        ->setExtra('display', 'modal_form');

        return $menu;
    }

    /**
     * @DI\Observe("claroline_retrieve_user_workspaces_by_tag")
     *
     * @param GenericDataEvent $event
     */
    public function onRetrieveUserWorkspacesByTag(GenericDataEvent $event)
    {
        $workspaces = [];
        $data = $event->getData();

        if (is_array($data) && isset($data['user']) && isset($data['tag'])) {
            $user = $data['user'];
            $tag = $data['tag'];
            $orderedBy = isset($data['ordered_by']) ? $data['ordered_by'] : 'id';
            $order = isset($data['order']) ? $data['order'] : 'ASC';
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
     * @param GenericDataEvent $event
     */
    public function onUsersDelete(GenericDataEvent $event)
    {
        $users = $event->getData();
        $ids = [];

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
     * @param GenericDataEvent $event
     */
    public function onGroupsDelete(GenericDataEvent $event)
    {
        $groups = $event->getData();
        $ids = [];

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
     * @param GenericDataEvent $event
     */
    public function onWorkspacesDelete(GenericDataEvent $event)
    {
        $workspaces = $event->getData();
        $ids = [];

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
     * @param GenericDataEvent $event
     */
    public function onResourcesDelete(GenericDataEvent $event)
    {
        $resources = $event->getData();
        $ids = [];

        foreach ($resources as $resource) {
            $ids[] = $resource->getId();
        }
        $this->tagManager->removeTaggedObjectsByClassAndIds(
            'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            $ids
        );
    }

    /**
     * @DI\Observe("claroline_retrieve_used_tags_by_class_and_ids")
     *
     * @param GenericDataEvent $event
     */
    public function onRetrieveUsedTagsByClassAndIds(GenericDataEvent $event)
    {
        $tags = [];
        $data = $event->getData();

        if (is_array($data) && isset($data['class']) && !empty($data['ids'])) {
            $taggedObjects = $this->tagManager->getTaggedObjects(
                null,
                false,
                $data['class'],
                '',
                false,
                'name',
                'ASC',
                false,
                1,
                50,
                $data['ids']
            );
            foreach ($taggedObjects as $taggedObject) {
                $tag = $taggedObject->getTag();
                $tags[$tag->getId()] = $tag->getName();
            }
        }
        $event->setResponse(array_values($tags));
    }
}
