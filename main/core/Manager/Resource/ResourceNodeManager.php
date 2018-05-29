<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.manager.resource_node")
 */
class ResourceNodeManager
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @var StrictDispatcher
     */
    private $eventDispatcher;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ResourceNodeSerializer
     */
    private $serializer;

    /**
     * @var RightsManager
     */
    private $rightsManager;

    /**
     * @var ResourceManager
     */
    private $resourceManager;

    /**
     * ResourceNodeManager constructor.
     *
     * @DI\InjectParams({
     *     "authorization"          = @DI\Inject("security.authorization_checker"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "resourceNodeSerializer" = @DI\Inject("claroline.serializer.resource_node"),
     *     "rightsManager"          = @DI\Inject("claroline.manager.rights_manager"),
     *     "resourceManager"        = @DI\Inject("claroline.manager.resource_manager"),
     *     "session"                = @DI\Inject("session")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param StrictDispatcher              $eventDispatcher
     * @param ObjectManager                 $om
     * @param ResourceNodeSerializer        $resourceNodeSerializer
     * @param RightsManager                 $rightsManager
     * @param ResourceManager               $resourceManager
     * @param SessionInterface              $session
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $eventDispatcher,
        ObjectManager $om,
        ResourceNodeSerializer $resourceNodeSerializer,
        RightsManager $rightsManager,
        ResourceManager $resourceManager,
        SessionInterface $session
    ) {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->serializer = $resourceNodeSerializer; // todo : load from the SerializerProvider
        $this->rightsManager = $rightsManager;
        $this->resourceManager = $resourceManager;
        $this->session = $session;
    }

    public function unlock(ResourceNode $resourceNode, $code)
    {
        //if a code is defined
        if ($accessCode = $resourceNode->getAccessCode()) {
            if ($accessCode === $code) {
                $this->session->set($resourceNode->getGuid(), true);

                return true;
            } else {
                $this->session->set($resourceNode->getGuid(), false);

                return false;
            }
        }

        return true;
    }

    public function isCodeProtected(ResourceNode $resourceNode)
    {
        return !empty($resourceNode->getAccessCode());
    }

    public function requiresUnlock(ResourceNode $resourceNode)
    {
        $isProtected = $this->isCodeProtected($resourceNode);

        if ($isProtected) {
            return !$this->isUnlocked($resourceNode);
        }

        return false;
    }

    public function isUnlocked(ResourceNode $node)
    {
        if ($node->getAccessCode()) {
            $access = $this->session->get($node->getGuid());

            return null !== $access ? $access : false;
        }

        return true;
    }

    public function addView(ResourceNode $node)
    {
        $node->addView();
        $this->om->persist($node);
        $this->om->flush();

        return $node;
    }

    /**
     * Replace a user by another in every resource.
     *
     * @param User $from
     * @param User $to
     *
     * @return int The number of updated resources
     */
    public function replaceCreator(User $from, User $to)
    {
        return $this->resourceManager->replaceCreator($from, $to);
    }
}
