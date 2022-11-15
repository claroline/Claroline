<?php

namespace Icap\BibliographyBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/book_reference_configuration")
 */
class BookReferenceConfigurationController extends AbstractCrudController
{
    public function getClass(): string
    {
        return 'Icap\BibliographyBundle\Entity\BookReferenceConfiguration';
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
        return 'bookReferenceConfiguration';
    }
}
