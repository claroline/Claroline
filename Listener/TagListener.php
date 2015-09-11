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

        if (is_array($datas)) {
            $search = isset($datas['tag']) ? $datas['tag'] : '';
            $user = isset($datas['user']) ? $datas['user'] : null;
            $withPlatform = isset($datas['with_platform']) && $datas['with_platform'];

            $objects = $this->tagManager->getTaggedObjects($user, $withPlatform, $search);

            foreach ($objects as $object) {
                $datas = array();
                $datas['class'] = $object->getObjectClass();
                $datas['objectId'] = $object->getObjectId();
                $taggedObjects[] = $datas;
            }
        }
        $event->setResponse($taggedObjects);
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
}
