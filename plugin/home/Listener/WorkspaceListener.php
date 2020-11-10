<?php

namespace Claroline\HomeBundle\Listener;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\HomeBundle\Entity\HomeTab;

class WorkspaceListener
{
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;

    public function __construct(ObjectManager $om, Crud $crud)
    {
        $this->om = $om;
        $this->crud = $crud;
    }

    public function preDelete(DeleteEvent $event)
    {
        $workspace = $event->getObject();

        $tabs = $this->om->getRepository(HomeTab::class)->findBy(['workspace' => $workspace]);
        foreach ($tabs as $tab) {
            $this->crud->delete($tab);
        }
    }
}
