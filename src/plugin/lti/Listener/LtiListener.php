<?php

namespace UJM\LtiBundle\Listener;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use UJM\LtiBundle\Entity\LtiApp;

class LtiListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Environment */
    private $templating;
    /** @var ToolManager */
    private $toolManager;

    private $ltiAppRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        SerializerProvider $serializer,
        Environment $templating,
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
