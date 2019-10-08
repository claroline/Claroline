<?php

namespace UJM\LtiBundle\Listener;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
     *     "om"            = @DI\Inject("Claroline\AppBundle\Persistence\ObjectManager"),
     *     "serializer"    = @DI\Inject("Claroline\AppBundle\API\SerializerProvider"),
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
