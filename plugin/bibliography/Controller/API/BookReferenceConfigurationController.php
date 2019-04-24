<?php

namespace Icap\BibliographyBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/book_reference_configuration")
 * @Security("has_role('ROLE_ADMIN')")
 */
class BookReferenceConfigurationController extends AbstractCrudController
{
    public function getClass()
    {
        return 'Icap\BibliographyBundle\Entity\BookReferenceConfiguration';
    }

    public function getIgnore()
    {
        return ['find', 'deleteBulk', 'doc', 'list', 'get', 'exist', 'create', 'copyBulk'];
    }

    /**
     * Get the name of the managed entity.
     *
     * @return string
     */
    public function getName()
    {
        return 'bookReferenceConfiguration';
    }
}
