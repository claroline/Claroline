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

use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120410 extends Updater
{
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->om = $container->get('Claroline\AppBundle\Persistence\ObjectManager');
    }

    public function postUpdate()
    {
        $this->restoreEntries();
    }

    private function restoreEntries()
    {
        $this->log('Loading entries...');
        $forms = $this->om->getRepository(ClacoForm::class)->findAll();
        $this->log('Updating '.count($forms).' entries...');
        $i = 0;

        foreach ($forms as $form) {
            ++$i;
            $this->log('Updating '.$i.'/'.count($forms).' entry...');
            $available = $form->getAvailableColumns();
            $form->setAvailableColumns($this->restoreColumnsName($available));
            $displayed = $form->getDisplayedColumns();
            $form->setDisplayedColumns($this->restoreColumnsName($displayed));
            $this->om->persist($form);

            if (0 === $i % 500) {
                $this->log('Flush...');
                $this->om->flush();
            }
        }

        $this->log('Flush...');
        $this->om->flush();
    }

    private function restoreColumnsName(array $columns)
    {
        foreach ($columns as &$column) {
            if (36 === strlen($column)) {
                $column = 'values.'.$column;
            }
        }

        return $columns;
    }
}
