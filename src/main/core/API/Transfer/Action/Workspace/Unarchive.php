<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Workspace;

use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;

class Unarchive extends AbstractAction
{
    /** @var ObjectManager */
    private $om;
    /** @var WorkspaceManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        WorkspaceManager $manager
    ) {
        $this->om = $om;
        $this->manager = $manager;
    }

    public function execute(array $data, &$successData = [])
    {
        /** @var Workspace $object */
        $object = $this->om->getObject($data[$this->getAction()[0]], $this->getClass(), array_keys($data[$this->getAction()[0]]));

        if (!empty($object)) {
            $this->manager->unarchive($object);

            $successData['unarchive'][] = [
                'data' => $data,
                'log' => $this->getAction()[0].' unarchived.',
            ];
        }
    }

    public function getClass()
    {
        return Workspace::class;
    }

    public function getAction()
    {
        return ['workspace', 'unarchive'];
    }

    public function getSchema(array $options = [], array $extra = [])
    {
        //this is so we don't show all properties. See TransferProvider and search $root
        return [$this->getAction()[0] => $this->getClass()];
    }
}
