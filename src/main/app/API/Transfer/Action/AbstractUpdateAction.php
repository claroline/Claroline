<?php

namespace Claroline\AppBundle\API\Transfer\Action;

use Claroline\AppBundle\API\Crud;

abstract class AbstractUpdateAction extends AbstractAction
{
    /** @var Crud */
    protected $crud;

    abstract public function getClass();

    public function setCrud(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function execute(array $data, &$successData = [])
    {
        $this->crud->update($this->getClass(), $data);

        $successData['update'][] = [
            'data' => $data,
            'log' => $this->getAction()[0].' updated.',
        ];
    }

    public function getSchema(array $options = [], array $extra = [])
    {
        return ['$root' => $this->getClass()];
    }

    public function getMode()
    {
        return self::MODE_UPDATE;
    }
}
