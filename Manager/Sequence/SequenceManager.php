<?php

namespace UJM\ExoBundle\Manager\Sequence;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;
use UJM\ExoBundle\Entity\Sequence\Sequence;
use UJM\ExoBundle\Entity\Sequence\Step;

/**
 * Description of SequenceManager.
 */
class SequenceManager
{
    protected $em;
    protected $translator;

    public function __construct(EntityManager $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function getRepository()
    {
        return $this->em->getRepository('UJMExoBundle:Sequence\Sequence');
    }

    public function createFirstAndLastStep(Sequence $s)
    {

        // add first page
        $first = new Step();
        $first->setIsFirst(true);
        $first->setIsContentStep(true);
        $first->setPosition(1);
        $first->setDescription('<h1>This is the first Step</h1>');
        $first->setSequence($s);
        $s->addStep($first);

        // add last page
        $last = new Step();
        $last->setIsLast(true);
        $last->setIsContentStep(true);
        $last->setPosition(2);
        $last->setDescription('<h1>This is the last Step</h1>');
        $last->setSequence($s);
        $s->addStep($last);

        $this->em->persist($s);
        $this->em->flush();

        return $s;
    }

    public function update(Sequence $s)
    {
        $this->em->persist($s);
        $this->em->flush();

        return $s;
    }
}
