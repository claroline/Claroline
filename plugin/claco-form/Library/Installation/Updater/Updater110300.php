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

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater110300 extends Updater
{
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateFieldsType();
    }

    private function updateFieldsType()
    {
        $this->log('Updating type of fields...');
        $fields = $this->om->getRepository('Claroline\ClacoFormBundle\Entity\Field')->findAll();
        $this->om->startFlushSuite();
        $index = 0;

        foreach ($fields as $field) {
            $type = $field->getType();

            switch ($type) {
                case FieldFacet::RADIO_TYPE:
                    $field->setType(FieldFacet::CHOICE_TYPE);
                    $options = $field->getDetails();
                    $options['multiple'] = false;
                    $options['condensed'] = false;
                    $field->setDetails($options);
                    $this->om->persist($field);
                    ++$index;
                    break;
                case FieldFacet::SELECT_TYPE:
                    $field->setType(FieldFacet::CHOICE_TYPE);
                    $options = $field->getDetails();
                    $options['multiple'] = false;
                    $options['condensed'] = true;
                    $field->setDetails($options);
                    $this->om->persist($field);
                    ++$index;
                    break;
                case FieldFacet::CHECKBOXES_TYPE:
                    $field->setType(FieldFacet::CHOICE_TYPE);
                    $options = $field->getDetails();
                    $options['multiple'] = true;
                    $options['condensed'] = false;
                    $field->setDetails($options);
                    $this->om->persist($field);
                    ++$index;
                    break;
            }
            if (in_array($type, [FieldFacet::RADIO_TYPE, FieldFacet::SELECT_TYPE, FieldFacet::CHECKBOXES_TYPE]) && 0 === $index % 100) {
                $this->om->forceFlush();
            }
        }
        $this->om->endFlushSuite();
        $this->log('Type of fields updated.');
    }
}
