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
        $created = false;

        // Load Ticket types
        $typeRepo = $om->getRepository('FormaLibre\SupportBundle\Entity\Type');
        $functional = $typeRepo->findOneByName('functional');

        if (is_null($functional)) {
            $functional = new Type();
            $functional->setName('functional');
            $om->persist($functional);
            $created = true;
        }
        $technical = $typeRepo->findOneByName('technical');

        if (is_null($technical)) {
            $technical = new Type();
            $technical->setName('technical');
            $om->persist($technical);
            $created = true;
        }

        // Load Ticket status
        $statusRepo = $om->getRepository('FormaLibre\SupportBundle\Entity\Status');
        $status = $statusRepo->findOneByName('PC');

        if (is_null($status)) {
            $status = new Status();
            $status->setCode('PC');
            $status->setName('status_pc');
            $status->setType(Status::STATUS_MANDATORY_START);
            $status->setOrder(1);
            $om->persist($status);
            $created = true;
        }
        $status = $statusRepo->findOneByName('AN');

        if (is_null($status)) {
            $status = new Status();
            $status->setCode('AN');
            $status->setName('status_an');
            $status->setType(Status::STATUS_NORMAL);
            $status->setOrder(2);
            $om->persist($status);
            $created = true;
        }
        $status = $statusRepo->findOneByName('CC');

        if (is_null($status)) {
            $status = new Status();
            $status->setCode('CC');
            $status->setName('status_cc');
            $status->setType(Status::STATUS_NORMAL);
            $status->setOrder(3);
            $om->persist($status);
            $created = true;
        }
        $status = $statusRepo->findOneByName('PR');

        if (is_null($status)) {
            $status = new Status();
            $status->setCode('PR');
            $status->setName('status_pr');
            $status->setType(Status::STATUS_NORMAL);
            $status->setOrder(4);
            $om->persist($status);
            $created = true;
        }
        $status = $statusRepo->findOneByName('AC');

        if (is_null($status)) {
            $status = new Status();
            $status->setCode('AC');
            $status->setName('status_ac');
            $status->setType(Status::STATUS_NORMAL);
            $status->setOrder(5);
            $om->persist($status);
            $created = true;
        }
        $status = $statusRepo->findOneByName('ET');

        if (is_null($status)) {
            $status = new Status();
            $status->setCode('ET');
            $status->setName('status_et');
            $status->setType(Status::STATUS_NORMAL);
            $status->setOrder(6);
            $om->persist($status);
            $created = true;
        }
        $status = $statusRepo->findOneByName('FA');

        if (is_null($status)) {
            $status = new Status();
            $status->setCode('FA');
            $status->setName('status_fa');
            $status->setType(Status::STATUS_MANDATORY_END);
            $status->setOrder(7);
            $om->persist($status);
            $created = true;
        }

        if ($created) {
            $om->flush();
        }
    }
}
