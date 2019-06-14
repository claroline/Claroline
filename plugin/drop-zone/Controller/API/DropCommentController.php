<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\DropZoneBundle\Entity\DropComment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\Route("/dropcomment")
 */
class DropCommentController extends AbstractCrudController
{
    public function getName()
    {
        return 'dropcomment';
    }

    public function getClass()
    {
        return DropComment::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find'];
    }
}
