<?php

namespace Claroline\WorkspaceBundle\Service\Manager;


use Claroline\WorkspaceBundle\Entity\Workspace;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class ACLWorkspaceManager
{
    /**
     * The current Security Context.
     *
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * The AclProvider.
     *
     * @var MutableAclProviderInterface
     */
    private $aclProvider;

    /** The actual workspace manager
     *
     * @var WorkspaceManager
     */
    private $real_manager;

    /**
     * Constructor.
     *
     * @param SecurityContextInterface $securityContext
     * @param ObjectIdentityRetrievalStrategyInterface $objectRetrieval
     * @param MutableAclProviderInterface $aclProvider
     * @param string $commentClass
     */
    public function __construct(
        WorkspaceManager $real_manager,
        SecurityContextInterface $securityContext,
        MutableAclProviderInterface $aclProvider
    )
    {
        $this->real_manager = $real_manager;
        $this->securityContext = $securityContext;
        $this->aclProvider = $aclProvider;
    
    }

    public function create(Workspace $ws)
    {
        $this->real_manager->create($ws);
        
        $owner = $ws->getOwner();
        $securityIdentity = UserSecurityIdentity::fromAccount($owner);

        $ws_identity = ObjectIdentity::fromDomainObject($ws);
        $ws_acl = $this->aclProvider->createAcl($ws_identity);


        $ws_acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
        $this->aclProvider->updateAcl($ws_acl);
        
    }

    public function delete(Workspace $ws)
    {
        if (false === $this->securityContext->isGranted('DELETE', $ws))
        {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }
        
        $ws_identity = ObjectIdentity::fromDomainObject($ws);
        $this->aclProvider->deleteAcl($ws_identity);

        $this->real_manager->delete($ws);
    }
}