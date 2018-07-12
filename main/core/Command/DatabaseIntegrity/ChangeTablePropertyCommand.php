<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ChangeTablePropertyCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    protected function configure()
    {
        $this->setName('claroline:entity:replace')
            ->setDescription('Update entity property')
            ->setDefinition([
                new InputArgument('value', InputArgument::REQUIRED, 'The value'),
                new InputArgument('class', InputArgument::REQUIRED, 'The class'),
                new InputArgument('property', InputArgument::REQUIRED, 'The property'),
           ])
           ->addOption(
               'force',
               'f',
               InputOption::VALUE_NONE,
               'When set to true, no confirmation required'
           );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $metas = $em->getMetadataFactory()->getAllMetadata();

        foreach ($metas as $meta) {
            $classes[] = $meta->getName();
        }

        sort($classes, SORT_NATURAL | SORT_FLAG_CASE);

        $question = new ChoiceQuestion('Entity to update: ', $classes);
        $question->setMultiselect(false);

        while (null === $entity = $input->getArgument('class')) {
            $entity = $helper->ask($input, $output, $question);
            $input->setArgument('class', $entity);
        }

        $class = $input->getArgument('class');
        $properties = array_keys($em->getClassMetadata($class)->getReflectionProperties());
        $question = new ChoiceQuestion('Entity to update: ', $properties);
        $question->setMultiselect(false);

        while (null === $entity = $input->getArgument('property')) {
            $property = $helper->ask($input, $output, $question);
            $input->setArgument('property', $property);
        }

        $property = $input->getArgument('property');

        $input->setArgument(
          'value',
          $this->getHelper('question')->ask($input, $output, new Question('New value: '))
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();
        $qb->update($input->getArgument('class'), 'obj')
          ->set('obj.'.$input->getArgument('property'), '?1')
          ->setParameter(1, $input->getArgument('value'))
          ->getQuery()
          ->execute();
    }
}
