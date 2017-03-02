<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\File;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_public_file_use")
 */
class PublicFileUse
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\File\PublicFile",
     *     inversedBy="publicFileUses"
     * )
     * @ORM\JoinColumn(name="public_file_id", onDelete="CASCADE")
     */
    protected $publicFile;

    /**
     * @ORM\Column(name="object_uuid")
     */
    protected $objectUuid;

    /**
     * @ORM\Column(name="object_class")
     */
    protected $objectClass;

    /**
     * @ORM\Column(name="object_name", nullable=true)
     */
    protected $objectName;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getPublicFile()
    {
        return $this->publicFile;
    }

    public function setPublicFile(PublicFile $publicFile)
    {
        $this->publicFile = $publicFile;
    }

    public function getObjectUuid()
    {
        return $this->objectUuid;
    }

    public function setObjectUuid($objectUuid)
    {
        $this->objectUuid = $objectUuid;
    }

    public function getObjectClass()
    {
        return $this->objectClass;
    }

    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;
    }

    public function getObjectName()
    {
        return $this->objectName;
    }

    public function setObjectName($objectName)
    {
        $this->objectName = $objectName;
    }
}
