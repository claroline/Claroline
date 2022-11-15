<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\OpenBadgeBundle\Entity\Endorsement;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/endorsement")
 */
class EndorsementController extends AbstractCrudController
{
    public function getName(): string
    {
        return 'badge-endorsement';
    }

    public function getClass(): string
    {
        return Endorsement::class;
    }
}
