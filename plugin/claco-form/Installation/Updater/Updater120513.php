<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120513 extends Updater
{
    /** @var ObjectManager */
    private $om;
    /** @var ClacoFormManager */
    private $manager;

    public function __construct(ContainerInterface $container)
    {
        $this->om = $container->get('Claroline\AppBundle\Persistence\ObjectManager');
        $this->manager = $container->get('Claroline\ClacoFormBundle\Manager\ClacoFormManager');
    }

    public function postUpdate()
    {
        $this->refactorTemplates();
    }

    private function refactorTemplates()
    {
        $this->log('Refactoring ClacoForm templates...');

        $clacoForms = $this->om->getRepository(ClacoForm::class)->findAll();

        $this->om->startFlushSuite();
        $i = 0;

        foreach ($clacoForms as $clacoForm) {
            $this->manager->refactorTemplateWithUuid($clacoForm);
            ++$i;

            if (0 === $i % 100) {
                $this->om->forceFlush();
            }
        }
        $this->om->endFlushSuite();

        $this->log('ClacoForm templates refactored.');
    }
}
