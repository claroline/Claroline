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
use Claroline\TagBundle\Form\TagType;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class TagController extends Controller
{
    private $formFactory;
    private $request;
    private $tagManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "tagManager"   = @DI\Inject("claroline.manager.tag_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        RequestStack $requestStack,
        TagManager $tagManager
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->tagManager = $tagManager;
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

        return array(
            'form' => $form->createView(),
            'resourceNode' => $resourceNode,
            'tags' => $tags,
            'resourceTags' => $resourceTags
        );
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
        $form = $this->formFactory->create(new TagType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $tagsList = $form->get('tag')->getData();
            $tags = explode(',', $tagsList);
            $this->tagManager->tagObject($tags, $resourceNode);

            return new JsonResponse('success', 200);
        } else {
            $tags = $this->tagManager->getPlatformTags();
            $resourceTags = $this->tagManager->getTagsByObject($resourceNode);

            return array(
                'form' => $form->createView(),
                'resourceNode' => $resourceNode,
                'tags' => $tags,
                'resourceTags' => $resourceTags
            );
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

        return array(
            'form' => $form->createView(),
            'group' => $group,
            'tags' => $tags,
            'groupTags' => $groupTags
        );
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
            $tagsList = $form->get('tag')->getData();
            $tags = explode(',', $tagsList);
            $this->tagManager->tagObject($tags, $group);

            return new JsonResponse('success', 200);
        } else {
            $tags = $this->tagManager->getPlatformTags();
            $groupTags = $this->tagManager->getTagsByObject($group);

            return array(
                'form' => $form->createView(),
                'group' => $group,
                'tags' => $tags,
                'groupTags' => $groupTags
            );
        }
    }
}
