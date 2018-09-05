<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetContainerConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migration12HomeTabCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:migration12:home-tab')
            ->setDescription('This command allow you to rebuild trim the tabs html datas and stuff');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //limite de 20 sur le short titre des tabs
        //titre widget & titre des tabs
        $toFilter = [
          HomeTabConfig::class => ['name', 20],
          WidgetContainerConfig::class => ['name', null],
        ];

        $om = $this->getContainer()->get('claroline.persistence.object_manager');

        foreach ($toFilter as $class => $data) {
            $entities = $om->getRepository($class)->findAll();
            $output->writeln($class);
            $i = 0;
            $total = count($entities);

            foreach ($entities as $entity) {
                $output->writeln("{$class}: {$i}/{$total}");

                ++$i;
                $setter = 'set'.ucfirst($data[0]);
                $getter = 'get'.ucfirst($data[0]);
                $entity->{$setter}($this->stripTrim($entity->$getter(), $data[1]));
                $om->persist($entity);

                if (0 === $i % 200) {
                    $om->flush();
                }
            }
        }

        $om->flush();
    }

    protected function stripTrim($text, $maxChar = null)
    {
        $text = html_entity_decode(strip_tags($text));

        if ($maxChar) {
            $text = substr($text, 0, $maxChar);
        }

        return $text;
    }
}
