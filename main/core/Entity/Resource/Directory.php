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

use Claroline\AppBundle\Entity\Parameters\ListParameters;
use Claroline\AppBundle\Entity\Parameters\SummaryParameters;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\DirectoryRepository")
 * @ORM\Table(name="claro_directory")
 */
class Directory extends AbstractResource
{
    use SummaryParameters;
    use ListParameters;

    /**
     * Is the directory the default upload destination (for tinyMCE and some other things).
     *
     * @ORM\Column(name="is_upload_destination", type="boolean")
     *
     * @var bool
     */
    private $uploadDestination = false;

    /**
     * @param bool $uploadDestination
     */
    public function setUploadDestination($uploadDestination)
    {
        $this->uploadDestination = $uploadDestination;
    }

    /**
     * @return bool
     */
    public function isUploadDestination()
    {
        return $this->uploadDestination;
    }
}
