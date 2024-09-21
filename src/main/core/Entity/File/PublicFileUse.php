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

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_public_file_use')]
#[ORM\Entity]
class PublicFileUse
{
    use Id;

    #[ORM\JoinColumn(name: 'public_file_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\File\PublicFile::class)]
    protected $publicFile;

    #[ORM\Column(name: 'object_uuid')]
    protected $objectUuid;

    #[ORM\Column(name: 'object_class')]
    protected $objectClass;

    #[ORM\Column(name: 'object_name', nullable: true)]
    protected $objectName;

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
