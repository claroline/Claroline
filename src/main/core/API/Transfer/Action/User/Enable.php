<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;

class Enable extends AbstractAction
{
    /** @var ObjectManager */
    private $om;
    /** @var UserManager */
    private $userManager;

    /**
     * Enable constructor.
     *
     * @param ObjectManager $om
     * @param UserManager   $userManager
     */
    public function __construct(ObjectManager $om, UserManager $userManager)
    {
        $this->om = $om;
        $this->userManager = $userManager;
    }

    public function execute(array $data, &$successData = [])
    {
        /** @var User $object */
        $object = $this->om->getObject($data[$this->getAction()[0]], $this->getClass(), array_keys($data[$this->getAction()[0]]));

        if (!empty($object)) {
            $this->userManager->enable($object);

            $successData['enable'][] = [
                'data' => $data,
                'log' => $this->getAction()[0].' enabled.',
            ];
        }
    }

    public function getClass()
    {
        return User::class;
    }

    public function getAction()
    {
        return ['user', 'enable'];
    }

    public function getSchema(array $options = [], array $extra = [])
    {
        //this is so we don't show all properties. See TransferProvider and search $root
        return [$this->getAction()[0] => $this->getClass()];
    }
}
