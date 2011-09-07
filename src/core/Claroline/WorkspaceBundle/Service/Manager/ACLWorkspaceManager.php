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
    private $realManager;

    /**
     * Constructor.
     *
     * @param SecurityContextInterface $securityContext
     * @param ObjectIdentityRetrievalStrategyInterface $objectRetrieval
     * @param MutableAclProviderInterface $aclProvider
     * @param string $commentClass
     */
    public function __construct(
        WorkspaceManager $realManager,
        SecurityContextInterface $securityContext,
        MutableAclProviderInterface $aclProvider
    )
    {
        $this->realManager = $realManager;
        $this->securityContext = $securityContext;
        $this->aclProvider = $aclProvider;
    
    }

    public function create(Workspace $ws)
    {
        $this->realManager->create($ws);
        
        $owner = $ws->getOwner();
        $securityIdentity = UserSecurityIdentity::fromAccount($owner);

        $wsIdentity = ObjectIdentity::fromDomainObject($ws);
        $wsAcl = $this->aclProvider->createAcl($wsIdentity);


        $wsAcl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
        $this->aclProvider->updateAcl($wsAcl);
        
    }

    public function delete(Workspace $ws)
    {
        if (false === $this->securityContext->isGranted('DELETE', $ws))
        {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }
        
        $wsIdentity = ObjectIdentity::fromDomainObject($ws);
        $this->aclProvider->deleteAcl($wsIdentity);

        $this->realManager->delete($ws);
    }
}