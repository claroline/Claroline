<?php

namespace Icap\BibliographyBundle\Controller\API;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @ApiMeta(
 *     class="Icap\BibliographyBundle\Entity\BookReferenceConfiguration",
 *     ignore={"find", "deleteBulk", "doc", "list", "get", "exist", "create", "copyBulk"}
 * )
 * @Route("/book_reference_configuration")
 * @Security("has_role('ROLE_ADMIN')")
 */
class BookReferenceConfigurationController extends AbstractCrudController
{
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
