<?php

namespace Claroline\CoreBundle\API\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\AppBundle\Persistence\ObjectManager;

class GroupValidator implements ValidatorInterface
{
    /**
     * GroupValidator constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $this->om->getRepository('Claroline\CoreBundle\Entity\Group');
    }

    public function validate($data, $mode, array $options = [])
    {
        return [];
    }

    public function getUniqueFields()
    {
        return [
          'name' => 'name',
        ];
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Group';
    }
}
