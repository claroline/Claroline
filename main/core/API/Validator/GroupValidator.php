<?php

namespace Claroline\CoreBundle\API\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.validator")
 */
class GroupValidator implements ValidatorInterface
{
    /**
     * GroupValidator constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $this->om->getRepository('Claroline\CoreBundle\Entity\Group');
    }

    public function validate($data)
    {
        return [];
    }

    //not sure yet if using this or deduce from getUnique()
    //note to myself: it should be deduced from getUnique
    public function validateBulk(array $users)
    {
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
