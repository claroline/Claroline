<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Form\ResourceNameType;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Claroline\TeamBundle\Manager\TeamManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service
 */
class DropzoneListener
{
    private $tokenStorage;

    /** @var DropzoneManager */
    private $dropzoneManager;

    /** @var FormFactory */
    private $formFactory;

    /** @var Request */
    private $request;

    /** @var TwigEngine */
    private $templating;

    /** @var SerializerProvider */
    private $serializer;

    /** @var TeamManager */
    private $teamManager;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * DropzoneListener constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "dropzoneManager" = @DI\Inject("claroline.manager.dropzone_manager"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "templating"      = @DI\Inject("templating"),
     *     "serializer"      = @DI\Inject("claroline.api.serializer"),
     *     "teamManager"     = @DI\Inject("claroline.manager.team_manager"),
     *     "translator"      = @DI\Inject("translator")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param DropzoneManager       $dropzoneManager
     * @param FormFactory           $formFactory
     * @param RequestStack          $requestStack
     * @param TwigEngine            $templating
     * @param SerializerProvider    $serializer
     * @param TeamManager           $teamManager
     * @param TranslatorInterface   $translator
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        DropzoneManager $dropzoneManager,
        FormFactory $formFactory,
        RequestStack $requestStack,
        TwigEngine $templating,
        SerializerProvider $serializer,
        TeamManager $teamManager,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->dropzoneManager = $dropzoneManager;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
        $this->serializer = $serializer;
        $this->teamManager = $teamManager;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("create_form_claroline_dropzone")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreationForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new ResourceNameType(true), new Dropzone());
        $content = $this->templating->render(
            'ClarolineCoreBundle:resource:create_form.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'claroline_dropzone',
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_dropzone")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(new ResourceNameType(true), new Dropzone());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $published = $form->get('published')->getData();
            $event->setPublished($published);
            $dropzone = $form->getData();
            $event->setResources([$dropzone]);
            $event->stopPropagation();
        } else {
            $content = $this->templating->render(
                'ClarolineCoreBundle:resource:create_form.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'claroline_dropzone',
                ]
            );
            $event->setErrorFormContent($content);
            $event->stopPropagation();
        }
    }

    /**
     * @DI\Observe("load_claroline_dropzone")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Dropzone $dropzone */
        $dropzone = $event->getResource();

        $event->setAdditionalData(
            $this->getDropzoneData($dropzone)
        );
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_claroline_dropzone")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        /** @var Dropzone $dropzone */
        $dropzone = $event->getResource();

        $content = $this->templating->render(
            'ClarolineDropZoneBundle:dropzone:open.html.twig', array_merge([
                '_resource' => $dropzone,
            ], $this->getDropzoneData($dropzone))
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_dropzone")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        /** @var Dropzone $dropzone */
        $dropzone = $event->getResource();

        $copy = $this->dropzoneManager->copyDropzone($dropzone);

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_dropzone")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        /** @var Dropzone $dropzone */
        $dropzone = $event->getResource();

        $this->dropzoneManager->delete($dropzone);

        $event->stopPropagation();
    }

    private function getDropzoneData(Dropzone $dropzone)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof User) {
            $user = null;
        }

        $resourceNode = $dropzone->getResourceNode();

        $serializedTeams = [];
        $teams = !empty($user) ?
            $this->teamManager->getTeamsByUserAndWorkspace($user, $resourceNode->getWorkspace()) :
            [];

        foreach ($teams as $team) {
            $serializedTeams[] = $this->serializer->serialize($team);
        }
        $myDrop = null;
        $finishedPeerDrops = [];
        $errorMessage = null;
        $teamId = null;

        if (!$dropzone->getDropClosed() && $dropzone->getAutoCloseDropsAtDropEndDate() && !$dropzone->getManualPlanning()) {
            $dropEndDate = $dropzone->getDropEndDate();

            if ($dropEndDate < new \DateTime()) {
                $this->dropzoneManager->closeAllUnfinishedDrops($dropzone);
            }
        }

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                $myDrop = !empty($user) ? $this->dropzoneManager->getUserDrop($dropzone, $user) : null;
                $finishedPeerDrops = $this->dropzoneManager->getFinishedPeerDrops($dropzone, $user);
                break;
            case Dropzone::DROP_TYPE_TEAM:
                $drops = [];
                $teamsIds = array_map(function ($team) {
                    return $team['id'];
                }, $serializedTeams);

                /* Fetches team drops associated to user */
                $teamDrops = !empty($user) ? $this->dropzoneManager->getTeamDrops($dropzone, $user) : [];

                /* Unregisters user from unfinished drops associated to team he doesn't belong to anymore */
                foreach ($teamDrops as $teamDrop) {
                    if (!$teamDrop->isFinished() && !in_array($teamDrop->getTeamId(), $teamsIds)) {
                        /* Unregisters user from unfinished drop */
                        $this->dropzoneManager->unregisterUserFromTeamDrop($teamDrop, $user);
                    } else {
                        $drops[] = $teamDrop;
                    }
                }
                if (0 === count($drops)) {
                    /* Checks if there are unfinished drops from teams he belongs but not associated to him */
                    $unfinishedTeamsDrops = $this->dropzoneManager->getTeamsUnfinishedDrops($dropzone, $teamsIds);

                    if (count($unfinishedTeamsDrops) > 0) {
                        $errorMessage = $this->translator->trans('existing_unfinished_team_drop_error', [], 'dropzone');
                    }
                } elseif (1 === count($drops)) {
                    $myDrop = $drops[0];
                } else {
                    $errorMessage = $this->translator->trans('more_than_one_drop_error', [], 'dropzone');
                }
                if (!empty($myDrop)) {
                    $teamId = $myDrop->getTeamId();
                }
                $finishedPeerDrops = $this->dropzoneManager->getFinishedPeerDrops($dropzone, $user, $teamId);
                break;
        }
        $serializedTools = $this->dropzoneManager->getSerializedTools();
        /* TODO: generate ResourceUserEvaluation for team */
        $userEvaluation = !empty($user) ? $this->dropzoneManager->generateResourceUserEvaluation($dropzone, $user) : null;

        return [
            'dropzone' => $this->serializer->serialize($dropzone),
            'user' => $this->serializer->serialize($user),
            'myDrop' => !empty($myDrop) ? $this->serializer->serialize($myDrop) : null,
            'nbCorrections' => count($finishedPeerDrops),
            'tools' => $serializedTools,
            'evaluation' => $this->serializer->serialize($userEvaluation),
            'teams' => $serializedTeams,
            'errorMessage' => $errorMessage,
        ];
    }
}
