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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_directory")
 */
class Directory extends AbstractResource
{
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
     * Directory constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // set some list configuration defaults
        // can be done later in the resource.directory.create event
        $this->count = true;
        $this->card = ['icon', 'flags', 'subtitle', 'description', 'footer'];

        $this->availableColumns = ['name', 'published', 'resourceType'];
        $this->displayedColumns = ['name', 'published', 'resourceType'];

        $this->filterable = true;
        $this->searchMode = 'unified';
        $this->availableFilters = ['name', 'published', 'resourceType'];

        $this->sortable = true;
        $this->sortBy = 'name';
        $this->availableSort = ['name', 'resourceType'];
    }

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
