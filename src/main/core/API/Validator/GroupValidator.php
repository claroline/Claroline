<?php

namespace Claroline\CoreBundle\API\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;

class GroupValidator implements ValidatorInterface
{
    /** @var ObjectManager */
    private $om;

    /**
     * GroupValidator constructor.
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
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

    public static function getClass(): string
    {
        return Group::class;
    }
}
