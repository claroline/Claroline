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
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rule")
 */
class RuleController extends AbstractCrudController
{
    public function getName(): string
    {
        return 'rule';
    }

    public function getClass(): string
    {
        return Rule::class;
    }
}
