<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Command;

use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefactorTemplatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:clacoform:refactor_templates')
            ->setDescription('Refactors id by uuid in all fields placeholders in ClacoForm templates');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Refactoring ClacoForm templates...');
        $om = $this->getContainer()->get('Claroline\AppBundle\Persistence\ObjectManager');
        $manager = $this->getContainer()->get('Claroline\ClacoFormBundle\Manager\ClacoFormManager');
        $clacoForms = $om->getRepository(ClacoForm::class)->findAll();

        $om->startFlushSuite();
        $i = 0;

        foreach ($clacoForms as $clacoForm) {
            $manager->refactorTemplateWithUuid($clacoForm);
            ++$i;

            if (0 === $i % 100) {
                $om->forceFlush();
            }
        }
        $om->endFlushSuite();

        $output->writeln('ClacoForm templates refactored.');
    }
}
