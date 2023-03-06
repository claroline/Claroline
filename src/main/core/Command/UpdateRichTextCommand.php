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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRichTextCommand extends Command
{
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
           ])
           ->addOption(
               'force',
               'f',
               InputOption::VALUE_NONE,
               'When set to true, no confirmation required'
           );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $toMatch = $input->getArgument('old_string');
        $toReplace = $input->getArgument('new_string');
        $search = '%'.addcslashes($toMatch, '%_').'%';

        $classes = $this->getParsableEntities();
        foreach ($classes as $class => $properties) {
            $output->writeln($class);

            // search for entities containing the string to replace
            $qb = $this->em->getRepository($class)->createQueryBuilder('e');
            foreach ($properties as $property) {
                $qb->andWhere("e.{$property} LIKE :str");
            }

            $entities = $qb->setParameter('str', $search)->getQuery()->getResult();
            $output->writeln('<error>'.count($entities).' entities found.</error>');

            // process found entities or display info about changes in the console for review
            foreach ($entities as $entity) {
                $output->writeln('<comment>'.$entity->getId().'</comment>');

                foreach ($properties as $property) {
                    $getter = 'get'.ucfirst($property);

                    $originalText = $entity->$getter();
                    $updatedText = str_replace($toMatch, $toReplace, $originalText);

                    if ($originalText !== $updatedText) {
                        // we want to exclude false positive : MySQL do a case-insensitive search
                        // and we will do a case-sensitive replace
                        if ($input->getOption('force')) {
                            $func = 'set'.ucfirst($property);
                            $entity->$func($updatedText);

                            $this->em->persist($entity);
                        } else {
                            $output->writeln(str_replace($toMatch, '<question>'.$toMatch.'</question>', $originalText));
                        }
                    }
                }
            }
        }

        if ($input->getOption('force')) {
            $this->em->flush();
        }

        return 0;
    }

    private function getParsableEntities(): array
    {
        return array_filter([
            'Claroline\CoreBundle\Entity\Content' => ['content'],
            'Claroline\CoreBundle\Entity\Resource\Revision' => ['content'],
            'Claroline\CoreBundle\Entity\Widget\Type\SimpleWidget' => ['content'],
            'Claroline\CoreBundle\Entity\Template\TemplateContent' => ['content'],
            'Claroline\CoreBundle\Entity\Planning\PlannedObject' => ['description'],
            // plugins (should not be here)
            'Claroline\AnnouncementBundle\Entity\Announcement' => ['content'],
            'Innova\PathBundle\Entity\Path\Path' => ['overviewMessage', 'endMessage', 'successMessage', 'failureMessage'],
            'Innova\PathBundle\Entity\Step' => ['description'],
            'UJM\ExoBundle\Entity\Exercise' => ['overviewMessage', 'endMessage', 'successMessage', 'failureMessage'],
            'UJM\ExoBundle\Entity\Item\Item' => ['title', 'content'],
            'Claroline\ForumBundle\Entity\Message' => ['content'],
            'Claroline\CursusBundle\Entity\Course' => ['description'],
            'Claroline\CursusBundle\Entity\Session' => ['description'],
            'HeVinci\UrlBundle\Entity\Url' => ['url'],
            'HeVinci\UrlBundle\Entity\Home\UrlTab' => ['url'],
            'Icap\LessonBundle\Entity\Chapter' => ['text', 'internalNote'],
        ], function (string $className) {
            return class_exists($className);
        }, ARRAY_FILTER_USE_KEY);
    }
}
