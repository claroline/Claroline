<?php

namespace Claroline\AppBundle\API\Transfer\Action;

use Claroline\AppBundle\API\Crud;

abstract class AbstractCreateAction extends AbstractAction
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
        $object = $this->crud->create($this->getClass(), $data);
        $successData['create'][] = [
          'data' => $data,
          'log' => $this->getAction()[0].' created.',
        ];

        return $object;
    }

    public function getSchema(array $options = [], array $extra = [])
    {
        return ['$root' => $this->getClass()];
    }

    public function getMode()
    {
        return self::MODE_CREATE;
    }
}
