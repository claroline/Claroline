<?php

namespace Claroline\CoreBundle\Entity\Model;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Gives an entity the ability to have an code.
 */
trait CodeTrait
{
    /**
     * @var string
     *
     * @ORM\Column("code", type="string", length=36, unique=true)
     */
    private $code;

    /**
     * Gets code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets code.
     *
     * @param $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    //default is same than uuid
    public function refreshCode()
    {
        $this->code = Uuid::uuid4()->toString();
    }
}
