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

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class UpdateRichTextCommand extends Command
{
    private $params = [
        'old_string' => 'The string to match',
        'new_string' => 'The string to replace',
    ];
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
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
           )
           ->addOption(
               'all',
               'a',
               InputOption::VALUE_NONE,
               'When set to true, all entities'
           )
           ->addOption(
               'regex',
               'r',
               InputOption::VALUE_NONE,
               'When set to true, use regex'
           );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->params as $parameter => $question) {
            if (!$input->getArgument($parameter)) {
                $input->setArgument(
                    $parameter,
                    $this->getHelper('question')->ask($input, $output, new Question($question.': '))
                );
            }
        }

        $helper = $this->getHelper('question');
        $entities = array_keys($this->getParsableEntities());
        $question = new ChoiceQuestion('Entity to parse: (use \',\' as a separator) ', $entities);
        $question->setMultiselect(true);
        while (null === $entity = $input->getArgument('classes')) {
            $entity = $helper->ask($input, $output, $question);
            $input->setArgument('classes', $entity);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parsable = $this->getParsableEntities();
        $toMatch = $input->getArgument('old_string');
        $toReplace = $input->getArgument('new_string');
        $classes = $input->getArgument('classes');
        $entities = [];
        $search = '%'.addcslashes($toMatch, '%_').'%';

        foreach ($classes as $class) {
            foreach ($parsable[$class] as $property) {
                if ($input->getOption('regex')) {
                    $metadata = $this->em->getClassMetadata($class);

                    $tableName = $metadata->getTableName();
                    $columnName = $metadata->getColumnName($property);
                    $sql = 'SELECT * from '.$tableName.' WHERE '.$columnName." RLIKE '{$toMatch}'";
                    $output->writeln($sql);
                    $rsm = new ResultSetMappingBuilder($this->em);
                    $rsm->addRootEntityFromClassMetadata($class, '');
                    $query = $this->em->createNativeQuery($sql, $rsm);
                    $data = $query->getResult();
                } else {
                    $data = $this->em->getRepository($class)->createQueryBuilder('e')
                      ->where("e.{$property} LIKE :str")
                      ->setParameter('str', $search)
                      ->getQuery()
                      ->getResult();
                }

                if ($data) {
                    $entities = array_merge($entities, $data);
                }
            }
        }

        if (!$entities) {
            $output->writeln('<error>No entities found...</error>');

            return 1;
        }

        $output->writeln('<error>'.count($entities).' entities found.</error>');

        $i = 0;

        foreach ($entities as $entity) {
            $continue = false;

            foreach ($parsable[ClassUtils::getRealClass(get_class($entity))] as $property) {
                $func = 'get'.ucfirst($property);
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

                    if ('yes' === $answer) {
                        $continue = true;
                    }
                }

                if ($continue) {
                    if ($input->getOption('regex')) {
                        $escapedMatcher = str_replace('#', '\#', $toMatch);
                        $text = preg_replace('#'.$escapedMatcher.'#', $toReplace, $text);
                    } else {
                        $text = str_replace($toMatch, $toReplace, $text);
                    }

                    $func = 'set'.ucfirst($property);
                    $entity->$func($text);
                    $this->em->persist($entity);
                    ++$i;
                }
            }
        }

        $output->writeln("<comment>{$i} element changed... flushing</comment>");
        $this->em->flush();
        $output->writeln('<comment>Done</comment>');

        return 0;
    }

    private function getParsableEntities()
    {
        return [
            'Claroline\CoreBundle\Entity\Content' => ['content'],
            'Claroline\CoreBundle\Entity\Resource\Revision' => ['content'],
            'Claroline\CoreBundle\Entity\Widget\Type\SimpleWidget' => ['content'],
            // plugins (should not be here)
            'Claroline\AgendaBundle\Entity\Event' => ['description'],
            'Claroline\AgendaBundle\Entity\Task' => ['description'],
            'Claroline\AnnouncementBundle\Entity\Announcement' => ['content'],
            'Innova\PathBundle\Entity\Path\Path' => ['description'],
            'Innova\PathBundle\Entity\Step' => ['description'],
            'UJM\ExoBundle\Entity\Exercise' => ['endMessage'],
            'UJM\ExoBundle\Entity\Item\Item' => ['content'],
            'Claroline\ForumBundle\Entity\Message' => ['content'],
            'Claroline\CursusBundle\Entity\Course' => ['description'],
            'Claroline\CursusBundle\Entity\Session' => ['description'],
            'Claroline\CursusBundle\Entity\Event' => ['description'],
            'HeVinci\UrlBundle\Entity\Url' => ['url'],
            'HeVinci\UrlBundle\Entity\Home\UrlTab' => ['url'],
        ];
    }
}
