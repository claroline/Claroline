<?php

namespace Claroline\ForumBundle\Manager;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ForumBundle\Entity\Widget\LastMessageWidgetConfig;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.manager.forum_widget")
 */
class ForumWidgetManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var Utilities
     */
    protected $securityUtilities;

    /**
     * @var WorkspaceManager
     */
    protected $workspaceManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "securityUtilities" = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(ObjectManager $objectManager, TokenStorageInterface $tokenStorage, Utilities $securityUtilities, WorkspaceManager $workspaceManager, EntityManager $entityManager)
    {
        $this->objectManager = $objectManager;
        $this->tokenStorage = $tokenStorage;
        $this->securityUtilities = $securityUtilities;
        $this->workspaceManager = $workspaceManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return \Claroline\ForumBundle\Entity\Widget\LastMessageWidgetConfig
     */
    public function getConfig(WidgetInstance $widgetInstance)
    {
        $lastMessageWidgetConfig = $this->objectManager
            ->getRepository('ClarolineForumBundle:Widget\LastMessageWidgetConfig')
            ->findOneOrNullByWidgetInstance($widgetInstance);

        if ($lastMessageWidgetConfig === null) {
            $lastMessageWidgetConfig = new LastMessageWidgetConfig();
            $lastMessageWidgetConfig->setWidgetInstance($widgetInstance);
        }

        return $lastMessageWidgetConfig;
    }

    /**
     * @param WidgetInstance $widgetInstance
     * @param Workspace|null $workspace
     *
     * @return \Claroline\ForumBundle\Entity\Message[]
     */
    public function getLastMessages(WidgetInstance $widgetInstance, Workspace $workspace = null)
    {
        $token = $this->tokenStorage->getToken();
        $roles = $this->securityUtilities->getRoles($token);
        $widgetInstanceConfig = $this->getConfig($widgetInstance);

        $workspaces = array($workspace);
        if ($workspace === null) {
            $workspaces = $this->workspaceManager->getWorkspacesByUser($token->getUser());
        }

        $user = null;
        if ($widgetInstanceConfig->getDisplayMyLastMessages()) {
            $user = $token->getUser();
        }

        return $this->entityManager->getRepository('ClarolineForumBundle:Message')
            ->findNLastByForum($workspaces, $roles, 10, $user);
    }
}
