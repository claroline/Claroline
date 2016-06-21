<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\ActivityType;
use Claroline\CoreBundle\Manager\ActivityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Observe;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Service
 */
class ActivityListener
{
    private $router;
    private $formFactory;
    private $templating;
    private $request;
    private $persistence;
    private $activityManager;
    private $tokenStorage;

    /**
     * @InjectParams({
     *     "router"             = @Inject("router"),
     *     "formFactory"        = @Inject("form.factory"),
     *     "templating"         = @Inject("templating"),
     *     "request"            = @Inject("request_stack"),
     *     "persistence"        = @Inject("claroline.persistence.object_manager"),
     *     "activityManager"    = @Inject("claroline.manager.activity_manager"),
     *     "tokenStorage"       = @Inject("security.token_storage"),
     * })
     */
    public function __construct(
        $router,
        $formFactory,
        $templating,
        $request,
        $persistence,
        ActivityManager $activityManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->request = $request->getMasterRequest();
        $this->persistence = $persistence;
        $this->activityManager = $activityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Observe("create_form_activity")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new ActivityType(), new Activity());
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'activity',
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @Observe("create_activity")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(new ActivityType(), new Activity());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $published = $form->get('published')->getData();
            $event->setPublished($published);
            $activity = $form->getData();
            $activity->setName($activity->getTitle());
            $activityParameters = new ActivityParameters();
            $activityParameters->setActivity($activity);
            $activity->setParameters($activityParameters);
            $event->setResources(array($activity));
            $event->stopPropagation();

            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'activity',
            )
        );

        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @Observe("delete_activity")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * @Observe("copy_activity")
     *
     * @todo: Do the resources need to be copied ?
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $activity = $this->activityManager->copyActivity($event->getResource());

        $this->persistence->persist($activity);
        $event->setCopy($activity);
        $event->stopPropagation();
    }

    /**
     * @Observe("open_activity")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $activity = $event->getResource();
        $params = $activity->getParameters();
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user === 'anon.') {
            throw new AccessDeniedException();
        }

        $evaluation =
            $this->activityManager->getEvaluationByUserAndActivityParams($user, $params) ?:
            $this->activityManager->createBlankEvaluation($user, $params);

        $content = $this->templating->render(
            'ClarolineCoreBundle:Activity:index.html.twig',
            array(
                '_resource' => $activity,
                'activityParams' => $params,
                'evaluation' => $evaluation,
            )
        );

        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @Observe("compose_activity")
     */
    public function onCompose(CustomActionResourceEvent $event)
    {
        $activity = $event->getResource();

        $event->setResponse(
            new RedirectResponse(
                $this->router->generate('claro_activity_edit', array('resource' => $activity->getId()))
            )
        );

        $event->stopPropagation();
    }
}
