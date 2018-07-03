<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @DI\Service("claroline.manager.resource_node")
 */
class ResourceNodeManager
{
    /** @var ObjectManager */
    private $om;

    /** @var ResourceManager */
    private $resourceManager;

    /**
     * ResourceNodeManager constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "session"         = @DI\Inject("session")
     * })
     *
     * @param ObjectManager    $om
     * @param ResourceManager  $resourceManager
     * @param SessionInterface $session
     */
    public function __construct(
        ObjectManager $om,
        ResourceManager $resourceManager,
        SessionInterface $session
    ) {
        $this->om = $om;
        $this->resourceManager = $resourceManager;
        $this->session = $session;
    }

    public function unlock(ResourceNode $resourceNode, $code)
    {
        //if a code is defined
        if ($accessCode = $resourceNode->getAccessCode()) {
            if ($accessCode === $code) {
                $this->session->set($resourceNode->getUuid(), true);

                return true;
            } else {
                $this->session->set($resourceNode->getUuid(), false);

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
            $access = $this->session->get($node->getUuid());

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
