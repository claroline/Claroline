<?php

namespace Innova\PathBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Manager\ResourceManager;

use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\Step2ResourceNode;
use Innova\PathBundle\Entity\NonDigitalResource;

/**
 * Step2ResourceNode Manager
 * Manages life cycle of Step2ResourceNode
 * @author Innovalangues <contact@innovalangues.net>
 *
 */
class Step2ResourceNodeManager
{
    /**
     * Current entity manage for data persist
     * @var \Doctrine\ORM\EntityManagerEntity Manager $em
     */
    protected $em;
    
    /**
     * Class constructor - Inject required services
     * @param EntityManager $entityManager
     * @param SecurityContext $securityContext
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }
    

    public function edit(Step $step, $resourceNodeId, $excluded, $propagated, $order)
    {

        $step2ressourceNode = $this->em->getRepository('InnovaPathBundle:Step2ResourceNode')->findOneBy(array (
            'step' => $step, 
            'resourceNode' => $resourceNodeId,
            'excluded' => $excluded,
        ));
        
        if (!$step2ressourceNode) {
            $step2ressourceNode = new Step2ResourceNode();
        }

        $step2ressourceNode->setResourceNode($this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($resourceNodeId));
        $step2ressourceNode->setStep($step);
        $step2ressourceNode->setExcluded($excluded);
        $step2ressourceNode->setPropagated($propagated);
        $step2ressourceNode->setResourceOrder($order);

        $this->em->persist($step2ressourceNode);
        $this->em->flush();

        return $step2ressourceNode;
    }
}