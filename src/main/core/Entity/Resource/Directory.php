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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_directory')]
#[ORM\Entity]
class Directory extends AbstractResource
{
    use ListParameters;

    /**
     * Is the directory the default upload destination (for tinyMCE and some other things).
     */
    #[ORM\Column(name: 'is_upload_destination', type: Types::BOOLEAN)]
    private bool $uploadDestination = false;

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

    public function setUploadDestination(bool $uploadDestination): void
    {
        $this->uploadDestination = $uploadDestination;
    }

    public function isUploadDestination(): bool
    {
        return $this->uploadDestination;
    }
}
