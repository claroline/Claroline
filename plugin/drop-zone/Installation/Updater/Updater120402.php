<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Installation\Updater;

use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\InstallationBundle\Updater\Updater;
use Claroline\TeamBundle\Entity\Team;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120402 extends Updater
{
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->om = $container->get('Claroline\AppBundle\Persistence\ObjectManager');
    }

    public function postUpdate()
    {
        $this->generateTeamIds();
    }

    private function generateTeamIds()
    {
        $drops = $this->om->getRepository(Drop::class)->findAll();
        $corrections = $this->om->getRepository(Correction::class)->findAll();
        $teamRepo = $this->om->getRepository(Team::class);
        $index = 0;

        $this->om->startFlushSuite();
        $this->log('Generating team uuid for drops...');

        foreach ($drops as $drop) {
            if ($drop->getTeamId() && !$drop->getTeamUuid()) {
                $team = $teamRepo->findOneBy(['id' => $drop->getTeamId()]);

                if ($team) {
                    $drop->setTeamUuid($team->getUuid());
                    $this->om->persist($drop);
                }
            }
            if (0 === $index % 200) {
                $this->om->forceFlush();
            }
        }
        $this->log('Team uuid generated for drops.');
        $this->om->endFlushSuite();

        $index = 0;
        $this->om->startFlushSuite();
        $this->log('Generating team uuid for corrections...');

        foreach ($corrections as $correction) {
            if ($correction->getTeamId() && !$correction->getTeamUuid()) {
                $team = $teamRepo->findOneBy(['id' => $correction->getTeamId()]);

                if ($team) {
                    $correction->setTeamUuid($team->getUuid());
                    $this->om->persist($correction);
                }
            }
            if (0 === $index % 200) {
                $this->om->forceFlush();
            }
        }
        $this->log('Team uuid generated for corrections.');
        $this->om->endFlushSuite();
    }
}
