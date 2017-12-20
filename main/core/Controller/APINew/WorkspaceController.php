<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\CoreBundle\Annotations\ApiMeta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiMeta(class="Claroline\CoreBundle\Entity\Workspace\Workspace", ignore={})
 * @Route("/workspace")
 */
class WorkspaceController extends AbstractCrudController
{
    public function getName()
    {
        return 'workspace';
    }

    public function copyBulkAction(Request $request, $class)
    {
        //add params for the copy here
        $this->options['copyBulk'] = [];

        return parent::copyBulkAction($request, $class);
    }
}
