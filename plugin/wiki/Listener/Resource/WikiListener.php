<?php

namespace Icap\WikiBundle\Listener\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Form\WikiType;
use Icap\WikiBundle\Manager\SectionManager;
use Icap\WikiBundle\Manager\WikiManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service()
 */
class WikiListener
{
    use PermissionCheckerTrait;

    /** @var null|\Symfony\Component\HttpFoundation\Request */
    private $request;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $templating;

    /** @var null|\Claroline\CoreBundle\Entity\User */
    private $user;

    /** @var ObjectManager */
    private $om;

    /** @var WikiManager */
    private $wikiManager;

    /** @var SectionManager */
    private $sectionManager;

    /** @var ResourceEvaluationManager */
    private $evaluationManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "templating"             = @DI\Inject("templating"),
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "objectManager"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "wikiManager"            = @DI\Inject("icap.wiki.manager"),
     *     "sectionManager"         = @DI\Inject("icap.wiki.section_manager"),
     *     "evaluationManager"      = @DI\Inject("claroline.manager.resource_evaluation_manager"),
     *     "requestStack"           = @DI\Inject("request_stack")
     * })
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EngineInterface $templating,
        TokenStorageInterface $tokenStorage,
        ObjectManager $objectManager,
        WikiManager $wikiManager,
        SectionManager $sectionManager,
        ResourceEvaluationManager $evaluationManager,
        RequestStack $requestStack
    ) {
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->om = $objectManager;
        $this->wikiManager = $wikiManager;
        $this->sectionManager = $sectionManager;
        $this->evaluationManager = $evaluationManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @DI\Observe("create_form_icap_wiki")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new WikiType(), new Wiki());
        $content = $this->templating->render(
            'ClarolineCoreBundle:resource:create_form.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'icap_wiki',
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_icap_wiki")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(new WikiType(), new Wiki());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $wiki = $form->getData();
            $event->setResources([$wiki]);
        } else {
            $content = $this->templating->render(
                'ClarolineCoreBundle:resource:create_form.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'icap_wiki',
                ]
            );
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_icap_wiki")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $resourceNode = $event->getResourceNode();
        $wiki = $event->getResource();
        $this->checkPermission('OPEN', $resourceNode, [], true);
        $isAdmin = $this->checkPermission('EDIT', $resourceNode);
        $sectionTree = $this->sectionManager->getSerializedSectionTree($wiki, $this->user, $isAdmin);
        $content = $this->templating->render(
            'IcapWikiBundle:wiki:open.html.twig',
            [
                '_resource' => $wiki,
                'sectionTree' => $sectionTree,
            ]
        );
        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_icap_wiki")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->om->remove($event->getResource());
        $this->om->flush();
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_icap_wiki")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $wiki = $event->getResource();
        $newWiki = $this->wikiManager->copyWiki($wiki, $this->user);
        $event->setCopy($newWiki);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("generate_resource_user_evaluation_icap_wiki")
     *
     * @param GenericDataEvent $event
     */
    public function onGenerateResourceTracking(GenericDataEvent $event)
    {
        $data = $event->getData();
        $node = $data['resourceNode'];
        $user = $data['user'];
        $startDate = $data['startDate'];

        $logs = $this->evaluationManager->getLogsForResourceTracking(
            $node,
            $user,
            ['resource-read', 'resource-icap_wiki-section_create', 'resource-icap_wiki-section_update'],
            $startDate
        );
        $nbLogs = count($logs);

        if ($nbLogs > 0) {
            $this->om->startFlushSuite();
            $tracking = $this->evaluationManager->getResourceUserEvaluation($node, $user);
            $tracking->setDate($logs[0]->getDateLog());
            $status = AbstractResourceEvaluation::STATUS_UNKNOWN;
            $nbAttempts = 0;
            $nbOpenings = 0;

            foreach ($logs as $log) {
                switch ($log->getAction()) {
                    case 'resource-read':
                        ++$nbOpenings;

                        if (AbstractResourceEvaluation::STATUS_UNKNOWN === $status) {
                            $status = AbstractResourceEvaluation::STATUS_OPENED;
                        }
                        break;
                    case 'resource-icap_wiki-section_create':
                    case 'resource-icap_wiki-section_update':
                        ++$nbAttempts;
                        $status = AbstractResourceEvaluation::STATUS_PARTICIPATED;
                        break;
                }
            }
            $tracking->setStatus($status);
            $tracking->setNbAttempts($nbAttempts);
            $tracking->setNbOpenings($nbOpenings);
            $this->om->persist($tracking);
            $this->om->endFlushSuite();
        }
        $event->stopPropagation();
    }
}
