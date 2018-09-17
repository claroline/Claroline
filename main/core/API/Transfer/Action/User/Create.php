<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Transfer\Action\AbstractCreateAction;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.transfer.action")
 */
class Create extends AbstractCreateAction
{
    public function getAction()
    {
        return ['user', self::MODE_CREATE];
    }

    public function execute(array $data, &$successData = [])
    {
        $this->crud->create($this->getClass(), $data, [
          Options::NO_PERSONAL_WORKSPACE,
          Options::FORCE_RANDOM_PUBLIC_URL, ]
        );

        $successData['create'][] = [
          'data' => $data,
          'log' => $this->getAction()[0].' created.',
        ];
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\User';
    }
}
