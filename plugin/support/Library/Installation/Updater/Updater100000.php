<?php

namespace FormaLibre\SupportBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use FormaLibre\SupportBundle\Entity\Status;
use FormaLibre\SupportBundle\Entity\Type;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater100000 extends Updater
{
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateTypesAndStatus();
    }

    private function updateTypesAndStatus()
    {
        $this->log('Updating type...');
        $typeRepo = $this->om->getRepository('FormaLibre\SupportBundle\Entity\Type');

        $typeFunctional = $typeRepo->findOneByName('functional');

        if (is_null($typeFunctional)) {
            $typeFunctional = new Type();
            $typeFunctional->setName('functional');
        }
        $typeFunctional->setLocked(true);
        $this->om->persist($typeFunctional);

        $typeTechnical = $typeRepo->findOneByName('technical');

        if (is_null($typeTechnical)) {
            $typeTechnical = new Type();
            $typeTechnical->setName('technical');
        }
        $typeTechnical->setLocked(true);
        $this->om->persist($typeTechnical);

        $this->log('Updating status...');
        $statusRepo = $this->om->getRepository('FormaLibre\SupportBundle\Entity\Status');

        $statusNew = $statusRepo->findOneByCode('NEW');

        if (is_null($statusNew)) {
            $statusNew = new Status();
            $statusNew->setCode('NEW');
            $statusNew->setName('status_new');
            $statusNew->setOrder(1);
        }
        $statusNew->setLocked(true);
        $this->om->persist($statusNew);

        $statusClosed = $statusRepo->findOneByCode('FA');

        if (is_null($statusClosed)) {
            $statusClosed = new Status();
            $statusClosed->setCode('FA');
            $statusClosed->setName('status_fa');
            $statusClosed->setOrder(8);
        }
        $statusClosed->setLocked(true);
        $this->om->persist($statusClosed);

        $statusForwarded = $statusRepo->findOneByCode('FW');

        if (is_null($statusForwarded)) {
            $statusForwarded = new Status();
            $statusForwarded->setCode('FW');
            $statusForwarded->setName('status_forwarded');
            $statusForwarded->setOrder(9);
        }
        $statusForwarded->setLocked(true);
        $this->om->persist($statusForwarded);
        $this->om->flush();
    }
}
