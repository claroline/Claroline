<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\Text;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiMeta(
 *     class="Claroline\CoreBundle\Entity\Resource\Text",
 *     ignore={"create", "exist", "list", "copyBulk", "deleteBulk", "schema", "find", "get"}
 * )
 * @EXT\Route("resource_text")
 */
class TextController extends AbstractCrudController
{
    /**
     * @EXT\Route(
     *     "/{id}/content",
     *     name="apiv2_resource_text_content"
     * )
     *
     * @param ResourceNode $resourceNode
     *
     * @return string
     */
    public function getContentAction(ResourceNode $resourceNode)
    {
        /** @var Text $text */
        $text = $this->om->getRepository($resourceNode->getClass())->findOneBy([
            'resourceNode' => $resourceNode,
        ]);

        if (empty($text)) {
            throw new NotFoundHttpException();
        }

        return new Response(
            $text->getContent()
        );
    }

    public function getName()
    {
        return 'resource_text';
    }
}
