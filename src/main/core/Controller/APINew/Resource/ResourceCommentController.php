<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceComment;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/resourcecomment")
 */
class ResourceCommentController extends AbstractCrudController
{
    public function getName(): string
    {
        return 'resourcecomment';
    }

    public function getClass(): string
    {
        return ResourceComment::class;
    }

    public function getIgnore(): array
    {
        return ['exist', 'copyBulk', 'schema', 'find'];
    }
}
