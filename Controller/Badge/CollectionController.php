<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Badge;

use Claroline\CoreBundle\Entity\Badge\BadgeCollection;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/badge_collection")
 */
class CollectionController extends Controller
{
    /**
     * @Route("/{sharedId}", name="claro_badge_collection_share_view")
     */
    public function shareAction(BadgeCollection $badgeCollection)
    {
        die("SSSSSTTTTTTOOOOOOPPPPPPP" . PHP_EOL);
    }
}
