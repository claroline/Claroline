<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\BibliographyBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/book_reference")
 */
class BookReferenceController extends AbstractCrudController
{
    public function getClass(): string
    {
        return 'Icap\BibliographyBundle\Entity\BookReference';
    }

    public function getIgnore(): array
    {
        return ['find', 'deleteBulk', 'doc', 'list', 'get', 'exist', 'create', 'copyBulk'];
    }

    /**
     * Get the name of the managed entity.
     */
    public function getName(): string
    {
        return 'bookReference';
    }
}
