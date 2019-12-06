<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Innova\PathBundle\Command\DatabaseIntegrity;

use Claroline\AppBundle\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Step;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PathSlugCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:path_slug:check')
            ->setDescription('Checks all the slug for steps of paths are unique.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Path slug check...');

        /** @var ObjectManager $om */
        $om = $this->getContainer()->get('doctrine.orm.entity_manager');

        $query = $om->createQuery('
            SELECT COUNT(s) AS nb, s.slug
            FROM Innova\PathBundle\Entity\Step AS s
            GROUP BY s.slug
            HAVING nb > 1
        ');

        $nonUniqueSlugs = $query->getResult();
        if (!empty($nonUniqueSlugs)) {
            $output->writeln('Restoring '.count($nonUniqueSlugs).' slugs...');

            foreach ($nonUniqueSlugs as $result) {
                /** @var Step[] $steps */
                $steps = $om->getRepository(Step::class)->findBy(['slug' => $result['slug']]);
                if (!empty($steps)) {
                    foreach ($steps as $index => $step) {
                        $step->setSlug($step->getSlug().$index);
                        $om->persist($step);
                    }

                    $om->flush();
                }
            }
        }
    }
}
