<?php

namespace Claroline\CoreBundle\Tests\Stub\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_test_specific_resource_1")
 */
class SpecificResource1 extends AbstractResource
{
    /**
     * @ORM\Column(type="string", name="some_field", length=255)
     */
    private $someField;

    public function getSomeField()
    {
        return $this->someField;
    }

    public function setSomeField($value)
    {
        $this->someField = $value;
    }
}