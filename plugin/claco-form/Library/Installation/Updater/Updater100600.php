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

class Updater100600 extends Updater
{
    private $manager;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->manager = $container->get('claroline.manager.claco_form_manager');
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateTemplates();
    }

    private function updateTemplates()
    {
        $this->log('Updating template of ClacoForm resources...');
        $clacoFormRepo = $this->om->getRepository('Claroline\ClacoFormBundle\Entity\ClacoForm');
        $clacoForms = $clacoFormRepo->findAll();
        $this->om->startFlushSuite();
        $index = 0;

        foreach ($clacoForms as $clacoForm) {
            $template = $clacoForm->getTemplate();

            if ($template) {
                $fields = $clacoForm->getFields();

                foreach ($fields as $field) {
                    $id = $field->getId();
                    $name = $this->manager->removeAccent($this->manager->removeQuote($field->getName()));
                    $oldKey = "%$name%";
                    $newKey = "%field_$id%";
                    $template = str_replace($oldKey, $newKey, $template);
                }
                $clacoForm->setTemplate($template);
                $clacoForm->setUseTemplate(true);
            } else {
                $clacoForm->setUseTemplate(false);
            }
            ++$index;
            $this->om->persist($clacoForm);

            if ($index % 100 === 0) {
                $this->om->forceFlush();
            }
        }
        $this->om->endFlushSuite();
    }
}
