<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Transfer\Action\AbstractDeleteAction;
use Claroline\CoreBundle\Entity\User;

class Delete extends AbstractDeleteAction
{
    public function getAction()
    {
        return ['user', self::MODE_DELETE];
    }

    public function getClass()
    {
        return User::class;
    }

    public function execute(array $data, &$successData = [])
    {
        $object = $this->om->getObject(
            $data[$this->getAction()[0]],
            $this->getClass(),
            array_keys($data[$this->getAction()[0]])
        );

        if (!empty($object)) {
            $this->crud->delete($object, [Options::SOFT_DELETE]);

            $successData['delete'][] = [
                'data' => $data,
                'log' => $this->getAction()[0].' deleted.',
            ];
        }
    }
}
