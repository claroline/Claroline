<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater110300 extends Updater
{
    const BATCH_SIZE = 500;

    private $container;
    protected $logger;
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateWorkspaceSlugs();
        $this->updateFieldFacetsType();
    }

    private function updateWorkspaceSlugs()
    {
        $this->log('Initializing workspace slug...');
        $offset = 0;
        $i = 0;
        $total = intval($this->om
            ->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace')
            ->countWorkspaces());

        while ($i < $total) {
            $workspaces = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace')
                ->findBy([], [], self::BATCH_SIZE, $offset);
            $offset += self::BATCH_SIZE;
            foreach ($workspaces as $workspace) {
                ++$i;
                $this->log('Update workspace slug for '.$workspace->getCode().' '.$i.'/'.$total);
                $workspace->setSlug(null);
                $this->om->persist($workspace);
            }

            $this->om->flush();
            $this->om->clear();
        }

        $this->log('Workspace slugs initialized!');
    }

    private function updateFieldFacetsType()
    {
        $this->log('Updating type of facet fields...');

        $fields = $this->om->getRepository('Claroline\CoreBundle\Entity\Facet\FieldFacet')->findAll();

        foreach ($fields as $field) {
            switch ($field->getType()) {
                case FieldFacet::RADIO_TYPE:
                    $field->setType(FieldFacet::CHOICE_TYPE);
                    $options = $field->getOptions();
                    $options['multiple'] = false;
                    $options['condensed'] = false;
                    $field->setOptions($options);
                    $this->om->persist($field);
                    break;
                case FieldFacet::SELECT_TYPE:
                    $field->setType(FieldFacet::CHOICE_TYPE);
                    $options = $field->getOptions();
                    $options['multiple'] = false;
                    $options['condensed'] = true;
                    $field->setOptions($options);
                    $this->om->persist($field);
                    break;
                case FieldFacet::CHECKBOXES_TYPE:
                    $field->setType(FieldFacet::CHOICE_TYPE);
                    $options = $field->getOptions();
                    $options['multiple'] = true;
                    $options['condensed'] = false;
                    $field->setOptions($options);
                    $this->om->persist($field);
                    break;
            }
        }
        $this->om->flush();
        $this->om->clear();

        $this->log('Type of facet fields updated.');
    }
}
