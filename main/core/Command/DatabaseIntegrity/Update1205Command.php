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

use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Update1205Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:routes:12.5')
            ->setDescription('Update 12.5 routes')
            ->setDefinition([
                new InputArgument('base_path', InputArgument::OPTIONAL, 'The value'),
            ])
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'If not force, command goes dry run')
            ->addOption('show-text', 's', InputOption::VALUE_NONE, 'Show the replaced texts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);
        $this->setLogger($consoleLogger);

        $this->log('Updating routes in database rich text');
        $prefix = $input->getArgument('base_path');
        //the list is probably incomplete, but it is a start

        $parsableEntities = [
            'Claroline\CoreBundle\Entity\Content' => ['content'],
            'Claroline\CoreBundle\Entity\Resource\Revision' => ['content'],
            'Claroline\AgendaBundle\Entity\Event' => ['description'],
            'Claroline\AnnouncementBundle\Entity\Announcement' => ['content'],
            'Innova\PathBundle\Entity\Path\Path' => ['description'],
            'Innova\PathBundle\Entity\Step' => ['description'],
            'Claroline\CoreBundle\Entity\Widget\Type\SimpleWidget' => ['content'],
            'UJM\ExoBundle\Entity\Exercise' => ['endMessage'],
            'UJM\ExoBundle\Entity\Item\Item' => ['content'],
            'Claroline\ForumBundle\Entity\Message' => ['content'],
        ];

        $endOfUrl = '[^"^#^&^<]';

        //this is the list of regexes we'll need to use
        $regexes = [
          //open can be id
          '\/workspaces\/([\d]+)\/open"' => [
              '#/desktop/workspaces/open/:wslug',
              ['Claroline\CoreBundle\Entity\Workspace\Workspace'],
          ],
          //open can be id
          '\/workspaces\/([\d]+)\/open\/tool\('.$endOfUrl.'*)' => [
              '#/desktop/workspaces/open/:wslug',
              ['Claroline\CoreBundle\Entity\Workspace\Workspace'],
          ],
          //open can be uuid or id
          '\/resource\/open\/([^\/]*)"' => [
            '#/desktop/workspaces/open/:wslug/resources/:nslug',
            ['Claroline\CoreBundle\Entity\Resource\ResourceNode'],
          ],
          //open can be uuid or id (resource type then id)
          '\/resource\/open\/([^\/]+)\/('.$endOfUrl.'*)' => [
            '#/desktop/workspaces/open/:wslug/resources/:nslug',
            [null, 'Claroline\CoreBundle\Entity\Resource\ResourceNode'],
          ],
          //show is type then id or uuid
          '\/resources\/show\/([^\/]*)"' => [
            '#/desktop/workspaces/open/:wslug/resources/:nslug',
            [
              null,
              'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            ],
          ],
          //show is type then id or uuid
          '\/resources\/show\/([^\/]*)\/('.$endOfUrl.'*)' => [
            '#/desktop/workspaces/open/:wslug/resources/:nslug',
            [
              null,
              'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            ],
          ],
        ];

        foreach ($parsableEntities as $class => $properties) {
            $this->log('Replacing old urls for '.$class.'...');
            foreach ($properties as $property) {
                $this->log('Looking for property '.$property.'...');
                $em = $this->getContainer()->get('doctrine.orm.entity_manager');
                $metadata = $em->getClassMetadata($class);

                $tableName = $metadata->getTableName();
                $columnName = $metadata->getColumnName($property);

                foreach ($regexes as $regex => $replacement) {
                    $this->log('Matching regex '.$regex.'...');
                    $sql = 'SELECT * from '.$tableName.' WHERE '.$columnName." RLIKE '{$regex}'";
                    $this->log($sql);
                    $rsm = new ResultSetMappingBuilder($em);
                    $rsm->addRootEntityFromClassMetadata($class, '');
                    $query = $em->createNativeQuery($sql, $rsm);
                    $data = $query->getResult();
                    $this->log(count($data).' results...');
                    $i = 0;

                    foreach ($data as $entity) {
                        $this->log('Updating '.$i.'/'.count($data));
                        $func = 'get'.ucfirst($property);
                        $text = $entity->$func();
                        $text = $this->replace($regex, $replacement, $text, $prefix, $input->getOption('show-text'));
                        $func = 'set'.ucfirst($property);

                        if ($input->getOption('force')) {
                            $entity->$func($text);
                            $em->persist($entity);
                        }

                        ++$i;
                    }

                    if ($input->getOption('force')) {
                        $this->log('Flushing...');
                        $em->flush();
                    }
                }
            }
        }
    }

    public function replace($regex, $replacement, $text, $prefix, $show = false)
    {
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $matches = [];
        preg_match('!'.$regex.'!', $text, $matches, PREG_OFFSET_CAPTURE);
        array_shift($matches);

        $regexError = true;

        //if (count($matches)) {
        foreach ($replacement[1] as $pos => $class) {
            if ($class) {
                if (isset($matches[$pos][0])) {
                    $this->log('Finding resource of class '.$class.' with identifier '.$matches[$pos][0]);
                    $object = $om->getRepository($class)->find($matches[$pos][0]);

                    if ($object) {
                        $regexError = false;
                        if (Workspace::class === $class) {
                            $replacement[0] = str_replace(':wslug', $object->getSlug(), $replacement[0]);
                        }

                        if (ResourceNode::class === $class) {
                            if ($object->getWorkspace()) {
                                $replacement[0] = str_replace(':nslug', $object->getSlug(), $replacement[0]);
                                $replacement[0] = str_replace(':wslug', $object->getWorkspace()->getSlug(), $replacement[0]);
                            } else {
                                $this->error('Resource '.$matches[$pos][0].' has no workspace');
                            }
                        }
                    } else {
                        $this->error('Could not find object... skipping');
                    }
                }
            }
        }

        $regex = $prefix.$regex;

        if ($regexError) {
            $this->error('Could not find some objects for replacing ids by slugs');
        } else {
            $this->log('Replacing '.$replacement[0].' using regex '.$regex);
            if ($show) {
                $this->log('Old text: '.$text);
            }
            $text = preg_replace('!'.$regex.'!', $replacement[0], $text);
            if ($show) {
                $this->log('New text: '.$text);
            }
        }

        return $text;
    }

    private function setLogger($logger)
    {
        $this->consoleLogger = $logger;
    }

    private function log($log)
    {
        $this->consoleLogger->info($log);
    }

    private function error($error)
    {
        $this->consoleLogger->error($error);
    }
}
