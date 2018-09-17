<?php

namespace Claroline\AppBundle\API\Transfer\Action;

use Claroline\AppBundle\API\Crud;
use JMS\DiExtraBundle\Annotation as DI;

abstract class AbstractCreateAction extends AbstractAction
{
    abstract public function getClass();

    /**
     * Action constructor.
     *
     * @DI\InjectParams({
     *     "crud" = @DI\Inject("claroline.api.crud")
     * })
     *
     * @param Crud $crud
     */
    public function __construct(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function execute(array $data, &$successData = [])
    {
        $this->crud->create($this->getClass(), $data);
        $successData['create'][] = [
          'data' => $data,
          'log' => $this->getAction()[0].' created.',
        ];
    }

    public function getSchema()
    {
        return ['$root' => $this->getClass()];
    }

    public function getMode()
    {
        return self::MODE_CREATE;
    }
}
