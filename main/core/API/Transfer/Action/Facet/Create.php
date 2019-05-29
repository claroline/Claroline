<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Facet;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.transfer.action")
 */
class Create extends AbstractAction
{
    /** @var Crud */
    private $crud;

    /**
     * Action constructor.
     *
     * @DI\InjectParams({
     *     "crud"       = @DI\Inject("claroline.api.crud")
     * })
     *
     * @param Crud $crud
     */
    public function __construct(Crud $crud)
    {
        $this->crud = $crud;
    }

    /**
     * @param array $data
     */
    public function execute(array $data, &$successData = [])
    {
        return $this->crud->create('Claroline\CoreBundle\Entity\Facet\Facet', $data);
    }

    /**
     * @return array
     */
    public function getSchema(array $options = [], array $extra = [])
    {
        return [
          '$root' => 'Claroline\CoreBundle\Entity\Facet\Facet',
        ];
    }

    /**
     * @return array
     */
    public function getAction()
    {
        return ['facet', 'create'];
    }

    public function getBatchSize()
    {
        return 100;
    }

    public function getMode()
    {
        return self::MODE_CREATE;
    }

    public function clear(ObjectManager $om)
    {
    }
}
