<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use UJM\ExoBundle\Entity\ItemType\BooleanQuestion;
use UJM\ExoBundle\Entity\ItemType\ChoiceQuestion;
use UJM\ExoBundle\Entity\Misc\Choice;
use UJM\ExoBundle\Library\Options\Direction;

class Updater120308 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->log('Migrating boolean questions');
        $om = $this->container->get('Claroline\AppBundle\Persistence\ObjectManager');
        $boolQs = $om->getRepository(BooleanQuestion::class)->findAll();
        $i = 0;
        $total = count($boolQs);

        /** @var BooleanQuestion $boolQ */
        foreach ($boolQs as $boolQ) {
            ++$i;
            $this->log("Migrating $i/$total...");
            $choiceQ = new ChoiceQuestion();
            $choiceQ->setQuestion($boolQ->getQuestion());
            $choiceQ->setMultiple(false);
            $choiceQ->setDirection(Direction::HORIZONTAL);
            $choices = $boolQ->getChoices()->toArray();

            foreach ($choices as $choice) {
                $newChoice = new Choice();
                $newChoice->setScore($choice->getScore());
                $newChoice->setFeedback($choice->getFeedback());
                $newChoice->setData($choice->getData());
                $newChoice->setResourceNode($choice->getResourceNode());
                $newChoice->setInteractionQCM($choiceQ);
                $om->persist($newChoice);
            }

            $item = $boolQ->getQuestion();
            $item->setInteraction($choiceQ);
            $item->setMimeType('application/x.choice+json');
            $om->persist($choiceQ);
            $om->persist($item);

            $om->remove($boolQ);

            if (0 === $i % 100) {
                $om->flush();
                $this->log('flush');
            }
        }

        $om->flush();
        $this->log('flush');
    }
}
