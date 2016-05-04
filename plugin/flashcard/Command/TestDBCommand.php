<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Do some test on the db.
 */
class TestDBCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:flashcard:testdb')
            ->setDescription('Test DB');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Get ObjectManager…');
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $output->writeln('Done');

        $output->writeln('Get Repository…');
        $repoNoteType = $om->getRepository('ClarolineFlashCardBundle:NoteType');
        $output->writeln('Done');

        $output->writeln('Get Object…');
        $noteType = $repoNoteType->find(1);
        $output->writeln('Done');

        $output->writeln('id : '.$noteType->getId());
        $output->writeln('name : '.$noteType->getName());
        foreach ($noteType->getFieldLabels()->toArray() as $f) {
            $output->writeln('  fieldlabel id : '.$f->getId());
            $output->writeln('  fieldlabel name : '.$f->getName());
        }
        foreach ($noteType->getCardTypes()->toArray() as $c) {
            $output->writeln('  cardtype id : '.$c->getId());
            $output->writeln('  cardtype question : ');
            foreach ($c->getQuestions()->toArray() as $f) {
                $output->writeln('    question id : '.$f->getId());
                $output->writeln('    question name : '.$f->getName());
            }
            $output->writeln('  cardtype answer : ');
            foreach ($c->getAnswers()->toArray() as $f) {
                $output->writeln('    answer id : '.$f->getId());
                $output->writeln('    answer name : '.$f->getName());
            }
        }
    }
}
