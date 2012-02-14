<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;

class LoadWorkspaceData extends AbstractFixture
{
    /**
     * Creates a simple workspace with the following structure :
     * 
     * Workspace A              (public)
     *      Workspace B         (public)
     *      Workspace C         (public)
     *          Workspace D     (private)
     *          Workspace E     (private)
     *              Workspace F (private)
     */
    public function load(ObjectManager $manager)
    {
        $wsA = new SimpleWorkspace();
        $wsA->setName('Workspace A');
        $wsB = new SimpleWorkspace();
        $wsB->setName('Workspace B');
        $wsC = new SimpleWorkspace();
        $wsC->setName('Workspace C');
        $wsD = new SimpleWorkspace();
        $wsD->setName('Workspace D');
        $wsD->setPublic(false);
        $wsE = new SimpleWorkspace();
        $wsE->setName('Workspace E');
        $wsE->setPublic(false);
        $wsF = new SimpleWorkspace();
        $wsF->setName('Workspace F');
        $wsF->setPublic(false);
        
        $wsB->setParent($wsA);
        $wsC->setParent($wsA);
        $wsD->setParent($wsC);
        $wsE->setParent($wsC);
        $wsF->setParent($wsE);
        
        $manager->persist($wsA);
        $manager->persist($wsB);
        $manager->persist($wsC);
        $manager->persist($wsD);
        $manager->persist($wsE);
        $manager->persist($wsF);
        $manager->flush();

        $this->addReference('workspace/ws_a', $wsA);
        $this->addReference('workspace/ws_b', $wsB);
        $this->addReference('workspace/ws_c', $wsC);
        $this->addReference('workspace/ws_d', $wsD);
        $this->addReference('workspace/ws_e', $wsE);
        $this->addReference('workspace/ws_f', $wsF);
    }
}