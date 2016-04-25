<?php

namespace FormaLibre\PresenceBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;

class Updater060101 extends Updater
{
    const MAX_BATCH_SIZE = 200;

    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->log('Fixing periods...');
        $om = $this->container->get('claroline.persistence.object_manager');
        $presences = $om->getRepository('FormaLibrePresenceBundle:Presence')->findAll();
        $count = count($presences);
        $i = 0;

        foreach ($presences as $presence) {
            ++$i;
            $date = $presence->getDate();
            $newDate = $this->invertDate($date);
            $presence->setDate($newDate);
            $om->persist($presence);

            if ($i % self::MAX_BATCH_SIZE === 0) {
                $this->log('Flushing '.$i.'/'.$count.' presences');
                $om->flush();
            }
        }

        $this->log('Done !');
        $om->flush();

        $this->log('Setting course session to null before the 13/10/2015...');
        $i = 0;
        $criticalDate = \DateTime::createFromFormat('d/m/y', '13/10/15');
        $criticalDate->setTime(0, 0);

        foreach ($presences as $presence) {
            if ($presence->getDate() <= $criticalDate) {
                ++$i;
                $presence->setCourseSession(null);
                $om->persist($presence);

                if ($i % self::MAX_BATCH_SIZE === 0) {
                    $this->log('Flushing '.$i.'/'.$count.' presences');
                    $om->flush();
                }
            }
        }

        $this->log('Done !');
        $om->flush();
    }

    private function invertDate(\DateTime $date)
    {
        $y = $date->format('y');
        $m = $date->format('m');
        $d = $date->format('d');

        $newDate = \DateTime::createFromFormat('d-m-y', $y.'-'.$m.'-'.$d);
        $newDate->setTime(0, 0);

        return $newDate;
    }
}
