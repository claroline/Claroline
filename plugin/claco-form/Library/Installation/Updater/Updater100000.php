<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater100000 extends Updater
{
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateClacoFormConfigs();
        $this->updateCategories();
    }

    private function updateClacoFormConfigs()
    {
        $this->log('Updating configuration of ClacoForm resources...');
        $clacoFormRepo = $this->om->getRepository('Claroline\ClacoFormBundle\Entity\ClacoForm');
        $clacoForms = $clacoFormRepo->findAll();
        $this->om->startFlushSuite();

        foreach ($clacoForms as $clacoForm) {
            if ($clacoForm->getLockedFieldsFor() === 'user') {
                $clacoForm->setLockedFieldsFor('user');
                $this->om->persist($clacoForm);
            }
        }
        $this->om->endFlushSuite();
    }

    private function updateCategories()
    {
        $this->log('Updating category option for notification of pending comments...');
        $categoryRepo = $this->om->getRepository('Claroline\ClacoFormBundle\Entity\Category');
        $categories = $categoryRepo->findAll();
        $this->om->startFlushSuite();

        foreach ($categories as $category) {
            if ($category->getNotifyPendingComment()) {
                $category->setNotifyPendingComment(true);
                $this->om->persist($category);
            }
        }
        $this->om->endFlushSuite();
    }
}
