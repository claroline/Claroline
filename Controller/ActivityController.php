<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Form\ActivityType;
use Claroline\CoreBundle\Manager\ActivityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ActivityController
{
    private $securityContext;
    private $formFactory;
    private $request;

    /**
     * @InjectParams({
     *     "securityContext"    = @Inject("security.context"),
     *     "formFactory"        = @Inject("form.factory"),
     *     "request"            = @Inject("request_stack"),
     *     "activityManager"    = @Inject("claroline.manager.activity_manager")
     * })
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        RequestStack $request,
        ActivityManager $activityManager
    )
    {
        $this->securityContext = $securityContext;
        $this->formFactory = $formFactory;
        $this->request = $request->getMasterRequest();
        $this->activityManager = $activityManager;
    }

    /**
     * @Route("edit/{resource}", name="activity_edit")
     * @ParamConverter("resource", class = "ClarolineCoreBundle:Resource\Activity", options = {"id" = "resource"})
     * @Template()
     */
    public function editAction($resource)
    {
        $this->checkAccess('edit', $resource);

        $form = $this->formFactory->create(new ActivityType, $resource);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->activityManager->editActivity($form->getData());
        }

        return array('_resource' => $resource, 'form' => $form->createView());
    }

    /**
     * @Route("add/{activity}/{resource}", name="activity_add", options = {"expose": true})
     * @ParamConverter("activity", class = "ClarolineCoreBundle:Resource\Activity", options = {"id" = "activity"})
     * @ParamConverter("resource", class = "ClarolineCoreBundle:Resource\ResourceNode", options = {"id" = "resource"})
     */
    public function addAction($activity, $resource)
    {
        $this->checkAccess('edit', $resource);

        if ($this->activityManager->addResource($activity, $resource)) {
            return new Response(
                json_encode(
                    array(
                        'id' => $resource->getId(),
                        'name' => $resource->getName(),
                        'type' => $resource->getResourceType(),
                    )
                )
            );
        }

        return new Response('false');
    }

    /**
     * @Route("remove/{activity}/{resource}", name="activity_remove_resource", options = {"expose": true})
     * @ParamConverter("activity", class = "ClarolineCoreBundle:Resource\Activity", options = {"id" = "activity"})
     * @ParamConverter("resource", class = "ClarolineCoreBundle:Resource\ResourceNode", options = {"id" = "resource"})
     */
    public function removeAction($activity, $resource)
    {
        $this->checkAccess('edit', $activity);

        if ($this->activityManager->removeResource($activity, $resource)) {
            return new Response('true');
        }

        return new Response('false');
    }

    private function checkAccess($permission, $resource)
    {
        if (!$this->securityContext->isGranted($permission, $resource)) {
            throw new AccessDeniedException();
        }
    }
}
