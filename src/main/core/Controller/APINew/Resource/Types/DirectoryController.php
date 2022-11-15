<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Resource\Types;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("resource_directory")
 */
class DirectoryController extends AbstractCrudController
{
    public function getClass(): string
    {
        return Directory::class;
    }

    public function getIgnore(): array
    {
        return ['create', 'exist', 'list', 'copyBulk', 'deleteBulk', 'find', 'get', 'csv'];
    }

    public function getName(): string
    {
        return 'resource_directory';
    }
}
