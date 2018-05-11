<?php

namespace Claroline\AppBundle\API\Transfer\Action;

use Claroline\AppBundle\API\Crud;
use JMS\DiExtraBundle\Annotation as DI;

abstract class AbstractUpdateAction extends AbstractAction
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
        $user = $this->crud->update($this->getClass(), $data);
        $successData['update'][] = [
          'data' => $data,
          'log' => 'The user '.$user->getUsername().' was successfully updated.',
        ];
    }

    public function getSchema()
    {
        return ['$root' => $this->getClass()];
    }

    public function getMode()
    {
        return self::MODE_UPDATE;
    }
}
