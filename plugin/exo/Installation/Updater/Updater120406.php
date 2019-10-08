<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Updater;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Library\Options\Direction;

class Updater120406 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->convertBooleanPapers();
    }

    private function convertBooleanPapers()
    {
        $this->log('Convert boolean papers...');

        /** @var ObjectManager $om */
        $om = $this->container->get('Claroline\AppBundle\Persistence\ObjectManager');

        $papers = $om
            ->createQuery('
                SELECT p 
                FROM UJM\ExoBundle\Entity\Attempt\Paper AS p 
                WHERE p.structure LIKE :booleanType
            ')
            ->setParameters([
                'booleanType' => '%x.boolean+json%',
            ])
            ->getResult();

        $i = 0;
        $total = count($papers);

        $this->log("Number of papers to migrate : $total.");

        /** @var Paper $paper */
        foreach ($papers as $paper) {
            ++$i;
            $this->log("Migrating $i/$total...");

            $structure = json_decode($paper->getStructure(), true);
            if ($structure && !empty($structure['steps'])) {
                foreach ($structure['steps'] as $stepIndex => $step) {
                    if (!empty($step['items'])) {
                        foreach ($step['items'] as $itemIndex => $item) {
                            if ('application/x.boolean+json' === $item['type']) {
                                // replace type
                                $item['type'] = 'application/x.choice+json';

                                // add missing config
                                $item['multiple'] = false;
                                $item['direction'] = Direction::HORIZONTAL;

                                // replace item in structure
                                $structure['steps'][$stepIndex]['items'][$itemIndex] = $item;
                            }
                        }
                    }
                }

                $paper->setStructure(json_encode($structure));
                $om->persist($paper);
            }

            if (0 === $i % 100) {
                $om->flush();
                $this->log('flush');
            }
        }

        $om->flush();
        $this->log('flush');
    }
}
