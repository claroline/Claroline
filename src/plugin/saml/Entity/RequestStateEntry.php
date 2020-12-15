<?php

namespace Claroline\SamlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_saml_request_entry")
 * @ORM\Entity()
 */
class RequestStateEntry
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="string", length=255, nullable=false)
     *
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $parameters;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return RequestStateEntry
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $parameters
     *
     * @return RequestStateEntry
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}
