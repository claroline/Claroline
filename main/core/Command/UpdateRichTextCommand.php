<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class UpdateRichTextCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:rich_texts:update')
            ->setDescription('Update a text string ')
            ->setDefinition([
               new InputArgument('old_string', InputArgument::REQUIRED, 'old str'),
               new InputArgument('new_string', InputArgument::REQUIRED, 'new str'),
               new InputArgument('classes', InputArgument::REQUIRED, 'classes'),
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
        $params = [
            'old_string' => 'The string to match',
            'new_string' => 'The string to replace',
        ];

        foreach ($params as $argument => $argumentName) {
            if (!$input->getArgument($argument)) {
                $input->setArgument(
                    $argument, $this->askArgument($output, $argumentName)
                );
            }
        }

        $helper = $this->getHelper('question');
        $entities = array_keys($this->getParsableEntities());
        $question = new ChoiceQuestion('Entity to parse: ', $entities);
        $question->setMultiselect(true);

        while (null === $entity = $input->getArgument('classes')) {
            $entity = $helper->ask($input, $output, $question);
            $input->setArgument('classes', $entity);
        }
    }

    protected function askArgument(OutputInterface $output, $argumentName)
    {
        $argument = $this->getHelper('dialog')->askAndValidate(
            $output,
            "Enter the {$argumentName}: ",
            function ($argument) {
                if (empty($argument)) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parsable = $this->getParsableEntities();
        $toMatch = $input->getArgument('old_string');
        $toReplace = $input->getArgument('new_string');
        $classes = $input->getArgument('classes');
        $entities = [];
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $escaped = addcslashes($toMatch, '%_');

        foreach ($classes as $class) {
            $data = $em->getRepository($class)->createQueryBuilder('e')
                ->where("e.{$parsable[$class]} LIKE :str")
                ->setParameter('str', "%{$escaped}%")
                ->getQuery()
                ->getResult();

            if ($data) {
                $entities = array_merge($entities, $data);
            }
        }

        if (!$entities) {
            $output->writeln('<error>No entities found...</error>');

            return;
        }

        $output->writeln('<error>'.count($entities).' entities found.</error>');

        $i = 0;

        foreach ($entities as $entity) {
            $continue = false;
            $func = 'get'.ucfirst($parsable[get_class($entity)]);
            $text = $entity->$func();

            if ($input->getOption('force')) {
                $continue = true;
            } else {
                $infos = 'Class: '.get_class($entity)."\n";
                $infos .= 'Id: '.$entity->getId()."\n";
                $infos .= $text;
                $output->writeln('<comment>'.$infos.'</comment>');

                $helper = $this->getHelper('question');
                $question = new ChoiceQuestion('Edit ?', ['yes', 'no']);
                $answer = $helper->ask($input, $output, $question);
                if ($answer === 'yes') {
                    $continue = true;
                }
            }

            if ($continue) {
                $text = str_replace($toMatch, $toReplace, $text);
                $func = 'set'.ucfirst($parsable[get_class($entity)]);
                $entity->$func($text);
                $em->persist($entity);
                ++$i;
            }
        }

        $output->writeln("<comment>{$i} element changed... flushing</comment>");
        $em->flush();
        $output->writeln('<comment>Done</comment>');
    }

    private function getParsableEntities()
    {
        return [
            'Claroline\CoreBundle\Entity\Resource\Revision' => 'content',
            'Claroline\AgendaBundle\Entity\Event' => 'description',
            'Claroline\CoreBundle\Entity\Resource\Activity' => 'description',
            'Innova\PathBundle\Entity\Path\Path' => 'description',
            'Claroline\CoreBundle\Entity\Widget\SimpleTextConfig' => 'content',
        ];
    }
}
