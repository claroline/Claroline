<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\DropZoneBundle\Entity\RevisionComment;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/revisioncomment", name="apiv2_revisioncomment_")
 */
class RevisionCommentController extends AbstractCrudController
{
    public static function getName(): string
    {
        return 'revisioncomment';
    }

    public static function getClass(): string
    {
        return RevisionComment::class;
    }
}
