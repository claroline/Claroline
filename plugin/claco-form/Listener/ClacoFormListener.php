<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Listener;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Form\ResourceNameType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\RoleManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service
 */
class ClacoFormListener
{
    private $clacoFormManager;
    private $formFactory;
    private $httpKernel;
    private $om;
    private $platformConfigHandler;
    private $request;
    private $roleManager;
    private $roleSerializer;
    private $templating;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "clacoFormManager"      = @DI\Inject("claroline.manager.claco_form_manager"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "httpKernel"            = @DI\Inject("http_kernel"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "roleManager"           = @DI\Inject("claroline.manager.role_manager"),
     *     "roleSerializer"        = @DI\Inject("claroline.serializer.role"),
     *     "templating"            = @DI\Inject("templating"),
     *     "tokenStorage"          = @DI\Inject("security.token_storage"),
     * })
     *
     * @param ClacoFormManager             $clacoFormManager
     * @param FormFactory                  $formFactory
     * @param HttpKernelInterface          $httpKernel
     * @param ObjectManager                $om
     * @param PlatformConfigurationHandler $platformConfigHandler
     * @param RequestStack                 $requestStack
     * @param RoleManager                  $roleManager,
     * @param RoleSerializer               $roleSerializer,
     * @param TwigEngine                   $templating
     * @param TokenStorageInterface        $tokenStorage
     */
    public function __construct(
        ClacoFormManager $clacoFormManager,
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        PlatformConfigurationHandler $platformConfigHandler,
        RequestStack $requestStack,
        RoleManager $roleManager,
        RoleSerializer $roleSerializer,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage
    ) {
        $this->clacoFormManager = $clacoFormManager;
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $requestStack->getCurrentRequest();
        $this->roleManager = $roleManager;
        $this->roleSerializer = $roleSerializer;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @DI\Observe("create_form_claroline_claco_form")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreationForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new ResourceNameType(true), new ClacoForm());
        $content = $this->templating->render(
            'ClarolineCoreBundle:resource:create_form.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'claroline_claco_form',
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_claco_form")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(new ResourceNameType(true), new ClacoForm());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $published = $form->get('published')->getData();
            $event->setPublished($published);
            $clacoForm = $this->clacoFormManager->initializeClacoForm($form->getData());
            $event->setResources([$clacoForm]);
            $event->stopPropagation();
        } else {
            $content = $this->templating->render(
                'ClarolineCoreBundle:resource:create_form.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'claroline_claco_form',
                ]
            );
            $event->setErrorFormContent($content);
            $event->stopPropagation();
        }
    }

    /**
     * @DI\Observe("open_claroline_claco_form")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $clacoForm = $event->getResource();
        $this->clacoFormManager->checkRight($clacoForm, 'OPEN');
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = 'anon.' === $user;
        $myEntries = $isAnon ? [] : $this->clacoFormManager->getUserEntries($clacoForm, $user);
        $canGeneratePdf = !$isAnon &&
            $this->platformConfigHandler->hasParameter('knp_pdf_binary_path') &&
            file_exists($this->platformConfigHandler->getParameter('knp_pdf_binary_path'));
        $cascadeLevelMax = $this->platformConfigHandler->hasParameter('claco_form_cascade_select_level_max') ?
            $this->platformConfigHandler->getParameter('claco_form_cascade_select_level_max') :
            2;
        $roles = [];
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $roleAnonymous = $this->roleManager->getRoleByName('ROLE_ANONYMOUS');
        $workspaceRoles = $this->roleManager->getWorkspaceRoles($clacoForm->getResourceNode()->getWorkspace());
        $roles[] = $this->roleSerializer->serialize($roleUser, [Options::SERIALIZE_MINIMAL]);
        $roles[] = $this->roleSerializer->serialize($roleAnonymous, [Options::SERIALIZE_MINIMAL]);

        foreach ($workspaceRoles as $workspaceRole) {
            $roles[] = $this->roleSerializer->serialize($workspaceRole, [Options::SERIALIZE_MINIMAL]);
        }
        $myRoles = $isAnon ? [$roleAnonymous->getName()] : $user->getRoles();

        $content = $this->templating->render(
            'ClarolineClacoFormBundle::claco_form/claco_form_open.html.twig', [
                '_resource' => $clacoForm,
                'clacoForm' => $clacoForm,
                'canGeneratePdf' => $canGeneratePdf,
                'cascadeLevelMax' => $cascadeLevelMax,
                'myEntriesCount' => count($myEntries),
                'roles' => $roles,
                'myRoles' => $myRoles,
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_claco_form")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $clacoForm = $event->getResource();
        $newNode = $event->getCopiedNode();
        $copy = $this->clacoFormManager->copyClacoForm($clacoForm, $newNode);

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_claco_form")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->om->remove($event->getResource());
        $event->stopPropagation();
    }
}
