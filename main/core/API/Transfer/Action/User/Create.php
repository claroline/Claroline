<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Transfer\Action\AbstractCreateAction;
use Claroline\CoreBundle\Entity\User;
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
        $hasWs = false;
        $options = [Options::FORCE_RANDOM_PUBLIC_URL];

        if (isset($data['meta']) && isset($data['meta']['personalWorkspace'])) {
            $hasWs = $data['meta']['personalWorkspace'];
        }

        if (!$hasWs) {
            $options[] = Options::NO_PERSONAL_WORKSPACE;
        }

        $this->crud->create($this->getClass(), $data, $options);

        $successData['create'][] = [
          'data' => $data,
          'log' => $this->getAction()[0].' created.',
        ];
    }

    public function getClass()
    {
        return User::class;
    }
}
