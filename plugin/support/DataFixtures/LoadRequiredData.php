<?php

namespace FormaLibre\SupportBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use FormaLibre\SupportBundle\Entity\Status;
use FormaLibre\SupportBundle\Entity\Type;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadRequiredData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $om)
    {
        // Load Ticket types
        $typeRepo = $om->getRepository('FormaLibre\SupportBundle\Entity\Type');
        $functional = $typeRepo->findOneByName('functional');

        if (is_null($functional)) {
            $functional = new Type();
            $functional->setName('functional');
            $functional->setLocked(true);
            $om->persist($functional);
        }
        $technical = $typeRepo->findOneByName('technical');

        if (is_null($technical)) {
            $technical = new Type();
            $technical->setName('technical');
            $technical->setLocked(true);
            $om->persist($technical);
        }

        // Load Ticket status
        $statusRepo = $om->getRepository('FormaLibre\SupportBundle\Entity\Status');
        $statusNew = $statusRepo->findOneByCode('NEW');

        if (is_null($statusNew)) {
            $statusNew = new Status();
            $statusNew->setCode('NEW');
            $statusNew->setName('status_new');
            $statusNew->setOrder(1);
            $statusNew->setLocked(true);
            $om->persist($statusNew);
        }
        $statusPC = $statusRepo->findOneByCode('PC');

        if (is_null($statusPC)) {
            $statusPC = new Status();
            $statusPC->setCode('PC');
            $statusPC->setName('status_pc');
            $statusPC->setOrder(2);
            $om->persist($statusPC);
        }
        $statusAN = $statusRepo->findOneByCode('AN');

        if (is_null($statusAN)) {
            $statusAN = new Status();
            $statusAN->setCode('AN');
            $statusAN->setName('status_an');
            $statusAN->setOrder(3);
            $om->persist($statusAN);
        }
        $statusCC = $statusRepo->findOneByCode('CC');

        if (is_null($statusCC)) {
            $statusCC = new Status();
            $statusCC->setCode('CC');
            $statusCC->setName('status_cc');
            $statusCC->setOrder(4);
            $om->persist($statusCC);
        }
        $statusPR = $statusRepo->findOneByCode('PR');

        if (is_null($statusPR)) {
            $statusPR = new Status();
            $statusPR->setCode('PR');
            $statusPR->setName('status_pr');
            $statusPR->setOrder(5);
            $om->persist($statusPR);
        }
        $statusAC = $statusRepo->findOneByCode('AC');

        if (is_null($statusAC)) {
            $statusAC = new Status();
            $statusAC->setCode('AC');
            $statusAC->setName('status_ac');
            $statusAC->setOrder(6);
            $om->persist($statusAC);
        }
        $statusET = $statusRepo->findOneByCode('ET');

        if (is_null($statusET)) {
            $statusET = new Status();
            $statusET->setCode('ET');
            $statusET->setName('status_et');
            $statusET->setOrder(7);
            $om->persist($statusET);
        }
        $statusClosed = $statusRepo->findOneByCode('FA');

        if (is_null($statusClosed)) {
            $statusClosed = new Status();
            $statusClosed->setCode('FA');
            $statusClosed->setName('status_fa');
            $statusClosed->setOrder(8);
            $statusClosed->setLocked(true);
            $om->persist($statusClosed);
        }
        $statusForwarded = $statusRepo->findOneByCode('FW');

        if (is_null($statusForwarded)) {
            $statusForwarded = new Status();
            $statusForwarded->setCode('FW');
            $statusForwarded->setName('status_forwarded');
            $statusForwarded->setOrder(9);
            $statusForwarded->setLocked(true);
            $om->persist($statusForwarded);
        }
        $om->flush();
    }
}
