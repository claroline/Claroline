<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Controller\Contact;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\MessageBundle\Entity\Contact\Options;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contact_options")
 */
class OptionsController extends AbstractCrudController
{
    public function getClass(): string
    {
        return Options::class;
    }

    public function getName(): string
    {
        return 'contact_options';
    }
}
