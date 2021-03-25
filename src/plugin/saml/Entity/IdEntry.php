<?php

namespace Claroline\SamlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_saml_id_entry")
 * @ORM\Entity()
 */
class IdEntry
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="string", length=255, nullable=false)
     *
     * @var string
     */
    protected $entityId;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="string", length=255, nullable=false)
     *
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $expiryTimestamp;

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param string $entityId
     *
     * @return IdEntry
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiryTime()
    {
        $dt = new \DateTime();
        $dt->setTimestamp($this->expiryTimestamp);

        return $dt;
    }

    /**
     * @return IdEntry
     */
    public function setExpiryTime(\DateTime $expiryTime)
    {
        $this->expiryTimestamp = $expiryTime->getTimestamp();

        return $this;
    }

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
     * @return IdEntry
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
