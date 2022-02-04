<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/clacoform")
 */
class ClacoFormController extends AbstractCrudController
{
    public function getClass()
    {
        return ClacoForm::class;
    }

    public function getIgnore()
    {
        return ['create', 'deleteBulk', 'exist', 'list', 'copyBulk', 'schema', 'find', 'get'];
    }

    public function getName()
    {
        return 'clacoform';
    }
}
