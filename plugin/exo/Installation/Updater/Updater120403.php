<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Library\Options\Direction;

class Updater120403 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->convertDuration();
        $this->convertBooleanPapers();
        $this->convertBooleanAnswers();
    }

    private function convertDuration()
    {
        $this->log('Convert quiz duration to seconds...');

        $sql = 'UPDATE ujm_exercise SET duration = (duration * 60) WHERE duration IS NOT NULL AND duration != 0';
        $sth = $this->container->get('doctrine.dbal.default_connection')->prepare($sql);
        $sth->execute();
    }

    private function convertBooleanPapers()
    {
        $this->log('Convert boolean papers...');

        $om = $this->container->get('claroline.persistence.object_manager');

        $papers = $om
            ->createQuery('
                SELECT p
                FROM UJM\ExoBundle\Entity\Attempt\Paper AS p
                WHERE p.structure LIKE "%application/x.boolean+json%"
            ')
            ->getResult();

        $i = 0;
        $total = count($papers);

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

    private function convertBooleanAnswers()
    {
        $this->log('Convert boolean answers...');

        $sql = '
            UPDATE ujm_response AS r
            JOIN ujm_question AS q ON (q.uuid = r.question_id)
            SET r.response = CONCAT("[", r.response, "]")
            WHERE r.response IS NOT NULL AND r.response != ""
              AND q.id IS NOT NULL
              AND q.mime_type = "application/x.choice+json"
              AND LEFT(r.response, 1) != "["
        ';
        $sth = $this->container->get('doctrine.dbal.default_connection')->prepare($sql);
        $sth->execute();
    }
}
