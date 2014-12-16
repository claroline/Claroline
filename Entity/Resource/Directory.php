<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * This entity is only an AbstractResource sub-type, with no additional attributes.
 *
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\DirectoryRepository")
 * @ORM\Table(name="claro_directory")
 */
class Directory extends AbstractResource
{
    /**
     * @ORM\Column(name="is_upload_destination", type="boolean")
     */
    protected $isUploadDestination = false;

    public function hasChildren()
    {
        return count($this->children) > 0 ? true : false;
    }

    public function setIsUploadDestination($boolean)
    {
        $this->isUploadDestination = $boolean;
    }

    public function getIsUploadDestination()
    {
        return $this->isUploadDestination;
    }

    public function isUploadDestination()
    {
        return $this->isUploadDestination;
    }
}
