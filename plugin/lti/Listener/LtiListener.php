<?php

namespace UJM\LtiBundle\Listener;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\LtiBundle\Entity\LtiApp;

/**
 * @DI\Service
 */
class LtiListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TwigEngine */
    private $templating;
    /** @var ToolManager */
    private $toolManager;

    private $ltiAppRepo;

    /**
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"    = @DI\Inject("claroline.api.serializer"),
     *     "templating"    = @DI\Inject("templating"),
     *     "toolManager"   = @DI\Inject("claroline.manager.tool_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param SerializerProvider            $serializer
     * @param TwigEngine                    $templating
     * @param ToolManager                   $toolManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        SerializerProvider $serializer,
        TwigEngine $templating,
        ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->serializer = $serializer;
        $this->templating = $templating;
        $this->toolManager = $toolManager;

        $this->ltiAppRepo = $om->getRepository(LtiApp::class);
    }

    /**
     * @DI\Observe("administration_tool_LTI")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onAdministrationToolOpen(OpenAdministrationToolEvent $event)
    {
        $ltiTool = $this->toolManager->getAdminToolByName('LTI');

        if (is_null($ltiTool) || !$this->authorization->isGranted('OPEN', $ltiTool)) {
            throw new AccessDeniedException();
        }

        $content = $this->templating->render('UJMLtiBundle:administration:management.html.twig', [
            'context' => [
                'type' => Tool::ADMINISTRATION,
            ],
        ]);

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * Loads a LTI resource.
     *
     * @DI\Observe("resource.ujm_lti_resource.load")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        $ltiResource = $event->getResource();
        $collection = new ResourceCollection([$ltiResource->getResourceNode()]);
        $ltiApps = $this->authorization->isGranted('EDIT', $collection) ?
            $this->ltiAppRepo->findBy([], ['title' => 'ASC']) :
            [];

        $event->setData([
            'ltiResource' => $this->serializer->serialize($ltiResource),
            'ltiApps' => array_map(function (LtiApp $app) {
                return $this->serializer->serialize($app, [Options::SERIALIZE_MINIMAL]);
            }, $ltiApps),
        ]);

        $event->stopPropagation();
    }
}
